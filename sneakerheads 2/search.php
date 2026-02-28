<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Set page title
$pageTitle = 'Search Results';

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Get search query
$searchQuery = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

// Get filter parameters
$brand = isset($_GET['brand']) ? sanitizeInput($_GET['brand']) : '';
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] > 0 ? (float)$_GET['max_price'] : 100000;
$size = isset($_GET['size']) ? sanitizeInput($_GET['size']) : '';
$condition = isset($_GET['condition']) ? sanitizeInput($_GET['condition']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;
$offset = ($page - 1) * $itemsPerPage;

// Build the SQL query
$sql = "SELECT s.*, 
               (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image,
               u.username as seller_username
        FROM sneakers s 
        JOIN users u ON s.seller_id = u.user_id 
        WHERE s.status = 'approved'";

// Add search query
if (!empty($searchQuery)) {
    $sql .= " AND (s.brand LIKE '%$searchQuery%' OR s.model LIKE '%$searchQuery%' OR s.description LIKE '%$searchQuery%')";
}

// Add filters
if (!empty($brand)) {
    $sql .= " AND s.brand = '$brand'";
}

if (!empty($category)) {
    $sql .= " AND s.category = '$category'";
}

if (!empty($size)) {
    $sql .= " AND s.size = '$size'";
}

if (!empty($condition)) {
    $sql .= " AND s.condition = '$condition'";
}

$sql .= " AND s.price BETWEEN $minPrice AND $maxPrice";

// Count total results for pagination
$countSql = str_replace("s.*, (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image, u.username as seller_username", "COUNT(*) as total", $sql);
$countResult = $db->query($countSql);
$row = $countResult->fetch_assoc();
$totalResults = isset($row['total']) ? $row['total'] : 0;
$totalPages = ceil($totalResults / $itemsPerPage);

// Add sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY s.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY s.price DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY s.views DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY s.created_at DESC";
        break;
}

// Add pagination
$sql .= " LIMIT $itemsPerPage OFFSET $offset";

// Execute the query
$result = $db->query($sql);
$sneakers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sneakers[] = $row;
    }
}

// Get all brands, categories, sizes, and conditions for filters
$brandsQuery = "SELECT DISTINCT brand FROM sneakers WHERE status = 'approved' ORDER BY brand";
$categoriesQuery = "SELECT DISTINCT category FROM sneakers WHERE status = 'approved' ORDER BY category";
$sizesQuery = "SELECT DISTINCT size FROM sneakers WHERE status = 'approved' ORDER BY size";
$conditionsQuery = "SELECT DISTINCT `condition` FROM sneakers WHERE status = 'approved' ORDER BY `condition`";

$brandsResult = $db->query($brandsQuery);
$categoriesResult = $db->query($categoriesQuery);
$sizesResult = $db->query($sizesQuery);
$conditionsResult = $db->query($conditionsQuery);

$brands = [];
$categories = [];
$sizes = [];
$conditions = [];

if ($brandsResult) {
    while ($row = $brandsResult->fetch_assoc()) {
        $brands[] = $row['brand'];
    }
}

if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

if ($sizesResult) {
    while ($row = $sizesResult->fetch_assoc()) {
        $sizes[] = $row['size'];
    }
}

if ($conditionsResult) {
    while ($row = $conditionsResult->fetch_assoc()) {
        $conditions[] = $row['condition'];
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container">
    <div class="search-results-header">
        <h1><?php echo empty($searchQuery) ? 'All Sneakers' : 'Search Results for "' . htmlspecialchars($searchQuery) . '"'; ?></h1>
        <p><?php echo $totalResults; ?> results found</p>
    </div>

    <div class="search-results-container">
        <div class="filters-sidebar">
            <div class="filters-header">
                <h3>Filters</h3>
                <button id="clear-filters" class="btn-secondary">Clear All</button>
            </div>

            <form id="filter-form" action="search.php" method="GET">
                <?php if (!empty($searchQuery)): ?>
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php endif; ?>
                
                <div class="filter-section">
                    <h4>Price Range</h4>
                    <div class="price-range">
                        <input type="number" name="min_price" id="min-price" placeholder="Min" value="<?php echo $minPrice; ?>">
                        <span>to</span>
                        <input type="number" name="max_price" id="max-price" placeholder="Max" value="<?php echo $maxPrice < 100000 ? $maxPrice : ''; ?>">
                    </div>
                </div>

                <div class="filter-section">
                    <h4>Brand</h4>
                    <select name="brand">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $brandOption): ?>
                            <option value="<?php echo htmlspecialchars($brandOption); ?>" <?php echo $brand === $brandOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brandOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-section">
                    <h4>Category</h4>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $categoryOption): ?>
                            <option value="<?php echo htmlspecialchars($categoryOption); ?>" <?php echo $category === $categoryOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categoryOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-section">
                    <h4>Size</h4>
                    <select name="size">
                        <option value="">All Sizes</option>
                        <?php foreach ($sizes as $sizeOption): ?>
                            <option value="<?php echo htmlspecialchars($sizeOption); ?>" <?php echo $size === $sizeOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sizeOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-section">
                    <h4>Condition</h4>
                    <select name="condition">
                        <option value="">Any Condition</option>
                        <?php foreach ($conditions as $conditionOption): ?>
                            <option value="<?php echo htmlspecialchars($conditionOption); ?>" <?php echo $condition === $conditionOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($conditionOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-section">
                    <h4>Sort By</h4>
                    <select name="sort">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                    </select>
                </div>

                <button type="submit" class="btn">Apply Filters</button>
            </form>
        </div>

        <div class="search-results">
            <?php if (count($sneakers) > 0): ?>
                <div class="grid">
                    <?php foreach ($sneakers as $sneaker): ?>
                        <div class="sneaker-card">
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

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search fa-3x"></i>
                    <h2>No results found</h2>
                    <p>We couldn't find any sneakers matching your search criteria.</p>
                    <p>Try adjusting your filters or search for something else.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.search-results-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.search-results-header {
    margin-bottom: 30px;
}

.filters-sidebar {
    background-color: var(--bg-secondary);
    padding: 20px;
    border-radius: 8px;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.filter-section {
    margin-bottom: 20px;
}

.filter-section h4 {
    margin-bottom: 10px;
}

.price-range {
    display: flex;
    align-items: center;
    gap: 10px;
}

.price-range input {
    flex: 1;
}

.price-range span {
    color: var(--text-secondary);
}

.no-results {
    text-align: center;
    padding: 50px 0;
    background-color: var(--bg-secondary);
    border-radius: 8px;
}

.no-results i {
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.no-results h2 {
    margin-bottom: 10px;
}

.no-results p {
    color: var(--text-secondary);
    margin-bottom: 10px;
}

@media (max-width: 992px) {
    .search-results-container {
        grid-template-columns: 1fr;
    }
    
    .filters-sidebar {
        position: static;
        margin-bottom: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clear filters button
    document.getElementById('clear-filters').addEventListener('click', function() {
        const searchQuery = new URLSearchParams(window.location.search).get('q');
        if (searchQuery) {
            window.location.href = 'search.php?q=' + encodeURIComponent(searchQuery);
        } else {
            window.location.href = 'search.php';
        }
    });

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
                    alert('Added to wishlist!');
                } else if (data.error === 'login_required') {
                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>

