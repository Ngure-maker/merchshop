<?php
// Global Language System for SmartSchool Uniforms

// Supported languages
$supported_languages = [
    'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇺🇸'],
    'sw' => ['name' => 'Swahili', 'native' => 'Kiswahili', 'flag' => '🇰🇪'],
    'fr' => ['name' => 'French', 'native' => 'Français', 'flag' => '🇫🇷'],
    'es' => ['name' => 'Spanish', 'native' => 'Español', 'flag' => '🇪🇸'],
    'de' => ['name' => 'German', 'native' => 'Deutsch', 'flag' => '🇩🇪'],
    'zh' => ['name' => 'Chinese', 'native' => '中文', 'flag' => '🇨🇳'],
    'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'flag' => '🇸🇦'],
    'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'flag' => '🇮🇳'],
    'pt' => ['name' => 'Portuguese', 'native' => 'Português', 'flag' => '🇵🇹'],
    'it' => ['name' => 'Italian', 'native' => 'Italiano', 'flag' => '🇮🇹'],
    'ja' => ['name' => 'Japanese', 'native' => '日本語', 'flag' => '🇯🇵'],
    'ru' => ['name' => 'Russian', 'native' => 'Русский', 'flag' => '🇷🇺']
];

// Handle language change from any page
if (isset($_POST['change_language']) && isset($_POST['language_code'])) {
    $lang_code = $_POST['language_code'];
    if (isset($supported_languages[$lang_code])) {
        $_SESSION['language'] = $lang_code;
        $_SESSION['language_changed'] = true;
    }
}

// Get current language (default to English)
$current_language = $_SESSION['language'] ?? 'en';

// Global translation function
function t($key, $lang = null) {
    global $current_language;
    
    if ($lang === null) {
        $lang = $current_language;
    }
    
    $translations = [
        'en' => [
            // Navigation
            'nav_home' => 'Home',
            'nav_categories' => 'Categories',
            'nav_shop_by_category' => 'Shop by Category',
            'nav_view_all_categories' => 'View All Categories',
            'nav_products' => 'Products',
            'nav_uniforms' => 'Uniforms',
            'nav_books' => 'Books',
            'nav_stationery' => 'Stationery',
            'nav_accessories' => 'Accessories',
            'nav_school_essentials' => 'School Essentials',
            'nav_about' => 'About',
            'nav_contact' => 'Contact',
            'nav_school_info' => 'School Info',
            'nav_uniform_guide' => 'Uniform Guide',
            'nav_size_calculator' => 'Size Calculator',
            'nav_educational_resources' => 'Educational Resources',
            'nav_student_resources' => 'Student Resources',
            'nav_teacher_resources' => 'Teacher Resources',
            'nav_parent_resources' => 'Parent Resources',
            'nav_mobile_apps' => 'Mobile Apps',
            'nav_mobile_applications' => 'Mobile Applications',
            'nav_download_app' => 'Download App',
            'nav_app_features' => 'App Features',
            'nav_mobile_support' => 'Mobile Support',
            'nav_quick_links' => 'Quick Links',
            'nav_emergency_contacts' => 'Emergency Contacts',
            'nav_sitemap' => 'Site Map',
            'nav_accessibility' => 'Accessibility',
            'nav_language_settings' => 'Language Settings',
            'nav_rss_feed' => 'RSS Feed',
            'nav_newsletter' => 'Newsletter',
            'nav_api_documentation' => 'API Documentation',
            'nav_developers' => 'Developers',
            'nav_my_account' => 'My Account',
            'nav_login' => 'Login',
            'nav_register' => 'Register',
            'nav_logout' => 'Logout',
            'nav_cart' => 'Cart',
            'nav_search_placeholder' => 'Search products...',
            
            // Common
            'welcome' => 'Welcome',
            'loading' => 'Loading...',
            'error' => 'Error',
            'success' => 'Success',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'view' => 'View',
            'add' => 'Add',
            'update' => 'Update',
            'submit' => 'Submit',
            'search' => 'Search',
            'filter' => 'Filter',
            'sort' => 'Sort',
            'price' => 'Price',
            'quantity' => 'Quantity',
            'total' => 'Total',
            'category' => 'Category',
            'product' => 'Product',
            'products' => 'Products',
            'order' => 'Order',
            'orders' => 'Orders',
            'account' => 'Account',
            'profile' => 'Profile',
            'settings' => 'Settings',
            'help' => 'Help',
            'support' => 'Support',
            'contact' => 'Contact',
            'about' => 'About',
            'home' => 'Home',
            'back' => 'Back',
            'next' => 'Next',
            'previous' => 'Previous',
            'close' => 'Close',
            'open' => 'Open',
            'yes' => 'Yes',
            'no' => 'No',
            'ok' => 'OK',
            'continue' => 'Continue',
            'finish' => 'Finish',
            'start' => 'Start',
            'stop' => 'Stop',
            'pause' => 'Pause',
            'play' => 'Play',
            'select' => 'Select',
            'choose' => 'Choose',
            'change' => 'Change',
            'remove' => 'Remove',
            'add_to_cart' => 'Add to Cart',
            'buy_now' => 'Buy Now',
            'checkout' => 'Checkout',
            'payment' => 'Payment',
            'shipping' => 'Shipping',
            'billing' => 'Billing',
            'address' => 'Address',
            'phone' => 'Phone',
            'email' => 'Email',
            'name' => 'Name',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
            'remember_me' => 'Remember Me',
            'forgot_password' => 'Forgot Password?',
            'create_account' => 'Create Account',
            'already_have_account' => 'Already have an account?',
            'login_here' => 'Login here',
            'register_here' => 'Register here',
            
            // Messages
            'language_changed' => 'Language Changed Successfully!',
            'language_changed_msg' => 'The page language has been changed and your preference has been saved.',
            'continue_browsing' => 'Continue Browsing',
            'go_to_homepage' => 'Go to Homepage',
            'item_added_to_cart' => 'Item added to cart!',
            'item_removed_from_cart' => 'Item removed from cart!',
            'cart_updated' => 'Cart updated!',
            'order_placed' => 'Order placed successfully!',
            'payment_successful' => 'Payment successful!',
            'login_successful' => 'Login successful!',
            'logout_successful' => 'Logout successful!',
            'account_created' => 'Account created successfully!',
            'profile_updated' => 'Profile updated successfully!',
            'password_changed' => 'Password changed successfully!',
            
            // Footer
            'footer_about' => 'About SmartSchool',
            'footer_about_desc' => 'Your trusted partner for quality school uniforms and educational supplies.',
            'footer_quick_links' => 'Quick Links',
            'footer_contact' => 'Contact Us',
            'footer_follow_us' => 'Follow Us',
            'footer_newsletter' => 'Newsletter',
            'footer_newsletter_desc' => 'Subscribe to get special offers and updates.',
            'footer_copyright' => '© 2023 SmartSchool Uniforms. All rights reserved.',
            'footer_privacy' => 'Privacy Policy',
            'footer_terms' => 'Terms of Service',
            'footer_cookie' => 'Cookie Policy',
            'footer_sitemap' => 'Sitemap'
        ],
        'sw' => [
            // Navigation
            'nav_home' => 'Nyumbani',
            'nav_categories' => 'Aina',
            'nav_shop_by_category' => 'Nunua kwa Aina',
            'nav_view_all_categories' => 'Ona Aina Zote',
            'nav_products' => 'Bidhaa',
            'nav_uniforms' => 'Sare',
            'nav_books' => 'Vitabu',
            'nav_stationery' => 'Vifaa vya Ofisi',
            'nav_accessories' => 'Vifaa',
            'nav_school_essentials' => 'Vihimu vya Shule',
            'nav_about' => 'Kuhusu',
            'nav_contact' => 'Wasiliana',
            'nav_school_info' => 'Maelezo ya Shule',
            'nav_uniform_guide' => 'Mwongozo wa Sare',
            'nav_size_calculator' => 'Kihesabu cha Ukubwa',
            'nav_educational_resources' => 'Rasilimali za Elimu',
            'nav_student_resources' => 'Rasilimali za Mwanafunzi',
            'nav_teacher_resources' => 'Rasilimali za Mwalimu',
            'nav_parent_resources' => 'Rasilimali za Mzazi',
            'nav_mobile_apps' => 'Programu za Simu',
            'nav_mobile_applications' => 'Maombi ya Simu',
            'nav_download_app' => 'Pakua Programu',
            'nav_app_features' => 'Vipengele vya Programu',
            'nav_mobile_support' => 'Usaidizi wa Simu',
            'nav_quick_links' => 'Viungo vya Haraka',
            'nav_emergency_contacts' => 'Mawasiliano ya Dharura',
            'nav_sitemap' => 'Ramani ya Tovuti',
            'nav_accessibility' => 'Uwezekano wa Kufikia',
            'nav_language_settings' => 'Mipangilio ya Lugha',
            'nav_rss_feed' => 'RSS Feed',
            'nav_newsletter' => 'Jarida',
            'nav_api_documentation' => 'Nyaraka za API',
            'nav_developers' => 'Wanaendelezaji',
            'nav_my_account' => 'Akaunti Yangu',
            'nav_login' => 'Ingia',
            'nav_register' => 'Jisajili',
            'nav_logout' => 'Toka',
            'nav_cart' => 'Gari',
            'nav_search_placeholder' => 'Tafuta bidhaa...',
            
            // Common
            'welcome' => 'Karibu',
            'loading' => 'Inapakia...',
            'error' => 'Kosa',
            'success' => 'Mafanikio',
            'save' => 'Hifadhi',
            'cancel' => 'Ghairi',
            'edit' => 'Hariri',
            'delete' => 'Futa',
            'view' => 'Ona',
            'add' => 'Ongeza',
            'update' => 'Sasisha',
            'submit' => 'Wasilisha',
            'search' => 'Tafuta',
            'filter' => 'Chuja',
            'sort' => 'Panga',
            'price' => 'Bei',
            'quantity' => 'Kiasi',
            'total' => 'Jumla',
            'category' => 'Aina',
            'product' => 'Bidhaa',
            'products' => 'Bidhaa',
            'order' => 'Oda',
            'orders' => 'Maagizo',
            'account' => 'Akaunti',
            'profile' => 'Wasifu',
            'settings' => 'Mipangilio',
            'help' => 'Msaada',
            'support' => 'Usaidizi',
            'contact' => 'Wasiliana',
            'about' => 'Kuhusu',
            'home' => 'Nyumbani',
            'back' => 'Rudi',
            'next' => 'Ifuatayo',
            'previous' => 'Iliyopita',
            'close' => 'Funga',
            'open' => 'Fungua',
            'yes' => 'Ndiyo',
            'no' => 'Hapana',
            'ok' => 'Sawa',
            'continue' => 'Endelea',
            'finish' => 'Maliza',
            'start' => 'Anza',
            'stop' => 'Simama',
            'pause' => 'Sitisha',
            'play' => 'Cheza',
            'select' => 'Chagua',
            'choose' => 'Chagua',
            'change' => 'Badilisha',
            'remove' => 'Ondoa',
            'add_to_cart' => 'Ongeza kwenye Gari',
            'buy_now' => 'Nunua Sasa',
            'checkout' => 'Angalia',
            'payment' => 'Malipo',
            'shipping' => 'Usafirishaji',
            'billing' => 'Ukadirishaji',
            'address' => 'Anwani',
            'phone' => 'Simu',
            'email' => 'Barua pepe',
            'name' => 'Jina',
            'first_name' => 'Jina la Kwanza',
            'last_name' => 'Jina la Mwisho',
            'password' => 'Nywila',
            'confirm_password' => 'Thibitisha Nywila',
            'remember_me' => 'Kumbuka',
            'forgot_password' => 'Umesahau Nywila?',
            'create_account' => 'Tengeneza Akaunti',
            'already_have_account' => 'Tayari una akaunti?',
            'login_here' => 'Ingia hapa',
            'register_here' => 'Jisajili hapa',
            
            // Messages
            'language_changed' => 'Lugha Imebadilishwa Kwa Mafanikio!',
            'language_changed_msg' => 'Lugha ya ukurasa imebadilishwa na mapendeleo yako imehifadhiwa.',
            'continue_browsing' => 'Endelea Kuvinjari',
            'go_to_homepage' => 'Nenda kwa Ukurasa wa Nyumbani',
            'item_added_to_cart' => 'Bidhaa imeongezwa kwenye gari!',
            'item_removed_from_cart' => 'Bidhaa imeondolewa kwenye gari!',
            'cart_updated' => 'Gari imesasishwa!',
            'order_placed' => 'Oda imewekwa kwa mafanikio!',
            'payment_successful' => 'Malipo yamefanikiwa!',
            'login_successful' => 'Kuingia kumefanikiwa!',
            'logout_successful' => 'Kutoka kumefanikiwa!',
            'account_created' => 'Akaunti imetengenezwa kwa mafanikio!',
            'profile_updated' => 'Wasifu umesasishwa kwa mafanikio!',
            'password_changed' => 'Nywila imebadilishwa kwa mafanikio!',
            
            // Footer
            'footer_about' => 'Kuhusu SmartSchool',
            'footer_about_desc' => 'Mshirika wako wa kuaminika kwa sare za shule za ubora na vifaa vya elimu.',
            'footer_quick_links' => 'Viungo vya Haraka',
            'footer_contact' => 'Wasiliana Nasi',
            'footer_follow_us' => 'Tufuate',
            'footer_newsletter' => 'Jarida',
            'footer_newsletter_desc' => 'Jiunge kupata ofa maalum na masasisho.',
            'footer_copyright' => '© 2023 SmartSchool Uniforms. Haki zote zimehifadhiwa.',
            'footer_privacy' => 'Sera ya Faragha',
            'footer_terms' => 'Masharti ya Huduma',
            'footer_cookie' => 'Sera ya Kuki',
            'footer_sitemap' => 'Ramani ya Tovuti'
        ]
    ];
    
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

// Get current language info
function getCurrentLanguage() {
    global $current_language, $supported_languages;
    return $supported_languages[$current_language] ?? $supported_languages['en'];
}

// Get language selector HTML
function getLanguageSelector() {
    global $current_language, $supported_languages;
    
    $html = '<div class="language-selector-dropdown">';
    $html .= '<button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">';
    $html .= getCurrentLanguage()['flag'] . ' ' . getCurrentLanguage()['name'];
    $html .= '</button>';
    $html .= '<ul class="dropdown-menu dropdown-menu-end">';
    
    foreach ($supported_languages as $lang_code => $lang_info) {
        $active = ($lang_code === $current_language) ? 'active' : '';
        $html .= '<li>';
        $html .= '<form method="POST" action="" style="display: inline;">';
        $html .= '<input type="hidden" name="change_language" value="1">';
        $html .= '<input type="hidden" name="language_code" value="' . $lang_code . '">';
        $html .= '<button type="submit" class="dropdown-item ' . $active . '">';
        $html .= $lang_info['flag'] . ' ' . $lang_info['name'];
        if ($lang_code === $current_language) {
            $html .= ' <i class="fas fa-check text-success"></i>';
        }
        $html .= '</button>';
        $html .= '</form>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</div>';
    
    return $html;
}

// Display language change success message if language was just changed
$language_changed = false;
$success_message = '';
if (isset($_SESSION['language_changed']) && $_SESSION['language_changed']) {
    $language_changed = true;
    $success_message = t('language_changed');
    unset($_SESSION['language_changed']);
}
?>
