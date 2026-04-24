<?php
require_once 'config/environment.php'; // Replaced session_start()
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo json_encode([]);
    exit;
}

$db = new DBHelper();
$product_id = $_GET['product_id'];

$sizes = $db->fetchAll("
    SELECT size, age_range, chest, waist, hips, height 
    FROM size_charts 
    WHERE product_id = ? 
    ORDER BY 
        CASE 
            WHEN size = 'XS' THEN 1
            WHEN size = 'S' THEN 2
            WHEN size = 'M' THEN 3
            WHEN size = 'L' THEN 4
            WHEN size = 'XL' THEN 5
            WHEN size = 'XXL' THEN 6
            ELSE 7
        END
", [$product_id]);

echo json_encode($sizes);
?>