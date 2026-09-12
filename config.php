<?php
// config.php - Load environment variables (MySQLi)
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: '***************';
$username = getenv('DB_USER') ?: '***************';
$password = getenv('DB_PASS') ?: '***************';

$conn = mysqli_connect($host, $username, $password, $db_name);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
