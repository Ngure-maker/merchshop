<?php
require_once 'config/environment.php';

require_once 'includes/auth.php';
require_once 'includes/db.php';
$auth = new Auth();

// Check if user is logged in, redirect to login if not
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = new DBHelper();
$user_id = $_SESSION['user_id'];

$order_id = $_GET['id'] ?? 0;

// If order_id is provided, show order detail
if ($order_id > 0) {
    $order = $db->fetchOne("
        SELECT o.*, u.full_name, u.email, u.phone 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ? AND o.user_id = ?
    ", [$order_id, $user_id]);

    if (!$order) {
        header('Location: order_history.php');
        exit;
    }

    // Get order items
    $order_items = $db->fetchAll("
        SELECT oi.*, p.name as product_name, p.image_url 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ", [$order_id]);
    
    $show_details = true;
} else {
    // Show order list
    $orders = $db->fetchAll("
        SELECT o.*, COUNT(oi.id) as item_count 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.id DESC
    ", [$user_id]);
    
    // Debug: Log the query and results
    error_log("Order history query for user_id: " . $user_id);
    error_log("Orders found: " . count($orders));
    if (empty($orders)) {
        // Check if there are orders with NULL user_id that might belong to this user
        $null_orders = $db->fetchAll("SELECT * FROM orders WHERE user_id IS NULL ORDER BY id DESC LIMIT 5");
        error_log("Orders with NULL user_id: " . count($null_orders));
    }
    
    $show_details = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $show_details ? 'Order Details' : 'Order History'; ?> - Merch Shop</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container py-4">
        <?php if ($show_details): ?>
        <!-- Order Details View -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="order_history.php">Order History</a></li>
                <li class="breadcrumb-item active">Order Details</li>
            </ol>
        </nav>
        
        <div class="row mb-4">
            <div class="col">
                <h2>Order Details</h2>
                <p class="text-muted">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
            </div>
            <div class="col-auto">
                <div class="d-print-none">
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Order Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-tshirt text-primary me-3 fa-lg"></i>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['size']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>KSh <?php echo number_format($item['unit_price'], 2); ?></td>
                                            <td>KSh <?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                        <td><strong>KSh <?php echo number_format($order['total_amount'] - 200, 2); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                        <td><strong>KSh 200.00</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>KSh <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Order Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Order Status:</strong><br>
                            <span class="badge bg-<?php echo getStatusBadgeColor($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Payment Status:</strong><br>
                            <span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Order Date:</strong><br>
                            <?php echo date('F j, Y g:i A', strtotime($order['created_at'] ?? $order['order_date'] ?? 'now')); ?>
                        </div>
                        
                        <?php if ($order['mpesa_receipt']): ?>
                        <div class="mb-3">
                            <strong>M-Pesa Receipt:</strong><br>
                            <?php echo $order['mpesa_receipt']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Customer:</strong><br>
                            <?php echo htmlspecialchars($order['full_name']); ?><br>
                            <?php echo htmlspecialchars($order['email']); ?><br>
                            <?php echo htmlspecialchars($order['phone']); ?>
                        </div>
                        
                        <?php if ($order['shipping_address']): ?>
                        <div class="mb-3">
                            <strong>Shipping Address:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>Contact Phone:</strong><br>
                            <?php echo htmlspecialchars($order['phone_number']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Progress -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Order Progress</h5>
            </div>
            <div class="card-body">
                <div class="progress-container">
                    <?php
                    $steps = [
                        'pending' => ['Pending', 'Order received'],
                        'confirmed' => ['Confirmed', 'Payment verified'],
                        'processing' => ['Processing', 'Preparing order'],
                        'shipped' => ['Shipped', 'On the way'],
                        'delivered' => ['Delivered', 'Order complete']
                    ];
                    
                    $current_step = array_search($order['status'], array_keys($steps));
                    ?>
                    
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo ($current_step / (count($steps) - 1)) * 100; ?>%">
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <?php $i = 0; ?>
                        <?php foreach ($steps as $status => $step_info): ?>
                            <div class="col">
                                <div class="step <?php echo $i <= $current_step ? 'active' : ''; ?>">
                                    <div class="step-icon">
                                        <i class="fas fa-<?php echo $i < $current_step ? 'check' : ($i == $current_step ? 'sync-alt' : 'clock'); ?>"></i>
                                    </div>
                                    <h6 class="mt-2"><?php echo $step_info[0]; ?></h6>
                                    <small class="text-muted"><?php echo $step_info[1]; ?></small>
                                </div>
                            </div>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Order List View -->
        <div class="row mb-4">
            <div class="col">
                <h2>Order History</h2>
                <p class="text-muted">View and track your orders</p>
            </div>
        </div>
        
        <?php if (empty($orders)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                <h4>No Orders Yet</h4>
                <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="catalog.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order Number</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'] ?? $order['order_date'] ?? 'now')); ?></td>
                                <td><?php echo $order['item_count']; ?> items</td>
                                <td>KSh <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeColor($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_history.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>

    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
    .step-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: all 0.3s ease;
    }
    
    .step.active .step-icon {
        background: var(--primary-color);
        color: white;
    }
    
    .step h6 {
        transition: all 0.3s ease;
    }
    
    .step.active h6 {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    @media print {
        .d-print-none {
            display: none !important;
        }
        
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }
    </style>
</body>
</html>

<?php
function getStatusBadgeColor($status) {
    switch ($status) {
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
