<?php
/**
 * Zuschuss Piloten - Formular API Endpoint
 * 
 * Empfängt Formulardaten, validiert sie und speichert in der Datenbank
 * 
 * Endpoint: POST /api/submit.php
 */

// CORS Headers für lokale Entwicklung
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

// Preflight Request beantworten
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

// Nur POST erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'error' => 'Nur POST-Anfragen erlaubt'
    ], 405);
}

// Content-Type prüfen
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

// JSON oder Form-Data verarbeiten
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse([
            'success' => false,
            'error' => 'Ungültiges JSON-Format'
        ], 400);
    }
} else {
    $input = $_POST;
}

// ============================================
// SPAM-SCHUTZ
// ============================================

// Honeypot prüfen (unsichtbares Feld für Bots)
if (isHoneypotFilled($input['website'] ?? null)) {
    // Fake-Success für Bot (aber nicht speichern)
    jsonResponse([
        'success' => true,
        'message' => 'Vielen Dank für Ihre Anfrage!'
    ]);
}

// Rate Limiting
$clientIp = getClientIp();
try {
    if (isRateLimited($clientIp)) {
        jsonResponse([
            'success' => false,
            'error' => 'Zu viele Anfragen. Bitte versuchen Sie es später erneut.'
        ], 429);
    }
} catch (Exception $e) {
    // DB-Fehler - Rate Limiting überspringen bei erster Einrichtung
    if (DEBUG_MODE) {
        error_log('Rate limiting error: ' . $e->getMessage());
    }
}

// ============================================
// VALIDIERUNG
// ============================================

$errors = [];

// Pflichtfelder
if (!isRequired($input['company'] ?? '')) {
    $errors['company'] = 'Unternehmensname ist erforderlich';
}

if (!isRequired($input['contact'] ?? '')) {
    $errors['contact'] = 'Ansprechpartner ist erforderlich';
}

if (!isRequired($input['email'] ?? '')) {
    $errors['email'] = 'E-Mail ist erforderlich';
} elseif (!isValidEmail($input['email'])) {
    $errors['email'] = 'Ungültige E-Mail-Adresse';
}

if (!isRequired($input['address'] ?? '')) {
    $errors['address'] = 'Adresse ist erforderlich';
}

if (!isRequired($input['industry'] ?? '')) {
    $errors['industry'] = 'Branche ist erforderlich';
}

if (!isRequired($input['employees'] ?? '')) {
    $errors['employees'] = 'Mitarbeiteranzahl ist erforderlich';
}

if (!isRequired($input['project'] ?? '')) {
    $errors['project'] = 'Projektbeschreibung ist erforderlich';
}

// Optionale Felder validieren wenn ausgefüllt
if (!empty($input['phone']) && !isValidPhone($input['phone'])) {
    $errors['phone'] = 'Ungültige Telefonnummer';
}

// Fehler zurückgeben
if (!empty($errors)) {
    jsonResponse([
        'success' => false,
        'error' => 'Bitte korrigieren Sie die markierten Felder',
        'errors' => $errors
    ], 400);
}

// ============================================
// DATEN SPEICHERN
// ============================================

$leadData = [
    'company' => sanitize($input['company']),
    'contact_name' => sanitize($input['contact']),
    'email' => sanitize($input['email']),
    'phone' => !empty($input['phone']) ? sanitize($input['phone']) : null,
    'address' => sanitize($input['address']),
    'industry' => sanitize($input['industry']),
    'employees' => sanitize($input['employees']),
    'project_description' => sanitize($input['project']),
    'ip_address' => $clientIp,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
];

try {
    $leadId = saveLead($leadData);
    
    if ($leadId) {
        jsonResponse([
            'success' => true,
            'message' => 'Vielen Dank! Ihre Anfrage wurde erfolgreich übermittelt. Wir melden uns innerhalb von 24 Stunden bei Ihnen.',
            'lead_id' => $leadId
        ]);
    } else {
        throw new Exception('Speichern fehlgeschlagen');
    }
} catch (Exception $e) {
    if (DEBUG_MODE) {
        jsonResponse([
            'success' => false,
            'error' => 'Datenbankfehler: ' . $e->getMessage()
        ], 500);
    } else {
        jsonResponse([
            'success' => false,
            'error' => 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.'
        ], 500);
    }
}
