<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require superadmin access
Auth::requireSuperAdmin();

$pageTitle = 'Payment Request Details';
$currentUser = Auth::getCurrentUser();

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if request ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/admin/payment-requests.php');
    exit;
}

$requestId = (int)$_GET['id'];

// Get payment request details
$requestQuery = "SELECT pr.*, u.username as seller_username, u.email as seller_email, u.phone as seller_phone,
                s.brand, s.model, s.size, s.serial_number, o.order_id, o.created_at as order_date
                FROM payment_requests pr
                JOIN users u ON pr.seller_id = u.user_id
                JOIN sneakers s ON pr.sneaker_id = s.sneaker_id
                JOIN orders o ON pr.order_id = o.order_id
                WHERE pr.request_id = '$requestId'";
$requestResult = $db->query($requestQuery);

if ($requestResult->num_rows === 0) {
    header('Location: ' . SITE_URL . '/admin/payment-requests.php');
    exit;
}

$request = $requestResult->fetch_assoc();

// Get payment transactions if any
$transactionsQuery = "SELECT * FROM payment_transactions WHERE request_id = '$requestId' ORDER BY transaction_date DESC";
$transactionsResult = $db->query($transactionsQuery);
$transactions = [];

if ($transactionsResult && $transactionsResult->num_rows > 0) {
    while ($row = $transactionsResult->fetch_assoc()) {
        $transactions[] = $row;
    }
}

// Handle request actions
$successMessage = '';
$errorMessage = '';

// Approve request
if (isset($_POST['approve'])) {
    // Check if request is pending
    if ($request['status'] !== 'pending' && $request['status'] !== 'on_hold') {
        $errorMessage = 'Only pending or on-hold requests can be approved';
    } else {
        $updateQuery = "UPDATE payment_requests SET status = 'approved' WHERE request_id = '$requestId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your payment request #$requestId has been approved and is being processed.";
            createNotification($request['seller_id'], $notificationMessage);
            
            $successMessage = "Payment request has been approved successfully";
            
            // Refresh request data
            $requestResult = $db->query($requestQuery);
            $request = $requestResult->fetch_assoc();
        } else {
            $errorMessage = 'Failed to approve payment request';
        }
    }
}

// Mark as paid
if (isset($_POST['mark_paid']) && isset($_POST['transaction_reference'])) {
    // Check if request is approved
    if ($request['status'] !== 'approved') {
        $errorMessage = 'Only approved requests can be marked as paid';
    } else {
        $transactionReference = sanitizeInput($_POST['transaction_reference']);
        $notes = sanitizeInput($_POST['notes']);
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
            createNotification($request['seller_id'], $notificationMessage);
            
            // Commit transaction
            $db->commit();
            
            $successMessage = "Payment has been marked as paid successfully";
            
            // Refresh request data
            $requestResult = $db->query($requestQuery);
            $request = $requestResult->fetch_assoc();
            
            // Refresh transactions
            $transactionsResult = $db->query($transactionsQuery);
            $transactions = [];
            if ($transactionsResult && $transactionsResult->num_rows > 0) {
                while ($row = $transactionsResult->fetch_assoc()) {
                    $transactions[] = $row;
                }
            }
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            $errorMessage = $e->getMessage();
        }
    }
}

// Reject request
if (isset($_POST['reject']) && isset($_POST['rejection_reason'])) {
    // Check if request is not paid
    if ($request['status'] === 'paid') {
        $errorMessage = 'Paid requests cannot be rejected';
    } else {
        $rejectionReason = sanitizeInput($_POST['rejection_reason']);
        
        $updateQuery = "UPDATE payment_requests SET status = 'rejected', admin_notes = '$rejectionReason' WHERE request_id = '$requestId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your payment request #$requestId has been rejected. Reason: $rejectionReason";
            createNotification($request['seller_id'], $notificationMessage);
            
            $successMessage = "Payment request has been rejected successfully";
            
            // Refresh request data
            $requestResult = $db->query($requestQuery);
            $request = $requestResult->fetch_assoc();
        } else {
            $errorMessage = 'Failed to reject payment request';
        }
    }
}

// Put request on hold
if (isset($_POST['hold']) && isset($_POST['hold_reason'])) {
    // Check if request is not paid
    if ($request['status'] === 'paid') {
        $errorMessage = 'Paid requests cannot be put on hold';
    } else {
        $holdReason = sanitizeInput($_POST['hold_reason']);
        
        $updateQuery = "UPDATE payment_requests SET status = 'on_hold', admin_notes = '$holdReason' WHERE request_id = '$requestId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your payment request #$requestId has been put on hold. Reason: $holdReason";
            createNotification($request['seller_id'], $notificationMessage);
            
            $successMessage = "Payment request has been put on hold successfully";
            
            // Refresh request data
            $requestResult = $db->query($requestQuery);
            $request = $requestResult->fetch_assoc();
        } else {
            $errorMessage = 'Failed to put payment request on hold';
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
                <li><a href="<?php echo SITE_URL; ?>/admin/payment-requests.php" class="active">Payment Requests</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Payment Request Details</h1>
                <a href="<?php echo SITE_URL; ?>/admin/payment-requests.php" class="btn btn-secondary">Back to Requests</a>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="payment-details-container">
                <div class="payment-header">
                    <div class="payment-title">
                        <h2>Payment Request #<?php echo $requestId; ?></h2>
                        <p>Submitted on <?php echo date('F j, Y, g:i a', strtotime($request['created_at'])); ?></p>
                    </div>
                    <div class="payment-status">
                        <span class="status-badge <?php echo $request['status']; ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="payment-content">
                    <div class="payment-section">
                        <h3>Seller Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Seller:</span>
                                <span class="value"><?php echo $request['seller_username']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Email:</span>
                                <span class="value"><?php echo $request['seller_email']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Phone:</span>
                                <span class="value"><?php echo $request['seller_phone'] ? $request['seller_phone'] : 'Not provided'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Seller ID:</span>
                                <span class="value"><?php echo $request['seller_id']; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-section">
                        <h3>Sneaker Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Sneaker:</span>
                                <span class="value"><?php echo $request['brand'] . ' ' . $request['model']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Size:</span>
                                <span class="value"><?php echo $request['size']; ?> UK</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Serial Number:</span>
                                <span class="value"><?php echo $request['serial_number']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Order ID:</span>
                                <span class="value">#<?php echo $request['order_id']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Order Date:</span>
                                <span class="value"><?php echo date('F j, Y', strtotime($request['order_date'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-section">
                        <h3>Payment Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Amount:</span>
                                <span class="value"><?php echo formatPrice($request['amount']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Platform Fee (<?php echo PLATFORM_FEE_PERCENTAGE; ?>%):</span>
                                <span class="value"><?php echo formatPrice($request['platform_fee']); ?></span>
                            </div>
                            <div class="info-item total">
                                <span class="label">Net Amount:</span>
                                <span class="value"><?php echo formatPrice($request['net_amount']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Payment Method:</span>
                                <span class="value"><?php echo $request['payment_method'] == 'bank_transfer' ? 'Bank Transfer' : 'UPI Payment'; ?></span>
                            </div>
                            <?php if ($request['payment_method'] == 'bank_transfer'): ?>
                                <div class="info-item">
                                    <span class="label">Bank Account:</span>
                                    <span class="value"><?php echo $request['bank_account']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">IFSC Code:</span>
                                    <span class="value"><?php echo $request['ifsc_code']; ?></span>
                                </div>
                            <?php else: ?>
                                <div class="info-item">
                                    <span class="label">UPI ID:</span>
                                    <span class="value"><?php echo $request['upi_id']; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($request['admin_notes'])): ?>
                        <div class="payment-section">
                            <h3>Admin Notes</h3>
                            <div class="admin-notes">
                                <?php echo nl2br(htmlspecialchars($request['admin_notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (count($transactions) > 0): ?>
                        <div class="payment-section">
                            <h3>Transaction History</h3>
                            <div class="transactions-list">
                                <?php foreach ($transactions as $transaction): ?>
                                    <div class="transaction-item">
                                        <div class="transaction-date">
                                            <?php echo date('F j, Y, g:i a', strtotime($transaction['transaction_date'])); ?>
                                        </div>
                                        <div class="transaction-details">
                                            <div class="transaction-amount <?php echo $transaction['transaction_type']; ?>">
                                                <?php echo $transaction['transaction_type'] == 'credit' ? '+' : '-'; ?> 
                                                <?php echo formatPrice($transaction['amount']); ?>
                                            </div>
                                            <div class="transaction-status <?php echo $transaction['status']; ?>">
                                                <?php echo ucfirst($transaction['status']); ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($transaction['transaction_reference'])): ?>
                                            <div class="transaction-reference">
                                                Ref: <?php echo $transaction['transaction_reference']; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($transaction['notes'])): ?>
                                            <div class="transaction-notes">
                                                <?php echo nl2br(htmlspecialchars($transaction['notes'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="payment-actions">
                    <?php if ($request['status'] === 'pending' || $request['status'] === 'on_hold'): ?>
                        <form method="POST" action="" class="inline-form">
                            <button type="submit" name="approve" class="btn btn-success">Approve Request</button>
                        </form>
                        
                        <button type="button" class="btn btn-warning hold-btn">Put On Hold</button>
                        
                        <button type="button" class="btn btn-danger reject-btn">Reject Request</button>
                    <?php endif; ?>
                    
                    <?php if ($request['status'] === 'approved'): ?>
                        <button type="button" class="btn btn-success pay-btn">Mark as Paid</button>
                        
                        <button type="button" class="btn btn-warning hold-btn">Put On Hold</button>
                        
                        <button type="button" class="btn btn-danger reject-btn">Reject Request</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div id="pay-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Mark Payment as Paid</h2>
        <form method="POST" action="">
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
            <div class="form-group">
                <label for="hold_reason">Reason for Holding:</label>
                <textarea id="hold_reason" name="hold_reason" rows="4" required></textarea>
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
.payment-details-container {
    background-color: var(--bg-light);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background-color: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}

.payment-title h2 {
    margin-bottom: 5px;
}

.payment-title p {
    color: var(--text-secondary);
    font-size: 14px;
}

.status-badge {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
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

.payment-content {
    padding: 20px;
}

.payment-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.payment-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.payment-section h3 {
    margin-bottom: 15px;
    font-size: 18px;
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

.info-item.total {
    font-weight: bold;
    font-size: 18px;
    color: var(--accent-color);
}

.label {
    color: var(--text-secondary);
    font-size: 14px;
}

.admin-notes {
    background-color: var(--bg-secondary);
    padding: 15px;
    border-radius: 8px;
    color: var(--text-secondary);
    font-style: italic;
}

.transactions-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.transaction-item {
    background-color: var(--bg-secondary);
    padding: 15px;
    border-radius: 8px;
}

.transaction-date {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 10px;
}

.transaction-details {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.transaction-amount {
    font-weight: bold;
}

.transaction-amount.credit {
    color: var(--success-color);
}

.transaction-amount.debit {
    color: var(--error-color);
}

.transaction-status {
    font-size: 14px;
    padding: 2px 8px;
    border-radius: 4px;
}

.transaction-status.success {
    background-color: rgba(46, 204, 113, 0.2);
    color: var(--success-color);
}

.transaction-status.failed {
    background-color: rgba(231, 76, 60, 0.2);
    color: var(--error-color);
}

.transaction-status.pending {
    background-color: rgba(243, 156, 18, 0.2);
    color: var(--warning-color);
}

.transaction-reference {
    font-size: 14px;
    margin-bottom: 5px;
}

.transaction-notes {
    font-size: 14px;
    color: var(--text-secondary);
    font-style: italic;
}

.payment-actions {
    padding: 20px;
    background-color: var(--bg-secondary);
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 10px;
}

.inline-form {
    display: inline;
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

@media (max-width: 992px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .payment-actions {
        flex-wrap: wrap;
    }
}

@media (max-width: 768px) {
    .payment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pay modal functionality
    const payModal = document.getElementById('pay-modal');
    const payBtn = document.querySelector('.pay-btn');
    
    if (payBtn) {
        payBtn.addEventListener('click', function() {
            payModal.style.display = 'block';
        });
    }
    
    // Reject modal functionality
    const rejectModal = document.getElementById('reject-modal');
    const rejectBtn = document.querySelector('.reject-btn');
    
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            rejectModal.style.display = 'block';
        });
    }
    
    // Hold modal functionality
    const holdModal = document.getElementById('hold-modal');
    const holdBtn = document.querySelector('.hold-btn');
    
    if (holdBtn) {
        holdBtn.addEventListener('click', function() {
            holdModal.style.display = 'block';
        });
    }
    
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
