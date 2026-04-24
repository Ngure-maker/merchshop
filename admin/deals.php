<?php
require_once '../config/environment.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

$auth = new Auth();
$auth->requireAdmin();

$db = new DBHelper();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $discount_prices = $_POST['discount_price'] ?? [];
    $deal_flags = $_POST['is_deal'] ?? [];

    if ($action === 'save_row') {
        $product_id = (int)($_POST['row_id'] ?? 0);
        if ($product_id > 0) {
            $discount_price = $discount_prices[$product_id] ?? null;
            $discount_price = ($discount_price !== '' && $discount_price !== null) ? $discount_price : null;
            $is_deal = isset($deal_flags[$product_id]) ? 1 : 0;
            $updated = $db->update('products', [
                'discount_price' => $discount_price,
                'is_deal' => $is_deal
            ], "id = {$product_id}");
            $_SESSION[$updated ? 'success' : 'error'] = $updated ? 'Deal updated successfully' : 'Failed to update deal';
        }
        header('Location: deals.php');
        exit;
    }

    if ($action === 'bulk_update') {
        $selected = $_POST['selected_ids'] ?? [];
        $bulk_action = $_POST['bulk_action'] ?? '';
        $bulk_discount = ($_POST['bulk_discount'] ?? '') !== '' ? $_POST['bulk_discount'] : null;
        $bulk_discount_percent = ($_POST['bulk_discount_percent'] ?? '') !== '' ? (float)$_POST['bulk_discount_percent'] : null;
        $updated_any = false;

        if ($bulk_action === '' || empty($selected)) {
            $_SESSION['error'] = 'Select products and a bulk action.';
            header('Location: deals.php');
            exit;
        }

        foreach ($selected as $id) {
            $product_id = (int)$id;
            if ($product_id <= 0) {
                continue;
            }
            if ($bulk_action === 'set_deal_on') {
                $updated_any = $db->update('products', ['is_deal' => 1], "id = {$product_id}") || $updated_any;
            } elseif ($bulk_action === 'set_deal_off') {
                $updated_any = $db->update('products', ['is_deal' => 0], "id = {$product_id}") || $updated_any;
            } elseif ($bulk_action === 'set_discount') {
                $updated_any = $db->update('products', ['discount_price' => $bulk_discount], "id = {$product_id}") || $updated_any;
            } elseif ($bulk_action === 'clear_discount') {
                $updated_any = $db->update('products', ['discount_price' => null], "id = {$product_id}") || $updated_any;
            } elseif ($bulk_action === 'set_discount_percent') {
                if ($bulk_discount_percent !== null && $bulk_discount_percent > 0 && $bulk_discount_percent < 100) {
                    $row = $db->fetchOne("SELECT price FROM products WHERE id = ?", [$product_id]);
                    $price = (float)($row['price'] ?? 0);
                    if ($price > 0) {
                        $discount_price = round($price * (1 - ($bulk_discount_percent / 100)), 2);
                        $updated_any = $db->update('products', ['discount_price' => $discount_price], "id = {$product_id}") || $updated_any;
                    }
                }
            }
        }

        $_SESSION[$updated_any ? 'success' : 'error'] = $updated_any ? 'Bulk update completed' : 'No products updated';
        header('Location: deals.php');
        exit;
    }
}

$search = trim((string)($_GET['search'] ?? ''));
$params = [];
$where = '';
if ($search !== '') {
    $where = 'WHERE p.name LIKE ? OR c.name LIKE ?';
    $like = '%' . $search . '%';
    $params = [$like, $like];
}

$products = $db->fetchAll(
    "SELECT p.*, c.name as category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     $where
     ORDER BY p.name",
    $params
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deals Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/zetech-theme.css" rel="stylesheet">
</head>
<body>
    <?php include '../views/admin_header.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-tags me-2"></i>Deals Management</h1>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search products or categories" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-primary">Search</button>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Products</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <form method="POST">
                    <div class="p-3 border-bottom bg-light">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <select name="bulk_action" class="form-select form-select-sm">
                                    <option value="">Bulk action...</option>
                                    <option value="set_deal_on">Mark as Deal</option>
                                    <option value="set_deal_off">Remove Deal</option>
                                    <option value="set_discount">Set Discount Price</option>
                                    <option value="set_discount_percent">Set Discount %</option>
                                    <option value="clear_discount">Clear Discount Price</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" min="0" name="bulk_discount" class="form-control form-control-sm" placeholder="Discount price">
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.1" min="1" max="90" name="bulk_discount_percent" class="form-control form-control-sm" placeholder="% off">
                            </div>
                            <div class="col-md-2 d-grid">
                                <button class="btn btn-sm btn-primary" name="action" value="bulk_update">Apply</button>
                            </div>
                        </div>
                    </div>
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Discount Price</th>
                                <th>Deal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_ids[]" value="<?php echo (int)$product['id']; ?>">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></td>
                                    <td>KSh <?php echo number_format((float)($product['price'] ?? 0), 2); ?></td>
                                    <td>
                                        <div class="col-md-6">
                                            <input type="number" step="0.01" min="0" name="discount_price[<?php echo (int)$product['id']; ?>]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($product['discount_price'] ?? ''); ?>" placeholder="Optional">
                                        </div>
                                    </td>
                                    <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_deal[<?php echo (int)$product['id']; ?>]" <?php echo !empty($product['is_deal']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label">Yes</label>
                                            </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary save-row" data-row-id="<?php echo (int)$product['id']; ?>">
                                            Save
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include '../views/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                document.querySelectorAll('input[name="selected_ids[]"]').forEach(cb => {
                    cb.checked = e.target.checked;
                });
            });
        }

        document.querySelectorAll('.save-row').forEach(btn => {
            btn.addEventListener('click', () => {
                const rowId = btn.getAttribute('data-row-id');
                const form = btn.closest('form');
                if (!form || !rowId) return;
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'save_row';
                const rowInput = document.createElement('input');
                rowInput.type = 'hidden';
                rowInput.name = 'row_id';
                rowInput.value = rowId;
                form.appendChild(actionInput);
                form.appendChild(rowInput);
                form.submit();
            });
        });
    </script>
</body>
</html>
