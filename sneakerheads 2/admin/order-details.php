<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in and is admin
if (!Auth::isLoggedIn() || !Auth::isSuperAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = 'Order Details';

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect(SITE_URL . '/admin/orders.php');
}

$orderId = $_GET['id'];

// Get order details
$db = Database::getInstance();
$conn = $db->getConnection();

$orderQuery = "SELECT o.*, u.username, u.email, u.full_name, u.phone,
                a.address_line1, a.address_line2, a.city, a.state, a.postal_code, a.country
               FROM orders o
               JOIN users u ON o.user_id = u.user_id
               JOIN addresses a ON o.address_id = a.address_id
               WHERE o.order_id = '$orderId'";
$orderResult = $db->query($orderQuery);

if (!$orderResult || $orderResult->num_rows === 0) {
    redirect(SITE_URL . '/admin/orders.php');
}

$order = $orderResult->fetch_assoc();

// Get order items
$itemsQuery = "SELECT oi.*, s.brand, s.model, s.size, s.serial_number, u.username as seller_username
               FROM order_items oi
               JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
               JOIN users u ON s.seller_id = u.user_id
               WHERE oi.order_id = '$orderId'";
$itemsResult = $db->query($itemsQuery);
$orderItems = [];

if ($itemsResult) {
    while ($row = $itemsResult->fetch_assoc()) {
        $orderItems[] = $row;
    }
}

// Handle status update
$statusUpdated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $db->escapeString($_POST['order_status']);
    $updateQuery = "UPDATE orders SET order_status = '$newStatus' WHERE order_id = '$orderId'";
    
    if ($db->query($updateQuery)) {
        $statusUpdated = true;
        
        // Update order object with new status
        $order['order_status'] = $newStatus;
        
        // Create notification for user
        $message = "Your order #$orderId status has been updated to " . ucfirst($newStatus);
        createNotification($order['user_id'], $message);
    }
}

// Handle tracking ID update
$trackingUpdated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tracking'])) {
    $trackingId = $db->escapeString($_POST['tracking_id']);
    $updateQuery = "UPDATE orders SET tracking_id = '$trackingId' WHERE order_id = '$orderId'";
    
    if ($db->query($updateQuery)) {
        $trackingUpdated = true;
        
        // Update order object with new tracking ID
        $order['tracking_id'] = $trackingId;
        
        // Create notification for user
        $message = "Tracking ID for your order #$orderId has been updated. Track your order with ID: $trackingId";
        createNotification($order['user_id'], $message);
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="admin-content">
        <div class="admin-header">
            <h1>Order #<?php echo $orderId; ?> Details</h1>
            <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
        
        <?php if ($statusUpdated): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Order status has been updated successfully.
            </div>
        <?php endif; ?>
        
        <?php if ($trackingUpdated): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Tracking ID has been updated successfully.
            </div>
        <?php endif; ?>
        
        <div class="order-details-container">
            <div class="order-details-grid">
                <!-- Order Information -->
                <div class="order-info-card">
                    <h2>Order Information</h2>
                    <div class="info-group">
                        <div class="info-label">Order ID:</div>
                        <div class="info-value">#<?php echo $orderId; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Order Date:</div>
                        <div class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Order Status:</div>
                        <div class="info-value">
                            <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Payment Method:</div>
                        <div class="info-value"><?php echo strtoupper($order['payment_method']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Payment Status:</div>
                        <div class="info-value">
                            <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Total Amount:</div>
                        <div class="info-value"><?php echo formatPrice($order['total_amount']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Tracking ID:</div>
                        <div class="info-value">
                            <?php echo !empty($order['tracking_id']) ? $order['tracking_id'] : 'Not available'; ?>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Delivery ETA:</div>
                        <div class="info-value">
                            <?php echo !empty($order['delivery_eta']) ? date('F j, Y', strtotime($order['delivery_eta'])) : 'Not available'; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Information -->
                <div class="order-info-card">
                    <h2>Customer Information</h2>
                    <div class="info-group">
                        <div class="info-label">Name:</div>
                        <div class="info-value"><?php echo $order['full_name']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Username:</div>
                        <div class="info-value"><?php echo $order['username']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?php echo $order['email']; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone:</div>
                        <div class="info-value"><?php echo !empty($order['phone']) ? $order['phone'] : 'Not provided'; ?></div>
                    </div>
                </div>
                
                <!-- Shipping Address -->
                <div class="order-info-card">
                    <h2>Shipping Address</h2>
                    <div class="address-details">
                        <p><?php echo $order['full_name']; ?></p>
                        <p><?php echo $order['address_line1']; ?></p>
                        <?php if (!empty($order['address_line2'])): ?>
                            <p><?php echo $order['address_line2']; ?></p>
                        <?php endif; ?>
                        <p><?php echo $order['city'] . ', ' . $order['state'] . ' ' . $order['postal_code']; ?></p>
                        <p><?php echo $order['country']; ?></p>
                        <?php if (!empty($order['phone'])): ?>
                            <p>Phone: <?php echo $order['phone']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Order Actions -->
                <div class="order-info-card">
                    <h2>Order Actions</h2>
                    
                    <!-- Update Order Status -->
                    <form action="" method="POST" class="action-form">
                        <div class="form-group">
                            <label for="order_status">Update Order Status:</label>
                            <select name="order_status" id="order_status" class="form-control">
                                <option value="placed" <?php echo $order['order_status'] === 'placed' ? 'selected' : ''; ?>>Placed</option>
                                <option value="processing" <?php echo $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </form>
                    
                    <!-- Update Tracking ID -->
                    <form action="" method="POST" class="action-form">
                        <div class="form-group">
                            <label for="tracking_id">Update Tracking ID:</label>
                            <input type="text" name="tracking_id" id="tracking_id" class="form-control" 
                                   value="<?php echo !empty($order['tracking_id']) ? $order['tracking_id'] : ''; ?>" 
                                   placeholder="Enter tracking ID">
                        </div>
                        <button type="submit" name="update_tracking" class="btn btn-primary">Update Tracking</button>
                    </form>
                    
                    <!-- Print Invoice -->
                    <a href="<?php echo SITE_URL; ?>/admin/print-invoice.php?id=<?php echo $orderId; ?>" 
                       class="btn btn-secondary" target="_blank">
                        <i class="fas fa-print"></i> Print Invoice
                    </a>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="order-items-section">
                <h2>Order Items</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Seller</th>
                                <th>Serial Number</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="item-info">
                                            <?php
                                            // Get sneaker image
                                            $imageQuery = "SELECT image_path FROM sneaker_images WHERE sneaker_id = '{$item['sneaker_id']}' LIMIT 1";
                                            $imageResult = $db->query($imageQuery);
                                            $imagePath = 'assets/images/placeholder.jpg';
                                            
                                            if ($imageResult && $imageResult->num_rows > 0) {
                                                $imageRow = $imageResult->fetch_assoc();
                                                $imagePath = 'assets/uploads/sneakers/' . $imageRow['image_path'];
                                            }
                                            ?>
                                            <div class="item-image">
                                                <img src="<?php echo SITE_URL . '/' . $imagePath; ?>" alt="<?php echo $item['brand'] . ' ' . $item['model']; ?>">
                                            </div>
                                            <div class="item-details">
                                                <div class="item-name"><?php echo $item['brand'] . ' ' . $item['model']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $item['seller_username']; ?></td>
                                    <td><?php echo $item['serial_number']; ?></td>
                                    <td><?php echo $item['size']; ?> UK</td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>/sneaker.php?id=<?php echo $item['sneaker_id']; ?>" 
                                           class="btn btn-sm" target="_blank">
                                            View Sneaker
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .order-details-container {
        margin-top: 20px;
    }
    
    .order-details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .order-info-card {
        background-color: var(--bg-secondary);
        border-radius: 8px;
        padding: 20px;
    }
    
    .order-info-card h2 {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .info-group {
        display: flex;
        margin-bottom: 10px;
    }
    
    .info-label {
        font-weight: bold;
        width: 150px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .status-placed {
        background-color: rgba(52, 152, 219, 0.2);
        color: #3498db;
    }
    
    .status-processing {
        background-color: rgba(243, 156, 18, 0.2);
        color: #f39c12;
    }
    
    .status-shipped {
        background-color: rgba(155, 89, 182, 0.2);
        color: #9b59b6;
    }
    
    .status-delivered {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
    }
    
    .status-cancelled {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }
    
    .status-pending {
        background-color: rgba(243, 156, 18, 0.2);
        color: #f39c12;
    }
    
    .status-completed {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
    }
    
    .status-failed {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }
    
    .address-details p {
        margin-bottom: 5px;
    }
    
    .action-form {
        margin-bottom: 20px;
    }
    
    .action-form .form-group {
        margin-bottom: 10px;
    }
    
    .action-form label {
        display: block;
        margin-bottom: 5px;
    }
    
    .action-form .form-control {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid var(--border-color);
        background-color: var(--bg-light);
        color: var(--text-color);
    }
    
    .order-items-section {
        background-color: var(--bg-secondary);
        border-radius: 8px;
        padding: 20px;
    }
    
    .order-items-section h2 {
        margin-bottom: 15px;
    }
    
    .item-info {
        display: flex;
        align-items: center;
    }
    
    .item-image {
        width: 60px;
        height: 60px;
        border-radius: 4px;
        overflow: hidden;
        margin-right: 10px;
    }
    
    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .item-name {
        font-weight: 500;
    }
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 14px;
    }
    
    @media (max-width: 768px) {
        .order-details-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>

