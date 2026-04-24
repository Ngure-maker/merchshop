<?php
require_once 'config/environment.php'; // Replaced session_start()

// Auto-detect environment and load appropriate configuration
$is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

if ($is_localhost) {
    // Local development
    require_once '../includes/auth.php';
    require_once '../includes/db.php';
} else {
    // Live hosting
    require_once __DIR__ . '/../config/universal_database.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';
}

$auth = new Auth();

// Check if user is logged in and is admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    die("Access denied");
}

$db = new DBHelper();

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query with universal field support
$query = "
    SELECT o.*, u.full_name, u.email
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id
    WHERE 1=1
";

$params = [];

if ($status_filter) {
    $query .= " AND COALESCE(o.status, o.order_status) = ?";
    $params[] = $status_filter;
}

if ($date_from) {
    $query .= " AND DATE(COALESCE(o.order_date, o.created_at)) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND DATE(COALESCE(o.order_date, o.created_at)) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY o.id DESC";

try {
    $orders = $db->fetchAll($query, $params);
    
    // If no orders found with JOIN, try without user info
    if (empty($orders)) {
        $simple_query = "
            SELECT o.*, 'Unknown Customer' as full_name, 'N/A' as email
            FROM orders o 
            WHERE 1=1
        ";
        
        $simple_params = [];
        
        if ($status_filter) {
            $simple_query .= " AND COALESCE(o.status, o.order_status) = ?";
            $simple_params[] = $status_filter;
        }
        
        if ($date_from) {
            $simple_query .= " AND DATE(COALESCE(o.order_date, o.created_at)) >= ?";
            $simple_params[] = $date_from;
        }
        
        if ($date_to) {
            $simple_query .= " AND DATE(COALESCE(o.order_date, o.created_at)) <= ?";
            $simple_params[] = $date_to;
        }
        
        $simple_query .= " ORDER BY o.id DESC";
        
        $orders = $db->fetchAll($simple_query, $simple_params);
    }
    
    // Handle missing customer info
    foreach ($orders as &$order) {
        if (!$order['full_name'] || $order['full_name'] === 'Unknown Customer') {
            if ($order['phone_number']) {
                $order['full_name'] = 'Customer (' . substr($order['phone_number'], -4) . ')';
                $order['email'] = 'N/A';
            } else {
                $order['full_name'] = 'Unknown Customer';
                $order['email'] = 'N/A';
            }
        }
    }
    
    // Get item counts for each order
    foreach ($orders as &$order) {
        try {
            $item_count = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?", [$order['id']]);
            $order['item_count'] = $item_count['count'] ?? 0;
        } catch (Exception $e) {
            $order['item_count'] = 0;
        }
    }
    
} catch (Exception $e) {
    die("Error fetching orders: " . $e->getMessage());
}

// Helper functions
function getOrderDate($order) {
    return $order['order_date'] ?? $order['created_at'] ?? 'Unknown';
}

function getOrderStatus($order) {
    return $order['status'] ?? $order['order_status'] ?? 'pending';
}

// Export to CSV
if ($_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orders_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, [
        'Order Number',
        'Customer Name',
        'Customer Email',
        'Phone Number',
        'Items Count',
        'Total Amount',
        'Order Status',
        'Payment Status',
        'M-Pesa Receipt',
        'Shipping Address',
        'Order Date'
    ]);
    
    // CSV data
    foreach ($orders as $order) {
        fputcsv($output, [
            $order['order_number'] ?? '#' . $order['id'],
            $order['full_name'],
            $order['email'],
            $order['phone_number'] ?? '',
            $order['item_count'],
            'KSh ' . number_format($order['total_amount'], 2),
            getOrderStatus($order),
            $order['payment_status'] ?? 'pending',
            $order['mpesa_receipt'] ?? '',
            $order['shipping_address'] ?? '',
            date('M j, Y H:i', strtotime(getOrderDate($order)))
        ]);
    }
    
    fclose($output);
    exit;
}

// Export to Excel (simple HTML table that Excel can open)
if ($_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="orders_export_' . date('Y-m-d') . '.xls"');
    
    echo '<table border="1">';
    
    // Headers
    echo '<tr>';
    echo '<th>Order Number</th>';
    echo '<th>Customer Name</th>';
    echo '<th>Customer Email</th>';
    echo '<th>Phone Number</th>';
    echo '<th>Items Count</th>';
    echo '<th>Total Amount</th>';
    echo '<th>Order Status</th>';
    echo '<th>Payment Status</th>';
    echo '<th>M-Pesa Receipt</th>';
    echo '<th>Shipping Address</th>';
    echo '<th>Order Date</th>';
    echo '</tr>';
    
    // Data
    foreach ($orders as $order) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($order['order_number'] ?? '#' . $order['id']) . '</td>';
        echo '<td>' . htmlspecialchars($order['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($order['email']) . '</td>';
        echo '<td>' . htmlspecialchars($order['phone_number'] ?? '') . '</td>';
        echo '<td>' . $order['item_count'] . '</td>';
        echo '<td>KSh ' . number_format($order['total_amount'], 2) . '</td>';
        echo '<td>' . getOrderStatus($order) . '</td>';
        echo '<td>' . ($order['payment_status'] ?? 'pending') . '</td>';
        echo '<td>' . htmlspecialchars($order['mpesa_receipt'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($order['shipping_address'] ?? '') . '</td>';
        echo '<td>' . date('M j, Y H:i', strtotime(getOrderDate($order))) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    exit;
}
?>
