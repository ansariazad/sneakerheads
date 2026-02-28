<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/newsletter.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if it's an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    
    // Get email from POST data
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    
    // Process subscription
    $result = process_newsletter_subscription($email);
    
    // Return JSON response
    echo json_encode($result);
    
} else {
    // Not an AJAX request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>

