<?php
// admin/dashboard.php
require_once '../config/database.php';
require_once '../models/Sales.php';
require_once '../models/Login.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Sales model
$sales = new Sales($db);

// Initialize Login object
$login = new Login($db);

// Check if user is logged in, if not redirect to login page
if (!$login->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Get current user data
$user = $login->getCurrentUser();

// Get filter values from request
$filters = [
    'item_id' => $_GET['item_id'] ?? null,
    'size' => $_GET['size'] ?? null,
    'start_date' => $_GET['start_date'] ?? null,
    'end_date' => $_GET['end_date'] ?? null
];

// Get data
$salesData = $sales->getSalesData($filters)->fetchAll(PDO::FETCH_ASSOC);
$weeklyIncome = $sales->getWeeklyIncome()->fetchAll(PDO::FETCH_ASSOC);
$monthlyIncome = $sales->getMonthlyIncome()->fetchAll(PDO::FETCH_ASSOC);
$items = $sales->getItems()->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for charts
$itemSalesData = array_reduce($salesData, function($result, $item) {
    $key = $item['food_name'];
    $result[$key] = ($result[$key] ?? 0) + $item['item_qty'];
    return $result;
}, []);

$sizeData = array_reduce($salesData, function($result, $item) {
    $key = $item['item_size'] ?: 'N/A';
    $result[$key] = ($result[$key] ?? 0) + $item['item_qty'];
    return $result;
}, []);
?>
<?php include 'header.php'?>
    <div class="container-fluid mt">
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-filter me-1"></i>
                Filters
            </div>
            <div class="card-body">
                <form method="GET" action="index.php">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Item</label>
                            <select name="item_id" class="form-select">
                                <option value="">All Items</option>
                                <?php foreach($items as $item): ?>
                                <option value="<?= htmlspecialchars($item['food_id']) ?>" 
                                    <?= $filters['item_id'] == $item['food_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['food_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Size</label>
                            <select name="size" class="form-select">
                                <option value="">All Sizes</option>
                                <option value="Solo" <?= $filters['size'] == 'Solo' ? 'selected' : '' ?>>Solo</option>
                                <option value="Regular" <?= $filters['size'] == 'Regular' ? 'selected' : '' ?>>Regular</option>
                                <option value="Large" <?= $filters['size'] == 'Large' ? 'selected' : '' ?>>Large</option>
                                <option value="Small" <?= $filters['size'] == 'Small' ? 'selected' : '' ?>>Small</option>
                                <option value="Addon" <?= $filters['size'] == 'Addon' ? 'selected' : '' ?>>Addon</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filters['start_date']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($filters['end_date']) ?>">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="row">
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-1"></i>
                        Sales by Item
                    </div>
                    <div class="card-body">
                        <canvas id="salesByItemChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-pie me-1"></i>
                        Size Distribution
                    </div>
                    <div class="card-body">
                        <canvas id="sizeDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-line me-1"></i>
                        Weekly Income
                    </div>
                    <div class="card-body">
                        <canvas id="weeklyIncomeChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-area me-1"></i>
                        Monthly Income
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyIncomeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sales Data Table -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Sales Data
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Size</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($salesData as $sale): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($sale['purchase_date']))) ?></td>
                                <td><?= htmlspecialchars($sale['food_name']) ?></td>
                                <td><?= htmlspecialchars($sale['item_size']) ?></td>
                                <td><?= htmlspecialchars($sale['item_qty']) ?></td>
                                <td>₱<?= htmlspecialchars(number_format($sale['item_price'], 2)) ?></td>
                                <td>₱<?= htmlspecialchars(number_format($sale['item_subtotal'], 2)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    // Sales by Item Chart
    const itemSalesCtx = document.getElementById('salesByItemChart').getContext('2d');
    new Chart(itemSalesCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($itemSalesData)) ?>,
            datasets: [{
                label: 'Quantity Sold',
                data: <?= json_encode(array_values($itemSalesData)) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Top Selling Items'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity Sold'
                    }
                }
            }
        }
    });

    // Size Distribution Chart
    const sizeCtx = document.getElementById('sizeDistributionChart').getContext('2d');
    new Chart(sizeCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_keys($sizeData)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($sizeData)) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Sales by Size'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Weekly Income Chart
    const weeklyCtx = document.getElementById('weeklyIncomeChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($weeklyIncome, 'day')) ?>,
            datasets: [{
                label: 'Daily Income (₱)',
                data: <?= json_encode(array_column($weeklyIncome, 'daily_total')) ?>,
                fill: false,
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Weekly Income Trend'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (₱)'
                    }
                }
            }
        }
    });

    // Monthly Income Chart
    const monthlyCtx = document.getElementById('monthlyIncomeChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($monthlyIncome, 'month')) ?>,
            datasets: [{
                label: 'Monthly Income (₱)',
                data: <?= json_encode(array_column($monthlyIncome, 'monthly_total')) ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.7)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Income'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (₱)'
                    }
                }
            }
        }
    });
    </script>
</body>
</html>