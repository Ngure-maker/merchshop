<?php
// Admin Payment Management
require_once '../config/environment.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/payment.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = new DBHelper();

// Handle manual payment recording
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'record_manual_payment') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $transaction_code = trim((string)($_POST['transaction_code'] ?? ''));
    $payment_method = trim((string)($_POST['payment_method'] ?? 'manual'));
    $payment = new Payment();
    $result = $payment->recordManualPayment($order_id, $transaction_code, $payment_method);

    if (!empty($result['success'])) {
        $_SESSION['success'] = $result['message'] ?? 'Manual payment recorded';
    } else {
        $_SESSION['error'] = $result['message'] ?? 'Failed to record payment';
    }
    header('Location: payments.php');
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Build query conditions
$conditions = [];
$params = [];

if ($status_filter !== 'all') {
    // Normalize filter to match our normalized_status logic
    if ($status_filter === 'completed') {
        $conditions[] = "(LOWER(p.status) IN ('completed','paid','success'))";
    } elseif ($status_filter === 'failed') {
        $conditions[] = "LOWER(p.status) = 'failed'";
    } elseif ($status_filter === 'pending') {
        $conditions[] = "(LOWER(p.status) NOT IN ('completed','paid','success','failed'))";
    } else {
        $conditions[] = "p.status = ?";
        $params[] = $status_filter;
    }
}

if ($date_filter) {
    $conditions[] = "DATE(p.created_at) = ?";
    $params[] = $date_filter;
}

if ($search) {
    $conditions[] = "(p.phone_number LIKE ? OR p.mpesa_receipt LIKE ? OR o.payment_code LIKE ? OR o.mpesa_receipt LIKE ? OR o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param]);
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get payments with order and user details
$query = "
    SELECT 
        p.*,
        COALESCE(NULLIF(p.mpesa_receipt, ''), NULLIF(o.payment_code, ''), NULLIF(o.mpesa_receipt, '')) AS transaction_code,
        o.order_number,
        o.total_amount as order_total,
        o.shipping_address,
        o.status as order_status,
        o.payment_status,
        u.name as customer_name,
        u.email as customer_email,
        CASE 
            WHEN LOWER(p.status) IN ('completed','paid','success') THEN 'completed'
            WHEN LOWER(p.status) = 'failed' THEN 'failed'
            ELSE 'pending'
        END AS normalized_status,
        GROUP_CONCAT(
            CONCAT(pr.name, ' (', oi.size, ') x', oi.quantity) 
            SEPARATOR ', '
        ) as products
    FROM payments p
    LEFT JOIN orders o ON p.order_id = o.id
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products pr ON oi.product_id = pr.id
    $where_clause
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 100
";

$payments = $db->fetchAll($query, $params);

// Get summary statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_payments,
        SUM(CASE WHEN LOWER(status) IN ('completed','paid','success') THEN 1 ELSE 0 END) as completed_payments,
        SUM(CASE WHEN LOWER(status) = 'failed' THEN 1 ELSE 0 END) as failed_payments,
        SUM(CASE WHEN LOWER(status) NOT IN ('completed','paid','success','failed') THEN 1 ELSE 0 END) as pending_payments,
        SUM(CASE WHEN LOWER(status) IN ('completed','paid','success') THEN amount ELSE 0 END) as total_revenue
    FROM payments
    WHERE DATE(created_at) = CURDATE()
";
$stats = $db->fetchOne($stats_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        .payment-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #28a745;
        }
        .status-badge {
            font-size: 0.8em;
        }
        .stats-card {
            border-left: 4px solid rgb(6, 25, 67);
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php include '../views/admin_header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-credit-card me-2"></i>Payment Management</h2>
                    <div>
                        <button class="btn btn-outline-primary" onclick="refreshPayments()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted">Today's Payments</h6>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_payments'] ?? 0); ?></h4>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fas fa-receipt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted">Completed</h6>
                                        <h4 class="mb-0 text-success"><?php echo number_format($stats['completed_payments'] ?? 0); ?></h4>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted">Failed</h6>
                                        <h4 class="mb-0 text-danger"><?php echo number_format($stats['failed_payments'] ?? 0); ?></h4>
                                    </div>
                                    <div class="text-danger">
                                        <i class="fas fa-times-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title text-muted">Revenue Today</h6>
                                        <h4 class="mb-0 text-success">KSh <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h4>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-money-bill-wave fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Phone, Receipt, Order, Customer..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Records (<?php echo count($payments); ?> results)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Products</th>
                                        <th>Amount</th>
                                        <th>Transaction Code</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($payments)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No payment records found</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y', strtotime($payment['created_at'])); ?><br>
                                                        <?php echo date('g:i A', strtotime($payment['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong>#<?php echo htmlspecialchars($payment['order_number'] ?? $payment['order_id']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php 
                                                        $order_status = $payment['order_status'] ?? 'unknown';
                                                        $payment_status = $payment['payment_status'] ?? 'unknown';
                                                        echo ucfirst($order_status) . ' / ' . ucfirst($payment_status);
                                                        ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if (!empty($payment['customer_name'])): ?>
                                                        <strong><?php echo htmlspecialchars($payment['customer_name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($payment['customer_email']); ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Guest Customer</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="payment-code">
                                                        <?php 
                                                        $phone = $payment['phone_number'];
                                                        if (substr($phone, 0, 3) === '254') {
                                                            echo '+' . $phone;
                                                        } else {
                                                            echo $phone;
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php 
                                                        $products = $payment['products'] ?? 'No products';
                                                        echo strlen($products) > 50 ? substr($products, 0, 50) . '...' : $products;
                                                        ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong>KSh <?php echo number_format($payment['amount'], 2); ?></strong>
                                                </td>
                                                <td>
                                                    <?php if (!empty($payment['transaction_code'])): ?>
                                                        <span class="payment-code"><?php echo htmlspecialchars($payment['transaction_code']); ?></span>
                                                    <?php elseif (!empty($payment['checkout_request_id'])): ?>
                                                        <small class="text-muted">
                                                            <?php echo substr($payment['checkout_request_id'], 0, 12) . '...'; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status = $payment['normalized_status'] ?? $payment['status'];
                                                    $badge_class = match($status) {
                                                        'completed' => 'bg-success',
                                                        'failed' => 'bg-danger',
                                                        'pending' => 'bg-warning',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?> status-badge">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                    <?php if ($status === 'failed' && !empty($payment['failure_reason'])): ?>
                                                        <br>
                                                        <small class="text-danger" title="<?php echo htmlspecialchars($payment['failure_reason']); ?>">
                                                            <?php echo substr($payment['failure_reason'], 0, 20) . '...'; ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="order_details.php?id=<?php echo $payment['order_id']; ?>" 
                                                           class="btn btn-outline-primary" title="View Order">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($status === 'pending'): ?>
                                                            <button class="btn btn-outline-success" 
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#manualPaymentModal"
                                                                    data-order-id="<?php echo (int)$payment['order_id']; ?>"
                                                                    data-order-number="<?php echo htmlspecialchars($payment['order_number'] ?? $payment['order_id']); ?>"
                                                                    title="Record Manual Payment">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button class="btn btn-outline-warning" 
                                                                    onclick="checkPaymentStatus(<?php echo $payment['id']; ?>)" 
                                                                    title="Check Status">
                                                                <i class="fas fa-sync-alt"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Payment Modal -->
    <div class="modal fade" id="manualPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="record_manual_payment">
                    <input type="hidden" name="order_id" id="manual_order_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Manual Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-2">Order: <strong id="manual_order_number">#</strong></p>
                        <div class="mb-3">
                            <label class="form-label">Transaction Code</label>
                            <input type="text" class="form-control" name="transaction_code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method">
                                <option value="mpesa">M-Pesa</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                        <small class="text-muted">Use this if the payment message is delayed or received outside the system.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function refreshPayments() {
            window.location.reload();
        }

        async function checkPaymentStatus(paymentId) {
            try {
                const response = await fetch(`../check_payment_status_ajax.php?payment_id=${paymentId}`);
                const data = await response.json();
                
                if (data.success) {
                    alert('Payment status updated. Refreshing page...');
                    window.location.reload();
                } else {
                    alert('Failed to check payment status: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error checking payment status: ' + error.message);
            }
        }

        const manualPaymentModal = document.getElementById('manualPaymentModal');
        if (manualPaymentModal) {
            manualPaymentModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                const orderId = button.getAttribute('data-order-id');
                const orderNumber = button.getAttribute('data-order-number');
                const idInput = document.getElementById('manual_order_id');
                const numberEl = document.getElementById('manual_order_number');
                if (idInput) idInput.value = orderId || '';
                if (numberEl) numberEl.textContent = '#' + (orderNumber || orderId || '');
            });
        }

        // Auto-refresh every 30 seconds for pending payments
        setInterval(() => {
            const pendingRows = document.querySelectorAll('.badge.bg-warning');
            if (pendingRows.length > 0) {
                console.log('Auto-refreshing for pending payments...');
                refreshPayments();
            }
        }, 30000);
    </script>
</body>
</html>
