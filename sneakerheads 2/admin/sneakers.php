<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require superadmin access
Auth::requireSuperAdmin();

$pageTitle = 'Approve Listings';
$currentUser = Auth::getCurrentUser();

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle search and filters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status = isset($_GET['status']) && in_array($_GET['status'], ['all', 'pending', 'approved', 'rejected', 'sold']) 
            ? $_GET['status'] : 'pending';

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Build query
$query = "SELECT s.*, u.username as seller_username, 
        (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image 
        FROM sneakers s 
        JOIN users u ON s.seller_id = u.user_id 
        WHERE 1=1";

if (!empty($search)) {
    $search = $db->escapeString($search);
    $query .= " AND (s.brand LIKE '%$search%' OR s.model LIKE '%$search%' OR s.serial_number LIKE '%$search%' OR u.username LIKE '%$search%')";
}

if ($status !== 'all') {
    $query .= " AND s.status = '$status'";
}

// Count total items for pagination
$countQuery = str_replace("SELECT s.*, u.username as seller_username, 
        (SELECT image_path FROM sneaker_images WHERE sneaker_id = s.sneaker_id LIMIT 1) as image", "SELECT COUNT(*) as total", $query);
$countResult = $db->query($countQuery);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Add pagination to query
$query .= " ORDER BY s.created_at DESC LIMIT $offset, $itemsPerPage";
$result = $db->query($query);

$sneakers = [];
while ($row = $result->fetch_assoc()) {
    $sneakers[] = $row;
}

// Handle sneaker actions
$successMessage = '';
$errorMessage = '';

// Approve sneaker
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $sneakerId = (int)$_GET['approve'];
    
    // Check if sneaker exists and is pending
    $checkQuery = "SELECT sneaker_id, seller_id, brand, model FROM sneakers WHERE sneaker_id = '$sneakerId' AND status = 'pending'";
    $checkResult = $db->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $sneakerInfo = $checkResult->fetch_assoc();
        $sellerId = $sneakerInfo['seller_id'];
        $brand = $sneakerInfo['brand'];
        $model = $sneakerInfo['model'];
        
        $updateQuery = "UPDATE sneakers SET status = 'approved' WHERE sneaker_id = '$sneakerId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your sneaker listing for $brand $model has been approved!";
            createNotification($sellerId, $notificationMessage);
            
            $successMessage = "Sneaker has been approved successfully";
            
            // Refresh sneaker list
            $result = $db->query($query);
            $sneakers = [];
            while ($row = $result->fetch_assoc()) {
                $sneakers[] = $row;
            }
        } else {
            $errorMessage = 'Failed to approve sneaker';
        }
    } else {
        $errorMessage = 'Sneaker not found or not pending approval';
    }
}

// Reject sneaker
if (isset($_POST['reject']) && isset($_POST['sneaker_id']) && isset($_POST['rejection_reason'])) {
    $sneakerId = (int)$_POST['sneaker_id'];
    $rejectionReason = sanitizeInput($_POST['rejection_reason']);
    
    if (empty($rejectionReason)) {
        $errorMessage = 'Please provide a reason for rejection';
    } else {
        // Check if sneaker exists and is pending
        $checkQuery = "SELECT sneaker_id, seller_id, brand, model FROM sneakers WHERE sneaker_id = '$sneakerId' AND status = 'pending'";
        $checkResult = $db->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $sneakerInfo = $checkResult->fetch_assoc();
            $sellerId = $sneakerInfo['seller_id'];
            $brand = $sneakerInfo['brand'];
            $model = $sneakerInfo['model'];
            
            $updateQuery = "UPDATE sneakers SET status = 'rejected' WHERE sneaker_id = '$sneakerId'";
            
            if ($db->query($updateQuery)) {
                // Create notification for seller
                $notificationMessage = "Your sneaker listing for $brand $model has been rejected. Reason: $rejectionReason";
                createNotification($sellerId, $notificationMessage);
                
                $successMessage = "Sneaker has been rejected successfully";
                
                // Refresh sneaker list
                $result = $db->query($query);
                $sneakers = [];
                while ($row = $result->fetch_assoc()) {
                    $sneakers[] = $row;
                }
            } else {
                $errorMessage = 'Failed to reject sneaker';
            }
        } else {
            $errorMessage = 'Sneaker not found or not pending approval';
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3>Admin Dashboard</h3>
            <ul>
                <li><a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/users.php">Manage Users</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/sneakers.php" class="active">Approve Listings</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/orders.php">Manage Orders</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/payments.php">Seller Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Approve Sneaker Listings</h1>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="filter-section">
                <form action="" method="GET" class="filter-form">
                    <div class="search-input">
                        <input type="text" name="search" placeholder="Search by brand, model, serial number or seller" value="<?php echo $search; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="sold" <?php echo $status === 'sold' ? 'selected' : ''; ?>>Sold</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Filter</button>
                    <a href="<?php echo SITE_URL; ?>/admin/sneakers.php" class="btn btn-secondary">Reset</a>
                </form>
            </div>
            
            <div class="sneakers-list">
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
                                    <p>Seller: <?php echo $sneaker['seller_username']; ?></p>
                                    <div class="sneaker-price"><?php echo formatPrice($sneaker['price']); ?></div>
                                    <p class="sneaker-date">Listed on <?php echo date('M j, Y', strtotime($sneaker['created_at'])); ?></p>
                                </div>
                                
                                <div class="sneaker-actions">
                                    <a href="<?php echo SITE_URL; ?>/admin/sneaker-details.php?id=<?php echo $sneaker['sneaker_id']; ?>" class="btn">View Details</a>
                                    
                                    <?php if ($sneaker['status'] === 'pending'): ?>
                                        <a href="<?php echo SITE_URL; ?>/admin/sneakers.php?approve=<?php echo $sneaker['sneaker_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this sneaker?')">Approve</a>
                                        
                                        <button type="button" class="btn btn-danger reject-btn" data-id="<?php echo $sneaker['sneaker_id']; ?>">Reject</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-data">
                        <p>No sneakers found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejection-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Reject Sneaker</h2>
        <form method="POST" action="">
            <input type="hidden" id="sneaker_id" name="sneaker_id" value="">
            
            <div class="form-group">
                <label for="rejection_reason">Reason for Rejection:</label>
                <textarea id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                <small>This reason will be sent to the seller.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="reject" class="btn btn-danger">Reject Sneaker</button>
                <button type="button" class="btn btn-secondary cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.filter-section {
    background-color: var(--bg-light);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.search-input {
    flex: 1;
    min-width: 250px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 150px;
}

.filter-group label {
    margin-bottom: 5px;
    font-size: 14px;
}

.sneakers-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.sneaker-card {
    background-color: var(--bg-light);
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
    background-color: var(--bg-secondary);
    flex-wrap: wrap;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: var(--bg-secondary);
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 500px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.close {
    color: var(--text-secondary);
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: var(--text-color);
}

@media (max-width: 992px) {
    .sneakers-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-input, .filter-group {
        width: 100%;
    }
    
    .sneaker-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rejection modal functionality
    const modal = document.getElementById('rejection-modal');
    const rejectBtns = document.querySelectorAll('.reject-btn');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.querySelector('.cancel-btn');
    const sneakerIdInput = document.getElementById('sneaker_id');
    
    rejectBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const sneakerId = this.getAttribute('data-id');
            sneakerIdInput.value = sneakerId;
            modal.style.display = 'block';
        });
    });
    
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>