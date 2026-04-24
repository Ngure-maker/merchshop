<?php
// Use centralized session management
require_once '../config/environment.php';

require_once '../includes/auth.php';
require_once '../includes/admin_functions.php';
$auth = new Auth();
$auth->requireAdmin();

$admin = new AdminFunctions();

// Date range for reports
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get reports data
$sales_report = $admin->getSalesReport($start_date, $end_date);
$product_performance = $admin->getProductPerformance();
$user_stats = $admin->getUserStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Merch Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../views/admin_header.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Reports & Analytics</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                </div>
            </div>
        </div>
                
                <!-- Date Range Filter -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        
                <!-- Summary Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">
                                            KSh <?php echo number_format(array_sum(array_column($sales_report, 'total_sales'))); ?>
                                        </h4>
                                        <p class="mb-0">Total Sales</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo array_sum(array_column($sales_report, 'order_count')); ?></h4>
                                        <p class="mb-0">Total Orders</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-shopping-bag fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">
                                            KSh <?php echo number_format(array_sum(array_column($sales_report, 'total_sales')) / max(array_sum(array_column($sales_report, 'order_count')), 1), 2); ?>
                                        </h4>
                                        <p class="mb-0">Avg Order Value</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calculator fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo $user_stats['total_users'] ?? 0; ?></h4>
                                        <p class="mb-0">Total Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Sales Chart -->
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Sales Trend</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Performance -->
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Top Products</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Sold</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($product_performance, 0, 5) as $product): ?>
                                                <tr>
                                                    <td>
                                                        <small><?php echo htmlspecialchars($product['name']); ?></small>
                                                    </td>
                                                    <td><?php echo $product['total_sold']; ?></td>
                                                    <td>KSh <?php echo number_format($product['total_revenue'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Sales Report -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Detailed Sales Report</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Orders</th>
                                        <th>Total Sales</th>
                                        <th>Average Order</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sales_report as $report): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y', strtotime($report['date'])); ?></td>
                                            <td><?php echo $report['order_count']; ?></td>
                                            <td>KSh <?php echo number_format($report['total_sales'], 2); ?></td>
                                            <td>KSh <?php echo number_format($report['average_order_value'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($sales_report)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No sales data for the selected period
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include '../views/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: [<?php echo implode(',', array_map(function($item) { return "'" . date('M j', strtotime($item['date'])) . "'"; }, $sales_report)); ?>],
            datasets: [{
                label: 'Daily Sales (KSh)',
                data: [<?php echo implode(',', array_column($sales_report, 'total_sales')); ?>],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'KSh ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    </script>
    </main>
    </div>
</body>
</html>
