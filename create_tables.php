<?php
// Auto-detect which database config to use (match includes/db.php)
$is_cli = php_sapi_name() === 'cli';
$is_localhost = $is_cli || (isset($_SERVER['SERVER_NAME']) && (
            ($_SERVER['SERVER_NAME'] == 'localhost') ||
            (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
            (strpos($_SERVER['SERVER_NAME'], '192.168') !== false)));

if ($is_localhost) {
    require_once 'config/database.php';
} else {
    require_once 'config/universal_database.php';
}

header('Content-Type: text/plain; charset=utf-8');

$db = new Database();
$conn = $db->getConnection();

try {
    // Users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        profile_image VARCHAR(255) DEFAULT NULL,
        user_type ENUM('parent', 'admin', 'staff') DEFAULT 'parent',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Add profile_image column if users table already existed without it
    try {
        $col = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'profile_image'")->fetch(PDO::FETCH_ASSOC);
        if (!$col) {
            $conn->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
        }
    } catch (Exception $e) {
        // ignore
    }

    // Ensure user_type enum contains staff (best effort)
    try {
        $ut_col = $conn->query("SHOW COLUMNS FROM users LIKE 'user_type'")->fetch(PDO::FETCH_ASSOC);
        if ($ut_col && isset($ut_col['Type']) && stripos((string)$ut_col['Type'], 'enum(') === 0) {
            if (stripos((string)$ut_col['Type'], "'staff'") === false) {
                $conn->exec("ALTER TABLE users MODIFY user_type ENUM('parent','admin','staff') DEFAULT 'parent'");
            }
        }
    } catch (Exception $e) {
        // ignore
    }

    // Addresses table (multiple addresses per user)
    $conn->exec("CREATE TABLE IF NOT EXISTS addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        label VARCHAR(100) DEFAULT NULL,
        recipient_name VARCHAR(255) DEFAULT NULL,
        phone VARCHAR(30) DEFAULT NULL,
        address_line1 VARCHAR(255) NOT NULL,
        address_line2 VARCHAR(255) DEFAULT NULL,
        city VARCHAR(120) DEFAULT NULL,
        state VARCHAR(120) DEFAULT NULL,
        postal_code VARCHAR(30) DEFAULT NULL,
        country VARCHAR(120) DEFAULT 'Kenya',
        is_default TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Categories table
    $conn->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Products table
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        discount_price DECIMAL(10,2) DEFAULT NULL,
        category_id INT,
        stock_quantity INT DEFAULT 0,
        image_url VARCHAR(500),
        is_deal TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");

    // Backward-compatible column adds for products
    try {
        $product_columns = $conn->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
        $product_map = array_flip($product_columns);
        if (!isset($product_map['discount_price'])) {
            $conn->exec("ALTER TABLE products ADD COLUMN discount_price DECIMAL(10,2) DEFAULT NULL");
        }
        if (!isset($product_map['is_deal'])) {
            $conn->exec("ALTER TABLE products ADD COLUMN is_deal TINYINT(1) DEFAULT 0");
        }
    } catch (Exception $e) {
        // ignore
    }

    // Size charts table
    $conn->exec("CREATE TABLE IF NOT EXISTS size_charts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        size VARCHAR(50) NOT NULL,
        chest VARCHAR(50),
        waist VARCHAR(50),
        hips VARCHAR(50),
        height VARCHAR(50),
        age_range VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    // Orders table
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'collected', 'uncollected') DEFAULT 'pending',
        shipping_address TEXT,
        payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Add shipping/payment columns if missing
    try {
        $columns = $conn->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);
        $column_map = array_flip($columns);

        // Ensure status ENUM contains collected/uncollected (best effort)
        try {
            $status_col = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
            if ($status_col && isset($status_col['Type']) && stripos($status_col['Type'], 'enum(') === 0) {
                if (stripos($status_col['Type'], "'collected'") === false || stripos($status_col['Type'], "'uncollected'") === false) {
                    $conn->exec("ALTER TABLE orders MODIFY status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','collected','uncollected') DEFAULT 'pending'");
                }
            }
        } catch (Exception $e) {
            // ignore
        }

        // Safer dedicated collected status column (used for filtering if desired)
        if (!isset($column_map['collected_status'])) {
            $conn->exec("ALTER TABLE orders ADD COLUMN collected_status ENUM('uncollected','collected') NOT NULL DEFAULT 'uncollected'");
        }

        if (!isset($column_map['payment_method'])) {
            $conn->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL");
        }

        if (!isset($column_map['shipping_fee'])) {
            $conn->exec("ALTER TABLE orders ADD COLUMN shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0");
        }

        if (!isset($column_map['payment_code'])) {
            $conn->exec("ALTER TABLE orders ADD COLUMN payment_code VARCHAR(255) DEFAULT NULL");
        }

        if (!isset($column_map['mpesa_receipt'])) {
            $conn->exec("ALTER TABLE orders ADD COLUMN mpesa_receipt VARCHAR(255) DEFAULT NULL");
        }

        if (!isset($column_map['customer_name'])) {
            $conn->exec("ALTER TABLE orders ADD COLUMN customer_name VARCHAR(255) DEFAULT NULL");
        }
    } catch (Exception $e) {
        // ignore
    }

    // Guest checkout compatibility: allow orders.user_id to be NULL
    try {
        $col = $conn->query("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'user_id'")->fetch(PDO::FETCH_ASSOC);
        if ($col && strtoupper((string)$col['IS_NULLABLE']) === 'NO') {
            $conn->exec("ALTER TABLE orders MODIFY user_id INT NULL");
        }
    } catch (Exception $e) {
        // ignore
    }

    // Order cancellations (store user-cancel reason)
    $conn->exec("CREATE TABLE IF NOT EXISTS order_cancellations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        user_id INT NOT NULL,
        reason_code VARCHAR(60) NOT NULL,
        reason_text TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (order_id),
        INDEX (user_id),
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Order items table
    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        size VARCHAR(50),
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");

    // Payments table
    $conn->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(255),
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id)
    )");

    // Add extra payment columns used by STK/IntaSend flow (best effort)
    try {
        $pay_columns = $conn->query("SHOW COLUMNS FROM payments")->fetchAll(PDO::FETCH_COLUMN);
        $pay_map = array_flip($pay_columns);

        if (!isset($pay_map['phone_number'])) {
            $conn->exec("ALTER TABLE payments ADD COLUMN phone_number VARCHAR(30) DEFAULT NULL");
        }
        if (!isset($pay_map['merchant_request_id'])) {
            $conn->exec("ALTER TABLE payments ADD COLUMN merchant_request_id VARCHAR(255) DEFAULT NULL");
        }
        if (!isset($pay_map['checkout_request_id'])) {
            $conn->exec("ALTER TABLE payments ADD COLUMN checkout_request_id VARCHAR(255) DEFAULT NULL");
        }
        if (!isset($pay_map['mpesa_receipt'])) {
            $conn->exec("ALTER TABLE payments ADD COLUMN mpesa_receipt VARCHAR(255) DEFAULT NULL");
        }
        if (!isset($pay_map['transaction_date'])) {
            $conn->exec("ALTER TABLE payments ADD COLUMN transaction_date DATETIME DEFAULT NULL");
        }

        // Expand status enum if needed (pending/completed/failed vs success)
        try {
            $status_col = $conn->query("SHOW COLUMNS FROM payments LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
            if ($status_col && isset($status_col['Type']) && stripos((string)$status_col['Type'], 'enum(') === 0) {
                if (stripos((string)$status_col['Type'], "'success'") !== false || stripos((string)$status_col['Type'], "'completed'") !== false) {
                    // leave as-is
                }
            }
        } catch (Exception $e) {
            // ignore
        }
    } catch (Exception $e) {
        // ignore
    }

    // Site settings table (for configurable shipping fee)
    $conn->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Wishlist
    $conn->exec("CREATE TABLE IF NOT EXISTS wishlist_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        size VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_wishlist (user_id, product_id, size),
        INDEX (user_id),
        INDEX (product_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    // Points ledger
    $conn->exec("CREATE TABLE IF NOT EXISTS points_ledger (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        order_id INT NULL,
        points INT NOT NULL,
        reason VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_points_order (order_id, reason),
        INDEX (user_id),
        INDEX (order_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
    )");

    // Backward-compatible column add for product_reviews
    try {
        $review_columns = $conn->query("SHOW COLUMNS FROM product_reviews")->fetchAll(PDO::FETCH_COLUMN);
        if ($review_columns && !in_array('admin_response', $review_columns, true)) {
            $conn->exec("ALTER TABLE product_reviews ADD COLUMN admin_response TEXT DEFAULT NULL");
        }
    } catch (Exception $e) {
        // ignore
    }

    // Coupons
    $conn->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        description VARCHAR(255) DEFAULT NULL,
        discount_type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
        discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
        min_order_amount DECIMAL(10,2) DEFAULT 0,
        max_uses INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        expires_at DATETIME DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS user_coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        coupon_id INT NOT NULL,
        redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        used_at TIMESTAMP NULL DEFAULT NULL,
        UNIQUE KEY uniq_user_coupon (user_id, coupon_id),
        INDEX (user_id),
        INDEX (coupon_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE
    )");

    // Product reviews (verified purchase required)
    $conn->exec("CREATE TABLE IF NOT EXISTS product_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        order_id INT DEFAULT NULL,
        rating TINYINT NOT NULL,
        title VARCHAR(255) DEFAULT NULL,
        review_text TEXT DEFAULT NULL,
        admin_response TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_review (user_id, product_id, order_id),
        INDEX (user_id),
        INDEX (product_id),
        INDEX (order_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
    )");

    // Product questions
    $conn->exec("CREATE TABLE IF NOT EXISTS product_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT DEFAULT NULL,
        guest_name VARCHAR(255) DEFAULT NULL,
        guest_email VARCHAR(255) DEFAULT NULL,
        question_text TEXT NOT NULL,
        admin_response TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (product_id),
        INDEX (user_id),
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // Backward-compatible column add for product_questions
    try {
        $question_columns = $conn->query("SHOW COLUMNS FROM product_questions")->fetchAll(PDO::FETCH_COLUMN);
        if ($question_columns && !in_array('admin_response', $question_columns, true)) {
            $conn->exec("ALTER TABLE product_questions ADD COLUMN admin_response TEXT DEFAULT NULL");
        }
    } catch (Exception $e) {
        // ignore
    }

    // Homepage offers/promotions
    $conn->exec("CREATE TABLE IF NOT EXISTS homepage_offers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        placement ENUM('rotating','hero','promo_card') DEFAULT 'rotating',
        title VARCHAR(255) NOT NULL,
        subtitle VARCHAR(255) DEFAULT NULL,
        link VARCHAR(500) DEFAULT NULL,
        cta_text VARCHAR(100) DEFAULT NULL,
        image_url VARCHAR(500) DEFAULT NULL,
        bg_color VARCHAR(20) DEFAULT 'rgba(4, 30, 66,1)',
        text_color VARCHAR(20) DEFAULT '#ffffff',
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        starts_at DATETIME DEFAULT NULL,
        ends_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Backward-compatible column adds for homepage_offers
    try {
        $columns = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'homepage_offers'")->fetchAll(PDO::FETCH_COLUMN);
        if ($columns) {
            if (!in_array('placement', $columns, true)) {
                $conn->exec("ALTER TABLE homepage_offers ADD COLUMN placement ENUM('rotating','hero','promo_card') DEFAULT 'rotating'");
            }
            if (!in_array('cta_text', $columns, true)) {
                $conn->exec("ALTER TABLE homepage_offers ADD COLUMN cta_text VARCHAR(100) DEFAULT NULL");
            }
            if (!in_array('sort_order', $columns, true)) {
                $conn->exec("ALTER TABLE homepage_offers ADD COLUMN sort_order INT DEFAULT 0");
            }
        }
    } catch (Exception $e) {
        // ignore
    }

    echo "All tables created successfully!\n";

    // Insert sample data ONLY on localhost/dev
    if ($is_localhost) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->exec("INSERT INTO users (email, password, full_name, user_type) VALUES ('admin@smartschool.com', '$hashed_password', 'Admin User', 'admin') ON DUPLICATE KEY UPDATE password = VALUES(password), full_name = VALUES(full_name), user_type = VALUES(user_type)");

        $conn->exec("INSERT IGNORE INTO categories (name, description) VALUES ('Uniform Shirts', 'School uniform shirts'), ('Uniform Pants', 'School uniform pants'), ('Uniform Skirts', 'School uniform skirts'), ('Accessories', 'School accessories')");

        $conn->exec("INSERT IGNORE INTO products (name, description, price, category_id, stock_quantity) VALUES
            ('White School Shirt', 'Standard white school shirt', 25.00, 1, 100),
            ('Navy Blue Pants', 'Standard navy blue school pants', 35.00, 2, 50),
            ('Plaid Skirt', 'School plaid skirt', 30.00, 3, 30),
            ('School Tie', 'Official school tie', 15.00, 4, 200)");

        // Keep sample inserts safe: do not delete existing data
        $conn->exec("INSERT IGNORE INTO size_charts (product_id, size, age_range, chest, waist, hips, height) VALUES
            (1, 'S', '6-8 years', '28-30', '24-26', '28-30', '45-48'),
            (1, 'M', '9-11 years', '30-32', '26-28', '30-32', '48-51'),
            (1, 'L', '12-14 years', '32-34', '28-30', '32-34', '51-54'),
            (1, 'XL', '15-17 years', '34-36', '30-32', '34-36', '54-57'),
            (2, '28', '12-14 years', '32-34', '28-30', '32-34', '51-54'),
            (2, '30', '13-15 years', '34-36', '30-32', '34-36', '54-57'),
            (2, '32', '14-16 years', '36-38', '32-34', '36-38', '57-60'),
            (2, '34', '15-17 years', '38-40', '34-36', '38-40', '60-63'),
            (3, 'S', '6-8 years', '28-30', '24-26', '28-30', '45-48'),
            (3, 'M', '9-11 years', '30-32', '26-28', '30-32', '48-51'),
            (3, 'L', '12-14 years', '32-34', '28-30', '32-34', '51-54'),
            (4, 'One Size', 'All ages', 'Adjustable', 'Adjustable', 'Adjustable', 'Adjustable')");

        echo "Sample data insert attempted (localhost only).\n";
    } else {
        echo "Production mode: sample data skipped.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
