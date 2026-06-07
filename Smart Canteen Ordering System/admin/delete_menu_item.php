<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);
    
    // First check if item exists in any order_item (to avoid orphan references)
    $check = mysqli_query($conn, "SELECT order_item_id FROM order_item WHERE item_id = $item_id LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        // Item has been ordered – soft delete or mark unavailable instead
        mysqli_query($conn, "UPDATE menu_item SET is_available = 0, is_special = 0 WHERE item_id = $item_id");
        header("Location: dashboard.php?warning=has_orders#menu");
    } else {
        // Safe to delete
        mysqli_query($conn, "DELETE FROM menu_item WHERE item_id = $item_id");
        header("Location: dashboard.php?success=item_deleted#menu");
    }
} else {
    header("Location: dashboard.php#menu");
}
exit();
?>