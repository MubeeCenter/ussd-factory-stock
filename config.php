<?php
// config.php - Load environment variables (MySQLi)
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'mubeetec_portfolio_db';
$username = getenv('DB_USER') ?: 'mubeetec_mubee';
$password = getenv('DB_PASS') ?: 'Mubee2019@';

$conn = mysqli_connect($host, $username, $password, $db_name);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>