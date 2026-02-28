<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$pageTitle = 'Reset Password';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$successMessage = '';
$errorMessage = '';
$token = '';
$validToken = false;
$userId = 0;

// Check if token is provided
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Validate token
    $token = $db->escapeString($token);
    $currentTime = date('Y-m-d H:i:s');
    $query = "SELECT pr.user_id, u.username, u.email 
              FROM password_resets pr 
              JOIN users u ON pr.user_id = u.user_id 
              WHERE pr.token = '$token' AND pr.expiry > '$currentTime'";
    $result = $db->query($query);
    
    if ($result->num_rows === 0) {
        $errorMessage = 'Invalid or expired reset link. Please request a new one.';
    } else {
        $validToken = true;
        $user = $result->fetch_assoc();
        $userId = $user['user_id'];
    }
} else {
    $errorMessage = 'Reset token is missing. Please request a password reset from the forgot password page.';
}

// Process reset password form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirmPassword)) {
        $errorMessage = 'Please enter both password fields';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters long';
    } else {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Hash new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user password
        $updateQuery = "UPDATE users SET password = '$hashedPassword' WHERE user_id = '$userId'";
        
        if ($db->query($updateQuery)) {
            // Delete used token
            $deleteQuery = "DELETE FROM password_resets WHERE user_id = '$userId'";
            $db->query($deleteQuery);
            
            $successMessage = 'Your password has been reset successfully. You can now login with your new password.';
        } else {
            $errorMessage = 'Failed to reset password. Please try again later.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 class="form-title">Reset Password</h2>
        
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
            
            <?php if (!$validToken): ?>
                <div class="form-actions">
                    <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="btn">Request New Reset Link</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <div class="form-actions">
                <a href="<?php echo SITE_URL; ?>/login.php" class="btn">Go to Login</a>
            </div>
        <?php elseif ($validToken): ?>
            <form method="POST" action="" data-validate>
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Must be at least 6 characters long</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Reset Password</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
