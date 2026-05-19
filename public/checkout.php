<?php
declare(strict_types=1);

require_once __DIR__ . '/../server/config.php';

$amountLabel = number_format(ORDER_AMOUNT_CENTS / 100, 2, ',', ' ');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=<?= urlencode(PAYPAL_CLIENT_ID) ?>&currency=<?= urlencode(strtoupper(ORDER_CURRENCY)) ?>&intent=capture"></script>
    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #0b6bcb;
            --border: #dbe2ee;
            --error: #b91c1c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            color: var(--text);
            background: linear-gradient(145deg, #eef2f9 0%, #f7f9fc 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
        }
        .wrap {
            width: 100%;
            max-width: 820px;
            display: grid;
            gap: 16px;
        }
        .summary, .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        }
        h1, h2 {
            margin: 0 0 12px;
        }
        .amount {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--accent);
        }
        .muted {
            color: var(--muted);
            font-size: 0.95rem;
        }
        .card-option {
            display: grid;
            gap: 12px;
        }
        button {
            border: 0;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 1rem;
            cursor: pointer;
        }
        button.primary {
            background: var(--accent);
            color: #fff;
        }
        button.primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        #stripe-form {
            display: none;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--border);
        }
        #payment-element {
            margin-bottom: 12px;
        }
        #status {
            min-height: 24px;
            color: var(--muted);
        }
        .error {
            color: var(--error) !important;
        }
    </style>
</head>
<body>
<main class="wrap">
    <section class="summary">
        <h1>Paiement</h1>
        <p class="muted">Montant défini côté serveur</p>
        <p class="amount"><?= htmlspecialchars($amountLabel, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars(strtoupper(ORDER_CURRENCY), ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <section class="card">
        <h2>1. Payer par carte (Stripe)</h2>
        <div class="card-option">
            <button id="start-stripe" class="primary" type="button">Continuer avec la carte</button>
            <form id="stripe-form">
                <div id="payment-element"></div>
                <button id="stripe-submit" class="primary" type="submit">Payer maintenant</button>
            </form>
        </div>
    </section>

    <section class="card">
        <h2>2. Payer avec PayPal</h2>
        <div id="paypal-button-container"></div>
    </section>

    <p id="status" class="muted"></p>
</main>

<script>
(() => {
    const statusEl = document.getElementById('status');
    const startStripeButton = document.getElementById('start-stripe');
    const stripeForm = document.getElementById('stripe-form');
    const stripeSubmitButton = document.getElementById('stripe-submit');

    let stripe = null;
    let elements = null;
    let localStripeOrderId = null;
    let stripeInitialized = false;
    const successBaseUrl = new URL('./success.php', window.location.href);

    const setStatus = (message, isError = false) => {
        statusEl.textContent = message;
        statusEl.classList.toggle('error', isError);
    };

    async function initStripe() {
        if (stripeInitialized) {
            stripeForm.style.display = 'block';
            return;
        }

        setStatus('Initialisation du paiement Stripe...');
        startStripeButton.disabled = true;

        const response = await fetch('../server/stripe_create_intent.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });

        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.error || 'Erreur Stripe.');
        }

        stripe = Stripe('<?= htmlspecialchars(STRIPE_PUBLISHABLE_KEY, ENT_QUOTES, 'UTF-8') ?>');
        elements = stripe.elements({ clientSecret: payload.clientSecret });
        const paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');

        localStripeOrderId = payload.orderId;
        stripeInitialized = true;
        stripeForm.style.display = 'block';
        setStatus('');
    }

    startStripeButton.addEventListener('click', async () => {
        try {
            await initStripe();
        } catch (error) {
            setStatus(error.message || 'Impossible de démarrer Stripe.', true);
            startStripeButton.disabled = false;
        }
    });

    stripeForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!stripe || !elements) {
            setStatus('Stripe non initialisé.', true);
            return;
        }

        stripeSubmitButton.disabled = true;
        setStatus('Confirmation du paiement...');

        const { error, paymentIntent } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: `${successBaseUrl.origin}${successBaseUrl.pathname}?provider=stripe&order_id=${encodeURIComponent(localStripeOrderId || '')}`
            },
            redirect: 'if_required'
        });

        stripeSubmitButton.disabled = false;

        if (error) {
            setStatus(error.message || 'Paiement Stripe échoué.', true);
            return;
        }

        if (paymentIntent && paymentIntent.status === 'succeeded') {
            window.location.href = `./success.php?provider=stripe&order_id=${encodeURIComponent(localStripeOrderId || '')}`;
            return;
        }

        setStatus('Paiement en cours de traitement.');
    });

    paypal.Buttons({
        createOrder: async () => {
            setStatus('Création de la commande PayPal...');
            const response = await fetch('../server/paypal_create_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            });

            const payload = await response.json();
            if (!response.ok || !payload.id) {
                throw new Error(payload.error || 'Impossible de créer la commande PayPal.');
            }

            setStatus('');
            return payload.id;
        },
        onApprove: async (data) => {
            setStatus('Capture du paiement PayPal...');
            const response = await fetch('../server/paypal_capture_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orderID: data.orderID })
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                setStatus(payload.error || 'Capture PayPal échouée.', true);
                return;
            }

            window.location.href = `./success.php?provider=paypal&order_id=${encodeURIComponent(payload.orderId)}`;
        },
        onCancel: () => {
            window.location.href = './cancel.php?provider=paypal';
        },
        onError: () => {
            setStatus('Une erreur est survenue pendant le paiement PayPal.', true);
        }
    }).render('#paypal-button-container');
})();
</script>
</body>
</html>
