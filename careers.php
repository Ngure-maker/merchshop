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
    <title>Careers - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <i class="fas fa-briefcase me-2"></i>Careers at SmartSchool
                </h1>
                <p class="lead text-muted">Join our team and help make education accessible to every Kenyan student</p>
            </div>

            <!-- Why Join Us -->
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="text-primary mb-4 text-center">Why Work With Us?</h2>
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Meaningful Impact</h5>
                                <p class="text-muted">Help thousands of students access quality education and school supplies</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Growth Opportunities</h5>
                                <p class="text-muted">Professional development, training programs, and career advancement</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Great Team</h5>
                                <p class="text-muted">Work with passionate, creative, and supportive colleagues</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Openings -->
            <div class="mb-5">
                <h2 class="text-primary mb-4">
                    <i class="fas fa-search me-2"></i>Current Openings
                </h2>
                
                <div class="row">
                    <!-- Job 1 -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h4 class="text-primary mb-2">Sales Executive</h4>
                                        <p class="text-muted mb-2">Drive sales growth and build relationships with schools and parents</p>
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <span class="badge bg-light text-dark">Full-time</span>
                                            <span class="badge bg-light text-dark">Nairobi</span>
                                            <span class="badge bg-success">Entry Level</span>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-clock me-1"></i>Posted 2 days ago
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="mb-3">
                                            <span class="fw-bold">KES 35,000 - 45,000</span>
                                            <small class="text-muted d-block">per month</small>
                                        </div>
                                        <button class="btn btn-primary" onclick="showJobDetails('sales-executive')">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job 2 -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h4 class="text-primary mb-2">Customer Service Representative</h4>
                                        <p class="text-muted mb-2">Provide excellent support to customers via phone, email, and chat</p>
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <span class="badge bg-light text-dark">Full-time</span>
                                            <span class="badge bg-light text-dark">Nairobi</span>
                                            <span class="badge bg-info">Mid Level</span>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-clock me-1"></i>Posted 5 days ago
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="mb-3">
                                            <span class="fw-bold">KES 28,000 - 35,000</span>
                                            <small class="text-muted d-block">per month</small>
                                        </div>
                                        <button class="btn btn-primary" onclick="showJobDetails('customer-service')">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job 3 -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h4 class="text-primary mb-2">Warehouse Assistant</h4>
                                        <p class="text-muted mb-2">Manage inventory, packing, and shipping of school supplies</p>
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <span class="badge bg-light text-dark">Full-time</span>
                                            <span class="badge bg-light text-dark">Nairobi</span>
                                            <span class="badge bg-success">Entry Level</span>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-clock me-1"></i>Posted 1 week ago
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="mb-3">
                                            <span class="fw-bold">KES 25,000 - 30,000</span>
                                            <small class="text-muted d-block">per month</small>
                                        </div>
                                        <button class="btn btn-primary" onclick="showJobDetails('warehouse-assistant')">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job 4 -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h4 class="text-primary mb-2">Digital Marketing Intern</h4>
                                        <p class="text-muted mb-2">Assist with social media, content creation, and online campaigns</p>
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <span class="badge bg-warning text-dark">Internship</span>
                                            <span class="badge bg-light text-dark">Nairobi</span>
                                            <span class="badge bg-success">Entry Level</span>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-clock me-1"></i>Posted 3 days ago
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="mb-3">
                                            <span class="fw-bold">KES 15,000 - 20,000</span>
                                            <small class="text-muted d-block">per month + stipend</small>
                                        </div>
                                        <button class="btn btn-primary" onclick="showJobDetails('marketing-intern')">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Process -->
            <div class="card bg-light border-0 mb-5">
                <div class="card-body p-4">
                    <h3 class="text-primary mb-4">Application Process</h3>
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="text-center">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">1</span>
                                </div>
                                <h6 class="fw-bold">Submit Application</h6>
                                <p class="small text-muted">Send your CV and cover letter</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="text-center">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">2</span>
                                </div>
                                <h6 class="fw-bold">Initial Screening</h6>
                                <p class="small text-muted">Review of your application</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="text-center">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">3</span>
                                </div>
                                <h6 class="fw-bold">Interview</h6>
                                <p class="small text-muted">Meet with our team</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="text-center">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">4</span>
                                </div>
                                <h6 class="fw-bold">Offer & Onboarding</h6>
                                <p class="small text-muted">Join the SmartSchool team</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefits -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">Employee Benefits</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-medkit fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="fw-bold">Health Insurance</h6>
                                <p class="small text-muted mb-0">Comprehensive medical coverage</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-piggy-bank fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="fw-bold">Retirement Plan</h6>
                                <p class="small text-muted mb-0">Company-matched contributions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-graduation-cap fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="fw-bold">Training & Development</h6>
                                <p class="small text-muted mb-0">Continuous learning opportunities</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-umbrella-beach fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="fw-bold">Paid Leave</h6>
                                <p class="small text-muted mb-0">Annual leave and holidays</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Openings Notice -->
            <div class="alert alert-info">
                <div class="text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5 class="mb-2">Don't See What You're Looking For?</h5>
                    <p class="mb-3">We're always looking for talented people to join our team. Send your CV to <strong>careers@smartschool.com</strong> and we'll keep you in mind for future openings.</p>
                    <a href="mailto:careers@smartschool.com" class="btn btn-primary">
                        <i class="fas fa-envelope me-2"></i>Send Your CV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showJobDetails(jobId) {
    // In a real implementation, this would show a modal with full job details
    // For now, we'll just show an alert
    alert('Job details would be displayed here. In a real implementation, this would show a modal with full job description, requirements, and application form.');
}
</script>
</body>
</html>


