<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require superadmin access
Auth::requireSuperAdmin();

$pageTitle = 'Manage Users';
$currentUser = Auth::getCurrentUser();

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle search and filters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$userType = isset($_GET['type']) && in_array($_GET['type'], ['all', 'buyer', 'seller_buyer', 'superadmin']) 
            ? $_GET['type'] : 'all';
$status = isset($_GET['status']) && in_array($_GET['status'], ['all', 'active', 'inactive']) 
            ? $_GET['status'] : 'all';

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Build query
$query = "SELECT * FROM users WHERE 1=1";

if (!empty($search)) {
    $search = $db->escapeString($search);
    $query .= " AND (username LIKE '%$search%' OR email LIKE '%$search%' OR full_name LIKE '%$search%')";
}

if ($userType !== 'all') {
    $query .= " AND user_type = '$userType'";
}

if ($status !== 'all') {
    $isActive = $status === 'active' ? 1 : 0;
    $query .= " AND is_active = $isActive";
}

// Count total items for pagination
$countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
$countResult = $db->query($countQuery);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Add pagination to query
$query .= " ORDER BY created_at DESC LIMIT $offset, $itemsPerPage";
$result = $db->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Handle user actions
$successMessage = '';
$errorMessage = '';

// Toggle user status (activate/deactivate)
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $userId = (int)$_GET['toggle_status'];
    
    // Don't allow deactivating self
    if ($userId === (int)$currentUser['user_id']) {
        $errorMessage = 'You cannot deactivate your own account';
    } else {
        // Get current status
        $statusQuery = "SELECT is_active FROM users WHERE user_id = '$userId'";
        $statusResult = $db->query($statusQuery);
        
        if ($statusResult->num_rows > 0) {
            $userStatus = $statusResult->fetch_assoc();
            $newStatus = $userStatus['is_active'] ? 0 : 1;
            $statusText = $newStatus ? 'activated' : 'deactivated';
            
            $updateQuery = "UPDATE users SET is_active = '$newStatus' WHERE user_id = '$userId'";
            
            if ($db->query($updateQuery)) {
                $successMessage = "User has been $statusText successfully";
                
                // Refresh user list
                $result = $db->query($query);
                $users = [];
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
            } else {
                $errorMessage = 'Failed to update user status';
            }
        } else {
            $errorMessage = 'User not found';
        }
    }
}

// Change user type
if (isset($_POST['change_type']) && isset($_POST['user_id']) && isset($_POST['user_type'])) {
    $userId = (int)$_POST['user_id'];
    $newType = sanitizeInput($_POST['user_type']);
    
    // Don't allow changing own type
    if ($userId === (int)$currentUser['user_id']) {
        $errorMessage = 'You cannot change your own user type';
    } else if (!in_array($newType, ['buyer', 'seller_buyer', 'superadmin'])) {
        $errorMessage = 'Invalid user type';
    } else {
        $updateQuery = "UPDATE users SET user_type = '$newType' WHERE user_id = '$userId'";
        
        if ($db->query($updateQuery)) {
            $successMessage = 'User type has been updated successfully';
            
            // Refresh user list
            $result = $db->query($query);
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        } else {
            $errorMessage = 'Failed to update user type';
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
                <li><a href="<?php echo SITE_URL; ?>/admin/users.php" class="active">Manage Users</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/sneakers.php">Approve Listings</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/orders.php">Manage Orders</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/payments.php">Seller Payments</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account.php">My Account</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Manage Users</h1>
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
                        <input type="text" name="search" placeholder="Search by username, email or name" value="<?php echo $search; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="type">User Type:</label>
                        <select name="type" id="type">
                            <option value="all" <?php echo $userType === 'all' ? 'selected' : ''; ?>>All Types</option>
                            <option value="buyer" <?php echo $userType === 'buyer' ? 'selected' : ''; ?>>Buyers</option>
                            <option value="seller_buyer" <?php echo $userType === 'seller_buyer' ? 'selected' : ''; ?>>Sellers</option>
                            <option value="superadmin" <?php echo $userType === 'superadmin' ? 'selected' : ''; ?>>Admins</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Filter</button>
                    <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn-secondary">Reset</a>
                </form>
            </div>
            
            <div class="users-list">
                <?php if (count($users) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Full Name</th>
                                    <th>User Type</th>
                                    <th>Status</th>
                                    <th>Registered On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td><?php echo $user['username']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td><?php echo $user['full_name']; ?></td>
                                        <td>
                                            <?php if ($user['user_id'] != $currentUser['user_id']): ?>
                                                <form method="POST" action="" class="inline-form">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <select name="user_type" onchange="this.form.submit()">
                                                        <option value="buyer" <?php echo $user['user_type'] === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                                                        <option value="seller_buyer" <?php echo $user['user_type'] === 'seller_buyer' ? 'selected' : ''; ?>>Seller</option>
                                                        <option value="superadmin" <?php echo $user['user_type'] === 'superadmin' ? 'selected' : ''; ?>>Admin</option>
                                                    </select>
                                                    <input type="hidden" name="change_type" value="1">
                                                </form>
                                            <?php else: ?>
                                                <?php echo ucfirst(str_replace('_', '/', $user['user_type'])); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if ($user['user_id'] != $currentUser['user_id']): ?>
                                                <a href="<?php echo SITE_URL; ?>/admin/users.php?toggle_status=<?php echo $user['user_id']; ?>&type=<?php echo $userType; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>" class="btn-sm <?php echo $user['is_active'] ? 'btn-danger' : 'btn-success'; ?>" onclick="return confirm('Are you sure you want to <?php echo $user['is_active'] ? 'deactivate' : 'activate'; ?> this user?')">
                                                    <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                </a>
                                                <a href="<?php echo SITE_URL; ?>/admin/user-details.php?id=<?php echo $user['user_id']; ?>" class="btn-sm">View Details</a>
                                            <?php else: ?>
                                                <span class="text-muted">Current User</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&type=<?php echo $userType; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&type=<?php echo $userType; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&type=<?php echo $userType; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-data">
                        <p>No users found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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

.inline-form {
    display: inline;
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.status-badge.active {
    background-color: rgba(46, 204, 113, 0.2);
    color: var(--success-color);
}

.status-badge.inactive {
    background-color: rgba(231, 76, 60, 0.2);
    color: var(--error-color);
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    margin-right: 5px;
}

.text-muted {
    color: var(--text-secondary);
    font-style: italic;
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-input, .filter-group {
        width: 100%;
    }
}
</style>

<?php include '../includes/footer.php'; ?>