<?php
// Direct Checkout Redirect Handler
require_once 'config/environment.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

$auth = new Auth();
$cart = new Cart();

// Log the attempt
error_log("=== GOTO_CHECKOUT.PHP ===");
error_log("User logged in: " . ($auth->isLoggedIn() ? 'YES' : 'NO'));
error_log("User is admin: " . ($auth->isAdmin() ? 'YES' : 'NO'));
error_log("Cart items: " . count($cart->getCart()));

// If not logged in, set checkout redirect and go to login
if (!$auth->isLoggedIn()) {
    error_log("User not logged in, setting checkout redirect and going to login");
    $_SESSION['checkout_redirect'] = true;
    $_SESSION['redirect_after_login'] = 'checkout.php';
    $_SESSION['force_checkout_redirect'] = true; // Extra flag to bypass admin redirect
    header('Location: login.php');
    exit;
}

// If logged in, go directly to checkout regardless of admin status
error_log("User is logged in, going directly to checkout");
header('Location: checkout.php');
exit;
?>