<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        $db = new DBHelper();
        $user = $db->fetchOne("SELECT id, full_name FROM users WHERE email = ? AND is_active = 1", [$email]);
        
        if ($user) {
            // Generate reset token (in real implementation)
            $reset_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database (you'd need a password_resets table)
            // $db->insert('password_resets', [
            //     'email' => $email,
            //     'token' => $reset_token,
            //     'expires_at' => $expires_at
            // ]);
            
            // Send email (in real implementation)
            // sendPasswordResetEmail($email, $user['full_name'], $reset_token);
            
            $success = 'Password reset instructions have been sent to your email address.';
        } else {
            $error = 'No account found with that email address.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h2 class="card-title fw-bold text-primary">Reset Password</h2>
                            <p class="text-muted">Enter your email to receive reset instructions</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">Send Reset Instructions</button>
                            </div>
                            
                            <div class="text-center">
                                <a href="login.php" class="text-decoration-none">Back to Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>