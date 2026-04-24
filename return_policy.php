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
    <title>Return Policy - SmartSchool Uniforms</title>
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
        
        .policy-header {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .policy-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        
        .highlight-box {
            background: var(--light-bg);
            border-left: 4px solid var(--primary-amber);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .return-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: var(--primary-amber);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .step-content h6 {
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }
        
        .timeline-info {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .timeline-info .days {
            background: var(--primary-amber);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .non-returnable {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .non-returnable h6 {
            color: #721c24;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="policy-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-undo me-3"></i>Return Policy</h1>
            <p class="lead mb-0">Hassle-free returns and exchanges for your peace of mind</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="policy-section">
            <h3 class="mb-4"><i class="fas fa-check-circle me-2"></i>Our Return Promise</h3>
            <p class="lead">We want you to be completely satisfied with your purchase. If you're not happy with your order, we're here to help make it right.</p>
            
            <div class="highlight-box">
                <h6><i class="fas fa-star me-2"></i>30-Day Return Window</h6>
                <p class="mb-0">You have 30 days from the date of delivery to return or exchange items for any reason.</p>
            </div>
            
            <div class="highlight-box">
                <h6><i class="fas fa-shield-alt me-2"></i>Quality Guarantee</h6>
                <p class="mb-0">All returned items are inspected for quality issues. Defective products are replaced or refunded immediately.</p>
            </div>
        </div>
        
        <div class="policy-section">
            <h3 class="mb-4"><i class="fas fa-list-ol me-2"></i>How to Return Items</h3>
            
            <div class="return-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h6>Initiate Return</h6>
                    <p>Contact our customer service team via phone, email, or visit our returns page. Provide your order number and the items you wish to return.</p>
                </div>
            </div>
            
            <div class="return-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h6>Pack Items</h6>
                    <p>Securely pack the items in their original packaging if possible. Include all tags, labels, and accessories that came with the product.</p>
                </div>
            </div>
            
            <div class="return-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h6>Attach Return Label</h6>
                    <p>Print and attach the return label provided by our customer service team. If no label was provided, contact us for assistance.</p>
                </div>
            </div>
            
            <div class="return-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h6>Ship the Package</h6>
                    <p>Drop off the package at any courier service location. Keep your tracking number for reference.</p>
                </div>
            </div>
            
            <div class="return-step">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h6>Receive Refund/Exchange</h6>
                    <p>Once we receive and inspect the returned items, we'll process your refund or exchange within 5-7 business days.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="policy-section">
                    <h3 class="mb-4"><i class="fas fa-clock me-2"></i>Return Timeline</h3>
                    
                    <div class="timeline-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6>Standard Returns</h6>
                                <p class="small text-muted mb-0">Most items qualify for standard return policy</p>
                            </div>
                            <span class="days">30 Days</span>
                        </div>
                    </div>
                    
                    <div class="timeline-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6>Size Exchanges</h6>
                                <p class="small text-muted mb-0">Wrong size or fit issues</p>
                            </div>
                            <span class="days">30 Days</span>
                        </div>
                    </div>
                    
                    <div class="timeline-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6>Defective Items</h6>
                                <p class="small text-muted mb-0">Manufacturing defects or quality issues</p>
                            </div>
                            <span class="days">90 Days</span>
                        </div>
                    </div>
                    
                    <div class="timeline-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6>Custom Uniforms</h6>
                                <p class="small text-muted mb-0">Custom-made or personalized items</p>
                            </div>
                            <span class="days">7 Days</span>
                        </div>
                    </div>
                </div>
                
                <div class="policy-section">
                    <h3 class="mb-4"><i class="fas fa-exclamation-triangle me-2"></i>Non-Returnable Items</h3>
                    
                    <div class="non-returnable">
                        <h6><i class="fas fa-times-circle me-2"></i>Customized Items</h6>
                        <p class="mb-0">Uniforms with custom embroidery, names, or specific school logos cannot be returned unless there's a manufacturing defect.</p>
                    </div>
                    
                    <div class="non-returnable">
                        <h6><i class="fas fa-times-circle me-2"></i>Undergarments</h6>
                        <p class="mb-0">For hygiene reasons, socks, underwear, and other intimate apparel cannot be returned.</p>
                    </div>
                    
                    <div class="non-returnable">
                        <h6><i class="fas fa-times-circle me-2"></i>Final Sale Items</h6>
                        <p class="mb-0">Items marked as "Final Sale" or purchased during clearance events cannot be returned.</p>
                    </div>
                    
                    <div class="non-returnable">
                        <h6><i class="fas fa-times-circle me-2"></i>Worn or Damaged Items</h6>
                        <p class="mb-0">Items that have been worn, washed, altered, or damaged by the customer cannot be returned.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="policy-section">
                    <h4 class="mb-3"><i class="fas fa-info-circle me-2"></i>Return Conditions</h4>
                    
                    <ul class="small">
                        <li class="mb-2">Items must be unworn and unwashed</li>
                        <li class="mb-2">Original tags and labels must be attached</li>
                        <li class="mb-2">Original packaging must be intact</li>
                        <li class="mb-2">Proof of purchase required</li>
                        <li class="mb-2">Returns must be in resalable condition</li>
                        <li class="mb-2">Custom items must have quality defects</li>
                    </ul>
                </div>
                
                <div class="policy-section">
                    <h4 class="mb-3"><i class="fas fa-truck me-2"></i>Return Shipping</h4>
                    
                    <div class="policy-item">
                        <h6>Free Returns</h6>
                        <p>Free return shipping for defective items or errors on our part.</p>
                    </div>
                    
                    <div class="policy-item">
                        <h6>Customer Pays</h6>
                        <p>Customer pays return shipping for size exchanges or change of mind returns.</p>
                    </div>
                    
                    <div class="policy-item">
                        <h6>Exchange Shipping</h6>
                        <p>We cover shipping for exchanged items sent to you.</p>
                    </div>
                </div>
                
                <div class="policy-section">
                    <h4 class="mb-3"><i class="fas fa-percentage me-2"></i>Refund Process</h4>
                    
                    <div class="policy-item">
                        <h6>Processing Time</h6>
                        <p>Refunds are processed within 5-7 business days after we receive the returned items.</p>
                    </div>
                    
                    <div class="policy-item">
                        <h6>Refund Method</h6>
                        <p>Refunds are issued to the original payment method used for the purchase.</p>
                    </div>
                    
                    <div class="policy-item">
                        <h6>Shipping Costs</h6>
                        <p>Original shipping costs are non-refundable unless the return is due to our error.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="policy-section">
            <h3 class="mb-4"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h3>
            
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            How do I know if my item is eligible for return?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Most items are eligible for return within 30 days if they're unworn, have original tags, and are in resalable condition. Check our "Non-Returnable Items" section for exceptions.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            What if I need a different size?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Size exchanges are free within 30 days. We'll send you the correct size and arrange return shipping for the original item at no additional cost.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            How long does the refund process take?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Once we receive your return, it takes 5-7 business days to process. Your bank may take an additional 3-5 business days to credit your account.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Can I return items purchased during a sale?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, sale items can be returned unless marked as "Final Sale." Clearance items and special promotional items may have different return policies.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="policy-section text-center">
            <h3 class="mb-4"><i class="fas fa-headset me-2"></i>Need Help with Returns?</h3>
            <p class="mb-4">Our customer service team is here to assist you with any questions or concerns about returns and exchanges.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="tel:+254700123456" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-phone me-2"></i>Call Us
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="mailto:returns@smartschool.com" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-envelope me-2"></i>Email Us
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="contact.php" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-comments me-2"></i>Live Chat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
