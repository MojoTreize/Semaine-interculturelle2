<?php
declare(strict_types=1);

$provider = isset($_GET['provider']) ? preg_replace('/[^a-z]/i', '', (string) $_GET['provider']) : '';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiement annulé</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }
        .box {
            background: #fff;
            border: 1px solid #dbe2ee;
            border-radius: 12px;
            padding: 24px;
            max-width: 520px;
            width: calc(100% - 32px);
        }
        a { color: #0b6bcb; }
    </style>
</head>
<body>
<section class="box">
    <h1>Paiement annulé</h1>
    <p>Le paiement n'a pas été finalisé.</p>
    <?php if ($provider !== ''): ?>
        <p>Fournisseur: <strong><?= htmlspecialchars($provider, ENT_QUOTES, 'UTF-8') ?></strong></p>
    <?php endif; ?>
    <p><a href="./checkout.php">Réessayer le paiement</a></p>
</section>
</body>
</html>
