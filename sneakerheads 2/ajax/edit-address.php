<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!Auth::isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'login_required', 'message' => 'You must be logged in to edit an address']);
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

// Get form data
$addressId = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;
$street = isset($_POST['street']) ? trim($_POST['street']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$state = isset($_POST['state']) ? trim($_POST['state']) : '';
$postalCode = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$isDefault = isset($_POST['is_default']) ? (int)$_POST['is_default'] : 0;

// Validate form data
$errors = [];

if ($addressId <= 0) {
    $errors[] = 'Invalid address ID';
}

if (empty($street)) {
    $errors[] = 'Street address is required';
}

if (empty($city)) {
    $errors[] = 'City is required';
}

if (empty($state)) {
    $errors[] = 'State/Province is required';
}

if (empty($postalCode)) {
    $errors[] = 'Postal code is required';
}

if (empty($country)) {
    $errors[] = 'Country is required';
}

// Return errors if any
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
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
    // Start transaction
    $pdo->beginTransaction();
    
    // If this is the default address, unset any existing default
    if ($isDefault) {
        $unsetDefaultSql = "UPDATE addresses SET is_default = 0 WHERE user_id = ?";
        $unsetDefaultStmt = $pdo->prepare($unsetDefaultSql);
        $unsetDefaultStmt->execute([$userId]);
    }
    
    // Update address
    $sql = "UPDATE addresses 
            SET street = ?, city = ?, state = ?, postal_code = ?, country = ?, is_default = ? 
            WHERE address_id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$street, $city, $state, $postalCode, $country, $isDefault, $addressId, $userId]);
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Address updated successfully',
        'address' => [
            'address_id' => $addressId,
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'postal_code' => $postalCode,
            'country' => $country,
            'is_default' => $isDefault
        ]
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

