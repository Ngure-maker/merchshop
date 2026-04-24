<?php
require_once 'config/environment.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    die('Please login first');
}

$db = new DBHelper();
$user_id = $_SESSION['user_id'];

echo "<h2>Order Creation Debug</h2>";
echo "<p><strong>Current User ID:</strong> " . $user_id . "</p>";
echo "<p><strong>Session Data:</strong></p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Check if user exists in users table
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$user_id]);
if ($user) {
    echo "<p>✅ User found in database:</p>";
    echo "<pre>" . print_r($user, true) . "</pre>";
} else {
    echo "<p>❌ User NOT found in database!</p>";
}

// Check orders for this user
echo "<h3>Orders for User ID: " . $user_id . "</h3>";
$orders = $db->fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC", [$user_id]);

if (empty($orders)) {
    echo "<p>❌ No orders found for this user</p>";
    
    // Check if there are any orders with NULL user_id
    $null_orders = $db->fetchAll("SELECT * FROM orders WHERE user_id IS NULL ORDER BY id DESC LIMIT 5");
    if (!empty($null_orders)) {
        echo "<h4>Orders with NULL user_id (might be yours):</h4>";
        echo "<table border='1'><tr><th>ID</th><th>Phone</th><th>Amount</th><th>Status</th><th>Created</th></tr>";
        foreach ($null_orders as $order) {
            echo "<tr>";
            echo "<td>{$order['id']}</td>";
            echo "<td>{$order['phone_number']}</td>";
            echo "<td>KSh " . number_format($order['total_amount'], 2) . "</td>";
            echo "<td>{$order['status']}</td>";
            echo "<td>{$order['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check all orders to see if any exist
    $all_orders = $db->fetchAll("SELECT id, user_id, phone_number, total_amount, status, created_at FROM orders ORDER BY id DESC LIMIT 10");
    if (!empty($all_orders)) {
        echo "<h4>All Recent Orders (Last 10):</h4>";
        echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Phone</th><th>Amount</th><th>Status</th><th>Created</th></tr>";
        foreach ($all_orders as $order) {
            echo "<tr>";
            echo "<td>{$order['id']}</td>";
            echo "<td>" . ($order['user_id'] ?? 'NULL') . "</td>";
            echo "<td>{$order['phone_number']}</td>";
            echo "<td>KSh " . number_format($order['total_amount'], 2) . "</td>";
            echo "<td>{$order['status']}</td>";
            echo "<td>{$order['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p>✅ Found " . count($orders) . " orders for this user:</p>";
    echo "<table border='1'><tr><th>ID</th><th>Order Number</th><th>Amount</th><th>Status</th><th>Payment Status</th><th>Created</th></tr>";
    foreach ($orders as $order) {
        echo "<tr>";
        echo "<td>{$order['id']}</td>";
        echo "<td>{$order['order_number']}</td>";
        echo "<td>KSh " . number_format($order['total_amount'], 2) . "</td>";
        echo "<td>{$order['status']}</td>";
        echo "<td>{$order['payment_status']}</td>";
        echo "<td>{$order['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check cart contents
require_once 'includes/cart.php';
$cart = new Cart();
$cart_items = $cart->getCart();

echo "<h3>Current Cart Contents:</h3>";
if (empty($cart_items)) {
    echo "<p>Cart is empty</p>";
} else {
    echo "<p>Cart has " . count($cart_items) . " items:</p>";
    echo "<pre>" . print_r($cart_items, true) . "</pre>";
}

// Test order creation (if cart has items)
if (!empty($cart_items)) {
    echo "<h3>Test Order Creation:</h3>";
    echo "<p><a href='?test_order=1' class='btn btn-primary'>Create Test Order</a></p>";
    
    if (isset($_GET['test_order'])) {
        require_once 'includes/payment.php';
        $payment = new Payment();
        
        $test_phone = '254708374149'; // Your test phone
        $test_address = 'Test Address';
        
        echo "<p>Creating test order with:</p>";
        echo "<ul>";
        echo "<li>User ID: " . $user_id . "</li>";
        echo "<li>Phone: " . $test_phone . "</li>";
        echo "<li>Address: " . $test_address . "</li>";
        echo "</ul>";
        
        $result = $payment->createOrder($user_id, $test_phone, $test_address);
        
        echo "<p><strong>Result:</strong></p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        if ($result['success']) {
            echo "<p>✅ Order created successfully! Order ID: " . $result['order_id'] . "</p>";
            echo "<p><a href='order_history.php'>Check Order History</a></p>";
        } else {
            echo "<p>❌ Order creation failed: " . $result['message'] . "</p>";
        }
    }
}

echo "<hr>";
echo "<p><a href='order_history.php'>Go to Order History</a></p>";
echo "<p><a href='cart.php'>Go to Cart</a></p>";
?>

<style>
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
.btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
</style>