<?php
/**
 * Zuschuss Piloten - Hilfsfunktionen
 * 
 * Validierung, Sanitization, CSRF-Schutz, etc.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// ============================================
// CSRF-SCHUTZ
// ============================================

/**
 * CSRF-Token generieren
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF-Token validieren
 */
function validateCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================
// EINGABE-VALIDIERUNG
// ============================================

/**
 * String säubern (XSS-Schutz)
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * E-Mail validieren
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Telefonnummer validieren (einfach)
 */
function isValidPhone(string $phone): bool {
    // Erlaubt: +, Zahlen, Leerzeichen, Bindestriche, Klammern
    return preg_match('/^[+\d\s\-()]{6,}$/', $phone) === 1;
}

/**
 * Pflichtfeld prüfen
 */
function isRequired(string $value): bool {
    return !empty(trim($value));
}

// ============================================
// SPAM-SCHUTZ
// ============================================

/**
 * Honeypot-Feld prüfen
 * Wenn ausgefüllt = wahrscheinlich Bot
 */
function isHoneypotFilled(?string $honeypot): bool {
    return !empty($honeypot);
}

/**
 * Rate Limiting prüfen
 */
function isRateLimited(string $ipAddress, string $action = 'form_submit'): bool {
    $pdo = db();
    
    // Alte Einträge löschen
    $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([RATE_LIMIT_WINDOW]);
    
    // Aktuelle Anzahl prüfen
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip_address = ? AND action = ?");
    $stmt->execute([$ipAddress, $action]);
    $count = $stmt->fetchColumn();
    
    if ($count >= RATE_LIMIT_MAX) {
        return true; // Rate Limited
    }
    
    // Neuen Eintrag hinzufügen
    $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, action) VALUES (?, ?)");
    $stmt->execute([$ipAddress, $action]);
    
    return false;
}

/**
 * Client IP-Adresse holen
 */
function getClientIp(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Proxy-Header prüfen (nur wenn vertrauenswürdig)
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
}

// ============================================
// LEAD-FUNKTIONEN
// ============================================

/**
 * Neuen Lead speichern
 */
function saveLead(array $data): int|false {
    $pdo = db();
    
    $sql = "INSERT INTO leads 
            (company, contact_name, email, phone, address, industry, employees, project_description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $data['company'],
        $data['contact_name'],
        $data['email'],
        $data['phone'] ?? null,
        $data['address'] ?? null,
        $data['industry'] ?? null,
        $data['employees'] ?? null,
        $data['project_description'] ?? null,
        $data['ip_address'] ?? null,
        $data['user_agent'] ?? null
    ]);
    
    return $result ? (int)$pdo->lastInsertId() : false;
}

/**
 * Alle Leads abrufen
 */
function getAllLeads(string $orderBy = 'created_at', string $order = 'DESC'): array {
    $pdo = db();
    
    // Whitelist für erlaubte Spalten
    $allowedColumns = ['id', 'created_at', 'status', 'company', 'email'];
    $allowedOrder = ['ASC', 'DESC'];
    
    $orderBy = in_array($orderBy, $allowedColumns) ? $orderBy : 'created_at';
    $order = in_array(strtoupper($order), $allowedOrder) ? strtoupper($order) : 'DESC';
    
    $sql = "SELECT * FROM leads ORDER BY {$orderBy} {$order}";
    $stmt = $pdo->query($sql);
    
    return $stmt->fetchAll();
}

/**
 * Lead nach ID abrufen
 */
function getLeadById(int $id): ?array {
    $pdo = db();
    
    $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
    $stmt->execute([$id]);
    
    $lead = $stmt->fetch();
    return $lead ?: null;
}

/**
 * Lead-Status aktualisieren
 */
function updateLeadStatus(int $id, string $status): bool {
    $pdo = db();
    
    $allowedStatus = ['neu', 'kontaktiert', 'qualifiziert', 'abgeschlossen', 'abgelehnt'];
    if (!in_array($status, $allowedStatus)) {
        return false;
    }
    
    $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
}

// ============================================
// JSON RESPONSE
// ============================================

/**
 * JSON-Antwort senden und beenden
 */
function jsonResponse(array $data, int $statusCode = 200): never {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
