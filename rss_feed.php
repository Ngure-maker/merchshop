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
    <title>RSS Feed - SmartSchool Uniforms</title>
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
        
        .rss-header {
            background: linear-gradient(135deg, var(--accent-orange), var(--primary-amber));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .rss-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .rss-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .rss-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .feed-showcase {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .feed-item {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-amber);
            transition: all 0.3s ease;
        }
        
        .feed-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .feed-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-amber);
            margin-bottom: 0.5rem;
        }
        
        .feed-meta {
            font-size: 0.9rem;
            color: var(--neutral-gray);
            margin-bottom: 0.75rem;
        }
        
        .feed-description {
            margin-bottom: 1rem;
        }
        
        .feed-link {
            color: var(--secondary-blue);
            text-decoration: none;
            font-weight: 500;
        }
        
        .feed-link:hover {
            text-decoration: underline;
        }
        
        .subscription-options {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .feed-types {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .feed-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .feed-type-card {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .feed-type-card:hover {
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .feed-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-amber);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .feed-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .feed-count {
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }
        
        .rss-instructions {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .instruction-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background: var(--primary-amber);
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
        
        .rss-readers {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .reader-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .reader-card {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .reader-card:hover {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .reader-logo {
            font-size: 2rem;
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
        }
        
        .reader-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .reader-platform {
            font-size: 0.8rem;
            color: var(--neutral-gray);
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
            color: var(--primary-amber);
            margin-bottom: 0.5rem;
        }
        
        .technical-info {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.9rem;
            margin: 1rem 0;
        }
        
        .copy-button {
            background: var(--primary-amber);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .copy-button:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="rss-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-rss me-3"></i>RSS Feed</h1>
            <p class="lead mb-0">Stay updated with our latest news, products, and announcements</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="subscription-options">
                    <h3 class="mb-4"><i class="fas fa-bell me-2"></i>Subscribe to Our RSS Feeds</h3>
                    
                    <p>Get the latest SmartSchool Uniforms updates delivered directly to your RSS reader. Choose from multiple feed options below.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h5>Main Feed URL</h5>
                                <div class="code-block">
                                    https://smartschool.co.ke/feed
                                </div>
                                <button class="copy-button" onclick="copyToClipboard('https://smartschool.co.ke/feed')">
                                    <i class="fas fa-copy me-1"></i>Copy URL
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h5>Products Feed</h5>
                                <div class="code-block">
                                    https://smartschool.co.ke/feed/products
                                </div>
                                <button class="copy-button" onclick="copyToClipboard('https://smartschool.co.ke/feed/products')">
                                    <i class="fas fa-copy me-1"></i>Copy URL
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="feed-showcase">
                    <h3 class="mb-4">Latest Feed Items</h3>
                    
                    <div class="feed-item">
                        <div class="feed-title">New School Uniform Collection 2024</div>
                        <div class="feed-meta">
                            <i class="fas fa-calendar me-2"></i>December 15, 2023 | 
                            <i class="fas fa-user me-2"></i>SmartSchool Team
                        </div>
                        <div class="feed-description">
                            Exciting news! We've just launched our new 2024 school uniform collection with improved fabrics, better fits, and eco-friendly materials. Check out the latest styles for primary and secondary schools.
                        </div>
                        <a href="#" class="feed-link">Read more →</a>
                    </div>
                    
                    <div class="feed-item">
                        <div class="feed-title">Holiday Sale - Up to 30% Off</div>
                        <div class="feed-meta">
                            <i class="fas fa-calendar me-2"></i>December 10, 2023 | 
                            <i class="fas fa-tag me-2"></i>Promotions
                        </div>
                        <div class="feed-description">
                            Don't miss our biggest sale of the year! Get up to 30% off on selected school uniforms and accessories. Perfect time to stock up for the next school term.
                        </div>
                        <a href="#" class="feed-link">Read more →</a>
                    </div>
                    
                    <div class="feed-item">
                        <div class="feed-title">New Size Calculator App Feature</div>
                        <div class="feed-meta">
                            <i class="fas fa-calendar me-2"></i>December 5, 2023 | 
                            <i class="fas fa-mobile-alt me-2"></i>Technology
                        </div>
                        <div class="feed-description">
                            Our mobile app now features an advanced size calculator with AR technology. Find the perfect uniform size for your child with just a few taps on your phone.
                        </div>
                        <a href="#" class="feed-link">Read more →</a>
                    </div>
                    
                    <div class="feed-item">
                        <div class="feed-title">School Partnership Program Expansion</div>
                        <div class="feed-meta">
                            <i class="fas fa-calendar me-2"></i>November 28, 2023 | 
                            <i class="fas fa-graduation-cap me-2"></i>Partnerships
                        </div>
                        <div class="feed-description">
                            We're excited to announce partnerships with 15 new schools across Kenya. Schools now enjoy bulk discounts, priority delivery, and dedicated support services.
                        </div>
                        <a href="#" class="feed-link">Read more →</a>
                    </div>
                    
                    <div class="feed-item">
                        <div class="feed-title">Eco-Friendly Uniform Initiative</div>
                        <div class="feed-meta">
                            <i class="fas fa-calendar me-2"></i>November 20, 2023 | 
                            <i class="fas fa-leaf me-2"></i>Sustainability
                        </div>
                        <div class="feed-description">
                            Introducing our new line of eco-friendly school uniforms made from recycled materials. Same quality and comfort, now with environmental benefits.
                        </div>
                        <a href="#" class="feed-link">Read more →</a>
                    </div>
                </div>
                
                <div class="feed-types">
                    <h3 class="mb-4">Available Feed Types</h3>
                    
                    <div class="feed-grid">
                        <div class="feed-type-card" onclick="showFeedInfo('all')">
                            <div class="feed-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="feed-name">All Updates</div>
                            <div class="feed-count">Everything in one feed</div>
                        </div>
                        
                        <div class="feed-type-card" onclick="showFeedInfo('products')">
                            <div class="feed-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="feed-name">Products</div>
                            <div class="feed-count">New items & updates</div>
                        </div>
                        
                        <div class="feed-type-card" onclick="showFeedInfo('news')">
                            <div class="feed-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="feed-name">News</div>
                            <div class="feed-count">Company news</div>
                        </div>
                        
                        <div class="feed-type-card" onclick="showFeedInfo('promotions')">
                            <div class="feed-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="feed-name">Promotions</div>
                            <div class="feed-count">Sales & offers</div>
                        </div>
                        
                        <div class="feed-type-card" onclick="showFeedInfo('blog')">
                            <div class="feed-icon">
                                <i class="fas fa-blog"></i>
                            </div>
                            <div class="feed-name">Blog</div>
                            <div class="feed-count">Articles & guides</div>
                        </div>
                        
                        <div class="feed-type-card" onclick="showFeedInfo('events')">
                            <div class="feed-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="feed-name">Events</div>
                            <div class="feed-count">School events</div>
                        </div>
                    </div>
                </div>
                
                <div class="rss-instructions">
                    <h3 class="mb-4">How to Subscribe to RSS Feeds</h3>
                    
                    <div class="instruction-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <div class="step-title">Choose Your RSS Reader</div>
                            <div class="step-description">Select an RSS reader app or service. Popular options include Feedly, Inoreader, or your email client.</div>
                        </div>
                    </div>
                    
                    <div class="instruction-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <div class="step-title">Copy Feed URL</div>
                            <div class="step-description">Copy the RSS feed URL from our subscription options above. Choose the feed type that interests you most.</div>
                        </div>
                    </div>
                    
                    <div class="instruction-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <div class="step-title">Add Feed to Reader</div>
                            <div class="step-description">Paste the URL into your RSS reader's "Add Feed" or "Subscribe" field.</div>
                        </div>
                    </div>
                    
                    <div class="instruction-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <div class="step-title">Enjoy Updates</div>
                            <div class="step-description">Your RSS reader will automatically fetch and display new updates from SmartSchool Uniforms.</div>
                        </div>
                    </div>
                </div>
                
                <div class="rss-readers">
                    <h3 class="mb-4">Popular RSS Readers</h3>
                    
                    <div class="reader-grid">
                        <div class="reader-card">
                            <div class="reader-logo">
                                <i class="fas fa-rss"></i>
                            </div>
                            <div class="reader-name">Feedly</div>
                            <div class="reader-platform">Web, iOS, Android</div>
                        </div>
                        
                        <div class="reader-card">
                            <div class="reader-logo">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <div class="reader-name">Inoreader</div>
                            <div class="reader-platform">Web, iOS, Android</div>
                        </div>
                        
                        <div class="reader-card">
                            <div class="reader-logo">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="reader-name">Feedspot</div>
                            <div class="reader-platform">Web</div>
                        </div>
                        
                        <div class="reader-card">
                            <div class="reader-logo">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="reader-name">The Old Reader</div>
                            <div class="reader-platform">Web</div>
                        </div>
                        
                        <div class="reader-card">
                            <div class="reader-logo">
                                <i class="fas fa-th"></i>
                            </div>
                            <div class="reader-name">Feedbin</div>
                            <div class="reader-platform">Web</div>
                        </div>
                        
                        <div class="reader-card">
                            <div class="reader-logo">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="reader-name">NewsBlur</div>
                            <div class="reader-platform">Web, iOS, Android</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Pro Tip</h6>
                        <p class="mb-0">Most modern email clients like Outlook and Gmail also support RSS feeds. You can subscribe directly in your email application!</p>
                    </div>
                </div>
                
                <div class="technical-info">
                    <h3 class="mb-4">Technical Information</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Feed Format</h5>
                            <p>We support both RSS 2.0 and Atom 1.0 formats for maximum compatibility with all RSS readers.</p>
                            
                            <h5 class="mt-3">Update Frequency</h5>
                            <p>Feeds are updated in real-time as new content is published. Most RSS readers check for updates every 1-6 hours.</p>
                            
                            <h5 class="mt-3">Content Types</h5>
                            <ul>
                                <li>Full text articles</li>
                                <li>Product descriptions and images</li>
                                <li>Event announcements</li>
                                <li>Promotional offers</li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Feed URLs</h5>
                            <div class="code-block">
                                Main Feed: https://smartschool.co.ke/feed<br>
                                Products: https://smartschool.co.ke/feed/products<br>
                                News: https://smartschool.co.ke/feed/news<br>
                                Blog: https://smartschool.co.ke/feed/blog
                            </div>
                            
                            <h5 class="mt-3">Authentication</h5>
                            <p>Public feeds require no authentication. Private feeds (for premium members) use API key authentication.</p>
                            
                            <h5 class="mt-3">Rate Limiting</h5>
                            <p>Feed requests are limited to 100 requests per hour per IP address to ensure fair usage.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="rss-section">
                    <h4 class="mb-3">RSS Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">5K+</div>
                            <h6>Active Subscribers</h6>
                            <p class="small text-muted mb-0">Growing daily</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">6</div>
                            <h6>Feed Types</h6>
                            <p class="small text-muted mb-0">Different categories</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <h6>Updates</h6>
                            <p class="small text-muted mb-0">Real-time publishing</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">100%</div>
                            <h6>Uptime</h6>
                            <p class="small text-muted mb-0">Reliable service</p>
                        </div>
                    </div>
                </div>
                
                <div class="rss-section">
                    <h4 class="mb-3">Quick Subscribe</h4>
                    
                    <div class="list-group">
                        <a href="https://feedly.com/i/subscription/feed/https://smartschool.co.ke/feed" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-rss me-2"></i>
                            <strong>Subscribe with Feedly</strong>
                            <small class="text-muted d-block">Most popular RSS reader</small>
                        </a>
                        
                        <a href="#" onclick="subscribeWithInoreader()" class="list-group-item list-group-item-action">
                            <i class="fas fa-book-reader me-2"></i>
                            <strong>Subscribe with Inoreader</strong>
                            <small class="text-muted d-block">Power user features</small>
                        </a>
                        
                        <a href="#" onclick="addToOutlook()" class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope me-2"></i>
                            <strong>Add to Outlook</strong>
                            <small class="text-muted d-block">Email subscription</small>
                        </a>
                        
                        <a href="#" onclick="addToGmail()" class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope me-2"></i>
                            <strong>Add to Gmail</strong>
                            <small class="text-muted d-block">Email subscription</small>
                        </a>
                    </div>
                </div>
                
                <div class="rss-section">
                    <h4 class="mb-3">Feed Benefits</h4>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle me-2"></i>Never Miss Updates</h6>
                        <p class="small mb-0">Get instant notifications about new products and promotions.</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-bolt me-2"></i>Fast Loading</h6>
                        <p class="small mb-0">RSS feeds load faster than visiting the website.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-shield-alt me-2"></i>Privacy Protected</h6>
                        <p class="small mb-0">No tracking or personal data required.</p>
                    </div>
                    
                    <div class="alert alert-primary">
                        <h6><i class="fas fa-mobile-alt me-2"></i>Mobile Friendly</h6>
                        <p class="small mb-0">Read updates on any device with RSS support.</p>
                    </div>
                </div>
                
                <div class="rss-section">
                    <h4 class="mb-3">Popular Feed Categories</h4>
                    
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-shopping-bag me-2"></i>
                                <strong>Products</strong>
                                <small class="text-muted d-block">New arrivals & updates</small>
                            </div>
                            <span class="badge bg-primary">Most Popular</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-tag me-2"></i>
                                <strong>Promotions</strong>
                                <small class="text-muted d-block">Sales & discounts</small>
                            </div>
                            <span class="badge bg-success">High Demand</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-newspaper me-2"></i>
                                <strong>News</strong>
                                <small class="text-muted d-block">Company updates</small>
                            </div>
                            <span class="badge bg-info">Growing</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-blog me-2"></i>
                                <strong>Blog</strong>
                                <small class="text-muted d-block">Articles & guides</small>
                            </div>
                            <span class="badge bg-warning">New</span>
                        </div>
                    </div>
                </div>
                
                <div class="rss-section">
                    <h4 class="mb-3">Need Help?</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-question-circle me-2"></i>FAQ</h6>
                        <p class="small mb-0">Check our help center for RSS setup guides and troubleshooting.</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-envelope me-2"></i>Email Support</h6>
                        <p class="small mb-0">rss@smartschool.co.ke</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-phone me-2"></i>Phone Support</h6>
                        <p class="small mb-0">+254 700 888 777</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('RSS feed URL copied to clipboard!\n\nNow you can paste it into your RSS reader.');
            }, function(err) {
                console.error('Could not copy text: ', err);
                alert('Failed to copy. Please copy the URL manually.');
            });
        }
        
        function showFeedInfo(feedType) {
            const feedInfo = {
                'all': 'All Updates Feed: Contains everything from SmartSchool Uniforms - products, news, promotions, blog posts, and events.',
                'products': 'Products Feed: New uniform arrivals, product updates, and inventory changes.',
                'news': 'News Feed: Company announcements, press releases, and important updates.',
                'promotions': 'Promotions Feed: Sales, discounts, special offers, and coupon codes.',
                'blog': 'Blog Feed: Educational articles, uniform care guides, and parent/student resources.',
                'events': 'Events Feed: School events, uniform fitting days, and community activities.'
            };
            
            alert(feedInfo[feedType] || 'Feed information not available.');
        }
        
        function subscribeWithInoreader() {
            window.open('https://www.inoreader.com/feed/https://smartschool.co.ke/feed', '_blank');
        }
        
        function addToOutlook() {
            alert('To add RSS feed to Outlook:\n\n1. Right-click "RSS Feeds" folder\n2. Select "Add a New RSS Feed"\n3. Enter: https://smartschool.co.ke/feed\n4. Click "Add"\n\nYour RSS feed will appear in Outlook!');
        }
        
        function addToGmail() {
            alert('To add RSS feed to Gmail:\n\n1. Install "RSS Feed for Gmail" extension\n2. Click the RSS icon in Gmail\n3. Add feed: https://smartschool.co.ke/feed\n4. Follow the setup instructions\n\nAlternatively, use Google Reader alternatives like Feedly.');
        }
    </script>
</body>
</html>
