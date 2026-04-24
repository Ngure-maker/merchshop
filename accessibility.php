<?php
require_once 'config/environment.php'; // Replaced session_start()
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/cart.php';

$auth = new Auth();
$db = new DBHelper();
$cart = new Cart();
$item_count = $cart->getItemCount();

// Get categories for dropdown menu
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessibility - Merch Shop</title>
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
        
        .accessibility-header {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .accessibility-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .accessibility-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .accessibility-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .feature-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--accent-purple);
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .feature-title {
            flex-grow: 1;
        }
        
        .feature-title h4 {
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
        
        .feature-title p {
            color: var(--neutral-gray);
            margin: 0;
            font-size: 1rem;
        }
        
        .accessibility-tools {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .tool-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .tool-card {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .tool-card:hover {
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .tool-icon {
            width: 60px;
            height: 60px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .tool-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .tool-description {
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }
        
        .compliance-section {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .compliance-item {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .compliance-item h6 {
            margin-bottom: 0.5rem;
        }
        
        .keyboard-shortcuts {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .shortcut-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .shortcut-table th {
            background: var(--accent-purple);
            color: white;
            padding: 1rem;
            text-align: left;
        }
        
        .shortcut-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .shortcut-table tr:hover {
            background: var(--light-bg);
        }
        
        .shortcut-key {
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
            border: 1px solid #dee2e6;
        }
        
        .screen-reader-info {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .testing-tools {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .test-button {
            background: var(--accent-purple);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            margin: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .test-button:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
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
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
        
        .contrast-checker {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .contrast-preview {
            display: flex;
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .contrast-sample {
            flex: 1;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .feedback-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .feedback-form {
            margin-top: 1.5rem;
        }
        
        .accessibility-statement {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-purple));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="accessibility-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-universal-access me-3"></i>Accessibility</h1>
            <p class="lead mb-0">Making SmartSchool Uniforms accessible to everyone</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="accessibility-statement">
                    <h3 class="mb-4"><i class="fas fa-bullhorn me-2"></i>Our Commitment to Accessibility</h3>
                    
                    <p>SmartSchool Uniforms is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle me-2"></i>WCAG 2.1 Compliance</h6>
                            <p class="small">We aim to meet Level AA standards of the Web Content Accessibility Guidelines.</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle me-2"></i>Continuous Improvement</h6>
                            <p class="small">Regular audits and updates to enhance accessibility features.</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle me-2"></i>User Feedback</h6>
                            <p class="small">We welcome feedback on accessibility from all users.</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle me-2"></i>Training & Awareness</h6>
                            <p class="small">Our team receives regular accessibility training.</p>
                        </div>
                    </div>
                </div>
                
                <div class="accessibility-section">
                    <h3 class="mb-4">Accessibility Features</h3>
                    
                    <div class="accessibility-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="feature-title">
                                <h4>Visual Accessibility</h4>
                                <p>Features for users with visual impairments</p>
                            </div>
                        </div>
                        
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>High contrast mode option</li>
                            <li><i class="fas fa-check text-success me-2"></i>Adjustable text size (100% - 200%)</li>
                            <li><i class="fas fa-check text-success me-2"></i>Screen reader compatibility</li>
                            <li><i class="fas fa-check text-success me-2"></i>Alt text for all images</li>
                            <li><i class="fas fa-check text-success me-2"></i>Clear, readable fonts</li>
                            <li><i class="fas fa-check text-success me-2"></i>Focus indicators for keyboard navigation</li>
                        </ul>
                    </div>
                    
                    <div class="accessibility-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-keyboard"></i>
                            </div>
                            <div class="feature-title">
                                <h4>Keyboard Navigation</h4>
                                <p>Complete keyboard control without mouse</p>
                            </div>
                        </div>
                        
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Tab navigation through all interactive elements</li>
                            <li><i class="fas fa-check text-success me-2"></i>Logical tab order following visual layout</li>
                            <li><i class="fas fa-check text-success me-2"></i>Keyboard shortcuts for common actions</li>
                            <li><i class="fas fa-check text-success me-2"></i>Skip links to main content</li>
                            <li><i class="fas fa-check text-success me-2"></i>Escape key to close modals and menus</li>
                            <li><i class="fas fa-check text-success me-2"></i>Enter and Space for button activation</li>
                        </ul>
                    </div>
                    
                    <div class="accessibility-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-volume-up"></i>
                            </div>
                            <div class="feature-title">
                                <h4>Audio & Cognitive</h4>
                                <p>Support for hearing and cognitive accessibility</p>
                            </div>
                        </div>
                        
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Captions for video content</li>
                            <li><i class="fas fa-check text-success me-2"></i>Transcripts for audio content</li>
                            <li><i class="fas fa-check text-success me-2"></i>Clear, simple language</li>
                            <li><i class="fas fa-check text-success me-2"></i>Consistent navigation and layout</li>
                            <li><i class="fas fa-check text-success me-2"></i>Error prevention and clear error messages</li>
                            <li><i class="fas fa-check text-success me-2"></i>Enough time to read and use content</li>
                        </ul>
                    </div>
                </div>
                
                <div class="accessibility-tools">
                    <h3 class="mb-4">Accessibility Tools</h3>
                    
                    <div class="tool-grid">
                        <div class="tool-card" onclick="toggleHighContrast()">
                            <div class="tool-icon">
                                <i class="fas fa-adjust"></i>
                            </div>
                            <div class="tool-name">High Contrast</div>
                            <div class="tool-description">Toggle high contrast mode for better visibility</div>
                        </div>
                        
                        <div class="tool-card" onclick="increaseFontSize()">
                            <div class="tool-icon">
                                <i class="fas fa-text-height"></i>
                            </div>
                            <div class="tool-name">Increase Text Size</div>
                            <div class="tool-description">Make text larger for easier reading</div>
                        </div>
                        
                        <div class="tool-card" onclick="decreaseFontSize()">
                            <div class="tool-icon">
                                <i class="fas fa-text-height"></i>
                            </div>
                            <div class="tool-name">Decrease Text Size</div>
                            <div class="tool-description">Make text smaller for more content</div>
                        </div>
                        
                        <div class="tool-card" onclick="toggleGrayscale()">
                            <div class="tool-icon">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="tool-name">Grayscale Mode</div>
                            <div class="tool-description">Remove colors for better contrast</div>
                        </div>
                        
                        <div class="tool-card" onclick="toggleFocusIndicator()">
                            <div class="tool-icon">
                                <i class="fas fa-crosshairs"></i>
                            </div>
                            <div class="tool-name">Focus Indicator</div>
                            <div class="tool-description">Enhanced focus for keyboard navigation</div>
                        </div>
                        
                        <div class="tool-card" onclick="toggleReadingMode()">
                            <div class="tool-icon">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <div class="tool-name">Reading Mode</div>
                            <div class="tool-description">Simplified layout for easier reading</div>
                        </div>
                    </div>
                </div>
                
                <div class="keyboard-shortcuts">
                    <h3 class="mb-4">Keyboard Shortcuts</h3>
                    
                    <table class="shortcut-table">
                        <thead>
                            <tr>
                                <th>Shortcut</th>
                                <th>Action</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="shortcut-key">Tab</span></td>
                                <td>Navigate Forward</td>
                                <td>Move to next interactive element</td>
                            </tr>
                            <tr>
                                <td><span class="shortcut-key">Shift + Tab</span></td>
                                <td>Navigate Backward</td>
                                <td>Move to previous interactive element</td>
                            </tr>
                            <tr>
                                <td><span class="shortcut-key">Enter</span></td>
                                <td>Activate</td>
                                <td>Activate buttons and links</td>
                            </tr>
                            <tr>
                                <td><span class="shortcut-key">Space</span></td>
                                <td>Select/Activate</td>
                                <td>Select checkboxes and activate buttons</td>
                            </tr>
                            <tr>
                                <td><span class="shortcut-key">Escape</span></td>
                                <td>Close</td>
                                <td>Close modals and menus</td>
                            </tr>
                            <tr>
                                <td><span class="shortcut-key">Alt + M</span></td>
                                <td>Main Menu</td>
                                <td>Jump to main navigation</td>
                            </tr>
                            <tr>
                                <td><span class="shortcut-key">Alt + S</span></td>
                                <td>Search</td>
                                <td>Jump to search box</td>
                            </tr>
                            <tr>
                                <td><span class="shortcut-key">Alt + C</span></td>
                                <td>Cart</td>
                                <td>Jump to shopping cart</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="compliance-section">
                    <h3 class="mb-4"><i class="fas fa-certificate me-2"></i>Accessibility Compliance</h3>
                    
                    <div class="compliance-item">
                        <h6>WCAG 2.1 Level AA</h6>
                        <p>We strive to meet the Web Content Accessibility Guidelines 2.1 Level AA standards, ensuring our website is perceivable, operable, understandable, and robust for all users.</p>
                    </div>
                    
                    <div class="compliance-item">
                        <h6>Section 508</h6>
                        <p>Our website complies with Section 508 standards, making electronic information and technology accessible to people with disabilities.</p>
                    </div>
                    
                    <div class="compliance-item">
                        <h6>ADA Compliance</h6>
                        <p>We follow the Americans with Disabilities Act guidelines to ensure equal access to our digital services and information.</p>
                    </div>
                    
                    <div class="compliance-item">
                        <h6>Regular Audits</h6>
                        <p>Quarterly accessibility audits are conducted to identify and address any accessibility issues, ensuring continuous improvement.</p>
                    </div>
                </div>
                
                <div class="screen-reader-info">
                    <h3 class="mb-4">Screen Reader Support</h3>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Supported Screen Readers</h6>
                        <p class="mb-0">Our website is optimized for popular screen readers including:</p>
                        <ul class="mt-2">
                            <li>NVDA (NonVisual Desktop Access)</li>
                            <li>JAWS (Job Access With Speech)</li>
                            <li>VoiceOver (macOS and iOS)</li>
                            <li>TalkBack (Android)</li>
                            <li>Windows Narrator</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle me-2"></i>Screen Reader Features</h6>
                        <ul class="mb-0">
                            <li>Proper heading structure (h1, h2, h3, etc.)</li>
                            <li>Descriptive alt text for images</li>
                            <li>Form labels and descriptions</li>
                            <li>Link context and purpose</li>
                            <li>Table headers and captions</li>
                            <li>ARIA landmarks and roles</li>
                        </ul>
                    </div>
                </div>
                
                <div class="feedback-section">
                    <h3 class="mb-4">Accessibility Feedback</h3>
                    
                    <p>We welcome feedback on the accessibility of our website. If you encounter any accessibility barriers or have suggestions for improvement, please let us know.</p>
                    
                    <div class="feedback-form">
                        <form id="accessibilityFeedback">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" placeholder="Your name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" placeholder="Your email">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Feedback Type</label>
                                <select class="form-select">
                                    <option>Select feedback type...</option>
                                    <option>Accessibility Issue</option>
                                    <option>Suggestion for Improvement</option>
                                    <option>Compliment</option>
                                    <option>General Question</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" rows="4" placeholder="Please describe your feedback in detail..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Page URL (if applicable)</label>
                                <input type="url" class="form-control" placeholder="https://smartschool.co.ke/page">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Assistive Technology Used</label>
                                <select class="form-select">
                                    <option>Select technology...</option>
                                    <option>Screen Reader</option>
                                    <option>Screen Magnifier</option>
                                    <item>Voice Recognition</item>
                                    <item>Keyboard Only</item>
                                    <item>Other</item>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Feedback
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="accessibility-section">
                    <h4 class="mb-3">Accessibility Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">98%</div>
                            <h6>WCAG Compliance</h6>
                            <p class="small text-muted mb-0">Level AA achieved</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">100%</div>
                            <h6>Keyboard Accessible</h6>
                            <p class="small text-muted mb-0">All interactive elements</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">A+</div>
                            <h6>Accessibility Rating</h6>
                            <p class="small text-muted mb-0">Independent audit</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <h6>Monitoring</h6>
                            <p class="small text-muted mb-0">Automated testing</p>
                        </div>
                    </div>
                </div>
                
                <div class="testing-tools">
                    <h4 class="mb-3">Test Your Experience</h4>
                    
                    <div class="text-center mb-4">
                        <p>Try these accessibility tests to experience our features:</p>
                        
                        <div class="d-grid gap-2">
                            <button class="test-button" onclick="testKeyboardNavigation()">
                                <i class="fas fa-keyboard me-2"></i>Test Keyboard Navigation
                            </button>
                            <button class="test-button" onclick="testScreenReader()">
                                <i class="fas fa-eye me-2"></i>Test Screen Reader Mode
                            </button>
                            <button class="test-button" onclick="testHighContrast()">
                                <i class="fas fa-adjust me-2"></i>Test High Contrast
                            </button>
                            <button class="test-button" onclick="testTextSize()">
                                <i class="fas fa-text-height me-2"></i>Test Text Resizing
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb me-2"></i>Testing Tips</h6>
                        <ul class="small mb-0">
                            <li>Use only Tab key to navigate</li>
                            <li>Try increasing text size to 200%</li>
                            <li>Test with high contrast mode</li>
                            <li>Check alt text with screen reader</li>
                        </ul>
                    </div>
                </div>
                
                <div class="contrast-checker">
                    <h4 class="mb-3">Color Contrast Checker</h4>
                    
                    <p>Check if our color combinations meet accessibility standards:</p>
                    
                    <div class="contrast-preview">
                        <div class="contrast-sample" style="background: #000; color: #fff;">
                            Black on White
                            <div class="small">21:1 AAA</div>
                        </div>
                        <div class="contrast-sample" style="background: var(--accent-purple); color: #fff;">
                            Purple on White
                            <div class="small">4.5:1 AA</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle me-2"></i>WCAG Compliant</h6>
                        <p class="small mb-0">All color combinations meet WCAG 2.1 AA standards for contrast ratio.</p>
                    </div>
                </div>
                
                <div class="accessibility-section">
                    <h4 class="mb-3">Quick Accessibility Tips</h4>
                    
                    <div class="list-group">
                        <div class="list-group-item">
                            <i class="fas fa-keyboard me-2"></i>
                            <strong>Use Tab Key</strong>
                            <small class="text-muted d-block">Navigate without mouse</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-search-plus me-2"></i>
                            <strong>Zoom In</strong>
                            <small class="text-muted d-block">Ctrl + Plus to zoom</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-adjust me-2"></i>
                            <strong>High Contrast</strong>
                            <small class="text-muted d-block">Use system high contrast</small>
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-volume-up me-2"></i>
                            <strong>Screen Reader</strong>
                            <small class="text-muted d-block">Enable for audio navigation</small>
                        </div>
                    </div>
                </div>
                
                <div class="accessibility-section">
                    <h4 class="mb-3">Accessibility Resources</h4>
                    
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-book me-2"></i>
                            <strong>Accessibility Guide</strong>
                            <small class="text-muted d-block">Complete user guide</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-video me-2"></i>
                            <strong>Video Tutorials</strong>
                            <small class="text-muted d-block">Learn accessibility features</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-download me-2"></i>
                            <strong>Accessibility Tools</strong>
                            <small class="text-muted d-block">Recommended software</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-question-circle me-2"></i>
                            <strong>Help Center</strong>
                            <small class="text-muted d-block">Get assistance</small>
                        </a>
                    </div>
                </div>
                
                <div class="accessibility-section">
                    <h4 class="mb-3">Contact Accessibility Team</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-envelope me-2"></i>Email</h6>
                        <p class="small mb-0">accessibility@smartschool.co.ke</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-phone me-2"></i>Phone</h6>
                        <p class="small mb-0">+254 700 999 888 (Accessibility Hotline)</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-comments me-2"></i>Live Chat</h6>
                        <p class="small mb-0">Available Monday-Friday, 9AM-5PM</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentFontSize = 100;
        let highContrastMode = false;
        let grayscaleMode = false;
        let focusIndicator = false;
        let readingMode = false;
        
        function toggleHighContrast() {
            highContrastMode = !highContrastMode;
            document.body.classList.toggle('high-contrast');
            alert('High contrast mode ' + (highContrastMode ? 'enabled' : 'disabled'));
        }
        
        function increaseFontSize() {
            if (currentFontSize < 200) {
                currentFontSize += 10;
                document.body.style.fontSize = currentFontSize + '%';
                alert('Text size increased to ' + currentFontSize + '%');
            } else {
                alert('Maximum text size reached');
            }
        }
        
        function decreaseFontSize() {
            if (currentFontSize > 100) {
                currentFontSize -= 10;
                document.body.style.fontSize = currentFontSize + '%';
                alert('Text size decreased to ' + currentFontSize + '%');
            } else {
                alert('Minimum text size reached');
            }
        }
        
        function toggleGrayscale() {
            grayscaleMode = !grayscaleMode;
            document.body.classList.toggle('grayscale');
            alert('Grayscale mode ' + (grayscaleMode ? 'enabled' : 'disabled'));
        }
        
        function toggleFocusIndicator() {
            focusIndicator = !focusIndicator;
            document.body.classList.toggle('enhanced-focus');
            alert('Enhanced focus indicator ' + (focusIndicator ? 'enabled' : 'disabled'));
        }
        
        function toggleReadingMode() {
            readingMode = !readingMode;
            document.body.classList.toggle('reading-mode');
            alert('Reading mode ' + (readingMode ? 'enabled' : 'disabled'));
        }
        
        function testKeyboardNavigation() {
            alert('Keyboard Navigation Test:\n\n1. Use Tab key to navigate forward\n2. Use Shift+Tab to navigate backward\n3. Use Enter to activate buttons\n4. Use Escape to close modals\n\nTry navigating the entire page using only your keyboard!');
        }
        
        function testScreenReader() {
            alert('Screen Reader Test:\n\n1. Enable your screen reader software\n2. Navigate through the page\n3. Listen for proper descriptions\n4. Check for heading structure\n\nAll elements should be properly labeled!');
        }
        
        function testHighContrast() {
            toggleHighContrast();
            alert('High Contrast Test:\n\nCheck if all text is clearly visible against backgrounds.\n\nToggle back to normal mode when done testing.');
        }
        
        function testTextSize() {
            alert('Text Size Test:\n\n1. Use Ctrl + Plus to increase text size\n2. Use Ctrl + Minus to decrease text size\n3. Test up to 200% zoom\n\nAll content should remain readable and functional!');
        }
        
        // Accessibility feedback form
        document.getElementById('accessibilityFeedback').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your accessibility feedback! We will review your submission and work to improve our accessibility features.');
            this.reset();
        });
    </script>
    
    <style>
        .high-contrast {
            filter: contrast(1.5);
            background: #000 !important;
            color: #fff !important;
        }
        
        .high-contrast * {
            background: #000 !important;
            color: #fff !important;
            border-color: #fff !important;
        }
        
        .grayscale {
            filter: grayscale(100%);
        }
        
        .enhanced-focus *:focus {
            outline: 3px solid #ff0 !important;
            outline-offset: 2px !important;
        }
        
        .reading-mode {
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
            font-family: Georgia, serif;
        }
        
        .reading-mode .sidebar,
        .reading-mode .header,
        .reading-mode .footer {
            display: none;
        }
    </style>
</body>
</html>
