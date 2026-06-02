<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - UIU Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_style.css">
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a class="home-button btn btn-light" href="dashboard.php" role="button"> <i class="bi bi-arrow-left-circle"></i></a>
            <h2>My Cart</h2>
        </div>
    </div>

    <div class="container mt-5">
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info text-center">Your cart is empty. <a href="dashboard.php">Browse Menu</a></div>
        <?php else: ?>
            <div class="table-responsive bg-white p-4 rounded shadow-sm">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grand_total = 0;
                        foreach ($cart_items as $item_id => $quantity):
                            $sql = "SELECT * FROM menu_item WHERE item_id = " . intval($item_id);
                            $result = mysqli_query($conn, $sql);
                            if ($result && mysqli_num_rows($result) > 0):
                                $item = mysqli_fetch_assoc($result);
                                $total_price = $item['price'] * $quantity;
                                $grand_total += $total_price;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../assets/images/<?php echo htmlspecialchars($item['image_url'] ?? 'default.jpg'); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                </div>
                            </td>
                            <td>৳<?php echo htmlspecialchars($item['price']); ?></td>
                            <td><?php echo $quantity; ?></td>
                            <td>৳<?php echo $total_price; ?></td>
                            <td>
                                <form action="remove_from_cart.php" method="POST">
                                    <input type="hidden" name="menu_item_id" value="<?php echo $item_id; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                            <td colspan="2" class="fw-bold fs-5 text-success">৳<?php echo $grand_total; ?></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-end mt-4">
                    <form action="checkout.php" method="POST">
                        <button type="submit" class="btn btn-success btn-lg px-5 py-3 rounded-pill fw-bold">Place Order <i class="bi bi-check-circle ms-2"></i></button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
