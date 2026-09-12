<?php
// admin/includes/config.php - Load from environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: '***************';
$username = getenv('DB_USER') ?: '***************';
$password = getenv('DB_PASS') ?: '***************';

$conn = mysqli_connect($host, $username, $password, $db_name);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Africa's Talking API Credentials
$at_username = getenv('AT_USERNAME') ?: 'sandbox';
$at_api_key = getenv('AT_API_KEY') ?: 'atsk_7e5a604de6579f58e0b1d49d31e4c0d64aeb1bf616856763f4fb32d096e68fa3073efc86';
$at_sender_id = getenv('AT_SENDER_ID') ?: '94409';
?>
