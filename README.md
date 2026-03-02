# Guinee Dortmund 2026 - Site officiel

Site institutionnel bilingue (FR/DE) en PHP natif pour:
- presentation de l evenement
- programme officiel
- inscriptions securisees
- contributions volontaires (Stripe, PayPal, virement)
- gestion partenaires/sponsors
- espace admin avec exports CSV/Excel

## 1. Stack technique

- PHP 8.1+
- MySQL 8+ (production)
- SQLite (mode dev local sans droits admin)
- JavaScript vanilla
- PHPMailer
- PhpSpreadsheet
- Apache/Nginx + HTTPS

## 2. Arborescence principale

```text
/
|-- admin/
|-- assets/
|   |-- css/
|   |-- js/
|   `-- images/
|-- config/
|-- database/
|-- includes/
|-- lang/
|-- uploads/
|-- index.php
|-- about.php
|-- program.php
|-- registration.php
|-- contribute.php
|-- partners.php
|-- contact.php
|-- privacy.php
|-- impressum.php
|-- payment_success.php
|-- payment_cancel.php
|-- sitemap.xml
|-- robots.txt
`-- .htaccess
```

## 3. Installation rapide

1. Cloner le projet dans le webroot.
2. Creer la base de donnees MySQL.
3. Importer le schema:
   - `database/schema.sql`
4. Copier `config/config.example.php` vers `config/config.php` puis adapter:
   - base_url
   - credentials MySQL
   - SMTP
   - Stripe/PayPal
5. Installer les dependances:
   - `composer install`
6. Donner les droits d ecriture sur `uploads/`.
7. Activer HTTPS et mod_rewrite (Apache) ou regles equivalentes (Nginx).

### Mode developpement sans droits admin (SQLite)

Ce mode ne requiert ni service MySQL, ni XAMPP.

1. Dans `config/config.php`, utiliser:
   - `db.driver = sqlite`
   - `db.path = dirname(__DIR__) . '/database/dev.sqlite'`
2. Activer les extensions SQLite (au choix):
   - Dans `php.ini`: decommenter `extension=sqlite3` et `extension=pdo_sqlite`
   - Ou au lancement:
     - `php -d extension=sqlite3 -d extension=pdo_sqlite -S 127.0.0.1:8000 -t .`
   - Ou via script:
     - `.\run-dev.ps1`
3. Ouvrir:
   - `http://127.0.0.1:8000/index.php`

Le schema SQLite (`database/schema.sqlite.sql`) est initialise automatiquement au premier lancement.

## 4. Compte admin initial

- Email: `admin@guineedortmund2026.org`
- Mot de passe: `Admin@2026`

Changer ce mot de passe immediatement en production.

## 5. Paiements

### Stripe Checkout
- Configurer `stripe_public_key` et `stripe_secret_key` dans:
  - `admin/settings.php` (site_settings)
  - ou `config/config.php`
- Le flux utilise une session Checkout Stripe server-side via cURL.

### PayPal
- Configurer `paypal_business_email` et `paypal_mode` (`sandbox` ou `live`).
- Retour de paiement sur `payment_success.php`.

### Virement bancaire
- Configurer IBAN/BIC/titulaire dans `admin/settings.php`.

## 6. Emails

Configurer SMTP dans `config/config.php`:
- `mail.use_smtp`
- `mail.smtp_host`
- `mail.smtp_port`
- `mail.smtp_user`
- `mail.smtp_pass`
- `mail.smtp_secure`

Les emails sont envoyes via PHPMailer si disponible, sinon fallback `mail()`.

## 7. Export CSV/Excel

- Export CSV: natif PHP
- Export Excel: PhpSpreadsheet (page `admin/registrations.php`)

## 8. RGPD, securite, SEO

### Deja implemente
- Consentement explicite dans les formulaires
- CSRF token
- Requetes preparees PDO
- Echappement HTML systematique
- Honeypot anti-spam
- Pages `privacy.php` et `impressum.php`
- Meta title/description + Open Graph
- Sitemap XML + robots.txt

### Recommandations production
1. Forcer HTTPS + HSTS.
2. Placer la config sensible hors webroot (ou via variables d environnement).
3. Restreindre l acces admin (IP allowlist, 2FA, fail2ban).
4. Activer logs de securite et rotation.
5. Mettre en place sauvegardes automatiques DB + fichiers.
6. Configurer webhook Stripe/PayPal pour confirmation stricte des paiements.
7. Ajouter CAPTCHA si volume spam eleve.

## 9. Deployment Apache

- Activer:
  - `mod_rewrite`
  - `mod_headers`
- Utiliser `.htaccess` fourni.

## 10. Deployment Nginx (exemple)

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Ajouter des regles d equivalence pour les URLs propres (`/about`, `/program`, etc.).
