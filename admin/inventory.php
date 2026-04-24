<?php
// Use centralized session management
require_once '../config/environment.php';

require_once '../includes/auth.php';
require_once '../includes/db.php';
$auth = new Auth();
$auth->requireAdmin();

$db = new DBHelper();

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_product') {
        $data = [
            'category_id' => $_POST['category_id'],
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'price' => $_POST['price'],
            'discount_price' => ($_POST['discount_price'] ?? '') !== '' ? $_POST['discount_price'] : null,
            'stock_quantity' => $_POST['stock_quantity'],
            'is_deal' => isset($_POST['is_deal']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Handle multiple image uploads
        $uploaded_images = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $upload_dir = '../assets/images/';
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = uniqid() . '_' . basename($name);
                    $target_file = $upload_dir . $file_name;
                    $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    
                    if (in_array($file_extension, $allowed_extensions) && $_FILES['images']['size'][$key] <= 5 * 1024 * 1024) {
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_file)) {
                            $uploaded_images[] = 'assets/images/' . $file_name;
                        }
                    }
                }
            }
            
            // Store the first image as primary in products table
            if (!empty($uploaded_images)) {
                $data['image_url'] = $uploaded_images[0];
            }
        }

        $product_id = $db->insert('products', $data);
        if ($product_id) {
            // Store all uploaded images in product_images table
            if (!empty($uploaded_images)) {
                foreach ($uploaded_images as $index => $image_url) {
                    $image_data = [
                        'product_id' => $product_id,
                        'image_url' => $image_url,
                        'is_primary' => ($index === 0) ? 1 : 0, // First image is primary
                        'sort_order' => $index
                    ];
                    $db->insert('product_images', $image_data);
                }
            }
            $_SESSION['success'] = 'Product added successfully with ' . count($uploaded_images) . ' image(s)';
        } else {
            $_SESSION['error'] = 'Failed to add product';
        }
    } elseif ($action === 'update_product') {
        $product_id = $_POST['product_id'];
        $data = [
            'category_id' => $_POST['category_id'],
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'price' => $_POST['price'],
            'discount_price' => ($_POST['discount_price'] ?? '') !== '' ? $_POST['discount_price'] : null,
            'stock_quantity' => $_POST['stock_quantity'],
            'is_deal' => isset($_POST['is_deal']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Handle multiple image uploads for update
        $uploaded_images = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $upload_dir = '../assets/images/';
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = uniqid() . '_' . basename($name);
                    $target_file = $upload_dir . $file_name;
                    $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    
                    if (in_array($file_extension, $allowed_extensions) && $_FILES['images']['size'][$key] <= 5 * 1024 * 1024) {
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_file)) {
                            $uploaded_images[] = 'assets/images/' . $file_name;
                        }
                    }
                }
            }
            
            // If new images uploaded, update primary image and store all in product_images table
            if (!empty($uploaded_images)) {
                // Update primary image in products table
                $data['image_url'] = $uploaded_images[0];
                
                // Delete old images from product_images table
                $db->delete('product_images', "product_id = $product_id");
                
                // Insert all new images
                foreach ($uploaded_images as $index => $image_url) {
                    $image_data = [
                        'product_id' => $product_id,
                        'image_url' => $image_url,
                        'is_primary' => ($index === 0) ? 1 : 0,
                        'sort_order' => $index
                    ];
                    $db->insert('product_images', $image_data);
                }
            }
        }

        if ($db->update('products', $data, "id = $product_id")) {
            $_SESSION['success'] = 'Product updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update product';
        }
    } elseif ($action === 'add_size') {
        $data = [
            'product_id' => $_POST['product_id'],
            'size' => $_POST['size'],
            'chest' => $_POST['chest'],
            'waist' => $_POST['waist'],
            'hips' => $_POST['hips'],
            'height' => $_POST['height'],
            'age_range' => $_POST['age_range']
        ];

        if ($db->insert('size_charts', $data)) {
            $_SESSION['success'] = 'Size chart added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add size chart';
        }
    } elseif ($action === 'add_category') {
        $name = trim($_POST['category_name']);
        $description = trim($_POST['category_description']);
        
        if (!empty($name)) {
            $data = [
                'name' => $name,
                'description' => $description
            ];
            
            if ($db->insert('categories', $data)) {
                $_SESSION['success'] = 'Category added successfully';
            } else {
                $_SESSION['error'] = 'Failed to add category';
            }
        } else {
            $_SESSION['error'] = 'Category name is required';
        }
    } elseif ($action === 'delete_category') {
        $category_id = $_POST['category_id'];
        
        // Check if category has products
        $products_count = $db->fetchSingle("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [$category_id]);
        
        if ($products_count > 0) {
            $_SESSION['error'] = 'Cannot delete category. It contains products. Please move or delete the products first.';
        } else {
            if ($db->delete('categories', "id = $category_id")) {
                $_SESSION['success'] = 'Category deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete category';
            }
        }
    } elseif ($action === 'delete_product') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        if ($product_id <= 0) {
            $_SESSION['error'] = 'Invalid product ID';
        } else {
            try {
                $db->query("START TRANSACTION");

                // Check if product has any orders
                $order_check = $db->fetchOne("SELECT COUNT(*) as cnt FROM order_items WHERE product_id = ?", [$product_id]);
                if ($order_check && $order_check['cnt'] > 0) {
                    $_SESSION['error'] = 'Cannot delete product: it has existing orders. Consider deactivating it instead.';
                } else {
                    // Delete product images first
                    $db->delete('product_images', "product_id = $product_id");

                    // Delete the product
                    $deleted = $db->delete('products', "id = $product_id");
                    if ($deleted) {
                        $_SESSION['success'] = 'Product deleted successfully';
                    } else {
                        $_SESSION['error'] = 'Failed to delete product (no changes made).';
                    }
                }

                $db->query("COMMIT");
            } catch (Exception $e) {
                $db->query("ROLLBACK");
                error_log("Delete product error: " . $e->getMessage());
                $_SESSION['error'] = 'Failed to delete product: ' . $e->getMessage();
            }
        }
    }
}

// Get all products with categories
$products = $db->fetchAll("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.name
");

$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

// Get low stock items
$low_stock = $db->fetchAll("SELECT * FROM products WHERE stock_quantity < 10 AND is_active = 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/zetech-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../views/admin_header.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Inventory Management</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Products</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                    <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($product['image_url']): ?>
                                                        <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <i class="fas fa-tshirt text-primary me-3 fa-2x"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($product['description']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td>
                                                <?php
                                                $price = (float)($product['price'] ?? 0);
                                                $discount = (float)($product['discount_price'] ?? 0);
                                                ?>
                                                <?php if ($discount > 0 && $discount < $price): ?>
                                                    <div class="fw-bold text-danger">KSh <?php echo number_format($discount, 2); ?></div>
                                                    <small class="text-muted text-decoration-line-through">KSh <?php echo number_format($price, 2); ?></small>
                                                <?php else: ?>
                                                    KSh <?php echo number_format($price, 2); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($product['is_deal'])): ?>
                                                    <span class="badge bg-warning text-dark ms-1">Deal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['stock_quantity'] > 10 ? 'success' : ($product['stock_quantity'] > 0 ? 'warning' : 'danger'); ?>">
                                                    <?php echo $product['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editProductModal"
                                                            data-product='<?php echo json_encode($product); ?>'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#sizeChartModal"
                                                            data-product-id="<?php echo $product['id']; ?>"
                                                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                                        <i class="fas fa-ruler-combined"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger"
                                                            onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Categories Management -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Categories</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        <th>Products</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <?php 
                                        $product_count = $db->fetchSingle("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [$category['id']]);
                                        ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                    <?php if ($category['description']): ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($category['description']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $product_count; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-danger" 
                                                            onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')"
                                                            <?php echo $product_count > 0 ? 'disabled title="Cannot delete category with products"' : ''; ?>>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock Alert -->
                <?php if (!empty($low_stock)): ?>
                <div class="card shadow-sm border-warning mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Low Stock Alert
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($low_stock as $product): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                    <small class="text-muted">Current stock: <?php echo $product['stock_quantity']; ?></small>
                                </div>
                                <span class="badge bg-danger">Low</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Stats -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Inventory Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Products:</span>
                            <strong><?php echo count($products); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Active Products:</span>
                            <strong><?php echo count(array_filter($products, fn($p) => $p['is_active'])); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Out of Stock:</span>
                            <strong class="text-danger"><?php echo count(array_filter($products, fn($p) => $p['stock_quantity'] == 0)); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Low Stock (<10):</span>
                            <strong class="text-warning"><?php echo count($low_stock); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_product">

                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" id="categorySelect" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            data-requires-size="<?php echo in_array(strtolower($category['name']), ['shirts', 'dresses', 'trousers', 'sweaters', 'school shoes', 'pe kits(t-shirts, shorts, track suits', 'school socks']) ? 'true' : 'false'; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>

                        <!-- Size Options - Only shown for clothing/shoes -->
                        <div id="sizeOptions" class="mb-3" style="display: none;">
                            <label class="form-label">Size Options</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Available Sizes</label>
                                    <div class="size-checkboxes">
                                        <?php 
                                        $common_sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL'];
                                        foreach ($common_sizes as $size): 
                                        ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="sizes[]" value="<?php echo $size; ?>" id="size_<?php echo $size; ?>">
                                                <label class="form-check-label" for="size_<?php echo $size; ?>"><?php echo $size; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Size Type</label>
                                    <select class="form-select" name="size_type" id="sizeType">
                                        <option value="standard">Standard (S, M, L, XL)</option>
                                        <option value="numeric">Numeric (28, 30, 32, 34)</option>
                                        <option value="age">Age-based (3-4, 5-6, 7-8, 9-10)</option>
                                        <option value="shoe">Shoe Sizes (20, 21, 22, etc.)</option>
                                    </select>
                                </div>
                            </div>
                            <small class="text-muted">Select all available sizes for this product</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (KSh)</label>
                                <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" name="stock_quantity" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount Price (optional)</label>
                                <input type="number" class="form-control" name="discount_price" step="0.01" min="0" placeholder="Leave blank for no discount">
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_deal" id="is_deal">
                                    <label class="form-check-label" for="is_deal">Deal of the Week</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Images</label>
                            <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                            <div class="form-text">You can select multiple images. Supported formats: JPG, PNG, GIF, WebP. Max size per image: 5MB</div>
                            <div id="imagePreview" class="mt-3"></div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Active Product</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_product">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" id="edit_category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            data-requires-size="<?php echo in_array(strtolower($category['name']), ['shirts', 'dresses', 'trousers', 'sweaters', 'school shoes', 'pe kits(t-shirts, shorts, track suits', 'school socks']) ? 'true' : 'false'; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>

                        <!-- Edit Size Options - Only shown for clothing/shoes -->
                        <div id="editSizeOptions" class="mb-3" style="display: none;">
                            <label class="form-label">Size Options</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Available Sizes</label>
                                    <div class="size-checkboxes-edit">
                                        <?php 
                                        $common_sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL'];
                                        foreach ($common_sizes as $size): 
                                        ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="edit_sizes[]" value="<?php echo $size; ?>" id="edit_size_<?php echo $size; ?>">
                                                <label class="form-check-label" for="edit_size_<?php echo $size; ?>"><?php echo $size; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Size Type</label>
                                    <select class="form-select" name="edit_size_type" id="edit_size_type">
                                        <option value="standard">Standard (S, M, L, XL)</option>
                                        <option value="numeric">Numeric (28, 30, 32, 34)</option>
                                        <option value="age">Age-based (3-4, 5-6, 7-8, 9-10)</option>
                                        <option value="shoe">Shoe Sizes (20, 21, 22, etc.)</option>
                                    </select>
                                </div>
                            </div>
                            <small class="text-muted">Select all available sizes for this product</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (KSh)</label>
                                <input type="number" class="form-control" name="price" id="edit_price" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" name="stock_quantity" id="edit_stock_quantity" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount Price (optional)</label>
                                <input type="number" class="form-control" name="discount_price" id="edit_discount_price" step="0.01" min="0" placeholder="Leave blank for no discount">
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_deal" id="edit_is_deal">
                                    <label class="form-check-label" for="edit_is_deal">Deal of the Week</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Images (leave empty to keep current)</label>
                            <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                            <div class="form-text">You can select multiple images. Supported formats: JPG, PNG, GIF, WebP. Max size per image: 5MB</div>
                            <div id="editImagePreview" class="mt-3"></div>
                            <div id="current_image_container" class="mt-2" style="display: none;">
                                <small class="text-muted">Current image:</small><br>
                                <img id="current_image" src="" alt="Current product image" style="max-width: 100px; max-height: 100px;">
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                            <label class="form-check-label" for="edit_is_active">Active Product</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
            </main>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_category">
                        
                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" class="form-control" name="category_name" required>
                            <div class="form-text">Enter a unique category name (e.g., School Uniforms, Books, Stationery)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="category_description" rows="3"></textarea>
                            <div class="form-text">Optional description for this category</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Category Confirmation Form (hidden) -->
    <form id="deleteCategoryForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_category">
        <input type="hidden" name="category_id" id="delete_category_id">
    </form>

    <!-- Delete Product Confirmation Form (hidden) -->
    <form id="deleteProductForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_product">
        <input type="hidden" name="product_id" id="delete_product_id">
    </form>

    <?php include '../views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Edit Product Modal
    const editProductModal = document.getElementById('editProductModal');
    if (editProductModal) {
        editProductModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const product = JSON.parse(button.getAttribute('data-product'));
            
            const modal = this;
            modal.querySelector('input[name="product_id"]').value = product.id;
            modal.querySelector('input[name="name"]').value = product.name;
            modal.querySelector('select[name="category_id"]').value = product.category_id;
            modal.querySelector('textarea[name="description"]').value = product.description;
            modal.querySelector('input[name="price"]').value = product.price;
            if (modal.querySelector('input[name="discount_price"]')) {
                modal.querySelector('input[name="discount_price"]').value = product.discount_price || '';
            }
            modal.querySelector('input[name="stock_quantity"]').value = product.stock_quantity;
            modal.querySelector('input[name="is_active"]').checked = product.is_active;
            if (modal.querySelector('input[name="is_deal"]')) {
                modal.querySelector('input[name="is_deal"]').checked = !!product.is_deal;
            }
            
            // Handle size options for edit modal
            const editCategorySelect = document.getElementById('edit_category_id');
            const editSizeOptions = document.getElementById('editSizeOptions');
            
            if (editCategorySelect && editSizeOptions) {
                // Trigger change event to show/hide size options
                const selectedOption = editCategorySelect.options[editCategorySelect.selectedIndex];
                const requiresSize = selectedOption.getAttribute('data-requires-size') === 'true';
                
                if (requiresSize) {
                    editSizeOptions.style.display = 'block';
                } else {
                    editSizeOptions.style.display = 'none';
                }
            }
        });
    }
    
    // Delete Category Function
    function deleteCategory(categoryId, categoryName) {
        if (confirm('Are you sure you want to delete the category "' + categoryName + '"? This action cannot be undone.')) {
            document.getElementById('delete_category_id').value = categoryId;
            document.getElementById('deleteCategoryForm').submit();
        }
    }
    
    // Delete Product Function
    function deleteProduct(productId, productName) {
        if (confirm('Are you sure you want to delete the product "' + productName + '"? This action cannot be undone.')) {
            document.getElementById('delete_product_id').value = productId;
            document.getElementById('deleteProductForm').submit();
        }
    }
    
    // Smart Size Management
    document.addEventListener('DOMContentLoaded', function() {
        // Add Product Modal Size Management
        const categorySelect = document.getElementById('categorySelect');
        const sizeOptions = document.getElementById('sizeOptions');
        
        if (categorySelect && sizeOptions) {
            categorySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const requiresSize = selectedOption.getAttribute('data-requires-size') === 'true';
                
                if (requiresSize) {
                    sizeOptions.style.display = 'block';
                    // Make size selection required for clothing items
                    const sizeCheckboxes = sizeOptions.querySelectorAll('input[name="sizes[]"]');
                    sizeCheckboxes.forEach(checkbox => {
                        checkbox.setAttribute('required', 'required');
                    });
                } else {
                    sizeOptions.style.display = 'none';
                    // Remove required attribute for non-clothing items
                    const sizeCheckboxes = sizeOptions.querySelectorAll('input[name="sizes[]"]');
                    sizeCheckboxes.forEach(checkbox => {
                        checkbox.removeAttribute('required');
                        checkbox.checked = false;
                    });
                }
            });
        }
        
        // Edit Product Modal Size Management
        const editCategorySelect = document.getElementById('edit_category_id');
        const editSizeOptions = document.getElementById('editSizeOptions');
        
        if (editCategorySelect && editSizeOptions) {
            editCategorySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const requiresSize = selectedOption.getAttribute('data-requires-size') === 'true';
                
                if (requiresSize) {
                    editSizeOptions.style.display = 'block';
                } else {
                    editSizeOptions.style.display = 'none';
                    // Clear size selections
                    const sizeCheckboxes = editSizeOptions.querySelectorAll('input[name="edit_sizes[]"]');
                    sizeCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                }
            });
        }
        
        // Handle size type changes for both modals
        const sizeType = document.getElementById('sizeType');
        const editSizeType = document.getElementById('edit_size_type');
        
        if (sizeType) {
            sizeType.addEventListener('change', function() {
                updateSizeOptions(this.value, 'size-checkboxes');
            });
        }
        
        if (editSizeType) {
            editSizeType.addEventListener('change', function() {
                updateSizeOptions(this.value, 'size-checkboxes-edit');
            });
        }
    });
    
    function updateSizeOptions(sizeType, containerClass = 'size-checkboxes') {
        const sizeCheckboxes = document.querySelector('.' + containerClass);
        let sizes = [];
        
        switch(sizeType) {
            case 'numeric':
                sizes = ['28', '30', '32', '34', '36', '38', '40', '42', '44'];
                break;
            case 'age':
                sizes = ['3-4', '5-6', '7-8', '9-10', '11-12', '13-14'];
                break;
            case 'shoe':
                sizes = ['20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];
                break;
            default: // standard
                sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL'];
        }
        
        let html = '';
        const checkboxName = containerClass === 'size-checkboxes-edit' ? 'edit_sizes[]' : 'sizes[]';
        
        sizes.forEach((size, index) => {
            html += `
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="${checkboxName}" value="${size}" id="size_${containerClass}_${size}">
                    <label class="form-check-label" for="size_${containerClass}_${size}">${size}</label>
                </div>
            `;
        });
        
        sizeCheckboxes.innerHTML = html;
    }

    // Multiple Image Preview Functionality
    function handleImagePreview(input, previewContainer) {
        const files = input.files;
        previewContainer.innerHTML = '';
        
        if (files.length > 0) {
            const row = document.createElement('div');
            row.className = 'row g-2';
            
            Array.from(files).forEach((file, index) => {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-3';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    col.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 150px; object-fit: cover;">
                            <div class="text-center mt-1">
                                <small class="text-muted">${file.name}</small>
                            </div>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
                
                row.appendChild(col);
            });
            
            previewContainer.appendChild(row);
        }
    }

    // Add event listeners for image previews
    const addImageInput = document.querySelector('input[name="images[]"]');
    const editImageInput = document.querySelector('#editProductModal input[name="images[]"]');
    
    if (addImageInput) {
        addImageInput.addEventListener('change', function() {
            handleImagePreview(this, document.getElementById('imagePreview'));
        });
    }
    
    if (editImageInput) {
        editImageInput.addEventListener('change', function() {
            handleImagePreview(this, document.getElementById('editImagePreview'));
        });
    }
    </script>
    </main>
    </div>
</body>
</html>
