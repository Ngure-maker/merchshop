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
$payment_method = $checkout_data['payment_method']; // visa or mastercard

// Handle card payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone_number = preg_replace('/[^0-9]/', '', $_POST['phone_number'] ?? ($checkout_data['phone_number'] ?? ''));
    $card_number = $_POST['card_number'] ?? '';
    $card_name = $_POST['card_name'] ?? '';
    $expiry = $_POST['expiry'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    
    // Basic validation
    if (empty($phone_number)) {
        $error = 'Please provide a phone number';
    } elseif (empty($card_number) || empty($card_name) || empty($expiry) || empty($cvv)) {
        $error = 'Please fill in all card details';
    } else {
        if (strlen($phone_number) === 9 && is_numeric($phone_number)) {
            $phone_number = '254' . $phone_number;
        } elseif (strlen($phone_number) === 10 && substr($phone_number, 0, 1) === '0') {
            $phone_number = '254' . substr($phone_number, 1);
        }

        // Create order
        require_once 'includes/payment.php';
        $payment = new Payment();
        
        $order_result = $payment->createOrder(
            $_SESSION['user_id'] ?? null, 
            $phone_number, 
            $checkout_data['shipping_address'],
            $payment_method,
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
            $_SESSION['payment_method'] = $payment_method;
            $_SESSION['payment_status'] = 'pending';
            
            header('Location: payment_status.php');
            exit;
        } else {
            $error = $order_result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Payment - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                            <?php if ($payment_method === 'visa'): ?>
                                <i class="fab fa-cc-visa text-primary me-2"></i>
                                Visa Card Payment
                            <?php else: ?>
                                <i class="fab fa-cc-mastercard text-danger me-2"></i>
                                Mastercard Payment
                            <?php endif; ?>
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <?php if ($payment_method === 'visa'): ?>
                                    <i class="fab fa-cc-visa fa-4x text-primary"></i>
                                <?php else: ?>
                                    <i class="fab fa-cc-mastercard fa-4x text-danger"></i>
                                <?php endif; ?>
                            </div>
                            <h5>Enter Card Details</h5>
                            <p class="text-muted">Secure payment processing</p>
                        </div>
                        
                        <div class="bg-light p-3 rounded mb-4">
                            <h6>Payment Details:</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Amount:</span>
                                <strong>KSh <?php echo number_format($checkout_data['total_amount'], 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Card Type:</span>
                                <strong><?php echo ucfirst($payment_method); ?></strong>
                            </div>
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
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" 
                                       placeholder="1234 5678 9012 3456" maxlength="19" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Cardholder Name</label>
                                <input type="text" class="form-control" id="card_name" name="card_name" 
                                       placeholder="John Doe" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry" name="expiry" 
                                           placeholder="MM/YY" maxlength="5" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" 
                                           placeholder="123" maxlength="4" required>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6><i class="fas fa-lock me-2"></i>Secure Payment:</h6>
                                <p class="mb-0">Your card details are encrypted and secure. We never store your card information.</p>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-lock me-2"></i>
                                    Pay Now
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
    <script>
        // Format card number
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
        
        // Format expiry date
        document.getElementById('expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
        
        // Only numbers for CVV
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>

