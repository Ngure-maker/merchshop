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
    <title>Returns & Exchanges - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .policy-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .policy-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(6, 25, 67, 0.2);
        }
        
        .return-step {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            position: relative;
        }
        
        .step-number {
            position: absolute;
            left: -20px;
            top: 1.5rem;
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 3px solid white;
        }
        
        .condition-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .condition-item:hover {
            border-color: var(--primary-color);
            background: var(--light-bg);
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--primary-color);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0.5rem;
            width: 20px;
            height: 20px;
            background: var(--primary-color);
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px rgba(6, 25, 67, 0.2);
        }
        
        .eligibility-check {
            background: linear-gradient(135deg, var(--light-bg), white);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            padding: 1.5rem;
        }
        
        .check-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .check-item i {
            color: var(--accent-green);
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .x-item i {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-primary mb-3">
                    <i class="fas fa-undo me-2"></i>Returns & Exchanges
                </h1>
                <p class="lead text-muted">Hassle-free returns and exchanges. Your satisfaction is our priority.</p>
            </div>

            <!-- Return Policy Summary -->
            <div class="policy-card">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-shield-alt me-2"></i>Our Return Policy
                </h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="eligibility-check mb-3">
                            <h6 class="fw-bold mb-3">✓ Eligible for Return</h6>
                            <div class="check-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Within 7 days of delivery</span>
                            </div>
                            <div class="check-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Unused and unworn items</span>
                            </div>
                            <div class="check-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Original packaging intact</span>
                            </div>
                            <div class="check-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Tags and labels attached</span>
                            </div>
                            <div class="check-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Proof of purchase available</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="eligibility-check mb-3">
                            <h6 class="fw-bold mb-3">✗ Not Eligible for Return</h6>
                            <div class="check-item x-item">
                                <i class="fas fa-times-circle"></i>
                                <span>After 7 days of delivery</span>
                            </div>
                            <div class="check-item x-item">
                                <i class="fas fa-times-circle"></i>
                                <span>Used or worn items</span>
                            </div>
                            <div class="check-item x-item">
                                <i class="fas fa-times-circle"></i>
                                <span>Damaged packaging</span>
                            </div>
                            <div class="check-item x-item">
                                <i class="fas fa-times-circle"></i>
                                <span>Customized items</span>
                            </div>
                            <div class="check-item x-item">
                                <i class="fas fa-times-circle"></i>
                                <span>Final sale items</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How to Return -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-list-ol me-2"></i>How to Return or Exchange
                </h3>

                <div class="return-step">
                    <div class="step-number">1</div>
                    <h6 class="fw-bold">Initiate Return Request</h6>
                    <p class="text-muted mb-2">Log into your account and go to order history. Select the order and items you want to return.</p>
                    <a href="order_history.php" class="btn btn-sm btn-outline-primary">Go to Order History</a>
                </div>

                <div class="return-step">
                    <div class="step-number">2</div>
                    <h6 class="fw-bold">Choose Return Option</h6>
                    <p class="text-muted mb-2">Select whether you want a refund or exchange. For exchanges, choose your preferred size/color.</p>
                </div>

                <div class="return-step">
                    <div class="step-number">3</div>
                    <h6 class="fw-bold">Pack Your Items</h6>
                    <p class="text-muted mb-2">Pack items in original packaging with all tags attached. Include your return slip or order number.</p>
                </div>

                <div class="return-step">
                    <div class="step-number">4</div>
                    <h6 class="fw-bold">Ship or Drop Off</h6>
                    <p class="text-muted mb-2">Use our prepaid return label or drop off at any SmartSchool location. We'll email you the return label.</p>
                </div>

                <div class="return-step">
                    <div class="step-number">5</div>
                    <h6 class="fw-bold">Receive Refund/Exchange</h6>
                    <p class="text-muted mb-2">Once we receive and inspect your items, we'll process your refund or ship your exchange within 3-5 business days.</p>
                </div>
            </div>

            <!-- Return Options -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-exchange-alt me-2"></i>Return Options
                </h3>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-money-bill-wave me-2"></i>Full Refund
                                </h6>
                                <p class="small text-muted mb-3">Get your money back to your original payment method</p>
                                <ul class="small mb-3">
                                    <li>Refund processed within 3-5 business days</li>
                                    <li>Original payment method used</li>
                                    <li>Shipping costs non-refundable</li>
                                </ul>
                                <span class="badge bg-success">Recommended for wrong items</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-sync-alt me-2"></i>Exchange
                                </h6>
                                <p class="small text-muted mb-3">Get the right size, color, or different item</p>
                                <ul class="small mb-3">
                                    <li>No additional shipping costs</li>
                                    <li>Same price or pay difference</li>
                                    <li>Faster processing time</li>
                                </ul>
                                <span class="badge bg-info">Recommended for size issues</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Conditions -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-info-circle me-2"></i>Special Conditions
                </h3>

                <div class="accordion" id="specialAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#special1">
                                <i class="fas fa-tshirt me-2"></i>Wrong Size or Fit
                            </button>
                        </h2>
                        <div id="special1" class="accordion-collapse collapse show" data-bs-parent="#specialAccordion">
                            <div class="accordion-body">
                                <p>If the uniform doesn't fit properly, we offer free size exchanges within 7 days. Check our <a href="size_guide.php">size guide</a> before ordering to ensure the best fit.</p>
                                <ul>
                                    <li>Free exchange for correct size</li>
                                    <li>Include measurements for better fit</li>
                                    <li>We'll help you find the right size</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#special2">
                                <i class="fas fa-exclamation-triangle me-2"></i>Defective Items
                            </button>
                        </h2>
                        <div id="special2" class="accordion-collapse collapse" data-bs-parent="#specialAccordion">
                            <div class="accordion-body">
                                <p>If you receive a defective item, we'll replace it immediately at no cost to you. Contact us within 48 hours of delivery.</p>
                                <ul>
                                    <li>Free replacement for defective items</li>
                                    <li>No return shipping costs</li>
                                    <li>Priority processing for defects</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#special3">
                                <i class="fas fa-truck me-2"></i>Wrong Item Delivered
                            </button>
                        </h2>
                        <div id="special3" class="accordion-collapse collapse" data-bs-parent="#specialAccordion">
                            <div class="accordion-body">
                                <p>If we send you the wrong item, we'll correct it immediately. Contact us right away and we'll arrange for the correct item to be sent.</p>
                                <ul>
                                    <li>Immediate correction of errors</li>
                                    <li>Keep the wrong item (no return needed)</li>
                                    <li>Express shipping for replacement</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Refund Timeline -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-clock me-2"></i>Refund Processing Timeline
                </h3>

                <div class="timeline">
                    <div class="timeline-item">
                        <h6 class="fw-bold">Item Received</h6>
                        <p class="text-muted small">We receive your returned item at our warehouse</p>
                        <small class="text-primary">Day 1</small>
                    </div>
                    <div class="timeline-item">
                        <h6 class="fw-bold">Quality Check</h6>
                        <p class="text-muted small">Our team inspects the item for compliance with return policy</p>
                        <small class="text-primary">Day 1-2</small>
                    </div>
                    <div class="timeline-item">
                        <h6 class="fw-bold">Refund Approved</h6>
                        <p class="text-muted small">Refund is processed and sent to your payment provider</p>
                        <small class="text-primary">Day 2-3</small>
                    </div>
                    <div class="timeline-item">
                        <h6 class="fw-bold">Refund Complete</h6>
                        <p class="text-muted small">Money appears in your account (timing varies by payment method)</p>
                        <small class="text-primary">Day 3-7</small>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="text-center">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">
                            <i class="fas fa-headset me-2"></i>Need Help with Returns?
                        </h4>
                        <p class="mb-4">Our customer support team is here to help make your return process smooth</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="tel:0712345678" class="btn btn-light">
                                <i class="fas fa-phone me-2"></i>Call Support
                            </a>
                            <a href="mailto:support@smartschool.com" class="btn btn-light">
                                <i class="fas fa-envelope me-2"></i>Email Support
                            </a>
                            <button class="btn btn-light" onclick="startLiveChat()">
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
function startLiveChat() {
    const phone = '0712345678';
    const waPhone = '254712345678';
    const message = encodeURIComponent('Hi, I need help with a return/refund.');
    const waUrl = `https://wa.me/${waPhone}?text=${message}`;
    const opened = window.open(waUrl, '_blank', 'noopener,noreferrer');
    if (!opened) {
        window.location.href = `tel:${phone}`;
    }
}
</script>
</body>
</html>


