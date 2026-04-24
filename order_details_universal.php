<?php
require_once 'config/environment.php';

require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireAuth();

$db = new DBHelper();

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    $_SESSION['error'] = 'Order not found.';
    header('Location: dashboard.php?tab=orders');
    exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$is_admin = $auth->isAdmin();

if ($is_admin) {
    $order = $db->fetchOne(
        "SELECT o.*, u.full_name, u.email, u.phone FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?",
        [$order_id]
    );
} else {
    $order = $db->fetchOne(
        "SELECT o.*, u.full_name, u.email, u.phone FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?",
        [$order_id, $user_id]
    );
}

if (!$order) {
    $_SESSION['error'] = 'Order not found or access denied.';
    header('Location: dashboard.php?tab=orders');
    exit;
}

$order_items = $db->fetchAll(
    "SELECT oi.*, p.name as product_name, p.image_url as product_image_url, p.private_id as product_private_id
     FROM order_items oi
     LEFT JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?",
    [$order_id]
);

function orderBadgeClass($status) {
    $s = strtolower(trim((string)$status));
    if ($s === 'pending') return 'secondary';
    if ($s === 'confirmed') return 'primary';
    if ($s === 'processing') return 'info';
    if ($s === 'shipped') return 'warning';
    if ($s === 'delivered') return 'success';
    if ($s === 'cancelled' || $s === 'canceled') return 'danger';
    return 'secondary';
}

function paymentBadgeClass($status) {
    $s = strtolower(trim((string)$status));
    if ($s === 'paid') return 'success';
    if ($s === 'pending') return 'warning';
    if ($s === 'failed') return 'danger';
    return 'secondary';
}

$order_number = $order['order_number'] ?? ('#' . (string)$order_id);
$order_date = $order['order_date'] ?? $order['created_at'] ?? '';
$order_status = $order['status'] ?? $order['order_status'] ?? 'pending';
$payment_status = $order['payment_status'] ?? 'pending';
$total_amount = (float)($order['total_amount'] ?? 0);
$shipping_fee = (float)($order['shipping_fee'] ?? 0);
$customer_name = (string)($order['full_name'] ?? $order['customer_name'] ?? '');
$customer_email = (string)($order['email'] ?? '');
$customer_phone = (string)($order['phone'] ?? $order['phone_number'] ?? '');
$shipping_address = (string)($order['shipping_address'] ?? '');

$subtotal = 0.0;
foreach ($order_items as $item) {
    $subtotal += (float)($item['unit_price'] ?? 0) * (int)($item['quantity'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'views/header.php'; ?>

    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php?tab=orders">Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">Order Details</li>
            </ol>
        </nav>

        <?php if (isset($_SESSION['error']) && $_SESSION['error'] !== ''): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h2 class="mb-1">Order Details</h2>
                <div class="text-muted">Order <?php echo htmlspecialchars((string)$order_number); ?></div>
            </div>
            <div class="d-print-none">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Order Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th class="text-end">Unit</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <?php
                                            $pid = $item['product_private_id'] ?? $item['product_id'] ?? '';
                                            $line_total = (float)($item['unit_price'] ?? 0) * (int)($item['quantity'] ?? 0);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($item['product_image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($item['product_image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>" class="rounded me-3" style="width: 44px; height: 44px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <i class="fas fa-tshirt text-primary me-3 fa-lg"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($item['product_name'] ?? 'Item'); ?></div>
                                                        <?php if (!empty($pid)): ?>
                                                            <a href="product.php?pid=<?php echo urlencode((string)$pid); ?>" class="small text-decoration-none">View product</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars((string)($item['size'] ?? '')); ?></td>
                                            <td><?php echo (int)($item['quantity'] ?? 0); ?></td>
                                            <td class="text-end">KSh <?php echo number_format((float)($item['unit_price'] ?? 0), 2); ?></td>
                                            <td class="text-end">KSh <?php echo number_format($line_total, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Subtotal</strong></td>
                                        <td class="text-end"><strong>KSh <?php echo number_format($subtotal, 2); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Shipping</strong></td>
                                        <td class="text-end"><strong>KSh <?php echo number_format($shipping_fee, 2); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total</strong></td>
                                        <td class="text-end"><strong>KSh <?php echo number_format($total_amount, 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Order Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Order Status</div>
                            <span class="badge bg-<?php echo orderBadgeClass($order_status); ?>">
                                <?php echo htmlspecialchars(ucfirst((string)$order_status)); ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Payment Status</div>
                            <span class="badge bg-<?php echo paymentBadgeClass($payment_status); ?>">
                                <?php echo htmlspecialchars(ucfirst((string)$payment_status)); ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Order Date</div>
                            <div><?php echo $order_date !== '' ? htmlspecialchars(date('F j, Y g:i A', strtotime($order_date))) : 'N/A'; ?></div>
                        </div>
                        <?php if (!empty($order['payment_method'])): ?>
                            <div class="mb-3">
                                <div class="text-muted small">Payment Method</div>
                                <div><?php echo htmlspecialchars((string)$order['payment_method']); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order['mpesa_receipt'])): ?>
                            <div class="mb-0">
                                <div class="text-muted small">M-Pesa Receipt</div>
                                <div class="fw-semibold"><?php echo htmlspecialchars((string)$order['mpesa_receipt']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Shipping</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Customer</div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($customer_name !== '' ? $customer_name : 'N/A'); ?></div>
                            <?php if ($customer_email !== ''): ?>
                                <div><?php echo htmlspecialchars($customer_email); ?></div>
                            <?php endif; ?>
                            <?php if ($customer_phone !== ''): ?>
                                <div><?php echo htmlspecialchars($customer_phone); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($shipping_address !== ''): ?>
                            <div class="mb-0">
                                <div class="text-muted small">Address</div>
                                <div><?php echo nl2br(htmlspecialchars($shipping_address)); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'views/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
