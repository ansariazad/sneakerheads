<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require superadmin access
Auth::requireSuperAdmin();

$pageTitle = 'Seller Payments';
$currentUser = Auth::getCurrentUser();

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle search and filters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status = isset($_GET['status']) && in_array($_GET['status'], ['all', 'requested', 'processing', 'completed', 'rejected']) 
           ? $_GET['status'] : 'all';

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Build query
$query = "SELECT p.*, u.username as seller_username, s.brand, s.model, o.order_id
       FROM payments p
       JOIN users u ON p.seller_id = u.user_id
       JOIN order_items oi ON p.order_item_id = oi.item_id
       JOIN orders o ON oi.order_id = o.order_id
       JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
       WHERE 1=1";

if (!empty($search)) {
    $search = $db->escapeString($search);
    $query .= " AND (u.username LIKE '%$search%' OR s.brand LIKE '%$search%' OR s.model LIKE '%$search%')";
}

if ($status !== 'all') {
    $query .= " AND p.status = '$status'";
}

// Count total items for pagination
$countQuery = str_replace("SELECT p.*, u.username as seller_username, s.brand, s.model, o.order_id", "SELECT COUNT(*) as total", $query);
$countResult = $db->query($countQuery);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Add pagination to query
$query .= " ORDER BY p.created_at DESC LIMIT $offset, $itemsPerPage";
$result = $db->query($query);

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

// Handle payment actions
$successMessage = '';
$errorMessage = '';

// Process payment
if (isset($_GET['process']) && is_numeric($_GET['process'])) {
    $paymentId = (int)$_GET['process'];
    
    // Check if payment exists and is in requested status
    $checkQuery = "SELECT p.*, u.username as seller_username, s.brand, s.model 
               FROM payments p
               JOIN users u ON p.seller_id = u.user_id
               JOIN order_items oi ON p.order_item_id = oi.item_id
               JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
               WHERE p.payment_id = '$paymentId' AND p.status = 'requested'";
    $checkResult = $db->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $payment = $checkResult->fetch_assoc();
        $sellerId = $payment['seller_id'];
        $brand = $payment['brand'];
        $model = $payment['model'];
        
        $updateQuery = "UPDATE payments SET status = 'processing' WHERE payment_id = '$paymentId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your payment request for $brand $model is being processed.";
            createNotification($sellerId, $notificationMessage);
            
            $successMessage = "Payment has been marked as processing";
            
            // Refresh payment list
            $result = $db->query($query);
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
        } else {
            $errorMessage = 'Failed to update payment status';
        }
    } else {
        $errorMessage = 'Payment not found or not in requested status';
    }
}

// Complete payment
if (isset($_GET['complete']) && is_numeric($_GET['complete'])) {
    $paymentId = (int)$_GET['complete'];
    
    // Check if payment exists and is in processing status
    $checkQuery = "SELECT p.*, u.username as seller_username, s.brand, s.model 
               FROM payments p
               JOIN users u ON p.seller_id = u.user_id
               JOIN order_items oi ON p.order_item_id = oi.item_id
               JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
               WHERE p.payment_id = '$paymentId' AND p.status = 'processing'";
    $checkResult = $db->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $payment = $checkResult->fetch_assoc();
        $sellerId = $payment['seller_id'];
        $brand = $payment['brand'];
        $model = $payment['model'];
        $amount = $payment['net_amount'];
        
        $updateQuery = "UPDATE payments SET status = 'completed' WHERE payment_id = '$paymentId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your payment of " . formatPrice($amount) . " for $brand $model has been completed.";
            createNotification($sellerId, $notificationMessage);
            
            $successMessage = "Payment has been marked as completed";
            
            // Refresh payment list
            $result = $db->query($query);
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
        } else {
            $errorMessage = 'Failed to update payment status';
        }
    } else {
        $errorMessage = 'Payment not found or not in processing status';
    }
}

// Reject payment
if (isset($_POST['reject']) && isset($_POST['payment_id']) && isset($_POST['rejection_reason'])) {
    $paymentId = (int)$_POST['payment_id'];
    $rejectionReason = sanitizeInput($_POST['rejection_reason']);
    
    if (empty($rejectionReason)) {
        $errorMessage = 'Please provide a reason for rejection';
    } else {
        // Check if payment exists and is not completed
        $checkQuery = "SELECT p.*, u.username as seller_username, s.brand, s.model 
                    FROM payments p
                    JOIN users u ON p.seller_id = u.user_id
                    JOIN order_items oi ON p.order_item_id = oi.item_id
                    JOIN sneakers s ON oi.sneaker_id = s.sneaker_id
                    WHERE p.payment_id = '$paymentId' AND p.status != 'completed'";
        $checkResult = $db->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $payment = $checkResult->fetch_assoc();
            $sellerId = $payment['seller_id'];
            $brand = $payment['brand'];
            $model = $payment['model'];
            
            $updateQuery = "UPDATE payments SET status = 'rejected' WHERE payment_id = '$paymentId'";
            
            if ($db->query($updateQuery)) {
                // Create notification for seller
                $notificationMessage = "Your payment request for $brand $model has been rejected. Reason: $rejectionReason";
                createNotification($sellerId, $notificationMessage);
                
                $successMessage = "Payment has been rejected";
                
                // Refresh payment list
                $result = $db->query($query);
                $payments = [];
                while ($row = $result->fetch_assoc()) {
                    $payments[] = $row;
                }
            } else {
                $errorMessage = 'Failed to update payment status';
            }
        } else {
            $errorMessage = 'Payment not found or already completed';
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
                <li><a href="<?php echo SITE_URL; ?>/admin/orders.php">Manage Orders</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/payments.php" class="active">Seller Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Seller Payments</h1>
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
                        <input type="text" name="search" placeholder="Search by seller, brand or model" value="<?php echo $search; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="requested" <?php echo $status === 'requested' ? 'selected' : ''; ?>>Requested</option>
                            <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Filter</button>
                    <a href="<?php echo SITE_URL; ?>/admin/payments.php" class="btn btn-secondary">Reset</a>
                </form>
            </div>
            
            <div class="payments-list">
                <?php if (count($payments) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Seller</th>
                                    <th>Sneaker</th>
                                    <th>Order ID</th>
                                    <th>Amount</th>
                                    <th>Platform Fee</th>
                                    <th>Net Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo $payment['payment_id']; ?></td>
                                        <td><?php echo $payment['seller_username']; ?></td>
                                        <td><?php echo $payment['brand'] . ' ' . $payment['model']; ?></td>
                                        <td>#<?php echo $payment['order_id']; ?></td>
                                        <td><?php echo formatPrice($payment['amount']); ?></td>
                                        <td><?php echo formatPrice($payment['platform_fee']); ?></td>
                                        <td><?php echo formatPrice($payment['net_amount']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $payment['status']; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                          ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                                        <td>
                                            <?php if ($payment['status'] === 'requested'): ?>
                                                <a href="<?php echo SITE_URL; ?>/admin/payments.php?process=<?php echo $payment['payment_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>" class="btn-sm btn-success" onclick="return confirm('Are you sure you want to process this payment?')">Process</a>
                                                <button type="button" class="btn-sm btn-danger reject-btn" data-id="<?php echo $payment['payment_id']; ?>">Reject</button>
                                            <?php elseif ($payment['status'] === 'processing'): ?>
                                                <a href="<?php echo SITE_URL; ?>/admin/payments.php?complete=<?php echo $payment['payment_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>" class="btn-sm btn-success" onclick="return confirm('Are you sure you want to mark this payment as completed?')">Complete</a>
                                                <button type="button" class="btn-sm btn-danger reject-btn" data-id="<?php echo $payment['payment_id']; ?>">Reject</button>
                                            <?php else: ?>
                                                <a href="<?php echo SITE_URL; ?>/admin/payment-details.php?id=<?php echo $payment['payment_id']; ?>" class="btn-sm">View Details</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-data">
                        <p>No payments found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejection-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Reject Payment</h2>
        <form method="POST" action="">
            <input type="hidden" id="payment_id" name="payment_id" value="">
            
            <div class="form-group">
                <label for="rejection_reason">Reason for Rejection:</label>
                <textarea id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                <small>This reason will be sent to the seller.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="reject" class="btn btn-danger">Reject Payment</button>
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
    // Rejection modal functionality
    const modal = document.getElementById('rejection-modal');
    const rejectBtns = document.querySelectorAll('.reject-btn');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.querySelector('.cancel-btn');
    const paymentIdInput = document.getElementById('payment_id');
    
    rejectBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-id');
            paymentIdInput.value = paymentId;
            modal.style.display = 'block';
        });
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
