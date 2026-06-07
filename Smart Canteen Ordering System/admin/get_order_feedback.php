<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id > 0) {
    $sql = "SELECT f.rating, f.comment, f.created_at, m.name as item_name 
            FROM feedback f 
            JOIN order_item oi ON f.order_item_id = oi.order_item_id 
            JOIN menu_item m ON oi.item_id = m.item_id 
            WHERE oi.order_id = $order_id 
            ORDER BY f.created_at DESC";
    $res = mysqli_query($conn, $sql);
    $feedback = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $feedback[] = $row;
    }
    echo json_encode(['success' => true, 'feedback' => $feedback]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
}
?>