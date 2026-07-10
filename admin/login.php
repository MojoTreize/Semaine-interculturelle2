<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

if (admin_is_logged_in()) {
    redirect('admin/index.php');
}

if (is_post()) {
    verify_csrf_or_fail();

    $email    = strtolower(post_string('email'));
    $password = post_string('password');

    if ($email === '' || $password === '') {
        set_flash('error', t('validation.required'));
        redirect('admin/login.php');
    }

    if (admin_login_throttled()) {
        set_flash('error', 'Trop de tentatives. Merci de reessayer dans une minute.');
        redirect('admin/login.php');
    }

    if (admin_attempt_login($pdo, $email, $password)) {
        redirect('admin/index.php');
    }

    set_flash('error', 'Identifiants invalides. Veuillez réessayer.');
    redirect('admin/login.php');
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Administration UGFA</title>
    <link rel="stylesheet" href="<?= e(base_url('assets/css/admin.css')) ?>">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f1e35 0%, #1a2e50 50%, #243656 100%);
            padding: 1rem;
        }

        .login-wrap {
            width: 100%;
            max-width: 420px;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-brand-logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            margin-bottom: 0.9rem;
        }

        .login-brand h1 {
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0 0 0.2rem;
            letter-spacing: 0.01em;
        }

        .login-brand p {
            color: rgba(255,255,255,0.5);
            font-size: 0.82rem;
            margin: 0;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem 2rem 1.6rem;
            box-shadow: 0 24px 60px rgba(0,0,0,0.35), 0 4px 12px rgba(0,0,0,0.2);
        }

        .login-card h2 {
            margin: 0 0 1.4rem;
            font-size: 1.18rem;
            color: #1a2433;
            font-weight: 700;
        }

        .login-field {
            margin-bottom: 1rem;
        }

        .login-field label {
            display: block;
            font-size: 0.83rem;
            font-weight: 600;
            color: #4f617e;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .login-field input {
            width: 100%;
            border: 1.5px solid #d0d8e8;
            border-radius: 10px;
            padding: 0.7rem 0.85rem;
            font-size: 0.96rem;
            color: #1a2433;
            background: #f8f9fc;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        .login-field input:focus {
            outline: none;
            border-color: #1f6eb0;
            box-shadow: 0 0 0 3px rgba(31,110,176,0.12);
            background: #fff;
        }

        .login-field .field-icon-wrap {
            position: relative;
        }

        .login-submit {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #1a3a6e 0%, #1f6eb0 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 0.5rem;
            letter-spacing: 0.02em;
            transition: opacity 0.2s, transform 0.15s;
        }

        .login-submit:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .login-submit:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.4rem;
            color: rgba(255,255,255,0.4);
            font-size: 0.78rem;
        }

        .alert {
            border-radius: 10px;
            padding: 0.7rem 0.9rem;
            margin-bottom: 1.1rem;
            font-size: 0.9rem;
        }

        .alert-success { background:#e6f4ec; border:1px solid #b8e0c5; color:#196a3b; }
        .alert-error   { background:#fae7e7; border:1px solid #f0b9b9; color:#8a2525; }
    </style>
</head>
<body>
<div class="login-wrap">

    <div class="login-brand">
        <img src="<?= e(base_url('assets/images/logo.jpeg')) ?>" alt="Logo UGFA" class="login-brand-logo">
        <h1>Union de la Guinée Forestière en Allemagne</h1>
        <p>Espace d'administration sécurisé</p>
    </div>

    <div class="login-card">
        <h2>Connexion administrateur</h2>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(admin_url('login.php')) ?>" autocomplete="off">
            <?= csrf_field() ?>

            <div class="login-field">
                <label for="email">Adresse e-mail</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autocomplete="username"
                    placeholder="votre@email.com"
                >
            </div>

            <div class="login-field">
                <label for="password">Mot de passe</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
            </div>

            <button type="submit" class="login-submit">Se connecter</button>
        </form>
    </div>

    <p class="login-footer">
        Dortmund 2026 &mdash; Accès réservé aux administrateurs
    </p>

</div>
</body>
</html>
