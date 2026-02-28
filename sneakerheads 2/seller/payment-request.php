<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require seller access
Auth::requireSeller();

$pageTitle = 'Request Payment';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if sneaker ID and order ID are provided
if (!isset($_GET['sneaker_id']) || !is_numeric($_GET['sneaker_id']) || 
    !isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: ' . SITE_URL . '/seller/sales.php');
    exit;
}

$sneakerId = (int)$_GET['sneaker_id'];
$orderId = (int)$_GET['order_id'];

// Check if sneaker belongs to seller and is sold
$sneakerQuery = "SELECT s.*, o.order_id, o.total_amount, o.order_status, o.created_at as order_date
                FROM sneakers s
                JOIN order_items oi ON s.sneaker_id = oi.sneaker_id
                JOIN orders o ON oi.order_id = o.order_id
                WHERE s.sneaker_id = '$sneakerId' 
                AND s.seller_id = '$userId' 
                AND s.status = 'sold'
                AND o.order_id = '$orderId'";
$sneakerResult = $db->query($sneakerQuery);

if ($sneakerResult->num_rows === 0) {
    header('Location: ' . SITE_URL . '/seller/sales.php');
    exit;
}

$sneaker = $sneakerResult->fetch_assoc();

// Check if payment request already exists
$checkRequestQuery = "SELECT * FROM payment_requests 
                     WHERE seller_id = '$userId' 
                     AND sneaker_id = '$sneakerId' 
                     AND order_id = '$orderId'";
$checkRequestResult = $db->query($checkRequestQuery);

if ($checkRequestResult->num_rows > 0) {
    $paymentRequest = $checkRequestResult->fetch_assoc();
    // Redirect to payment status page
    header('Location: ' . SITE_URL . '/seller/payment-status.php?request_id=' . $paymentRequest['request_id']);
    exit;
}

// Calculate platform fee and net amount
$platformFee = $sneaker['price'] * (PLATFORM_FEE_PERCENTAGE / 100);
$netAmount = $sneaker['price'] - $platformFee;

// Get seller's bank details
$bankDetailsQuery = "SELECT * FROM bank_details WHERE user_id = '$userId' AND is_default = 1";
$bankDetailsResult = $db->query($bankDetailsQuery);
$hasBankDetails = ($bankDetailsResult && $bankDetailsResult->num_rows > 0);
$bankDetails = $hasBankDetails ? $bankDetailsResult->fetch_assoc() : null;

$successMessage = '';
$errorMessage = '';

// Process payment request form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    
    // Validate payment method
    if ($paymentMethod !== 'bank_transfer' && $paymentMethod !== 'upi') {
        $errorMessage = 'Please select a valid payment method';
    } else {
        // Get payment details based on method
        if ($paymentMethod === 'bank_transfer') {
            $accountHolder = sanitizeInput($_POST['account_holder_name']);
            $accountNumber = sanitizeInput($_POST['account_number']);
            $ifscCode = sanitizeInput($_POST['ifsc_code']);
            $bankName = sanitizeInput($_POST['bank_name']);
            
            // Validate bank details
            if (empty($accountHolder) || empty($accountNumber) || empty($ifscCode) || empty($bankName)) {
                $errorMessage = 'Please fill in all bank details';
            } else {
                // Save bank details if "save_details" is checked
                if (isset($_POST['save_details']) && $_POST['save_details'] == 1) {
                    // Check if bank details already exist
                    if ($hasBankDetails) {
                        // Update existing bank details
                        $updateBankQuery = "UPDATE bank_details 
                                          SET account_holder_name = '$accountHolder',
                                              account_number = '$accountNumber',
                                              ifsc_code = '$ifscCode',
                                              bank_name = '$bankName'
                                          WHERE user_id = '$userId' AND is_default = 1";
                        $db->query($updateBankQuery);
                    } else {
                        // Insert new bank details
                        $insertBankQuery = "INSERT INTO bank_details 
                                          (user_id, account_holder_name, account_number, ifsc_code, bank_name, is_default)
                                          VALUES 
                                          ('$userId', '$accountHolder', '$accountNumber', '$ifscCode', '$bankName', 1)";
                        $db->query($insertBankQuery);
                    }
                }
                
                // Create payment request
                $insertRequestQuery = "INSERT INTO payment_requests 
                                     (seller_id, sneaker_id, order_id, amount, platform_fee, net_amount, 
                                      payment_method, bank_account, ifsc_code)
                                     VALUES 
                                     ('$userId', '$sneakerId', '$orderId', '{$sneaker['price']}', 
                                      '$platformFee', '$netAmount', 'bank_transfer', 
                                      '$accountNumber', '$ifscCode')";
                
                if ($db->query($insertRequestQuery)) {
                    $requestId = $db->getLastId();
                    
                    // Create notification for admin
                    $adminQuery = "SELECT user_id FROM users WHERE user_type = 'superadmin' LIMIT 1";
                    $adminResult = $db->query($adminQuery);
                    
                    if ($adminResult->num_rows > 0) {
                        $adminId = $adminResult->fetch_assoc()['user_id'];
                        $notificationMessage = "New payment request from seller for {$sneaker['brand']} {$sneaker['model']}";
                        createNotification($adminId, $notificationMessage);
                    }
                    
                    // Redirect to payment status page
                    header('Location: ' . SITE_URL . '/seller/payment-status.php?request_id=' . $requestId);
                    exit;
                } else {
                    $errorMessage = 'Failed to submit payment request';
                }
            }
        } else if ($paymentMethod === 'upi') {
            $upiId = sanitizeInput($_POST['upi_id']);
            
            // Validate UPI ID
            if (empty($upiId)) {
                $errorMessage = 'Please enter your UPI ID';
            } else {
                // Save UPI details if "save_details" is checked
                if (isset($_POST['save_details']) && $_POST['save_details'] == 1) {
                    // Check if bank details already exist
                    if ($hasBankDetails) {
                        // Update existing bank details with UPI
                        $updateUpiQuery = "UPDATE bank_details 
                                         SET upi_id = '$upiId'
                                         WHERE user_id = '$userId' AND is_default = 1";
                        $db->query($updateUpiQuery);
                    } else {
                        // Insert new bank details with UPI
                        $insertUpiQuery = "INSERT INTO bank_details 
                                         (user_id, account_holder_name, account_number, ifsc_code, bank_name, upi_id, is_default)
                                         VALUES 
                                         ('$userId', '{$currentUser['full_name']}', 'N/A', 'N/A', 'N/A', '$upiId', 1)";
                        $db->query($insertUpiQuery);
                    }
                }
                
                // Create payment request
                $insertRequestQuery = "INSERT INTO payment_requests 
                                     (seller_id, sneaker_id, order_id, amount, platform_fee, net_amount, 
                                      payment_method, upi_id)
                                     VALUES 
                                     ('$userId', '$sneakerId', '$orderId', '{$sneaker['price']}', 
                                      '$platformFee', '$netAmount', 'upi', '$upiId')";
                
                if ($db->query($insertRequestQuery)) {
                    $requestId = $db->getLastId();
                    
                    // Create notification for admin
                    $adminQuery = "SELECT user_id FROM users WHERE user_type = 'superadmin' LIMIT 1";
                    $adminResult = $db->query($adminQuery);
                    
                    if ($adminResult->num_rows > 0) {
                        $adminId = $adminResult->fetch_assoc()['user_id'];
                        $notificationMessage = "New payment request from seller for {$sneaker['brand']} {$sneaker['model']}";
                        createNotification($adminId, $notificationMessage);
                    }
                    
                    // Redirect to payment status page
                    header('Location: ' . SITE_URL . '/seller/payment-status.php?request_id=' . $requestId);
                    exit;
                } else {
                    $errorMessage = 'Failed to submit payment request';
                }
            }
        }
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
                <h1>Request Payment</h1>
                <a href="<?php echo SITE_URL; ?>/seller/sales.php" class="btn btn-secondary">Back to Sales</a>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="payment-request-container">
                <div class="sneaker-details-card">
                    <h2>Sneaker Details</h2>
                    <div class="sneaker-info">
                        <div class="info-row">
                            <span class="label">Sneaker:</span>
                            <span class="value"><?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Size:</span>
                            <span class="value"><?php echo $sneaker['size']; ?> UK</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Order ID:</span>
                            <span class="value">#<?php echo $sneaker['order_id']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Order Date:</span>
                            <span class="value"><?php echo date('F j, Y', strtotime($sneaker['order_date'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Order Status:</span>
                            <span class="value status-badge <?php echo $sneaker['order_status']; ?>">
                                <?php echo ucfirst($sneaker['order_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="payment-details-card">
                    <h2>Payment Details</h2>
                    <div class="payment-info">
                        <div class="info-row">
                            <span class="label">Selling Price:</span>
                            <span class="value"><?php echo formatPrice($sneaker['price']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Platform Fee (<?php echo PLATFORM_FEE_PERCENTAGE; ?>%):</span>
                            <span class="value"><?php echo formatPrice($platformFee); ?></span>
                        </div>
                        <div class="info-row total">
                            <span class="label">Net Amount:</span>
                            <span class="value"><?php echo formatPrice($netAmount); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="payment-method-card">
                    <h2>Payment Method</h2>
                    <form method="POST" action="" id="payment-request-form">
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer" <?php echo $hasBankDetails && $bankDetails['account_number'] !== 'N/A' ? 'checked' : ''; ?>>
                                <label for="bank_transfer">Bank Transfer</label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="upi" name="payment_method" value="upi" <?php echo $hasBankDetails && !empty($bankDetails['upi_id']) ? 'checked' : ''; ?>>
                                <label for="upi">UPI Payment</label>
                            </div>
                        </div>
                        
                        <div id="bank-details" class="payment-details-section" style="display: <?php echo $hasBankDetails && $bankDetails['account_number'] !== 'N/A' ? 'block' : 'none'; ?>;">
                            <h3>Bank Account Details</h3>
                            <div class="form-group">
                                <label for="account_holder_name">Account Holder Name</label>
                                <input type="text" id="account_holder_name" name="account_holder_name" value="<?php echo $hasBankDetails ? $bankDetails['account_holder_name'] : $currentUser['full_name']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="account_number">Account Number</label>
                                <input type="text" id="account_number" name="account_number" value="<?php echo $hasBankDetails && $bankDetails['account_number'] !== 'N/A' ? $bankDetails['account_number'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="ifsc_code">IFSC Code</label>
                                <input type="text" id="ifsc_code" name="ifsc_code" value="<?php echo $hasBankDetails && $bankDetails['ifsc_code'] !== 'N/A' ? $bankDetails['ifsc_code'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="bank_name">Bank Name</label>
                                <input type="text" id="bank_name" name="bank_name" value="<?php echo $hasBankDetails && $bankDetails['bank_name'] !== 'N/A' ? $bankDetails['bank_name'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div id="upi-details" class="payment-details-section" style="display: <?php echo $hasBankDetails && !empty($bankDetails['upi_id']) ? 'block' : 'none'; ?>;">
                            <h3>UPI Details</h3>
                            <div class="form-group">
                                <label for="upi_id">UPI ID</label>
                                <input type="text" id="upi_id" name="upi_id" value="<?php echo $hasBankDetails && !empty($bankDetails['upi_id']) ? $bankDetails['upi_id'] : ''; ?>" placeholder="example@upi">
                            </div>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="save_details" value="1" checked>
                                Save payment details for future requests
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Submit Payment Request</button>
                            <a href="<?php echo SITE_URL; ?>/seller/sales.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-request-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sneaker-details-card,
.payment-details-card,
.payment-method-card {
    background-color: var(--bg-light);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.sneaker-info,
.payment-info {
    margin-top: 15px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.info-row:last-child {
    border-bottom: none;
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

.payment-methods {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 10px;
}

.payment-details-section {
    background-color: var(--bg-secondary);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.payment-details-section h3 {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.checkbox-group {
    margin: 20px 0;
}

.form-actions {
    display: flex;
    gap: 15px;
}

@media (max-width: 768px) {
    .info-row {
        flex-direction: column;
        gap: 5px;
    }
    
    .info-row .label {
        margin-bottom: 5px;
    }
    
    .payment-methods {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bankTransferRadio = document.getElementById('bank_transfer');
    const upiRadio = document.getElementById('upi');
    const bankDetailsSection = document.getElementById('bank-details');
    const upiDetailsSection = document.getElementById('upi-details');
    
    // Toggle payment details sections based on selected payment method
    function togglePaymentDetails() {
        if (bankTransferRadio.checked) {
            bankDetailsSection.style.display = 'block';
            upiDetailsSection.style.display = 'none';
        } else if (upiRadio.checked) {
            bankDetailsSection.style.display = 'none';
            upiDetailsSection.style.display = 'block';
        }
    }
    
    bankTransferRadio.addEventListener('change', togglePaymentDetails);
    upiRadio.addEventListener('change', togglePaymentDetails);
    
    // Form validation
    const form = document.getElementById('payment-request-form');
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        if (bankTransferRadio.checked) {
            const accountHolder = document.getElementById('account_holder_name').value.trim();
            const accountNumber = document.getElementById('account_number').value.trim();
            const ifscCode = document.getElementById('ifsc_code').value.trim();
            const bankName = document.getElementById('bank_name').value.trim();
            
            if (!accountHolder || !accountNumber || !ifscCode || !bankName) {
                isValid = false;
                alert('Please fill in all bank account details');
            }
        } else if (upiRadio.checked) {
            const upiId = document.getElementById('upi_id').value.trim();
            
            if (!upiId) {
                isValid = false;
                alert('Please enter your UPI ID');
            } else if (!upiId.includes('@')) {
                isValid = false;
                alert('Please enter a valid UPI ID (e.g., example@upi)');
            }
        } else {
            isValid = false;
            alert('Please select a payment method');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
