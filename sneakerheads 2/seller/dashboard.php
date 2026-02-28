<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require seller access
Auth::requireSeller();

$pageTitle = 'Seller Dashboard';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Get seller stats
$totalListingsQuery = "SELECT COUNT(*) as count FROM sneakers WHERE seller_id = '$userId'";
$totalListingsResult = $db->query($totalListingsQuery);
$totalListings = $totalListingsResult->fetch_assoc()['count'];

$pendingListingsQuery = "SELECT COUNT(*) as count FROM sneakers WHERE seller_id = '$userId' AND status = 'pending'";
$pendingListingsResult = $db->query($pendingListingsQuery);
$pendingListings = $pendingListingsResult->fetch_assoc()['count'];

$approvedListingsQuery = "SELECT COUNT(*) as count FROM sneakers WHERE seller_id = '$userId' AND status = 'approved'";
$approvedListingsResult = $db->query($approvedListingsQuery);
$approvedListings = $approvedListingsResult->fetch_assoc()['count'];

$soldListingsQuery = "SELECT COUNT(*) as count FROM sneakers WHERE seller_id = '$userId' AND status = 'sold'";
$soldListingsResult = $db->query($soldListingsQuery);
$soldListings = $soldListingsResult->fetch_assoc()['count'];

// Get recent sales
$recentSalesQuery = "SELECT s.brand, s.model, s.price, o.created_at, o.order_id 
                    FROM sneakers s 
                    JOIN order_items oi ON s.sneaker_id = oi.sneaker_id 
                    JOIN orders o ON oi.order_id = o.order_id 
                    WHERE s.seller_id = '$userId' AND s.status = 'sold' 
                    ORDER BY o.created_at DESC 
                    LIMIT 5";
$recentSalesResult = $db->query($recentSalesQuery);

$recentSales = [];
while ($row = $recentSalesResult->fetch_assoc()) {
    $recentSales[] = $row;
}

// Get recent listings
$recentListingsQuery = "SELECT sneaker_id, brand, model, price, status, created_at 
                       FROM sneakers 
                       WHERE seller_id = '$userId' 
                       ORDER BY created_at DESC 
                       LIMIT 5";
$recentListingsResult = $db->query($recentListingsQuery);

$recentListings = [];
while ($row = $recentListingsResult->fetch_assoc()) {
    $recentListings[] = $row;
}

// Get pending payments
$pendingPaymentsQuery = "SELECT p.payment_id, p.amount, p.platform_fee, p.net_amount, p.status, p.created_at,
                        s.brand, s.model
                        FROM payments p
                        JOIN order_items oi ON p.order_item_id = oi.item_id
                        JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
                        WHERE p.seller_id = '$userId' AND p.status IN ('requested', 'processing')
                        ORDER BY p.created_at DESC
                        LIMIT 5";
$pendingPaymentsResult = $db->query($pendingPaymentsQuery);

$pendingPayments = [];
while ($row = $pendingPaymentsResult->fetch_assoc()) {
    $pendingPayments[] = $row;
}

include '../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3>Seller Dashboard</h3>
            <ul>
                <li><a href="<?php echo SITE_URL; ?>/seller/dashboard.php" class="active">Dashboard</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/sneakers.php">My Listings</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/add-sneaker.php">Add Sneaker</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/sales.php">My Sales</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/payments.php">Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Seller Dashboard</h1>
                <a href="<?php echo SITE_URL; ?>/seller/add-sneaker.php" class="btn">Add New Sneaker</a>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3><?php echo $totalListings; ?></h3>
                    <p>Total Listings</p>
                </div>
                
                <div class="stat-card">
                    <h3><?php echo $pendingListings; ?></h3>
                    <p>Pending Approval</p>
                </div>
                
                <div class="stat-card">
                    <h3><?php echo $approvedListings; ?></h3>
                    <p>Active Listings</p>
                </div>
                
                <div class="stat-card">
                    <h3><?php echo $soldListings; ?></h3>
                    <p>Sold Sneakers</p>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Recent Listings</h2>
                        <a href="<?php echo SITE_URL; ?>/seller/sneakers.php" class="view-all">View All</a>
                    </div>
                    
                    <?php if (count($recentListings) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sneaker</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date Listed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentListings as $listing): ?>
                                        <tr>
                                            <td><?php echo $listing['brand'] . ' ' . $listing['model']; ?></td>
                                            <td><?php echo formatPrice($listing['price']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $listing['status']; ?>">
                                                    <?php echo ucfirst($listing['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($listing['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/seller/edit-sneaker.php?id=<?php echo $listing['sneaker_id']; ?>" class="btn-sm">Edit</a>
                                                <a href="<?php echo SITE_URL; ?>/sneaker.php?id=<?php echo $listing['sneaker_id']; ?>" class="btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>You haven't listed any sneakers yet.</p>
                            <a href="<?php echo SITE_URL; ?>/seller/add-sneaker.php" class="btn">Add Sneaker</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Recent Sales</h2>
                        <a href="<?php echo SITE_URL; ?>/seller/sales.php" class="view-all">View All</a>
                    </div>
                    
                    <?php if (count($recentSales) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sneaker</th>
                                        <th>Price</th>
                                        <th>Sale Date</th>
                                        <th>Order ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentSales as $sale): ?>
                                        <tr>
                                            <td><?php echo $sale['brand'] . ' ' . $sale['model']; ?></td>
                                            <td><?php echo formatPrice($sale['price']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($sale['created_at'])); ?></td>
                                            <td>#<?php echo $sale['order_id']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>You haven't made any sales yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Pending Payments</h2>
                        <a href="<?php echo SITE_URL; ?>/seller/payments.php" class="view-all">View All</a>
                    </div>
                    
                    <?php if (count($pendingPayments) > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Sneaker</th>
                    <th>Amount</th>
                    <th>Platform Fee</th>
                    <th>Net Amount</th>
                    <th>Status</th>
                    <th>Requested On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingPayments as $payment): ?>
                    <tr>
                        <td><?php echo $payment['brand'] . ' ' . $payment['model']; ?></td>
                        <td><?php echo formatPrice($payment['amount']); ?></td>
                        <td><?php echo formatPrice($payment['platform_fee']); ?></td>
                        <td><?php echo formatPrice($payment['net_amount']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $payment['status']; ?>">
                                <?php echo ucfirst($payment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="no-data">
        <p>You have no pending payments.</p>
    </div>
<?php endif; ?>