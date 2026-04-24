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
    <title>Shipping Information - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .shipping-option {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .shipping-option:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(6, 25, 67, 0.2);
        }
        
        .shipping-option.recommended {
            border-color: var(--primary-color);
            background: var(--light-bg);
        }
        
        .delivery-time {
            display: inline-block;
            background: var(--secondary-green);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .price-tag {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .location-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .location-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
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
        
        .tracking-step {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .tracking-step.completed {
            background: var(--light-bg);
            border-left-color: var(--secondary-green);
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
                    <i class="fas fa-truck me-2"></i>Shipping Information
                </h1>
                <p class="lead text-muted">Fast, reliable delivery across Kenya. Get your school supplies when you need them.</p>
            </div>

            <!-- Delivery Options -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-shipping-fast me-2"></i>Delivery Options
                </h3>

                <div class="shipping-option recommended">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <h5 class="fw-bold mb-0 me-3">Standard Delivery</h5>
                                <span class="badge bg-warning">Most Popular</span>
                            </div>
                            <p class="text-muted mb-2">Reliable delivery to your doorstep</p>
                            <div class="delivery-time mb-2">
                                <i class="fas fa-clock me-1"></i>3-5 Business Days
                            </div>
                            <ul class="small text-muted mb-0">
                                <li>Available nationwide (47 counties)</li>
                                <li>Free for orders above KES 3,000</li>
                                <li>Real-time tracking available</li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="price-tag">KES 150-350</div>
                            <small class="text-muted">Based on location</small>
                        </div>
                    </div>
                </div>

                <div class="shipping-option">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <h5 class="fw-bold mb-0 me-3">Express Delivery</h5>
                                <span class="badge bg-danger">Fast</span>
                            </div>
                            <p class="text-muted mb-2">Get your order as quickly as possible</p>
                            <div class="delivery-time mb-2">
                                <i class="fas fa-clock me-1"></i>1-2 Business Days
                            </div>
                            <ul class="small text-muted mb-0">
                                <li>Major cities only (Nairobi, Mombasa, Kisumu, etc.)</li>
                                <li>Priority handling</li>
                                <li>SMS notifications</li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="price-tag">KES 500-800</div>
                            <small class="text-muted">Based on location</small>
                        </div>
                    </div>
                </div>

                <div class="shipping-option">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <h5 class="fw-bold mb-0 me-3">Cash on Delivery</h5>
                                <span class="badge bg-success">Secure</span>
                            </div>
                            <p class="text-muted mb-2">Pay when you receive your order</p>
                            <div class="delivery-time mb-2">
                                <i class="fas fa-clock me-1"></i>3-5 Business Days
                            </div>
                            <ul class="small text-muted mb-0">
                                <li>Nairobi metropolitan area only</li>
                                <li>No additional fees</li>
                                <li>Pay exact amount or mobile money</li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="price-tag">KES 200</div>
                            <small class="text-muted">Service fee</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coverage Areas -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-map-marked-alt me-2"></i>Delivery Coverage
                </h3>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="location-card">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="fas fa-city me-2"></i>Major Cities
                            </h6>
                            <ul class="small mb-0">
                                <li>Nairobi</li>
                                <li>Mombasa</li>
                                <li>Kisumu</li>
                                <li>Nakuru</li>
                                <li>Eldoret</li>
                                <li>Thika</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="location-card">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="fas fa-map me-2"></i>Counties Covered
                            </h6>
                            <p class="small mb-0">All 47 counties with varying delivery times</p>
                            <small class="text-muted">Remote areas may take 5-7 days</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="location-card">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="fas fa-school me-2"></i>School Delivery
                            </h6>
                            <p class="small mb-0">Direct delivery to schools available</p>
                            <small class="text-muted">Coordinate with school administration</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Tracking -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-search-location me-2"></i>Track Your Order
                </h3>

                <div class="card bg-light">
                    <div class="card-body p-4">
                        <form class="row g-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Enter your order number or tracking ID">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Track Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sample Tracking Timeline -->
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">Sample Order Journey</h6>
                    <div class="timeline">
                        <div class="timeline-item">
                            <h6 class="fw-bold">Order Placed</h6>
                            <p class="text-muted small">Your order has been received and is being processed</p>
                            <small class="text-primary">Nov 20, 2024 - 2:30 PM</small>
                        </div>
                        <div class="timeline-item">
                            <h6 class="fw-bold">Order Confirmed</h6>
                            <p class="text-muted small">Payment verified and order confirmed</p>
                            <small class="text-primary">Nov 20, 2024 - 3:15 PM</small>
                        </div>
                        <div class="timeline-item">
                            <h6 class="fw-bold">Processing</h6>
                            <p class="text-muted small">Your items are being packed and prepared for shipment</p>
                            <small class="text-primary">Nov 21, 2024 - 9:00 AM</small>
                        </div>
                        <div class="timeline-item">
                            <h6 class="fw-bold">Shipped</h6>
                            <p class="text-muted small">Your order has been handed over to our delivery partner</p>
                            <small class="text-primary">Nov 21, 2024 - 2:00 PM</small>
                        </div>
                        <div class="timeline-item">
                            <h6 class="fw-bold">Out for Delivery</h6>
                            <p class="text-muted small">Your order is on the way to your address</p>
                            <small class="text-primary">Nov 22, 2024 - 8:00 AM</small>
                        </div>
                        <div class="timeline-item">
                            <h6 class="fw-bold">Delivered</h6>
                            <p class="text-muted small">Order successfully delivered</p>
                            <small class="text-muted">Expected: Nov 22, 2024</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Policies -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-file-contract me-2"></i>Shipping Policies
                </h3>

                <div class="accordion" id="shippingAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#shipping1">
                                <i class="fas fa-clock me-2"></i>Delivery Times
                            </button>
                        </h2>
                        <div id="shipping1" class="accordion-collapse collapse show" data-bs-parent="#shippingAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li>Standard Delivery: 3-5 business days</li>
                                    <li>Express Delivery: 1-2 business days (major cities only)</li>
                                    <li>Rural Areas: 5-7 business days</li>
                                    <li>Orders placed before 2 PM are processed same day</li>
                                    <li>Weekend orders are processed on Monday</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#shipping2">
                                <i class="fas fa-money-bill me-2"></i>Shipping Costs
                            </button>
                        </h2>
                        <div id="shipping2" class="accordion-collapse collapse" data-bs-parent="#shippingAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li>Free shipping on orders above KES 3,000</li>
                                    <li>Standard delivery: KES 150-350 based on location</li>
                                    <li>Express delivery: KES 500-800 based on location</li>
                                    <li>Cash on delivery service: KES 200 (Nairobi only)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#shipping3">
                                <i class="fas fa-exchange-alt me-2"></i>Failed Deliveries
                            </button>
                        </h2>
                        <div id="shipping3" class="accordion-collapse collapse" data-bs-parent="#shippingAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li>We attempt delivery 2 times</li>
                                    <li>Second attempt is made the next business day</li>
                                    <li>After 2 failed attempts, order is returned to warehouse</li>
                                    <li>Customer can arrange re-delivery at additional cost</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="text-center">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">
                            <i class="fas fa-headset me-2"></i>Need Help with Shipping?
                        </h4>
                        <p class="mb-4">Our customer support team is here to help with any shipping questions</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="tel:0712345678" class="btn btn-light">
                                <i class="fas fa-phone me-2"></i>Call Us
                            </a>
                            <a href="mailto:support@smartschool.com" class="btn btn-light">
                                <i class="fas fa-envelope me-2"></i>Email Us
                            </a>
                            <a href="help_center.php" class="btn btn-light">
                                <i class="fas fa-question-circle me-2"></i>Help Center
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


