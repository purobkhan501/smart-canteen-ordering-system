<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url'] ?? 'default.jpg');
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $is_special = isset($_POST['is_special']) ? 1 : 0;
    
    $sql = "INSERT INTO menu_item (category_id, name, price, image_url, is_available, is_special) 
            VALUES ($category_id, '$name', $price, '$image_url', $is_available, $is_special)";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php?success=item_added#menu");
    } else {
        header("Location: dashboard.php?error=add_failed#menu");
    }
} else {
    header("Location: dashboard.php#menu");
}
exit();
?>