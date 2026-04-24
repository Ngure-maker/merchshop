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
    <title>Mobile Support - SmartSchool Uniforms</title>
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
        
        .support-header {
            background: linear-gradient(135deg, var(--secondary-teal), var(--accent-green));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .support-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .support-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .support-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .support-header-card {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .support-icon {
            width: 80px;
            height: 80px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--secondary-teal);
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .support-title {
            flex-grow: 1;
        }
        
        .support-title h4 {
            color: var(--secondary-teal);
            margin-bottom: 0.5rem;
        }
        
        .support-title p {
            color: var(--neutral-gray);
            margin: 0;
            font-size: 1rem;
        }
        
        .contact-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .contact-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .contact-icon {
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
        
        .troubleshooting {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .issue-category {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .issue-category h6 {
            color: var(--secondary-teal);
            margin-bottom: 1rem;
        }
        
        .issue-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .issue-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }
        
        .issue-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .issue-description {
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }
        
        .faq-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .guide-section {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .guide-steps {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .step-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .step-content {
            flex-grow: 1;
        }
        
        .emergency-support {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .emergency-button {
            background: white;
            color: #dc3545;
            border: none;
            border-radius: 10px;
            padding: 1rem 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .emergency-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
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
            color: var(--secondary-teal);
            margin-bottom: 0.5rem;
        }
        
        .support-hours {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .hours-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .hours-card {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        
        .hours-day {
            font-weight: 600;
            color: var(--secondary-teal);
            margin-bottom: 0.5rem;
        }
        
        .hours-time {
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }
        
        .resource-links {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .link-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }
        
        .link-item:hover {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }
        
        .link-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="support-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-headset me-3"></i>Mobile Support</h1>
            <p class="lead mb-0">Get help with your SmartSchool mobile app anytime, anywhere</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="emergency-support">
                    <h3 class="mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Emergency Support</h3>
                    <p class="mb-4">Having urgent issues with your app? Get immediate help from our emergency support team.</p>
                    <button class="emergency-button" onclick="callEmergency()">
                        <i class="fas fa-phone me-2"></i>Call Emergency Support
                    </button>
                    <p class="mt-3 mb-0">Available 24/7 for critical issues only</p>
                </div>
                
                <div class="support-section">
                    <h3 class="mb-4">Contact Support</h3>
                    
                    <div class="contact-methods">
                        <div class="contact-card" onclick="openChat()">
                            <div class="contact-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h5>Live Chat</h5>
                            <p class="text-muted">Instant help from our support team</p>
                            <span class="badge bg-success">Available Now</span>
                        </div>
                        
                        <div class="contact-card" onclick="callSupport()">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <h5>Phone Support</h5>
                            <p class="text-muted">Speak with a support specialist</p>
                            <span class="badge bg-success">Available Now</span>
                        </div>
                        
                        <div class="contact-card" onclick="emailSupport()">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h5>Email Support</h5>
                            <p class="text-muted">Get detailed help via email</p>
                            <span class="badge bg-info">24-48hr Response</span>
                        </div>
                        
                        <div class="contact-card" onclick="openHelpCenter()">
                            <div class="contact-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h5>Help Center</h5>
                            <p class="text-muted">Self-service resources and guides</p>
                            <span class="badge bg-primary">Always Available</span>
                        </div>
                    </div>
                </div>
                
                <div class="troubleshooting">
                    <h3 class="mb-4">Common Issues & Solutions</h3>
                    
                    <div class="issue-category">
                        <h6><i class="fas fa-sign-in-alt me-2"></i>Login & Account Issues</h6>
                        
                        <div class="issue-item" onclick="showSolution('forgot-password')">
                            <div class="issue-title">Forgot Password</div>
                            <div class="issue-description">Can't remember your password? Reset it easily.</div>
                        </div>
                        
                        <div class="issue-item" onclick="showSolution('account-locked')">
                            <div class="issue-title">Account Locked</div>
                            <div class="issue-description">Too many failed login attempts? Unlock your account.</div>
                        </div>
                        
                        <div class="issue-item" onclick="showSolution('sync-issues')">
                            <div class="issue-title">Account Sync Issues</div>
                            <div class="issue-description">App not syncing with your website account?</div>
                        </div>
                    </div>
                    
                    <div class="issue-category">
                        <h6><i class="fas fa-shopping-bag me-2"></i>Shopping & Orders</h6>
                        
                        <div class="issue-item" onclick="showSolution('payment-failed')">
                            <div class="issue-title">Payment Failed</div>
                            <div class="issue-description">Payment not going through? Check these solutions.</div>
                        </div>
                        
                        <div class="issue-item" onclick="showSolution('cart-issues')">
                            <div class="issue-title">Cart Problems</div>
                            <div class="issue-description">Items disappearing or can't add to cart?</div>
                        </div>
                        
                        <div class="issue-item" onclick="showSolution('order-tracking')">
                            <div class="issue-title">Order Tracking</div>
                            <div class="issue-description">Can't see your order status or updates?</div>
                        </div>
                    </div>
                    
                    <div class="issue-category">
                        <h6><i class="fas fa-mobile-alt me-2"></i>App Performance</h6>
                        
                        <div class="issue-item" onclick="showSolution('app-crashes')">
                            <div class="issue-title">App Crashing</div>
                            <div class="issue-description">App keeps closing unexpectedly? Try these fixes.</div>
                        </div>
                        
                        <div class="issue-item" onclick="showSolution('slow-performance')">
                            <div class="issue-title">Slow Performance</div>
                            <div class="issue-description">App running slowly? Optimize its performance.</div>
                        </div>
                        
                        <div class="issue-item" onclick="showSolution('connection-issues')">
                            <div class="issue-title">Connection Problems</div>
                            <div class="issue-description">Can't connect to servers? Check your connection.</div>
                        </div>
                    </div>
                    
                    <div class="issue-category">
                        <h6><i class="fas fa-bell me-2"></i>Notifications & Features</h6>
                        
                        <div class="issue-item" onclick="showSolution('no-notifications')">
                            <div class="issue-title">No Notifications</div>
                            <div class="issue-description">Not receiving push notifications?</div>
                        </div>
                        
                        <div class="issue-item" onclick="showSolution('size-calculator')">
                            <div class="issue-title">Size Calculator Issues</div>
                            <div class="issue-description">Calculator not working or giving wrong results?</div>
                        </div>
                        
                        <div class="issue-item" onclick="showSolution('barcode-scanner')">
                            <div class="issue-title">Barcode Scanner</div>
                            <div class="issue-description">Scanner not working in-store?</div>
                        </div>
                    </div>
                </div>
                
                <div class="guide-section">
                    <h3 class="mb-4"><i class="fas fa-book-open me-2"></i>Quick Setup Guide</h3>
                    
                    <div class="guide-steps">
                        <h5 class="mb-3">Getting Started with the App</h5>
                        
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h6>Download & Install</h6>
                                <p>Download the app from App Store or Google Play and install it on your device.</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h6>Create Account or Login</h6>
                                <p>Use your existing SmartSchool account or create a new one in seconds.</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h6>Set Up Profile</h6>
                                <p>Add your children's information, sizes, and school details for personalized experience.</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h6>Enable Notifications</h6>
                                <p>Allow push notifications to stay updated on orders and special offers.</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h6>Start Shopping</h6>
                                <p>Browse our catalog, use the size calculator, and enjoy mobile shopping!</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="faq-section">
                    <h3 class="mb-4">Frequently Asked Questions</h3>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I reset my password?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Tap "Forgot Password" on the login screen, enter your email address, and follow the instructions sent to your email. You'll receive a link to reset your password within minutes.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Why isn't the app working offline?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The app requires an internet connection for most features like shopping, order tracking, and real-time updates. However, you can browse previously loaded products and access your wishlist offline.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How do I enable push notifications?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Go to your phone's Settings > Notifications > SmartSchool App, and enable notifications. You can also manage notification preferences in the app's Settings menu.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Is my payment information secure?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, all payment information is encrypted using industry-standard security protocols. We never store your credit card details on our servers, and all transactions are processed through secure payment gateways.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="support-section">
                    <h4 class="mb-3">Support Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">95%</div>
                            <h6>Issues Resolved</h6>
                            <p class="small text-muted mb-0">On first contact</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">2min</div>
                            <h6>Avg Response Time</h6>
                            <p class="small text-muted mb-0">Live chat</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <h6>Support Available</h6>
                            <p class="small text-muted mb-0">For emergencies</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">4.9</div>
                            <h6>Satisfaction Rating</h6>
                            <p class="small text-muted mb-0">From users</p>
                        </div>
                    </div>
                </div>
                
                <div class="support-hours">
                    <h4 class="mb-3">Support Hours</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Regular Support</h6>
                        <p class="small mb-0">Monday - Friday: 8AM - 8PM<br>
                        Saturday: 9AM - 6PM<br>
                        Sunday: 10AM - 4PM</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-phone me-2"></i>Phone Support</h6>
                        <p class="small mb-0">Monday - Friday: 9AM - 6PM<br>
                        Saturday: 10AM - 4PM<br>
                        Sunday: Closed</p>
                    </div>
                    
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Emergency Support</h6>
                        <p class="small mb-0">Available 24/7 for critical issues only</p>
                    </div>
                </div>
                
                <div class="support-section">
                    <h4 class="mb-3">Quick Actions</h4>
                    
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action" onclick="reportBug()">
                            <i class="fas fa-bug me-2"></i>
                            Report a Bug
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="requestFeature()">
                            <i class="fas fa-lightbulb me-2"></i>
                            Request a Feature
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="rateApp()">
                            <i class="fas fa-star me-2"></i>
                            Rate the App
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="sendFeedback()">
                            <i class="fas fa-comment me-2"></i>
                            Send Feedback
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="checkUpdates()">
                            <i class="fas fa-download me-2"></i>
                            Check for Updates
                        </a>
                    </div>
                </div>
                
                <div class="resource-links">
                    <h4 class="mb-3">Helpful Resources</h4>
                    
                    <a href="#" class="link-item" onclick="openUserGuide()">
                        <div class="link-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <div class="fw-bold">User Guide</div>
                            <small class="text-muted">Complete app documentation</small>
                        </div>
                    </a>
                    
                    <a href="#" class="link-item" onclick="openVideoTutorials()">
                        <div class="link-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Video Tutorials</div>
                            <small class="text-muted">Step-by-step video guides</small>
                        </div>
                    </a>
                    
                    <a href="#" class="link-item" onclick="openFAQ()">
                        <div class="link-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Extended FAQ</div>
                            <small class="text-muted">More questions answered</small>
                        </div>
                    </a>
                    
                    <a href="#" class="link-item" onclick="openCommunity()">
                        <div class="link-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Community Forum</div>
                            <small class="text-muted">Get help from other users</small>
                        </div>
                    </a>
                </div>
                
                <div class="support-section">
                    <h4 class="mb-3">Device-Specific Help</h4>
                    
                    <div class="accordion" id="deviceAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#iosHelp">
                                    <i class="fab fa-apple me-2"></i>iOS Help
                                </button>
                            </h2>
                            <div id="iosHelp" class="accordion-collapse collapse show" data-bs-parent="#deviceAccordion">
                                <div class="accordion-body">
                                    <ul class="small">
                                        <li>Update to latest iOS version</li>
                                        <li>Clear app cache if slow</li>
                                        <li>Check storage space</li>
                                        <li>Restart device if needed</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#androidHelp">
                                    <i class="fab fa-android me-2"></i>Android Help
                                </button>
                            </h2>
                            <div id="androidHelp" class="accordion-collapse collapse" data-bs-parent="#deviceAccordion">
                                <div class="accordion-body">
                                    <ul class="small">
                                        <li>Update to latest Android version</li>
                                        <li>Clear app data and cache</li>
                                        <li>Check app permissions</li>
                                        <li>Enable auto-updates</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="support-section">
                    <h4 class="mb-3">Contact Information</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-phone me-2"></i>Phone Support</h6>
                        <p class="small mb-0"><strong>Main:</strong> +254 700 123 456<br>
                        <strong>Emergency:</strong> +254 700 789 012</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-envelope me-2"></i>Email Support</h6>
                        <p class="small mb-0"><strong>General:</strong> support@smartschool.co.ke<br>
                        <strong>Emergency:</strong> emergency@smartschool.co.ke</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-comments me-2"></i>Live Chat</h6>
                        <p class="small mb-0">Available in-app and on website<br>
                        Response time: Under 2 minutes</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function callEmergency() {
            alert('Emergency Support: +254 700 789 012\n\nThis line is for critical issues only. For regular support, please use our standard support channels.');
        }
        
        function openChat() {
            alert('Live chat is available! Click the chat widget at the bottom of the screen to start a conversation with our support team.');
        }
        
        function callSupport() {
            alert('Call us at: +254 700 123 456\n\nOur support team is available Monday-Friday 8AM-8PM, Saturday 9AM-6PM, Sunday 10AM-4PM.');
        }
        
        function emailSupport() {
            alert('Send us an email at: support@smartschool.co.ke\n\nWe typically respond within 24-48 hours. For urgent issues, please use live chat or phone support.');
        }
        
        function openHelpCenter() {
            alert('Help Center: Browse our self-service resources, guides, and FAQs at help.smartschool.co.ke');
        }
        
        function showSolution(issue) {
            const solutions = {
                'forgot-password': 'To reset your password:\n1. Tap "Forgot Password" on login screen\n2. Enter your email address\n3. Check your email for reset link\n4. Create a new password\n5. Login with your new password',
                'account-locked': 'To unlock your account:\n1. Wait 30 minutes for automatic unlock\n2. Or use "Forgot Password" to reset\n3. Contact support if still locked',
                'sync-issues': 'To fix sync issues:\n1. Check internet connection\n2. Pull down to refresh\n3. Log out and log back in\n4. Update app to latest version',
                'payment-failed': 'Payment failed solutions:\n1. Check card details and expiry\n2. Ensure sufficient funds\n3. Try different payment method\n4. Contact your bank if declined',
                'cart-issues': 'Cart problem fixes:\n1. Clear app cache\n2. Restart the app\n3. Check internet connection\n4. Remove and re-add items',
                'order-tracking': 'Order tracking fixes:\n1. Refresh order status\n2. Check order confirmation email\n3. Wait 24 hours for status update\n4. Contact support with order number',
                'app-crashes': 'App crash solutions:\n1. Update to latest app version\n2. Restart your device\n3. Clear app cache/data\n4. Reinstall the app if needed',
                'slow-performance': 'Speed up the app:\n1. Close other apps\n2. Clear app cache\n3. Update app and device\n4. Check storage space',
                'connection-issues': 'Connection fixes:\n1. Check WiFi/cellular data\n2. Restart internet connection\n3. Try different network\n4. Check app permissions',
                'no-notifications': 'Enable notifications:\n1. Go to phone Settings > Notifications\n2. Find SmartSchool app\n3. Enable all notifications\n4. Check app notification settings',
                'size-calculator': 'Calculator fixes:\n1. Allow camera permissions\n2. Update app to latest version\n4. Restart the app\n4. Use manual measurement option',
                'barcode-scanner': 'Scanner fixes:\n1. Allow camera permissions\n2. Check camera lens cleanliness\n3. Ensure good lighting\n4. Hold steady while scanning'
            };
            
            alert(solutions[issue] || 'Solution not available. Please contact support for assistance.');
        }
        
        function reportBug() {
            alert('To report a bug:\n1. Note what you were doing\n2. Take screenshots if possible\n3. Email: bugs@smartschool.co.ke\n4. Include device info and app version');
        }
        
        function requestFeature() {
            alert('To request a feature:\n1. Describe the feature you want\n2. Explain why it would be useful\n3. Email: features@smartschool.co.ke\n4. We\'ll consider it for future updates!');
        }
        
        function rateApp() {
            alert('Thank you for using our app! Please rate us on:\n• App Store (iOS)\n• Google Play Store (Android)\n\nYour feedback helps us improve!');
        }
        
        function sendFeedback() {
            alert('We value your feedback!\n\nEmail: feedback@smartschool.co.ke\n\nTell us what you love about the app and what we can improve.');
        }
        
        function checkUpdates() {
            alert('To check for updates:\n• iOS: App Store > Updates\n• Android: Google Play > SmartSchool App\n\nEnable auto-updates to always have the latest version!');
        }
        
        function openUserGuide() {
            alert('User Guide: Visit help.smartschool.co.ke/guide\n\nComplete documentation with screenshots and step-by-step instructions.');
        }
        
        function openVideoTutorials() {
            alert('Video Tutorials: Visit help.smartschool.co.ke/videos\n\nWatch step-by-step guides for all app features.');
        }
        
        function openFAQ() {
            alert('Extended FAQ: Visit help.smartschool.co.ke/faq\n\nMore detailed answers to common questions.');
        }
        
        function openCommunity() {
            alert('Community Forum: Visit community.smartschool.co.ke\n\nConnect with other users, share tips, and get help from the community.');
        }
    </script>
</body>
</html>
