<?php
require_once '../config/environment.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    die('Access denied');
}

$db = new DBHelper();

echo "<h2>Order Database Debug</h2>";

// Check orders table structure
echo "<h3>Orders Table Structure:</h3>";
try {
    $columns = $db->fetchAll("SHOW COLUMNS FROM orders");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Show recent orders
echo "<h3>Recent Orders (Last 10):</h3>";
try {
    $orders = $db->fetchAll("SELECT id, total_amount, status, payment_status, created_at, phone_number FROM orders ORDER BY id DESC LIMIT 10");
    if (empty($orders)) {
        echo "<p>No orders found in database</p>";
    } else {
        echo "<table border='1'><tr><th>ID</th><th>Amount</th><th>Status</th><th>Payment</th><th>Phone</th><th>Created</th><th>Test Receipt</th></tr>";
        foreach ($orders as $order) {
            echo "<tr>";
            echo "<td>{$order['id']}</td>";
            echo "<td>KSh " . number_format($order['total_amount'], 2) . "</td>";
            echo "<td>{$order['status']}</td>";
            echo "<td>{$order['payment_status']}</td>";
            echo "<td>{$order['phone_number']}</td>";
            echo "<td>{$order['created_at']}</td>";
            echo "<td><a href='order_details.php?id={$order['id']}&modal=1' target='_blank'>Test Receipt</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Show order items for first order
if (!empty($orders)) {
    $first_order_id = $orders[0]['id'];
    echo "<h3>Order Items for Order #{$first_order_id}:</h3>";
    try {
        $items = $db->fetchAll("SELECT oi.*, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?", [$first_order_id]);
        if (empty($items)) {
            echo "<p>No items found for this order</p>";
        } else {
            echo "<table border='1'><tr><th>Product ID</th><th>Product Name</th><th>Quantity</th><th>Unit Price</th></tr>";
            foreach ($items as $item) {
                echo "<tr>";
                echo "<td>{$item['product_id']}</td>";
                echo "<td>" . ($item['name'] ?? 'Unknown Product') . "</td>";
                echo "<td>{$item['quantity']}</td>";
                echo "<td>KSh " . number_format($item['unit_price'], 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Test specific order ID
if (isset($_GET['test_id'])) {
    $test_id = (int)$_GET['test_id'];
    echo "<h3>Testing Order ID: {$test_id}</h3>";
    
    try {
        $test_order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$test_id]);
        if ($test_order) {
            echo "<p>✅ Order found!</p>";
            echo "<pre>" . print_r($test_order, true) . "</pre>";
            
            echo "<p><a href='order_details.php?id={$test_id}&modal=1' target='_blank'>Test Receipt for Order {$test_id}</a></p>";
        } else {
            echo "<p>❌ Order not found</p>";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

echo "<hr>";
echo "<p>To test a specific order ID, add ?test_id=X to the URL</p>";
?>