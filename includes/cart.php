<?php
require_once 'db.php';

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = new DBHelper();
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Preserve guest cart when user logs in
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
    }
    
    public function addItem($product_id, $size, $quantity = 1) {
        // Get product info
        $product = $this->db->fetchOne("
            SELECT * FROM products
            WHERE id = ? AND stock_quantity >= ? AND is_active = 1
        ", [$product_id, $quantity]);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not available or insufficient stock'];
        }

        // Check if product requires size
        $has_sizes = $this->db->fetchOne("SELECT COUNT(*) as count FROM size_charts WHERE product_id = ?", [$product_id]);
        $requires_size = $has_sizes['count'] > 0;

        // For products that require size, validate size is provided and exists
        if ($requires_size) {
            if (empty($size)) {
                return ['success' => false, 'message' => 'Size selection is required for this product'];
            }
            $size_exists = $this->db->fetchOne("
                SELECT * FROM size_charts
                WHERE product_id = ? AND size = ?
            ", [$product_id, $size]);

            if (!$size_exists) {
                return ['success' => false, 'message' => 'Invalid size selected'];
            }
        }
        
        $item_key = $product_id . '_' . ($size ?: 'default');
        
        if (isset($_SESSION['cart'][$item_key])) {
            $_SESSION['cart'][$item_key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$item_key] = [
                'product_id' => $product_id,
                'size' => $size ?: null,
                'quantity' => $quantity,
                'price' => $product['price'],
                'name' => $product['name']
            ];
        }
        
        return ['success' => true, 'message' => 'Item added to cart'];
    }
    
    public function updateItem($product_id, $size, $quantity) {
        $item_key = $product_id . '_' . ($size ?: 'default');
        
        if ($quantity <= 0) {
            $this->removeItem($product_id, $size);
            return ['success' => true, 'message' => 'Item removed from cart'];
        }
        
        if (isset($_SESSION['cart'][$item_key])) {
            // Check stock
            $product = $this->db->fetchOne("SELECT stock_quantity FROM products WHERE id = ?", [$product_id]);
            if ($product && $quantity <= $product['stock_quantity']) {
                $_SESSION['cart'][$item_key]['quantity'] = $quantity;
                return ['success' => true, 'message' => 'Cart updated'];
            } else {
                return ['success' => false, 'message' => 'Insufficient stock'];
            }
        }
        
        return ['success' => false, 'message' => 'Item not found in cart'];
    }
    
    public function removeItem($product_id, $size) {
        $item_key = $product_id . '_' . ($size ?: 'default');
        unset($_SESSION['cart'][$item_key]);
        return ['success' => true, 'message' => 'Item removed from cart'];
    }
    
    public function getCart() {
        return $_SESSION['cart'] ?? [];
    }
    
    public function getCartItems() {
        return $this->getCart();
    }
    
    public function getTotal() {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
    
    public function getItemCount() {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
    
    public function mergeGuestCart() {
        // Merge guest cart into user cart when logging in
        if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
            foreach ($_SESSION['guest_cart'] as $item_key => $item) {
                if (isset($_SESSION['cart'][$item_key])) {
                    $_SESSION['cart'][$item_key]['quantity'] += $item['quantity'];
                } else {
                    $_SESSION['cart'][$item_key] = $item;
                }
            }
            // Clear guest cart after merging
            unset($_SESSION['guest_cart']);
        }
    }
    
    public function saveGuestCart() {
        // Save current cart as guest cart before login
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $_SESSION['guest_cart'] = $_SESSION['cart'];
        }
    }
    
    public function clear() {
        $_SESSION['cart'] = [];
    }
    
    public function validateStock() {
        $errors = [];
        foreach ($_SESSION['cart'] as $item_key => $item) {
            $product = $this->db->fetchOne("SELECT stock_quantity, name FROM products WHERE id = ?", [$item['product_id']]);
            if (!$product || $product['stock_quantity'] < $item['quantity']) {
                $errors[] = "Insufficient stock for {$item['name']} (Size: {$item['size']})";
            }
        }
        return $errors;
    }
}
?>