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
    <title>Newsletter - SmartSchool Uniforms</title>
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
        
        .newsletter-header {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .newsletter-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .newsletter-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .newsletter-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .subscription-form {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 0.2rem rgba(123, 31, 162, 0.25);
        }
        
        .newsletter-types {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .type-card {
            background: var(--light-bg);
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .type-card:hover {
            border-color: var(--accent-purple);
            background: white;
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .type-card.selected {
            border-color: var(--accent-purple);
            background: var(--accent-purple);
            color: white;
        }
        
        .type-icon {
            width: 60px;
            height: 60px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .type-card.selected .type-icon {
            background: white;
            color: var(--accent-purple);
        }
        
        .past-newsletters {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .newsletter-item {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-green);
            transition: all 0.3s ease;
        }
        
        .newsletter-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .newsletter-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent-green);
            margin-bottom: 0.5rem;
        }
        
        .newsletter-meta {
            font-size: 0.9rem;
            color: var(--neutral-gray);
            margin-bottom: 0.75rem;
        }
        
        .newsletter-preview {
            margin-bottom: 1rem;
        }
        
        .newsletter-link {
            color: var(--secondary-blue);
            text-decoration: none;
            font-weight: 500;
        }
        
        .newsletter-link:hover {
            text-decoration: underline;
        }
        
        .benefits-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .benefit-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .benefit-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
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
            color: var(--accent-green);
            margin-bottom: 0.5rem;
        }
        
        .testimonials {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .testimonial-card {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .testimonial-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .testimonial-avatar {
            width: 50px;
            height: 50px;
            background: var(--accent-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-weight: bold;
        }
        
        .testimonial-info {
            flex-grow: 1;
        }
        
        .testimonial-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .testimonial-role {
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }
        
        .privacy-info {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="newsletter-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-envelope me-3"></i>Newsletter</h1>
            <p class="lead mb-0">Stay updated with the latest news, products, and exclusive offers</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="subscription-form">
                    <h3 class="mb-4"><i class="fas fa-paper-plane me-2"></i>Subscribe to Our Newsletter</h3>
                    
                    <p>Join thousands of parents and schools who receive our weekly newsletter with exclusive content, special offers, and uniform tips.</p>
                    
                    <form id="newsletterForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" placeholder="Your first name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" placeholder="Your last name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" placeholder="your.email@example.com" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number (Optional)</label>
                                    <input type="tel" class="form-control" placeholder="+254 700 000 000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">School Name (Optional)</label>
                                    <input type="text" class="form-control" placeholder="Your school name">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">I am a...</label>
                            <select class="form-select" required>
                                <option value="">Select your role...</option>
                                <option value="parent">Parent</option>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                                <option value="administrator">School Administrator</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Newsletter Preferences</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="weekly" checked>
                                        <label class="form-check-label" for="weekly">
                                            Weekly Newsletter
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="promotions" checked>
                                        <label class="form-check-label" for="promotions">
                                            Special Offers & Promotions
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="newproducts">
                                        <label class="form-check-label" for="newproducts">
                                            New Product Announcements
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="events">
                                        <label class="form-check-label" for="events">
                                            School Events & Activities
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="privacy" required>
                            <label class="form-check-label" for="privacy">
                                I agree to the privacy policy and consent to receive marketing emails
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-light btn-lg">
                            <i class="fas fa-envelope me-2"></i>Subscribe Now
                        </button>
                    </form>
                </div>
                
                <div class="newsletter-types">
                    <h3 class="mb-4">Choose Your Newsletter Types</h3>
                    
                    <div class="type-grid">
                        <div class="type-card selected" onclick="toggleNewsletterType(this)">
                            <div class="type-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="type-name">Weekly Digest</div>
                            <div class="type-description">News, tips, and updates</div>
                        </div>
                        
                        <div class="type-card" onclick="toggleNewsletterType(this)">
                            <div class="type-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="type-name">Promotions</div>
                            <div class="type-description">Sales and special offers</div>
                        </div>
                        
                        <div class="type-card" onclick="toggleNewsletterType(this)">
                            <div class="type-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="type-name">New Products</div>
                            <div class="type-description">Latest uniform arrivals</div>
                        </div>
                        
                        <div class="type-card" onclick="toggleNewsletterType(this)">
                            <div class="type-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="type-name">School News</div>
                            <div class="type-description">Educational content</div>
                        </div>
                        
                        <div class="type-card" onclick="toggleNewsletterType(this)">
                            <div class="type-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="type-name">Events</div>
                            <div class="type-description">School activities</div>
                        </div>
                        
                        <div class="type-card" onclick="toggleNewsletterType(this)">
                            <div class="type-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="type-name">Tips & Guides</div>
                            <div class="type-description">Uniform care advice</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Flexible Subscriptions</h6>
                        <p class="mb-0">You can change your newsletter preferences at any time. Each type has its own frequency and content focus.</p>
                    </div>
                </div>
                
                <div class="past-newsletters">
                    <h3 class="mb-4">Recent Newsletters</h3>
                    
                    <div class="newsletter-item">
                        <div class="newsletter-title">Holiday Special Edition - December 2023</div>
                        <div class="newsletter-meta">
                            <i class="fas fa-calendar me-2"></i>December 15, 2023 | 
                            <i class="fas fa-users me-2"></i>2,500 subscribers
                        </div>
                        <div class="newsletter-preview">
                            This month's holiday special features our biggest sale of the year, new winter uniform collection, and gift ideas for students. Plus, tips for uniform care during the holidays...
                        </div>
                        <a href="#" class="newsletter-link">Read full newsletter →</a>
                    </div>
                    
                    <div class="newsletter-item">
                        <div class="newsletter-title">Back to School Guide - January 2024</div>
                        <div class="newsletter-meta">
                            <i class="fas fa-calendar me-2"></i>January 5, 2024 | 
                            <i class="fas fa-users me-2"></i>3,200 subscribers
                        </div>
                        <div class="newsletter-preview">
                            Start the new term right with our comprehensive back-to-school guide. Includes uniform checklists, size calculators, and exclusive new term discounts for registered schools...
                        </div>
                        <a href="#" class="newsletter-link">Read full newsletter →</a>
                    </div>
                    
                    <div class="newsletter-item">
                        <div class="newsletter-title">Eco-Friendly Uniforms - November 2023</div>
                        <div class="newsletter-meta">
                            <i class="fas fa-calendar me-2"></i>November 20, 2023 | 
                            <i class="fas fa-users me-2"></i>2,800 subscribers
                        </div>
                        <div class="newsletter-preview">
                            Introducing our new sustainable uniform line made from recycled materials. Learn about our environmental initiatives and how you can contribute to a greener future...
                        </div>
                        <a href="#" class="newsletter-link">Read full newsletter →</a>
                    </div>
                </div>
                
                <div class="benefits-section">
                    <h3 class="mb-4">Newsletter Benefits</h3>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div>
                            <h6>Exclusive Offers</h6>
                            <p class="mb-0">Get subscriber-only discounts and early access to sales.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <h6>Stay Informed</h6>
                            <p class="mb-0">Be the first to know about new products and school requirements.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div>
                            <h6>Helpful Tips</h6>
                            <p class="mb-0">Receive expert advice on uniform care, sizing, and maintenance.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h6>Important Dates</h6>
                            <p class="mb-0">Never miss school uniform deadlines and important events.</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonials">
                    <h3 class="mb-4">What Our Subscribers Say</h3>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">SM</div>
                            <div class="testimonial-info">
                                <div class="testimonial-name">Sarah Mwangi</div>
                                <div class="testimonial-role">Parent of 3</div>
                            </div>
                        </div>
                        <p>"The weekly newsletter helps me stay on top of uniform needs and I've saved so much with the subscriber-only discounts!"</p>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">JO</div>
                            <div class="testimonial-info">
                                <div class="testimonial-name">John Odhiambo</div>
                                <div class="testimonial-role">School Administrator</div>
                            </div>
                        </div>
                        <p>"The school newsletter keeps our parents informed about uniform requirements and deadlines. It's been a game-changer for our communication."</p>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">AK</div>
                            <div class="testimonial-info">
                                <div class="testimonial-name">Alice Kimani</div>
                                <div class="testimonial-role">Parent</div>
                            </div>
                        </div>
                        <p>"I love the uniform care tips and new product announcements. The newsletter is always helpful and informative."</p>
                    </div>
                </div>
                
                <div class="privacy-info">
                    <h3 class="mb-4">Privacy & Unsubscribe</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Your Privacy Matters</h5>
                            <ul>
                                <li>We never share your email with third parties</li>
                                <li>You can unsubscribe at any time</li>
                                <li>Your data is securely stored and protected</li>
                                <li>We only send relevant, valuable content</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Manage Your Subscription</h5>
                            <p>Need to update your preferences or unsubscribe? Every newsletter includes a link to manage your subscription settings. You can also:</p>
                            <ul>
                                <li>Change newsletter types</li>
                                <li>Update your email address</li>
                                <li>Pause subscriptions temporarily</li>
                                <li>View your subscription history</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>No Spam Guarantee</h6>
                        <p class="mb-0">We hate spam as much as you do! Our newsletters are designed to provide value, not clutter your inbox.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="newsletter-section">
                    <h4 class="mb-3">Newsletter Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">15K+</div>
                            <h6>Active Subscribers</h6>
                            <p class="small text-muted mb-0">Growing weekly</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">95%</div>
                            <h6>Open Rate</h6>
                            <p class="small text-muted mb-0">Above industry average</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">6</div>
                            <h6>Newsletter Types</h6>
                            <p class="small text-muted mb-0">Customized content</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">4.8</div>
                            <h6>Subscriber Rating</h6>
                            <p class="small text-muted mb-0">Excellent feedback</p>
                        </div>
                    </div>
                </div>
                
                <div class="newsletter-section">
                    <h4 class="mb-3">Quick Subscribe</h4>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-bolt me-2"></i>Instant Benefits</h6>
                        <p class="mb-0">Subscribe now and get 10% off your first order!</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-gift me-2"></i>Welcome Gift</h6>
                        <p class="mb-0">New subscribers receive a free uniform care guide.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-star me-2"></i>Exclusive Content</h6>
                        <p class="mb-0">Get access to subscriber-only articles and tips.</p>
                    </div>
                </div>
                
                <div class="newsletter-section">
                    <h4 class="mb-3">Popular Content</h4>
                    
                    <div class="list-group">
                        <div class="list-group-item">
                            <i class="fas fa-fire me-2"></i>
                            <strong>Uniform Care Tips</strong>
                            <small class="text-muted d-block">Most viewed section</small>
                        </div>
                        
                        <div class="list-group-item">
                            <i class="fas fa-tag me-2"></i>
                            <strong>Monthly Promotions</strong>
                            <small class="text-muted d-block">Highest engagement</small>
                        </div>
                        
                        <div class="list-group-item">
                            <i class="fas fa-graduation-cap me-2"></i>
                            <strong>School Guidelines</strong>
                            <small class="text-muted d-block">Essential reading</small>
                        </div>
                        
                        <div class="list-group-item">
                            <i class="fas fa-ruler me-2"></i>
                            <strong>Size Guides</strong>
                            <small class="text-muted d-block">Very helpful</small>
                        </div>
                    </div>
                </div>
                
                <div class="newsletter-section">
                    <h4 class="mb-3">Newsletter Schedule</h4>
                    
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-newspaper me-2"></i>
                                <strong>Weekly Digest</strong>
                                <small class="text-muted d-block">Every Monday</small>
                            </div>
                            <span class="badge bg-primary">Regular</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-tag me-2"></i>
                                <strong>Promotions</strong>
                                <small class="text-muted d-block">Twice monthly</small>
                            </div>
                            <span class="badge bg-success">Special</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-shopping-bag me-2"></i>
                                <strong>New Products</strong>
                                <small class="text-muted d-block">As available</small>
                            </div>
                            <span class="badge bg-info">Variable</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-calendar-alt me-2"></i>
                                <strong>Events</strong>
                                <small class="text-muted d-block">Event-based</small>
                            </div>
                            <span class="badge bg-warning">Seasonal</span>
                        </div>
                    </div>
                </div>
                
                <div class="newsletter-section">
                    <h4 class="mb-3">Contact Newsletter Team</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-envelope me-2"></i>Email</h6>
                        <p class="small mb-0">newsletter@smartschool.co.ke</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-phone me-2"></i>Phone</h6>
                        <p class="small mb-0">+254 700 666 555</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-question-circle me-2"></i>Help</h6>
                        <p class="small mb-0">FAQ section available in every newsletter</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleNewsletterType(element) {
            element.classList.toggle('selected');
        }
        
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get selected newsletter types
            const selectedTypes = [];
            document.querySelectorAll('.type-card.selected').forEach(card => {
                const typeName = card.querySelector('.type-name').textContent;
                selectedTypes.push(typeName);
            });
            
            alert('Thank you for subscribing to our newsletter!\n\nYou will receive:\n' + selectedTypes.join('\n') + '\n\nCheck your email for a confirmation message and your 10% welcome discount!');
            this.reset();
        });
    </script>
</body>
</html>
