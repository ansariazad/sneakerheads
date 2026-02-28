<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require seller access
Auth::requireSeller();

$pageTitle = 'Edit Sneaker';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if sneaker ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/seller/sneakers.php');
    exit;
}

$sneakerId = (int)$_GET['id'];

// Check if sneaker belongs to seller and is not sold
$sneakerQuery = "SELECT * FROM sneakers WHERE sneaker_id = '$sneakerId' AND seller_id = '$userId' AND status != 'sold'";
$sneakerResult = $db->query($sneakerQuery);

if ($sneakerResult->num_rows === 0) {
    header('Location: ' . SITE_URL . '/seller/sneakers.php');
    exit;
}

$sneaker = $sneakerResult->fetch_assoc();

// Get sneaker images
$imagesQuery = "SELECT * FROM sneaker_images WHERE sneaker_id = '$sneakerId'";
$imagesResult = $db->query($imagesQuery);

$images = [];
while ($row = $imagesResult->fetch_assoc()) {
    $images[$row['image_type']] = $row;
}

// Get sneaker video
$videoQuery = "SELECT * FROM sneaker_videos WHERE sneaker_id = '$sneakerId'";
$videoResult = $db->query($videoQuery);
$video = $videoResult->num_rows > 0 ? $videoResult->fetch_assoc() : null;

// Get purchase bill
$billQuery = "SELECT * FROM purchase_bills WHERE sneaker_id = '$sneakerId'";
$billResult = $db->query($billQuery);
$bill = $billResult->num_rows > 0 ? $billResult->fetch_assoc() : null;

$successMessage = '';
$errorMessage = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $brand = sanitizeInput($_POST['brand']);
    $model = sanitizeInput($_POST['model']);
    $size = (float)$_POST['size'];
    $serialNumber = sanitizeInput($_POST['serial_number']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    
    if (empty($brand) || empty($model) || empty($serialNumber) || $price <= 0) {
        $errorMessage = 'Please fill in all required fields';
    } else {
        // Check if serial number already exists (excluding current sneaker)
        $checkQuery = "SELECT sneaker_id FROM sneakers WHERE serial_number = '$serialNumber' AND sneaker_id != '$sneakerId'";
        $checkResult = $db->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $errorMessage = 'A sneaker with this serial number already exists';
        } else {
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Update sneaker
                $updateQuery = "UPDATE sneakers SET 
                              brand = '$brand', 
                              model = '$model', 
                              size = '$size', 
                              serial_number = '$serialNumber', 
                              description = '$description', 
                              price = '$price',
                              status = 'pending'
                              WHERE sneaker_id = '$sneakerId'";
                
                if (!$db->query($updateQuery)) {
                    throw new Exception('Failed to update sneaker');
                }
                
                // Process images
                $imageTypes = ['top', 'bottom', 'side', 'front'];
                
                foreach ($imageTypes as $type) {
                    if (isset($_FILES["image_$type"]) && $_FILES["image_$type"]['error'] === UPLOAD_ERR_OK) {
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        $uploadResult = uploadFile($_FILES["image_$type"], SNEAKER_UPLOAD_PATH, $allowedTypes);
                        
                        if ($uploadResult['success']) {
                            $imagePath = $uploadResult['filename'];
                            
                            // Check if image exists for this type
                            if (isset($images[$type])) {
                                // Update existing image
                                $updateImageQuery = "UPDATE sneaker_images SET 
                                                  image_path = '$imagePath' 
                                                  WHERE image_id = '{$images[$type]['image_id']}'";
                                
                                if (!$db->query($updateImageQuery)) {
                                    throw new Exception('Failed to update image');
                                }
                            } else {
                                // Insert new image
                                $insertImageQuery = "INSERT INTO sneaker_images (
                                                  sneaker_id, image_path, image_type
                                                  ) VALUES (
                                                  '$sneakerId', '$imagePath', '$type'
                                                  )";
                                
                                if (!$db->query($insertImageQuery)) {
                                    throw new Exception('Failed to save image');
                                }
                            }
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    }
                }
                
                // Process video
                if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
                    $uploadResult = uploadFile($_FILES['video'], SNEAKER_UPLOAD_PATH, $allowedTypes, 20971520); // 20MB max
                    
                    if ($uploadResult['success']) {
                        $videoPath = $uploadResult['filename'];
                        
                        if ($video) {
                            // Update existing video
                            $updateVideoQuery = "UPDATE sneaker_videos SET 
                                              video_path = '$videoPath' 
                                              WHERE video_id = '{$video['video_id']}'";
                            
                            if (!$db->query($updateVideoQuery)) {
                                throw new Exception('Failed to update video');
                            }
                        } else {
                            // Insert new video
                            $insertVideoQuery = "INSERT INTO sneaker_videos (
                                              sneaker_id, video_path
                                              ) VALUES (
                                              '$sneakerId', '$videoPath'
                                              )";
                            
                            if (!$db->query($insertVideoQuery)) {
                                throw new Exception('Failed to save video');
                            }
                        }
                    } else {
                        throw new Exception($uploadResult['message']);
                    }
                }
                
                // Process purchase bill
                if (isset($_FILES['purchase_bill']) && $_FILES['purchase_bill']['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    $uploadResult = uploadFile($_FILES['purchase_bill'], BILL_UPLOAD_PATH, $allowedTypes);
                    
                    if ($uploadResult['success']) {
                        $billPath = $uploadResult['filename'];
                        
                        if ($bill) {
                            // Update existing bill
                            $updateBillQuery = "UPDATE purchase_bills SET 
                                             bill_path = '$billPath' 
                                             WHERE bill_id = '{$bill['bill_id']}'";
                            
                            if (!$db->query($updateBillQuery)) {
                                throw new Exception('Failed to update purchase bill');
                            }
                        } else {
                            // Insert new bill
                            $insertBillQuery = "INSERT INTO purchase_bills (
                                             sneaker_id, bill_path
                                             ) VALUES (
                                             '$sneakerId', '$billPath'
                                             )";
                            
                            if (!$db->query($insertBillQuery)) {
                                throw new Exception('Failed to save purchase bill');
                            }
                        }
                    } else {
                        throw new Exception($uploadResult['message']);
                    }
                }
                
                // Create notification for admin
                $adminQuery = "SELECT user_id FROM users WHERE user_type = 'superadmin' LIMIT 1";
                $adminResult = $db->query($adminQuery);
                
                if ($adminResult->num_rows > 0) {
                    $adminId = $adminResult->fetch_assoc()['user_id'];
                    $notificationMessage = "Updated sneaker listing: $brand $model requires approval";
                    createNotification($adminId, $notificationMessage);
                }
                
                // Commit transaction
                $db->commit();
                
                $successMessage = 'Sneaker updated successfully! It will be listed once approved by admin.';
                
                // Refresh sneaker data
                $sneakerResult = $db->query($sneakerQuery);
                $sneaker = $sneakerResult->fetch_assoc();
                
                // Refresh images
                $imagesResult = $db->query($imagesQuery);
                $images = [];
                while ($row = $imagesResult->fetch_assoc()) {
                    $images[$row['image_type']] = $row;
                }
                
                // Refresh video
                $videoResult = $db->query($videoQuery);
                $video = $videoResult->num_rows > 0 ? $videoResult->fetch_assoc() : null;
                
                // Refresh bill
                $billResult = $db->query($billQuery);
                $bill = $billResult->num_rows > 0 ? $billResult->fetch_assoc() : null;
            } catch (Exception $e) {
                // Rollback transaction
                $db->rollback();
                $errorMessage = $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3>Seller Dashboard</h3>
            <ul>
                <li><a href="<?php echo SITE_URL; ?>/seller/dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/sneakers.php">My Listings</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/add-sneaker.php">Add Sneaker</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/sales.php">My Sales</a></li>
                <li><a href="<?php echo SITE_URL; ?>/seller/payments.php">Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Edit Sneaker</h1>
                <div class="sneaker-status <?php echo $sneaker['status']; ?>">
                    <?php echo ucfirst($sneaker['status']); ?>
                </div>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="edit-sneaker-form">
                <form method="POST" action="" enctype="multipart/form-data" data-validate>
                    <div class="form-section">
                        <h2>Sneaker Details</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="brand">Brand *</label>
                                <input type="text" id="brand" name="brand" value="<?php echo $sneaker['brand']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="model">Model *</label>
                                <input type="text" id="model" name="model" value="<?php echo $sneaker['model']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="size">UK Size *</label>
                                <input type="number" id="size" name="size" step="0.5" min="3" max="15" value="<?php echo $sneaker['size']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="serial_number">Serial Number *</label>
                                <input type="text" id="serial_number" name="serial_number" value="<?php echo $sneaker['serial_number']; ?>" required>
                                <small>Unique identifier found on the sneaker</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4"><?php echo $sneaker['description']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (₹) *</label>
                            <input type="number" id="price" name="price" min="1" step="0.01" value="<?php echo $sneaker['price']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Sneaker Images</h2>
                        <p class="section-info">Please upload clear images of the sneaker from all angles. Leave empty to keep current images.</p>
                        
                        <div class="image-upload-grid">
                            <?php foreach (['top', 'bottom', 'side', 'front'] as $type): ?>
                                <div class="image-upload-item">
                                    <label for="image_<?php echo $type; ?>"><?php echo ucfirst($type); ?> View <?php echo isset($images[$type]) ? '' : '*'; ?></label>
                                    <div class="image-preview" id="preview_<?php echo $type; ?>">
                                        <?php if (isset($images[$type])): ?>
                                            <img src="<?php echo SITE_URL; ?>/assets/uploads/sneakers/<?php echo $images[$type]['image_path']; ?>" alt="<?php echo $type; ?> view">
                                        <?php else: ?>
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>No image selected</span>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" id="image_<?php echo $type; ?>" name="image_<?php echo $type; ?>" accept="image/*" <?php echo isset($images[$type]) ? '' : 'required'; ?>>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Video & Purchase Bill</h2>
                        <p class="section-info">Please upload a short video of the sneaker and the purchase bill for authenticity verification. Leave empty to keep current files.</p>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="video">Video <?php echo $video ? '' : '*'; ?></label>
                                <?php if ($video): ?>
                                    <div class="file-preview">
                                        <i class="fas fa-file-video"></i>
                                        <span>Current video: <?php echo $video['video_path']; ?></span>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="video" name="video" accept="video/*" <?php echo $video ? '' : 'required'; ?>>
                                <small>Max size: 20MB. Formats: MP4, MOV, AVI</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="purchase_bill">Purchase Bill <?php echo $bill ? '' : '*'; ?></label>
                                <?php if ($bill): ?>
                                    <div class="file-preview">
                                        <i class="fas fa-file-invoice"></i>
                                        <span>Current bill: <?php echo $bill['bill_path']; ?></span>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="purchase_bill" name="purchase_bill" accept="image/*, application/pdf" <?php echo $bill ? '' : 'required'; ?>>
                                <small>Formats: JPG, PNG, PDF</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">Update Sneaker</button>
                        <a href="<?php echo SITE_URL; ?>/seller/sneakers.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sneaker-status {
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

.edit-sneaker-form {
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 20px;
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.form-section h2 {
    margin-bottom: 15px;
}

.section-info {
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.image-upload-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.image-upload-item {
    margin-bottom: 15px;
}

.image-preview {
    width: 100%;
    height: 150px;
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin-bottom: 10px;
    cursor: pointer;
    overflow: hidden;
    background-color: var(--bg-light);
}

.image-preview i {
    font-size: 30px;
    margin-bottom: 10px;
    color: var(--text-secondary);
}

.image-preview span {
    color: var(--text-secondary);
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-upload-item input[type="file"] {
    display: none;
}

.file-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background-color: var(--bg-light);
    border-radius: 4px;
    margin-bottom: 10px;
}

.file-preview i {
    font-size: 20px;
    color: var(--primary-color);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
}

@media (max-width: 768px) {
    .image-upload-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const imageTypes = ['top', 'bottom', 'side', 'front'];
    
    imageTypes.forEach(type => {
        const input = document.getElementById(`image_${type}`);
        const preview = document.getElementById(`preview_${type}`);
        
        preview.addEventListener('click', function() {
            input.click();
        });
        
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="${type} view">`;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Form validation
    const form = document.querySelector('form[data-validate]');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (field.type === 'file') {
                if (!field.files || field.files.length === 0) {
                    isValid = false;
                    field.parentElement.classList.add('error');
                } else {
                    field.parentElement.classList.remove('error');
                }
            } else if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields and upload all required files');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>

