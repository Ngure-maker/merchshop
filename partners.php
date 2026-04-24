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
    <title>Partnership Opportunities - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-teal) 100%);
            color: white;
            padding: 4rem 0;
        }
        
        .card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-primary mb-3">
                    <i class="fas fa-handshake me-2"></i>Partnership Opportunities
                </h1>
                <p class="lead text-muted">Collaborate with us to make quality education accessible to all</p>
            </div>

            <!-- Partnership Types -->
            <div class="mb-5">
                <h2 class="text-primary mb-4">
                    <i class="fas fa-users me-2"></i>Partnership Programs
                </h2>
                
                <div class="row">
                    <!-- School Partnership -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <div class="text-center mb-3">
                                    <i class="fas fa-school fa-3x text-primary"></i>
                                </div>
                                <h4 class="text-primary text-center mb-3">School Partnerships</h4>
                                <p class="mb-3">Partner with schools to provide quality uniforms and supplies at discounted rates.</p>
                                <ul class="list-unstyled mb-4">
                                    <li><i class="fas fa-check text-success me-2"></i>Bulk pricing discounts</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Dedicated account manager</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Custom uniform designs</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Annual supply contracts</li>
                                </ul>
                                <div class="text-center">
                                    <button class="btn btn-primary" onclick="showPartnershipForm('school')">
                                        Become School Partner
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier Partnership -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <div class="text-center mb-3">
                                    <i class="fas fa-truck fa-3x text-primary"></i>
                                </div>
                                <h4 class="text-primary text-center mb-3">Supplier Partnerships</h4>
                                <p class="mb-3">Join our network of trusted suppliers for uniforms, books, and educational materials.</p>
                                <ul class="list-unstyled mb-4">
                                    <li><i class="fas fa-check text-success me-2"></i>Large order volumes</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Long-term contracts</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Quality assurance program</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Timely payment guarantee</li>
                                </ul>
                                <div class="text-center">
                                    <button class="btn btn-primary" onclick="showPartnershipForm('supplier')">
                                        Become Supplier
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Partnership -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <div class="text-center mb-3">
                                    <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                                </div>
                                <h4 class="text-primary text-center mb-3">Delivery Partnerships</h4>
                                <p class="mb-3">Join our delivery network and help us reach customers across Kenya.</p>
                                <ul class="list-unstyled mb-4">
                                    <li><i class="fas fa-check text-success me-2"></i>Flexible working hours</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Competitive delivery rates</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Route optimization support</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Weekly payment schedule</li>
                                </ul>
                                <div class="text-center">
                                    <button class="btn btn-primary" onclick="showPartnershipForm('delivery')">
                                        Join Delivery Network
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Technology Partnership -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <div class="text-center mb-3">
                                    <i class="fas fa-laptop-code fa-3x text-primary"></i>
                                </div>
                                <h4 class="text-primary text-center mb-3">Technology Partnerships</h4>
                                <p class="mb-3">Collaborate on innovative solutions for education and e-commerce.</p>
                                <ul class="list-unstyled mb-4">
                                    <li><i class="fas fa-check text-success me-2"></i>API integration opportunities</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Joint development projects</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Revenue sharing models</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Co-marketing initiatives</li>
                                </ul>
                                <div class="text-center">
                                    <button class="btn btn-primary" onclick="showPartnershipForm('technology')">
                                        Tech Partnership
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefits -->
            <div class="card bg-light border-0 mb-5">
                <div class="card-body p-4">
                    <h3 class="text-primary mb-4">Why Partner With SmartSchool?</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold">Market Reach</h6>
                                <p class="small text-muted">Access to 50,000+ customers across 47 counties</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <i class="fas fa-award fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold">Brand Association</h6>
                                <p class="small text-muted">Partner with a trusted education brand</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <i class="fas fa-trophy fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold">Growth Support</h6>
                                <p class="small text-muted">Marketing and operational support</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Partners -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">Our Trusted Partners</h3>
                <div class="row">
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card bg-light border-0 text-center">
                            <div class="card-body p-3">
                                <img src="https://via.placeholder.com/150x80/FF6B35/FFFFFF?text=Partner+1" 
                                     class="img-fluid mb-2" alt="Partner">
                                <h6 class="small fw-bold">Nairobi Schools Association</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card bg-light border-0 text-center">
                            <div class="card-body p-3">
                                <img src="https://via.placeholder.com/150x80/00897B/FFFFFF?text=Partner+2" 
                                     class="img-fluid mb-2" alt="Partner">
                                <h6 class="small fw-bold">Kenya Publishers Ltd</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card bg-light border-0 text-center">
                            <div class="card-body p-3">
                                <img src="https://via.placeholder.com/150x80/1976D2/FFFFFF?text=Partner+3" 
                                     class="img-fluid mb-2" alt="Partner">
                                <h6 class="small fw-bold">Swift Delivery Co.</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card bg-light border-0 text-center">
                            <div class="card-body p-3">
                                <img src="https://via.placeholder.com/150x80/388E3C/FFFFFF?text=Partner+4" 
                                     class="img-fluid mb-2" alt="Partner">
                                <h6 class="small fw-bold">EduTech Solutions</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partnership Inquiry Form -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="text-primary mb-4">Partnership Inquiry</h3>
                    
                    <?php if (isset($_SESSION['partnership_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['partnership_success']; unset($_SESSION['partnership_success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="partnership_handler.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company/Organization Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="company_name" name="company_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="partnership_type" class="form-label">Partnership Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="partnership_type" name="partnership_type" required>
                                    <option value="">Select partnership type</option>
                                    <option value="school">School Partnership</option>
                                    <option value="supplier">Supplier Partnership</option>
                                    <option value="delivery">Delivery Partnership</option>
                                    <option value="technology">Technology Partnership</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="City/County">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Tell us about your partnership proposal <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                            <label class="form-check-label" for="newsletter">
                                Send me partnership updates and opportunities
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Partnership Inquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showPartnershipForm(type) {
    // Set the partnership type and scroll to form
    document.getElementById('partnership_type').value = type;
    document.getElementById('company_name').focus();
    document.getElementById('company_name').scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>
</body>
</html>


