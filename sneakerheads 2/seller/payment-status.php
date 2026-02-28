<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require seller access
Auth::requireSeller();

$pageTitle = 'Payment Status';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if request ID is provided
if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
    header('Location: ' . SITE_URL . '/seller/payments.php');
    exit;
}

$requestId = (int)$_GET['request_id'];

// Get payment request details
$requestQuery = "SELECT pr.*, s.brand, s.model, s.size, o.order_id, o.created_at as order_date
                FROM payment_requests pr
                JOIN sneakers s ON pr.sneaker_id = s.sneaker_id
                JOIN orders o ON pr.order_id = o.order_id
                WHERE pr.request_id = '$requestId' AND pr.seller_id = '$userId'";
$requestResult = $db->query($requestQuery);

if ($requestResult->num_rows === 0) {
    header('Location: ' . SITE_URL . '/seller/payments.php');
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
                <h1>Payment Request Status</h1>
                <a href="<?php echo SITE_URL; ?>/seller/payments.php" class="btn btn-secondary">Back to Payments</a>
            </div>
            
            <div class="payment-status-container">
                <div class="status-card">
                    <div class="status-header">
                        <h2>Payment Request #<?php echo $requestId; ?></h2>
                        <div class="status-badge <?php echo $request['status']; ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </div>
                    </div>
                    
                    <div class="status-timeline">
                        <div class="timeline-item <?php echo $request['status'] != 'rejected' ? 'active' : ''; ?>">
                            <div class="timeline-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="timeline-content">
                                <h3>Request Submitted</h3>
                                <p><?php echo date('F j, Y, g:i a', strtotime($request['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="timeline-item <?php echo in_array($request['status'], ['approved', 'paid']) ? 'active' : ''; ?>">
                            <div class="timeline-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h3>Request Approved</h3>
                                <p><?php echo $request['status'] == 'approved' || $request['status'] == 'paid' ? 'Your payment request has been approved and is being processed' : 'Waiting for admin approval'; ?></p>
                            </div>
                        </div>
                        
                        <div class="timeline-item <?php echo $request['status'] == 'paid' ? 'active' : ''; ?>">
                            <div class="timeline-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="timeline-content">
                                <h3>Payment Completed</h3>
                                <p><?php echo $request['status'] == 'paid' ? 'Payment has been processed and sent to your account' : 'Waiting for payment processing'; ?></p>
                            </div>
                        </div>
                        
                        <?php if ($request['status'] == 'rejected'): ?>
                            <div class="timeline-item active rejected">
                                <div class="timeline-icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h3>Request Rejected</h3>
                                    <p>Your payment request has been rejected. Please see admin notes for details.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] == 'on_hold'): ?>
                            <div class="timeline-item active on-hold">
                                <div class="timeline-icon">
                                    <i class="fas fa-pause-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h3>Request On Hold</h3>
                                    <p>Your payment request has been put on hold. Please see admin notes for details.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($request['admin_notes'])): ?>
                        <div class="admin-notes">
                            <h3>Admin Notes</h3>
                            <div class="notes-content">
                                <?php echo nl2br(htmlspecialchars($request['admin_notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="payment-details-card">
                    <h2>Payment Details</h2>
                    <div class="details-section">
                        <h3>Sneaker Information</h3>
                        <div class="info-row">
                            <span class="label">Sneaker:</span>
                            <span class="value"><?php echo $request['brand'] . ' ' . $request['model']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Size:</span>
                            <span class="value"><?php echo $request['size']; ?> UK</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Order ID:</span>
                            <span class="value">#<?php echo $request['order_id']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Order Date:</span>
                            <span class="value"><?php echo date('F j, Y', strtotime($request['order_date'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="details-section">
                        <h3>Payment Information</h3>
                        <div class="info-row">
                            <span class="label">Amount:</span>
                            <span class="value"><?php echo formatPrice($request['amount']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Platform Fee (<?php echo PLATFORM_FEE_PERCENTAGE; ?>%):</span>
                            <span class="value"><?php echo formatPrice($request['platform_fee']); ?></span>
                        </div>
                        <div class="info-row total">
                            <span class="label">Net Amount:</span>
                            <span class="value"><?php echo formatPrice($request['net_amount']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Payment Method:</span>
                            <span class="value"><?php echo $request['payment_method'] == 'bank_transfer' ? 'Bank Transfer' : 'UPI Payment'; ?></span>
                        </div>
                        <?php if ($request['payment_method'] == 'bank_transfer'): ?>
                            <div class="info-row">
                                <span class="label">Bank Account:</span>
                                <span class="value"><?php echo $request['bank_account']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">IFSC Code:</span>
                                <span class="value"><?php echo $request['ifsc_code']; ?></span>
                            </div>
                        <?php else: ?>
                            <div class="info-row">
                                <span class="label">UPI ID:</span>
                                <span class="value"><?php echo $request['upi_id']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($transactions) > 0): ?>
                        <div class="details-section">
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
            </div>
        </div>
    </div>
</div>

<style>
.payment-status-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.status-card,
.payment-details-card {
    background-color: var(--bg-light);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border-color);
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

.status-timeline {
    display: flex;
    flex-direction: column;
    gap: 30px;
    margin-bottom: 30px;
}

.timeline-item {
    display: flex;
    gap: 15px;
    opacity: 0.5;
}

.timeline-item.active {
    opacity: 1;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    background-color: var(--bg-secondary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.timeline-item.active .timeline-icon {
    background-color: var(--primary-color);
    color: white;
}

.timeline-item.rejected .timeline-icon {
    background-color: var(--error-color);
}

.timeline-item.on-hold .timeline-icon {
    background-color: #9b59b6;
}

.timeline-content h3 {
    margin-bottom: 5px;
    font-size: 16px;
}

.timeline-content p {
    color: var(--text-secondary);
    font-size: 14px;
}

.admin-notes {
    background-color: var(--bg-secondary);
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
}

.admin-notes h3 {
    margin-bottom: 10px;
    font-size: 16px;
}

.notes-content {
    color: var(--text-secondary);
    font-style: italic;
}

.details-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.details-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.details-section h3 {
    margin-bottom: 15px;
    font-size: 18px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.info-row.total {
    margin-top: 15px;
    font-weight: bold;
    font-size: 18px;
    color: var(--accent-color);
}

.label {
    color: var(--text-secondary);
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

@media (max-width: 992px) {
    .payment-status-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .info-row {
        flex-direction: column;
        gap: 5px;
    }
    
    .info-row .label {
        margin-bottom: 5px;
    }
    
    .status-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
