<?php
require_once 'config/environment.php'; // Replaced session_start()
require_once 'includes/google_oauth.php';

$googleOAuth = new GoogleOAuth();

// Check if we have an authorization code
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $code = $_GET['code'];
    
    // Authenticate with Google
    $result = $googleOAuth->authenticate($code);
    
    if ($result['success']) {
        // Redirect based on user role
        if ($result['user']['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    } else {
        // Authentication failed, redirect to login with error
        $_SESSION['error'] = $result['message'];
        header('Location: login.php');
        exit;
    }
} elseif (isset($_GET['error'])) {
    // User denied access or other error
    $_SESSION['error'] = 'Google authentication was cancelled or failed: ' . htmlspecialchars($_GET['error']);
    header('Location: login.php');
    exit;
} else {
    // No code parameter, redirect to login
    $_SESSION['error'] = 'Invalid authentication request';
    header('Location: login.php');
    exit;
}
?>
