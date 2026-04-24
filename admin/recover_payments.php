<?php
// Recover and Display Existing Payments
require_once '../config/environment.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = new DBHelper();

// Check what payment data exists
echo "<h2>Payment Recovery Tool</h2>";
echo "<p>This tool will help identify and recover existing payments in your database.</p>";

// 1. Check orders table for payment information
echo "<h3>1. Orders with Payment Information</h3>";
try {
    $orders_with_payments = $db->fetchAll("
        SELECT id, order_number, total_amount, payment_status, payment_method, 
               mpesa_receipt, payment_code, phone_number, created_at, user_id
        FROM orders 
        WHERE (payment_status = 'paid' OR mpesa_receipt IS NOT NULL OR payment_code IS NOT NULL)
        ORDER BY id DESC
        LIMIT 20
    ");
    
    if (!empty($orders_with_payments)) {
        echo "<div class='alert alert-success'>Found " . count($orders_with_payments) . " orders with payment information!</div>";
        echo "<table class='table table-striped'>";
        echo "<tr><th>Order ID</th><th>Order Number</th><th>Amount</th><th>Payment Status</th><th>M-Pesa Receipt</th><th>Phone</th><th>Date</th></tr>";
        
        foreach ($orders_with_payments as $order) {
            echo "<tr>";
            echo "<td>" . $order['id'] . "</td>";
            echo "<td>" . htmlspecialchars($order['order_number'] ?? 'N/A') . "</td>";
            echo "<td>KSh " . number_format($order['total_amount'], 2) . "</td>";
            echo "<td><span class='badge bg-success'>" . ($order['payment_status'] ?? 'N/A') . "</span></td>";
            echo "<td><strong>" . htmlspecialchars($order['mpesa_receipt'] ?? $order['payment_code'] ?? 'N/A') . "</strong></td>";
            echo "<td>" . htmlspecialchars($order['phone_number'] ?? 'N/A') . "</td>";
            echo "<td>" . date('M j, Y g:i A', strtotime($order['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='alert alert-warning'>No orders with payment information found in orders table.</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error checking orders: " . $e->getMessage() . "</div>";
}

// 2. Check payments table
echo "<h3>2. Payments Table Records</h3>";
try {
    $payments = $db->fetchAll("
        SELECT p.*, o.order_number, o.total_amount as order_total
        FROM payments p
        LEFT JOIN orders o ON p.order_id = o.id
        ORDER BY p.id DESC
        LIMIT 20
    ");
    
    if (!empty($payments)) {
        echo "<div class='alert alert-success'>Found " . count($payments) . " payment records!</div>";
        echo "<table class='table table-striped'>";
        echo "<tr><th>Payment ID</th><th>Order</th><th>Phone</th><th>Amount</th><th>Status</th><th>M-Pesa Receipt</th><th>Method</th><th>Date</th></tr>";
        
        foreach ($payments as $payment) {
            echo "<tr>";
            echo "<td>" . $payment['id'] . "</td>";
            echo "<td>#" . htmlspecialchars($payment['order_number'] ?? $payment['order_id']) . "</td>";
            echo "<td>" . htmlspecialchars($payment['phone_number'] ?? 'N/A') . "</td>";
            echo "<td>KSh " . number_format($payment['amount'], 2) . "</td>";
            echo "<td><span class='badge bg-" . ($payment['status'] === 'completed' ? 'success' : ($payment['status'] === 'failed' ? 'danger' : 'warning')) . "'>" . ($payment['status'] ?? 'N/A') . "</span></td>";
            echo "<td><strong>" . htmlspecialchars($payment['mpesa_receipt'] ?? 'N/A') . "</strong></td>";
            echo "<td>" . htmlspecialchars($payment['payment_method'] ?? 'N/A') . "</td>";
            echo "<td>" . date('M j, Y g:i A', strtotime($payment['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='alert alert-warning'>No records found in payments table.</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error checking payments table: " . $e->getMessage() . "</div>";
}

// 3. Check for orphaned payments (orders without payment records)
echo "<h3>3. Orders Missing Payment Records</h3>";
try {
    $orphaned_orders = $db->fetchAll("
        SELECT o.id, o.order_number, o.total_amount, o.payment_status, o.mpesa_receipt, o.phone_number, o.created_at
        FROM orders o
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE o.payment_status = 'paid' AND p.id IS NULL
        ORDER BY o.id DESC
        LIMIT 10
    ");
    
    if (!empty($orphaned_orders)) {
        echo "<div class='alert alert-warning'>Found " . count($orphaned_orders) . " paid orders without payment records!</div>";
        echo "<p><strong>These orders are marked as paid but don't have corresponding payment records. We can create them:</strong></p>";
        echo "<table class='table table-striped'>";
        echo "<tr><th>Order ID</th><th>Order Number</th><th>Amount</th><th>M-Pesa Receipt</th><th>Phone</th><th>Date</th><th>Action</th></tr>";
        
        foreach ($orphaned_orders as $order) {
            echo "<tr>";
            echo "<td>" . $order['id'] . "</td>";
            echo "<td>" . htmlspecialchars($order['order_number'] ?? 'N/A') . "</td>";
            echo "<td>KSh " . number_format($order['total_amount'], 2) . "</td>";
            echo "<td><strong>" . htmlspecialchars($order['mpesa_receipt'] ?? 'N/A') . "</strong></td>";
            echo "<td>" . htmlspecialchars($order['phone_number'] ?? 'N/A') . "</td>";
            echo "<td>" . date('M j, Y g:i A', strtotime($order['created_at'])) . "</td>";
            echo "<td><button class='btn btn-sm btn-success' onclick='createPaymentRecord(" . $order['id'] . ")'>Create Payment Record</button></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div class='mt-3'>";
        echo "<button class='btn btn-primary' onclick='createAllPaymentRecords()'>Create All Missing Payment Records</button>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>All paid orders have corresponding payment records!</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error checking for orphaned orders: " . $e->getMessage() . "</div>";
}

// 4. Summary and recommendations
echo "<h3>4. Summary & Recommendations</h3>";
echo "<div class='alert alert-info'>";
echo "<h5>What to do next:</h5>";
echo "<ul>";
echo "<li><strong>If you see payment records above:</strong> Your previous payments are already in the system and should show up in the admin payments page.</li>";
echo "<li><strong>If you see 'Orders Missing Payment Records':</strong> Click the buttons above to create payment records for those orders.</li>";
echo "<li><strong>If no payments are found:</strong> This might be because payments were processed outside the current system or in a different database.</li>";
echo "</ul>";
echo "<p><strong>After running this tool, go to <a href='payments.php'>Admin → Payments</a> to see all your payment data!</strong></p>";
echo "</div>";

?>

<script>
async function createPaymentRecord(orderId) {
    try {
        const response = await fetch('create_payment_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: orderId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Payment record created successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error creating payment record: ' + error.message);
    }
}

async function createAllPaymentRecords() {
    if (!confirm('Create payment records for all paid orders missing them?')) {
        return;
    }
    
    try {
        const response = await fetch('create_payment_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ create_all: true })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Created ' + result.created_count + ' payment records successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error creating payment records: ' + error.message);
    }
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
.alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
.alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
.alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
.table { width: 100%; border-collapse: collapse; margin: 10px 0; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background: #f8f9fa; font-weight: bold; }
.table-striped tr:nth-child(even) { background: #f9f9f9; }
.badge { padding: 4px 8px; border-radius: 4px; color: white; font-size: 12px; }
.bg-success { background: #28a745; }
.bg-warning { background: #ffc107; color: #212529; }
.bg-danger { background: #dc3545; }
.btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
.btn-sm { padding: 4px 8px; font-size: 12px; }
.btn-success { background: #28a745; color: white; }
.btn-primary { background: #007bff; color: white; }
</style>

<p><a href="payments.php">← Back to Payments</a> | <a href="orders.php">← Back to Orders</a></p>