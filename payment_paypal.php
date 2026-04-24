<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

// Handle PayPal payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone_number = preg_replace('/[^0-9]/', '', $_POST['phone_number'] ?? ($checkout_data['phone_number'] ?? ''));
    if (empty($phone_number)) {
        $error = 'Please provide a phone number';
    }

    if (!isset($error)) {
        if (strlen($phone_number) === 9 && is_numeric($phone_number)) {
            $phone_number = '254' . $phone_number;
        } elseif (strlen($phone_number) === 10 && substr($phone_number, 0, 1) === '0') {
            $phone_number = '254' . substr($phone_number, 1);
        }
    }

    // For now, simulate successful payment
    // In production, integrate with PayPal API
    
    if (!isset($error)) {
        // Create order
        require_once 'includes/payment.php';
        $payment = new Payment();
        
        $order_result = $payment->createOrder(
            $_SESSION['user_id'] ?? null, 
            $phone_number, 
            $checkout_data['shipping_address'] ?? ''
        );
    }
    
    if (!isset($error) && !empty($order_result['success'])) {
        // Clear cart and store payment info
        $cart->clear();
        $_SESSION['order_id'] = $order_result['order_id'];
        $_SESSION['payment_method'] = 'paypal';
        $_SESSION['payment_status'] = 'pending';
        
        header('Location: payment_status.php');
        exit;
    } else {
        if (!isset($error)) {
            $error = $order_result['message'] ?? 'Payment could not be processed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Payment - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <h4 class="mb-0">
                            <i class="fab fa-paypal text-primary me-2"></i>
                            PayPal Payment
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fab fa-paypal fa-4x text-primary"></i>
                            </div>
                            <h5>Complete Your PayPal Payment</h5>
                            <p class="text-muted">Pay securely with your PayPal account</p>
                        </div>
                        
                        <div class="bg-light p-3 rounded mb-4">
                            <h6>Payment Details:</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Amount:</span>
                                <strong>KSh <?php echo number_format($checkout_data['total_amount'], 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Email:</span>
                                <strong><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'N/A'); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Merchant:</span>
                                <strong>SmartSchool Uniforms</strong>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>How to Pay:</h6>
                            <ol class="mb-0">
                                <li>Click "Pay with PayPal" below</li>
                                <li>You'll be redirected to PayPal's secure site</li>
                                <li>Login to your PayPal account or pay as guest</li>
                                <li>Confirm payment and return to our store</li>
                            </ol>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">+254</span>
                                    <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                           placeholder="+254712345678 or 0712345678" inputmode="tel"
                                           value="<?php echo htmlspecialchars(isset($checkout_data['phone_number']) ? ltrim((string)$checkout_data['phone_number'], '254') : ''); ?>" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fab fa-paypal me-2"></i>
                                    Pay with PayPal
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

