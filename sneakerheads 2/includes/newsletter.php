<?php
// Process newsletter subscription
function process_newsletter_subscription($email) {
    global $db;
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ];
    }
    
    // Check if email already exists in subscribers
    $check_query = "SELECT * FROM newsletter_subscribers WHERE email = ?";
    $stmt = $db->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return [
            'success' => false,
            'message' => 'This email is already subscribed to our newsletter.'
        ];
    }
    
    // Insert new subscriber
    $insert_query = "INSERT INTO newsletter_subscribers (email, status, created_at) VALUES (?, 'active', NOW())";
    $stmt = $db->prepare($insert_query);
    $stmt->bind_param("s", $email);
    
    if ($stmt->execute()) {
        // Send welcome email (in a real application)
        // send_welcome_email($email);
        
        return [
            'success' => true,
            'message' => 'Thank you for subscribing to our newsletter!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ];
    }
}
?>

