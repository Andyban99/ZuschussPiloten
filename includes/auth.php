<?php
/**
 * Zuschuss Piloten - Authentifizierung
 * 
 * Login-Funktionen für Admin-Bereich
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Prüfen ob Admin eingeloggt ist
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Login erforderlich - Redirect wenn nicht eingeloggt
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Admin-Login durchführen
 */
function login(string $username, string $password): bool {
    $pdo = db();
    
    $stmt = $pdo->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Session regenerieren (Session Fixation verhindern)
        session_regenerate_id(true);
        
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_username'] = $username;
        
        // Letzten Login aktualisieren
        $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return true;
    }
    
    return false;
}

/**
 * Logout durchführen
 */
function logout(): void {
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    session_destroy();
}

/**
 * Passwort-Hash erstellen (für neuen Admin)
 */
function createPasswordHash(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}
