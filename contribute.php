<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.contribute_title');
$pageDescription = t('contribute.subtitle');

$paypalPoolUrl = 'https://www.paypal.com/pool/9qyFAaYjtw?sr=wccr';

$totals     = collection_totals($pdo);
$goalAmount = (float) get_setting($pdo, 'collection_goal', '10000');
$progressPct = $goalAmount > 0
    ? min(100.0, round(($totals['amount'] / $goalAmount) * 100, 1))
    : 0.0;

$bankHolder = get_setting($pdo, 'bank_holder', 'Association Guinee Forestiere Allemagne e.V.');
$bankIban   = get_setting($pdo, 'bank_iban', 'DE00 0000 0000 0000 0000 00');
$bankBic    = get_setting($pdo, 'bank_bic', 'GENODE00XXX');
$bankName   = get_setting($pdo, 'bank_name', 'Banque Exemple Dortmund');

require __DIR__ . '/includes/header.php';
?>

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
                <label><?= e(t('contribute.amount')) ?></label>
                <div class="donate-presets">
                    <button type="button" class="donate-preset-btn" data-amount="10">10 €</button>
                    <button type="button" class="donate-preset-btn" data-amount="20">20 €</button>
                    <button type="button" class="donate-preset-btn" data-amount="50">50 €</button>
                    <button type="button" class="donate-preset-btn" data-amount="100">100 €</button>
                </div>
                <input id="amount-input" type="number" step="1" min="1"
                       placeholder="<?= e(t('contribute.amount_custom')) ?>">
            </div>

            <!-- PayPal pool CTA -->
            <div class="donate-actions">
                <a href="<?= e($paypalPoolUrl) ?>"
                   class="btn-paypal-cta"
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
            </div>

            <script>
            (function () {
                var presets = document.querySelectorAll('.donate-preset-btn');
                var input = document.getElementById('amount-input');
                if (!input) return;
                presets.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        input.value = btn.getAttribute('data-amount');
                        presets.forEach(function (b) { b.classList.remove('is-active'); });
                        btn.classList.add('is-active');
                    });
                });
                input.addEventListener('input', function () {
                    presets.forEach(function (b) { b.classList.remove('is-active'); });
                });
            })();
            </script>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
