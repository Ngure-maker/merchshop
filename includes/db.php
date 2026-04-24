<?php
// Auto-detect which database config to use
$is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

if ($is_localhost) {
    require_once __DIR__ . '/../config/database.php';
} else {
    require_once __DIR__ . '/../config/universal_database.php';
}

class DBHelper {
    private $conn;

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Exception $e) {
            error_log("DBHelper constructor error: " . $e->getMessage());
            $this->conn = null;
        }
    }

    public function query($sql, $params = []) {
        try {
            if (!$this->conn) {
                return false;
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("DB Error: " . $e->getMessage());
            return false;
        }
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            try {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("FetchAll error: " . $e->getMessage());
                return [];
            }
        }
        return [];
    }

    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            try {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("FetchOne error: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->query($sql, $data);

        return $stmt ? $this->conn->lastInsertId() : false;
    }

    public function update($table, $data, $where) {
        $set = [];
        foreach(array_keys($data) as $column) {
            $set[] = "$column = :$column";
        }
        $set = implode(', ', $set);

        $sql = "UPDATE $table SET $set WHERE $where";
        return $this->query($sql, $data);
    }

    public function delete($table, $where) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql);
        return $stmt !== false;
    }

    public function fetchSingle($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['count'] ?? $result : $result;
        }
        return false;
    }
}
?>
