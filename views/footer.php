<?php
require_once __DIR__ . '/../includes/language.php';
require_once __DIR__ . '/../includes/settings.php';

$site_name = (string)getSetting('site_name', 'Merch Shop');
$footer_text = (string)getSetting('footer_text', '');
$facebook_url = (string)getSetting('facebook_url', '#');
$instagram_url = (string)getSetting('instagram_url', '#');
$twitter_url = (string)getSetting('twitter_url', '#');
$whatsapp_number = (string)getSetting('whatsapp_number', '');
$whatsapp_url = $whatsapp_number !== '' ? ('https://wa.me/' . preg_replace('/\D+/', '', $whatsapp_number)) : '#';
$special_offers_title = (string)getSetting('special_offers_title', 'Special Offers & Deals');
$special_offers_subtitle = (string)getSetting('special_offers_subtitle', 'Amazing discounts and special promotions just for you!');
?>
<footer class="bg-gradient-to-dark text-light mt-5">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="mb-3 footer-heading">
                    <i class="fas fa-graduation-cap me-2"></i><?php echo htmlspecialchars($site_name); ?>
                </h5>
                <?php if (trim($footer_text) !== ''): ?>
                    <p class="text-light"><?php echo htmlspecialchars($footer_text); ?></p>
                <?php else: ?>
                    <p class="text-light">Your trusted partner for quality school uniforms and educational supplies. Making school shopping easier for parents and students across Kenya.</p>
                <?php endif; ?>
                <div class="social-links mb-3">
                    <a href="<?php echo htmlspecialchars($facebook_url); ?>" class="text-light me-3 social-icon" title="Facebook" target="_blank" rel="noopener">
                        <i class="fab fa-facebook fa-lg"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars($twitter_url); ?>" class="text-light me-3 social-icon" title="Twitter" target="_blank" rel="noopener">
                        <i class="fab fa-twitter fa-lg"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars($instagram_url); ?>" class="text-light me-3 social-icon" title="Instagram" target="_blank" rel="noopener">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars($whatsapp_url); ?>" class="text-light social-icon" title="WhatsApp" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp fa-lg"></i>
                    </a>
                </div>
                <div class="payment-methods">
                    <small class="text-light d-block mb-2">Payment Methods:</small>
                    <div class="d-flex gap-2">
                        <i class="fab fa-cc-visa fa-2x text-light opacity-75"></i>
                        <i class="fab fa-cc-mastercard fa-2x text-light opacity-75"></i>
                        <i class="fab fa-mpesa fa-2x text-light opacity-75"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 mb-4">
                <h6 class="mb-3 footer-heading">Shop</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="catalog.php" class="footer-link text-decoration-none">All Products</a></li>
                    <li class="mb-2"><a href="catalog.php?category=uniforms" class="footer-link text-decoration-none">Uniforms</a></li>
                    <li class="mb-2"><a href="catalog.php?category=books" class="footer-link text-decoration-none">Books</a></li>
                    <li class="mb-2"><a href="catalog.php?category=stationery" class="footer-link text-decoration-none">Stationery</a></li>
                    <li class="mb-2"><a href="deals.php" class="footer-link text-decoration-none">Special Offers</a></li>
                    <li class="mb-2"><a href="new_arrivals.php" class="footer-link text-decoration-none">New Arrivals</a></li>
                </ul>
            </div>
            
            <div class="col-md-2 mb-4">
                <h6 class="mb-3 footer-heading">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="about.php" class="footer-link text-decoration-none">About Us</a></li>
                    <li class="mb-2"><a href="contact.php" class="footer-link text-decoration-none">Contact</a></li>
                    <li class="mb-2"><a href="faq.php" class="footer-link text-decoration-none">FAQ</a></li>
                    <li class="mb-2"><a href="blog.php" class="footer-link text-decoration-none">Blog</a></li>
                    <li class="mb-2"><a href="careers.php" class="footer-link text-decoration-none">Careers</a></li>
                    <li class="mb-2"><a href="partners.php" class="footer-link text-decoration-none">Partners</a></li>
                </ul>
            </div>
            
            <div class="col-md-4 mb-4">
                <h6 class="mb-3 footer-heading">Customer Service</h6>
                <div class="row">
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="help_center.php" class="footer-link text-decoration-none">Help Center</a></li>
                            <li class="mb-2"><a href="shipping_info.php" class="footer-link text-decoration-none">Shipping Info</a></li>
                            <li class="mb-2"><a href="returns.php" class="footer-link text-decoration-none">Returns & Exchanges</a></li>
                            <li class="mb-2"><a href="size_guide.php" class="footer-link text-decoration-none">Size Guide</a></li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="order_history.php" class="footer-link text-decoration-none">Track Order</a></li>
                            <li class="mb-2"><a href="payment_options.php" class="footer-link text-decoration-none">Payment Options</a></li>
                            <li class="mb-2"><a href="gift_cards.php" class="footer-link text-decoration-none">Gift Cards</a></li>
                            <li class="mb-2"><a href="bulk_orders.php" class="footer-link text-decoration-none">Bulk Orders</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Newsletter Signup -->
                <div class="newsletter mt-3 p-3 bg-primary rounded">
                    <h6 class="text-white mb-2">Stay Updated</h6>
                    <p class="small text-light mb-2">Get exclusive offers and new product updates</p>
                    <form class="d-flex gap-2" action="newsletter_subscribe.php" method="POST">
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="Your email" required>
                        <button type="submit" class="btn btn-primary btn-sm">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
        
        <hr class="bg-secondary opacity-25">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-light mb-0">
                    <i class="fas fa-copyright me-1"></i> <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-links">
                    <a href="privacy_policy.php" class="footer-link text-decoration-none me-3"><?php echo t('footer_privacy'); ?></a>
                    <a href="terms_of_service.php" class="footer-link text-decoration-none me-3"><?php echo t('footer_terms'); ?></a>
                    <a href="cookie_policy.php" class="footer-link text-decoration-none me-3"><?php echo t('footer_cookie'); ?></a>
                    <a href="sitemap.php" class="footer-link text-decoration-none"><?php echo t('footer_sitemap'); ?></a>
                </div>
            </div>
        </div>
        
        <!-- Trust Badges -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <div class="trust-badges d-flex justify-content-center align-items-center gap-4 flex-wrap">
                    <a href="payment_options.php" class="trust-item text-decoration-none">
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        <small class="text-light">Secure Shopping</small>
                    </a>
                    <a href="shipping_info.php" class="trust-item text-decoration-none">
                        <i class="fas fa-truck text-info me-2"></i>
                        <small class="text-light">Fast Delivery</small>
                    </a>
                    <a href="returns.php" class="trust-item text-decoration-none">
                        <i class="fas fa-undo text-warning me-2"></i>
                        <small class="text-light">Easy Returns</small>
                    </a>
                    <a href="help_center.php" class="trust-item text-decoration-none">
                        <i class="fas fa-headset text-primary me-2"></i>
                        <small class="text-light">24/7 Support</small>
                    </a>
                </div>
            </div>
        </div>
        
</footer>

<style>
.bg-gradient-to-dark {
    background: linear-gradient(135deg, #2C3E50 0%, #1A252F 100%) !important;
}

.bg-primary-dark {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-rich-black)) !important;
}

.footer-heading {
    color: rgba(255, 255, 255, 0.95) !important;
    font-weight: 600;
}

.footer-link {
    color: rgba(255, 255, 255, 0.85) !important;
}

.footer-link:hover {
    color: #ffffff !important;
}

.text-primary {
    color: var(--primary-color) !important;
}

.social-icon {
    transition: all 0.3s ease;
}

.social-icon:hover {
    color: var(--primary-color) !important;
    transform: translateY(-2px);
}

.hover-primary:hover {
    color: var(--primary-color) !important;
}

.payment-methods i {
    transition: all 0.3s ease;
}

.payment-methods i:hover {
    opacity: 1 !important;
    transform: scale(1.1);
}

.newsletter .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(6, 25, 67, 0.25);
}

.newsletter .btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
    border: none;
}

.newsletter .btn-primary:hover {
    background: linear-gradient(135deg, var(--secondary-rich-black), var(--primary-color));
    transform: translateY(-1px);
}

.trust-item {
    transition: all 0.3s ease;
}

.trust-item:hover {
    transform: translateY(-2px);
}

.footer-links a {
    position: relative;
}

.footer-links a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background-color: var(--primary-color);
    transition: width 0.3s ease;
}

.footer-links a:hover::after {
    width: 100%;
}

/* Offer Cards Styling */
.offer-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.offer-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    border-color: var(--primary-color);
}

.offer-card .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.offer-card .btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
    border: none;
    font-size: 0.8rem;
    padding: 0.25rem 0.75rem;
}

.offer-card .btn-primary:hover {
    background: linear-gradient(135deg, var(--secondary-rich-black), var(--primary-color));
    transform: translateY(-1px);
}

.terms-section {
    border-left: 4px solid var(--primary-color);
}
</style>
