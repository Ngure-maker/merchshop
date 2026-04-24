<?php
session_start();

echo "<h2>Minimal Checkout Test</h2>";
echo "<p>This page has NO includes, NO redirects, just basic HTML</p>";

echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>User logged in:</strong> " . (isset($_SESSION['user_id']) ? 'YES' : 'NO') . "</p>";

echo "<h3>Session Data:</h3>";
echo "<pre>" . json_encode($_SESSION, JSON_PRETTY_PRINT) . "</pre>";

echo "<hr>";
echo "<p><strong>If you see this page, then checkout.php itself has the redirect issue</strong></p>";
echo "<p><a href='checkout.php'>Try going to actual checkout.php</a></p>";
echo "<p><a href='cart.php'>Go back to cart</a></p>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Minimal Checkout Test</title>
</head>
<body>
    <h1>Minimal Checkout Page</h1>
    <p>This is a test page with no PHP includes or redirects.</p>
    <p>If this page loads but checkout.php redirects to order_history, then the issue is in checkout.php or its includes.</p>
    
    <script>
        console.log('Minimal checkout page loaded');
        console.log('Current URL:', window.location.href);
        
        // Check for any JavaScript redirects
        setTimeout(function() {
            console.log('Still on minimal checkout page after 2 seconds');
        }, 2000);
    </script>
</body>
</html>
