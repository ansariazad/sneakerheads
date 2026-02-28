<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require seller access
Auth::requireSeller();

$pageTitle = 'Payments';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle payment request
$successMessage = '';
$errorMessage = '';

if (isset($_POST['request_payment']) && isset($_POST['order_item_id']) && is_numeric($_POST['order_item_id'])) {
    $orderItemId = (int)$_POST['order_item_id'];
    
    // Check if payment already requested
    $checkQuery = "SELECT payment_id FROM payments WHERE seller_id = '$userId' AND order_item_id = '$orderItemId'";
    $checkResult = $db->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $errorMessage = 'Payment has already been requested for this item';
    } else {
        // Get order item details
        $itemQuery = "SELECT oi.item_id, oi.price, s.brand, s.model 
                    FROM order_items oi 
                    JOIN sneakers s ON oi.sneaker_id = s.sneaker_id 
                    WHERE oi.item_id = '$orderItemId' AND s.seller_id = '$userId'";
        $itemResult = $db->query($itemQuery);
        
        if ($itemResult->num_rows === 0) {
            $errorMessage = 'Invalid order item';
        } else {
            $item = $itemResult->fetch_assoc();
            $amount = $item['price'];
            $platformFee = $amount * (PLATFORM_FEE_PERCENTAGE / 100);
            $netAmount = $amount - $platformFee;
            
            // Create payment request
            $insertQuery = "INSERT INTO payments (
                          seller_id, order_item_id, amount, platform_fee, net_amount
                          ) VALUES (
                          '$userId', '$orderItemId', '$amount', '$platformFee', '$netAmount'
                          )";
            
            if ($db->query($insertQuery)) {
                $successMessage = 'Payment request submitted successfully';
                
                // Create notification for admin
                $adminQuery = "SELECT user_id FROM users WHERE user_type = 'superadmin' LIMIT 1";
                $adminResult = $db->query($adminQuery);
                
                if ($adminResult->num_rows > 0) {
                    $adminId = $adminResult->fetch_assoc()['user_id'];
                    $notificationMessage = "New payment request from seller for {$item['brand']} {$item['model']}";
                    createNotification($adminId, $notificationMessage);
                }
            } else {
                $errorMessage = 'Failed to submit payment request';
            }
        }
    }
}

// Get pending payments
$pendingQuery = "SELECT p.payment_id, p.amount, p.platform_fee, p.net_amount, p.status, p.created_at,
               s.brand, s.model, o.order_id
               FROM payments p
               JOIN order_items oi ON p.order_item_id = oi.item_id
               JOIN orders o ON oi.order_id = o.order_id
               JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
               WHERE p.seller_id = '$userId' AND p.status IN ('requested', 'processing')
               ORDER BY p.created_at DESC";
$pendingResult = $db->query($pendingQuery);

$pendingPayments = [];
while ($row = $pendingResult->fetch_assoc()) {
    $pendingPayments[] = $row;
}

// Get completed payments
$completedQuery = "SELECT p.payment_id, p.amount, p.platform_fee, p.net_amount, p.status, p.created_at,
                s.brand, s.model, o.order_id
                FROM payments p
                JOIN order_items oi ON p.order_item_id = oi.item_id
                JOIN orders o ON oi.order_id = o.order_id
                JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
                WHERE p.seller_id = '$userId' AND p.status = 'completed'
                ORDER BY p.created_at DESC";
$completedResult = $db->query($completedQuery);

$completedPayments = [];
while ($row = $completedResult->fetch_assoc()) {
    $completedPayments[] = $row;
}

// Get eligible items for payment request
$eligibleQuery = "SELECT oi.item_id, oi.price, s.brand, s.model, o.order_id, o.created_at
               FROM order_items oi
               JOIN orders o ON oi.order_id = o.order_id
               JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
               LEFT JOIN payments p ON oi.item_id = p.order_item_id
               WHERE s.seller_id = '$userId' 
               AND s.status = 'sold' 
               AND o.order_status = 'delivered'
               AND p.payment_id IS NULL
               ORDER BY o.created_at DESC";
$eligibleResult = $db->query($eligibleQuery);

$eligibleItems = [];
while ($row = $eligibleResult->fetch_assoc()) {
    $eligibleItems[] = $row;
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
                <li><a href="<?php echo SITE_URL; ?>/seller/sales.php">My Sales</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/payments.php" class="active">Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Payments</h1>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="payments-tabs">
                <div class="tabs-header">
                    <button class="tab-btn active" data-tab="eligible">Eligible for Payment</button>
                    <button class="tab-btn" data-tab="pending">Pending Payments</button>
                    <button class="tab-btn" data-tab="completed">Completed Payments</button>
                </div>
                
                <div class="tab-content active" id="eligible-tab">
                    <h2>Eligible for Payment</h2>
                    
                    <?php if (count($eligibleItems) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sneaker</th>
                                        <th>Order ID</th>
                                        <th>Sale Date</th>
                                        <th>Amount</th>
                                        <th>Platform Fee</th>
                                        <th>Net Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($eligibleItems as $item): ?>
                                        <tr>
                                            <td><?php echo $item['brand'] . ' ' . $item['model']; ?></td>
                                            <td>#<?php echo $item['order_id']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                                            <td><?php echo formatPrice($item['price']); ?></td>
                                            <td><?php echo formatPrice($item['price'] * (PLATFORM_FEE_PERCENTAGE / 100)); ?></td>
                                            <td><?php echo formatPrice($item['price'] * (1 - PLATFORM_FEE_PERCENTAGE / 100)); ?></td>
                                            <td>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="order_item_id" value="<?php echo $item['item_id']; ?>">
                                                    <button type="submit" name="request_payment" class="btn-sm">Request Payment</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>You don't have any items eligible for payment request.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="tab-content" id="pending-tab">
                    <h2>Pending Payments</h2>
                    
                    <?php if (count($pendingPayments) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sneaker</th>
                                        <th>Order ID</th>
                                        <th>Request Date</th>
                                        <th>Amount</th>
                                        <th>Platform Fee</th>
                                        <th>Net Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingPayments as $payment): ?>
                                        <tr>
                                            <td><?php echo $payment['brand'] . ' ' . $payment['model']; ?></td>
                                            <td>#<?php echo $payment['order_id']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                                            <td><?php echo formatPrice($payment['amount']); ?></td>
                                            <td><?php echo formatPrice($payment['platform_fee']); ?></td>
                                            <td><?php echo formatPrice($payment['net_amount']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $payment['status']; ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>You don't have any pending payment requests.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="tab-content" id="completed-tab">
                    <h2>Completed Payments</h2>
                    
                    <?php if (count($completedPayments) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sneaker</th>
                                        <th>Order ID</th>
                                        <th>Payment Date</th>
                                        <th>Amount</th>
                                        <th>Platform Fee</th>
                                        <th>Net Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($completedPayments as $payment): ?>
                                        <tr>
                                            <td><?php echo $payment['brand'] . ' ' . $payment['model']; ?></td>
                                            <td>#<?php echo $payment['order_id']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                                            <td><?php echo formatPrice($payment['amount']); ?></td>
                                            <td><?php echo formatPrice($payment['platform_fee']); ?></td>
                                            <td><?php echo formatPrice($payment['net_amount']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>You don't have any completed payments yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payments-tabs {
    background-color: var(--bg-secondary);
    backdrop-filter: var(--glass-blur);
    -webkit-backdrop-filter: var(--glass-blur);
    border: var(--glass-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow);
    margin-bottom: 30px;
}

.tabs-header {
    display: flex;
    border-bottom: var(--glass-border);
}

.tab-btn {
    flex: 1;
    padding: 15px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: var(--text-color);
    transition: var(--transition);
}

.tab-btn:hover {
    background-color: var(--bg-light);
}

.tab-btn.active {
    background-color: var(--bg-light);
    font-weight: bold;
    border-bottom: 2px solid var(--primary-color);
}

.tab-content {
    display: none;
    padding: 20px;
}

.tab-content.active {
    display: block;
}

.tab-content h2 {
    margin-bottom: 20px;
    font-size: 24px;
    color: var(--text-color);
}

.status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.requested {
    background-color: rgba(243, 156, 18, 0.2);
    color: var(--warning-color);
}

.status-badge.processing {
    background-color: rgba(52, 152, 219, 0.2);
    color: var(--info-color);
}

.status-badge.completed {
    background-color: rgba(46, 204, 113, 0.2);
    color: var(--success-color);
}

.status-badge.rejected {
    background-color: rgba(231, 76, 60, 0.2);
    color: var(--error-color);
}

.btn-sm {
    padding: 8px 12px;
    font-size: 14px;
    background: rgba(52, 152, 219, 0.7);
    backdrop-filter: var(--glass-blur);
    -webkit-backdrop-filter: var(--glass-blur);
    color: white;
    border: var(--glass-border);
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
}

.btn-sm:hover {
    background: rgba(41, 128, 185, 0.8);
    transform: translateY(-2px);
}

.no-data {
    text-align: center;
    padding: 30px;
    color: var(--text-secondary);
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: var(--glass-border);
}

table th {
    background-color: var(--bg-light);
    font-weight: 600;
    color: var(--text-color);
}

table tr:hover {
    background-color: var(--bg-light);
}

@media (max-width: 768px) {
    .tabs-header {
        flex-direction: column;
    }
    
    .tab-btn {
        text-align: left;
        padding: 12px 15px;
    }
    
    .tab-btn.active {
        border-bottom: none;
        border-left: 2px solid var(--primary-color);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabBtns.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
