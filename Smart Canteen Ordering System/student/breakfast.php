<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

// Fetch active discounts to map by item_id
$discounts = [];
$disc_res = mysqli_query($conn, "SELECT * FROM discount WHERE is_active = 1");
if ($disc_res) {
    while ($d = mysqli_fetch_assoc($disc_res)) {
        $discounts[$d['item_id']] = $d['percentage'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breakfast</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_style.css">
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
        <a class="cat-button btn btn-light nav-link active" href="breakfast.php">Breakfast</a>
        <a class="cat-button btn btn-light" href="snacks.php">Snacks</a>
        <a class="cat-button btn btn-light" href="fast_food.php">Fast Food</a>
        <a class="cat-button btn btn-light" href="rice.php">Rice</a>
        <a class="cat-button btn btn-light" href="drinks.php">Drinks</a>
        <a class="cat-button btn btn-light" href="deals.php">Deals</a>
        <a class="cat-button btn btn-light" href="special.php">Special</a>
    </div>

    <div class="workflow container-fluid">
        <!-- The entire special section has been removed -->
        
        <div class="food-grid">
            <?php
            $sql = "SELECT m.* FROM menu_item m JOIN category c ON m.category_id = c.category_id WHERE c.name = 'Breakfast'";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $is_avail = isset($row['is_available']) ? $row['is_available'] : 1;
                    $gray_class = ($is_avail == 0) ? 'grayscale' : '';
                    $btn_disabled = ($is_avail == 0) ? 'disabled' : '';
                    $btn_type = ($is_avail == 0) ? 'button' : 'submit';
                    
                    $original_price = $row['price'];
                    $has_discount = isset($discounts[$row['item_id']]);
                    $final_price = $original_price;
                    if ($has_discount) {
                        $final_price = $original_price - ($original_price * $discounts[$row['item_id']] / 100);
                    }
                    ?>
                    <div class="food-card <?php echo $gray_class; ?> position-relative">
                        <?php if($has_discount): ?>
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">-<?php echo $discounts[$row['item_id']]; ?>%</span>
                        <?php endif; ?>
                        <img src="../assets/images/<?php echo htmlspecialchars($row['image_url'] ?? 'default.jpg'); ?>" alt="Food" id="food-img">
                        <div class="food-details">
                            <h4><?php echo htmlspecialchars($row['name'] ?? 'Unknown'); ?></h4>
                            <div class="price-row">
                                <span>
                                    <?php if($has_discount): ?>
                                        <del class="text-muted small">৳<?php echo $original_price; ?></del> 
                                    <?php endif; ?>
                                    ৳<?php echo number_format($final_price, 0); ?>
                                </span>
                                <form action="add_to_cart.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="menu_item_id" value="<?php echo htmlspecialchars($row['item_id'] ?? 0); ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="<?php echo $btn_type; ?>" class="plus-button btn btn-light" <?php echo $btn_disabled; ?>>+</button>
                                </form>
                            </div>
                            <?php if ($is_avail == 0): ?>
                                <small class="text-danger d-block text-center mt-1" style="font-size: 0.7rem; font-weight: bold;">Currently Unavailable</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='text-muted'>No food items available in this category.</p>";
            }
            ?>
        </div>
    </div>

    <?php
    // Fetch active order for widget
    $active_order = null;
    $res = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = ".intval($_SESSION['user_id'])." AND status IN ('Pending', 'Preparing', 'Ready') ORDER BY order_id DESC LIMIT 1");
    if($res && mysqli_num_rows($res) > 0) {
        $active_order = mysqli_fetch_assoc($res);
    }
    ?>
    <?php if ($active_order): ?>
    <a href="order_tracking.php?order_id=<?php echo $active_order['order_id']; ?>" class="floating-widget">
        <div class="d-flex align-items-center">
            <div class="widget-icon"><i class="bi bi-bicycle"></i></div>
            <div class="ms-3 text-start">
                <small class="d-block text-uppercase fw-bold text-success" style="font-size:0.7rem; letter-spacing:1px;">Tracking Active Order</small>
                <strong class="text-dark">Order #<?php echo $active_order['order_id']; ?> • <?php echo $active_order['status']; ?></strong>
            </div>
            <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </div>
    </a>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>