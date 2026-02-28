<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require superadmin access
Auth::requireSuperAdmin();

$pageTitle = 'Sneaker Details';
$currentUser = Auth::getCurrentUser();

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if sneaker ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/admin/sneakers.php');
    exit;
}

$sneakerId = (int)$_GET['id'];

// Get sneaker details
$sneakerQuery = "SELECT s.*, u.username as seller_username, u.email as seller_email, u.phone as seller_phone
                FROM sneakers s
                JOIN users u ON s.seller_id = u.user_id
                WHERE s.sneaker_id = '$sneakerId'";
$sneakerResult = $db->query($sneakerQuery);

if ($sneakerResult->num_rows === 0) {
    header('Location: ' . SITE_URL . '/admin/sneakers.php');
    exit;
}

$sneaker = $sneakerResult->fetch_assoc();

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

// Get purchase bill
$billQuery = "SELECT * FROM purchase_bills WHERE sneaker_id = '$sneakerId'";
$billResult = $db->query($billQuery);
$bill = $billResult->num_rows > 0 ? $billResult->fetch_assoc() : null;

// Handle sneaker actions
$successMessage = '';
$errorMessage = '';

// Approve sneaker
if (isset($_POST['approve'])) {
    // Check if sneaker is pending
    if ($sneaker['status'] !== 'pending') {
        $errorMessage = 'Sneaker is not pending approval';
    } else {
        $updateQuery = "UPDATE sneakers SET status = 'approved' WHERE sneaker_id = '$sneakerId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your sneaker listing for {$sneaker['brand']} {$sneaker['model']} has been approved!";
            createNotification($sneaker['seller_id'], $notificationMessage);
            
            $successMessage = "Sneaker has been approved successfully";
            
            // Refresh sneaker data
            $sneakerResult = $db->query($sneakerQuery);
            $sneaker = $sneakerResult->fetch_assoc();
        } else {
            $errorMessage = 'Failed to approve sneaker';
        }
    }
}

// Reject sneaker
if (isset($_POST['reject']) && isset($_POST['rejection_reason'])) {
    $rejectionReason = sanitizeInput($_POST['rejection_reason']);
    
    if (empty($rejectionReason)) {
        $errorMessage = 'Please provide a reason for rejection';
    } else if ($sneaker['status'] !== 'pending') {
        $errorMessage = 'Sneaker is not pending approval';
    } else {
        $updateQuery = "UPDATE sneakers SET status = 'rejected' WHERE sneaker_id = '$sneakerId'";
        
        if ($db->query($updateQuery)) {
            // Create notification for seller
            $notificationMessage = "Your sneaker listing for {$sneaker['brand']} {$sneaker['model']} has been rejected. Reason: $rejectionReason";
            createNotification($sneaker['seller_id'], $notificationMessage);
            
            $successMessage = "Sneaker has been rejected successfully";
            
            // Refresh sneaker data
            $sneakerResult = $db->query($sneakerQuery);
            $sneaker = $sneakerResult->fetch_assoc();
        } else {
            $errorMessage = 'Failed to reject sneaker';
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
                <h1>Sneaker Details</h1>
                <a href="<?php echo SITE_URL; ?>/admin/sneakers.php" class="btn btn-secondary">Back to Listings</a>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="sneaker-details-container">
                <div class="sneaker-status-bar">
                    <div class="sneaker-status <?php echo $sneaker['status']; ?>">
                        <?php echo ucfirst($sneaker['status']); ?>
                    </div>
                    
                    <?php if ($sneaker['status'] === 'pending'): ?>
                        <div class="sneaker-actions">
                            <form method="POST" action="" class="inline-form">
                                <button type="submit" name="approve" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this sneaker?')">Approve</button>
                            </form>
                            <button type="button" class="btn btn-danger reject-btn">Reject</button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="sneaker-details-content">
                    <div class="sneaker-images-section">
                        <h2>Sneaker Images</h2>
                        
                        <div class="sneaker-gallery">
                            <?php if (count($images) > 0): ?>
                                <div class="main-image">
                                    <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $images[0]['image_path']; ?>" alt="<?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?>">
                                </div>
                                
                                <div class="thumbnail-images">
                                    <?php foreach ($images as $index => $image): ?>
                                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                            <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $image['image_path']; ?>" alt="<?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?> - <?php echo $image['image_type']; ?>">
                                            <span class="image-type"><?php echo ucfirst($image['image_type']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                  ?>
                                </div>
                                
                                <div class="no-images">
                                    <p>No images available for this sneaker.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($video): ?>
                            <div class="sneaker-video">
                                <h3>Sneaker Video</h3>
                                <video controls>
                                    <source src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $video['video_path']; ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($bill): ?>
                            <div class="purchase-bill">
                                <h3>Purchase Bill</h3>
                                <?php 
                                $fileExtension = pathinfo($bill['bill_path'], PATHINFO_EXTENSION);
                                if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])): 
                                ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/uploads/bills/<?php echo $bill['bill_path']; ?>" alt="Purchase Bill">
                                <?php else: ?>
                                    <a href="<?php echo SITE_URL; ?>/assets/uploads/bills/<?php echo $bill['bill_path']; ?>" target="_blank" class="btn">View Purchase Bill</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="sneaker-info-section">
                        <h2><?php echo $sneaker['brand'] . ' ' . $sneaker['model']; ?></h2>
                        
                        <div class="sneaker-price">
                            <?php echo formatPrice($sneaker['price']); ?>
                        </div>
                        
                        <div class="info-group">
                            <h3>Sneaker Details</h3>
                            <div class="info-row">
                                <span class="info-label">Brand:</span>
                                <span class="info-value"><?php echo $sneaker['brand']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Model:</span>
                                <span class="info-value"><?php echo $sneaker['model']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Size:</span>
                                <span class="info-value"><?php echo $sneaker['size']; ?> UK</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Serial Number:</span>
                                <span class="info-value"><?php echo $sneaker['serial_number']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Listed On:</span>
                                <span class="info-value"><?php echo date('F j, Y', strtotime($sneaker['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <h3>Description</h3>
                            <p><?php echo $sneaker['description'] ? nl2br($sneaker['description']) : 'No description provided.'; ?></p>
                        </div>
                        
                        <div class="info-group">
                            <h3>Seller Information</h3>
                            <div class="info-row">
                                <span class="info-label">Username:</span>
                                <span class="info-value"><?php echo $sneaker['seller_username']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo $sneaker['seller_email']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phone:</span>
                                <span class="info-value"><?php echo $sneaker['seller_phone'] ? $sneaker['seller_phone'] : 'Not provided'; ?></span>
                            </div>
                            <div class="seller-actions">
                                <a href="<?php echo SITE_URL; ?>/admin/user-details.php?id=<?php echo $sneaker['seller_id']; ?>" class="btn">View Seller Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
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
.sneaker-details-container {
    background-color: var(--bg-light);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 30px;
}

.sneaker-status-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background-color: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}

.sneaker-status {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: bold;
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

.sneaker-actions {
    display: flex;
    gap: 10px;
}

.inline-form {
    display: inline;
}

.sneaker-details-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    padding: 20px;
}

.sneaker-gallery {
    margin-bottom: 20px;
}

.main-image {
    height: 300px;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 15px;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-images {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.thumbnail {
    width: 80px;
    height: 80px;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    position: relative;
}

.thumbnail.active {
    border-color: var(--primary-color);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-type {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 10px;
    text-align: center;
    padding: 2px 0;
}

.sneaker-video {
    margin-top: 20px;
}

.sneaker-video video {
    width: 100%;
    border-radius: 8px;
    margin-top: 10px;
}

.purchase-bill {
    margin-top: 20px;
}

.purchase-bill img {
    max-width: 100%;
    border-radius: 8px;
    margin-top: 10px;
}

.sneaker-info-section h2 {
    margin-bottom: 10px;
}

.sneaker-price {
    font-size: 24px;
    font-weight: bold;
    color: var(--accent-color);
    margin-bottom: 20px;
}

.info-group {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.info-group:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-group h3 {
    margin-bottom: 10px;
}

.info-row {
    display: flex;
    margin-bottom: 8px;
}

.info-label {
    width: 120px;
    font-weight: bold;
}

.seller-actions {
    margin-top: 15px;
}

.no-images {
    text-align: center;
    padding: 50px 0;
    background-color: var(--bg-secondary);
    border-radius: 8px;
    color: var(--text-secondary);
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
    .sneaker-details-content {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image gallery functionality
    const mainImage = document.querySelector('.main-image img');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Update main image
            mainImage.src = this.querySelector('img').src;
            
            // Update active thumbnail
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Rejection modal functionality
    const modal = document.getElementById('rejection-modal');
    const rejectBtn = document.querySelector('.reject-btn');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            modal.style.display = 'block';
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>

