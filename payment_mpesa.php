<?php
// M-Pesa Payment Page
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'config/environment.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';
require_once 'includes/payment.php';
require_once 'includes/db.php';

$auth = new Auth();

// Guest checkout allowed (login optional)

// Check if checkout data exists or if this is a retry
$is_retry = isset($_GET['retry']) && isset($_GET['order_id']);
$retry_order_id = $is_retry ? (int)$_GET['order_id'] : null;

if (!isset($_SESSION['checkout_data']) && !$is_retry) {
    ob_end_clean();
    header('Location: checkout.php', true, 302);
    exit();
}

// For retry, get order details from database
if ($is_retry && $retry_order_id > 0) {
    $order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$retry_order_id]);
    
    if (!$order) {
        ob_end_clean();
        header('Location: checkout.php', true, 302);
        exit();
    }
    
    // Verify user can access this order
    $session_user_id = $_SESSION['user_id'] ?? null;
    if (!empty($order['user_id'])) {
        if (empty($session_user_id) || (int)$order['user_id'] !== (int)$session_user_id) {
            ob_end_clean();
            header('Location: checkout.php', true, 302);
            exit();
        }
    }
    
    // Create checkout data from order
    $checkout_data = [
        'phone_number' => $order['phone_number'],
        'total_amount' => $order['total_amount'],
        'shipping_address' => $order['shipping_address'],
        'shipping_fee' => $order['shipping_fee'] ?? 0,
        'datetime' => 'Retry for Order #' . ($order['order_number'] ?? $retry_order_id)
    ];
} else {
    $checkout_data = $_SESSION['checkout_data'];
}
$cart = new Cart();
$payment = new Payment();
$db = new DBHelper();

$pending_order_id = $_SESSION['mpesa_pending_order_id'] ?? null;

// FORCE extract values from session to avoid any caching issues
$session_phone = $checkout_data['phone_number'];
$session_amount = $checkout_data['total_amount'];
$session_address = $checkout_data['shipping_address'];

$mpesa_min_amount = (float)($_ENV['INTASEND_MPESA_MIN_AMOUNT'] ?? 1);
$mpesa_min_amount = max(0, $mpesa_min_amount);
$simulate_payments = filter_var($_ENV['INTASEND_SIMULATE'] ?? $_ENV['MPESA_SIMULATE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

// Format phone for display
$display_phone = $session_phone;
if (substr($display_phone, 0, 3) === '254') {
    $display_phone = '+' . $display_phone;
}

// Debug: Log what we're reading from session
error_log("=== PAYMENT_MPESA.PHP - Reading Session ===");
error_log("Session checkout_data: " . json_encode($checkout_data));
error_log("Phone from session: " . $session_phone);
error_log("Amount from session: " . $session_amount);
error_log("Session timestamp: " . ($checkout_data['datetime'] ?? 'NO TIMESTAMP'));

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (ob_get_level()) {
        ob_end_clean();
    }

    $amount_to_charge = (float)$session_amount;
    if ($mpesa_min_amount > 0 && $amount_to_charge > 0 && $amount_to_charge < $mpesa_min_amount) {
        unset($_SESSION['mpesa_pending_order_id']);
        $error = 'M-Pesa STK requires a minimum amount of KSh ' . number_format($mpesa_min_amount, 2) . '. Please add more items or choose another payment method.';
    }

    // Log the phone number being used
    error_log("=== PAYMENT_MPESA.PHP - Processing Payment ===");
    error_log("Phone number: " . $session_phone);
    error_log("Total amount: " . $session_amount);
    error_log("User ID: " . ($_SESSION['user_id'] ?? 'GUEST'));

    if (!isset($error)) {
        if ($is_retry && $retry_order_id > 0) {
            // For retry, use existing order
            $order_result = ['success' => true, 'order_id' => $retry_order_id];
            
            // Reset order status to allow retry
            $db->update('orders', [
                'payment_status' => 'pending',
                'status' => 'pending',
                'failure_reason' => null
            ], "id = " . (int)$retry_order_id);
            
        } elseif (!empty($pending_order_id)) {
            $order_result = ['success' => true, 'order_id' => (int)$pending_order_id];
        } else {
            // Create actual order in database
            $order_result = $payment->createOrder(
                $_SESSION['user_id'] ?? null, 
                $session_phone, 
                $session_address,
                'mpesa',
                (float)($checkout_data['shipping_fee'] ?? 0)
            );
        }
    }

    if (!isset($error) && $order_result['success']) {
        $order_id = $order_result['order_id'];

        $order_row = $db->fetchOne("SELECT total_amount FROM orders WHERE id = ?", [$order_id]);
        if (!empty($order_row) && isset($order_row['total_amount'])) {
            $session_amount = (float)$order_row['total_amount'];
        }

        // If this is a guest checkout, remember the order so we can attach it after login
        if (empty($_SESSION['user_id'])) {
            if (!isset($_SESSION['guest_order_ids']) || !is_array($_SESSION['guest_order_ids'])) {
                $_SESSION['guest_order_ids'] = [];
            }
            $_SESSION['guest_order_ids'][] = (int)$order_id;
            $_SESSION['guest_order_ids'] = array_values(array_unique($_SESSION['guest_order_ids']));
        }
        
        // Try REAL M-Pesa STK Push first (or simulation if enabled)
        $mpesa_result = $payment->initiateMpesaPayment(
            $session_phone,
            $session_amount,
            $order_id
        );
        
        // If M-Pesa API fails, show the real error (no silent simulation)
        if (!$mpesa_result['success']) {
            $_SESSION['mpesa_pending_order_id'] = (int)$order_id;
            $error = 'Failed to send STK push: ' . ($mpesa_result['message'] ?? 'Unknown error');
            if (!empty($mpesa_result['http_code'])) {
                $error .= ' (HTTP ' . $mpesa_result['http_code'] . ')';
            }
            if (!empty($mpesa_result['response'])) {
                $error .= ' Response: ' . $mpesa_result['response'];
            }
            error_log("M-Pesa STK Push failed: " . $error);
        } else {
            unset($_SESSION['mpesa_pending_order_id']);
            $checkout_request_id = $mpesa_result['checkout_request_id'];
            $merchant_request_id = $mpesa_result['merchant_request_id'];
            $_SESSION['simulation_mode'] = $simulate_payments;
            if ($simulate_payments) {
                if (!isset($_SESSION['sim_pin_' . (int)$order_id])) {
                    $_SESSION['sim_pin_' . (int)$order_id] = '1234';
                }
            }
        }
        
        if (!isset($error)) {
            // Clear cart only if not a retry (cart already cleared for original order)
            if (!$is_retry) {
                $cart->clear();
            }

            // Mark order as processing once STK has been sent (guidance for admin)
            try {
                $db->update('orders', ['status' => 'processing'], "id = " . (int)$order_id);
            } catch (Exception $e) {
                // ignore
            }

            $_SESSION['checkout_request_id'] = $checkout_request_id;
            $_SESSION['merchant_request_id'] = $merchant_request_id;
            $_SESSION['order_id'] = $order_id;
            $_SESSION['payment_method'] = 'mpesa';
            
            // Redirect to payment status page
            header('Location: payment_status.php?order_id=' . (int)$order_id, true, 302);
            exit();
        }
    } else {
        $error = $order_result['message'];
    }
}

// Clean output buffer for page display
if (ob_get_level()) {
    ob_end_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Pesa Payment - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        .phone-mockup {
            max-width: 300px;
            margin: 20px auto;
            padding: 20px;
            border: 3px solid #333;
            border-radius: 30px;
            background: #f5f5f5;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .stk-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white text-center">
                        <h4 class="mb-0">
                            <i class="fas fa-mobile-alt me-2"></i>
                            M-Pesa Payment
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <?php if ($is_retry): ?>
                                <div class="alert alert-warning mb-3">
                                    <i class="fas fa-redo me-2"></i>
                                    <strong>Retry Payment</strong> - Attempting payment again for Order #<?php echo htmlspecialchars($order['order_number'] ?? $retry_order_id); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="phone-mockup stk-animation">
                                <i class="fas fa-mobile-alt fa-4x text-success"></i>
                                <p class="mt-3 mb-0"><strong>STK Push</strong></p>
                            </div>
                            <h5><?php echo $is_retry ? 'Retry Your M-Pesa Payment' : 'Complete Your M-Pesa Payment'; ?></h5>
                            <p class="text-muted">You will receive an M-Pesa prompt on your phone</p>
                        </div>
                        
                        <div class="bg-light p-3 rounded mb-4">
                            <h6>Payment Details:</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Amount:</span>
                                <strong>KSh <?php echo number_format($session_amount, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phone:</span>
                                <strong><?php echo htmlspecialchars($display_phone); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Account:</span>
                                <strong>SmartSchool Uniforms</strong>
                            </div>
                            <?php if (isset($checkout_data['datetime'])): ?>
                            <div class="mt-2 pt-2 border-top">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Data from checkout: <?php echo $checkout_data['datetime']; ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>How to Pay:</h6>
                            <ol class="mb-0">
                                <li>Click "Send Payment Request" below</li>
                                <li>You'll receive an M-Pesa prompt on your phone</li>
                                <li>Enter your M-Pesa PIN to complete payment</li>
                                <li>You'll receive confirmation once payment is successful</li>
                            </ol>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Send Payment Request
                                </button>
                                <a href="checkout.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Checkout
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
