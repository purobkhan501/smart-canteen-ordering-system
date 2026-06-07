<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deals</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/student_style.css">
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a class="home-button btn btn-light" href="../index.php" role="button"> <i class="bi bi-arrow-left-circle"></i> </a>
            <h2>UIU Canteen</h2>
        </div>
        <div class="header-right"> 
            <input type="text" placeholder="Search food..." id="search-bar"> 
            
            <a href="my_orders.php" class="btn btn-outline-primary btn-sm rounded-pill fw-bold" title="Order History"><i class="bi bi-clock-history"></i></a>
            <a href="../includes/logout.php" class="btn btn-outline-danger btn-sm rounded-pill fw-bold" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
            <a href="view_cart.php" class="cart-icon btn btn-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                </svg>
                <?php
                $cart_count = 0;
                if(isset($_SESSION['cart'])){
                    foreach($_SESSION['cart'] as $qty) {
                        $cart_count += $qty;
                    }
                }
                if($cart_count > 0) {
                    echo "<span class='badge bg-danger rounded-pill'>$cart_count</span>";
                }
                ?>
            </a>
        </div>
    </div>

    <div class="catagories">
        <a class="cat-button btn btn-light" href="dashboard.php" role="button">All</a>
        <a class="cat-button btn btn-light" href="./breakfast.php" role="button">Breakfast</a>
        <a class="cat-button btn btn-light" href="./snacks.php" role="button">Snacks</a>
        <a class="cat-button btn btn-light" href="./fast_food.php" role="button">Fast Food</a>
        <a class="cat-button btn btn-light" href="./rice.php" role="button">Rice</a>
        <a class="cat-button btn btn-light" href="./drinks.php" role="button">Drinks</a>
        <a class="cat-button btn btn-light nav-link active" href="./deals.php" role="button">Deals</a>
        <a class="cat-button btn btn-light" href="./special.php" role="button">Special</a>
    </div>

    <div class="container text-center mt-5">
        <div row>
            <div>No Deals Available Right Now</div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>

</body>
</html>

