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
    <title>Size Guide - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <style>
        /* Only unique styles not in zetech-theme.css */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-teal) 100%);
            color: white;
            padding: 4rem 0;
        }
        
        .size-chart {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .size-chart table {
            margin: 0;
        }
        
        .size-chart th {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
        }
        
        .size-chart td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        
        .size-chart tr:hover {
            background: var(--light-bg);
        }
        
        .size-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .size-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(6, 25, 67, 0.2);
            transform: translateY(-2px);
        }
        
        .size-card.selected {
            border-color: var(--primary-color);
            background: var(--light-bg);
        }
        
        .measurement-guide {
            background: linear-gradient(135deg, #f8f9fa, white);
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .measurement-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .step-icon {
            width: 30px;
            height: 30px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .size-tip {
            background: var(--light-bg);
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            border-radius: 0 8px 8px 0;
            margin-bottom: 1rem;
        }
        
        .age-group {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .age-group:hover {
            border-color: var(--primary-color);
            background: var(--light-bg);
        }
        
        .size-calculator {
            background: linear-gradient(135deg, var(--light-bg), white);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            padding: 2rem;
        }
        
        .fit-type {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .fit-type:hover {
            border-color: var(--primary-color);
        }
        
        .fit-type.selected {
            border-color: var(--primary-color);
            background: var(--light-bg);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-primary mb-3">
                    <i class="fas fa-ruler me-2"></i>Size Guide
                </h1>
                <p class="lead text-muted">Find the perfect fit with our comprehensive sizing charts and measurement guide</p>
            </div>

            <!-- Quick Size Finder -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-search me-2"></i>Quick Size Finder
                </h3>
                
                <div class="size-calculator">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Age Group</label>
                            <select class="form-select" id="ageGroup">
                                <option value="">Select Age</option>
                                <option value="toddler">Toddler (2-4 years)</option>
                                <option value="preschool">Preschool (4-6 years)</option>
                                <option value="primary">Primary (6-12 years)</option>
                                <option value="secondary">Secondary (13-18 years)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Height (cm)</label>
                            <input type="number" class="form-control" id="height" placeholder="Enter height">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Weight (kg)</label>
                            <input type="number" class="form-control" id="weight" placeholder="Enter weight">
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary" onclick="calculateSize()">
                            <i class="fas fa-calculator me-2"></i>Find My Size
                        </button>
                    </div>
                    <div id="sizeResult" class="mt-3 text-center" style="display: none;">
                        <div class="alert alert-success">
                            <h5 class="fw-bold">Recommended Size: <span id="recommendedSize">L</span></h5>
                            <small class="text-muted">Based on your measurements</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- School Uniform Size Charts -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-tshirt me-2"></i>School Uniform Size Charts
                </h3>

                <!-- Boys Uniforms -->
                <div class="size-chart">
                    <div class="p-3 bg-light border-bottom">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-male me-2 text-primary"></i>Boys Uniforms
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Age</th>
                                    <th>Height (cm)</th>
                                    <th>Chest (cm)</th>
                                    <th>Waist (cm)</th>
                                    <th>Hip (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>XS</strong></td>
                                    <td>4-5</td>
                                    <td>104-110</td>
                                    <td>56-58</td>
                                    <td>54-56</td>
                                    <td>58-60</td>
                                </tr>
                                <tr>
                                    <td><strong>S</strong></td>
                                    <td>6-7</td>
                                    <td>116-122</td>
                                    <td>60-62</td>
                                    <td>56-58</td>
                                    <td>62-64</td>
                                </tr>
                                <tr>
                                    <td><strong>M</strong></td>
                                    <td>8-9</td>
                                    <td>128-134</td>
                                    <td>66-68</td>
                                    <td>60-62</td>
                                    <td>68-70</td>
                                </tr>
                                <tr>
                                    <td><strong>L</strong></td>
                                    <td>10-11</td>
                                    <td>140-146</td>
                                    <td>72-74</td>
                                    <td>64-66</td>
                                    <td>74-76</td>
                                </tr>
                                <tr>
                                    <td><strong>XL</strong></td>
                                    <td>12-13</td>
                                    <td>152-158</td>
                                    <td>78-80</td>
                                    <td>68-70</td>
                                    <td>80-82</td>
                                </tr>
                                <tr>
                                    <td><strong>XXL</strong></td>
                                    <td>14-15</td>
                                    <td>164-170</td>
                                    <td>84-86</td>
                                    <td>72-74</td>
                                    <td>86-88</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Girls Uniforms -->
                <div class="size-chart">
                    <div class="p-3 bg-light border-bottom">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-female me-2 text-primary"></i>Girls Uniforms
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Age</th>
                                    <th>Height (cm)</th>
                                    <th>Chest (cm)</th>
                                    <th>Waist (cm)</th>
                                    <th>Hip (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>XS</strong></td>
                                    <td>4-5</td>
                                    <td>104-110</td>
                                    <td>54-56</td>
                                    <td>52-54</td>
                                    <td>56-58</td>
                                </tr>
                                <tr>
                                    <td><strong>S</strong></td>
                                    <td>6-7</td>
                                    <td>116-122</td>
                                    <td>58-60</td>
                                    <td>54-56</td>
                                    <td>60-62</td>
                                </tr>
                                <tr>
                                    <td><strong>M</strong></td>
                                    <td>8-9</td>
                                    <td>128-134</td>
                                    <td>64-66</td>
                                    <td>58-60</td>
                                    <td>66-68</td>
                                </tr>
                                <tr>
                                    <td><strong>L</strong></td>
                                    <td>10-11</td>
                                    <td>140-146</td>
                                    <td>70-72</td>
                                    <td>62-64</td>
                                    <td>72-74</td>
                                </tr>
                                <tr>
                                    <td><strong>XL</strong></td>
                                    <td>12-13</td>
                                    <td>152-158</td>
                                    <td>76-78</td>
                                    <td>66-68</td>
                                    <td>78-80</td>
                                </tr>
                                <tr>
                                    <td><strong>XXL</strong></td>
                                    <td>14-15</td>
                                    <td>164-170</td>
                                    <td>82-84</td>
                                    <td>70-72</td>
                                    <td>84-86</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- How to Measure -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-tape me-2"></i>How to Measure Correctly
                </h3>

                <div class="row">
                    <div class="col-md-6">
                        <div class="measurement-guide">
                            <h6 class="fw-bold mb-3">Step-by-Step Guide</h6>
                            
                            <div class="measurement-step">
                                <div class="step-icon">1</div>
                                <div>
                                    <h6 class="fw-bold">Height</h6>
                                    <p class="small text-muted mb-0">Stand straight against a wall. Measure from the top of the head to the floor without shoes.</p>
                                </div>
                            </div>

                            <div class="measurement-step">
                                <div class="step-icon">2</div>
                                <div>
                                    <h6 class="fw-bold">Chest</h6>
                                    <p class="small text-muted mb-0">Measure around the fullest part of the chest, under the arms, keeping the tape horizontal.</p>
                                </div>
                            </div>

                            <div class="measurement-step">
                                <div class="step-icon">3</div>
                                <div>
                                    <h6 class="fw-bold">Waist</h6>
                                    <p class="small text-muted mb-0">Measure around the natural waistline, keeping the tape comfortably loose.</p>
                                </div>
                            </div>

                            <div class="measurement-step">
                                <div class="step-icon">4</div>
                                <div>
                                    <h6 class="fw-bold">Hips</h6>
                                    <p class="small text-muted mb-0">Measure around the fullest part of the hips, keeping feet together.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="measurement-guide">
                            <h6 class="fw-bold mb-3">Pro Tips</h6>
                            
                            <div class="size-tip">
                                <h6 class="fw-bold text-primary"><i class="fas fa-lightbulb me-2"></i>Use a Flexible Tape</h6>
                                <p class="small mb-0">A soft measuring tape works best for accurate measurements.</p>
                            </div>

                            <div class="size-tip">
                                <h6 class="fw-bold text-primary"><i class="fas fa-lightbulb me-2"></i>Measure Over Underwear</h6>
                                <p class="small mb-0">For best results, measure while wearing minimal clothing.</p>
                            </div>

                            <div class="size-tip">
                                <h6 class="fw-bold text-primary"><i class="fas fa-lightbulb me-2"></i>Keep Tape Level</h6>
                                <p class="small mb-0">Ensure the measuring tape is parallel to the floor.</p>
                            </div>

                            <div class="size-tip">
                                <h6 class="fw-bold text-primary"><i class="fas fa-lightbulb me-2"></i>Don't Pull Too Tight</h6>
                                <p class="small mb-0">The tape should be snug but not constricting.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fit Preferences -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-user-cog me-2"></i>Fit Preferences
                </h3>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="fit-type">
                            <i class="fas fa-compress fa-2x text-primary mb-2"></i>
                            <h6 class="fw-bold">Slim Fit</h6>
                            <p class="small text-muted">Closer to body, modern look</p>
                            <small class="text-primary">Choose one size smaller</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="fit-type selected">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h6 class="fw-bold">Regular Fit</h6>
                            <p class="small text-muted">Standard comfortable fit</p>
                            <small class="text-success">Recommended</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="fit-type">
                            <i class="fas fa-expand fa-2x text-info mb-2"></i>
                            <h6 class="fw-bold">Relaxed Fit</h6>
                            <p class="small text-muted">Roomier for growth</p>
                            <small class="text-info">Choose one size larger</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Size by Age -->
            <div class="mb-5">
                <h3 class="text-primary mb-4">
                    <i class="fas fa-child me-2"></i>General Size by Age
                </h3>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Primary School (Ages 6-12)</h6>
                        <div class="age-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Class 1-2 (Age 6-7)</span>
                                <span class="badge bg-primary">Size S</span>
                            </div>
                        </div>
                        <div class="age-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Class 3-4 (Age 8-9)</span>
                                <span class="badge bg-primary">Size M</span>
                            </div>
                        </div>
                        <div class="age-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Class 5-6 (Age 10-11)</span>
                                <span class="badge bg-primary">Size L</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Secondary School (Ages 13-18)</h6>
                        <div class="age-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Form 1-2 (Age 13-14)</span>
                                <span class="badge bg-primary">Size XL</span>
                            </div>
                        </div>
                        <div class="age-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Form 3-4 (Age 15-16)</span>
                                <span class="badge bg-primary">Size XXL</span>
                            </div>
                        </div>
                        <div class="age-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Advanced Level (Age 17-18)</span>
                                <span class="badge bg-primary">Size XXXL</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still Unsure -->
            <div class="text-center">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">
                            <i class="fas fa-question-circle me-2"></i>Still Unsure About Your Size?
                        </h4>
                        <p class="mb-4">Our sizing experts are here to help you find the perfect fit</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="tel:0712345678" class="btn btn-light">
                                <i class="fas fa-phone me-2"></i>Call for Help
                            </a>
                            <a href="contact.php" class="btn btn-light">
                                <i class="fas fa-envelope me-2"></i>Email Support
                            </a>
                            <button class="btn btn-light" onclick="scheduleFitting()">
                                <i class="fas fa-calendar me-2"></i>Schedule Fitting
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function calculateSize() {
    const ageGroup = document.getElementById('ageGroup').value;
    const height = document.getElementById('height').value;
    const weight = document.getElementById('weight').value;
    
    if (!ageGroup || !height || !weight) {
        alert('Please fill in all fields to calculate your size');
        return;
    }
    
    let recommendedSize = 'M';
    
    // Simple size calculation logic
    if (ageGroup === 'toddler') {
        recommendedSize = 'XS';
    } else if (ageGroup === 'preschool') {
        recommendedSize = 'S';
    } else if (ageGroup === 'primary') {
        if (height < 130) {
            recommendedSize = 'S';
        } else if (height < 145) {
            recommendedSize = 'M';
        } else {
            recommendedSize = 'L';
        }
    } else if (ageGroup === 'secondary') {
        if (height < 160) {
            recommendedSize = 'L';
        } else if (height < 170) {
            recommendedSize = 'XL';
        } else {
            recommendedSize = 'XXL';
        }
    }
    
    document.getElementById('recommendedSize').textContent = recommendedSize;
    document.getElementById('sizeResult').style.display = 'block';
}

function scheduleFitting() {
    const phone = '0712345678';
    const waPhone = '254712345678';
    const message = encodeURIComponent('Hi, I would like to schedule a fitting appointment.');
    const waUrl = `https://wa.me/${waPhone}?text=${message}`;
    const opened = window.open(waUrl, '_blank', 'noopener,noreferrer');
    if (!opened) {
        window.location.href = `tel:${phone}`;
    }
}

// Fit type selection
document.querySelectorAll('.fit-type').forEach(fit => {
    fit.addEventListener('click', function() {
        document.querySelectorAll('.fit-type').forEach(f => f.classList.remove('selected'));
        this.classList.add('selected');
    });
});
</script>
</body>
</html>


