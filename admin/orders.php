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
    require_once '../includes/payment.php';
    require_once '../includes/intasend.php';
} else {
    // Live hosting
    require_once __DIR__ . '/../config/universal_database.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/payment.php';
    require_once __DIR__ . '/../includes/intasend.php';
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

// Server-side reconciliation: ensure completed STK payments flip orders to paid even if customer closes polling page
try {
    $pending_refs = $db->fetchAll(
        "SELECT p.order_id, p.checkout_request_id, p.merchant_request_id, o.payment_status
         FROM payments p
         INNER JOIN orders o ON o.id = p.order_id
         WHERE COALESCE(o.payment_status, 'pending') <> 'paid'
           AND COALESCE(p.status, 'pending') = 'pending'
           AND (p.checkout_request_id IS NOT NULL OR p.merchant_request_id IS NOT NULL)
           AND COALESCE(o.created_at, NOW()) >= DATE_SUB(NOW(), INTERVAL 1 DAY)
         ORDER BY p.id DESC
         LIMIT 5"
    );

    if (!empty($pending_refs)) {
        $intasend = new IntaSend();
        $payment = new Payment();

        foreach ($pending_refs as $row) {
            $checkout_request_id = (string)($row['checkout_request_id'] ?? '');
            $merchant_request_id = (string)($row['merchant_request_id'] ?? '');

            $status_result = null;
            if ($checkout_request_id !== '') {
                $status_result = $intasend->checkPaymentStatus($checkout_request_id);
            }
            if ((empty($status_result) || empty($status_result['success'])) && $merchant_request_id !== '' && $merchant_request_id !== $checkout_request_id) {
                $status_result = $intasend->checkPaymentStatus($merchant_request_id);
            }

            if (!empty($status_result['success']) && !empty($status_result['paid'])) {
                $transaction_code = (string)($status_result['mpesa_receipt'] ?? '');
                if ($transaction_code === '') {
                    $transaction_code = 'INTASEND-' . ($checkout_request_id ?: $merchant_request_id);
                }
                $confirm_ref = $checkout_request_id !== '' ? $checkout_request_id : $merchant_request_id;
                $payment->confirmPayment($confirm_ref, $transaction_code);
            }
        }
    }
} catch (Exception $e) {
    // ignore
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'] ?? null;

    if ($order_id <= 0) {
        $_SESSION['error'] = 'Failed to update order';
        header('Location: orders.php');
        exit;
    }
    
    try {
        // Update order status
        $update_data = ['status' => $status];

        // Collected/uncollected are stored in collected_status for compatibility
        if ($status === 'collected' || $status === 'uncollected') {
            $update_data = ['collected_status' => $status];
        }
        
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
$view = $_GET['view'] ?? '';

// Detect available date columns (some deployments may not have order_date/created_at)
$has_order_date = false;
$has_created_at = false;
try {
    $cols = $db->fetchAll("SHOW COLUMNS FROM orders");
    $col_map = [];
    foreach ($cols as $c) {
        if (!empty($c['Field'])) {
            $col_map[(string)$c['Field']] = true;
        }
    }
    $has_order_date = !empty($col_map['order_date']);
    $has_created_at = !empty($col_map['created_at']);
} catch (Exception $e) {
    $has_order_date = false;
    $has_created_at = false;
}

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query with universal field support - simplified without COUNT aggregation
$query = "
    SELECT o.*, o.id AS order_id, u.full_name, u.email,
           p.mpesa_receipt AS pay_mpesa_receipt,
           p.transaction_id AS pay_transaction_id,
           p.checkout_request_id AS pay_checkout_request_id,
           p.merchant_request_id AS pay_merchant_request_id,
           p.status AS pay_status,
           p.payment_method AS pay_payment_method
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN (
        SELECT p1.order_id,
               p1.mpesa_receipt,
               p1.transaction_id,
               p1.checkout_request_id,
               p1.merchant_request_id,
               p1.status,
               p1.payment_method
        FROM payments p1
        INNER JOIN (
            SELECT order_id, MAX(id) AS max_id
            FROM payments
            GROUP BY order_id
        ) p2 ON p1.id = p2.max_id
    ) p ON p.order_id = o.id
    WHERE 1=1
";

$params = [];

if ($view === 'paid') {
    $query .= " AND COALESCE(o.payment_status, 'pending') = 'paid'";
} elseif ($view === 'unpaid') {
    $query .= " AND COALESCE(o.payment_status, 'pending') <> 'paid'";
} elseif ($view === 'cancelled') {
    $query .= " AND COALESCE(o.status, o.order_status) = 'cancelled'";
} elseif ($view === 'collected') {
    $query .= " AND COALESCE(o.collected_status, 'uncollected') = 'collected'";
} elseif ($view === 'uncollected') {
    $query .= " AND COALESCE(o.collected_status, 'uncollected') = 'uncollected'";
}

if ($status_filter) {
    $query .= " AND COALESCE(o.status, o.order_status) = ?";
    $params[] = $status_filter;
}

if ($date_from) {
    if ($has_order_date) {
        $query .= " AND DATE(o.order_date) >= ?";
        $params[] = $date_from;
    } elseif ($has_created_at) {
        $query .= " AND DATE(o.created_at) >= ?";
        $params[] = $date_from;
    }
}

if ($date_to) {
    if ($has_order_date) {
        $query .= " AND DATE(o.order_date) <= ?";
        $params[] = $date_to;
    } elseif ($has_created_at) {
        $query .= " AND DATE(o.created_at) <= ?";
        $params[] = $date_to;
    }
}

$query .= " ORDER BY o.id DESC";

// Pagination
$query .= " LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;

$count_query = "SELECT COUNT(*) as cnt FROM orders o WHERE 1=1";
$count_params = [];

if ($view === 'paid') {
    $count_query .= " AND COALESCE(o.payment_status, 'pending') = 'paid'";
} elseif ($view === 'unpaid') {
    $count_query .= " AND COALESCE(o.payment_status, 'pending') <> 'paid'";
} elseif ($view === 'cancelled') {
    $count_query .= " AND COALESCE(o.status, o.order_status) = 'cancelled'";
} elseif ($view === 'collected') {
    $count_query .= " AND COALESCE(o.collected_status, 'uncollected') = 'collected'";
} elseif ($view === 'uncollected') {
    $count_query .= " AND COALESCE(o.collected_status, 'uncollected') = 'uncollected'";
}

if ($status_filter) {
    $count_query .= " AND COALESCE(o.status, o.order_status) = ?";
    $count_params[] = $status_filter;
}

if ($date_from) {
    if ($has_order_date) {
        $count_query .= " AND DATE(o.order_date) >= ?";
        $count_params[] = $date_from;
    } elseif ($has_created_at) {
        $count_query .= " AND DATE(o.created_at) >= ?";
        $count_params[] = $date_from;
    }
}

if ($date_to) {
    if ($has_order_date) {
        $count_query .= " AND DATE(o.order_date) <= ?";
        $count_params[] = $date_to;
    } elseif ($has_created_at) {
        $count_query .= " AND DATE(o.created_at) <= ?";
        $count_params[] = $date_to;
    }
}

$total_orders_count = 0;
try {
    $cnt_row = $db->fetchOne($count_query, $count_params);
    $total_orders_count = (int)($cnt_row['cnt'] ?? 0);
} catch (Exception $e) {
    $total_orders_count = 0;
}

$total_pages = (int)ceil(($total_orders_count > 0 ? $total_orders_count : 0) / $per_page);
if ($total_pages < 1) {
    $total_pages = 1;
}

try {
    $orders = $db->fetchAll($query, $params);
    
    // If no orders found with JOIN, try without user info first
    if (empty($orders)) {
        error_log("JOIN query failed, trying orders without user info");
        $simple_query = "
            SELECT o.*, o.id AS order_id, 'Unknown Customer' as full_name, 'N/A' as email
            FROM orders o 
            WHERE 1=1
        ";
        
        $simple_params = [];

        if ($view === 'paid') {
            $simple_query .= " AND COALESCE(o.payment_status, 'pending') = 'paid'";
        } elseif ($view === 'unpaid') {
            $simple_query .= " AND COALESCE(o.payment_status, 'pending') <> 'paid'";
        } elseif ($view === 'cancelled') {
            $simple_query .= " AND COALESCE(o.status, o.order_status) = 'cancelled'";
        } elseif ($view === 'collected') {
            $simple_query .= " AND COALESCE(o.collected_status, 'uncollected') = 'collected'";
        } elseif ($view === 'uncollected') {
            $simple_query .= " AND COALESCE(o.collected_status, 'uncollected') = 'uncollected'";
        }
        
        if ($status_filter) {
            $simple_query .= " AND COALESCE(o.status, o.order_status) = ?";
            $simple_params[] = $status_filter;
        }
        
        if ($date_from) {
            if ($has_order_date) {
                $simple_query .= " AND DATE(o.order_date) >= ?";
                $simple_params[] = $date_from;
            } elseif ($has_created_at) {
                $simple_query .= " AND DATE(o.created_at) >= ?";
                $simple_params[] = $date_from;
            }
        }
        
        if ($date_to) {
            if ($has_order_date) {
                $simple_query .= " AND DATE(o.order_date) <= ?";
                $simple_params[] = $date_to;
            } elseif ($has_created_at) {
                $simple_query .= " AND DATE(o.created_at) <= ?";
                $simple_params[] = $date_to;
            }
        }
        
        $simple_query .= " ORDER BY o.id DESC";

        // Pagination
        $simple_query .= " LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
        
        $orders = $db->fetchAll($simple_query, $simple_params);
        error_log("Simple orders query found: " . count($orders) . " orders");
    }
    
    // If still no orders, try the most basic query
    if (empty($orders)) {
        error_log("Even simple query failed, trying most basic query");
        $basic_query = "SELECT o.*, o.id AS order_id FROM orders o ORDER BY o.id DESC LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
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
                $order['full_name'] = (string)$order['phone_number'];
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
                $oid = (int)($order['order_id'] ?? $order['id'] ?? 0);
                $item_count = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?", [$oid]);
                $order['item_count'] = $item_count['count'] ?? 0;
            } catch (Exception $e) {
                $order['item_count'] = 0;
                $log_id = (int)($order['order_id'] ?? $order['id'] ?? 0);
                error_log("Item count error for order {$log_id}: " . $e->getMessage());
            }
        }
    }

    // Attach product names summary for each order (works for past orders too)
    if (!empty($orders)) {
        try {
            $order_ids = [];
            foreach ($orders as $o) {
                $oid = (int)($o['order_id'] ?? $o['id'] ?? 0);
                if ($oid > 0) {
                    $order_ids[] = $oid;
                }
            }
            $order_ids = array_values(array_unique($order_ids));

            if (!empty($order_ids)) {
                $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
                $rows = $db->fetchAll(
                    "SELECT oi.order_id,
                            GROUP_CONCAT(CONCAT(COALESCE(p.name, CONCAT('Product #', oi.product_id)), ' x', oi.quantity) ORDER BY oi.id SEPARATOR ', ') AS product_summary
                     FROM order_items oi
                     LEFT JOIN products p ON p.id = oi.product_id
                     WHERE oi.order_id IN ($placeholders)
                     GROUP BY oi.order_id",
                    $order_ids
                );

                $summary_map = [];
                foreach ($rows as $r) {
                    $rid = (int)($r['order_id'] ?? 0);
                    if ($rid > 0) {
                        $summary_map[$rid] = (string)($r['product_summary'] ?? '');
                    }
                }

                foreach ($orders as &$order) {
                    $oid = (int)($order['order_id'] ?? $order['id'] ?? 0);
                    $order['product_summary'] = $summary_map[$oid] ?? '';
                }
                unset($order);
            } else {
                foreach ($orders as &$order) {
                    $order['product_summary'] = '';
                }
                unset($order);
            }
        } catch (Exception $e) {
            foreach ($orders as &$order) {
                $order['product_summary'] = '';
            }
            unset($order);
            error_log('Product summary error: ' . $e->getMessage());
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
    <link href="../assets/css/zetech-theme.css" rel="stylesheet">
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
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <a href="payments.php" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-credit-card"></i> Payment Codes
                    </a>
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
                <h5 class="card-title mb-0">Orders (<?php echo (int)$total_orders_count; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Products</th>
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
                                <?php $oid = (int)($order['order_id'] ?? $order['id'] ?? 0); ?>
                                <?php
                                    $view_identifier = $oid > 0 ? (string)$oid : '';
                                ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($order['product_summary'])): ?>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($order['product_summary']); ?></div>
                                            <?php if (!empty($order['payment_method'])): ?>
                                                <small class="text-muted"><?php echo strtoupper((string)$order['payment_method']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($order['full_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td><?php echo (int)($order['item_count'] ?? 0); ?> items</td>
                                    <td>KSh <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                            <?php $current_status = !empty($order['collected_status']) ? $order['collected_status'] : getOrderStatus($order); ?>
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $current_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $current_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="processing" <?php echo $current_status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $current_status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $current_status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="collected" <?php echo $current_status === 'collected' ? 'selected' : ''; ?>>Collected</option>
                                                <option value="uncollected" <?php echo $current_status === 'uncollected' ? 'selected' : ''; ?>>Uncollected</option>
                                                <option value="cancelled" <?php echo $current_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                            <input type="hidden" name="status" value="<?php echo getOrderStatus($order); ?>">
                                            <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="pending" <?php echo ($order['payment_status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="paid" <?php echo ($order['payment_status'] ?? 'pending') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                <option value="failed" <?php echo ($order['payment_status'] ?? 'pending') === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                            </select>
                                        </form>
                                        <?php 
                                        // Show M-Pesa receipt code if available
                                        $mpesa_receipt = $order['pay_mpesa_receipt'] ?? $order['mpesa_receipt'] ?? '';
                                        if (!empty($mpesa_receipt) && ($order['payment_status'] ?? 'pending') === 'paid'): 
                                        ?>
                                            <small class="text-success d-block mt-1">
                                                <i class="fas fa-receipt me-1"></i><?php echo htmlspecialchars($mpesa_receipt); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime(getOrderDate($order))); ?></td>
                                    <td>
                                        <?php if ($view_identifier !== ''): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showOrderReceipt(<?php echo $oid; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>View</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white">
                <nav aria-label="Orders pagination">
                    <ul class="pagination pagination-sm mb-0 justify-content-end">
                        <?php
                            $base_params = $_GET;
                            unset($base_params['page']);
                            $mk = function ($p) use ($base_params) {
                                $qs = http_build_query(array_merge($base_params, ['page' => $p]));
                                return 'orders.php' . ($qs ? ('?' . $qs) : '');
                            };
                            $prev = max(1, $page - 1);
                            $next = min($total_pages, $page + 1);
                        ?>
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo htmlspecialchars($mk($prev)); ?>">Previous</a>
                        </li>
                        <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                        ?>
                        <?php for ($p = $start; $p <= $end; $p++): ?>
                            <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo htmlspecialchars($mk($p)); ?>"><?php echo $p; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo htmlspecialchars($mk($next)); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </main>
    </div>

    <!-- Order Receipt Modal -->
    <div class="modal fade" id="orderReceiptModal" tabindex="-1" aria-labelledby="orderReceiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderReceiptModalLabel">Order Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="receiptContent">
                    <!-- Receipt will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="printReceipt()">Print</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../views/footer.php'; ?>
    
    <script>
    // Auto-refresh functionality for real-time payment updates
    let autoRefreshInterval;
    let lastRefreshTime = Date.now();
    
    function startAutoRefresh() {
        // Check for pending payments every 15 seconds
        autoRefreshInterval = setInterval(async () => {
            try {
                const response = await fetch('check_pending_payments.php');
                const data = await response.json();
                
                if (data.hasUpdates) {
                    console.log('Payment updates detected, refreshing page...');
                    showUpdateNotification('Payment status updated! Refreshing...');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } catch (error) {
                console.error('Auto-refresh error:', error);
            }
        }, 15000); // 15 seconds
    }
    
    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    }
    
    function showUpdateNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-sync-alt me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
    
    // Start auto-refresh when page loads
    document.addEventListener('DOMContentLoaded', function() {
        startAutoRefresh();
        
        // Add visual indicator for auto-refresh
        const refreshIndicator = document.createElement('div');
        refreshIndicator.id = 'refresh-indicator';
        refreshIndicator.className = 'position-fixed';
        refreshIndicator.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000;';
        refreshIndicator.innerHTML = `
            <div class="badge bg-success">
                <i class="fas fa-sync-alt me-1"></i>Auto-refresh ON
            </div>
        `;
        document.body.appendChild(refreshIndicator);
    });
    
    // Stop auto-refresh when page is hidden/user navigates away
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
    
    function showOrderReceipt(orderId) {
        console.log('Loading receipt for order:', orderId);
        
        // Show loading state
        document.getElementById('receiptContent').innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Loading receipt...</div>';
        var modal = new bootstrap.Modal(document.getElementById('orderReceiptModal'));
        modal.show();
        
        fetch('order_details.php?id=' + orderId + '&modal=1')
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                }
                return response.text();
            })
            .then(html => {
                console.log('Receipt HTML loaded, length:', html.length);
                if (html.trim() === '') {
                    throw new Error('Empty response received');
                }
                document.getElementById('receiptContent').innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading receipt:', error);
                document.getElementById('receiptContent').innerHTML = 
                    '<div class="alert alert-danger">Error loading receipt: ' + error.message + 
                    '<br><small>Order ID: ' + orderId + '</small></div>';
            });
    }

    function printReceipt() {
        const receiptContent = document.getElementById('receiptContent').innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #000; background: #fff; margin: 0; padding: 20px; }
                    .receipt { max-width: 400px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; background: #fff; }
                    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                    .header h2 { margin: 0; font-size: 18px; color: rgb(6, 25, 67); }
                    .header p { margin: 5px 0 0 0; font-size: 14px; color: #666; }
                    .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
                    .items-table { width: 100%; margin: 20px 0; border-collapse: collapse; }
                    .items-table th, .items-table td { border-bottom: 1px solid #ddd; padding: 8px 4px; text-align: left; }
                    .items-table th { font-weight: bold; background: #f9f9f9; color: rgb(6, 25, 67); }
                    .totals { text-align: right; margin-top: 20px; }
                    .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 15px; }
                    hr { border: none; border-top: 1px solid #ddd; margin: 15px 0; }
                </style>
            </head>
            <body>${receiptContent}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }

    function exportOrders() {
        window.location.href = 'export_orders.php';
    }

    function printOrders() {
        window.location.href = 'print_orders.php';
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

