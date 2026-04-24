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
    <title>Mobile Applications - SmartSchool Uniforms</title>
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
        
        .mobile-header {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .mobile-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .app-showcase {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .app-mockup {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .phone-mockup {
            width: 200px;
            height: 400px;
            background: var(--secondary-blue);
            border-radius: 30px;
            padding: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            position: relative;
        }
        
        .phone-screen {
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .app-header {
            background: var(--secondary-blue);
            color: white;
            padding: 1rem;
            text-align: center;
            font-size: 0.8rem;
        }
        
        .app-content {
            flex-grow: 1;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .app-item {
            background: var(--light-bg);
            padding: 0.5rem;
            border-radius: 5px;
            font-size: 0.7rem;
        }
        
        .app-nav {
            background: #f8f9fa;
            padding: 0.5rem;
            display: flex;
            justify-content: space-around;
            font-size: 0.8rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .feature-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--secondary-blue);
            margin-right: 1rem;
        }
        
        .feature-title {
            flex-grow: 1;
        }
        
        .feature-title h5 {
            color: var(--secondary-blue);
            margin-bottom: 0.25rem;
        }
        
        .feature-title p {
            color: var(--neutral-gray);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .benefit-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .benefit-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--accent-purple);
            margin: 0 auto 1rem;
        }
        
        .compatibility-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .device-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .device-card {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .device-card:hover {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .device-icon {
            font-size: 2rem;
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
        }
        
        .coming-soon {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .countdown-item {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1rem;
            min-width: 80px;
        }
        
        .countdown-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .countdown-label {
            font-size: 0.8rem;
        }
        
        .notification-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
        }
        
        .app-store-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
        }
        
        .store-button {
            background: black;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .store-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .store-button.apple {
            background: #000;
        }
        
        .store-button.google {
            background: #4285F4;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="mobile-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-mobile-alt me-3"></i>Mobile Applications</h1>
            <p class="lead mb-0">Shop SmartSchool uniforms anytime, anywhere with our mobile apps</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="coming-soon">
                    <h3 class="mb-3"><i class="fas fa-rocket me-2"></i>Mobile App Coming Soon!</h3>
                    <p class="lead mb-4">Get ready for the ultimate uniform shopping experience on your mobile device</p>
                    
                    <div class="countdown">
                        <div class="countdown-item">
                            <div class="countdown-number">30</div>
                            <div class="countdown-label">Days</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-number">15</div>
                            <div class="countdown-label">Hours</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-number">45</div>
                            <div class="countdown-label">Minutes</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-number">22</div>
                            <div class="countdown-label">Seconds</div>
                        </div>
                    </div>
                    
                    <p class="mb-4">Be the first to know when our app launches! Get exclusive early bird discounts and special offers.</p>
                    
                    <div class="app-store-buttons">
                        <a href="#" class="store-button apple">
                            <i class="fab fa-apple"></i>
                            <div>
                                <small>Download on the</small>
                                <div>App Store</div>
                            </div>
                        </a>
                        <a href="#" class="store-button google">
                            <i class="fab fa-google-play"></i>
                            <div>
                                <small>Get it on</small>
                                <div>Google Play</div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="app-showcase">
                    <h3 class="mb-4">What to Expect from Our Mobile App</h3>
                    
                    <div class="app-mockup">
                        <div class="phone-mockup">
                            <div class="phone-screen">
                                <div class="app-header">
                                    <i class="fas fa-graduation-cap me-2"></i>SmartSchool
                                </div>
                                <div class="app-content">
                                    <div class="app-item">
                                        <i class="fas fa-tshirt me-2"></i>Shop Uniforms
                                    </div>
                                    <div class="app-item">
                                        <i class="fas fa-ruler me-2"></i>Size Calculator
                                    </div>
                                    <div class="app-item">
                                        <i class="fas fa-heart me-2"></i>Wishlist
                                    </div>
                                    <div class="app-item">
                                        <i class="fas fa-shopping-cart me-2"></i>Cart (3)
                                    </div>
                                    <div class="app-item">
                                        <i class="fas fa-user me-2"></i>My Account
                                    </div>
                                </div>
                                <div class="app-nav">
                                    <i class="fas fa-home"></i>
                                    <i class="fas fa-search"></i>
                                    <i class="fas fa-shopping-cart"></i>
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4>Complete Mobile Experience</h4>
                            <p>Our mobile app will bring the full SmartSchool experience to your fingertips. Browse, shop, and manage your uniform needs seamlessly.</p>
                            
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Easy navigation and intuitive interface</li>
                                <li><i class="fas fa-check text-success me-2"></i>Secure payments and saved preferences</li>
                                <li><i class="fas fa-check text-success me-2"></i>Real-time order tracking</li>
                                <li><i class="fas fa-check text-success me-2"></i>Push notifications for deals and updates</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="mobile-section">
                    <h3 class="mb-4">Key Features</h3>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="feature-title">
                                <h5>Mobile Shopping</h5>
                                <p>Shop uniforms on the go</p>
                            </div>
                        </div>
                        
                        <p>Browse our complete catalog, filter by size and color, and make purchases directly from your phone. Save items to your wishlist and access your order history anytime.</p>
                        
                        <ul class="small">
                            <li>Full product catalog with detailed descriptions</li>
                            <li>Advanced search and filtering options</li>
                            <li>Secure checkout with multiple payment methods</li>
                            <li>Save shipping addresses and payment methods</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-ruler-combined"></i>
                            </div>
                            <div class="feature-title">
                                <h5>Size Calculator</h5>
                                <p>Find the perfect fit</p>
                            </div>
                        </div>
                        
                        <p>Use our mobile-optimized size calculator to find the perfect uniform size. Input measurements or use augmented reality for virtual fitting.</p>
                        
                        <ul class="small">
                            <li>Interactive size calculator</li>
                            <li>Augmented reality fitting room</li>
                            <li>Size history and preferences</li>
                            <li>Multiple child profiles</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="feature-title">
                                <h5>Smart Notifications</h5>
                                <p>Never miss important updates</p>
                            </div>
                        </div>
                        
                        <p>Receive personalized notifications about new arrivals, special offers, order updates, and stock alerts for your favorite items.</p>
                        
                        <ul class="small">
                            <li>Order status updates</li>
                            <li>Back in stock notifications</li>
                            <li>Personalized deals and discounts</li>
                            <li>School-specific announcements</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div class="feature-title">
                                <h5>In-Store Features</h5>
                                <p>Enhanced retail experience</p>
                            </div>
                        </div>
                        
                        <p>Use our app in-store for barcode scanning, quick checkout, and accessing your loyalty points. Scan products to get more information and availability.</p>
                        
                        <ul class="small">
                            <li>Barcode scanner for product details</li>
                            <li>Quick in-store checkout</li>
                            <li>Store locator with directions</li>
                            <li>Appointment booking for fittings</li>
                        </ul>
                    </div>
                </div>
                
                <div class="compatibility-section">
                    <h3 class="mb-4">Device Compatibility</h3>
                    
                    <div class="device-grid">
                        <div class="device-card">
                            <div class="device-icon">
                                <i class="fab fa-apple"></i>
                            </div>
                            <h6>iOS</h6>
                            <small>iPhone & iPad</small>
                            <div class="text-success">iOS 12+</div>
                        </div>
                        
                        <div class="device-card">
                            <div class="device-icon">
                                <i class="fab fa-android"></i>
                            </div>
                            <h6>Android</h6>
                            <small>Phones & Tablets</small>
                            <div class="text-success">Android 8+</div>
                        </div>
                        
                        <div class="device-card">
                            <div class="device-icon">
                                <i class="fas fa-tablet-alt"></i>
                            </div>
                            <h6>Tablets</h6>
                            <small>Optimized Experience</small>
                            <div class="text-success">Fully Supported</div>
                        </div>
                        
                        <div class="device-card">
                            <div class="device-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h6>Responsive</h6>
                            <small>All Screen Sizes</small>
                            <div class="text-success">Adaptive UI</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>System Requirements</h6>
                        <ul class="small mb-0">
                            <li>iOS 12.0 or later / Android 8.0 (Oreo) or later</li>
                            <li>Minimum 2GB RAM recommended</li>
                            <li>Stable internet connection required</li>
                            <li>100MB storage space for installation</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="mobile-section">
                    <h4 class="mb-3">App Benefits</h4>
                    
                    <div class="benefits-grid">
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h6>Fast Shopping</h6>
                            <p class="small mb-0">Quick checkout and saved preferences</p>
                        </div>
                        
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <h6>Exclusive Deals</h6>
                            <p class="small mb-0">App-only discounts and promotions</p>
                        </div>
                        
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h6>Store Locator</h6>
                            <p class="small mb-0">Find nearest stores and get directions</p>
                        </div>
                        
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h6>Save Favorites</h6>
                            <p class="small mb-0">Wishlist and quick reorder options</p>
                        </div>
                    </div>
                </div>
                
                <div class="notification-form">
                    <h4 class="mb-3">Get Notified</h4>
                    <p class="mb-3">Be the first to know when our app launches!</p>
                    
                    <form id="notificationForm">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number (Optional)</label>
                            <input type="tel" class="form-control" placeholder="For SMS notifications">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Device Type</label>
                            <select class="form-select">
                                <option>Select your device...</option>
                                <option>iPhone/iOS</option>
                                <option>Android</option>
                                <option>Both</option>
                            </select>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="earlyAccess" checked>
                            <label class="form-check-label" for="earlyAccess">
                                I want early access and exclusive offers
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-bell me-2"></i>Notify Me
                        </button>
                    </form>
                </div>
                
                <div class="mobile-section">
                    <h4 class="mb-3">App Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">50K+</div>
                            <h6>Expected Downloads</h6>
                            <p class="small text-muted mb-0">First month</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">4.8</div>
                            <h6>Target Rating</h6>
                            <p class="small text-muted mb-0">App Store</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <h6>Availability</h6>
                            <p class="small text-muted mb-0">Shopping anytime</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">100%</div>
                            <h6>Mobile Optimized</h6>
                            <p class="small text-muted mb-0">Full functionality</p>
                        </div>
                    </div>
                </div>
                
                <div class="mobile-section">
                    <h4 class="mb-3">What's Included</h4>
                    
                    <div class="list-group">
                        <div class="list-group-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Complete Catalog</strong>
                            <small class="text-muted d-block">Full product range</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Secure Payments</strong>
                            <small class="text-muted d-block">Multiple payment options</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Order Tracking</strong>
                            <small class="text-muted d-block">Real-time updates</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Loyalty Points</strong>
                            <small class="text-muted d-block">Earn and redeem points</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Customer Support</strong>
                            <small class="text-muted d-block">In-app chat and help</small>
                        </div>
                    </div>
                </div>
                
                <div class="mobile-section">
                    <h4 class="mb-3">Development Updates</h4>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-code me-2"></i>Beta Testing</h6>
                        <p class="small mb-0">Currently in closed beta with select users</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-bug me-2"></i>Testing Phase</h6>
                        <p class="small mb-0">Bug fixes and performance optimization in progress</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-rocket me-2"></i>Launch Preparation</h6>
                        <p class="small mb-0">Final testing and App Store submission underway</p>
                    </div>
                </div>
                
                <div class="mobile-section">
                    <h4 class="mb-3">Frequently Asked Questions</h4>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    When will the app be available?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We're targeting a launch within the next 30 days. Sign up for notifications to be the first to know!
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Will the app be free to download?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, the SmartSchool app will be completely free to download from both App Store and Google Play.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Can I use my existing account?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Absolutely! You can log in with your existing SmartSchool account and access all your order history and preferences.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown timer
        function updateCountdown() {
            const launchDate = new Date();
            launchDate.setDate(launchDate.getDate() + 30);
            
            const now = new Date();
            const diff = launchDate - now;
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            document.querySelector('.countdown-item:nth-child(1) .countdown-number').textContent = days;
            document.querySelector('.countdown-item:nth-child(2) .countdown-number').textContent = hours;
            document.querySelector('.countdown-item:nth-child(3) .countdown-number').textContent = minutes;
            document.querySelector('.countdown-item:nth-child(4) .countdown-number').textContent = seconds;
        }
        
        setInterval(updateCountdown, 1000);
        updateCountdown();
        
        // Notification form
        document.getElementById('notificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for signing up! We\'ll notify you as soon as our app launches.');
            this.reset();
        });
    </script>
</body>
</html>
