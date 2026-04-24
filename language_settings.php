<?php
require_once 'config/environment.php'; // Replaced session_start()
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/cart.php';
require_once 'includes/language.php';

$auth = new Auth();
$db = new DBHelper();
$cart = new Cart();
$item_count = $cart->getItemCount();

// Get categories for dropdown menu
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

// Display language change success message if language was just changed
$language_changed = false;
$success_message = '';
if (isset($_SESSION['language_changed']) && $_SESSION['language_changed']) {
    $language_changed = true;
    $success_message = t('language_changed');
    unset($_SESSION['language_changed']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('page_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-amber: #FF6B35;
            --primary-dark: #E85A2C;
            --secondary-teal: #00897B;
            --secondary-blue: #1976D2;
            --accent-purple: #7B1FA2;
            --accent-green: #388E3C;
            --neutral-gray: #546E7A;
            --light-bg: #FFF8E1;
        }
        
        .language-header {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .language-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .language-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .language-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .language-selector {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .language-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .language-option {
            background: var(--light-bg);
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .language-option:hover {
            border-color: var(--accent-purple);
            background: white;
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .language-option.active {
            border-color: var(--accent-purple);
            background: var(--accent-purple);
            color: white;
        }
        
        .language-flag {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .language-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .language-native {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .current-language {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .translation-status {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .progress-item {
            margin-bottom: 1.5rem;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
        }
        
        .language-features {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .regional-settings {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .currency-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .currency-option {
            background: var(--light-bg);
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .currency-option:hover {
            border-color: var(--accent-purple);
            background: white;
        }
        
        .currency-option.active {
            border-color: var(--accent-purple);
            background: var(--accent-purple);
            color: white;
        }
        
        .date-format-options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .format-option {
            background: var(--light-bg);
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .format-option:hover {
            border-color: var(--accent-purple);
            background: white;
        }
        
        .format-option.active {
            border-color: var(--accent-purple);
            background: var(--accent-purple);
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
        }
        
        .help-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .help-item {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .help-item h6 {
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
        
        .contribution-section {
            background: linear-gradient(135deg, var(--primary-amber), var(--primary-dark));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="language-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-language me-3"></i><?php echo t('header_title'); ?></h1>
            <p class="lead mb-0"><?php echo t('header_subtitle'); ?></p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <?php if ($language_changed): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <h5><i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?></h5>
                    <p><?php echo t('language_changed_msg'); ?></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-primary me-2"><?php echo t('go_to_homepage'); ?></a>
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="alert"><?php echo t('continue_browsing'); ?></button>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="current-language">
                    <h3 class="mb-4"><i class="fas fa-globe me-2"></i><?php echo t('current_settings'); ?></h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6><?php echo t('display_language'); ?></h6>
                            <p class="mb-3"><strong><?php echo $supported_languages[$current_language]['name']; ?> (<?php echo $supported_languages[$current_language]['native']; ?>)</strong></p>
                            <form method="POST" action="">
                                <input type="hidden" name="change_language" value="1">
                                <input type="hidden" name="language_code" value="">
                                <button type="button" class="btn btn-light" onclick="showLanguageSelector()">
                                    <i class="fas fa-edit me-2"></i><?php echo t('change_language'); ?>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h6><?php echo t('content_language'); ?></h6>
                            <p class="mb-3"><strong><?php echo $supported_languages[$current_language]['name']; ?></strong></p>
                            <button class="btn btn-light" onclick="changeContentLanguage()">
                                <i class="fas fa-edit me-2"></i><?php echo t('change_content'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="language-selector">
                    <h3 class="mb-4"><?php echo t('choose_language'); ?></h3>
                    
                    <div class="language-grid">
                        <?php foreach ($supported_languages as $lang_code => $lang_info): ?>
                        <div class="language-option <?php echo ($lang_code === $current_language) ? 'active' : ''; ?>" 
                             onclick="changeLanguage('<?php echo $lang_code; ?>')">
                            <div class="language-flag"><?php echo $lang_info['flag']; ?></div>
                            <div class="language-name"><?php echo $lang_info['name']; ?></div>
                            <div class="language-native"><?php echo $lang_info['native']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i><?php echo t('language_info'); ?></h6>
                    </div>
                </div>
                
                <div class="translation-status">
                    <h3 class="mb-4">Translation Progress</h3>
                    
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>English</span>
                            <span>100%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>Swahili</span>
                            <span>95%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 95%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>French</span>
                            <span>85%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 85%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>Spanish</span>
                            <span>80%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 80%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>German</span>
                            <span>75%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 75%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>Other Languages</span>
                            <span>60%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="regional-settings">
                    <h3 class="mb-4">Regional Settings</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Currency</h5>
                            <div class="currency-selector">
                                <div class="currency-option active" onclick="selectCurrency(this, 'KES')">
                                    <div class="fw-bold">KES</div>
                                    <small>Kenyan Shilling</small>
                                </div>
                                <div class="currency-option" onclick="selectCurrency(this, 'USD')">
                                    <div class="fw-bold">USD</div>
                                    <small>US Dollar</small>
                                </div>
                                <div class="currency-option" onclick="selectCurrency(this, 'EUR')">
                                    <div class="fw-bold">EUR</div>
                                    <small>Euro</small>
                                </div>
                                <div class="currency-option" onclick="selectCurrency(this, 'GBP')">
                                    <div class="fw-bold">GBP</div>
                                    <small>British Pound</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Date Format</h5>
                            <div class="date-format-options">
                                <div class="format-option active" onclick="selectDateFormat(this, 'MM/DD/YYYY')">
                                    MM/DD/YYYY
                                </div>
                                <div class="format-option" onclick="selectDateFormat(this, 'DD/MM/YYYY')">
                                    DD/MM/YYYY
                                </div>
                                <div class="format-option" onclick="selectDateFormat(this, 'YYYY-MM-DD')">
                                    YYYY-MM-DD
                                </div>
                                <div class="format-option" onclick="selectDateFormat(this, 'DD-MMM-YYYY')">
                                    DD-MMM-YYYY
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5 class="mb-3">Time Format</h5>
                            <div class="date-format-options">
                                <div class="format-option active" onclick="selectTimeFormat(this, '12h')">
                                    12-hour (AM/PM)
                                </div>
                                <div class="format-option" onclick="selectTimeFormat(this, '24h')">
                                    24-hour
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Number Format</h5>
                            <div class="date-format-options">
                                <div class="format-option active" onclick="selectNumberFormat(this, '1,234.56')">
                                    1,234.56
                                </div>
                                <div class="format-option" onclick="selectNumberFormat(this, '1.234,56')">
                                    1.234,56
                                </div>
                                <div class="format-option" onclick="selectNumberFormat(this, '1 234,56')">
                                    1 234,56
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="language-features">
                    <h3 class="mb-4">Language Features</h3>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div>
                            <h6>Automatic Detection</h6>
                            <p class="mb-0">We automatically detect your browser language and suggest the best match.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-save"></i>
                        </div>
                        <div>
                            <h6>Remember Preferences</h6>
                            <p class="mb-0">Your language choice is saved and applied automatically on future visits.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div>
                            <h6>Mobile Optimized</h6>
                            <p class="mb-0">All languages are fully optimized for mobile devices and tablets.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <h6>Localized Search</h6>
                            <p class="mb-0">Search functionality works in all supported languages with proper results.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h6>Email Localization</h6>
                            <p class="mb-0">Order confirmations and notifications are sent in your preferred language.</p>
                        </div>
                    </div>
                </div>
                
                <div class="contribution-section">
                    <h3 class="mb-4"><i class="fas fa-hands-helping me-2"></i>Help Us Improve Translations</h3>
                    
                    <p>We're always working to improve our translations. If you notice any errors or have suggestions for better translations, we'd love to hear from you!</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <button class="btn btn-light btn-lg w-100" onclick="reportTranslationIssue()">
                                <i class="fas fa-bug me-2"></i>Report Translation Issue
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-light btn-lg w-100" onclick="suggestTranslation()">
                                <i class="fas fa-lightbulb me-2"></i>Suggest Better Translation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="language-section">
                    <h4 class="mb-3">Language Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">12</div>
                            <h6>Supported Languages</h6>
                            <p class="small text-muted mb-0">And growing</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">95%</div>
                            <h6>Translation Coverage</h6>
                            <p class="small text-muted mb-0">Average completion</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <h6>Translation Updates</h6>
                            <p class="small text-muted mb-0">Continuous improvement</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">100%</div>
                            <h6>Professional Quality</h6>
                            <p class="small text-muted mb-0">Native translators</p>
                        </div>
                    </div>
                </div>
                
                <div class="language-section">
                    <h4 class="mb-3">Quick Language Tips</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb me-2"></i>Pro Tip</h6>
                        <p class="small mb-0">You can switch languages anytime using the language selector in the footer.</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-mobile-alt me-2"></i>Mobile Friendly</h6>
                        <p class="small mb-0">All languages work perfectly on mobile devices with optimized layouts.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-clock me-2"></i>Loading Time</h6>
                        <p class="small mb-0">Language changes are instant - no page reload required!</p>
                    </div>
                </div>
                
                <div class="help-section">
                    <h4 class="mb-3">Help & Support</h4>
                    
                    <div class="help-item">
                        <h6><i class="fas fa-question-circle me-2"></i>How do I change language?</h6>
                        <p class="small mb-0">Click on any language option above or use the language selector in the footer.</p>
                    </div>
                    
                    <div class="help-item">
                        <h6><i class="fas fa-sync me-2"></i>Can I switch back to English?</h6>
                        <p class="small mb-0">Yes, you can switch between any languages at any time.</p>
                    </div>
                    
                    <div class="help-item">
                        <h6><i class="fas fa-globe me-2"></i>Will my language be remembered?</h6>
                        <p class="small mb-0">Yes, your preference is saved and applied automatically on future visits.</p>
                    </div>
                    
                    <div class="help-item">
                        <h6><i class="fas fa-plus me-2"></i>Request new language?</h6>
                        <p class="small mb-0">Contact us to request support for additional languages.</p>
                    </div>
                </div>
                
                <div class="language-section">
                    <h4 class="mb-3">Popular Languages</h4>
                    
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users me-2"></i>
                                <strong>English</strong>
                                <small class="text-muted d-block">45% of users</small>
                            </div>
                            <span class="badge bg-primary">Most Popular</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users me-2"></i>
                                <strong>Swahili</strong>
                                <small class="text-muted d-block">30% of users</small>
                            </div>
                            <span class="badge bg-success">Growing Fast</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users me-2"></i>
                                <strong>French</strong>
                                <small class="text-muted d-block">10% of users</small>
                            </div>
                            <span class="badge bg-info">Steady</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users me-2"></i>
                                <strong>Spanish</strong>
                                <small class="text-muted d-block">8% of users</small>
                            </div>
                            <span class="badge bg-warning">Emerging</span>
                        </div>
                    </div>
                </div>
                
                <div class="language-section">
                    <h4 class="mb-3">Contact Language Support</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-envelope me-2"></i>Email Support</h6>
                        <p class="small mb-0">languages@smartschool.co.ke</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-phone me-2"></i>Phone Support</h6>
                        <p class="small mb-0">+254 700 777 888</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-comments me-2"></i>Live Chat</h6>
                        <p class="small mb-0">Available in English and Swahili</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeLanguage(langCode) {
            // Create form and submit to change language
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const input1 = document.createElement('input');
            input1.type = 'hidden';
            input1.name = 'change_language';
            input1.value = '1';
            
            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = 'language_code';
            input2.value = langCode;
            
            form.appendChild(input1);
            form.appendChild(input2);
            document.body.appendChild(form);
            form.submit();
        }
        
        function showLanguageSelector() {
            // Scroll to language selector
            document.querySelector('.language-selector').scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
        }
        
        function selectCurrency(element, currency) {
            // Remove active class from all options
            document.querySelectorAll('.currency-option').forEach(opt => {
                opt.classList.remove('active');
            });
            
            // Add active class to selected option
            element.classList.add('active');
            
            alert(`Currency changed to ${currency}!\n\nPrices will be displayed in your selected currency.`);
        }
        
        function selectDateFormat(element, format) {
            // Remove active class from all options
            document.querySelectorAll('.format-option').forEach(opt => {
                opt.classList.remove('active');
            });
            
            // Add active class to selected option
            element.classList.add('active');
            
            alert(`Date format changed to ${format}!\n\nThis will affect how dates are displayed throughout the site.`);
        }
        
        function selectTimeFormat(element, format) {
            // Remove active class from all options
            document.querySelectorAll('.format-option').forEach(opt => {
                opt.classList.remove('active');
            });
            
            // Add active class to selected option
            element.classList.add('active');
            
            alert(`Time format changed to ${format}!\n\nThis will affect how times are displayed.`);
        }
        
        function selectNumberFormat(element, format) {
            // Remove active class from all options
            document.querySelectorAll('.format-option').forEach(opt => {
                opt.classList.remove('active');
            });
            
            // Add active class to selected option
            element.classList.add('active');
            
            alert(`Number format changed to ${format}!\n\nThis will affect how numbers and prices are displayed.`);
        }
        
        function changeContentLanguage() {
            alert('Content language determines the language of product descriptions and educational content. This can be different from your interface language.');
        }
        
        function reportTranslationIssue() {
            alert('To report a translation issue:\n\n1. Note the page and text with the issue\n2. Describe the problem\n3. Suggest the correct translation if possible\n4. Email: languages@smartschool.co.ke\n\nThank you for helping us improve!');
        }
        
        function suggestTranslation() {
            alert('To suggest better translations:\n\n1. Provide the original text and current translation\n2. Suggest your improved translation\n3. Explain why it\'s better\n4. Email: languages@smartschool.co.ke\n\nWe appreciate your contribution!');
        }
        
        // Auto-hide success message after 10 seconds
        setTimeout(function() {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 10000);
    </script>
</body>
</html>
