<?php
// Use centralized session management
require_once 'config/environment.php';
require_once 'includes/settings.php';

require_once 'includes/auth.php';
$auth = new Auth();

// Check if payment was processed
$order_id = (int)($_GET['order_id'] ?? ($_SESSION['order_id'] ?? 0));
if ($order_id <= 0) {
    header('Location: checkout.php');
    exit;
}

$payment_method = $_SESSION['payment_method'] ?? null;
$payment_status = $_SESSION['payment_status'] ?? 'pending';
$checkout_request_id = $_SESSION['checkout_request_id'] ?? '';
$simulation_mode = filter_var($_ENV['INTASEND_SIMULATE'] ?? $_ENV['MPESA_SIMULATE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$pin_lockout_seconds = (int)getSetting('pin_lockout_seconds', 600);
$pin_lockout_seconds = max(60, $pin_lockout_seconds);
$sim_lock_key = 'sim_pin_locked_until_' . (int)$order_id;
$sim_locked_until = (int)($_SESSION[$sim_lock_key] ?? 0);
$sim_locked_remaining = $sim_locked_until > time() ? ($sim_locked_until - time()) : 0;

// Clear checkout session data but keep order info for display
unset($_SESSION['checkout_data']);

// Get order details
require_once 'includes/db.php';
$db = new DBHelper();
$order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$order_id]);

if (!$order) {
    header('Location: checkout.php');
    exit;
}

// Prevent viewing other customers' orders
$session_user_id = $_SESSION['user_id'] ?? null;
if (!empty($order['user_id'])) {
    if (empty($session_user_id) || (int)$order['user_id'] !== (int)$session_user_id) {
        header('Location: checkout.php');
        exit;
    }
} else {
    if ((int)($_SESSION['order_id'] ?? 0) !== (int)$order_id) {
        header('Location: checkout.php');
        exit;
    }
}

if (empty($payment_method)) {
    $payment_method = $order['payment_method'] ?? 'mpesa';
}

$is_mpesa_like = in_array(strtolower((string)$payment_method), ['mpesa', 'intasend', 'intasend (simulation)'], true);

$payment_method_label = strtolower(trim((string)$payment_method));
if ($payment_method_label === 'cash') {
    $payment_method_label = 'Cash (Delivery/Collection)';
} elseif ($is_mpesa_like) {
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

if (($order['payment_status'] ?? '') === 'paid') {
    $payment_status = 'completed';
} elseif (($order['payment_status'] ?? '') === 'failed') {
    $payment_status = 'failed';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white text-center">
                        <h4 class="mb-0">Payment Status</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <?php if ($payment_status === 'completed'): ?>
                                    <i class="fas fa-check-circle fa-4x text-success"></i>
                                <?php elseif ($payment_status === 'failed'): ?>
                                    <i class="fas fa-times-circle fa-4x text-danger"></i>
                                <?php else: ?>
                                    <i class="fas fa-clock fa-4x text-warning"></i>
                                <?php endif; ?>
                            </div>
                            <h5 id="payment-title">
                                <?php if ($payment_status === 'completed'): ?>
                                    Payment Successful (Paid)!
                                <?php elseif ($payment_status === 'failed'): ?>
                                    Payment Failed
                                <?php else: ?>
                                    Payment Processing
                                <?php endif; ?>
                            </h5>
                            <p class="text-muted" id="payment-subtitle">
                                <?php if ($payment_status === 'completed'): ?>
                                    Your payment is completed and your order is fully paid.
                                <?php elseif ($payment_status === 'failed'): ?>
                                    Your payment was cancelled or failed. You can try again or use a different payment method.
                                <?php else: ?>
                                    <?php if ($is_mpesa_like && $simulation_mode): ?>
                                        Waiting for M-Pesa PIN confirmation. Enter your PIN below to complete payment.
                                    <?php else: ?>
                                        Your payment is being processed. You will receive a confirmation shortly.
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="bg-light p-3 rounded mb-4">
                            <h6>Order Details:</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Order ID:</span>
                                <strong>#<?php echo $order['order_number'] ?? $order_id; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Payment Method:</span>
                                <strong><?php echo htmlspecialchars($payment_method_label); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Amount:</span>
                                <strong>KSh <?php echo number_format($order['total_amount'] ?? 0, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Status:</span>
                                <strong id="status-badge">
                                    <?php if ($payment_status === 'completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php elseif ($payment_status === 'failed'): ?>
                                        <span class="badge bg-danger">Failed</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Processing</span>
                                    <?php endif; ?>
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span>Payment:</span>
                                <strong>
                                    <?php if ($payment_status === 'completed'): ?>
                                        <span class="text-success">Paid</span>
                                    <?php elseif ($payment_status === 'failed'): ?>
                                        <span class="text-danger">Failed</span>
                                    <?php else: ?>
                                        <span class="text-warning">Pending</span>
                                    <?php endif; ?>
                                </strong>
                            </div>
                            <div id="transaction-row" class="d-flex justify-content-between mt-2" style="<?php echo (!empty($order['payment_code']) || !empty($order['mpesa_receipt'])) ? '' : 'display:none;'; ?>">
                                <span>Transaction Code:</span>
                                <strong id="transaction-code"><?php echo htmlspecialchars($order['payment_code'] ?? $order['mpesa_receipt'] ?? ''); ?></strong>
                            </div>
                            <div id="failure-row" class="d-flex justify-content-between mt-2" style="<?php echo ($payment_status === 'failed' && !empty($order['failure_reason'])) ? '' : 'display:none;'; ?>">
                                <span>Failure Reason:</span>
                                <strong id="failure-reason" class="text-danger"><?php echo htmlspecialchars($order['failure_reason'] ?? ''); ?></strong>
                            </div>
                        </div>
                        
                        <?php if ($payment_status === 'failed'): ?>
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Payment Failed:</h6>
                                <p class="mb-2">
                                    Your payment was not completed. This could be due to:
                                </p>
                                <ul class="mb-2">
                                    <li>Cancelled by user</li>
                                    <li>Incorrect PIN entered</li>
                                    <li>Insufficient funds</li>
                                    <li>Network timeout</li>
                                </ul>
                                <p class="mb-0">
                                    <strong>You can try again with the same order or create a new order.</strong>
                                </p>
                            </div>
                            
                            <div class="d-grid gap-2 mb-3">
                                <a href="payment_mpesa.php?order_id=<?php echo $order_id; ?>&retry=1" class="btn btn-warning">
                                    <i class="fas fa-redo me-2"></i>
                                    Retry Payment
                                </a>
                                <a href="checkout.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Checkout
                                </a>
                            </div>
                        <?php elseif ($is_mpesa_like): ?>
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>M-Pesa Payment:</h6>
                                <p class="mb-0">
                                    <?php if ($simulation_mode): ?>
                                        Simulation mode is enabled. Enter your M-Pesa PIN below to complete the payment.
                                    <?php else: ?>
                                        Check your phone for the M-Pesa confirmation message. 
                                        If you didn't receive the prompt, please check your M-Pesa messages or try again.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if ($is_mpesa_like && $payment_status === 'pending' && $simulation_mode): ?>
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-key me-2"></i>Enter M-Pesa PIN</h6>
                                <form id="simPinForm" class="row g-2">
                                    <div class="col-8">
                                        <input type="password" class="form-control" id="simPinInput" maxlength="6" placeholder="Enter PIN" required>
                                    </div>
                                    <div class="col-4 d-grid">
                                        <button type="submit" class="btn btn-warning">Submit PIN</button>
                                    </div>
                                </form>
                                <?php if ($sim_locked_remaining > 0): ?>
                                    <small class="text-danger d-block mt-2">
                                        Too many attempts. Try again in <?php echo ceil($sim_locked_remaining / 60); ?> minute(s).
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted d-block mt-2">For simulation use PIN 1234 (default).</small>
                                <?php endif; ?>
                                <div id="simPinMessage" class="mt-2"></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-success">
                            <h6><i class="fas fa-truck me-2"></i>Delivery Information:</h6>
                            <p class="mb-0">
                                Your order will be delivered to: <?php echo htmlspecialchars($order['shipping_address'] ?? 'Address on file'); ?><br>
                                Expected delivery: 3-5 business days
                            </p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <?php if ($auth->isLoggedIn()): ?>
                                <a href="order_history.php" class="btn btn-primary">
                                    <i class="fas fa-list me-2"></i>
                                    View Order History
                                </a>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-home me-2"></i>
                                    Back to Dashboard
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-user me-2"></i>
                                    Login to Track Orders
                                </a>
                            <?php endif; ?>
                            <a href="catalog.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-bag me-2"></i>
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($is_mpesa_like && $payment_status === 'pending'): ?>
    <script>
    (function () {
        const titleEl = document.getElementById('payment-title');
        const subtitleEl = document.getElementById('payment-subtitle');
        const badgeEl = document.getElementById('status-badge');
        const txnRow = document.getElementById('transaction-row');
        const txnCodeEl = document.getElementById('transaction-code');
        const failureRow = document.getElementById('failure-row');
        const failureReasonEl = document.getElementById('failure-reason');
        const iconContainer = document.querySelector('.fa-clock').parentElement;

        let attempts = 0;
        const maxAttempts = 40; // ~2 minutes at 3s interval
        const intervalMs = 3000;

        async function poll() {
            attempts++;
            try {
                const res = await fetch('check_payment_status_ajax.php?order_id=<?php echo (int)$order_id; ?>', { credentials: 'same-origin' });
                const data = await res.json();

                if (data && data.success) {
                    if (data.paid) {
                        // Payment successful
                        iconContainer.innerHTML = '<i class="fas fa-check-circle fa-4x text-success"></i>';
                        titleEl.textContent = 'Payment Successful!';
                        subtitleEl.textContent = 'Your order has been successfully placed and paid for.';
                        badgeEl.innerHTML = '<span class="badge bg-success">Completed</span>';
                        
                        // Update payment status display
                        const paymentStatusEl = badgeEl.parentElement.parentElement.nextElementSibling.querySelector('strong');
                        if (paymentStatusEl) {
                            paymentStatusEl.innerHTML = '<span class="text-success">Paid</span>';
                        }
                        
                        if (data.transaction_code) {
                            txnCodeEl.textContent = data.transaction_code;
                            txnRow.style.display = '';
                        }
                        
                        // Reload page after 2 seconds to show final state
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                        return;
                    } else if (data.failed) {
                        // Payment failed
                        iconContainer.innerHTML = '<i class="fas fa-times-circle fa-4x text-danger"></i>';
                        titleEl.textContent = 'Payment Failed';
                        subtitleEl.textContent = 'Your payment was cancelled or failed. You can try again or use a different payment method.';
                        badgeEl.innerHTML = '<span class="badge bg-danger">Failed</span>';
                        
                        // Update payment status display
                        const paymentStatusEl = badgeEl.parentElement.parentElement.nextElementSibling.querySelector('strong');
                        if (paymentStatusEl) {
                            paymentStatusEl.innerHTML = '<span class="text-danger">Failed</span>';
                        }
                        
                        // Show failure reason if available
                        if (data.failure_reason) {
                            failureReasonEl.textContent = data.failure_reason;
                            failureRow.style.display = '';
                        }
                        
                        // Reload page after 2 seconds to show final failed state with retry options
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                        return;
                    }
                }

                // Continue polling if still pending
                if (attempts < maxAttempts) {
                    setTimeout(poll, intervalMs);
                } else {
                    subtitleEl.textContent = 'Still waiting for confirmation. If you already entered your PIN, please wait a bit longer or try again.';
                }
            } catch (e) {
                console.error('Payment status check error:', e);
                if (attempts < maxAttempts) {
                    setTimeout(poll, intervalMs);
                }
            }
        }

        // Only start polling if payment is still pending
        poll();
    })();
    </script>
    <?php endif; ?>
    <?php if ($is_mpesa_like && $payment_status === 'pending' && $simulation_mode): ?>
    <script>
    (function () {
        const form = document.getElementById('simPinForm');
        const input = document.getElementById('simPinInput');
        const messageEl = document.getElementById('simPinMessage');
        if (!form || !input || !messageEl) return;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            messageEl.textContent = 'Submitting PIN...';
            messageEl.className = 'mt-2 text-muted';
            try {
                const res = await fetch('simulate_pin_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        order_id: '<?php echo (int)$order_id; ?>',
                        pin: input.value
                    }),
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (data && data.success) {
                    messageEl.textContent = 'PIN accepted. Completing payment...';
                    messageEl.className = 'mt-2 text-success';
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    messageEl.textContent = data.message || 'Incorrect PIN. Try again.';
                    messageEl.className = 'mt-2 text-danger';
                }
            } catch (e) {
                messageEl.textContent = 'Error submitting PIN. Please try again.';
                messageEl.className = 'mt-2 text-danger';
            }
        });
    })();
    </script>
    <?php endif; ?>
</body>
</html>
