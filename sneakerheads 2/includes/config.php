<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sneakerheads');

// Website configuration
define('SITE_NAME', 'Sneakerheads');
define('SITE_URL', 'http://localhost/sneakerheads');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/sneakerheads/assets/uploads/');
define('SNEAKER_UPLOAD_PATH', UPLOAD_PATH . 'sneakers/');
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . 'profiles/');
define('BILL_UPLOAD_PATH', UPLOAD_PATH . 'bills/');

// Session configuration
define('SESSION_NAME', 'sneakerheads_session');
define('SESSION_LIFETIME', 86400); // 24 hours

// Platform fee percentage
define('PLATFORM_FEE_PERCENTAGE', 10);

// COD convenience fee
define('COD_FEE', 30);

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
if (!file_exists(SNEAKER_UPLOAD_PATH)) {
    mkdir(SNEAKER_UPLOAD_PATH, 0777, true);
}
if (!file_exists(PROFILE_UPLOAD_PATH)) {
    mkdir(PROFILE_UPLOAD_PATH, 0777, true);
}
if (!file_exists(BILL_UPLOAD_PATH)) {
    mkdir(BILL_UPLOAD_PATH, 0777, true);
}
?>