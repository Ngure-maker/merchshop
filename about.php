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
    <title>About Us - Merch Shop</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="shortcut icon" href="assets/images/favicon.ico">    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Only unique styles not in zetech-theme.css */
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
                    <i class="fas fa-info-circle me-2"></i>About SmartSchool Uniforms
                </h1>
                <p class="lead text-muted">Learn more about our mission and commitment to quality education</p>
            </div>

            <!-- About Content -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center mb-5">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <img src="https://via.placeholder.com/500x400/FF6B35/FFFFFF?text=SmartSchool+Uniforms" 
                                 class="img-fluid rounded shadow" alt="SmartSchool Uniforms">
                        </div>
                        <div class="col-md-6">
                            <h2 class="text-primary mb-3">Our Story</h2>
                            <p class="mb-3">Founded in 2020, SmartSchool Uniforms has been dedicated to providing high-quality, affordable school uniforms and educational supplies to students across Kenya.</p>
                            <p class="mb-3">We understand the importance of quality education and believe that every student deserves access to proper school attire without financial burden.</p>
                            <p>Our mission is to make school shopping easier, more affordable, and more convenient for parents and students throughout Kenya.</p>
                        </div>
                    </div>

                    <!-- Mission & Vision -->
                    <div class="row mb-5">
                        <div class="col-md-6 mb-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-bullseye fa-3x text-primary"></i>
                                    </div>
                                    <h3 class="text-center text-primary mb-3">Our Mission</h3>
                                    <p>To provide quality school uniforms and educational supplies at affordable prices, making education accessible to every student in Kenya.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-eye fa-3x text-primary"></i>
                                    </div>
                                    <h3 class="text-center text-primary mb-3">Our Vision</h3>
                                    <p>To be the leading provider of school uniforms and educational supplies in Kenya, known for quality, affordability, and exceptional customer service.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Values -->
                    <div class="mb-5">
                        <h3 class="text-primary mb-4 text-center">Our Core Values</h3>
                        <div class="row">
                            <div class="col-md-3 col-6 mb-4">
                                <div class="text-center">
                                    <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                                    <h5 class="fw-bold">Quality</h5>
                                    <p class="small text-muted">Premium materials and craftsmanship</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <div class="text-center">
                                    <i class="fas fa-tag fa-2x text-primary mb-2"></i>
                                    <h5 class="fw-bold">Affordability</h5>
                                    <p class="small text-muted">Competitive prices for all budgets</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <div class="text-center">
                                    <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                                    <h5 class="fw-bold">Reliability</h5>
                                    <p class="small text-muted">Fast and dependable delivery</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <div class="text-center">
                                    <i class="fas fa-smile fa-2x text-primary mb-2"></i>
                                    <h5 class="fw-bold">Customer Care</h5>
                                    <p class="small text-muted">Exceptional service and support</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="bg-primary text-white rounded p-4 mb-5">
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <h2 class="fw-bold">50,000+</h2>
                                <p>Happy Customers</p>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <h2 class="fw-bold">100+</h2>
                                <p>School Partners</p>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <h2 class="fw-bold">47</h2>
                                <p>Counties Served</p>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <h2 class="fw-bold">4.8★</h2>
                                <p>Customer Rating</p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Section -->
                    <div class="text-center mb-4">
                        <h3 class="text-primary mb-4">Meet Our Team</h3>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center p-4">
                                        <img src="https://via.placeholder.com/150x150/FF6B35/FFFFFF?text=CEO" 
                                             class="rounded-circle mb-3" alt="CEO">
                                        <h5 class="fw-bold">John Mwangi</h5>
                                        <p class="text-muted">Founder & CEO</p>
                                        <p class="small">Passionate about making education accessible to all Kenyan students.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center p-4">
                                        <img src="https://via.placeholder.com/150x150/FF6B35/FFFFFF?text=COO" 
                                             class="rounded-circle mb-3" alt="COO">
                                        <h5 class="fw-bold">Sarah Kamau</h5>
                                        <p class="text-muted">Chief Operations Officer</p>
                                        <p class="small">Ensuring smooth operations and excellent customer service.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center p-4">
                                        <img src="https://via.placeholder.com/150x150/FF6B35/FFFFFF?text=CFO" 
                                             class="rounded-circle mb-3" alt="CFO">
                                        <h5 class="fw-bold">David Ochieng</h5>
                                        <p class="text-muted">Chief Financial Officer</p>
                                        <p class="small">Managing finances to keep our prices affordable.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="text-center">
                <h3 class="mb-3">Ready to Shop With Us?</h3>
                <p class="text-muted mb-4">Join thousands of satisfied parents and students across Kenya</p>
                <a href="catalog.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-shopping-cart me-2"></i>Shop Now
                </a>
                <a href="contact.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-phone me-2"></i>Contact Us
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


