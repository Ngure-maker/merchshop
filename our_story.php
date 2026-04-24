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
    <title>Our Story - SmartSchool Uniforms</title>
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
        
        .story-header {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .story-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .story-header .container {
            position: relative;
            z-index: 1;
        }
        
        .story-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .timeline-item {
            display: flex;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 40px;
            top: 80px;
            bottom: -40px;
            width: 2px;
            background: var(--primary-amber);
        }
        
        .timeline-item:last-child::before {
            display: none;
        }
        
        .timeline-year {
            background: var(--primary-amber);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            font-weight: bold;
            min-width: 80px;
            text-align: center;
            margin-right: 2rem;
        }
        
        .timeline-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            flex-grow: 1;
        }
        
        .milestone-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-amber);
        }
        
        .milestone-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .milestone-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .quote-section {
            background: linear-gradient(135deg, var(--secondary-teal), var(--secondary-blue));
            color: white;
            padding: 3rem;
            border-radius: 15px;
            text-align: center;
            margin: 3rem 0;
        }
        
        .quote-text {
            font-size: 1.5rem;
            font-style: italic;
            margin-bottom: 1.5rem;
        }
        
        .quote-author {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .value-item {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .value-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .value-icon {
            width: 80px;
            height: 80px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-amber);
            margin: 0 auto 1.5rem;
            transition: all 0.3s ease;
        }
        
        .value-item:hover .value-icon {
            background: var(--primary-amber);
            color: white;
            transform: scale(1.1);
        }
        
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            height: 250px;
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover img {
            transform: scale(1.05);
        }
        
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 1.5rem;
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--accent-purple), var(--primary-amber));
            color: white;
            padding: 4rem;
            border-radius: 15px;
            text-align: center;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="story-header">
        <div class="container">
            <h1 class="display-4 mb-3">Our Story</h1>
            <p class="lead mb-0">From a small dream to Kenya's trusted uniform provider</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="story-section">
            <div class="row align-items-center mb-5">
                <div class="col-lg-6">
                    <h2 class="mb-4">The Beginning</h2>
                    <p class="lead">SmartSchool Uniforms began in 2015 with a simple observation: parents and students deserved better when it came to school uniform shopping.</p>
                    <p>Our founder, Mary Johnson, a mother of three, experienced firsthand the challenges of finding quality school uniforms that were both affordable and durable. Late-night shopping trips, inconsistent sizing, and poor quality materials were the norm.</p>
                    <p>"I remember standing in a crowded market, trying to find uniforms that would actually last the school term," Mary recalls. "I thought, there has to be a better way."</p>
                    <p>That frustration sparked an idea: create a one-stop shop for quality school uniforms that parents could trust, with transparent pricing and reliable service.</p>
                </div>
                <div class="col-lg-6">
                    <div class="photo-gallery">
                        <div class="gallery-item">
                            <img src="https://via.placeholder.com/400x250/FF6B35/FFFFFF?text=2015+Small+Shop" alt="First Shop">
                            <div class="gallery-overlay">
                                <h6>Our First Shop</h6>
                                <small>2015 - Nairobi</small>
                            </div>
                        </div>
                        <div class="gallery-item">
                            <img src="https://via.placeholder.com/400x250/00897B/FFFFFF?text=Early+Team" alt="Early Team">
                            <div class="gallery-overlay">
                                <h6>Our Early Team</h6>
                                <small>Just 3 passionate people</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="quote-section">
                <div class="quote-text">"We didn't just want to sell uniforms. We wanted to make a difference in how families experience back-to-school shopping."</div>
                <div class="quote-author">- Mary Johnson, Founder & CEO</div>
            </div>
        </div>
        
        <div class="story-section">
            <h2 class="text-center mb-5">Our Journey</h2>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-year">2015</div>
                    <div class="timeline-content">
                        <h4>The Dream Takes Shape</h4>
                        <p>Mary Johnson invested her savings to open a small uniform shop in Nairobi's Westlands area. With just three employees and a vision to transform school uniform shopping, SmartSchool Uniforms was born.</p>
                        <p>Our first month saw 47 customers, many of whom became lifelong advocates for our quality and service.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2016</div>
                    <div class="timeline-content">
                        <h4>First School Partnership</h4>
                        <p>Nairobi Academy became our first official school partner. This partnership allowed us to understand bulk uniform needs and develop our school program that now serves 500+ schools.</p>
                        <p>We learned that consistency, reliability, and understanding school requirements were key to success.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2017</div>
                    <div class="timeline-content">
                        <h4>Expanding Our Reach</h4>
                        <p>Growing demand led us to open our second location in Karen. We also launched our first website, allowing parents to browse uniforms online before visiting our stores.</p>
                        <p>Our team grew to 12 employees, each sharing our passion for quality service.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2018</div>
                    <div class="timeline-content">
                        <h4>Quality Innovation</h4>
                        <p>We introduced our proprietary fabric blend that became the standard for durability and comfort. This innovation set us apart from competitors who used cheaper materials.</p>
                        <p>Customer satisfaction rates soared to 95%, with parents praising the longevity of our uniforms.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2019</div>
                    <div class="timeline-content">
                        <h4>Digital Transformation</h4>
                        <p>Launched our full e-commerce platform, making quality school uniforms accessible to families across all 47 counties. This was a game-changer for rural families who previously had limited access.</p>
                        <p>Online sales accounted for 40% of our business within the first year.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2020</div>
                    <div class="timeline-content">
                        <h4>Pandemic Response</h4>
                        <p>When COVID-19 hit, we quickly adapted. We implemented contactless delivery, virtual size consultations, and flexible payment options to help families during challenging times.</p>
                        <p>We also donated 500 uniforms to families affected by the pandemic.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2021</div>
                    <div class="timeline-content">
                        <h4>Custom Uniform Service</h4>
                        <p>Launched our custom uniform design service, working directly with schools to create unique uniform collections that reflect their identity and values.</p>
                        <p>Designed uniforms for 25 schools in our first year of custom service.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2022</div>
                    <div class="timeline-content">
                        <h4>Expansion Beyond Uniforms</h4>
                        <p>Added school supplies, books, and accessories to our product range based on customer feedback. Parents wanted a one-stop solution for all school needs.</p>
                        <p>School supplies now represent 30% of our total sales.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2023</div>
                    <div class="timeline-content">
                        <h4>Community Impact</h4>
                        <p>Launched our uniform assistance program, providing free uniforms to over 1,000 students from financially challenged families. This program continues to grow each year.</p>
                        <p>Also introduced our referral and loyalty programs to reward our loyal customers.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2024</div>
                    <div class="timeline-content">
                        <h4>National Recognition</h4>
                        <p>Awarded "Best School Uniform Provider" by the Kenya Education Sector. Our team now includes 50+ dedicated employees serving over 50,000 students nationwide.</p>
                        <p>We're proud to be Kenya's most trusted uniform provider.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="story-section">
            <h2 class="text-center mb-5">Key Milestones</h2>
            
            <div class="row">
                <div class="col-md-6 col-lg-4">
                    <div class="milestone-card">
                        <div class="milestone-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <h4>500+ Schools</h4>
                        <p>Partnered with educational institutions across Kenya, from small primary schools to large secondary schools.</p>
                        <div class="text-primary fw-bold">Achieved: 2023</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="milestone-card">
                        <div class="milestone-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>50,000+ Students</h4>
                        <p>Serving students from all 47 counties with quality uniforms and exceptional service.</p>
                        <div class="text-primary fw-bold">Achieved: 2024</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="milestone-card">
                        <div class="milestone-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h4>5 Physical Stores</h4>
                        <p>Strategically located stores in Nairobi, Mombasa, Kisumu, Nakuru, and Eldoret.</p>
                        <div class="text-primary fw-bold">Achieved: 2023</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="milestone-card">
                        <div class="milestone-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h4>Nationwide Delivery</h4>
                        <p>Fast, reliable delivery to all 47 counties within 2-3 business days.</p>
                        <div class="text-primary fw-bold">Achieved: 2020</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="milestone-card">
                        <div class="milestone-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>1,000+ Donations</h4>
                        <p>Free uniforms provided to students from financially challenged families.</p>
                        <div class="text-primary fw-bold">Achieved: 2023</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="milestone-card">
                        <div class="milestone-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h4>Industry Award</h4>
                        <p>Recognized as Kenya's Best School Uniform Provider by the Education Sector.</p>
                        <div class="text-primary fw-bold">Achieved: 2024</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="story-section">
            <h2 class="text-center mb-5">The Values That Guide Us</h2>
            
            <div class="values-grid">
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4>Education First</h4>
                    <p>Everything we do is centered on supporting education and helping students succeed. We believe proper school attire contributes to a focused learning environment.</p>
                </div>
                
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4>Quality Commitment</h4>
                    <p>We never compromise on quality. Every uniform is crafted with care using premium materials that withstand daily wear and frequent washing.</p>
                </div>
                
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Customer Focus</h4>
                    <p>Our customers are at the heart of everything we do. We listen, adapt, and continuously improve to exceed expectations.</p>
                </div>
                
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Integrity</h4>
                    <p>We conduct business with honesty, transparency, and ethical practices. Trust is the foundation of our relationships with customers and partners.</p>
                </div>
                
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4>Sustainability</h4>
                    <p>We're committed to environmentally responsible practices, from eco-friendly materials to sustainable packaging and reduced waste.</p>
                </div>
                
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Innovation</h4>
                    <p>We continuously innovate to improve our products, services, and customer experience through technology and creative solutions.</p>
                </div>
            </div>
        </div>
        
        <div class="story-section">
            <h2 class="text-center mb-5">Looking to the Future</h2>
            
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <p class="lead mb-4">Our journey is far from over. We're constantly looking ahead, planning new ways to serve our customers better and make quality education more accessible.</p>
                    
                    <div class="milestone-card">
                        <h5><i class="fas fa-rocket me-2"></i>2025 Goals</h5>
                        <ul>
                            <li>Launch mobile app for easier shopping</li>
                            <li>Expand to 10 physical locations</li>
                            <li>Introduce eco-friendly uniform line</li>
                            <li>Serve 100,000+ students nationwide</li>
                        </ul>
                    </div>
                    
                    <div class="milestone-card">
                        <h5><i class="fas fa-globe-africa me-2"></i>Regional Expansion</h5>
                        <p>We're exploring opportunities to serve neighboring East African countries, bringing our quality uniforms and exceptional service to more families.</p>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="photo-gallery">
                        <div class="gallery-item">
                            <img src="https://via.placeholder.com/400x250/7B1FA2/FFFFFF?text=Future+Goals" alt="Future Vision">
                            <div class="gallery-overlay">
                                <h6>Our Vision</h6>
                                <small>Making quality education accessible to all</small>
                            </div>
                        </div>
                        <div class="gallery-item">
                            <img src="https://via.placeholder.com/400x250/388E3C/FFFFFF?text=Team+Growth" alt="Team Growth">
                            <div class="gallery-overlay">
                                <h6>Growing Team</h6>
                                <small>50+ dedicated employees</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cta-section">
            <h2 class="mb-4">Be Part of Our Story</h2>
            <p class="lead mb-4">Join thousands of families who trust SmartSchool Uniforms for quality, affordability, and exceptional service.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="catalog.php" class="btn btn-light btn-lg w-100">
                                <i class="fas fa-shopping-bag me-2"></i>Shop Now
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="school_programs.php" class="btn btn-outline-light btn-lg w-100">
                                <i class="fas fa-school me-2"></i>School Programs
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="contact.php" class="btn btn-light btn-lg w-100">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate timeline items on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateX(0)';
                }
            });
        }, observerOptions);
        
        // Initially hide timeline items
        document.querySelectorAll('.timeline-item').forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-50px)';
            item.style.transition = 'all 0.6s ease';
            item.style.transitionDelay = `${index * 0.1}s`;
            observer.observe(item);
        });
    </script>
</body>
</html>
