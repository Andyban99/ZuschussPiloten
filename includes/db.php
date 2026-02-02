<?php
/**
 * Zuschuss Piloten - Datenbankverbindung
 * 
 * Sichere PDO-Verbindung mit Prepared Statements
 */

require_once __DIR__ . '/config.php';

/**
 * Singleton-Pattern für Datenbankverbindung
 */
class Database {
    private static ?PDO $instance = null;
    
    /**
     * Private Konstruktor (Singleton)
     */
    private function __construct() {}
    
    /**
     * Datenbankverbindung holen oder erstellen
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];
                
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    die('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
                } else {
                    die('Datenbankverbindung fehlgeschlagen. Bitte später erneut versuchen.');
                }
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Verbindung schließen
     */
    public static function close(): void {
        self::$instance = null;
    }
}

/**
 * Hilfsfunktion für einfachen Zugriff
 */
function db(): PDO {
    return Database::getConnection();
}
