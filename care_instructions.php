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
    <title>Care Instructions - SmartSchool Uniforms</title>
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
        
        .care-header {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .care-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .care-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .care-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .care-header-card {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .care-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--accent-green);
            margin-right: 1rem;
        }
        
        .care-title {
            flex-grow: 1;
        }
        
        .care-title h5 {
            color: var(--accent-green);
            margin-bottom: 0.25rem;
        }
        
        .care-title p {
            color: var(--neutral-gray);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .instruction-steps {
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
            background: var(--accent-green);
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
        
        .fabric-guide {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .fabric-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .fabric-icon {
            width: 50px;
            height: 50px;
            background: var(--secondary-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .fabric-details {
            flex-grow: 1;
        }
        
        .fabric-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .fabric-care {
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }
        
        .wash-symbols {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .symbol-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .symbol-card:hover {
            border-color: var(--accent-green);
            transform: translateY(-2px);
        }
        
        .symbol-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 1.5rem;
        }
        
        .symbol-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .symbol-meaning {
            font-size: 0.8rem;
            color: var(--neutral-gray);
        }
        
        .troubleshooting {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .problem-item {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .problem-title {
            font-weight: 600;
            color: var(--primary-amber);
            margin-bottom: 0.5rem;
        }
        
        .solution-steps {
            margin-left: 1rem;
        }
        
        .solution-steps li {
            margin-bottom: 0.5rem;
        }
        
        .seasonal-care {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .seasonal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .seasonal-card {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1.5rem;
        }
        
        .quick-tips {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-green);
        }
        
        .quick-tips h6 {
            color: var(--accent-green);
            margin-bottom: 0.5rem;
        }
        
        .quick-tips ul {
            margin-bottom: 0;
        }
        
        .quick-tips li {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .care-calendar {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .calendar-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .calendar-date {
            background: var(--accent-green);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: bold;
            margin-right: 1rem;
            min-width: 80px;
            text-align: center;
        }
        
        .calendar-task {
            flex-grow: 1;
        }
        
        .calendar-task h6 {
            margin-bottom: 0.25rem;
        }
        
        .calendar-task p {
            font-size: 0.9rem;
            color: var(--neutral-gray);
            margin: 0;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="care-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-soap me-3"></i>Care Instructions</h1>
            <p class="lead mb-0">Keep your school uniforms looking new with proper care</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="care-section">
                    <h3 class="mb-4">General Care Guidelines</h3>
                    
                    <div class="care-card">
                        <div class="care-header-card">
                            <div class="care-icon">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="care-title">
                                <h5>Daily Care</h5>
                                <p>Maintain uniform quality day to day</p>
                            </div>
                        </div>
                        
                        <div class="instruction-steps">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <div class="step-title">Air After Use</div>
                                    <div class="step-description">Hang uniforms immediately after school to air out and prevent wrinkles</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <div class="step-title">Spot Clean</div>
                                    <div class="step-description">Treat stains immediately with mild soap and cold water</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <div class="step-title">Check Pockets</div>
                                    <div class="step-description">Empty pockets before washing to prevent damage</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <div class="step-title">Proper Storage</div>
                                    <div class="step-description">Store in a cool, dry place away from direct sunlight</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="care-card">
                        <div class="care-header-card">
                            <div class="care-icon">
                                <i class="fas fa-washing-machine"></i>
                            </div>
                            <div class="care-title">
                                <h5>Washing Instructions</h5>
                                <p>Proper washing techniques for longevity</p>
                            </div>
                        </div>
                        
                        <div class="instruction-steps">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <div class="step-title">Separate Colors</div>
                                    <div class="step-description">Wash whites separately from colors to prevent bleeding</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <div class="step-title">Use Cold Water</div>
                                    <div class="step-description">Wash in cold water (30°C max) to preserve fabric and colors</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <div class="step-title">Mild Detergent</div>
                                    <div class="step-description">Use mild, color-safe detergent. Avoid bleach and fabric softeners</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <div class="step-title">Gentle Cycle</div>
                                    <div class="step-description">Use gentle or delicate cycle to minimize wear and tear</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">5</div>
                                <div class="step-content">
                                    <div class="step-title">Don't Overload</div>
                                    <div class="step-description">Wash small loads to ensure thorough cleaning</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="care-card">
                        <div class="care-header-card">
                            <div class="care-icon">
                                <i class="fas fa-sun"></i>
                            </div>
                            <div class="care-title">
                                <h5>Drying & Ironing</h5>
                                <p>Proper drying and ironing techniques</p>
                            </div>
                        </div>
                        
                        <div class="instruction-steps">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <div class="step-title">Tumble Dry Low</div>
                                    <div class="step-description">Use low heat or air dry to prevent shrinking</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <div class="step-title">Remove Promptly</div>
                                    <div class="step-description">Remove from dryer while slightly damp to minimize wrinkles</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <div class="step-title">Hang to Finish</div>
                                    <div class="step-description">Hang on proper hangers to finish drying naturally</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <div class="step-title">Iron While Damp</div>
                                    <div class="step-description">Iron while slightly damp using medium heat</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">5</div>
                                <div class="step-content">
                                    <div class="step-title">Press, Don't Pull</div>
                                    <div class="step-description">Use pressing motions rather than pulling to maintain shape</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="fabric-guide">
                    <h3 class="mb-4">Fabric-Specific Care</h3>
                    
                    <div class="fabric-item">
                        <div class="fabric-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="fabric-details">
                            <div class="fabric-name">Cotton & Cotton Blends</div>
                            <div class="fabric-care">
                                Machine wash cold, tumble dry low, iron medium heat. Pre-shrunk but may shrink slightly with hot water.
                            </div>
                        </div>
                    </div>
                    
                    <div class="fabric-item">
                        <div class="fabric-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="fabric-details">
                            <div class="fabric-name">Polyester Blends</div>
                            <div class="fabric-care">
                                Machine wash cold, low heat dry, cool iron. Resistant to wrinkles and shrinking.
                            </div>
                        </div>
                    </div>
                    
                    <div class="fabric-item">
                        <div class="fabric-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="fabric-details">
                            <div class="fabric-name">Wool & Wool Blends</div>
                            <div class="fabric-care">
                                Hand wash cold or dry clean only. Lay flat to dry. Cool iron if needed.
                            </div>
                        </div>
                    </div>
                    
                    <div class="fabric-item">
                        <div class="fabric-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="fabric-details">
                            <div class="fabric-name">Performance Fabrics</div>
                            <div class="fabric-care">
                                Machine wash cold, no fabric softener, low heat dry. Don't iron directly on logos.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="care-section">
                    <h3 class="mb-4">Wash Care Symbols</h3>
                    
                    <div class="wash-symbols">
                        <div class="symbol-card">
                            <div class="symbol-icon">
                                <i class="fas fa-tint"></i>
                            </div>
                            <div class="symbol-name">Wash at 30°C</div>
                            <div class="symbol-meaning">Machine wash cold</div>
                        </div>
                        
                        <div class="symbol-card">
                            <div class="symbol-icon">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div class="symbol-name">Do Not Bleach</div>
                            <div class="symbol-meaning">No bleach allowed</div>
                        </div>
                        
                        <div class="symbol-card">
                            <div class="symbol-icon">
                                <i class="fas fa-sun"></i>
                            </div>
                            <div class="symbol-name">Tumble Dry Low</div>
                            <div class="symbol-meaning">Low heat drying</div>
                        </div>
                        
                        <div class="symbol-card">
                            <div class="symbol-icon">
                                <i class="fas fa-iron"></i>
                            </div>
                            <div class="symbol-name">Iron Medium</div>
                            <div class="symbol-meaning">Medium heat ironing</div>
                        </div>
                        
                        <div class="symbol-card">
                            <div class="symbol-icon">
                                <i class="fas fa-hands-wash"></i>
                            </div>
                            <div class="symbol-name">Hand Wash</div>
                            <div class="symbol-meaning">Hand wash only</div>
                        </div>
                        
                        <div class="symbol-card">
                            <div class="symbol-icon">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="symbol-name">Dry Clean Only</div>
                            <div class="symbol-meaning">Professional dry cleaning</div>
                        </div>
                    </div>
                </div>
                
                <div class="troubleshooting">
                    <h3 class="mb-4">Common Problems & Solutions</h3>
                    
                    <div class="problem-item">
                        <div class="problem-title">Stains on White Shirts</div>
                        <div class="solution-steps">
                            <ol>
                                <li>Treat immediately with cold water and mild soap</li>
                                <li>For tough stains, use baking soda paste</li>
                                <li>Avoid hot water which can set stains</li>
                                <li>Consider oxygen-based bleach for persistent stains</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="problem-item">
                        <div class="problem-title">Yellowing of White Fabrics</div>
                        <div class="solution-steps">
                            <ol>
                                <li>Soak in vinegar solution (1 part vinegar to 4 parts water)</li>
                                <li>Wash with baking soda added to detergent</li>
                                <li>Avoid excessive chlorine bleach</li>
                                <li>Store in acid-free tissue paper long-term</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="problem-item">
                        <div class="problem-title">Fabric Pilling</div>
                        <div class="solution-steps">
                            <ol>
                                <li>Turn clothes inside out before washing</li>
                                <li>Use gentle cycle and mesh laundry bags</li>
                                <li>Remove pills with fabric shaver or sweater stone</li>
                                <li>Avoid high heat in drying</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="problem-item">
                        <div class="problem-title">Shrinking</div>
                        <div class="solution-steps">
                            <ol>
                                <li>Always wash in cold water</li>
                                <li>Avoid high heat drying</li>
                                <li>Reshape while damp and lay flat to dry</li>
                                <li>For minor shrinkage, gently stretch while damp</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div class="seasonal-care">
                    <h3 class="mb-4">Seasonal Care Tips</h3>
                    
                    <div class="seasonal-grid">
                        <div class="seasonal-card">
                            <h6><i class="fas fa-sun me-2"></i>Dry Season Care</h6>
                            <ul class="small">
                                <li>Increased washing frequency due to sweat</li>
                                <li>Use extra rinse cycles to remove sweat</li>
                                <li>Air dry in shade to prevent sun damage</li>
                                <li>Store in breathable cotton bags</li>
                            </ul>
                        </div>
                        
                        <div class="seasonal-card">
                            <h6><i class="fas fa-cloud-rain me-2"></i>Rainy Season Care</h6>
                            <ul class="small">
                                <li>Ensure complete drying to prevent mildew</li>
                                <li>Use dehumidifiers in storage areas</li>
                                <li>Waterproof storage for backup uniforms</li>
                                <li>Quick-dry fabrics recommended</li>
                            </ul>
                        </div>
                        
                        <div class="seasonal-card">
                            <h6><i class="fas fa-snowflake me-2"></i>Holiday Storage</h6>
                            <ul class="small">
                                <li>Clean thoroughly before long-term storage</li>
                                <li>Use cedar blocks to prevent moths</li>
                                <li>Store in acid-free containers</li>
                                <li>Avoid plastic bags which trap moisture</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="care-calendar">
                    <h4 class="mb-3">Care Calendar</h4>
                    
                    <div class="calendar-item">
                        <div class="calendar-date">Daily</div>
                        <div class="calendar-task">
                            <h6>Air Out Uniforms</h6>
                            <p>Hang after school to prevent wrinkles</p>
                        </div>
                    </div>
                    
                    <div class="calendar-item">
                        <div class="calendar-date">Weekly</div>
                        <div class="calendar-task">
                            <h6>Deep Clean</h6>
                            <p>Wash all uniforms with proper care</p>
                        </div>
                    </div>
                    
                    <div class="calendar-item">
                        <div class="calendar-date">Monthly</div>
                        <div class="calendar-task">
                            <h6>Inspection</h6>
                            <p>Check for wear, tears, or needed repairs</p>
                        </div>
                    </div>
                    
                    <div class="calendar-item">
                        <div class="calendar-date">Term End</div>
                        <div class="calendar-task">
                            <h6>Deep Storage Prep</h6>
                            <p>Clean and prepare for holiday storage</p>
                        </div>
                    </div>
                </div>
                
                <div class="care-section">
                    <h4 class="mb-3">Quick Tips</h4>
                    
                    <div class="quick-tips">
                        <h6><i class="fas fa-magic me-2"></i>Extend Uniform Life</h6>
                        <ul>
                            <li>Rotate between multiple sets</li>
                            <li>Follow care labels strictly</li>
                            <li>Address repairs immediately</li>
                            <li>Use proper storage techniques</li>
                        </ul>
                    </div>
                    
                    <div class="quick-tips">
                        <h6><i class="fas fa-palette me-2"></i>Color Care</h6>
                        <ul>
                            <li>Wash dark colors inside out</li>
                            <li>Use color-safe detergents</li>
                            <li>Separate whites and colors</li>
                            <li>Avoid direct sunlight when drying</li>
                        </ul>
                    </div>
                    
                    <div class="quick-tips">
                        <h6><i class="fas fa-dollar-sign me-2"></i>Save Money</h6>
                        <ul>
                            <li>Air dry when possible</li>
                            <li>Use cold water washing</li>
                            <li>Mend small tears promptly</li>
                            <li>Buy quality over quantity</li>
                        </ul>
                    </div>
                </div>
                
                <div class="care-section">
                    <h4 class="mb-3">Essential Supplies</h4>
                    
                    <div class="list-group">
                        <div class="list-group-item">
                            <i class="fas fa-soap me-2"></i>
                            <strong>Mild Detergent</strong>
                            <small class="text-muted d-block">Color-safe, no bleach</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-spray-can me-2"></i>
                            <strong>Stain Remover</strong>
                            <small class="text-muted d-block">For spot treatments</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-iron me-2"></i>
                            <strong>Steam Iron</strong>
                            <small class="text-muted d-block">With temperature control</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-hanger me-2"></i>
                            <strong>Quality Hangers</strong>
                            <small class="text-muted d-block">Padded or wooden</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-box me-2"></i>
                            <strong>Storage Bags</strong>
                            <small class="text-muted d-block">Breathable cotton</small>
                        </div>
                    </div>
                </div>
                
                <div class="care-section">
                    <h4 class="mb-3">Professional Services</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-phone me-2"></i>Care Hotline</h6>
                        <p class="small mb-0">Call <strong>+254 700 123 456</strong> for care advice</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-cut me-2"></i>Repair Service</h6>
                        <p class="small mb-0">Free minor repairs with purchase</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-home me-2"></i>Home Delivery</h6>
                        <p class="small mb-0">Pickup and delivery service available</p>
                    </div>
                </div>
                
                <div class="care-section">
                    <h4 class="mb-3">Video Tutorials</h4>
                    
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-play-circle me-2"></i>
                            Basic Uniform Care
                            <small class="text-muted d-block">5 min tutorial</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-play-circle me-2"></i>
                            Stain Removal Techniques
                            <small class="text-muted d-block">8 min tutorial</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-play-circle me-2"></i>
                            Ironing Like a Pro
                            <small class="text-muted d-block">6 min tutorial</small>
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
