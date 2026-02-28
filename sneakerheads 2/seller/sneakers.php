<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require seller access
Auth::requireSeller();

$pageTitle = 'My Listings';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle status filter
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'all';
$validStatuses = ['all', 'pending', 'approved', 'rejected', 'sold'];
if (!in_array($statusFilter, $validStatuses)) {
    $statusFilter = 'all';
}

// Handle search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Build query
$query = "SELECT s.*, 
          (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image 
          FROM sneakers s 
          WHERE s.seller_id = '$userId'";

if ($statusFilter !== 'all') {
    $query .= " AND s.status = '$statusFilter'";
}

if (!empty($search)) {
    $query .= " AND (s.brand LIKE '%$search%' OR s.model LIKE '%$search%' OR s.serial_number LIKE '%$search%')";
}

// Count total items for pagination
$countQuery = str_replace("s.*, (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image", "COUNT(*) as total", $query);
$countResult = $db->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalItems = isset($countRow['total']) ? $countRow['total'] : 0;
$totalPages = ceil($totalItems / $itemsPerPage);

// Add pagination to query
$query .= " ORDER BY s.created_at DESC LIMIT $offset, $itemsPerPage";
$result = $db->query($query);

$sneakers = [];
while ($row = $result->fetch_assoc()) {
    $sneakers[] = $row;
}

// Handle delete action
$successMessage = '';
$errorMessage = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $sneakerId = (int)$_GET['delete'];
    
    // Check if sneaker belongs to seller and is not sold
    $checkQuery = "SELECT sneaker_id, status FROM sneakers WHERE sneaker_id = '$sneakerId' AND seller_id = '$userId' AND status != 'sold'";
    $checkResult = $db->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Delete sneaker images
            $deleteImagesQuery = "DELETE FROM sneaker_images WHERE sneaker_id = '$sneakerId'";
            if (!$db->query($deleteImagesQuery)) {
                throw new Exception('Failed to delete sneaker images');
            }
            
            // Delete sneaker videos
            $deleteVideosQuery = "DELETE FROM sneaker_videos WHERE sneaker_id = '$sneakerId'";
            if (!$db->query($deleteVideosQuery)) {
                throw new Exception('Failed to delete sneaker videos');
            }
            
            // Delete purchase bills
            $deleteBillsQuery = "DELETE FROM purchase_bills WHERE sneaker_id = '$sneakerId'";
            if (!$db->query($deleteBillsQuery)) {
                throw new Exception('Failed to delete purchase bills');
            }
            
            // Delete sneaker
            $deleteSneakerQuery = "DELETE FROM sneakers WHERE sneaker_id = '$sneakerId'";
            if (!$db->query($deleteSneakerQuery)) {
                throw new Exception('Failed to delete sneaker');
            }
            
            // Commit transaction
            $db->commit();
            
            $successMessage = 'Sneaker deleted successfully';
            
            // Refresh page to update listing
            header('Location: ' . SITE_URL . '/seller/sneakers.php?status=' . $statusFilter . '&search=' . urlencode($search) . '&success=deleted');
            exit;
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            $errorMessage = $e->getMessage();
        }
    } else {
        $errorMessage = 'You cannot delete this sneaker';
    }
}

// Check for success message from URL
if (isset($_GET['success']) && $_GET['success'] === 'deleted') {
    $successMessage = 'Sneaker deleted successfully';
}

include '../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3>Seller Dashboard</h3>
            <ul>
                <li><a href="<?php echo SITE_URL; ?>/seller/dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/sneakers.php" class="active">My Listings</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/add-sneaker.php">Add Sneaker</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/sales.php">My Sales</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/payments.php">Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>My Sneaker Listings</h1>
                <a href="<?php echo SITE_URL; ?>/seller/add-sneaker.php" class="btn">Add New Sneaker</a>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="filter-section">
                <div class="status-filter">
                    <span>Filter by Status:</span>
                    <div class="filter-buttons">
                        <a href="?status=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">All</a>
                        <a href="?status=pending<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">Pending</a>
                        <a href="?status=approved<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $statusFilter === 'approved' ? 'active' : ''; ?>">Approved</a>
                        <a href="?status=rejected<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $statusFilter === 'rejected' ? 'active' : ''; ?>">Rejected</a>
                        <a href="?status=sold<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $statusFilter === 'sold' ? 'active' : ''; ?>">Sold</a>
                    </div>
                </div>
                
                <div class="search-section">
                    <form action="" method="GET">
                        <input type="hidden" name="status" value="<?php echo $statusFilter; ?>">
                        <div class="search-input">
                            <input type="text" name="search" placeholder="Search by brand, model, or serial number" value="<?php echo $search; ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (count($sneakers) > 0): ?>
                <div class="sneakers-grid">
                    <?php foreach ($sneakers as $sneaker): ?>
                        <div class="sneaker-card">
                            <div class="sneaker-status <?php echo $sneaker['status']; ?>">
                                <?php echo ucfirst($sneaker['status']); ?>
                            </div>
                            
                            <div class="sneaker-image">
                                <?php if ($sneaker['image']): ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $sneaker['image']; ?>" alt="<?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?>">
                                <?php else: ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.jpg" alt="Sneaker placeholder">
                                <?php endif; ?>
                            </div>
                            
                            <div class="sneaker-details">
                                <h3><?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?></h3>
                                <p>Size: <?php echo $sneaker['size']; ?> UK</p>
                                <p>Serial: <?php echo $sneaker['serial_number']; ?></p>
                                <div class="sneaker-price"><?php echo formatPrice($sneaker['price']); ?></div>
                                <p class="sneaker-date">Listed on <?php echo date('M j, Y', strtotime($sneaker['created_at'])); ?></p>
                            </div>
                            
                            <div class="sneaker-actions">
                                <a href="<?php echo SITE_URL; ?>/sneaker.php?id=<?php echo $sneaker['sneaker_id']; ?>" class="btn btn-secondary">View</a>
                                
                                <?php if ($sneaker['status'] !== 'sold'): ?>
                                    <a href="<?php echo SITE_URL; ?>/seller/edit-sneaker.php?id=<?php echo $sneaker['sneaker_id']; ?>" class="btn">Edit</a>
                                    
                                    <?php if ($sneaker['status'] !== 'approved'): ?>
                                        <a href="?delete=<?php echo $sneaker['sneaker_id']; ?>&status=<?php echo $statusFilter; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this sneaker?')">Delete</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?status=<?php echo $statusFilter; ?>&page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?status=<?php echo $statusFilter; ?>&page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?status=<?php echo $statusFilter; ?>&page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-sneakers">
                    <div class="no-data-icon">
                        <i class="fas fa-shoe-prints"></i>
                    </div>
                    <h2>No sneakers found</h2>
                    <?php if (!empty($search)): ?>
                        <p>No sneakers match your search criteria. Try a different search or clear filters.</p>
                        <a href="?status=all" class="btn">Clear Filters</a>
                    <?php elseif ($statusFilter !== 'all'): ?>
                        <p>You don't have any sneakers with status "<?php echo ucfirst($statusFilter); ?>".</p>
                        <a href="?status=all" class="btn">View All Sneakers</a>
                    <?php else: ?>
                        <p>You haven't listed any sneakers yet.</p>
                        <a href="<?php echo SITE_URL; ?>/seller/add-sneaker.php" class="btn">Add Your First Sneaker</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.filter-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    background-color: var(--bg-secondary);
    padding: 15px;
    border-radius: 8px;
}

.status-filter {
    display: flex;
    align-items: center;
    gap: 15px;
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.filter-btn {
    padding: 5px 15px;
    border-radius: 20px;
    background-color: var(--bg-light);
    color: var(--text-color);
    font-size: 14px;
    transition: var(--transition);
}

.filter-btn:hover, .filter-btn.active {
    background-color: var(--primary-color);
    color: white;
}

.search-input {
    display: flex;
    width: 300px;
}

.search-input input {
    flex: 1;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    margin-bottom: 0;
}

.search-input button {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    padding: 10px 15px;
}

.sneakers-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.sneaker-card {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
}

.sneaker-status {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    z-index: 1;
}

.sneaker-status.pending {
    background-color: var(--warning-color);
    color: white;
}

.sneaker-status.approved {
    background-color: var(--success-color);
    color: white;
}

.sneaker-status.rejected {
    background-color: var(--error-color);
    color: white;
}

.sneaker-status.sold {
    background-color: var(--info-color);
    color: white;
}

.sneaker-image {
    height: 200px;
    overflow: hidden;
}

.sneaker-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.sneaker-card:hover .sneaker-image img {
    transform: scale(1.05);
}

.sneaker-details {
    padding: 15px;
    flex: 1;
}

.sneaker-details h3 {
    margin-bottom: 10px;
    font-size: 18px;
}

.sneaker-details p {
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.sneaker-price {
    font-size: 20px;
    font-weight: bold;
    color: var(--accent-color);
    margin: 10px 0;
}

.sneaker-date {
    font-size: 12px;
    margin-top: 10px;
}

.sneaker-actions {
    display: flex;
    gap: 10px;
    padding: 15px;
    background-color: var(--bg-light);
}

.no-sneakers {
    text-align: center;
    padding: 50px 0;
}

.no-data-icon {
    font-size: 60px;
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.no-sneakers h2 {
    margin-bottom: 10px;
}

.no-sneakers p {
    color: var(--text-secondary);
    margin-bottom: 20px;
}

@media (max-width: 992px) {
    .filter-section {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .search-input {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .sneakers-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>

