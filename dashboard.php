<?php
require_once 'config/environment.php';

require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireAuth();

$db = new DBHelper();
$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$flash_success = '';
$flash_error = '';

$current_user = null;
try {
    $current_user = $db->fetchOne("SELECT id, full_name, email, phone, password, profile_image FROM users WHERE id = ?", [$user_id]);
} catch (Exception $e) {
    // Backward compatibility if profile_image column doesn't exist yet
    $current_user = $db->fetchOne("SELECT id, full_name, email, phone, password FROM users WHERE id = ?", [$user_id]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    if (empty($posted_token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $posted_token)) {
        $flash_error = 'Invalid request. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $full_name = trim((string)($_POST['full_name'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));

            if ($full_name === '') {
                $flash_error = 'Full name is required.';
            } else {
                $db->update('users', [
                    'full_name' => $full_name,
                    'phone' => $phone
                ], "id = " . (int)$user_id);

                $_SESSION['full_name'] = $full_name;
                if (isset($_SESSION['user_email'])) {
                    // keep
                }
                $flash_success = 'Profile updated successfully.';
            }
        } elseif ($action === 'update_profile_image') {
            $image_data = (string)($_POST['profile_image_data'] ?? '');
            if ($image_data === '') {
                $flash_error = 'Please select a profile picture.';
            } else {
                if (strpos($image_data, 'data:image/') !== 0) {
                    $flash_error = 'Invalid image format.';
                } else {
                    $parts = explode(',', $image_data, 2);
                    if (count($parts) !== 2) {
                        $flash_error = 'Invalid image data.';
                    } else {
                        $meta = $parts[0];
                        $b64 = $parts[1];

                        $ext = 'jpg';
                        if (strpos($meta, 'image/png') !== false) {
                            $ext = 'png';
                        } elseif (strpos($meta, 'image/jpeg') !== false || strpos($meta, 'image/jpg') !== false) {
                            $ext = 'jpg';
                        } else {
                            $flash_error = 'Only PNG and JPG images are allowed.';
                        }

                        if ($flash_error === '') {
                            $raw = base64_decode($b64, true);
                            if ($raw === false) {
                                $flash_error = 'Could not decode image.';
                            } elseif (strlen($raw) > (3 * 1024 * 1024)) {
                                $flash_error = 'Image is too large (max 3MB).';
                            } else {
                                $upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile_pics';
                                if (!is_dir($upload_dir)) {
                                    @mkdir($upload_dir, 0755, true);
                                }

                                if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
                                    $flash_error = 'Upload folder is not writable.';
                                } else {
                                    $filename = 'user_' . (int)$user_id . '_' . time() . '.' . $ext;
                                    $abs_path = $upload_dir . DIRECTORY_SEPARATOR . $filename;
                                    $rel_path = 'uploads/profile_pics/' . $filename;

                                    $written = @file_put_contents($abs_path, $raw);
                                    if ($written === false) {
                                        $flash_error = 'Failed to save image.';
                                    } else {
                                        try {
                                            $db->update('users', [
                                                'profile_image' => $rel_path
                                            ], "id = " . (int)$user_id);
                                            $_SESSION['profile_image'] = $rel_path;
                                            $flash_success = 'Profile picture updated.';
                                        } catch (Exception $e) {
                                            $flash_error = 'Profile picture could not be saved. Please run create_tables.php once to update the users table.';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($action === 'change_password') {
            $current_password = (string)($_POST['current_password'] ?? '');
            $new_password = (string)($_POST['new_password'] ?? '');
            $confirm_password = (string)($_POST['confirm_password'] ?? '');

            if ($new_password === '' || strlen($new_password) < 6) {
                $flash_error = 'New password must be at least 6 characters.';
            } elseif ($new_password !== $confirm_password) {
                $flash_error = 'New password and confirmation do not match.';
            } elseif (empty($current_user) || empty($current_user['password']) || !password_verify($current_password, $current_user['password'])) {
                $flash_error = 'Current password is incorrect.';
            } else {
                $db->update('users', [
                    'password' => password_hash($new_password, PASSWORD_DEFAULT)
                ], "id = " . (int)$user_id);
                $flash_success = 'Password updated successfully.';
            }
        } elseif ($action === 'address_save') {
            $address_id = (int)($_POST['address_id'] ?? 0);
            $label = trim((string)($_POST['label'] ?? ''));
            $recipient_name = trim((string)($_POST['recipient_name'] ?? ''));
            $addr_phone = trim((string)($_POST['addr_phone'] ?? ''));
            $address_line1 = trim((string)($_POST['address_line1'] ?? ''));
            $address_line2 = trim((string)($_POST['address_line2'] ?? ''));
            $city = trim((string)($_POST['city'] ?? ''));
            $state = trim((string)($_POST['state'] ?? ''));
            $postal_code = trim((string)($_POST['postal_code'] ?? ''));
            $country = trim((string)($_POST['country'] ?? 'Kenya'));
            $is_default = (int)($_POST['is_default'] ?? 0) === 1 ? 1 : 0;

            if ($address_line1 === '') {
                $flash_error = 'Address line 1 is required.';
            } else {
                try {
                    if ($is_default === 1) {
                        $db->query("UPDATE addresses SET is_default = 0 WHERE user_id = ?", [$user_id]);
                    }

                    if ($address_id > 0) {
                        $updated = $db->query(
                            "UPDATE addresses SET label = ?, recipient_name = ?, phone = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, country = ?, is_default = ? WHERE id = ? AND user_id = ?",
                            [$label, $recipient_name, $addr_phone, $address_line1, $address_line2, $city, $state, $postal_code, $country, $is_default, $address_id, $user_id]
                        );
                        if ($updated) {
                            $flash_success = 'Address updated.';
                        } else {
                            $flash_error = 'Could not update address. Please make sure you ran create_tables.php and try again.';
                        }
                    } else {
                        $new_id = $db->insert('addresses', [
                            'user_id' => $user_id,
                            'label' => $label,
                            'recipient_name' => $recipient_name,
                            'phone' => $addr_phone,
                            'address_line1' => $address_line1,
                            'address_line2' => $address_line2,
                            'city' => $city,
                            'state' => $state,
                            'postal_code' => $postal_code,
                            'country' => $country,
                            'is_default' => $is_default
                        ]);
                        if ($new_id) {
                            $flash_success = 'Address added.';
                        } else {
                            $flash_error = 'Could not save address. Please make sure you ran create_tables.php and try again.';
                        }
                    }
                } catch (Exception $e) {
                    error_log('Address save error: ' . $e->getMessage());
                    $flash_error = 'Could not save address. Please make sure you ran create_tables.php and try again.';
                }
            }
        } elseif ($action === 'address_delete') {
            $address_id = (int)($_POST['address_id'] ?? 0);
            if ($address_id > 0) {
                $db->query("DELETE FROM addresses WHERE id = ? AND user_id = ?", [$address_id, $user_id]);
                $flash_success = 'Address deleted.';
            }
        } elseif ($action === 'address_set_default') {
            $address_id = (int)($_POST['address_id'] ?? 0);
            if ($address_id > 0) {
                $db->query("UPDATE addresses SET is_default = 0 WHERE user_id = ?", [$user_id]);
                $db->query("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?", [$address_id, $user_id]);
                $flash_success = 'Default address updated.';
            }
        } elseif ($action === 'wishlist_add') {
            $product_id = (int)($_POST['product_id'] ?? 0);
            $size = trim((string)($_POST['size'] ?? ''));
            if ($product_id > 0) {
                $db->query(
                    "INSERT IGNORE INTO wishlist_items (user_id, product_id, size) VALUES (?, ?, ?)",
                    [$user_id, $product_id, ($size !== '' ? $size : null)]
                );
                $flash_success = 'Added to wishlist.';
            }
        } elseif ($action === 'wishlist_remove') {
            $wishlist_id = (int)($_POST['wishlist_id'] ?? 0);
            if ($wishlist_id > 0) {
                $db->query("DELETE FROM wishlist_items WHERE id = ? AND user_id = ?", [$wishlist_id, $user_id]);
                $flash_success = 'Removed from wishlist.';
            }
        } elseif ($action === 'coupon_redeem') {
            $code = strtoupper(trim((string)($_POST['coupon_code'] ?? '')));
            if ($code === '') {
                $flash_error = 'Enter a coupon code.';
            } else {
                $coupon = $db->fetchOne(
                    "SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())",
                    [$code]
                );

                if (!$coupon) {
                    $flash_error = 'Invalid or expired coupon.';
                } else {
                    if (!empty($coupon['max_uses']) && (int)$coupon['used_count'] >= (int)$coupon['max_uses']) {
                        $flash_error = 'This coupon has reached maximum usage.';
                    } else {
                        $db->query(
                            "INSERT IGNORE INTO user_coupons (user_id, coupon_id) VALUES (?, ?)",
                            [$user_id, (int)$coupon['id']]
                        );
                        $db->query("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?", [(int)$coupon['id']]);
                        $flash_success = 'Coupon redeemed.';
                    }
                }
            }
        } elseif ($action === 'review_add') {
            $product_id = (int)($_POST['product_id'] ?? 0);
            $rating = (int)($_POST['rating'] ?? 0);
            $title = trim((string)($_POST['title'] ?? ''));
            $review_text = trim((string)($_POST['review_text'] ?? ''));

            if ($product_id <= 0) {
                $flash_error = 'Select a product.';
            } elseif ($rating < 1 || $rating > 5) {
                $flash_error = 'Rating must be between 1 and 5.';
            } else {
                $order = $db->fetchOne(
                    "SELECT o.id FROM orders o INNER JOIN order_items oi ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.payment_status = 'paid' ORDER BY o.id DESC LIMIT 1",
                    [$user_id, $product_id]
                );

                if (!$order) {
                    $flash_error = 'You can only review products you have purchased.';
                } else {
                    $order_id = (int)$order['id'];
                    $existing = $db->fetchOne(
                        "SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ? AND order_id = ?",
                        [$user_id, $product_id, $order_id]
                    );
                    if ($existing) {
                        $db->query(
                            "UPDATE product_reviews SET rating = ?, title = ?, review_text = ? WHERE id = ? AND user_id = ?",
                            [$rating, $title, $review_text, (int)$existing['id'], $user_id]
                        );
                        $flash_success = 'Review updated.';
                    } else {
                        $db->insert('product_reviews', [
                            'user_id' => $user_id,
                            'product_id' => $product_id,
                            'order_id' => $order_id,
                            'rating' => $rating,
                            'title' => $title,
                            'review_text' => $review_text
                        ]);
                        $flash_success = 'Review submitted.';
                    }
                }
            }
        } elseif ($action === 'cancel_order') {
            $order_id = (int)($_POST['order_id'] ?? 0);
            $reason_code = trim((string)($_POST['reason_code'] ?? ''));
            $reason_text = trim((string)($_POST['reason_text'] ?? ''));

            if ($order_id <= 0) {
                $flash_error = 'Invalid order.';
            } elseif ($reason_code === '') {
                $flash_error = 'Please select a cancellation reason.';
            } else {
                $order = $db->fetchOne(
                    "SELECT id, status, payment_status FROM orders WHERE id = ? AND user_id = ?",
                    [$order_id, $user_id]
                );

                if (!$order) {
                    $flash_error = 'Order not found.';
                } else {
                    $status = strtolower((string)($order['status'] ?? 'pending'));
                    if (in_array($status, ['delivered', 'shipped', 'collected'], true)) {
                        $flash_error = 'This order cannot be cancelled at its current stage.';
                    } elseif ($status === 'cancelled' || $status === 'canceled') {
                        $flash_error = 'This order is already cancelled.';
                    } else {
                        try {
                            $db->query("START TRANSACTION");
                            $db->update('orders', ['status' => 'cancelled'], "id = " . (int)$order_id . " AND user_id = " . (int)$user_id);
                            $db->insert('order_cancellations', [
                                'order_id' => (int)$order_id,
                                'user_id' => (int)$user_id,
                                'reason_code' => $reason_code,
                                'reason_text' => ($reason_text !== '' ? $reason_text : null)
                            ]);
                            $db->query("COMMIT");
                            $flash_success = 'Order cancelled.';
                        } catch (Exception $e) {
                            $db->query("ROLLBACK");
                            $flash_error = 'Could not cancel order. Please try again.';
                        }
                    }
                }
            }
        } elseif ($action === 'review_delete') {
            $review_id = (int)($_POST['review_id'] ?? 0);
            if ($review_id > 0) {
                $db->query("DELETE FROM product_reviews WHERE id = ? AND user_id = ?", [$review_id, $user_id]);
                $flash_success = 'Review deleted.';
            }
        }

        try {
            $current_user = $db->fetchOne("SELECT id, full_name, email, phone, password, profile_image FROM users WHERE id = ?", [$user_id]);
        } catch (Exception $e) {
            $current_user = $db->fetchOne("SELECT id, full_name, email, phone, password FROM users WHERE id = ?", [$user_id]);
        }
    }
}

// Initialize variables with default values
$recent_orders = [];
$order_stats = [
    'total_orders' => 0,
    'delivered_orders' => 0,
    'total_spent' => 0
];
$cart_count = 0;
$recommended_products = [];

// Get recent orders with universal queries - simplified without COUNT aggregation
try {
    // First try simple query without COUNT
    $orders_query = "
        SELECT o.*, u.full_name, u.email
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.user_id = ? 
        ORDER BY o.id DESC 
        LIMIT 5
    ";
    
    $recent_orders = $db->fetchAll($orders_query, [$user_id]);
    
    // If no orders found with JOIN, try without user info
    if (empty($recent_orders)) {
        error_log("User dashboard JOIN failed, trying orders without user info");
        $simple_query = "
            SELECT o.*, 'You' as full_name, 'N/A' as email
            FROM orders o 
            WHERE o.user_id = ? 
            ORDER BY o.id DESC 
            LIMIT 5
        ";
        
        $recent_orders = $db->fetchAll($simple_query, [$user_id]);
        error_log("User dashboard simple query found: " . count($recent_orders) . " orders");
    }
    
    // If still no orders, try the most basic query
    if (empty($recent_orders)) {
        error_log("Even simple query failed, trying most basic query");
        $basic_query = "SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5";
        $recent_orders = $db->fetchAll($basic_query, [$user_id]);
        error_log("User dashboard basic query found: " . count($recent_orders) . " orders");
        
        // Add default customer info to basic results
        foreach ($recent_orders as &$order) {
            $order['full_name'] = 'You';
            $order['email'] = 'N/A';
        }
    }
    
    // Get item counts separately for each order
    if (!empty($recent_orders)) {
        foreach ($recent_orders as &$order) {
            try {
                $item_count = $db->fetchOne("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?", [$order['id']]);
                $order['item_count'] = $item_count['count'] ?? 0;
            } catch (Exception $e) {
                $order['item_count'] = 0;
                error_log("Item count error for order {$order['id']}: " . $e->getMessage());
            }
        }
    }
    
    error_log("User dashboard recent orders found: " . count($recent_orders));
    
} catch(Exception $e) {
    error_log("Recent orders error: " . $e->getMessage());
    $recent_orders = [];
}

// Get order stats with universal queries
try {
    $order_stats = $db->fetchOne("
        SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as total_spent,
            COALESCE(SUM(total_amount), 0) as total_amount
        FROM orders 
        WHERE user_id = ?
    ", [$user_id]);
} catch(Exception $e) {
    error_log("Order stats error: " . $e->getMessage());
    $order_stats = [
        'total_orders' => 0,
        'delivered_orders' => 0,
        'completed_orders' => 0,
        'total_spent' => 0,
        'total_amount' => 0
    ];
}

// Get cart items count with universal queries
try {
    $cart_result = $db->fetchOne("SELECT COUNT(*) as count FROM cart WHERE user_id = ?", [$user_id]);
    $cart_count = $cart_result['count'] ?? 0;
} catch(Exception $e) {
    $cart_count = 0;
}

// Get recommended products with universal queries
try {
    $recommended_products = $db->fetchAll("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE (p.is_active = 1 OR p.status = 'active') AND 
              (p.stock_quantity > 0 OR p.stock > 0 OR p.quantity > 0)
        ORDER BY COALESCE(p.created_at, p.date_added) DESC
        LIMIT 4
    ");
} catch(Exception $e) {
    error_log("Recommended products error: " . $e->getMessage());
    try {
        $recommended_products = $db->fetchAll("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock_quantity > 0
            ORDER BY p.created_at DESC
            LIMIT 20
        ");
    } catch(Exception $e2) {
        error_log("Recommended products fallback error: " . $e2->getMessage());
        $recommended_products = [];
    }
}

// Helper function to get product stock
function getProductStock($product) {
    if (isset($product['stock_quantity'])) return $product['stock_quantity'];
    if (isset($product['stock'])) return $product['stock'];
    if (isset($product['quantity'])) return $product['quantity'];
    return 0;
}

// Helper function to get product price
function getProductPrice($product) {
    if (isset($product['discount_price']) && $product['discount_price'] > 0) {
        return $product['discount_price'];
    } elseif (isset($product['sale_price']) && $product['sale_price'] > 0) {
        return $product['sale_price'];
    }
    return $product['price'];
}

// Helper function to get order date
function getOrderDate($order) {
    return $order['order_date'] ?? $order['created_at'] ?? 'Unknown';
}

// Helper function to get order status
function getOrderStatus($order) {
    return $order['status'] ?? $order['order_status'] ?? 'pending';
}

function getOrderStatusBadgeClass($status) {
    $status = $status ?: 'pending';

    switch ($status) {
        case 'pending':
            return 'secondary';
        case 'confirmed':
            return 'primary';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'warning';
        case 'delivered':
            return 'success';
        case 'cancelled':
        case 'canceled':
            return 'danger';
        default:
            return 'secondary';
    }
}

$tab = $_GET['tab'] ?? 'home';
$orders_filter = $_GET['filter'] ?? 'all';

$allowed_tabs = [
    'home',
    'transaction_management',
    'orders',
    'account',
    'personal_information',
    'address_management',
    'wish',
    'explorer_point',
    'coupons_center',
    'product_reviews'
];

if (!in_array($tab, $allowed_tabs, true)) {
    $tab = 'home';
}

$allowed_order_filters = ['all', 'pre_payment', 'to_be_received', 'completed', 'canceled'];
if (!in_array($orders_filter, $allowed_order_filters, true)) {
    $orders_filter = 'all';
}

$orders_for_tab = [];
$payments_for_tab = [];

$addresses_for_tab = [];
$wishlist_for_tab = [];
$wishlist_products = [];
$points_summary = ['points_balance' => 0];
$points_ledger = [];
$coupons_for_tab = [];
$reviews_for_tab = [];
$purchased_products_for_review = [];

if ($tab === 'orders') {
    $where = 'o.user_id = ?';
    $params = [$user_id];

    if ($orders_filter === 'pre_payment') {
        $where .= " AND (o.payment_status = 'pending' OR o.payment_status IS NULL)";
    } elseif ($orders_filter === 'to_be_received') {
        $where .= " AND o.status IN ('confirmed', 'processing', 'shipped')";
    } elseif ($orders_filter === 'completed') {
        $where .= " AND o.status IN ('delivered')";
    } elseif ($orders_filter === 'canceled') {
        $where .= " AND o.status IN ('cancelled', 'canceled')";
    }

    $orders_for_tab = $db->fetchAll(
        "SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE $where GROUP BY o.id ORDER BY o.created_at DESC",
        $params
    );
}

if ($tab === 'transaction_management') {
    $payments_for_tab = $db->fetchAll(
        "SELECT p.*, o.order_number FROM payments p LEFT JOIN orders o ON p.order_id = o.id WHERE o.user_id = ? ORDER BY p.payment_date DESC",
        [$user_id]
    );
}

if ($tab === 'address_management') {
    try {
        $addresses_for_tab = $db->fetchAll(
            "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC",
            [$user_id]
        );
    } catch (Exception $e) {
        error_log('Address load error: ' . $e->getMessage());
        $addresses_for_tab = [];
        if ($flash_error === '') {
            $flash_error = 'Could not load addresses. Please refresh and try again.';
        }
    }
}

if ($tab === 'wish') {
    $wishlist_for_tab = $db->fetchAll(
        "SELECT w.id as wishlist_id, w.size, w.created_at, p.* FROM wishlist_items w INNER JOIN products p ON p.id = w.product_id WHERE w.user_id = ? ORDER BY w.id DESC",
        [$user_id]
    );

    // Product list for the wishlist dropdown (do not filter by stock)
    try {
        $wishlist_products = $db->fetchAll(
            "SELECT id, name FROM products WHERE is_active = 1 ORDER BY name ASC LIMIT 200"
        );
    } catch (Exception $e) {
        $wishlist_products = [];
    }
}

if ($tab === 'explorer_point') {
    $points_summary = $db->fetchOne(
        "SELECT COALESCE(SUM(points), 0) as points_balance FROM points_ledger WHERE user_id = ?",
        [$user_id]
    );
    $points_ledger = $db->fetchAll(
        "SELECT * FROM points_ledger WHERE user_id = ? ORDER BY id DESC LIMIT 50",
        [$user_id]
    );
}

if ($tab === 'coupons_center') {
    $coupons_for_tab = $db->fetchAll(
        "SELECT c.*, uc.redeemed_at, uc.used_at FROM user_coupons uc INNER JOIN coupons c ON c.id = uc.coupon_id WHERE uc.user_id = ? ORDER BY uc.id DESC",
        [$user_id]
    );
}

if ($tab === 'product_reviews') {
    $reviews_for_tab = $db->fetchAll(
        "SELECT r.*, p.name as product_name FROM product_reviews r INNER JOIN products p ON p.id = r.product_id WHERE r.user_id = ? ORDER BY r.id DESC",
        [$user_id]
    );
    $purchased_products_for_review = $db->fetchAll(
        "SELECT DISTINCT p.id, p.name FROM orders o INNER JOIN order_items oi ON oi.order_id = o.id INNER JOIN products p ON p.id = oi.product_id WHERE o.user_id = ? AND o.payment_status = 'paid' ORDER BY p.name ASC",
        [$user_id]
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
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
        
        .dashboard-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-teal) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            text-align: center;
        }
        .dashboard-hero .display-5 {
            text-align: center;
        }
        .dashboard-hero .lead {
            text-align: center;
        }
        .dashboard-hero .d-flex {
            justify-content: center;
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .stat-card.bg-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color)) !important;
        }
        .stat-card.bg-success {
            background: linear-gradient(135deg, var(--accent-green), #2E7D32) !important;
        }
        .stat-card.bg-info {
            background: linear-gradient(135deg, var(--secondary-blue), #1565C0) !important;
        }
        .stat-card.bg-warning {
            background: linear-gradient(135deg, var(--accent-purple), #6A1B9A) !important;
        }
        .order-status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .product-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .quick-action-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 12px;
        }
        .quick-action-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(6, 25, 67, 0.2);
        }
        .card-title {
            text-align: center;
            font-weight: 600;
            color: var(--neutral-gray);
        }
        .card-header h5 {
            text-align: center;
            color: var(--neutral-gray);
            font-weight: 600;
        }
        .card-header {
            background: linear-gradient(135deg, #FFFFFF, #F5F5F5);
            border-bottom: 2px solid var(--primary-color);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-color), rgb(6, 25, 67));
            transform: translateY(-1px);
        }
        .table-hover tbody tr:hover {
            background-color: var(--light-bg);
        }
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }

        .account-shell {
            padding-top: 1.5rem;
            padding-bottom: 2rem;
        }
        .account-sidebar {
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .account-sidebar .list-group-item {
            border: 0;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 0.85rem 1rem;
        }
        .account-sidebar .list-group-item.active {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
        }
        .account-content {
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            background: #fff;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>

    <div class="container account-shell">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="account-sidebar bg-white">
                    <div class="p-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&size=64&background=ffffff&color=667eea" alt="Profile" class="rounded-circle" width="52" height="52">
                            <div class="ms-3">
                                <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                                <div class="text-muted small">My Dashboard</div>
                            </div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush">
                        <a class="list-group-item list-group-item-action <?php echo $tab === 'home' ? 'active' : ''; ?>" href="dashboard.php?tab=home">
                            <i class="fas fa-home me-2"></i>Home
                        </a>

                        <a class="list-group-item list-group-item-action <?php echo $tab === 'orders' ? 'active' : ''; ?>" href="dashboard.php?tab=orders&filter=all">
                            <i class="fas fa-shopping-bag me-2"></i>My Order
                        </a>
                        <a class="list-group-item list-group-item-action <?php echo $tab === 'transaction_management' ? 'active' : ''; ?>" href="dashboard.php?tab=transaction_management">
                            <i class="fas fa-receipt me-2"></i>Transaction Management
                        </a>

                        <a class="list-group-item list-group-item-action <?php echo $tab === 'account' ? 'active' : ''; ?>" href="dashboard.php?tab=account">
                            <i class="fas fa-user-circle me-2"></i>Account
                        </a>
                        <a class="list-group-item list-group-item-action <?php echo $tab === 'personal_information' ? 'active' : ''; ?>" href="dashboard.php?tab=personal_information">
                            <i class="fas fa-id-card me-2"></i>Personal Information
                        </a>
                        <a class="list-group-item list-group-item-action <?php echo $tab === 'address_management' ? 'active' : ''; ?>" href="dashboard.php?tab=address_management">
                            <i class="fas fa-map-marker-alt me-2"></i>Address Management
                        </a>

                        <a class="list-group-item list-group-item-action <?php echo $tab === 'wish' ? 'active' : ''; ?>" href="dashboard.php?tab=wish">
                            <i class="fas fa-heart me-2"></i>My Wish
                        </a>
                        <a class="list-group-item list-group-item-action <?php echo $tab === 'explorer_point' ? 'active' : ''; ?>" href="dashboard.php?tab=explorer_point">
                            <i class="fas fa-coins me-2"></i>My Explorer Point
                        </a>
                        <a class="list-group-item list-group-item-action <?php echo $tab === 'coupons_center' ? 'active' : ''; ?>" href="dashboard.php?tab=coupons_center">
                            <i class="fas fa-ticket-alt me-2"></i>Coupons Center
                        </a>
                        <a class="list-group-item list-group-item-action <?php echo $tab === 'product_reviews' ? 'active' : ''; ?>" href="dashboard.php?tab=product_reviews">
                            <i class="fas fa-star me-2"></i>Product Reviews
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="account-content">
                    <div class="p-4">
                        <?php if (!empty($flash_success)): ?>
                            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($flash_success); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($flash_error)): ?>
                            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($flash_error); ?></div>
                        <?php endif; ?>

                        <?php if ($tab === 'home'): ?>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                                <div>
                                    <h4 class="mb-1">Home</h4>
                                    <div class="text-muted">Manage your orders and account</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="catalog.php" class="btn btn-primary">
                                        <i class="fas fa-shopping-bag me-2"></i>Shop
                                    </a>
                                    <a href="cart.php" class="btn btn-outline-primary">
                                        <i class="fas fa-shopping-cart me-2"></i>Cart (<?php echo $cart_count; ?>)
                                    </a>
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-3">
                                    <div class="card stat-card bg-primary text-white h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-shopping-bag fa-3x mb-3"></i>
                                            <h3 class="card-title"><?php echo $order_stats['total_orders'] ?? 0; ?></h3>
                                            <p class="card-text">Total Orders</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card stat-card bg-success text-white h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                                            <h3 class="card-title"><?php echo $order_stats['delivered_orders'] ?? 0; ?></h3>
                                            <p class="card-text">Delivered</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card stat-card bg-info text-white h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                            <h3 class="card-title"><?php echo $cart_count; ?></h3>
                                            <p class="card-text">Cart Items</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card stat-card bg-warning text-white h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-coins fa-3x mb-3"></i>
                                            <h3 class="card-title">KSh <?php echo number_format($order_stats['total_spent'] ?? 0, 2); ?></h3>
                                            <p class="card-text">Total Spent</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-lg-8">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Recent Orders</h5>
                                            <a href="dashboard.php?tab=orders&filter=all" class="btn btn-sm btn-outline-primary">View All</a>
                                        </div>
                                        <div class="card-body">
                                            <?php if (!empty($recent_orders)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Order #</th>
                                                                <th>Date</th>
                                                                <th>Items</th>
                                                                <th>Total</th>
                                                                <th>Status</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($recent_orders as $order): ?>
                                                                <tr>
                                                                    <td><strong><?php echo htmlspecialchars($order['order_number'] ?? '#' . $order['id']); ?></strong></td>
                                                                    <td><?php echo date('M j, Y', strtotime($order['order_date'] ?? $order['created_at'])); ?></td>
                                                                    <td><?php echo $order['item_count'] ?? 0; ?> items</td>
                                                                    <td>KSh <?php echo number_format($order['total_amount'], 2); ?></td>
                                                                    <td>
                                                                        <?php $order_status_value = $order['status'] ?? $order['order_status'] ?? 'pending'; ?>
                                                                        <span class="badge order-status-badge bg-<?php echo getOrderStatusBadgeClass($order_status_value); ?>">
                                                                            <?php echo ucfirst($order['status'] ?? $order['order_status'] ?? 'pending'); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <a href="order_details_universal.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center py-5">
                                                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No orders yet</h5>
                                                    <p class="text-muted">Start shopping to see your orders here</p>
                                                    <a href="catalog.php" class="btn btn-primary">Browse Products</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white"><h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5></div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <div class="card quick-action-card text-center h-100" onclick="window.location.href='catalog.php'">
                                                        <div class="card-body p-3"><i class="fas fa-shopping-bag fa-2x text-primary mb-2"></i><h6 class="card-title small">Shop</h6></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="card quick-action-card text-center h-100" onclick="window.location.href='cart.php'">
                                                        <div class="card-body p-3"><i class="fas fa-shopping-cart fa-2x text-success mb-2"></i><h6 class="card-title small">Cart</h6></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="card quick-action-card text-center h-100" onclick="window.location.href='dashboard.php?tab=orders&filter=all'">
                                                        <div class="card-body p-3"><i class="fas fa-history fa-2x text-info mb-2"></i><h6 class="card-title small">Orders</h6></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="card quick-action-card text-center h-100" onclick="window.location.href='dashboard.php?tab=personal_information'">
                                                        <div class="card-body p-3"><i class="fas fa-user fa-2x text-warning mb-2"></i><h6 class="card-title small">Profile</h6></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card shadow-sm">
                                        <div class="card-header bg-white"><h5 class="card-title mb-0"><i class="fas fa-star me-2"></i>Recommended</h5></div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <?php foreach (array_slice($recommended_products, 0, 2) as $product): ?>
                                                    <div class="col-12">
                                                        <div class="card product-card h-100">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-3">
                                                                        <?php if ($product['image_url']): ?>
                                                                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                                                        <?php else: ?>
                                                                            <i class="fas fa-tshirt fa-2x text-primary"></i>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="card-title small mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                                        <p class="text-muted small mb-1"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <span class="h6 mb-0 text-primary">KSh <?php echo number_format($product['price'], 2); ?></span>
                                                                            <a href="product.php?pid=<?php echo urlencode(!empty($product['private_id']) ? $product['private_id'] : $product['id']); ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="text-center mt-3"><a href="catalog.php" class="btn btn-sm btn-outline-primary">View All Products</a></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php elseif ($tab === 'orders'): ?>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                                <div>
                                    <h4 class="mb-1">My Order</h4>
                                    <div class="text-muted">Track and manage your orders</div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <a class="btn btn-sm <?php echo $orders_filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="dashboard.php?tab=orders&filter=all">All Order</a>
                                <a class="btn btn-sm <?php echo $orders_filter === 'pre_payment' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="dashboard.php?tab=orders&filter=pre_payment">Pre-payment</a>
                                <a class="btn btn-sm <?php echo $orders_filter === 'to_be_received' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="dashboard.php?tab=orders&filter=to_be_received">To be Received</a>
                                <a class="btn btn-sm <?php echo $orders_filter === 'completed' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="dashboard.php?tab=orders&filter=completed">Completed</a>
                                <a class="btn btn-sm <?php echo $orders_filter === 'canceled' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="dashboard.php?tab=orders&filter=canceled">Canceled</a>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <?php if (!empty($orders_for_tab)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Order #</th>
                                                        <th>Date</th>
                                                        <th>Items</th>
                                                        <th>Total</th>
                                                        <th>Payment</th>
                                                        <th>Status</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($orders_for_tab as $order): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($order['order_number'] ?? '#' . $order['id']); ?></strong></td>
                                                            <td><?php echo date('M j, Y', strtotime($order['created_at'] ?? 'now')); ?></td>
                                                            <td><?php echo (int)($order['item_count'] ?? 0); ?></td>
                                                            <td>KSh <?php echo number_format($order['total_amount'] ?? 0, 2); ?></td>
                                                            <td><?php echo ucfirst($order['payment_status'] ?? 'pending'); ?></td>
                                                            <td><?php echo ucfirst($order['status'] ?? 'pending'); ?></td>
                                                            <td>
                                                                <a href="order_details_universal.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>

                                                                <?php
                                                                    $can_cancel = true;
                                                                    $st = strtolower((string)($order['status'] ?? 'pending'));
                                                                    if (in_array($st, ['delivered', 'shipped', 'collected', 'cancelled', 'canceled'], true)) {
                                                                        $can_cancel = false;
                                                                    }
                                                                ?>
                                                                <?php if ($can_cancel): ?>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo (int)$order['id']; ?>">Cancel</button>

                                                                    <div class="modal fade" id="cancelModal<?php echo (int)$order['id']; ?>" tabindex="-1" aria-hidden="true">
                                                                        <div class="modal-dialog">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Cancel Order <?php echo htmlspecialchars($order['order_number'] ?? ('#' . $order['id'])); ?></h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                </div>
                                                                                <form method="post" action="dashboard.php?tab=orders&filter=<?php echo urlencode($orders_filter); ?>">
                                                                                    <div class="modal-body">
                                                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                                                        <input type="hidden" name="action" value="cancel_order">
                                                                                        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">

                                                                                        <div class="mb-3">
                                                                                            <label class="form-label">Reason *</label>
                                                                                            <select class="form-select" name="reason_code" required>
                                                                                                <option value="">Select reason</option>
                                                                                                <option value="changed_mind">Changed my mind</option>
                                                                                                <option value="wrong_item">Wrong item/size selected</option>
                                                                                                <option value="found_better_price">Found a better price</option>
                                                                                                <option value="delivery_too_slow">Delivery might take too long</option>
                                                                                                <option value="other">Other</option>
                                                                                            </select>
                                                                                        </div>

                                                                                        <div class="mb-3">
                                                                                            <label class="form-label">More details (optional)</label>
                                                                                            <textarea class="form-control" name="reason_text" rows="3"></textarea>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                                                        <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No orders found</h5>
                                            <a href="catalog.php" class="btn btn-primary">Shop Now</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($tab === 'transaction_management'): ?>
                            <h4 class="mb-1">Transaction Management</h4>
                            <div class="text-muted mb-3">Payments linked to your orders</div>
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <?php if (!empty($payments_for_tab)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Order</th>
                                                        <th>Method</th>
                                                        <th>Transaction ID</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($payments_for_tab as $payment): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($payment['order_number'] ?? ('#' . ($payment['order_id'] ?? ''))); ?></td>
                                                            <td><?php echo htmlspecialchars($payment['payment_method'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($payment['transaction_id'] ?? ''); ?></td>
                                                            <td>KSh <?php echo number_format($payment['amount'] ?? 0, 2); ?></td>
                                                            <td><?php echo htmlspecialchars($payment['status'] ?? 'pending'); ?></td>
                                                            <td><?php echo htmlspecialchars($payment['payment_date'] ?? ''); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No transactions found</h5>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($tab === 'account'): ?>
                            <h4 class="mb-1">Account</h4>
                            <div class="text-muted mb-3">Manage your account settings</div>
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="mb-3">Change Password</h6>
                                    <form method="post" action="dashboard.php?tab=account">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="action" value="change_password">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Current Password</label>
                                                <input type="password" class="form-control" name="current_password" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">New Password</label>
                                                <input type="password" class="form-control" name="new_password" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Confirm New Password</label>
                                                <input type="password" class="form-control" name="confirm_password" required>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">Update Password</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php elseif ($tab === 'personal_information'): ?>
                            <h4 class="mb-1">Personal Information</h4>
                            <div class="text-muted mb-3">View and update your profile details</div>
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <h6 class="mb-3">Profile Picture</h6>
                                    <div class="d-flex align-items-center gap-3">
                                        <div>
                                            <img
                                                id="profilePreview"
                                                src="<?php echo !empty($current_user['profile_image']) ? htmlspecialchars($current_user['profile_image']) : 'data:image/gif;base64,R0lGODlhAQABAAAAACw='; ?>"
                                                alt="Profile"
                                                style="width: 90px; height: 90px; object-fit: cover; border-radius: 50%;"
                                            >
                                        </div>
                                        <div>
                                            <input type="file" class="form-control" id="profileImageInput" accept="image/*">
                                            <div class="form-text">Upload a JPG/PNG. You can crop and rotate before saving.</div>
                                        </div>
                                    </div>

                                    <form method="post" action="dashboard.php?tab=personal_information" class="mt-3" id="profileImageForm">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="action" value="update_profile_image">
                                        <input type="hidden" name="profile_image_data" id="profileImageData" value="">
                                        <button type="submit" class="btn btn-primary" id="saveProfileImageBtn" disabled>Save Profile Picture</button>
                                    </form>
                                </div>
                            </div>
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <form method="post" action="dashboard.php?tab=personal_information">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="action" value="update_profile">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($current_user['full_name'] ?? ($_SESSION['full_name'] ?? '')); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($current_user['email'] ?? ($_SESSION['user_email'] ?? '')); ?>" disabled>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php elseif ($tab === 'address_management'): ?>
                            <h4 class="mb-1">Address Management</h4>
                            <div class="text-muted mb-3">Manage delivery addresses</div>
                            <?php
                                $edit_address_id = (int)($_GET['edit_address_id'] ?? 0);
                                $edit_address = null;
                                if ($edit_address_id > 0) {
                                    $edit_address = $db->fetchOne("SELECT * FROM addresses WHERE id = ? AND user_id = ?", [$edit_address_id, $user_id]);
                                }
                            ?>
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <h6 class="mb-3"><?php echo $edit_address ? 'Edit Address' : 'Add New Address'; ?></h6>
                                    <form method="post" action="dashboard.php?tab=address_management<?php echo $edit_address ? '&edit_address_id=' . (int)$edit_address_id : ''; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="action" value="address_save">
                                        <input type="hidden" name="address_id" value="<?php echo (int)($edit_address['id'] ?? 0); ?>">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Label</label>
                                                <input type="text" class="form-control" name="label" value="<?php echo htmlspecialchars($edit_address['label'] ?? ''); ?>" placeholder="Home / Office">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Recipient Name</label>
                                                <input type="text" class="form-control" name="recipient_name" value="<?php echo htmlspecialchars($edit_address['recipient_name'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Phone</label>
                                                <input type="text" class="form-control" name="addr_phone" value="<?php echo htmlspecialchars($edit_address['phone'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Address Line 1</label>
                                                <input type="text" class="form-control" name="address_line1" value="<?php echo htmlspecialchars($edit_address['address_line1'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Address Line 2</label>
                                                <input type="text" class="form-control" name="address_line2" value="<?php echo htmlspecialchars($edit_address['address_line2'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($edit_address['city'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">State</label>
                                                <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($edit_address['state'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" name="postal_code" value="<?php echo htmlspecialchars($edit_address['postal_code'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Country</label>
                                                <input type="text" class="form-control" name="country" value="<?php echo htmlspecialchars($edit_address['country'] ?? 'Kenya'); ?>">
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" <?php echo !empty($edit_address['is_default']) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="is_default">Set as default address</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary"><?php echo $edit_address ? 'Update Address' : 'Add Address'; ?></button>
                                            <?php if ($edit_address): ?>
                                                <a class="btn btn-outline-secondary" href="dashboard.php?tab=address_management">Cancel</a>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="mb-3">Your Addresses</h6>
                                    <?php if (!empty($addresses_for_tab)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Label</th>
                                                        <th>Address</th>
                                                        <th>Default</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($addresses_for_tab as $addr): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($addr['label'] ?? ''); ?></td>
                                                            <td>
                                                                <div class="fw-semibold"><?php echo htmlspecialchars($addr['recipient_name'] ?? ''); ?></div>
                                                                <div class="text-muted small"><?php echo htmlspecialchars($addr['address_line1'] ?? ''); ?><?php echo !empty($addr['city']) ? ', ' . htmlspecialchars($addr['city']) : ''; ?></div>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($addr['is_default'])): ?>
                                                                    <span class="badge bg-success">Default</span>
                                                                <?php else: ?>
                                                                    <form method="post" action="dashboard.php?tab=address_management" class="d-inline">
                                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                                        <input type="hidden" name="action" value="address_set_default">
                                                                        <input type="hidden" name="address_id" value="<?php echo (int)$addr['id']; ?>">
                                                                        <button class="btn btn-sm btn-outline-primary" type="submit">Set Default</button>
                                                                    </form>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-end">
                                                                <a class="btn btn-sm btn-outline-primary" href="dashboard.php?tab=address_management&edit_address_id=<?php echo (int)$addr['id']; ?>">Edit</a>
                                                                <form method="post" action="dashboard.php?tab=address_management" class="d-inline" onsubmit="return confirm('Delete this address?');">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                                    <input type="hidden" name="action" value="address_delete">
                                                                    <input type="hidden" name="address_id" value="<?php echo (int)$addr['id']; ?>">
                                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">No addresses yet.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($tab === 'wish'): ?>
                            <h4 class="mb-1">My Wish</h4>
                            <div class="text-muted mb-3">Saved items</div>
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <h6 class="mb-3">Add product to wishlist</h6>
                                    <form method="post" action="dashboard.php?tab=wish">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="action" value="wishlist_add">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-7">
                                                <label class="form-label">Product</label>
                                                <select class="form-select" name="product_id" required>
                                                    <option value="">Select a product</option>
                                                    <?php foreach ($wishlist_products as $p): ?>
                                                        <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Size (optional)</label>
                                                <input type="text" class="form-control" name="size" placeholder="e.g. M">
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-primary w-100" type="submit">Add</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="mb-3">Your Wishlist</h6>
                                    <?php if (!empty($wishlist_for_tab)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Size</th>
                                                        <th>Price</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($wishlist_for_tab as $w): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($w['name'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($w['size'] ?? ''); ?></td>
                                                            <td>KSh <?php echo number_format((float)($w['price'] ?? 0), 2); ?></td>
                                                            <td class="text-end">
                                                                <a class="btn btn-sm btn-outline-primary" href="product.php?pid=<?php echo urlencode(!empty($w['private_id']) ? $w['private_id'] : $w['id']); ?>">View</a>
                                                                <form method="post" action="dashboard.php?tab=wish" class="d-inline" onsubmit="return confirm('Remove from wishlist?');">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                                    <input type="hidden" name="action" value="wishlist_remove">
                                                                    <input type="hidden" name="wishlist_id" value="<?php echo (int)$w['wishlist_id']; ?>">
                                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">Your wishlist is empty.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($tab === 'explorer_point'): ?>
                            <h4 class="mb-1">My Explorer Point</h4>
                            <div class="text-muted mb-3">Rewards / points</div>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="card stat-card bg-warning text-white h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-coins fa-3x mb-3"></i>
                                            <h3 class="card-title"><?php echo (int)($points_summary['points_balance'] ?? 0); ?></h3>
                                            <p class="card-text">Points Balance</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body">
                                            <h6 class="mb-3">Points History</h6>
                                            <?php if (!empty($points_ledger)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover align-middle">
                                                        <thead>
                                                            <tr>
                                                                <th>Reason</th>
                                                                <th>Points</th>
                                                                <th>Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($points_ledger as $pl): ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($pl['reason'] ?? ''); ?></td>
                                                                    <td><?php echo (int)($pl['points'] ?? 0); ?></td>
                                                                    <td><?php echo htmlspecialchars($pl['created_at'] ?? ''); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-muted">No points yet. Points are earned automatically for paid orders.</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php elseif ($tab === 'coupons_center'): ?>
                            <h4 class="mb-1">Coupons Center</h4>
                            <div class="text-muted mb-3">Available coupons</div>
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <h6 class="mb-3">Redeem Coupon</h6>
                                    <form method="post" action="dashboard.php?tab=coupons_center">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="action" value="coupon_redeem">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-8">
                                                <label class="form-label">Coupon Code</label>
                                                <input type="text" class="form-control" name="coupon_code" placeholder="Enter code" required>
                                            </div>
                                            <div class="col-md-4">
                                                <button class="btn btn-primary w-100" type="submit">Redeem</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="mb-3">My Coupons</h6>
                                    <?php if (!empty($coupons_for_tab)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Description</th>
                                                        <th>Discount</th>
                                                        <th>Redeemed</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($coupons_for_tab as $c): ?>
                                                        <tr>
                                                            <td><span class="fw-semibold"><?php echo htmlspecialchars($c['code'] ?? ''); ?></span></td>
                                                            <td><?php echo htmlspecialchars($c['description'] ?? ''); ?></td>
                                                            <td>
                                                                <?php
                                                                    $dtype = $c['discount_type'] ?? 'percent';
                                                                    $dval = (float)($c['discount_value'] ?? 0);
                                                                    echo htmlspecialchars($dtype === 'fixed' ? ('KSh ' . number_format($dval, 2)) : (number_format($dval, 0) . '%'));
                                                                ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($c['redeemed_at'] ?? ''); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">No coupons yet.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($tab === 'product_reviews'): ?>
                            <h4 class="mb-1">Product Reviews</h4>
                            <div class="text-muted mb-3">Your reviews</div>
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <h6 class="mb-3">Write a Review (verified purchase only)</h6>
                                    <?php if (empty($purchased_products_for_review)): ?>
                                        <div class="alert alert-warning mb-3">
                                            You can only review products from your paid orders. Complete a purchase first, then the product will appear here.
                                        </div>
                                    <?php endif; ?>
                                    <form method="post" action="dashboard.php?tab=product_reviews">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="action" value="review_add">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Product</label>
                                                <select class="form-select" name="product_id" required <?php echo empty($purchased_products_for_review) ? 'disabled' : ''; ?>>
                                                    <option value="">Select purchased product</option>
                                                    <?php foreach ($purchased_products_for_review as $pp): ?>
                                                        <option value="<?php echo (int)$pp['id']; ?>"><?php echo htmlspecialchars($pp['name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Rating</label>
                                                <select class="form-select" name="rating" required <?php echo empty($purchased_products_for_review) ? 'disabled' : ''; ?>>
                                                    <option value="">--</option>
                                                    <option value="5">5</option>
                                                    <option value="4">4</option>
                                                    <option value="3">3</option>
                                                    <option value="2">2</option>
                                                    <option value="1">1</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Title</label>
                                                <input type="text" class="form-control" name="title" placeholder="Optional" <?php echo empty($purchased_products_for_review) ? 'disabled' : ''; ?>>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Review</label>
                                                <textarea class="form-control" name="review_text" rows="3" placeholder="Write your review..." <?php echo empty($purchased_products_for_review) ? 'disabled' : ''; ?>></textarea>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn btn-primary" type="submit" <?php echo empty($purchased_products_for_review) ? 'disabled' : ''; ?>>Submit Review</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="mb-3">My Reviews</h6>
                                    <?php if (!empty($reviews_for_tab)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Rating</th>
                                                        <th>Title</th>
                                                        <th>Date</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reviews_for_tab as $r): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($r['product_name'] ?? ''); ?></td>
                                                            <td><?php echo (int)($r['rating'] ?? 0); ?>/5</td>
                                                            <td><?php echo htmlspecialchars($r['title'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars($r['created_at'] ?? ''); ?></td>
                                                            <td class="text-end">
                                                                <form method="post" action="dashboard.php?tab=product_reviews" class="d-inline" onsubmit="return confirm('Delete this review?');">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                                    <input type="hidden" name="action" value="review_delete">
                                                                    <input type="hidden" name="review_id" value="<?php echo (int)$r['id']; ?>">
                                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">No reviews yet.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'views/footer.php'; ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
        (function() {
            var input = document.getElementById('profileImageInput');
            var preview = document.getElementById('profilePreview');
            var dataField = document.getElementById('profileImageData');
            var saveBtn = document.getElementById('saveProfileImageBtn');

            if (!input || !preview || !dataField || !saveBtn) return;

            var cropper = null;
            var modalEl = null;

            function ensureModal() {
                if (modalEl) return modalEl;

                var html = '' +
                    '<div class="modal fade" id="profileCropModal" tabindex="-1" aria-hidden="true">' +
                    '  <div class="modal-dialog modal-lg modal-dialog-centered">' +
                    '    <div class="modal-content">' +
                    '      <div class="modal-header">' +
                    '        <h5 class="modal-title">Crop Profile Picture</h5>' +
                    '        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                    '      </div>' +
                    '      <div class="modal-body">' +
                    '        <div class="w-100" style="max-height: 60vh;">' +
                    '          <img id="cropperImage" src="" style="max-width: 100%;">' +
                    '        </div>' +
                    '      </div>' +
                    '      <div class="modal-footer">' +
                    '        <button type="button" class="btn btn-outline-secondary" id="rotateLeftBtn">Rotate Left</button>' +
                    '        <button type="button" class="btn btn-outline-secondary" id="rotateRightBtn">Rotate Right</button>' +
                    '        <button type="button" class="btn btn-primary" id="applyCropBtn">Use This Photo</button>' +
                    '      </div>' +
                    '    </div>' +
                    '  </div>' +
                    '</div>';

                document.body.insertAdjacentHTML('beforeend', html);
                modalEl = document.getElementById('profileCropModal');
                return modalEl;
            }

            input.addEventListener('change', function(e) {
                var file = (e.target.files && e.target.files[0]) ? e.target.files[0] : null;
                if (!file) return;

                var modalNode = ensureModal();
                var cropImg = document.getElementById('cropperImage');
                var url = URL.createObjectURL(file);
                cropImg.src = url;

                var bsModal = new bootstrap.Modal(modalNode);
                bsModal.show();

                modalNode.addEventListener('shown.bs.modal', function handlerShown() {
                    modalNode.removeEventListener('shown.bs.modal', handlerShown);
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                    cropper = new Cropper(cropImg, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 1
                    });
                });

                modalNode.addEventListener('hidden.bs.modal', function handlerHidden() {
                    modalNode.removeEventListener('hidden.bs.modal', handlerHidden);
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                    URL.revokeObjectURL(url);
                    input.value = '';
                });

                document.getElementById('rotateLeftBtn').onclick = function() {
                    if (cropper) cropper.rotate(-90);
                };
                document.getElementById('rotateRightBtn').onclick = function() {
                    if (cropper) cropper.rotate(90);
                };
                document.getElementById('applyCropBtn').onclick = function() {
                    if (!cropper) return;
                    var canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
                    var dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                    dataField.value = dataUrl;
                    preview.src = dataUrl;
                    saveBtn.disabled = false;
                    bsModal.hide();
                };
            });
        })();
    </script>
</body>
</html>

