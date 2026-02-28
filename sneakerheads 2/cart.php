<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
Auth::requireLogin();

$pageTitle = 'Shopping Cart';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

$successMessage = '';
$errorMessage = '';

// Process add to cart
if (isset($_GET['add']) && is_numeric($_GET['add'])) {
    $sneakerId = (int)$_GET['add'];
    
    // Check if sneaker exists and is available
    $sneakerQuery = "SELECT * FROM sneakers WHERE sneaker_id = '$sneakerId' AND status = 'approved'";
    $sneakerResult = $db->query($sneakerQuery);
    
    if ($sneakerResult->num_rows > 0) {
        // Check if already in cart
        if (isSneakerInCart($userId, $sneakerId)) {
            $errorMessage = 'This sneaker is already in your cart';
        } else {
            // Add to cart
            $addQuery = "INSERT INTO cart (user_id, sneaker_id) VALUES ('$userId', '$sneakerId')";
            
            if ($db->query($addQuery)) {
                $successMessage = 'Sneaker added to cart successfully';
            } else {
                $errorMessage = 'Failed to add sneaker to cart';
            }
        }
    } else {
        $errorMessage = 'Sneaker not found or not available';
    }
}

// Process remove from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $cartId = (int)$_GET['remove'];
    
    $removeQuery = "DELETE FROM cart WHERE cart_id = '$cartId' AND user_id = '$userId'";
    
    if ($db->query($removeQuery)) {
        $successMessage = 'Item removed from cart successfully';
    } else {
        $errorMessage = 'Failed to remove item from cart';
    }
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

include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title">Shopping Cart</h1>
    
    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <?php if (count($cartItems) > 0): ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $item['image']; ?>" alt="<?php echo $item['brand'] . ' ' . $item['model']; ?>">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="Sneaker placeholder">
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-item-details">
                            <h3><?php echo $item['brand'] . ' ' . $item['model']; ?></h3>
                            <p>Size: <?php echo $item['size']; ?> UK</p>
                            <p>Serial Number: <?php echo $item['serial_number']; ?></p>
                        </div>
                        
                        <div class="cart-item-price">
                            <span><?php echo formatPrice($item['price']); ?></span>
                        </div>
                        
                        <div class="cart-item-actions">
                            <a href="?remove=<?php echo $item['cart_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this item from your cart?')">Remove</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h2>Order Summary</h2>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($cartTotal); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?php echo formatPrice($cartTotal); ?></span>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn btn-success checkout-btn">Proceed to Checkout</a>
                
                <div class="continue-shopping">
                    <a href="<?php echo SITE_URL; ?>">Continue Shopping</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any sneakers to your cart yet.</p>
            <a href="<?php echo SITE_URL; ?>" class="btn">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<style>
.cart-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.cart-items {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 20px;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto;
    gap: 20px;
    padding: 20px 0;
    border-bottom: 1px solid var(--border-color);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    overflow: hidden;
    border-radius: 8px;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-details h3 {
    margin-bottom: 10px;
}

.cart-item-details p {
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.cart-item-price {
    font-size: 18px;
    font-weight: bold;
    color: var(--accent-color);
}

.cart-summary {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 20px;
    height: fit-content;
}

.cart-summary h2 {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
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

.checkout-btn {
    width: 100%;
    margin-top: 20px;
    text-align: center;
}

.continue-shopping {
    margin-top: 15px;
    text-align: center;
}

.empty-cart {
    text-align: center;
    padding: 50px 0;
}

.empty-cart-icon {
    font-size: 60px;
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.empty-cart h2 {
    margin-bottom: 10px;
}

.empty-cart p {
    color: var(--text-secondary);
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .cart-container {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        grid-template-rows: auto auto;
    }
    
    .cart-item-price {
        grid-column: 2;
        grid-row: 2;
    }
    
    .cart-item-actions {
        grid-column: 1 / -1;
        grid-row: 3;
        margin-top: 10px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

