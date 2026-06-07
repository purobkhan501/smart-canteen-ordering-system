<?php
require_once '../includes/auth.php';
require_role('staff');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_item_id']) && isset($_POST['order_id'])) {
    $order_item_id = intval($_POST['order_item_id']);
    $order_id = intval($_POST['order_id']);

    // Get item cost
    $sql = "SELECT quantity, unit_price FROM order_item WHERE order_item_id = $order_item_id AND order_id = $order_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $item = mysqli_fetch_assoc($result);
        $cost = $item['quantity'] * $item['unit_price'];

        // Delete item
        mysqli_query($conn, "DELETE FROM order_item WHERE order_item_id = $order_item_id");

        // Deduct from order total
        mysqli_query($conn, "UPDATE orders SET total_amount = total_amount - $cost WHERE order_id = $order_id");

        // Check if any items remain
        $count_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM order_item WHERE order_id = $order_id");
        if ($count_res) {
            $count_row = mysqli_fetch_assoc($count_res);
            if ($count_row['cnt'] == 0) {
                // Cancel the order if no items left
                mysqli_query($conn, "UPDATE orders SET status = 'Cancelled' WHERE order_id = $order_id");
            }
        }
    }
}

header("Location: dashboard.php");
exit();
?>
