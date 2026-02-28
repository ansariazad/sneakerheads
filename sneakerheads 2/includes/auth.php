<?php
require_once 'db.php';
require_once 'functions.php';

session_name(SESSION_NAME);
session_start();

class Auth {
    // Register a new user
    public static function register($username, $email, $password, $fullName, $userType = 'buyer') {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Sanitize inputs
        $username = $db->escapeString(sanitizeInput($username));
        $email = $db->escapeString(sanitizeInput($email));
        $fullName = $db->escapeString(sanitizeInput($fullName));
        $userType = $db->escapeString(sanitizeInput($userType));
        
        // Validate email
        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        // Check if username or email already exists
        if (userExistsByUsername($username)) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        
        if (userExistsByEmail($email)) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into database
        $query = "INSERT INTO users (username, email, password, full_name, user_type) 
                  VALUES ('$username', '$email', '$hashedPassword', '$fullName', '$userType')";
        
        if ($db->query($query)) {
            $userId = $db->getLastId();
            return ['success' => true, 'user_id' => $userId];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    // Login user
    public static function login($email, $password) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Sanitize inputs
        $email = $db->escapeString(sanitizeInput($email));
        
        // Get user by email
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = $db->query($query);
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if user is active
                if (!$user['is_active']) {
                    return ['success' => false, 'message' => 'Your account has been deactivated'];
                }
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['logged_in'] = true;
                
                return ['success' => true, 'user' => $user];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    // Logout user
    public static function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        return true;
    }
    
    // Check if user is logged in
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Get current user
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'user_type' => $_SESSION['user_type']
            ];
        }
        
        return null;
    }
    
    // Check if user is superadmin
    public static function isSuperAdmin() {
        return self::isLoggedIn() && $_SESSION['user_type'] === 'superadmin';
    }
    
    // Check if user is seller
    public static function isSeller() {
        return self::isLoggedIn() && $_SESSION['user_type'] === 'seller_buyer';
    }
    
    // Check if user can sell (superadmin or seller_buyer)
    public static function canSell() {
        return self::isLoggedIn() && ($_SESSION['user_type'] === 'superadmin' || $_SESSION['user_type'] === 'seller_buyer');
    }
    
    // Update user password
    public static function updatePassword($userId, $currentPassword, $newPassword) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $userId = $db->escapeString($userId);
        
        // Get user
        $query = "SELECT password FROM users WHERE user_id = '$userId'";
        $result = $db->query($query);
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify current password
            if (password_verify($currentPassword, $user['password'])) {
                // Hash new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password
                $updateQuery = "UPDATE users SET password = '$hashedPassword' WHERE user_id = '$userId'";
                
                if ($db->query($updateQuery)) {
                    return ['success' => true];
                } else {
                    return ['success' => false, 'message' => 'Failed to update password'];
                }
            } else {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
        }
        
        return ['success' => false, 'message' => 'User not found'];
    }
    
    // Redirect if not logged in
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
    }
    
    // Redirect if not superadmin
    public static function requireSuperAdmin() {
        self::requireLogin();
        
        if (!self::isSuperAdmin()) {
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        }
    }
    
    // Redirect if not seller
    public static function requireSeller() {
        self::requireLogin();
        
        if (!self::canSell()) {
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        }
    }
}
?>