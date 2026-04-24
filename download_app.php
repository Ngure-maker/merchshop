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
    <title>Download App - SmartSchool Uniforms</title>
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
        
        .download-header {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .coming-soon-section {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 3rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .coming-soon-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .countdown-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .countdown-item {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            min-width: 120px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .countdown-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .countdown-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .countdown-label {
            font-size: 1rem;
            text-transform: uppercase;
        }
        
        .app-preview {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .phone-showcase {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3rem;
            margin: 2rem 0;
        }
        
        .phone-mockup {
            width: 250px;
            height: 500px;
            background: linear-gradient(135deg, #333, #666);
            border-radius: 35px;
            padding: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            position: relative;
            transform: rotate(-5deg);
            transition: all 0.3s ease;
        }
        
        .phone-mockup:hover {
            transform: rotate(0deg) scale(1.05);
        }
        
        .phone-screen {
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 25px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .app-header {
            background: var(--secondary-blue);
            color: white;
            padding: 1.5rem;
            text-align: center;
            font-weight: bold;
        }
        
        .app-content {
            flex-grow: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .app-feature {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .app-feature-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .app-nav {
            background: #f8f9fa;
            padding: 1rem;
            display: flex;
            justify-content: space-around;
            border-top: 1px solid #e9ecef;
        }
        
        .app-nav-item {
            color: var(--neutral-gray);
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .app-nav-item:hover {
            color: var(--secondary-blue);
            transform: scale(1.2);
        }
        
        .store-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }
        
        .store-button {
            background: black;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .store-button.apple {
            background: #000;
        }
        
        .store-button.google {
            background: #4285F4;
        }
        
        .store-button:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }
        
        .notification-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
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
            color: var(--accent-green);
            margin: 0 auto 1.5rem;
        }
        
        .benefits-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .benefit-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .benefit-item:hover {
            transform: translateX(10px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .benefit-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .faq-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .social-proof {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .preview-stats {
            display: flex;
            justify-content: space-around;
            margin: 2rem 0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-green);
        }
        
        .stat-label {
            color: var(--neutral-gray);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="download-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-download me-3"></i>Download SmartSchool App</h1>
            <p class="lead mb-0">Get the future of uniform shopping in your pocket</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="coming-soon-section">
            <div class="coming-soon-title">
                <i class="fas fa-rocket me-3"></i>COMING SOON
            </div>
            <p class="lead mb-4">The SmartSchool mobile app is currently under development</p>
            <p class="mb-4">Be the first to experience uniform shopping like never before!</p>
            
            <div class="preview-stats">
                <div class="stat-item">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">Waitlist Signups</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.9</div>
                    <div class="stat-label">Expected Rating</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Features</div>
                </div>
            </div>
        </div>
        
        <div class="countdown-section">
            <h3 class="text-center mb-4"><i class="fas fa-clock me-2"></i>Launch Countdown</h3>
            
            <div class="countdown">
                <div class="countdown-item">
                    <div class="countdown-number" id="days">30</div>
                    <div class="countdown-label">Days</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="hours">15</div>
                    <div class="countdown-label">Hours</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="minutes">45</div>
                    <div class="countdown-label">Minutes</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="seconds">22</div>
                    <div class="countdown-label">Seconds</div>
                </div>
            </div>
            
            <div class="text-center">
                <p class="text-muted">Expected launch date: <strong>January 2024</strong></p>
            </div>
        </div>
        
        <div class="app-preview">
            <h3 class="text-center mb-4">Sneak Peek: What's Coming</h3>
            
            <div class="phone-showcase">
                <div class="phone-mockup">
                    <div class="phone-screen">
                        <div class="app-header">
                            <i class="fas fa-graduation-cap me-2"></i>SmartSchool
                        </div>
                        <div class="app-content">
                            <div class="app-feature">
                                <div class="app-feature-icon">
                                    <i class="fas fa-tshirt"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Shop Uniforms</div>
                                    <small>Complete catalog</small>
                                </div>
                            </div>
                            <div class="app-feature">
                                <div class="app-feature-icon">
                                    <i class="fas fa-ruler"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Size Calculator</div>
                                    <small>Perfect fit guaranteed</small>
                                </div>
                            </div>
                            <div class="app-feature">
                                <div class="app-feature-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Wishlist</div>
                                    <small>Save favorites</small>
                                </div>
                            </div>
                            <div class="app-feature">
                                <div class="app-feature-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Cart</div>
                                    <small>3 items</small>
                                </div>
                            </div>
                        </div>
                        <div class="app-nav">
                            <i class="fas fa-home app-nav-item"></i>
                            <i class="fas fa-search app-nav-item"></i>
                            <i class="fas fa-shopping-cart app-nav-item"></i>
                            <i class="fas fa-user app-nav-item"></i>
                        </div>
                    </div>
                </div>
                
                <div class="store-buttons">
                    <h4>Available Soon On</h4>
                    <a href="#" class="store-button apple">
                        <i class="fab fa-apple fa-2x"></i>
                        <div>
                            <small>Download on the</small>
                            <div class="fw-bold">App Store</div>
                        </div>
                    </a>
                    <a href="#" class="store-button google">
                        <i class="fab fa-google-play fa-2x"></i>
                        <div>
                            <small>Get it on</small>
                            <div class="fw-bold">Google Play</div>
                        </div>
                    </a>
                    <p class="text-muted mt-3">
                        <i class="fas fa-lock me-1"></i>
                        Sign up below to be notified when available
                    </p>
                </div>
            </div>
        </div>
        
        <div class="notification-section">
            <h3 class="text-center mb-4"><i class="fas fa-bell me-2"></i>Get Notified on Launch Day</h3>
            
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <form id="notificationForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" placeholder="Enter your email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" placeholder="For SMS alerts (optional)">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Device Type</label>
                                    <select class="form-select">
                                        <option>Select your device...</option>
                                        <option>iPhone/iOS</option>
                                        <option>Android</option>
                                        <option>Both</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">School (Optional)</label>
                                    <input type="text" class="form-control" placeholder="Your school name">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="earlyAccess" checked>
                            <label class="form-check-label" for="earlyAccess">
                                I want early access and exclusive launch offers
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="updates" checked>
                            <label class="form-check-label" for="updates">
                                Send me updates about app development progress
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-bell me-2"></i>Notify Me on Launch
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h4>Complete Mobile Shopping</h4>
                <p>Browse our entire catalog, filter by size and color, and make purchases directly from your phone with our secure checkout system.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-ruler-combined"></i>
                </div>
                <h4>Smart Size Calculator</h4>
                <p>Use our mobile-optimized size calculator with AR technology to find the perfect uniform fit for your child.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h4>Push Notifications</h4>
                <p>Get instant updates about new arrivals, special deals, order status, and back-in-stock alerts for your favorite items.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h4>In-Store Integration</h4>
                <p>Scan products in-store for details, quick checkout, and access your loyalty points seamlessly.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h4>Wishlist & Favorites</h4>
                <p>Save your favorite uniform combinations and get notified when they're on sale or back in stock.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h4>Account Management</h4>
                <p>Manage your profile, track orders, view purchase history, and update preferences easily.</p>
            </div>
        </div>
        
        <div class="benefits-section">
            <h3 class="mb-4">Why Waitlist for Our App?</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div>
                            <h6>Early Bird Discount</h6>
                            <p class="text-muted mb-0">Get 20% off your first app purchase when you sign up now</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <h6>Exclusive Access</h6>
                            <p class="text-muted mb-0">Be among the first to download and use the app before public release</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div>
                            <h6>App-Only Deals</h6>
                            <p class="text-muted mb-0">Access special promotions and discounts available only to app users</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h6>Priority Support</h6>
                            <p class="text-muted mb-0">Get dedicated customer support for app-related questions and issues</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div>
                            <h6>Double Loyalty Points</h6>
                            <p class="text-muted mb-0">Earn 2x loyalty points on all purchases made through the app for the first month</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h6>Regular Updates</h6>
                            <p class="text-muted mb-0">Stay informed about development progress and new features</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="faq-section">
            <h3 class="mb-4">Frequently Asked Questions</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="accordion" id="faqAccordion1">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    When will the app be available?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion1">
                                <div class="accordion-body">
                                    We're targeting a launch in January 2024. Sign up for notifications to be the first to know when it's available!
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Will the app be free to download?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion1">
                                <div class="accordion-body">
                                    Yes, the SmartSchool app will be completely free to download from both App Store and Google Play Store.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    What devices will be supported?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion1">
                                <div class="accordion-body">
                                    The app will support iOS 12+ and Android 8+ devices, including both phones and tablets.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="accordion" id="faqAccordion2">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Can I use my existing account?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion2">
                                <div class="accordion-body">
                                    Absolutely! You can log in with your existing SmartSchool account and access all your order history and preferences.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    What features will be included?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion2">
                                <div class="accordion-body">
                                    The app will include full shopping capabilities, size calculator, wishlist, order tracking, loyalty points, and much more.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    Is my payment information secure?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion2">
                                <div class="accordion-body">
                                    Yes, all payments are processed using industry-standard encryption and security measures to protect your information.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="social-proof">
            <h3 class="text-center mb-4">Join the Waitlist Community</h3>
            
            <div class="text-center mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-number">10,000+</div>
                            <div class="stat-label">Parents Waiting</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Schools Registered</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-number">4.8</div>
                            <div class="stat-label">Expected Rating</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-success">
                <h6><i class="fas fa-users me-2"></i>Community Growing</h6>
                <p class="mb-0">Thousands of parents and students are already waiting for the SmartSchool app. Join them today and get exclusive launch benefits!</p>
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
            
            if (diff > 0) {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                document.getElementById('days').textContent = days;
                document.getElementById('hours').textContent = hours;
                document.getElementById('minutes').textContent = minutes;
                document.getElementById('seconds').textContent = seconds;
            }
        }
        
        setInterval(updateCountdown, 1000);
        updateCountdown();
        
        // Notification form
        document.getElementById('notificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for joining the waitlist! You\'ll be notified as soon as our app launches. Check your email for a confirmation message.');
            this.reset();
        });
    </script>
</body>
</html>
