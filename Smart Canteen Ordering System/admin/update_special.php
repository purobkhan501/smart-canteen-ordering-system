<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);
    
    // Handle delete
    if (isset($_POST['delete_special'])) {
        $sql = "UPDATE menu_item SET is_special = 0, min_preorders = NULL, preorder_available_date = NULL WHERE item_id = $item_id";
        mysqli_query($conn, $sql);
        header("Location: dashboard.php#special-menu");
        exit();
    }
    
    // Handle save (update min_preorders and available_date)
    if (isset($_POST['save_settings'])) {
        $min_preorders = intval($_POST['min_preorders']);
        $available_date = mysqli_real_escape_string($conn, $_POST['preorder_available_date']);
        
        $sql = "UPDATE menu_item SET min_preorders = $min_preorders, preorder_available_date = '$available_date' WHERE item_id = $item_id";
        mysqli_query($conn, $sql);
        
        // Also update is_special if checkbox toggled
        if (isset($_POST['is_active'])) {
            mysqli_query($conn, "UPDATE menu_item SET is_special = 1 WHERE item_id = $item_id");
        } else {
            mysqli_query($conn, "UPDATE menu_item SET is_special = 0 WHERE item_id = $item_id");
        }
        
        header("Location: dashboard.php#special-menu");
        exit();
    }
}

header("Location: dashboard.php");
exit();
?>