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
    <title>Uniform Guide - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .guide-header {
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-teal));
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .guide-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .uniform-category {
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .uniform-category:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .category-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .category-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--secondary-blue);
            margin-right: 1rem;
        }
        
        .category-title {
            flex-grow: 1;
        }
        
        .category-title h5 {
            color: var(--secondary-blue);
            margin-bottom: 0.25rem;
        }
        
        .category-title p {
            color: var(--neutral-gray);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .uniform-items {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .uniform-item {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .uniform-item:hover {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .item-price {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .tips-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tip-card {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-green);
        }
        
        .tip-card h6 {
            color: var(--accent-green);
            margin-bottom: 0.5rem;
        }
        
        .size-guide {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .size-table {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .size-table table {
            margin-bottom: 0;
        }
        
        .size-table th {
            background: var(--secondary-blue);
            color: white;
            border: none;
            padding: 0.75rem;
            text-align: center;
        }
        
        .size-table td {
            padding: 0.75rem;
            text-align: center;
            border: none;
            border-bottom: 1px solid #e9ecef;
        }
        
        .size-table tr:last-child td {
            border-bottom: none;
        }
        
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .quick-link-card {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .quick-link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .quick-link-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
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
        
        .seasonal-guide {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .seasonal-item {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="guide-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-book-open me-3"></i>Uniform Guide</h1>
            <p class="lead mb-0">Complete guide to school uniforms for all levels</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="guide-section">
                    <h3 class="mb-4"><i class="fas fa-graduation-cap me-2"></i>Uniform Categories</h3>
                    
                    <div class="uniform-category">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-child"></i>
                            </div>
                            <div class="category-title">
                                <h5>Primary School Uniforms</h5>
                                <p>Ages 6-12 • Classes 1-6</p>
                            </div>
                        </div>
                        
                        <div class="uniform-items">
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=Shirt" alt="Shirt" class="item-image">
                                <div class="item-name">School Shirt</div>
                                <div class="item-price">KSh 850</div>
                            </div>
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/546E7A/FFFFFF?text=Trousers" alt="Trousers" class="item-image">
                                <div class="item-name">School Trousers</div>
                                <div class="item-price">KSh 1,200</div>
                            </div>
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/1976D2/FFFFFF?text=Dress" alt="Dress" class="item-image">
                                <div class="item-name">School Dress</div>
                                <div class="item-price">KSh 1,100</div>
                            </div>
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/000000/FFFFFF?text=Shoes" alt="Shoes" class="item-image">
                                <div class="item-name">School Shoes</div>
                                <div class="item-price">KSh 1,800</div>
                            </div>
                        </div>
                        
                        <div class="tips-section">
                            <h6><i class="fas fa-lightbulb me-2"></i>Primary School Tips</h6>
                            <ul class="small">
                                <li>Choose durable fabrics that can withstand daily play</li>
                                <li>Consider growth - buy slightly larger sizes</li>
                                <li>Purchase extra sets for frequent changes</li>
                                <li>Label all items clearly with child's name</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="uniform-category">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="category-title">
                                <h5>Secondary School Uniforms</h5>
                                <p>Ages 13-18 • Forms 1-4</p>
                            </div>
                        </div>
                        
                        <div class="uniform-items">
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=Shirt" alt="Shirt" class="item-image">
                                <div class="item-name">Formal Shirt</div>
                                <div class="item-price">KSh 950</div>
                            </div>
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/546E7A/FFFFFF?text=Trousers" alt="Trousers" class="item-image">
                                <div class="item-name">Formal Trousers</div>
                                <div class="item-price">KSh 1,350</div>
                            </div>
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/7B1FA2/FFFFFF?text=Blazer" alt="Blazer" class="item-image">
                                <div class="item-name">School Blazer</div>
                                <div class="item-price">KSh 2,500</div>
                            </div>
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/000000/FFFFFF?text=Shoes" alt="Shoes" class="item-image">
                                <div class="item-name">Formal Shoes</div>
                                <div class="item-price">KSh 2,200</div>
                            </div>
                        </div>
                        
                        <div class="tips-section">
                            <h6><i class="fas fa-lightbulb me-2"></i>Secondary School Tips</h6>
                            <ul class="small">
                                <li>Invest in higher quality for longer wear</li>
                                <li>Consider multiple sets for weekly rotation</li>
                                <li>Include formal wear for special occasions</li>
                                <li>Purchase sports kit for PE activities</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="uniform-category">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-running"></i>
                            </div>
                            <div class="category-title">
                                <h5>Sports & PE Kits</h5>
                                <p>All Levels • Physical Education</p>
                            </div>
                        </div>
                        
                        <div class="uniform-items">
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/00897B/FFFFFF?text=T-Shirt" alt="T-Shirt" class="item-image">
                                <div class="item-name">PE T-Shirt</div>
                                <div class="item-price">KSh 650</div>
                            </div>
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/388E3C/FFFFFF?text=Shorts" alt="Shorts" class="item-image">
                                <div class="item-name">Sports Shorts</div>
                                <div class="item-price">KSh 750</div>
                            </div>
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=Tracksuit" alt="Tracksuit" class="item-image">
                                <div class="item-name">Tracksuit</div>
                                <div class="item-price">KSh 1,800</div>
                            </div>
                            <div class="uniform-item">
                                <img src="https://via.placeholder.com/80x00897B/FFFFFF?text=Shoes" alt="Shoes" class="item-image">
                                <div class="item-name">Sports Shoes</div>
                                <div class="item-price">KSh 1,500</div>
                            </div>
                        </div>
                        
                        <div class="tips-section">
                            <h6><i class="fas fa-lightbulb me-2"></i>Sports Kit Tips</h6>
                            <ul class="small">
                                <li>Choose breathable, moisture-wicking fabrics</li>
                                <li>Ensure proper fit for physical activities</li>
                                <li>Consider indoor and outdoor sports requirements</li>
                                <li>Label all items for easy identification</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="size-guide">
                    <h3 class="mb-4"><i class="fas fa-ruler me-2"></i>Size Guide</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="size-table">
                                <h6 class="mb-3">Shirt Size Chart</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Age</th>
                                            <th>Chest (cm)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>XS</td><td>6-7</td><td>64-68</td></tr>
                                        <tr><td>S</td><td>8-9</td><td>70-74</td></tr>
                                        <tr><td>M</td><td>10-11</td><td>76-80</td></tr>
                                        <tr><td>L</td><td>12-13</td><td>82-86</td></tr>
                                        <tr><td>XL</td><td>14-15</td><td>88-92</td></tr>
                                        <tr><td>XXL</td><td>16+</td><td>94-98</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="size-table">
                                <h6 class="mb-3">Trouser Size Chart</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Age</th>
                                            <th>Waist (cm)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>XS</td><td>6-7</td><td>58-62</td></tr>
                                        <tr><td>S</td><td>8-9</td><td>64-68</td></tr>
                                        <tr><td>M</td><td>10-11</td><td>70-74</td></tr>
                                        <tr><td>L</td><td>12-13</td><td>76-80</td></tr>
                                        <tr><td>XL</td><td>14-15</td><td>82-86</td></tr>
                                        <tr><td>XXL</td><td>16+</td><td>88-92</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="size-table">
                                <h6 class="mb-3">Shoe Size Chart</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>UK Size</th>
                                            <th>EU Size</th>
                                            <th>Age</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>11</td><td>28</td><td>6-7</td></tr>
                                        <tr><td>12</td><td>30</td><td>7-8</td></tr>
                                        <tr><td>13</td><td>32</td><td>8-9</td></tr>
                                        <tr><td>1</td><td>33</td><td>9-10</td></tr>
                                        <tr><td>2</td><td>34</td><td>10-11</td></tr>
                                        <tr><td>3</td><td>36</td><td>11-12</td></tr>
                                        <tr><td>4</td><td>37</td><td>12-13</td></tr>
                                        <tr><td>5</td><td>38</td><td>13-14</td></tr>
                                        <tr><td>6</td><td>39</td><td>14-15</td></tr>
                                        <tr><td>7</td><td>40</td><td>15-16</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="size-table">
                                <h6 class="mb-3">Dress Size Chart</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Age</th>
                                            <th>Chest (cm)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>XS</td><td>6-7</td><td>64-68</td></tr>
                                        <tr><td>S</td><td>8-9</td><td>70-74</td></tr>
                                        <tr><td>M</td><td>10-11</td><td>76-80</td></tr>
                                        <tr><td>L</td><td>12-13</td><td>82-86</td></tr>
                                        <tr><td>XL</td><td>14-15</td><td>88-92</td></tr>
                                        <tr><td>XXL</td><td>16+</td><td>94-98</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="seasonal-guide">
                    <h3 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Seasonal Uniform Guide</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="seasonal-item">
                                <h6><i class="fas fa-sun me-2"></i>Dry Season (Jan-Mar, Jul-Oct)</h6>
                                <ul class="small">
                                    <li>Lightweight cotton shirts</li>
                                    <li>Breathable trousers and dresses</li>
                                    <li>Light sweaters for early mornings</li>
                                    <li>Comfortable socks and shoes</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="seasonal-item">
                                <h6><i class="fas fa-cloud-rain me-2"></i>Rainy Season (Apr-Jun, Nov-Dec)</h6>
                                <ul class="small">
                                    <li>Water-resistant jackets</li>
                                    <li>Quick-dry fabrics</li>
                                    <li>Sturdy waterproof shoes</li>
                                    <li>Extra socks for changes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="guide-section">
                    <h4 class="mb-3"><i class="fas fa-clipboard-list me-2"></i>Uniform Checklist</h4>
                    
                    <div class="checklist">
                        <h6 class="mb-3">Primary School</h6>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>3 sets of shirts</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>2 pairs of trousers/skirts</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>1 school sweater</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>1 PE kit</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>1 pair school shoes</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>3 pairs socks</div>
                        </div>
                    </div>
                    
                    <div class="checklist">
                        <h6 class="mb-3">Secondary School</h6>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>4 formal shirts</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>2 pairs formal trousers</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>1 school blazer</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>1 school tie</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>1 PE kit</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox"><i class="fas fa-check"></i></div>
                            <div>1 pair formal shoes</div>
                        </div>
                    </div>
                </div>
                
                <div class="guide-section">
                    <h4 class="mb-3"><i class="fas fa-lightbulb me-2"></i>Quick Tips</h4>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-tag me-2"></i>Budget Planning</h6>
                        <p class="small mb-0">Plan your uniform budget 2 months before school opens. Take advantage of early bird discounts.</p>
                    </div>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-ruler me-2"></i>Measuring Tips</h6>
                        <p class="small mb-0">Measure your child in the evening when they're at their largest. Add 2-3cm for growth room.</p>
                    </div>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-shopping-bag me-2"></i>Shopping Strategy</h6>
                        <p class="small mb-0">Buy essentials first, then accessories. Check school requirements before purchasing.</p>
                    </div>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-cut me-2"></i>Alterations</h6>
                        <p class="small mb-0">Consider professional alterations for perfect fit. Most trousers can be adjusted.</p>
                    </div>
                </div>
                
                <div class="guide-section">
                    <h4 class="mb-3"><i class="fas fa-link me-2"></i>Quick Links</h4>
                    
                    <div class="quick-links">
                        <div class="quick-link-card" onclick="window.location.href='size_calculator.php'">
                            <div class="quick-link-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <h6>Size Calculator</h6>
                            <p class="small mb-0">Find the perfect fit</p>
                        </div>
                        
                        <div class="quick-link-card" onclick="window.location.href='school_requirements.php'">
                            <div class="quick-link-icon">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <h6>School Requirements</h6>
                            <p class="small mb-0">Check specific needs</p>
                        </div>
                        
                        <div class="quick-link-card" onclick="window.location.href='care_instructions.php'">
                            <div class="quick-link-icon">
                                <i class="fas fa-soap"></i>
                            </div>
                            <h6>Care Instructions</h6>
                            <p class="small mb-0">Maintain quality</p>
                        </div>
                    </div>
                </div>
                
                <div class="guide-section">
                    <h4 class="mb-3"><i class="fas fa-question-circle me-2"></i>Common Questions</h4>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How many uniform sets should I buy?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We recommend 3-4 sets for primary school and 4-5 sets for secondary school to allow for daily rotation and washing.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What if the uniform doesn't fit?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We offer free exchanges within 30 days. Bring the receipt and original packaging for quick exchange.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Do you offer payment plans?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we offer flexible payment plans for bulk orders and school partnerships. Contact us for details.
                                </div>
                            </div>
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


