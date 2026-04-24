<?php
// CRITICAL: Suppress ALL output before redirects
@error_reporting(E_ALL);
@ini_set('display_errors', 0);
@ini_set('log_errors', 1);

// Use centralized session management
require_once __DIR__ . '/../config/environment.php';

// Include language system for t() function
require_once __DIR__ . '/../includes/language.php';
require_once __DIR__ . '/../includes/settings.php';

$auth = null;
$db = null;
$cart = null;
$item_count = 0;
$categories = [];
$profile_image = null;

try {
    require_once __DIR__ . '/../includes/auth.php';
    $auth = new Auth();
    
    // Try to get categories from database
    require_once __DIR__ . '/../includes/db.php';
    $db = new DBHelper();
    
    if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])) {
        $profile_image = $_SESSION['profile_image'];
    } elseif ($db && isset($_SESSION['user_id'])) {
        try {
            $u = $db->fetchOne("SELECT profile_image FROM users WHERE id = ?", [(int)$_SESSION['user_id']]);
            if (!empty($u) && !empty($u['profile_image'])) {
                $profile_image = $u['profile_image'];
            }
        } catch (Exception $e) {
            // ignore
        }
    }
    
    // Load cart for all users (logged in and guests)
    require_once __DIR__ . '/../includes/cart.php';
    $cart = new Cart();
    $item_count = $cart->getItemCount();
    
    // Check if database connection worked
    if ($db) {
        $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
        if (empty($categories)) {
            // Database connected but no categories - use fallback
            $categories = [
                ['id' => 1, 'name' => 'Uniforms'],
                ['id' => 2, 'name' => 'Shoes'],
                ['id' => 3, 'name' => 'Bags'],
                ['id' => 4, 'name' => 'Books'],
                ['id' => 5, 'name' => 'Stationery']
            ];
        }
    } else {
        // Database failed - use fallback
        $categories = [
            ['id' => 1, 'name' => 'Uniforms'],
            ['id' => 2, 'name' => 'Shoes'],
            ['id' => 3, 'name' => 'Bags'],
            ['id' => 4, 'name' => 'Books'],
            ['id' => 5, 'name' => 'Stationery']
        ];
    }
    
} catch (Exception $e) {
    @error_log("Header error: " . $e->getMessage());
    
    // Always provide fallback categories
    $categories = [
        ['id' => 1, 'name' => 'Uniforms'],
        ['id' => 2, 'name' => 'Shoes'],
        ['id' => 3, 'name' => 'Bags'],
        ['id' => 4, 'name' => 'Books'],
        ['id' => 5, 'name' => 'Stationery']
    ];
}

$site_name = (string)getSetting('site_name', 'Merch Shop');
$site_logo = (string)getSetting('site_logo', 'assets/images/logo.png');
$custom_css = (string)getSetting('custom_css', '');
$zetech_contacts = [
    ['label' => '+254 719 034 500', 'icon' => 'fas fa-phone', 'url' => 'tel:+254719034500'],
    ['label' => '+254 706 622 557', 'icon' => 'fab fa-whatsapp', 'url' => 'https://wa.me/254706622557'],
    ['label' => 'info@zetech.ac.ke', 'icon' => 'fas fa-envelope', 'url' => 'mailto:info@zetech.ac.ke']
];
$zetech_social = [
    ['label' => 'Facebook', 'icon' => 'fab fa-facebook-f', 'url' => 'https://www.facebook.com/ZetechUniv'],
    [
        'label' => 'X (Twitter)',
        'icon' => 'custom-x',
        'url' => 'https://twitter.com/ZetechUni',
        'svg' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M18.244 2H21l-6.431 7.35L22.5 22h-6.34l-4.97-6.503L5.416 22H2.66l6.88-7.868L1.5 2h6.5l4.49 5.882L18.244 2zm-1.11 18h1.757L7.86 4H6.04l11.094 16z"/></svg>'
    ],
    ['label' => 'Instagram', 'icon' => 'fab fa-instagram', 'url' => 'https://www.instagram.com/zetechuniversity/'],
    ['label' => 'TikTok', 'icon' => 'fab fa-tiktok', 'url' => 'https://www.tiktok.com/@zetechuniversity']
];
?>

<?php if (trim($custom_css) !== ''): ?>
<style>
<?php echo $custom_css; ?>
</style>
<?php endif; ?>

<style>
    .top-social-bar {
        background: #0b1d3a;
        color: #e2e8f0;
        font-size: 12px;
    }
    .top-social-bar a {
        color: #e2e8f0;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 8px;
        border-radius: 999px;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .top-social-bar a:hover {
        background: rgba(255,255,255,0.12);
        color: #ffffff;
    }
    .top-social-bar .contact-wrap,
    .top-social-bar .social-wrap {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .top-social-bar .contact-wrap {
        justify-content: flex-start;
    }
    .top-social-bar .social-wrap {
        justify-content: flex-end;
    }
    .top-social-bar .social-wrap .social-x {
        width: 14px;
        height: 14px;
        display: inline-block;
    }
@media (min-width: 992px) {
    .navbar .navbar-collapse {
        flex-wrap: nowrap;
    }
    .navbar .navbar-nav {
        flex-wrap: nowrap;
    }
}
</style>

<div class="top-social-bar">
    <div class="container py-1 d-flex justify-content-between align-items-center">
        <div class="contact-wrap">
            <?php foreach ($zetech_contacts as $contact): ?>
                <a href="<?php echo htmlspecialchars($contact['url']); ?>">
                    <i class="<?php echo htmlspecialchars($contact['icon']); ?>"></i>
                    <span><?php echo htmlspecialchars($contact['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="social-wrap">
            <?php foreach ($zetech_social as $social): ?>
                <a href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank" rel="noopener" aria-label="<?php echo htmlspecialchars($social['label']); ?>">
                    <?php if (!empty($social['svg'])): ?>
                        <span class="social-x"><?php echo $social['svg']; ?></span>
                    <?php else: ?>
                        <i class="<?php echo htmlspecialchars($social['icon']); ?>"></i>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="<?php echo htmlspecialchars($site_name); ?>" height="55" style="object-fit: contain;">
            <span class="fw-bold ms-3" style="color: rgb(28, 29, 60);"><?php echo htmlspecialchars($site_name); ?></span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse align-items-center" id="navbarNav">
            <ul class="navbar-nav me-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><?php echo t('nav_home'); ?></a>
                </li>
                
                <!-- Categories Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <?php echo t('nav_categories'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header"><?php echo t('nav_shop_by_category'); ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a class="dropdown-item" href="catalog.php?category=<?php echo $category['id']; ?>">
                                    <i class="fas fa-<?php echo $category['name'] == 'Uniforms' ? 'tshirt' : ($category['name'] == 'Books' ? 'book' : 'pen'); ?> me-2"></i>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-primary" href="catalog.php"><?php echo t('nav_view_all_categories'); ?></a></li>
                    </ul>
                </li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="order_history.php">Order History</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- Search Bar -->
            <div class="d-flex me-3 flex-shrink-0">
                <form class="d-flex flex-nowrap" action="catalog.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="<?php echo t('nav_search_placeholder'); ?>" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link position-relative" href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <?php echo t('nav_cart'); ?>
                        <?php if ($item_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $item_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?php if (!empty($profile_image)): ?>
                                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" style="width: 26px; height: 26px; object-fit: cover; border-radius: 50%; margin-right: 6px;">
                            <?php else: ?>
                                <i class="fas fa-user me-1"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="dashboard.php?tab=personal_information"><i class="fas fa-id-badge me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if ($_SESSION['user_type'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (!defined('CHAT_WIDGET_LOADED')): ?>
<?php define('CHAT_WIDGET_LOADED', true); ?>
<script>
    window.ChatWidgetConfig = {
        endpoint: 'https://approve-consultants-positions-bicycle.trycloudflare.com',
        client: 'abc123'
    };
</script>
<script
  src="https://approve-consultants-positions-bicycle.trycloudflare.com/static/chat/js/widget_embed.js?v=20260407"
  data-endpoint="https://approve-consultants-positions-bicycle.trycloudflare.com"
  data-client="abc123"
  defer>
</script>
<?php endif; ?>

<style>
    .chat-fallback-btn {
        position: fixed;
        right: 18px;
        bottom: 18px;
        z-index: 9999;
        background: #0b1d3a;
        color: #ffffff;
        border: none;
        border-radius: 999px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .chat-fallback-btn:hover {
        background: #102a56;
    }
    .chat-fallback-hidden {
        display: none !important;
    }
</style>

<button type="button" class="chat-fallback-btn" id="chatFallbackBtn">
    <i class="fas fa-comment-dots"></i>
    Message Us
</button>

<script>
    (function() {
        var btn = document.getElementById('chatFallbackBtn');
        if (!btn) return;

        function tryOpenWidget() {
            // Try common widget launchers
            var launcher =
                document.querySelector('.chat-widget-toggle') ||
                document.querySelector('.chat-widget-button') ||
                document.querySelector('[data-chat-widget-button]') ||
                document.querySelector('iframe[src*="approve-consultants-positions-bicycle.trycloudflare.com"]') ||
                document.querySelector('iframe[src*="trycloudflare.com"]');

            if (launcher) {
                // If it's a button/div, click it. If it's an iframe, just focus user attention.
                if (launcher.click) launcher.click();
                return true;
            }
            return false;
        }

        btn.addEventListener('click', function() {
            if (!tryOpenWidget()) {
                window.open('https://approve-consultants-positions-bicycle.trycloudflare.com', '_blank');
            }
        });

        // Hide fallback once the widget renders
        var attempts = 0;
        var timer = setInterval(function() {
            attempts++;
            if (tryOpenWidget()) {
                btn.classList.add('chat-fallback-hidden');
                clearInterval(timer);
            }
            if (attempts > 30) {
                clearInterval(timer);
            }
        }, 1000);
    })();
</script>

<?php if (isset($language_changed) && $language_changed): ?>
<div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;" role="alert">
    <h5><i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?></h5>
    <p><?php echo t('language_changed_msg'); ?></p>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    <div class="mt-3">
        <a href="index.php" class="btn btn-primary btn-sm me-2"><?php echo t('go_to_homepage'); ?></a>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="alert"><?php echo t('continue_browsing'); ?></button>
    </div>
</div>
<?php endif; ?>
