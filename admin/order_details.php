<?php
// Use centralized session management
require_once '../config/environment.php';

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

require_once __DIR__ . '/../includes/settings.php';

$auth = new Auth();
$auth->requireAdmin();

// Helper functions (defined early for templates)
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

function getOrderStatusValue($order) {
    return $order['status'] ?? $order['order_status'] ?? 'pending';
}

function getOrderDateValue($order) {
    return $order['order_date'] ?? $order['created_at'] ?? null;
}

$db = new DBHelper();

$id_param = trim((string)($_GET['id'] ?? ''));
if ($id_param === '') {
    if (!empty($_GET['modal'])) {
        echo '<div class="alert alert-danger">No order ID provided</div>';
        exit;
    }
    $_SESSION['error'] = 'Order not found';
    header('Location: orders.php');
    exit;
}

// Simplified order ID resolution - just use the ID as provided
$order_id = (int)$id_param;

if ($order_id <= 0) {
    if (!empty($_GET['modal'])) {
        echo '<div class="alert alert-danger">Invalid order ID: ' . htmlspecialchars($id_param) . '</div>';
        exit;
    }
    $_SESSION['error'] = 'Invalid order ID: ' . $id_param;
    header('Location: orders.php');
    exit;
}

// Simple order fetch with better error handling
try {
    $order = $db->fetchOne("
        SELECT o.*, 
               p.mpesa_receipt AS pay_mpesa_receipt,
               p.transaction_id AS pay_transaction_id,
               p.checkout_request_id AS pay_checkout_request_id,
               p.merchant_request_id AS pay_merchant_request_id,
               p.status AS pay_status,
               p.payment_method AS pay_payment_method
        FROM orders o
        LEFT JOIN (
            SELECT p1.*
            FROM payments p1
            INNER JOIN (
                SELECT order_id, MAX(id) AS max_id
                FROM payments
                GROUP BY order_id
            ) p2 ON p1.id = p2.max_id
        ) p ON p.order_id = o.id
        WHERE o.id = ?
    ", [$order_id]);

    if (!$order) {
        // Try without JOIN if the complex query fails
        $order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$order_id]);
        
        if ($order) {
            // Add default values for missing user data
            $order['full_name'] = $order['customer_name'] ?? ($order['phone_number'] ?? 'Unknown Customer');
            $order['email'] = 'N/A';
            $order['phone'] = $order['phone_number'] ?? '';
        }
    } else {
        // Fetch user data separately to support both users.full_name and users.name schemas
        $order['full_name'] = $order['customer_name'] ?? '';
        $order['email'] = $order['email'] ?? '';
        $order['phone'] = $order['phone'] ?? '';

        $user_id = !empty($order['user_id']) ? (int)$order['user_id'] : 0;
        if ($user_id > 0) {
            $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$user_id]);
            if (!empty($user)) {
                $order['full_name'] = (string)($user['full_name'] ?? $user['name'] ?? $order['full_name'] ?? '');
                $order['email'] = (string)($user['email'] ?? $order['email'] ?? '');
                $order['phone'] = (string)($user['phone'] ?? $order['phone'] ?? '');
            }
        }

        if (trim((string)($order['full_name'] ?? '')) === '') {
            $order['full_name'] = (string)($order['phone_number'] ?? $order['customer_name'] ?? '');
        }
        if (trim((string)($order['email'] ?? '')) === '') {
            $order['email'] = 'N/A';
        }
        if (trim((string)($order['phone'] ?? '')) === '') {
            $order['phone'] = (string)($order['phone_number'] ?? '');
        }
    }
} catch (Exception $e) {
    error_log("Order fetch error: " . $e->getMessage());
    if (!empty($_GET['modal'])) {
        echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        exit;
    }
    $_SESSION['error'] = 'Database error occurred';
    header('Location: orders.php');
    exit;
}

if (!$order) {
    if (!empty($_GET['modal'])) {
        echo '<div class="alert alert-danger">Order not found: ' . htmlspecialchars($id_param) . '</div>';
        exit;
    }
    $_SESSION['error'] = 'Order not found: ' . $id_param;
    header('Location: orders.php');
    exit;
}

// Get order items using the order ID
try {
    $order_items = $db->fetchAll("
        SELECT oi.*, p.name as product_name, p.description as product_description
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ", [$order_id]);
} catch (Exception $e) {
    error_log("Order items fetch error: " . $e->getMessage());
    $order_items = [];
}

$order_number = $order['order_number'] ?? $order['id'];
$order_status = getOrderStatusValue($order);
$order_date = getOrderDateValue($order);

// Compute totals and logo path for both modal and full-page
$site_name = (string)getSetting('site_name', 'Merch Shop');
$site_logo = (string)getSetting('site_logo', 'assets/images/logo.png');
$admin_email = (string)getSetting('admin_email', (defined('ADMIN_EMAIL') ? ADMIN_EMAIL : ''));

// Normalize logo path so it works when this receipt is served from /admin/*.
// If the stored path is relative (e.g. assets/images/logo.png), prefix ../.
$receipt_logo = trim($site_logo);
if ($receipt_logo !== ''
    && stripos($receipt_logo, 'http://') !== 0
    && stripos($receipt_logo, 'https://') !== 0
    && strpos($receipt_logo, '/') !== 0
    && strpos($receipt_logo, '../') !== 0
) {
    $receipt_logo = '../' . $receipt_logo;
}

// Compute totals
$subtotal = 0.0;
$shipping_fee = (float)($order['shipping_fee'] ?? 0);
foreach ($order_items as $item) {
    $subtotal += (float)($item['unit_price'] ?? 0) * (int)($item['quantity'] ?? 0);
}
$total = $subtotal + $shipping_fee;

// If requested as modal, return only the receipt HTML
if (!empty($_GET['modal'])) {
    
    // Debug information (remove in production)
    error_log("Receipt request for order ID: " . $order_id);
    error_log("Order data: " . print_r($order, true));
    error_log("Order items count: " . count($order_items));
    
    ob_start();
    ?>
    <div class="receipt">
        <div class="header">
            <?php if (!empty($receipt_logo)): ?>
                <div class="logo-wrap">
                    <img class="logo" src="<?php echo htmlspecialchars($receipt_logo); ?>" alt="<?php echo htmlspecialchars($site_name); ?>">
                </div>
            <?php endif; ?>
            <div class="biz-name"><?php echo htmlspecialchars($site_name); ?></div>
            <?php if (!empty($admin_email)): ?>
                <div class="biz-meta"><?php echo htmlspecialchars($admin_email); ?></div>
            <?php endif; ?>
            <div class="biz-meta">Order Receipt</div>
        </div>

        <div class="kv"><span>Order ID:</span><span><?php echo htmlspecialchars('#' . $order_number); ?></span></div>
        <div class="kv"><span>Date:</span><span><?php echo $order_date ? date('d/m/Y H:i', strtotime($order_date)) : 'N/A'; ?></span></div>
        <?php
            $raw_method = strtolower(trim((string)($order['payment_method'] ?? $order['pay_payment_method'] ?? '')));
            $payment_method_label = $raw_method !== '' ? $raw_method : 'electronic';
            if ($payment_method_label === 'cash') {
                $payment_method_label = 'Cash (Delivery/Collection)';
            } elseif (in_array($payment_method_label, ['mpesa', 'intasend', 'intasend (simulation)'], true)) {
                $payment_method_label = 'M-Pesa';
            } elseif ($payment_method_label === 'airtel_money') {
                $payment_method_label = 'Airtel Money';
            } elseif ($payment_method_label === 'visa') {
                $payment_method_label = 'Visa';
            } elseif ($payment_method_label === 'mastercard') {
                $payment_method_label = 'Mastercard';
            } elseif ($payment_method_label === 'paypal') {
                $payment_method_label = 'PayPal';
            } else {
                $payment_method_label = ucfirst($payment_method_label);
            }
        ?>
        <div class="kv"><span>Method:</span><span><?php echo htmlspecialchars($payment_method_label); ?></span></div>
        <div class="kv"><span>Paid:</span><span><?php echo htmlspecialchars(ucfirst($order['payment_status'] ?? 'pending')); ?></span></div>

        <?php if (strtolower(trim($order['payment_method'] ?? '')) === 'cash' && ($order['payment_status'] ?? '') !== 'paid'): ?>
            <div class="cash-pending-notice">
                Cash payment pending admin confirmation.
            </div>
        <?php endif; ?>

        <hr>

        <div class="section-title">Customer</div>
        <?php
            $customer_name = trim((string)($order['full_name'] ?? $order['customer_name'] ?? ''));
            $customer_phone = trim((string)($order['phone'] ?? $order['phone_number'] ?? ''));
            $customer_email = trim((string)($order['email'] ?? ''));
            if ($customer_name === '' && $customer_phone !== '') {
                $customer_name = 'Customer';
            }
        ?>
        <div class="line"><strong><?php echo htmlspecialchars($customer_name !== '' ? $customer_name : 'N/A'); ?></strong></div>
        <?php if ($customer_phone !== ''): ?>
            <div class="line">Phone: <?php echo htmlspecialchars($customer_phone); ?></div>
        <?php endif; ?>
        <div class="line"><?php echo htmlspecialchars($customer_email !== '' ? $customer_email : 'N/A'); ?></div>
        <?php if (!empty($order['shipping_address'])): ?>
            <div class="section-title">Delivery</div>
            <div class="line"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></div>
        <?php endif; ?>

        <hr>

        <div class="section-title">Items</div>
        <?php if (!empty($order_items)): ?>
            <?php foreach ($order_items as $item): ?>
                <?php
                    $qty = (int)($item['quantity'] ?? 1);
                    $price = (float)($item['unit_price'] ?? 0);
                    $line_total = $qty * $price;
                    $item_name = (string)($item['product_name'] ?? ('Product #' . ($item['product_id'] ?? 'Unknown')));
                    $size = (string)($item['size'] ?? '');
                ?>
                <div class="item">
                    <div class="item-name"><?php echo htmlspecialchars(trim($item_name . ($size !== '' ? ' (' . $size . ')' : ''))); ?></div>
                    <div class="kv"><span><?php echo $qty; ?> x <?php echo number_format($price, 2); ?></span><span>KSh <?php echo number_format($line_total, 2); ?></span></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="line">No items found for this order</div>
        <?php endif; ?>

        <div class="totals">
            <div class="info-row">
                <span>Subtotal:</span>
                <span>KSh <?php echo number_format(($order['total_amount'] ?? 0) - ($order['shipping_fee'] ?? 0), 2); ?></span>
            </div>
            <?php if (!empty($order['shipping_fee']) && $order['shipping_fee'] > 0): ?>
                <div class="info-row">
                    <span>Shipping:</span>
                    <span>KSh <?php echo number_format($order['shipping_fee'], 2); ?></span>
                </div>
            <?php endif; ?>
            <div class="info-row" style="font-weight: bold; font-size: 16px; border-top: 1px solid #000; padding-top: 8px; margin-top: 8px;">
                <span>Total:</span>
                <span>KSh <?php echo number_format($order['total_amount'] ?? 0, 2); ?></span>
            </div>
        </div>

        <?php if (!empty($order['pay_mpesa_receipt'])): ?>
            <hr>
            <div><strong>M-Pesa Receipt:</strong> <?php echo htmlspecialchars($order['pay_mpesa_receipt']); ?></div>
        <?php elseif (!empty($order['mpesa_receipt'])): ?>
            <hr>
            <div><strong>M-Pesa Receipt:</strong> <?php echo htmlspecialchars($order['mpesa_receipt']); ?></div>
        <?php endif; ?>

        <?php if (!empty($order['pay_transaction_id'])): ?>
            <div><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['pay_transaction_id']); ?></div>
        <?php endif; ?>

        <div class="footer">
            THANK YOU FOR YOUR PURCHASE!
            <div class="biz-meta">Generated on <?php echo date('d/m/Y H:i'); ?></div>
        </div>
        
            
    <style>
        .receipt {
            width: 320px;
            max-width: 320px;
            margin: 0 auto;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 12px;
            line-height: 1.35;
            color: #000;
            background: #fff;
            padding: 10px 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .logo-wrap { margin-bottom: 6px; }
        .logo { max-height: 48px; max-width: 100%; object-fit: contain; }
        .biz-name { font-weight: 700; font-size: 14px; letter-spacing: .3px; }
        .biz-meta { font-size: 11px; color: #111; }
        .section-title { margin-top: 10px; font-weight: 700; text-transform: uppercase; font-size: 11px; }
        .line { margin-top: 2px; }
        .kv { display: flex; justify-content: space-between; gap: 8px; }
        .kv span:last-child { text-align: right; }
        .item { margin-top: 8px; }
        .item-name { font-weight: 700; }
        .totals { margin-top: 10px; }
        .info-row { display: flex; justify-content: space-between; margin-top: 3px; }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 11px;
        }
        .cash-pending-notice {
            margin-top: 10px;
            padding: 8px;
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.5);
            border-radius: 4px;
            font-size: 10px;
            text-align: center;
        }
        @media print {
            body { margin: 0; }
            .receipt { width: 80mm; max-width: 80mm; padding: 0; }
            .cash-pending-notice { display: none; }
        }
    </style>
    <?php
    $receipt_html = ob_get_clean();
    echo $receipt_html;
    exit;
}

// Full-page view (not modal)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo htmlspecialchars($order_number); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/zetech-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .receipt { width: 320px; max-width: 320px; margin: 0 auto; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 12px; line-height: 1.35; color: #000; background: #fff; padding: 10px 8px; border: 1px solid #ddd; }
        .header { text-align: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #000; }
        .logo-wrap { margin-bottom: 6px; }
        .logo { max-height: 48px; max-width: 100%; object-fit: contain; }
        .biz-name { font-weight: 700; font-size: 14px; letter-spacing: .3px; }
        .biz-meta { font-size: 11px; color: #111; }
        .section-title { margin-top: 10px; font-weight: 700; text-transform: uppercase; font-size: 11px; }
        .line { margin-top: 2px; }
        .kv { display: flex; justify-content: space-between; gap: 8px; }
        .kv span:last-child { text-align: right; }
        .item { margin-top: 8px; }
        .item-name { font-weight: 700; }
        .totals { margin-top: 10px; }
        .info-row { display: flex; justify-content: space-between; margin-top: 3px; }
        hr { border: none; border-top: 1px dashed #000; margin: 10px 0; }
        .footer { text-align: center; margin-top: 12px; padding-top: 10px; border-top: 1px dashed #000; font-size: 11px; }
        .cash-pending-notice { margin-top: 10px; padding: 8px; background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.5); border-radius: 4px; font-size: 10px; text-align: center; }
        @media print { body { margin: 0; } .receipt { width: 80mm; max-width: 80mm; padding: 0; } .cash-pending-notice { display: none; } }
    </style>
</head>
<body>
    <?php include '../views/admin_header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order Receipt</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                            <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Orders
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        // Re-render receipt HTML for full page
                        ob_start();
                        ?>
                        <div class="receipt">
                            <div class="header">
                                <?php if ($receipt_logo !== ''): ?>
                                    <div class="logo-wrap">
                                        <img src="<?php echo htmlspecialchars($receipt_logo); ?>" alt="Logo" class="logo">
                                    </div>
                                <?php endif; ?>
                                <div class="biz-name"><?php echo htmlspecialchars($site_name); ?></div>
                                <div class="biz-meta">Order Receipt</div>
                            </div>

                            <div class="kv"><span>Order ID:</span><span><?php echo htmlspecialchars('#' . $order_number); ?></span></div>
                            <div class="kv"><span>Date:</span><span><?php echo $order_date ? date('d/m/Y H:i', strtotime($order_date)) : 'N/A'; ?></span></div>
                            <?php
                                $raw_method = strtolower(trim((string)($order['payment_method'] ?? $order['pay_payment_method'] ?? '')));
                                $payment_method_label = $raw_method !== '' ? $raw_method : 'electronic';
                                if ($payment_method_label === 'cash') {
                                    $payment_method_label = 'Cash (Delivery/Collection)';
                                } elseif (in_array($payment_method_label, ['mpesa', 'intasend', 'intasend (simulation)'], true)) {
                                    $payment_method_label = 'M-Pesa';
                                } elseif ($payment_method_label === 'airtel_money') {
                                    $payment_method_label = 'Airtel Money';
                                } elseif ($payment_method_label === 'visa') {
                                    $payment_method_label = 'Visa';
                                } elseif ($payment_method_label === 'mastercard') {
                                    $payment_method_label = 'Mastercard';
                                } elseif ($payment_method_label === 'paypal') {
                                    $payment_method_label = 'PayPal';
                                } else {
                                    $payment_method_label = ucfirst($payment_method_label);
                                }
                            ?>
                            <div class="kv"><span>Method:</span><span><?php echo htmlspecialchars($payment_method_label); ?></span></div>
                            <div class="kv"><span>Paid:</span><span><?php echo htmlspecialchars(ucfirst($order['payment_status'] ?? 'pending')); ?></span></div>

                            <?php if (strtolower(trim($order['payment_method'] ?? '')) === 'cash' && ($order['payment_status'] ?? '') !== 'paid'): ?>
                                <div class="cash-pending-notice">
                                    Cash payment pending admin confirmation.
                                </div>
                            <?php endif; ?>

                            <hr>

                            <div class="section-title">Customer</div>
                            <?php
                                $customer_name = trim((string)($order['full_name'] ?? $order['customer_name'] ?? ''));
                                $customer_phone = trim((string)($order['phone'] ?? $order['phone_number'] ?? ''));
                                $customer_email = trim((string)($order['email'] ?? ''));
                                if ($customer_name === '' && $customer_phone !== '') {
                                    $customer_name = 'Customer';
                                }
                            ?>
                            <div class="line"><strong><?php echo htmlspecialchars($customer_name !== '' ? $customer_name : 'N/A'); ?></strong></div>
                            <?php if ($customer_phone !== ''): ?>
                                <div class="line">Phone: <?php echo htmlspecialchars($customer_phone); ?></div>
                            <?php endif; ?>
                            <div class="line"><?php echo htmlspecialchars($customer_email !== '' ? $customer_email : 'N/A'); ?></div>

                            <?php if (!empty($order['shipping_address'])): ?>
                                <hr>
                                <div class="section-title">Shipping Address</div>
                                <div class="line"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></div>
                            <?php endif; ?>

                            <hr>

                            <div class="section-title">Items</div>
                            <?php foreach ($order_items as $item): ?>
                                <div class="item">
                                    <div class="item-name"><?php echo htmlspecialchars(($item['product_name'] ?? $item['name'] ?? 'Unknown Product')); ?> (<?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?>) x<?php echo (int)($item['quantity'] ?? 0); ?></div>
                                    <div class="kv"><span></span><span>KSh <?php echo number_format((float)($item['unit_price'] ?? 0), 2); ?></span></div>
                                </div>
                            <?php endforeach; ?>

                            <hr>

                            <div class="totals">
                                <div class="info-row">
                                    <span>Subtotal:</span>
                                    <span>KSh <?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <?php if ($shipping_fee > 0): ?>
                                    <div class="info-row">
                                        <span>Shipping:</span>
                                        <span>KSh <?php echo number_format($shipping_fee, 2); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="info-row" style="font-weight: 700; font-size: 13px; border-top: 1px dashed #000; padding-top: 6px; margin-top: 6px;">
                                    <span>Total:</span>
                                    <span>KSh <?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>

                            <div class="footer">
                                Thank you for your order!
                            </div>
                        </div>
                        <?php
                        $receipt_html = ob_get_clean();
                        echo $receipt_html;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


