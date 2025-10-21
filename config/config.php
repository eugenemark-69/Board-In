<?php
// =========================================
// Board-In Application Configuration
// =========================================

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'boardin');
define('DB_USER', 'root');
define('DB_PASS', ''); // empty for default XAMPP

// Application Configuration
define('BASE_URL', 'http://localhost/board-in');
define('SITE_NAME', 'Board-In');

// Upload directories (optional setup)
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PROFILE_IMG_DIR', UPLOAD_DIR . 'profiles/');
define('BH_IMG_DIR', UPLOAD_DIR . 'boarding_houses/');

// Create upload directories if missing
if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
if (!file_exists(PROFILE_IMG_DIR)) mkdir(PROFILE_IMG_DIR, 0777, true);
if (!file_exists(BH_IMG_DIR)) mkdir(BH_IMG_DIR, 0777, true);

// Database connection class (Singleton)
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function to get DB connection easily
function getDB() {
    return Database::getInstance()->getConnection();
}
?>
