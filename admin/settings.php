<?php
// Use centralized session management
require_once '../config/environment.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/settings.php';

$auth = new Auth();
$auth->requireSystemAdmin();

$db = new DBHelper();

$flash_success = '';
$flash_error = '';

$site_name = '';
$support_phone = '';
$support_email = '';
$whatsapp_number = '';
$facebook_url = '';
$instagram_url = '';
$twitter_url = '';
$footer_text = '';
$custom_css = '';
$special_offers_title = '';
$special_offers_subtitle = '';
$pin_max_attempts = 3;
$pin_lockout_seconds = 600;

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    if (empty($posted_token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $posted_token)) {
        $flash_error = 'Invalid request. Please refresh and try again.';
    } else {
        $site_name = trim((string)($_POST['site_name'] ?? ''));
        $support_phone = trim((string)($_POST['support_phone'] ?? ''));
        $support_email = trim((string)($_POST['support_email'] ?? ''));
        $whatsapp_number = trim((string)($_POST['whatsapp_number'] ?? ''));
        $facebook_url = trim((string)($_POST['facebook_url'] ?? ''));
        $instagram_url = trim((string)($_POST['instagram_url'] ?? ''));
        $twitter_url = trim((string)($_POST['twitter_url'] ?? ''));
        $footer_text = trim((string)($_POST['footer_text'] ?? ''));
        $custom_css = (string)($_POST['custom_css'] ?? '');
        $special_offers_title = trim((string)($_POST['special_offers_title'] ?? ''));
        $special_offers_subtitle = trim((string)($_POST['special_offers_subtitle'] ?? ''));
        $pin_max_attempts = (int)($_POST['pin_max_attempts'] ?? 3);
        $pin_lockout_seconds = (int)($_POST['pin_lockout_seconds'] ?? 600);
        if ($pin_max_attempts < 1) {
            $pin_max_attempts = 1;
        }
        if ($pin_lockout_seconds < 60) {
            $pin_lockout_seconds = 60;
        }

        $shipping_fee = (float)($_POST['shipping_fee'] ?? 0);
        if ($shipping_fee < 0) {
            $shipping_fee = 0;
        }

        $ok = true;
        if ($site_name !== '') {
            $ok = $ok && setSetting('site_name', $site_name);
        }
        $ok = $ok && setSetting('support_phone', $support_phone);
        $ok = $ok && setSetting('support_email', $support_email);
        $ok = $ok && setSetting('whatsapp_number', $whatsapp_number);
        $ok = $ok && setSetting('facebook_url', $facebook_url);
        $ok = $ok && setSetting('instagram_url', $instagram_url);
        $ok = $ok && setSetting('twitter_url', $twitter_url);
        $ok = $ok && setSetting('footer_text', $footer_text);
        $ok = $ok && setSetting('special_offers_title', $special_offers_title);
        $ok = $ok && setSetting('special_offers_subtitle', $special_offers_subtitle);
        $ok = $ok && setSetting('custom_css', $custom_css);
        $ok = $ok && setSetting('shipping_fee', $shipping_fee);
        $ok = $ok && setSetting('pin_max_attempts', $pin_max_attempts);
        $ok = $ok && setSetting('pin_lockout_seconds', $pin_lockout_seconds);

        if (!empty($_FILES['site_logo']) && isset($_FILES['site_logo']['tmp_name']) && is_uploaded_file($_FILES['site_logo']['tmp_name'])) {
            $file = $_FILES['site_logo'];
            $max_size = 2 * 1024 * 1024;
            if (!empty($file['size']) && (int)$file['size'] > $max_size) {
                $flash_error = 'Logo is too large (max 2MB).';
                $ok = false;
            } else {
                $name = (string)($file['name'] ?? '');
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
                    $flash_error = 'Logo must be an image file (PNG/JPG/WEBP/GIF).';
                    $ok = false;
                } else {
                    $upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'site';
                    if (!is_dir($upload_dir)) {
                        @mkdir($upload_dir, 0755, true);
                    }
                    if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
                        $flash_error = 'Upload folder is not writable.';
                        $ok = false;
                    } else {
                        $filename = 'logo_' . time() . '.' . $ext;
                        $abs_path = $upload_dir . DIRECTORY_SEPARATOR . $filename;
                        $rel_path = 'uploads/site/' . $filename;
                        if (@move_uploaded_file($file['tmp_name'], $abs_path)) {
                            $ok = $ok && setSetting('site_logo', $rel_path);
                        } else {
                            $flash_error = 'Failed to upload logo.';
                            $ok = false;
                        }
                    }
                }
            }
        }

        if ($ok) {
            $flash_success = 'Settings updated.';
        } elseif ($flash_error === '') {
            $flash_error = 'Failed to update settings.';
        }
    }
}

$current_shipping_fee = (float)(getSetting('shipping_fee', 200));
$current_site_name = (string)(getSetting('site_name', 'Merch Shop'));
$current_support_phone = (string)(getSetting('support_phone', ''));
$current_support_email = (string)(getSetting('support_email', ''));
$current_whatsapp_number = (string)(getSetting('whatsapp_number', ''));
$current_facebook_url = (string)(getSetting('facebook_url', ''));
$current_instagram_url = (string)(getSetting('instagram_url', ''));
$current_twitter_url = (string)(getSetting('twitter_url', ''));
$current_footer_text = (string)(getSetting('footer_text', ''));
$current_special_offers_title = (string)(getSetting('special_offers_title', 'Special Offers & Deals'));
$current_special_offers_subtitle = (string)(getSetting('special_offers_subtitle', 'Amazing discounts and special promotions just for you!'));
$current_custom_css = (string)(getSetting('custom_css', ''));
$current_site_logo = (string)(getSetting('site_logo', 'assets/images/logo.png'));
$current_pin_max_attempts = (int)(getSetting('pin_max_attempts', 3));
$current_pin_lockout_seconds = (int)(getSetting('pin_lockout_seconds', 600));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Settings - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../views/admin_header.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Store Settings</h1>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flash_success); ?></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($flash_error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Branding</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($current_site_name); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo</label>
                            <input type="file" name="site_logo" class="form-control" accept="image/*">
                            <div class="form-text">Current: <?php echo htmlspecialchars($current_site_logo); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Contact & Social Links</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Support Phone</label>
                            <input type="text" name="support_phone" class="form-control" value="<?php echo htmlspecialchars($current_support_phone); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Support Email</label>
                            <input type="email" name="support_email" class="form-control" value="<?php echo htmlspecialchars($current_support_email); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" class="form-control" value="<?php echo htmlspecialchars($current_whatsapp_number); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Facebook URL</label>
                        <div class="col-md-6">
                            <label class="form-label">Footer Special Offers Title</label>
                            <input type="text" name="special_offers_title" class="form-control" value="<?php echo htmlspecialchars($current_special_offers_title); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Footer Special Offers Subtitle</label>
                            <input type="text" name="special_offers_subtitle" class="form-control" value="<?php echo htmlspecialchars($current_special_offers_subtitle); ?>">
                        </div>
                            <input type="url" name="facebook_url" class="form-control" value="<?php echo htmlspecialchars($current_facebook_url); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="<?php echo htmlspecialchars($current_instagram_url); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Twitter/X URL</label>
                            <input type="url" name="twitter_url" class="form-control" value="<?php echo htmlspecialchars($current_twitter_url); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Store Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment PIN Max Attempts</label>
                            <input type="number" min="1" name="pin_max_attempts" class="form-control" value="<?php echo htmlspecialchars((string)$current_pin_max_attempts); ?>">
                            <div class="form-text">Number of allowed PIN attempts before lockout.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment PIN Lockout (seconds)</label>
                            <input type="number" min="60" name="pin_lockout_seconds" class="form-control" value="<?php echo htmlspecialchars((string)$current_pin_lockout_seconds); ?>">
                            <div class="form-text">Minimum 60 seconds.</div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Pay on Delivery Shipping Fee (KSh)</label>
                            <input type="number" step="0.01" min="0" name="shipping_fee" class="form-control" value="<?php echo htmlspecialchars((string)$current_shipping_fee); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Footer Text</label>
                            <input type="text" name="footer_text" class="form-control" value="<?php echo htmlspecialchars($current_footer_text); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Custom CSS</h5>
                </div>
                <div class="card-body">
                    <textarea name="custom_css" class="form-control" rows="8" spellcheck="false"><?php echo htmlspecialchars($current_custom_css); ?></textarea>
                    <div class="form-text">This CSS is injected into the website and admin pages.</div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Settings
                </button>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
