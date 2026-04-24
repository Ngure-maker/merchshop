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
    <title>Loyalty Program - SmartSchool Uniforms</title>
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
        
        .loyalty-header {
            background: linear-gradient(135deg, var(--accent-purple), var(--primary-amber));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .loyalty-card {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent-purple);
        }
        
        .loyalty-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .points-display {
            background: linear-gradient(135deg, var(--accent-purple), var(--primary-amber));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .points-display::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 4s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .points-number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .tier-progress {
            background: #e9ecef;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .tier-progress-bar {
            background: linear-gradient(90deg, var(--accent-purple), var(--primary-amber));
            height: 100%;
            transition: width 1s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .reward-item {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .reward-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .reward-badge {
            background: var(--accent-purple);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .tier-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .tier-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
        }
        
        .tier-card.bronze::before {
            background: linear-gradient(90deg, #CD7F32, #8B4513);
        }
        
        .tier-card.silver::before {
            background: linear-gradient(90deg, #C0C0C0, #808080);
        }
        
        .tier-card.gold::before {
            background: linear-gradient(90deg, #FFD700, #FFA500);
        }
        
        .tier-card.platinum::before {
            background: linear-gradient(90deg, #E5E4E2, #BCC6CC);
        }
        
        .tier-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .tier-icon.bronze {
            background: linear-gradient(135deg, #CD7F32, #8B4513);
            color: white;
        }
        
        .tier-icon.silver {
            background: linear-gradient(135deg, #C0C0C0, #808080);
            color: white;
        }
        
        .tier-icon.gold {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: white;
        }
        
        .tier-icon.platinum {
            background: linear-gradient(135deg, #E5E4E2, #BCC6CC);
            color: #333;
        }
        
        .earning-method {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-purple);
        }
        
        .earning-method i {
            font-size: 2rem;
            color: var(--accent-purple);
            margin-right: 1rem;
        }
        
        .redemption-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .redemption-option:hover {
            border-color: var(--accent-purple);
            transform: translateY(-2px);
        }
        
        .points-needed {
            background: var(--light-bg);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            color: var(--accent-purple);
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="loyalty-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-award me-3"></i>Loyalty Program</h1>
            <p class="lead mb-0">Earn points with every purchase and unlock exclusive rewards!</p>
        </div>
    </div>
    
    <main class="container my-5">
        <?php if ($auth->isLoggedIn()): ?>
            <div class="loyalty-card">
                <h3 class="mb-4"><i class="fas fa-user-circle me-2"></i>Your Loyalty Status</h3>
                
                <div class="points-display">
                    <div class="points-number">2,450</div>
                    <div class="mb-3">Available Points</div>
                    <div class="tier-progress">
                        <div class="tier-progress-bar" style="width: 65%;">
                            650 points to Silver Tier
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <small>Total Earned: 3,100</small>
                        </div>
                        <div class="col-md-4">
                            <small>Current Tier: Bronze</small>
                        </div>
                        <div class="col-md-4">
                            <small>Member Since: Jan 2024</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="reward-item">
                            <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                            <h6>Orders Placed</h6>
                            <div class="h4 mb-0">12</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="reward-item">
                            <i class="fas fa-coins fa-2x text-warning mb-2"></i>
                            <h6>Points Earned</h6>
                            <div class="h4 mb-0">3,100</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="reward-item">
                            <i class="fas fa-gift fa-2x text-success mb-2"></i>
                            <h6>Rewards Redeemed</h6>
                            <div class="h4 mb-0">3</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 text-center">
                        <div class="reward-item">
                            <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                            <h6>Total Saved</h6>
                            <div class="h4 mb-0">KSh 2,800</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="loyalty-card text-center">
                <h3 class="mb-3"><i class="fas fa-sign-in-alt me-2"></i>Join Our Loyalty Program</h3>
                <p class="mb-4">Create an account to start earning points and unlocking exclusive rewards!</p>
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Join Now
                </a>
                <a href="login.php" class="btn btn-outline-primary btn-lg ms-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        <?php endif; ?>
        
        <div class="loyalty-card">
            <h3 class="mb-4"><i class="fas fa-trophy me-2"></i>Loyalty Tiers</h3>
            
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="tier-card bronze">
                        <div class="tier-icon bronze">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h5>Bronze Tier</h5>
                        <p class="text-muted mb-3">0 - 1,000 points</p>
                        
                        <ul class="small">
                            <li>1 point per KSh 10 spent</li>
                            <li>5% birthday discount</li>
                            <li>Free shipping on orders over KSh 3,000</li>
                            <li>Early access to sales</li>
                        </ul>
                        
                        <div class="text-center">
                            <span class="badge bg-warning text-dark">STARTER</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="tier-card silver">
                        <div class="tier-icon silver">
                            <i class="fas fa-award"></i>
                        </div>
                        <h5>Silver Tier</h5>
                        <p class="text-muted mb-3">1,001 - 5,000 points</p>
                        
                        <ul class="small">
                            <li>1.5 points per KSh 10 spent</li>
                            <li>10% birthday discount</li>
                            <li>Free shipping on all orders</li>
                            <li>Exclusive member deals</li>
                            <li>Priority customer support</li>
                        </ul>
                        
                        <div class="text-center">
                            <span class="badge bg-secondary">POPULAR</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="tier-card gold">
                        <div class="tier-icon gold">
                            <i class="fas fa-crown"></i>
                        </div>
                        <h5>Gold Tier</h5>
                        <p class="text-muted mb-3">5,001 - 15,000 points</p>
                        
                        <ul class="small">
                            <li>2 points per KSh 10 spent</li>
                            <li>15% birthday discount</li>
                            <li>Free express shipping</li>
                            <li>Exclusive member deals</li>
                            <li>Dedicated account manager</li>
                            <li>Invitation to special events</li>
                        </ul>
                        
                        <div class="text-center">
                            <span class="badge bg-warning text-dark">PREMIUM</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="tier-card platinum">
                        <div class="tier-icon platinum">
                            <i class="fas fa-gem"></i>
                        </div>
                        <h5>Platinum Tier</h5>
                        <p class="text-muted mb-3">15,001+ points</p>
                        
                        <ul class="small">
                            <li>3 points per KSh 10 spent</li>
                            <li>20% birthday discount</li>
                            <li>Free express shipping</li>
                            <li>VIP exclusive deals</li>
                            <li>Dedicated account manager</li>
                            <li>Invitation to special events</li>
                            <li>First access to new products</li>
                            <li>Custom uniform consultation</li>
                        </ul>
                        
                        <div class="text-center">
                            <span class="badge bg-dark">ELITE</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6">
                <div class="loyalty-card">
                    <h3 class="mb-4"><i class="fas fa-plus-circle me-2"></i>How to Earn Points</h3>
                    
                    <div class="earning-method">
                        <i class="fas fa-shopping-cart"></i>
                        <div>
                            <h6>Purchase Products</h6>
                            <p class="small mb-0">Earn 1-3 points for every KSh 10 spent, depending on your tier</p>
                        </div>
                    </div>
                    
                    <div class="earning-method">
                        <i class="fas fa-user-plus"></i>
                        <div>
                            <h6>Refer Friends</h6>
                            <p class="small mb-0">Get 100 points for each successful referral</p>
                        </div>
                    </div>
                    
                    <div class="earning-method">
                        <i class="fas fa-birthday-cake"></i>
                        <div>
                            <h6>Birthday Bonus</h6>
                            <p class="small mb-0">Receive 200 bonus points on your birthday</p>
                        </div>
                    </div>
                    
                    <div class="earning-method">
                        <i class="fas fa-comment"></i>
                        <div>
                            <h6>Product Reviews</h6>
                            <p class="small mb-0">Earn 25 points for each product review</p>
                        </div>
                    </div>
                    
                    <div class="earning-method">
                        <i class="fas fa-share-alt"></i>
                        <div>
                            <h6>Social Media Share</h6>
                            <p class="small mb-0">Get 10 points for sharing purchases on social media</p>
                        </div>
                    </div>
                    
                    <div class="earning-method">
                        <i class="fas fa-calendar-check"></i>
                        <div>
                            <h6>Anniversary Bonus</h6>
                            <p class="small mb-0">Earn 500 points on your membership anniversary</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="loyalty-card">
                    <h3 class="mb-4"><i class="fas fa-gift me-2"></i>Redeem Rewards</h3>
                    
                    <div class="redemption-option">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6>KSh 100 Discount</h6>
                                <p class="small text-muted mb-2">Instant discount on your next purchase</p>
                                <span class="points-needed">500 points</span>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Redeem</button>
                        </div>
                    </div>
                    
                    <div class="redemption-option">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6>KSh 250 Discount</h6>
                                <p class="small text-muted mb-2">Save more on larger purchases</p>
                                <span class="points-needed">1,000 points</span>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Redeem</button>
                        </div>
                    </div>
                    
                    <div class="redemption-option">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6>KSh 500 Discount</h6>
                                <p class="small text-muted mb-2">Maximum savings on big orders</p>
                                <span class="points-needed">2,000 points</span>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Redeem</button>
                        </div>
                    </div>
                    
                    <div class="redemption-option">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6>Free Shipping Voucher</h6>
                                <p class="small text-muted mb-2">Free shipping on your next order</p>
                                <span class="points-needed">300 points</span>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Redeem</button>
                        </div>
                    </div>
                    
                    <div class="redemption-option">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6>Priority Processing</h6>
                                <p class="small text-muted mb-2">Skip the queue with priority order processing</p>
                                <span class="points-needed">400 points</span>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Redeem</button>
                        </div>
                    </div>
                    
                    <div class="redemption-option">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6>Exclusive Gift</h6>
                                <p class="small text-muted mb-2">Special member-only gift with purchase</p>
                                <span class="points-needed">1,500 points</span>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Redeem</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="loyalty-card">
            <h3 class="mb-4"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h3>
            
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            How do I join the loyalty program?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Simply create an account on SmartSchool Uniforms - you're automatically enrolled in our loyalty program and start earning points from your first purchase!
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Do my points expire?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Points expire after 12 months of inactivity. However, any purchase, referral, or engagement will reset the expiration timer for all your points.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Can I combine rewards with other discounts?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Loyalty rewards can be combined with most promotions, except for other loyalty rewards or special clearance sales. The maximum discount cannot exceed 50% of the order value.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            How do I track my points and tier status?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Your loyalty dashboard shows your current points, tier status, and progress to the next tier. You'll also receive email notifications when you reach new milestones.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate tier progress bar on page load
        window.addEventListener('load', function() {
            const progressBar = document.querySelector('.tier-progress-bar');
            if (progressBar) {
                const targetWidth = progressBar.style.width;
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = targetWidth;
                }, 500);
            }
        });
        
        // Handle reward redemption
        document.querySelectorAll('.redemption-option button').forEach(btn => {
            btn.addEventListener('click', function() {
                const option = this.closest('.redemption-option');
                const rewardName = option.querySelector('h6').textContent;
                const pointsNeeded = option.querySelector('.points-needed').textContent;
                
                if (confirm(`Redeem ${rewardName} for ${pointsNeeded}?`)) {
                    // Show success message
                    this.textContent = 'Redeemed!';
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-success');
                    this.disabled = true;
                }
            });
        });
    </script>
</body>
</html>
