<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$pageTitle = 'Login';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$error = '';
$email = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $result = Auth::login($email, $password);
        
        if ($result['success']) {
            // Redirect based on user type
            if (Auth::isSuperAdmin()) {
                header('Location: ' . SITE_URL . '/admin/dashboard.php');
            } elseif (Auth::isSeller()) {
                header('Location: ' . SITE_URL . '/seller/dashboard.php');
            } else {
                header('Location: ' . SITE_URL);
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 class="form-title">Login to Your Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Login</button>
                <a href="<?php echo SITE_URL; ?>/forgot-password.php">Forgot Password?</a>
            </div>
        </form>
        
        <div class="form-footer">
            <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/register.php">Register</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

