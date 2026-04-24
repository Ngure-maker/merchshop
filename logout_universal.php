<?php
// Universal logout that works on both local and live hosting
require_once 'config/environment.php'; // Replaced session_start()

// Destroy session
session_destroy();

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

// Redirect to home page
header('Location: ' . $redirect_url);
exit;
?>
