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
    <title>Privacy Policy - SmartSchool Uniforms</title>
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
        
        .policy-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .policy-header {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .policy-item {
            border-left: 4px solid var(--primary-amber);
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .policy-item h5 {
            color: var(--primary-dark);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <main class="container my-5">
        <div class="policy-header">
            <h1 class="mb-3"><i class="fas fa-shield-alt me-3"></i>Privacy Policy</h1>
            <p class="mb-0">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
        
        <div class="policy-section">
            <h3 class="mb-4">Information We Collect</h3>
            
            <div class="policy-item">
                <h5>Personal Information</h5>
                <p>When you register on our platform, we collect information such as your name, email address, phone number, and school affiliation. This information is used to provide you with personalized services and to process your orders.</p>
            </div>
            
            <div class="policy-item">
                <h5>Order Information</h5>
                <p>We collect details about your orders, including product selections, shipping address, and payment information. This helps us process and deliver your orders efficiently.</p>
            </div>
            
            <div class="policy-item">
                <h5>Technical Information</h5>
                <p>We automatically collect certain technical information when you visit our website, including your IP address, browser type, and device information. This helps us improve our services and ensure security.</p>
            </div>
        </div>
        
        <div class="policy-section">
            <h3 class="mb-4">How We Use Your Information</h3>
            
            <div class="policy-item">
                <h5>Service Provision</h5>
                <p>We use your information to provide, maintain, and improve our services, including processing orders, providing customer support, and personalizing your experience.</p>
            </div>
            
            <div class="policy-item">
                <h5>Communication</h5>
                <p>We may use your contact information to send you important updates about your orders, account information, and relevant promotional content (with your consent).</p>
            </div>
            
            <div class="policy-item">
                <h5>Security and Fraud Prevention</h5>
                <p>Your information helps us detect and prevent fraudulent activities, ensure the security of our platform, and comply with legal obligations.</p>
            </div>
        </div>
        
        <div class="policy-section">
            <h3 class="mb-4">Data Protection</h3>
            
            <div class="policy-item">
                <h5>Security Measures</h5>
                <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
            </div>
            
            <div class="policy-item">
                <h5>Data Retention</h5>
                <p>We retain your personal information only as long as necessary to fulfill the purposes for which it was collected, unless a longer retention period is required or permitted by law.</p>
            </div>
        </div>
        
        <div class="policy-section">
            <h3 class="mb-4">Your Rights</h3>
            
            <div class="policy-item">
                <h5>Access and Correction</h5>
                <p>You have the right to access and update your personal information through your account settings or by contacting our support team.</p>
            </div>
            
            <div class="policy-item">
                <h5>Data Portability</h5>
                <p>You can request a copy of your personal information in a structured, machine-readable format.</p>
            </div>
            
            <div class="policy-item">
                <h5>Right to Erasure</h5>
                <p>You can request the deletion of your personal information, subject to certain legal obligations and legitimate business interests.</p>
            </div>
        </div>
        
        <div class="policy-section">
            <h3 class="mb-4">Contact Us</h3>
            <p>If you have any questions about this Privacy Policy or how we handle your personal information, please contact us:</p>
            <div class="row">
                <div class="col-md-6">
                    <p><i class="fas fa-envelope me-2"></i>privacy@smartschool.com</p>
                    <p><i class="fas fa-phone me-2"></i>+254 700 123 456</p>
                </div>
                <div class="col-md-6">
                    <p><i class="fas fa-map-marker-alt me-2"></i>Nairobi, Kenya</p>
                    <p><i class="fas fa-clock me-2"></i>Monday - Friday: 8:00 AM - 6:00 PM</p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
