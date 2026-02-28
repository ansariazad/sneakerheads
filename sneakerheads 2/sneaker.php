<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if sneaker ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL);
    exit;
}

$sneakerId = (int)$_GET['id'];

// Get sneaker details
$sneakerQuery = "SELECT s.*, u.username as seller_username 
                FROM sneakers s 
                JOIN users u ON s.seller_id = u.user_id 
                WHERE s.sneaker_id = '$sneakerId' AND s.status = 'approved'";
$sneakerResult = $db->query($sneakerQuery);

if ($sneakerResult->num_rows === 0) {
    header('Location: ' . SITE_URL);
    exit;
}

$sneaker = $sneakerResult->fetch_assoc();
$pageTitle = $sneaker['brand'] . ' ' . $sneaker['model'];

// Get sneaker images
$imagesQuery = "SELECT * FROM sneaker_images WHERE sneaker_id = '$sneakerId'";
$imagesResult = $db->query($imagesQuery);

$images = [];
while ($row = $imagesResult->fetch_assoc()) {
    $images[] = $row;
}

// Get sneaker video
$videoQuery = "SELECT * FROM sneaker_videos WHERE sneaker_id = '$sneakerId'";
$videoResult = $db->query($videoQuery);
$video = $videoResult->num_rows > 0 ? $videoResult->fetch_assoc() : null;

// Get similar sneakers
$similarQuery = "SELECT s.*, 
                (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image 
                FROM sneakers s 
                WHERE s.status = 'approved' 
                AND s.sneaker_id != '$sneakerId' 
                AND (s.brand = '{$sneaker['brand']}' OR s.model LIKE '%{$sneaker['model']}%') 
                LIMIT 4";
$similarResult = $db->query($similarQuery);

$similarSneakers = [];
while ($row = $similarResult->fetch_assoc()) {
    $similarSneakers[] = $row;
}

// Handle add to cart
$successMessage = '';
$errorMessage = '';

if (isset($_POST['add_to_cart']) && Auth::isLoggedIn()) {
    $currentUser = Auth::getCurrentUser();
    $userId = $currentUser['user_id'];
    
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
}

// Handle add to wishlist
if (isset($_POST['add_to_wishlist']) && Auth::isLoggedIn()) {
    $currentUser = Auth::getCurrentUser();
    $userId = $currentUser['user_id'];
    
    // Check if already in wishlist
    if (isSneakerInWishlist($userId, $sneakerId)) {
        $errorMessage = 'This sneaker is already in your wishlist';
    } else {
        // Add to wishlist
        $addQuery = "INSERT INTO wishlist (user_id, sneaker_id) VALUES ('$userId', '$sneakerId')";
        
        if ($db->query($addQuery)) {
            $successMessage = 'Sneaker added to wishlist successfully';
        } else {
            $errorMessage = 'Failed to add sneaker to wishlist';
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <div class="product-detail">
        <div class="product-images">
            <?php if (count($images) > 0): ?>
                <div class="product-main-image">
                    <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $images[0]['image_path']; ?>" alt="<?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?>">
                </div>
                
                <div class="product-thumbnails">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="product-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                            <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $image['image_path']; ?>" alt="<?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?> - <?php echo $image['image_type']; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($video): ?>
                    <div class="product-video">
                        <video controls>
                            <source src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $video['video_path']; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="product-main-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="Sneaker placeholder">
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-info">
            <h1><?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?></h1>
            
            <div class="product-price">
                <?php echo formatPrice($sneaker['price']); ?>
            </div>
            
            <div class="product-meta">
                <p><strong>Size:</strong> <?php echo $sneaker['size']; ?> UK</p>
                <p><strong>Serial Number:</strong> <?php echo $sneaker['serial_number']; ?></p>
                <p><strong>Seller:</strong> <?php echo $sneaker['seller_username']; ?></p>
                <p><strong>Listed on:</strong> <?php echo date('F j, Y', strtotime($sneaker['created_at'])); ?></p>
            </div>
            
            <div class="product-description">
                <h3>Description</h3>
                <p><?php echo $sneaker['description'] ? nl2br($sneaker['description']) : 'No description provided.'; ?></p>
            </div>
            
            <div class="product-actions">
                <?php if (Auth::isLoggedIn()): ?>
                    <form method="POST" action="">
                        <button type="submit" name="add_to_cart" class="btn add-to-cart">Add to Cart</button>
                    </form>
                    
                    <form method="POST" action="">
                        <button type="submit" name="add_to_wishlist" class="btn btn-secondary">
                            <i class="far fa-heart"></i> Add to Wishlist
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn">Login to Purchase</a>
                <?php endif; ?>
            </div>
            
            <div class="product-guarantee">
                <div class="guarantee-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h4>100% Authentic</h4>
                        <p>All sneakers are verified by our experts</p>
                    </div>
                </div>
                
                <div class="guarantee-item">
                    <i class="fas fa-truck"></i>
                    <div>
                        <h4>Fast Shipping</h4>
                        <p>Delivery within 3-5 business days</p>
                    </div>
                </div>
                
                <div class="guarantee-item">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <h4>Secure Payment</h4>
                        <p>Multiple payment options available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (count($similarSneakers) > 0): ?>
        <div class="similar-products">
            <h2>Similar Sneakers</h2>
            
            <div class="grid">
                <?php foreach ($similarSneakers as $similar): ?>
                    <div class="card">
                        <div class="card-img">
                            <?php if ($similar['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $similar['image']; ?>" alt="<?php echo $similar['brand'] . ' ' . $similar['model']; ?>">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="Sneaker placeholder">
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $similar['brand'] . ' ' . $similar['model']; ?></h3>
                            <p class="card-text">Size: <?php echo $similar['size']; ?> UK</p>
                            <div class="card-price"><?php echo formatPrice($similar['price']); ?></div>
                        </div>
                        <div class="card-footer">
                            <a href="<?php echo SITE_URL; ?>/sneaker.php?id=<?php echo $similar['sneaker_id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.product-detail {
    margin-bottom: 40px;
}

.product-main-image {
    margin-bottom: 15px;
    border-radius: 8px;
    overflow: hidden;
}

.product-thumbnails {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.product-thumbnail {
    width: 80px;
    height: 80px;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
}

.product-thumbnail.active {
    border-color: var(--primary-color);
}

.product-video {
    margin-top: 20px;
}

.product-video video {
    width: 100%;
    border-radius: 8px;
}

.product-info h1 {
    font-size: 28px;
    margin-bottom: 15px;
}

.product-price {
    font-size: 24px;
    font-weight: bold;
    color: var(--accent-color);
    margin-bottom: 20px;
}

.product-meta {
    margin-bottom: 20px;
}

.product-meta p {
    margin-bottom: 10px;
}

.product-description {
    margin-bottom: 30px;
}

.product-description h3 {
    margin-bottom: 10px;
}

.product-actions {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
}

.product-guarantee {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 20px;
}

.guarantee-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.guarantee-item:last-child {
    margin-bottom: 0;
}

.guarantee-item i {
    font-size: 24px;
    color: var(--primary-color);
}

.guarantee-item h4 {
    margin-bottom: 5px;
}

.guarantee-item p {
    color: var(--text-secondary);
    font-size: 14px;
}

.similar-products {
    margin-top: 40px;
}

.similar-products h2 {
    margin-bottom: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Product image gallery
    const mainImage = document.querySelector('.product-main-image img');
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Update main image
            mainImage.src = this.querySelector('img').src;
            
            // Update active thumbnail
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>

