<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$pageTitle = 'Register';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$error = '';
$success = '';
$username = '';
$email = '';
$fullName = '';

// Default user type
$userType = isset($_GET['type']) && $_GET['type'] === 'seller' ? 'seller_buyer' : 'buyer';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $fullName = sanitizeInput($_POST['full_name']);
    $userType = sanitizeInput($_POST['user_type']);
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($fullName)) {
        $error = 'All fields are required';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email address';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
        // yha pe change kiya
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        $result = Auth::register($username, $email, $password, $fullName, $userType);
        
        if ($result['success']) {
            $success = 'Registration successful! You can now login.';
            $username = '';
            $email = '';
            $fullName = '';
        } else {
            $error = $result['message'];
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 class="form-title">Create an Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo $fullName; ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Account Type</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="user_type" value="buyer" <?php echo $userType === 'buyer' ? 'checked' : ''; ?>>
                        Buyer (I want to buy sneakers)
                    </label>
                    <label>
                        <input type="radio" name="user_type" value="seller_buyer" <?php echo $userType === 'seller_buyer' ? 'checked' : ''; ?>>
                        Seller/Buyer (I want to buy and sell sneakers)
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Register</button>
            </div>
        </form>
        
        <div class="form-footer">
            <p>Already have an account? <a href="<?php echo SITE_URL; ?>/login.php">Login</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
