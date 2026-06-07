<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

// Fetch special items with pre-order settings
$special_items = [];
$sp_res = mysqli_query($conn, "SELECT * FROM menu_item WHERE is_special = 1 AND is_available = 1");
if ($sp_res) {
    while ($sp = mysqli_fetch_assoc($sp_res)) {
        // Count current pre-orders for this item
        $count_res = mysqli_query($conn, "SELECT SUM(quantity) as total FROM pre_orders WHERE item_id = ".$sp['item_id']);
        $cnt_row = mysqli_fetch_assoc($count_res);
        $sp['current_preorders'] = $cnt_row['total'] ?? 0;
        $special_items[] = $sp;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Pre-Order Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_style.css">
    <style>
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }
        .qty-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f0f0f0;
            border: 1px solid #ddd;
            font-weight: bold;
            font-size: 1.2rem;
            cursor: pointer;
        }
        .qty-btn:hover {
            background: #e0e0e0;
        }
        .qty-number {
            font-weight: bold;
            min-width: 40px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a class="home-button btn btn-light" href="../index.php"><i class="bi bi-arrow-left-circle"></i></a>
            <h2>UIU Canteen</h2>
        </div>
        <div class="header-right"> 
            <input type="text" placeholder="Search food..." id="search-bar"> 
            <a href="my_orders.php" class="btn btn-outline-primary btn-sm rounded-pill fw-bold"><i class="bi bi-clock-history"></i></a>
            <a href="../includes/logout.php" class="btn btn-outline-danger btn-sm rounded-pill fw-bold"><i class="bi bi-box-arrow-right"></i></a>
            <a href="view_cart.php" class="cart-icon btn btn-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                </svg>
                <?php
                $cart_count = 0;
                if(isset($_SESSION['cart'])){
                    foreach($_SESSION['cart'] as $qty) $cart_count += $qty;
                }
                if($cart_count > 0) echo "<span class='badge bg-danger rounded-pill'>$cart_count</span>";
                ?>
            </a>
        </div>
    </div>

    <div class="catagories">
        <a class="cat-button btn btn-light" href="dashboard.php">All</a>
        <a class="cat-button btn btn-light" href="breakfast.php">Breakfast</a>
        <a class="cat-button btn btn-light" href="snacks.php">Snacks</a>
        <a class="cat-button btn btn-light" href="fast_food.php">Fast Food</a>
        <a class="cat-button btn btn-light" href="rice.php">Rice</a>
        <a class="cat-button btn btn-light" href="drinks.php">Drinks</a>
        <a class="cat-button btn btn-light" href="deals.php">Deals</a>
        <a class="cat-button btn btn-light nav-link active" href="special.php">Special</a>
    </div>

    <div class="workflow container-fluid">
        <div class="special">
            <h2 class="section-title">⭐ Special Pre-Order Items <span class="badge-tag">Pre-Order Required</span></h2>
            
            <?php if(empty($special_items)): ?>
                <p class="text-muted ms-3">No special pre-order items available at the moment.</p>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($special_items as $sp): 
                        $can_preorder = (strtotime($sp['preorder_available_date']) > time());
                        $button_text = $can_preorder ? "Pre-Order" : "Deadline Passed";
                        $button_class = $can_preorder ? "btn-orange" : "btn-secondary";
                        $disabled = !$can_preorder ? "disabled" : "";
                        $pct = ($sp['min_preorders'] > 0) ? min(100, ($sp['current_preorders'] / $sp['min_preorders']) * 100) : 0;
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="food-card position-relative">
                            <img src="../assets/images/<?php echo htmlspecialchars($sp['image_url'] ?? 'default.jpg'); ?>" alt="Food" id="food-img">
                            <div class="food-details">
                                <h4><?php echo htmlspecialchars($sp['name']); ?></h4>
                                <div class="price-row">
                                    <span>৳<?php echo number_format($sp['price'], 0); ?></span>
                                </div>
                                <div class="pre-order-stats small">
                                    <span><?php echo $sp['current_preorders']; ?> / <?php echo $sp['min_preorders']; ?> pre-orders</span>
                                    <span>📅 <?php echo date('M d, Y', strtotime($sp['preorder_available_date'])); ?></span>
                                </div>
                                <div class="progress-bar mb-2">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: <?php echo $pct; ?>%;"></div>
                                    </div>
                                </div>
                                
                                <!-- Quantity Selector -->
                                <form action="pre_order.php" method="POST" class="mt-2">
                                    <input type="hidden" name="menu_item_id" value="<?php echo $sp['item_id']; ?>">
                                    <div class="quantity-control">
                                        <button type="button" class="qty-btn" onclick="changeQty(this, -1)">-</button>
                                        <input type="number" name="quantity" value="1" min="1" max="99" class="qty-number form-control form-control-sm text-center" style="width: 70px; display: inline-block;" readonly>
                                        <button type="button" class="qty-btn" onclick="changeQty(this, 1)">+</button>
                                    </div>
                                    <button type="submit" class="btn <?php echo $button_class; ?> w-100 mt-2" <?php echo $disabled; ?>><?php echo $button_text; ?></button>
                                </form>
                                <?php if(!$can_preorder): ?>
                                    <small class="text-danger d-block mt-1">Pre-order deadline passed</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function changeQty(btn, delta) {
            let input = btn.parentNode.querySelector('.qty-number');
            let newVal = parseInt(input.value) + delta;
            if (newVal < 1) newVal = 1;
            if (newVal > 99) newVal = 99;
            input.value = newVal;
        }
    </script>

    <?php
    // Active order widget
    $active_order = null;
    $res = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = ".intval($_SESSION['user_id'])." AND status IN ('Pending','Preparing','Ready') ORDER BY order_id DESC LIMIT 1");
    if($res && mysqli_num_rows($res) > 0) $active_order = mysqli_fetch_assoc($res);
    ?>
    <?php if ($active_order): ?>
    <a href="order_tracking.php?order_id=<?php echo $active_order['order_id']; ?>" class="floating-widget">
        <div class="d-flex align-items-center">
            <div class="widget-icon"><i class="bi bi-bicycle"></i></div>
            <div class="ms-3 text-start">
                <small class="d-block text-uppercase fw-bold text-success">Tracking Active Order</small>
                <strong class="text-dark">Order #<?php echo $active_order['order_id']; ?> • <?php echo $active_order['status']; ?></strong>
            </div>
            <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </div>
    </a>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>