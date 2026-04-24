<?php
// Auto-detect environment and load appropriate configuration
$is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

require_once __DIR__ . '/../config/environment.php';
require_once __DIR__ . '/../includes/settings.php';

$site_name = (string)getSetting('site_name', 'Merch Shop');
$site_logo = (string)getSetting('site_logo', 'assets/images/logo.png');

if ($is_localhost) {
    // Local development
    require_once 'includes/auth.php';
    require_once 'includes/db.php';
} else {
    // Live hosting
    require_once __DIR__ . '/../config/universal_database.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';
}

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: order_history.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        
        // Validate inputs
        if (empty($full_name) || empty($email) || empty($password)) {
            $error = 'Please fill in all required fields';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            $result = $auth->register($email, $password, $full_name, $phone);
            if ($result['success']) {
                $success = $result['message'];
                // Redirect after 2 seconds
                header('Refresh: 2; URL=order_history.php');
            } else {
                $error = $result['message'];
            }
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        $error = 'Registration failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo htmlspecialchars($site_name); ?></title>
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
            <div class="col-md-8 col-lg-6">
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
                            <p class="text-muted">Create your account</p>
                        </div>

                        <!-- Progress Steps -->
                        <div class="mb-4">
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 100%;"></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-primary fw-semibold">Account Details</small>
                                <small class="text-muted">Complete Registration</small>
                            </div>
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
                                <p class="mb-0 mt-2">Redirecting to your dashboard...</p>
                            </div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <form method="POST" action="" id="registrationForm" data-validate>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" id="full_name" name="full_name" 
                                               placeholder="John Doe" required 
                                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-phone text-muted"></i>
                                        </span>
                                        <input type="tel" class="form-control border-start-0" id="phone" name="phone" 
                                               placeholder="254712345678"
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                    <div class="form-text">For M-Pesa payments and notifications</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0" id="email" name="email" 
                                           placeholder="your@email.com" required 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                <div class="form-text">We'll send order confirmations to this email</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0" id="password" name="password" 
                                               placeholder="Create password" required minlength="6">
                                        <button type="button" class="input-group-text bg-light border-start-0 toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Minimum 6 characters</div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" 
                                               placeholder="Confirm password" required>
                                        <button type="button" class="input-group-text bg-light border-start-0 toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="terms.php" class="text-decoration-none">Terms of Service</a> 
                                        and <a href="privacy.php" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg py-2">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </form>

                        <!-- Divider -->
                        <div class="position-relative text-center mb-4">
                            <hr>
                            <span class="position-absolute top-50 start-50 translate-middle bg-light px-3 text-muted">
                                or sign up with
                            </span>
                        </div>

                        <!-- Social Sign Up Options -->
                        <div class="social-signup mb-4">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" onclick="handleGoogleSignUp()">
                                    <i class="fab fa-google me-2"></i>
                                    Sign up with Google
                                </button>
                                <button type="button" class="btn btn-outline-primary d-flex align-items-center justify-content-center" onclick="handleEmailSignUp()">
                                    <i class="fas fa-envelope me-2"></i>
                                    Sign up with Email
                                </button>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="position-relative text-center mb-4">
                            <hr>
                            <span class="position-absolute top-50 start-50 translate-middle bg-light px-3 text-muted">
                                Already have an account?
                            </span>
                        </div>

                        <!-- Login Link -->
                        <div class="text-center">
                            <a href="login.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In to Existing Account
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-primary text-white">
                            <div class="card-body p-4 text-center">
                                <h5 class="card-title mb-3">Why Register?</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                        <h6>Easy Ordering</h6>
                                        <small>Quick checkout and order tracking</small>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-mobile-alt fa-2x mb-2"></i>
                                        <h6>M-Pesa Payments</h6>
                                        <small>Secure mobile payments</small>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-bell fa-2x mb-2"></i>
                                        <h6>Order Updates</h6>
                                        <small>Real-time order status</small>
                                    </div>
                                </div>
                            </div>
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

    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        return strength;
    }
    
    function updatePasswordStrength() {
        const password = passwordInput.value;
        const strength = checkPasswordStrength(password);
        const strengthBar = document.getElementById('password-strength');
        
        if (strengthBar) {
            const width = (strength / 4) * 100;
            strengthBar.style.width = width + '%';
            
            let color = 'danger';
            let text = 'Weak';
            
            if (strength >= 3) {
                color = 'success';
                text = 'Strong';
            } else if (strength >= 2) {
                color = 'warning';
                text = 'Medium';
            }
            
            strengthBar.className = `progress-bar bg-${color}`;
            strengthBar.textContent = text;
        }
    }
    
    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword && password !== confirmPassword) {
            confirmPasswordInput.classList.add('is-invalid');
            confirmPasswordInput.classList.remove('is-valid');
        } else if (confirmPassword) {
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.add('is-valid');
        }
    }
    
    passwordInput?.addEventListener('input', updatePasswordStrength);
    passwordInput?.addEventListener('input', validatePasswordMatch);
    confirmPasswordInput?.addEventListener('input', validatePasswordMatch);

    // Auto-focus on name field
    document.getElementById('full_name')?.focus();

    // Google Client ID (in production, this should be rendered by PHP)
    const GOOGLE_CLIENT_ID = '<?php echo defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : 'your-google-client-id'; ?>';

    // Social Sign Up Functions
    function handleGoogleSignUp() {
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

    function handleEmailSignUp() {
        // Focus on email field for quick email signup
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