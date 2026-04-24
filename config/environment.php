<?php
// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        if (stripos($name, 'export ') === 0) {
            $name = trim(substr($name, 7));
        }
        $value = trim($value);
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        $_ENV[$name] = $value;
    }
}

// Site Configuration
define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'Merch Shop');
define('SITE_URL', $_ENV['SITE_URL'] ?? 'https://merchshop.rf.gd/');
define('BASE_URL', SITE_URL);
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'kariithingure@gmail.com');

// Global favicon injection
if (!defined('FAVICON_LINK_TAGS')) {
    define('FAVICON_LINK_TAGS', "\n    <link rel=\"icon\" type=\"image/x-icon\" href=\"/assets/images/favicon.ico\">\n    <link rel=\"shortcut icon\" href=\"/assets/images/favicon.ico\">\n");
}

if (!defined('FAVICON_INJECTOR_STARTED')) {
    define('FAVICON_INJECTOR_STARTED', true);
    if (php_sapi_name() !== 'cli') {
        ob_start(function ($buffer) {
            if (stripos($buffer, '</head>') !== false && stripos($buffer, 'rel="icon"') === false) {
                return preg_replace('/<\/head>/i', FAVICON_LINK_TAGS . '</head>', $buffer, 1);
            }
            return $buffer;
        });
    }
}

define('GOOGLE_CLIENT_ID', '442980103706-frscu0danml0eb84hik53qjjnbenq1br.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'https://merchshop.rf.gd/google_callback.php');


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

// Security
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'your-secret-key-here');
define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY'] ?? 'your-encryption-key-here');

// Session Configuration - Database-backed for InfinityFree hosting
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
$session_domain = '';

// Force session start with database storage for hosting compatibility
if (session_status() === PHP_SESSION_NONE) {
    // Try to use custom session save path first
    $custom_path = __DIR__ . '/../sessions';
    if (!is_dir($custom_path)) {
        @mkdir($custom_path, 0777, true);
    }
    
    if (is_dir($custom_path) && is_writable($custom_path)) {
        ini_set('session.save_path', $custom_path);
    } else {
        // Fallback to system temp
        ini_set('session.save_path', sys_get_temp_dir());
    }
    
    // Set session cookie params before starting
    // NOTE: SameSite=None requires Secure=true in modern browsers. Otherwise cookies are rejected (causing login loops).
    session_set_cookie_params([
        'lifetime' => 86400 * 30, // 30 days
        'path' => '/',
        'domain' => $session_domain,
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => $is_https ? 'None' : 'Lax'
    ]);
    
    // Additional session settings for hosting
    ini_set('session.use_strict_mode', 0); // Disable for hosting compatibility
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', $is_https ? 1 : 0);
    ini_set('session.gc_maxlifetime', 86400 * 30);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_trans_sid', 0);
    
    session_start();
    
    // Force session data to be written immediately
    if (!isset($_SESSION['session_test'])) {
        $_SESSION['session_test'] = time();
        $_SESSION['initialized'] = true;
    }
}

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Error Reporting
if ($_ENV['APP_ENV'] ?? 'production' === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>