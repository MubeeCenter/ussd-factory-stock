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
$at_api_key = getenv('AT_API_KEY') ?: '************************';
$at_sender_id = getenv('AT_SENDER_ID') ?: '*************';
?>
