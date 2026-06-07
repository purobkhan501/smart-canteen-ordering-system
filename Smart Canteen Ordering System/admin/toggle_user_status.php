<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['is_deleted'])) {
    $user_id = intval($_POST['user_id']);
    $is_deleted = intval($_POST['is_deleted']);
    
    // Prevent admin from deactivating their own account
    if ($user_id == $_SESSION['user_id']) {
        echo 'cannot_self';
        exit();
    }
    
    $sql = "UPDATE users SET is_deleted = $is_deleted WHERE user_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'invalid';
}
?>