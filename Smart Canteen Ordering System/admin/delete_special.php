<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);
    $sql = "UPDATE menu_item SET is_special = 0, min_preorders = NULL, preorder_available_date = NULL WHERE item_id = $item_id";
    mysqli_query($conn, $sql);
}

header("Location: dashboard.php#special-menu");
exit();
?>