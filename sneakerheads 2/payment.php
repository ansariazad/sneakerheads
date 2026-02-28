<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
Auth::requireLogin();

$pageTitle = 'Payment';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if order ID is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: ' . SITE_URL . '/orders.php');
    exit;
}

$orderId = (int)$_GET['order_id'];

// Verify order belongs to user
$orderQuery = "SELECT * FROM orders WHERE order_id = '$orderId' AND user_id = '$userId'";
$orderResult = $db->query($orderQuery);

if ($orderResult->num_rows === 0) {
    header('Location: ' . SITE_URL . '/orders.php');
    exit;
}

$order = $orderResult->fetch_assoc();

// Check if payment method is UPI
if ($order['payment_method'] !== 'upi') {
    header('Location: ' . SITE_URL . '/order-confirmation.php?order_id=' . $orderId);
    exit;
}

// Check if payment is already completed
if ($order['payment_status'] === 'completed') {
    header('Location: ' . SITE_URL . '/order-confirmation.php?order_id=' . $orderId);
    exit;
}

include 'includes/header.php';
?>

<div class="container">
    <div class="payment-container">
        <h1>Complete Your Payment</h1>
        
        <div class="payment-details">
            <div class="order-info">
                <h2>Order #<?php echo $orderId; ?></h2>
                <p>Total Amount: <strong><?php echo formatPrice($order['total_amount']); ?></strong></p>
            </div>
            
            <div class="payment-qr">
                <img src="<?php echo SITE_URL; ?>/assets/images/sajidgpayqr.jpeg" alt="UPI QR Code">
                <p>Scan the QR code with any UPI app to pay</p>
            </div>
            
            <div class="payment-timer">
                <p>Completing payment automatically in <span id="countdown">10</span> seconds...</p>
            </div>
            
            <div class="payment-status"></div>
            
            <div class="payment-actions">
                <button class="btn btn-success upi-pay-button" data-redirect="<?php echo SITE_URL; ?>/order-confirmation.php?order_id=<?php echo $orderId; ?>">
                    I've Made the Payment
                </button>
                <a href="<?php echo SITE_URL; ?>/orders.php" class="btn btn-secondary">Cancel Payment</a>
            </div>
        </div>
    </div>
</div>

<style>
.payment-container {
    max-width: 600px;
    margin: 0 auto;
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 30px;
    text-align: center;
}

.payment-container h1 {
    margin-bottom: 30px;
}

.order-info {
    margin-bottom: 30px;
}

.payment-qr {
    margin-bottom: 30px;
}

.payment-qr img {
    max-width: 250px;
    margin-bottom: 15px;
    border: 1px solid var(--border-color);
    padding: 10px;
    background-color: white;
    border-radius: 8px;
}

.payment-timer {
    margin-bottom: 20px;
    font-size: 18px;
}

.payment-status {
    margin-bottom: 20px;
    min-height: 50px;
}

.payment-processing, .payment-success {
    padding: 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.payment-processing {
    background-color: rgba(243, 156, 18, 0.2);
    color: var(--warning-color);
}

.payment-success {
    background-color: rgba(46, 204, 113, 0.2);
    color: var(--success-color);
}

.payment-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Countdown timer
    let countdown = 10;
    const countdownElement = document.getElementById('countdown');
    const paymentStatus = document.querySelector('.payment-status');
    const upiPayButton = document.querySelector('.upi-pay-button');
    
    const timer = setInterval(function() {
        countdown--;
        countdownElement.textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(timer);
            simulatePayment();
        }
    }, 1000);
    
    // Simulate payment
    function simulatePayment() {
        upiPayButton.disabled = true;
        paymentStatus.innerHTML = '<div class="payment-processing"><i class="fas fa-spinner fa-spin"></i> Processing payment...</div>';
        
        setTimeout(() => {
            paymentStatus.innerHTML = '<div class="payment-success"><i class="fas fa-check-circle"></i> Payment successful!</div>';
            
            // Update payment status in database via AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo SITE_URL; ?>/ajax/update-payment.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Redirect to order confirmation page after 2 seconds
                    setTimeout(() => {
                        window.location.href = upiPayButton.getAttribute('data-redirect');
                    }, 2000);
                }
            };
            xhr.send('order_id=<?php echo $orderId; ?>');
        }, 3000);
    }
    
    // Manual payment button
    upiPayButton.addEventListener('click', function() {
        clearInterval(timer);
        simulatePayment();
    });
});
</script>

<?php include 'includes/footer.php'; ?>

