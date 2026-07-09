<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$paypalPoolUrl = 'https://www.paypal.com/pool/9qyFAaYjtw?sr=wccr';
$adminEmail    = (string) app_config('admin.email', 'admin@ugfa.de');
$whatsappRaw   = trim((string) get_setting($pdo, 'whatsapp_number', ''));
$whatsappNum   = preg_replace('/[^0-9+]/', '', $whatsappRaw);
$whatsappUrl   = $whatsappNum !== '' ? 'https://wa.me/' . ltrim($whatsappNum, '+') : '';

/* â”€â”€ POST: save intent then redirect to PayPal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
if (is_post() && post_string('action') === 'initiate_donation') {
    verify_csrf_or_fail();

    $donorName = trim(post_string('donor_name'));
    $phone     = trim(post_string('phone'));
    $amount    = (float) post_string('amount');

    if ($donorName === '') {
        set_flash('error', 'Veuillez indiquer votre nom avant de continuer.');
        redirect('contribute');
    }
    if ($amount < 1) {
        set_flash('error', 'Veuillez choisir ou saisir un montant (minimum 1 â‚¬).');
        redirect('contribute');
    }

    $pdo->prepare('INSERT INTO donations (donor_name, donor_email, phone, amount, motive, payment_method, payment_status, created_at)
                   VALUES (:n, :e, :p, :a, :m, :pm, :s, :ca)')
        ->execute([
            'n'  => $donorName,
            'e'  => '',
            'p'  => $phone !== '' ? $phone : null,
            'a'  => $amount,
            'm'  => 'general',
            'pm' => 'paypal',
            's'  => 'pending',
            'ca' => db_now(),
        ]);

    $htmlBody = '<h2 style="color:#c61e31">Nouvelle contribution initiÃ©e â€” ' . $amount . ' â‚¬</h2>
        <table style="border-collapse:collapse;width:100%">
            <tr><td style="padding:.4rem .8rem;font-weight:600;width:140px">Nom</td><td style="padding:.4rem .8rem">' . htmlspecialchars($donorName, ENT_QUOTES) . '</td></tr>
            <tr style="background:#f8faff"><td style="padding:.4rem .8rem;font-weight:600">TÃ©lÃ©phone</td><td style="padding:.4rem .8rem">' . htmlspecialchars($phone ?: 'â€”', ENT_QUOTES) . '</td></tr>
            <tr><td style="padding:.4rem .8rem;font-weight:600">Montant</td><td style="padding:.4rem .8rem"><strong>' . $amount . ' â‚¬</strong></td></tr>
            <tr style="background:#f8faff"><td style="padding:.4rem .8rem;font-weight:600">MÃ©thode</td><td style="padding:.4rem .8rem">PayPal (cagnotte)</td></tr>
            <tr><td style="padding:.4rem .8rem;font-weight:600">Statut</td><td style="padding:.4rem .8rem;color:#e9a800">En attente de confirmation</td></tr>
        </table>
        <p style="margin-top:1rem;font-size:.9rem;color:#4f617e">Confirmez le paiement manuellement dans l\'espace admin aprÃ¨s vÃ©rification.</p>';

    send_email($adminEmail, 'Administrateur UGFA',
        "[UGFA] Contribution {$amount} â‚¬ â€” {$donorName}", $htmlBody);

    header('Location: ' . $paypalPoolUrl);
    exit;
}

/* â”€â”€ Page data â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
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

        <!-- â”€â”€ Main form card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
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

            <!-- Flash message -->
            <?php $flash = get_flash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" style="margin-bottom:1rem">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Form head -->
            <div class="contribute-form-head">
                <h2><?= e(t('contribute.form_title')) ?></h2>
                <p class="hint"><?= e(t('contribute.form_intro_simple')) ?></p>
            </div>

            <form method="post" id="donate-form" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="initiate_donation">
                <input type="hidden" name="amount" id="hidden-amount" value="">

                <!-- Amount presets -->
                <div class="form-group" style="margin-bottom:1.1rem">
                    <label><?= e(t('contribute.amount')) ?></label>
                    <div class="donate-presets">
                        <button type="button" class="donate-preset-btn" data-amount="10">10 â‚¬</button>
                        <button type="button" class="donate-preset-btn" data-amount="20">20 â‚¬</button>
                        <button type="button" class="donate-preset-btn" data-amount="50">50 â‚¬</button>
                        <button type="button" class="donate-preset-btn" data-amount="100">100 â‚¬</button>
                    </div>
                    <input id="amount-input" type="number" step="1" min="1" max="10000"
                           placeholder="<?= e(t('contribute.amount_custom')) ?>">
                    <p class="donate-amount-error hint" id="amount-error" hidden>
                        Veuillez sÃ©lectionner ou saisir un montant (min 1 â‚¬).
                    </p>
                </div>

                <!-- Name + Phone on the same line -->
                <div class="donate-identity-row">
                    <div class="donate-identity-field">
                        <label for="donor_name">Votre nom <span style="color:var(--color-red)">*</span></label>
                        <input type="text" id="donor_name" name="donor_name"
                               placeholder="PrÃ©nom et nom" required autocomplete="name">
                    </div>
                    <div class="donate-identity-field">
                        <label for="donor_phone">NumÃ©ro de tÃ©lÃ©phone</label>
                        <input type="tel" id="donor_phone" name="phone"
                               placeholder="+49 000 000 000" autocomplete="tel">
                    </div>
                </div>

                <!-- PayPal button -->
                <button type="submit" class="btn-paypal-cta donate-submit-btn" id="donate-submit">
                    <span class="pp-wordmark">
                        <span class="pp-blue">Pay</span><span class="pp-sky">Pal</span>
                    </span>
                    <?= e(t('contribute.pay_paypal')) ?>
                    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"
                         aria-hidden="true" style="width:18px;height:18px;flex-shrink:0">
                        <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <p class="donate-paypal-note" style="margin-top:.6rem">
                    <?= e(t('contribute.paypal_pool_note')) ?>
                    <?php if ($whatsappUrl !== ''): ?>
                        â€” <a href="<?= e($whatsappUrl) ?>?text=Bonjour%2C+j%27ai+un+probl%C3%A8me+avec+ma+contribution+PayPal."
                             target="_blank" rel="noopener noreferrer" class="donate-whatsapp-inline">
                            Un problÃ¨me ? WhatsApp
                        </a>
                    <?php endif; ?>
                </p>
            </form>

        </article>

        <!-- â”€â”€ Side cards â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
        <div class="contribute-side">

            <!-- Donors who already paid -->
            <article class="card about-info-card contribute-donors-card" data-aos="fade-left">
                <h2 class="contribute-donors-title">
                    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"
                         aria-hidden="true" style="width:20px;height:20px;color:var(--color-red);flex-shrink:0">
                        <path d="M10 2l2.39 4.84 5.34.78-3.86 3.76.91 5.32L10 14.27l-4.78 2.51.91-5.32L2.27 7.62l5.34-.78L10 2z"
                              stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                    </svg>
                    Ils ont dÃ©jÃ  contribuÃ©
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
                            <button type="button" class="donors-nav-btn" id="donors-prev" aria-label="PrÃ©cÃ©dent" disabled>&#8249;</button>
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
                            var slice = donors.slice(page * perPage, page * perPage + perPage);
                            slice.forEach(function (d) {
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
                        Soyez le premier Ã  contribuer !
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
                    <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                         aria-hidden="true" style="width:20px;height:20px;color:#25D366;flex-shrink:0">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Besoin d'aide ?
                </h3>
                <p class="hint">En cas de problÃ¨me avec votre paiement ou pour toute question, contactez-nous directement via WhatsApp.</p>
                <?php if ($whatsappUrl !== ''): ?>
                    <a href="<?= e($whatsappUrl) ?>?text=Bonjour%2C+j%27ai+besoin+d%27aide+pour+ma+contribution+%C3%A0+l%27UGFA."
                       class="btn-whatsapp" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             style="width:18px;height:18px;flex-shrink:0">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Contacter via WhatsApp
                    </a>
                <?php else: ?>
                    <p class="hint" style="font-size:.85rem">
                        NumÃ©ro WhatsApp non encore configurÃ©.<br>
                        <a href="<?= e(admin_url('settings.php')) ?>">Configurer depuis l'admin â†’</a>
                    </p>
                <?php endif; ?>
            </article>

        </div>
    </div>
</section>

<script>
(function () {
    var presets   = document.querySelectorAll('.donate-preset-btn');
    var amtInput  = document.getElementById('amount-input');
    var hiddenAmt = document.getElementById('hidden-amount');
    var amtError  = document.getElementById('amount-error');
    var form      = document.getElementById('donate-form');

    function setAmount(val) {
        if (hiddenAmt) hiddenAmt.value = val > 0 ? val : '';
    }

    function getAmount() {
        return parseFloat((amtInput && amtInput.value) || '0') || 0;
    }

    function showError(show) {
        if (amtError) amtError.hidden = !show;
        if (amtInput) amtInput.classList.toggle('invalid', show);
    }

    // Preset click
    presets.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var val = parseFloat(btn.getAttribute('data-amount')) || 0;
            if (amtInput) amtInput.value = btn.getAttribute('data-amount');
            presets.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            showError(false);
            setAmount(val);
        });
    });

    // Free input
    if (amtInput) {
        amtInput.addEventListener('input', function () {
            presets.forEach(function (b) { b.classList.remove('is-active'); });
            showError(false);
            setAmount(getAmount());
        });
    }

    // Form submit validation
    if (form) {
        form.addEventListener('submit', function (e) {
            var amount = getAmount();
            var name   = (document.getElementById('donor_name') || {}).value || '';
            var ok = true;

            if (amount < 1) { showError(true); ok = false; }
            if (name.trim() === '') {
                var nameEl = document.getElementById('donor_name');
                if (nameEl) { nameEl.classList.add('invalid'); nameEl.focus(); }
                ok = false;
            }

            if (!ok) { e.preventDefault(); return; }

            setAmount(amount);
            var btn = document.getElementById('donate-submit');
            if (btn) { btn.disabled = true; btn.style.opacity = '0.75'; }
        });

        var nameEl = document.getElementById('donor_name');
        if (nameEl) {
            nameEl.addEventListener('input', function () {
                nameEl.classList.remove('invalid');
            });
        }
    }
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>

