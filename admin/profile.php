<?php
// Use centralized session management
require_once '../config/environment.php';

require_once '../includes/auth.php';
require_once '../includes/db.php';
$auth = new Auth();

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = new DBHelper();
$current_user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($full_name) || empty($email)) {
        $error = 'Full name and email are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        // Check if email is taken by another user
        $existing = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $current_user_id]);
        if ($existing) {
            $error = 'Email is already in use by another account';
        } else {
            $update_data = [
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone
            ];

            // Password change logic (optional)
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error = 'Current password is required to change password';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters';
                } else {
                    // Verify current password
                    $user = $db->fetchOne("SELECT password FROM users WHERE id = ?", [$current_user_id]);
                    if ($user && password_verify($current_password, $user['password'])) {
                        $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                    } else {
                        $error = 'Current password is incorrect';
                    }
                }
            }

            if (empty($error)) {
                if ($db->update('users', $update_data, "id = $current_user_id")) {
                    $message = 'Profile updated successfully';
                    // Update session email if changed
                    $_SESSION['email'] = $email;
                } else {
                    $error = 'Failed to update profile';
                }
            }
        }
    }
}

// Fetch current user data
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$current_user_id]);
if (!$user) {
    $_SESSION['error'] = 'User not found';
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/zetech-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../views/admin_header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>My Profile</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>

                            <hr>
                            <h6 class="mb-3">Change Password (Optional)</h6>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                <div class="form-text">Leave blank to keep current password</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
