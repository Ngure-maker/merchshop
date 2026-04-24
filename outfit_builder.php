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
    <title>Outfit Builder - SmartSchool Uniforms</title>
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
        
        .builder-header {
            background: linear-gradient(135deg, var(--secondary-blue), var(--secondary-teal));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .builder-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .outfit-canvas {
            background: white;
            border: 2px dashed #e9ecef;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .outfit-preview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .outfit-item {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .outfit-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .outfit-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .remove-item {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-amber);
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
        
        .category-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .category-tab {
            padding: 0.75rem 1.5rem;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .category-tab:hover {
            border-color: var(--secondary-blue);
            transform: translateY(-2px);
        }
        
        .category-tab.active {
            background: var(--secondary-blue);
            color: white;
            border-color: var(--secondary-blue);
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 1rem;
        }
        
        .product-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .product-price {
            color: var(--primary-amber);
            font-weight: bold;
        }
        
        .add-to-outfit {
            background: var(--secondary-blue);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .add-to-outfit:hover {
            background: var(--secondary-teal);
        }
        
        .outfit-summary {
            background: linear-gradient(135deg, var(--accent-purple), var(--primary-amber));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .price-breakdown {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .total-price {
            font-size: 1.5rem;
            font-weight: bold;
            border-top: 2px solid white;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .saved-outfits {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .saved-outfit {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .saved-outfit:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .outfit-thumbnail {
            display: flex;
            gap: 0.5rem;
            margin-right: 1rem;
        }
        
        .outfit-thumbnail img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="builder-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-palette me-3"></i>Outfit Builder</h1>
            <p class="lead mb-0">Create the perfect uniform outfit for your school needs</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="builder-section">
                    <h3 class="mb-4"><i class="fas fa-tshirt me-2"></i>Build Your Outfit</h3>
                    
                    <div class="outfit-canvas">
                        <div id="emptyOutfit">
                            <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Start Building Your Outfit</h4>
                            <p class="text-muted">Select items from the categories below to create your complete uniform</p>
                        </div>
                        
                        <div class="outfit-preview" id="outfitPreview" style="display: none;">
                            <!-- Outfit items will be added here dynamically -->
                        </div>
                    </div>
                    
                    <div class="category-tabs">
                        <div class="category-tab active" data-category="shirts">
                            <i class="fas fa-tshirt me-2"></i>Shirts
                        </div>
                        <div class="category-tab" data-category="trousers">
                            <i class="fas fa-socks me-2"></i>Trousers
                        </div>
                        <div class="category-tab" data-category="dresses">
                            <i class="fas fa-user-tie me-2"></i>Dresses
                        </div>
                        <div class="category-tab" data-category="sweaters">
                            <i class="fas fa-mitten me-2"></i>Sweaters
                        </div>
                        <div class="category-tab" data-category="shoes">
                            <i class="fas fa-shoe-prints me-2"></i>Shoes
                        </div>
                        <div class="category-tab" data-category="accessories">
                            <i class="fas fa-bow-tie me-2"></i>Accessories
                        </div>
                    </div>
                    
                    <div class="product-grid" id="productGrid">
                        <!-- Products will be loaded here based on selected category -->
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="outfit-summary">
                    <h4 class="mb-3"><i class="fas fa-shopping-bag me-2"></i>Outfit Summary</h4>
                    
                    <div id="outfitItems">
                        <p class="text-center">No items selected yet</p>
                    </div>
                    
                    <div class="price-breakdown">
                        <span>Subtotal:</span>
                        <span id="subtotal">KSh 0</span>
                    </div>
                    
                    <div class="price-breakdown">
                        <span>Tax (16%):</span>
                        <span id="tax">KSh 0</span>
                    </div>
                    
                    <div class="price-breakdown">
                        <span>Shipping:</span>
                        <span id="shipping">KSh 250</span>
                    </div>
                    
                    <div class="price-breakdown total-price">
                        <span>Total:</span>
                        <span id="total">KSh 250</span>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-light" onclick="saveOutfit()">
                            <i class="fas fa-save me-2"></i>Save Outfit
                        </button>
                        <button class="btn btn-light" onclick="addToCart()">
                            <i class="fas fa-cart-plus me-2"></i>Add to Cart
                        </button>
                    </div>
                </div>
                
                <div class="saved-outfits">
                    <h5 class="mb-3"><i class="fas fa-bookmark me-2"></i>Saved Outfits</h5>
                    
                    <div class="saved-outfit" onclick="loadOutfit(1)">
                        <div class="outfit-thumbnail">
                            <img src="https://via.placeholder.com/40x40/FF6B35/FFFFFF?text=S" alt="Shirt">
                            <img src="https://via.placeholder.com/40x40/00897B/FFFFFF?text=T" alt="Trousers">
                            <img src="https://via.placeholder.com/40x40/1976D2/FFFFFF?text=SH" alt="Shoes">
                        </div>
                        <div>
                            <div class="fw-bold">Primary School Uniform</div>
                            <small class="text-muted">3 items • KSh 2,850</small>
                        </div>
                    </div>
                    
                    <div class="saved-outfit" onclick="loadOutfit(2)">
                        <div class="outfit-thumbnail">
                            <img src="https://via.placeholder.com/40x40/7B1FA2/FFFFFF?text=D" alt="Dress">
                            <img src="https://via.placeholder.com/40x40/388E3C/FFFFFF?text=SH" alt="Shoes">
                            <img src="https://via.placeholder.com/40x40/FF6B35/FFFFFF?text=SO" alt="Socks">
                        </div>
                        <div>
                            <div class="fw-bold">Girls Primary Uniform</div>
                            <small class="text-muted">3 items • KSh 2,650</small>
                        </div>
                    </div>
                    
                    <div class="saved-outfit" onclick="loadOutfit(3)">
                        <div class="outfit-thumbnail">
                            <img src="https://via.placeholder.com/40x40/FF6B35/FFFFFF?text=S" alt="Shirt">
                            <img src="https://via.placeholder.com/40x40/00897B/FFFFFF?text=T" alt="Trousers">
                            <img src="https://via.placeholder.com/40x40/1976D2/FFFFFF?text=SW" alt="Sweater">
                            <img src="https://via.placeholder.com/40x40/7B1FA2/FFFFFF?text=SH" alt="Shoes">
                        </div>
                        <div>
                            <div class="fw-bold">Secondary School Uniform</div>
                            <small class="text-muted">4 items • KSh 4,200</small>
                        </div>
                    </div>
                </div>
                
                <div class="builder-section">
                    <h5 class="mb-3"><i class="fas fa-lightbulb me-2"></i>Outfit Tips</h5>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Complete Your Look</h6>
                        <p class="small mb-0">A complete uniform typically includes shirt/top, bottom, shoes, and appropriate accessories for your school.</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-piggy-bank me-2"></i>Save Money</h6>
                        <p class="small mb-0">Complete outfits often qualify for bulk discounts. Check our school programs for special pricing.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-ruler me-2"></i>Size Matters</h6>
                        <p class="small mb-0">Use our size calculator to ensure perfect fit for all items in your outfit.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentOutfit = [];
        let currentCategory = 'shirts';
        
        // Sample product data
        const products = {
            shirts: [
                { id: 1, name: 'White School Shirt', price: 850, image: 'https://via.placeholder.com/200x150/FF6B35/FFFFFF?text=White+Shirt' },
                { id: 2, name: 'Blue School Shirt', price: 850, image: 'https://via.placeholder.com/200x150/00897B/FFFFFF?text=Blue+Shirt' },
                { id: 3, name: 'Girls Blouse', price: 950, image: 'https://via.placeholder.com/200x150/1976D2/FFFFFF?text=Blouse' },
                { id: 4, name: 'Polo Shirt', price: 750, image: 'https://via.placeholder.com/200x150/7B1FA2/FFFFFF?text=Polo' }
            ],
            trousers: [
                { id: 5, name: 'Grey School Trousers', price: 1200, image: 'https://via.placeholder.com/200x150/546E7A/FFFFFF?text=Grey+Trousers' },
                { id: 6, name: 'Black School Trousers', price: 1200, image: 'https://via.placeholder.com/200x150/000000/FFFFFF?text=Black+Trousers' },
                { id: 7, name: 'Navy Shorts', price: 800, image: 'https://via.placeholder.com/200x150/1976D2/FFFFFF?text=Navy+Shorts' },
                { id: 8, name: 'Khaki Shorts', price: 750, image: 'https://via.placeholder.com/200x150/8D6E63/FFFFFF?text=Khaki+Shorts' }
            ],
            dresses: [
                { id: 9, name: 'School Dress', price: 1100, image: 'https://via.placeholder.com/200x150/FF6B35/FFFFFF?text=School+Dress' },
                { id: 10, name: 'Pinafore', price: 950, image: 'https://via.placeholder.com/200x150/00897B/FFFFFF?text=Pinafore' },
                { id: 11, name: 'Skirt', price: 800, image: 'https://via.placeholder.com/200x150/7B1FA2/FFFFFF?text=Skirt' },
                { id: 12, name: 'Jumper Dress', price: 1200, image: 'https://via.placeholder.com/200x150/388E3C/FFFFFF?text=Jumper' }
            ],
            sweaters: [
                { id: 13, name: 'V-Neck Sweater', price: 950, image: 'https://via.placeholder.com/200x150/FF6B35/FFFFFF?text=V-Neck' },
                { id: 14, name: 'Crew Neck Sweater', price: 950, image: 'https://via.placeholder.com/200x150/00897B/FFFFFF?text=Crew+Neck' },
                { id: 15, name: 'Cardigan', price: 1100, image: 'https://via.placeholder.com/200x150/1976D2/FFFFFF?text=Cardigan' },
                { id: 16, name: 'School Blazer', price: 1500, image: 'https://via.placeholder.com/200x150/7B1FA2/FFFFFF?text=Blazer' }
            ],
            shoes: [
                { id: 17, name: 'Black School Shoes', price: 1800, image: 'https://via.placeholder.com/200x150/000000/FFFFFF?text=Black+Shoes' },
                { id: 18, name: 'Brown School Shoes', price: 1700, image: 'https://via.placeholder.com/200x150/8D6E63/FFFFFF?text=Brown+Shoes' },
                { id: 19, name: 'Girls School Shoes', price: 1650, image: 'https://via.placeholder.com/200x150/FF6B35/FFFFFF?text=Girls+Shoes' },
                { id: 20, name: 'Sports Shoes', price: 1200, image: 'https://via.placeholder.com/200x150/00897B/FFFFFF?text=Sports+Shoes' }
            ],
            accessories: [
                { id: 21, name: 'School Tie', price: 350, image: 'https://via.placeholder.com/200x150/FF6B35/FFFFFF?text=Tie' },
                { id: 22, name: 'School Belt', price: 450, image: 'https://via.placeholder.com/200x150/546E7A/FFFFFF?text=Belt' },
                { id: 23, name: 'School Socks', price: 250, image: 'https://via.placeholder.com/200x150/FFFFFF/000000?text=Socks' },
                { id: 24, name: 'School Bag', price: 1200, image: 'https://via.placeholder.com/200x150/7B1FA2/FFFFFF?text=School+Bag' }
            ]
        };
        
        // Category tab switching
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentCategory = this.dataset.category;
                loadProducts(currentCategory);
            });
        });
        
        // Load products for selected category
        function loadProducts(category) {
            const productGrid = document.getElementById('productGrid');
            const categoryProducts = products[category] || [];
            
            productGrid.innerHTML = categoryProducts.map(product => `
                <div class="product-card" onclick="addToOutfit(${product.id}, '${product.name}', ${product.price}, '${product.image}')">
                    <img src="${product.image}" alt="${product.name}">
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-price">KSh ${product.price}</div>
                        <button class="add-to-outfit">
                            <i class="fas fa-plus me-1"></i>Add to Outfit
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        // Add item to outfit
        function addToOutfit(id, name, price, image) {
            // Check if item already exists
            if (currentOutfit.find(item => item.id === id)) {
                alert('This item is already in your outfit!');
                return;
            }
            
            currentOutfit.push({ id, name, price, image });
            updateOutfitDisplay();
            updateSummary();
        }
        
        // Remove item from outfit
        function removeFromOutfit(id) {
            currentOutfit = currentOutfit.filter(item => item.id !== id);
            updateOutfitDisplay();
            updateSummary();
        }
        
        // Update outfit display
        function updateOutfitDisplay() {
            const emptyOutfit = document.getElementById('emptyOutfit');
            const outfitPreview = document.getElementById('outfitPreview');
            
            if (currentOutfit.length === 0) {
                emptyOutfit.style.display = 'block';
                outfitPreview.style.display = 'none';
            } else {
                emptyOutfit.style.display = 'none';
                outfitPreview.style.display = 'grid';
                
                outfitPreview.innerHTML = currentOutfit.map(item => `
                    <div class="outfit-item">
                        <button class="remove-item" onclick="removeFromOutfit(${item.id})">
                            <i class="fas fa-times"></i>
                        </button>
                        <img src="${item.image}" alt="${item.name}">
                        <div class="fw-bold">${item.name}</div>
                        <div class="text-primary">KSh ${item.price}</div>
                    </div>
                `).join('');
            }
        }
        
        // Update summary
        function updateSummary() {
            const subtotal = currentOutfit.reduce((sum, item) => sum + item.price, 0);
            const tax = Math.round(subtotal * 0.16);
            const shipping = currentOutfit.length > 0 ? 250 : 0;
            const total = subtotal + tax + shipping;
            
            document.getElementById('subtotal').textContent = `KSh ${subtotal}`;
            document.getElementById('tax').textContent = `KSh ${tax}`;
            document.getElementById('shipping').textContent = `KSh ${shipping}`;
            document.getElementById('total').textContent = `KSh ${total}`;
            
            // Update outfit items list
            const outfitItems = document.getElementById('outfitItems');
            if (currentOutfit.length === 0) {
                outfitItems.innerHTML = '<p class="text-center">No items selected yet</p>';
            } else {
                outfitItems.innerHTML = currentOutfit.map(item => `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>${item.name}</span>
                        <span>KSh ${item.price}</span>
                    </div>
                `).join('');
            }
        }
        
        // Save outfit
        function saveOutfit() {
            if (currentOutfit.length === 0) {
                alert('Please add items to your outfit first!');
                return;
            }
            
            // Here you would normally save to backend
            alert('Outfit saved successfully! You can access it from your saved outfits.');
        }
        
        // Add to cart
        function addToCart() {
            if (currentOutfit.length === 0) {
                alert('Please add items to your outfit first!');
                return;
            }
            
            // Here you would normally add to cart via backend
            alert(`Added ${currentOutfit.length} items to cart!`);
            window.location.href = 'cart.php';
        }
        
        // Load saved outfit
        function loadOutfit(outfitId) {
            // Sample saved outfits - in real app this would come from backend
            const savedOutfits = {
                1: [
                    { id: 1, name: 'White School Shirt', price: 850, image: 'https://via.placeholder.com/200x150/FF6B35/FFFFFF?text=White+Shirt' },
                    { id: 5, name: 'Grey School Trousers', price: 1200, image: 'https://via.placeholder.com/200x150/546E7A/FFFFFF?text=Grey+Trousers' },
                    { id: 17, name: 'Black School Shoes', price: 1800, image: 'https://via.placeholder.com/200x150/000000/FFFFFF?text=Black+Shoes' }
                ],
                2: [
                    { id: 9, name: 'School Dress', price: 1100, image: 'https://via.placeholder.com/200x150/FF6B35/FFFFFF?text=School+Dress' },
                    { id: 17, name: 'Black School Shoes', price: 1800, image: 'https://via.placeholder.com/200x150/000000/FFFFFF?text=Black+Shoes' },
                    { id: 23, name: 'School Socks', price: 250, image: 'https://via.placeholder.com/200x150/FFFFFF/000000?text=Socks' }
                ],
                3: [
                    { id: 1, name: 'White School Shirt', price: 850, image: 'https://via.placeholder.com/200x150/FF6B35/FFFFFF?text=White+Shirt' },
                    { id: 5, name: 'Grey School Trousers', price: 1200, image: 'https://via.placeholder.com/200x150/546E7A/FFFFFF?text=Grey+Trousers' },
                    { id: 13, name: 'V-Neck Sweater', price: 950, image: 'https://via.placeholder.com/200x150/FF6B35/FFFFFF?text=V-Neck' },
                    { id: 17, name: 'Black School Shoes', price: 1800, image: 'https://via.placeholder.com/200x150/000000/FFFFFF?text=Black+Shoes' }
                ]
            };
            
            currentOutfit = savedOutfits[outfitId] || [];
            updateOutfitDisplay();
            updateSummary();
        }
        
        // Load initial products
        loadProducts(currentCategory);
    </script>
</body>
</html>
