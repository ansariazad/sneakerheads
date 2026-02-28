<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require login
Auth::requireLogin();

$currentUser = Auth::getCurrentUser();
$userId = $currentUser['user_id'];

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if order ID is provided
if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$orderId = (int)$_POST['order_id'];

// Verify order belongs to user
$orderQuery = "SELECT * FROM orders WHERE order_id = '$orderId' AND user_id = '$userId'";
$orderResult = $db->query($orderQuery);

if ($orderResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$order = $orderResult->fetch_assoc();

// Update payment status
$updateQuery = "UPDATE orders SET payment_status = 'completed' WHERE order_id = '$orderId'";

if ($db->query($updateQuery)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update payment status']);
}
?>