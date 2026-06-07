<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['menu_item_id'])) {
    $menu_item_id = (int)$_POST['menu_item_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$menu_item_id])) {
        $_SESSION['cart'][$menu_item_id] += $quantity;
    } else {
        $_SESSION['cart'][$menu_item_id] = $quantity;
    }

    // Redirect back to dashboard or cart page
    header("Location: dashboard.php");
    exit();
}
?>

