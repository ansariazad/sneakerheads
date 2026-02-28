<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
Auth::requireLogin();

$pageTitle = 'Checkout';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if cart is empty
$cartCountQuery = "SELECT COUNT(*) as count FROM cart WHERE user_id = '$userId'";
$cartCountResult = $db->query($cartCountQuery);
$cartCount = $cartCountResult->fetch_assoc()['count'];

if ($cartCount == 0) {
    header('Location: ' . SITE_URL . '/cart.php');
    exit;
}

// Get user details
$userQuery = "SELECT * FROM users WHERE user_id = '$userId'";
$userResult = $db->query($userQuery);
$user = $userResult->fetch_assoc();

// Get user addresses
$addressQuery = "SELECT * FROM addresses WHERE user_id = '$userId' ORDER BY is_default DESC";
$addressResult = $db->query($addressQuery);

$addresses = [];
while ($row = $addressResult->fetch_assoc()) {
    $addresses[] = $row;
}

// Get cart items
$cartQuery = "SELECT c.cart_id, s.*, 
              (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image 
              FROM cart c 
              JOIN sneakers s ON c.sneaker_id = s.sneaker_id 
              WHERE c.user_id = '$userId'";
$cartResult = $db->query($cartQuery);

$cartItems = [];
while ($row = $cartResult->fetch_assoc()) {
    $cartItems[] = $row;
}

// Calculate cart total
$cartTotal = getCartTotal($userId);

$successMessage = '';
$errorMessage = '';

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addressId = (int)$_POST['address_id'];
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    
    // Validate address
    $addressCheckQuery = "SELECT * FROM addresses WHERE address_id = '$addressId' AND user_id = '$userId'";
    $addressCheckResult = $db->query($addressCheckQuery);
    
    if ($addressCheckResult->num_rows === 0) {
        $errorMessage = 'Invalid address selected';
    } 
    // Validate payment method
    elseif ($paymentMethod !== 'upi' && $paymentMethod !== 'cod') {
        $errorMessage = 'Invalid payment method selected';
    } 
    else {
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Calculate total amount
            $totalAmount = $cartTotal;
            if ($paymentMethod === 'cod') {
                $totalAmount += COD_FEE;
            }
            
            // Generate tracking ID
            $trackingId = generateTrackingId();
            
            // Calculate delivery ETA (5 days from now)
            $deliveryEta = calculateDeliveryEta();
            
            // Create order
            $orderQuery = "INSERT INTO orders (
                          user_id, address_id, total_amount, payment_method, 
                          tracking_id, delivery_eta
                          ) VALUES (
                          '$userId', '$addressId', '$totalAmount', '$paymentMethod',
                          '$trackingId', '$deliveryEta'
                          )";
            
            if (!$db->query($orderQuery)) {
                throw new Exception('Failed to create order');
            }
            
            $orderId = $db->getLastId();
            
            // Add order items
            foreach ($cartItems as $item) {
                $sneakerId = $item['sneaker_id'];
                $price = $item['price'];
                
                $orderItemQuery = "INSERT INTO order_items (order_id, sneaker_id, price) 
                                  VALUES ('$orderId', '$sneakerId', '$price')";
                
                if (!$db->query($orderItemQuery)) {
                    throw new Exception('Failed to add order item');
                }
                
                // Update sneaker status to sold
                $updateSneakerQuery = "UPDATE sneakers SET status = 'sold' WHERE sneaker_id = '$sneakerId'";
                
                if (!$db->query($updateSneakerQuery)) {
                    throw new Exception('Failed to update sneaker status');
                }
                
                // Create notification for seller
                $sellerId = $item['seller_id'];
                $notificationMessage = "Your sneaker {$item['brand']} {$item['model']} has been sold!";
                createNotification($sellerId, $notificationMessage);
            }
            
            // Clear cart
            $clearCartQuery = "DELETE FROM cart WHERE user_id = '$userId'";
            
            if (!$db->query($clearCartQuery)) {
                throw new Exception('Failed to clear cart');
            }
            
            // Create notification for buyer
            $notificationMessage = "Your order #$orderId has been placed successfully!";
            createNotification($userId, $notificationMessage);
            
            // Commit transaction
            $db->commit();
            
            // Redirect to order confirmation page
            if ($paymentMethod === 'upi') {
                header('Location: ' . SITE_URL . '/payment.php?order_id=' . $orderId);
            } else {
                header('Location: ' . SITE_URL . '/order-confirmation.php?order_id=' . $orderId);
            }
            exit;
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            $errorMessage = $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title">Checkout</h1>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <div class="checkout-container">
        <div class="checkout-form">
            <form method="POST" action="" data-validate>
                <section class="checkout-section">
                    <h2>Shipping Address</h2>
                    
                    <?php if (count($addresses) > 0): ?>
                        <div class="address-selection">
                            <?php foreach ($addresses as $address): ?>
                                <div class="address-option">
                                    <label>
                                        <input type="radio" name="address_id" value="<?php echo $address['address_id']; ?>" <?php echo $address['is_default'] ? 'checked' : ''; ?> required>
                                        <div class="address-card">
                                            <?php if ($address['is_default']): ?>
                                                <div class="default-badge">Default</div>
                                            <?php endif; ?>
                                            
                                            <div class="address-details">
                                                <p><?php echo $address['address_line1']; ?></p>
                                                <?php if ($address['address_line2']): ?>
                                                    <p><?php echo $address['address_line2']; ?></p>
                                                <?php endif; ?>
                                                <p><?php echo $address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']; ?></p>
                                                <p><?php echo $address['country']; ?></p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="add-address-link">
                            <a href="<?php echo SITE_URL; ?>/account.php#addresses">Add New Address</a>
                        </div>
                    <?php else: ?>
                        <div class="no-addresses">
                            <p>You don't have any saved addresses. Please add an address to continue.</p>
                            <a href="<?php echo SITE_URL; ?>/account.php#addresses" class="btn">Add Address</a>
                        </div>
                    <?php endif; ?>
                </section>
                
                <section class="checkout-section">
                    <h2>Payment Method</h2>
                    
                    <div class="payment-methods">
                        <div class="payment-option">
                            <label>
                                <input type="radio" name="payment_method" value="upi" class="payment-method" checked required>
                                <div class="payment-card">
                                    <div class="payment-icon">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div class="payment-details">
                                        <h3>Pay with UPI</h3>
                                        <p>Make payment using UPI apps like Google Pay, PhonePe, etc.</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <label>
                                <input type="radio" name="payment_method" value="cod" class="payment-method" required>
                                <div class="payment-card">
                                    <div class="payment-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="payment-details">
                                        <h3>Cash on Delivery</h3>
                                        <p>Pay when you receive your order (additional fee of ₹<?php echo COD_FEE; ?> applies)</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </section>
                
                <?php if (count($addresses) > 0): ?>
                    <div class="checkout-actions">
                        <button type="submit" class="btn btn-success">Place Order</button>
                        <a href="<?php echo SITE_URL; ?>/cart.php" class="btn btn-secondary">Back to Cart</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="order-summary">
            <h2>Order Summary</h2>
            
            <div class="order-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <div class="order-item-image">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $item['image']; ?>" alt="<?php echo $item['brand'] . ' ' . $item['model']; ?>">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="Sneaker placeholder">
                            <?php endif; ?>
                        </div>
                        
                        <div class="order-item-details">
                            <h3><?php echo $item['brand'] . ' ' . $item['model']; ?></h3>
                            <p>Size: <?php echo $item['size']; ?> UK</p>
                        </div>
                        
                        <div class="order-item-price">
                            <?php echo formatPrice($item['price']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-totals">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($cartTotal); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                
                <div class="summary-row cod-fee-row" style="display: none;">
                    <span>COD Fee</span>
                    <span class="cod-fee" data-fee="<?php echo COD_FEE; ?>">₹<?php echo COD_FEE; ?></span>
                </div>
                
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="order-total" data-total="<?php echo $cartTotal; ?>"><?php echo formatPrice($cartTotal); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-container {
    display: grid;
    grid-template-columns: 3fr 2fr;
    gap: 30px;
}

.checkout-form {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 20px;
}

.checkout-section {
    margin-bottom: 30px;
}

.checkout-section h2 {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.address-selection, .payment-methods {
    display: grid;
    gap: 15px;
}

.address-option label, .payment-option label {
    display: flex;
    cursor: pointer;
}

.address-card, .payment-card {
    flex: 1;
    padding: 15px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    position: relative;
    transition: var(--transition);
}

input[type="radio"]:checked + .address-card,
input[type="radio"]:checked + .payment-card {
    border-color: var(--primary-color);
    background-color: rgba(52, 152, 219, 0.1);
}

.address-option input[type="radio"],
.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.default-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: var(--primary-color);
    color: white;
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 4px;
}

.add-address-link {
    margin-top: 15px;
    text-align: right;
}

.payment-card {
    display: flex;
    align-items: center;
}

.payment-icon {
    font-size: 24px;
    margin-right: 15px;
    color: var(--primary-color);
}

.payment-details h3 {
    margin-bottom: 5px;
}

.payment-details p {
    color: var(--text-secondary);
    font-size: 14px;
}

.checkout-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
}

.order-summary {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 20px;
    height: fit-content;
}

.order-summary h2 {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.order-items {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.order-item {
    display: grid;
    grid-template-columns: 60px 1fr auto;
    gap: 15px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 60px;
    height: 60px;
    overflow: hidden;
    border-radius: 4px;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item-details h3 {
    font-size: 16px;
    margin-bottom: 5px;
}

.order-item-details p {
    color: var(--text-secondary);
    font-size: 14px;
}

.order-item-price {
    font-weight: bold;
    color: var(--accent-color);
}

.order-totals {
    padding-top: 15px;
    border-top: 1px solid var(--border-color);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.summary-row.total {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid var(--border-color);
    font-size: 20px;
    font-weight: bold;
}

.no-addresses {
    text-align: center;
    padding: 20px;
    background-color: var(--bg-light);
    border-radius: 8px;
}

.no-addresses p {
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .checkout-container {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        order: -1;
        margin-bottom: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    const codFeeRow = document.querySelector('.cod-fee-row');
    const codFee = parseFloat(document.querySelector('.cod-fee').getAttribute('data-fee'));
    const orderTotal = document.querySelector('.order-total');
    const originalTotal = parseFloat(orderTotal.getAttribute('data-total'));
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'cod') {
                codFeeRow.style.display = 'flex';
                orderTotal.textContent = '₹' + (originalTotal + codFee).toFixed(2);
            } else {
                codFeeRow.style.display = 'none';
                orderTotal.textContent = '₹' + originalTotal.toFixed(2);
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>

