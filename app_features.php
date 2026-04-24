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
    <title>App Features - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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
        
        .features-header {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .features-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .feature-showcase {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 5px solid var(--accent-purple);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .feature-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--accent-purple);
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .feature-title {
            flex-grow: 1;
        }
        
        .feature-title h4 {
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
        
        .feature-title p {
            color: var(--neutral-gray);
            margin: 0;
            font-size: 1rem;
        }
        
        .feature-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .detail-item {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        
        .detail-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .interactive-demo {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .demo-phone {
            width: 300px;
            height: 600px;
            background: #333;
            border-radius: 35px;
            padding: 15px;
            margin: 0 auto;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .demo-screen {
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            position: relative;
        }
        
        .demo-header {
            background: var(--secondary-blue);
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: bold;
        }
        
        .demo-content {
            padding: 1rem;
            height: calc(100% - 60px);
            overflow-y: auto;
        }
        
        .demo-item {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .demo-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .comparison-table {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .table-responsive {
            margin-top: 1.5rem;
        }
        
        .table th {
            background: var(--accent-purple);
            color: white;
            border: none;
            padding: 1rem;
            text-align: center;
        }
        
        .table td {
            padding: 1rem;
            text-align: center;
            vertical-align: middle;
        }
        
        .check-icon {
            color: var(--accent-green);
            font-size: 1.5rem;
        }
        
        .cross-icon {
            color: #dc3545;
            font-size: 1.5rem;
        }
        
        .premium-badge {
            background: var(--accent-purple);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .benefit-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .benefit-icon {
            width: 80px;
            height: 80px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--accent-green);
            margin: 0 auto 1.5rem;
        }
        
        .tech-specs {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .spec-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .spec-item:last-child {
            border-bottom: none;
        }
        
        .spec-label {
            font-weight: 600;
            color: var(--neutral-gray);
        }
        
        .spec-value {
            color: var(--accent-purple);
            font-weight: bold;
        }
        
        .user-stories {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .story-card {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .story-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .story-avatar {
            width: 50px;
            height: 50px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-weight: bold;
        }
        
        .story-info {
            flex-grow: 1;
        }
        
        .story-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .story-role {
            color: var(--neutral-gray);
            font-size: 0.9rem;
        }
        
        .coming-soon-features {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .future-feature {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .future-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="features-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-star me-3"></i>App Features</h1>
            <p class="lead mb-0">Discover the powerful features that make uniform shopping amazing</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="features-section">
                    <h3 class="mb-4">Core Features</h3>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="feature-title">
                                <h4>Complete Mobile Shopping</h4>
                                <p>Full catalog access with advanced search and filtering</p>
                            </div>
                        </div>
                        
                        <p>Shop our entire uniform collection directly from your phone. Browse by category, size, color, or price range with our intuitive mobile interface.</p>
                        
                        <div class="feature-details">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div>
                                    <strong>Smart Search</strong><br>
                                    <small>Find exactly what you need quickly</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-filter"></i>
                                </div>
                                <div>
                                    <strong>Advanced Filters</strong><br>
                                    <small>Size, color, price, school requirements</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div>
                                    <strong>Wishlist</strong><br>
                                    <small>Save favorites for later purchase</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div>
                                    <strong>Quick Checkout</strong><br>
                                    <small>Save addresses and payment methods</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-ruler-combined"></i>
                            </div>
                            <div class="feature-title">
                                <h4>Smart Size Calculator</h4>
                                <p>AI-powered sizing with AR technology</p>
                            </div>
                        </div>
                        
                        <p>Never worry about wrong sizes again. Our intelligent size calculator uses augmented reality and machine learning to recommend the perfect fit.</p>
                        
                        <div class="feature-details">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-camera"></i>
                                </div>
                                <div>
                                    <strong>AR Fitting</strong><br>
                                    <small>Virtual try-on with your camera</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-ruler"></i>
                                </div>
                                <div>
                                    <strong>Measurement Guide</strong><br>
                                    <small>Step-by-step measuring instructions</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div>
                                    <strong>Size History</strong><br>
                                    <small>Track your child's growth over time</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-child"></i>
                                </div>
                                <div>
                                    <strong>Multiple Profiles</strong><br>
                                    <small>Manage sizes for all your children</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="feature-title">
                                <h4>Smart Notifications</h4>
                                <p>Personalized alerts and updates</p>
                            </div>
                        </div>
                        
                        <p>Stay informed with intelligent push notifications tailored to your preferences and shopping habits.</p>
                        
                        <div class="feature-details">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div>
                                    <strong>Deal Alerts</strong><br>
                                    <small>Personalized offers and discounts</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <strong>Order Updates</strong><br>
                                    <small>Real-time shipping and delivery status</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-redo"></i>
                                </div>
                                <div>
                                    <strong>Stock Alerts</strong><br>
                                    <small>Notify when out-of-stock items return</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div>
                                    <strong>School Updates</strong><br>
                                    <small>Important announcements and deadlines</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div class="feature-title">
                                <h4>In-Store Integration</h4>
                                <p>Seamless online-to-offline experience</p>
                            </div>
                        </div>
                        
                        <p>Enhance your in-store shopping experience with our app's advanced features and seamless integration.</p>
                        
                        <div class="feature-details">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-barcode"></i>
                                </div>
                                <div>
                                    <strong>Barcode Scanner</strong><br>
                                    <small>Scan products for instant details</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div>
                                    <strong>Mobile Payment</strong><br>
                                    <small>Quick checkout with saved cards</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <strong>Store Locator</strong><br>
                                    <small>Find nearest stores with directions</small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div>
                                    <strong>Appointment Booking</strong><br>
                                    <small>Schedule fitting sessions</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="comparison-table">
                    <h3 class="mb-4">App vs Website Comparison</h3>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <th>Mobile App</th>
                                    <th>Website</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Push Notifications</strong></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                    <td><i class="fas fa-times cross-icon"></i></td>
                                </tr>
                                <tr>
                                    <td><strong>AR Size Calculator</strong></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                    <td><i class="fas fa-times cross-icon"></i></td>
                                </tr>
                                <tr>
                                    <td><strong>Offline Access</strong></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                    <td><i class="fas fa-times cross-icon"></i></td>
                                </tr>
                                <tr>
                                    <td><strong>Barcode Scanner</strong></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                    <td><i class="fas fa-times cross-icon"></i></td>
                                </tr>
                                <tr>
                                    <td><strong>Biometric Login</strong></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                    <td><i class="fas fa-times cross-icon"></i></td>
                                </tr>
                                <tr>
                                    <td><strong>Full Catalog</strong></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                </tr>
                                <tr>
                                    <td><strong>Secure Checkout</strong></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                </tr>
                                <tr>
                                    <td><strong>Order Tracking</strong></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                    <td><i class="fas fa-check check-icon"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="coming-soon-features">
                    <h3 class="mb-4"><i class="fas fa-rocket me-2"></i>Coming Soon Features</h3>
                    
                    <div class="future-feature">
                        <div class="future-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h6>Parent Community</h6>
                            <p class="mb-0">Connect with other parents, share tips, and get advice</p>
                        </div>
                    </div>
                    
                    <div class="future-feature">
                        <div class="future-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <div>
                            <h6>Gamified Shopping</h6>
                            <p class="mb-0">Earn points, unlock achievements, and get rewards</p>
                        </div>
                    </div>
                    
                    <div class="future-feature">
                        <div class="future-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div>
                            <h6>AI Shopping Assistant</h6>
                            <p class="mb-0">Get personalized recommendations and instant help</p>
                        </div>
                    </div>
                    
                    <div class="future-feature">
                        <div class="future-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h6>Student Portal</h6>
                            <p class="mb-0>Dedicated features for students to manage their uniforms</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="interactive-demo">
                    <h4 class="text-center mb-4">Interactive Demo</h4>
                    
                    <div class="demo-phone">
                        <div class="demo-screen">
                            <div class="demo-header">
                                <i class="fas fa-graduation-cap me-2"></i>SmartSchool
                            </div>
                            <div class="demo-content">
                                <div class="demo-item" onclick="showFeature('shopping')">
                                    <i class="fas fa-shopping-bag me-2"></i>
                                    <strong>Shop Uniforms</strong>
                                    <div class="text-muted small">Browse catalog</div>
                                </div>
                                <div class="demo-item" onclick="showFeature('calculator')">
                                    <i class="fas fa-ruler-combined me-2"></i>
                                    <strong>Size Calculator</strong>
                                    <div class="text-muted small">Find perfect fit</div>
                                </div>
                                <div class="demo-item" onclick="showFeature('wishlist')">
                                    <i class="fas fa-heart me-2"></i>
                                    <strong>My Wishlist</strong>
                                    <div class="text-muted small">5 items saved</div>
                                </div>
                                <div class="demo-item" onclick="showFeature('orders')">
                                    <i class="fas fa-box me-2"></i>
                                    <strong>Track Orders</strong>
                                    <div class="text-muted small">2 active orders</div>
                                </div>
                                <div class="demo-item" onclick="showFeature('profile')">
                                    <i class="fas fa-user me-2"></i>
                                    <strong>My Profile</strong>
                                    <div class="text-muted small">Account settings</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small>Tap on items to explore features</small>
                    </div>
                </div>
                
                <div class="features-section">
                    <h4 class="mb-3">User Benefits</h4>
                    
                    <div class="benefits-grid">
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h6>Save Time</h6>
                            <p class="small mb-0">Shop anytime, anywhere with quick checkout</p>
                        </div>
                        
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h6>Save Money</h6>
                            <p class="small mb-0">Exclusive app-only deals and discounts</p>
                        </div>
                        
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <h6>Smart Shopping</h6>
                            <p class="small mb-0">AI recommendations and size matching</p>
                        </div>
                        
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h6>Secure & Safe</h6>
                            <p class="small mb-0">Bank-level security for all transactions</p>
                        </div>
                    </div>
                </div>
                
                <div class="tech-specs">
                    <h4 class="mb-3">Technical Specifications</h4>
                    
                    <div class="spec-item">
                        <span class="spec-label">Platform Support</span>
                        <span class="spec-value">iOS 12+ & Android 8+</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">App Size</span>
                        <span class="spec-value">~100MB</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Offline Mode</span>
                        <span class="spec-value">Limited browsing</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Security</span>
                        <span class="spec-value">256-bit encryption</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Languages</span>
                        <span class="spec-value">English & Swahili</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Updates</span>
                        <span class="spec-value">Automatic & Free</span>
                    </div>
                </div>
                
                <div class="user-stories">
                    <h4 class="mb-3">What Users Love</h4>
                    
                    <div class="story-card">
                        <div class="story-header">
                            <div class="story-avatar">SM</div>
                            <div class="story-info">
                                <div class="story-name">Sarah Mwangi</div>
                                <div class="story-role">Parent of 3</div>
                            </div>
                        </div>
                        <p class="small mb-0">"The size calculator saved me from ordering the wrong sizes. So accurate!"</p>
                    </div>
                    
                    <div class="story-card">
                        <div class="story-header">
                            <div class="story-avatar">JO</div>
                            <div class="story-info">
                                <div class="story-name">John Odhiambo</div>
                                <div class="story-role">School Administrator</div>
                            </div>
                        </div>
                        <p class="small mb-0">"Our parents love the convenience. Bulk ordering has never been easier!"</p>
                    </div>
                    
                    <div class="story-card">
                        <div class="story-header">
                            <div class="story-avatar">AK</div>
                            <div class="story-info">
                                <div class="story-name">Alice Kimani</div>
                                <div class="story-role">Student</div>
                            </div>
                        </div>
                        <p class="small mb-0">"I can track my uniform orders and get notified when they arrive!"</p>
                    </div>
                </div>
                
                <div class="features-section">
                    <h4 class="mb-3">Premium Features</h4>
                    
                    <div class="list-group">
                        <div class="list-group-item">
                            <i class="fas fa-crown text-warning me-2"></i>
                            <strong>Early Access</strong>
                            <small class="text-muted d-block">Get new features before anyone else</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-crown text-warning me-2"></i>
                            <strong>Priority Support</strong>
                            <small class="text-muted d-block">24/7 dedicated customer service</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-crown text-warning me-2"></i>
                            <strong>Exclusive Deals</strong>
                            <small class="text-muted d-block">Members-only discounts and offers</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-crown text-warning me-2"></i>
                            <strong>Advanced Analytics</strong>
                            <small class="text-muted d-block">Detailed shopping insights and reports</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showFeature(feature) {
            const features = {
                shopping: "Browse our complete uniform catalog with advanced filters and search options.",
                calculator: "Use our AI-powered size calculator with AR technology for perfect fitting.",
                wishlist: "Save your favorite items and get notified when they're on sale.",
                orders: "Track all your orders in real-time with detailed shipping updates.",
                profile: "Manage your account, preferences, and payment methods securely."
            };
            
            alert(features[feature] || "Feature coming soon!");
        }
    </script>
</body>
</html>
