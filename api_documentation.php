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
    <title>API Documentation - Merch Shop</title>
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
        
        .api-header {
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .api-section {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .api-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .api-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .endpoint-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .endpoint-item {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--accent-purple);
        }
        
        .endpoint-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .method-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: bold;
            margin-right: 1rem;
            font-size: 0.9rem;
        }
        
        .method-get {
            background: var(--accent-green);
            color: white;
        }
        
        .method-post {
            background: var(--primary-amber);
            color: white;
        }
        
        .method-put {
            background: var(--secondary-blue);
            color: white;
        }
        
        .method-delete {
            background: #dc3545;
            color: white;
        }
        
        .endpoint-url {
            font-family: monospace;
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin: 1rem 0;
            overflow-x: auto;
        }
        
        .copy-button {
            background: var(--accent-purple);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.8rem;
        }
        
        .copy-button:hover {
            background: var(--secondary-blue);
        }
        
        .auth-section {
            background: linear-gradient(135deg, var(--secondary-teal), var(--accent-green));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .response-example {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
            border: 1px solid #e9ecef;
        }
        
        .status-code {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.8rem;
        }
        
        .status-200 {
            background: #d4edda;
            color: #155724;
        }
        
        .status-201 {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-400 {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-401 {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-404 {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-500 {
            background: #f8d7da;
            color: #721c24;
        }
        
        .parameter-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        
        .parameter-table th {
            background: var(--accent-purple);
            color: white;
            padding: 0.75rem;
            text-align: left;
        }
        
        .parameter-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .required-badge {
            background: var(--primary-amber);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .optional-badge {
            background: var(--neutral-gray);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .quick-start {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .step-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background: var(--accent-purple);
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
        
        .rate-limiting {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        
        .sdk-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .language-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .language-card {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .language-card:hover {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .language-icon {
            font-size: 2rem;
            color: var(--accent-purple);
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="api-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-code me-3"></i>API Documentation</h1>
            <p class="lead mb-0">Integrate SmartSchool Uniforms services into your applications</p>
        </div>
    </div>
    
    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="quick-start">
                    <h3 class="mb-4"><i class="fas fa-rocket me-2"></i>Quick Start Guide</h3>
                    
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <div class="step-title">Get API Key</div>
                            <div class="step-description">Register for a developer account and generate your API key from the developer dashboard.</div>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <div class="step-title">Choose Your Endpoint</div>
                            <div class="step-description">Select the API endpoint that matches your needs from our comprehensive list.</div>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <div class="step-title">Make Your First Request</div>
                            <div class="step-description">Use your API key to authenticate and make your first API call.</div>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <div class="step-title">Integrate & Build</div>
                            <div class="step-description">Integrate the API into your application and start building amazing features.</div>
                        </div>
                    </div>
                </div>
                
                <div class="auth-section">
                    <h3 class="mb-4"><i class="fas fa-key me-2"></i>Authentication</h3>
                    
                    <p>All API requests require authentication using an API key. Include your API key in the Authorization header:</p>
                    
                    <div class="code-block">
Authorization: Bearer YOUR_API_KEY
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Getting Your API Key</h5>
                            <ol>
                                <li>Register for a developer account</li>
                                <li>Go to Developer Dashboard</li>
                                <li>Click "Generate New Key"</li>
                                <li>Copy and securely store your key</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h5>Security Best Practices</h5>
                            <ul>
                                <li>Never expose your API key in client-side code</li>
                                <li>Use environment variables for key storage</li>
                                <li>Rotate keys regularly</li>
                                <li>Monitor API usage for anomalies</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="endpoint-section">
                    <h3 class="mb-4">API Endpoints</h3>
                    
                    <div class="endpoint-item">
                        <div class="endpoint-header">
                            <span class="method-badge method-get">GET</span>
                            <span class="endpoint-url">/api/v1/products</span>
                        </div>
                        <h5>Get All Products</h5>
                        <p>Retrieve a list of all available uniform products with filtering options.</p>
                        
                        <div class="mt-3">
                            <h6>Parameters</h6>
                            <table class="parameter-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>category</td>
                                        <td>string</td>
                                        <td><span class="optional-badge">Optional</span></td>
                                        <td>Filter by category ID</td>
                                    </tr>
                                    <tr>
                                        <td>school</td>
                                        <td>string</td>
                                        <td><span class="optional-badge">Optional</span></td>
                                        <td>Filter by school ID</td>
                                    </tr>
                                    <tr>
                                        <td>size</td>
                                        <td>string</td>
                                        <td><span class="optional-badge">Optional</span></td>
                                        <td>Filter by size</td>
                                    </tr>
                                    <tr>
                                        <td>limit</td>
                                        <td>integer</td>
                                        <td><span class="optional-badge">Optional</span></td>
                                        <td>Number of results (max: 100)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <h6>Example Request</h6>
                            <div class="code-block">
curl -X GET "https://api.smartschool.co.ke/api/v1/products?category=shirts&limit=10" \
  -H "Authorization: Bearer YOUR_API_KEY"
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6>Example Response</h6>
                            <div class="response-example">
                                <span class="status-code status-200">200 OK</span>
                                <div class="code-block">
{
  "success": true,
  "data": [
    {
      "id": "prod_123",
      "name": "White School Shirt",
      "category": "shirts",
      "price": 1200,
      "sizes": ["S", "M", "L", "XL"],
      "in_stock": true
    }
  ],
  "pagination": {
    "total": 45,
    "page": 1,
    "limit": 10
  }
}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="endpoint-item">
                        <div class="endpoint-header">
                            <span class="method-badge method-get">GET</span>
                            <span class="endpoint-url">/api/v1/products/{id}</span>
                        </div>
                        <h5>Get Product Details</h5>
                        <p>Retrieve detailed information about a specific product.</p>
                        
                        <div class="mt-3">
                            <h6>Example Request</h6>
                            <div class="code-block">
curl -X GET "https://api.smartschool.co.ke/api/v1/products/prod_123" \
  -H "Authorization: Bearer YOUR_API_KEY"
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6>Example Response</h6>
                            <div class="response-example">
                                <span class="status-code status-200">200 OK</span>
                                <div class="code-block">
{
  "success": true,
  "data": {
    "id": "prod_123",
    "name": "White School Shirt",
    "description": "High-quality cotton school shirt",
    "category": "shirts",
    "price": 1200,
    "sizes": ["S", "M", "L", "XL"],
    "colors": ["white", "light_blue"],
    "material": "100% cotton",
    "in_stock": true,
    "images": [
      "https://cdn.smartschool.co.ke/images/prod_123_1.jpg"
    ],
    "school_requirements": ["Nairobi Academy", "St. Mary's"]
  }
}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="endpoint-item">
                        <div class="endpoint-header">
                            <span class="method-badge method-post">POST</span>
                            <span class="endpoint-url">/api/v1/orders</span>
                        </div>
                        <h5>Create Order</h5>
                        <p>Create a new order for uniform products.</p>
                        
                        <div class="mt-3">
                            <h6>Request Body</h6>
                            <table class="parameter-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>customer_name</td>
                                        <td>string</td>
                                        <td><span class="required-badge">Required</span></td>
                                        <td>Customer full name</td>
                                    </tr>
                                    <tr>
                                        <td>customer_email</td>
                                        <td>string</td>
                                        <td><span class="required-badge">Required</span></td>
                                        <td>Customer email address</td>
                                    </tr>
                                    <tr>
                                        <td>customer_phone</td>
                                        <td>string</td>
                                        <td><span class="required-badge">Required</span></td>
                                        <td>Customer phone number</td>
                                    </tr>
                                    <tr>
                                        <td>items</td>
                                        <td>array</td>
                                        <td><span class="required-badge">Required</span></td>
                                        <td>Array of product items</td>
                                    </tr>
                                    <tr>
                                        <td>delivery_address</td>
                                        <td>object</td>
                                        <td><span class="optional-badge">Optional</span></td>
                                        <td>Delivery address details</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <h6>Example Request</h6>
                            <div class="code-block">
curl -X POST "https://api.smartschool.co.ke/api/v1/orders" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "customer_phone": "+254700123456",
    "items": [
      {
        "product_id": "prod_123",
        "quantity": 2,
        "size": "M"
      }
    ],
    "delivery_address": {
      "street": "123 Main St",
      "city": "Nairobi",
      "postal_code": "00100"
    }
  }'
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6>Example Response</h6>
                            <div class="response-example">
                                <span class="status-code status-201">201 Created</span>
                                <div class="code-block">
{
  "success": true,
  "data": {
    "order_id": "ord_456",
    "status": "pending",
    "total_amount": 2400,
    "created_at": "2023-12-15T10:30:00Z",
    "estimated_delivery": "2023-12-18T10:30:00Z"
  }
}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="endpoint-item">
                        <div class="endpoint-header">
                            <span class="method-badge method-get">GET</span>
                            <span class="endpoint-url">/api/v1/orders/{id}</span>
                        </div>
                        <h5>Get Order Status</h5>
                        <p>Retrieve the current status and details of an order.</p>
                        
                        <div class="mt-3">
                            <h6>Example Response</h6>
                            <div class="response-example">
                                <span class="status-code status-200">200 OK</span>
                                <div class="code-block">
{
  "success": true,
  "data": {
    "order_id": "ord_456",
    "status": "shipped",
    "tracking_number": "TRK123456",
    "items": [
      {
        "product_id": "prod_123",
        "quantity": 2,
        "size": "M"
      }
    ],
    "total_amount": 2400,
    "created_at": "2023-12-15T10:30:00Z",
    "shipped_at": "2023-12-16T14:20:00Z",
    "estimated_delivery": "2023-12-18T10:30:00Z"
  }
}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="endpoint-item">
                        <div class="endpoint-header">
                            <span class="method-badge method-get">GET</span>
                            <span class="endpoint-url">/api/v1/schools</span>
                        </div>
                        <h5>Get Schools</h5>
                        <p>Retrieve a list of schools and their uniform requirements.</p>
                        
                        <div class="mt-3">
                            <h6>Example Response</h6>
                            <div class="response-example">
                                <span class="status-code status-200">200 OK</span>
                                <div class="code-block">
{
  "success": true,
  "data": [
    {
      "id": "sch_789",
      "name": "Nairobi Academy",
      "location": "Nairobi",
      "uniform_requirements": {
        "shirts": ["white", "light_blue"],
        "trousers": ["khaki", "navy"],
        "sweaters": ["navy"]
      }
    }
  ]
}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="rate-limiting">
                    <h3 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Rate Limiting</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Rate Limits</h5>
                            <ul>
                                <li><strong>Free Tier:</strong> 100 requests/hour</li>
                                <li><strong>Basic Plan:</strong> 1,000 requests/hour</li>
                                <li><strong>Pro Plan:</strong> 10,000 requests/hour</li>
                                <li><strong>Enterprise:</strong> Unlimited</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Headers</h5>
                            <p>Rate limit information is included in response headers:</p>
                            <div class="code-block">
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Exceeding Limits</h6>
                        <p class="mb-0">When rate limits are exceeded, you'll receive a <span class="status-code status-429">429 Too Many Requests</span> response with the following body:</p>
                        <div class="code-block">
{
  "error": "Rate limit exceeded",
  "message": "Too many requests. Try again in 3600 seconds.",
  "retry_after": 3600
}
                        </div>
                    </div>
                </div>
                
                <div class="sdk-section">
                    <h3 class="mb-4"><i class="fas fa-code-branch me-2"></i>SDKs & Libraries</h3>
                    
                    <p>We provide official SDKs for popular programming languages to make integration easier:</p>
                    
                    <div class="language-grid">
                        <div class="language-card" onclick="showSDKInfo('javascript')">
                            <div class="language-icon">
                                <i class="fab fa-js"></i>
                            </div>
                            <div>JavaScript</div>
                        </div>
                        
                        <div class="language-card" onclick="showSDKInfo('python')">
                            <div class="language-icon">
                                <i class="fab fa-python"></i>
                            </div>
                            <div>Python</div>
                        </div>
                        
                        <div class="language-card" onclick="showSDKInfo('php')">
                            <div class="language-icon">
                                <i class="fab fa-php"></i>
                            </div>
                            <div>PHP</div>
                        </div>
                        
                        <div class="language-card" onclick="showSDKInfo('java')">
                            <div class="language-icon">
                                <i class="fab fa-java"></i>
                            </div>
                            <div>Java</div>
                        </div>
                        
                        <div class="language-card" onclick="showSDKInfo('csharp')">
                            <div class="language-icon">
                                <i class="fab fa-microsoft"></i>
                            </div>
                            <div>C#</div>
                        </div>
                        
                        <div class="language-card" onclick="showSDKInfo('ruby')">
                            <div class="language-icon">
                                <i class="fas fa-gem"></i>
                            </div>
                            <div>Ruby</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Installation Example (JavaScript)</h6>
                        <div class="code-block">
npm install smartschool-api
                        </div>
                        <div class="code-block">
const SmartSchoolAPI = require('smartschool-api');
const client = new SmartSchoolAPI('YOUR_API_KEY');
                        </div>
                    </div>
                </div>
                
                <div class="api-section">
                    <h3 class="mb-4">Error Handling</h3>
                    
                    <p>The API uses standard HTTP status codes to indicate success or failure:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Success Codes</h5>
                            <ul>
                                <li><span class="status-code status-200">200 OK</span> - Request successful</li>
                                <li><span class="status-code status-201">201 Created</span> - Resource created</li>
                                <li><span class="status-code status-204">204 No Content</span> - Request successful, no content</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Error Codes</h5>
                            <ul>
                                <li><span class="status-code status-400">400 Bad Request</span> - Invalid request</li>
                                <li><span class="status-code status-401">401 Unauthorized</span> - Invalid API key</li>
                                <li><span class="status-code status-404">404 Not Found</span> - Resource not found</li>
                                <li><span class="status-code status-429">429 Too Many Requests</span> - Rate limit exceeded</li>
                                <li><span class="status-code status-500">500 Server Error</span> - Internal server error</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-danger mt-3">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Error Response Format</h6>
                        <div class="code-block">
{
  "success": false,
  "error": {
    "code": "INVALID_API_KEY",
    "message": "The provided API key is invalid",
    "details": "Please check your API key and try again"
  }
}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="api-section">
                    <h4 class="mb-3">API Statistics</h4>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">99.9%</div>
                            <h6>Uptime</h6>
                            <p class="small text-muted mb-0">Last 30 days</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">15</div>
                            <h6>Endpoints</h6>
                            <p class="small text-muted mb-0">Comprehensive coverage</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">50ms</div>
                            <h6>Avg Response</h6>
                            <p class="small text-muted mb-0">Lightning fast</p>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">1M+</div>
                            <h6>Daily Requests</h6>
                            <p class="small text-muted mb-0">Growing rapidly</p>
                        </div>
                    </div>
                </div>
                
                <div class="api-section">
                    <h4 class="mb-3">Base URL</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-globe me-2"></i>Production</h6>
                        <div class="code-block">
https://api.smartschool.co.ke
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-flask me-2"></i>Testing</h6>
                        <div class="code-block">
https://api-test.smartschool.co.ke
                        </div>
                    </div>
                </div>
                
                <div class="api-section">
                    <h4 class="mb-3">API Plans</h4>
                    
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6>Free Tier</h6>
                                    <small class="text-muted">Perfect for testing and small projects</small>
                                    <ul class="small mt-2">
                                        <li>100 requests/hour</li>
                                        <li>Basic endpoints</li>
                                        <li>Community support</li>
                                    </ul>
                                </div>
                                <span class="badge bg-success">Free</span>
                            </div>
                        </div>
                        
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6>Basic Plan</h6>
                                    <small class="text-muted">For growing applications</small>
                                    <ul class="small mt-2">
                                        <li>1,000 requests/hour</li>
                                        <li>All endpoints</li>
                                        <li>Email support</li>
                                    </ul>
                                </div>
                                <span class="badge bg-primary">$29/mo</span>
                            </div>
                        </div>
                        
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6>Pro Plan</h6>
                                    <small class="text-muted">For professional applications</small>
                                    <ul class="small mt-2">
                                        <li>10,000 requests/hour</li>
                                        <li>Premium features</li>
                                        <li>Priority support</li>
                                    </ul>
                                </div>
                                <span class="badge bg-warning">$99/mo</span>
                            </div>
                        </div>
                        
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6>Enterprise</h6>
                                    <small class="text-muted">For large organizations</small>
                                    <ul class="small mt-2">
                                        <li>Unlimited requests</li>
                                        <li>Custom features</li>
                                        <li>Dedicated support</li>
                                    </ul>
                                </div>
                                <span class="badge bg-danger">Custom</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="api-section">
                    <h4 class="mb-3">Quick Links</h4>
                    
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-book me-2"></i>
                            <strong>Full API Reference</strong>
                            <small class="text-muted d-block">Complete endpoint documentation</small>
                        </a>
                        
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-code me-2"></i>
                            <strong>Code Examples</strong>
                            <small class="text-muted d-block">Sample implementations</small>
                        </a>
                        
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-download me-2"></i>
                            <strong>Download SDKs</strong>
                            <small class="text-muted d-block">Official libraries</small>
                        </a>
                        
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-comments me-2"></i>
                            <strong>API Support</strong>
                            <small class="text-muted d-block">Get help from our team</small>
                        </a>
                    </div>
                </div>
                
                <div class="api-section">
                    <h4 class="mb-3">Contact API Team</h4>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-envelope me-2"></i>Email</h6>
                        <p class="small mb-0">api@smartschool.co.ke</p>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-slack me-2"></i>Slack</h6>
                        <p class="small mb-0">Join our developer community</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-github me-2"></i>GitHub</h6>
                        <p class="small mb-0">github.com/smartschool/api</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Code copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
                alert('Failed to copy. Please copy manually.');
            });
        }
        
        function showSDKInfo(language) {
            const sdkInfo = {
                'javascript': 'JavaScript SDK: npm install smartschool-api',
                'python': 'Python SDK: pip install smartschool-api',
                'php': 'PHP SDK: composer require smartschool/api',
                'java': 'Java SDK: Available via Maven Central',
                'csharp': 'C# SDK: Available via NuGet',
                'ruby': 'Ruby SDK: gem install smartschool-api'
            };
            
            alert(sdkInfo[language] || 'SDK information not available.');
        }
        
        // Add copy buttons to code blocks
        document.addEventListener('DOMContentLoaded', function() {
            const codeBlocks = document.querySelectorAll('.code-block');
            codeBlocks.forEach(function(block) {
                const button = document.createElement('button');
                button.className = 'copy-button';
                button.innerHTML = '<i class="fas fa-copy me-1"></i>Copy';
                button.onclick = function() {
                    copyToClipboard(block.textContent.trim());
                };
                block.style.position = 'relative';
                block.appendChild(button);
            });
        });
    </script>
</body>
</html>
