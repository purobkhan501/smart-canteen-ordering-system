<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

session_start(); // ensure session is started

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['menu_item_id'])) {
    $item_id = intval($_POST['menu_item_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $user_id = intval($_SESSION['user_id']);

    // Get item details
    $item_res = mysqli_query($conn, "SELECT is_special, min_preorders, preorder_available_date, name, price FROM menu_item WHERE item_id = $item_id");
    if ($item_res && $item = mysqli_fetch_assoc($item_res)) {
        if ($item['is_special'] == 1 && !empty($item['preorder_available_date'])) {
            $deadline = strtotime($item['preorder_available_date']);
            if ($deadline <= time()) {
                echo "<script>alert('Pre-order deadline has passed for this item.'); window.history.back();</script>";
                exit();
            }

            // Count current total pre-orders (including this one)
            $count_res = mysqli_query($conn, "SELECT SUM(quantity) as total FROM pre_orders WHERE item_id = $item_id");
            $cnt_row = mysqli_fetch_assoc($count_res);
            $current_total = ($cnt_row['total'] ?? 0) + $quantity;
            
            // Check if adding this would exceed min_preorders? (optional, but we can allow)
            // For now, just allow.
            
            // Insert or update pre_orders table
            $check_sql = "SELECT * FROM pre_orders WHERE item_id = $item_id AND user_id = $user_id";
            $check_res = mysqli_query($conn, $check_sql);
            if (mysqli_num_rows($check_res) == 0) {
                $sql = "INSERT INTO pre_orders (item_id, user_id, quantity, status, created_at) VALUES ($item_id, $user_id, $quantity, 'Pending', NOW())";
                mysqli_query($conn, $sql);
            } else {
                $row = mysqli_fetch_assoc($check_res);
                $new_qty = $row['quantity'] + $quantity;
                $update_sql = "UPDATE pre_orders SET quantity = $new_qty WHERE item_id = $item_id AND user_id = $user_id";
                mysqli_query($conn, $update_sql);
            }

            // ALSO add to session cart (so it appears in cart and can be checked out)
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            if (isset($_SESSION['cart'][$item_id])) {
                $_SESSION['cart'][$item_id] += $quantity;
            } else {
                $_SESSION['cart'][$item_id] = $quantity;
            }

            echo "<script>alert('Pre-order added to cart! Quantity: $quantity. Minimum $item[min_preorders] pre-orders needed in total.'); window.location.href='view_cart.php';</script>";
        } else {
            echo "<script>alert('This item is not available for pre-order.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid item.'); window.history.back();</script>";
    }
} else {
    header("Location: dashboard.php");
}
exit();
?>