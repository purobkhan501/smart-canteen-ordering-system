<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_item_id'], $_POST['rating'])) {
    $order_item_id = intval($_POST['order_item_id']);
    $order_id = intval($_POST['order_id']);
    $rating = intval($_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment'] ?? '');
    $user_id = intval($_SESSION['user_id']);
    
    // Check if already rated
    $check = mysqli_query($conn, "SELECT feedback_id FROM feedback WHERE order_item_id = $order_item_id AND user_id = $user_id");
    if (mysqli_num_rows($check) == 0) {
        $date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO feedback (user_id, order_item_id, rating, comment, created_at) 
                VALUES ($user_id, $order_item_id, $rating, '$comment', '$date')";
        mysqli_query($conn, $sql);
    }
    
    header("Location: rate_order.php?order_id=$order_id");
    exit();
}

header("Location: my_orders.php");
exit();
?>
