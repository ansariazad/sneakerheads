<?php
require_once 'db.php';

// Generate a random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Generate a unique tracking ID
function generateTrackingId() {
    return 'TRK' . strtoupper(generateRandomString(8));
}

// Generate a unique order ID
function generateOrderId() {
    return 'ORD' . strtoupper(generateRandomString(8));
}

// Format price with currency
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

// Sanitize input
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Check if user exists by email
function userExistsByEmail($email) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $email = $db->escapeString($email);
    $query = "SELECT user_id FROM users WHERE email = '$email'";
    $result = $db->query($query);
    
    return $result->num_rows > 0;
}

// Check if user exists by username
function userExistsByUsername($username) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $username = $db->escapeString($username);
    $query = "SELECT user_id FROM users WHERE username = '$username'";
    $result = $db->query($query);
    
    return $result->num_rows > 0;
}

// Get user by ID
function getUserById($userId) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $userId = $db->escapeString($userId);
    $query = "SELECT * FROM users WHERE user_id = '$userId'";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get user's default address
function getDefaultAddress($userId) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $userId = $db->escapeString($userId);
    $query = "SELECT * FROM addresses WHERE user_id = '$userId' AND is_default = 1";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // If no default address, get the first address
    $query = "SELECT * FROM addresses WHERE user_id = '$userId' LIMIT 1";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Create notification
function createNotification($userId, $message) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $userId = $db->escapeString($userId);
    $message = $db->escapeString($message);
    
    $query = "INSERT INTO notifications (user_id, message) VALUES ('$userId', '$message')";
    return $db->query($query);
}

// Get unread notifications count
function getUnreadNotificationsCount($userId) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $userId = $db->escapeString($userId);
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = '$userId' AND is_read = 0";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    return 0;
}

// Get sneaker by ID
function getSneakerById($sneakerId) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sneakerId = $db->escapeString($sneakerId);
    $query = "SELECT s.*, u.username as seller_username FROM sneakers s 
              JOIN users u ON s.seller_id = u.user_id 
              WHERE s.sneaker_id = '$sneakerId'";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get sneaker images
function getSneakerImages($sneakerId) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sneakerId = $db->escapeString($sneakerId);
    $query = "SELECT * FROM sneaker_images WHERE sneaker_id = '$sneakerId'";
    $result = $db->query($query);
    
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    
    return $images;
}

// Get cart items count
function getCartItemsCount($userId) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $userId = $db->escapeString($userId);
    $query = "SELECT COUNT(*) as count FROM cart WHERE user_id = '$userId'";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    return 0;
}

// Calculate delivery ETA (5 days from current date)
function calculateDeliveryEta() {
    return date('Y-m-d', strtotime('+5 days'));
}

// Check if sneaker is in cart
function isSneakerInCart($userId, $sneakerId) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $userId = $db->escapeString($userId);
    $sneakerId = $db->escapeString($sneakerId);
    
    $query = "SELECT cart_id FROM cart WHERE user_id = '$userId' AND sneaker_id = '$sneakerId'";
    $result = $db->query($query);
    
    return $result->num_rows > 0;
}

// Check if sneaker is in wishlist
function isSneakerInWishlist($userId, $sneakerId) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $userId = $db->escapeString($userId);
    $sneakerId = $db->escapeString($sneakerId);
    
    $query = "SELECT wishlist_id FROM wishlist WHERE user_id = '$userId' AND sneaker_id = '$sneakerId'";
    $result = $db->query($query);
    
    return $result->num_rows > 0;
}

// Get cart total
function getCartTotal($userId) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $userId = $db->escapeString($userId);
    $query = "SELECT SUM(s.price) as total FROM cart c 
              JOIN sneakers s ON c.sneaker_id = s.sneaker_id 
              WHERE c.user_id = '$userId'";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'] ? $row['total'] : 0;
    }
    
    return 0;
}

// Upload file with validation
function uploadFile($file, $destination, $allowedTypes = ['image/jpeg', 'image/png'], $maxSize = 5242880) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error: ' . $file['error']];
    }
    
    // Check file size (5MB max by default)
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File is too large. Maximum size is ' . ($maxSize / 1024 / 1024) . 'MB'];
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)];
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = uniqid() . '.' . $fileExtension;
    $uploadPath = $destination . $newFilename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $newFilename, 'path' => $uploadPath];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Format time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}
?>

