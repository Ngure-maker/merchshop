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
    <title>Custom Uniforms - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: rgb(6, 25, 67);
            --primary-color-dark: rgb(6, 25, 67);
            --secondary-teal: #00897B;
            --secondary-blue: #1976D2;
            --accent-purple: #7B1FA2;
            --accent-green: #388E3C;
            --neutral-gray: #546E7A;
            --light-bg: #FFF8E1;
        }
        
        .custom-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .custom-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .custom-feature {
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: var(--light-bg);
            border-radius: 8px;
        }
        
        .design-step {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .step-content h5 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .material-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .material-option:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .material-option.selected {
            border-color: var(--primary-color);
            background: var(--light-bg);
        }
        
        .custom-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #ddd;
            display: inline-block;
            margin-right: 0.5rem;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .gallery-item img {
            width: 100%;
            height: 200px;
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
            padding: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="custom-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-cut me-3"></i>Custom Uniforms</h1>
            <p class="lead mb-0">Design unique uniforms that represent your school's identity and values</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="custom-section">
            <h3 class="mb-4"><i class="fas fa-palette me-2"></i>Why Choose Custom Uniforms?</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="custom-feature">
                        <h5><i class="fas fa-university me-2"></i>School Identity</h5>
                        <p>Create a distinctive look that reflects your school's values, colors, and heritage. Custom uniforms build school pride and unity among students.</p>
                    </div>
                    
                    <div class="custom-feature">
                        <h5><i class="fas fa-shield-alt me-2"></i>Quality Materials</h5>
                        <p>Premium fabrics designed for durability, comfort, and easy maintenance. Our materials withstand daily wear and frequent washing.</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="custom-feature">
                        <h5><i class="fas fa-users me-2"></i>Inclusive Sizing</h5>
                        <p>Comprehensive size ranges ensuring every student gets the perfect fit. From small children to teenagers, we accommodate all body types.</p>
                    </div>
                    
                    <div class="custom-feature">
                        <h5><i class="fas fa-award me-2"></i>Professional Design</h5>
                        <p>Expert designers work with you to create uniforms that are both stylish and practical, meeting all educational requirements.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="custom-section">
                    <h3 class="mb-4"><i class="fas fa-tasks me-2"></i>Our Custom Design Process</h3>
                    
                    <div class="design-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h5>Consultation & Design Brief</h5>
                            <p>We meet with your school administration to understand your requirements, preferences, and budget. We discuss colors, styles, and special features.</p>
                        </div>
                    </div>
                    
                    <div class="design-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h5>Design Concept Development</h5>
                            <p>Our design team creates initial concepts and mockups based on your requirements. We provide multiple design options for your review.</p>
                        </div>
                    </div>
                    
                    <div class="design-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h5>Sample Creation & Review</h5>
                            <p>We produce physical samples of the selected design for your approval. Adjustments are made based on your feedback.</p>
                        </div>
                    </div>
                    
                    <div class="design-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h5>Final Production & Delivery</h5>
                            <p>Once approved, we proceed with bulk production. Quality control checks ensure every uniform meets our high standards.</p>
                        </div>
                    </div>
                </div>
                
                <div class="custom-section">
                    <h3 class="mb-4"><i class="fas fa-tshirt me-2"></i>Customization Options</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-paint-brush me-2"></i>Color Customization</h5>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <div class="color-preview" style="background: rgb(6, 25, 67);"></div>
                                <div class="color-preview" style="background: #00897B;"></div>
                                <div class="color-preview" style="background: #1976D2;"></div>
                                <div class="color-preview" style="background: #7B1FA2;"></div>
                                <div class="color-preview" style="background: #388E3C;"></div>
                                <div class="color-preview" style="background: #546E7A;"></div>
                                <div class="color-preview" style="background: #D32F2F;"></div>
                                <div class="color-preview" style="background: rgb(6, 25, 67);"></div>
                            </div>
                            <p class="small text-muted">Custom color matching available to match your school's exact brand colors.</p>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-font me-2"></i>Logo & Embroidery</h5>
                            <ul class="small">
                                <li>School logo embroidery</li>
                                <li>Custom text and mottos</li>
                                <li>House badges and emblems</li>
                                <li>Student name embroidery</li>
                                <li>Year group indicators</li>
                            </ul>
                        </div>
                    </div>
                    
                    <h5 class="mb-3 mt-4"><i class="fas fa-cut me-2"></i>Style Variations</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="material-option">
                                <h6>Classic Style</h6>
                                <p class="small mb-0">Traditional designs with timeless appeal</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="material-option">
                                <h6>Modern Style</h6>
                                <p class="small mb-0">Contemporary cuts and fashionable elements</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="material-option">
                                <h6>Sport Style</h6>
                                <p class="small mb-0">Athletic-inspired designs for PE uniforms</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="custom-section">
                    <h4 class="mb-3"><i class="fas fa-star me-2"></i>Premium Materials</h4>
                    
                    <div class="material-option">
                        <h6><i class="fas fa-tshirt me-2"></i>Premium Cotton Blend</h6>
                        <p class="small mb-0">60% cotton, 40% polyester for comfort and durability</p>
                    </div>
                    
                    <div class="material-option">
                        <h6><i class="fas fa-wind me-2"></i>Breathable Mesh</h6>
                        <p class="small mb-0">Perfect for sports uniforms and hot climates</p>
                    </div>
                    
                    <div class="material-option">
                        <h6><i class="fas fa-tint me-2"></i>Water-Resistant</h6>
                        <p class="small mb-0">Treated fabrics for all-weather protection</p>
                    </div>
                    
                    <div class="material-option">
                        <h6><i class="fas fa-recycle me-2"></i>Eco-Friendly</h6>
                        <p class="small mb-0">Sustainable materials for environmentally conscious schools</p>
                    </div>
                </div>
                
                <div class="custom-section">
                    <h4 class="mb-3"><i class="fas fa-images me-2"></i>Recent Projects</h4>
                    
                    <div class="gallery-item">
                        <img src="https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=Nairobi+Academy" alt="Nairobi Academy">
                        <div class="gallery-overlay">
                            <h6>Nairobi Academy</h6>
                            <small>Complete uniform redesign 2024</small>
                        </div>
                    </div>
                    
                    <div class="gallery-item">
                        <img src="https://via.placeholder.com/300x200/00897B/FFFFFF?text=St.+Mary's" alt="St. Mary's">
                        <div class="gallery-overlay">
                            <h6>St. Mary's Primary</h6>
                            <small>Custom PE uniforms</small>
                        </div>
                    </div>
                    
                    <div class="gallery-item">
                        <img src="https://via.placeholder.com/300x200/1976D2/FFFFFF?text=Excel+Learning" alt="Excel Learning">
                        <div class="gallery-overlay">
                            <h6>Excel Learning Center</h6>
                            <small>Modern uniform collection</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="custom-form">
            <h3 class="mb-4"><i class="fas fa-edit me-2"></i>Start Your Custom Uniform Project</h3>
            <p class="mb-4">Fill out the form below and our design team will contact you within 24 hours to discuss your requirements.</p>
            
            <form action="custom_uniform_request.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">School Name *</label>
                        <input type="text" class="form-control" name="school_name" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Person *</label>
                        <input type="text" class="form-control" name="contact_person" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="tel" class="form-control" name="phone" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Number of Students</label>
                        <input type="number" class="form-control" name="student_count" min="1">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Uniform Type</label>
                        <select class="form-select" name="uniform_type">
                            <option value="">Select Type</option>
                            <option value="daily">Daily Uniform</option>
                            <option value="sports">Sports/PE Uniform</option>
                            <option value="formal">Formal/Dress Uniform</option>
                            <option value="all">Complete Set</option>
                        </select>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label">Project Description</label>
                        <textarea class="form-control" name="description" rows="4" placeholder="Tell us about your custom uniform requirements..."></textarea>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label">Timeline</label>
                        <select class="form-select" name="timeline">
                            <option value="">Select Timeline</option>
                            <option value="urgent">Within 1 month</option>
                            <option value="normal">2-3 months</option>
                            <option value="flexible">4-6 months</option>
                            <option value="planning">Planning for next term</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Design Request
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg ms-2" onclick="window.location.href='tel:+254700123456'">
                            <i class="fas fa-phone me-2"></i>Call Instead
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Material option selection
        document.querySelectorAll('.material-option').forEach(option => {
            option.addEventListener('click', function() {
                this.classList.toggle('selected');
            });
        });
    </script>
</body>
</html>


