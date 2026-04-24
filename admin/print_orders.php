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

// Calculate statistics
$total_orders = count($orders);
$total_amount = array_sum(array_column($orders, 'total_amount'));
$pending_count = 0;
$confirmed_count = 0;
$processing_count = 0;
$delivered_count = 0;

foreach ($orders as $order) {
    $status = getOrderStatus($order);
    switch ($status) {
        case 'pending': $pending_count++; break;
        case 'confirmed': $confirmed_count++; break;
        case 'processing': $processing_count++; break;
        case 'delivered': $delivered_count++; break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Report - Merch Shop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item h3 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .stat-item p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .status-pending { background-color: #fff3cd; }
        .status-confirmed { background-color: #cfe2ff; }
        .status-processing { background-color: #cff4fc; }
        .status-shipped { background-color: #fff3cd; }
        .status-delivered { background-color: #d1e7dd; }
        .payment-paid { background-color: #d1e7dd; }
        .payment-pending { background-color: #fff3cd; }
        .payment-failed { background-color: #f8d7da; }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 10px;
            }
            .header {
                margin-bottom: 20px;
            }
            .stats {
                margin-bottom: 20px;
            }
            th, td {
                padding: 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Orders Report</h1>
        <p>Merch Shop Management System</p>
        <p>Generated on: <?php echo date('F j, Y, g:i A'); ?></p>
        <?php if ($status_filter || $date_from || $date_to): ?>
            <p><strong>Filters Applied:</strong>
                <?php if ($status_filter): ?> Status: <?php echo ucfirst($status_filter); ?><?php endif; ?>
                <?php if ($date_from): ?> From: <?php echo $date_from; ?><?php endif; ?>
                <?php if ($date_to): ?> To: <?php echo $date_to; ?><?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="stats">
        <div class="stat-item">
            <h3><?php echo $total_orders; ?></h3>
            <p>Total Orders</p>
        </div>
        <div class="stat-item">
            <h3>KSh <?php echo number_format($total_amount, 2); ?></h3>
            <p>Total Revenue</p>
        </div>
        <div class="stat-item">
            <h3><?php echo $pending_count; ?></h3>
            <p>Pending Orders</p>
        </div>
        <div class="stat-item">
            <h3><?php echo $confirmed_count; ?></h3>
            <p>Confirmed Orders</p>
        </div>
        <div class="stat-item">
            <h3><?php echo $delivered_count; ?></h3>
            <p>Delivered Orders</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer Name</th>
                <th>Customer Email</th>
                <th>Phone</th>
                <th class="text-center">Items</th>
                <th class="text-right">Amount</th>
                <th>Order Status</th>
                <th>Payment Status</th>
                <th>M-Pesa Receipt</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                    <td><?php echo htmlspecialchars($order['phone_number'] ?? ''); ?></td>
                    <td class="text-center"><?php echo $order['item_count']; ?></td>
                    <td class="text-right">KSh <?php echo number_format($order['total_amount'], 2); ?></td>
                    <td class="status-<?php echo getOrderStatus($order); ?>"><?php echo ucfirst(getOrderStatus($order)); ?></td>
                    <td class="payment-<?php echo $order['payment_status'] ?? 'pending'; ?>"><?php echo ucfirst($order['payment_status'] ?? 'pending'); ?></td>
                    <td><?php echo htmlspecialchars($order['mpesa_receipt'] ?? ''); ?></td>
                    <td><?php echo date('M j, Y H:i', strtotime(getOrderDate($order))); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total:</th>
                <th class="text-right">KSh <?php echo number_format($total_amount, 2); ?></th>
                <th colspan="4"></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report was generated from Merch Shop Management System</p>
        <p>Page <?php echo ceil(count($orders) / 25); ?> of <?php echo ceil(count($orders) / 25); ?></p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary">Print This Report</button>
        <button onclick="window.close()" class="btn btn-secondary">Close Window</button>
    </div>
</body>
</html>
