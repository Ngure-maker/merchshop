<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include environment first to ensure session is started
require_once __DIR__ . '/config/environment.php';

$auth = null;
$db = null;
$cart = null;
$products = [];
$categories = [];
$message = '';
$showSuccessModal = false;
$successMessage = '';

try {
    require_once 'includes/auth.php';
    $auth = new Auth();
    
    require_once 'includes/db.php';
    $db = new DBHelper();
    
    require_once 'includes/cart.php';
    $cart = new Cart();

    // Handle add to cart from catalog
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
        $product_id = $_POST['product_id'];
        $size = $_POST['size'];
        $quantity = intval($_POST['quantity']);
        
        $result = $cart->addItem($product_id, $size, $quantity);
        if ($result['success']) {
            $showSuccessModal = true;
            $successMessage = $result['message'];
        }
        $message = $result['success'] 
            ? '<div class="alert alert-success mb-3">' . $result['message'] . '</div>'
            : '<div class="alert alert-danger mb-3">' . $result['message'] . '</div>';
    }

    // Get categories and products
    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
    
    // DEBUG: Show database connection status
    error_log("Catalog DEBUG - Categories count: " . count($categories));
    
    // Filter by category if specified
    $category_id = $_GET['category'] ?? '';
    $search_term = $_GET['search'] ?? '';
    $deals_only = ($_GET['deals'] ?? '') === '1';

    // Build the base query
    $query = "
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1
    ";
    $params = [];

    // Add search filter
    if (!empty($search_term)) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search_term%";
        $params[] = "%$search_term%";
    }

    // Add category filter
    if ($category_id && is_numeric($category_id)) {
        $query .= " AND p.category_id = ?";
        $params[] = $category_id;
    }

    if ($deals_only) {
        $query .= " AND (p.is_deal = 1 OR (p.discount_price IS NOT NULL AND p.discount_price > 0))";
    }

    $products = $db->fetchAll($query, $params);
    
    // DEBUG: Show products count
    error_log("Catalog DEBUG - Products count: " . count($products));

    // Add size information and images to each product
    foreach ($products as &$product) {
        $size_count = $db->fetchOne("SELECT COUNT(*) as count FROM size_charts WHERE product_id = ?", [$product['id']]);
        $product['has_sizes'] = $size_count['count'] > 0;
        
        // Get product images
        $product_images = $db->fetchAll("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY sort_order", [$product['id']]);
        $product['images'] = [];
        if (!empty($product_images)) {
            $product['images'] = array_values(array_filter(array_map(static function ($row) {
                return $row['image_url'] ?? '';
            }, $product_images)));

            if (!empty($product['images'])) {
                $product['image_url'] = $product['images'][0];
            }
        }
    }

} catch (Exception $e) {
    error_log("Catalog page error: " . $e->getMessage());
    
    // DEBUG: Show error on page
    $message = '<div class="alert alert-danger mb-3">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    
    // Fallback static data
    $categories = [
        ['id' => 1, 'name' => 'Uniforms'],
        ['id' => 2, 'name' => 'Shoes'],
        ['id' => 3, 'name' => 'Bags'],
        ['id' => 4, 'name' => 'Books'],
        ['id' => 5, 'name' => 'Stationery']
    ];
    
    $products = [
        ['id' => 1, 'name' => 'School Uniform', 'price' => 2500, 'image_url' => '', 'category_name' => 'Uniforms', 'description' => 'Quality school uniform', 'stock_quantity' => 10, 'has_sizes' => true],
        ['id' => 2, 'name' => 'School Shoes', 'price' => 1800, 'image_url' => '', 'category_name' => 'Shoes', 'description' => 'Comfortable school shoes', 'stock_quantity' => 15, 'has_sizes' => true],
        ['id' => 3, 'name' => 'School Bag', 'price' => 1200, 'image_url' => '', 'category_name' => 'Bags', 'description' => 'Durable school bag', 'stock_quantity' => 20, 'has_sizes' => false]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
    /* Only unique styles not in zetech-theme.css */
    .product-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .list-group-item.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }
    .card-header {
        background: linear-gradient(135deg, #FFFFFF, #F5F5F5);
        border-bottom: 2px solid var(--primary-color);
    }
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    .add-to-cart-btn {
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }
    .product-card:hover .add-to-cart-btn {
        opacity: 1;
        transform: translateY(0);
    }
    .product-card .card-footer {
        border-top: none;
        background: transparent;
    }
    .catalog-image-wrap {
        height: 220px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .catalog-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #f8f9fa;
    }
    .product-desc {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .read-more-link {
        font-size: 0.85rem;
        text-decoration: none;
    }
    .read-more-link:hover {
        text-decoration: underline;
    }
    
    #sizeSelectionDiv {
        transition: all 0.3s ease;
    }
    
    .size-message {
        font-size: 0.9em;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">Categories</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="catalog.php" 
                               class="list-group-item list-group-item-action <?php echo !$category_id ? 'active' : ''; ?>">
                                All Categories
                            </a>
                            <?php foreach ($categories as $category): ?>
                                <a href="catalog.php?category=<?php echo $category['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="row mb-4">
                    <div class="col">
                        <h2 class="h4">Uniform Catalog</h2>
                        <p class="text-muted">Browse our complete range of school uniforms</p>
                        <div class="d-flex gap-2 flex-wrap mt-2">
                            <?php
                                $qs = $_GET;
                                $qs['deals'] = '1';
                                $deals_url = 'catalog.php' . (count($qs) ? ('?' . http_build_query($qs)) : '');
                                $qs_all = $_GET;
                                unset($qs_all['deals']);
                                $all_url = 'catalog.php' . (count($qs_all) ? ('?' . http_build_query($qs_all)) : '');
                            ?>
                            <a href="<?php echo htmlspecialchars($all_url); ?>" class="btn btn-sm <?php echo $deals_only ? 'btn-outline-secondary' : 'btn-primary'; ?>">All Products</a>
                            <a href="<?php echo htmlspecialchars($deals_url); ?>" class="btn btn-sm <?php echo $deals_only ? 'btn-danger' : 'btn-outline-danger'; ?>">Deals Only</a>
                        </div>
                        <?php echo $message; ?>
                        
                        <!-- Search Results Info -->
                        <?php if (!empty($search_term)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-search me-2"></i>
                                Search results for "<strong><?php echo htmlspecialchars($search_term); ?></strong>":
                                <?php echo count($products); ?> products found
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($category_id)): ?>
                            <?php foreach ($categories as $category): ?>
                                <?php if ($category['id'] == $category_id): ?>
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-filter me-2"></i>
                                        Browsing category: <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                        <a href="catalog.php" class="float-end text-decoration-none">
                                            <i class="fas fa-times me-1"></i>Clear filter
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row g-4">
                    <?php if (empty($products)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-t-shirt fa-3x text-muted mb-3"></i>
                            <h5>No products found</h5>
                            <p class="text-muted">Try selecting a different category</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                                $price = (float)($product['price'] ?? 0);
                                $discount = (float)($product['discount_price'] ?? 0);
                                $has_discount = $discount > 0 && $discount < $price;
                                $final_price = $has_discount ? $discount : $price;
                                $discount_pct = $has_discount && $price > 0 ? (int)round((1 - ($discount / $price)) * 100) : null;
                                $is_deal = !empty($product['is_deal']) || $has_discount;
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card product-card h-100 shadow-sm" data-detail-url="product.php?pid=<?php echo urlencode((string)($product['private_id'] ?? $product['id'])); ?>" style="cursor: pointer;">
                                    <div class="card-img-top bg-light text-center position-relative catalog-image-wrap">
                                        <?php if ($is_deal): ?>
                                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                                <?php echo $discount_pct ? ('-' . $discount_pct . '%') : 'Deal'; ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php $card_images = $product['images'] ?? []; ?>
                                        <?php if (!empty($card_images) && count($card_images) > 1): ?>
                                            <?php $carousel_id = 'catalogCarousel' . (string)$product['id']; ?>
                                            <div id="<?php echo htmlspecialchars($carousel_id); ?>" class="carousel slide w-100" data-bs-touch="true" data-bs-interval="false">
                                                <div class="carousel-inner">
                                                    <?php foreach ($card_images as $idx => $img_url): ?>
                                                        <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                                            <img src="<?php echo htmlspecialchars($img_url); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="d-block w-100">
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo htmlspecialchars($carousel_id); ?>" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#<?php echo htmlspecialchars($carousel_id); ?>" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            </div>
                                        <?php elseif (!empty($product['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                                        <?php else: ?>
                                            <i class="fas fa-t-shirt fa-4x" style="color: rgb(6, 25, 67);"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="card-text text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                        <p class="card-text flex-grow-1 product-desc"><?php echo htmlspecialchars($product['description']); ?></p>
                                        <a class="read-more-link" href="product.php?pid=<?php echo urlencode((string)($product['private_id'] ?? $product['id'])); ?>">Read more</a>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0" style="color: rgb(6, 25, 67);">
                                                KSh <?php echo number_format($final_price, 2); ?>
                                                <?php if ($has_discount): ?>
                                                    <small class="text-muted text-decoration-line-through d-block">KSh <?php echo number_format($price, 2); ?></small>
                                                <?php endif; ?>
                                            </span>
                                            <span class="badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-primary btn-sm add-to-cart-btn"
                                                    data-product-id="<?php echo $product['id']; ?>"
                                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                    <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>
                                            <a href="product.php?pid=<?php echo urlencode((string)($product['private_id'] ?? $product['id'])); ?>"
                                               class="btn btn-outline-primary btn-sm">
                                                <?php echo $product['has_sizes'] ? 'View Details & Sizes' : 'View Details'; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'views/footer.php'; ?>

    <!-- Add to Cart Modal -->
    <div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addToCartModalLabel">Add <span id="modalProductName"></span> to Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="catalog.php" id="addToCartForm">
                    <div class="modal-body">
                        <input type="hidden" name="add_to_cart" value="1">
                        <input type="hidden" id="modalProductId" name="product_id" value="">
                        
                        <div class="mb-3" id="sizeSelectionDiv">
                            <label for="modalSize" class="form-label">Select Size</label>
                            <select class="form-select" id="modalSize" name="size">
                                <option value="">Choose size...</option>
                            </select>
                            <input type="hidden" id="modalSizeRequired" name="size_required" value="false">
                        </div>
                        
                        <div class="mb-3">
                            <label for="modalQuantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="modalQuantity" name="quantity" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="addSuccessModal" tabindex="-1" aria-labelledby="addSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addSuccessModalLabel"><i class="fas fa-check-circle me-2"></i>Added to Cart</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="addSuccessMessage"><?php echo htmlspecialchars($successMessage); ?></p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Add to cart modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const productCards = document.querySelectorAll('.product-card[data-detail-url]');
        productCards.forEach(card => {
            card.addEventListener('click', (event) => {
                const ignore = event.target.closest('a, button, input, select, textarea, .carousel-control-prev, .carousel-control-next, .carousel-indicators');
                if (ignore) return;
                const url = card.getAttribute('data-detail-url');
                if (url) window.location.href = url;
            });
        });

        const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                const modal = new bootstrap.Modal(document.getElementById('addToCartModal'));
                document.getElementById('modalProductId').value = productId;
                document.getElementById('modalProductName').textContent = productName;
                
                // Load sizes for this product
                fetch('get_sizes.php?product_id=' + productId)
                    .then(response => response.json())
                    .then(data => {
                        const sizeSelect = document.getElementById('modalSize');
                        const sizeDiv = document.getElementById('sizeSelectionDiv');
                        const sizeRequired = document.getElementById('modalSizeRequired');
                        
                        sizeSelect.innerHTML = '';
                        
                        if (data.length > 0) {
                            // Product has sizes - show size selection
                            sizeDiv.style.display = 'block';
                            sizeSelect.required = true;
                            sizeRequired.value = 'true';
                            
                            // Add default option
                            const defaultOption = document.createElement('option');
                            defaultOption.value = '';
                            defaultOption.textContent = 'Choose size...';
                            sizeSelect.appendChild(defaultOption);
                            
                            // Add size options
                            data.forEach(size => {
                                const option = document.createElement('option');
                                option.value = size.size;
                                option.textContent = size.size + ' (' + size.age_range + ')';
                                sizeSelect.appendChild(option);
                            });
                        } else {
                            // Product doesn't have sizes - hide size selection
                            sizeDiv.style.display = 'none';
                            sizeSelect.required = false;
                            sizeRequired.value = 'false';
                            
                            // Add hidden input with empty size
                            const hiddenOption = document.createElement('option');
                            hiddenOption.value = '';
                            hiddenOption.selected = true;
                            sizeSelect.appendChild(hiddenOption);
                            
                            // Show message that no size is needed
                            const message = document.createElement('div');
                            message.className = 'size-message';
                            message.textContent = 'This item doesn\'t require size selection';
                            sizeDiv.parentNode.insertBefore(message, sizeDiv.nextSibling);
                        }
                    });
                
                modal.show();
            });
        });

        // Show success modal after add
        const shouldShowSuccess = <?php echo $showSuccessModal ? 'true' : 'false'; ?>;
        if (shouldShowSuccess) {
            const successModal = new bootstrap.Modal(document.getElementById('addSuccessModal'));
            successModal.show();
        }

        // Cancel modal trigger
        const cancelButtons = document.querySelectorAll('#addToCartModal .btn.btn-secondary');
        cancelButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
                cancelModal.show();
            });
        });
    });
    </script>
    
    <?php require_once 'views/footer.php'; ?>
</body>
</html>
