<?php
// Add error handling and timeout
error_reporting(0);
ini_set('display_errors', 0);
ini_set('max_execution_time', 10);

// Include environment first to ensure session is started
require_once 'config/environment.php';

$auth = null;
$db = null;
$featured_products = [];
$deals_products = [];
$categories = [];
$product_images = [];
$homepage_offers = [];
$rotating_offers = [];
$hero_offers = [];
$promo_card_offers = [];
$jumia_promos = [];

try {
    require_once 'includes/auth.php';
    $auth = new Auth();

    require_once 'includes/db.php';
    $db = new DBHelper();

    // Get featured products with timeout protection
    $featured_products = $db->fetchAll("
        SELECT p.*, c.name as category_name,
               (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1
        ORDER BY p.created_at DESC
    ");

    // Get deals of the week
    $deals_products = $db->fetchAll("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1
          AND (p.is_deal = 1 OR (p.discount_price IS NOT NULL AND p.discount_price > 0))
        ORDER BY COALESCE(p.discount_price, p.price) ASC
        LIMIT 4
    ");

    foreach ($featured_products as &$product) {
        $product_images = $db->fetchAll("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY sort_order", [$product['id']]);
        $product['images'] = [];
        if (!empty($product_images)) {
            $product['images'] = array_values(array_filter(array_map(static function ($row) {
                return $row['image_url'] ?? '';
            }, $product_images)));

            if (!empty($product['images'])) {
                $product['primary_image'] = $product['images'][0];
                $product['image_url'] = $product['images'][0];
            }
        }
    }

    foreach ($deals_products as &$product) {
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

    // Get categories
    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name LIMIT 6");

    // Get active homepage offers
    $now = date('Y-m-d H:i:s');
    $homepage_offers = $db->fetchAll(
        "SELECT * FROM homepage_offers
         WHERE is_active = 1
           AND (starts_at IS NULL OR starts_at <= ?)
           AND (ends_at IS NULL OR ends_at >= ?)
         ORDER BY sort_order ASC, id DESC",
        [$now, $now]
    );

    foreach ($homepage_offers as $offer) {
        $placement = $offer['placement'] ?? 'rotating';
        if ($placement === 'hero') {
            $hero_offers[] = $offer;
        } elseif ($placement === 'promo_card') {
            $promo_card_offers[] = $offer;
        } else {
            $rotating_offers[] = $offer;
        }
    }

    foreach ($rotating_offers as $offer) {
        $jumia_promos[] = [
            'image' => $offer['image_url'] ?? '',
            'title' => $offer['title'] ?? '',
            'subtitle' => $offer['subtitle'] ?? '',
            'link' => $offer['link'] ?? ''
        ];
    }

} catch (Exception $e) {
    error_log("Index page error: " . $e->getMessage());
    
    // Fallback static data
    $featured_products = [
        ['id' => 1, 'name' => 'School Uniform', 'price' => 2500, 'image_url' => '', 'category_name' => 'Uniforms', 'description' => 'Quality school uniform'],
        ['id' => 2, 'name' => 'School Shoes', 'price' => 1800, 'image_url' => '', 'category_name' => 'Shoes', 'description' => 'Comfortable school shoes'],
        ['id' => 3, 'name' => 'School Bag', 'price' => 1200, 'image_url' => '', 'category_name' => 'Bags', 'description' => 'Durable school bag']
    ];
    
    $deals_products = [
        ['id' => 4, 'name' => 'Textbook Set', 'price' => 3500, 'image_url' => '', 'category_name' => 'Books', 'description' => 'Complete textbook set'],
        ['id' => 5, 'name' => 'Stationery Kit', 'price' => 800, 'image_url' => '', 'category_name' => 'Stationery', 'description' => 'Complete stationery kit']
    ];
    
    $categories = [
        ['id' => 1, 'name' => 'Uniforms'],
        ['id' => 2, 'name' => 'Shoes'],
        ['id' => 3, 'name' => 'Bags'],
        ['id' => 4, 'name' => 'Books'],
        ['id' => 5, 'name' => 'Stationery']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merch Shop</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: rgb(6, 25, 67);
            --primary-color-light: rgba(6, 25, 67, 0.8);
            --secondary-rich-black: #000000;
            --secondary-cool-gray: #8C92AC;
            --secondary-sky-blue: rgba(6, 25, 67, 0.7);
            --accent-brown: #543D21;
            --accent-sienna: #A01705;
            --accent-umber: #9E7369;
            --accent-coral: #E63C84;
            --accent-olive: #8A5542;
            --accent-mustard: #D6C542;
            --neutral-gray: #546E7A;
            --light-bg: #FFF8E1;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-sky-blue) 100%);
            color: white;
            padding: 4rem 0;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        .btn-primary:hover {
            background: var(--primary-color);
            transform: translateY(-1px);
        }
        .category-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
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
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
        }
        .hero-carousel-section {
            margin: 0;
            padding: 0;
            position: relative;
            height: 100vh;
            overflow: hidden;
        }

        .hero-carousel-section #heroCarousel,
        .hero-carousel-section #heroCarousel .carousel-inner,
        .hero-carousel-section #heroCarousel .carousel-item,
        .hero-carousel-section #heroCarousel .carousel-slide {
            height: 100%;
        }
        .carousel-item {
            height: 100vh;
            position: relative;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        .carousel-slide {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .carousel-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 1;
        }
        
        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.7), rgba(0,0,0,0.4));
            z-index: 2;
        }
        
        .carousel-content {
            position: relative;
            z-index: 3;
            text-align: center;
            max-width: 800px;
            color: white;
        }
        
        .hero-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Clean Navigation Bar */
        .clean-nav-bar {
            background: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 0;
            position: relative;
            z-index: 100;
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            min-height: 50px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
        }

        .nav-link {
            color: #333;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 15px 24px;
            transition: all 0.3s ease;
            white-space: nowrap;
            position: relative;
            border-radius: 0;
        }

        .nav-link:hover {
            color: white;
            text-decoration: none;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-sky-blue));
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 25, 67, 0.3);
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-sky-blue));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .nav-link:hover::before {
            opacity: 1;
        }

        /* Responsive Navigation */
        @media (max-width: 768px) {
            .nav-container {
                padding: 8px 0;
            }
            
            .nav-links {
                justify-content: center;
                gap: 0;
            }
            
            .nav-link {
                padding: 12px 16px;
                font-size: 12px;
            }
        }

        @media (max-width: 576px) {
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .nav-link {
                padding: 10px 12px;
                font-size: 11px;
            }
        }

        /* Jumia Style Category Cards */
        .category-jumia-card {
            transition: all 0.3s ease;
            border-radius: 8px;
            overflow: hidden;
        }

        .category-jumia-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .category-image-container {
            position: relative;
            width: 100%;
            height: 150px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .category-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .category-jumia-card:hover .category-image {
            transform: scale(1.05);
        }

        .category-info {
            background: white;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .category-title {
            font-weight: 600;
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
        }

        .category-count {
            font-size: 12px;
            color: #666;
        }

        /* Image Gallery Styles */
        .product-gallery {
            position: relative;
            cursor: pointer;
            overflow: hidden;
            height: 240px;
        }

        .product-gallery img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .product-gallery:hover img {
            transform: scale(1.05);
        }

        .gallery-thumbnails {
            display: flex;
            gap: 5px;
            margin-top: 8px;
            justify-content: center;
        }

        .gallery-thumbnail {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .gallery-thumbnail:hover,
        .gallery-thumbnail.active {
            border-color: var(--primary-color);
            transform: scale(1.1);
        }

        .image-count {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Lightbox Styles */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 9999;
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
            height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10000;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(0,0,0,0.5);
            padding: 10px 15px;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .lightbox-nav:hover {
            background: rgba(0,0,0,0.8);
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
            color: white;
            font-size: 16px;
            background: rgba(0,0,0,0.5);
            padding: 8px 16px;
            border-radius: 20px;
        }

        /* Jumia Style Promotional Banner */
        .jumia-promo-bar {
            background: var(--light-bg);
            border-bottom: 1px solid var(--secondary-cool-gray);
            padding: 8px 0;
            overflow: hidden;
            position: relative;
        }

        .promo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            font-size: 14px;
            color: var(--primary-color);
        }

        .promo-icon {
            color: var(--accent-sienna);
            font-size: 16px;
            margin-right: 4px;
        }

        .promo-image {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            object-fit: cover;
            border-radius: 2px;
        }

        .promo-text {
            font-weight: 500;
            color: var(--primary-color);
        }

        .promo-highlight {
            color: var(--accent-sienna);
            font-weight: 600;
        }

        /* Dynamic Promotional Banner */
        .dynamic-promo-banner {
            position: relative;
            height: 50px;
            border-radius: 8px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .banner-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.5s ease-in-out;
        }

        .banner-slide.active {
            opacity: 1;
            transform: translateX(0);
        }

        .banner-slide.prev {
            transform: translateX(-100%);
        }

        .banner-slide[data-banner="hot-deals"] {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
        }

        .banner-slide[data-banner="mega-sale"] {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
        }

        .banner-slide[data-banner="phone-deals"] {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
        }

        .banner-slide[data-banner="flash-sale"] {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
        }

        .banner-slide[data-banner="free-delivery"] {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
        }

        .banner-slide[data-banner="back-to-school"] {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
        }

        .banner-slide[data-banner="clearance"] {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
        }

        .banner-slide[data-banner="contact"] {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
        }

        .banner-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            color: white;
        }

        .banner-icon {
            flex-shrink: 0;
            font-size: 1.5rem;
            animation: pulse 2s infinite;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .banner-text-content {
            flex-grow: 1;
            min-width: 0;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .banner-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0;
            line-height: 1.2;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .banner-subtitle {
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 0;
            opacity: 0.95;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .banner-description {
            display: none;
        }

        .banner-content .btn {
            display: none;
        }

        /* Countdown Timer */
        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .timer-unit {
            text-align: center;
            min-width: 40px;
        }

        .timer-value {
            display: block;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1;
        }

        .timer-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            opacity: 0.8;
        }

        /* Deal Cards */
        .deal-card {
            position: relative;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .deal-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .deal-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            z-index: 1;
        }
        .deal-card .card-img-top {
            padding: 0 !important;
        }
        .deal-card .carousel,
        .deal-card .carousel-inner,
        .deal-card .carousel-item {
            height: 200px;
        }
        .deal-card .carousel-item {
            background: #f8f9fa;
        }
        .deal-card .deal-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        .deal-card .card-body {
            padding: 0.9rem 1rem 0.75rem;
        }
        .deal-card .card-title {
            margin-bottom: 0.35rem;
            font-size: 0.95rem;
            line-height: 1.2;
            height: 2.4em;
            overflow: hidden;
        }

        /* Modern Product Cards - Full Image Design */
        .modern-product-card {
            position: relative;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 380px;
            display: flex;
            flex-direction: column;
        }

        .modern-product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .product-image-container {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
            background: #f8f9fa;
            flex-shrink: 0;
        }

        .product-image-container .carousel,
        .product-image-container .carousel-inner,
        .product-image-container .carousel-item {
            height: 100%;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #f8f9fa;
            object-position: center;
            display: block;
            transition: transform 0.3s ease;
        }

        .product-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: #6c757d;
        }

        .product-placeholder i {
            font-size: 3rem;
        }

        .modern-product-card:hover .product-image {
            transform: scale(1.03);
        }

        .wishlist-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 50%;
            background: rgba(255,255,255,0.95);
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 3;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .wishlist-btn:hover {
            background: white;
            color: var(--accent-sienna);
            transform: scale(1.1);
        }

        .wishlist-btn.active {
            background: var(--accent-sienna);
            color: white;
        }

        .sale-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--accent-sienna);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 3;
        }

        .add-to-cart-btn {
            position: absolute;
            bottom: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
            z-index: 3;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .add-to-cart-btn:hover {
            background: var(--secondary-sky-blue);
            transform: scale(1.1);
        }

        .add-to-cart-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .add-to-cart-btn:disabled:hover {
            background: #ccc;
            transform: none;
        }

        .product-info {
            padding: 12px 16px 16px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: white;
        }

        .product-title {
            margin-bottom: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.3;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .product-title a:hover {
            color: var(--primary-color);
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: auto;
        }

        .current-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .original-price {
            font-size: 0.85rem;
            color: #999;
            text-decoration: line-through;
        }

        /* Responsive adjustments for modern product cards */
        @media (max-width: 768px) {
            .modern-product-card {
                height: 320px;
                margin-bottom: 1rem;
            }
            
            .product-image-container {
                height: 240px;
            }
            
            .product-info {
                padding: 10px 12px 12px;
            }
            
            .product-title {
                font-size: 0.85rem;
            }
            
            .current-price {
                font-size: 0.9rem;
            }
            
            .wishlist-btn,
            .add-to-cart-btn {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }
            
            .wishlist-btn {
                top: 8px;
                right: 8px;
            }
            
            .add-to-cart-btn {
                bottom: 8px;
                right: 8px;
            }
        }

        @media (max-width: 576px) {
            .modern-product-card {
                height: 300px;
            }
            
            .product-image-container {
                height: 220px;
            }
            
            .modern-product-card {
                border-radius: 6px;
            }
            
            .product-title {
                font-size: 0.8rem;
                -webkit-line-clamp: 1;
            }
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <!-- Clean Navigation Bar -->
    <div class="clean-nav-bar">
        <div class="container">
            <div class="nav-container">
                <div class="nav-links">
                    <a href="catalog.php" class="nav-link">SHOP</a>
                    <a href="catalog.php?category=uniforms" class="nav-link">UNIFORMS</a>
                    <a href="catalog.php?category=shoes" class="nav-link">SHOES</a>
                    <a href="catalog.php?category=bags" class="nav-link">BAGS</a>
                    <a href="catalog.php?category=accessories" class="nav-link">ACCESSORIES</a>
                    <a href="catalog.php?category=stationery" class="nav-link">STATIONERY</a>
                    <a href="new_arrivals.php" class="nav-link">NEW IN</a>
                    <a href="size_guide.php" class="nav-link">SIZE GUIDE</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hero Banner with Carousel -->
    <section class="hero-carousel-section">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <?php if (!empty($hero_offers)): ?>
                <div class="carousel-indicators">
                    <?php foreach ($hero_offers as $index => $offer): ?>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach ($hero_offers as $index => $offer): ?>
                        <?php
                        $background_style = '';
                        if (!empty($offer['image_url'])) {
                            $background_style = "background-image: url('" . htmlspecialchars($offer['image_url']) . "');";
                        } elseif (!empty($offer['bg_color'])) {
                            $background_style = "background: " . htmlspecialchars($offer['bg_color']) . ";";
                        }
                        $cta_text = !empty($offer['cta_text']) ? $offer['cta_text'] : 'Shop Now';
                        ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="carousel-slide">
                                <div class="carousel-background" style="<?php echo $background_style; ?>"></div>
                                <div class="carousel-overlay"></div>
                                <div class="carousel-content" style="color: <?php echo htmlspecialchars($offer['text_color'] ?? '#ffffff'); ?>;">
                                    <h1 class="display-3 fw-bold mb-4"><?php echo htmlspecialchars($offer['title']); ?></h1>
                                    <?php if (!empty($offer['subtitle'])): ?>
                                        <p class="lead mb-4"><?php echo htmlspecialchars($offer['subtitle']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($offer['link'])): ?>
                                        <div class="d-grid gap-2 d-md-flex justify-content-center">
                                            <a href="<?php echo htmlspecialchars($offer['link']); ?>" class="btn btn-light btn-lg px-4 me-md-2" target="_blank" rel="noopener">
                                                <?php echo htmlspecialchars($cta_text); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="4"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="carousel-slide">
                            <div class="carousel-background" style="background-image: url('https://picsum.photos/seed/school-uniforms-backpack/1470/500.jpg');"></div>
                            <div class="carousel-overlay"></div>
                            <div class="carousel-content">
                                <h1 class="display-3 fw-bold mb-4">Back to School Sale!</h1>
                                <p class="lead mb-4">Get 20% off on all school uniforms. Limited time offer!</p>
                                <div class="d-grid gap-2 d-md-flex justify-content-center">
                                    <a href="catalog.php" class="btn btn-light btn-lg px-4 me-md-2">Shop Now</a>
                                    <a href="register.php" class="btn btn-outline-light btn-lg px-4">Sign Up</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="carousel-slide">
                            <div class="carousel-background" style="background-image: url('https://picsum.photos/seed/school-uniforms-professional/1470/500.jpg');"></div>
                            <div class="carousel-overlay"></div>
                            <div class="carousel-content">
                                <h1 class="display-3 fw-bold mb-4">Premium Quality Uniforms</h1>
                                <p class="lead mb-4">Durable, comfortable, and stylish uniforms for every student</p>
                                <div class="d-grid gap-2 d-md-flex justify-content-center">
                                    <a href="catalog.php" class="btn btn-light btn-lg px-4 me-md-2">Explore Collection</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="carousel-slide">
                            <div class="carousel-background" style="background-image: url('https://picsum.photos/seed/fast-delivery-shipping/1470/500.jpg');"></div>
                            <div class="carousel-overlay"></div>
                            <div class="carousel-content">
                                <h1 class="display-3 fw-bold mb-4">Fast Delivery</h1>
                                <p class="lead mb-4">Order today, receive within 48 hours. Free delivery on orders above KSh 5000!</p>
                                <div class="d-grid gap-2 d-md-flex justify-content-center">
                                    <a href="register.php" class="btn btn-light btn-lg px-4 me-md-2">Get Started</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="carousel-slide">
                            <div class="carousel-background" style="background-image: url('https://picsum.photos/seed/stationery-school-supplies/1470/500.jpg');"></div>
                            <div class="carousel-overlay"></div>
                            <div class="carousel-content">
                                <h1 class="display-3 fw-bold mb-4">Stationery Essentials</h1>
                                <p class="lead mb-4">Complete your school supplies with our premium stationery collection</p>
                                <div class="d-grid gap-2 d-md-flex justify-content-center">
                                    <a href="catalog.php?category=stationery" class="btn btn-light btn-lg px-4 me-md-2">Browse Stationery</a>
                                    <a href="register.php" class="btn btn-outline-light btn-lg px-4">Create Account</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="carousel-slide">
                            <div class="carousel-background" style="background-image: url('https://picsum.photos/seed/educational-books-learning/1470/500.jpg');"></div>
                            <div class="carousel-overlay"></div>
                            <div class="carousel-content">
                                <h1 class="display-3 fw-bold mb-4">Books & Learning</h1>
                                <p class="lead mb-4">Educational books and learning materials for academic excellence</p>
                                <div class="d-grid gap-2 d-md-flex justify-content-center">
                                    <a href="catalog.php?category=books" class="btn btn-light btn-lg px-4 me-md-2">Shop Books</a>
                                    <a href="register.php" class="btn btn-outline-light btn-lg px-4">Join Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <!-- Jumia Style Promotional Banner -->
    <div class="jumia-promo-bar">
        <div class="container">
            <div class="promo-container" id="jumiaPromo">
                <i class="fas fa-fire promo-icon"></i>
                <span class="promo-text">Hot Deals: <span class="promo-highlight">Up to 50% OFF</span> on selected items</span>
            </div>
        </div>
    </div>

    <!-- Deals of the Week Section -->
    <?php if (!empty($deals_products)): ?>
    <section class="bg-light py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col">
                    <h2 class="fw-bold">
                        <a href="deals.php" class="text-decoration-none" style="color: inherit;">
                            <i class="fas fa-fire text-danger me-2"></i>Deals of the Week
                        </a>
                    </h2>
                    <p class="text-muted">Amazing offers on selected items</p>
                    <a href="deals.php" class="btn btn-outline-primary btn-sm">View all deals</a>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach ($deals_products as $product): ?>
                    <?php
                        $price = (float)($product['price'] ?? 0);
                        $discount = (float)($product['discount_price'] ?? 0);
                        $has_discount = $discount > 0 && $discount < $price;
                        $final_price = $has_discount ? $discount : $price;
                        $discount_pct = $has_discount && $price > 0 ? (int)round((1 - ($discount / $price)) * 100) : null;
                    ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card deal-card h-100 shadow-sm" data-detail-url="product.php?pid=<?php echo urlencode(!empty($product['private_id']) ? $product['private_id'] : $product['id']); ?>" style="cursor: pointer;">
                            <div class="deal-badge bg-danger text-white">
                                <small><?php echo $discount_pct ? ('SAVE ' . $discount_pct . '%') : 'DEAL'; ?></small>
                            </div>
                            <div class="card-img-top bg-light text-center">
                                <?php $card_images = $product['images'] ?? []; ?>
                                <?php if (!empty($card_images) && count($card_images) > 1): ?>
                                    <?php $carousel_id = 'dealsCarousel' . (string)$product['id']; ?>
                                    <div id="<?php echo htmlspecialchars($carousel_id); ?>" class="carousel slide" data-bs-touch="true" data-bs-interval="false">
                                        <div class="carousel-inner">
                                            <?php foreach ($card_images as $idx => $img_url): ?>
                                                <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                                    <img src="<?php echo htmlspecialchars($img_url); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="d-block w-100 deal-image">
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
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="deal-image">
                                <?php else: ?>
                                    <i class="fas fa-tshirt fa-3x" style="color: var(--primary-color); margin: 60px 0;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="h5 mb-0 text-danger">KSh <?php echo number_format($final_price, 2); ?></span>
                                            <?php if ($has_discount): ?>
                                                <small class="text-muted text-decoration-line-through d-block">KSh <?php echo number_format($price, 2); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="product.php?pid=<?php echo urlencode(!empty($product['private_id']) ? $product['private_id'] : $product['id']); ?>" class="btn btn-primary btn-sm w-100">
                                    View Deal
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Content Area with Sidebar -->
    <div class="container py-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-12">
                <!-- Featured Products Section -->
                <?php if (!empty($featured_products)): ?>
                <section class="mb-5">
                    <div class="row align-items-end mb-4">
                        <div class="col-12 col-md">
                            <h2 class="fw-bold mb-1" style="color: var(--primary-color);">Featured Products</h2>
                            <p class="text-muted mb-0">Check out our latest uniform collection</p>
                        </div>
                        <div class="col-12 col-md-auto mt-3 mt-md-0">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Shop by Category
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <?php foreach ($categories as $category): ?>
                                        <li>
                                            <a class="dropdown-item" href="catalog.php?category=<?php echo urlencode($category['id']); ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modern Product Cards -->
                    <div class="row g-4">
                        <?php foreach ($featured_products as $product): ?>
                            <?php 
                            $primary_image = $product['primary_image'] ?? $product['image_url'] ?? '';
                            $card_images = $product['images'] ?? [];
                            $original_price = $product['price'];
                            $sale_price = isset($product['discount_price']) && $product['discount_price'] > 0 && $product['discount_price'] < $original_price ? $product['discount_price'] : null;
                            $display_price = $sale_price ?? $original_price;
                            $is_deal = !empty($product['is_deal']) || $sale_price !== null;
                            ?>
                            <div class="col-md-6 col-lg-3">
                                <div class="modern-product-card" data-product-id="<?php echo $product['id']; ?>" data-detail-url="product.php?pid=<?php echo urlencode(!empty($product['private_id']) ? $product['private_id'] : $product['id']); ?>" style="cursor: pointer;">
                                    <!-- Product Image -->
                                    <div class="product-image-container">
                                        <?php if (!empty($card_images) && count($card_images) > 1): ?>
                                            <?php $carousel_id = 'featuredCarousel' . (string)$product['id']; ?>
                                            <div id="<?php echo htmlspecialchars($carousel_id); ?>" class="carousel slide" data-bs-touch="true" data-bs-interval="false">
                                                <div class="carousel-inner">
                                                    <?php foreach ($card_images as $idx => $img_url): ?>
                                                        <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                                            <img src="<?php echo htmlspecialchars($img_url); ?>"
                                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                                 class="d-block w-100 product-image">
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
                                        <?php elseif (!empty($primary_image)): ?>
                                            <img src="<?php echo htmlspecialchars($primary_image); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="product-image">
                                        <?php else: ?>
                                            <div class="product-placeholder">
                                                <i class="fas fa-tshirt"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Wishlist Heart -->
                                        <button class="wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="far fa-heart"></i>
                                        </button>
                                        
                                        <!-- Sale Badge -->
                                        <?php if ($sale_price): ?>
                                            <div class="sale-badge">
                                                <?php echo round((($original_price - $sale_price) / $original_price) * 100); ?>% OFF
                                            </div>
                                        <?php elseif ($is_deal): ?>
                                            <div class="sale-badge">DEAL</div>
                                        <?php endif; ?>
                                        
                                        <!-- Add to Cart Button -->
                                        <button class="add-to-cart-btn" 
                                                data-product-id="<?php echo $product['id']; ?>"
                                                data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Product Info -->
                                    <div class="product-info">
                                        <h6 class="product-title">
                                            <a href="product.php?pid=<?php echo urlencode(!empty($product['private_id']) ? $product['private_id'] : $product['id']); ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h6>
                                        
                                        <!-- Price -->
                                        <div class="product-price">
                                            <?php if ($sale_price): ?>
                                                <span class="current-price">KSh <?php echo number_format($sale_price, 2); ?></span>
                                                <span class="original-price">KSh <?php echo number_format($original_price, 2); ?></span>
                                            <?php else: ?>
                                                <span class="current-price">KSh <?php echo number_format($display_price, 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <section class="container py-5">
        <div class="row text-center mb-5">
            <div class="col">
                <h2 class="fw-bold section-title">Frequently Asked Questions</h2>

                <p class="text-muted">Everything you need to know about ordering school uniforms online</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="fas fa-question-circle me-2" style="color: var(--primary-color);"></i>
                                How can I place a school uniform order?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Placing an order is easy! Simply browse our uniform catalog, select the items you need, add them to your cart, 
                                choose the correct sizes using our size charts, and proceed to checkout. Follow the on-screen instructions 
                                to complete your purchase. You can pay via M-Pesa, Airtel Money, PayPal, or credit/debit cards.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="fas fa-credit-card me-2" style="color: var(--primary-color);"></i>
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept a variety of payment methods for your convenience: M-Pesa, Airtel Money for mobile payments, 
                                PayPal for international transactions, and Visa/Mastercard for credit/debit card payments. 
                                Choose the option that suits you best during the checkout process.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="fas fa-shipping-fast me-2" style="color: var(--primary-color);"></i>
                                How long does delivery take?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Delivery times vary depending on your location in Kenya. For Nairobi and major towns, delivery typically 
                                takes 2-3 business days. For other areas, delivery may take 3-5 business days. You can check the estimated 
                                delivery time during the checkout process. We also offer express delivery options for urgent orders.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="fas fa-exchange-alt me-2" style="color: var(--primary-color);"></i>
                                Do you offer refunds or returns?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we have a hassle-free return and refund policy. If you're not satisfied with your purchase due to 
                                size issues or quality concerns, you can initiate a return request within 7 days of delivery. 
                                We'll guide you through the process and ensure you get the right size or a refund.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                Is my personal information safe?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely. We take data security seriously and have robust measures in place to protect your personal 
                                information. All payment transactions are encrypted, and we never share your details with third parties. 
                                Your data is safe with us, and we comply with all data protection regulations in Kenya.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Countdown Timer Script -->
    <script>
        // Set the target date (7 days from now for demo)
        const targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + 7);
        targetDate.setHours(23, 59, 59, 999);

        function updateCountdown() {
            const daysEl = document.getElementById('days');
            const hoursEl = document.getElementById('hours');
            const minutesEl = document.getElementById('minutes');
            const secondsEl = document.getElementById('seconds');

            if (!daysEl || !hoursEl || !minutesEl || !secondsEl) {
                return;
            }

            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                daysEl.textContent = '00';
                hoursEl.textContent = '00';
                minutesEl.textContent = '00';
                secondsEl.textContent = '00';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            daysEl.textContent = String(days).padStart(2, '0');
            hoursEl.textContent = String(hours).padStart(2, '0');
            minutesEl.textContent = String(minutes).padStart(2, '0');
            secondsEl.textContent = String(seconds).padStart(2, '0');
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown(); // Initial call

        // Jumia Style Promotional Banner Rotation
        const jumiaPromos = <?php echo json_encode($jumia_promos); ?>;

        class JumiaPromoRotator {
            constructor() {
                this.promoElement = document.getElementById('jumiaPromo');
                this.currentIndex = 0;
                this.interval = 3000; // 3 seconds rotation
                this.autoRotateTimer = null;
                
                this.promos = (Array.isArray(jumiaPromos) && jumiaPromos.length > 0) ? jumiaPromos : [
                    { image: 'https://picsum.photos/seed/hotdeals/20/20.jpg', title: 'Hot Deals', subtitle: 'Up to 50% OFF on selected items', link: '' },
                    { image: 'https://picsum.photos/seed/megasale/20/20.jpg', title: 'Mega Sale', subtitle: 'Buy 2 Get 1 FREE on stationery items', link: '' },
                    { image: 'https://picsum.photos/seed/phones/20/20.jpg', title: 'Phone Deals', subtitle: 'Smart Tablets from KES 15,999', link: '' },
                    { image: 'https://picsum.photos/seed/flashsale/20/20.jpg', title: 'Flash Sale', subtitle: '70% OFF on selected backpacks', link: '' },
                    { image: 'https://picsum.photos/seed/delivery/20/20.jpg', title: 'Free Delivery', subtitle: 'Orders Above KES 3,000', link: '' },
                    { image: 'https://picsum.photos/seed/school/20/20.jpg', title: 'Back to School', subtitle: 'Complete Sets from KES 2,500', link: '' },
                    { image: 'https://picsum.photos/seed/clearance/20/20.jpg', title: 'Clearance', subtitle: 'Up to 80% OFF on last season items', link: '' },
                    { image: 'https://picsum.photos/seed/support/20/20.jpg', title: 'Support', subtitle: '0712 345 678 - 24/7 Support', link: '' }
                ];
                
                this.init();
            }

            init() {
                // Start auto rotation
                this.startAutoRotate();
                
                // Add hover pause functionality
                const promoBar = document.querySelector('.jumia-promo-bar');
                promoBar.addEventListener('mouseenter', () => this.pauseAutoRotate());
                promoBar.addEventListener('mouseleave', () => this.startAutoRotate());
            }

            showPromo(index) {
                const promo = this.promos[index];
                const title = promo.title || 'Offer';
                const subtitle = promo.subtitle || '';
                const link = promo.link || '';
                const text = subtitle ? `${title}: <span class="promo-highlight">${subtitle}</span>` : title;
                const linkHtml = link ? ` <a href="${link}" class="promo-link" target="_blank" rel="noopener">Learn more</a>` : '';
                const imageHtml = promo.image ? `<img src="${promo.image}" alt="Promo" class="promo-image">` : '';

                this.promoElement.innerHTML = `
                    ${imageHtml}
                    <span class="promo-text">${text}${linkHtml}</span>
                `;
            }

            nextPromo() {
                this.currentIndex = (this.currentIndex + 1) % this.promos.length;
                this.showPromo(this.currentIndex);
            }

            startAutoRotate() {
                this.pauseAutoRotate(); // Clear any existing timer
                this.autoRotateTimer = setInterval(() => this.nextPromo(), this.interval);
            }

            pauseAutoRotate() {
                if (this.autoRotateTimer) {
                    clearInterval(this.autoRotateTimer);
                    this.autoRotateTimer = null;
                }
            }
        }

        // Initialize Jumia promo rotator when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new JumiaPromoRotator();
        });

    </script>

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

    <script>
    // Product Gallery Lightbox
    const productImages = <?php echo json_encode($product_images); ?>;
    console.log('Product images loaded:', productImages);
    let currentProductId = null;
    let currentImageIndex = 0;

    function openLightbox(productId, imageIndex) {
        console.log('Opening lightbox for product:', productId, 'image:', imageIndex);
        
        // Prevent event bubbling
        if (event) {
            event.stopPropagation();
        }
        
        currentProductId = productId;
        currentImageIndex = imageIndex;
        
        // Check if product has gallery images
        const images = productImages[productId] || [];
        console.log('Images for product:', productId, ':', images);
        
        // If no gallery images, try to use the main product image
        if (images.length === 0) {
            console.log('No gallery images found, trying to find product main image');
            // Find the product element and get its main image
            const productElement = document.querySelector(`[data-product-id="${productId}"]`);
            if (productElement) {
                const mainImage = productElement.querySelector('.product-gallery img');
                if (mainImage && mainImage.src) {
                    console.log('Using main product image:', mainImage.src);
                    showLightboxImage(mainImage.src, 1, 1);
                    return;
                }
            }
            console.log('No images found for this product');
            return;
        }
        
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const counter = document.getElementById('lightbox-counter');
        
        lightboxImage.src = images[imageIndex].image_url;
        counter.textContent = `${imageIndex + 1} / ${images.length}`;
        
        lightbox.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function showLightboxImage(imageSrc, currentIndex, totalImages) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const counter = document.getElementById('lightbox-counter');
        
        lightboxImage.src = imageSrc;
        counter.textContent = `${currentIndex} / ${totalImages}`;
        
        lightbox.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        console.log('Closing lightbox');
        const lightbox = document.getElementById('lightbox');
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto';
        currentProductId = null;
        currentImageIndex = 0;
    }

    function navigateLightbox(direction) {
        console.log('Navigating lightbox:', direction);
        
        // Prevent event bubbling
        if (event) {
            event.stopPropagation();
        }
        
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

    // Add click event listeners to all product galleries
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up gallery listeners');
        
        // Find all product gallery elements
        const galleries = document.querySelectorAll('.product-gallery');
        console.log('Found galleries:', galleries.length);
        
        galleries.forEach((gallery, index) => {
            gallery.addEventListener('click', function(e) {
                console.log('Gallery clicked:', index);
                // Try to extract product ID from the onclick attribute or data attribute
                const onclick = this.getAttribute('onclick');
                if (onclick) {
                    const match = onclick.match(/openLightbox\((\d+),\s*(\d+)\)/);
                    if (match) {
                        const productId = parseInt(match[1]);
                        const imageIndex = parseInt(match[2]);
                        openLightbox(productId, imageIndex);
                    }
                }
            });
        });

        // Handle Quick Add to Cart buttons
        const quickAddButtons = document.querySelectorAll('.quick-add-btn');
        console.log('Found quick-add buttons:', quickAddButtons.length);
        
        quickAddButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                const originalText = this.innerHTML;
                
                console.log('Quick add clicked for product:', productId, productName);
                
                // Disable button and show loading state
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';
                
                // Send AJAX request to add to cart
                fetch('add_to_cart_ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Add to cart response:', data);
                    
                    if (data.success) {
                        // Update cart count in navbar
                        const cartBadge = document.querySelector('.navbar .badge');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                        } else if (data.cart_count > 0) {
                            // Create badge if it doesn't exist
                            const cartLink = document.querySelector('.navbar a[href="cart.php"]');
                            if (cartLink) {
                                const badge = document.createElement('span');
                                badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                                badge.textContent = data.cart_count;
                                cartLink.appendChild(badge);
                            }
                        }
                        
                        // Show success feedback
                        this.innerHTML = '<i class="fas fa-check me-1"></i> Added!';
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-success');
                        
                        // Reset button after 2 seconds
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-outline-primary');
                            this.disabled = false;
                        }, 2000);
                        
                        // Show toast notification
                        showToast('Success', data.message, 'success');
                    } else if (data.requires_size) {
                        // Product requires size selection - redirect to product page
                        window.location.href = data.redirect_url;
                    } else {
                        // Show error
                        this.innerHTML = originalText;
                        this.disabled = false;
                        showToast('Error', data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    this.innerHTML = originalText;
                    this.disabled = false;
                    showToast('Error', 'Failed to add item to cart. Please try again.', 'danger');
                });
            });
        });
    });

    // Modern Product Card Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const detailCards = document.querySelectorAll('.modern-product-card[data-detail-url], .deal-card[data-detail-url]');
        detailCards.forEach(card => {
            card.addEventListener('click', (event) => {
                const ignore = event.target.closest('a, button, input, select, textarea, .carousel-control-prev, .carousel-control-next, .carousel-indicators');
                if (ignore) return;
                const url = card.getAttribute('data-detail-url');
                if (url) window.location.href = url;
            });
        });

        // Handle wishlist buttons
        const wishlistButtons = document.querySelectorAll('.wishlist-btn');
        wishlistButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = this.getAttribute('data-product-id');
                const icon = this.querySelector('i');
                
                // Toggle wishlist state
                if (this.classList.contains('active')) {
                    // Remove from wishlist
                    this.classList.remove('active');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    showToast('Wishlist', 'Item removed from wishlist', 'info');
                } else {
                    // Add to wishlist
                    this.classList.add('active');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    showToast('Wishlist', 'Item added to wishlist', 'success');
                }
                
                // Here you could add AJAX call to save wishlist state to server
                // saveWishlistState(productId, this.classList.contains('active'));
            });
        });

        // Handle modern add-to-cart buttons
        const modernAddToCartButtons = document.querySelectorAll('.add-to-cart-btn');
        modernAddToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (this.disabled) return;
                
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                const originalIcon = this.innerHTML;
                
                // Show loading state
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                // Send AJAX request to add to cart
                fetch('add_to_cart_ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count in navbar
                        const cartBadge = document.querySelector('.navbar .badge');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                        } else if (data.cart_count > 0) {
                            // Create badge if it doesn't exist
                            const cartLink = document.querySelector('.navbar a[href="cart.php"]');
                            if (cartLink) {
                                const badge = document.createElement('span');
                                badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                                badge.textContent = data.cart_count;
                                cartLink.appendChild(badge);
                            }
                        }
                        
                        // Show success feedback
                        this.innerHTML = '<i class="fas fa-check"></i>';
                        this.style.background = '#28a745';
                        
                        // Reset button after 2 seconds
                        setTimeout(() => {
                            this.innerHTML = originalIcon;
                            this.style.background = '';
                            this.disabled = false;
                        }, 2000);
                        
                        // Show toast notification
                        showToast('Success', data.message, 'success');
                    } else if (data.requires_size) {
                        // Product requires size selection - redirect to product page
                        window.location.href = data.redirect_url;
                    } else {
                        // Show error
                        this.innerHTML = originalIcon;
                        this.disabled = false;
                        showToast('Error', data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    this.innerHTML = originalIcon;
                    this.disabled = false;
                    showToast('Error', 'Failed to add item to cart. Please try again.', 'danger');
                });
            });
        });
    });

    // Toast notification function
    function showToast(title, message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toastId = 'toast-' + Date.now();
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
    </script>
    
    <?php require_once 'views/footer.php'; ?>
</body>
</html>
