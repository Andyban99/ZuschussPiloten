<?php
/**
 * Debug-Script für Admin-Login
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Admin Login</h2>";

// 1. DB-Verbindung testen
echo "<h3>1. Datenbank-Verbindung</h3>";
try {
    require_once __DIR__ . '/../includes/db.php';
    $pdo = db();
    echo "✅ DB-Verbindung erfolgreich<br>";
} catch (Exception $e) {
    echo "❌ DB-Fehler: " . $e->getMessage() . "<br>";
    exit;
}

// 2. Admin-User prüfen
echo "<h3>2. Admin-User in DB</h3>";
$stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = 'admin'");
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    echo "✅ User gefunden: " . $user['username'] . "<br>";
    echo "Hash: " . substr($user['password_hash'], 0, 30) . "...<br>";
} else {
    echo "❌ Kein Admin-User gefunden!<br>";
    exit;
}

// 3. Passwort-Verify testen
echo "<h3>3. Passwort-Verify Test</h3>";
$testPassword = 'admin123';
$result = password_verify($testPassword, $user['password_hash']);
echo "password_verify('admin123', hash) = " . ($result ? "✅ TRUE" : "❌ FALSE") . "<br>";

// 4. Erwarteter Hash
echo "<h3>4. Hash-Vergleich</h3>";
echo "Erwarteter Hash-Start: \$2y\$10\$92IXUNpkjO0rOQ5byMi...<br>";
echo "Aktueller Hash-Start: " . substr($user['password_hash'], 0, 35) . "<br>";

// 5. Session-Status
echo "<h3>5. Session</h3>";
echo "Session-Status: " . (session_status() === PHP_SESSION_ACTIVE ? "Aktiv" : "Nicht aktiv") . "<br>";
?>
