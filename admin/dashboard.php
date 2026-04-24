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

// Initialize variables with default values
$stats = [
    'total_orders' => 0,
    'pending_orders' => 0,
    'processing_orders' => 0,
    'delivered_orders' => 0,
    'total_customers' => 0,
    'total_revenue' => 0,
    'low_stock_items' => 0
];

$recent_orders = [];
$today_stats = [
    'today_orders' => 0,
    'today_revenue' => 0
];
$top_products = [];

// Get dashboard statistics - simplified query
try {
    $stats_query = "SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        COALESCE(SUM(total_amount), 0) as total_revenue
        FROM orders";
    
    $result = $db->fetchOne($stats_query);
    if ($result) {
        $stats = array_merge($stats, $result);
    }
} catch (Exception $e) {
    error_log("Stats query error: " . $e->getMessage());
}

// Get total customers - simplified
try {
    $customers_result = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE user_type = 'parent'");
    if ($customers_result) {
        $stats['total_customers'] = $customers_result['count'];
    }
} catch (Exception $e) {
    error_log("Customers query error: " . $e->getMessage());
}

// Get recent orders - simplified without COUNT aggregation
try {
    // First try the simple query with flexible JOIN
    $orders_query = "SELECT o.*, u.full_name, u.email 
                     FROM orders o 
                     LEFT JOIN users u ON o.user_id = u.id
                     ORDER BY o.id DESC 
                     LIMIT 10";
    
    $recent_orders = $db->fetchAll($orders_query);
    
    // If no orders found with JOIN, try without user info
    if (empty($recent_orders)) {
        error_log("Dashboard JOIN query failed, trying orders without user info");
        $simple_query = "SELECT o.*, 'Unknown Customer' as full_name, 'N/A' as email
                         FROM orders o 
                         ORDER BY o.id DESC 
                         LIMIT 10";
        
        $recent_orders = $db->fetchAll($simple_query);
        error_log("Dashboard simple query found: " . count($recent_orders) . " orders");
    }
    
    // If still no orders, try the most basic query
    if (empty($recent_orders)) {
        error_log("Even simple query failed, trying most basic query");
        $basic_query = "SELECT * FROM orders ORDER BY id DESC LIMIT 10";
        $recent_orders = $db->fetchAll($basic_query);
        error_log("Dashboard basic query found: " . count($recent_orders) . " orders");
        
        // Add default customer info to basic results
        foreach ($recent_orders as &$order) {
            $order['full_name'] = 'Unknown Customer';
            $order['email'] = 'N/A';
        }
    }
    
    // Handle missing customer info - use phone number as identifier
    foreach ($recent_orders as &$order) {
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
    
    // Debug: Log what we found
    error_log("Recent orders found: " . count($recent_orders));
    
    // If we got orders, try to get item counts separately
    if (!empty($recent_orders)) {
        foreach ($recent_orders as &$order) {
            try {
                $item_count = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?", [$order['id']]);
                $order['item_count'] = $item_count['count'] ?? 0;
            } catch (Exception $e) {
                $order['item_count'] = 0;
                error_log("Item count error for order {$order['id']}: " . $e->getMessage());
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Recent orders error: " . $e->getMessage());
    $recent_orders = [];
}

// Get today's stats - simplified
try {
    $today_query = "SELECT 
        COUNT(*) as today_orders,
        COALESCE(SUM(total_amount), 0) as today_revenue
        FROM orders 
        WHERE DATE(COALESCE(order_date, created_at)) = CURDATE()";
    
    $today_result = $db->fetchOne($today_query);
    if ($today_result) {
        $today_stats = $today_result;
    }
} catch (Exception $e) {
    error_log("Today stats error: " . $e->getMessage());
}

// Get top products - simplified
try {
    $products_query = "SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE (p.is_active = 1 OR p.status = 'active')
                       ORDER BY p.created_at DESC 
                       LIMIT 5";
    
    $top_products = $db->fetchAll($products_query);
} catch (Exception $e) {
    error_log("Top products error: " . $e->getMessage());
}

// Helper functions
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

function getOrderDate($order) {
    return $order['order_date'] ?? $order['created_at'] ?? 'Unknown';
}

function getOrderStatus($order) {
    return $order['status'] ?? $order['order_status'] ?? 'pending';
}

function getProductStock($product) {
    if (isset($product['stock_quantity'])) return $product['stock_quantity'];
    if (isset($product['stock'])) return $product['stock'];
    if (isset($product['quantity'])) return $product['quantity'];
    return 0;
}

// Auto-detect environment for paths
if ($is_localhost) {
    $base_path = '/Smart%20School%20Uniform%20Odering%20System';
    $admin_path = $base_path . '/admin';
} else {
    $base_path = '';
    $admin_path = '/admin';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: rgb(28, 29, 60);
            --primary-dark: rgb(15, 16, 35);
            --secondary-teal: #00897B;
            --secondary-blue: #1976D2;
            --accent-purple: #7B1FA2;
            --accent-green: #388E3C;
            --neutral-gray: #546E7A;
            --light-bg: #FFF8E1;
        }
        
        .dashboard-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-teal) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            text-align: center;
        }
        .dashboard-hero .display-5 {
            text-align: center;
        }
        .dashboard-hero .lead {
            text-align: center;
        }
        .dashboard-hero .d-flex {
            justify-content: center;
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .stat-card.bg-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
        }
        .stat-card.bg-success {
            background: linear-gradient(135deg, var(--accent-green), #2E7D32) !important;
        }
        .stat-card.bg-info {
            background: linear-gradient(135deg, var(--secondary-blue), #1565C0) !important;
        }
        .stat-card.bg-warning {
            background: linear-gradient(135deg, var(--accent-purple), #6A1B9A) !important;
        }
        .order-status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .quick-action-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 12px;
        }
        .quick-action-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(28, 29, 60, 0.2);
        }
        .card-title {
            text-align: center;
            font-weight: 600;
            color: var(--neutral-gray);
        }
        .card-header h5 {
            text-align: center;
            color: var(--neutral-gray);
            font-weight: 600;
        }
        .card-header {
            background: linear-gradient(135deg, #FFFFFF, #F5F5F5);
            border-bottom: 2px solid var(--primary-color);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), rgb(10, 11, 25));
            transform: translateY(-1px);
        }
        .table-hover tbody tr:hover {
            background-color: var(--light-bg);
        }
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>
    <?php include '../views/admin_header.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <!-- Dashboard Hero Section -->
        <section class="dashboard-hero">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-center">
                    <div class="col-md-8 text-center">
                        <h1 class="display-5 fw-bold mb-3">Admin Dashboard</h1>
                        <p class="lead mb-4">Manage orders, inventory, and monitor system performance</p>
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="orders.php" class="btn btn-light btn-lg">
                                <i class="fas fa-shopping-bag me-2"></i>View Orders
                            </a>
                            <a href="inventory.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-boxes me-2"></i>Manage Inventory
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-tachometer-alt fa-5x"></i>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $stats['total_orders'] ?? 0; ?></h4>
                                <p class="mb-0">Total Orders</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-shopping-bag fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">KSh <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h4>
                                <p class="mb-0">Revenue</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $stats['pending_orders'] ?? 0; ?></h4>
                                <p class="mb-0">Pending Orders</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $stats['low_stock_items'] ?? 0; ?></h4>
                                <p class="mb-0">Low Stock Items</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>Recent Orders
                                </h5>
                                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
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
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_orders as $order): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($order['order_number'] ?? '#' . $order['id']); ?></strong></td>
                                                    <td>
                                                        <div><?php echo htmlspecialchars($order['full_name'] ?? 'Unknown'); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></small>
                                                    </td>
                                                    <td><?php echo $order['item_count'] ?? 0; ?> items</td>
                                                    <td>KSh <?php echo number_format($order['total_amount'], 2); ?></td>
                                                    <td>
                                                        <span class="badge order-status-badge bg-<?php echo getStatusBadgeColor(getOrderStatus($order)); ?>">
                                                            <?php echo ucfirst(getOrderStatus($order)); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime(getOrderDate($order))); ?></td>
                                                    <td>
                                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($recent_orders)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-muted">
                                                        No orders found
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Today's Summary -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>Today's Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Orders Today:</span>
                                    <strong><?php echo $today_stats['today_orders'] ?? 0; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Revenue Today:</span>
                                    <strong>KSh <?php echo number_format($today_stats['today_revenue'] ?? 0, 2); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Processing:</span>
                                    <strong><?php echo $stats['processing_orders'] ?? 0; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Avg Order Value:</span>
                                    <strong>KSh <?php echo number_format(($today_stats['today_revenue'] ?? 0) / max($today_stats['today_orders'] ?? 1, 1), 2); ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt me-2"></i>Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="card quick-action-card text-center h-100" onclick="window.location.href='orders.php?status=pending'">
                                            <div class="card-body p-3">
                                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                                <h6 class="card-title small">Pending Orders</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card quick-action-card text-center h-100" onclick="window.location.href='inventory.php'">
                                            <div class="card-body p-3">
                                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                                <h6 class="card-title small">Low Stock</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card quick-action-card text-center h-100" onclick="window.location.href='users.php'">
                                            <div class="card-body p-3">
                                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                                <h6 class="card-title small">Manage Users</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card quick-action-card text-center h-100" onclick="window.location.href='reports.php'">
                                            <div class="card-body p-3">
                                                <i class="fas fa-chart-bar fa-2x text-success mb-2"></i>
                                                <h6 class="card-title small">View Reports</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- System Stats -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-server me-2"></i>System Stats
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Customers:</span>
                                    <strong><?php echo $stats['total_customers'] ?? 0; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Low Stock Items:</span>
                                    <strong class="text-danger"><?php echo $stats['low_stock_items'] ?? 0; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include '../views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
