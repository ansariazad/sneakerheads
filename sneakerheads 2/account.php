<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
Auth::requireLogin();

$pageTitle = 'My Account';
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Get user details
$query = "SELECT * FROM users WHERE user_id = '$userId'";
$result = $db->query($query);
$user = $result->fetch_assoc();

// Get user addresses
$addressQuery = "SELECT * FROM addresses WHERE user_id = '$userId' ORDER BY is_default DESC";
$addressResult = $db->query($addressQuery);

$addresses = [];
while ($row = $addressResult->fetch_assoc()) {
    $addresses[] = $row;
}

// Get bank details
$bankQuery = "SELECT * FROM bank_details WHERE user_id = '$userId'";
$bankResult = $db->query($bankQuery);
$bankDetails = $bankResult->num_rows > 0 ? $bankResult->fetch_assoc() : null;

$successMessage = '';
$errorMessage = '';

// Process profile update
if (isset($_POST['update_profile'])) {
    $fullName = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    
    // Update user profile
    $updateQuery = "UPDATE users SET full_name = '$fullName', phone = '$phone' WHERE user_id = '$userId'";
    
    if ($db->query($updateQuery)) {
        $successMessage = 'Profile updated successfully';
        
        // Update user details
        $user['full_name'] = $fullName;
        $user['phone'] = $phone;
    } else {
        $errorMessage = 'Failed to update profile';
    }
}

// Process password change
if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = 'All password fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = 'New passwords do not match';
    } elseif (strlen($newPassword) < 6) {
        $errorMessage = 'Password must be at least 6 characters long';
    } else {
        $result = Auth::updatePassword($userId, $currentPassword, $newPassword);
        
        if ($result['success']) {
            $successMessage = 'Password changed successfully';
        } else {
            $errorMessage = $result['message'];
        }
    }
}

// Process add/edit address
if (isset($_POST['save_address'])) {
    $addressId = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;
    $addressLine1 = sanitizeInput($_POST['address_line1']);
    $addressLine2 = sanitizeInput($_POST['address_line2']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $postalCode = sanitizeInput($_POST['postal_code']);
    $country = sanitizeInput($_POST['country']);
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    
    // Validate required fields
    if (empty($addressLine1) || empty($city) || empty($state) || empty($postalCode)) {
        $errorMessage = 'Please fill in all required address fields';
    } else {
        // If setting as default, unset all other addresses as default
        if ($isDefault) {
            $unsetDefaultQuery = "UPDATE addresses SET is_default = 0 WHERE user_id = '$userId'";
            $db->query($unsetDefaultQuery);
        }
        
        if ($addressId > 0) {
            // Update existing address
            $updateAddressQuery = "UPDATE addresses SET 
                                address_line1 = '$addressLine1', 
                                address_line2 = '$addressLine2', 
                                city = '$city', 
                                state = '$state', 
                                postal_code = '$postalCode', 
                                country = '$country', 
                                is_default = '$isDefault' 
                                WHERE address_id = '$addressId' AND user_id = '$userId'";
            
            if ($db->query($updateAddressQuery)) {
                $successMessage = 'Address updated successfully';
            } else {
                $errorMessage = 'Failed to update address';
            }
        } else {
            // Add new address
            $insertAddressQuery = "INSERT INTO addresses (
                                user_id, address_line1, address_line2, city, state, postal_code, country, is_default
                                ) VALUES (
                                '$userId', '$addressLine1', '$addressLine2', '$city', '$state', '$postalCode', '$country', '$isDefault'
                                )";
            
            if ($db->query($insertAddressQuery)) {
                $successMessage = 'Address added successfully';
            } else {
                $errorMessage = 'Failed to add address';
            }
        }
        
        // Refresh addresses
        $addressResult = $db->query($addressQuery);
        $addresses = [];
        while ($row = $addressResult->fetch_assoc()) {
            $addresses[] = $row;
        }
    }
}

// Process delete address
if (isset($_GET['delete_address']) && is_numeric($_GET['delete_address'])) {
    $addressId = (int)$_GET['delete_address'];
    
    $deleteQuery = "DELETE FROM addresses WHERE address_id = '$addressId' AND user_id = '$userId'";
    
    if ($db->query($deleteQuery)) {
        $successMessage = 'Address deleted successfully';
        
        // Refresh addresses
        $addressResult = $db->query($addressQuery);
        $addresses = [];
        while ($row = $addressResult->fetch_assoc()) {
            $addresses[] = $row;
        }
    } else {
        $errorMessage = 'Failed to delete address';
    }
}

// Process bank details update
if (isset($_POST['save_bank_details'])) {
    $accountHolderName = sanitizeInput($_POST['account_holder_name']);
    $accountNumber = sanitizeInput($_POST['account_number']);
    $ifscCode = sanitizeInput($_POST['ifsc_code']);
    $bankName = sanitizeInput($_POST['bank_name']);
    $branchName = sanitizeInput($_POST['branch_name']);
    $upiId = sanitizeInput($_POST['upi_id']);
    
    // Validate required fields
    if ((empty($accountHolderName) || empty($accountNumber) || empty($ifscCode) || empty($bankName)) && empty($upiId)) {
        $errorMessage = 'Please provide either bank account details or UPI ID';
    } else {
        if ($bankDetails) {
            // Update existing bank details
            $updateBankQuery = "UPDATE bank_details SET 
                            account_holder_name = '$accountHolderName', 
                            account_number = '$accountNumber', 
                            ifsc_code = '$ifscCode', 
                            bank_name = '$bankName', 
                            branch_name = '$branchName', 
                            upi_id = '$upiId' 
                            WHERE user_id = '$userId'";
            
            if ($db->query($updateBankQuery)) {
                $successMessage = 'Payment details updated successfully';
                
                // Refresh bank details
                $bankResult = $db->query($bankQuery);
                $bankDetails = $bankResult->fetch_assoc();
            } else {
                $errorMessage = 'Failed to update payment details';
            }
        } else {
            // Add new bank details
            $insertBankQuery = "INSERT INTO bank_details (
                            user_id, account_holder_name, account_number, ifsc_code, bank_name, branch_name, upi_id
                            ) VALUES (
                            '$userId', '$accountHolderName', '$accountNumber', '$ifscCode', '$bankName', '$branchName', '$upiId'
                            )";
            
            if ($db->query($insertBankQuery)) {
                $successMessage = 'Payment details added successfully';
                
                // Refresh bank details
                $bankResult = $db->query($bankQuery);
                $bankDetails = $bankResult->fetch_assoc();
            } else {
                $errorMessage = 'Failed to add payment details';
            }
        }
    }
}

// Process profile image upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $uploadResult = uploadFile($_FILES['profile_image'], PROFILE_UPLOAD_PATH, $allowedTypes);
    
    if ($uploadResult['success']) {
        $profileImage = $uploadResult['filename'];
        
        // Update user profile image
        $updateImageQuery = "UPDATE users SET profile_image = '$profileImage' WHERE user_id = '$userId'";
        
        if ($db->query($updateImageQuery)) {
            $successMessage = 'Profile image updated successfully';
            $user['profile_image'] = $profileImage;
        } else {
            $errorMessage = 'Failed to update profile image';
        }
    } else {
        $errorMessage = $uploadResult['message'];
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3>Account Settings</h3>
            <ul>
                <li><a href="#profile" class="active">Profile Information</a></li>
                <li><a href="#password">Change Password</a></li>
                <li><a href="#addresses">Manage Addresses</a></li>
                <?php if (Auth::isSeller() || Auth::isSuperAdmin()): ?>
                    <li><a href="#payment">Payment Details</a></li>
                <?php endif; ?>
                <li><a href="<?php echo SITE_URL; ?>/orders.php">My Orders</a></li>
                <li><a href="<?php echo SITE_URL; ?>/wishlist.php">My Wishlist</a></li>
                <li><a href="<?php echo SITE_URL; ?>/notifications.php">Notifications</a></li>
                <?php if (Auth::isSeller()): ?>
                    <li><a href="<?php echo SITE_URL; ?>/seller/dashboard.php">Seller Dashboard</a></li>
                <?php endif; ?>
                <?php if (Auth::isSuperAdmin()): ?>
                    <li><a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Admin Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <section id="profile" class="account-section">
                <h2>Profile Information</h2>
                
                <div class="profile-container">
                    <div class="profile-image">
                        <?php if ($user['profile_image'] && $user['profile_image'] !== 'default.jpg'): ?>
                            <img src="<?php echo SITE_URL; ?>/assets/uploads/profiles/<?php echo $user['profile_image']; ?>" alt="Profile Image">
                        <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/default-profile.jpg" alt="Default Profile">
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" class="profile-image-form">
                            <label for="profile_image" class="btn btn-secondary">Change Image</label>
                            <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;">
                            <button type="submit" class="btn">Upload</button>
                        </form>
                    </div>
                    
                    <div class="profile-details">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" value="<?php echo $user['username']; ?>" disabled>
                                <small>Username cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?php echo $user['email']; ?>" disabled>
                                <small>Email cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="user_type">Account Type</label>
                                <input type="text" id="user_type" value="<?php echo ucfirst(str_replace('_', '/', $user['user_type'])); ?>" disabled>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn">Update Profile</button>
                        </form>
                    </div>
                </div>
            </section>
            
            <section id="password" class="account-section">
                <h2>Change Password</h2>
                
                <form method="POST" action="" data-validate>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn">Change Password</button>
                </form>
            </section>
            
            <section id="addresses" class="account-section">
                <h2>Manage Addresses</h2>
                
                <div class="addresses-container">
                    <?php if (count($addresses) > 0): ?>
                        <div class="addresses-list">
                            <?php foreach ($addresses as $address): ?>
                                <div class="address-card">
                                    <?php if ($address['is_default']): ?>
                                        <div class="default-badge">Default</div>
                                    <?php endif; ?>
                                    
                                    <div class="address-details">
                                        <p><?php echo $address['address_line1']; ?></p>
                                        <?php if ($address['address_line2']): ?>
                                            <p><?php echo $address['address_line2']; ?></p>
                                        <?php endif; ?>
                                        <p><?php echo $address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']; ?></p>
                                        <p><?php echo $address['country']; ?></p>
                                    </div>
                                    
                                    <div class="address-actions">
                                        <button class="btn btn-secondary edit-address-btn" data-id="<?php echo $address['address_id']; ?>">Edit</button>
                                        <a href="?delete_address=<?php echo $address['address_id']; ?>#addresses" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this address?')">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-addresses">
                            <p>You don't have any saved addresses yet.</p>
                        </div>
                    <?php endif; ?>
                    
                    <button class="btn add-address-btn">Add New Address</button>
                    
                    <div class="address-form-container" style="display: none;">
                        <h3 id="address-form-title">Add New Address</h3>
                        
                        <form method="POST" action="#addresses" data-validate>
                            <input type="hidden" id="address_id" name="address_id" value="0">
                            
                            <div class="form-group">
                                <label for="address_line1">Address Line 1 *</label>
                                <input type="text" id="address_line1" name="address_line1" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="address_line2">Address Line 2 (Optional)</label>
                                <input type="text" id="address_line2" name="address_line2">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City *</label>
                                    <input type="text" id="city" name="city" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="state">State *</label>
                                    <input type="text" id="state" name="state" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="postal_code">Postal Code *</label>
                                    <input type="text" id="postal_code" name="postal_code" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="country">Country *</label>
                                    <input type="text" id="country" name="country" value="India" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="is_default" name="is_default">
                                    Set as default address
                                </label>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="save_address" class="btn">Save Address</button>
                                <button type="button" class="btn btn-secondary cancel-address-btn">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
            
            <?php if (Auth::isSeller() || Auth::isSuperAdmin()): ?>
                <section id="payment" class="account-section">
                    <h2>Payment Details</h2>
                    <p class="section-info">Add your bank account or UPI details to receive payments for your sold sneakers.</p>
                    
                    <form method="POST" action="#payment" data-validate>
                        <div class="payment-form">
                            <div class="form-section">
                                <h3>Bank Account Details</h3>
                                
                                <div class="form-group">
                                    <label for="account_holder_name">Account Holder Name</label>
                                    <input type="text" id="account_holder_name" name="account_holder_name" value="<?php echo $bankDetails ? $bankDetails['account_holder_name'] : ''; ?>">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="account_number">Account Number</label>
                                        <input type="text" id="account_number" name="account_number" value="<?php echo $bankDetails ? $bankDetails['account_number'] : ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="ifsc_code">IFSC Code</label>
                                        <input type="text" id="ifsc_code" name="ifsc_code" value="<?php echo $bankDetails ? $bankDetails['ifsc_code'] : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="bank_name">Bank Name</label>
                                        <input type="text" id="bank_name" name="bank_name" value="<?php echo $bankDetails ? $bankDetails['bank_name'] : ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="branch_name">Branch Name</label>
                                        <input type="text" id="branch_name" name="branch_name" value="<?php echo $bankDetails ? $bankDetails['branch_name'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>UPI Details</h3>
                                
                                <div class="form-group">
                                    <label for="upi_id">UPI ID</label>
                                    <input type="text" id="upi_id" name="upi_id" value="<?php echo $bankDetails ? $bankDetails['upi_id'] : ''; ?>" placeholder="example@upi">
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="save_bank_details" class="btn">Save Payment Details</button>
                            </div>
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile image upload
    const profileImageInput = document.getElementById('profile_image');
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                this.closest('form').submit();
            }
        });
    }
    
    // Address form handling
    const addAddressBtn = document.querySelector('.add-address-btn');
    const cancelAddressBtn = document.querySelector('.cancel-address-btn');
    const addressFormContainer = document.querySelector('.address-form-container');
    const addressFormTitle = document.getElementById('address-form-title');
    const addressIdInput = document.getElementById('address_id');
    const addressForm = addressFormContainer.querySelector('form');
    
    // Show add address form
    if (addAddressBtn) {
        addAddressBtn.addEventListener('click', function() {
            addressFormTitle.textContent = 'Add New Address';
            addressIdInput.value = '0';
            addressForm.reset();
            
            // Set default country
            document.getElementById('country').value = 'India';
            
            addressFormContainer.style.display = 'block';
            addAddressBtn.style.display = 'none';
            
            // Scroll to form
            addressFormContainer.scrollIntoView({ behavior: 'smooth' });
        });
    }
    
    // Hide address form
    if (cancelAddressBtn) {
        cancelAddressBtn.addEventListener('click', function() {
            addressFormContainer.style.display = 'none';
            addAddressBtn.style.display = 'block';
        });
    }
    
    // Edit address
    const editAddressBtns = document.querySelectorAll('.edit-address-btn');
    editAddressBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const addressId = this.getAttribute('data-id');
            const addressCard = this.closest('.address-card');
            const addressDetails = addressCard.querySelector('.address-details').children;
            
            // Set form title and address ID
            addressFormTitle.textContent = 'Edit Address';
            addressIdInput.value = addressId;
            
            // Fill form with address details
            document.getElementById('address_line1').value = addressDetails[0].textContent;
            
            let addressLine2 = '';
            let cityStateZipIndex = 1;
            
            // Check if address line 2 exists
            if (addressDetails.length > 2 && !addressDetails[1].textContent.includes(',')) {
                addressLine2 = addressDetails[1].textContent;
                cityStateZipIndex = 2;
            }
            
            document.getElementById('address_line2').value = addressLine2;
            
            const cityStateZip = addressDetails[cityStateZipIndex].textContent.split(', ');
            const stateZip = cityStateZip[1].split(' ');
            
            document.getElementById('city').value = cityStateZip[0];
            document.getElementById('state').value = stateZip[0];
            document.getElementById('postal_code').value = stateZip[1];
            document.getElementById('country').value = addressDetails[cityStateZipIndex + 1].textContent;
            
            // Set default checkbox
            document.getElementById('is_default').checked = addressCard.querySelector('.default-badge') !== null;
            
            // Show form
            addressFormContainer.style.display = 'block';
            addAddressBtn.style.display = 'none';
            
            // Scroll to form
            addressFormContainer.scrollIntoView({ behavior: 'smooth' });
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Add error message if it doesn't exist
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                        errorMsg = document.createElement('div');
                        errorMsg.classList.add('error-message');
                        errorMsg.textContent = 'This field is required';
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    }
                } else {
                    field.classList.remove('error');
                    
                    // Remove error message if it exists
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-message')) {
                        errorMsg.remove();
                    }
                }
            });
            
            // Password validation for password change form
            if (form.querySelector('#new_password')) {
                const newPassword = form.querySelector('#new_password');
                const confirmPassword = form.querySelector('#confirm_password');
                
                if (newPassword.value !== confirmPassword.value) {
                    isValid = false;
                    confirmPassword.classList.add('error');
                    
                    // Add error message if it doesn't exist
                    let errorMsg = confirmPassword.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                        errorMsg = document.createElement('div');
                        errorMsg.classList.add('error-message');
                        errorMsg.textContent = 'Passwords do not match';
                        confirmPassword.parentNode.insertBefore(errorMsg, confirmPassword.nextSibling);
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Sidebar navigation
    const sidebarLinks = document.querySelectorAll('.dashboard-sidebar a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                // Remove active class from all links
                sidebarLinks.forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Scroll to section
                const targetId = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                
                if (targetSection) {
                    targetSection.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
    
    // Check if URL has a hash and scroll to that section
    if (window.location.hash) {
        const targetSection = document.querySelector(window.location.hash);
        if (targetSection) {
            setTimeout(() => {
                targetSection.scrollIntoView({ behavior: 'smooth' });
                
                // Activate the corresponding sidebar link
                const sidebarLink = document.querySelector(`.dashboard-sidebar a[href="${window.location.hash}"]`);
                if (sidebarLink) {
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    sidebarLink.classList.add('active');
                }
            }, 100);
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>

