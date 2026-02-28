<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Redirect if not logged in
if (!Auth::isLoggedIn()) {
    header("Location: login.php?redirect=my-orders.php");
    exit;
}

// Redirect if superadmin
if (Auth::isSuperAdmin()) {
    header("Location: admin/dashboard.php");
    exit;
}

$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];
$isSeller = Auth::canSell();

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 5;
$offset = ($page - 1) * $itemsPerPage;

// Get orders based on user type
if ($isSeller) {
    // For sellers, get orders containing their products
    $countQuery = "SELECT COUNT(DISTINCT o.order_id) as total 
                  FROM orders o
                  JOIN order_items oi ON o.order_id = oi.order_id
                  JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
                  WHERE s.seller_id = '$userId'";
                  
    $ordersQuery = "SELECT o.*, a.address_line1, a.city, a.state, a.postal_code, a.country,
                         u.username, u.email, u.full_name, u.phone
                  FROM orders o
                  JOIN addresses a ON o.address_id = a.address_id
                  JOIN users u ON o.user_id = u.user_id
                  JOIN order_items oi ON o.order_id = oi.order_id
                  JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
                  WHERE s.seller_id = '$userId'
                  GROUP BY o.order_id
                  ORDER BY o.created_at DESC
                  LIMIT $offset, $itemsPerPage";
} else {
    // For buyers, get their orders
    $countQuery = "SELECT COUNT(*) as total FROM orders o WHERE o.user_id = '$userId'";
    
    $ordersQuery = "SELECT o.*, a.address_line1, a.city, a.state, a.postal_code, a.country
                  FROM orders o
                  JOIN addresses a ON o.address_id = a.address_id
                  WHERE o.user_id = '$userId'
                  ORDER BY o.created_at DESC
                  LIMIT $offset, $itemsPerPage";
}

// Get total count
$countResult = $db->query($countQuery);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get orders
$ordersResult = $db->query($ordersQuery);
$orders = [];

if ($ordersResult && $ordersResult->num_rows > 0) {
    while ($order = $ordersResult->fetch_assoc()) {
        // Get order items
        $itemsQuery = "SELECT oi.*, s.brand, s.model, s.size, s.price, s.seller_id,
                           (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image,
                           u.username as seller_username
                      FROM order_items oi
                      JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
                      JOIN users u ON s.seller_id = u.user_id
                      WHERE oi.order_id = '{$order['order_id']}'";
        
        $itemsResult = $db->query($itemsQuery);
        $items = [];
        
        if ($itemsResult && $itemsResult->num_rows > 0) {
            while ($item = $itemsResult->fetch_assoc()) {
                $items[] = $item;
            }
        }
        
        $order['items'] = $items;
        
        // Calculate tracking stages based on days since order
        $orderDate = strtotime($order['created_at']);
        $currentDate = time();
        $daysSinceOrder = floor(($currentDate - $orderDate) / (60 * 60 * 24));
        
        // Set tracking stage (1-5) based on days since order
        if ($order['order_status'] === 'cancelled') {
            $order['tracking_stage'] = 0; // Cancelled
        } else if ($order['order_status'] === 'delivered') {
            $order['tracking_stage'] = 5; // Delivered
        } else {
            // Calculate stage based on days (5-day delivery window)
            if ($daysSinceOrder < 1) {
                $order['tracking_stage'] = 1; // Picked from seller
            } else if ($daysSinceOrder < 2) {
                $order['tracking_stage'] = 2; // At delivery office
            } else if ($daysSinceOrder < 3) {
                $order['tracking_stage'] = 3; // Out for delivery
            } else if ($daysSinceOrder < 4) {
                $order['tracking_stage'] = 4; // On the way
            } else {
                $order['tracking_stage'] = 5; // Delivered
                
                // If it's been 5+ days and status isn't updated, update it to delivered
                if ($order['order_status'] !== 'delivered') {
                    $updateQuery = "UPDATE orders SET order_status = 'delivered' WHERE order_id = '{$order['order_id']}'";
                    $db->query($updateQuery);
                    $order['order_status'] = 'delivered';
                }
            }
        }
        
        $orders[] = $order;
    }
}

// Set page title
$pageTitle = 'My Orders';

// Include header
include 'includes/header.php';
?>

<div class="container">
    <div class="orders-container">
        <div class="orders-header">
            <h1>My Orders</h1>
        </div>

        <?php if (count($orders) > 0): ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Order #<?php echo $order['order_id']; ?></h3>
                                <p>Placed on: <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                <p>Delivery ETA: <?php echo date('M d, Y', strtotime($order['delivery_eta'])); ?></p>
                                <p>Status: <span class="status-badge status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></p>
                            </div>
                            <div class="order-total">
                                <p>Total: <?php echo formatPrice($order['total_amount']); ?></p>
                                <button class="btn track-order-btn" data-order="<?php echo $order['order_id']; ?>">Track Order</button>
                            </div>
                        </div>
                        
                        <div class="order-tracking" id="tracking-<?php echo $order['order_id']; ?>" style="display: none;">
                            <div class="tracking-stages">
                                <div class="tracking-stage <?php echo $order['tracking_stage'] >= 1 ? 'active' : ''; ?> <?php echo $order['tracking_stage'] === 1 ? 'current' : ''; ?>">
                                    <div class="stage-icon"><i class="fas fa-box"></i></div>
                                    <div class="stage-label">Picked from seller</div>
                                    <div class="stage-date"><?php echo date('M d', strtotime($order['created_at'])); ?></div>
                                </div>
                                <div class="tracking-stage <?php echo $order['tracking_stage'] >= 2 ? 'active' : ''; ?> <?php echo $order['tracking_stage'] === 2 ? 'current' : ''; ?>">
                                    <div class="stage-icon"><i class="fas fa-warehouse"></i></div>
                                    <div class="stage-label">At delivery office</div>
                                    <div class="stage-date"><?php echo date('M d', strtotime($order['created_at'] . ' +1 day')); ?></div>
                                </div>
                                <div class="tracking-stage <?php echo $order['tracking_stage'] >= 3 ? 'active' : ''; ?> <?php echo $order['tracking_stage'] === 3 ? 'current' : ''; ?>">
                                    <div class="stage-icon"><i class="fas fa-truck"></i></div>
                                    <div class="stage-label">Out for delivery</div>
                                    <div class="stage-date"><?php echo date('M d', strtotime($order['created_at'] . ' +2 days')); ?></div>
                                </div>
                                <div class="tracking-stage <?php echo $order['tracking_stage'] >= 4 ? 'active' : ''; ?> <?php echo $order['tracking_stage'] === 4 ? 'current' : ''; ?>">
                                    <div class="stage-icon"><i class="fas fa-route"></i></div>
                                    <div class="stage-label">On the way</div>
                                    <div class="stage-date"><?php echo date('M d', strtotime($order['created_at'] . ' +3 days')); ?></div>
                                </div>
                                <div class="tracking-stage <?php echo $order['tracking_stage'] >= 5 ? 'active' : ''; ?> <?php echo $order['tracking_stage'] === 5 ? 'current' : ''; ?>">
                                    <div class="stage-icon"><i class="fas fa-check-circle"></i></div>
                                    <div class="stage-label">Delivered</div>
                                    <div class="stage-date"><?php echo date('M d', strtotime($order['created_at'] . ' +4 days')); ?></div>
                                </div>
                            </div>
                            <?php if ($order['order_status'] === 'cancelled'): ?>
                                <div class="cancelled-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <p>This order has been cancelled.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="order-items">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <?php
                                        $image = !empty($item['image']) ? 'assets/uploads/sneakers/' . $item['image'] : 'assets/images/placeholder.jpg';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($item['brand'] . ' ' . $item['model']); ?>">
                                    </div>
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($item['brand'] . ' ' . $item['model']); ?></h4>
                                        <p>Size: <?php echo htmlspecialchars($item['size']); ?> UK</p>
                                        <p>Price: <?php echo formatPrice($item['price']); ?></p>
                                        <p>Seller: <?php echo htmlspecialchars($item['seller_username']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-address">
                            <h4>Shipping Address:</h4>
                            <p><?php echo htmlspecialchars($order['address_line1']); ?></p>
                            <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' - ' . $order['postal_code']); ?></p>
                            <p><?php echo htmlspecialchars($order['country']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
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
            <div class="no-orders">
                <i class="fas fa-shopping-bag fa-4x"></i>
                <h2>No orders found</h2>
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" class="btn">Browse Sneakers</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.orders-container {
    padding: 30px 0;
}

.orders-header {
    margin-bottom: 30px;
}

.orders-header h1 {
    font-size: 28px;
    margin-bottom: 15px;
    color: #333;
    position: relative;
    display: inline-block;
}

.orders-header h1:after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #3a86ff, #5e60ce);
    border-radius: 3px;
}

.order-card {
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    padding: 25px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.order-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.order-info h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #333;
}

.order-info p {
    margin: 8px 0;
    color: #666;
}

.order-total {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.order-total p {
    margin: 8px 0;
    color: #666;
    font-size: 18px;
    font-weight: 600;
}

.track-order-btn {
    margin-top: 10px;
    padding: 8px 15px;
    background: linear-gradient(90deg, #3a86ff, #5e60ce);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.track-order-btn:hover {
    background: linear-gradient(90deg, #5e60ce, #3a86ff);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(58, 134, 255, 0.3);
}

.status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-placed {
    background-color: #e2f0fd;
    color: #0c63e4;
}

.status-processing {
    background-color: #fff4de;
    color: #ff9800;
}

.status-shipped {
    background-color: #e0f5ea;
    color: #00c853;
}

.status-delivered {
    background-color: #d4edda;
    color: #155724;
}

.status-cancelled {
    background-color: #f8d7da;
    color: #721c24;
}

/* Order Tracking */
.order-tracking {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 20px;
}

.tracking-stages {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 20px;
}

.tracking-stages:before {
    content: '';
    position: absolute;
    top: 30px;
    left: 50px;
    right: 50px;
    height: 4px;
    background-color: #e9ecef;
    z-index: 1;
}

.tracking-stage {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}

.stage-icon {
    width: 60px;
    height: 60px;
    background-color: #fff;
    border: 2px solid #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 24px;
    color: #adb5bd;
    transition: all 0.3s ease;
}

.stage-label {
    font-size: 14px;
    font-weight: 600;
    color: #adb5bd;
    margin-bottom: 5px;
    transition: all 0.3s ease;
}

.stage-date {
    font-size: 12px;
    color: #adb5bd;
    transition: all 0.3s ease;
}

.tracking-stage.active .stage-icon {
    border-color: #3a86ff;
    color: #3a86ff;
}

.tracking-stage.active .stage-label,
.tracking-stage.active .stage-date {
    color: #495057;
}

.tracking-stage.current .stage-icon {
    background-color: #3a86ff;
    border-color: #3a86ff;
    color: white;
    box-shadow: 0 0 0 5px rgba(58, 134, 255, 0.2);
}

.tracking-stage.current .stage-label {
    color: #3a86ff;
    font-weight: 700;
}

.cancelled-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    margin-top: 20px;
}

.cancelled-message i {
    font-size: 24px;
    margin-right: 15px;
}

.cancelled-message p {
    margin: 0;
    font-weight: 600;
}

.order-items {
    margin-bottom: 25px;
}

.order-item {
    display: flex;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.order-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.item-image {
    width: 120px;
    height: 120px;
    margin-right: 20px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    position: relative;
}

.item-image:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.05) 0%, rgba(0,0,0,0) 100%);
    z-index: 1;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.item-image:hover img {
    transform: scale(1.1);
}

.item-details {
    flex: 1;
}

.item-details h4 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #333;
}

.item-details p {
    margin: 8px 0;
    color: #666;
    font-size: 14px;
}

.item-details p:nth-child(2) {
    font-weight: 600;
    color: #444;
}

.order-address {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid #3a86ff;
}

.order-address h4 {
    margin-bottom: 12px;
    color: #333;
    font-size: 16px;
}

.order-address p {
    margin: 8px 0;
    color: #666;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.pagination a, .pagination span {
    margin: 0 5px;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
}

.pagination a:hover {
    background-color: #f5f5f5;
    border-color: #3a86ff;
}

.pagination span.active {
    background: linear-gradient(90deg, #3a86ff, #5e60ce);
    color: white;
    border-color: #3a86ff;
}

.no-orders {
    text-align: center;
    padding: 60px 0;
    background-color: #f9f9f9;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.no-orders i {
    color: #ccc;
    margin-bottom: 25px;
}

.no-orders h2 {
    margin-bottom: 15px;
    color: #333;
    font-size: 24px;
}

.no-orders p {
    color: #666;
    margin-bottom: 25px;
    font-size: 16px;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
    }
    
    .order-total {
        margin-top: 15px;
        text-align: left;
        align-items: flex-start;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .item-image {
        width: 100%;
        height: 200px;
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .tracking-stages {
        flex-direction: column;
    }
    
    .tracking-stages:before {
        top: 0;
        bottom: 0;
        left: 30px;
        right: auto;
        width: 4px;
        height: auto;
    }
    
    .tracking-stage {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        text-align: left;
    }
    
    .stage-icon {
        margin: 0 20px 0 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Track order button functionality
    const trackButtons = document.querySelectorAll('.track-order-btn');
    
    trackButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order');
            const trackingDiv = document.getElementById('tracking-' + orderId);
            
            if (trackingDiv.style.display === 'none') {
                trackingDiv.style.display = 'block';
                this.textContent = 'Hide Tracking';
            } else {
                trackingDiv.style.display = 'none';
                this.textContent = 'Track Order';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 