<?php
// Use the same session handling as login
require_once 'config/environment.php';

require_once 'includes/auth.php';
require_once 'includes/cart.php';
require_once 'includes/db.php';

$auth = new Auth();

$cart = new Cart();
$db = new DBHelper();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = $_POST['product_id'] ?? '';
    $size = $_POST['size'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 1);
    
    switch ($action) {
        case 'update':
            $result = $cart->updateItem($product_id, $size, $quantity);
            break;
        case 'remove':
            $result = $cart->removeItem($product_id, $size);
            break;
        case 'clear':
            $cart->clear();
            $result = ['success' => true, 'message' => 'Cart cleared'];
            break;
    }
}

$cart_items = $cart->getCart();
$total = $cart->getTotal();

// DEBUG: Log cart results
error_log("CART PAGE - Cart class items: " . json_encode($cart_items));
error_log("CART PAGE - Cart items count: " . count($cart_items));
error_log("CART PAGE - Total: " . $total);

// Get product images for cart items
$cart_items_with_images = [];
if (!empty($cart_items)) {
    $product_ids = array_unique(array_column($cart_items, 'product_id'));
    
    if (!empty($product_ids)) {
        // Get product details including primary images
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        $products_query = "SELECT id, name, image_url FROM products WHERE id IN ($placeholders)";
        $products = $db->fetchAll($products_query, $product_ids);
        
        // Get product images for gallery
        $images_query = "SELECT * FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order";
        $product_images = $db->fetchAll($images_query, $product_ids);
        
        // Group images by product_id
        $grouped_images = [];
        foreach ($product_images as $image) {
            $grouped_images[$image['product_id']][] = $image;
        }
        
        // Create product lookup
        $product_lookup = [];
        foreach ($products as $product) {
            $product_lookup[$product['id']] = $product;
        }
        
        // Enhance cart items with image data
        foreach ($cart_items as $item_key => $item) {
            $product_id = $item['product_id'];
            $cart_items_with_images[$item_key] = $item;
            
            // Get primary image
            $primary_image = null;
            if (isset($product_lookup[$product_id])) {
                $primary_image = $product_lookup[$product_id]['image_url'];
            }
            
            // Get gallery images (prefer first gallery image over product primary)
            if (isset($grouped_images[$product_id]) && !empty($grouped_images[$product_id])) {
                $primary_image = $grouped_images[$product_id][0]['image_url'];
                $cart_items_with_images[$item_key]['gallery_images'] = $grouped_images[$product_id];
            } else {
                $cart_items_with_images[$item_key]['gallery_images'] = [];
            }
            
            $cart_items_with_images[$item_key]['primary_image'] = $primary_image;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        /* Only unique styles not in zetech-theme.css */
        .card-header {
            background: linear-gradient(135deg, #FFFFFF, #F5F5F5);
            border-bottom: 2px solid var(--primary-color);
        }
        .card-title {
            font-weight: 600;
            color: var(--neutral-gray);
        }
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        
        /* Cart Product Image Styles */
        .cart-product-image {
            transition: all 0.3s ease;
            border: 2px solid transparent;
            border-radius: 8px;
        }
        
        .cart-product-image:hover {
            transform: scale(1.05);
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(6, 25, 67, 0.3);
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
            background-color: rgba(0, 0, 0, 0.9);
            animation: fadeIn 0.3s ease;
        }
        
        .lightbox-content {
            position: relative;
            margin: auto;
            padding: 0;
            width: 90%;
            max-width: 800px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .lightbox-image {
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
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .lightbox-close:hover {
            color: var(--primary-color);
            background: rgba(6, 25, 67, 0.2);
        }
        
        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            padding: 0;
        }
        
        .lightbox-nav:hover {
            color: var(--primary-color);
            background: rgba(6, 25, 67, 0.3);
        }
        
        .lightbox-prev {
            left: 20px;
        }
        
        .lightbox-next {
            right: 20px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>

</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col">
                <h2 class="mb-4">Shopping Cart</h2>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h4>Your cart is empty</h4>
                        <p class="text-muted mb-4">Browse our catalog and add items to get started</p>
                        <a href="catalog.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Cart Items (<?php echo $cart->getItemCount(); ?>)</h5>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('Are you sure you want to clear your cart?')">
                                    Clear Cart
                                </button>
                            </form>
                        </div>
                        <div class="card-body p-0">
                            <?php foreach ($cart_items_with_images as $item_key => $item): ?>
                                <div class="cart-item p-3 border-bottom">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 text-center">
                                            <?php if (!empty($item['primary_image'])): ?>
                                                <a href="product.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none">
                                                    <img src="<?php echo htmlspecialchars($item['primary_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                         class="img-fluid rounded cart-product-image"
                                                         style="max-height: 60px; object-fit: cover; cursor: pointer;"
                                                         onclick="openLightbox('<?php echo $item['product_id']; ?>', 0)">
                                                </a>
                                                <?php if (!empty($item['gallery_images']) && count($item['gallery_images']) > 1): ?>
                                                    <div class="text-muted small mt-1">
                                                        +<?php echo count($item['gallery_images']) - 1; ?> more
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="product.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none">
                                                    <i class="fas fa-tshirt fa-2x" style="color: rgb(6, 25, 67);"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="mb-1">
                                                <a href="product.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </a>
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                Size: <?php echo htmlspecialchars($item['size'] ?: 'N/A'); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="h6 mb-0">KSh <?php echo number_format($item['price'], 2); ?></span>
                                        </div>
                                        <div class="col-md-2">
                                            <form method="POST" class="d-flex align-items-center">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <input type="hidden" name="size" value="<?php echo $item['size']; ?>">
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" class="form-control form-control-sm" 
                                                       onchange="this.form.submit()">
                                            </form>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <input type="hidden" name="size" value="<?php echo $item['size']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>KSh <?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>KSh 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong>KSh <?php echo number_format($total, 2); ?></strong>
                            </div>
                            <div class="d-grid">
                                <form action="checkout.php" method="get" class="m-0">
                                    <input type="hidden" name="cart_snapshot" value="<?php echo htmlspecialchars(rtrim(strtr(base64_encode(json_encode($cart_items)), '+/', '-_'), '=')); ?>">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">Proceed to Checkout</button>
                                </form>
                            </div>
                            <div class="text-center mt-3">
                                <a href="catalog.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left"></i> Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'views/footer.php'; ?>
    
    <!-- Lightbox HTML -->
    <div id="imageLightbox" class="lightbox">
        <div class="lightbox-content">
            <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
            <img id="lightboxImage" class="lightbox-image" src="" alt="">
            <button class="lightbox-nav lightbox-prev" onclick="changeImage(-1)">&#10094;</button>
            <button class="lightbox-nav lightbox-next" onclick="changeImage(1)">&#10095;</button>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Lightbox functionality
        let currentProductImages = {};
        let currentImageIndex = 0;
        let currentProductId = null;

        function openLightbox(productId, imageIndex = 0) {
            // Find the cart item with this product ID
            const cartItems = <?php echo json_encode($cart_items_with_images); ?>;
            
            if (!cartItems || !cartItems[productId]) {
                return;
            }
            
            currentProductId = productId;
            currentImageIndex = imageIndex;
            currentProductImages = cartItems[productId].gallery_images || [];
            
            // If no gallery images, use primary image
            if (currentProductImages.length === 0 && cartItems[productId].primary_image) {
                currentProductImages = [{
                    image_url: cartItems[productId].primary_image,
                    alt_text: cartItems[productId].name
                }];
            }
            
            if (currentProductImages.length === 0) {
                return;
            }
            
            updateLightboxImage();
            document.getElementById('imageLightbox').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('imageLightbox').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function changeImage(direction) {
            currentImageIndex += direction;
            
            if (currentImageIndex < 0) {
                currentImageIndex = currentProductImages.length - 1;
            } else if (currentImageIndex >= currentProductImages.length) {
                currentImageIndex = 0;
            }
            
            updateLightboxImage();
        }

        function updateLightboxImage() {
            const image = currentProductImages[currentImageIndex];
            const lightboxImage = document.getElementById('lightboxImage');
            
            if (image) {
                lightboxImage.src = image.image_url;
                lightboxImage.alt = image.alt_text || 'Product image';
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(event) {
            if (document.getElementById('imageLightbox').style.display === 'block') {
                if (event.key === 'Escape') {
                    closeLightbox();
                } else if (event.key === 'ArrowLeft') {
                    changeImage(-1);
                } else if (event.key === 'ArrowRight') {
                    changeImage(1);
                }
            }
        });

        // Close lightbox when clicking outside the image
        document.getElementById('imageLightbox').addEventListener('click', function(event) {
            if (event.target === this) {
                closeLightbox();
            }
        });
    </script>
</body>
</html>

