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
    <title>Size Calculator - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: rgb(6, 25, 67);
            --primary-color-dark: rgb(6, 25, 67);
            --secondary-teal: #00897B;
            --secondary-blue: #1976D2;
            --accent-purple: #7B1FA2;
            --accent-green: #388E3C;
            --neutral-gray: #546E7A;
            --light-bg: #FFF8E1;
        }
        
        .calculator-header {
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-teal));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .calculator-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .measurement-input {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .size-result {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            display: none;
        }
        
        .size-recommendation {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .size-chart {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .size-chart table {
            margin-bottom: 0;
        }
        
        .size-chart th {
            background: var(--light-bg);
            color: var(--primary-color);
            font-weight: 600;
            border: none;
        }
        
        .size-chart td, .size-chart th {
            text-align: center;
            padding: 0.75rem;
            vertical-align: middle;
        }
        
        .recommended-size {
            background: var(--light-bg) !important;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .measurement-guide {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .measurement-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .step-number {
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
        
        .age-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .age-option {
            padding: 1rem 2rem;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .age-option:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .age-option.selected {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }
        
        .tips-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .tips-card i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
        
        .input-group-text {
            background: var(--light-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(6, 25, 67, 0.25);
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="calculator-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-ruler me-3"></i>Size Calculator</h1>
            <p class="lead mb-0">Find the perfect fit with our intelligent size calculator</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="calculator-section">
            <h3 class="mb-4"><i class="fas fa-calculator me-2"></i>Calculate Your Size</h3>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="measurement-input">
                        <h5 class="mb-3">Step 1: Select Product Type</h5>
                        <select class="form-select form-select-lg mb-4" id="productType">
                            <option value="">Choose Product Type</option>
                            <option value="shirts">Shirts & Blouses</option>
                            <option value="trousers">Trousers & Shorts</option>
                            <option value="dresses">Dresses & Skirts</option>
                            <option value="sweaters">Sweaters & Cardigans</option>
                            <option value="shoes">School Shoes</option>
                        </select>
                    </div>
                    
                    <div class="measurement-input">
                        <h5 class="mb-3">Step 2: Enter Measurements (in cm)</h5>
                        
                        <div id="shirtsMeasurements" class="measurements-group" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Chest</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="chest" placeholder="76">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Waist</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="waist" placeholder="64">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Height</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="height" placeholder="120">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="trousersMeasurements" class="measurements-group" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Waist</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="trousersWaist" placeholder="64">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Hip</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="hip" placeholder="74">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Inseam</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="inseam" placeholder="60">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="shoesMeasurements" class="measurements-group" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Foot Length</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="footLength" placeholder="22">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Age (Optional)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="shoeAge" placeholder="8">
                                        <span class="input-group-text">years</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="measurement-input">
                        <h5 class="mb-3">Step 3: Or Select by Age</h5>
                        <p class="text-muted mb-3">Quick size estimate based on age (less accurate than measurements)</p>
                        
                        <div class="age-selector">
                            <div class="age-option" data-age="3-4">
                                <div class="fw-bold">3-4 Years</div>
                                <small>Pre-School</small>
                            </div>
                            <div class="age-option" data-age="5-6">
                                <div class="fw-bold">5-6 Years</div>
                                <small>Kindergarten</small>
                            </div>
                            <div class="age-option" data-age="7-8">
                                <div class="fw-bold">7-8 Years</div>
                                <small>Class 1-2</small>
                            </div>
                            <div class="age-option" data-age="9-10">
                                <div class="fw-bold">9-10 Years</div>
                                <small>Class 3-4</small>
                            </div>
                            <div class="age-option" data-age="11-12">
                                <div class="fw-bold">11-12 Years</div>
                                <small>Class 5-6</small>
                            </div>
                            <div class="age-option" data-age="13-14">
                                <div class="fw-bold">13-14 Years</div>
                                <small>Class 7-8</small>
                            </div>
                            <div class="age-option" data-age="15-16">
                                <div class="fw-bold">15-16 Years</div>
                                <small>Form 1-2</small>
                            </div>
                            <div class="age-option" data-age="17-18">
                                <div class="fw-bold">17-18 Years</div>
                                <small>Form 3-4</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button class="btn btn-primary btn-lg" onclick="calculateSize()">
                            <i class="fas fa-calculator me-2"></i>Calculate Size
                        </button>
                    </div>
                    
                    <div class="size-result" id="sizeResult">
                        <h4 class="mb-3">Your Recommended Size</h4>
                        <div class="size-recommendation" id="recommendedSize">MEDIUM</div>
                        <p class="mb-3">Based on your measurements, we recommend this size for the best fit.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <small>Fit Type: Regular Fit</small>
                            </div>
                            <div class="col-md-6">
                                <small>Confidence: 85%</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="measurement-guide">
                        <h5 class="mb-3"><i class="fas fa-ruler-combined me-2"></i>How to Measure</h5>
                        
                        <div class="measurement-step">
                            <div class="step-number">1</div>
                            <div>
                                <h6>Use a Flexible Tape</h6>
                                <p class="small mb-0">Use a soft measuring tape for accurate measurements.</p>
                            </div>
                        </div>
                        
                        <div class="measurement-step">
                            <div class="step-number">2</div>
                            <div>
                                <h6>Stand Straight</h6>
                                <p class="small mb-0">Stand naturally with arms at sides for consistent measurements.</p>
                            </div>
                        </div>
                        
                        <div class="measurement-step">
                            <div class="step-number">3</div>
                            <div>
                                <h6>Measure Over Underwear</h6>
                                <p class="small mb-0">For best results, measure over light clothing or underwear.</p>
                            </div>
                        </div>
                        
                        <div class="measurement-step">
                            <div class="step-number">4</div>
                            <div>
                                <h6>Keep Tape Level</h6>
                                <p class="small mb-0">Ensure the measuring tape is parallel to the floor.</p>
                            </div>
                        </div>
                        
                        <div class="measurement-step">
                            <div class="step-number">5</div>
                            <div>
                                <h6>Don't Pull Too Tight</h6>
                                <p class="small mb-0">Keep the tape snug but not tight - you should be able to fit a finger underneath.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tips-card">
                        <h6><i class="fas fa-lightbulb me-2"></i>Pro Tips</h6>
                        <ul class="small mb-0">
                            <li>Measure twice for accuracy</li>
                            <li>Consider growth room for children</li>
                            <li>Check specific product size charts</li>
                            <li>When in doubt, size up for growing kids</li>
                        </ul>
                    </div>
                    
                    <div class="tips-card">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Common Mistakes</h6>
                        <ul class="small mb-0">
                            <li>Measuring over bulky clothing</li>
                            <li>Pulling the tape too tight</li>
                            <li>Not measuring at the right points</li>
                            <li>Using rigid measuring tapes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="calculator-section">
            <h3 class="mb-4"><i class="fas fa-table me-2"></i>Size Charts</h3>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="size-chart">
                        <h5 class="mb-3">Shirts & Blouses</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Chest (cm)</th>
                                        <th>Waist (cm)</th>
                                        <th>Height (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>XS</td>
                                        <td>70-76</td>
                                        <td>60-64</td>
                                        <td>110-120</td>
                                    </tr>
                                    <tr>
                                        <td>S</td>
                                        <td>76-82</td>
                                        <td>64-68</td>
                                        <td>120-130</td>
                                    </tr>
                                    <tr>
                                        <td>M</td>
                                        <td>82-88</td>
                                        <td>68-74</td>
                                        <td>130-140</td>
                                    </tr>
                                    <tr>
                                        <td>L</td>
                                        <td>88-94</td>
                                        <td>74-80</td>
                                        <td>140-150</td>
                                    </tr>
                                    <tr>
                                        <td>XL</td>
                                        <td>94-100</td>
                                        <td>80-86</td>
                                        <td>150-160</td>
                                    </tr>
                                    <tr>
                                        <td>XXL</td>
                                        <td>100-106</td>
                                        <td>86-92</td>
                                        <td>160-170</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="size-chart">
                        <h5 class="mb-3">Trousers & Shorts</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Waist (cm)</th>
                                        <th>Hip (cm)</th>
                                        <th>Inseam (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>28</td>
                                        <td>68-72</td>
                                        <td>78-82</td>
                                        <td>70</td>
                                    </tr>
                                    <tr>
                                        <td>30</td>
                                        <td>72-76</td>
                                        <td>82-86</td>
                                        <td>73</td>
                                    </tr>
                                    <tr>
                                        <td>32</td>
                                        <td>76-80</td>
                                        <td>86-90</td>
                                        <td>76</td>
                                    </tr>
                                    <tr>
                                        <td>34</td>
                                        <td>80-84</td>
                                        <td>90-94</td>
                                        <td>79</td>
                                    </tr>
                                    <tr>
                                        <td>36</td>
                                        <td>84-88</td>
                                        <td>94-98</td>
                                        <td>82</td>
                                    </tr>
                                    <tr>
                                        <td>38</td>
                                        <td>88-92</td>
                                        <td>98-102</td>
                                        <td>85</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="size-chart">
                        <h5 class="mb-3">School Shoes</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>EU Size</th>
                                        <th>UK Size</th>
                                        <th>Foot Length (cm)</th>
                                        <th>Age (Years)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>20</td>
                                        <td>20</td>
                                        <td>4</td>
                                        <td>12.3</td>
                                        <td>2-3</td>
                                    </tr>
                                    <tr>
                                        <td>25</td>
                                        <td>25</td>
                                        <td>8</td>
                                        <td>15.6</td>
                                        <td>4-5</td>
                                    </tr>
                                    <tr>
                                        <td>30</td>
                                        <td>30</td>
                                        <td>12</td>
                                        <td>18.8</td>
                                        <td>7-8</td>
                                    </tr>
                                    <tr>
                                        <td>35</td>
                                        <td>35</td>
                                        <td>2.5</td>
                                        <td>22.5</td>
                                        <td>10-11</td>
                                    </tr>
                                    <tr>
                                        <td>40</td>
                                        <td>40</td>
                                        <td>6.5</td>
                                        <td>25.4</td>
                                        <td>13-14</td>
                                    </tr>
                                    <tr>
                                        <td>45</td>
                                        <td>45</td>
                                        <td>10.5</td>
                                        <td>28.4</td>
                                        <td>16+</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="size-chart">
                        <h5 class="mb-3">Age-Based Quick Guide</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Age Range</th>
                                        <th>Shirt Size</th>
                                        <th>Trouser Size</th>
                                        <th>Shoe Size</th>
                                        <th>Height (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>3-4 years</td>
                                        <td>XS</td>
                                        <td>24</td>
                                        <td>22-23</td>
                                        <td>95-105</td>
                                    </tr>
                                    <tr>
                                        <td>5-6 years</td>
                                        <td>S</td>
                                        <td>26</td>
                                        <td>25-26</td>
                                        <td>105-115</td>
                                    </tr>
                                    <tr>
                                        <td>7-8 years</td>
                                        <td>M</td>
                                        <td>28</td>
                                        <td>28-29</td>
                                        <td>115-125</td>
                                    </tr>
                                    <tr>
                                        <td>9-10 years</td>
                                        <td>L</td>
                                        <td>30</td>
                                        <td>31-32</td>
                                        <td>125-135</td>
                                    </tr>
                                    <tr>
                                        <td>11-12 years</td>
                                        <td>XL</td>
                                        <td>32</td>
                                        <td>34-35</td>
                                        <td>135-145</td>
                                    </tr>
                                    <tr>
                                        <td>13-14 years</td>
                                        <td>XXL</td>
                                        <td>34</td>
                                        <td>37-38</td>
                                        <td>145-155</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Product type selection
        document.getElementById('productType').addEventListener('change', function() {
            // Hide all measurement groups
            document.querySelectorAll('.measurements-group').forEach(group => {
                group.style.display = 'none';
            });
            
            // Show relevant measurement group
            const selectedType = this.value;
            if (selectedType && document.getElementById(selectedType + 'Measurements')) {
                document.getElementById(selectedType + 'Measurements').style.display = 'block';
            }
        });
        
        // Age selection
        document.querySelectorAll('.age-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.age-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
        
        // Size calculation
        function calculateSize() {
            const productType = document.getElementById('productType').value;
            
            if (!productType) {
                alert('Please select a product type first.');
                return;
            }
            
            let recommendedSize = 'MEDIUM';
            let confidence = 85;
            
            // Calculate based on measurements
            if (productType === 'shirts') {
                const chest = document.getElementById('chest').value;
                const waist = document.getElementById('waist').value;
                const height = document.getElementById('height').value;
                
                if (chest && waist && height) {
                    if (height < 120) recommendedSize = 'XS';
                    else if (height < 130) recommendedSize = 'S';
                    else if (height < 140) recommendedSize = 'M';
                    else if (height < 150) recommendedSize = 'L';
                    else if (height < 160) recommendedSize = 'XL';
                    else recommendedSize = 'XXL';
                    
                    confidence = 90;
                } else {
                    // Check if age is selected
                    const selectedAge = document.querySelector('.age-option.selected');
                    if (selectedAge) {
                        const age = selectedAge.dataset.age;
                        if (age === '3-4' || age === '5-6') recommendedSize = 'XS';
                        else if (age === '7-8') recommendedSize = 'S';
                        else if (age === '9-10') recommendedSize = 'M';
                        else if (age === '11-12') recommendedSize = 'L';
                        else if (age === '13-14') recommendedSize = 'XL';
                        else recommendedSize = 'XXL';
                        
                        confidence = 75;
                    } else {
                        alert('Please enter measurements or select an age.');
                        return;
                    }
                }
            } else if (productType === 'trousers') {
                const trousersWaist = document.getElementById('trousersWaist').value;
                const hip = document.getElementById('hip').value;
                const inseam = document.getElementById('inseam').value;
                
                if (trousersWaist && hip && inseam) {
                    if (trousersWaist < 72) recommendedSize = '28';
                    else if (trousersWaist < 76) recommendedSize = '30';
                    else if (trousersWaist < 80) recommendedSize = '32';
                    else if (trousersWaist < 84) recommendedSize = '34';
                    else if (trousersWaist < 88) recommendedSize = '36';
                    else recommendedSize = '38';
                    
                    confidence = 90;
                } else {
                    const selectedAge = document.querySelector('.age-option.selected');
                    if (selectedAge) {
                        const age = selectedAge.dataset.age;
                        if (age === '3-4' || age === '5-6') recommendedSize = '24';
                        else if (age === '7-8') recommendedSize = '26';
                        else if (age === '9-10') recommendedSize = '28';
                        else if (age === '11-12') recommendedSize = '30';
                        else if (age === '13-14') recommendedSize = '32';
                        else recommendedSize = '34';
                        
                        confidence = 75;
                    } else {
                        alert('Please enter measurements or select an age.');
                        return;
                    }
                }
            } else if (productType === 'shoes') {
                const footLength = document.getElementById('footLength').value;
                const shoeAge = document.getElementById('shoeAge').value;
                
                if (footLength) {
                    if (footLength < 14) recommendedSize = '22';
                    else if (footLength < 16) recommendedSize = '25';
                    else if (footLength < 18) recommendedSize = '28';
                    else if (footLength < 20) recommendedSize = '31';
                    else if (footLength < 22) recommendedSize = '34';
                    else if (footLength < 24) recommendedSize = '37';
                    else recommendedSize = '40';
                    
                    confidence = 90;
                } else if (shoeAge) {
                    if (shoeAge < 4) recommendedSize = '22';
                    else if (shoeAge < 6) recommendedSize = '25';
                    else if (shoeAge < 8) recommendedSize = '28';
                    else if (shoeAge < 10) recommendedSize = '31';
                    else if (shoeAge < 12) recommendedSize = '34';
                    else if (shoeAge < 14) recommendedSize = '37';
                    else recommendedSize = '40';
                    
                    confidence = 75;
                } else {
                    alert('Please enter foot length or age.');
                    return;
                }
            } else {
                // For other product types, use age-based estimation
                const selectedAge = document.querySelector('.age-option.selected');
                if (selectedAge) {
                    const age = selectedAge.dataset.age;
                    if (age === '3-4' || age === '5-6') recommendedSize = 'XS';
                    else if (age === '7-8') recommendedSize = 'S';
                    else if (age === '9-10') recommendedSize = 'M';
                    else if (age === '11-12') recommendedSize = 'L';
                    else if (age === '13-14') recommendedSize = 'XL';
                    else recommendedSize = 'XXL';
                    
                    confidence = 70;
                } else {
                    alert('Please select an age for size estimation.');
                    return;
                }
            }
            
            // Show result
            document.getElementById('recommendedSize').textContent = recommendedSize;
            document.getElementById('sizeResult').style.display = 'block';
            
            // Scroll to result
            document.getElementById('sizeResult').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>


