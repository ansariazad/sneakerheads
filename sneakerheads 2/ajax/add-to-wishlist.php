<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!Auth::isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'login_required', 'message' => 'You must be logged in to add items to your wishlist.']);
    exit;
}

// Check if sneaker_id is provided
if (!isset($_POST['sneaker_id']) || !is_numeric($_POST['sneaker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid sneaker ID.']);
    exit;
}

$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];
$sneakerId = (int)$_POST['sneaker_id'];

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

try {
    // Check if sneaker exists and is approved
    $checkSneakerQuery = "SELECT sneaker_id FROM sneakers WHERE sneaker_id = '$sneakerId' AND status = 'approved'";
    $checkSneakerResult = $db->query($checkSneakerQuery);
    
    if ($checkSneakerResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Sneaker not found or not available.']);
        exit;
    }
    
    // Check if already in wishlist
    $checkWishlistQuery = "SELECT wishlist_id FROM wishlist WHERE user_id = '$userId' AND sneaker_id = '$sneakerId'";
    $checkWishlistResult = $db->query($checkWishlistQuery);
    
    if ($checkWishlistResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This item is already in your wishlist.']);
        exit;
    }
    
    // Add to wishlist
    $addToWishlistQuery = "INSERT INTO wishlist (user_id, sneaker_id) VALUES ('$userId', '$sneakerId')";
    $addResult = $db->query($addToWishlistQuery);
    
    if ($addResult) {
        // Get wishlist count
        $countQuery = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = '$userId'";
        $countResult = $db->query($countQuery);
        $wishlistCount = $countResult->fetch_assoc()['count'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Added to wishlist successfully.',
            'wishlistCount' => $wishlistCount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist.']);
    }
    
} catch (Exception $e) {
    error_log("Error adding to wishlist: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}

