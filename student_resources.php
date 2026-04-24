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
    <title>Student Resources - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .student-header {
            background: linear-gradient(135deg, var(--secondary-teal), var(--accent-green));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .student-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .resource-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .resource-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .resource-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--secondary-teal);
            margin-right: 1rem;
        }
        
        .resource-title {
            flex-grow: 1;
        }
        
        .resource-title h5 {
            color: var(--secondary-teal);
            margin-bottom: 0.25rem;
        }
        
        .resource-title p {
            color: var(--neutral-gray);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .guide-steps {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .step-item {
            display: flex;
            align-items: flex-start;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .step-item:last-child {
            border-bottom: none;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background: var(--secondary-teal);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .step-content {
            flex-grow: 1;
        }
        
        .step-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .step-description {
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }
        
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .tip-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .tip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .tip-icon {
            width: 60px;
            height: 60px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--secondary-teal);
            margin: 0 auto 1rem;
        }
        
        .activity-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .activity-card {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-green);
        }
        
        .activity-card h6 {
            color: var(--accent-green);
            margin-bottom: 0.5rem;
        }
        
        .checklist {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .checklist-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .checklist-item:last-child {
            border-bottom: none;
        }
        
        .checklist-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid var(--accent-green);
            border-radius: 4px;
            margin-right: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-green);
            cursor: pointer;
        }
        
        .checklist-checkbox.checked {
            background: var(--accent-green);
            color: white;
        }
        
        .style-showcase {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .style-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .style-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
        
        .style-details {
            flex-grow: 1;
        }
        
        .style-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .style-description {
            font-size: 0.9rem;
            color: var(--neutral-gray);
        }
        
        .quiz-section {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .quiz-question {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .quiz-options {
            display: grid;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .quiz-option {
            background: rgba(255,255,255,0.2);
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quiz-option:hover {
            border-color: white;
            background: rgba(255,255,255,0.3);
        }
        
        .download-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .download-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .download-item:last-child {
            border-bottom: none;
        }
        
        .download-btn {
            background: var(--secondary-teal);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .download-btn:hover {
            background: var(--accent-green);
        }
        
        .video-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .video-card {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .video-thumbnail {
            width: 100%;
            height: 120px;
            background: var(--secondary-teal);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .video-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .video-duration {
            font-size: 0.8rem;
            color: var(--neutral-gray);
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
            color: var(--secondary-teal);
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="student-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-graduation-cap me-3"></i>Student Resources</h1>
            <p class="lead mb-0">Everything students need to know about their school uniforms</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="student-section">
                    <h3 class="mb-4">Uniform Care & Maintenance</h3>
                    
                    <div class="resource-card">
                        <div class="resource-header">
                            <div class="resource-icon">
                                <i class="fas fa-soap"></i>
                            </div>
                            <div class="resource-title">
                                <h5>Daily Uniform Care</h5>
                                <p>Keep your uniform looking great every day</p>
                            </div>
                        </div>
                        
                        <div class="guide-steps">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <div class="step-title">Hang After School</div>
                                    <div class="step-description">Hang your uniform as soon as you get home to prevent wrinkles</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <div class="step-title">Check for Stains</div>
                                    <div class="step-description">Look for any stains or dirt and treat them immediately</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <div class="step-title">Air Out</div>
                                    <div class="step-description">Let your uniform air out overnight to freshen it up</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <div class="step-title">Prepare for Tomorrow</div>
                                    <div class="step-description">Lay out your clean uniform the night before school</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-header">
                            <div class="resource-icon">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="resource-title">
                                <h5>Washing Your Uniform</h5>
                                <p>Simple steps for clean uniforms</p>
                            </div>
                        </div>
                        
                        <div class="guide-steps">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <div class="step-title">Empty Pockets</div>
                                    <div class="step-description">Check all pockets for pens, notes, or other items</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <div class="step-title">Use Cold Water</div>
                                    <div class="step-description">Wash in cold water to protect colors and fabric</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <div class="step-title">Mild Detergent</div>
                                    <div class="step-description">Use a small amount of mild, color-safe detergent</div>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <div class="step-title">Dry Properly</div>
                                    <div class="step-description">Hang to dry or use low heat in the dryer</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="activity-section">
                    <h3 class="mb-4">Fun Uniform Activities</h3>
                    
                    <div class="activity-card">
                        <h6><i class="fas fa-palette me-2"></i>Decorate Your Name Tags</h6>
                        <p>Personalize your uniform name tags with creative designs that follow school rules. Use fabric markers or iron-on patches to make your uniform unique while staying within dress code.</p>
                    </div>
                    
                    <div class="activity-card">
                        <h6><i class="fas fa-camera me-2"></i>Uniform Fashion Show</h6>
                        <p>Organize a class uniform fashion show to showcase proper wearing techniques and creative accessories that complement your school uniform.</p>
                    </div>
                    
                    <div class="activity-card">
                        <h6><i class="fas fa-medal me-2"></i>Uniform Care Challenge</h6>
                        <p>Compete with classmates to see who can keep their uniform in the best condition throughout the term. Track your progress and earn points for proper care.</p>
                    </div>
                    
                    <div class="activity-card">
                        <h6><i class="fas fa-book me-2"></i>Uniform Journal</h6>
                        <p>Keep a journal about your uniform experiences, including care tips, favorite memories, and how your uniform makes you feel confident at school.</p>
                    </div>
                </div>
                
                <div class="style-showcase">
                    <h3 class="mb-4">Uniform Style Guide</h3>
                    
                    <div class="style-item">
                        <img src="https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=Shirt" alt="Proper Shirt" class="style-image">
                        <div class="style-details">
                            <div class="style-name">Perfect Shirt Wear</div>
                            <div class="style-description">Tucked in, buttoned properly, clean and pressed. Collar should lay flat and be comfortable.</div>
                        </div>
                    </div>
                    
                    <div class="style-item">
                        <img src="https://via.placeholder.com/80x80/546E7A/FFFFFF?text=Trousers" alt="Proper Trousers" class="style-image">
                        <div class="style-details">
                            <div class="style-name">Smart Trousers</div>
                            <div class="style-description">Worn at waist length, properly hemmed, clean and pressed. Belt should be visible and neat.</div>
                        </div>
                    </div>
                    
                    <div class="style-item">
                        <img src="https://via.placeholder.com/80x80/000000/FFFFFF?text=Shoes" alt="Proper Shoes" class="style-image">
                        <div class="style-details">
                            <div class="style-name">Polished Shoes</div>
                            <div class="style-description">Clean, polished, and in good condition. Laces should be tied properly and neatly.</div>
                        </div>
                    </div>
                    
                    <div class="style-item">
                        <img src="https://via.placeholder.com/80x80/7B1FA2/FFFFFF?text=Accessories" alt="Accessories" class="style-image">
                        <div class="style-details">
                            <div class="style-name">Appropriate Accessories</div>
                            <div class="style-description">Simple, school-approved accessories only. Watches, simple jewelry, and school badges.</div>
                        </div>
                    </div>
                </div>
                
                <div class="quiz-section">
                    <h3 class="mb-4"><i class="fas fa-gamepad me-2"></i>Uniform Quiz</h3>
                    
                    <div class="quiz-question">
                        <h6>Question 1: What temperature should you wash your uniform in?</h6>
                        <div class="quiz-options">
                            <div class="quiz-option" onclick="checkAnswer(this, true)">
                                A) Cold water
                            </div>
                            <div class="quiz-option" onclick="checkAnswer(this, false)">
                                B) Hot water
                            </div>
                            <div class="quiz-option" onclick="checkAnswer(this, false)">
                                C) Warm water
                            </div>
                            <div class="quiz-option" onclick="checkAnswer(this, false)">
                                D) Boiling water
                            </div>
                        </div>
                    </div>
                    
                    <div class="quiz-question">
                        <h6>Question 2: How often should you check for stains?</h6>
                        <div class="quiz-options">
                            <div class="quiz-option" onclick="checkAnswer(this, false)">
                                A) Once a week
                            </div>
                            <div class="quiz-option" onclick="checkAnswer(this, true)">
                                B) Every day after school
                            </div>
                            <div class="quiz-option" onclick="checkAnswer(this, false)">
                                C) Only when you see them
                            </div>
                            <div class="quiz-option" onclick="checkAnswer(this, false)">
                                D) Never
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="video-section">
                    <h3 class="mb-4">Video Tutorials</h3>
                    
                    <div class="video-grid">
                        <div class="video-card">
                            <div class="video-thumbnail">
                                <i class="fas fa-play"></i>
                            </div>
                            <div class="video-title">How to Fold Your Shirt</div>
                            <div class="video-duration">3:45</div>
                        </div>
                        
                        <div class="video-card">
                            <div class="video-thumbnail">
                                <i class="fas fa-play"></i>
                            </div>
                            <div class="video-title">Shoe Polishing Tips</div>
                            <div class="video-duration">5:20</div>
                        </div>
                        
                        <div class="video-card">
                            <div class="video-thumbnail">
                                <i class="fas fa-play"></i>
                            </div>
                            <div class="video-title">Stain Removal Magic</div>
                            <div class="video-duration">4:15</div>
                        </div>
                        
                        <div class="video-card">
                            <div class="video-thumbnail">
                                <i class="fas fa-play"></i>
                            </div>
                            <div class="video-title">Uniform Style Guide</div>
                            <div class="video-duration">6:30</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="student-section">
                    <h4 class="mb-3">Student Stats</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">95%</div>
                            <h6>Students Love</h6>
                            <p class="small text-muted mb-0">Their uniform style</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">87%</div>
                            <h6>Care for Uniforms</h6>
                            <p class="small text-muted mb-0">Properly daily</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">4.5</div>
                            <h6>Average Sets</h6>
                            <p class="small text-muted mb-0">Per student</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">92%</div>
                            <h6>Feel Confident</h6>
                            <p class="small text-muted mb-0">In uniform</p>
                        </div>
                    </div>
                </div>
                
                <div class="student-section">
                    <h4 class="mb-3">Daily Checklist</h4>
                    
                    <div class="checklist">
                        <h6 class="mb-3">Morning Routine</h6>
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleCheck(this)"></div>
                            <div>Uniform is clean and pressed</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleCheck(this)"></div>
                            <div>Shoes are polished and clean</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleCheck(this)"></div>
                            <div>All buttons are secure</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleCheck(this)"></div>
                            <div>Name tag is visible</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleCheck(this)"></div>
                            <div>Hair is neat and tidy</div>
                        </div>
                    </div>
                    
                    <div class="checklist">
                        <h6 class="mb-3">After School</h6>
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleCheck(this)"></div>
                            <div>Hang uniform properly</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleCheck(this)"></div>
                            <div>Check for stains or damage</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleCheck(this)"></div>
                            <div>Air out shoes</div>
                        </div>
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleCheck(this)"></div>
                            <div>Prepare for tomorrow</div>
                        </div>
                    </div>
                </div>
                
                <div class="student-section">
                    <h4 class="mb-3">Quick Tips</h4>
                    
                    <div class="tips-grid">
                        <div class="tip-card">
                            <div class="tip-icon">
                                <i class="fas fa-magic"></i>
                            </div>
                            <h6>Magic Stain Remover</h6>
                            <p class="small mb-0">Mix baking soda with water for tough stains!</p>
                        </div>
                        
                        <div class="tip-card">
                            <div class="tip-icon">
                                <i class="fas fa-wind"></i>
                            </div>
                            <h6>Wrinkle-Free Trick</h6>
                            <p class="small mb-0">Hang in bathroom while showering for steam!</p>
                        </div>
                        
                        <div class="tip-card">
                            <div class="tip-icon">
                                <i class="fas fa-shoe-prints"></i>
                            </div>
                            <h6>Shine Shoes Fast</h6>
                            <p class="small mb-0">Use banana peel for quick shoe shine!</p>
                        </div>
                        
                        <div class="tip-card">
                            <div class="tip-icon">
                                <i class="fas fa-spray-can"></i>
                            </div>
                            <h6>Freshen Up</h6>
                            <p class="small mb-0">Spray with fabric freshener between washes!</p>
                        </div>
                    </div>
                </div>
                
                <div class="student-section">
                    <h4 class="mb-3">Downloadable Resources</h4>
                    
                    <div class="download-section">
                        <div class="download-item">
                            <div>
                                <div class="fw-bold">Student Care Guide</div>
                                <small class="text-muted">PDF • Illustrated guide</small>
                            </div>
                            <button class="download-btn">
                                <i class="fas fa-download me-1"></i>Download
                            </button>
                        </div>
                        
                        <div class="download-item">
                            <div>
                                <div class="fw-bold">Uniform Checklist</div>
                                <small class="text-muted">PDF • Daily checklist</small>
                            </div>
                            <button class="download-btn">
                                <i class="fas fa-download me-1"></i>Download
                            </button>
                        </div>
                        
                        <div class="download-item">
                            <div>
                                <div class="fw-bold">Style Guide</div>
                                <small class="text-muted">PDF • How to wear properly</small>
                            </div>
                            <button class="download-btn">
                                <i class="fas fa-download me-1"></i>Download
                            </button>
                        </div>
                        
                        <div class="download-item">
                            <div>
                                <div class="fw-bold">Activity Book</div>
                                <small class="text-muted">PDF • Fun activities</small>
                            </div>
                            <button class="download-btn">
                                <i class="fas fa-download me-1"></i>Download
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="student-section">
                    <h4 class="mb-3">Student Corner</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-trophy me-2"></i>Uniform Champion</h6>
                        <p class="small mb-0">Become this month's Uniform Champion by showing excellent care and style!</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-camera me-2"></i>Photo Contest</h6>
                        <p class="small mb-0">Share your best uniform photos and win prizes!</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-users me-2"></i>Student Club</h6>
                        <p class="small mb-0">Join our Uniform Care Club for exclusive tips and activities!</p>
                    </div>
                </div>
                
                <div class="student-section">
                    <h4 class="mb-3">Helpful Links</h4>
                    
                    <div class="list-group">
                        <a href="size_calculator.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-calculator me-2"></i>
                            Size Calculator
                        </a>
                        <a href="uniform_guide.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-book-open me-2"></i>
                            Uniform Guide
                        </a>
                        <a href="care_instructions.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-soap me-2"></i>
                            Care Instructions
                        </a>
                        <a href="parent_resources.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i>
                            Parent Resources
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCheck(checkbox) {
            checkbox.classList.toggle('checked');
            if (checkbox.classList.contains('checked')) {
                checkbox.innerHTML = '<i class="fas fa-check"></i>';
            } else {
                checkbox.innerHTML = '';
            }
        }
        
        function checkAnswer(option, isCorrect) {
            // Remove previous selections
            option.parentElement.querySelectorAll('.quiz-option').forEach(opt => {
                opt.style.background = 'rgba(255,255,255,0.2)';
                opt.style.borderColor = 'transparent';
            });
            
            if (isCorrect) {
                option.style.background = 'rgba(56, 142, 60, 0.3)';
                option.style.borderColor = '#388E3C';
                option.innerHTML += ' ✓ Correct!';
            } else {
                option.style.background = 'rgba(244, 67, 54, 0.3)';
                option.style.borderColor = '#F44336';
                option.innerHTML += ' ✗ Try again!';
            }
        }
    </script>
</body>
</html>


