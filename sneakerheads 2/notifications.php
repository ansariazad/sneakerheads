<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Redirect if not logged in
if (!Auth::isLoggedIn()) {
    header("Location: login.php?redirect=notifications.php");
    exit;
}

$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Mark all as read if requested
if (isset($_GET['mark_all_read'])) {
    $markReadQuery = "UPDATE notifications SET is_read = 1 WHERE user_id = '$userId'";
    $db->query($markReadQuery);
    header("Location: notifications.php?marked=1");
    exit;
}

// Mark single notification as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notificationId = (int)$_GET['mark_read'];
    $markReadQuery = "UPDATE notifications SET is_read = 1 WHERE notification_id = '$notificationId' AND user_id = '$userId'";
    $db->query($markReadQuery);
    
    // Redirect back to referrer or notifications page
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'notifications.php';
    header("Location: $redirect");
    exit;
}

// Delete notification if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notificationId = (int)$_GET['delete'];
    $deleteQuery = "DELETE FROM notifications WHERE notification_id = '$notificationId' AND user_id = '$userId'";
    $db->query($deleteQuery);
    header("Location: notifications.php?deleted=1");
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Get total notifications count
$countQuery = "SELECT COUNT(*) as total FROM notifications WHERE user_id = '$userId'";
$countResult = $db->query($countQuery);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get notifications
$notifications = [];
if ($totalItems > 0) {
    $query = "SELECT * FROM notifications 
              WHERE user_id = '$userId' 
              ORDER BY created_at DESC 
              LIMIT $offset, $itemsPerPage";
    
    $result = $db->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
}

// Set page title
$pageTitle = 'Notifications';

// Include header
include 'includes/header.php';
?>

<div class="container">
    <div class="notifications-container">
        <div class="notifications-header">
            <h1>Notifications</h1>
            
            <?php if (isset($_GET['marked'])): ?>
                <div class="alert alert-success">All notifications marked as read.</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Notification deleted successfully.</div>
            <?php endif; ?>
            
            <?php if (count($notifications) > 0): ?>
                <div class="notifications-actions">
                    <a href="notifications.php?mark_all_read=1" class="btn btn-secondary">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (count($notifications) > 0): ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                        <div class="notification-icon">
                            <?php if (strpos($notification['message'], 'order') !== false): ?>
                                <i class="fas fa-shopping-bag"></i>
                            <?php elseif (strpos($notification['message'], 'wishlist') !== false): ?>
                                <i class="fas fa-heart"></i>
                            <?php elseif (strpos($notification['message'], 'message') !== false): ?>
                                <i class="fas fa-envelope"></i>
                            <?php elseif (strpos($notification['message'], 'review') !== false): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="fas fa-bell"></i>
                            <?php endif; ?>
                        </div>
                        <div class="notification-content">
                            <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                            <div class="notification-time"><?php echo timeAgo($notification['created_at']); ?></div>
                        </div>
                        <div class="notification-actions">
                            <?php if (!$notification['is_read']): ?>
                                <a href="notifications.php?mark_read=<?php echo $notification['notification_id']; ?>" class="mark-read" title="Mark as Read">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>
                            <a href="notifications.php?delete=<?php echo $notification['notification_id']; ?>" class="delete-notification" title="Delete" onclick="return confirm('Are you sure you want to delete this notification?')">
                                <i class="fas fa-times"></i>
                            </a>
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
            <div class="no-notifications">
                <i class="fas fa-bell-slash fa-4x"></i>
                <h2>No notifications</h2>
                <p>You don't have any notifications at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

