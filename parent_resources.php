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
    <title>Parent Resources - SmartSchool Uniforms</title>
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
        
        .parent-header {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .parent-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .resource-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .resource-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .resource-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--accent-purple);
            margin-right: 1rem;
        }
        
        .resource-title {
            flex-grow: 1;
        }
        
        .resource-title h5 {
            color: var(--accent-purple);
            margin-bottom: 0.25rem;
        }
        
        .resource-title p {
            color: var(--neutral-gray);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .guide-steps {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .step-item {
            display: flex;
            align-items: flex-start;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .step-item:last-child {
            border-bottom: none;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background: var(--accent-purple);
            color: white;
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
        
        .step-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .step-description {
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }
        
        .budget-planner {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .budget-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .budget-item:last-child {
            border-bottom: none;
        }
        
        .budget-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent-purple);
            border-top: 2px solid var(--accent-purple);
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .timeline {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .timeline-date {
            background: var(--accent-purple);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: bold;
            margin-right: 1rem;
            min-width: 100px;
            text-align: center;
        }
        
        .timeline-content {
            flex-grow: 1;
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 8px;
        }
        
        .timeline-content h6 {
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
        
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .tip-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .tip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .tip-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--accent-purple);
            margin: 0 auto 1rem;
        }
        
        .download-section {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .download-item {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .download-btn {
            background: white;
            color: var(--accent-green);
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .download-btn:hover {
            background: var(--light-bg);
        }
        
        .faq-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
        
        .checklist {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .checklist-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .checklist-item:last-child {
            border-bottom: none;
        }
        
        .checklist-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid var(--accent-green);
            border-radius: 4px;
            margin-right: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-green);
        }
        
        .support-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .contact-item:last-child {
            border-bottom: none;
        }
        
        .contact-icon {
            width: 40px;
            height: 40px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-purple);
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="parent-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-users me-3"></i>Parent Resources</h1>
            <p class="lead mb-0">Everything parents need to know about school uniforms</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="parent-section">
                    <h3 class="mb-4">Essential Parent Guides</h3>
                    
                    <div class="resource-card">
                        <div class="resource-header">
                            <div class="resource-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="resource-title">
                                <h5>Uniform Buying Guide</h5>
                                <p>Smart shopping for school uniforms</p>
                            </div>
                        </div>
                        
                        <div class="guide-steps">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <div class="step-title">Plan Ahead</div>
                                    <div class="step-description">Start shopping 2-3 months before school opens to avoid rush and get best prices</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <div class="step-title">Check School Requirements</div>
                                    <div class="step-description">Review your school's specific uniform requirements and dress code policies</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <div class="step-title">Measure Correctly</div>
                                    <div class="step-description">Use our size calculator and measure your child for accurate sizing</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <div class="step-title">Buy Extra Sets</div>
                                    <div class="step-description">Purchase at least 3-4 sets for rotation and emergencies</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">5</div>
                                <div class="step-content">
                                    <div class="step-title">Quality Over Price</div>
                                    <div class="step-description">Invest in durable uniforms that last longer, saving money in the long run</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-header">
                            <div class="resource-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="resource-title">
                                <h5>Budget Planning</h5>
                                <p>Manage uniform expenses effectively</p>
                            </div>
                        </div>
                        
                        <div class="budget-planner">
                            <h6 class="mb-3">Typical Uniform Budget Breakdown</h6>
                            
                            <div class="budget-item">
                                <span>Primary School Uniform Set</span>
                                <span>KSh 8,000 - 12,000</span>
                            </div>
                            
                            <div class="budget-item">
                                <span>Secondary School Uniform Set</span>
                                <span>KSh 15,000 - 25,000</span>
                            </div>
                            
                            <div class="budget-item">
                                <span>Sports Kit</span>
                                <span>KSh 2,500 - 4,000</span>
                            </div>
                            
                            <div class="budget-item">
                                <span>School Shoes</span>
                                <span>KSh 2,000 - 3,500</span>
                            </div>
                            
                            <div class="budget-item">
                                <span>Accessories (Socks, Belts, etc.)</span>
                                <span>KSh 1,000 - 2,000</span>
                            </div>
                            
                            <div class="budget-item">
                                <span>Replacement Items (Mid-year)</span>
                                <span>KSh 3,000 - 5,000</span>
                            </div>
                            
                            <div class="budget-total">
                                Total Annual Budget: KSh 17,000 - 41,500
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-header">
                            <div class="resource-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="resource-title">
                                <h5>School Year Timeline</h5>
                                <p>Important dates and deadlines</p>
                            </div>
                        </div>
                        
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-date">Nov-Dec</div>
                                <div class="timeline-content">
                                    <h6>Early Bird Shopping</h6>
                                    <p>Best prices and availability. Take advantage of holiday sales.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-date">Jan</div>
                                <div class="timeline-content">
                                    <h6>School Opening</h6>
                                    <p>Last-minute shopping rush. Higher prices and limited stock.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-date">Apr-May</div>
                                <div class="timeline-content">
                                    <h6>Mid-Year Replacement</h6>
                                    <p>Replace outgrown or worn items before second term.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-date">Aug-Sep</div>
                                <div class="timeline-content">
                                    <h6>Third Term Prep</h6>
                                    <p>Final term uniform checks and replacements needed.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-header">
                            <div class="resource-icon">
                                <i class="fas fa-child"></i>
                            </div>
                            <div class="resource-title">
                                <h5>Age-Specific Tips</h5>
                                <p>Tailored advice for different age groups</p>
                            </div>
                        </div>
                        
                        <div class="tips-grid">
                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-baby"></i>
                                </div>
                                <h6>Primary (6-12 years)</h6>
                                <p class="small">Focus on durability, easy care, and room for growth. Buy adjustable waistbands.</p>
                            </div>
                            
                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <h6>Secondary (13-18 years)</h6>
                                <p class="small">Consider style preferences within school rules. Invest in quality formal wear.</p>
                            </div>
                            
                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-running"></i>
                                </div>
                                <h6>Active Children</h6>
                                <p class="small">Choose breathable fabrics and reinforced knees. Extra sets recommended.</p>
                            </div>
                            
                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-tint"></i>
                                </div>
                                <h6>Sensitive Skin</h6>
                                <p class="small">Opt for cotton and hypoallergenic materials. Avoid harsh chemicals.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="download-section">
                    <h3 class="mb-4"><i class="fas fa-download me-2"></i>Downloadable Resources</h3>
                    
                    <div class="download-item">
                        <div>
                            <h6 class="mb-1">Parent's Uniform Guide</h6>
                            <p class="small mb-0">Complete guide to uniform shopping and care</p>
                        </div>
                        <button class="download-btn">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </button>
                    </div>
                    
                    <div class="download-item">
                        <div>
                            <h6 class="mb-1">Budget Planner Template</h6>
                            <p class="small mb-0">Excel sheet to plan uniform expenses</p>
                        </div>
                        <button class="download-btn">
                            <i class="fas fa-download me-1"></i>Download Excel
                        </button>
                    </div>
                    
                    <div class="download-item">
                        <div>
                            <h6 class="mb-1">Size Measurement Chart</h6>
                            <p class="small mb-0">Printable guide for measuring at home</p>
                        </div>
                        <button class="download-btn">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </button>
                    </div>
                    
                    <div class="download-item">
                        <div>
                            <h6 class="mb-1">School Calendar Template</h6>
                            <p class="small mb-0">Track uniform needs throughout the year</p>
                        </div>
                        <button class="download-btn">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </button>
                    </div>
                </div>
                
                <div class="faq-section">
                    <h3 class="mb-4">Frequently Asked Questions</h3>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How many uniform sets should I buy?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We recommend 3-4 sets for primary school and 4-5 sets for secondary school. This allows for daily rotation while having clean backups for emergencies.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What if my child outgrows their uniform mid-year?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Most schools allow uniform changes mid-year. We offer exchange policies within 30 days of purchase. Consider buying slightly larger sizes for growing children.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Are there payment plans available?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we offer flexible payment plans for purchases over KSh 10,000. Contact our customer service for details on installment options.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    How do I remove tough stains?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Treat stains immediately with cold water and mild soap. For tough stains, use baking soda paste or specialized stain removers. Avoid hot water which can set stains.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="parent-section">
                    <h4 class="mb-3">Parent Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">85%</div>
                            <h6>Parents Save Money</h6>
                            <p class="small text-muted mb-0">By planning ahead</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">92%</div>
                            <h6>Satisfied Parents</h6>
                            <p class="small text-muted mb-0">With our quality</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">3.5</div>
                            <h6>Average Sets</h6>
                            <p class="small text-muted mb-0">Purchased per child</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">2.5</div>
                            <h6>Years Lifespan</h6>
                            <p class="small text-muted mb-0">Per uniform set</p>
                        </div>
                    </div>
                </div>
                
                <div class="parent-section">
                    <h4 class="mb-3">Quick Checklists</h4>
                    
                    <div class="checklist">
                        <h6 class="mb-3">Before Shopping</h6>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>Check school uniform requirements</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>Measure child's current size</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>Set budget and payment method</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>Check for early bird discounts</div>
                        </div>
                    </div>
                    
                    <div class="checklist">
                        <h6 class="mb-3">After Purchase</h6>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>Label all items with child's name</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>Wash before first use</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>Keep receipts for exchanges</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>Create care routine</div>
                        </div>
                    </div>
                </div>
                
                <div class="parent-section">
                    <h4 class="mb-3">Money-Saving Tips</h4>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-tag me-2"></i>Shop Off-Season</h6>
                        <p class="small mb-0">Buy during holidays for best discounts and availability.</p>
                    </div>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-box me-2"></i>Bulk Orders</h6>
                        <p class="small mb-0">Save 10-15% when buying multiple sets at once.</p>
                    </div>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-exchange-alt me-2"></i>Exchange Programs</h6>
                        <p class="small mb-0">Join parent groups for uniform exchanges and swaps.</p>
                    </div>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-tools me-2"></i>DIY Repairs</h6>
                        <p class="small mb-0">Learn basic sewing to extend uniform life and save money.</p>
                    </div>
                </div>
                
                <div class="parent-section">
                    <h4 class="mb-3">Parent Support</h4>
                    
                    <div class="support-section">
                        <h6 class="mb-3">Get Help</h6>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Parent Helpline</div>
                                <small class="text-muted">+254 700 123 456</small>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Email Support</div>
                                <small class="text-muted">parents@smartschool.co.ke</small>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Live Chat</div>
                                <small class="text-muted">Mon-Fri, 8AM-6PM</small>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Parent Community</div>
                                <small class="text-muted">Join our Facebook group</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="parent-section">
                    <h4 class="mb-3">Workshops & Events</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-calendar me-2"></i>Uniform Care Workshop</h6>
                        <p class="small mb-0">Free workshop on uniform maintenance and repair. Every first Saturday of the month.</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-graduation-cap me-2"></i>Parent Orientation</h6>
                        <p class="small mb-0">New parent guide to uniform requirements and shopping tips. Sessions available.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-tag me-2"></i>Bulk Buying Day</h6>
                        <p class="small mb-0">Special discounts for parent groups. Contact us to organize.</p>
                    </div>
                </div>
                
                <div class="parent-section">
                    <h4 class="mb-3">Useful Links</h4>
                    
                    <div class="list-group">
                        <a href="size_calculator.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-calculator me-2"></i>
                            Size Calculator
                        </a>
                        <a href="uniform_guide.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-book-open me-2"></i>
                            Uniform Guide
                        </a>
                        <a href="school_requirements.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-list-check me-2"></i>
                            School Requirements
                        </a>
                        <a href="care_instructions.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-soap me-2"></i>
                            Care Instructions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
