<?php
// Use centralized session management
require_once '../config/environment.php';

require_once '../includes/auth.php';
require_once '../includes/db.php';
$auth = new Auth();
$auth->requireAdmin();

$db = new DBHelper();

// Handle size chart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_size') {
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
    } elseif ($action === 'update_size') {
        $size_id = $_POST['size_id'];
        $data = [
            'size' => $_POST['size'],
            'chest' => $_POST['chest'],
            'waist' => $_POST['waist'],
            'hips' => $_POST['hips'],
            'height' => $_POST['height'],
            'age_range' => $_POST['age_range']
        ];
        
        if ($db->update('size_charts', $data, "id = $size_id")) {
            $_SESSION['success'] = 'Size chart updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update size chart';
        }
    } elseif ($action === 'delete_size') {
        $size_id = $_POST['size_id'];
        if ($db->query("DELETE FROM size_charts WHERE id = ?", [$size_id])) {
            $_SESSION['success'] = 'Size chart deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete size chart';
        }
    }
}

// Get all products and their size charts
$products = $db->fetchAll("
    SELECT p.*, c.name as category_name,
           (SELECT COUNT(*) FROM size_charts WHERE product_id = p.id) as size_count
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1
    ORDER BY p.name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Size Chart Management - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../views/admin_header.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Size Chart Management</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSizeModal">
                        <i class="fas fa-plus"></i> Add Size Chart
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
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <span class="badge bg-primary"><?php echo $product['size_count']; ?> sizes</span>
                                    </div>
                                    
                                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                    
                                    <!-- Size Charts for this product -->
                                    <?php
                                    $sizes = $db->fetchAll("SELECT * FROM size_charts WHERE product_id = ? ORDER BY size", [$product['id']]);
                                    ?>
                                    
                                    <?php if (!empty($sizes)): ?>
                                        <div class="table-responsive mb-3">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Size</th>
                                                        <th>Chest</th>
                                                        <th>Waist</th>
                                                        <th>Age</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($sizes as $size): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($size['size']); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($size['chest']); ?></td>
                                                            <td><?php echo htmlspecialchars($size['waist']); ?></td>
                                                            <td><?php echo htmlspecialchars($size['age_range']); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editSizeModal"
                                                                        data-size='<?php echo json_encode($size); ?>'>
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="action" value="delete_size">
                                                                    <input type="hidden" name="size_id" value="<?php echo $size['id']; ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                            onclick="return confirm('Are you sure you want to delete this size?')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning text-center py-2 mb-3">
                                            <small>No size charts defined</small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline-primary btn-sm w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addSizeModal"
                                            data-product-id="<?php echo $product['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                        <i class="fas fa-plus"></i> Add Size
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

    <!-- Add Size Modal -->
    <div class="modal fade" id="addSizeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add_size">
                    <input type="hidden" name="product_id" id="add_product_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Size Chart</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="add_product_name" readonly>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Size</label>
                                <input type="text" class="form-control" name="size" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Age Range</label>
                                <input type="text" class="form-control" name="age_range" placeholder="e.g., 6-8 years">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Chest (inches)</label>
                                <input type="text" class="form-control" name="chest" placeholder="e.g., 32-34">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Waist (inches)</label>
                                <input type="text" class="form-control" name="waist" placeholder="e.g., 28-30">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hips (inches)</label>
                                <input type="text" class="form-control" name="hips" placeholder="e.g., 32-34">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Height (inches)</label>
                            <input type="text" class="form-control" name="height" placeholder="e.g., 48-52">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Size</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Size Modal -->
    <div class="modal fade" id="editSizeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="update_size">
                    <input type="hidden" name="size_id" id="edit_size_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Size Chart</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Size</label>
                                <input type="text" class="form-control" name="size" id="edit_size" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Age Range</label>
                                <input type="text" class="form-control" name="age_range" id="edit_age_range">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Chest (inches)</label>
                                <input type="text" class="form-control" name="chest" id="edit_chest">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Waist (inches)</label>
                                <input type="text" class="form-control" name="waist" id="edit_waist">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hips (inches)</label>
                                <input type="text" class="form-control" name="hips" id="edit_hips">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Height (inches)</label>
                            <input type="text" class="form-control" name="height" id="edit_height">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Size</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
            </main>
        </div>
    </div>

    <?php include '../views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Add Size Modal
    const addSizeModal = document.getElementById('addSizeModal');
    addSizeModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const productId = button.getAttribute('data-product-id');
        const productName = button.getAttribute('data-product-name');
        
        const modal = this;
        modal.querySelector('#add_product_id').value = productId;
        modal.querySelector('#add_product_name').value = productName;
    });
    
    // Edit Size Modal
    const editSizeModal = document.getElementById('editSizeModal');
    editSizeModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const size = JSON.parse(button.getAttribute('data-size'));
        
        const modal = this;
        modal.querySelector('#edit_size_id').value = size.id;
        modal.querySelector('#edit_size').value = size.size;
        modal.querySelector('#edit_age_range').value = size.age_range;
        modal.querySelector('#edit_chest').value = size.chest;
        modal.querySelector('#edit_waist').value = size.waist;
        modal.querySelector('#edit_hips').value = size.hips;
        modal.querySelector('#edit_height').value = size.height;
    });
    </script>
    </main>
    </div>
</body>
</html>
