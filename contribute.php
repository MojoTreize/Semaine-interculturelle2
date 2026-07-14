<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$whatsappRaw = trim((string) get_setting($pdo, 'whatsapp_number', ''));
$whatsappNum = preg_replace('/[^0-9+]/', '', $whatsappRaw);
$whatsappUrl = $whatsappNum !== '' ? 'https://wa.me/' . ltrim($whatsappNum, '+') : '';

$paypalClientId = paypal_client_id($pdo);
$paypalCurrency = payment_currency($pdo);

/* -- Page data ----------------------------------------------------------- */
$pageTitle       = t('seo.contribute_title');
$pageDescription = t('contribute.subtitle');

$totals      = collection_totals($pdo);
$goalAmount  = (float) get_setting($pdo, 'collection_goal', '10000');
$progressPct = $goalAmount > 0
    ? min(100.0, round(($totals['amount'] / $goalAmount) * 100, 1))
    : 0.0;

$bankHolder = get_setting($pdo, 'bank_holder', 'Nestor Mermoz Thea');
$bankIban   = get_setting($pdo, 'bank_iban', 'DE07 2687 0024 0335 0642 00');
$bankBic    = get_setting($pdo, 'bank_bic', 'DEUTDEDB268');
$bankName   = get_setting($pdo, 'bank_name', 'Deutsche Bank');

$donors = $pdo->query(
    "SELECT donor_name, amount, created_at FROM donations
     WHERE payment_status = 'paid'
     ORDER BY created_at DESC LIMIT 20"
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<?php if ($paypalClientId !== ''): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= e($paypalClientId) ?>&currency=<?= e($paypalCurrency) ?>&intent=capture&locale=fr_FR"></script>
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

        <!-- Main form card -->
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

            <!-- Form head -->
            <div class="contribute-form-head">
                <h2><?= e(t('contribute.form_title')) ?></h2>
                <p class="hint"><?= e(t('contribute.form_intro_simple')) ?></p>
            </div>

            <!-- Error / success messages -->
            <div id="donate-alert" hidden style="margin-bottom:1rem"></div>

            <div id="donate-form-body">
                <!-- Amount presets -->
                <div class="form-group" style="margin-bottom:1.1rem">
                    <label><?= e(t('contribute.amount')) ?></label>
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

                <!-- Nom + Email -->
                <div class="donate-identity-row">
                    <div class="donate-identity-field">
                        <label for="donor_name">Votre nom <span style="color:var(--color-red)">*</span></label>
                        <input type="text" id="donor_name" name="donor_name"
                               placeholder="Prénom et nom" autocomplete="name">
                        <p class="hint" id="name-error" hidden style="color:var(--color-red);margin-top:.3rem">
                            Veuillez indiquer votre nom.
                        </p>
                    </div>
                    <div class="donate-identity-field">
                        <label for="donor_email">Email <span class="hint">(optionnel)</span></label>
                        <input type="email" id="donor_email" name="donor_email"
                               placeholder="pour recevoir un reçu" autocomplete="email">
                    </div>
                </div>

                <!-- PayPal SDK button container -->
                <?php if ($paypalClientId !== ''): ?>
                <div id="paypal-button-container" style="margin-top:1.4rem"></div>
                <?php else: ?>
                <p class="alert alert-error" style="margin-top:1rem">
                    PayPal n'est pas encore configuré.
                    <a href="<?= e(admin_url('settings.php')) ?>">Configurer →</a>
                </p>
                <?php endif; ?>

                <p class="donate-paypal-note" style="margin-top:.6rem">
                    Paiement sécurisé via PayPal — vos données ne transitent pas par notre serveur.
                    <?php if ($whatsappUrl !== ''): ?>
                        — <a href="<?= e($whatsappUrl) ?>?text=Bonjour%2C+j%27ai+un+probl%C3%A8me+avec+ma+contribution+PayPal."
                             target="_blank" rel="noopener noreferrer" class="donate-whatsapp-inline">
                            Un problème ? WhatsApp
                        </a>
                    <?php endif; ?>
                </p>
            </div>

        </article>

        <!-- Side cards -->
        <div class="contribute-side">

            <!-- Donors who already paid -->
            <article class="card about-info-card contribute-donors-card" data-aos="fade-left">
                <h2 class="contribute-donors-title">
                    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"
                         aria-hidden="true" style="width:20px;height:20px;color:var(--color-red);flex-shrink:0">
                        <path d="M10 2l2.39 4.84 5.34.78-3.86 3.76.91 5.32L10 14.27l-4.78 2.51.91-5.32L2.27 7.62l5.34-.78L10 2z"
                              stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                    </svg>
                    Ils ont déjà contribué
                    <?php if (count($donors) > 0): ?>
                        <span class="donors-total-badge"><?= count($donors) ?></span>
                    <?php endif; ?>
                </h2>
                <?php if ($donors): ?>
                    <?php
                    $donorsJson = json_encode(array_map(static fn($d) => [
                        'name'   => (string) ($d['donor_name'] ?? ''),
                        'amount' => format_amount((float) $d['amount']),
                        'init'   => mb_strtoupper(mb_substr(trim((string) ($d['donor_name'] ?? 'X')), 0, 1)),
                    ], $donors), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
                    ?>
                    <ul class="donors-list" id="donors-list"></ul>
                    <?php if (count($donors) > 3): ?>
                        <div class="donors-nav">
                            <button type="button" class="donors-nav-btn" id="donors-prev" aria-label="Précédent" disabled>&#8249;</button>
                            <span class="donors-nav-page" id="donors-page">1 / <?= ceil(count($donors) / 3) ?></span>
                            <button type="button" class="donors-nav-btn" id="donors-next" aria-label="Suivant">&#8250;</button>
                        </div>
                    <?php endif; ?>
                    <script>
                    (function () {
                        var donors  = <?= $donorsJson ?>;
                        var perPage = 3;
                        var page    = 0;
                        var list    = document.getElementById('donors-list');
                        var prev    = document.getElementById('donors-prev');
                        var next    = document.getElementById('donors-next');
                        var pageEl  = document.getElementById('donors-page');
                        var total   = Math.ceil(donors.length / perPage);

                        function render() {
                            list.innerHTML = '';
                            donors.slice(page * perPage, page * perPage + perPage).forEach(function (d) {
                                var li = document.createElement('li');
                                li.className = 'donor-item';
                                li.innerHTML =
                                    '<div class="donor-avatar">' + d.init + '</div>' +
                                    '<div class="donor-info">' +
                                        '<span class="donor-name">' + d.name + '</span>' +
                                        '<span class="donor-amount">' + d.amount + '</span>' +
                                    '</div>';
                                list.appendChild(li);
                            });
                            if (prev) prev.disabled = page === 0;
                            if (next) next.disabled = page >= total - 1;
                            if (pageEl) pageEl.textContent = (page + 1) + ' / ' + total;
                        }

                        if (prev) prev.addEventListener('click', function () { if (page > 0) { page--; render(); } });
                        if (next) next.addEventListener('click', function () { if (page < total - 1) { page++; render(); } });
                        render();
                    })();
                    </script>
                <?php else: ?>
                    <p class="hint" style="text-align:center;padding:1rem 0">
                        Soyez le premier à contribuer !
                    </p>
                <?php endif; ?>
            </article>

            <!-- Bank transfer -->
            <article class="card about-info-card contribute-bank-card" data-aos="fade-left" data-aos-delay="80">
                <h2><?= e(t('contribute.bank_title')) ?></h2>
                <p><strong><?= e(t('contribute.bank_holder')) ?> :</strong> <?= e($bankHolder) ?></p>
                <p><strong><?= e(t('contribute.bank_iban')) ?> :</strong> <?= e($bankIban) ?></p>
                <p><strong><?= e(t('contribute.bank_bic')) ?> :</strong> <?= e($bankBic) ?></p>
                <p><strong><?= e(t('contribute.bank_name')) ?> :</strong> <?= e($bankName) ?></p>
                <p class="contribute-bank-reference">
                    <strong><?= e(t('contribute.bank_reference')) ?> :</strong>
                    <?= e(t('contribute.bank_reference_value')) ?>
                </p>
            </article>

            <!-- WhatsApp support -->
            <article class="card about-info-card contribute-whatsapp-card" data-aos="fade-left" data-aos-delay="140">
                <h3 class="contribute-whatsapp-title">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                         style="width:20px;height:20px;color:#25D366;flex-shrink:0">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Besoin d'aide ?
                </h3>
                <p class="hint">En cas de problème avec votre paiement ou pour toute question, contactez-nous directement via WhatsApp.</p>
                <?php if ($whatsappUrl !== ''): ?>
                    <a href="<?= e($whatsappUrl) ?>?text=Bonjour%2C+j%27ai+besoin+d%27aide+pour+ma+contribution."
                       class="btn-whatsapp" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             style="width:18px;height:18px;flex-shrink:0">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Contacter via WhatsApp
                    </a>
                <?php else: ?>
                    <p class="hint" style="font-size:.85rem">
                        Numéro WhatsApp non encore configuré.<br>
                        <a href="<?= e(admin_url('settings.php')) ?>">Configurer depuis l'admin →</a>
                    </p>
                <?php endif; ?>
            </article>

        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
(function () {
    /* ── Helpers ── */
    var amtInput = document.getElementById('amount-input');
    var amtError = document.getElementById('amount-error');
    var nameEl   = document.getElementById('donor_name');
    var nameErr  = document.getElementById('name-error');
    var alertEl  = document.getElementById('donate-alert');
    var presets  = document.querySelectorAll('.donate-preset-btn');

    function getAmount() {
        return parseFloat((amtInput && amtInput.value) || '0') || 0;
    }

    function showAmountError(show) {
        if (amtError) amtError.hidden = !show;
        if (amtInput) amtInput.classList.toggle('invalid', show);
    }

    function showNameError(show) {
        if (nameErr) nameErr.hidden = !show;
        if (nameEl)  nameEl.classList.toggle('invalid', show);
    }

    function showAlert(msg, type) {
        if (!alertEl) return;
        alertEl.className = 'alert alert-' + (type || 'error');
        alertEl.textContent = msg;
        alertEl.hidden = false;
        alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideAlert() {
        if (alertEl) alertEl.hidden = true;
    }

    function validateForm() {
        var ok = true;
        if (getAmount() < 1)                   { showAmountError(true); ok = false; }
        if (!nameEl || nameEl.value.trim() === '') { showNameError(true);  ok = false; }
        return ok;
    }

    /* ── Preset buttons ── */
    presets.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (amtInput) amtInput.value = btn.getAttribute('data-amount');
            presets.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            showAmountError(false);
            hideAlert();
        });
    });

    if (amtInput) {
        amtInput.addEventListener('input', function () {
            presets.forEach(function (b) { b.classList.remove('is-active'); });
            showAmountError(false);
        });
    }
    if (nameEl) {
        nameEl.addEventListener('input', function () { showNameError(false); });
    }

    /* ── PayPal SDK buttons ── */
    var container = document.getElementById('paypal-button-container');
    if (!container || typeof paypal === 'undefined') return;

    var _donationId = null;

    paypal.Buttons({
        style: {
            layout : 'vertical',
            color  : 'blue',
            shape  : 'rect',
            label  : 'donate',
            height : 48
        },

        onClick: function (data, actions) {
            hideAlert();
            if (!validateForm()) {
                return actions.reject();
            }
            return actions.resolve();
        },

        createOrder: function () {
            var lang = document.documentElement.lang || 'fr';
            return fetch('/api/payment/paypal/create', {
                method : 'POST',
                headers: {
                    'Content-Type'     : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    amount      : getAmount(),
                    donor_name  : nameEl ? nameEl.value.trim() : '',
                    donor_email : (document.getElementById('donor_email') || {}).value || '',
                    motive      : 'general',
                    language    : lang
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) throw new Error(data.error);
                _donationId = data.donationId;
                return data.id;
            });
        },

        onApprove: function (data) {
            return fetch('/api/payment/paypal/capture', {
                method : 'POST',
                headers: {
                    'Content-Type'     : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    orderID    : data.orderID,
                    donationId : _donationId
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (result.success) {
                    window.location.href = '/payment_success';
                } else {
                    showAlert('Paiement non confirmé par PayPal. Contactez-nous si vous avez été débité.', 'error');
                }
            })
            .catch(function () {
                showAlert('Erreur lors de la confirmation. Contactez-nous avec votre ID de transaction PayPal.', 'error');
            });
        },

        onCancel: function () {
            showAlert('Paiement annulé. Vous pouvez recommencer à tout moment.', 'info');
        },

        onError: function () {
            showAlert('Une erreur PayPal est survenue. Veuillez réessayer ou contacter le support.', 'error');
        }

    }).render('#paypal-button-container');
})();
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
