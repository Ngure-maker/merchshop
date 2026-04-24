<?php
// Include environment first to ensure session is started
require_once 'config/environment.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/cart.php';
$auth = new Auth();

$db = new DBHelper();
$cart = new Cart();

$product_id = $_GET['pid'] ?? $_GET['id'] ?? 0;
$product = null;

try {
    $product = $db->fetchOne("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE (p.private_id = ? OR p.id = ?) AND p.is_active = 1
    ", [$product_id, $product_id]);
} catch (Exception $e) {
    $product = null;
}

if (!$product) {
    $product = $db->fetchOne("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.is_active = 1
    ", [$product_id]);
}

if (!$product) {
    $product = $db->fetchOne("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE (p.private_id = ? OR p.id = ?)
    ", [$product_id, $product_id]);
}

if (!$product) {
    $product = $db->fetchOne("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ", [$product_id]);
}

if (!$product) {
    header('Location: index.php');
    exit;
}

// Normalize for queries that require numeric product id
$product_numeric_id = $product['id'] ?? 0;

// Get product images for gallery functionality
$product_images = [];
if (!empty($product)) {
    $images_query = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order";
    $product_images = $db->fetchAll($images_query, [$product_numeric_id]);
}

// Get size chart
$sizes = $db->fetchAll("SELECT * FROM size_charts WHERE product_id = ? ORDER BY size", [$product_numeric_id]);

// Similar products (same category)
$similar_products = [];
if (!empty($product['category_id'])) {
    $similar_products = $db->fetchAll(
        "SELECT p.*, c.name as category_name,
                (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order LIMIT 1) as primary_image
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE p.is_active = 1
           AND p.category_id = ?
           AND p.id <> ?
         ORDER BY p.created_at DESC
         LIMIT 8",
        [$product['category_id'], $product_numeric_id]
    );
}

// Product reviews and questions
$product_reviews = [];
$review_summary = ['average_rating' => 0, 'total_reviews' => 0];
$product_questions = [];
try {
    $review_summary = $db->fetchOne(
        "SELECT AVG(rating) as average_rating, COUNT(*) as total_reviews FROM product_reviews WHERE product_id = ?",
        [$product_numeric_id]
    ) ?: $review_summary;
    $product_reviews = $db->fetchAll(
        "SELECT r.*, u.full_name FROM product_reviews r LEFT JOIN users u ON u.id = r.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC",
        [$product_numeric_id]
    );
    $product_questions = $db->fetchAll(
        "SELECT q.*, u.full_name FROM product_questions q LEFT JOIN users u ON u.id = q.user_id WHERE q.product_id = ? ORDER BY q.created_at DESC",
        [$product_numeric_id]
    );
} catch (Exception $e) {
    $product_reviews = [];
    $product_questions = [];
}

// Handle add to cart
$message = '';
$showSuccessModal = false;
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add_to_cart';

    if ($action === 'add_to_cart') {
        $size = $_POST['size'] ?? '';
        $quantity = intval($_POST['quantity'] ?? 1);

        // Check if size is required (only for products with size charts)
        $size_required = !empty($sizes);

        if ($size_required && empty($size)) {
            $message = '<div class="alert alert-danger">Please select a size</div>';
        } else {
            $result = $cart->addItem($product['id'], $size, $quantity);
            if ($result['success']) {
                $showSuccessModal = true;
                $successMessage = $result['message'];
            }
            $message = $result['success']
                ? '<div class="alert alert-success mb-3">' . $result['message'] . '</div>'
                : '<div class="alert alert-danger mb-3">' . $result['message'] . '</div>';
        }
    }

    if ($action === 'review_submit') {
        if (!isset($_SESSION['user_id'])) {
            $message = '<div class="alert alert-warning mb-3">Please sign in to submit a review.</div>';
        } else {
            $rating = (int)($_POST['rating'] ?? 0);
            $title = trim((string)($_POST['title'] ?? ''));
            $review_text = trim((string)($_POST['review_text'] ?? ''));

            if ($rating < 1 || $rating > 5) {
                $message = '<div class="alert alert-danger mb-3">Rating must be between 1 and 5.</div>';
            } else {
                $order = $db->fetchOne(
                    "SELECT o.id FROM orders o INNER JOIN order_items oi ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.payment_status = 'paid' ORDER BY o.id DESC LIMIT 1",
                    [$_SESSION['user_id'], $product_numeric_id]
                );

                if (!$order) {
                    $message = '<div class="alert alert-warning mb-3">You can only review products you have purchased.</div>';
                } else {
                    $order_id = (int)$order['id'];
                    $existing = $db->fetchOne(
                        "SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ? AND order_id = ?",
                        [$_SESSION['user_id'], $product_numeric_id, $order_id]
                    );
                    if ($existing) {
                        $db->query(
                            "UPDATE product_reviews SET rating = ?, title = ?, review_text = ? WHERE id = ? AND user_id = ?",
                            [$rating, $title, $review_text, (int)$existing['id'], $_SESSION['user_id']]
                        );
                        $message = '<div class="alert alert-success mb-3">Review updated.</div>';
                    } else {
                        $db->insert('product_reviews', [
                            'user_id' => $_SESSION['user_id'],
                            'product_id' => $product_numeric_id,
                            'order_id' => $order_id,
                            'rating' => $rating,
                            'title' => $title,
                            'review_text' => $review_text
                        ]);
                        $message = '<div class="alert alert-success mb-3">Review submitted.</div>';
                    }
                }
            }
        }
    }

    if ($action === 'question_submit') {
        $question_text = trim((string)($_POST['question_text'] ?? ''));
        $guest_name = trim((string)($_POST['guest_name'] ?? ''));
        $guest_email = trim((string)($_POST['guest_email'] ?? ''));

        if ($question_text === '') {
            $message = '<div class="alert alert-danger mb-3">Please enter your question.</div>';
        } elseif (!isset($_SESSION['user_id']) && ($guest_name === '' || $guest_email === '')) {
            $message = '<div class="alert alert-danger mb-3">Please provide your name and email.</div>';
        } else {
            $db->insert('product_questions', [
                'product_id' => $product_numeric_id,
                'user_id' => $_SESSION['user_id'] ?? null,
                'guest_name' => $guest_name !== '' ? $guest_name : null,
                'guest_email' => $guest_email !== '' ? $guest_email : null,
                'question_text' => $question_text
            ]);
            $message = '<div class="alert alert-success mb-3">Question submitted. We will get back to you soon.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        .thumbnail {
            transition: all 0.3s ease;
            border-color: #dee2e6 !important;
        }
        
        .thumbnail:hover {
            transform: scale(1.05);
            border-color: var(--primary-color) !important;
        }
        
        .thumbnail.active {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 2px rgba(6, 25, 67, 0.25);
        }
        
        .main-image img {
            transition: transform 0.3s ease;
        }
        
        .main-image img:hover {
            transform: scale(1.02);
        }
        
        /* Only unique styles not in zetech-theme.css */
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

        .rating-stars {
            color: #f1c40f;
        }

        .rating-input {
            display: flex;
            gap: 6px;
            flex-direction: row-reverse;
            justify-content: flex-start;
        }

        .rating-input input {
            display: none;
        }

        .rating-input label {
            font-size: 1.4rem;
            color: #d7d7d7;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .rating-input input:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #f1c40f;
        }

        .review-card + .review-card {
            border-top: 1px solid #e5e5e5;
            padding-top: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container py-4">
        
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="catalog.php">Catalog</a></li>
                <li class="breadcrumb-item"><a href="catalog.php?category=<?php echo $product['category_id']; ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8">
                <!-- Product Image Gallery -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-0">
                        <?php 
                        $primary_image = $product['image_url'];
                        if (!empty($product_images)) {
                            $primary_image = $product_images[0]['image_url'];
                        }
                        ?>
                        
                        <?php if ($primary_image): ?>
                            <div class="main-image position-relative">
                                <img src="<?php echo htmlspecialchars($primary_image); ?>" 
                                     class="w-100" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="height: 560px; object-fit: cover; cursor: pointer;"
                                     onclick="openLightbox(0)">
                                <?php if (count($product_images) > 1): ?>
                                    <span class="position-absolute top-0 end-0 m-2 badge bg-info">
                                        +<?php echo count($product_images) - 1; ?> more
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-tshirt fa-8x" style="color: rgb(6, 25, 67);"></i>
                                <p class="text-muted mt-3">No image available</p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Thumbnail Gallery -->
                        <?php if (!empty($product_images) && count($product_images) > 1): ?>
                            <div class="thumbnail-gallery p-3 bg-light">
                                <div class="row g-2">
                                    <?php foreach ($product_images as $index => $image): ?>
                                        <div class="col-3">
                                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                                 class="img-fluid rounded cursor-pointer thumbnail <?php echo $index === 0 ? 'border-primary' : ''; ?>"
                                                 alt="Thumbnail <?php echo $index + 1; ?>"
                                                 style="height: 60px; object-fit: cover; border: 2px solid;"
                                                 onclick="changeMainImage(<?php echo $index; ?>)"
                                                 data-main="<?php echo htmlspecialchars($image['image_url']); ?>"
                                                 data-index="<?php echo $index; ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h3 mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        
                        <div class="mb-4">
                            <?php
                                $price = (float)($product['price'] ?? 0);
                                $discount = (float)($product['discount_price'] ?? 0);
                                $has_discount = $discount > 0 && $discount < $price;
                                $final_price = $has_discount ? $discount : $price;
                                $discount_pct = $has_discount && $price > 0 ? (int)round((1 - ($discount / $price)) * 100) : null;
                            ?>
                            <span class="h2" style="color: rgb(6, 25, 67);">KSh <?php echo number_format($final_price, 2); ?></span>
                            <?php if ($has_discount): ?>
                                <span class="text-muted text-decoration-line-through ms-2">KSh <?php echo number_format($price, 2); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($product['is_deal']) || $has_discount): ?>
                                <span class="badge bg-danger ms-2"><?php echo $discount_pct ? ('-' . $discount_pct . '%') : 'Deal'; ?></span>
                            <?php endif; ?>
                            <span class="badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?> ms-2">
                                <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </div>
                        
                        <p class="mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                        
                        <?php echo $message; ?>
                        
                        <form method="POST" action="">
                            <div class="row g-3">
                                <?php if (!empty($sizes)): ?>
                                    <div class="col-12">
                                        <label for="size" class="form-label">Select Size</label>
                                        <select class="form-select" id="size" name="size" required>
                                            <option value="">Choose size...</option>
                                            <?php foreach ($sizes as $size): ?>
                                                <option value="<?php echo htmlspecialchars($size['size']); ?>">
                                                    <?php echo htmlspecialchars($size['size']); ?> 
                                                    (<?php echo htmlspecialchars($size['age_range']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php else: ?>
                                    <input type="hidden" name="size" value="">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            This item doesn't require size selection
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="col-12">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" required>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100" 
                                            <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Size Chart -->
        <?php if (!empty($sizes)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Size Chart</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Size</th>
                                        <th>Chest</th>
                                        <th>Waist</th>
                                        <th>Hips</th>
                                        <th>Height</th>
                                        <th>Age Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sizes as $size): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($size['size']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($size['chest']); ?></td>
                                            <td><?php echo htmlspecialchars($size['waist']); ?></td>
                                            <td><?php echo htmlspecialchars($size['hips']); ?></td>
                                            <td><?php echo htmlspecialchars($size['height']); ?></td>
                                            <td><?php echo htmlspecialchars($size['age_range']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reviews & Questions -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <h4 class="mb-1">Customer Reviews</h4>
                                <div class="text-muted small">Share how you like this product</div>
                            </div>
                            <div class="text-end">
                                <div class="h3 mb-0">
                                    <?php echo number_format((float)($review_summary['average_rating'] ?? 0), 1); ?>
                                    <span class="rating-stars">★★★★★</span>
                                </div>
                                <div class="text-muted small">
                                    <?php echo (int)($review_summary['total_reviews'] ?? 0); ?> reviews
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="mb-3">Write a Review</h5>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="review_submit">
                                    <div class="mb-3">
                                        <label class="form-label">Rating</label>
                                        <div class="rating-input">
                                            <input type="radio" id="rate5" name="rating" value="5" required>
                                            <label for="rate5">★</label>
                                            <input type="radio" id="rate4" name="rating" value="4">
                                            <label for="rate4">★</label>
                                            <input type="radio" id="rate3" name="rating" value="3">
                                            <label for="rate3">★</label>
                                            <input type="radio" id="rate2" name="rating" value="2">
                                            <label for="rate2">★</label>
                                            <input type="radio" id="rate1" name="rating" value="1">
                                            <label for="rate1">★</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Review Title</label>
                                        <input type="text" class="form-control" name="title" placeholder="Summarize your experience">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">What did you like?</label>
                                        <textarea class="form-control" name="review_text" rows="4" placeholder="Tell us what you loved about this product..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Review</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="mb-3">Questions & Answers</h5>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="question_submit">
                                    <div class="mb-3">
                                        <label class="form-label">Ask a Question</label>
                                        <textarea class="form-control" name="question_text" rows="3" placeholder="What would you like to know?" required></textarea>
                                    </div>
                                    <?php if (!isset($_SESSION['user_id'])): ?>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Your Name</label>
                                                <input type="text" class="form-control" name="guest_name" placeholder="Full name" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="guest_email" placeholder="name@email.com" required>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-outline-primary mt-3">Submit Question</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-3">Latest Reviews</h6>
                                <?php if (!empty($product_reviews)): ?>
                                    <?php foreach ($product_reviews as $review): ?>
                                        <div class="review-card">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong><?php echo htmlspecialchars($review['full_name'] ?? 'Customer'); ?></strong>
                                                <span class="rating-stars">
                                                    <?php echo str_repeat('★', (int)($review['rating'] ?? 0)); ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($review['title'])): ?>
                                                <div class="fw-semibold mt-1"><?php echo htmlspecialchars($review['title']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($review['review_text'])): ?>
                                                <p class="mb-1 text-muted"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($review['admin_response'])): ?>
                                                <div class="mt-2 p-2 bg-light border rounded">
                                                    <small class="text-muted d-block">Admin response</small>
                                                    <div><?php echo nl2br(htmlspecialchars($review['admin_response'])); ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($review['created_at'] ?? ''); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted">No reviews yet. Be the first to review.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-3">Customer Questions</h6>
                                <?php if (!empty($product_questions)): ?>
                                    <?php foreach ($product_questions as $question): ?>
                                        <div class="review-card">
                                            <strong>
                                                <?php echo htmlspecialchars($question['full_name'] ?? $question['guest_name'] ?? 'Customer'); ?>
                                            </strong>
                                            <p class="mb-1 text-muted"><?php echo nl2br(htmlspecialchars($question['question_text'] ?? '')); ?></p>
                                            <?php if (!empty($question['admin_response'])): ?>
                                                <div class="mt-2 p-2 bg-light border rounded">
                                                    <small class="text-muted d-block">Admin response</small>
                                                    <div><?php echo nl2br(htmlspecialchars($question['admin_response'])); ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($question['created_at'] ?? ''); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted">No questions yet. Ask the first one.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Similar Products -->
        <?php if (!empty($similar_products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="mb-0">Similar Products</h4>
                    <a href="catalog.php?category=<?php echo urlencode($product['category_id']); ?>" class="text-decoration-none">
                        View all
                    </a>
                </div>
                <div class="row g-4">
                    <?php foreach ($similar_products as $sp): ?>
                        <?php
                        $sp_image = $sp['primary_image'] ?? '';
                        if (empty($sp_image)) {
                            $sp_image = $sp['image_url'] ?? '';
                        }
                        ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <a class="text-decoration-none" href="product.php?pid=<?php echo urlencode(!empty($sp['private_id']) ? $sp['private_id'] : $sp['id']); ?>">
                                <div class="card product-card h-100 shadow-sm">
                                    <div class="card-img-top bg-light position-relative" style="overflow: hidden;">
                                        <?php if (!empty($sp_image)): ?>
                                            <img src="<?php echo htmlspecialchars($sp_image); ?>" alt="<?php echo htmlspecialchars($sp['name']); ?>" class="img-fluid" style="width: 100%; height: 200px; object-fit: cover; display: block;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-tshirt fa-3x" style="color: rgb(6, 25, 67);"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="fw-semibold" style="color: rgb(6, 25, 67);">
                                            <?php echo htmlspecialchars($sp['name']); ?>
                                        </div>
                                        <div class="text-muted small mb-2"><?php echo htmlspecialchars($sp['category_name'] ?? ''); ?></div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold" style="color: rgb(6, 25, 67);">KSh <?php echo number_format((float)$sp['price'], 2); ?></span>
                                            <span class="badge bg-<?php echo ((int)($sp['stock_quantity'] ?? 0)) > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo ((int)($sp['stock_quantity'] ?? 0)) > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'views/footer.php'; ?>

    <!-- Success Modal -->
    <div class="modal fade" id="addSuccessModal" tabindex="-1" aria-labelledby="addSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addSuccessModalLabel"><i class="fas fa-check-circle me-2"></i>Added to Cart</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0"><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
                <div class="modal-footer">
                    <a href="cart.php" class="btn btn-primary">Go to Cart</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Continue Shopping</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="cancelModalLabel"><i class="fas fa-ban me-2"></i>Action Cancelled</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">No changes were made to your cart.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

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
    // Show success modal if item added
    document.addEventListener('DOMContentLoaded', function() {
        const shouldShowSuccess = <?php echo $showSuccessModal ? 'true' : 'false'; ?>;
        if (shouldShowSuccess) {
            const successModal = new bootstrap.Modal(document.getElementById('addSuccessModal'));
            successModal.show();
        }

        const cancelButtons = document.querySelectorAll('.show-cancel-modal');
        cancelButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
                cancelModal.show();
            });
        });
    });

    // Product Gallery JavaScript
    const productImages = <?php echo json_encode($product_images); ?>;
    let currentImageIndex = 0;

    function changeMainImage(index) {
        currentImageIndex = index;
        const mainImage = document.querySelector('.main-image img');
        const thumbnail = document.querySelector(`.thumbnail[data-index="${index}"]`);

        // Update main image
        mainImage.src = thumbnail.dataset.main;
        
        // Update thumbnail active state
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active', 'border-primary');
            thumb.style.borderColor = '#dee2e6';
        });
        thumbnail.classList.add('active', 'border-primary');
        thumbnail.style.borderColor = 'var(--primary-color)';
    }

    function openLightbox(index) {
        event.stopPropagation();
        currentImageIndex = index;
        
        if (productImages.length === 0) return;
        
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const counter = document.getElementById('lightbox-counter');
        
        lightboxImage.src = productImages[index].image_url;
        counter.textContent = `${index + 1} / ${productImages.length}`;
        
        lightbox.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function navigateLightbox(direction) {
        event.stopPropagation();
        if (productImages.length === 0) return;
        
        currentImageIndex += direction;
        if (currentImageIndex < 0) {
            currentImageIndex = productImages.length - 1;
        } else if (currentImageIndex >= productImages.length) {
            currentImageIndex = 0;
        }
        
        const lightboxImage = document.getElementById('lightbox-image');
        const counter = document.getElementById('lightbox-counter');
        
        lightboxImage.src = productImages[currentImageIndex].image_url;
        counter.textContent = `${currentImageIndex + 1} / ${productImages.length}`;
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
