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
    <title>Stock Alerts - SmartSchool Uniforms</title>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/zetech-theme.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .alerts-header {
            background: linear-gradient(135deg, var(--accent-green), var(--secondary-teal));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .alerts-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .alert-item {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent-green);
        }
        
        .alert-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .alert-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .alert-badge.in-stock {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-badge.low-stock {
            background: #fff3cd;
            color: #856404;
        }
        
        .alert-badge.out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-badge.price-drop {
            background: #cce5ff;
            color: #004085;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
        
        .alert-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .alert-btn {
            padding: 0.25rem 0.75rem;
            border: none;
            border-radius: 5px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .alert-btn.primary {
            background: var(--accent-green);
            color: white;
        }
        
        .alert-btn.secondary {
            background: var(--light-bg);
            color: var(--primary-color);
        }
        
        .alert-btn.danger {
            background: #dc3545;
            color: white;
        }
        
        .alert-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .create-alert {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .form-control:focus {
            border-color: var(--accent-green);
            box-shadow: 0 0 0 0.2rem rgba(56, 142, 60, 0.25);
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
            color: var(--accent-green);
            margin-bottom: 0.5rem;
        }
        
        .notification-settings {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .setting-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .form-switch .form-check-input {
            background-color: var(--accent-green);
            border-color: var(--accent-green);
        }
        
        .form-switch .form-check-input:checked {
            background-color: var(--accent-green);
            border-color: var(--accent-green);
        }
        
        .alert-history {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .history-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .history-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .product-selector {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 1rem;
        }
        
        .product-card {
            background: var(--light-bg);
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .product-card:hover {
            border-color: var(--accent-green);
            transform: translateY(-2px);
        }
        
        .product-card.selected {
            border-color: var(--accent-green);
            background: white;
        }
        
        .product-card img {
            width: 100%;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }
        
        .product-name {
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }
        
        .product-price {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="alerts-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-bell me-3"></i>Stock Alerts</h1>
            <p class="lead mb-0">Get notified when your favorite items are back in stock or on sale</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="alerts-section">
                    <h3 class="mb-4"><i class="fas fa-bell me-2"></i>Your Stock Alerts</h3>
                    
                    <div class="alert-item">
                        <div class="alert-badge in-stock">
                            <i class="fas fa-check me-1"></i>In Stock
                        </div>
                        <div class="product-info">
                            <img src="https://via.placeholder.com/60x60/FF6B35/FFFFFF?text=Shirt" alt="White School Shirt">
                            <div>
                                <h6 class="mb-1">White School Shirt - Size M</h6>
                                <small class="text-muted">Previously out of stock • Now available</small>
                            </div>
                        </div>
                        <div class="alert-actions">
                            <button class="alert-btn primary">
                                <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                            </button>
                            <button class="alert-btn secondary">
                                <i class="fas fa-eye me-1"></i>View Details
                            </button>
                            <button class="alert-btn danger">
                                <i class="fas fa-trash me-1"></i>Remove Alert
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert-item">
                        <div class="alert-badge price-drop">
                            <i class="fas fa-tag me-1"></i>Price Drop
                        </div>
                        <div class="product-info">
                            <img src="https://via.placeholder.com/60x60/00897B/FFFFFF?text=Trousers" alt="Grey School Trousers">
                            <div>
                                <h6 class="mb-1">Grey School Trousers - Size 32</h6>
                                <small class="text-muted">Price reduced from KSh 1,500 to KSh 1,200 • Save KSh 300</small>
                            </div>
                        </div>
                        <div class="alert-actions">
                            <button class="alert-btn primary">
                                <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                            </button>
                            <button class="alert-btn secondary">
                                <i class="fas fa-eye me-1"></i>View Details
                            </button>
                            <button class="alert-btn danger">
                                <i class="fas fa-trash me-1"></i>Remove Alert
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert-item">
                        <div class="alert-badge low-stock">
                            <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
                        </div>
                        <div class="product-info">
                            <img src="https://via.placeholder.com/60x60/1976D2/FFFFFF?text=Shoes" alt="Black School Shoes">
                            <div>
                                <h6 class="mb-1">Black School Shoes - Size 7</h6>
                                <small class="text-muted">Only 3 pairs left • Order soon</small>
                            </div>
                        </div>
                        <div class="alert-actions">
                            <button class="alert-btn primary">
                                <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                            </button>
                            <button class="alert-btn secondary">
                                <i class="fas fa-eye me-1"></i>View Details
                            </button>
                            <button class="alert-btn danger">
                                <i class="fas fa-trash me-1"></i>Remove Alert
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert-item">
                        <div class="alert-badge out-of-stock">
                            <i class="fas fa-times me-1"></i>Out of Stock
                        </div>
                        <div class="product-info">
                            <img src="https://via.placeholder.com/60x60/7B1FA2/FFFFFF?text=Sweater" alt="V-Neck Sweater">
                            <div>
                                <h6 class="mb-1">V-Neck Sweater - Size L</h6>
                                <small class="text-muted">Currently out of stock • We'll notify you when available</small>
                            </div>
                        </div>
                        <div class="alert-actions">
                            <button class="alert-btn secondary">
                                <i class="fas fa-eye me-1"></i>View Details
                            </button>
                            <button class="alert-btn danger">
                                <i class="fas fa-trash me-1"></i>Remove Alert
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="create-alert">
                    <h4 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Create New Alert</h4>
                    
                    <form id="alertForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Select Product</label>
                                    <div class="product-selector">
                                        <input type="text" class="form-control mb-2" placeholder="Search products..." id="productSearch">
                                        <div class="product-grid" id="productGrid">
                                            <!-- Products will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Alert Type</label>
                                    <select class="form-select" id="alertType">
                                        <option value="stock">Back in Stock</option>
                                        <option value="price">Price Drop</option>
                                        <option value="both">Both Stock & Price</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Price Threshold (Optional)</label>
                                    <input type="number" class="form-control" id="priceThreshold" placeholder="Alert when price drops below...">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notification Method</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                <label class="form-check-label" for="emailNotif">
                                    Email Notification
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="smsNotif">
                                <label class="form-check-label" for="smsNotif">
                                    SMS Notification
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pushNotif" checked>
                                <label class="form-check-label" for="pushNotif">
                                    Push Notification (Mobile App)
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-bell me-2"></i>Create Alert
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="alerts-section">
                    <h4 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Alert Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">12</div>
                            <h6>Active Alerts</h6>
                            <p class="small text-muted mb-0">Currently monitoring</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">8</div>
                            <h6>Notifications</h6>
                            <p class="small text-muted mb-0">Received this month</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">5</div>
                            <h6>Purchases</h6>
                            <p class="small text-muted mb-0">From alerts</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">KSh 1,500</div>
                            <h6>Total Saved</h6>
                            <p class="small text-muted mb-0">From price alerts</p>
                        </div>
                    </div>
                </div>
                
                <div class="notification-settings">
                    <h4 class="mb-3"><i class="fas fa-cog me-2"></i>Notification Settings</h4>
                    
                    <div class="setting-item">
                        <div>
                            <h6 class="mb-1">Email Notifications</h6>
                            <small class="text-muted">Receive alerts via email</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="emailSwitch" checked>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <div>
                            <h6 class="mb-1">SMS Notifications</h6>
                            <small class="text-muted">Get SMS alerts for urgent updates</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="smsSwitch">
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <div>
                            <h6 class="mb-1">Push Notifications</h6>
                            <small class="text-muted">Mobile app notifications</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pushSwitch" checked>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <div>
                            <h6 class="mb-1">Daily Summary</h6>
                            <small class="text-muted">Get daily digest of all alerts</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="summarySwitch">
                        </div>
                    </div>
                </div>
                
                <div class="alert-history">
                    <h4 class="mb-3"><i class="fas fa-history me-2"></i>Recent Activity</h4>
                    
                    <div class="history-item">
                        <div class="history-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">White Shirt Back in Stock</h6>
                            <small class="text-muted">2 hours ago • Added to cart</small>
                        </div>
                    </div>
                    
                    <div class="history-item">
                        <div class="history-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Price Drop on Trousers</h6>
                            <small class="text-muted">1 day ago • Saved KSh 300</small>
                        </div>
                    </div>
                    
                    <div class="history-item">
                        <div class="history-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Low Stock Alert</h6>
                            <small class="text-muted">3 days ago • Shoes running low</small>
                        </div>
                    </div>
                    
                    <div class="history-item">
                        <div class="history-icon">
                            <i class="fas fa-times"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Sweater Out of Stock</h6>
                            <small class="text-muted">1 week ago • Alert created</small>
                        </div>
                    </div>
                </div>
                
                <div class="alerts-section">
                    <h4 class="mb-3"><i class="fas fa-lightbulb me-2"></i>Alert Tips</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Smart Alerts</h6>
                        <p class="small mb-0">Set price thresholds to get notified only when prices drop below your target amount.</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-bolt me-2"></i>Quick Response</h6>
                        <p class="small mb-0">Popular items sell out fast when back in stock. Act quickly when you receive notifications.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-calendar me-2"></i>Seasonal Items</h6>
                        <p class="small mb-0">Set alerts for seasonal items before the school season begins for best availability.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample product data
        const products = [
            { id: 1, name: 'White School Shirt', price: 850, category: 'shirts', image: 'https://via.placeholder.com/150x60/FF6B35/FFFFFF?text=White+Shirt' },
            { id: 2, name: 'Blue School Shirt', price: 850, category: 'shirts', image: 'https://via.placeholder.com/150x60/00897B/FFFFFF?text=Blue+Shirt' },
            { id: 3, name: 'Grey School Trousers', price: 1200, category: 'trousers', image: 'https://via.placeholder.com/150x60/546E7A/FFFFFF?text=Grey+Trousers' },
            { id: 4, name: 'Black School Trousers', price: 1200, category: 'trousers', image: 'https://via.placeholder.com/150x60/000000/FFFFFF?text=Black+Trousers' },
            { id: 5, name: 'School Dress', price: 1100, category: 'dresses', image: 'https://via.placeholder.com/150x60/FF6B35/FFFFFF?text=School+Dress' },
            { id: 6, name: 'Black School Shoes', price: 1800, category: 'shoes', image: 'https://via.placeholder.com/150x60/000000/FFFFFF?text=Black+Shoes' },
            { id: 7, name: 'V-Neck Sweater', price: 950, category: 'sweaters', image: 'https://via.placeholder.com/150x60/7B1FA2/FFFFFF?text=V-Neck+Sweater' },
            { id: 8, name: 'School Bag', price: 1200, category: 'accessories', image: 'https://via.placeholder.com/150x60/1976D2/FFFFFF?text=School+Bag' }
        ];
        
        let selectedProduct = null;
        
        // Load products
        function loadProducts(search = '') {
            const productGrid = document.getElementById('productGrid');
            let filteredProducts = products;
            
            if (search) {
                filteredProducts = filteredProducts.filter(p => 
                    p.name.toLowerCase().includes(search.toLowerCase())
                );
            }
            
            productGrid.innerHTML = filteredProducts.map(product => `
                <div class="product-card ${selectedProduct?.id === product.id ? 'selected' : ''}" 
                     onclick="selectProduct(${product.id})">
                    <img src="${product.image}" alt="${product.name}">
                    <div class="product-name">${product.name}</div>
                    <div class="product-price">KSh ${product.price}</div>
                </div>
            `).join('');
        }
        
        // Select product
        function selectProduct(productId) {
            selectedProduct = products.find(p => p.id === productId);
            loadProducts(document.getElementById('productSearch').value);
        }
        
        // Search functionality
        document.getElementById('productSearch').addEventListener('input', function() {
            loadProducts(this.value);
        });
        
        // Form submission
        document.getElementById('alertForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedProduct) {
                alert('Please select a product for the alert');
                return;
            }
            
            // Here you would normally submit to backend
            alert(`Alert created for ${selectedProduct.name}! You'll be notified when conditions are met.`);
            
            // Reset form
            selectedProduct = null;
            this.reset();
            loadProducts();
        });
        
        // Initialize
        loadProducts();
    </script>
</body>
</html>


