-- Semaine de Dialogue Interculturel de la Guinee Forestiere - Dortmund 2026
-- Schema MySQL 8+

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    organization VARCHAR(190) NULL,
    participation_type ENUM('participant','partner','speaker','sponsor') NOT NULL,
    gdpr_consent TINYINT(1) NOT NULL DEFAULT 0,
    language CHAR(2) NOT NULL DEFAULT 'fr',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reg_type (participation_type),
    INDEX idx_reg_email (email),
    INDEX idx_reg_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS donations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donor_name VARCHAR(150) NULL,
    donor_email VARCHAR(190) NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EUR',
    motive ENUM('general','logistics','youth','culture','other') NOT NULL,
    custom_motive VARCHAR(190) NULL,
    message TEXT NULL,
    payment_method ENUM('stripe','paypal','bank_transfer') NOT NULL,
    payment_provider_id VARCHAR(190) NULL,
    payment_status ENUM('pending','paid','failed','canceled') NOT NULL DEFAULT 'pending',
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    language CHAR(2) NOT NULL DEFAULT 'fr',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME NULL,
    INDEX idx_donation_status (payment_status),
    INDEX idx_donation_method (payment_method),
    INDEX idx_donation_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sponsor_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_name VARCHAR(190) NOT NULL,
    contact_person VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(60) NULL,
    website VARCHAR(255) NULL,
    sponsorship_level ENUM('bronze','silver','gold','strategic') NOT NULL DEFAULT 'bronze',
    message TEXT NULL,
    gdpr_consent TINYINT(1) NOT NULL DEFAULT 0,
    language CHAR(2) NOT NULL DEFAULT 'fr',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sponsor_level (sponsorship_level),
    INDEX idx_sponsor_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    subject VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    gdpr_consent TINYINT(1) NOT NULL DEFAULT 0,
    language CHAR(2) NOT NULL DEFAULT 'fr',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contact_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS partners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    website_url VARCHAR(255) NULL,
    logo_path VARCHAR(255) NULL,
    partner_type ENUM('partner','sponsor','institutional') NOT NULL DEFAULT 'partner',
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_partner_active (is_active),
    INDEX idx_partner_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS speakers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(190) NOT NULL,
    title VARCHAR(190) NULL,
    organization VARCHAR(190) NULL,
    bio TEXT NULL,
    photo_path VARCHAR(255) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS program_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    title_fr VARCHAR(255) NOT NULL,
    title_de VARCHAR(255) NOT NULL,
    description_fr TEXT NULL,
    description_de TEXT NULL,
    location VARCHAR(190) NULL,
    item_type ENUM('conference','panel','exhibition','networking','ceremony','workshop') NOT NULL,
    speaker_id INT UNSIGNED NULL,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_program_date (event_date),
    INDEX idx_program_type (item_type),
    INDEX idx_program_active (is_active),
    CONSTRAINT fk_program_speaker FOREIGN KEY (speaker_id) REFERENCES speakers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admins (full_name, email, password_hash, role)
VALUES ('Admin Dortmund 2026', 'admin@guineedortmund2026.org', '$2y$12$3IoV/If7m9b0iV1FtE3rxeRPHVAVuQLiN0IELuGc.lRJjmmgynd1e', 'super_admin')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'Semaine de Dialogue Interculturel de la Guinee Forestiere - Dortmund 2026'),
('site_domain', 'https://guineedortmund2026.org'),
('contact_email', 'contact@guineedortmund2026.org'),
('organizer_email', 'organisation@guineedortmund2026.org'),
('bank_holder', 'Association Guinee Forestiere Allemagne e.V.'),
('bank_iban', 'DE00 0000 0000 0000 0000 00'),
('bank_bic', 'GENODE00XXX'),
('bank_name', 'Banque Exemple Dortmund'),
('stripe_public_key', ''),
('stripe_secret_key', ''),
('paypal_business_email', ''),
('paypal_mode', 'sandbox'),
('currency', 'EUR'),
('collection_goal', '50000')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO speakers (full_name, title, organization, is_featured)
VALUES
('Dr. Fatou Kaba', 'Experte en politiques minières', 'Consultante independante', 1),
('Prof. Amadou Camara', 'Economiste du developpement', 'Universite de Conakry', 1),
('Mariam Diallo', 'Entrepreneure sociale', 'Jeunesse Forestiere Initiative', 1)
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);

INSERT INTO program_items (event_date, start_time, end_time, title_fr, title_de, description_fr, description_de, location, item_type, display_order, is_active)
VALUES
('2026-07-04', '09:00:00', '11:00:00', 'Ceremonie d\'ouverture officielle', 'Offizielle Eroffnungszeremonie', 'Ouverture de la semaine avec les institutions et la diaspora.', 'Auftaktwoche mit Institutionen und Diaspora.', 'Dortmund Centrum', 'ceremony', 1, 1),
('2026-07-05', '10:00:00', '12:00:00', 'Conference: Simandou 2040 et developpement durable', 'Konferenz: Simandou 2040 und nachhaltige Entwicklung', 'Etat des lieux et opportunites pour la Guinee Forestiere.', 'Bestandsaufnahme und Chancen fur Guinée Forestière.', 'Salle Konrad-Adenauer', 'conference', 2, 1),
('2026-07-07', '14:00:00', '16:00:00', 'Panel diaspora-investissement', 'Diaspora-Investitionspanel', 'Strategies d\'investissement responsable.', 'Strategien fur verantwortungsvolle Investitionen.', 'Westfalen Forum', 'panel', 3, 1),
('2026-07-09', '13:00:00', '18:00:00', 'Exposition culturelle et economique', 'Kulturelle und wirtschaftliche Ausstellung', 'Artisanat, culture, innovation et projets locaux.', 'Handwerk, Kultur, Innovation und lokale Projekte.', 'Expo Hall Dortmund', 'exhibition', 4, 1),
('2026-07-12', '18:30:00', '21:00:00', 'Soiree networking Afrique-Allemagne', 'Afrika-Deutschland Networking-Abend', 'Rencontres entre institutions, entreprises et diaspora.', 'Treffen zwischen Institutionen, Unternehmen und Diaspora.', 'Business Club Dortmund', 'networking', 5, 1),
('2026-07-13', '11:00:00', '13:00:00', 'Cloture et feuille de route 2026-2030', 'Abschluss und Roadmap 2026-2030', 'Restitution des recommandations et engagements.', 'Zusammenfassung der Empfehlungen und Verpflichtungen.', 'Dortmund Centrum', 'ceremony', 6, 1)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;
