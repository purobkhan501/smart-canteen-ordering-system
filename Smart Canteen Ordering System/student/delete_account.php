<?php
session_start();
require_once '../includes/auth.php';
require_login(); // ensures user is logged in
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Soft delete: set is_deleted = 1
$sql = "UPDATE users SET is_deleted = 1 WHERE user_id = $user_id";
if (mysqli_query($conn, $sql)) {
    // Destroy session
    session_unset();
    session_destroy();
    // Redirect to home page with success message
    header("Location: ../index.php?account_deleted=1");
    exit();
} else {
    // Error occurred
    header("Location: dashboard.php?error=delete_failed");
    exit();
}
?>