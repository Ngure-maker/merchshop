<?php
// Use centralized session management
require_once '../config/environment.php';

// Auto-detect environment and load appropriate configuration
$is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                (strpos($_SERVER['SERVER_NAME'], '192.168') !== false) ||
                (php_sapi_name() === 'cli')); // Add CLI check for debugging

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

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// Check if user is admin
if (!$auth->isAdmin()) {
    $_SESSION['error'] = 'You do not have permission to access the admin panel.';
    header('Location: ../dashboard.php');
    exit();
}

$db = new DBHelper();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'] ?? null;
    
    try {
        // Update order status
        $update_data = ['status' => $status];
        
        // Update payment status if provided
        if ($payment_status) {
            $update_data['payment_status'] = $payment_status;
        }
        
        if ($db->update('orders', $update_data, "id = $order_id")) {
            $_SESSION['success'] = 'Order updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update order';
        }
    } catch (Exception $e) {
        error_log("Order update error: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to update order';
    }
    
    header('Location: orders.php');
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query with universal field support - simplified without COUNT aggregation
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
    
    // If no orders found with JOIN, try without user info first
    if (empty($orders)) {
        error_log("JOIN query failed, trying orders without user info");
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
        error_log("Simple orders query found: " . count($orders) . " orders");
    }
    
    // If still no orders, try the most basic query
    if (empty($orders)) {
        error_log("Even simple query failed, trying most basic query");
        $basic_query = "SELECT * FROM orders ORDER BY id DESC";
        $orders = $db->fetchAll($basic_query);
        error_log("Basic query found: " . count($orders) . " orders");
        
        // Add default customer info to basic results
        foreach ($orders as &$order) {
            $order['full_name'] = 'Unknown Customer';
            $order['email'] = 'N/A';
        }
    }
    
    // Handle missing customer info - use phone number as identifier
    foreach ($orders as &$order) {
        // If no customer name from JOIN, create one from available info
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
    
    // Get item counts separately for each order
    if (!empty($orders)) {
        foreach ($orders as &$order) {
            try {
                $item_count = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?", [$order['id']]);
                $order['item_count'] = $item_count['count'] ?? 0;
            } catch (Exception $e) {
                $order['item_count'] = 0;
                error_log("Item count error for order {$order['id']}: " . $e->getMessage());
            }
        }
    }
    
    error_log("Final orders found: " . count($orders));
    
} catch (Exception $e) {
    error_log("Orders query error: " . $e->getMessage());
    $orders = [];
}

// Get order statistics with universal field support
try {
    $stats = $db->fetchOne("
        SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN COALESCE(status, order_status) = 'pending' THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN COALESCE(status, order_status) = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
            SUM(CASE WHEN COALESCE(status, order_status) = 'processing' THEN 1 ELSE 0 END) as processing_orders,
            SUM(CASE WHEN COALESCE(status, order_status) = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
            SUM(CASE WHEN COALESCE(status, order_status) = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
        FROM orders
    ");
} catch (Exception $e) {
    error_log("Order stats error: " . $e->getMessage());
    $stats = [
        'total_orders' => 0,
        'pending_orders' => 0,
        'confirmed_orders' => 0,
        'processing_orders' => 0,
        'shipped_orders' => 0,
        'delivered_orders' => 0
    ];
}

// Helper functions
function getOrderStatus($order) {
    return $order['status'] ?? $order['order_status'] ?? 'pending';
}

function getOrderDate($order) {
    return $order['order_date'] ?? $order['created_at'] ?? 'Unknown';
}

function getStatusBadgeColor($status) {
    switch (strtolower($status)) {
        case 'pending': return 'secondary';
        case 'confirmed': return 'primary';
        case 'processing': return 'info';
        case 'shipped': return 'warning';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../views/admin_header.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Order Management</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportOrders()">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printOrders()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <div class="btn-group mb-3" role="group">
            <a class="btn btn-sm btn-outline-primary <?php echo $view === '' ? 'active' : ''; ?>" href="orders.php">All</a>
            <a class="btn btn-sm btn-outline-primary <?php echo $view === 'paid' ? 'active' : ''; ?>" href="orders.php?view=paid">Paid</a>
            <a class="btn btn-sm btn-outline-primary <?php echo $view === 'unpaid' ? 'active' : ''; ?>" href="orders.php?view=unpaid">Unpaid</a>
            <a class="btn btn-sm btn-outline-primary <?php echo $view === 'collected' ? 'active' : ''; ?>" href="orders.php?view=collected">Collected</a>
            <a class="btn btn-sm btn-outline-primary <?php echo $view === 'uncollected' ? 'active' : ''; ?>" href="orders.php?view=uncollected">Uncollected</a>
            <a class="btn btn-sm btn-outline-danger <?php echo $view === 'cancelled' ? 'active' : ''; ?>" href="orders.php?view=cancelled">Cancelled</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <!-- Order Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center p-3">
                        <h4 class="mb-0"><?php echo $stats['total_orders'] ?? 0; ?></h4>
                        <small>Total Orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center p-3">
                        <h4 class="mb-0"><?php echo $stats['pending_orders'] ?? 0; ?></h4>
                        <small>Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white">
                    <div class="card-body text-center p-3">
                        <h4 class="mb-0"><?php echo $stats['processing_orders'] ?? 0; ?></h4>
                        <small>Processing</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body text-center p-3">
                        <h4 class="mb-0"><?php echo $stats['delivered_orders'] ?? 0; ?></h4>
                        <small>Delivered</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark text-white">
                    <div class="card-body text-center p-3">
                        <h4 class="mb-0">KSh <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h4>
                        <small>Total Revenue</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="collected" <?php echo $status_filter === 'collected' ? 'selected' : ''; ?>>Collected</option>
                            <option value="uncollected" <?php echo $status_filter === 'uncollected' ? 'selected' : ''; ?>>Uncollected</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Orders (<?php echo count($orders); ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                        <?php if (!empty($order['payment_method'])): ?>
                                            <br>
                                            <small class="text-muted"><?php echo strtoupper($order['payment_method']); ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($order['payment_code']) || !empty($order['mpesa_receipt'])): ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['payment_code'] ?? $order['mpesa_receipt']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($order['full_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td><?php echo $order['item_count']; ?> items</td>
                                    <td>KSh <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="pending" <?php echo getOrderStatus($order) === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo getOrderStatus($order) === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="processing" <?php echo getOrderStatus($order) === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo getOrderStatus($order) === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo getOrderStatus($order) === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="collected" <?php echo getOrderStatus($order) === 'collected' ? 'selected' : ''; ?>>Collected</option>
                                                <option value="uncollected" <?php echo getOrderStatus($order) === 'uncollected' ? 'selected' : ''; ?>>Uncollected</option>
                                                <option value="cancelled" <?php echo getOrderStatus($order) === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo getOrderStatus($order); ?>">
                                            <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="pending" <?php echo ($order['payment_status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="paid" <?php echo ($order['payment_status'] ?? 'pending') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                <option value="failed" <?php echo ($order['payment_status'] ?? 'pending') === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime(getOrderDate($order))); ?></td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    </div>

    <?php include '../views/footer.php'; ?>
    
    <script>
    function exportOrders() {
        // Get current filter parameters
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status') || '';
        const dateFrom = urlParams.get('date_from') || '';
        const dateTo = urlParams.get('date_to') || '';
        
        // Build export URL
        let exportUrl = 'export_orders.php?export=csv';
        if (status) exportUrl += '&status=' + encodeURIComponent(status);
        if (dateFrom) exportUrl += '&date_from=' + encodeURIComponent(dateFrom);
        if (dateTo) exportUrl += '&date_to=' + encodeURIComponent(dateTo);
        
        // Open export URL in new window
        window.open(exportUrl, '_blank');
    }
    
    function printOrders() {
        // Get current filter parameters
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status') || '';
        const dateFrom = urlParams.get('date_from') || '';
        const dateTo = urlParams.get('date_to') || '';
        
        // Build print URL
        let printUrl = 'print_orders.php?';
        if (status) printUrl += 'status=' + encodeURIComponent(status) + '&';
        if (dateFrom) printUrl += 'date_from=' + encodeURIComponent(dateFrom) + '&';
        if (dateTo) printUrl += 'date_to=' + encodeURIComponent(dateTo) + '&';
        
        // Open print-friendly version in new window
        window.open(printUrl, '_blank');
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
