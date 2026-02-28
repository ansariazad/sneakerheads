<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require seller access
Auth::requireSeller();

$pageTitle = 'Sale Details';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if order ID and sneaker ID are provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id']) || !isset($_GET['sneaker_id']) || !is_numeric($_GET['sneaker_id'])) {
    header('Location: ' . SITE_URL . '/seller/sales.php');
    exit;
}

$orderId = (int)$_GET['order_id'];
$sneakerId = (int)$_GET['sneaker_id'];

// Get sale details
$saleQuery = "SELECT s.*, u.username as seller_username, 
             o.order_id, o.user_id as buyer_id, o.created_at as order_date, o.order_status, o.tracking_id, o.delivery_eta,
             oi.price as sale_price,
             bu.username as buyer_username, bu.email as buyer_email
             FROM sneakers s
             JOIN users u ON s.seller_id = u.user_id
             JOIN order_items oi ON s.sneaker_id = oi.sneaker_id
             JOIN orders o ON oi.order_id = o.order_id
             JOIN users bu ON o.user_id = bu.user_id
             WHERE s.sneaker_id = '$sneakerId' AND o.order_id = '$orderId' AND s.seller_id = '$userId'";
$saleResult = $db->query($saleQuery);

if ($saleResult->num_rows === 0) {
    header('Location: ' . SITE_URL . '/seller/sales.php');
    exit;
}

$sale = $saleResult->fetch_assoc();

// Get sneaker images
$imagesQuery = "SELECT * FROM sneaker_images WHERE sneaker_id = '$sneakerId'";
$imagesResult = $db->query($imagesQuery);

$images = [];
while ($row = $imagesResult->fetch_assoc()) {
    $images[] = $row;
}

// Get payment status
$paymentQuery = "SELECT p.* FROM payments p
               JOIN order_items oi ON p.order_item_id = oi.item_id
               WHERE oi.order_id = '$orderId' AND oi.sneaker_id = '$sneakerId'";
$paymentResult = $db->query($paymentQuery);
$payment = $paymentResult->num_rows > 0 ? $paymentResult->fetch_assoc() : null;

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
                <h1>Sale Details</h1>
                <a href="<?php echo SITE_URL; ?>/seller/sales.php" class="btn btn-secondary">Back to Sales</a>
            </div>
            
            <div class="sale-details">
                <div class="sale-header">
                    <div class="sale-info">
                        <h2><?php echo $sale['brand'] . ' ' . $sale['model']; ?></h2>
                        <div class="sale-meta">
                            <span>Order #<?php echo $sale['order_id']; ?></span>
                            <span>•</span>
                            <span>Sold on <?php echo date('F j, Y', strtotime($sale['order_date'])); ?></span>
                        </div>
                    </div>
                    <div class="sale-status">
                        <span class="status-badge <?php echo $sale['order_status']; ?>">
                            <?php echo ucfirst($sale['order_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="sale-content">
                    <div class="sneaker-details">
                        <div class="sneaker-images">
                            <?php if (count($images) > 0): ?>
                                <div class="main-image">
                                    <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $images[0]['image_path']; ?>" alt="<?php echo $sale['brand'] . ' ' . $sale['model']; ?>">
                                </div>
                                
                                <?php if (count($images) > 1): ?>
                                    <div class="thumbnail-images">
                                        <?php foreach ($images as $index => $image): ?>
                                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>">
                                                <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $image['image_path']; ?>" alt="<?php echo $sale['brand'] . ' ' . $sale['model']; ?> - <?php echo $image['image_type']; ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="main-image">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="Sneaker placeholder">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="sneaker-info">
                            <div class="info-section">
                                <h3>Sneaker Details</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Brand</span>
                                        <span class="info-value"><?php echo $sale['brand']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Model</span>
                                        <span class="info-value"><?php echo $sale['model']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Size</span>
                                        <span class="info-value"><?php echo $sale['size']; ?> UK</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Serial Number</span>
                                        <span class="info-value"><?php echo $sale['serial_number']; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-section">
                                <h3>Sale Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Sale Price</span>
                                        <span class="info-value"><?php echo formatPrice($sale['sale_price']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Platform Fee</span>
                                        <span class="info-value"><?php echo formatPrice($sale['sale_price'] * (PLATFORM_FEE_PERCENTAGE / 100)); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Your Earnings</span>
                                        <span class="info-value"><?php echo formatPrice($sale['sale_price'] * (1 - PLATFORM_FEE_PERCENTAGE / 100)); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Payment Status</span>
                                        <span class="info-value">
                                            <?php if ($payment): ?>
                                                <span class="status-badge <?php echo $payment['status']; ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge pending">Not Requested</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-section">
                                <h3>Order Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Order Date</span>
                                        <span class="info-value"><?php echo date('F j, Y', strtotime($sale['order_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Order Status</span>
                                        <span class="info-value">
                                            <span class="status-badge <?php echo $sale['order_status']; ?>">
                                                <?php echo ucfirst($sale['order_status']); ?>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Tracking ID</span>
                                        <span class="info-value"><?php echo $sale['tracking_id'] ?? 'N/A'; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Expected Delivery</span>
                                        <span class="info-value"><?php echo $sale['delivery_eta'] ? date('F j, Y', strtotime($sale['delivery_eta'])) : 'N/A'; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-section">
                                <h3>Buyer Information</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Username</span>
                                        <span class="info-value"><?php echo $sale['buyer_username']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Email</span>
                                        <span class="info-value"><?php echo $sale['buyer_email']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="sale-actions">
                    <?php if ($sale['order_status'] === 'delivered' && !$payment): ?>
                        <form method="POST" action="<?php echo SITE_URL; ?>/seller/payments.php">
                            <input type="hidden" name="order_item_id" value="<?php echo $sale['item_id']; ?>">
                            <button type="submit" name="request_payment" class="btn btn-success">Request Payment</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sale-details {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    overflow: hidden;
}

.sale-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.sale-meta {
    display: flex;
    gap: 10px;
    color: var(--text-secondary);
    margin-top: 5px;
}

.sale-content {
    padding: 20px;
}

.sneaker-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.sneaker-images {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.main-image {
    height: 300px;
    border-radius: 8px;
    overflow: hidden;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-images {
    display: flex;
    gap: 10px;
}

.thumbnail {
    width: 60px;
    height: 60px;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
}

.thumbnail.active {
    border-color: var(--primary-color);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sneaker-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-section {
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.info-section:last-child {
    border-bottom: none;
}

.info-section h3 {
    margin-bottom: 15px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-label {
    font-size: 12px;
    color: var(--text-secondary);
}

.info-value {
    font-weight: bold;
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

.status-badge.requested, .status-badge.pending {
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

.sale-actions {
    padding: 20px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
}

@media (max-width: 992px) {
    .sneaker-details {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image gallery functionality
    const mainImage = document.querySelector('.main-image img');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Update main image
            mainImage.src = this.querySelector('img').src;
            
            // Update active thumbnail
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
