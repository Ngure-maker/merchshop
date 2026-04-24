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
    <title>Our Team - SmartSchool Uniforms</title>
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
        
        .team-header {
            background: linear-gradient(135deg, var(--secondary-teal), var(--secondary-blue));
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .team-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .team-header .container {
            position: relative;
            z-index: 1;
        }
        
        .team-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .team-member {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .team-member::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-teal), var(--secondary-blue));
        }
        
        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .team-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .team-photo::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--secondary-teal), var(--secondary-blue));
            z-index: 0;
        }
        
        .team-photo i {
            font-size: 3rem;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .team-member h5 {
            color: var(--secondary-teal);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .team-member .position {
            color: var(--primary-amber);
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .team-member .bio {
            color: var(--neutral-gray);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .social-links a {
            width: 35px;
            height: 35px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary-teal);
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--secondary-teal);
            color: white;
            transform: translateY(-3px);
        }
        
        .leadership-section {
            background: linear-gradient(135deg, var(--secondary-teal), var(--secondary-blue));
            color: white;
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
        }
        
        .leadership-member {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 4px solid white;
        }
        
        .leadership-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .department-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .department-header {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .department-icon {
            width: 60px;
            height: 60px;
            background: var(--secondary-teal);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .team-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--secondary-teal), var(--secondary-blue));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .culture-section {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
        }
        
        .culture-value {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--secondary-teal);
        }
        
        .join-team {
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
    
    <div class="team-header">
        <div class="container">
            <h1 class="display-4 mb-3">Our Team</h1>
            <p class="lead mb-0">Meet the passionate people behind SmartSchool Uniforms</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="team-stats">
            <div class="stat-card">
                <div class="stat-number">50+</div>
                <h6>Dedicated Team Members</h6>
                <p class="small mb-0">Working together to serve our customers</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">9</div>
                <h6>Years Average Experience</h6>
                <p class="small mb-0">In education and retail sectors</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">47</div>
                <h6>Counties Served</h6>
                <p class="small mb-0">Nationwide coverage and support</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">98%</div>
                <h6>Team Satisfaction</h6>
                <p class="small mb-0">Happy team, happy customers</p>
            </div>
        </div>
        
        <div class="leadership-section">
            <h2 class="text-center mb-5">Leadership Team</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="leadership-member">
                        <div class="leadership-photo">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>Mary Johnson</h4>
                        <p class="mb-2">Founder & CEO</p>
                        <p class="small">Visionary leader with 15+ years in education retail. Mary founded SmartSchool Uniforms in 2015 with a mission to transform school uniform shopping in Kenya. Her passion for quality education and customer service drives our company's growth and success.</p>
                        <div class="social-links mt-3">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="leadership-member">
                        <div class="leadership-photo">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>James Kimani</h4>
                        <p class="mb-2">Operations Director</p>
                        <p class="small">Expert in supply chain management with 12 years of experience. James ensures smooth operations from procurement to delivery, maintaining our commitment to quality and timely service across all 47 counties.</p>
                        <div class="social-links mt-3">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="leadership-member">
                        <div class="leadership-photo">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>Sarah Wanjiku</h4>
                        <p class="mb-2">Head of Design & Innovation</p>
                        <p class="small">Creative designer with 10 years of experience in fashion and textiles. Sarah leads our design team in creating comfortable, durable, and stylish uniforms that students love to wear while meeting school requirements.</p>
                        <div class="social-links mt-3">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="leadership-member">
                        <div class="leadership-photo">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>David Ochieng</h4>
                        <p class="mb-2">Customer Experience Director</p>
                        <p class="small">Customer service expert with 8 years of experience. David is dedicated to ensuring every customer has an exceptional experience from browsing to delivery, leading our customer support team to excellence.</p>
                        <div class="social-links mt-3">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="team-section">
            <h2 class="text-center mb-5">Department Teams</h2>
            
            <div class="department-section">
                <div class="department-header">
                    <div class="department-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h4>Retail & Store Operations</h4>
                    <p class="text-muted">Our frontline team serving customers in physical stores</p>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Grace Mutua</h5>
                            <div class="position">Store Manager - Nairobi</div>
                            <div class="bio">8 years in retail management, ensuring excellent customer service and store operations at our flagship Nairobi location.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Michael Kamau</h5>
                            <div class="position">Store Manager - Mombasa</div>
                            <div class="bio">6 years of experience leading our coastal region operations, serving schools and parents with dedication.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Patricia Achieng</h5>
                            <div class="position">Store Manager - Kisumu</div>
                            <div class="bio">5 years managing our Lake Region store, building strong relationships with schools and communities.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="department-section">
                <div class="department-header">
                    <div class="department-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <h4>Digital & E-commerce Team</h4>
                    <p class="text-muted">Technology experts driving our online presence</p>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Alex Mwangi</h5>
                            <div class="position">Head of E-commerce</div>
                            <div class="bio">7 years in digital commerce, leading our online platform development and digital customer experience.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Esther Wanjiru</h5>
                            <div class="position">Digital Marketing Manager</div>
                            <div class="bio">5 years in digital marketing, driving our online presence and connecting with customers across digital channels.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Samuel Kariuki</h5>
                            <div class="position">Web Developer</div>
                            <div class="bio">4 years of web development experience, maintaining and improving our e-commerce platform for optimal user experience.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-github"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="department-section">
                <div class="department-header">
                    <div class="department-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h4>Logistics & Delivery Team</h4>
                    <p class="text-muted">Ensuring timely delivery across Kenya</p>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Robert Odhiambo</h5>
                            <div class="position">Logistics Manager</div>
                            <div class="bio">10 years in logistics management, coordinating our nationwide delivery network to ensure timely service.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Nancy Chebet</h5>
                            <div class="position">Delivery Coordinator</div>
                            <div class="bio">6 years coordinating deliveries, ensuring every order reaches our customers safely and on time.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Peter Maina</h5>
                            <div class="position">Warehouse Manager</div>
                            <div class="bio">8 years managing inventory and warehouse operations, ensuring efficient order processing and stock management.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="department-section">
                <div class="department-header">
                    <div class="department-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4>Customer Support Team</h4>
                    <p class="text-muted">Dedicated to exceptional customer service</p>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Lilian Atieno</h5>
                            <div class="position">Customer Support Lead</div>
                            <div class="bio">7 years in customer service, leading our support team to provide exceptional assistance to all customers.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Benjamin Muiruri</h5>
                            <div class="position">School Relations Manager</div>
                            <div class="bio">5 years building relationships with schools, ensuring our partnership programs meet their unique needs.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="team-member">
                            <div class="team-photo">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Ruth Nduta</h5>
                            <div class="position">Quality Assurance Specialist</div>
                            <div class="bio">4 years ensuring product quality and customer satisfaction through rigorous quality control processes.</div>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="culture-section">
            <h2 class="text-center mb-5">Our Culture & Values</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="culture-value">
                        <h5><i class="fas fa-heart me-2"></i>Customer-Centric</h5>
                        <p>We put our customers at the center of everything we do, making decisions that benefit them and enhance their experience with SmartSchool Uniforms.</p>
                    </div>
                    
                    <div class="culture-value">
                        <h5><i class="fas fa-users me-2"></i>Team Collaboration</h5>
                        <p>We work together as one team, supporting each other and leveraging our diverse skills to achieve common goals and deliver exceptional results.</p>
                    </div>
                    
                    <div class="culture-value">
                        <h5><i class="fas fa-lightbulb me-2"></i>Innovation & Learning</h5>
                        <p>We embrace innovation and continuous learning, always seeking new ways to improve our products, services, and customer experience.</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="culture-value">
                        <h5><i class="fas fa-shield-alt me-2"></i>Integrity & Trust</h5>
                        <p>We conduct ourselves with the highest level of integrity, building trust with our customers, partners, and team members through honest and ethical practices.</p>
                    </div>
                    
                    <div class="culture-value">
                        <h5><i class="fas fa-award me-2"></i>Excellence</h5>
                        <p>We strive for excellence in everything we do, setting high standards for ourselves and continuously working to exceed expectations.</p>
                    </div>
                    
                    <div class="culture-value">
                        <h5><i class="fas fa-smile me-2"></i>Positive Environment</h5>
                        <p>We foster a positive, supportive work environment where team members feel valued, motivated, and empowered to do their best work.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="team-section">
            <h2 class="text-center mb-5">Join Our Team</h2>
            
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <p class="lead mb-4">We're always looking for talented, passionate individuals to join our mission of transforming school uniform shopping in Kenya.</p>
                    
                    <h5 class="mb-3">Why Work With Us?</h5>
                    <ul class="mb-4">
                        <li>Be part of a growing company with a meaningful mission</li>
                        <li>Opportunities for professional growth and development</li>
                        <li>Competitive compensation and benefits</li>
                        <li>Positive, collaborative work environment</li>
                        <li>Make a real impact on education in Kenya</li>
                    </ul>
                    
                    <h5 class="mb-3">Current Opportunities</h5>
                    <div class="mb-3">
                        <h6><i class="fas fa-briefcase me-2"></i>Sales Associate - Nairobi</h6>
                        <p class="small text-muted">Join our retail team to help customers find the perfect uniforms.</p>
                    </div>
                    <div class="mb-3">
                        <h6><i class="fas fa-truck me-2"></i>Delivery Driver - Nairobi</h6>
                        <p class="small text-muted">Help us deliver quality uniforms to families across Nairobi.</p>
                    </div>
                    <div class="mb-3">
                        <h6><i class="fas fa-headset me-2"></i>Customer Support Representative</h6>
                        <p class="small text-muted">Provide exceptional service to our customers via phone and email.</p>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="team-section text-center">
                        <div class="team-photo mx-auto mb-4">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <h4 class="mb-3">Grow With Us</h4>
                        <p class="mb-4">Join a team that's making a difference in education across Kenya. We offer competitive salaries, benefits, and opportunities for career advancement.</p>
                        
                        <div class="d-grid gap-2">
                            <a href="careers.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>View All Opportunities
                            </a>
                            <a href="mailto:careers@smartschool.com" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-envelope me-2"></i>Send Your Resume
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="join-team">
            <h2 class="mb-4">Ready to Join Our Team?</h2>
            <p class="lead mb-4">Be part of a company that's transforming education in Kenya through quality school uniforms and exceptional service.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="careers.php" class="btn btn-light btn-lg w-100">
                                <i class="fas fa-briefcase me-2"></i>Careers
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="internship.php" class="btn btn-outline-light btn-lg w-100">
                                <i class="fas fa-graduation-cap me-2"></i>Internships
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="contact.php" class="btn btn-light btn-lg w-100">
                                <i class="fas fa-phone me-2"></i>Contact HR
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
        // Animate team members on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Initially hide team members
        document.querySelectorAll('.team-member').forEach((member, index) => {
            member.style.opacity = '0';
            member.style.transform = 'translateY(30px)';
            member.style.transition = 'all 0.6s ease';
            member.style.transitionDelay = `${index * 0.1}s`;
            observer.observe(member);
        });
    </script>
</body>
</html>
