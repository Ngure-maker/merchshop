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
    <title>Cookie Policy - SmartSchool Uniforms</title>
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
        
        .cookie-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .cookie-header {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .cookie-item {
            border-left: 4px solid var(--primary-amber);
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .cookie-item h5 {
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        .cookie-type {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-amber);
        }
        
        .cookie-settings {
            background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
        }
        
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
            background: #ccc;
            border-radius: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .toggle-switch.active {
            background: var(--primary-amber);
        }
        
        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 26px;
            height: 26px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: transform 0.3s;
        }
        
        .toggle-switch.active::after {
            transform: translateX(30px);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <main class="container my-5">
        <div class="cookie-header">
            <h1 class="mb-3"><i class="fas fa-cookie-bite me-3"></i>Cookie Policy</h1>
            <p class="mb-0">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
        
        <div class="cookie-section">
            <h3 class="mb-4">What Are Cookies?</h3>
            <p>Cookies are small text files that are stored on your device when you visit our website. They help us improve your experience by remembering your preferences and providing personalized content.</p>
        </div>
        
        <div class="cookie-section">
            <h3 class="mb-4">How We Use Cookies</h3>
            
            <div class="cookie-item">
                <h5>Essential Cookies</h5>
                <p>These cookies are necessary for the website to function and cannot be switched off in our systems. They are usually only set in response to actions made by you which amount to a request for services.</p>
            </div>
            
            <div class="cookie-item">
                <h5>Performance Cookies</h5>
                <p>These cookies allow us to count visits and traffic sources so we can measure and improve the performance of our site. They help us to know which pages are the most and least popular and see how visitors move around the site.</p>
            </div>
            
            <div class="cookie-item">
                <h5>Functional Cookies</h5>
                <p>These cookies enable the website to provide enhanced functionality and personalization. They may be set by us or by third party providers whose services we have added to our pages.</p>
            </div>
            
            <div class="cookie-item">
                <h5>Targeting Cookies</h5>
                <p>These cookies may be set through our site by our advertising partners. They may be used by those companies to build a profile of your interests and show you relevant adverts on other sites.</p>
            </div>
        </div>
        
        <div class="cookie-section">
            <h3 class="mb-4">Types of Cookies We Use</h3>
            
            <div class="cookie-type">
                <h5><i class="fas fa-lock me-2 text-success"></i>Strictly Necessary Cookies</h5>
                <p class="mb-2"><strong>Purpose:</strong> Essential for the website to function properly</p>
                <p class="mb-2"><strong>Examples:</strong> Login authentication, shopping cart contents, security tokens</p>
                <p class="mb-0"><strong>Duration:</strong> Session or persistent</p>
            </div>
            
            <div class="cookie-type">
                <h5><i class="fas fa-chart-line me-2 text-info"></i>Performance Cookies</h5>
                <p class="mb-2"><strong>Purpose:</strong> Collect information about how visitors use our website</p>
                <p class="mb-2"><strong>Examples:</strong> Google Analytics, page load times, user behavior tracking</p>
                <p class="mb-0"><strong>Duration:</strong> Persistent (typically 1-2 years)</p>
            </div>
            
            <div class="cookie-type">
                <h5><i class="fas fa-cog me-2 text-warning"></i>Functional Cookies</h5>
                <p class="mb-2"><strong>Purpose:</strong> Remember your preferences and choices</p>
                <p class="mb-2"><strong>Examples:</strong> Language preferences, product filters, saved shopping lists</p>
                <p class="mb-0"><strong>Duration:</strong> Persistent (typically 1 year)</p>
            </div>
            
            <div class="cookie-type">
                <h5><i class="fas fa-bullseye me-2 text-danger"></i>Marketing Cookies</h5>
                <p class="mb-2"><strong>Purpose:</strong> Track your online activity to deliver relevant advertising</p>
                <p class="mb-2"><strong>Examples:</strong> Social media cookies, retargeting pixels, affiliate tracking</p>
                <p class="mb-0"><strong>Duration:</strong> Persistent (typically 6 months to 2 years)</p>
            </div>
        </div>
        
        <div class="cookie-settings">
            <h3 class="mb-4"><i class="fas fa-sliders-h me-2"></i>Cookie Preferences</h3>
            <p class="mb-4">You can control which cookies are set on your device through your browser settings or by using the options below:</p>
            
            <div class="row align-items-center mb-3">
                <div class="col-md-8">
                    <h6 class="mb-1">Essential Cookies</h6>
                    <small class="text-muted">Required for the website to function</small>
                </div>
                <div class="col-md-4 text-end">
                    <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
                </div>
            </div>
            
            <div class="row align-items-center mb-3">
                <div class="col-md-8">
                    <h6 class="mb-1">Performance Cookies</h6>
                    <small class="text-muted">Help us improve our website</small>
                </div>
                <div class="col-md-4 text-end">
                    <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
                </div>
            </div>
            
            <div class="row align-items-center mb-3">
                <div class="col-md-8">
                    <h6 class="mb-1">Functional Cookies</h6>
                    <small class="text-muted">Remember your preferences</small>
                </div>
                <div class="col-md-4 text-end">
                    <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
                </div>
            </div>
            
            <div class="row align-items-center mb-3">
                <div class="col-md-8">
                    <h6 class="mb-1">Marketing Cookies</h6>
                    <small class="text-muted">Show you relevant advertisements</small>
                </div>
                <div class="col-md-4 text-end">
                    <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button class="btn btn-primary btn-lg" onclick="saveCookiePreferences()">
                    <i class="fas fa-save me-2"></i>Save Preferences
                </button>
            </div>
        </div>
        
        <div class="cookie-section">
            <h3 class="mb-4">Managing Cookies</h3>
            
            <div class="cookie-item">
                <h5>Browser Settings</h5>
                <p>Most web browsers allow you to control cookies through their settings. You can:</p>
                <ul>
                    <li>Accept or reject cookies</li>
                    <li>Delete existing cookies</li>
                    <li>Block third-party cookies</li>
                    <li>Set notifications when cookies are set</li>
                </ul>
            </div>
            
            <div class="cookie-item">
                <h5>Mobile Devices</h5>
                <p>Cookie settings on mobile devices can be managed through the device's browser settings or application preferences.</p>
            </div>
            
            <div class="cookie-item">
                <h5>Third-Party Cookies</h5>
                <p>Some cookies on our website are set by third-party services. You can manage these cookies by visiting the respective third-party websites or using their opt-out tools.</p>
            </div>
        </div>
        
        <div class="cookie-section">
            <h3 class="mb-4">Cookie Updates</h3>
            <p>We may update this Cookie Policy from time to time to reflect changes in our use of cookies or in relevant laws. We encourage you to review this policy periodically for the latest information.</p>
        </div>
        
        <div class="cookie-section">
            <h3 class="mb-4">Contact Us</h3>
            <p>If you have any questions about our Cookie Policy, please contact us:</p>
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
    <script>
        function saveCookiePreferences() {
            // Show success message
            alert('Your cookie preferences have been saved successfully!');
            
            // In a real implementation, this would save preferences to localStorage
            // and potentially send them to the server
            localStorage.setItem('cookiePreferences', 'saved');
        }
    </script>
</body>
</html>
