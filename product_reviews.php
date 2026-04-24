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
    <title>Product Reviews - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .reviews-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .reviews-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .review-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .review-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .reviewer-avatar {
            width: 50px;
            height: 50px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-weight: bold;
        }
        
        .reviewer-info {
            flex-grow: 1;
        }
        
        .reviewer-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .review-date {
            font-size: 0.8rem;
            color: var(--neutral-gray);
        }
        
        .rating {
            color: #ffc107;
            margin-bottom: 0.5rem;
        }
        
        .rating .stars {
            font-size: 1rem;
        }
        
        .review-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .review-text {
            color: var(--neutral-gray);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .review-product {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .review-product img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 1rem;
        }
        
        .helpful-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .helpful-btn {
            background: transparent;
            border: 1px solid #e9ecef;
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .helpful-btn:hover {
            border-color: var(--primary-color);
            background: var(--light-bg);
        }
        
        .helpful-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .write-review {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .star-rating {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .star-rating .star {
            font-size: 1.5rem;
            color: #e9ecef;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .star-rating .star:hover,
        .star-rating .star.active {
            color: #ffc107;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(6, 25, 67, 0.25);
        }
        
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .filter-tag {
            background: var(--light-bg);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .filter-tag:hover {
            border-color: var(--primary-color);
        }
        
        .filter-tag.active {
            background: var(--primary-color);
            color: white;
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
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .rating-distribution {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .rating-bar {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .rating-label {
            width: 60px;
            font-size: 0.8rem;
            color: var(--neutral-gray);
        }
        
        .rating-progress {
            flex-grow: 1;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            margin: 0 1rem;
            overflow: hidden;
        }
        
        .rating-fill {
            height: 100%;
            background: #ffc107;
            border-radius: 4px;
        }
        
        .rating-count {
            width: 40px;
            font-size: 0.8rem;
            color: var(--neutral-gray);
            text-align: right;
        }
        
        .product-selector {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 1rem;
        }
        
        .product-card {
            background: var(--light-bg);
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .product-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .product-card.selected {
            border-color: var(--primary-color);
            background: white;
        }
        
        .product-card img {
            width: 100%;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }
        
        .product-name {
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }
        
        .product-price {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 0.8rem;
        }
        
        .verified-badge {
            background: var(--accent-green);
            color: white;
            padding: 0.125rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            margin-left: 0.5rem;
        }
        
        .review-images {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .review-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .review-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="reviews-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-star me-3"></i>Product Reviews</h1>
            <p class="lead mb-0">Read and write reviews for SmartSchool products</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="filter-section">
                    <h5 class="mb-3">Filter Reviews</h5>
                    
                    <div class="filter-tags">
                        <div class="filter-tag active" data-filter="all">All Reviews</div>
                        <div class="filter-tag" data-filter="5">5 Stars</div>
                        <div class="filter-tag" data-filter="4">4 Stars</div>
                        <div class="filter-tag" data-filter="3">3 Stars</div>
                        <div class="filter-tag" data-filter="2">2 Stars</div>
                        <div class="filter-tag" data-filter="1">1 Star</div>
                        <div class="filter-tag" data-filter="verified">Verified Purchase</div>
                        <div class="filter-tag" data-filter="images">With Images</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-select">
                                <option>Most Recent</option>
                                <option>Most Helpful</option>
                                <option>Highest Rating</option>
                                <option>Lowest Rating</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select">
                                <option>All Products</option>
                                <option>Shirts</option>
                                <option>Trousers</option>
                                <option>Dresses</option>
                                <option>Shoes</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="reviews-section">
                    <h3 class="mb-4">Customer Reviews</h3>
                    
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-avatar">JD</div>
                            <div class="reviewer-info">
                                <div class="reviewer-name">John Doe <span class="verified-badge">Verified</span></div>
                                <div class="review-date">2 days ago</div>
                            </div>
                            <div class="rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-product">
                            <img src="https://via.placeholder.com/40x40/FF6B35/FFFFFF?text=Shirt" alt="White School Shirt">
                            <div>
                                <div class="fw-bold">White School Shirt</div>
                                <small class="text-muted">Size M • Color: White</small>
                            </div>
                        </div>
                        
                        <div class="review-title">Excellent Quality and Perfect Fit!</div>
                        <div class="review-text">
                            I'm very impressed with the quality of this school shirt. The fabric is durable yet comfortable, and it maintains its shape even after multiple washes. The fit is perfect for my son, and the color hasn't faded at all. Definitely worth the price!
                        </div>
                        
                        <div class="review-images">
                            <img src="https://via.placeholder.com/60x60/FF6B35/FFFFFF?text=Photo1" alt="Review Photo" class="review-image">
                            <img src="https://via.placeholder.com/60x60/00897B/FFFFFF?text=Photo2" alt="Review Photo" class="review-image">
                            <img src="https://via.placeholder.com/60x60/1976D2/FFFFFF?text=Photo3" alt="Review Photo" class="review-image">
                        </div>
                        
                        <div class="helpful-buttons">
                            <button class="helpful-btn">
                                <i class="fas fa-thumbs-up me-1"></i>Helpful (12)
                            </button>
                            <button class="helpful-btn">
                                <i class="fas fa-thumbs-down me-1"></i>Not Helpful (1)
                            </button>
                            <button class="helpful-btn">
                                <i class="fas fa-flag me-1"></i>Report
                            </button>
                        </div>
                    </div>
                    
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-avatar">SM</div>
                            <div class="reviewer-info">
                                <div class="reviewer-name">Sarah Mwangi <span class="verified-badge">Verified</span></div>
                                <div class="review-date">1 week ago</div>
                            </div>
                            <div class="rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-product">
                            <img src="https://via.placeholder.com/40x40/546E7A/FFFFFF?text=Trousers" alt="Grey School Trousers">
                            <div>
                                <div class="fw-bold">Grey School Trousers</div>
                                <small class="text-muted">Size 32 • Color: Grey</small>
                            </div>
                        </div>
                        
                        <div class="review-title">Great Value for Money</div>
                        <div class="review-text">
                            These trousers are well-made and comfortable. The stitching is strong, and the material is perfect for school wear - not too heavy for warm weather but substantial enough for cooler days. My only minor complaint is that they run slightly small, so I'd recommend ordering one size up.
                        </div>
                        
                        <div class="helpful-buttons">
                            <button class="helpful-btn">
                                <i class="fas fa-thumbs-up me-1"></i>Helpful (8)
                            </button>
                            <button class="helpful-btn">
                                <i class="fas fa-thumbs-down me-1"></i>Not Helpful (0)
                            </button>
                            <button class="helpful-btn">
                                <i class="fas fa-flag me-1"></i>Report
                            </button>
                        </div>
                    </div>
                    
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-avatar">AK</div>
                            <div class="reviewer-info">
                                <div class="reviewer-name">Alice Kimani</div>
                                <div class="review-date">2 weeks ago</div>
                            </div>
                            <div class="rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-product">
                            <img src="https://via.placeholder.com/40x40/000000/FFFFFF?text=Shoes" alt="Black School Shoes">
                            <div>
                                <div class="fw-bold">Black School Shoes</div>
                                <small class="text-muted">Size 7 • Color: Black</small>
                            </div>
                        </div>
                        
                        <div class="review-title">Perfect School Shoes!</div>
                        <div class="review-text">
                            These shoes exceeded my expectations! They're sturdy, comfortable, and look very professional. My daughter loves them and says they're much more comfortable than her previous pair. They've held up well to daily wear and are easy to clean. Highly recommend!
                        </div>
                        
                        <div class="review-images">
                            <img src="https://via.placeholder.com/60x60/000000/FFFFFF?text=Photo1" alt="Review Photo" class="review-image">
                            <img src="https://via.placeholder.com/60x60/7B1FA2/FFFFFF?text=Photo2" alt="Review Photo" class="review-image">
                        </div>
                        
                        <div class="helpful-buttons">
                            <button class="helpful-btn">
                                <i class="fas fa-thumbs-up me-1"></i>Helpful (15)
                            </button>
                            <button class="helpful-btn">
                                <i class="fas fa-thumbs-down me-1"></i>Not Helpful (2)
                            </button>
                            <button class="helpful-btn">
                                <i class="fas fa-flag me-1"></i>Report
                            </button>
                        </div>
                    </div>
                    
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-avatar">MO</div>
                            <div class="reviewer-info">
                                <div class="reviewer-name">Michael Odhiambo</div>
                                <div class="review-date">3 weeks ago</div>
                            </div>
                            <div class="rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-product">
                            <img src="https://via.placeholder.com/40x40/7B1FA2/FFFFFF?text=Sweater" alt="V-Neck Sweater">
                            <div>
                                <div class="fw-bold">V-Neck Sweater</div>
                                <small class="text-muted">Size L • Color: Navy</small>
                            </div>
                        </div>
                        
                        <div class="review-title">Good Quality But Sizing Issues</div>
                        <div class="review-text">
                            The sweater is well-made and the material is soft and warm. However, the sizing is inconsistent with the size chart. I ordered based on the measurements provided but it's much smaller than expected. Had to exchange for a larger size. Quality is good though, just be careful with sizing.
                        </div>
                        
                        <div class="helpful-buttons">
                            <button class="helpful-btn">
                                <i class="fas fa-thumbs-up me-1"></i>Helpful (6)
                            </button>
                            <button class="helpful-btn">
                                <i class="fas fa-thumbs-down me-1"></i>Not Helpful (1)
                            </button>
                            <button class="helpful-btn">
                                <i class="fas fa-flag me-1"></i>Report
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="write-review">
                    <h4 class="mb-4"><i class="fas fa-pen me-2"></i>Write a Review</h4>
                    
                    <form id="reviewForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Select Product</label>
                                    <div class="product-selector">
                                        <input type="text" class="form-control mb-2" placeholder="Search products..." id="productSearch">
                                        <div class="product-grid" id="productGrid">
                                            <!-- Products will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <div class="star-rating" id="starRating">
                                        <i class="fas fa-star star" data-rating="1"></i>
                                        <i class="fas fa-star star" data-rating="2"></i>
                                        <i class="fas fa-star star" data-rating="3"></i>
                                        <i class="fas fa-star star" data-rating="4"></i>
                                        <i class="fas fa-star star" data-rating="5"></i>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Review Title</label>
                                    <input type="text" class="form-control" id="reviewTitle" placeholder="Summarize your experience">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Your Review</label>
                            <textarea class="form-control" id="reviewText" rows="4" placeholder="Tell us about your experience with this product..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Upload Photos (Optional)</label>
                            <input type="file" class="form-control" id="reviewPhotos" multiple accept="image/*">
                            <small class="text-muted">Share photos of the product to help other customers</small>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="verifiedPurchase">
                            <label class="form-check-label" for="verifiedPurchase">
                                This is a verified purchase
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Review
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="reviews-section">
                    <h4 class="mb-3">Review Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">4.2</div>
                            <h6>Average Rating</h6>
                            <div class="rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">1,247</div>
                            <h6>Total Reviews</h6>
                            <p class="small text-muted mb-0">From verified customers</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">89%</div>
                            <h6>Would Recommend</h6>
                            <p class="small text-muted mb-0">Based on reviews</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">956</div>
                            <h6>Verified Purchases</h6>
                            <p class="small text-muted mb-0">Confirmed buyers</p>
                        </div>
                    </div>
                </div>
                
                <div class="rating-distribution">
                    <h5 class="mb-3">Rating Distribution</h5>
                    
                    <div class="rating-bar">
                        <div class="rating-label">5 Stars</div>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: 65%"></div>
                        </div>
                        <div class="rating-count">811</div>
                    </div>
                    
                    <div class="rating-bar">
                        <div class="rating-label">4 Stars</div>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: 20%"></div>
                        </div>
                        <div class="rating-count">249</div>
                    </div>
                    
                    <div class="rating-bar">
                        <div class="rating-label">3 Stars</div>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: 10%"></div>
                        </div>
                        <div class="rating-count">125</div>
                    </div>
                    
                    <div class="rating-bar">
                        <div class="rating-label">2 Stars</div>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: 3%"></div>
                        </div>
                        <div class="rating-count">37</div>
                    </div>
                    
                    <div class="rating-bar">
                        <div class="rating-label">1 Star</div>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: 2%"></div>
                        </div>
                        <div class="rating-count">25</div>
                    </div>
                </div>
                
                <div class="reviews-section">
                    <h4 class="mb-3">Top Rated Products</h4>
                    
                    <div class="review-product mb-2">
                        <img src="https://via.placeholder.com/40x40/FF6B35/FFFFFF?text=Shirt" alt="White School Shirt">
                        <div class="flex-grow-1">
                            <div class="fw-bold">White School Shirt</div>
                            <div class="rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <small>(4.8)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="review-product mb-2">
                        <img src="https://via.placeholder.com/40x40/000000/FFFFFF?text=Shoes" alt="Black School Shoes">
                        <div class="flex-grow-1">
                            <div class="fw-bold">Black School Shoes</div>
                            <div class="rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <small>(4.7)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="review-product mb-2">
                        <img src="https://via.placeholder.com/40x40/546E7A/FFFFFF?text=Trousers" alt="Grey School Trousers">
                        <div class="flex-grow-1">
                            <div class="fw-bold">Grey School Trousers</div>
                            <div class="rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <small>(4.5)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="reviews-section">
                    <h4 class="mb-3">Review Guidelines</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>What to Include</h6>
                        <ul class="small mb-0">
                            <li>Product quality and features</li>
                            <li>Fit and sizing information</li>
                            <li>Durability after washing/use</li>
                            <li>Value for money</li>
                            <li>Comparison with similar products</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>What to Avoid</h6>
                        <ul class="small mb-0">
                            <li>Off-topic content</li>
                            <li>Personal information</li>
                            <li>Offensive language</li>
                            <li>False or misleading information</li>
                            <li>Competitor promotions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample product data
        const products = [
            { id: 1, name: 'White School Shirt', price: 850, category: 'shirts', image: 'https://via.placeholder.com/150x60/FF6B35/FFFFFF?text=White+Shirt' },
            { id: 2, name: 'Blue School Shirt', price: 850, category: 'shirts', image: 'https://via.placeholder.com/150x60/00897B/FFFFFF?text=Blue+Shirt' },
            { id: 3, name: 'Grey School Trousers', price: 1200, category: 'trousers', image: 'https://via.placeholder.com/150x60/546E7A/FFFFFF?text=Grey+Trousers' },
            { id: 4, name: 'Black School Trousers', price: 1200, category: 'trousers', image: 'https://via.placeholder.com/150x60/000000/FFFFFF?text=Black+Trousers' },
            { id: 5, name: 'School Dress', price: 1100, category: 'dresses', image: 'https://via.placeholder.com/150x60/FF6B35/FFFFFF?text=School+Dress' },
            { id: 6, name: 'Black School Shoes', price: 1800, category: 'shoes', image: 'https://via.placeholder.com/150x60/000000/FFFFFF?text=Black+Shoes' },
            { id: 7, name: 'V-Neck Sweater', price: 950, category: 'sweaters', image: 'https://via.placeholder.com/150x60/7B1FA2/FFFFFF?text=V-Neck+Sweater' }
        ];
        
        let selectedProduct = null;
        let selectedRating = 0;
        
        // Load products
        function loadProducts(search = '') {
            const productGrid = document.getElementById('productGrid');
            let filteredProducts = products;
            
            if (search) {
                filteredProducts = filteredProducts.filter(p => 
                    p.name.toLowerCase().includes(search.toLowerCase())
                );
            }
            
            productGrid.innerHTML = filteredProducts.map(product => `
                <div class="product-card ${selectedProduct?.id === product.id ? 'selected' : ''}" 
                     onclick="selectProduct(${product.id})">
                    <img src="${product.image}" alt="${product.name}">
                    <div class="product-name">${product.name}</div>
                    <div class="product-price">KSh ${product.price}</div>
                </div>
            `).join('');
        }
        
        // Select product
        function selectProduct(productId) {
            selectedProduct = products.find(p => p.id === productId);
            loadProducts(document.getElementById('productSearch').value);
        }
        
        // Star rating
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.dataset.rating);
                updateStarRating(selectedRating);
            });
            
            star.addEventListener('mouseenter', function() {
                const hoverRating = parseInt(this.dataset.rating);
                updateStarRating(hoverRating);
            });
        });
        
        document.getElementById('starRating').addEventListener('mouseleave', function() {
            updateStarRating(selectedRating);
        });
        
        function updateStarRating(rating) {
            document.querySelectorAll('.star').forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }
        
        // Search functionality
        document.getElementById('productSearch').addEventListener('input', function() {
            loadProducts(this.value);
        });
        
        // Form submission
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedProduct) {
                alert('Please select a product to review');
                return;
            }
            
            if (selectedRating === 0) {
                alert('Please select a rating');
                return;
            }
            
            // Here you would normally submit to backend
            alert(`Review submitted for ${selectedProduct.name} with ${selectedRating} stars!`);
            
            // Reset form
            selectedProduct = null;
            selectedRating = 0;
            this.reset();
            loadProducts();
            updateStarRating(0);
        });
        
        // Filter tags
        document.querySelectorAll('.filter-tag').forEach(tag => {
            tag.addEventListener('click', function() {
                document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                // Here you would filter the reviews based on the selected filter
            });
        });
        
        // Helpful buttons
        document.querySelectorAll('.helpful-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.classList.contains('active')) {
                    this.classList.remove('active');
                } else {
                    // Remove active from sibling buttons
                    this.parentElement.querySelectorAll('.helpful-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
        
        // Initialize
        loadProducts();
    </script>
</body>
</html>


