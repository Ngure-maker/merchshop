<?php
require_once 'config/environment.php';
header('Content-Type: application/json');

require_once 'includes/cart.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = $_POST['product_id'] ?? 0;
$size = $_POST['size'] ?? '';
$quantity = intval($_POST['quantity'] ?? 1);

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$cart = new Cart();
$db = new DBHelper();

// Check if product requires size
$has_sizes = $db->fetchOne("SELECT COUNT(*) as count FROM size_charts WHERE product_id = ?", [$product_id]);
$requires_size = $has_sizes['count'] > 0;

if ($requires_size && empty($size)) {
    // Product requires size selection - return special flag
    echo json_encode([
        'success' => false, 
        'requires_size' => true,
        'message' => 'This product requires size selection',
        'redirect_url' => "product.php?id={$product_id}"
    ]);
    exit;
}

// Add to cart
$result = $cart->addItem($product_id, $size, $quantity);

// Add cart count to response
if ($result['success']) {
    $result['cart_count'] = $cart->getItemCount();
}

echo json_encode($result);
?>
