<?php
// Use centralized session management
require_once 'config/environment.php';

require_once 'includes/auth.php';
require_once 'includes/cart.php';
$auth = new Auth();

// Guest checkout allowed (login optional)

// Check if checkout data exists
if (!isset($_SESSION['checkout_data'])) {
    header('Location: checkout.php');
    exit;
}

$checkout_data = $_SESSION['checkout_data'];
$cart = new Cart();

// FORCE extract values from session to avoid any caching issues
$session_phone = $checkout_data['phone_number'];
$session_amount = $checkout_data['total_amount'];
$session_address = $checkout_data['shipping_address'];

// Format phone for display
$display_phone = $session_phone;
if (substr($display_phone, 0, 3) === '254') {
    $display_phone = '+' . $display_phone;
}

// Handle Airtel Money payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // For now, simulate successful payment
    // In production, integrate with Airtel Money API
    
    // Create order (simplified)
    require_once 'includes/payment.php';
    $payment = new Payment();
    
    $order_result = $payment->createOrder(
        $_SESSION['user_id'] ?? null, 
        $session_phone, 
        $session_address,
        'airtel',
        (float)($checkout_data['shipping_fee'] ?? 0)
    );
    
    if ($order_result['success']) {
        // If this is a guest checkout, remember the order so we can attach it after login
        if (empty($_SESSION['user_id'])) {
            if (!isset($_SESSION['guest_order_ids']) || !is_array($_SESSION['guest_order_ids'])) {
                $_SESSION['guest_order_ids'] = [];
            }
            $_SESSION['guest_order_ids'][] = (int)$order_result['order_id'];
            $_SESSION['guest_order_ids'] = array_values(array_unique($_SESSION['guest_order_ids']));
        }

        // Clear cart and store payment info
        $cart->clear();
        $_SESSION['order_id'] = $order_result['order_id'];
        $_SESSION['payment_method'] = 'airtel_money';
        $_SESSION['payment_status'] = 'pending';
        
        header('Location: payment_status.php');
        exit;
    } else {
        $error = $order_result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airtel Money Payment - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white text-center">
                        <h4 class="mb-0">
                            <i class="fas fa-mobile-alt text-danger me-2"></i>
                            Airtel Money Payment
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-4x text-danger"></i>
                            </div>
                            <h5>Complete Your Airtel Money Payment</h5>
                            <p class="text-muted">Pay using your Airtel Money account</p>
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
                                <span>Merchant:</span>
                                <strong>SmartSchool Uniforms</strong>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>How to Pay:</h6>
                            <ol class="mb-0">
                                <li>Click "Process Payment" below</li>
                                <li>You'll receive an Airtel Money USSD prompt</li>
                                <li>Follow the instructions to complete payment</li>
                                <li>Enter your Airtel Money PIN when prompted</li>
                            </ol>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Process Payment
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
