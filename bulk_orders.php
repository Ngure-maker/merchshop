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
    <title>Gift Cards - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .gift-card {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .gift-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .gift-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 40px rgba(6, 25, 67, 0.3);
        }
        
        .gift-card.blue {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
        }
        
        .gift-card.green {
            background: linear-gradient(135deg, var(--secondary-teal), var(--accent-green));
        }
        
        .gift-card-amount {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .gift-card-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(6, 25, 67, 0.2);
            transform: translateY(-2px);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .balance-check {
            background: linear-gradient(135deg, #f8f9fa, white);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            padding: 2rem;
        }
        
        .testimonial-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .testimonial-card .quote {
            font-style: italic;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .testimonial-card .author {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .how-it-works {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .how-it-works:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(6, 25, 67, 0.2);
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
                    <i class="fas fa-gift me-2"></i>Gift Cards
                </h1>
                <p class="lead text-muted">The perfect gift for students and parents. Give the gift of quality school uniforms and supplies.</p>
            </div>

            <!-- Featured Gift Cards -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-star me-2"></i>Popular Gift Cards
                </h3>

                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="gift-card">
                            <div class="gift-card-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h6 class="fw-bold">Starter Card</h6>
                            <div class="gift-card-amount">KES 1,000</div>
                            <p class="small mb-3">Perfect for basic supplies</p>
                            <button class="btn btn-light btn-sm">Buy Now</button>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="gift-card blue">
                            <div class="gift-card-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h6 class="fw-bold">Student Card</h6>
                            <div class="gift-card-amount">KES 2,500</div>
                            <p class="small mb-3">Complete uniform set</p>
                            <button class="btn btn-light btn-sm">Buy Now</button>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="gift-card green">
                            <div class="gift-card-icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <h6 class="fw-bold">Premium Card</h6>
                            <div class="gift-card-amount">KES 5,000</div>
                            <p class="small mb-3">Full school year supplies</p>
                            <button class="btn btn-light btn-sm">Buy Now</button>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="gift-card">
                            <div class="gift-card-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h6 class="fw-bold">Elite Card</h6>
                            <div class="gift-card-amount">KES 10,000</div>
                            <p class="small mb-3">Ultimate school package</p>
                            <button class="btn btn-light btn-sm">Buy Now</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Amount -->
            <div class="mb-5">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="fw-bold mb-3">
                                    <i class="fas fa-palette me-2"></i>Create Custom Gift Card
                                </h4>
                                <p class="mb-3">Choose any amount from KES 500 to KES 50,000</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" placeholder="Enter amount" min="500" max="50000">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="Recipient name (optional)">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="email" class="form-control" placeholder="Recipient email (optional)">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <button class="btn btn-light btn-lg">
                                    <i class="fas fa-plus-circle me-2"></i>Create Gift Card
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-check-circle me-2"></i>Why Choose SmartSchool Gift Cards?
                </h3>

                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-infinity"></i>
                            </div>
                            <h6 class="fw-bold">No Expiry</h6>
                            <p class="small text-muted">Gift cards never expire</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <h6 class="fw-bold">Free Delivery</h6>
                            <p class="small text-muted">Instant email delivery</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <h6 class="fw-bold">Flexible</h6>
                            <p class="small text-muted">Use for any product</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h6 class="fw-bold">Personalized</h6>
                            <p class="small text-muted">Add custom message</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-info-circle me-2"></i>How Gift Cards Work
                </h3>

                <div class="how-it-works">
                    <div class="d-flex align-items-center">
                        <div class="step-number">1</div>
                        <div>
                            <h6 class="fw-bold">Purchase Gift Card</h6>
                            <p class="text-muted mb-0">Choose amount and recipient details</p>
                        </div>
                    </div>
                </div>

                <div class="how-it-works">
                    <div class="d-flex align-items-center">
                        <div class="step-number">2</div>
                        <div>
                            <h6 class="fw-bold">Instant Delivery</h6>
                            <p class="text-muted mb-0">Gift card sent via email immediately</p>
                        </div>
                    </div>
                </div>

                <div class="how-it-works">
                    <div class="d-flex align-items-center">
                        <div class="step-number">3</div>
                        <div>
                            <h6 class="fw-bold">Redeem Online</h6>
                            <p class="text-muted mb-0">Recipient uses code during checkout</p>
                        </div>
                    </div>
                </div>

                <div class="how-it-works">
                    <div class="d-flex align-items-center">
                        <div class="step-number">4</div>
                        <div>
                            <h6 class="fw-bold">Shop & Save</h6>
                            <p class="text-muted mb-0">Apply to any purchase on our store</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balance Check -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-search me-2"></i>Check Gift Card Balance
                </h3>

                <div class="balance-check">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" placeholder="Enter gift card code">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Check Balance
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonials -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-quote-left me-2"></i>What Our Customers Say
                </h3>

                <div class="row">
                    <div class="col-md-6">
                        <div class="testimonial-card">
                            <div class="quote">
                                "The gift card was perfect for my nephew's birthday. His parents could get exactly what he needed for school."
                            </div>
                            <div class="author">
                                <i class="fas fa-user me-2"></i>Sarah M., Nairobi
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="testimonial-card">
                            <div class="quote">
                                "I received a SmartSchool gift card and it made my back-to-school shopping so much easier!"
                            </div>
                            <div class="author">
                                <i class="fas fa-user me-2"></i>James K., Mombasa
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Corporate Gift Cards -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-building me-2"></i>Corporate & Bulk Gift Cards
                </h3>

                <div class="card bg-light">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="fw-bold mb-3">Perfect for Schools & Organizations</h5>
                                <ul class="mb-3">
                                    <li>Special pricing for bulk orders (10+ cards)</li>
                                    <li>Custom branding available</li>
                                    <li>Dedicated account manager</li>
                                    <li>Flexible payment terms</li>
                                </ul>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-primary">School Programs</span>
                                    <span class="badge bg-success">Employee Benefits</span>
                                    <span class="badge bg-info">Community Projects</span>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <a href="bulk_orders.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-phone me-2"></i>Contact Sales
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-file-contract me-2"></i>Gift Card Terms & Conditions
                </h3>

                <div class="card">
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Gift cards have no expiry date</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Can be used for any product on SmartSchool Uniforms</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Non-refundable and cannot be exchanged for cash</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Lost or stolen cards cannot be replaced</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Balance can be checked online anytime</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Can be combined with other promotions</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="text-center">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">
                            <i class="fas fa-headset me-2"></i>Need Help with Gift Cards?
                        </h4>
                        <p class="mb-4">Our gift card specialists are here to assist you</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="tel:0712345678" class="btn btn-light">
                                <i class="fas fa-phone me-2"></i>Call Support
                            </a>
                            <a href="mailto:giftcards@smartschool.com" class="btn btn-light">
                                <i class="fas fa-envelope me-2"></i>Email Us
                            </a>
                            <button class="btn btn-light" onclick="startGiftChat()">
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
function startGiftChat() {
    const phone = '0712345678';
    const waPhone = '254712345678';
    const message = encodeURIComponent('Hi, I need help with bulk orders.');
    const waUrl = `https://wa.me/${waPhone}?text=${message}`;
    const opened = window.open(waUrl, '_blank', 'noopener,noreferrer');
    if (!opened) {
        window.location.href = `tel:${phone}`;
    }
}
</script>
</body>
</html>


