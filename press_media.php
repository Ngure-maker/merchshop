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
    <title>Press & Media - SmartSchool Uniforms</title>
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
        
        .press-header {
            background: linear-gradient(135deg, var(--accent-purple), var(--primary-amber));
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .press-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .press-header .container {
            position: relative;
            z-index: 1;
        }
        
        .press-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .press-release {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent-purple);
        }
        
        .press-release:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .press-date {
            color: var(--accent-purple);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .press-category {
            display: inline-block;
            background: var(--light-bg);
            color: var(--primary-amber);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .media-coverage {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .media-coverage:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .media-logo {
            width: 100px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--neutral-gray);
            margin-bottom: 1rem;
        }
        
        .media-quote {
            font-style: italic;
            color: var(--neutral-gray);
            margin-bottom: 1rem;
            border-left: 3px solid var(--accent-purple);
            padding-left: 1rem;
        }
        
        .award-card {
            background: linear-gradient(135deg, var(--accent-purple), var(--primary-amber));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .award-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 4s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .award-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .press-kit {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
        }
        
        .download-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .download-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .download-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .media-contact {
            background: linear-gradient(135deg, var(--secondary-teal), var(--secondary-blue));
            color: white;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .contact-info {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
        
        .timeline-item {
            display: flex;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 40px;
            top: 40px;
            bottom: -20px;
            width: 2px;
            background: var(--accent-purple);
        }
        
        .timeline-item:last-child::before {
            display: none;
        }
        
        .timeline-date {
            background: var(--accent-purple);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            min-width: 100px;
            text-align: center;
            margin-right: 2rem;
            height: fit-content;
        }
        
        .timeline-content {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            flex-grow: 1;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="press-header">
        <div class="container">
            <h1 class="display-4 mb-3">Press & Media</h1>
            <p class="lead mb-0">News, updates, and media resources for SmartSchool Uniforms</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">50+</div>
                <h6>Media Mentions</h6>
                <p class="small text-muted mb-0">Featured in national and local media</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">12</div>
                <h6>Industry Awards</h6>
                <p class="small text-muted mb-0">Recognition for excellence and innovation</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">8</div>
                <h6>Partnerships</h6>
                <p class="small text-muted mb-0">Strategic media and industry partnerships</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">100K+</div>
                <h6>Media Reach</h6>
                <p class="small text-muted mb-0">Combined audience reach through media coverage</p>
            </div>
        </div>
        
        <div class="press-section">
            <h2 class="text-center mb-5">Latest Press Releases</h2>
            
            <div class="press-release">
                <div class="press-date">November 15, 2024</div>
                <div class="press-category">Company News</div>
                <h4>SmartSchool Uniforms Launches Revolutionary Mobile App with Virtual Try-On Technology</h4>
                <p>Nairobi, Kenya - SmartSchool Uniforms today announced the launch of its groundbreaking mobile application featuring AI-powered virtual try-on technology, marking a significant milestone in digital transformation for Kenya's education retail sector.</p>
                <p>The innovative app allows parents and students to virtually try on school uniforms using their smartphone cameras, eliminating sizing uncertainties and reducing return rates by an estimated 40%.</p>
                <a href="#" class="btn btn-outline-primary">Read Full Press Release</a>
            </div>
            
            <div class="press-release">
                <div class="press-date">October 28, 2024</div>
                <div class="press-category">Partnership</div>
                <h4>SmartSchool Uniforms Partners with Ministry of Education to Support Digital Learning Initiative</h4>
                <p>Nairobi, Kenya - SmartSchool Uniforms has announced a strategic partnership with the Ministry of Education to support the government's digital learning initiative by providing subsidized uniforms and educational technology to underserved communities.</p>
                <p>The partnership will benefit over 10,000 students across 200 schools in rural areas, combining quality uniforms with digital learning tools to enhance educational outcomes.</p>
                <a href="#" class="btn btn-outline-primary">Read Full Press Release</a>
            </div>
            
            <div class="press-release">
                <div class="press-date">September 10, 2024</div>
                <div class="press-category">Sustainability</div>
                <h4>SmartSchool Uniforms Introduces Eco-Friendly Uniform Line Made from Recycled Materials</h4>
                <p>Nairobi, Kenya - In a groundbreaking move toward sustainability, SmartSchool Uniforms today unveiled its new eco-friendly uniform collection made from 100% recycled materials, setting a new standard for environmental responsibility in the education sector.</p>
                <p>The new collection, made from recycled plastic bottles and sustainable fabrics, reduces environmental impact by 60% compared to traditional uniform materials while maintaining the same quality and durability standards.</p>
                <a href="#" class="btn btn-outline-primary">Read Full Press Release</a>
            </div>
            
            <div class="press-release">
                <div class="press-date">August 5, 2024</div>
                <div class="press-category">Expansion</div>
                <h4>SmartSchool Uniforms Expands to East African Markets with Launch in Uganda and Tanzania</h4>
                <p>Nairobi, Kenya - SmartSchool Uniforms today announced its expansion into East African markets with the launch of operations in Uganda and Tanzania, marking the company's first step toward becoming Africa's leading school uniform provider.</p>
                <p>The expansion includes new distribution centers in Kampala and Dar es Salaam, creating over 100 new jobs and bringing quality school uniforms to an additional 25,000 students.</p>
                <a href="#" class="btn btn-outline-primary">Read Full Press Release</a>
            </div>
        </div>
        
        <div class="press-section">
            <h2 class="text-center mb-5">Media Coverage</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="media-coverage">
                        <div class="media-logo">Daily Nation</div>
                        <h5>How SmartSchool Uniforms is Transforming Education Retail in Kenya</h5>
                        <div class="media-quote">"SmartSchool Uniforms has revolutionized how parents shop for school uniforms, combining technology with exceptional service to create a seamless experience."</div>
                        <p class="small text-muted">Published: October 15, 2024</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Read Article</a>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="media-coverage">
                        <div class="media-logo">Citizen TV</div>
                        <h5>Local Business Goes Digital: SmartSchool's E-commerce Success Story</h5>
                        <div class="media-quote">"From a small shop in Nairobi to serving over 50,000 students nationwide, SmartSchool Uniforms exemplifies Kenyan entrepreneurship at its best."</div>
                        <p class="small text-muted">Aired: September 28, 2024</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Watch Segment</a>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="media-coverage">
                        <div class="media-logo">Business Daily</div>
                        <h5>Sustainability in Education: SmartSchool's Eco-Friendly Initiative</h5>
                        <div class="media-quote">"The company's commitment to environmental responsibility while maintaining affordability sets a new benchmark for the education sector."</div>
                        <p class="small text-muted">Published: September 10, 2024</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Read Article</a>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="media-coverage">
                        <div class="media-logo">KBC</div>
                        <h5>Supporting Education: SmartSchool's Community Impact Program</h5>
                        <div class="media-quote">"Beyond business, SmartSchool Uniforms is making a real difference in communities through education support and job creation."</div>
                        <p class="small text-muted">Aired: August 20, 2024</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Listen to Interview</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="press-section">
            <h2 class="text-center mb-5">Awards & Recognition</h2>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="award-card">
                        <div class="award-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h5>Best School Uniform Provider 2024</h5>
                        <p class="mb-0">Kenya Education Sector Awards</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="award-card">
                        <div class="award-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h5>Innovation in Retail 2024</h5>
                        <p class="mb-0">Kenya Business Awards</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="award-card">
                        <div class="award-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h5>Customer Excellence 2023</h5>
                        <p class="mb-0">East Africa Customer Service Awards</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="award-card">
                        <div class="award-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h5>Sustainability Leadership 2023</h5>
                        <p class="mb-0">Green Business Kenya Awards</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="award-card">
                        <div class="award-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h5>Fastest Growing Company 2023</h5>
                        <p class="mb-0">Kenya SME Awards</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="award-card">
                        <div class="award-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5>Social Impact Award 2022</h5>
                        <p class="mb-0">Kenya Corporate Social Responsibility Awards</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="press-section">
            <h2 class="text-center mb-5">Media Timeline</h2>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-date">Nov 2024</div>
                    <div class="timeline-content">
                        <h6>Mobile App Launch Coverage</h6>
                        <p class="small text-muted">Featured on TechTrends Africa, Kenya Tech News, and Digital Kenya</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">Oct 2024</div>
                    <div class="timeline-content">
                        <h6>Ministry Partnership Announcement</h6>
                        <p class="small text-muted">Coverage by Daily Nation, Standard Media, and KBC News</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">Sep 2024</div>
                    <div class="timeline-content">
                        <h6>Sustainability Initiative Launch</h6>
                        <p class="small text-muted">Featured in Business Daily, The Star, and Citizen Business</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">Aug 2024</div>
                    <div class="timeline-content">
                        <h6>East Africa Expansion</h6>
                        <p class="small text-muted">Coverage by NTV Uganda, Tanzania Daily News, and East African Business Week</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">Jul 2024</div>
                    <div class="timeline-content">
                        <h6>Customer Success Stories</h6>
                        <p class="small text-muted">Featured on KBC's "SME Success" program and Radio Africa</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="press-kit">
            <h2 class="text-center mb-5">Press Kit</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <h4 class="mb-3">Company Information</h4>
                    
                    <div class="download-item">
                        <div class="download-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6>Company Profile</h6>
                            <p class="small text-muted mb-0">Complete overview of SmartSchool Uniforms</p>
                        </div>
                        <a href="#" class="btn btn-sm btn-primary">Download</a>
                    </div>
                    
                    <div class="download-item">
                        <div class="download-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6>Fact Sheet</h6>
                            <p class="small text-muted mb-0">Key facts and figures about our company</p>
                        </div>
                        <a href="#" class="btn btn-sm btn-primary">Download</a>
                    </div>
                    
                    <div class="download-item">
                        <div class="download-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6>Company History</h6>
                            <p class="small text-muted mb-0">Our journey from 2015 to present</p>
                        </div>
                        <a href="#" class="btn btn-sm btn-primary">Download</a>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4 class="mb-3">Visual Assets</h4>
                    
                    <div class="download-item">
                        <div class="download-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6>Logo Package</h6>
                            <p class="small text-muted mb-0">High-resolution logos in various formats</p>
                        </div>
                        <a href="#" class="btn btn-sm btn-primary">Download</a>
                    </div>
                    
                    <div class="download-item">
                        <div class="download-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6>Product Images</h6>
                            <p class="small text-muted mb-0">High-quality product photography</p>
                        </div>
                        <a href="#" class="btn btn-sm btn-primary">Download</a>
                    </div>
                    
                    <div class="download-item">
                        <div class="download-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6>B-Roll Videos</h6>
                            <p class="small text-muted mb-0">Company and product video footage</p>
                        </div>
                        <a href="#" class="btn btn-sm btn-primary">Download</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="media-contact">
            <h2 class="mb-4">Media Contact</h2>
            <p class="lead mb-4">For media inquiries, interviews, or additional information, please contact our media relations team.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="contact-info">
                        <h5><i class="fas fa-user me-2"></i>Media Relations Manager</h5>
                        <p class="mb-0">Grace Mutua</p>
                        <p class="small mb-0">grace.mutua@smartschool.com</p>
                        <p class="small">+254 700 123 456</p>
                    </div>
                    
                    <div class="contact-info">
                        <h5><i class="fas fa-envelope me-2"></i>General Media Inquiries</h5>
                        <p class="mb-0">media@smartschool.com</p>
                        <p class="small">Response within 24 hours</p>
                    </div>
                    
                    <div class="contact-info">
                        <h5><i class="fas fa-clock me-2"></i>Office Hours</h5>
                        <p class="mb-0">Monday - Friday: 8:00 AM - 6:00 PM</p>
                        <p class="small">Weekend: Emergency media inquiries only</p>
                    </div>
                </div>
            </div>
            
            <div class="row justify-content-center mt-4">
                <div class="col-md-6">
                    <div class="d-grid gap-2">
                        <a href="mailto:media@smartschool.com" class="btn btn-light btn-lg">
                            <i class="fas fa-envelope me-2"></i>Email Media Relations
                        </a>
                        <a href="tel:+254700123456" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Call Media Team
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate stats on scroll
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = entry.target.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        if (!stat.classList.contains('animated')) {
                            stat.classList.add('animated');
                            const finalValue = stat.textContent;
                            let currentValue = 0;
                            const increment = parseInt(finalValue) / 50;
                            
                            const timer = setInterval(() => {
                                currentValue += increment;
                                if (currentValue >= parseInt(finalValue)) {
                                    stat.textContent = finalValue;
                                    clearInterval(timer);
                                } else {
                                    stat.textContent = Math.floor(currentValue) + '+';
                                }
                            }, 30);
                        }
                    });
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.stats-grid').forEach(section => {
            observer.observe(section);
        });
    </script>
</body>
</html>
