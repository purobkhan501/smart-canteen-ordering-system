<?php
require_once '../includes/auth.php';
require_role('staff');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $allowed_statuses = ['Pending', 'Preparing', 'Ready', 'Picked Up', 'Cancelled'];
    if (in_array($status, $allowed_statuses)) {
        // Update status
        $sql = "UPDATE orders SET status = '$status' WHERE order_id = $order_id";
        mysqli_query($conn, $sql);

        // Set ready_time when status becomes Ready
        if ($status == 'Ready') {
            mysqli_query($conn, "UPDATE orders SET ready_time = NOW() WHERE order_id = $order_id");
        }
        // Set pickup_time when picked up
        if ($status == 'Picked Up') {
            mysqli_query($conn, "UPDATE orders SET pickup_time = NOW(), payment_status = 'Paid'  WHERE order_id = $order_id");
        }
    }
}

header("Location: dashboard.php");
exit();
?>
