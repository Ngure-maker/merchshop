<?php
// Auto-detect environment and load appropriate configuration
$is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

if ($is_localhost) {
    // Local development
    require_once 'includes/auth.php';
    require_once 'includes/db.php';
} else {
    // Live hosting
    require_once __DIR__ . '/config/universal_database.php';
    require_once __DIR__ . '/includes/db.php';
    require_once __DIR__ . '/includes/auth.php';
}

// Include the registration form
include 'views/register_form.php';
?>
