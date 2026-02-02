<?php
/**
 * Zuschuss Piloten - Konfiguration
 * 
 * WICHTIG: Diese Datei enthält sensible Daten!
 * - Nicht in öffentliche Repositories hochladen
 * - Auf dem Server entsprechend schützen
 */

// Fehlerberichterstattung (in Produktion auf false setzen)
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// DATENBANK-KONFIGURATION (STRATO)
// ============================================
define('DB_HOST', 'database-5019531275.webspace-host.com');
define('DB_NAME', 'dbs15265930');
define('DB_USER', 'dbu2285787');
define('DB_PASS', 'Freunde999...');
define('DB_CHARSET', 'utf8mb4');


// ============================================
// SICHERHEITSEINSTELLUNGEN
// ============================================

// CSRF Token Secret Key (zufällig generiert)
define('CSRF_SECRET', 'zp_' . bin2hex(random_bytes(16)));

// Rate Limiting: Max Anfragen pro IP pro Stunde
define('RATE_LIMIT_MAX', 10);
define('RATE_LIMIT_WINDOW', 3600); // 1 Stunde in Sekunden

// Session-Konfiguration
define('SESSION_NAME', 'ZP_Admin');
define('SESSION_LIFETIME', 3600); // 1 Stunde

// ============================================
// PFADE
// ============================================
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', __DIR__);

// ============================================
// ZEITZONEN
// ============================================
date_default_timezone_set('Europe/Berlin');

// ============================================
// SESSION STARTEN (sicher)
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => !DEBUG_MODE, // HTTPS in Produktion
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}
