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
    <title>Blog - Merch Shop</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .blog-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .blog-card img {
            height: 200px;
            object-fit: cover;
        }
        
        .blog-meta {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .category-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .sidebar-widget {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .sidebar-widget h5 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Blog Posts -->
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-primary mb-3">
                    <i class="fas fa-blog me-2"></i>SmartSchool Blog
                </h1>
                <p class="lead text-muted">Tips, insights, and updates about school uniforms and education in Kenya</p>
            </div>

            <!-- Featured Post -->
            <div class="blog-card mb-4">
                <img src="https://picsum.photos/800/400?random=1" class="card-img-top" alt="Featured Post">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <span class="category-badge me-2">Featured</span>
                        <div class="blog-meta">
                            <i class="fas fa-calendar me-1"></i> November 20, 2024
                            <i class="fas fa-user ms-3 me-1"></i> Admin
                        </div>
                    </div>
                    <h2 class="card-title fw-bold mb-3">Back to School 2025: Complete Guide for Kenyan Parents</h2>
                    <p class="card-text text-muted mb-3">Everything you need to know about preparing your children for the new school year, from uniform requirements to school supplies shopping tips.</p>
                    <a href="#" class="btn btn-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>

            <!-- Recent Posts -->
            <h3 class="text-primary mb-4">
                <i class="fas fa-newspaper me-2"></i>Recent Posts
            </h3>

            <div class="row">
                <!-- Post 1 -->
                <div class="col-md-6 mb-4">
                    <div class="blog-card h-100">
                        <img src="https://picsum.photos/400/250?random=2" class="card-img-top" alt="Uniform Care">
                        <div class="card-body p-3">
                            <span class="category-badge mb-2 d-inline-block">Tips</span>
                            <h5 class="card-title fw-bold mb-2">How to Make School Uniforms Last Longer</h5>
                            <p class="card-text text-muted small mb-3">Practical tips for maintaining and extending the life of your children's school uniforms.</p>
                            <div class="blog-meta small">
                                <i class="fas fa-calendar me-1"></i> November 18, 2024
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Post 2 -->
                <div class="col-md-6 mb-4">
                    <div class="blog-card h-100">
                        <img src="https://picsum.photos/400/250?random=3" class="card-img-top" alt="Education Trends">
                        <div class="card-body p-3">
                            <span class="category-badge mb-2 d-inline-block">Education</span>
                            <h5 class="card-title fw-bold mb-2">Education Trends in Kenya 2025</h5>
                            <p class="card-text text-muted small mb-3">Latest developments in Kenyan education system and how they affect parents and students.</p>
                            <div class="blog-meta small">
                                <i class="fas fa-calendar me-1"></i> November 15, 2024
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Post 3 -->
                <div class="col-md-6 mb-4">
                    <div class="blog-card h-100">
                        <img src="https://picsum.photos/400/250?random=4" class="card-img-top" alt="Budget Shopping">
                        <div class="card-body p-3">
                            <span class="category-badge mb-2 d-inline-block">Shopping</span>
                            <h5 class="card-title fw-bold mb-2">Budget-Friendly School Shopping Guide</h5>
                            <p class="card-text text-muted small mb-3">How to save money on school supplies without compromising on quality.</p>
                            <div class="blog-meta small">
                                <i class="fas fa-calendar me-1"></i> November 12, 2024
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Post 4 -->
                <div class="col-md-6 mb-4">
                    <div class="blog-card h-100">
                        <img src="https://picsum.photos/400/250?random=5" class="card-img-top" alt="School Activities">
                        <div class="card-body p-3">
                            <span class="category-badge mb-2 d-inline-block">Activities</span>
                            <h5 class="card-title fw-bold mb-2">Extracurricular Activities Guide</h5>
                            <p class="card-text text-muted small mb-3">Best after-school activities for holistic child development in Kenya.</p>
                            <div class="blog-meta small">
                                <i class="fas fa-calendar me-1"></i> November 10, 2024
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load More Button -->
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-plus-circle me-2"></i>Load More Posts
                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Categories Widget -->
            <div class="sidebar-widget">
                <h5><i class="fas fa-folder me-2"></i>Categories</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted hover-primary"><i class="fas fa-angle-right me-2"></i>Tips & Guides (8)</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted hover-primary"><i class="fas fa-angle-right me-2"></i>Education (6)</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted hover-primary"><i class="fas fa-angle-right me-2"></i>Shopping (5)</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted hover-primary"><i class="fas fa-angle-right me-2"></i>Activities (4)</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted hover-primary"><i class="fas fa-angle-right me-2"></i>News & Updates (7)</a></li>
                </ul>
            </div>

            <!-- Popular Posts Widget -->
            <div class="sidebar-widget">
                <h5><i class="fas fa-fire me-2"></i>Popular Posts</h5>
                <div class="mb-3">
                    <div class="d-flex">
                        <img src="https://picsum.photos/80/80?random=6" class="rounded me-3" alt="Popular Post 1" style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h6 class="mb-1"><a href="#" class="text-decoration-none text-primary">Uniform Size Guide</a></h6>
                            <small class="text-muted">1,234 views</small>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex">
                        <img src="https://picsum.photos/80/80?random=7" class="rounded me-3" alt="Popular Post 2" style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h6 class="mb-1"><a href="#" class="text-decoration-none text-primary">School Shopping Tips</a></h6>
                            <small class="text-muted">987 views</small>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex">
                        <img src="https://picsum.photos/80/80?random=8" class="rounded me-3" alt="Popular Post 3" style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h6 class="mb-1"><a href="#" class="text-decoration-none text-primary">Education News 2025</a></h6>
                            <small class="text-muted">856 views</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletter Widget -->
            <div class="sidebar-widget bg-primary text-white">
                <h5 class="text-white border-bottom border-white"><i class="fas fa-envelope me-2"></i>Newsletter</h5>
                <p class="small mb-3">Get the latest posts and updates delivered to your inbox</p>
                <form action="newsletter_subscribe.php" method="POST">
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Your email" required>
                    </div>
                    <button type="submit" class="btn btn-light btn-sm w-100">Subscribe</button>
                </form>
            </div>

            <!-- Tags Widget -->
            <div class="sidebar-widget">
                <h5><i class="fas fa-tags me-2"></i>Popular Tags</h5>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-secondary">uniforms</span>
                    <span class="badge bg-secondary">education</span>
                    <span class="badge bg-secondary">shopping</span>
                    <span class="badge bg-secondary">tips</span>
                    <span class="badge bg-secondary">kenya</span>
                    <span class="badge bg-secondary">schools</span>
                    <span class="badge bg-secondary">parents</span>
                    <span class="badge bg-secondary">students</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


