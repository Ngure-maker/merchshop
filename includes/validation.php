<?php
class Validation {
    
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validatePhone($phone) {
        // Kenyan phone number validation
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        return preg_match('/^254[0-9]{9}$/', $cleaned) || preg_match('/^0[0-9]{9}$/', $cleaned);
    }
    
    public static function validatePassword($password) {
        return strlen($password) >= 6;
    }
    
    public static function validateName($name) {
        return preg_match('/^[a-zA-Z\s\-\']{2,100}$/', $name);
    }
    
    public static function validatePrice($price) {
        return is_numeric($price) && $price >= 0;
    }
    
    public static function validateQuantity($quantity) {
        return is_numeric($quantity) && $quantity > 0 && $quantity <= 1000;
    }
    
    public static function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function generateCSRFToken() {
        return $_SESSION['csrf_token'];
    }
    
    public static function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], $maxSize = 2097152) {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed';
            return $errors;
        }
        
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size too large. Maximum 2MB allowed.';
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowedTypes)) {
            $errors[] = 'Invalid file type. Only JPEG, PNG, and GIF allowed.';
        }
        
        return $errors;
    }
}
?>