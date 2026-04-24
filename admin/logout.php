<?php
require_once 'config/environment.php'; // Replaced session_start()

// Destroy session
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to home page using relative path
header('Location: index.php');
exit;
?>
