-- Semaine de Dialogue Interculturel de la Guinee Forestiere - Dortmund 2026
-- Schema SQLite (dev local sans MySQL)

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'editor' CHECK (role IN ('super_admin', 'editor')),
    is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)),
    last_login_at TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS registrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    country TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT NOT NULL,
    organization TEXT NULL,
    participation_type TEXT NOT NULL CHECK (participation_type IN ('participant', 'partner', 'speaker', 'sponsor')),
    gdpr_consent INTEGER NOT NULL DEFAULT 0 CHECK (gdpr_consent IN (0, 1)),
    language TEXT NOT NULL DEFAULT 'fr',
    ip_address TEXT NULL,
    user_agent TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_reg_type ON registrations (participation_type);
CREATE INDEX IF NOT EXISTS idx_reg_email ON registrations (email);
CREATE INDEX IF NOT EXISTS idx_reg_created ON registrations (created_at);

CREATE TABLE IF NOT EXISTS donations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    donor_name TEXT NULL,
    donor_email TEXT NULL,
    amount NUMERIC NOT NULL,
    currency TEXT NOT NULL DEFAULT 'EUR',
    motive TEXT NOT NULL CHECK (motive IN ('general', 'logistics', 'youth', 'culture', 'other')),
    custom_motive TEXT NULL,
    message TEXT NULL,
    payment_method TEXT NOT NULL CHECK (payment_method IN ('stripe', 'paypal', 'bank_transfer')),
    payment_provider_id TEXT NULL,
    payment_status TEXT NOT NULL DEFAULT 'pending' CHECK (payment_status IN ('pending', 'paid', 'failed', 'canceled')),
    is_public INTEGER NOT NULL DEFAULT 1 CHECK (is_public IN (0, 1)),
    language TEXT NOT NULL DEFAULT 'fr',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at TEXT NULL
);
CREATE INDEX IF NOT EXISTS idx_donation_status ON donations (payment_status);
CREATE INDEX IF NOT EXISTS idx_donation_method ON donations (payment_method);
CREATE INDEX IF NOT EXISTS idx_donation_created ON donations (created_at);

CREATE TABLE IF NOT EXISTS sponsor_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    organization_name TEXT NOT NULL,
    contact_person TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT NULL,
    website TEXT NULL,
    sponsorship_level TEXT NOT NULL DEFAULT 'bronze' CHECK (sponsorship_level IN ('bronze', 'silver', 'gold', 'strategic')),
    message TEXT NULL,
    gdpr_consent INTEGER NOT NULL DEFAULT 0 CHECK (gdpr_consent IN (0, 1)),
    language TEXT NOT NULL DEFAULT 'fr',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_sponsor_level ON sponsor_requests (sponsorship_level);
CREATE INDEX IF NOT EXISTS idx_sponsor_created ON sponsor_requests (created_at);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL,
    subject TEXT NOT NULL,
    message TEXT NOT NULL,
    gdpr_consent INTEGER NOT NULL DEFAULT 0 CHECK (gdpr_consent IN (0, 1)),
    language TEXT NOT NULL DEFAULT 'fr',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_contact_created ON contact_messages (created_at);

CREATE TABLE IF NOT EXISTS page_views (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_path TEXT NOT NULL,
    page_title TEXT NULL,
    referrer TEXT NULL,
    language TEXT NOT NULL DEFAULT 'fr',
    session_id TEXT NULL,
    ip_address TEXT NULL,
    user_agent TEXT NULL,
    viewed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_page_views_date ON page_views (viewed_at);
CREATE INDEX IF NOT EXISTS idx_page_views_path ON page_views (page_path);
CREATE INDEX IF NOT EXISTS idx_page_views_lang ON page_views (language);

CREATE TABLE IF NOT EXISTS partners (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    website_url TEXT NULL,
    logo_path TEXT NULL,
    partner_type TEXT NOT NULL DEFAULT 'partner' CHECK (partner_type IN ('partner', 'sponsor', 'institutional')),
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_partner_active ON partners (is_active);
CREATE INDEX IF NOT EXISTS idx_partner_order ON partners (display_order);

CREATE TABLE IF NOT EXISTS speakers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    title TEXT NULL,
    organization TEXT NULL,
    bio TEXT NULL,
    photo_path TEXT NULL,
    is_featured INTEGER NOT NULL DEFAULT 0 CHECK (is_featured IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS program_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_date TEXT NOT NULL,
    start_time TEXT NULL,
    end_time TEXT NULL,
    title_fr TEXT NOT NULL,
    title_de TEXT NOT NULL,
    description_fr TEXT NULL,
    description_de TEXT NULL,
    location TEXT NULL,
    item_type TEXT NOT NULL CHECK (item_type IN ('conference', 'panel', 'exhibition', 'networking', 'ceremony', 'workshop')),
    speaker_id INTEGER NULL,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (speaker_id) REFERENCES speakers(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_program_date ON program_items (event_date);
CREATE INDEX IF NOT EXISTS idx_program_type ON program_items (item_type);
CREATE INDEX IF NOT EXISTS idx_program_active ON program_items (is_active);

CREATE TABLE IF NOT EXISTS site_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT NOT NULL UNIQUE,
    setting_value TEXT NULL,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (full_name, email, password_hash, role)
VALUES ('Admin Dortmund 2026', 'admin@guineedortmund2026.org', '$2y$12$nLiXAZ.m6abolKwk99R1su12fsGidXN1e3cP4d9ClNK.ub0IqZXDK', 'super_admin')
ON CONFLICT(email) DO UPDATE SET updated_at = CURRENT_TIMESTAMP;

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
ON CONFLICT(setting_key) DO UPDATE SET
    setting_value = excluded.setting_value,
    updated_at = CURRENT_TIMESTAMP;

INSERT OR IGNORE INTO partners (name, website_url, partner_type, display_order, is_active)
VALUES
('Ville de Dortmund', 'https://www.dortmund.de', 'institutional', 1, 1),
('Diaspora Guinee Forestiere Allemagne', '#', 'partner', 2, 1),
('Chambre de Commerce Guinee - Allemagne', '#', 'partner', 3, 1);

INSERT OR IGNORE INTO speakers (full_name, title, organization, is_featured)
VALUES
('Dr. Fatou Kaba', 'Experte en politiques minieres', 'Consultante independante', 1),
('Prof. Amadou Camara', 'Economiste du developpement', 'Universite de Conakry', 1),
('Mariam Diallo', 'Entrepreneure sociale', 'Jeunesse Forestiere Initiative', 1);

INSERT INTO program_items (event_date, start_time, end_time, title_fr, title_de, description_fr, description_de, location, item_type, display_order, is_active)
VALUES
('2026-07-04', '09:00:00', '11:00:00', 'Ceremonie d''ouverture officielle', 'Offizielle Eroffnungszeremonie', 'Ouverture de la semaine avec les institutions et la diaspora.', 'Auftaktwoche mit Institutionen und Diaspora.', 'Dortmund Centrum', 'ceremony', 1, 1),
('2026-07-05', '10:00:00', '12:00:00', 'Conference: Simandou 2040 et developpement durable', 'Konferenz: Simandou 2040 und nachhaltige Entwicklung', 'Etat des lieux et opportunites pour la Guinee Forestiere.', 'Bestandsaufnahme und Chancen fur Guinee Forestiere.', 'Salle Konrad-Adenauer', 'conference', 2, 1),
('2026-07-07', '14:00:00', '16:00:00', 'Panel diaspora-investissement', 'Diaspora-Investitionspanel', 'Strategies d''investissement responsable.', 'Strategien fur verantwortungsvolle Investitionen.', 'Westfalen Forum', 'panel', 3, 1),
('2026-07-09', '13:00:00', '18:00:00', 'Exposition culturelle et economique', 'Kulturelle und wirtschaftliche Ausstellung', 'Artisanat, culture, innovation et projets locaux.', 'Handwerk, Kultur, Innovation und lokale Projekte.', 'Expo Hall Dortmund', 'exhibition', 4, 1),
('2026-07-12', '18:30:00', '21:00:00', 'Soiree networking Afrique-Allemagne', 'Afrika-Deutschland Networking-Abend', 'Rencontres entre institutions, entreprises et diaspora.', 'Treffen zwischen Institutionen, Unternehmen und Diaspora.', 'Business Club Dortmund', 'networking', 5, 1),
('2026-07-13', '11:00:00', '13:00:00', 'Cloture et feuille de route 2026-2030', 'Abschluss und Roadmap 2026-2030', 'Restitution des recommandations et engagements.', 'Zusammenfassung der Empfehlungen und Verpflichtungen.', 'Dortmund Centrum', 'ceremony', 6, 1);
