<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Toggle the is_special column
    $sql = "UPDATE menu_item SET is_special = $is_active WHERE item_id = $item_id";
    mysqli_query($conn, $sql);
    
    // If deactivating, also clear pre-order settings? (optional)
    if ($is_active == 0) {
        // Optional: clear min_preorders and date
        // mysqli_query($conn, "UPDATE menu_item SET min_preorders = NULL, preorder_available_date = NULL WHERE item_id = $item_id");
    }
}

// Redirect back to the special menu tab
header("Location: dashboard.php#special-menu");
exit();
?>