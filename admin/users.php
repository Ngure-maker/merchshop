<?php
// Use centralized session management
require_once '../config/environment.php';

require_once '../includes/auth.php';
require_once '../includes/db.php';
$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// Check if user is admin
if (!$auth->isSystemAdmin()) {
    $_SESSION['error'] = 'You do not have permission to access the admin panel.';
    header('Location: ../dashboard.php');
    exit();
}

$db = new DBHelper();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_user') {
        $user_id = $_POST['user_id'];
        $data = [
            'full_name' => trim($_POST['full_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'user_type' => $_POST['user_type'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if ($db->update('users', $data, "id = $user_id")) {
            $_SESSION['success'] = 'User updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update user';
        }
    } elseif ($action === 'add_user') {
        $data = [
            'full_name' => trim($_POST['full_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'user_type' => $_POST['user_type'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if ($db->insert('users', $data)) {
            $_SESSION['success'] = 'User added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add user';
        }
    }
    
    header('Location: users.php');
    exit;
}

// Get users
$users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../views/admin_header.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">User Management</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus me-2"></i>Add User
                    </button>
                </div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">All Users</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Add User
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['user_type'] === 'admin' ? 'danger' : ($user['user_type'] === 'staff' ? 'warning' : 'primary'); ?>">
                                                    <?php echo ucfirst($user['user_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                        data-bs-target="#editUserModal" 
                                                        data-user='<?php echo json_encode($user); ?>'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        
                        <div class="mb-3">
                            <label for="user_type" class="form-label">User Type</label>
                            <select class="form-select" id="user_type" name="user_type" required>
                                <option value="parent">Parent</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        
                        <div class="mb-3">
                            <label for="edit_full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_user_type" class="form-label">User Type</label>
                            <select class="form-select" id="edit_user_type" name="user_type" required>
                                <option value="parent">Parent</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit user modal
        document.getElementById('editUserModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const user = JSON.parse(button.getAttribute('data-user'));
            
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_full_name').value = user.full_name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_phone').value = user.phone;
            document.getElementById('edit_user_type').value = user.user_type;
            document.getElementById('edit_is_active').checked = user.is_active;
        });
    </script>
    </main>
    </div>
</body>
</html>
