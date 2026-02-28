<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$pageTitle = 'Forgot Password';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$successMessage = '';
$errorMessage = '';
$email = '';

// Process forgot password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    if (empty($email)) {
        $errorMessage = 'Please enter your email address';
    } elseif (!validateEmail($email)) {
        $errorMessage = 'Please enter a valid email address';
    } else {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Check if email exists
        $email = $db->escapeString($email);
        $query = "SELECT user_id, username FROM users WHERE email = '$email'";
        $result = $db->query($query);
        
        if ($result->num_rows === 0) {
            $errorMessage = 'No account found with this email address';
        } else {
            $user = $result->fetch_assoc();
            $userId = $user['user_id'];
            $username = $user['username'];
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Delete any existing tokens for this user
            $deleteQuery = "DELETE FROM password_resets WHERE user_id = '$userId'";
            $db->query($deleteQuery);
            
            // Insert new token
            $insertQuery = "INSERT INTO password_resets (user_id, token, expiry) VALUES ('$userId', '$token', '$expiry')";
            
            if ($db->query($insertQuery)) {
                // Send email with reset link
                $resetLink = SITE_URL . '/reset-password.php?token=' . $token;
                
                // In a real application, you would send an actual email here
                // For this example, we'll just show the reset link
                $successMessage = "A password reset link has been sent to your email address. The link will expire in 1 hour.<br><br>
                                  <strong>For demonstration purposes:</strong><br>
                                  Reset Link: <a href='$resetLink'>$resetLink</a>";
                
                $email = '';
            } else {
                $errorMessage = 'Failed to process your request. Please try again later.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 class="form-title">Forgot Password</h2>
        
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php else: ?>
            <p class="form-description">Enter your email address below and we'll send you a link to reset your password.</p>
            
            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Send Reset Link</button>
                    <a href="<?php echo SITE_URL; ?>/login.php">Back to Login</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.form-description {
    margin-bottom: 20px;
    color: var(--text-secondary);
}
</style>

<?php include 'includes/footer.php'; ?>
