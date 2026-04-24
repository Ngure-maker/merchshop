<?php
// Force session start at the very beginning
require_once 'config/environment.php';

require_once 'includes/auth.php';
require_once 'includes/cart.php';
require_once 'includes/settings.php';
$require_db = true;
$auth = new Auth();

$site_name = (string)getSetting('site_name', 'Merch Shop');
$site_logo = (string)getSetting('site_logo', 'assets/images/logo.png');

if ($auth->isLoggedIn()) {
    // If user was attempting to checkout, send them to checkout
    if (isset($_SESSION['checkout_redirect']) && $_SESSION['checkout_redirect'] === true) {
        unset($_SESSION['checkout_redirect']);
        header('Location: checkout.php');
    } else {
        header('Location: dashboard.php');
    }

    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Save guest cart before login attempt
    $cart = new Cart();
    $cart->saveGuestCart();

    $result = $auth->login($email, $password);
    if ($result['success']) {
        // Merge guest cart into user cart after successful login
        $cart->mergeGuestCart();

        // Attach any guest orders created before login to this user
        if (isset($_SESSION['guest_order_ids']) && is_array($_SESSION['guest_order_ids']) && !empty($_SESSION['user_id'])) {
            require_once 'includes/db.php';
            require_once 'includes/payment.php';
            $db = new DBHelper();
            $payment = new Payment();
            $user_id = (int)$_SESSION['user_id'];
            $guest_order_ids = array_values(array_unique(array_map('intval', $_SESSION['guest_order_ids'])));

            foreach ($guest_order_ids as $guest_order_id) {
                if ($guest_order_id > 0) {
                    // Only attach if it's still a guest order
                    $db->query(
                        "UPDATE orders SET user_id = ? WHERE id = ? AND (user_id IS NULL OR user_id = 0)",
                        [$user_id, $guest_order_id]
                    );

                    // If the guest order was already paid, award points now that it has a user_id
                    $payment->awardPointsForPaidOrder($guest_order_id);
                }
            }
            unset($_SESSION['guest_order_ids']);
        }

        // Check if user is admin and redirect accordingly
        if ($auth->isAdmin()) {
            header('Location: admin/dashboard.php');
        } else {
            // Check if user was trying to checkout
            if (isset($_SESSION['checkout_redirect']) && $_SESSION['checkout_redirect'] === true) {
                unset($_SESSION['checkout_redirect']);
                header('Location: checkout.php');
            } else {
                header('Location: dashboard.php');
            }
        }
        exit;
    } else {
        $error = $result['message'];
    }
}

// Ensure CSRF token exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($site_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        :root {
            --secondary-teal: #00897B;
            --secondary-blue: #1976D2;
            --accent-purple: #7B1FA2;
            --accent-green: #388E3C;
            --neutral-gray: #546E7A;
            --light-bg: #FFF8E1;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(4, 30, 66, 0.25);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-sm-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <a href="index.php" class="text-decoration-none">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="<?php echo htmlspecialchars($site_name); ?>" height="55" style="object-fit: contain;">
                                    <h2 class="fw-bold text-primary mb-0"><?php echo htmlspecialchars($site_name); ?></h2>
                                </div>
                            </a>
                            <p class="text-muted">Sign in to your account</p>
                        </div>

                        <!-- Messages -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form method="POST" action="" id="loginForm" data-validate>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0" id="email" name="email" 
                                           placeholder="your@email.com" required 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                <div class="form-text">Enter your registered email address</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" id="password" name="password" 
                                           placeholder="Enter your password" required>
                                    <button type="button" class="input-group-text bg-light border-start-0 toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <a href="forgot_password.php" class="text-decoration-none">Forgot your password?</a>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                    <label class="form-check-label" for="remember_me">
                                        Remember me
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg py-2">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </div>
                        </form>

                        <!-- Divider -->
                        <div class="position-relative text-center mb-4">
                            <hr>
                            <span class="position-absolute top-50 start-50 translate-middle bg-light px-3 text-muted">
                                or sign in with
                            </span>
                        </div>

                        <!-- Social Login Options -->
                        <div class="social-login mb-4">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" onclick="handleGoogleSignIn()">
                                    <i class="fab fa-google me-2"></i>
                                    Continue with Google
                                </button>
                                <button type="button" class="btn btn-outline-primary d-flex align-items-center justify-content-center" onclick="handleEmailSignIn()">
                                    <i class="fas fa-envelope me-2"></i>
                                    Continue with Email
                                </button>
                            </div>
                        </div>

                        <!-- Registration Link -->
                        <div class="text-center">
                            <p class="mb-0">Don't have an account? 
                                <a href="register.php" class="text-decoration-none fw-semibold">Create one here</a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="row mt-4 text-center">
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-shield-alt text-primary me-2"></i>
                            <small class="text-muted">Secure Payments</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-shipping-fast text-primary me-2"></i>
                            <small class="text-muted">Fast Delivery</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-headset text-primary me-2"></i>
                            <small class="text-muted">24/7 Support</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.parentNode.querySelector('input');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Auto-focus on email field
    document.getElementById('email')?.focus();

    // Google Client ID (in production, this should be rendered by PHP)
    const GOOGLE_CLIENT_ID = '<?php echo defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : 'your-google-client-id'; ?>';

    // Social Login Functions
    function handleGoogleSignIn() {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connecting...';
        button.disabled = true;

        // Redirect to Google OAuth
        setTimeout(() => {
            const authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' + 
                'client_id=' + encodeURIComponent(GOOGLE_CLIENT_ID) + 
                '&redirect_uri=' + encodeURIComponent(window.location.origin.replace(/\/$/, '') + '/google_callback.php') + 
                '&response_type=code&scope=openid email profile&access_type=offline&prompt=consent';
            
            window.location.href = authUrl;
        }, 1000);
    }

    function handleEmailSignIn() {
        // Focus on email field for quick email login
        document.getElementById('email').focus();
        document.getElementById('email').scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Highlight the email field
        const emailField = document.getElementById('email');
        emailField.classList.add('border-primary', 'border-2');
        setTimeout(() => {
            emailField.classList.remove('border-primary', 'border-2');
        }, 2000);
    }

    function showNotification(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.card-body');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    </script>
</body>
</html>