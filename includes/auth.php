<?php
require_once __DIR__ . '/db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new DBHelper();
    }
    
    public function register($email, $password, $full_name, $phone = '', $user_type = 'parent') {
        // Validate input
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters'];
        }
        
        // Check if user exists
        $existing = $this->db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Create user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $user_id = $this->db->insert('users', [
            'email' => $email,
            'password' => $hashed_password,
            'full_name' => $full_name,
            'phone' => $phone,
            'user_type' => $user_type
        ]);
        
        if ($user_id) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['full_name'] = $full_name;
            
            return ['success' => true, 'message' => 'Registration successful'];
        }
        
        return ['success' => false, 'message' => 'Registration failed'];
    }
    
    public function login($email, $password) {
        $user = $this->db->fetchOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Update last login
            $this->db->update('users', ['updated_at' => date('Y-m-d H:i:s')], "id = {$user['id']}");
            
            return ['success' => true, 'message' => 'Login successful'];
        }
        
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    public function logout() {
        session_destroy();
    }
    
    public function logoutAndRedirect($redirect_url = 'login.php') {
        session_destroy();
        header('Location: ' . $redirect_url);
        exit;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        $type = $_SESSION['user_type'] ?? '';
        return ($type === 'admin' || $type === 'staff');
    }

    public function isSystemAdmin() {
        return $this->isLoggedIn() && (($_SESSION['user_type'] ?? '') === 'admin');
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            // Check if we're in admin directory
            $current_dir = basename(dirname($_SERVER['PHP_SELF']));
            if ($current_dir === 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: ../admin/dashboard.php');
            }
            exit;
        }
    }

    public function requireSystemAdmin() {
        $this->requireAuth();
        if (!$this->isSystemAdmin()) {
            $current_dir = basename(dirname($_SERVER['PHP_SELF']));
            if ($current_dir === 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: ../admin/dashboard.php');
            }
            exit;
        }
    }
}
?>