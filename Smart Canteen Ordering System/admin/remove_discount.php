<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['discount_id'])) {
    $discount_id = intval($_POST['discount_id']);
    
    mysqli_query($conn, "DELETE FROM discount WHERE discount_id = $discount_id");
}

header("Location: dashboard.php");
exit();
?>
