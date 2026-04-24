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
    <title>FAQ - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-teal) 100%);
            color: white;
            padding: 4rem 0;
        }
        
        .card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-primary mb-3">
                    <i class="fas fa-question-circle me-2"></i>Frequently Asked Questions
                </h1>
                <p class="lead text-muted">Find answers to common questions about our products and services</p>
            </div>

            <!-- FAQ Categories -->
            <div class="row mb-4">
                <div class="col-md-3 col-6 mb-3">
                    <button class="btn btn-outline-primary w-100 category-btn" data-category="general">
                        <i class="fas fa-info-circle me-2"></i>General
                    </button>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <button class="btn btn-outline-primary w-100 category-btn" data-category="orders">
                        <i class="fas fa-shopping-cart me-2"></i>Orders
                    </button>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <button class="btn btn-outline-primary w-100 category-btn" data-category="payment">
                        <i class="fas fa-credit-card me-2"></i>Payment
                    </button>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <button class="btn btn-outline-primary w-100 category-btn" data-category="shipping">
                        <i class="fas fa-truck me-2"></i>Shipping
                    </button>
                </div>
            </div>

            <!-- FAQ Accordion -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="accordion" id="faqAccordion">
                        
                        <!-- General FAQs -->
                        <div class="faq-category" data-category="general">
                            <h3 class="text-primary mb-3">General Questions</h3>
                            
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        What is SmartSchool Uniforms?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        SmartSchool Uniforms is Kenya's leading online store for quality school uniforms and educational supplies. We provide affordable, high-quality uniforms, books, stationery, and other school essentials to students across Kenya.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Do you ship to all counties in Kenya?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Yes! We deliver to all 47 counties in Kenya. Delivery times may vary depending on your location, but we ensure every corner of Kenya receives our quality products.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        How can I track my order?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Once your order is shipped, you'll receive a tracking number via email and SMS. You can use this number on our website or call our customer service to track your order status.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order FAQs -->
                        <div class="faq-category" data-category="orders" style="display: none;">
                            <h3 class="text-primary mb-3">Order Related Questions</h3>
                            
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        How do I place an order?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Simply browse our catalog, select the items you need, choose sizes and quantities, add to cart, and proceed to checkout. You can create an account or checkout as a guest.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                        Can I modify or cancel my order?
                                    </button>
                                </h2>
                                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        You can modify or cancel your order within 2 hours of placing it. After that, please contact our customer service immediately, and we'll do our best to accommodate your request.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                        What if an item is out of stock?
                                    </button>
                                </h2>
                                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        If an item is out of stock, you can sign up for restock notifications. We'll email you as soon as the item is available again. Popular items are usually restocked within 3-5 days.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment FAQs -->
                        <div class="faq-category" data-category="payment" style="display: none;">
                            <h3 class="text-primary mb-3">Payment Questions</h3>
                            
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                        What payment methods do you accept?
                                    </button>
                                </h2>
                                <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        We accept M-Pesa, Airtel Money, Visa, Mastercard, PayPal, and cash on delivery (Nairobi only). All transactions are secure and encrypted.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                        Is M-Pesa payment safe?
                                    </button>
                                </h2>
                                <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Yes, M-Pesa payments are completely safe. We use Safaricom's official API and all transactions are encrypted. You'll receive confirmation from both M-Pesa and SmartSchool.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                        Do you offer payment plans?
                                    </button>
                                </h2>
                                <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Yes, we offer flexible payment plans for bulk orders (10+ items). You can pay in 2-3 installments. Contact our sales team for more information about payment plans.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping FAQs -->
                        <div class="faq-category" data-category="shipping" style="display: none;">
                            <h3 class="text-primary mb-3">Shipping & Delivery</h3>
                            
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                        How long does delivery take?
                                    </button>
                                </h2>
                                <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Nairobi: 1-2 working days<br>
                                        Major towns: 3-4 working days<br>
                                        Other counties: 5-7 working days<br>
                                        Express delivery available for urgent orders.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq11">
                                        How much does shipping cost?
                                    </button>
                                </h2>
                                <div id="faq11" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Shipping costs vary by location and order size:<br>
                                        Nairobi: KES 150 (orders over KES 3000 = free)<br>
                                        Major towns: KES 250 (orders over KES 5000 = free)<br>
                                        Other counties: KES 350 (orders over KES 7000 = free)
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq12">
                                        Can I pick up my order?
                                    </button>
                                </h2>
                                <div id="faq12" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Yes! You can pick up your order from our main office in Nairobi (Moi Avenue) at no extra cost. Select "Pickup" during checkout and we'll notify you when your order is ready.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still Need Help -->
            <div class="text-center mt-5">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <h3 class="mb-3">Still Have Questions?</h3>
                        <p class="mb-4">Can't find what you're looking for? Our customer service team is here to help!</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="contact.php" class="btn btn-light btn-lg">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </a>
                            <a href="tel:+254712345678" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-mobile-alt me-2"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryButtons = document.querySelectorAll('.category-btn');
    const faqCategories = document.querySelectorAll('.faq-category');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Remove active class from all buttons
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Hide all categories
            faqCategories.forEach(cat => cat.style.display = 'none');
            // Show selected category
            document.querySelector(`.faq-category[data-category="${category}"]`).style.display = 'block';
        });
    });
    
    // Show general category by default
    document.querySelector('.category-btn[data-category="general"]').classList.add('active');
});
</script>
</body>
</html>


