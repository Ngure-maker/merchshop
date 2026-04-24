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
    <title>Referral Program - SmartSchool Uniforms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
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
        
        .referral-header {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .referral-card {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent-green);
        }
        
        .referral-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .reward-tier {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .reward-tier::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--accent-green), var(--secondary-teal));
        }
        
        .reward-tier.gold::before {
            background: linear-gradient(90deg, #FFD700, #FFA500);
        }
        
        .reward-tier.platinum::before {
            background: linear-gradient(90deg, #E5E4E2, #BCC6CC);
        }
        
        .tier-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent-green);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .tier-badge.gold {
            background: linear-gradient(135deg, #FFD700, #FFA500);
        }
        
        .tier-badge.platinum {
            background: linear-gradient(135deg, #E5E4E2, #BCC6CC);
            color: #333;
        }
        
        .referral-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .referral-link-box {
            background: var(--light-bg);
            border: 2px dashed var(--accent-green);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .copy-btn {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .copy-btn:hover {
            background: var(--secondary-teal);
            transform: translateY(-2px);
        }
        
        .referral-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .referral-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 10%;
            right: 10%;
            height: 2px;
            background: var(--accent-green);
            z-index: 0;
        }
        
        .step {
            background: white;
            border: 2px solid var(--accent-green);
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 1;
            position: relative;
        }
        
        .step i {
            font-size: 1.5rem;
            color: var(--accent-green);
            margin-bottom: 0.25rem;
        }
        
        .step-number {
            font-size: 0.8rem;
            font-weight: bold;
            color: var(--accent-green);
        }
        
        .testimonial-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .testimonial-card .stars {
            color: #ffc107;
            margin-bottom: 0.5rem;
        }
        
        .leaderboard-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .leaderboard-rank {
            width: 40px;
            height: 40px;
            background: var(--accent-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
        }
        
        .leaderboard-rank.gold {
            background: linear-gradient(135deg, #FFD700, #FFA500);
        }
        
        .leaderboard-rank.silver {
            background: linear-gradient(135deg, #C0C0C0, #808080);
        }
        
        .leaderboard-rank.bronze {
            background: linear-gradient(135deg, #CD7F32, #8B4513);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="referral-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-share-alt me-3"></i>Referral Program</h1>
            <p class="lead mb-0">Share SmartSchool with friends and earn rewards together!</p>
        </div>
    </div>
    
    <main class="container my-5">
        <?php if ($auth->isLoggedIn()): ?>
            <div class="referral-card">
                <h3 class="mb-4"><i class="fas fa-user-circle me-2"></i>Your Referral Dashboard</h3>
                
                <div class="referral-stats">
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div>Total Referrals</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">KSh 0</div>
                        <div>Total Earned</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">KSh 500</div>
                        <div>Pending Rewards</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">Bronze</div>
                        <div>Current Tier</div>
                    </div>
                </div>
                
                <div class="referral-link-box">
                    <h4 class="mb-3"><i class="fas fa-link me-2"></i>Your Referral Link</h4>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="https://smartschool.com/ref?code=USER123" readonly id="referralLink">
                        <button class="copy-btn" onclick="copyReferralLink()">
                            <i class="fas fa-copy me-1"></i>Copy
                        </button>
                    </div>
                    <p class="small text-muted mb-0">Share this link with friends and family. When they make a purchase, you both get rewarded!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="referral-card text-center">
                <h3 class="mb-3"><i class="fas fa-sign-in-alt me-2"></i>Join to Start Earning</h3>
                <p class="mb-4">Create an account to access your referral dashboard and start earning rewards!</p>
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </a>
                <a href="login.php" class="btn btn-outline-primary btn-lg ms-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        <?php endif; ?>
        
        <div class="referral-card">
            <h3 class="mb-4"><i class="fas fa-gift me-2"></i>How It Works</h3>
            
            <div class="referral-steps">
                <div class="step">
                    <i class="fas fa-share"></i>
                    <div class="step-number">1</div>
                </div>
                
                <div class="step">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="step-number">2</div>
                </div>
                
                <div class="step">
                    <i class="fas fa-check-circle"></i>
                    <div class="step-number">3</div>
                </div>
                
                <div class="step">
                    <i class="fas fa-coins"></i>
                    <div class="step-number">4</div>
                </div>
            </div>
            
            <div class="row text-center">
                <div class="col-md-3">
                    <h5>Share Your Link</h5>
                    <p class="small">Share your unique referral link with friends and family</p>
                </div>
                
                <div class="col-md-3">
                    <h5>They Shop</h5>
                    <p class="small">Your referrals use the link to make their first purchase</p>
                </div>
                
                <div class="col-md-3">
                    <h5>Order Confirmed</h5>
                    <p class="small">Once their order is delivered, rewards are unlocked</p>
                </div>
                
                <div class="col-md-3">
                    <h5>Get Rewarded</h5>
                    <p class="small">Both you and your referral earn rewards!</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="referral-card">
                    <h3 class="mb-4"><i class="fas fa-trophy me-2"></i>Reward Tiers</h3>
                    
                    <div class="reward-tier">
                        <div class="tier-badge">STARTER</div>
                        <h5>Bronze Tier</h5>
                        <p class="text-muted mb-3">0-10 successful referrals</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-user me-2"></i>Your Reward</h6>
                                <p class="mb-0"><strong>KSh 200</strong> per successful referral</p>
                            </div>
                            
                            <div class="col-md-6">
                                <h6><i class="fas fa-gift me-2"></i>Friend's Reward</h6>
                                <p class="mb-0"><strong>10% discount</strong> on first order</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="reward-tier gold">
                        <div class="tier-badge gold">POPULAR</div>
                        <h5>Gold Tier</h5>
                        <p class="text-muted mb-3">11-25 successful referrals</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-user me-2"></i>Your Reward</h6>
                                <p class="mb-0"><strong>KSh 300</strong> per successful referral</p>
                            </div>
                            
                            <div class="col-md-6">
                                <h6><i class="fas fa-gift me-2"></i>Friend's Reward</h6>
                                <p class="mb-0"><strong>15% discount</strong> on first order</p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <span class="badge bg-warning text-dark">Exclusive: Priority customer support</span>
                        </div>
                    </div>
                    
                    <div class="reward-tier platinum">
                        <div class="tier-badge platinum">ELITE</div>
                        <h5>Platinum Tier</h5>
                        <p class="text-muted mb-3">26+ successful referrals</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-user me-2"></i>Your Reward</h6>
                                <p class="mb-0"><strong>KSh 500</strong> per successful referral</p>
                            </div>
                            
                            <div class="col-md-6">
                                <h6><i class="fas fa-gift me-2"></i>Friend's Reward</h6>
                                <p class="mb-0"><strong>20% discount</strong> on first order</p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <span class="badge bg-secondary">Exclusive: VIP benefits + early access to sales</span>
                        </div>
                    </div>
                </div>
                
                <div class="referral-card">
                    <h3 class="mb-4"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h3>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    When do I get my rewards?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Rewards are credited to your account within 24 hours after your referral's order is successfully delivered and the return period expires (7 days).
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Can I refer multiple people?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes! There's no limit to how many people you can refer. The more you refer, the higher your tier and the more you earn per referral.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How can I track my referrals?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Your referral dashboard shows all your referrals, their status, and rewards earned. You'll also receive email notifications for each successful referral.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    What counts as a successful referral?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    A successful referral is when someone uses your link to make their first purchase of at least KSh 1,000, and the order is successfully delivered without being returned.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="referral-card">
                    <h4 class="mb-3"><i class="fas fa-crown me-2"></i>Top Referrers</h4>
                    
                    <div class="leaderboard-item">
                        <div class="leaderboard-rank gold">1</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Sarah M.</h6>
                            <small class="text-muted">47 referrals</small>
                        </div>
                        <div class="text-end">
                            <strong>KSh 23,500</strong>
                        </div>
                    </div>
                    
                    <div class="leaderboard-item">
                        <div class="leaderboard-rank silver">2</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">James K.</h6>
                            <small class="text-muted">32 referrals</small>
                        </div>
                        <div class="text-end">
                            <strong>KSh 9,600</strong>
                        </div>
                    </div>
                    
                    <div class="leaderboard-item">
                        <div class="leaderboard-rank bronze">3</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Mary J.</h6>
                            <small class="text-muted">28 referrals</small>
                        </div>
                        <div class="text-end">
                            <strong>KSh 5,600</strong>
                        </div>
                    </div>
                    
                    <div class="leaderboard-item">
                        <div class="leaderboard-rank">4</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">John D.</h6>
                            <small class="text-muted">15 referrals</small>
                        </div>
                        <div class="text-end">
                            <strong>KSh 3,000</strong>
                        </div>
                    </div>
                    
                    <div class="leaderboard-item">
                        <div class="leaderboard-rank">5</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Lucy W.</h6>
                            <small class="text-muted">12 referrals</small>
                        </div>
                        <div class="text-end">
                            <strong>KSh 2,400</strong>
                        </div>
                    </div>
                </div>
                
                <div class="referral-card">
                    <h4 class="mb-3"><i class="fas fa-share-alt me-2"></i>Share Your Link</h4>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp me-2"></i>Share on WhatsApp
                        </button>
                        
                        <button class="btn btn-info" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook me-2"></i>Share on Facebook
                        </button>
                        
                        <button class="btn btn-secondary" onclick="shareViaEmail()">
                            <i class="fas fa-envelope me-2"></i>Share via Email
                        </button>
                        
                        <button class="btn btn-success" onclick="copyReferralLink()">
                            <i class="fas fa-copy me-2"></i>Copy Link
                        </button>
                    </div>
                </div>
                
                <div class="referral-card">
                    <h4 class="mb-3"><i class="fas fa-comments me-2"></i>Success Stories</h4>
                    
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="small mb-0">"I've earned over KSh 20,000 just by sharing with other parents at my child's school. It's so easy!"</p>
                        <small class="text-muted">- Sarah M., Platinum Tier</small>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="small mb-0">"My friends love getting the discount, and I earn rewards. It's a win-win for everyone!"</p>
                        <small class="text-muted">- James K., Gold Tier</small>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyReferralLink() {
            const linkInput = document.getElementById('referralLink');
            linkInput.select();
            document.execCommand('copy');
            
            // Show success message
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
            }, 2000);
        }
        
        function shareOnWhatsApp() {
            const link = document.getElementById('referralLink').value;
            const message = `Check out SmartSchool Uniforms! Get quality school uniforms at great prices. Use my referral link: ${link}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
        }
        
        function shareOnFacebook() {
            const link = document.getElementById('referralLink').value;
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`, '_blank');
        }
        
        function shareViaEmail() {
            const link = document.getElementById('referralLink').value;
            const subject = 'Check out SmartSchool Uniforms';
            const body = `Hi,\n\nI wanted to share SmartSchool Uniforms with you. They offer quality school uniforms at great prices.\n\nUse my referral link to get a discount on your first order: ${link}\n\nBest regards`;
            window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        }
    </script>
</body>
</html>
