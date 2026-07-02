<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.contribute_title');
$pageDescription = t('contribute.subtitle');

$paypalClientId  = paypal_client_id($pdo);
$paypalMode      = paypal_mode($pdo);
$stripePublicKey = stripe_public_key($pdo);
$paypalPoolUrl   = 'https://www.paypal.com/pool/9qyFAaYjtw?sr=wccr';
$currency        = payment_currency($pdo);

$totals      = collection_totals($pdo);
$goalAmount  = (float) get_setting($pdo, 'collection_goal', '10000');
$progressPct = $goalAmount > 0
    ? min(100.0, round(($totals['amount'] / $goalAmount) * 100, 1))
    : 0.0;

$bankHolder = get_setting($pdo, 'bank_holder', 'Nestor Mermoz Thea');
$bankIban   = get_setting($pdo, 'bank_iban', 'DE07 2687 0024 0335 0642 00');
$bankBic    = get_setting($pdo, 'bank_bic', 'DEUTDEDB268');
$bankName   = get_setting($pdo, 'bank_name', 'Deutsche Bank');

$useSmartButtons = $paypalClientId !== '';

require __DIR__ . '/includes/header.php';
?>

<?php if ($useSmartButtons): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= e($paypalClientId) ?>&currency=<?= e($currency) ?>&intent=capture&locale=fr_FR&enable-funding=card" crossorigin="anonymous"></script>
<?php endif; ?>

<section class="section contribute-intro-section">
    <div class="container">
        <div class="about-section-head contribute-intro-shell" data-aos="fade-up">
            <h1><?= e(t('contribute.title')) ?></h1>
            <p class="lead"><?= e(t('contribute.subtitle')) ?></p>
        </div>
    </div>
</section>

<section class="section about-roadmap-section">
    <div class="container contribute-layout">

        <article class="form-card contribute-form-card" data-aos="fade-right">

            <!-- Progress bar -->
            <div class="donate-progress-block">
                <div class="donate-prog-row">
                    <strong class="donate-prog-collected"><?= e(format_amount($totals['amount'])) ?></strong>
                    <span class="donate-prog-goal-label"><?= e(t('contribute.goal')) ?> : <?= e(format_amount($goalAmount)) ?></span>
                </div>
                <div class="donate-prog-track">
                    <div class="donate-prog-fill" style="width:<?= e(number_format($progressPct, 1, '.', '')) ?>%"></div>
                </div>
                <div class="donate-prog-meta">
                    <span><?= e((string) $totals['count']) ?> <?= e(t('contribute.donors_count')) ?></span>
                    <span class="donate-prog-pct"><?= e(number_format($progressPct, 0)) ?>%</span>
                </div>
            </div>

            <!-- Head -->
            <div class="contribute-form-head">
                <h2><?= e(t('contribute.form_title')) ?></h2>
                <p class="hint"><?= e(t('contribute.form_intro_simple')) ?></p>
            </div>

            <!-- Amount presets -->
            <div class="form-group">
                <label for="amount-input"><?= e(t('contribute.amount')) ?></label>
                <div class="donate-presets">
                    <button type="button" class="donate-preset-btn" data-amount="10">10 €</button>
                    <button type="button" class="donate-preset-btn" data-amount="20">20 €</button>
                    <button type="button" class="donate-preset-btn" data-amount="50">50 €</button>
                    <button type="button" class="donate-preset-btn" data-amount="100">100 €</button>
                </div>
                <input id="amount-input" type="number" step="1" min="1" max="10000"
                       placeholder="<?= e(t('contribute.amount_custom')) ?>">
                <p class="donate-amount-error hint" id="amount-error" hidden>
                    Veuillez sélectionner ou saisir un montant (min 1 €).
                </p>
            </div>

            <!-- Payment area -->
            <div class="donate-actions" id="donate-actions">

                <?php if ($useSmartButtons): ?>

                <!-- PayPal Smart Buttons (PayPal + Visa/Mastercard inline) -->
                <div id="paypal-button-container" class="donate-paypal-sdk"></div>

                <?php if ($stripePublicKey !== ''): ?>
                <div class="donate-or"><span><?= e(t('contribute.donate_or')) ?></span></div>
                <button type="button" class="btn btn-primary donate-stripe-btn" id="stripe-pay-btn">
                    <?= e(t('contribute.pay_card')) ?>
                </button>
                <?php endif; ?>

                <?php else: ?>

                <!-- Fallback : lien cagnotte PayPal -->
                <a href="<?= e($paypalPoolUrl) ?>"
                   class="btn-paypal-cta"
                   id="paypal-pool-btn"
                   target="_blank"
                   rel="noopener noreferrer">
                    <span class="pp-wordmark">
                        <span class="pp-blue">Pay</span><span class="pp-sky">Pal</span>
                    </span>
                    <?= e(t('contribute.pay_paypal')) ?>
                    <svg class="paypal-arrow" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <p class="donate-paypal-note"><?= e(t('contribute.paypal_pool_note')) ?></p>

                <?php if ($stripePublicKey !== ''): ?>
                <div class="donate-or"><span><?= e(t('contribute.donate_or')) ?></span></div>
                <button type="button" class="btn btn-primary donate-stripe-btn" id="stripe-pay-btn">
                    <?= e(t('contribute.pay_card')) ?>
                </button>
                <?php endif; ?>

                <?php endif; ?>

            </div>

            <!-- Success message (shown after PayPal Smart Buttons complete) -->
            <div class="donate-success-block" id="donate-success" hidden>
                <div class="donate-success-icon">✓</div>
                <h3><?= e(t('contribute.thanks_paid')) ?></h3>
                <p class="hint">Don enregistré et validé.</p>
            </div>

        </article>

        <div class="contribute-side">
            <article class="card about-info-card contribute-bank-card" data-aos="fade-left" data-aos-delay="80">
                <h2><?= e(t('contribute.bank_title')) ?></h2>
                <p class="hint"><?= e(t('contribute.bank_intro')) ?></p>
                <p><strong><?= e(t('contribute.bank_holder')) ?> :</strong> <?= e($bankHolder) ?></p>
                <p><strong><?= e(t('contribute.bank_iban')) ?> :</strong> <?= e($bankIban) ?></p>
                <p><strong><?= e(t('contribute.bank_bic')) ?> :</strong> <?= e($bankBic) ?></p>
                <p><strong><?= e(t('contribute.bank_name')) ?> :</strong> <?= e($bankName) ?></p>
            </article>
        </div>

    </div>
</section>

<script>
(function () {
    var presets    = document.querySelectorAll('.donate-preset-btn');
    var input      = document.getElementById('amount-input');
    var poolBtn    = document.getElementById('paypal-pool-btn');
    var amountErr  = document.getElementById('amount-error');
    var poolBase   = poolBtn ? poolBtn.getAttribute('href') : '';

    function getAmount() {
        return parseFloat((input && input.value) || '0') || 0;
    }

    function showAmountError(show) {
        if (amountErr) amountErr.hidden = !show;
        if (input) input.classList.toggle('invalid', show);
    }

    function updatePoolUrl(val) {
        if (!poolBtn || !poolBase) return;
        poolBtn.setAttribute('href', val > 0 ? poolBase + '&amount=' + val.toFixed(2) : poolBase);
    }

    // Preset buttons
    presets.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var val = parseFloat(btn.getAttribute('data-amount')) || 0;
            if (input) input.value = btn.getAttribute('data-amount');
            presets.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            showAmountError(false);
            updatePoolUrl(val);
        });
    });

    // Free input
    if (input) {
        input.addEventListener('input', function () {
            presets.forEach(function (b) { b.classList.remove('is-active'); });
            showAmountError(false);
            updatePoolUrl(getAmount());
        });
    }

    // PayPal Smart Buttons
    if (window.paypal) {
        paypal.Buttons({
            style: {
                layout : 'vertical',
                color  : 'gold',
                shape  : 'rect',
                label  : 'pay',
                height : 50,
            },

            onClick: function (data, actions) {
                if (getAmount() < 1) {
                    showAmountError(true);
                    if (input) input.focus();
                    return actions.reject();
                }
                return actions.resolve();
            },

            createOrder: function () {
                return fetch('/api/payment/paypal/create', {
                    method : 'POST',
                    headers: {
                        'Content-Type'     : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ amount: getAmount() }),
                })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.error) throw new Error(d.error);
                    window._donateId = d.donationId;
                    return d.id;
                });
            },

            onApprove: function (data) {
                return fetch('/api/payment/paypal/capture', {
                    method : 'POST',
                    headers: {
                        'Content-Type'     : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ orderID: data.orderID, donationId: window._donateId }),
                })
                .then(function (r) { return r.json(); })
                .then(function (result) {
                    if (result.success) {
                        document.getElementById('donate-actions').hidden = true;
                        document.getElementById('donate-success').hidden = false;
                    } else {
                        alert(result.error || 'Erreur lors du paiement.');
                    }
                });
            },

            onError: function () {
                alert('Une erreur PayPal est survenue. Veuillez réessayer.');
            },
        }).render('#paypal-button-container');
    }

    // Stripe pay button
    var stripeBtn = document.getElementById('stripe-pay-btn');
    if (stripeBtn) {
        stripeBtn.addEventListener('click', function () {
            var amount = getAmount();
            if (amount < 1) { showAmountError(true); if (input) input.focus(); return; }

            stripeBtn.disabled    = true;
            stripeBtn.textContent = 'Chargement…';

            fetch('/api/payment/stripe/session', {
                method : 'POST',
                headers: {
                    'Content-Type'     : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest',
                },
                body: JSON.stringify({ amount: amount }),
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.url) {
                    window.location.href = d.url;
                } else {
                    alert(d.error || 'Stripe indisponible.');
                    stripeBtn.disabled    = false;
                    stripeBtn.textContent = '<?= e(t('contribute.pay_card')) ?>';
                }
            })
            .catch(function () {
                alert('Erreur réseau. Veuillez réessayer.');
                stripeBtn.disabled    = false;
                stripeBtn.textContent = '<?= e(t('contribute.pay_card')) ?>';
            });
        });
    }
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
