<?php
session_start();
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
    <title>Share Wishlist - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .share-header {
            background: linear-gradient(135deg, var(--accent-purple), var(--primary-color));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .share-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .wishlist-preview {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .wishlist-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .wishlist-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .wishlist-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
        
        .item-details {
            flex-grow: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .item-price {
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .item-quantity {
            background: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-left: 1rem;
        }
        
        .share-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .share-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .share-option:hover {
            border-color: var(--accent-purple);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .share-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
            color: white;
        }
        
        .share-icon.whatsapp {
            background: #25D366;
        }
        
        .share-icon.email {
            background: var(--secondary-blue);
        }
        
        .share-icon.facebook {
            background: #1877F2;
        }
        
        .share-icon.twitter {
            background: #1DA1F2;
        }
        
        .share-icon.link {
            background: var(--accent-purple);
        }
        
        .share-icon.sms {
            background: var(--secondary-teal);
        }
        
        .share-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .form-control:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 0.2rem rgba(123, 31, 162, 0.25);
        }
        
        .generated-link {
            background: var(--light-bg);
            border: 2px solid var(--accent-purple);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            word-break: break-all;
        }
        
        .copy-button {
            background: var(--accent-purple);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .copy-button:hover {
            background: var(--primary-color);
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
        
        .shared-with {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .person-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .person-avatar {
            width: 40px;
            height: 40px;
            background: var(--accent-purple);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .person-details {
            flex-grow: 1;
        }
        
        .person-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .person-date {
            font-size: 0.8rem;
            color: var(--neutral-gray);
        }
        
        .message-preview {
            background: var(--light-bg);
            border-left: 4px solid var(--accent-purple);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="share-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-share me-3"></i>Share Wishlist</h1>
            <p class="lead mb-0">Share your wishlist with family and friends</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="share-section">
                    <h3 class="mb-4"><i class="fas fa-heart me-2"></i>Your Wishlist</h3>
                    
                    <div class="wishlist-preview">
                        <div class="wishlist-item">
                            <img src="https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=Shirt" alt="White School Shirt">
                            <div class="item-details">
                                <div class="item-name">White School Shirt</div>
                                <div class="item-price">KSh 850</div>
                                <small class="text-muted">Size: M • Color: White</small>
                            </div>
                            <div class="item-quantity">Qty: 2</div>
                        </div>
                        
                        <div class="wishlist-item">
                            <img src="https://via.placeholder.com/80x80/00897B/FFFFFF?text=Trousers" alt="Grey School Trousers">
                            <div class="item-details">
                                <div class="item-name">Grey School Trousers</div>
                                <div class="item-price">KSh 1,200</div>
                                <small class="text-muted">Size: 32 • Color: Grey</small>
                            </div>
                            <div class="item-quantity">Qty: 1</div>
                        </div>
                        
                        <div class="wishlist-item">
                            <img src="https://via.placeholder.com/80x80/1976D2/FFFFFF?text=Shoes" alt="Black School Shoes">
                            <div class="item-details">
                                <div class="item-name">Black School Shoes</div>
                                <div class="item-price">KSh 1,800</div>
                                <small class="text-muted">Size: 7 • Color: Black</small>
                            </div>
                            <div class="item-quantity">Qty: 1</div>
                        </div>
                        
                        <div class="wishlist-item">
                            <img src="https://via.placeholder.com/80x80/7B1FA2/FFFFFF?text=Sweater" alt="V-Neck Sweater">
                            <div class="item-details">
                                <div class="item-name">V-Neck Sweater</div>
                                <div class="item-price">KSh 950</div>
                                <small class="text-muted">Size: M • Color: Navy</small>
                            </div>
                            <div class="item-quantity">Qty: 1</div>
                        </div>
                    </div>
                    
                    <div class="text-end mb-4">
                        <h5>Total: <span class="text-primary">KSh 5,650</span></h5>
                    </div>
                    
                    <h4 class="mb-3">Share Your Wishlist</h4>
                    
                    <div class="share-options">
                        <div class="share-option" onclick="shareViaWhatsApp()">
                            <div class="share-icon whatsapp">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div>
                                <div class="fw-bold">WhatsApp</div>
                                <small class="text-muted">Share via WhatsApp</small>
                            </div>
                        </div>
                        
                        <div class="share-option" onclick="shareViaEmail()">
                            <div class="share-icon email">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Email</div>
                                <small class="text-muted">Send via email</small>
                            </div>
                        </div>
                        
                        <div class="share-option" onclick="shareViaFacebook()">
                            <div class="share-icon facebook">
                                <i class="fab fa-facebook-f"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Facebook</div>
                                <small class="text-muted">Share on Facebook</small>
                            </div>
                        </div>
                        
                        <div class="share-option" onclick="shareViaTwitter()">
                            <div class="share-icon twitter">
                                <i class="fab fa-twitter"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Twitter</div>
                                <small class="text-muted">Share on Twitter</small>
                            </div>
                        </div>
                        
                        <div class="share-option" onclick="copyLink()">
                            <div class="share-icon link">
                                <i class="fas fa-link"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Copy Link</div>
                                <small class="text-muted">Copy wishlist link</small>
                            </div>
                        </div>
                        
                        <div class="share-option" onclick="shareViaSMS()">
                            <div class="share-icon sms">
                                <i class="fas fa-sms"></i>
                            </div>
                            <div>
                                <div class="fw-bold">SMS</div>
                                <small class="text-muted">Share via SMS</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="share-form">
                    <h4 class="mb-4"><i class="fas fa-paper-plane me-2"></i>Share via Email</h4>
                    
                    <form id="shareForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Recipient Email</label>
                                    <input type="email" class="form-control" id="recipientEmail" placeholder="Enter email address" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="senderName" placeholder="Enter your name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" id="emailSubject" value="My SmartSchool Uniform Wishlist" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Message (Optional)</label>
                            <textarea class="form-control" id="emailMessage" rows="4" placeholder="Add a personal message...">Hi! I've created a wishlist of school uniforms I'd love to have. Please check it out and let me know what you think!</textarea>
                        </div>
                        
                        <div class="message-preview">
                            <h6><i class="fas fa-eye me-2"></i>Message Preview</h6>
                            <div id="previewText">
                                Hi! I've created a wishlist of school uniforms I'd love to have. Please check it out and let me know what you think!<br><br>
                                View my wishlist: <strong>https://smartschool.com/wishlist/ABC123</strong><br><br>
                                Total: KSh 5,650<br>
                                Items: 4
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Send Email
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="share-section">
                    <h4 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Sharing Stats</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">12</div>
                            <h6>Times Shared</h6>
                            <p class="small text-muted mb-0">Total shares</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">8</div>
                            <h6>People Reached</h6>
                            <p class="small text-muted mb-0">Unique recipients</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">3</div>
                            <h6>Items Purchased</h6>
                            <p class="small text-muted mb-0">From your wishlist</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">KSh 2,850</div>
                            <h6>Value Received</h6>
                            <p class="small text-muted mb-0">Gift value</p>
                        </div>
                    </div>
                </div>
                
                <div class="share-section">
                    <h4 class="mb-3"><i class="fas fa-users me-2"></i>Recently Shared With</h4>
                    
                    <div class="shared-with">
                        <div class="person-item">
                            <div class="person-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="person-details">
                                <div class="person-name">Mary Johnson</div>
                                <div class="person-date">Shared via WhatsApp • 2 days ago</div>
                            </div>
                        </div>
                        
                        <div class="person-item">
                            <div class="person-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="person-details">
                                <div class="person-name">James Kimani</div>
                                <div class="person-date">Shared via Email • 5 days ago</div>
                            </div>
                        </div>
                        
                        <div class="person-item">
                            <div class="person-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="person-details">
                                <div class="person-name">Sarah Wanjiku</div>
                                <div class="person-date">Shared via Facebook • 1 week ago</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="share-section">
                    <h4 class="mb-3"><i class="fas fa-link me-2"></i>Wishlist Link</h4>
                    
                    <div class="generated-link">
                        <strong>https://smartschool.com/wishlist/ABC123</strong>
                    </div>
                    
                    <button class="copy-button w-100 mb-3" onclick="copyLink()">
                        <i class="fas fa-copy me-2"></i>Copy Link
                    </button>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Link Benefits</h6>
                        <ul class="small mb-0">
                            <li>Anyone with the link can view your wishlist</li>
                            <li>Link expires after 30 days</li>
                            <li>You can disable sharing at any time</li>
                            <li>Updates to your wishlist are reflected instantly</li>
                        </ul>
                    </div>
                </div>
                
                <div class="share-section">
                    <h4 class="mb-3"><i class="fas fa-lightbulb me-2"></i>Sharing Tips</h4>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-gift me-2"></i>Gift Ideas</h6>
                        <p class="small mb-0">Share your wishlist before birthdays, holidays, or special occasions to help friends and family choose the perfect gifts.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-users me-2"></i>Group Gifting</h6>
                        <p class="small mb-0">Multiple people can contribute to purchase items from your wishlist. Perfect for larger purchases like complete uniform sets.</p>
                    </div>
                    
                    <div class="alert alert-primary">
                        <h6><i class="fas fa-sync me-2"></i>Keep Updated</h6>
                        <p class="small mb-0">Remember to update your wishlist when you receive items or if your needs change.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Generate unique wishlist link
        const wishlistLink = 'https://smartschool.com/wishlist/ABC123';
        
        // Share via WhatsApp
        function shareViaWhatsApp() {
            const message = `Hi! I've created a wishlist of school uniforms I'd love to have. Check it out: ${wishlistLink}`;
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
        
        // Share via Email
        function shareViaEmail() {
            const subject = document.getElementById('emailSubject').value;
            const message = document.getElementById('emailMessage').value;
            const body = `${message}\n\nView my wishlist: ${wishlistLink}\n\nTotal: KSh 5,650\nItems: 4`;
            const mailtoUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.location.href = mailtoUrl;
        }
        
        // Share via Facebook
        function shareViaFacebook() {
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(wishlistLink)}`;
            window.open(facebookUrl, '_blank', 'width=600,height=400');
        }
        
        // Share via Twitter
        function shareViaTwitter() {
            const message = `Check out my SmartSchool uniform wishlist! ${wishlistLink}`;
            const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}`;
            window.open(twitterUrl, '_blank', 'width=600,height=400');
        }
        
        // Copy link to clipboard
        function copyLink() {
            navigator.clipboard.writeText(wishlistLink).then(() => {
                alert('Wishlist link copied to clipboard!');
            });
        }
        
        // Share via SMS
        function shareViaSMS() {
            const message = `Hi! I've created a wishlist of school uniforms. Check it out: ${wishlistLink}`;
            const smsUrl = `sms:?body=${encodeURIComponent(message)}`;
            window.location.href = smsUrl;
        }
        
        // Handle email form submission
        document.getElementById('shareForm').addEventListener('submit', function(e) {
            e.preventDefault();
            shareViaEmail();
        });
        
        // Update message preview
        document.getElementById('emailMessage').addEventListener('input', function() {
            const message = this.value || 'Hi! I\'ve created a wishlist of school uniforms I\'d love to have. Please check it out and let me know what you think!';
            const previewText = `
                ${message}<br><br>
                View my wishlist: <strong>${wishlistLink}</strong><br><br>
                Total: KSh 5,650<br>
                Items: 4
            `;
            document.getElementById('previewText').innerHTML = previewText;
        });
    </script>
</body>
</html>


