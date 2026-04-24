<?php
// Comprehensive diagnostic for cart checkout issue
session_start();

echo "<h2>Cart Checkout Diagnostic</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
</style>";

// Check 1: Session Status
echo "<div class='section'>";
echo "<h3>1. Session Status</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? '<span class="success">✓ Active</span>' : '<span class="error">✗ Inactive</span>') . "</p>";
echo "</div>";

// Check 2: User Authentication
echo "<div class='section'>";
echo "<h3>2. User Authentication</h3>";
require_once 'includes/auth.php';
$auth = new Auth();
if ($auth->isLoggedIn()) {
    echo "<p class='success'>✓ User is logged in</p>";
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'not set') . "</p>";
    echo "<p>Full Name: " . ($_SESSION['full_name'] ?? 'not set') . "</p>";
} else {
    echo "<p class='error'>✗ User is NOT logged in</p>";
    echo "<p><strong>This will cause redirect to login page!</strong></p>";
}
echo "</div>";

// Check 3: Cart Session Data
echo "<div class='section'>";
echo "<h3>3. Cart Session Data</h3>";
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    echo "<p class='success'>✓ Cart session data exists</p>";
    echo "<p>Items in session: " . count($_SESSION['cart']) . "</p>";
    echo "<pre>" . print_r($_SESSION['cart'], true) . "</pre>";
} else {
    echo "<p class='error'>✗ No cart data in session</p>";
    echo "<p><strong>This will cause redirect back to cart!</strong></p>";
}
echo "</div>";

// Check 4: Cart Class
echo "<div class='section'>";
echo "<h3>4. Cart Class Check</h3>";
require_once 'includes/cart.php';
$cart = new Cart();
$cart_items = $cart->getCart();
if (!empty($cart_items)) {
    echo "<p class='success'>✓ Cart class returns items</p>";
    echo "<p>Items from Cart class: " . count($cart_items) . "</p>";
    echo "<ul>";
    foreach ($cart_items as $item) {
        echo "<li>" . htmlspecialchars($item['name']) . " (Qty: " . $item['quantity'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='error'>✗ Cart class returns empty</p>";
    echo "<p><strong>This is the problem! Cart class can't read session data.</strong></p>";
}
echo "</div>";

// Check 5: Session vs Cart Class Mismatch
echo "<div class='section'>";
echo "<h3>5. Session vs Cart Class Comparison</h3>";
$session_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$cart_count = count($cart_items);
if ($session_count === $cart_count && $session_count > 0) {
    echo "<p class='success'>✓ Session and Cart class match</p>";
} else {
    echo "<p class='error'>✗ Mismatch detected!</p>";
    echo "<p>Session has: $session_count items</p>";
    echo "<p>Cart class has: $cart_count items</p>";
    echo "<p><strong>This is why checkout fails!</strong></p>";
}
echo "</div>";

// Check 6: Direct Checkout Test
echo "<div class='section'>";
echo "<h3>6. What Will Happen When You Click Checkout?</h3>";
if (!$auth->isLoggedIn()) {
    echo "<p class='error'>→ Will redirect to LOGIN page</p>";
} elseif (empty($cart_items)) {
    echo "<p class='error'>→ Will redirect back to CART page (cart appears empty)</p>";
} else {
    echo "<p class='success'>→ Should load CHECKOUT page successfully!</p>";
}
echo "</div>";

// Check 7: Solution
echo "<div class='section'>";
echo "<h3>7. Recommended Action</h3>";
if (!$auth->isLoggedIn()) {
    echo "<p>Please <a href='login.php'>login first</a></p>";
} elseif (empty($cart_items)) {
    echo "<p class='warning'>Cart data issue detected. Try:</p>";
    echo "<ol>";
    echo "<li>Clear your browser cache and cookies</li>";
    echo "<li>Go back to <a href='cart.php'>cart page</a></li>";
    echo "<li>If cart shows items, try checkout again</li>";
    echo "<li>If problem persists, add items to cart again</li>";
    echo "</ol>";
} else {
    echo "<p class='success'>Everything looks good!</p>";
    echo "<p><a href='checkout.php' class='btn btn-primary' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Try Checkout Now</a></p>";
}
echo "</div>";

// Check 8: Full Session Dump
echo "<div class='section'>";
echo "<h3>8. Full Session Data</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "</div>";
?>