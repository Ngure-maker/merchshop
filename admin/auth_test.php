<?php
require_once 'config/environment.php'; // Replaced session_start()

echo "Admin Users Page - Simple Test";
echo "<br>Current directory: " . __DIR__;
echo "<br>Request URI: " . $_SERVER['REQUEST_URI'];

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo "<br>ERROR: User not logged in";
    echo "<br><a href='../login.php'>Please login first</a>";
    exit;
}

if ($_SESSION['user_type'] !== 'admin') {
    echo "<br>ERROR: User is not admin. User type: " . $_SESSION['user_type'];
    echo "<br><a href='../dashboard.php'>Go to user dashboard</a>";
    exit;
}

echo "<br>✓ Authentication passed";
echo "<br>✓ Admin access confirmed";
echo "<br><a href='users.php'>Try accessing actual users page</a>";
?>
