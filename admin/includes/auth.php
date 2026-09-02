<?php
// admin/includes/auth.php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Optional: Check if admin still exists in database
require_once 'config.php';
$stmt = mysqli_prepare($conn, "SELECT id FROM ussd_admins WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['admin_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$admin) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>