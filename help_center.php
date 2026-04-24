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
    <title>Help Center - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .help-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: 100%;
        }
        
        .help-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .help-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .search-box {
            background: white;
            border-radius: 50px;
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: none;
        }
        
        .search-box:focus {
            box-shadow: 0 4px 20px rgba(6, 25, 67, 0.3);
            border-color: var(--primary-color);
        }
        
        .category-pill {
            background: #f8f9fa;
            border: 2px solid transparent;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            margin: 0.25rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .category-pill:hover {
            background: var(--light-bg);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .category-pill.active {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            border-color: var(--primary-color);
        }
        
        .faq-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .faq-question {
            padding: 1rem 1.5rem;
            background: white;
            border: none;
            width: 100%;
            text-align: left;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background: var(--light-bg);
        }
        
        .faq-answer {
            padding: 0 1.5rem;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-answer.show {
            padding: 1rem 1.5rem;
            max-height: 500px;
        }
        
        .contact-option {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .contact-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-primary mb-3">
                    <i class="fas fa-headset me-2"></i>Help Center
                </h1>
                <p class="lead text-muted">We're here to help! Find answers, contact support, and get the assistance you need.</p>
            </div>

            <!-- Search Bar -->
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto">
                    <div class="input-group">
                        <input type="text" class="form-control search-box" placeholder="Search for help articles, FAQs, or topics...">
                        <button class="btn btn-primary px-4">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Help Categories -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-th-large me-2"></i>How can we help you?
                </h3>
                <div class="row">
                    <div class="col-md-3 col-6 mb-4">
                        <div class="help-card text-center p-4">
                            <div class="help-icon mx-auto">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h6 class="fw-bold">Orders</h6>
                            <p class="small text-muted">Track orders, returns, exchanges</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="help-card text-center p-4">
                            <div class="help-icon mx-auto">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h6 class="fw-bold">Payment</h6>
                            <p class="small text-muted">Payment methods, billing issues</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="help-card text-center p-4">
                            <div class="help-icon mx-auto">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h6 class="fw-bold">Shipping</h6>
                            <p class="small text-muted">Delivery, tracking, locations</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="help-card text-center p-4">
                            <div class="help-icon mx-auto">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <h6 class="fw-bold">Products</h6>
                            <p class="small text-muted">Sizing, quality, availability</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Topics -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-fire me-2"></i>Popular Topics
                </h3>
                <div class="d-flex flex-wrap">
                    <span class="category-pill active">All Topics</span>
                    <span class="category-pill">Order Status</span>
                    <span class="category-pill">Returns</span>
                    <span class="category-pill">Payment Issues</span>
                    <span class="category-pill">Shipping</span>
                    <span class="category-pill">Sizing</span>
                    <span class="category-pill">Account</span>
                    <span class="category-pill">Bulk Orders</span>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="row">
                <div class="col-lg-8">
                    <h3 class="text-primary mb-4">
                        <i class="fas fa-question-circle me-2"></i>Frequently Asked Questions
                    </h3>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <i class="fas fa-chevron-right me-2"></i>How do I track my order?
                        </button>
                        <div class="faq-answer">
                            <p>You can track your order by logging into your account and viewing your order history. Click on the order you want to track, and you'll see the current status and tracking number. You can also track by entering your order number on our tracking page.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <i class="fas fa-chevron-right me-2"></i>What is your return policy?
                        </button>
                        <div class="faq-answer">
                            <p>We offer a 7-day return policy for unused items in original packaging. Simply contact our customer service team to initiate a return. Refunds are processed within 3-5 business days after we receive the returned item.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <i class="fas fa-chevron-right me-2"></i>What payment methods do you accept?
                        </button>
                        <div class="faq-answer">
                            <p>We accept M-Pesa, Airtel Money, Visa, Mastercard, PayPal, and cash on delivery (Nairobi only). All transactions are secure and encrypted.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <i class="fas fa-chevron-right me-2"></i>How long does shipping take?
                        </button>
                        <div class="faq-answer">
                            <p>Standard delivery takes 3-5 business days within major cities. Express delivery (1-2 days) is available for an additional fee. Rural areas may take 5-7 business days.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <i class="fas fa-chevron-right me-2"></i>Do you offer bulk discounts?
                        </button>
                        <div class="faq-answer">
                            <p>Yes! We offer special pricing for bulk orders (10+ items). Contact our sales team at sales@smartschool.com for a custom quote based on your needs.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Options -->
                <div class="col-lg-4">
                    <h3 class="text-primary mb-4">
                        <i class="fas fa-phone me-2"></i>Contact Support
                    </h3>

                    <div class="contact-option mb-3">
                        <i class="fas fa-phone fa-2x text-primary mb-3"></i>
                        <h6 class="fw-bold">Phone Support</h6>
                        <p class="small text-muted">Mon-Fri: 8AM-6PM</p>
                        <a href="tel:0712345678" class="btn btn-primary btn-sm">Call Now</a>
                    </div>

                    <div class="contact-option mb-3">
                        <i class="fab fa-whatsapp fa-2x text-success mb-3"></i>
                        <h6 class="fw-bold">WhatsApp</h6>
                        <p class="small text-muted">24/7 Support</p>
                        <a href="#" class="btn btn-success btn-sm">Chat on WhatsApp</a>
                    </div>

                    <div class="contact-option mb-3">
                        <i class="fas fa-envelope fa-2x text-info mb-3"></i>
                        <h6 class="fw-bold">Email Support</h6>
                        <p class="small text-muted">Response within 24hrs</p>
                        <a href="mailto:support@smartschool.com" class="btn btn-info btn-sm">Send Email</a>
                    </div>

                    <!-- Live Chat Widget -->
                    <div class="card bg-primary text-white mt-4">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-comments fa-2x mb-3"></i>
                            <h6 class="fw-bold">Need Immediate Help?</h6>
                            <p class="small mb-3">Start a live chat with our support team</p>
                            <button class="btn btn-light btn-sm">Start Live Chat</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Articles -->
            <div class="mt-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-book me-2"></i>Help Articles
                </h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="fw-bold"><i class="fas fa-ruler me-2 text-primary"></i>Size Guide</h6>
                                <p class="small text-muted">Find the perfect fit with our comprehensive sizing charts</p>
                                <a href="size_guide.php" class="btn btn-sm btn-outline-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="fw-bold"><i class="fas fa-truck me-2 text-primary"></i>Shipping Information</h6>
                                <p class="small text-muted">Learn about our delivery options and shipping policies</p>
                                <a href="shipping_info.php" class="btn btn-sm btn-outline-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="fw-bold"><i class="fas fa-undo me-2 text-primary"></i>Returns & Exchanges</h6>
                                <p class="small text-muted">Our return policy and exchange procedures</p>
                                <a href="returns.php" class="btn btn-sm btn-outline-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="fw-bold"><i class="fas fa-credit-card me-2 text-primary"></i>Payment Options</h6>
                                <p class="small text-muted">Available payment methods and billing information</p>
                                <a href="payment_options.php" class="btn btn-sm btn-outline-primary">Read More</a>
                            </div>
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
function toggleFAQ(button) {
    const answer = button.nextElementSibling;
    const icon = button.querySelector('i');
    
    answer.classList.toggle('show');
    
    if (answer.classList.contains('show')) {
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
    } else {
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
    }
}

// Category pill functionality
document.querySelectorAll('.category-pill').forEach(pill => {
    pill.addEventListener('click', function() {
        document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
</body>
</html>


