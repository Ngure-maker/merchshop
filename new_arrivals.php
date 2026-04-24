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

// Get product images for gallery functionality
$product_images = [];
$new_arrivals = $db->fetchAll("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 12
");

if (!empty($new_arrivals)) {
    $product_ids = array_column($new_arrivals, 'id');
    if (!empty($product_ids)) {
        $images_query = "SELECT * FROM product_images WHERE product_id IN (" . str_repeat('?,', count($product_ids) - 1) . "?) ORDER BY sort_order";
        $product_images = $db->fetchAll($images_query, $product_ids);
        
        // Group images by product_id
        $grouped_images = [];
        foreach ($product_images as $image) {
            $grouped_images[$image['product_id']][] = $image;
        }
        $product_images = $grouped_images;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Arrivals - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-teal) 100%);
            color: white;
            padding: 4rem 0;
        }
        
        .product-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }

        /* Product Gallery Styles */
        .card-img-top {
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .card-img-top:hover {
            transform: scale(1.05);
        }

        /* Lightbox Styles */
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            cursor: pointer;
        }
        
        .lightbox-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
        }
        
        .lightbox-content img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .lightbox-close:hover {
            color: #bbb;
        }
        
        .lightbox-nav {
            position: absolute;
            top: 50%;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            padding: 16px;
            margin-top: -30px;
            user-select: none;
        }
        
        .lightbox-prev {
            left: 20px;
        }
        
        .lightbox-next {
            right: 20px;
        }
        
        .lightbox-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #f1f1f1;
            font-size: 16px;
            background: rgba(0,0,0,0.5);
            padding: 5px 10px;
            border-radius: 20px;
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
                    <i class="fas fa-sparkles me-2"></i>New Arrivals
                </h1>
                <p class="lead text-muted">Check out the latest additions to our collection</p>
            </div>

            <!-- Featured New Arrivals -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-star me-2"></i>Just In This Week
                </h3>
                
                <?php if (!empty($new_arrivals)): ?>
                    <div class="row">
                        <?php foreach ($new_arrivals as $product): ?>
                            <?php 
                            // Get all images for this product
                            $product_gallery = isset($product_images[$product['id']]) ? $product_images[$product['id']] : [];
                            $primary_image = $product['image_url'];
                            if (!empty($product_gallery)) {
                                $primary_image = $product_gallery[0]['image_url'];
                            }
                            $price = (float)($product['price'] ?? 0);
                            $discount = (float)($product['discount_price'] ?? 0);
                            $has_discount = $discount > 0 && $discount < $price;
                            $final_price = $has_discount ? $discount : $price;
                            $discount_pct = $has_discount && $price > 0 ? (int)round((1 - ($discount / $price)) * 100) : null;
                            $is_deal = !empty($product['is_deal']) || $has_discount;
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="position-relative">
                                        <?php if ($primary_image): ?>
                                            <img src="<?php echo htmlspecialchars($primary_image); ?>" 
                                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 style="height: 200px; object-fit: cover;"
                                                 onclick="openLightbox(<?php echo $product['id']; ?>, 0)">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=No+Image" 
                                                 class="card-img-top" alt="No image available">
                                        <?php endif; ?>
                                        <span class="position-absolute top-0 start-0 m-2 badge bg-danger">NEW</span>
                                        <?php if ($is_deal): ?>
                                            <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark">
                                                <?php echo $discount_pct ? ('-' . $discount_pct . '%') : 'DEAL'; ?>
                                            </span>
                                        <?php elseif (count($product_gallery) > 1): ?>
                                            <span class="position-absolute top-0 end-0 m-2 badge bg-info">+<?php echo count($product_gallery) - 1; ?> images</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title text-primary"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="card-text text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <span class="fw-bold text-primary">KSh <?php echo number_format($final_price, 2); ?></span>
                                                <?php if ($has_discount): ?>
                                                    <small class="text-muted text-decoration-line-through d-block">KSh <?php echo number_format($price, 2); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <span class="badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                    <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No new arrivals yet</h4>
                        <p class="text-muted">Check back soon for the latest additions to our collection!</p>
                        <a href="catalog.php" class="btn btn-primary">Browse All Products</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Categories -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-th-large me-2"></i>Browse New Arrivals by Category
                </h3>
                
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card bg-light border-0 h-100 text-center">
                            <div class="card-body p-3">
                                <i class="fas fa-tshirt fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold">Uniforms</h6>
                                <small class="text-muted">12 new items</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card bg-light border-0 h-100 text-center">
                            <div class="card-body p-3">
                                <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold">Books</h6>
                                <small class="text-muted">8 new items</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card bg-light border-0 h-100 text-center">
                            <div class="card-body p-3">
                                <i class="fas fa-pencil-alt fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold">Stationery</h6>
                                <small class="text-muted">15 new items</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card bg-light border-0 h-100 text-center">
                            <div class="card-body p-3">
                                <i class="fas fa-dumbbell fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold">Sports</h6>
                                <small class="text-muted">6 new items</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notify Me -->
            <div class="card bg-primary text-white">
                <div class="card-body p-4 text-center">
                    <h3 class="mb-3">
                        <i class="fas fa-bell me-2"></i>Stay Updated on New Arrivals
                    </h3>
                    <p class="mb-4">Be the first to know about new products and exclusive launches</p>
                    <form class="row justify-content-center g-3" method="POST" action="new_arrivals_notify.php">
                        <div class="col-md-6">
                            <input type="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-light btn-lg w-100">
                                Notify Me
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<!-- Lightbox HTML -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <div class="lightbox-content">
        <img id="lightbox-image" src="" alt="Product image">
    </div>
    <span class="lightbox-nav lightbox-prev" onclick="navigateLightbox(-1)">&#10094;</span>
    <span class="lightbox-nav lightbox-next" onclick="navigateLightbox(1)">&#10095;</span>
    <div class="lightbox-counter" id="lightbox-counter"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Product Gallery Lightbox
const productImages = <?php echo json_encode($product_images); ?>;
let currentProductId = null;
let currentImageIndex = 0;

function openLightbox(productId, imageIndex) {
    event.stopPropagation();
    currentProductId = productId;
    currentImageIndex = imageIndex;
    
    const images = productImages[productId] || [];
    if (images.length === 0) return;
    
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const counter = document.getElementById('lightbox-counter');
    
    lightboxImage.src = images[imageIndex].image_url;
    counter.textContent = `${imageIndex + 1} / ${images.length}`;
    
    lightbox.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.style.display = 'none';
    document.body.style.overflow = 'auto';
    currentProductId = null;
    currentImageIndex = 0;
}

function navigateLightbox(direction) {
    event.stopPropagation();
    const images = productImages[currentProductId] || [];
    if (images.length === 0) return;
    
    currentImageIndex += direction;
    if (currentImageIndex < 0) {
        currentImageIndex = images.length - 1;
    } else if (currentImageIndex >= images.length) {
        currentImageIndex = 0;
    }
    
    const lightboxImage = document.getElementById('lightbox-image');
    const counter = document.getElementById('lightbox-counter');
    
    lightboxImage.src = images[currentImageIndex].image_url;
    counter.textContent = `${currentImageIndex + 1} / ${images.length}`;
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (document.getElementById('lightbox').style.display === 'block') {
        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === 'ArrowLeft') {
            navigateLightbox(-1);
        } else if (e.key === 'ArrowRight') {
            navigateLightbox(1);
        }
    }
});
</script>
</body>
</html>

