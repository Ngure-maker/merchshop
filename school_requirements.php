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
    <title>School Requirements - SmartSchool Uniforms</title>
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
        
        .requirements-header {
            background: linear-gradient(135deg, var(--accent-purple), var(--primary-amber));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .requirements-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .school-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .school-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .school-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .school-logo {
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
        
        .school-info {
            flex-grow: 1;
        }
        
        .school-name {
            font-weight: 600;
            color: var(--accent-purple);
            margin-bottom: 0.25rem;
        }
        
        .school-type {
            color: var(--neutral-gray);
            font-size: 0.9rem;
        }
        
        .requirement-list {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .requirement-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .requirement-item:last-child {
            border-bottom: none;
        }
        
        .requirement-icon {
            width: 30px;
            height: 30px;
            background: var(--accent-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 0.8rem;
        }
        
        .requirement-details {
            flex-grow: 1;
        }
        
        .requirement-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .requirement-spec {
            font-size: 0.8rem;
            color: var(--neutral-gray);
        }
        
        .requirement-price {
            font-weight: bold;
            color: var(--primary-amber);
        }
        
        .search-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .form-control:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 0.2rem rgba(123, 31, 162, 0.25);
        }
        
        .filter-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .filter-tag {
            background: var(--light-bg);
            color: var(--accent-purple);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .filter-tag:hover {
            border-color: var(--accent-purple);
        }
        
        .filter-tag.active {
            background: var(--accent-purple);
            color: white;
        }
        
        .package-deal {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .package-items {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .package-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .package-item:last-child {
            border-bottom: none;
        }
        
        .package-total {
            font-size: 1.5rem;
            font-weight: bold;
            border-top: 2px solid white;
            padding-top: 1rem;
            margin-top: 1rem;
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
        
        .deadline-alert {
            background: #f8d7da;
            color: #721c24;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #dc3545;
        }
        
        .deadline-alert h6 {
            margin-bottom: 0.5rem;
        }
        
        .deadline-normal {
            background: #d1ecf1;
            color: #0c5460;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #17a2b8;
        }
        
        .add-school-btn {
            background: var(--accent-purple);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .add-school-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="requirements-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-list-check me-3"></i>School Requirements</h1>
            <p class="lead mb-0">Find specific uniform requirements for your school</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="search-section">
                    <h4 class="mb-3">Find Your School</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">School Name</label>
                                <input type="text" class="form-control" placeholder="Enter school name...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <select class="form-select">
                                    <option>Select location...</option>
                                    <option>Nairobi</option>
                                    <option>Mombasa</option>
                                    <option>Kisumu</option>
                                    <option>Nakuru</option>
                                    <option>Eldoret</option>
                                    <option>Thika</option>
                                    <option>Kisii</option>
                                    <option>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">School Type</label>
                        <div class="filter-tags">
                            <div class="filter-tag active" data-type="all">All Schools</div>
                            <div class="filter-tag" data-type="primary">Primary</div>
                            <div class="filter-tag" data-type="secondary">Secondary</div>
                            <div class="filter-tag" data-type="academy">Academy</div>
                            <div class="filter-tag" data-type="international">International</div>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-2"></i>Search Schools
                    </button>
                </div>
                
                <div class="requirements-section">
                    <h3 class="mb-4">Featured Schools</h3>
                    
                    <div class="school-card">
                        <div class="school-header">
                            <div class="school-logo">
                                <i class="fas fa-school"></i>
                            </div>
                            <div class="school-info">
                                <div class="school-name">Nairobi Primary School</div>
                                <div class="school-type">Public Primary School • Nairobi</div>
                            </div>
                            <div>
                                <span class="badge bg-success">Verified</span>
                            </div>
                        </div>
                        
                        <div class="deadline-alert">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Order Deadline: January 15, 2024</h6>
                            <p class="small mb-0">Place orders before deadline to ensure delivery before term starts</p>
                        </div>
                        
                        <div class="requirement-list">
                            <h6 class="mb-3">Required Uniform Items</h6>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-tshirt"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">White School Shirt</div>
                                    <div class="requirement-spec">3 sets • Short sleeve • Cotton blend</div>
                                </div>
                                <div class="requirement-price">KSh 850</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-socks"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">Grey School Trousers</div>
                                    <div class="requirement-spec">2 pairs • Pleated • Polyester blend</div>
                                </div>
                                <div class="requirement-price">KSh 1,200</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-mitten"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">Navy Blue Sweater</div>
                                    <div class="requirement-spec">1 piece • V-neck • School logo</div>
                                </div>
                                <div class="requirement-price">KSh 950</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-shoe-prints"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">Black School Shoes</div>
                                    <div class="requirement-spec">1 pair • Leather • Non-marking sole</div>
                                </div>
                                <div class="requirement-price">KSh 1,800</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-running"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">PE Kit</div>
                                    <div class="requirement-spec">1 set • White T-shirt • Blue shorts</div>
                                </div>
                                <div class="requirement-price">KSh 1,400</div>
                            </div>
                        </div>
                        
                        <div class="package-deal">
                            <h5 class="mb-3"><i class="fas fa-gift me-2"></i>Complete Package Deal</h5>
                            <div class="package-items">
                                <div class="package-item">
                                    <span>3 White Shirts</span>
                                    <span>KSh 2,550</span>
                                </div>
                                <div class="package-item">
                                    <span>2 Grey Trousers</span>
                                    <span>KSh 2,400</span>
                                </div>
                                <div class="package-item">
                                    <span>1 Navy Sweater</span>
                                    <span>KSh 950</span>
                                </div>
                                <div class="package-item">
                                    <span>1 Black Shoes</span>
                                    <span>KSh 1,800</span>
                                </div>
                                <div class="package-item">
                                    <span>1 PE Kit</span>
                                    <span>KSh 1,400</span>
                                </div>
                                <div class="package-item">
                                    <span>3 Pairs Socks</span>
                                    <span>KSh 750</span>
                                </div>
                            </div>
                            <div class="package-total">
                                Total: KSh 9,850 <small class="text-decoration-line-through">KSh 10,850</small>
                                <span class="badge bg-light text-dark ms-2">Save KSh 1,000</span>
                            </div>
                            <button class="btn btn-light btn-lg w-100 mt-3">
                                <i class="fas fa-shopping-cart me-2"></i>Order Complete Package
                            </button>
                        </div>
                    </div>
                    
                    <div class="school-card">
                        <div class="school-header">
                            <div class="school-logo">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="school-info">
                                <div class="school-name">Alliance Girls High School</div>
                                <div class="school-type">Girls Secondary School • Kikuyu</div>
                            </div>
                            <div>
                                <span class="badge bg-success">Verified</span>
                            </div>
                        </div>
                        
                        <div class="deadline-normal">
                            <h6><i class="fas fa-info-circle me-2"></i>Order Deadline: January 20, 2024</h6>
                            <p class="small mb-0">Standard ordering timeline applies</p>
                        </div>
                        
                        <div class="requirement-list">
                            <h6 class="mb-3">Required Uniform Items</h6>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-tshirt"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">White School Blouse</div>
                                    <div class="requirement-spec">4 sets • Peter Pan collar • Cotton</div>
                                </div>
                                <div class="requirement-price">KSh 950</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">Navy Blue Skirt</div>
                                    <div class="requirement-spec">2 pieces • Pleated • Knee-length</div>
                                </div>
                                <div class="requirement-price">KSh 1,350</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">School Dress</div>
                                    <div class="requirement-spec">2 pieces • Check pattern • Formal</div>
                                </div>
                                <div class="requirement-price">KSh 1,800</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-mitten"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">Navy Blue Blazer</div>
                                    <div class="requirement-spec">1 piece • School badge • Wool blend</div>
                                </div>
                                <div class="requirement-price">KSh 2,800</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-shoe-prints"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">Black School Shoes</div>
                                    <div class="requirement-spec">1 pair • Mary Jane style • Low heel</div>
                                </div>
                                <div class="requirement-price">KSh 2,200</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="school-card">
                        <div class="school-header">
                            <div class="school-logo">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="school-info">
                                <div class="school-name">International School of Kenya</div>
                                <div class="school-type">International School • Nairobi</div>
                            </div>
                            <div>
                                <span class="badge bg-info">Premium</span>
                            </div>
                        </div>
                        
                        <div class="deadline-normal">
                            <h6><i class="fas fa-info-circle me-2"></i>Order Deadline: January 25, 2024</h6>
                            <p class="small mb-0">Extended ordering period available</p>
                        </div>
                        
                        <div class="requirement-list">
                            <h6 class="mb-3">Required Uniform Items</h6>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-tshirt"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">Polo Shirts</div>
                                    <div class="requirement-spec">6 sets • Various colors • Performance fabric</div>
                                </div>
                                <div class="requirement-price">KSh 1,200</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-socks"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">Cargo Shorts/Trousers</div>
                                    <div class="requirement-spec">3 sets • Khaki • Multi-pocket</div>
                                </div>
                                <div class="requirement-price">KSh 1,800</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-running"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">Sports Uniform</div>
                                    <div class="requirement-spec">2 sets • House colors • Technical fabric</div>
                                </div>
                                <div class="requirement-price">KSh 2,500</div>
                            </div>
                            
                            <div class="requirement-item">
                                <div class="requirement-icon">
                                    <i class="fas fa-backpack"></i>
                                </div>
                                <div class="requirement-details">
                                    <div class="requirement-name">School Backpack</div>
                                    <div class="requirement-spec">1 piece • School logo • Ergonomic</div>
                                </div>
                                <div class="requirement-price">KSh 3,500</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <button class="add-school-btn" onclick="alert('Add school feature coming soon!')">
                        <i class="fas fa-plus me-2"></i>Add Your School
                    </button>
                    <p class="text-muted">Can't find your school? Add it to our database</p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="requirements-section">
                    <h4 class="mb-3">Requirements Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">250+</div>
                            <h6>Schools Listed</h6>
                            <p class="small text-muted mb-0">Across Kenya</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">15,000</div>
                            <h6>Students Served</h6>
                            <p class="small text-muted mb-0">This year</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">98%</div>
                            <h6>School Coverage</h6>
                            <p class="small text-muted mb-0">Major counties</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <h6>Support Available</h6>
                            <p class="small text-muted mb-0">For parents</p>
                        </div>
                    </div>
                </div>
                
                <div class="requirements-section">
                    <h4 class="mb-3"><i class="fas fa-clock me-2"></i>Upcoming Deadlines</h4>
                    
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Urgent</h6>
                        <p class="small mb-0"><strong>Nairobi Primary</strong> - Jan 15 (3 days left)</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-clock me-2"></i>This Week</h6>
                        <p class="small mb-0"><strong>Alliance Girls</strong> - Jan 20 (8 days left)</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-calendar me-2"></i>Next Week</h6>
                        <p class="small mb-0"><strong>International School</strong> - Jan 25 (13 days left)</p>
                    </div>
                </div>
                
                <div class="requirements-section">
                    <h4 class="mb-3"><i class="fas fa-download me-2"></i>Download Requirements</h4>
                    
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-pdf me-2"></i>
                            Nairobi Primary - Full Requirements
                            <small class="text-muted d-block">PDF • 2.3 MB</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-pdf me-2"></i>
                            Alliance Girls - Uniform Guidelines
                            <small class="text-muted d-block">PDF • 1.8 MB</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-pdf me-2"></i>
                            ISK - Dress Code Policy
                            <small class="text-muted d-block">PDF • 3.1 MB</small>
                        </a>
                    </div>
                </div>
                
                <div class="requirements-section">
                    <h4 class="mb-3"><i class="fas fa-question-circle me-2"></i>Help & Support</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-phone me-2"></i>Need Help?</h6>
                        <p class="small mb-0">Call our school uniform specialists: <strong>+254 700 123 456</strong></p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-envelope me-2"></i>Email Support</h6>
                        <p class="small mb-0">Send requirements to: <strong>schools@smartschool.co.ke</strong></p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-comments me-2"></i>Live Chat</h6>
                        <p class="small mb-0">Chat with our team Monday-Friday, 8AM-6PM</p>
                    </div>
                </div>
                
                <div class="requirements-section">
                    <h4 class="mb-3"><i class="fas fa-lightbulb me-2"></i>Order Tips</h4>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-calendar-alt me-2"></i>Order Early</h6>
                        <p class="small mb-0">Place orders at least 2 weeks before deadline to avoid rush charges.</p>
                    </div>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-ruler me-2"></i>Measure Accurately</h6>
                        <p class="small mb-0">Use our size calculator to ensure perfect fit and avoid exchanges.</p>
                    </div>
                    
                    <div class="tip-card">
                        <h6><i class="fas fa-box me-2"></i>Bulk Orders</h6>
                        <p class="small mb-0">Save 10-15% on bulk orders. Contact us for school discounts.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter functionality
        document.querySelectorAll('.filter-tag').forEach(tag => {
            tag.addEventListener('click', function() {
                document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                // Here you would filter the school cards based on the selected type
            });
        });
    </script>
</body>
</html>
