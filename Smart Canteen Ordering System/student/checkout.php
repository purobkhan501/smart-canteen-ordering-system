<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_SESSION['cart'])) {
        echo "<script>alert('Your cart is empty!'); window.location.href='dashboard.php';</script>";
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $cart_items = $_SESSION['cart'];
    $total_amount = 0;

    // Calculate total price
    foreach ($cart_items as $item_id => $quantity) {
        $sql = "SELECT price FROM menu_item WHERE item_id = " . intval($item_id);
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $item = mysqli_fetch_assoc($result);
            $total_amount += ($item['price'] * $quantity);
        }
    }

    // Generate a unique token number (e.g., T-0001)
    $token_res = mysqli_query($conn, "SELECT MAX(order_id) as max_id FROM orders");
    $max_row = mysqli_fetch_assoc($token_res);
    $next_id = ($max_row['max_id'] ?? 0) + 1;
    $token_number = 'T-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);

    // Insert into orders table
    $status = 'Pending';
    $payment_status = 'Unpaid';
    $order_query = "INSERT INTO orders (user_id, total_amount, status, payment_status, token_number) 
                    VALUES ('$user_id', '$total_amount', '$status', '$payment_status', '$token_number')";
    
    if (mysqli_query($conn, $order_query)) {
        $order_id = mysqli_insert_id($conn);

        // Insert order items
        foreach ($cart_items as $item_id => $quantity) {
            $sql = "SELECT price FROM menu_item WHERE item_id = " . intval($item_id);
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $item = mysqli_fetch_assoc($result);
                $unit_price = $item['price'];
                $item_query = "INSERT INTO order_item (order_id, item_id, quantity, unit_price) 
                               VALUES ('$order_id', '$item_id', '$quantity', '$unit_price')";
                mysqli_query($conn, $item_query);
            }
        }

        // Clear cart
        unset($_SESSION['cart']);

        // Store the order info in session for the token popup
        $_SESSION['last_order_id'] = $order_id;
        $_SESSION['last_token'] = $token_number;
        $_SESSION['last_order_total'] = $total_amount;

        // Redirect to the order tracking page (which will show the token popup)
        header("Location: order_tracking.php?order_id=$order_id&new=1");
        exit();
    } else {
        echo "<script>alert('Failed to place order. Please try again.'); window.location.href='view_cart.php';</script>";
    }
} else {
    header("Location: dashboard.php");
}
?>
