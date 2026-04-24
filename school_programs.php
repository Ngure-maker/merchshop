<?php
require_once 'config/environment.php'; // Replaced session_start()
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
    <title>School Programs - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        :root {
            --primary-amber: #FF6B35;
            --primary-dark: #E85A2C;
            --secondary-teal: #00897B;
            --secondary-blue: #1976D2;
            --accent-purple: #7B1FA2;
            --accent-green: #388E3C;
            --neutral-gray: #546E7A;
            --light-bg: #FFF8E1;
        }
        
        .program-card {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-amber);
        }
        
        .program-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .program-header {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .benefit-item i {
            color: var(--primary-amber);
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .program-feature {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-amber);
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--secondary-teal), var(--secondary-blue));
            color: white;
            padding: 3rem;
            border-radius: 15px;
            text-align: center;
            margin-top: 3rem;
        }
        
        .school-logo {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: var(--primary-amber);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <main class="container my-5">
        <div class="program-header">
            <h1 class="mb-3"><i class="fas fa-graduation-cap me-3"></i>School Programs</h1>
            <p class="mb-0">Specialized programs designed for educational institutions</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="program-card">
                    <h3 class="mb-4"><i class="fas fa-school me-2"></i>Partnership Programs</h3>
                    <p class="mb-4">Join our exclusive school partnership program and enjoy numerous benefits designed to make uniform procurement seamless and cost-effective for your institution.</p>
                    
                    <div class="program-feature">
                        <h5><i class="fas fa-handshake me-2"></i>Bulk Order Discounts</h5>
                        <p>Special pricing for schools ordering uniforms in bulk. Discounts range from 15% to 30% based on order volume and frequency.</p>
                    </div>
                    
                    <div class="program-feature">
                        <h5><i class="fas fa-truck me-2"></i>Free Delivery Service</h5>
                        <p>Complimentary delivery to schools within Nairobi metropolitan area. Special rates available for schools outside Nairobi.</p>
                    </div>
                    
                    <div class="program-feature">
                        <h5><i class="fas fa-user-tie me-2"></i>Dedicated Account Manager</h5>
                        <p>Each partner school gets a dedicated account manager to handle all uniform needs and provide personalized service.</p>
                    </div>
                    
                    <div class="program-feature">
                        <h5><i class="fas fa-calendar-alt me-2"></i>Flexible Ordering Schedule</h5>
                        <p>Order uniforms throughout the year with flexible delivery schedules to match your school calendar.</p>
                    </div>
                </div>
                
                <div class="program-card">
                    <h3 class="mb-4"><i class="fas fa-users me-2"></i>Student Uniform Assistance</h3>
                    <p class="mb-4">We believe every student deserves access to quality school uniforms. Our assistance programs help families who need support with uniform costs.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="benefit-item">
                                <i class="fas fa-donate"></i>
                                <div>
                                    <h6>Scholarship Program</h6>
                                    <p class="small mb-0">Free uniforms for students from financially challenged families</p>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <i class="fas fa-exchange-alt"></i>
                                <div>
                                    <h6>Exchange Program</h6>
                                    <p class="small mb-0">Gently used uniforms collection and redistribution</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="benefit-item">
                                <i class="fas fa-percentage"></i>
                                <div>
                                    <h6>Subsidized Pricing</h6>
                                    <p class="small mb-0">Special discounts for eligible families</p>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <i class="fas fa-credit-card"></i>
                                <div>
                                    <h6>Payment Plans</h6>
                                    <p class="small mb-0">Flexible payment options for uniform purchases</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="program-card">
                    <h3 class="mb-4"><i class="fas fa-medal me-2"></i>Excellence Recognition Program</h3>
                    <p class="mb-4">Recognizing and rewarding schools that maintain high standards in uniform compliance and student presentation.</p>
                    
                    <div class="program-feature">
                        <h5><i class="fas fa-trophy me-2"></i>Best Dressed School Award</h5>
                        <p>Annual recognition for schools with outstanding uniform standards and student presentation.</p>
                    </div>
                    
                    <div class="program-feature">
                        <h5><i class="fas fa-star me-2"></i>Academic Excellence Bonus</h5>
                        <p>Additional discounts for schools that achieve academic excellence milestones.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="program-card">
                    <h4 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Program Benefits</h4>
                    
                    <div class="benefit-item">
                        <i class="fas fa-piggy-bank"></i>
                        <div>
                            <h6>Cost Savings</h6>
                            <p class="small mb-0">Save up to 30% on uniform costs</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h6>Time Efficiency</h6>
                            <p class="small mb-0">Streamlined ordering process</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <h6>Quality Assurance</h6>
                            <p class="small mb-0">Premium quality materials</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <i class="fas fa-headset"></i>
                        <div>
                            <h6>Priority Support</h6>
                            <p class="small mb-0">Dedicated customer service</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <i class="fas fa-sync"></i>
                        <div>
                            <h6>Easy Returns</h6>
                            <p class="small mb-0">Hassle-free exchange policy</p>
                        </div>
                    </div>
                </div>
                
                <div class="program-card">
                    <h4 class="mb-3"><i class="fas fa-chart-line me-2"></i>Success Stories</h4>
                    
                    <div class="mb-3">
                        <div class="school-logo">
                            <i class="fas fa-school"></i>
                        </div>
                        <h6>Nairobi Academy</h6>
                        <p class="small text-muted">"Partnering with SmartSchool has saved us 25% on uniform costs while improving quality."</p>
                        <small class="text-primary">- Principal, 500+ students</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="school-logo">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h6>St. Mary's Primary</h6>
                        <p class="small text-muted">"The bulk ordering system is seamless and the delivery service is exceptional."</p>
                        <small class="text-primary">- Head Teacher, 300+ students</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="school-logo">
                            <i class="fas fa-book"></i>
                        </div>
                        <h6>Excel Learning Center</h6>
                        <p class="small text-muted">"Our parents appreciate the quality and affordability of the uniforms."</p>
                        <small class="text-primary">- Director, 200+ students</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cta-section">
            <h3 class="mb-3">Ready to Join Our School Programs?</h3>
            <p class="mb-4">Contact our school programs team today to learn more about how we can benefit your institution.</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="contact.php?program=partnership" class="btn btn-light btn-lg w-100">
                                <i class="fas fa-handshake me-2"></i>Partnership Inquiry
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="bulk_orders.php" class="btn btn-outline-light btn-lg w-100">
                                <i class="fas fa-shopping-cart me-2"></i>Bulk Order
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="tel:+254700123456" class="btn btn-light btn-lg w-100">
                                <i class="fas fa-phone me-2"></i>Call Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
