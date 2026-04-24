<?php
// Performance optimization for InfinityFree hosting
// This file helps reduce database connection delays

// Enable output buffering to start sending HTML immediately
ob_start();

// Set faster execution time limits
set_time_limit(30);

// Optimize session settings
ini_set('session.gc_maxlifetime', 1440); // 24 minutes
ini_set('session.cookie_lifetime', 1440);

// Cache database connection if possible
class OptimizedDBHelper {
    private static $instance = null;
    private $conn;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Use the existing database configuration
        require_once __DIR__ . '/includes/db.php';
        $db = new DBHelper();
        $this->conn = $db->getConnection();
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

// Preload common queries to reduce database hits
function preloadCommonData() {
    static $cached = [];
    
    if (empty($cached)) {
        try {
            $db = OptimizedDBHelper::getInstance();
            
            // Cache categories
            $stmt = $db->getConnection()->query("SELECT * FROM categories ORDER BY name LIMIT 10");
            $cached['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Cache featured products (limited)
            $stmt = $db->getConnection()->query("SELECT id, name, price, image_url FROM products WHERE active = 1 ORDER BY created_at DESC LIMIT 6");
            $cached['featured'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Preload error: " . $e->getMessage());
            $cached['categories'] = [];
            $cached['featured'] = [];
        }
    }
    
    return $cached;
}

// Flush output early for better perceived performance
function flushOutput() {
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}
?>
