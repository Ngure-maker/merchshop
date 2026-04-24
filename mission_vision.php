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
    <title>Mission & Vision - SmartSchool Uniforms</title>
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
        
        .mission-header {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .mission-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .mission-header .container {
            position: relative;
            z-index: 1;
        }
        
        .mission-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .vision-card {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .vision-card::before {
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
        
        .vision-icon {
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 2rem;
            position: relative;
            z-index: 1;
        }
        
        .vision-text {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .mission-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent-purple);
        }
        
        .mission-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .mission-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .value-pillar {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .value-pillar:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .pillar-icon {
            width: 80px;
            height: 80px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--accent-purple);
            margin: 0 auto 1.5rem;
            transition: all 0.3s ease;
        }
        
        .value-pillar:hover .pillar-icon {
            background: var(--accent-purple);
            color: white;
            transform: scale(1.1);
        }
        
        .impact-section {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
        }
        
        .impact-stat {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .impact-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
        
        .commitment-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .commitment-icon {
            width: 50px;
            height: 50px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .future-goals {
            background: linear-gradient(135deg, var(--secondary-teal), var(--secondary-blue));
            color: white;
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
        }
        
        .goal-item {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid white;
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
    
    <div class="mission-header">
        <div class="container">
            <h1 class="display-4 mb-3">Mission & Vision</h1>
            <p class="lead mb-0">Our purpose and aspirations for transforming education in Kenya</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="vision-card">
            <div class="vision-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div class="vision-text">Our Vision</div>
            <p class="lead">To be Africa's leading provider of quality school attire, making education accessible and empowering every student to learn with confidence and pride.</p>
            <div class="row mt-4">
                <div class="col-md-4">
                    <h5><i class="fas fa-globe-africa me-2"></i>Pan-African Reach</h5>
                    <p class="small mb-0">Expanding quality uniform services across the continent</p>
                </div>
                <div class="col-md-4">
                    <h5><i class="fas fa-graduation-cap me-2"></i>Educational Excellence</h5>
                    <p class="small mb-0">Supporting academic success through quality attire</p>
                </div>
                <div class="col-md-4">
                    <h5><i class="fas fa-users me-2"></i>Inclusive Access</h5>
                    <p class="small mb-0">Making quality uniforms affordable for all families</p>
                </div>
            </div>
        </div>
        
        <div class="mission-section">
            <h2 class="text-center mb-5">Our Mission</h2>
            
            <div class="mission-card">
                <div class="mission-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h4>Primary Mission</h4>
                <p class="lead">To provide high-quality, affordable school uniforms and educational supplies that enable students to focus on learning while feeling confident and proud of their appearance.</p>
                <p>We believe that proper school attire contributes to a positive learning environment and helps students develop a sense of belonging and discipline. Every child deserves access to quality uniforms that fit well, last long, and make them feel proud to represent their school.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Quality Assurance</h5>
                        <p>Ensure every uniform meets the highest standards of durability, comfort, and style, giving parents value for their investment and students clothing they can wear with pride.</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <h5>Affordability</h5>
                        <p>Make quality school uniforms accessible to families from all economic backgrounds through competitive pricing, flexible payment options, and assistance programs.</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h5>Convenience</h5>
                        <p>Provide a seamless shopping experience through multiple channels - physical stores, online platform, and mobile services - making uniform shopping easy and stress-free for busy parents.</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Partnership</h5>
                        <p>Build strong relationships with schools, parents, and communities to understand their needs and deliver solutions that exceed expectations.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mission-section">
            <h2 class="text-center mb-5">Our Core Values</h2>
            
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="value-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h5>Education First</h5>
                        <p>Everything we do is centered on supporting education and helping students succeed. We believe proper school attire contributes to a focused learning environment.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5>Quality Commitment</h5>
                        <p>We never compromise on quality. Every uniform is crafted with care using premium materials that withstand daily wear and frequent washing.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Customer Focus</h5>
                        <p>Our customers are at the heart of everything we do. We listen, adapt, and continuously improve to exceed expectations.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Integrity</h5>
                        <p>We conduct business with honesty, transparency, and ethical practices. Trust is the foundation of our relationships with customers and partners.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h5>Sustainability</h5>
                        <p>We're committed to environmentally responsible practices, from eco-friendly materials to sustainable packaging and reduced waste.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h5>Innovation</h5>
                        <p>We continuously innovate to improve our products, services, and customer experience through technology and creative solutions.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h5>Community Impact</h5>
                        <p>We believe in giving back to the communities we serve through education support, job creation, and community development initiatives.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="value-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h5>Excellence</h5>
                        <p>We strive for excellence in everything we do, from product quality to customer service, setting the standard for the industry.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="impact-section">
            <h2 class="text-center mb-5">Our Impact</h2>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="impact-stat">
                        <div class="stat-number">500+</div>
                        <h6>Schools Partnered</h6>
                        <p class="small text-muted">Educational institutions trust us</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="impact-stat">
                        <div class="stat-number">50K+</div>
                        <h6>Students Served</h6>
                        <p class="small text-muted">Confident learners nationwide</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="impact-stat">
                        <div class="stat-number">1,000+</div>
                        <h6>Uniforms Donated</h6>
                        <p class="small text-muted">Supporting needy families</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="impact-stat">
                        <div class="stat-number">50+</div>
                        <h6>Jobs Created</h6>
                        <p class="small text-muted">Local employment opportunities</p>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="commitment-item">
                        <div class="commitment-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <h6>Education Support</h6>
                            <p>We actively support educational initiatives through donations, scholarships, and partnerships with educational foundations.</p>
                        </div>
                    </div>
                    
                    <div class="commitment-item">
                        <div class="commitment-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <div>
                            <h6>Community Development</h6>
                            <p>We invest in local communities through job creation, skills training, and supporting local suppliers and manufacturers.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="commitment-item">
                        <div class="commitment-icon">
                            <i class="fas fa-recycle"></i>
                        </div>
                        <div>
                            <h6>Environmental Responsibility</h6>
                            <p>We're reducing our carbon footprint through sustainable materials, eco-friendly packaging, and waste reduction programs.</p>
                        </div>
                    </div>
                    
                    <div class="commitment-item">
                        <div class="commitment-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h6>Skill Development</h6>
                            <p>We provide training and development opportunities for our team members, helping them build careers and grow professionally.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="future-goals">
            <h2 class="text-center mb-4">Future Goals & Aspirations</h2>
            <p class="text-center mb-4">Our roadmap for the next 5 years and beyond</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="goal-item">
                        <h5><i class="fas fa-globe-africa me-2"></i>Regional Expansion</h5>
                        <p>Expand our services to neighboring East African countries, bringing quality uniforms and exceptional service to more families across the continent.</p>
                    </div>
                    
                    <div class="goal-item">
                        <h5><i class="fas fa-mobile-alt me-2"></i>Digital Innovation</h5>
                        <p>Launch a comprehensive mobile app with virtual try-on features, AI-powered size recommendations, and enhanced shopping experience.</p>
                    </div>
                    
                    <div class="goal-item">
                        <h5><i class="fas fa-leaf me-2"></i>Sustainability Leadership</h5>
                        <p>Become the industry leader in sustainable practices with eco-friendly materials, carbon-neutral operations, and circular economy initiatives.</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="goal-item">
                        <h5><i class="fas fa-school me-2"></i>Educational Impact</h5>
                        <p>Establish a foundation to support education for underprivileged children, providing uniforms, books, and learning materials to those in need.</p>
                    </div>
                    
                    <div class="goal-item">
                        <h5><i class="fas fa-users me-2"></i>Community Empowerment</h5>
                        <p>Create 100+ new jobs through expansion and implement skills development programs for youth in communities we serve.</p>
                    </div>
                    
                    <div class="goal-item">
                        <h5><i class="fas fa-award me-2"></i>Industry Excellence</h5>
                        <p>Set new industry standards for quality, service, and innovation while maintaining our commitment to affordability and accessibility.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mission-section">
            <h2 class="text-center mb-5">Our Strategic Priorities</h2>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mission-card text-center">
                        <div class="mission-icon mx-auto">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5>Growth & Expansion</h5>
                        <p>Strategic growth through new locations, digital channels, and market expansion while maintaining service quality and customer satisfaction.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mission-card text-center">
                        <div class="mission-icon mx-auto">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h5>Operational Excellence</h5>
                        <p>Continuous improvement of processes, systems, and technologies to enhance efficiency, reduce costs, and improve customer experience.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mission-card text-center">
                        <div class="mission-icon mx-auto">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5>Social Responsibility</h5>
                        <p>Meaningful contribution to society through education support, environmental stewardship, and community development initiatives.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cta-section">
            <h2 class="mb-4">Join Our Mission</h2>
            <p class="lead mb-4">Be part of our journey to transform education in Kenya and beyond through quality school uniforms and exceptional service.</p>
            
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
                                <i class="fas fa-school me-2"></i>Partner With Us
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="careers.php" class="btn btn-light btn-lg w-100">
                                <i class="fas fa-briefcase me-2"></i>Careers
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
        
        document.querySelectorAll('.impact-section').forEach(section => {
            observer.observe(section);
        });
    </script>
</body>
</html>
