<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require superadmin access
Auth::requireSuperAdmin();

$pageTitle = 'Payment Requests';
$currentUser = Auth::getCurrentUser();

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle filter
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'all';
$validStatuses = ['all', 'pending', 'approved', 'paid', 'rejected', 'on_hold'];
if (!in_array($status, $validStatuses)) {
    $status = 'all';
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Build query
$query = "SELECT pr.*, u.username as seller_username, s.brand, s.model, s.size
          FROM payment_requests pr
          JOIN users u ON pr.seller_id = u.user_id
          JOIN sneakers s ON pr.sneaker_id = s.sneaker_id
          WHERE 1=1";

if ($status !== 'all') {
    $query .= " AND pr.status = '$status'";
}

// Count total items for pagination
$countQuery = str_replace("pr.*, u.username as seller_username, s.brand, s.model, s.size", "COUNT(*) as total", $query);
$countResult = $db->query($countQuery);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Add pagination to query
$query .= " ORDER BY pr.created_at DESC LIMIT $offset, $itemsPerPage";
$result = $db->query($query);

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

// Handle request actions
$successMessage = '';
$errorMessage = '';

// Approve request
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $requestId = (int)$_GET['approve'];
    
    // Check if request exists and is pending
    $checkQuery = "SELECT * FROM payment_requests WHERE request_id = '$requestId' AND status = 'pending'";
    $checkResult = $db->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $request = $checkResult->fetch_assoc();
        $sellerId = $request['seller_id'];
        
        $updateQuery = "UPDATE payment_requests SET status = 'approved' WHERE request_id = '$requestId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your payment request #$requestId has been approved and is being processed.";
            createNotification($sellerId, $notificationMessage);
            
            $successMessage = "Payment request has been approved successfully";
            
            // Refresh requests list
            $result = $db->query($query);
            $requests = [];
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
        } else {
            $errorMessage = 'Failed to approve payment request';
        }
    } else {
        $errorMessage = 'Payment request not found or not pending';
    }
}

// Mark as paid
if (isset($_POST['mark_paid']) && isset($_POST['request_id']) && isset($_POST['transaction_reference'])) {
    $requestId = (int)$_POST['request_id'];
    $transactionReference = sanitizeInput($_POST['transaction_reference']);
    $notes = sanitizeInput($_POST['notes']);
    
    // Check if request exists and is approved
    $checkQuery = "SELECT * FROM payment_requests WHERE request_id = '$requestId' AND status = 'approved'";
    $checkResult = $db->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $request = $checkResult->fetch_assoc();
        $sellerId = $request['seller_id'];
        $amount = $request['net_amount'];
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Update request status
            $updateQuery = "UPDATE payment_requests SET status = 'paid' WHERE request_id = '$requestId'";
            if (!$db->query($updateQuery)) {
                throw new Exception('Failed to update payment request status');
            }
            
            // Create transaction record
            $insertTransactionQuery = "INSERT INTO payment_transactions 
                                     (request_id, amount, transaction_type, transaction_reference, notes)
                                     VALUES 
                                     ('$requestId', '$amount', 'credit', '$transactionReference', '$notes')";
            if (!$db->query($insertTransactionQuery)) {
                throw new Exception('Failed to create transaction record');
            }
            
            // Create notification for seller
            $notificationMessage = "Your payment request #$requestId has been processed. Amount: " . formatPrice($amount);
            createNotification($sellerId, $notificationMessage);
            
            // Commit transaction
            $db->commit();
            
            $successMessage = "Payment has been marked as paid successfully";
            
            // Refresh requests list
            $result = $db->query($query);
            $requests = [];
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            $errorMessage = $e->getMessage();
        }
    } else {
        $errorMessage = 'Payment request not found or not approved';
    }
}

// Reject request
if (isset($_POST['reject']) && isset($_POST['request_id']) && isset($_POST['rejection_reason'])) {
    $requestId = (int)$_POST['request_id'];
    $rejectionReason = sanitizeInput($_POST['rejection_reason']);
    
    // Check if request exists and is not paid
    $checkQuery = "SELECT * FROM payment_requests WHERE request_id = '$requestId' AND status != 'paid'";
    $checkResult = $db->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $request = $checkResult->fetch_assoc();
        $sellerId = $request['seller_id'];
        
        $updateQuery = "UPDATE payment_requests SET status = 'rejected', admin_notes = '$rejectionReason' WHERE request_id = '$requestId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your payment request #$requestId has been rejected. Reason: $rejectionReason";
            createNotification($sellerId, $notificationMessage);
            
            $successMessage = "Payment request has been rejected successfully";
            
            // Refresh requests list
            $result = $db->query($query);
            $requests = [];
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
        } else {
            $errorMessage = 'Failed to reject payment request';
        }
    } else {
        $errorMessage = 'Payment request not found or already paid';
    }
}

// Put request on hold
if (isset($_POST['hold']) && isset($_POST['request_id']) && isset($_POST['hold_reason'])) {
    $requestId = (int)$_POST['request_id'];
    $holdReason = sanitizeInput($_POST['hold_reason']);
    
    // Check if request exists and is not paid
    $checkQuery = "SELECT * FROM payment_requests WHERE request_id = '$requestId' AND status != 'paid'";
    $checkResult = $db->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $request = $checkResult->fetch_assoc();
        $sellerId = $request['seller_id'];
        
        $updateQuery = "UPDATE payment_requests SET status = 'on_hold', admin_notes = '$holdReason' WHERE request_id = '$requestId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your payment request #$requestId has been put on hold. Reason: $holdReason";
            createNotification($sellerId, $notificationMessage);
            
            $successMessage = "Payment request has been put on hold successfully";
            
            // Refresh requests list
            $result = $db->query($query);
            $requests = [];
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
        } else {
            $errorMessage = 'Failed to put payment request on hold';
        }
    } else {
        $errorMessage = 'Payment request not found or already paid';
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
                <li><a href="<?php echo SITE_URL; ?>/admin/payment-requests.php" class="active">Payment Requests</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Seller Payment Requests</h1>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="filter-tabs">
                <a href="?status=all" class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">All</a>
                <a href="?status=pending" class="filter-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="?status=approved" class="filter-tab <?php echo $status === 'approved' ? 'active' : ''; ?>">Approved</a>
                <a href="?status=paid" class="filter-tab <?php echo $status === 'paid' ? 'active' : ''; ?>">Paid</a>
                <a href="?status=rejected" class="filter-tab <?php echo $status === 'rejected' ? 'active' : ''; ?>">Rejected</a>
                <a href="?status=on_hold" class="filter-tab <?php echo $status === 'on_hold' ? 'active' : ''; ?>">On Hold</a>
            </div>
            
            <?php if (count($requests) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Seller</th>
                                <th>Sneaker</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>#<?php echo $request['request_id']; ?></td>
                                    <td><?php echo $request['seller_username']; ?></td>
                                    <td><?php echo $request['brand'] . ' ' . $request['model'] . ' (Size ' . $request['size'] . ')'; ?></td>
                                    <td><?php echo formatPrice($request['net_amount']); ?></td>
                                    <td><?php echo $request['payment_method'] == 'bank_transfer' ? 'Bank Transfer' : 'UPI'; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $request['status']; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo SITE_URL; ?>/admin/payment-details.php?id=<?php echo $request['request_id']; ?>" class="btn-sm">View</a>
                                            
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <a href="?approve=<?php echo $request['request_id']; ?>&status=<?php echo $status; ?>&page=<?php echo $page; ?>" class="btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this payment request?')">Approve</a>
                                                
                                                <button type="button" class="btn-sm btn-warning hold-btn" data-id="<?php echo $request['request_id']; ?>">Hold</button>
                                                
                                                <button type="button" class="btn-sm btn-danger reject-btn" data-id="<?php echo $request['request_id']; ?>">Reject</button>
                                            <?php endif; ?>
                                            
                                            <?php if ($request['status'] === 'approved'): ?>
                                                <button type="button" class="btn-sm btn-success pay-btn" data-id="<?php echo $request['request_id']; ?>" data-amount="<?php echo $request['net_amount']; ?>">Mark Paid</button>
                                                
                                                <button type="button" class="btn-sm btn-warning hold-btn" data-id="<?php echo $request['request_id']; ?>">Hold</button>
                                                
                                                <button type="button" class="btn-sm btn-danger reject-btn" data-id="<?php echo $request['request_id']; ?>">Reject</button>
                                            <?php endif; ?>
                                            
                                            <?php if ($request['status'] === 'on_hold'): ?>
                                                <a href="?approve=<?php echo $request['request_id']; ?>&status=<?php echo $status; ?>&page=<?php echo $page; ?>" class="btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this payment request?')">Approve</a>
                                                
                                                <button type="button" class="btn-sm btn-danger reject-btn" data-id="<?php echo $request['request_id']; ?>">Reject</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?status=<?php echo $status; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?status=<?php echo $status; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?status=<?php echo $status; ?>&page=<?php echo $page + 1; ?>">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-data">
                    <p>No payment requests found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div id="pay-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Mark Payment as Paid</h2>
        <form method="POST" action="">
            <input type="hidden" id="pay_request_id" name="request_id" value="">
            
            <div class="form-group">
                <label for="transaction_reference">Transaction Reference/ID:</label>
                <input type="text" id="transaction_reference" name="transaction_reference" required>
                <small>Enter bank transaction ID, UPI reference, or any other payment identifier</small>
            </div>
            
            <div class="form-group">
                <label for="notes">Payment Notes (Optional):</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
                <small>Add any additional information about this payment</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="mark_paid" class="btn btn-success">Confirm Payment</button>
                <button type="button" class="btn btn-secondary cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Reject Payment Request</h2>
        <form method="POST" action="">
            <input type="hidden" id="reject_request_id" name="request_id" value="">
            
            <div class="form-group">
                <label for="rejection_reason">Reason for Rejection:</label>
                <textarea id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                <small>This reason will be sent to the seller</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="reject" class="btn btn-danger">Reject Request</button>
                <button type="button" class="btn btn-secondary cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Hold Modal -->
<div id="hold-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Put Payment Request On Hold</h2>
        <form method="POST" action="">
            <input type="hidden" id="hold_request_id" name="request_id" value="">
            
            <div class="form-group">
                <label for="hold_reason">Reason for Holding:</label>
                <textarea id="hold_reason" name="hold_reason" rows="4" required></textarea>
                <small>This reason will be sent to the seller</small>  rows="4" required></textarea>
                <small>This reason will be sent to the seller</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="hold" class="btn btn-warning">Put On Hold</button>
                <button type="button" class="btn btn-secondary cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 8px 15px;
    background-color: var(--bg-light);
    border-radius: 20px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.filter-tab:hover {
    background-color: var(--bg-secondary);
}

.filter-tab.active {
    background-color: var(--primary-color);
    color: white;
}

.status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.status-badge.pending {
    background-color: var(--warning-color);
    color: #000;
}

.status-badge.approved {
    background-color: var(--info-color);
    color: #fff;
}

.status-badge.paid {
    background-color: var(--success-color);
    color: #fff;
}

.status-badge.rejected {
    background-color: var(--error-color);
    color: #fff;
}

.status-badge.on_hold {
    background-color: #9b59b6;
    color: #fff;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 4px;
}

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
    background-color: var(--bg-light);
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

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-sm {
        margin-bottom: 5px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pay modal functionality
    const payModal = document.getElementById('pay-modal');
    const payBtns = document.querySelectorAll('.pay-btn');
    const payRequestId = document.getElementById('pay_request_id');
    
    payBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            payRequestId.value = requestId;
            payModal.style.display = 'block';
        });
    });
    
    // Reject modal functionality
    const rejectModal = document.getElementById('reject-modal');
    const rejectBtns = document.querySelectorAll('.reject-btn');
    const rejectRequestId = document.getElementById('reject_request_id');
    
    rejectBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            rejectRequestId.value = requestId;
            rejectModal.style.display = 'block';
        });
    });
    
    // Hold modal functionality
    const holdModal = document.getElementById('hold-modal');
    const holdBtns = document.querySelectorAll('.hold-btn');
    const holdRequestId = document.getElementById('hold_request_id');
    
    holdBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            holdRequestId.value = requestId;
            holdModal.style.display = 'block';
        });
    });
    
    // Close modals
    const closeBtns = document.querySelectorAll('.close');
    const cancelBtns = document.querySelectorAll('.cancel-btn');
    
    closeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            payModal.style.display = 'none';
            rejectModal.style.display = 'none';
            holdModal.style.display = 'none';
        });
    });
    
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            payModal.style.display = 'none';
            rejectModal.style.display = 'none';
            holdModal.style.display = 'none';
        });
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === payModal) {
            payModal.style.display = 'none';
        }
        if (event.target === rejectModal) {
            rejectModal.style.display = 'none';
        }
        if (event.target === holdModal) {
            holdModal.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
