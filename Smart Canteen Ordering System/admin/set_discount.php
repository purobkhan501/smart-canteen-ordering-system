<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'], $_POST['percentage'])) {
    $item_id = intval($_POST['item_id']);
    $percentage = floatval($_POST['percentage']);
    $created_by = intval($_SESSION['user_id']);
    
    // Check if discount already exists
    $check_res = mysqli_query($conn, "SELECT discount_id FROM discount WHERE item_id = $item_id");
    if (mysqli_num_rows($check_res) > 0) {
        $row = mysqli_fetch_assoc($check_res);
        $d_id = $row['discount_id'];
        mysqli_query($conn, "UPDATE discount SET percentage = $percentage, is_active = 1 WHERE discount_id = $d_id");
    } else {
        mysqli_query($conn, "INSERT INTO discount (item_id, percentage, is_active, created_by) VALUES ($item_id, $percentage, 1, $created_by)");
    }
}

header("Location: dashboard.php");
exit();
?>
