<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require seller access
Auth::requireSeller();

$pageTitle = 'My Sales';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Get sales
$salesQuery = "SELECT s.sneaker_id, s.brand, s.model, s.size, s.price, s.serial_number,
              o.order_id, o.created_at as order_date, o.order_status,
              (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image
              FROM sneakers s
              JOIN order_items oi ON s.sneaker_id = oi.sneaker_id
              JOIN orders o ON oi.order_id = o.order_id
              WHERE s.seller_id = '$userId' AND s.status = 'sold'
              ORDER BY o.created_at DESC
              LIMIT $offset, $itemsPerPage";
$salesResult = $db->query($salesQuery);

$sales = [];
while ($row = $salesResult->fetch_assoc()) {
    $sales[] = $row;
}

// Count total sales for pagination
$countQuery = "SELECT COUNT(*) as total
              FROM sneakers s
              JOIN order_items oi ON s.sneaker_id = oi.sneaker_id
              JOIN orders o ON oi.order_id = o.order_id
              WHERE s.seller_id = '$userId' AND s.status = 'sold'";
$countResult = $db->query($countQuery);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get sales statistics
$totalSalesQuery = "SELECT COUNT(*) as count, SUM(s.price) as total
                  FROM sneakers s
                  WHERE s.seller_id = '$userId' AND s.status = 'sold'";
$totalSalesResult = $db->query($totalSalesQuery);
$salesStats = $totalSalesResult->fetch_assoc();

// Get monthly sales
$monthlySalesQuery = "SELECT DATE_FORMAT(o.created_at, '%Y-%m') as month, COUNT(*) as count, SUM(s.price) as total
                    FROM sneakers s
                    JOIN order_items oi ON s.sneaker_id = oi.sneaker_id
                    JOIN orders o ON oi.order_id = o.order_id
                    WHERE s.seller_id = '$userId' AND s.status = 'sold'
                    GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
                    ORDER BY month DESC
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
            <h3>Seller Dashboard</h3>
            <ul>
                <li><a href="<?php echo SITE_URL; ?>/seller/dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/sneakers.php">My Listings</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/add-sneaker.php">Add Sneaker</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/sales.php" class="active">My Sales</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/payments.php">Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>My Sales</h1>
            </div>
            
            <div class="sales-stats">
                <div class="stat-card">
                    <h3><?php echo $salesStats['count']; ?></h3>
                    <p>Total Sneakers Sold</p>
                </div>
                
                <div class="stat-card">
                    <h3><?php echo formatPrice($salesStats['total'] ?? 0); ?></h3>
                    <p>Total Sales Value</p>
                </div>
                
                <div class="stat-card">
                    <h3><?php echo formatPrice(($salesStats['total'] ?? 0) * (1 - PLATFORM_FEE_PERCENTAGE / 100)); ?></h3>
                    <p>Total Earnings</p>
                </div>
            </div>
            
            <?php if (count($monthlySales) > 0): ?>
                <div class="monthly-sales">
                    <h2>Monthly Sales</h2>
                    <div class="monthly-sales-grid">
                        <?php foreach ($monthlySales as $month): ?>
                            <div class="month-card">
                                <div class="month-name"><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></div>
                                <div class="month-stats">
                                    <div class="month-count"><?php echo $month['count']; ?> sneakers</div>
                                    <div class="month-total"><?php echo formatPrice($month['total']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="sales-list">
                <h2>Sales History</h2>
                
                <?php if (count($sales) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Sneaker</th>
                                    <th>Order ID</th>
                                    <th>Sale Date</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td>
                                            <div class="sneaker-info">
                                                <div class="sneaker-image">
                                                    <?php if ($sale['image']): ?>
                                                        <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $sale['image']; ?>" alt="<?php echo $sale['brand'] . ' ' . $sale['model']; ?>">
                                                    <?php else: ?>
                                                        <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="Sneaker placeholder">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="sneaker-details">
                                                    <div class="sneaker-name"><?php echo $sale['brand'] . ' ' . $sale['model']; ?></div>
                                                    <div class="sneaker-size">Size: <?php echo $sale['size']; ?> UK</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>#<?php echo $sale['order_id']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($sale['order_date'])); ?></td>
                                        <td><?php echo formatPrice($sale['price']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $sale['order_status']; ?>">
                                                <?php echo ucfirst($sale['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>/seller/sale-details.php?order_id=<?php echo $sale['order_id']; ?>&sneaker_id=<?php echo $sale['sneaker_id']; ?>" class="btn-sm">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-sales">
                        <div class="no-data-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3>No sales yet</h3>
                        <p>You haven't sold any sneakers yet. Once your sneakers are sold, they will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.sales-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background-color: var(--bg-secondary);
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.stat-card h3 {
    font-size: 24px;
    margin-bottom: 10px;
    color: var(--accent-color);
}

.monthly-sales {
    margin-bottom: 30px;
}

.monthly-sales h2 {
    margin-bottom: 15px;
}

.monthly-sales-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.month-card {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 15px;
}

.month-name {
    font-weight: bold;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.month-stats {
    display: flex;
    justify-content: space-between;
}

.month-total {
    font-weight: bold;
    color: var(--accent-color);
}

.sales-list h2 {
    margin-bottom: 15px;
}

.sneaker-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sneaker-image {
    width: 50px;
    height: 50px;
    border-radius: 4px;
    overflow: hidden;
}

.sneaker-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sneaker-name {
    font-weight: bold;
}

.sneaker-size {
    font-size: 12px;
    color: var(--text-secondary);
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.status-badge.placed {
    background-color: rgba(243, 156, 18, 0.2);
    color: var(--warning-color);
}

.status-badge.processing {
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

.status-badge.cancelled {
    background-color: rgba(231, 76, 60, 0.2);
    color: var(--error-color);
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.no-sales {
    text-align: center;
    padding: 50px 0;
    background-color: var(--bg-secondary);
    border-radius: 8px;
}

.no-data-icon {
    font-size: 60px;
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.no-sales h3 {
    margin-bottom: 10px;
}

.no-sales p {
    color: var(--text-secondary);
}

@media (max-width: 992px) {
    .sales-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .monthly-sales-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .sales-stats {
        grid-template-columns: 1fr;
    }
    
    .monthly-sales-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
