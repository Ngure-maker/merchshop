<?php
// Direct Checkout - Bypasses ALL redirect logic
require_once 'config/environment.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

$auth = new Auth();
$cart = new Cart();

// Log the attempt
error_log("=== DIRECT_CHECKOUT.PHP ===");
error_log("User logged in: " . ($auth->isLoggedIn() ? 'YES' : 'NO'));
error_log("User is admin: " . ($auth->isAdmin() ? 'YES' : 'NO'));
error_log("User is system admin: " . ($auth->isSystemAdmin() ? 'YES' : 'NO'));
error_log("Cart items: " . count($cart->getCart()));

// Clear any redirect flags that might interfere
unset($_SESSION['checkout_redirect']);
unset($_SESSION['force_checkout_redirect']);
unset($_SESSION['redirect_after_login']);

// If not logged in, require login but set explicit checkout redirect
if (!$auth->isLoggedIn()) {
    error_log("User not logged in, setting checkout redirect");
    $_SESSION['force_checkout_redirect'] = true;
    $_SESSION['checkout_redirect'] = true;
    $_SESSION['redirect_after_login'] = 'direct_checkout.php';
    header('Location: login.php');
    exit;
}

// If logged in (regardless of admin status), go directly to checkout
error_log("User is logged in, going directly to checkout (bypassing all admin redirects)");

// Get cart items and create snapshot for checkout
$cart_items = $cart->getCart();
if (!empty($cart_items)) {
    // Pass cart snapshot to checkout
    $cart_snapshot = rtrim(strtr(base64_encode(json_encode($cart_items)), '+/', '-_'), '=');
    $_GET['cart_snapshot'] = $cart_snapshot;
}

// Include the checkout page content directly instead of redirecting
// This completely bypasses any redirect logic
include 'checkout.php';
exit;
?>