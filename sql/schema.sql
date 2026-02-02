-- Zuschuss Piloten - Datenbank Schema
-- Für MySQL / MariaDB

-- Leads Tabelle (Formular-Einsendungen)
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('neu', 'kontaktiert', 'qualifiziert', 'abgeschlossen', 'abgelehnt') DEFAULT 'neu',
    
    -- Kontaktdaten
    company VARCHAR(255) NOT NULL COMMENT 'Unternehmensname',
    contact_name VARCHAR(255) NOT NULL COMMENT 'Ansprechpartner',
    email VARCHAR(255) NOT NULL COMMENT 'E-Mail Adresse',
    phone VARCHAR(50) DEFAULT NULL COMMENT 'Telefonnummer',
    address TEXT DEFAULT NULL COMMENT 'Unternehmensadresse',
    
    -- Unternehmensdaten
    industry VARCHAR(100) DEFAULT NULL COMMENT 'Branche',
    employees VARCHAR(50) DEFAULT NULL COMMENT 'Mitarbeiteranzahl',
    
    -- Projektbeschreibung
    project_description TEXT DEFAULT NULL COMMENT 'Beschreibung des Vorhabens',
    
    -- Metadaten (DSGVO-relevant)
    ip_address VARCHAR(45) DEFAULT NULL COMMENT 'IP-Adresse bei Einsendung',
    user_agent TEXT DEFAULT NULL COMMENT 'Browser/Gerät',
    
    -- CRM-Erweiterungsfelder
    notes TEXT DEFAULT NULL COMMENT 'Interne Notizen',
    
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin User Tabelle
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL COMMENT 'Gehashtes Passwort mit password_hash()',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rate Limiting Tabelle (Spam-Schutz)
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_action (ip_address, action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Standard Admin-User erstellen (Passwort ändern!)
-- Passwort: 'admin123' (BITTE ÄNDERN!)
-- INSERT IGNORE verhindert Fehler bei erneutem Ausführen
INSERT IGNORE INTO admin_users (username, password_hash) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
