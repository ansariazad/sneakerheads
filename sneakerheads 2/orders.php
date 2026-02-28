<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Redirect if not logged in
if (!Auth::isLoggedIn()) {
    redirect('login.php?redirect=orders.php');
}

$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

// Get order ID from URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    redirect('orders.php');
}

// Fetch order details
$sql = "SELECT o.*, s.name as sneaker_name, s.images, s.size, s.condition, 
               u.username as seller_name, u.email as seller_email,
               a.street, a.city, a.state, a.postal_code, a.country
        FROM orders o
        JOIN sneakers s ON o.sneaker_id = s.sneaker_id
        JOIN users u ON s.seller_id = u.user_id
        LEFT JOIN addresses a ON o.shipping_address_id = a.address_id
        WHERE o.order_id = ? AND o.buyer_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if order not found or doesn't belong to current user
if (!$order) {
    redirect('orders.php');
}

// Set page title
$pageTitle = 'Order #' . $orderId;

// Include header
include 'includes/header.php';
?>

<div class="container">
    <div class="order-details-container">
        <div class="order-header">
            <h1>Order #<?php echo $orderId; ?></h1>
            <div class="order-status <?php echo strtolower($order['status']); ?>">
                <?php echo ucfirst($order['status']); ?>
            </div>
        </div>

        <div class="order-date">
            <p>Ordered on <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
        </div>

        <div class="order-sections">
            <div class="order-section">
                <div class="order-product">
                    <div class="product-image">
                        <?php
                        $images = json_decode($order['images'], true);
                        $mainImage = !empty($images) ? $images[0] : 'assets/images/placeholder.jpg';
                        ?>
                        <img src="<?php echo htmlspecialchars($mainImage); ?>" alt="<?php echo htmlspecialchars($order['sneaker_name']); ?>">
                    </div>
                    <div class="product-details">
                        <h3><?php echo htmlspecialchars($order['sneaker_name']); ?></h3>
                        <p>Size: <?php echo htmlspecialchars($order['size']); ?></p>
                        <p>Condition: <?php echo htmlspecialchars($order['condition']); ?></p>
                        <p>Price: $<?php echo number_format($order['price'], 2); ?></p>
                        <a href="sneaker.php?id=<?php echo $order['sneaker_id']; ?>" class="btn-secondary">View Sneaker</a>
                    </div>
                </div>
            </div>

            <div class="order-section">
                <h2>Order Summary</h2>
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($order['price'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>$<?php echo number_format($order['shipping_fee'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>$<?php echo number_format($order['tax'], 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="order-section">
                <h2>Shipping Information</h2>
                <div class="shipping-info">
                    <p><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></p>
                    <p><?php echo htmlspecialchars($order['street']); ?></p>
                    <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['postal_code']); ?></p>
                    <p><?php echo htmlspecialchars($order['country']); ?></p>
                </div>
            </div>

            <div class="order-section">
                <h2>Payment Information</h2>
                <div class="payment-info">
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                    <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
                    <?php if ($order['payment_id']): ?>
                        <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($order['payment_id']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="order-section">
                <h2>Seller Information</h2>
                <div class="seller-info">
                    <p><strong>Seller:</strong> <?php echo htmlspecialchars($order['seller_name']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($order['seller_email']); ?></p>
                </div>
            </div>

            <?php if ($order['status'] === 'delivered'): ?>
                <div class="order-section">
                    <h2>Leave a Review</h2>
                    <?php
                    // Check if user has already left a review
                    $reviewSql = "SELECT * FROM reviews WHERE order_id = ? AND buyer_id = ?";
                    $reviewStmt = $pdo->prepare($reviewSql);
                    $reviewStmt->execute([$orderId, $userId]);
                    $existingReview = $reviewStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existingReview): 
                    ?>
                        <div class="existing-review">
                            <h3>Your Review</h3>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $existingReview['rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p><?php echo htmlspecialchars($existingReview['comment']); ?></p>
                            <p class="review-date">Posted on <?php echo date('F j, Y', strtotime($existingReview['created_at'])); ?></p>
                        </div>
                    <?php else: ?>
                        <form id="review-form" action="ajax/submit-review.php" method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                            <input type="hidden" name="sneaker_id" value="<?php echo $order['sneaker_id']; ?>">
                            <input type="hidden" name="seller_id" value="<?php echo $order['seller_id']; ?>">
                            
                            <div class="form-group">
                                <label for="rating">Rating:</label>
                                <div class="star-rating">
                                    <input type="hidden" name="rating" id="rating" value="5">
                                    <i class="fas fa-star star-1 active" data-rating="1"></i>
                                    <i class="fas fa-star star-2 active" data-rating="2"></i>
                                    <i class="fas fa-star star-3 active" data-rating="3"></i>
                                    <i class="fas fa-star star-4 active" data-rating="4"></i>
                                    <i class="fas fa-star star-5 active" data-rating="5"></i>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="comment">Your Review:</label>
                                <textarea name="comment" id="comment" rows="4" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn">Submit Review</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($order['status'] === 'shipped'): ?>
                <div class="order-section">
                    <h2>Tracking Information</h2>
                    <div class="tracking-info">
                        <?php if ($order['tracking_number']): ?>
                            <p><strong>Tracking Number:</strong> <?php echo htmlspecialchars($order['tracking_number']); ?></p>
                            <p><strong>Carrier:</strong> <?php echo htmlspecialchars($order['shipping_carrier']); ?></p>
                            <a href="#" class="btn track-shipment" data-tracking="<?php echo htmlspecialchars($order['tracking_number']); ?>" data-carrier="<?php echo htmlspecialchars($order['shipping_carrier']); ?>">Track Shipment</a>
                            <button class="btn-success mark-delivered" data-order-id="<?php echo $orderId; ?>">Mark as Delivered</button>
                        <?php else: ?>
                            <p>Tracking information will be available once the seller ships your order.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
                <div class="order-section">
                    <h2>Order Actions</h2>
                    <div class="order-actions">
                        <button class="btn-danger cancel-order" data-order-id="<?php echo $orderId; ?>">Cancel Order</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Star rating functionality
    const stars = document.querySelectorAll('.star-rating i');
    const ratingInput = document.getElementById('rating');
    
    if (stars.length > 0 && ratingInput) {
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                ratingInput.value = rating;
                
                // Update star appearance
                stars.forEach(s => {
                    const starRating = parseInt(s.getAttribute('data-rating'));
                    if (starRating <= rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    }
    
    // Cancel order functionality
    const cancelButtons = document.querySelectorAll('.cancel-order');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel this order?')) {
                const orderId = this.getAttribute('data-order-id');
                
                fetch('ajax/cancel-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order cancelled successfully!');
                        window.location.reload();
                    } else {
                        alert(data.message || 'An error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });
    
    // Mark as delivered functionality
    const deliveredButtons = document.querySelectorAll('.mark-delivered');
    deliveredButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to mark this order as delivered?')) {
                const orderId = this.getAttribute('data-order-id');
                
                fetch('ajax/mark-delivered.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order marked as delivered!');
                        window.location.reload();
                    } else {
                        alert(data.message || 'An error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });
    
    // Track shipment functionality
    const trackButtons = document.querySelectorAll('.track-shipment');
    trackButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tracking = this.getAttribute('data-tracking');
            const carrier = this.getAttribute('data-carrier');
            
            // Open tracking in a new window based on carrier
            let trackingUrl = '';
            
            switch(carrier.toLowerCase()) {
                case 'usps':
                    trackingUrl = `https://tools.usps.com/go/TrackConfirmAction?tLabels=${tracking}`;
                    break;
                case 'ups':
                    trackingUrl = `https://www.ups.com/track?tracknum=${tracking}`;
                    break;
                case 'fedex':
                    trackingUrl = `https://www.fedex.com/fedextrack/?trknbr=${tracking}`;
                    break;
                case 'dhl':
                    trackingUrl = `https://www.dhl.com/en/express/tracking.html?AWB=${tracking}`;
                    break;
                default:
                    alert('Tracking information not available for this carrier.');
                    return;
            }
            
            window.open(trackingUrl, '_blank');
        });
    });
    
    // Review form submission
    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const formDataObj = {};
            formData.forEach((value, key) => {
                formDataObj[key] = value;
            });
            
            fetch('ajax/submit-review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(formDataObj).toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review submitted successfully!');
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>

