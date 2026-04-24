<?php
// Use centralized session management
require_once '../config/environment.php';

require_once '../includes/auth.php';
require_once '../includes/db.php';

$auth = new Auth();
$auth->requireAdmin();

$db = new DBHelper();

$flash_success = '';
$flash_error = '';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    if (empty($posted_token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $posted_token)) {
        $flash_error = 'Invalid request. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create_coupon') {
            $code = strtoupper(trim((string)($_POST['code'] ?? '')));
            $description = trim((string)($_POST['description'] ?? ''));
            $discount_type = (string)($_POST['discount_type'] ?? 'percent');
            $discount_value = (float)($_POST['discount_value'] ?? 0);
            $min_order_amount = (float)($_POST['min_order_amount'] ?? 0);
            $max_uses_raw = trim((string)($_POST['max_uses'] ?? ''));
            $expires_at_raw = trim((string)($_POST['expires_at'] ?? ''));
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $max_uses = null;
            if ($max_uses_raw !== '') {
                $max_uses = (int)$max_uses_raw;
                if ($max_uses <= 0) {
                    $max_uses = null;
                }
            }

            $expires_at = null;
            if ($expires_at_raw !== '') {
                $ts = strtotime($expires_at_raw);
                if ($ts !== false) {
                    $expires_at = date('Y-m-d H:i:s', $ts);
                }
            }

            if ($code === '') {
                $flash_error = 'Coupon code is required.';
            } elseif (!preg_match('/^[A-Z0-9_-]{3,50}$/', $code)) {
                $flash_error = 'Coupon code must be 3-50 chars (A-Z, 0-9, _ or -).';
            } elseif (!in_array($discount_type, ['percent', 'fixed'], true)) {
                $flash_error = 'Invalid discount type.';
            } elseif ($discount_value <= 0) {
                $flash_error = 'Discount value must be greater than 0.';
            } elseif ($discount_type === 'percent' && $discount_value > 100) {
                $flash_error = 'Percent discount cannot exceed 100.';
            } else {
                $existing = $db->fetchOne("SELECT id FROM coupons WHERE code = ?", [$code]);
                if ($existing) {
                    $flash_error = 'Coupon code already exists.';
                } else {
                    $new_id = $db->insert('coupons', [
                        'code' => $code,
                        'description' => $description,
                        'discount_type' => $discount_type,
                        'discount_value' => $discount_value,
                        'min_order_amount' => $min_order_amount,
                        'max_uses' => $max_uses,
                        'used_count' => 0,
                        'expires_at' => $expires_at,
                        'is_active' => $is_active
                    ]);

                    if ($new_id) {
                        $flash_success = 'Coupon created.';
                    } else {
                        $flash_error = 'Failed to create coupon.';
                    }
                }
            }
        } elseif ($action === 'toggle_coupon') {
            $coupon_id = (int)($_POST['coupon_id'] ?? 0);
            $new_state = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
            if ($coupon_id > 0) {
                $ok = $db->update('coupons', ['is_active' => $new_state], "id = " . (int)$coupon_id);
                if ($ok) {
                    $flash_success = 'Coupon updated.';
                } else {
                    $flash_error = 'Failed to update coupon.';
                }
            }
        } elseif ($action === 'delete_coupon') {
            $coupon_id = (int)($_POST['coupon_id'] ?? 0);
            if ($coupon_id > 0) {
                $stmt = $db->query("DELETE FROM coupons WHERE id = ?", [$coupon_id]);
                if ($stmt) {
                    $flash_success = 'Coupon deleted.';
                } else {
                    $flash_error = 'Failed to delete coupon. It may already be in use.';
                }
            }
        }
    }
}

$coupons = $db->fetchAll("SELECT * FROM coupons ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Coupons - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../views/admin_header.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h3 mb-0">Coupons</h1>
        </div>

        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flash_success); ?></div>
        <?php endif; ?>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($flash_error); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Create Coupon</h5>
            </div>
            <div class="card-body">
                <form method="post" action="coupons.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="create_coupon">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. WELCOME10" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Optional">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="discount_type" class="form-select" required>
                                <option value="percent">Percent (%)</option>
                                <option value="fixed">Fixed (KSh)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Value</label>
                            <input type="number" step="0.01" min="0" name="discount_value" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Min Order Amount</label>
                            <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Uses</label>
                            <input type="number" step="1" min="1" name="max_uses" class="form-control" placeholder="Blank = unlimited">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expires At</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Create Coupon</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">All Coupons</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($coupons)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Discount</th>
                                    <th>Min</th>
                                    <th>Uses</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($coupons as $c): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($c['code'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($c['description'] ?? ''); ?></td>
                                        <td>
                                            <?php
                                                $dtype = $c['discount_type'] ?? 'percent';
                                                $dval = (float)($c['discount_value'] ?? 0);
                                                echo htmlspecialchars($dtype === 'fixed' ? ('KSh ' . number_format($dval, 2)) : (number_format($dval, 0) . '%'));
                                            ?>
                                        </td>
                                        <td>KSh <?php echo number_format((float)($c['min_order_amount'] ?? 0), 2); ?></td>
                                        <td>
                                            <?php
                                                $used = (int)($c['used_count'] ?? 0);
                                                $max = $c['max_uses'];
                                                echo htmlspecialchars($max === null ? ($used . ' / ') : ($used . ' / ' . (int)$max));
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['expires_at'] ?? ''); ?></td>
                                        <td>
                                            <?php if (!empty($c['is_active'])): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <form method="post" action="coupons.php" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <input type="hidden" name="action" value="toggle_coupon">
                                                <input type="hidden" name="coupon_id" value="<?php echo (int)$c['id']; ?>">
                                                <input type="hidden" name="is_active" value="<?php echo !empty($c['is_active']) ? 0 : 1; ?>">
                                                <button class="btn btn-sm btn-outline-primary" type="submit"><?php echo !empty($c['is_active']) ? 'Deactivate' : 'Activate'; ?></button>
                                            </form>
                                            <form method="post" action="coupons.php" class="d-inline" onsubmit="return confirm('Delete this coupon?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <input type="hidden" name="action" value="delete_coupon">
                                                <input type="hidden" name="coupon_id" value="<?php echo (int)$c['id']; ?>">
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
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
    </main>

    </div>
</div>

<?php include '../views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
