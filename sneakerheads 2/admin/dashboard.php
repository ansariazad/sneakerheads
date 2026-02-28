<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require superadmin access
Auth::requireSuperAdmin();

$pageTitle = 'Admin Dashboard';
$currentUser = Auth::getCurrentUser();

$db = Database::getInstance();
$conn = $db->getConnection();

// Get platform statistics
// Total users
$usersQuery = "SELECT COUNT(*) as count FROM users";
$usersResult = $db->query($usersQuery);
$totalUsers = $usersResult->fetch_assoc()['count'];

// Total sellers
$sellersQuery = "SELECT COUNT(*) as count FROM users WHERE user_type = 'seller_buyer'";
$sellersResult = $db->query($sellersQuery);
$totalSellers = $sellersResult->fetch_assoc()['count'];

// Total sneakers
$sneakersQuery = "SELECT COUNT(*) as count FROM sneakers";
$sneakersResult = $db->query($sneakersQuery);
$totalSneakers = $sneakersResult->fetch_assoc()['count'];

// Pending approvals
$pendingQuery = "SELECT COUNT(*) as count FROM sneakers WHERE status = 'pending'";
$pendingResult = $db->query($pendingQuery);
$pendingApprovals = $pendingResult->fetch_assoc()['count'];

// Total orders
$ordersQuery = "SELECT COUNT(*) as count FROM orders";
$ordersResult = $db->query($ordersQuery);
$totalOrders = $ordersResult->fetch_assoc()['count'];

// Total sales amount
$salesQuery = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'";
$salesResult = $db->query($salesQuery);
$totalSales = $salesResult->fetch_assoc()['total'] ?? 0;

// Platform earnings (platform fee from all sales)
$earningsQuery = "SELECT SUM(platform_fee) as total FROM payments";
$earningsResult = $db->query($earningsQuery);
$platformEarnings = $earningsResult->fetch_assoc()['total'] ?? 0;

// Pending payments
$pendingPaymentsQuery = "SELECT COUNT(*) as count FROM payments WHERE status IN ('requested', 'processing')";
$pendingPaymentsResult = $db->query($pendingPaymentsQuery);
$pendingPayments = $pendingPaymentsResult->fetch_assoc()['count'];

// Recent orders
$recentOrdersQuery = "SELECT o.order_id, o.user_id, o.total_amount, o.payment_method, o.payment_status, 
                    o.order_status, o.created_at, u.username
                    FROM orders o
                    JOIN users u ON o.user_id = u.user_id
                    ORDER BY o.created_at DESC
                    LIMIT 5";
$recentOrdersResult = $db->query($recentOrdersQuery);

$recentOrders = [];
while ($row = $recentOrdersResult->fetch_assoc()) {
    $recentOrders[] = $row;
}

// Recent sneaker listings
$recentSneakersQuery = "SELECT s.sneaker_id, s.brand, s.model, s.price, s.status, s.created_at,
                      u.username as seller_username
                      FROM sneakers s
                      JOIN users u ON s.seller_id = u.user_id
                      ORDER BY s.created_at DESC
                      LIMIT 5";
$recentSneakersResult = $db->query($recentSneakersQuery);

$recentSneakers = [];
while ($row = $recentSneakersResult->fetch_assoc()) {
    $recentSneakers[] = $row;
}

// Monthly sales data for chart
$monthlySalesQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                    SUM(total_amount) as total,
                    COUNT(*) as count
                    FROM orders
                    WHERE payment_status = 'completed'
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month ASC
                    LIMIT 6";
$monthlySalesResult = $db->query($monthlySalesQuery);

$monthlySales = [];
while ($row = $monthlySalesResult->fetch_assoc()) {
    $monthlySales[] = $row;
}

include '../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3>Admin Dashboard</h3>
            <ul>
                <li><a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="active">Dashboard</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/users.php">Manage Users</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/sneakers.php">Approve Listings</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/orders.php">Manage Orders</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/payments.php">Seller Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Admin Dashboard</h1>
                <div class="date-display">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y'); ?>
                </div>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalSellers; ?></h3>
                        <p>Total Sellers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shoe-prints"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalSneakers; ?></h3>
                        <p>Total Sneakers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $pendingApprovals; ?></h3>
                        <p>Pending Approvals</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalOrders; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo formatPrice($totalSales); ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo formatPrice($platformEarnings); ?></h3>
                        <p>Platform Earnings</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $pendingPayments; ?></h3>
                        <p>Pending Payments</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Recent Orders</h2>
                        <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="view-all">View All</a>
                    </div>
                    
                    <?php if (count($recentOrders) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td><?php echo $order['username']; ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $order['payment_status']; ?>">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $order['order_status']; ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/admin/order-details.php?id=<?php echo $order['order_id']; ?>" class="btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>No orders found.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Recent Sneaker Listings</h2>
                        <a href="<?php echo SITE_URL; ?>/admin/sneakers.php" class="view-all">View All</a>
                    </div>
                    
                    <?php if (count($recentSneakers) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sneaker</th>
                                        <th>Seller</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date Listed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentSneakers as $sneaker): ?>
                                        <tr>
                                            <td><?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?></td>
                                            <td><?php echo $sneaker['seller_username']; ?></td>
                                            <td><?php echo formatPrice($sneaker['price']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $sneaker['status']; ?>">
                                                    <?php echo ucfirst($sneaker['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($sneaker['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/admin/sneaker-details.php?id=<?php echo $sneaker['sneaker_id']; ?>" class="btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>No sneaker listings found.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Monthly Sales</h2>
                    </div>
                    
                    <?php if (count($monthlySales) > 0): ?>
                        <div class="sales-chart">
                            <canvas id="monthlySalesChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>No sales data available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background-color: var(--bg-light);
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    font-size: 24px;
    width: 50px;
    height: 50px;
    background-color: rgba(52, 152, 219, 0.1);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 15px;
    color: var(--primary-color);
}

.stat-details h3 {
    font-size: 24px;
    margin-bottom: 5px;
}

.stat-details p {
    color: var(--text-secondary);
    margin: 0;
}

.dashboard-sections {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.dashboard-section {
    background-color: var(--bg-light);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.view-all {
    font-size: 14px;
}

.date-display {
    font-size: 16px;
    color: var(--text-secondary);
}

.sales-chart {
    height: 300px;
    position: relative;
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.status-badge.pending {
    background-color: rgba(243, 156, 18, 0.2);
    color: var(--warning-color);
}

.status-badge.approved {
    background-color: rgba(46, 204, 113, 0.2);
    color: var(--success-color);
}

.status-badge.rejected {
    background-color: rgba(231, 76, 60, 0.2);
    color: var(--error-color);
}

.status-badge.sold {
    background-color: rgba(52, 152, 219, 0.2);
    color: var(--info-color);
}

.status-badge.completed {
    background-color: rgba(46, 204, 113, 0.2);
    color: var(--success-color);
}

.status-badge.placed, .status-badge.processing {
    background-color: rgba(52, 152, 219, 0.2);
    color: var(--info-color);
}

.status-badge.shipped {
    background-color: rgba(155, 89, 182, 0.2);
    color: #9b59b6;
}

.status-badge.delivered {
    background-color: rgba(46, 204, 113, 0.2);
    color: var(--success-color);
}

.status-badge.cancelled, .status-badge.failed {
    background-color: rgba(231, 76, 60, 0.2);
    color: var(--error-color);
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.no-data {
    text-align: center;
    padding: 20px;
    color: var(--text-secondary);
}

@media (max-width: 992px) {
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$extraScripts = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Monthly sales chart
    const salesChartCanvas = document.getElementById("monthlySalesChart");
    
    if (salesChartCanvas) {
        const months = [' . implode(',', array_map(function($sale) {
            return '"' . date('M Y', strtotime($sale['month'] . '-01')) . '"';
        }, $monthlySales)) . '];
        
        const salesData = [' . implode(',', array_map(function($sale) {
            return $sale['total'];
        }, $monthlySales)) . '];
        
        const ordersData = [' . implode(',', array_map(function($sale) {
            return $sale['count'];
        }, $monthlySales)) . '];
        
        new Chart(salesChartCanvas, {
            type: "bar",
            data: {
                labels: months,
                datasets: [
                    {
                        label: "Sales Amount (₹)",
                        data: salesData,
                        backgroundColor: "rgba(52, 152, 219, 0.5)",
                        borderColor: "rgba(52, 152, 219, 1)",
                        borderWidth: 1
                    },
                    {
                        label: "Number of Orders",
                        data: ordersData,
                        backgroundColor: "rgba(46, 204, 113, 0.5)",
                        borderColor: "rgba(46, 204, 113, 1)",
                        borderWidth: 1,
                        type: "line"
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(255, 255, 255, 0.1)"
                        },
                        ticks: {
                            color: "#bdc3c7"
                        }
                    },
                    x: {
                        grid: {
                            color: "rgba(255, 255, 255, 0.1)"
                        },
                        ticks: {
                            color: "#bdc3c7"
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: "#bdc3c7"
                        }
                    }
                }
            }
        });
    }
});
</script>
';
?>

<?php include '../includes/footer.php'; ?>