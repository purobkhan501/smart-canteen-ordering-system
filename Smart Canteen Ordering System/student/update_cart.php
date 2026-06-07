<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['menu_item_id'], $_POST['action'])) {
    $menu_item_id = (int)$_POST['menu_item_id'];
    $action = $_POST['action'];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if ($action == 'increase') {
        if (isset($_SESSION['cart'][$menu_item_id])) {
            $_SESSION['cart'][$menu_item_id]++;
        } else {
            $_SESSION['cart'][$menu_item_id] = 1;
        }
    } elseif ($action == 'decrease') {
        if (isset($_SESSION['cart'][$menu_item_id])) {
            if ($_SESSION['cart'][$menu_item_id] > 1) {
                $_SESSION['cart'][$menu_item_id]--;
            } else {
                unset($_SESSION['cart'][$menu_item_id]);
            }
        }
    }
}

header("Location: view_cart.php");
exit();
?>