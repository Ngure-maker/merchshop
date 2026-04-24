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
    <title>Terms of Service - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: rgb(28, 29, 60);
            --primary-dark: #E85A2C;
            --secondary-teal: #00897B;
            --secondary-blue: #1976D2;
            --accent-purple: #7B1FA2;
            --accent-green: #388E3C;
            --neutral-gray: #546E7A;
            --light-bg: #FFF8E1;
        }
        
        .terms-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .terms-header {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .terms-item {
            border-left: 4px solid var(--primary-amber);
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .terms-item h5 {
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        .highlight-box {
            background: var(--light-bg);
            border-left: 4px solid var(--primary-amber);
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <main class="container my-5">
        <div class="terms-header">
            <h1 class="mb-3"><i class="fas fa-file-contract me-3"></i>Terms of Service</h1>
            <p class="mb-0">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">Agreement to Terms</h3>
            <p>By accessing and using SmartSchool Uniforms, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">Products and Services</h3>
            
            <div class="terms-item">
                <h5>Product Availability</h5>
                <p>All products are subject to availability. We reserve the right to discontinue any products at any time. Product descriptions and prices are subject to change without notice.</p>
            </div>
            
            <div class="terms-item">
                <h5>Pricing</h5>
                <p>All prices are displayed in Kenyan Shillings (KSh) and are inclusive of applicable taxes unless otherwise stated. We reserve the right to change prices at any time without prior notice.</p>
            </div>
            
            <div class="terms-item">
                <h5>Product Images</h5>
                <p>We make every effort to display as accurately as possible the colors, features, specifications, and details of the products available. However, we do not guarantee that the colors, features, specifications, and details of the products will be accurate, complete, reliable, current, or free of other errors.</p>
            </div>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">Orders and Payment</h3>
            
            <div class="terms-item">
                <h5>Order Acceptance</h5>
                <p>Your receipt of an electronic order confirmation does not signify our acceptance of your order, nor does it constitute confirmation of our offer to sell.</p>
            </div>
            
            <div class="terms-item">
                <h5>Payment Methods</h5>
                <p>We accept various payment methods including M-Pesa, credit/debit cards, and bank transfers. Payment must be received before orders are processed and shipped.</p>
            </div>
            
            <div class="terms-item">
                <h5>Order Cancellation</h5>
                <p>You may cancel your order within 24 hours of placement. After this period, cancellation may not be possible if the order has already been processed.</p>
            </div>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">Shipping and Delivery</h3>
            
            <div class="terms-item">
                <h5>Delivery Times</h5>
                <p>Standard delivery typically takes 3-5 business days within Nairobi metropolitan area and 5-7 business days for other regions. Delivery times are estimates and not guaranteed.</p>
            </div>
            
            <div class="terms-item">
                <h5>Shipping Costs</h5>
                <p>Shipping costs vary based on location and order size. Free shipping is available for orders above KSh 5,000 within Nairobi.</p>
            </div>
            
            <div class="terms-item">
                <h5>Delivery Issues</h5>
                <p>We are not responsible for delays or failures in delivery caused by events beyond our reasonable control, including but not limited to acts of God, strikes, or severe weather conditions.</p>
            </div>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">Returns and Refunds</h3>
            
            <div class="highlight-box">
                <h6><i class="fas fa-info-circle me-2"></i>Return Policy</h6>
                <p class="mb-0">You may return unused items in their original packaging within 7 days of delivery for a full refund or exchange. Shipping costs for returns are the responsibility of the customer unless the return is due to our error.</p>
            </div>
            
            <div class="terms-item">
                <h5>Non-Returnable Items</h5>
                <p>Customized uniforms, undergarments, and items marked as final sale cannot be returned unless defective.</p>
            </div>
            
            <div class="terms-item">
                <h5>Refund Processing</h5>
                <p>Refunds are processed within 5-7 business days after we receive the returned items. Refunds will be issued to the original payment method.</p>
            </div>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">User Accounts</h3>
            
            <div class="terms-item">
                <h5>Account Responsibilities</h5>
                <p>You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account.</p>
            </div>
            
            <div class="terms-item">
                <h5>Account Termination</h5>
                <p>We reserve the right to suspend or terminate your account if you violate these terms or engage in fraudulent activities.</p>
            </div>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">Intellectual Property</h3>
            <p>All content included on this site, such as text, graphics, logos, images, and software, is the property of SmartSchool Uniforms and protected by international copyright and trademark laws.</p>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">Limitation of Liability</h3>
            <p>SmartSchool Uniforms shall not be liable for any indirect, incidental, special, or consequential damages resulting from your use of our services or products.</p>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">Changes to Terms</h3>
            <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting on our website. Your continued use of our services constitutes acceptance of any changes.</p>
        </div>
        
        <div class="terms-section">
            <h3 class="mb-4">Contact Information</h3>
            <p>If you have any questions about these Terms of Service, please contact us:</p>
            <div class="row">
                <div class="col-md-6">
                    <p><i class="fas fa-envelope me-2"></i>legal@smartschool.com</p>
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
