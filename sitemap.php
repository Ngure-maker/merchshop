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
    <title>Sitemap - SmartSchool Uniforms</title>
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
        
        .sitemap-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .sitemap-header {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        /* Promotional Banner Styles */
        .promo-banner-container {
            margin-bottom: 2rem;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .promo-carousel {
            height: 300px;
            position: relative;
            overflow: hidden;
        }
        
        .promo-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }
        
        .promo-slide.active {
            opacity: 1;
        }
        
        .promo-slide.back-to-school {
            background: linear-gradient(135deg, #FF6B35, #E85A2C);
        }
        
        .promo-slide.free-shipping {
            background: linear-gradient(135deg, #00897B, #00695C);
        }
        
        .promo-slide.uniform-bundle {
            background: linear-gradient(135deg, #1976D2, #1565C0);
        }
        
        .promo-slide.stationery-deal {
            background: linear-gradient(135deg, #7B1FA2, #6A1B9A);
        }
        
        .promo-content {
            max-width: 800px;
            padding: 2rem;
        }
        
        .promo-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            animation: slideInDown 1s ease-out;
        }
        
        .promo-subtitle {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }
        
        .promo-cta {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: rgba(255,255,255,0.2);
            border: 2px solid white;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .promo-cta:hover {
            background: white;
            color: var(--primary-amber);
            transform: translateY(-2px);
        }
        
        .promo-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }
        
        .promo-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .promo-indicator.active {
            background: white;
            transform: scale(1.2);
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .sitemap-category {
            margin-bottom: 2rem;
        }
        
        .sitemap-category h4 {
            color: var(--primary-dark);
            border-bottom: 2px solid var(--primary-amber);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .sitemap-links {
            list-style: none;
            padding: 0;
        }
        
        .sitemap-links li {
            margin-bottom: 0.5rem;
        }
        
        .sitemap-links a {
            color: var(--neutral-gray);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .sitemap-links a:hover {
            color: var(--primary-amber);
            transform: translateX(5px);
        }
        
        .sitemap-links a i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .priority-link {
            font-weight: 600;
            color: var(--primary-dark) !important;
        }
        
        .sitemap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .breadcrumb-sitemap {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .breadcrumb-sitemap .breadcrumb {
            background: transparent;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <main class="container my-5">
        <div class="breadcrumb-sitemap">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sitemap</li>
                </ol>
            </nav>
        </div>
        
        <!-- Rotating Promotional Banners -->
        <div class="promo-banner-container">
            <div class="promo-carousel">
                <!-- Banner 1: Back to School -->
                <div class="promo-slide back-to-school active">
                    <div class="promo-content">
                        <h2 class="promo-title">
                            <i class="fas fa-graduation-cap me-3"></i>Back to School Sale
                        </h2>
                        <p class="promo-subtitle">Get 30% off on complete uniform sets for primary and secondary students</p>
                        <a href="catalog.php?category=uniforms" class="promo-cta">Shop Now <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
                
                <!-- Banner 2: Free Shipping -->
                <div class="promo-slide free-shipping">
                    <div class="promo-content">
                        <h2 class="promo-title">
                            <i class="fas fa-truck me-3"></i>Free Shipping Week
                        </h2>
                        <p class="promo-subtitle">Enjoy free delivery on all orders above KES 2,000 - nationwide coverage</p>
                        <a href="catalog.php" class="promo-cta">Start Shopping <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
                
                <!-- Banner 3: Uniform Bundle -->
                <div class="promo-slide uniform-bundle">
                    <div class="promo-content">
                        <h2 class="promo-title">
                            <i class="fas fa-box me-3"></i>Uniform Bundle Deal
                        </h2>
                        <p class="promo-subtitle">Buy 3 uniforms and get 1 free - Perfect for the entire school year</p>
                        <a href="catalog.php?category=uniforms" class="promo-cta">View Bundles <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
                
                <!-- Banner 4: Stationery Deal -->
                <div class="promo-slide stationery-deal">
                    <div class="promo-content">
                        <h2 class="promo-title">
                            <i class="fas fa-pencil-alt me-3"></i>Stationery Special
                        </h2>
                        <p class="promo-subtitle">Buy 2 stationery items and get the 3rd at 50% off - Stock up now!</p>
                        <a href="catalog.php?category=stationery" class="promo-cta">Browse Stationery <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
                
                <!-- Carousel Indicators -->
                <div class="promo-indicators">
                    <div class="promo-indicator active" data-slide="0"></div>
                    <div class="promo-indicator" data-slide="1"></div>
                    <div class="promo-indicator" data-slide="2"></div>
                    <div class="promo-indicator" data-slide="3"></div>
                </div>
            </div>
        </div>
        
        <div class="sitemap-header">
            <h1 class="mb-3"><i class="fas fa-sitemap me-3"></i>Sitemap</h1>
            <p class="mb-0">Navigate easily through all pages of SmartSchool Uniforms</p>
        </div>
        
        <div class="sitemap-section">
            <h3 class="mb-4"><i class="fas fa-store me-2"></i>Main Pages</h3>
            <div class="sitemap-grid">
                <div class="sitemap-category">
                    <h4>Home & Shop</h4>
                    <ul class="sitemap-links">
                        <li><a href="index.php" class="priority-link"><i class="fas fa-home"></i>Home</a></li>
                        <li><a href="catalog.php"><i class="fas fa-shopping-bag"></i>Catalog</a></li>
                        <li><a href="categories.php"><i class="fas fa-th-large"></i>All Categories</a></li>
                        <li><a href="search.php"><i class="fas fa-search"></i>Search Products</a></li>
                        <li><a href="deals.php"><i class="fas fa-tag"></i>Special Deals</a></li>
                        <li><a href="new_arrivals.php"><i class="fas fa-sparkles"></i>New Arrivals</a></li>
                    </ul>
                </div>
                
                <div class="sitemap-category">
                    <h4>Product Categories</h4>
                    <ul class="sitemap-links">
                        <li><a href="category.php?id=1"><i class="fas fa-tshirt"></i>Shirts</a></li>
                        <li><a href="category.php?id=3"><i class="fas fa-user-tie"></i>Dresses</a></li>
                        <li><a href="category.php?id=2"><i class="fas fa-socks"></i>Trousers</a></li>
                        <li><a href="category.php?id=4"><i class="fas fa-mitten"></i>Sweaters</a></li>
                        <li><a href="category.php?id=8"><i class="fas fa-shoe-prints"></i>School Shoes</a></li>
                        <li><a href="category.php?id=6"><i class="fas fa-running"></i>PE Kits</a></li>
                        <li><a href="category.php?id=5"><i class="fas fa-bow-tie"></i>Accessories</a></li>
                        <li><a href="category.php?id=9"><i class="fas fa-backpack"></i>School Bags</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="sitemap-section">
            <h3 class="mb-4"><i class="fas fa-user me-2"></i>Customer Account</h3>
            <div class="sitemap-grid">
                <div class="sitemap-category">
                    <h4>Account Management</h4>
                    <ul class="sitemap-links">
                        <li><a href="login.php" class="priority-link"><i class="fas fa-sign-in-alt"></i>Login</a></li>
                        <li><a href="register.php"><i class="fas fa-user-plus"></i>Register</a></li>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>My Dashboard</a></li>
                        <li><a href="profile.php"><i class="fas fa-user-edit"></i>Edit Profile</a></li>
                        <li><a href="change_password.php"><i class="fas fa-key"></i>Change Password</a></li>
                        <li><a href="wishlist.php"><i class="fas fa-heart"></i>My Wishlist</a></li>
                    </ul>
                </div>
                
                <div class="sitemap-category">
                    <h4>Orders & Shopping</h4>
                    <ul class="sitemap-links">
                        <li><a href="cart.php" class="priority-link"><i class="fas fa-shopping-cart"></i>Shopping Cart</a></li>
                        <li><a href="checkout.php"><i class="fas fa-credit-card"></i>Checkout</a></li>
                        <li><a href="order_history.php"><i class="fas fa-history"></i>Order History</a></li>
                        <li><a href="track_order.php"><i class="fas fa-truck"></i>Track Order</a></li>
                        <li><a href="returns.php"><i class="fas fa-undo"></i>Returns & Exchanges</a></li>
                        <li><a href="payment_methods.php"><i class="fas fa-credit-card"></i>Payment Methods</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="sitemap-section">
            <h3 class="mb-4"><i class="fas fa-cog me-2"></i>Services & Support</h3>
            <div class="sitemap-grid">
                <div class="sitemap-category">
                    <h4>Customer Service</h4>
                    <ul class="sitemap-links">
                        <li><a href="help_center.php" class="priority-link"><i class="fas fa-question-circle"></i>Help Center</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope"></i>Contact Us</a></li>
                        <li><a href="faq.php"><i class="fas fa-comments"></i>FAQ</a></li>
                        <li><a href="size_guide.php"><i class="fas fa-ruler"></i>Size Guide</a></li>
                        <li><a href="shipping_info.php"><i class="fas fa-truck"></i>Shipping Information</a></li>
                        <li><a href="payment_info.php"><i class="fas fa-money-bill"></i>Payment Options</a></li>
                    </ul>
                </div>
                
                <div class="sitemap-category">
                    <h4>Special Services</h4>
                    <ul class="sitemap-links">
                        <li><a href="bulk_orders.php"><i class="fas fa-users"></i>Bulk Orders</a></li>
                        <li><a href="school_programs.php"><i class="fas fa-graduation-cap"></i>School Programs</a></li>
                        <li><a href="custom_uniforms.php"><i class="fas fa-cut"></i>Custom Uniforms</a></li>
                        <li><a href="gift_cards.php"><i class="fas fa-gift"></i>Gift Cards</a></li>
                        <li><a href="referral_program.php"><i class="fas fa-share-alt"></i>Referral Program</a></li>
                        <li><a href="loyalty_program.php"><i class="fas fa-award"></i>Loyalty Program</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="sitemap-section">
            <h3 class="mb-4"><i class="fas fa-info-circle me-2"></i>Information</h3>
            <div class="sitemap-grid">
                <div class="sitemap-category">
                    <h4>About Us</h4>
                    <ul class="sitemap-links">
                        <li><a href="about.php" class="priority-link"><i class="fas fa-info"></i>About SmartSchool</a></li>
                        <li><a href="our_story.php"><i class="fas fa-book"></i>Our Story</a></li>
                        <li><a href="mission_vision.php"><i class="fas fa-bullseye"></i>Mission & Vision</a></li>
                        <li><a href="our_team.php"><i class="fas fa-users"></i>Our Team</a></li>
                        <li><a href="careers.php"><i class="fas fa-briefcase"></i>Careers</a></li>
                        <li><a href="press_media.php"><i class="fas fa-newspaper"></i>Press & Media</a></li>
                    </ul>
                </div>
                
                <div class="sitemap-category">
                    <h4>Policies & Legal</h4>
                    <ul class="sitemap-links">
                        <li><a href="privacy_policy.php" class="priority-link"><i class="fas fa-shield-alt"></i>Privacy Policy</a></li>
                        <li><a href="terms_of_service.php"><i class="fas fa-file-contract"></i>Terms of Service</a></li>
                        <li><a href="cookie_policy.php"><i class="fas fa-cookie-bite"></i>Cookie Policy</a></li>
                        <li><a href="return_policy.php"><i class="fas fa-undo"></i>Return Policy</a></li>
                        <li><a href="shipping_policy.php"><i class="fas fa-truck"></i>Shipping Policy</a></li>
                        <li><a href="refund_policy.php"><i class="fas fa-money-bill"></i>Refund Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="sitemap-section">
            <h3 class="mb-4"><i class="fas fa-tools me-2"></i>Tools & Resources</h3>
            <div class="sitemap-grid">
                <div class="sitemap-category">
                    <h4>Shopping Tools</h4>
                    <ul class="sitemap-links">
                        <li><a href="size_calculator.php"><i class="fas fa-calculator"></i>Size Calculator</a></li>
                        <li><a href="outfit_builder.php"><i class="fas fa-palette"></i>Outfit Builder</a></li>
                        <li><a href="wishlist_sharing.php"><i class="fas fa-share"></i>Share Wishlist</a></li>
                        <li><a href="price_comparison.php"><i class="fas fa-balance-scale"></i>Price Comparison</a></li>
                        <li><a href="stock_alerts.php"><i class="fas fa-bell"></i>Stock Alerts</a></li>
                        <li><a href="product_reviews.php"><i class="fas fa-star"></i>Product Reviews</a></li>
                    </ul>
                </div>
                
                <div class="sitemap-category">
                    <h4>Educational Resources</h4>
                    <ul class="sitemap-links">
                        <li><a href="uniform_guide.php"><i class="fas fa-book-open"></i>Uniform Guide</a></li>
                        <li><a href="school_requirements.php"><i class="fas fa-list-check"></i>School Requirements</a></li>
                        <li><a href="care_instructions.php"><i class="fas fa-soap"></i>Care Instructions</a></li>
                        <li><a href="blog.php"><i class="fas fa-blog"></i>Blog</a></li>
                        <li><a href="parent_resources.php"><i class="fas fa-users"></i>Parent Resources</a></li>
                        <li><a href="student_resources.php"><i class="fas fa-graduation-cap"></i>Student Resources</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="sitemap-section">
            <h3 class="mb-4"><i class="fas fa-mobile-alt me-2"></i>Mobile & Apps</h3>
            <div class="sitemap-grid">
                <div class="sitemap-category">
                    <h4>Mobile Applications</h4>
                    <ul class="sitemap-links">
                        <li><a href="mobile_applications.php" class="priority-link"><i class="fas fa-mobile"></i>Mobile App</a></li>
                        <li><a href="download_app.php"><i class="fas fa-download"></i>Download App</a></li>
                        <li><a href="app_features.php"><i class="fas fa-star"></i>App Features</a></li>
                        <li><a href="mobile_support.php"><i class="fas fa-headset"></i>Mobile Support</a></li>
                    </ul>
                </div>
                
                <div class="sitemap-category">
                    <h4>Social Media</h4>
                    <ul class="sitemap-links">
                        <li><a href="https://facebook.com/smartschool" target="_blank"><i class="fab fa-facebook"></i>Facebook</a></li>
                        <li><a href="https://twitter.com/smartschool" target="_blank"><i class="fab fa-twitter"></i>Twitter</a></li>
                        <li><a href="https://instagram.com/smartschool" target="_blank"><i class="fab fa-instagram"></i>Instagram</a></li>
                        <li><a href="https://youtube.com/smartschool" target="_blank"><i class="fab fa-youtube"></i>YouTube</a></li>
                        <li><a href="https://linkedin.com/smartschool" target="_blank"><i class="fab fa-linkedin"></i>LinkedIn</a></li>
                        <li><a href="https://whatsapp.com/smartschool" target="_blank"><i class="fab fa-whatsapp"></i>WhatsApp</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="sitemap-section">
            <h3 class="mb-4"><i class="fas fa-exclamation-triangle me-2"></i>Quick Links</h3>
            <div class="row">
                <div class="col-md-6">
                    <ul class="sitemap-links">
                        <li><a href="emergency_contacts.php" class="priority-link"><i class="fas fa-phone-alt"></i>Emergency Contacts</a></li>
                        <li><a href="sitemap.php"><i class="fas fa-map"></i>Site Map</a></li>
                        <li><a href="accessibility.php"><i class="fas fa-universal-access"></i>Accessibility</a></li>
                        <li><a href="language_settings.php"><i class="fas fa-language"></i>Language Settings</a></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="sitemap-links">
                        <li><a href="rss_feed.php"><i class="fas fa-rss"></i>RSS Feed</a></li>
                        <li><a href="newsletter.php"><i class="fas fa-envelope"></i>Newsletter</a></li>
                        <li><a href="api_documentation.php"><i class="fas fa-code"></i>API Documentation</a></li>
                        <li><a href="developers.php"><i class="fas fa-laptop-code"></i>Developers</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Can't find what you're looking for?</strong> 
                <a href="contact.php" class="alert-link">Contact our support team</a> or use the 
                <a href="search.php" class="alert-link">search function</a> to find specific content.
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling to all links
        document.querySelectorAll('.sitemap-links a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Add a subtle animation effect
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 200);
            });
        });
        
        // Promotional Banner Carousel
        class PromoCarousel {
            constructor() {
                this.slides = document.querySelectorAll('.promo-slide');
                this.indicators = document.querySelectorAll('.promo-indicator');
                this.currentSlide = 0;
                this.autoPlayInterval = null;
                this.autoPlayDelay = 4000; // 4 seconds
                
                this.init();
            }
            
            init() {
                // Start auto-play
                this.startAutoPlay();
                
                // Add click handlers to indicators
                this.indicators.forEach((indicator, index) => {
                    indicator.addEventListener('click', () => {
                        this.goToSlide(index);
                        this.resetAutoPlay();
                    });
                });
                
                // Pause on hover
                const carousel = document.querySelector('.promo-carousel');
                carousel.addEventListener('mouseenter', () => this.stopAutoPlay());
                carousel.addEventListener('mouseleave', () => this.startAutoPlay());
            }
            
            goToSlide(slideIndex) {
                // Hide current slide
                this.slides[this.currentSlide].classList.remove('active');
                this.indicators[this.currentSlide].classList.remove('active');
                
                // Show new slide
                this.currentSlide = slideIndex;
                this.slides[this.currentSlide].classList.add('active');
                this.indicators[this.currentSlide].classList.add('active');
            }
            
            nextSlide() {
                const nextIndex = (this.currentSlide + 1) % this.slides.length;
                this.goToSlide(nextIndex);
            }
            
            startAutoPlay() {
                this.stopAutoPlay(); // Clear any existing interval
                this.autoPlayInterval = setInterval(() => this.nextSlide(), this.autoPlayDelay);
            }
            
            stopAutoPlay() {
                if (this.autoPlayInterval) {
                    clearInterval(this.autoPlayInterval);
                    this.autoPlayInterval = null;
                }
            }
            
            resetAutoPlay() {
                this.stopAutoPlay();
                this.startAutoPlay();
            }
        }
        
        // Initialize carousel when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            new PromoCarousel();
        });
    </script>
</body>
</html>
