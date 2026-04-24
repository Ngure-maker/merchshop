<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/cart.php';

$auth = new Auth();
$db = new DBHelper();
$cart = new Cart();
$item_count = $cart->getItemCount();

// Get categories for dropdown menu
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-teal) 100%);
            color: white;
            padding: 4rem 0;
        }
        
        .card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .payment-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .payment-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 8px 25px rgba(6, 25, 67, 0.2);
            transform: translateY(-5px);
        }
        
        .payment-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }
        
        .payment-features {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }
        
        .payment-features li {
            padding: 0.5rem 0;
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .payment-features li i {
            color: var(--accent-green);
            margin-right: 0.5rem;
        }
        
        .security-badge {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .process-step {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .process-step:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(6, 25, 67, 0.2);
        }
        
        .step-number {
            position: absolute;
            top: -15px;
            left: 20px;
            width: 30px;
            height: 30px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 3px solid white;
        }
        
        .faq-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .faq-question {
            padding: 1rem 1.5rem;
            background: white;
            border: none;
            width: 100%;
            text-align: left;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background: var(--light-bg);
        }
        
        .faq-answer {
            padding: 0 1.5rem;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-answer.show {
            padding: 1rem 1.5rem;
            max-height: 500px;
        }
        
        .mobile-money-option {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .mpesa-option {
            background: linear-gradient(135deg, #1CB142, #0A5D38);
        }
        
        .airtel-option {
            background: linear-gradient(135deg, #ED1C24, #B71C1C);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-primary mb-3">
                    <i class="fas fa-credit-card me-2"></i>Payment Options
                </h1>
                <p class="lead text-muted">Secure, convenient payment methods to make your shopping experience smooth and hassle-free</p>
            </div>

            <!-- Security Badge -->
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center gap-3">
                    <span class="security-badge">
                        <i class="fas fa-lock me-2"></i>256-bit SSL Encryption
                    </span>
                    <span class="security-badge">
                        <i class="fas fa-shield-alt me-2"></i>PCI DSS Compliant
                    </span>
                    <span class="security-badge">
                        <i class="fas fa-check-circle me-2"></i>Secure Transactions
                    </span>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-wallet me-2"></i>Available Payment Methods
                </h3>

                <div class="row">
                    <!-- M-Pesa -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h5 class="fw-bold mb-3">M-Pesa</h5>
                            <span class="badge bg-success mb-3">Most Popular</span>
                            <ul class="payment-features">
                                <li><i class="fas fa-check"></i>Instant confirmation</li>
                                <li><i class="fas fa-check"></i>No extra charges</li>
                                <li><i class="fas fa-check"></i>Available 24/7</li>
                                <li><i class="fas fa-check"></i>Pay from anywhere</li>
                            </ul>
                            <button class="btn btn-primary w-100">Pay with M-Pesa</button>
                        </div>
                    </div>

                    <!-- Airtel Money -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Airtel Money</h5>
                            <span class="badge bg-info mb-3">Fast & Easy</span>
                            <ul class="payment-features">
                                <li><i class="fas fa-check"></i>Quick processing</li>
                                <li><i class="fas fa-check"></i>No registration needed</li>
                                <li><i class="fas fa-check"></i>Secure transactions</li>
                                <li><i class="fas fa-check"></i>Low transaction fees</li>
                            </ul>
                            <button class="btn btn-primary w-100">Pay with Airtel Money</button>
                        </div>
                    </div>

                    <!-- Visa/Mastercard -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Visa & Mastercard</h5>
                            <span class="badge bg-primary mb-3">International</span>
                            <ul class="payment-features">
                                <li><i class="fas fa-check"></i>Global acceptance</li>
                                <li><i class="fas fa-check"></i>Bank-level security</li>
                                <li><i class="fas fa-check"></i>Instant verification</li>
                                <li><i class="fas fa-check"></i>Fraud protection</li>
                            </ul>
                            <button class="btn btn-primary w-100">Pay with Card</button>
                        </div>
                    </div>

                    <!-- PayPal -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fab fa-paypal"></i>
                            </div>
                            <h5 class="fw-bold mb-3">PayPal</h5>
                            <span class="badge bg-warning mb-3">Trusted Worldwide</span>
                            <ul class="payment-features">
                                <li><i class="fas fa-check"></i>Buyer protection</li>
                                <li><i class="fas fa-check"></i>Easy refunds</li>
                                <li><i class="fas fa-check"></i>Multi-currency</li>
                                <li><i class="fas fa-check"></i>Express checkout</li>
                            </ul>
                            <button class="btn btn-primary w-100">Pay with PayPal</button>
                        </div>
                    </div>

                    <!-- Cash on Delivery -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Cash on Delivery</h5>
                            <span class="badge bg-secondary mb-3">Nairobi Only</span>
                            <ul class="payment-features">
                                <li><i class="fas fa-check"></i>Pay when you receive</li>
                                <li><i class="fas fa-check"></i>No advance payment</li>
                                <li><i class="fas fa-check"></i>Inspect before paying</li>
                                <li><i class="fas fa-check"></i>Flexible payment</li>
                            </ul>
                            <button class="btn btn-primary w-100">Select COD</button>
                        </div>
                    </div>

                    <!-- Bank Transfer -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-university"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Bank Transfer</h5>
                            <span class="badge bg-dark mb-3">For Bulk Orders</span>
                            <ul class="payment-features">
                                <li><i class="fas fa-check"></i>Official receipts</li>
                                <li><i class="fas fa-check"></i>Account tracking</li>
                                <li><i class="fas fa-check"></i>Corporate payments</li>
                                <li><i class="fas fa-check"></i>Payment records</li>
                            </ul>
                            <button class="btn btn-primary w-100">Bank Details</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Money Instructions -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-mobile-alt me-2"></i>Mobile Money Payment Instructions
                </h3>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mobile-money-option mpesa-option">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-mobile-alt me-2"></i>M-Pesa Payment
                            </h5>
                            <ol class="text-white mb-3">
                                <li>Select "Pay Bill" from M-Pesa menu</li>
                                <li>Enter Business Number: <strong>123456</strong></li>
                                <li>Enter Account Number: <strong>Your Order ID</strong></li>
                                <li>Enter the amount and confirm</li>
                                <li>Enter your M-Pesa PIN to complete</li>
                            </ol>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone me-2"></i>
                                <span>Till Number: 987654</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mobile-money-option airtel-option">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-mobile-alt me-2"></i>Airtel Money Payment
                            </h5>
                            <ol class="text-white mb-3">
                                <li>Go to Airtel Money menu</li>
                                <li>Select "Make Payments" > "Pay Bill"</li>
                                <li>Enter Business Number: <strong>123456</strong></li>
                                <li>Enter Reference: <strong>Your Order ID</strong></li>
                                <li>Enter amount and confirm with PIN</li>
                            </ol>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone me-2"></i>
                                <span>Till Number: 987654</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Process -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-cogs me-2"></i>How Payment Processing Works
                </h3>

                <div class="process-step">
                    <div class="step-number">1</div>
                    <h6 class="fw-bold">Select Payment Method</h6>
                    <p class="text-muted mb-2">Choose your preferred payment method during checkout</p>
                </div>

                <div class="process-step">
                    <div class="step-number">2</div>
                    <h6 class="fw-bold">Enter Payment Details</h6>
                    <p class="text-muted mb-2">Provide necessary payment information securely</p>
                </div>

                <div class="process-step">
                    <div class="step-number">3</div>
                    <h6 class="fw-bold">Confirm & Authorize</h6>
                    <p class="text-muted mb-2">Review your order and authorize the payment</p>
                </div>

                <div class="process-step">
                    <div class="step-number">4</div>
                    <h6 class="fw-bold">Instant Confirmation</h6>
                    <p class="text-muted mb-2">Receive immediate confirmation and order tracking</p>
                </div>
            </div>

            <!-- Payment FAQ -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-question-circle me-2"></i>Payment FAQ
                </h3>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <i class="fas fa-chevron-right me-2"></i>Is my payment information secure?
                    </button>
                    <div class="faq-answer">
                        <p>Yes! We use industry-standard 256-bit SSL encryption to protect your payment information. We are also PCI DSS compliant, which means we follow the highest security standards for payment processing.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <i class="fas fa-chevron-right me-2"></i>Can I pay in installments?
                    </button>
                    <div class="faq-answer">
                        <p>Currently, we don't offer installment plans for individual orders. However, for bulk orders (KES 50,000+), we can arrange flexible payment terms. Contact our sales team for more information.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <i class="fas fa-chevron-right me-2"></i>What happens if my payment fails?
                    </button>
                    <div class="faq-answer">
                        <p>If your payment fails, don't worry! Your order will remain in your cart, and you can try again with a different payment method. You'll receive an email notification about the failed payment attempt.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <i class="fas fa-chevron-right me-2"></i>Can I get a refund if I pay by mobile money?
                    </button>
                    <div class="faq-answer">
                        <p>Yes! Refunds for mobile money payments are processed back to the original payment number within 3-5 business days after we receive and approve your return.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <i class="fas fa-chevron-right me-2"></i>Are there any hidden charges?
                    </button>
                    <div class="faq-answer">
                        <p>No hidden charges! The price you see is the price you pay. Mobile money providers may charge their standard transaction fees, but we don't add any additional processing fees.</p>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="text-center">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">
                            <i class="fas fa-headset me-2"></i>Payment Support Available 24/7
                        </h4>
                        <p class="mb-4">Having trouble with payment? Our support team is here to help you complete your order</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="tel:0712345678" class="btn btn-light">
                                <i class="fas fa-phone me-2"></i>Call Support
                            </a>
                            <a href="mailto:billing@smartschool.com" class="btn btn-light">
                                <i class="fas fa-envelope me-2"></i>Email Billing
                            </a>
                            <button class="btn btn-light" onclick="startPaymentChat()">
                                <i class="fas fa-comments me-2"></i>Live Chat
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleFAQ(button) {
    const answer = button.nextElementSibling;
    const icon = button.querySelector('i');
    
    answer.classList.toggle('show');
    
    if (answer.classList.contains('show')) {
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
    } else {
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
    }
}

function startPaymentChat() {
    const phone = '0712345678';
    const waPhone = '254712345678';
    const message = encodeURIComponent('Hi, I need help with payment on my order.');
    const waUrl = `https://wa.me/${waPhone}?text=${message}`;
    const opened = window.open(waUrl, '_blank', 'noopener,noreferrer');
    if (!opened) {
        window.location.href = `tel:${phone}`;
    }
}
</script>
</body>
</html>


