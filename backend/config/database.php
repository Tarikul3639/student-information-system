<?php
/**
 * Database Configuration (PDO Singleton)
 * Secure + Production-ready structure
 */

header('Content-Type: application/json');

// ========================
// DB CONFIG
// ========================
define('DB_HOST', 'localhost');
define('DB_NAME', 'sis_db');
define('DB_USER', 'sis_user');   // change to root if testing
define('DB_PASS', '1234');       // must match MySQL user password
define('DB_CHARSET', 'utf8mb4');

// ========================
// DATABASE CLASS
// ========================
class Database
{
    private static $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                'status'  => false,
                'message' => 'Database connection failed',
                'error'   => $e->getMessage()
            ]);

            exit;
        }
    }

    // ========================
    // SINGLETON INSTANCE
    // ========================
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ========================
    // GET PDO CONNECTION
    // ========================
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}