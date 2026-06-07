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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_style.css">
    <style>
        .qty-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            background: #f0f0f0;
            border: 1px solid #ddd;
            transition: 0.2s;
        }
        .qty-btn:hover {
            background: #e0e0e0;
        }
        .qty-number {
            min-width: 40px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
        }
        .update-form {
            display: inline-block;
            margin: 0 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a class="home-button btn btn-light" href="dashboard.php"><i class="bi bi-arrow-left-circle"></i></a>
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
                            $sql = "SELECT m.*, d.percentage FROM menu_item m LEFT JOIN discount d ON m.item_id = d.item_id AND d.is_active = 1 WHERE m.item_id = " . intval($item_id);
                            $result = mysqli_query($conn, $sql);
                            if ($result && mysqli_num_rows($result) > 0):
                                $item = mysqli_fetch_assoc($result);
                                $price = $item['price'];
                                if (!empty($item['percentage'])) {
                                    $price = $price - ($price * $item['percentage'] / 100);
                                }
                                $total_price = $price * $quantity;
                                $grand_total += $total_price;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../assets/images/<?php echo htmlspecialchars($item['image_url'] ?? 'default.jpg'); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <?php if(!empty($item['percentage'])): ?>
                                        <span class="badge bg-danger ms-2">-<?php echo $item['percentage']; ?>%</span>
                                    <?php endif; ?>
                                </div>
                             </td>
                             <td>
                                <?php if(!empty($item['percentage'])): ?>
                                    <del class="text-muted small">৳<?php echo $item['price']; ?></del>
                                <?php endif; ?>
                                ৳<?php echo number_format($price, 0); ?>
                             </td>
                             <td>
                                <form action="update_cart.php" method="POST" class="update-form">
                                    <input type="hidden" name="menu_item_id" value="<?php echo $item_id; ?>">
                                    <input type="hidden" name="action" value="decrease">
                                    <button type="submit" class="qty-btn" <?php echo $quantity <= 1 ? 'disabled' : ''; ?>>-</button>
                                </form>
                                <span class="qty-number"><?php echo $quantity; ?></span>
                                <form action="update_cart.php" method="POST" class="update-form">
                                    <input type="hidden" name="menu_item_id" value="<?php echo $item_id; ?>">
                                    <input type="hidden" name="action" value="increase">
                                    <button type="submit" class="qty-btn">+</button>
                                </form>
                             </td>
                             <td>
    <div class="d-flex align-items-center">
        <img src="../assets/images/<?php echo htmlspecialchars($item['image_url'] ?? 'default.jpg'); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
        <?php if($item['is_special'] == 1): ?>
            <span class="badge bg-warning text-dark ms-2">Pre-order</span>
        <?php endif; ?>
        <?php if(!empty($item['percentage'])): ?>
            <span class="badge bg-danger ms-2">-<?php echo $item['percentage']; ?>%</span>
        <?php endif; ?>
    </div>
</td>
                             <td>৳<?php echo number_format($total_price, 0); ?></td>
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
                            <td colspan="2" class="fw-bold fs-5 text-success">৳<?php echo number_format($grand_total, 0); ?></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>