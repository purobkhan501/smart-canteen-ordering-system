<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['menu_item_id'])) {
    $menu_item_id = (int)$_POST['menu_item_id'];
    
    if (isset($_SESSION['cart'][$menu_item_id])) {
        unset($_SESSION['cart'][$menu_item_id]);
    }
}

header("Location: view_cart.php");
exit();
?>

