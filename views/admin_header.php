<?php
require_once __DIR__ . '/../includes/cart.php';
$cart = new Cart();
$item_count = $cart->getItemCount();

// Ensure session is started and user is authenticated
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/settings.php';
$auth = new Auth();

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$is_system_admin = $auth->isSystemAdmin();

$site_name = (string)getSetting('site_name', 'Merch Shop');
$site_logo = (string)getSetting('site_logo', 'assets/images/logo.png');
$custom_css = (string)getSetting('custom_css', '');

// Set default full_name if not set
if (!isset($_SESSION['full_name'])) {
    $_SESSION['full_name'] = $_SESSION['user_email'] ?? 'Admin User';
}

// Auto-detect environment for paths
$is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                (strpos($_SERVER['SERVER_NAME'], '192.168') !== false));

if ($is_localhost) {
    $base_path = '/Smart%20School%20Uniform%20Odering%20System';
    $admin_path = $base_path . '/admin';
} else {
    $base_path = '';
    $admin_path = '/admin';
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $admin_path; ?>/dashboard.php">
            <img src="<?php echo htmlspecialchars('../' . ltrim($site_logo, '/')); ?>" alt="Logo" height="45" style="object-fit: contain;">
            <span class="fw-bold ms-3" style="color: rgb(6, 25, 67);"><?php echo htmlspecialchars($site_name); ?> Admin</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_path; ?>/catalog.php">
                        <i class="fas fa-store"></i> View Store
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin User'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo $admin_path; ?>/profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo $base_path; ?>/dashboard.php">User Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo $base_path; ?>/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
        
        <style>
        body {
            overflow-x: hidden;
        }
        .main-content {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }
        .navbar {
            margin-bottom: 0 !important;
        }
        main.col-md-9,
        main.col-lg-10 {
            min-height: calc(100vh - 56px);
            padding-bottom: 2rem;
            margin-left: 0;
            width: 100%;
            transition: margin-left 0.25s ease, width 0.25s ease;
        }
        main.ms-sm-auto {
            margin-left: 0 !important;
        }
        body.sidebar-open main.col-md-9,
        body.sidebar-open main.col-lg-10 {
            margin-left: 240px;
            width: calc(100% - 240px);
        }
        body.sidebar-open main.ms-sm-auto {
            margin-left: 240px !important;
        }
        .sidebar {
            height: calc(100vh - 56px) !important;
            position: fixed !important;
            top: 56px !important;
            left: -240px;
            width: 240px;
            overflow-y: auto;
            transition: left 0.25s ease;
            z-index: 1045;
            box-shadow: 4px 0 16px rgba(0, 0, 0, 0.08);
        }
        body.sidebar-open .sidebar {
            left: 0;
        }
        .sidebar-hover-zone {
            position: fixed;
            top: 56px;
            left: 0;
            width: 14px;
            height: calc(100vh - 56px);
            z-index: 1040;
        }
        .sidebar .nav-link {
            color: #333 !important;
            font-weight: 500;
            padding: 12px 16px !important;
            border-radius: 6px;
            margin-bottom: 4px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            background-color: #e9ecef !important;
            color: #000 !important;
            transform: translateX(4px);
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd !important;
            color: #fff !important;
            font-weight: 600;
        }
        .sidebar .nav-link i {
            color: inherit !important;
        }
        </style>

        <?php if (trim($custom_css) !== ''): ?>
        <style>
        <?php echo $custom_css; ?>
        </style>
        <?php endif; ?>

<div class="sidebar-hover-zone" aria-hidden="true"></div>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'orders.php' || basename($_SERVER['PHP_SELF']) === 'order_details.php') && empty($_GET['view']) ? 'active' : ''; ?>" href="orders.php">
                            <i class="fas fa-shopping-bag me-2"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'orders.php' && ($_GET['view'] ?? '') === 'paid') ? 'active' : ''; ?>" href="orders.php?view=paid">
                            <i class="fas fa-receipt me-2"></i> Paid Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'orders.php' && ($_GET['view'] ?? '') === 'unpaid') ? 'active' : ''; ?>" href="orders.php?view=unpaid">
                            <i class="fas fa-hourglass-half me-2"></i> Unpaid Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'orders.php' && ($_GET['view'] ?? '') === 'collected') ? 'active' : ''; ?>" href="orders.php?view=collected">
                            <i class="fas fa-check me-2"></i> Collected
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'orders.php' && ($_GET['view'] ?? '') === 'uncollected') ? 'active' : ''; ?>" href="orders.php?view=uncollected">
                            <i class="fas fa-box-open me-2"></i> Uncollected
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'orders.php' && ($_GET['view'] ?? '') === 'cancelled') ? 'active' : ''; ?>" href="orders.php?view=cancelled">
                            <i class="fas fa-times me-2"></i> Cancelled
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?>" href="payments.php">
                            <i class="fas fa-credit-card me-2"></i> Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'inventory.php' ? 'active' : ''; ?>" href="inventory.php">
                            <i class="fas fa-boxes me-2"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'deals.php' ? 'active' : ''; ?>" href="deals.php">
                            <i class="fas fa-tags me-2"></i> Deals
                        </a>
                    </li>
                    <li class="nav-item">
                        <?php if ($is_system_admin): ?>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" href="users.php">
                            <i class="fas fa-users me-2"></i> Users
                        </a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'size_chart.php' ? 'active' : ''; ?>" href="size_chart.php">
                            <i class="fas fa-ruler-combined me-2"></i> Size Charts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reviews_questions.php' ? 'active' : ''; ?>" href="reviews_questions.php">
                            <i class="fas fa-star me-2"></i> Reviews & Questions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'coupons.php' ? 'active' : ''; ?>" href="coupons.php">
                            <i class="fas fa-ticket-alt me-2"></i> Coupons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'offers.php' ? 'active' : ''; ?>" href="offers.php">
                            <i class="fas fa-tags me-2"></i> Offers
                        </a>
                    </li>
                    <li class="nav-item">
                        <?php if ($is_system_admin): ?>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                            <i class="fas fa-sliders-h me-2"></i> Store Settings
                        </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </nav>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const hoverZone = document.querySelector('.sidebar-hover-zone');
            const sidebar = document.querySelector('.sidebar');

            if (!hoverZone || !sidebar) {
                return;
            }

            hoverZone.addEventListener('mouseenter', () => {
                body.classList.add('sidebar-open');
            });

            hoverZone.addEventListener('click', () => {
                body.classList.add('sidebar-open');
            });

            hoverZone.addEventListener('touchstart', () => {
                body.classList.add('sidebar-open');
            }, { passive: true });

            sidebar.addEventListener('mouseleave', () => {
                body.classList.remove('sidebar-open');
            });

            sidebar.querySelectorAll('a.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    body.classList.remove('sidebar-open');
                });
            });

            document.addEventListener('click', (event) => {
                if (!sidebar.contains(event.target) && !hoverZone.contains(event.target)) {
                    body.classList.remove('sidebar-open');
                }
            });
        });
        </script>
