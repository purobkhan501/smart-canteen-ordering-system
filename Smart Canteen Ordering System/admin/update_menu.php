<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['all_items'])) {
    $all_items = $_POST['all_items'];
    $available_items = isset($_POST['available_items']) ? $_POST['available_items'] : [];

    // Convert to integers for safety
    $all_items = array_map('intval', $all_items);
    $available_items = array_map('intval', $available_items);

    // Start transaction for better data integrity
    mysqli_begin_transaction($conn);

    try {
        // First, set all submitted items to unavailable
        if (!empty($all_items)) {
            $ids = implode(',', $all_items);
            mysqli_query($conn, "UPDATE menu_item SET is_available = 0 WHERE item_id IN ($ids)");
        }

        // Then, set the checked items to available
        if (!empty($available_items)) {
            $ids = implode(',', $available_items);
            mysqli_query($conn, "UPDATE menu_item SET is_available = 1 WHERE item_id IN ($ids)");
        }

        mysqli_commit($conn);
        echo "<script>alert('Menu availability updated successfully!'); window.location.href='dashboard.php#menu';</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Error updating menu. Please try again.'); window.location.href='dashboard.php#menu';</script>";
    }
} else {
    header("Location: dashboard.php");
}
?>
