<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'], $_POST['min_preorders'], $_POST['preorder_available_date'])) {
    $item_id = intval($_POST['item_id']);
    $min_preorders = intval($_POST['min_preorders']);
    $available_date = mysqli_real_escape_string($conn, $_POST['preorder_available_date']);
    
    // Mark as special and set pre-order parameters
    $sql = "UPDATE menu_item SET is_special = 1, min_preorders = $min_preorders, preorder_available_date = '$available_date' WHERE item_id = $item_id";
    mysqli_query($conn, $sql);
}

header("Location: dashboard.php#special-menu");
exit();
?>