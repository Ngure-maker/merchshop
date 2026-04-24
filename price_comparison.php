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
    <title>Price Comparison - SmartSchool Uniforms</title>
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
        
        .comparison-header {
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-teal));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .comparison-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .product-selector {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .comparison-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .comparison-table table {
            margin-bottom: 0;
        }
        
        .comparison-table th {
            background: var(--light-bg);
            color: var(--primary-dark);
            font-weight: 600;
            border: none;
            padding: 1rem;
            text-align: center;
        }
        
        .comparison-table td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #e9ecef;
        }
        
        .comparison-table tr:last-child td {
            border-bottom: none;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .price-cell {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .price-lowest {
            color: var(--accent-green);
        }
        
        .price-highest {
            color: #dc3545;
        }
        
        .rating {
            color: #ffc107;
        }
        
        .availability {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .availability.in-stock {
            background: #d4edda;
            color: #155724;
        }
        
        .availability.low-stock {
            background: #fff3cd;
            color: #856404;
        }
        
        .availability.out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        .feature-check {
            color: var(--accent-green);
            font-size: 1.2rem;
        }
        
        .feature-cross {
            color: #dc3545;
            font-size: 1.2rem;
        }
        
        .add-to-comparison {
            background: var(--secondary-blue);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .add-to-comparison:hover {
            background: var(--secondary-teal);
        }
        
        .remove-from-comparison {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .search-box input {
            padding-left: 2.5rem;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--neutral-gray);
        }
        
        .filter-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .filter-tag {
            background: var(--light-bg);
            color: var(--primary-dark);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .filter-tag:hover {
            border-color: var(--secondary-blue);
        }
        
        .filter-tag.active {
            background: var(--secondary-blue);
            color: white;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            max-height: 300px;
            overflow-y: auto;
            padding: 1rem;
        }
        
        .product-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            border-color: var(--secondary-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .product-card.selected {
            border-color: var(--secondary-blue);
            background: var(--light-bg);
        }
        
        .product-card img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }
        
        .product-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        
        .product-price {
            color: var(--primary-amber);
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .comparison-summary {
            background: linear-gradient(135deg, var(--accent-purple), var(--primary-amber));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .summary-stat {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .recommendation {
            background: white;
            border-left: 4px solid var(--accent-green);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .recommendation h6 {
            color: var(--accent-green);
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="comparison-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-balance-scale me-3"></i>Price Comparison</h1>
            <p class="lead mb-0">Compare prices and features across different products</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="comparison-section">
                    <h3 class="mb-4"><i class="fas fa-exchange-alt me-2"></i>Compare Products</h3>
                    
                    <div class="product-selector">
                        <h5 class="mb-3">Select Products to Compare</h5>
                        
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search products..." id="searchInput">
                        </div>
                        
                        <div class="filter-tags">
                            <div class="filter-tag active" data-category="all">All</div>
                            <div class="filter-tag" data-category="shirts">Shirts</div>
                            <div class="filter-tag" data-category="trousers">Trousers</div>
                            <div class="filter-tag" data-category="dresses">Dresses</div>
                            <div class="filter-tag" data-category="shoes">Shoes</div>
                        </div>
                        
                        <div class="product-grid" id="productGrid">
                            <!-- Products will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="comparison-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SmartSchool</th>
                                    <th>Competitor A</th>
                                    <th>Competitor B</th>
                                    <th>Competitor C</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>White School Shirt</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=Shirt" alt="Shirt" class="product-image me-3">
                                            <div>
                                                <div class="price-cell price-lowest">KSh 850</div>
                                                <small class="text-success">Lowest Price!</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/00897B/FFFFFF?text=Shirt" alt="Shirt" class="product-image me-3">
                                            <div>
                                                <div class="price-cell">KSh 950</div>
                                                <small class="text-muted">+100</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/1976D2/FFFFFF?text=Shirt" alt="Shirt" class="product-image me-3">
                                            <div>
                                                <div class="price-cell">KSh 1,100</div>
                                                <small class="text-muted">+250</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/7B1FA2/FFFFFF?text=Shirt" alt="Shirt" class="product-image me-3">
                                            <div>
                                                <div class="price-cell price-highest">KSh 1,250</div>
                                                <small class="text-muted">+400</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>Grey School Trousers</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/546E7A/FFFFFF?text=Trousers" alt="Trousers" class="product-image me-3">
                                            <div>
                                                <div class="price-cell price-lowest">KSh 1,200</div>
                                                <small class="text-success">Lowest Price!</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=Trousers" alt="Trousers" class="product-image me-3">
                                            <div>
                                                <div class="price-cell">KSh 1,350</div>
                                                <small class="text-muted">+150</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/00897B/FFFFFF?text=Trousers" alt="Trousers" class="product-image me-3">
                                            <div>
                                                <div class="price-cell">KSh 1,200</div>
                                                <small class="text-muted">Same price</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/1976D2/FFFFFF?text=Trousers" alt="Trousers" class="product-image me-3">
                                            <div>
                                                <div class="price-cell">KSh 1,450</div>
                                                <small class="text-muted">+250</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>Black School Shoes</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/000000/FFFFFF?text=Shoes" alt="Shoes" class="product-image me-3">
                                            <div>
                                                <div class="price-cell">KSh 1,800</div>
                                                <small class="text-muted">Standard</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/7B1FA2/FFFFFF?text=Shoes" alt="Shoes" class="product-image me-3">
                                            <div>
                                                <div class="price-cell price-lowest">KSh 1,750</div>
                                                <small class="text-success">-50</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=Shoes" alt="Shoes" class="product-image me-3">
                                            <div>
                                                <div class="price-cell">KSh 1,900</div>
                                                <small class="text-muted">+100</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/80x80/00897B/FFFFFF?text=Shoes" alt="Shoes" class="product-image me-3">
                                            <div>
                                                <div class="price-cell price-highest">KSh 2,100</div>
                                                <small class="text-muted">+300</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>Quality Rating</strong></td>
                                    <td>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <small class="text-muted">(5.0)</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <small class="text-muted">(4.0)</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <small class="text-muted">(4.2)</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <small class="text-muted">(3.5)</small>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>Availability</strong></td>
                                    <td>
                                        <span class="availability in-stock">In Stock</span>
                                    </td>
                                    <td>
                                        <span class="availability low-stock">Low Stock</span>
                                    </td>
                                    <td>
                                        <span class="availability in-stock">In Stock</span>
                                    </td>
                                    <td>
                                        <span class="availability out-of-stock">Out of Stock</span>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>Free Shipping</strong></td>
                                    <td>
                                        <i class="fas fa-check feature-check"></i>
                                    </td>
                                    <td>
                                        <i class="fas fa-times feature-cross"></i>
                                    </td>
                                    <td>
                                        <i class="fas fa-check feature-check"></i>
                                    </td>
                                    <td>
                                        <i class="fas fa-times feature-cross"></i>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>Return Policy</strong></td>
                                    <td>
                                        <i class="fas fa-check feature-check"></i>
                                        <small class="text-muted">30 days</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-check feature-check"></i>
                                        <small class="text-muted">14 days</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-check feature-check"></i>
                                        <small class="text-muted">21 days</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-times feature-cross"></i>
                                        <small class="text-muted">No returns</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>Loyalty Points</strong></td>
                                    <td>
                                        <i class="fas fa-check feature-check"></i>
                                        <small class="text-muted">Yes</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-times feature-cross"></i>
                                        <small class="text-muted">No</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-times feature-cross"></i>
                                        <small class="text-muted">No</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-times feature-cross"></i>
                                        <small class="text-muted">No</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="comparison-summary">
                    <h4 class="mb-4"><i class="fas fa-chart-pie me-2"></i>Comparison Summary</h4>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="summary-stat">
                                <div class="summary-number">3</div>
                                <div>Products</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-stat">
                                <div class="summary-number">4</div>
                                <div>Retailers</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-stat">
                                <div class="summary-number">KSh 850</div>
                                <div>Lowest Price</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-stat">
                                <div class="summary-number">KSh 2,100</div>
                                <div>Highest Price</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="recommendation">
                        <h6><i class="fas fa-trophy me-2"></i>Best Value: SmartSchool</h6>
                        <p class="small mb-0">SmartSchool offers the best overall value with competitive pricing, excellent quality, and superior customer service.</p>
                    </div>
                    
                    <div class="recommendation">
                        <h6><i class="fas fa-piggy-bank me-2"></i>Biggest Savings: KSh 400</h6>
                        <p class="small mb-0">Save up to KSh 400 per item by choosing SmartSchool over the most expensive competitor.</p>
                    </div>
                </div>
                
                <div class="comparison-section">
                    <h4 class="mb-3"><i class="fas fa-info-circle me-2"></i>Why Choose SmartSchool?</h4>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check me-2"></i>Price Guarantee</h6>
                        <p class="small mb-0">We match prices from authorized competitors and offer additional loyalty points.</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-truck me-2"></i>Free Shipping</h6>
                        <p class="small mb-0">Free shipping on orders over KSh 3,000. Most competitors charge extra.</p>
                    </div>
                    
                    <div class="alert alert-primary">
                        <h6><i class="fas fa-shield-alt me-2"></i>Quality Assurance</h6>
                        <p class="small mb-0">All products come with quality guarantee and 30-day return policy.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-award me-2"></i>Loyalty Rewards</h6>
                        <p class="small mb-0">Earn points on every purchase and redeem for discounts on future orders.</p>
                    </div>
                </div>
                
                <div class="comparison-section">
                    <h4 class="mb-3"><i class="fas fa-lightbulb me-2"></i>Comparison Tips</h4>
                    
                    <ul class="small">
                        <li class="mb-2">Look beyond price - consider quality, shipping, and return policies</li>
                        <li class="mb-2">Check customer reviews and ratings for real feedback</li>
                        <li class="mb-2">Factor in shipping costs when comparing prices</li>
                        <li class="mb-2">Consider loyalty programs and long-term benefits</li>
                        <li class="mb-2">Verify product authenticity and warranty coverage</li>
                        <li class="mb-2">Compare customer service and support options</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample product data
        const products = [
            { id: 1, name: 'White School Shirt', price: 850, category: 'shirts', image: 'https://via.placeholder.com/200x100/FF6B35/FFFFFF?text=White+Shirt' },
            { id: 2, name: 'Blue School Shirt', price: 850, category: 'shirts', image: 'https://via.placeholder.com/200x100/00897B/FFFFFF?text=Blue+Shirt' },
            { id: 3, name: 'Grey School Trousers', price: 1200, category: 'trousers', image: 'https://via.placeholder.com/200x100/546E7A/FFFFFF?text=Grey+Trousers' },
            { id: 4, name: 'Black School Trousers', price: 1200, category: 'trousers', image: 'https://via.placeholder.com/200x100/000000/FFFFFF?text=Black+Trousers' },
            { id: 5, name: 'School Dress', price: 1100, category: 'dresses', image: 'https://via.placeholder.com/200x100/FF6B35/FFFFFF?text=School+Dress' },
            { id: 6, name: 'Black School Shoes', price: 1800, category: 'shoes', image: 'https://via.placeholder.com/200x100/000000/FFFFFF?text=Black+Shoes' },
            { id: 7, name: 'Brown School Shoes', price: 1700, category: 'shoes', image: 'https://via.placeholder.com/200x100/8D6E63/FFFFFF?text=Brown+Shoes' },
            { id: 8, name: 'V-Neck Sweater', price: 950, category: 'sweaters', image: 'https://via.placeholder.com/200x100/7B1FA2/FFFFFF?text=V-Neck+Sweater' }
        ];
        
        let selectedProducts = [];
        let currentFilter = 'all';
        
        // Initialize product grid
        function loadProducts(filter = 'all', search = '') {
            const productGrid = document.getElementById('productGrid');
            let filteredProducts = products;
            
            // Apply category filter
            if (filter !== 'all') {
                filteredProducts = filteredProducts.filter(p => p.category === filter);
            }
            
            // Apply search filter
            if (search) {
                filteredProducts = filteredProducts.filter(p => 
                    p.name.toLowerCase().includes(search.toLowerCase())
                );
            }
            
            productGrid.innerHTML = filteredProducts.map(product => `
                <div class="product-card ${selectedProducts.includes(product.id) ? 'selected' : ''}" 
                     onclick="toggleProduct(${product.id})">
                    <img src="${product.image}" alt="${product.name}">
                    <div class="product-name">${product.name}</div>
                    <div class="product-price">KSh ${product.price}</div>
                    ${selectedProducts.includes(product.id) ? 
                        '<small class="text-success"><i class="fas fa-check me-1"></i>Selected</small>' : 
                        '<small class="text-muted">Click to add</small>'}
                </div>
            `).join('');
        }
        
        // Toggle product selection
        function toggleProduct(productId) {
            const index = selectedProducts.indexOf(productId);
            if (index > -1) {
                selectedProducts.splice(index, 1);
            } else {
                if (selectedProducts.length < 4) {
                    selectedProducts.push(productId);
                } else {
                    alert('You can compare up to 4 products at a time');
                    return;
                }
            }
            loadProducts(currentFilter, document.getElementById('searchInput').value);
        }
        
        // Filter tags
        document.querySelectorAll('.filter-tag').forEach(tag => {
            tag.addEventListener('click', function() {
                document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.category;
                loadProducts(currentFilter, document.getElementById('searchInput').value);
            });
        });
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            loadProducts(currentFilter, this.value);
        });
        
        // Initialize
        loadProducts();
    </script>
</body>
</html>
