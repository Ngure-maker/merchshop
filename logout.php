<?php
require_once 'includes/auth.php';
$auth = new Auth();

// Start session to destroy it properly
require_once 'config/environment.php'; // Replaced session_start()

// Auto-detect environment and redirect appropriately
$is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

if ($is_localhost) {
    // Local development - redirect to local path
    $redirect_url = '/Smart%20School%20Uniform%20Odering%20System/index.php';
} else {
    // Live hosting - redirect to root
    $redirect_url = '/index.php';
}

// Logout and redirect
$auth->logoutAndRedirect($redirect_url);
?>