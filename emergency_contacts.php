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
    <title>Emergency Contacts - SmartSchool Uniforms</title>
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
        
        .emergency-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .emergency-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .emergency-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .emergency-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .critical-contacts {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .contact-item {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .contact-item:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .contact-details {
            flex-grow: 1;
        }
        
        .contact-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .contact-info {
            font-size: 0.9rem;
            opacity: 0.9;
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
            text-decoration: none;
            display: inline-block;
        }
        
        .emergency-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .department-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .department-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .department-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .department-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .department-icon {
            width: 50px;
            height: 50px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--accent-purple);
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .department-title {
            flex-grow: 1;
        }
        
        .department-title h5 {
            color: var(--accent-purple);
            margin-bottom: 0.25rem;
        }
        
        .department-title p {
            color: var(--neutral-gray);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .contact-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .contact-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .contact-list li:last-child {
            border-bottom: none;
        }
        
        .contact-label {
            font-weight: 600;
            color: var(--neutral-gray);
        }
        
        .contact-value {
            color: var(--secondary-blue);
            font-weight: 500;
        }
        
        .emergency-types {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .emergency-type {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 5px solid var(--primary-amber);
        }
        
        .emergency-type h6 {
            color: var(--primary-amber);
            margin-bottom: 0.5rem;
        }
        
        .emergency-type p {
            margin-bottom: 0;
        }
        
        .location-emergency {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
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
            color: #dc3545;
            margin-bottom: 0.5rem;
        }
        
        .quick-dial {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .dial-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .dial-button {
            background: var(--light-bg);
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .dial-button:hover {
            border-color: var(--accent-purple);
            background: white;
            transform: translateY(-2px);
        }
        
        .dial-icon {
            font-size: 1.5rem;
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
        
        .dial-label {
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="emergency-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-phone-alt me-3"></i>Emergency Contacts</h1>
            <p class="lead mb-0">Quick access to all important contacts and emergency services</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="critical-contacts">
                    <h3 class="mb-4"><i class="fas fa-exclamation-triangle me-2"></i>Critical Emergency Contacts</h3>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-name">Emergency Hotline</div>
                            <div class="contact-info">24/7 Emergency Support for Critical Issues</div>
                        </div>
                        <a href="tel:+254700789012" class="emergency-button">
                            <i class="fas fa-phone me-2"></i>Call Now
                        </a>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-ambulance"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-name">Medical Emergency</div>
                            <div class="contact-info">Ambulance Services - 999</div>
                        </div>
                        <a href="tel:999" class="emergency-button">
                            <i class="fas fa-phone me-2"></i>Call 999
                        </a>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-fire-extinguisher"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-name">Fire Emergency</div>
                            <div class="contact-info">Fire Services - 911</div>
                        </div>
                        <a href="tel:911" class="emergency-button">
                            <i class="fas fa-phone me-2"></i>Call 911
                        </a>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-name">Police Emergency</div>
                            <div class="contact-info">Police Services - 911</div>
                        </div>
                        <a href="tel:911" class="emergency-button">
                            <i class="fas fa-phone me-2"></i>Call 911
                        </a>
                    </div>
                </div>
                
                <div class="emergency-section">
                    <h3 class="mb-4">Department Contacts</h3>
                    
                    <div class="department-grid">
                        <div class="department-card">
                            <div class="department-header">
                                <div class="department-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="department-title">
                                    <h5>Customer Support</h5>
                                    <p>General inquiries and assistance</p>
                                </div>
                            </div>
                            
                            <ul class="contact-list">
                                <li>
                                    <span class="contact-label">Phone:</span>
                                    <span class="contact-value">+254 700 123 456</span>
                                </li>
                                <li>
                                    <span class="contact-label">Email:</span>
                                    <span class="contact-value">support@smartschool.co.ke</span>
                                </li>
                                <li>
                                    <span class="contact-label">Hours:</span>
                                    <span class="contact-value">Mon-Fri 8AM-8PM</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="department-card">
                            <div class="department-header">
                                <div class="department-icon">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <div class="department-title">
                                    <h5>Delivery & Logistics</h5>
                                    <p>Order delivery and tracking</p>
                                </div>
                            </div>
                            
                            <ul class="contact-list">
                                <li>
                                    <span class="contact-label">Phone:</span>
                                    <span class="contact-value">+254 700 234 567</span>
                                </li>
                                <li>
                                    <span class="contact-label">Email:</span>
                                    <span class="contact-value">delivery@smartschool.co.ke</span>
                                </li>
                                <li>
                                    <span class="contact-label">Hours:</span>
                                    <span class="contact-value">Mon-Sat 9AM-6PM</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="department-card">
                            <div class="department-header">
                                <div class="department-icon">
                                    <i class="fas fa-undo"></i>
                                </div>
                                <div class="department-title">
                                    <h5>Returns & Exchanges</h5>
                                    <p>Product returns and exchanges</p>
                                </div>
                            </div>
                            
                            <ul class="contact-list">
                                <li>
                                    <span class="contact-label">Phone:</span>
                                    <span class="contact-value">+254 700 345 678</span>
                                </li>
                                <li>
                                    <span class="contact-label">Email:</span>
                                    <span class="contact-value">returns@smartschool.co.ke</span>
                                </li>
                                <li>
                                    <span class="contact-label">Hours:</span>
                                    <span class="contact-value">Mon-Fri 9AM-5PM</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="department-card">
                            <div class="department-header">
                                <div class="department-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="department-title">
                                    <h5>School Relations</h5>
                                    <p>School partnerships and bulk orders</p>
                                </div>
                            </div>
                            
                            <ul class="contact-list">
                                <li>
                                    <span class="contact-label">Phone:</span>
                                    <span class="contact-value">+254 700 456 789</span>
                                </li>
                                <li>
                                    <span class="contact-label">Email:</span>
                                    <span class="contact-value">schools@smartschool.co.ke</span>
                                </li>
                                <li>
                                    <span class="contact-label">Hours:</span>
                                    <span class="contact-value">Mon-Fri 8AM-6PM</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="department-card">
                            <div class="department-header">
                                <div class="department-icon">
                                    <i class="fas fa-bug"></i>
                                </div>
                                <div class="department-title">
                                    <h5>Technical Support</h5>
                                    <p>Website and app technical issues</p>
                                </div>
                            </div>
                            
                            <ul class="contact-list">
                                <li>
                                    <span class="contact-label">Phone:</span>
                                    <span class="contact-value">+254 700 567 890</span>
                                </li>
                                <li>
                                    <span class="contact-label">Email:</span>
                                    <span class="contact-value">tech@smartschool.co.ke</span>
                                </li>
                                <li>
                                    <span class="contact-label">Hours:</span>
                                    <span class="contact-value">24/7 Support</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="department-card">
                            <div class="department-header">
                                <div class="department-icon">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div class="department-title">
                                    <h5>Marketing & PR</h5>
                                    <p>Media inquiries and partnerships</p>
                                </div>
                            </div>
                            
                            <ul class="contact-list">
                                <li>
                                    <span class="contact-label">Phone:</span>
                                    <span class="contact-value">+254 700 678 901</span>
                                </li>
                                <li>
                                    <span class="contact-label">Email:</span>
                                    <span class="contact-value">marketing@smartschool.co.ke</span>
                                </li>
                                <li>
                                    <span class="contact-label">Hours:</span>
                                    <span class="contact-value">Mon-Fri 9AM-5PM</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="emergency-types">
                    <h3 class="mb-4">When to Call Emergency Contacts</h3>
                    
                    <div class="emergency-type">
                        <h6><i class="fas fa-exclamation-circle me-2"></i>Order Emergencies</h6>
                        <p>Wrong items delivered, urgent delivery needed, payment issues, or order cancellation requests.</p>
                    </div>
                    
                    <div class="emergency-type">
                        <h6><i class="fas fa-exclamation-circle me-2"></i>Product Issues</h6>
                        <p>Defective products, sizing emergencies, quality concerns, or immediate replacement needs.</p>
                    </div>
                    
                    <div class="emergency-type">
                        <h6><i class="fas fa-exclamation-circle me-2"></i>Technical Emergencies</h6>
                        <p>Website crashes, app malfunctions, payment processing errors, or account access issues.</p>
                    </div>
                    
                    <div class="emergency-type">
                        <h6><i class="fas fa-exclamation-circle me-2"></i>School Uniform Emergencies</h6>
                        <p>Last-minute uniform needs, school deadline emergencies, or bulk order urgent requirements.</p>
                    </div>
                </div>
                
                <div class="location-emergency">
                    <h3 class="mb-4"><i class="fas fa-map-marker-alt me-2"></i>Store Locations & Emergency Contacts</h3>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-name">Nairobi Main Store</div>
                            <div class="contact-info">Moi Avenue, Nairobi CBD | +254 700 111 222</div>
                        </div>
                        <a href="tel:+254700111222" class="emergency-button">
                            <i class="fas fa-phone me-2"></i>Call Store
                        </a>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-name">Mombasa Branch</div>
                            <div class="contact-info">Digo Road, Mombasa | +254 700 333 444</div>
                        </div>
                        <a href="tel:+254700333444" class="emergency-button">
                            <i class="fas fa-phone me-2"></i>Call Store
                        </a>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-name">Kisumu Branch</div>
                            <div class="contact-info">Oginga Odinga Street, Kisumu | +254 700 555 666</div>
                        </div>
                        <a href="tel:+254700555666" class="emergency-button">
                            <i class="fas fa-phone me-2"></i>Call Store
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="emergency-section">
                    <h4 class="mb-3">Emergency Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <h6>Emergency Support</h6>
                            <p class="small text-muted mb-0">Always available</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">2min</div>
                            <h6>Avg Response</h6>
                            <p class="small text-muted mb-0">Emergency calls</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">98%</div>
                            <h6>Issues Resolved</h6>
                            <p class="small text-muted mb-0">On first contact</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">15+</div>
                            <h6>Support Staff</h6>
                            <p class="small text-muted mb-0">Always on duty</p>
                        </div>
                    </div>
                </div>
                
                <div class="quick-dial">
                    <h4 class="mb-3">Quick Dial</h4>
                    
                    <div class="dial-buttons">
                        <div class="dial-button" onclick="window.location.href='tel:+254700123456'">
                            <div class="dial-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="dial-label">Support</div>
                        </div>
                        
                        <div class="dial-button" onclick="window.location.href='tel:+254700789012'">
                            <div class="dial-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="dial-label">Emergency</div>
                        </div>
                        
                        <div class="dial-button" onclick="window.location.href='tel:+254700234567'">
                            <div class="dial-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="dial-label">Delivery</div>
                        </div>
                        
                        <div class="dial-button" onclick="window.location.href='tel:+254700345678'">
                            <div class="dial-icon">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div class="dial-label">Returns</div>
                        </div>
                        
                        <div class="dial-button" onclick="window.location.href='tel:+254700456789'">
                            <div class="dial-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="dial-label">Schools</div>
                        </div>
                        
                        <div class="dial-button" onclick="window.location.href='tel:+254700567890'">
                            <div class="dial-icon">
                                <i class="fas fa-bug"></i>
                            </div>
                            <div class="dial-label">Technical</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Quick Tips</h6>
                        <ul class="small mb-0">
                            <li>Save important numbers in your phone</li>
                            <li>Have your order number ready when calling</li>
                            <li>Use emergency line for critical issues only</li>
                            <li>Live chat available for non-urgent matters</li>
                        </ul>
                    </div>
                </div>
                
                <div class="emergency-section">
                    <h4 class="mb-3">Emergency Procedures</h4>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Step 1: Assess the Situation</h6>
                        <p class="small mb-0">Determine if this is a true emergency or can wait for regular support hours.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-phone-alt me-2"></i>Step 2: Call Appropriate Number</h6>
                        <p class="small mb-0">Use the emergency hotline for critical issues, or department numbers for specific help.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-info-circle me-2"></i>Step 3: Provide Information</h6>
                        <p class="small mb-0">Have your order number, account details, and a clear description of the issue ready.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-check-circle me-2"></i>Step 4: Follow Instructions</h6>
                        <p class="small mb-0">Listen carefully to support staff and follow their guidance for resolution.</p>
                    </div>
                </div>
                
                <div class="emergency-section">
                    <h4 class="mb-3">Alternative Contact Methods</h4>
                    
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action" onclick="startLiveChat()">
                            <i class="fas fa-comments me-2"></i>
                            <strong>Live Chat</strong>
                            <small class="text-muted d-block">Instant support during business hours</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="sendWhatsApp()">
                            <i class="fab fa-whatsapp me-2"></i>
                            <strong>WhatsApp Support</strong>
                            <small class="text-muted d-block">+254 700 123 456</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="sendEmail()">
                            <i class="fas fa-envelope me-2"></i>
                            <strong>Email Support</strong>
                            <small class="text-muted d-block">24-48 hour response time</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="openHelpCenter()">
                            <i class="fas fa-question-circle me-2"></i>
                            <strong>Help Center</strong>
                            <small class="text-muted d-block">Self-service resources</small>
                        </a>
                    </div>
                </div>
                
                <div class="emergency-section">
                    <h4 class="mb-3">Important Reminders</h4>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-clock me-2"></i>Response Times</h6>
                        <p class="small mb-0">Emergency: Under 2 minutes<br>
                        Regular Support: Under 5 minutes<br>
                                Email: 24-48 hours</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-calendar me-2"></i>Support Hours</h6>
                        <p class="small mb-0">Emergency: 24/7<br>
                        Phone Support: Mon-Fri 8AM-8PM<br>
                                Live Chat: Mon-Sat 9AM-6PM</p>
                    </div>
                    
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>When to Use Emergency</h6>
                        <p class="small mb-0">• Critical order issues<br>
                        • Payment failures<br>
                        • Account lockouts<br>
                        • Website/app crashes</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function startLiveChat() {
            alert('Live chat is available! Click the chat widget at the bottom of the screen to start a conversation with our support team.');
        }
        
        function sendWhatsApp() {
            window.open('https://wa.me/254700123456', '_blank');
        }
        
        function sendEmail() {
            window.location.href = 'mailto:support@smartschool.co.ke';
        }
        
        function openHelpCenter() {
            alert('Help Center: Visit help.smartschool.co.ke for self-service resources and guides.');
        }
    </script>
</body>
</html>
