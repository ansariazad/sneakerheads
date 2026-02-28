<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!Auth::isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'login_required', 'message' => 'You must be logged in to delete an address']);
    exit;
}

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get current user
$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

// Get address ID
$addressId = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;

if ($addressId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid address ID']);
    exit;
}

// Check if address belongs to user
$checkSql = "SELECT * FROM addresses WHERE address_id = ? AND user_id = ?";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([$addressId, $userId]);
$address = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$address) {
    echo json_encode(['success' => false, 'message' => 'Address not found or does not belong to you']);
    exit;
}

try {
    // Delete address
    $sql = "DELETE FROM addresses WHERE address_id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$addressId, $userId]);
    
    // If the deleted address was the default, set another address as default
    if ($address['is_default']) {
        $newDefaultSql = "SELECT address_id FROM addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        $newDefaultStmt = $pdo->prepare($newDefaultSql);
        $newDefaultStmt->execute([$userId]);
        $newDefault = $newDefaultStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($newDefault) {
            $updateDefaultSql = "UPDATE addresses SET is_default = 1 WHERE address_id = ?";
            $updateDefaultStmt = $pdo->prepare($updateDefaultSql);
            $updateDefaultStmt->execute([$newDefault['address_id']]);
        }
    }
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Address deleted successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

