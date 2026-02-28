<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Get featured sneakers
$featuredQuery = "SELECT s.*, 
                        (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image,
                        u.username as seller_username
                 FROM sneakers s
                 JOIN users u ON s.seller_id = u.user_id
                 WHERE s.status = 'approved' AND s.featured = 1
                 ORDER BY s.created_at DESC
                 LIMIT 8";
$featuredResult = $db->query($featuredQuery);
$featuredSneakers = [];

if ($featuredResult) {
    while ($row = $featuredResult->fetch_assoc()) {
        $featuredSneakers[] = $row;
    }
}

// Get new arrivals
$newArrivalsQuery = "SELECT s.*, 
                           (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image,
                           u.username as seller_username
                      FROM sneakers s
                      JOIN users u ON s.seller_id = u.user_id
                      WHERE s.status = 'approved'
                      ORDER BY s.created_at DESC
                      LIMIT 8";
$newArrivalsResult = $db->query($newArrivalsQuery);
$newArrivals = [];

if ($newArrivalsResult) {
    while ($row = $newArrivalsResult->fetch_assoc()) {
        $newArrivals[] = $row;
    }
}

// Get popular brands
$brandsQuery = "SELECT DISTINCT brand, COUNT(*) as count 
                FROM sneakers 
                WHERE status = 'approved' 
                GROUP BY brand 
                ORDER BY count DESC 
                LIMIT 6";
$brandsResult = $db->query($brandsQuery);
$popularBrands = [];

if ($brandsResult) {
    while ($row = $brandsResult->fetch_assoc()) {
        $popularBrands[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <div class="hero">
        <div class="hero-content">
            <h1>Find Your Perfect Pair</h1>
            <p>Buy and sell authentic sneakers with confidence</p>
            <div class="hero-buttons">
                <a href="search.php" class="btn">Shop Now</a>
                <a href="seller/add-sneaker.php" class="btn btn-secondary">Sell Sneakers</a>
            </div>
        </div>
    </div>

    <!--  class="btn btn-secondary">Sell Sneakers</a>
            </div>
        </div>
    </div>

    <!-- Featured Sneakers Section -->
    <div class="featured-section">
        <div class="section-header">
            <h2>Featured Sneakers</h2>
            <a href="search.php?featured=1" class="view-all">View All</a>
        </div>
        
        <div class="grid">
            <?php foreach ($featuredSneakers as $sneaker): ?>
                <div class="sneaker-card fade-in">
                    <div class="card-img">
                        <?php
                        $image = !empty($sneaker['image']) ? 'assets/uploads/sneakers/' . $sneaker['image'] : 'assets/images/jogging.png';
                        ?>
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($sneaker['brand'] . ' ' . $sneaker['model']); ?>">
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($sneaker['brand'] . ' ' . $sneaker['model']); ?></h3>
                        <p class="card-text">Size: <?php echo htmlspecialchars($sneaker['size']); ?> UK</p>
                        <div class="card-price"><?php echo formatPrice($sneaker['price']); ?></div>
                    </div>
                    <div class="card-footer">
                        <a href="sneaker.php?id=<?php echo $sneaker['sneaker_id']; ?>" class="btn">View Details</a>
                        <button class="btn-secondary add-to-wishlist" data-id="<?php echo $sneaker['sneaker_id']; ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- New Arrivals Section -->
    <div class="featured-section">
        <div class="section-header">
            <h2>New Arrivals</h2>
            <a href="search.php?sort=newest" class="view-all">View All</a>
        </div>
        
        <div class="grid">
            <?php foreach ($newArrivals as $sneaker): ?>
                <div class="sneaker-card fade-in">
                    <div class="card-img">
                        <?php
                        $image = !empty($sneaker['image']) ? 'assets/uploads/sneakers/' . $sneaker['image'] : 'assets/images/jogging.png';
                        ?>
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($sneaker['brand'] . ' ' . $sneaker['model']); ?>">
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($sneaker['brand'] . ' ' . $sneaker['model']); ?></h3>
                        <p class="card-text">Size: <?php echo htmlspecialchars($sneaker['size']); ?> UK</p>
                        <div class="card-price"><?php echo formatPrice($sneaker['price']); ?></div>
                    </div>
                    <div class="card-footer">
                        <a href="sneaker.php?id=<?php echo $sneaker['sneaker_id']; ?>" class="btn">View Details</a>
                        <button class="btn-secondary add-to-wishlist" data-id="<?php echo $sneaker['sneaker_id']; ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Popular Brands Section -->
    <div class="brands-section">
        <div class="section-header">
            <h2>Popular Brands</h2>
            <a href="search.php" class="view-all">View All Brands</a>
        </div>
        
        <div class="brands-grid">
            <?php foreach ($popularBrands as $brand): ?>
                <a href="search.php?brand=<?php echo urlencode($brand['brand']); ?>" class="brand-card fade-in">
                    <h3><?php echo htmlspecialchars($brand['brand']); ?></h3>
                    <p><?php echo $brand['count']; ?> products</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="how-it-works">
        <div class="section-header">
            <h2>How It Works</h2>
        </div>
        
        <div class="steps-grid">
            <div class="step-card fade-in">
                <div class="step-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Find</h3>
                <p>Browse our collection of authentic sneakers from top sellers</p>
            </div>
            
            <div class="step-card fade-in">
                <div class="step-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Buy</h3>
                <p>Purchase with confidence using our secure payment system</p>
            </div>
            
            <div class="step-card fade-in">
                <div class="step-icon">
                    <i class="fas fa-box"></i>
                </div>
                <h3>Enjoy</h3>
                <p>Receive your authenticated sneakers delivered to your doorstep</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to wishlist functionality
    const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sneakerId = this.getAttribute('data-id');
            const thisButton = this;
            
            // AJAX request to add to wishlist
            fetch('ajax/add-to-wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'sneaker_id=' + sneakerId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Change button appearance
                    thisButton.innerHTML = '<i class="fas fa-heart" style="color: var(--error-color);"></i>';
                    thisButton.classList.add('in-wishlist');
                    
                    // Show notification
                    showNotification('Added to wishlist!', 'success');
                } else if (data.error === 'login_required') {
                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                } else {
                    showNotification(data.message || 'An error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        });
    });
    
    // Notification function
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'notification ' + type;
        notification.innerHTML = message;
        
        // Add to body
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Add animation to cards on scroll
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.sneaker-card, .brand-card, .step-card').forEach(card => {
        card.classList.remove('fade-in');
        observer.observe(card);
    });
});
</script>

<?php include 'includes/footer.php'; ?>

