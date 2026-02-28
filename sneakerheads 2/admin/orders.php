<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require superadmin access
Auth::requireSuperAdmin();

$pageTitle = 'Manage Orders';
$currentUser = Auth::getCurrentUser();

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle search and filters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$orderStatus = isset($_GET['order_status']) && in_array($_GET['order_status'], ['all', 'placed', 'processing', 'shipped', 'delivered', 'cancelled']) 
            ? $_GET['order_status'] : 'all';
$paymentStatus = isset($_GET['payment_status']) && in_array($_GET['payment_status'], ['all', 'pending', 'completed', 'failed']) 
            ? $_GET['payment_status'] : 'all';

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Build query
$query = "SELECT o.*, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE 1=1";

if (!empty($search)) {
    $search = $db->escapeString($search);
    $query .= " AND (o.order_id LIKE '%$search%' OR u.username LIKE '%$search%' OR o.tracking_id LIKE '%$search%')";
}

if ($orderStatus !== 'all') {
    $query .= " AND o.order_status = '$orderStatus'";
}

if ($paymentStatus !== 'all') {
    $query .= " AND o.payment_status = '$paymentStatus'";
}

// Count total items for pagination
$countQuery = str_replace("SELECT o.*, u.username", "SELECT COUNT(*) as total", $query);
$countResult = $db->query($countQuery);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Add pagination to query
$query .= " ORDER BY o.created_at DESC LIMIT $offset, $itemsPerPage";
$result = $db->query($query);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Handle order actions
$successMessage = '';
$errorMessage = '';

// Update order status
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['order_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitizeInput($_POST['order_status']);
    $trackingId = isset($_POST['tracking_id']) ? sanitizeInput($_POST['tracking_id']) : '';
    
    if (!in_array($newStatus, ['placed', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        $errorMessage = 'Invalid order status';
    } else {
        // Get current order details
        $orderQuery = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = '$orderId'";
        $orderResult = $db->query($orderQuery);
        
        if ($orderResult->num_rows > 0) {
            $order = $orderResult->fetch_assoc();
            $userId = $order['user_id'];
            $currentStatus = $order['order_status'];
            
            // Don't allow status change if order is cancelled
            if ($currentStatus === 'cancelled') {
                $errorMessage = 'Cannot update status of a cancelled order';
            } else {
                // Update tracking ID if provided and status is shipped
                $trackingUpdate = '';
                if ($newStatus === 'shipped' && !empty($trackingId)) {
                    $trackingUpdate = ", tracking_id = '$trackingId'";
                }
                
                $updateQuery = "UPDATE orders SET order_status = '$newStatus'$trackingUpdate WHERE order_id = '$orderId'";
                
                if ($db->query($updateQuery)) {
                    // Create notification for user
                    $notificationMessage = "Your order #$orderId has been updated to: " . ucfirst($newStatus);
                    createNotification($userId, $notificationMessage);
                    
                    $successMessage = "Order status has been updated successfully";
                    
                    // Refresh order list
                    $result = $db->query($query);
                    $orders = [];
                    while ($row = $result->fetch_assoc()) {
                        $orders[] = $row;
                    }
                } else {
                    $errorMessage = 'Failed to update order status';
                }
            }
        } else {
            $errorMessage = 'Order not found';
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3>Admin Dashboard</h3>
            <ul>
                <li><a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/users.php">Manage Users</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/sneakers.php">Approve Listings</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/orders.php" class="active">Manage Orders</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/payments.php">Seller Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Manage Orders</h1>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="filter-section">
                <form action="" method="GET" class="filter-form">
                    <div class="search-input">
                        <input type="text" name="search" placeholder="Search by order ID, username or tracking ID" value="<?php echo $search; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="order_status">Order Status:</label>
                        <select name="order_status" id="order_status">
                            <option value="all" <?php echo $orderStatus === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="placed" <?php echo $orderStatus === 'placed' ? 'selected' : ''; ?>>Placed</option>
                            <option value="processing" <?php echo $orderStatus === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $orderStatus === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $orderStatus === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $orderStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="payment_status">Payment Status:</label>
                        <select name="payment_status" id="payment_status">
                            <option value="all" <?php echo $paymentStatus === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $paymentStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $paymentStatus === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $paymentStatus === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Filter</button>
                    <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-secondary">Reset</a>
                </form>
            </div>
            
            <div class="orders-list">
                <?php if (count($orders) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Payment Status</th>
                                    <th>Order Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo $order['username']; ?></td>
                                        <td><?php echo formatPrice($order['total_amount']); ?></td>
                                        <td><?php echo $order['payment_method'] === 'upi' ? 'UPI Payment' : 'Cash on Delivery'; ?></td>
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
                                            <button type="button" class="btn-sm update-status-btn" data-id="<?php echo $order['order_id']; ?>" data-status="<?php echo $order['order_status']; ?>">Update Status</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&order_status=<?php echo $orderStatus; ?>&payment_status=<?php echo $paymentStatus; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&order_status=<?php echo $orderStatus; ?>&payment_status=<?php echo $paymentStatus; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&order_status=<?php echo $orderStatus; ?>&payment_status=<?php echo $paymentStatus; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-data">
                        <p>No orders found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="status-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Update Order Status</h2>
        <form method="POST" action="">
            <input type="hidden" id="order_id" name="order_id" value="">
            
            <div class="form-group">
                <label for="order_status">Order Status:</label>
                <select id="order_status_select" name="order_status" required>
                    <option value="placed">Placed</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="form-group tracking-field" style="display: none;">
                <label for="tracking_id">Tracking ID:</label>
                <input type="text" id="tracking_id" name="tracking_id" placeholder="Enter tracking ID">
                <small>Required for shipped status</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
                <button type="button" class="btn btn-secondary cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.filter-section {
    background-color: var(--bg-light);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.search-input {
    flex: 1;
    min-width: 250px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 150px;
}

.filter-group label {
    margin-bottom: 5px;
    font-size: 14px;
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

.status-badge.completed {
    background-color: rgba(46, 204, 113, 0.2);
    color: var(--success-color);
}

.status-badge.failed {
    background-color: rgba(231, 76, 60, 0.2);
    color: var(--error-color);
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

.status-badge.cancelled {
    background-color: rgba(231, 76, 60, 0.2);
    color: var(--error-color);
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    margin-right: 5px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: var(--bg-secondary);
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 500px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.close {
    color: var(--text-secondary);
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: var(--text-color);
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-input, .filter-group {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update status modal functionality
    const modal = document.getElementById('status-modal');
    const updateBtns = document.querySelectorAll('.update-status-btn');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.querySelector('.cancel-btn');
    const orderIdInput = document.getElementById('order_id');
    const statusSelect = document.getElementById('order_status_select');
    const trackingField = document.querySelector('.tracking-field');
    
    updateBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            
            orderIdInput.value = orderId;
            statusSelect.value = currentStatus;
            
            // Show tracking field if status is shipped
            if (statusSelect.value === 'shipped') {
                trackingField.style.display = 'block';
            } else {
                trackingField.style.display = 'none';
            }
            
            modal.style.display = 'block';
        });
    });
    
    // Show/hide tracking field based on status selection
    statusSelect.addEventListener('change', function() {
        if (this.value === 'shipped') {
            trackingField.style.display = 'block';
        } else {
            trackingField.style.display = 'none';
        }
    });
    
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>