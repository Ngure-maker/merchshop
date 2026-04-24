<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'smart_school_uniforms';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            // First, connect without specifying the database to create it if it doesn't exist
            $temp_conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $temp_conn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name);
            $temp_conn = null;

            // Now connect to the specific database
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Database Connection Error: " . $exception->getMessage());
            throw new Exception("Database connection failed. Please check your configuration.");
        }
        return $this->conn;
    }
}
?>