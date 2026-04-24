<?php
// Simple performance optimization for InfinityFree
// Add this to the top of your main pages

// Start output buffering immediately
if (!ob_get_level()) {
    ob_start();
}

// Set faster execution
set_time_limit(20);

// Cache database connection to reduce connection overhead
class FastDB {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                // Use environment variables or fallback to hardcoded values
                $host = $_ENV['DB_HOST'] ?? 'sql309.infinityfree.com';
                $dbname = $_ENV['DB_NAME'] ?? 'if0_40896080_merch_shop';
                $username = $_ENV['DB_USER'] ?? 'if0_40896080';
                $password = $_ENV['DB_PASS'] ?? 'NPDOjPJ5q3';
                
                self::$connection = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                error_log("FastDB Connection Error: " . $e->getMessage());
                // Return a mock connection for testing
                self::$connection = new class {
                    public function query($sql) {
                        return new class {
                            public function fetchAll() {
                                return [];
                            }
                        };
                    }
                };
            }
        }
        return self::$connection;
    }
}

// Quick cache for common queries
function quickCache($key, $callback, $ttl = 300) {
    static $cache = [];
    $now = time();
    
    if (isset($cache[$key]) && ($now - $cache[$key]['time']) < $ttl) {
        return $cache[$key]['data'];
    }
    
    try {
        $data = $callback();
        $cache[$key] = ['data' => $data, 'time' => $now];
        return $data;
    } catch (Exception $e) {
        error_log("Cache error for $key: " . $e->getMessage());
        return [];
    }
}

// Flush output early for better perceived speed
function earlyFlush() {
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}
?>
