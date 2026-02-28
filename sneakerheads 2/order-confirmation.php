<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
Auth::requireLogin();

$pageTitle = 'Order Confirmation';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if order ID is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: ' . SITE_URL . '/my-orders.php');
    exit;
}

$orderId = (int)$_GET['order_id'];

// Verify order belongs to user
$orderQuery = "SELECT o.*, a.* FROM orders o 
              JOIN addresses a ON o.address_id = a.address_id 
              WHERE o.order_id = '$orderId' AND o.user_id = '$userId'";
$orderResult = $db->query($orderQuery);

if ($orderResult->num_rows === 0) {
    header('Location: ' . SITE_URL . '/my-orders.php');
    exit;
}

$order = $orderResult->fetch_assoc();

// Get order items
$itemsQuery = "SELECT oi.*, s.brand, s.model, s.size, s.serial_number,
              (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image
              FROM order_items oi
              JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
              WHERE oi.order_id = '$orderId'";
$itemsResult = $db->query($itemsQuery);

$orderItems = [];
while ($row = $itemsResult->fetch_assoc()) {
    $orderItems[] = $row;
}

// Update payment status for UPI payments
if ($order['payment_method'] === 'upi' && $order['payment_status'] === 'pending') {
    $updateQuery = "UPDATE orders SET payment_status = 'completed' WHERE order_id = '$orderId'";
    $db->query($updateQuery);
    $order['payment_status'] = 'completed';
}

include 'includes/header.php';
?>

<div class="container">
    <div class="order-confirmation">
        <div class="confirmation-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your order. We've received your order and will begin processing it soon.</p>
        </div>
        
        <div class="order-details">
            <div class="order-info-card">
                <h2>Order Information</h2>
                <div class="info-row">
                    <span>Order Number:</span>
                    <span>#<?php echo $orderId; ?></span>
                </div>
                <div class="info-row">
                    <span>Order Date:</span>
                    <span><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span>Payment Method:</span>
                    <span><?php echo $order['payment_method'] === 'upi' ? 'UPI Payment' : 'Cash on Delivery'; ?></span>
                </div>
                <div class="info-row">
                    <span>Payment Status:</span>
                    <span class="status-badge <?php echo $order['payment_status']; ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span>Order Status:</span>
                    <span class="status-badge <?php echo $order['order_status']; ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span>Tracking ID:</span>
                    <span><?php echo $order['tracking_id']; ?></span>
                </div>
                <div class="info-row">
                    <span>Expected Delivery:</span>
                    <span><?php echo date('F j, Y', strtotime($order['delivery_eta'])); ?></span>
                </div>
            </div>
            
            <div class="shipping-info-card">
                <h2>Shipping Address</h2>
                <div class="address-details">
                    <p><?php echo $order['address_line1']; ?></p>
                    <?php if ($order['address_line2']): ?>
                        <p><?php echo $order['address_line2']; ?></p>
                    <?php endif; ?>
                    <p><?php echo $order['city'] . ', ' . $order['state'] . ' ' . $order['postal_code']; ?></p>
                    <p><?php echo $order['country']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="order-items-section">
            <h2>Order Items</h2>
            
            <div class="order-items-list">
                <?php foreach ($orderItems as $item): ?>
                    <div class="order-item">
                        <div class="item-image">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $item['image']; ?>" alt="<?php echo $item['brand'] . ' ' . $item['model']; ?>">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="Sneaker placeholder">
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-details">
                            <h3><?php echo $item['brand'] . ' ' . $item['model']; ?></h3>
                            <p>Size: <?php echo $item['size']; ?> UK</p>
                            <p>Serial Number: <?php echo $item['serial_number']; ?></p>
                        </div>
                        
                        <div class="item-price">
                            <?php echo formatPrice($item['price']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><?php echo formatPrice($order['total_amount'] - ($order['payment_method'] === 'cod' ? COD_FEE : 0)); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                
                <?php if ($order['payment_method'] === 'cod'): ?>
                    <div class="summary-row">
                        <span>COD Fee:</span>
                        <span><?php echo formatPrice(COD_FEE); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="summary-row total">
                    <span>Total:</span>
                    <span><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="confirmation-actions">
            <a href="<?php echo SITE_URL; ?>/my-orders.php" class="btn">View All Orders</a>
            <a href="<?php echo SITE_URL; ?>" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>

<style>
.order-confirmation {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 30px;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 30px;
}

.success-icon {
    font-size: 60px;
    color: var(--success-color);
    margin-bottom: 20px;
}

.confirmation-header h1 {
    margin-bottom: 10px;
}

.confirmation-header p {
    color: var(--text-secondary);
}

.order-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.order-info-card, .shipping-info-card {
    background-color: var(--bg-light);
    border-radius: 8px;
    padding: 20px;
}

.order-info-card h2, .shipping-info-card h2 {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 14px;
}

.status-badge.pending {
    background-color: rgba(243, 156, 18, 0.2);
    color: var(--warning-color);
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

.address-details p {
    margin-bottom: 5px;
}

.order-items-section {
    margin-bottom: 30px;
}

.order-items-section h2 {
    margin-bottom: 20px;
}

.order-items-list {
    margin-bottom: 20px;
}

.order-item {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 20px;
    padding: 15px;
    background-color: var(--bg-light);
    border-radius: 8px;
    margin-bottom: 15px;
}

.item-image {
    width: 80px;
    height: 80px;
    overflow: hidden;
    border-radius: 4px;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details h3 {
    margin-bottom: 5px;
}

.item-details p {
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.item-price {
    font-weight: bold;
    color: var(--accent-color);
}

.order-summary {
    background-color: var(--bg-light);
    border-radius: 8px;
    padding: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.summary-row.total {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid var(--border-color);
    font-size: 18px;
    font-weight: bold;
}

.confirmation-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
}

@media (max-width: 768px) {
    .order-details {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        grid-template-columns: 60px 1fr;
        grid-template-rows: auto auto;
    }
    
    .item-price {
        grid-column: 2;
        grid-row: 2;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

