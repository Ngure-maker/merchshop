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

// Get deals products (products with discount or special pricing)
$deals_products = $db->fetchAll("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
      AND (p.is_deal = 1 OR (p.discount_price IS NOT NULL AND p.discount_price > 0))
    ORDER BY COALESCE(p.discount_price, p.price) ASC
    LIMIT 12
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers & Deals - SmartSchool Uniforms</title>
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
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-amber) 0%, var(--secondary-teal) 100%);
            color: white;
            padding: 4rem 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), #D84315);
            transform: translateY(-1px);
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
        
        .text-primary {
            color: var(--primary-amber) !important;
        }
        
        .nav-link:hover {
            color: var(--primary-amber) !important;
        }
        
        .dropdown-item:hover {
            background-color: var(--light-bg);
            color: var(--primary-amber);
        }
        
        .deal-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        
        .countdown-timer {
            background: linear-gradient(135deg, var(--primary-dark), #D84315);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
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
                    <i class="fas fa-tags me-2"></i>Special Offers & Deals
                </h1>
                <p class="lead text-muted">Amazing discounts and special promotions just for you!</p>
            </div>

            <!-- Current Promotions -->
            <div class="mb-5">
                <h2 class="text-primary mb-4">
                    <i class="fas fa-fire me-2"></i>Hot Deals This Week
                </h2>
                <?php if (empty($deals_products)): ?>
                    <div class="alert alert-info">No deals have been published yet. Check back soon!</div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($deals_products as $product): ?>
                        <?php
                            $price = (float)($product['price'] ?? 0);
                            $discount = (float)($product['discount_price'] ?? 0);
                            $has_discount = $discount > 0 && $discount < $price;
                            $final_price = $has_discount ? $discount : $price;
                            $discount_pct = $has_discount && $price > 0 ? (int)round((1 - ($discount / $price)) * 100) : null;
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-danger fs-6">
                                            <?php echo $discount_pct ? ('-' . $discount_pct . '%') : 'Deal'; ?>
                                        </span>
                                        <small class="text-muted">Limited time</small>
                                    </div>
                                    <h4 class="text-primary mb-3"><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="mb-3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($has_discount): ?>
                                                <span class="text-decoration-line-through text-muted">KES <?php echo number_format($price, 2); ?></span>
                                                <span class="fs-4 fw-bold text-primary ms-2">KES <?php echo number_format($final_price, 2); ?></span>
                                            <?php else: ?>
                                                <span class="fs-4 fw-bold text-primary">KES <?php echo number_format($final_price, 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="product.php?pid=<?php echo urlencode(!empty($product['private_id']) ? $product['private_id'] : $product['id']); ?>" class="btn btn-primary">
                                            Shop Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Seasonal Offers -->
            <div class="mb-5">
                <h2 class="text-primary mb-4">
                    <i class="fas fa-calendar-alt me-2"></i>Seasonal Promotions
                </h2>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-sun fa-3x text-warning mb-3"></i>
                                <h5 class="text-primary mb-2">Summer Holiday</h5>
                                <p class="text-muted mb-3">Get ready for the new term with our summer collection</p>
                                <small class="text-primary fw-bold">Coming in December</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                                <h5 class="text-primary mb-2">Graduation Special</h5>
                                <p class="text-muted mb-3">Special packages for graduating students</p>
                                <small class="text-primary fw-bold">November - December</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-gift fa-3x text-success mb-3"></i>
                                <h5 class="text-primary mb-2">Christmas Sale</h5>
                                <p class="text-muted mb-3">Massive discounts on all products</p>
                                <small class="text-primary fw-bold">December 15-31</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletter Signup -->
            <div class="card bg-primary text-white">
                <div class="card-body p-4 text-center">
                    <h3 class="mb-3">
                        <i class="fas fa-envelope me-2"></i>Never Miss a Deal!
                    </h3>
                    <p class="mb-4">Subscribe to our newsletter and get exclusive offers delivered to your inbox</p>
                    <form class="row justify-content-center g-3" method="POST" action="newsletter_subscribe.php">
                        <div class="col-md-6">
                            <input type="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-light btn-lg w-100">
                                Subscribe Now
                            </button>
                        </div>
                    </form>
                    <small class="mt-3 d-block">Join 50,000+ subscribers. No spam, unsubscribe anytime.</small>
                </div>
            </div>

            <!-- Terms -->
            <div class="mt-4">
                <div class="card bg-light border-0">
                    <div class="card-body p-4">
                        <h5 class="text-primary mb-3">Terms & Conditions</h5>
                        <ul class="small text-muted">
                            <li>All offers are valid while stocks last</li>
                            <li>Discounts cannot be combined unless specified</li>
                            <li>Free shipping applies to standard delivery only</li>
                            <li>SmartSchool reserves the right to modify or cancel promotions</li>
                            <li>For bulk orders, contact our sales team for custom pricing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
