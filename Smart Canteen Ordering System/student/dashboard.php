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
    <title>Student Dashboard - UIU Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/student_style.css">
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a class="home-button btn btn-light" href="../index.php" role="button"> <i class="bi bi-arrow-left-circle"></i></a>
            <h2>UIU Canteen</h2>
        </div>
        <div class="header-right"> 
            <input type="text" placeholder="Search food..." id="search-bar"> 
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
        <a class="cat-button btn btn-light nav-link active" href="dashboard.php" role="button">All</a>
        <a class="cat-button btn btn-light " href="./breakfast.php" role="button">Breakfast</a>
        <a class="cat-button btn btn-light " href="./snacks.php" role="button">Snacks</a>
        <a class="cat-button btn btn-light " href="./fast_food.php" role="button">Fast Food</a>
        <a class="cat-button btn btn-light " href="./rice.php" role="button">Rice</a>
        <a class="cat-button btn btn-light " href="./drinks.php" role="button">Drinks</a>
        <a class="cat-button btn btn-light " href="./deals.php" role="button">Deals</a>
        <a class="cat-button btn btn-light " href="./special.php" role="button">Special</a>
    </div>

    <div class="workflow container-fluid">
        <div class="special">
            <h2 class="section-title">⭐ Special Pre-Order Items <span class="badge-tag">Pre-Order Required</span></h2>
            <div class="special-card">
                <div class="special-img">
                    <img src="../assets/images/Kacchi.jpg" alt="Kacchi Biriyani" id="special-img">
                    <span class="price-tag">৳210</span>
                    <span class="special-label">⭐Special</span>
                </div>
                <div class="special-info">
                    <p>Premium Mutton Kacchi.Only made if 10+ pre-orders received!</p>
                    <div class="pre-order-stats">
                        <span>0 / 10 pre-orders</span>
                        <span>📅 2026-05-04</span>
                    </div>

                    <div class="progress-bar">
                        <progress value="0" max="100" id="progress-bar"></progress>
                    </div>
                    <div class="pre-order-button">
                        <input type="button"  value="Pre-Order Now" id="pre-order-button">
                    </div>

                </div>
                
                
            </div>
        </div>
        


        <div class="food-grid">
            <?php
            // Fetch all menu items
            $sql = "SELECT * FROM menu_item";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="food-card">
                        <img src="../assets/images/<?php echo htmlspecialchars($row['image_url'] ?? 'default.jpg'); ?>" alt="Food" id="food-img">
                        <div class="food-details">
                            <h4><?php echo htmlspecialchars($row['name'] ?? 'Unknown'); ?></h4>
                            <div class="price-row">
                                <span>৳<?php echo htmlspecialchars($row['price'] ?? '0'); ?></span>
                                <form action="add_to_cart.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="menu_item_id" value="<?php echo htmlspecialchars($row['item_id'] ?? 0); ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="plus-button btn btn-light">+</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='text-muted'>No food items available at the moment.</p>";
            }
            ?>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>
