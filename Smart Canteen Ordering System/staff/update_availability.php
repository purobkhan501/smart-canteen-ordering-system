<?php
require_once '../includes/auth.php';
require_role('staff');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'], $_POST['is_available'])) {
    $item_id = intval($_POST['item_id']);
    $is_available = intval($_POST['is_available']);
    $sql = "UPDATE menu_item SET is_available = $is_available WHERE item_id = $item_id";
    if (mysqli_query($conn, $sql)) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'invalid';
}
?>