<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Redirect if not logged in
if (!Auth::isLoggedIn()) {
    header("Location: login.php?redirect=wishlist.php");
    exit;
}

$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Handle remove from wishlist
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $sneakerId = (int)$_GET['remove'];
    $removeQuery = "DELETE FROM wishlist WHERE user_id = '$userId' AND sneaker_id = '$sneakerId'";
    if ($db->query($removeQuery)) {
        header("Location: wishlist.php?removed=1");
        exit;
    } else {
        $error = "Failed to remove item from wishlist.";
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;
$offset = ($page - 1) * $itemsPerPage;

// Get total wishlist items count
$countQuery = "SELECT COUNT(*) as total FROM wishlist WHERE user_id = '$userId'";
$countResult = $db->query($countQuery);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get wishlist items
$wishlistItems = [];
if ($totalItems > 0) {
    $query = "SELECT w.*, s.brand, s.model, s.size, s.price, s.status,
                    (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image,
                    u.username as seller_username
            FROM wishlist w
            JOIN sneakers s ON w.sneaker_id = s.sneaker_id
            JOIN users u ON s.seller_id = u.user_id
            WHERE w.user_id = '$userId'
            ORDER BY w.wishlist_id DESC
            LIMIT $offset, $itemsPerPage";
    
    $result = $db->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $wishlistItems[] = $row;
        }
    } else {
        $error = "Failed to retrieve wishlist items: " . $conn->error;
        error_log("Wishlist query error: " . $conn->error);
    }
}

// Set page title
$pageTitle = 'My Wishlist';

// Include header
include 'includes/header.php';
?>

<div class="container">
    <div class="wishlist-container">
        <div class="wishlist-header">
            <h1>My Wishlist</h1>
            <?php if (isset($_GET['removed'])): ?>
                <div class="alert alert-success">Item removed from wishlist successfully.</div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
        </div>

        <?php if (count($wishlistItems) > 0): ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="sneaker-card">
                        <div class="card-img">
                            <?php
                            $image = !empty($item['image']) ? 'assets/uploads/sneakers/' . $item['image'] : 'assets/images/placeholder.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($item['brand'] . ' ' . $item['model']); ?>">
                            <a href="wishlist.php?remove=<?php echo $item['sneaker_id']; ?>" class="remove-wishlist" onclick="return confirm('Are you sure you want to remove this item from your wishlist?')">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($item['brand'] . ' ' . $item['model']); ?></h3>
                            <p class="card-text">Size: <?php echo htmlspecialchars($item['size']); ?> UK</p>
                            <div class="card-price"><?php echo formatPrice($item['price']); ?></div>
                        </div>
                        <div class="card-footer">
                            <a href="sneaker.php?id=<?php echo $item['sneaker_id']; ?>" class="btn">View Details</a>
                            <button class="btn-secondary add-to-cart" data-id="<?php echo $item['sneaker_id']; ?>">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-wishlist">
                <i class="fas fa-heart-broken fa-4x"></i>
                <h2>Your wishlist is empty</h2>
                <p>Browse our collection and add items to your wishlist.</p>
                <a href="index.php" class="btn">Browse Sneakers</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.wishlist-container {
    padding: 40px 0;
}

.wishlist-header {
    margin-bottom: 30px;
    text-align: center;
}

.wishlist-header h1 {
    font-size: 32px;
    margin-bottom: 15px;
    color: #333;
    position: relative;
    display: inline-block;
}

.wishlist-header h1:after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, #3a86ff, #5e60ce);
    border-radius: 3px;
}

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.no-wishlist {
    text-align: center;
    padding: 80px 0;
    background-color: #f9f9f9;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.no-wishlist i {
    color: #ccc;
    margin-bottom: 25px;
}

.no-wishlist h2 {
    margin-bottom: 15px;
    color: #333;
    font-size: 24px;
}

.no-wishlist p {
    color: #666;
    margin-bottom: 25px;
    font-size: 16px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.alert {
    padding: 15px 20px;
    margin-bottom: 25px;
    border-radius: 8px;
    font-weight: 500;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.pagination a, .pagination span {
    margin: 0 5px;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
}

.pagination a:hover {
    background-color: #f5f5f5;
    border-color: #3a86ff;
}

.pagination span.active {
    background: linear-gradient(90deg, #3a86ff, #5e60ce);
    color: white;
    border-color: #3a86ff;
}

@media (max-width: 768px) {
    .wishlist-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 480px) {
    .wishlist-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sneakerId = this.getAttribute('data-id');
            
            // AJAX request to add to cart
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/add-to-cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        
                        if (response.success) {
                            alert('Added to cart successfully!');
                            // Update cart count in header if needed
                            const cartBadge = document.querySelector('.cart-icon .badge');
                            if (cartBadge) {
                                cartBadge.textContent = response.cartCount;
                            }
                        } else {
                            alert(response.message || 'Failed to add to cart.');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('An error occurred. Please try again.');
                    }
                } else {
                    alert('An error occurred. Please try again.');
                }
            };
            
            xhr.send('sneaker_id=' + sneakerId);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>

