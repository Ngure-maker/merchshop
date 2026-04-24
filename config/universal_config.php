<?php
// Universal configuration that works on both local and live hosting

// Auto-detect environment
$is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

if ($is_localhost) {
    // Local development configuration
    define('SITE_NAME', 'SmartSchool Uniforms');
    define('SITE_URL', 'http://localhost/smart-school-uniforms');
    define('BASE_URL', SITE_URL);
    define('ADMIN_EMAIL', 'admin@smartschool.com');
    
    // Database configuration for local
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'smart_school_uniforms');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    
    // Google OAuth for local
    define('GOOGLE_CLIENT_ID', '442980103706-frscu0danml0eb84hik53qjjnbenq1br.apps.googleusercontent.com');
    define('GOOGLE_CLIENT_SECRET', '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');
    define('GOOGLE_REDIRECT_URI', 'http://localhost/smart-school-uniforms/google_callback.php');
    
    // Session configuration for local
    ini_set('session.cookie_secure', 0);
    ini_set('session.cookie_httponly', 1);
    
    // Error reporting for local
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
} else {
    // Live hosting configuration
    
    // Load environment variables from .env file
    if (file_exists(__DIR__ . '/../.env')) {
        $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
    
    // Site Configuration
    define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'Merch Shop');
    define('SITE_URL', $_ENV['SITE_URL'] ?? 'https://merchshop.rf.gd');
    define('BASE_URL', SITE_URL);
    define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'kariithingure@gmail.com');
    
    // Database configuration for live
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'sql309.infinityfree.com');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'if0_40896080_merch_shop');
    define('DB_USER', $_ENV['DB_USER'] ?? 'if0_40896080');
    define('DB_PASS', $_ENV['DB_PASS'] ?? 'NPDOjPJ5q3');
    
    // Google OAuth for live
    define('GOOGLE_CLIENT_ID', '442980103706-frscu0danml0eb84hik53qjjnbenq1br.apps.googleusercontent.com');
    define('GOOGLE_CLIENT_SECRET', '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');
    define('GOOGLE_REDIRECT_URI', "https://merchshop.rf.gd'/google_callback.php');
    
    // Session configuration for live
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_domain', '.rf.gd');
    
    // Error reporting for live
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Common configuration for both environments
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'your-secret-key-here');
define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY'] ?? 'your-encryption-key-here');

// M-Pesa Configuration
define('MPESA_CONSUMER_KEY', $_ENV['MPESA_CONSUMER_KEY'] ?? '');
define('MPESA_CONSUMER_SECRET', $_ENV['MPESA_CONSUMER_SECRET'] ?? '');
define('MPESA_SHORTCODE', $_ENV['MPESA_SHORTCODE'] ?? '');
define('MPESA_PASSKEY', $_ENV['MPESA_PASSKEY'] ?? '');
define('MPESA_CALLBACK_URL', SITE_URL . '/payment_callback.php');

// Email Configuration
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');

// Session Configuration
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => ($is_localhost ? '' : '.rf.gd'),
    'secure' => !$is_localhost,
    'httponly' => true,
    'samesite' => 'Strict'
]);

ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

if (session_status() === PHP_SESSION_NONE) {
    require_once 'config/environment.php'; // Replaced session_start()
}

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
