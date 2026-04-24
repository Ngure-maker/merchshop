<?php
// CRITICAL: Handle redirects BEFORE any includes or output
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1); 

// Load environment/session configuration first (starts session safely)
require_once 'config/environment.php';

error_log("=== CHECKOUT.PHP LOADED ===");
error_log("Session ID: " . session_id());
error_log("User ID: " . ($_SESSION['user_id'] ?? 'not set'));
error_log("Session cart exists: " . (isset($_SESSION['cart']) ? 'YES' : 'NO'));

// Handle form submission IMMEDIATELY - before any includes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $raw_phone = (string)($_POST['phone_number'] ?? '');
    $phone_number = preg_replace('/[^0-9]/', '', $raw_phone);
    $customer_name = trim((string)($_POST['customer_name'] ?? ''));
    $shipping_address = '';
    
    // Need to load cart to get total
    require_once 'config/environment.php';
    require_once 'includes/cart.php';
    $cart = new Cart();
    $cart_total = $cart->getTotal();
    $total_with_shipping = $cart_total;
    
    // Validate required fields
    if (empty($payment_method)) {
        $_SESSION['error'] = 'Please select a payment method';
    } elseif (!isset($_POST['terms']) || $_POST['terms'] !== '1') {
        $_SESSION['error'] = 'You must accept the terms and conditions';
    } elseif (in_array($payment_method, ['mpesa', 'airtel_money'], true)) {
        if ($phone_number === '') {
            $_SESSION['error'] = 'Please provide a phone number';
        } else {
            // Normalize phone number to 2547XXXXXXXX format
            if (strlen($phone_number) === 12 && substr($phone_number, 0, 3) === '254') {
                // ok
            } elseif (strlen($phone_number) === 10 && substr($phone_number, 0, 1) === '0') {
                $phone_number = '254' . substr($phone_number, 1);
            } elseif (strlen($phone_number) === 9) {
                $phone_number = '254' . $phone_number;
            }

            if (!(strlen($phone_number) === 12 && substr($phone_number, 0, 3) === '254')) {
                $_SESSION['error'] = 'Invalid phone number format. Use +2547XXXXXXXX, 0712XXXXXX or 2547XXXXXXXX.';
            }
        }
    } elseif ($payment_method === 'cash') {
        if ($customer_name === '') {
            $_SESSION['error'] = 'Please provide your full name for cash payment';
        } elseif ($phone_number === '') {
            $_SESSION['error'] = 'Please provide a phone number for cash payment';
        }
    }

    error_log("=== CHECKOUT.PHP - POST SUBMIT ===");
    error_log("Payment method: " . $payment_method);
    error_log("Raw phone: " . $raw_phone);
    error_log("Normalized phone: " . $phone_number);
    error_log("Customer name: " . $customer_name);
    error_log("Terms: " . (string)($_POST['terms'] ?? ''));

    // If validation failed, redirect back to checkout (PRG)
    if (!empty($_SESSION['error'])) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        session_write_close();
        header('Location: checkout.php', true, 302);
        exit;
    }

    {
        
        error_log("=== CHECKOUT.PHP - Phone Number Formatting ===");
        error_log("Original phone input: " . ($_POST['phone_number'] ?? 'not set'));
        error_log("After cleaning: " . preg_replace('/[^0-9]/', '', $_POST['phone_number'] ?? ''));
        error_log("After formatting: " . $phone_number);
        error_log("Phone length: " . strlen($phone_number));
        error_log("Cart total: " . $cart_total);
        error_log("Total with shipping: " . $total_with_shipping);
        
        // Store checkout data in session with timestamp
        $_SESSION['checkout_data'] = [
            'payment_method' => $payment_method,
            'phone_number' => $phone_number,
            'customer_name' => $customer_name,
            'shipping_address' => $shipping_address,
            'shipping_fee' => 0,
            'total_amount' => $total_with_shipping,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s')
        ];
        
        error_log("=== CHECKOUT DATA SAVED TO SESSION ===");
        error_log("Timestamp: " . date('Y-m-d H:i:s'));
        error_log("Phone: $phone_number");
        error_log("Amount: $total_with_shipping");
        error_log("Full session data: " . json_encode($_SESSION['checkout_data']));

        // Ensure session data is saved before redirecting to payment page
        session_write_close();
        
        // Clean output buffer and redirect
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // CRITICAL: Redirect IMMEDIATELY
        switch ($payment_method) {
            case 'mpesa':
                header('Location: payment_mpesa.php', true, 302);
                exit();
            case 'airtel_money':
                header('Location: payment_airtel.php', true, 302);
                exit();
            case 'paypal':
                header('Location: payment_paypal.php', true, 302);
                exit();
            case 'visa':
            case 'mastercard':
                header('Location: payment_card.php', true, 302);
                exit();
            case 'cash':
                require_once 'includes/payment.php';
                $payment = new Payment();
                $user_id = $_SESSION['user_id'] ?? null;
                $create = $payment->createOrder($user_id, $phone_number, $shipping_address, 'cash', 0, $customer_name);
                if (!empty($create['success'])) {
                    $_SESSION['order_id'] = $create['order_id'];
                    $_SESSION['payment_method'] = 'cash';
                    $_SESSION['payment_status'] = 'pending';
                    session_write_close();
                    header('Location: payment_status.php?order_id=' . (int)$create['order_id'], true, 302);
                    exit();
                }

                $_SESSION['error'] = $create['message'] ?? 'Failed to create cash order';
                session_write_close();
                header('Location: checkout.php', true, 302);
                exit();
            default:
                $_SESSION['error'] = 'Invalid payment method';
        }
        exit();
    }
}

// If we reach here, it's a GET request or there was an error
// Now safe to include other files
require_once 'config/environment.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

$auth = new Auth();

// Guest checkout allowed (login optional)
if ($auth->isLoggedIn()) {
    error_log("Checkout access - User is logged in: " . ($_SESSION['user_id'] ?? 'unknown')); 
} else {
    error_log("Checkout access - Guest checkout");
}

$cart = new Cart();

// CRITICAL: Validate cart FIRST before any output
$cart_items = $cart->getCart();

// Check if cart snapshot was passed (from cart page)
$cart_snapshot = $_GET['cart_snapshot'] ?? '';
if (!empty($cart_snapshot)) {
    // Try to decode cart snapshot
    try {
        $decoded_snapshot = json_decode(
            base64_decode(
                strtr(
                    $cart_snapshot . str_repeat('=', (4 - strlen($cart_snapshot) % 4) % 4),
                    '-_',
                    '+/'
                )
            ),
            true
        );

        if (!empty($decoded_snapshot) && is_array($decoded_snapshot)) {
            error_log("Using cart snapshot data - found " . count($decoded_snapshot) . " items");
            $cart_items = $decoded_snapshot;

            // Hydrate session cart so downstream payment/order creation reads the correct cart
            if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = $decoded_snapshot;
            }
        }
    } catch (Exception $e) {
        error_log("Failed to decode cart snapshot: " . $e->getMessage());
    }

    // Clean URL to avoid exposing snapshot in the address bar
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Location: checkout.php');
        exit;
    }
}

error_log("=== CHECKOUT ACCESS DEBUG ===");
error_log("Cart items count: " . count($cart_items));
error_log("Cart items: " . json_encode($cart_items));
error_log("Session cart: " . json_encode($_SESSION['cart'] ?? 'not set'));
error_log("User ID: " . ($_SESSION['user_id'] ?? 'not set'));
error_log("Cart snapshot provided: " . (!empty($cart_snapshot) ? 'YES' : 'NO'));

// For admins, be more lenient with cart validation
if (empty($cart_items)) {
    if ($auth->isAdmin()) {
        error_log("ADMIN USER: Cart appears empty but allowing checkout access for testing");
        // Create a dummy cart item for admin testing
        $cart_items = [
            [
                'product_id' => 1,
                'name' => 'Test Product (Admin)',
                'price' => 100.00,
                'quantity' => 1,
                'size' => 'M'
            ]
        ];
    } else {
        error_log("CHECKOUT BLOCKED: Cart is empty, redirecting to cart");
        $_SESSION['error'] = 'Your cart appears to be empty. Please try adding items again.';
        header('Location: cart.php');
        exit;
    }
}

error_log("✓ Checkout access granted - Cart has " . count($cart_items) . " items");

// Validate stock before checkout - TEMPORARILY DISABLED FOR TESTING
$stock_errors = []; // $cart->validateStock();
if (!empty($stock_errors)) {
    $_SESSION['error'] = implode(', ', $stock_errors);
    $_SESSION['debug_error'] = 'Stock validation failed: ' . $_SESSION['error'];
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

// Calculate total from the resolved cart_items (may come from snapshot)
$total = 0;
foreach ($cart_items as $item) {
    $price = (float)($item['price'] ?? 0);
    $qty = (int)($item['quantity'] ?? 0);
    $total += ($price * $qty);
}

// Clean output buffer for normal page rendering
if (ob_get_level()) {
    ob_end_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            min-height: 100%;
        }
        body {
            overflow-x: hidden;
        }
        .checkout-container {
            min-height: calc(100vh - 200px);
        }
        .checkout-summary-sticky {
            top: 20px;
            position: sticky;
            z-index: 10;
        }
        @media (max-width: 768px) {
            .checkout-summary-sticky {
                position: relative;
                top: auto;
                margin-top: 2rem;
            }
        }
        .card {
            margin-bottom: 1.5rem;
        }
        .form-check {
            margin-bottom: 0.75rem;
        }
        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1.125rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container checkout-container py-4">
        <div class="row">
            <div class="col">
                <h2 class="mb-4">Checkout</h2>
            </div>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Debug alerts removed to prevent header issues -->
        
        <form method="POST" action="checkout.php" id="checkoutForm">
            <div class="row">
                <div class="col-md-8">
                    <!-- Payment Methods -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0 text-center">Select Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="mpesa" id="mpesa" required>
                                <label class="form-check-label" for="mpesa">M-Pesa</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="airtel_money" id="airtel_money">
                                <label class="form-check-label" for="airtel_money">Airtel Money</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="paypal" id="paypal">
                                <label class="form-check-label" for="paypal">PayPal</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="visa" id="visa">
                                <label class="form-check-label" for="visa">Visa Card</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="mastercard" id="mastercard">
                                <label class="form-check-label" for="mastercard">Mastercard</label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="cash" id="cash">
                                <label class="form-check-label" for="cash">Cash on Delivery/Collection</label>
                            </div>

                            <div id="phoneSection" style="display: none;" class="mt-4">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">+254</span>
                                    <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                           placeholder="+254712345678 or 0712345678" inputmode="tel">
                                </div>
                                <div class="form-text">Required for M-Pesa/Airtel/Cash payments</div>
                            </div>

                            <div id="nameSection" style="display: none;" class="mt-3">
                                <label for="customer_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter your full name">
                                <div class="form-text">Required for Cash payment</div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none">terms and conditions</a>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow-sm checkout-summary-sticky">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <small><?php echo htmlspecialchars($item['name']); ?></small>
                                        <br>
                                        <small class="text-muted">Size: <?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?> × <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <small>KSh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></small>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>KSh <?php echo number_format($total, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong>KSh <?php echo number_format($total, 2); ?></strong>
                            </div>
                            
                            <div class="d-grid">
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger mb-3">
                                        <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                                    </div>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary btn-lg" id="proceedBtn" disabled>
                                    Proceed to Payment
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-lock me-1"></i> Secure payment processing
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        const proceedBtn = document.getElementById('proceedBtn');
        const phoneSection = document.getElementById('phoneSection');
        const phoneInput = document.getElementById('phone_number');
        const nameSection = document.getElementById('nameSection');
        const nameInput = document.getElementById('customer_name');
        const checkoutForm = document.getElementById('checkoutForm');
        const termsCheckbox = document.getElementById('terms');

        function updatePhoneVisibility() {
            const selected = document.querySelector('input[name="payment_method"]:checked');
            const method = selected ? selected.value : '';
            const needsPhone = (method === 'mpesa' || method === 'airtel_money' || method === 'cash');
            const needsName = (method === 'cash');

            if (needsPhone) {
                phoneSection.style.display = '';
                phoneInput.required = true;
            } else {
                phoneSection.style.display = 'none';
                phoneInput.required = false;
                phoneInput.value = '';
            }

            if (needsName) {
                nameSection.style.display = '';
                nameInput.required = true;
            } else {
                nameSection.style.display = 'none';
                nameInput.required = false;
                nameInput.value = '';
            }

            proceedBtn.disabled = !selected;
        }

        paymentRadios.forEach(radio => {
            radio.addEventListener('change', updatePhoneVisibility);
        });

        checkoutForm.addEventListener('submit', function(e) {
            const selected = document.querySelector('input[name="payment_method"]:checked');
            const method = selected ? selected.value : '';
            const needsPhone = (method === 'mpesa' || method === 'airtel_money' || method === 'cash');
            const needsName = (method === 'cash');

            if (!selected) {
                e.preventDefault();
                alert('Please select a payment method');
                return;
            }

            if (!termsCheckbox.checked) {
                e.preventDefault();
                alert('You must accept the terms and conditions');
                return;
            }

            if (needsPhone && !phoneInput.value.trim()) {
                e.preventDefault();
                alert('Please enter your phone number');
                phoneInput.focus();
                return;
            }

            if (needsName && !nameInput.value.trim()) {
                e.preventDefault();
                alert('Please enter your full name');
                nameInput.focus();
                return;
            }
        });

        updatePhoneVisibility();
    </script>
</body>
</html>

