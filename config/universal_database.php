<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Auto-detect environment
        $is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                        (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                        (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

        if ($is_localhost) {
            // Local development
            $this->host = 'localhost';
            $this->db_name = 'smart_school_uniforms';
            $this->username = 'root';
            $this->password = '';
        } else {
            // Live hosting - use environment variables
            $this->host = getenv('DB_HOST') ?: 'sql309.infinityfree.com';
            $this->db_name = getenv('DB_NAME') ?: 'if0_40896080_merch_shop';
            $this->username = getenv('DB_USER') ?: 'if0_40896080';
            $this->password = getenv('DB_PASS') ?: 'NPDOjPJ5q3';
        }
    }

    public function getConnection() {
        $this->conn = null;
        try {
            // Auto-detect environment
            $is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                            (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                            (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

            // Guard against missing PDO extension on some hosts
            if (!class_exists('PDO')) {
                $msg = "PDO extension not available";
                error_log($msg);
                if ($is_localhost) {
                    throw new Exception($msg);
                }
                return $this->getMockConnection();
            }

            if ($is_localhost) {
                // Local development - can create database
                $temp_conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
                $temp_conn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name);
                $temp_conn = null;
            }

            // Connect to the specific database with timeout
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5
                ]
            );
            
            return $this->conn;
        } catch(PDOException $exception) {
            error_log("Database Connection Error: " . $exception->getMessage());
            
            // Auto-detect environment for error message
            $is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                            (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                            (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

            if ($is_localhost) {
                throw new Exception("Database connection failed: " . $exception->getMessage());
            } else {
                // On live hosting, don't throw fatal error - return mock connection
                error_log("Live DB failed: " . $exception->getMessage());
                return $this->getMockConnection();
            }
        }
    }
    
    private function getMockConnection() {
        // Return a mock connection for fallback
        return new class {
            public function query($sql) {
                return new class {
                    public function fetchAll() {
                        return [];
                    }
                    public function fetch() {
                        return null;
                    }
                };
            }
            
            public function prepare($sql) {
                return new class {
                    public function execute($params = []) {
                        return true;
                    }
                    public function fetchAll() {
                        return [];
                    }
                    public function fetch() {
                        return null;
                    }
                };
            }
        };
    }
}
?>
