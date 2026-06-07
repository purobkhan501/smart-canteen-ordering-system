<?php
$files = [
    'dashboard.php',
    'breakfast.php',
    'snacks.php',
    'rice.php',
    'drinks.php',
    'deals.php',
    'fast_food.php'
];

$header_buttons = '
            <a href="my_orders.php" class="btn btn-outline-primary btn-sm rounded-pill fw-bold" title="Order History"><i class="bi bi-clock-history"></i></a>
            <a href="../includes/logout.php" class="btn btn-outline-danger btn-sm rounded-pill fw-bold" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
            <a href="view_cart.php" class="cart-icon btn btn-light">';

$floating_widget = '
    <?php
    // Fetch active order for widget
    $active_order = null;
    $res = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = ".intval($_SESSION[\'user_id\'])." AND status IN (\'Pending\', \'Preparing\', \'Ready\') ORDER BY order_id DESC LIMIT 1");
    if($res && mysqli_num_rows($res) > 0) {
        $active_order = mysqli_fetch_assoc($res);
    }
    ?>
    <?php if ($active_order): ?>
    <a href="order_tracking.php?order_id=<?php echo $active_order[\'order_id\']; ?>" class="floating-widget">
        <div class="d-flex align-items-center">
            <div class="widget-icon"><i class="bi bi-bicycle"></i></div>
            <div class="ms-3 text-start">
                <small class="d-block text-uppercase fw-bold text-success" style="font-size:0.7rem; letter-spacing:1px;">Tracking Active Order</small>
                <strong class="text-dark">Order #<?php echo $active_order[\'order_id\']; ?> • <?php echo $active_order[\'status\']; ?></strong>
            </div>
            <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </div>
    </a>
    <?php endif; ?>
    <script';

foreach ($files as $file) {
    $path = __DIR__ . '/../student/' . $file;
    if (!file_exists($path)) continue;
    $content = file_get_contents($path);

    // 1. Add History & Logout buttons before cart-icon
    $content = preg_replace(
        '/<a href="view_cart.php" class="cart-icon btn btn-light">/',
        $header_buttons,
        $content
    );

    // 2. Modify SQL to remove `AND m.is_available = 1` if exists
    $content = str_replace('AND m.is_available = 1"', '"', $content);

    // 3. Update the food-card loop to handle unavailability
    $old_loop = 'while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="food-card">
                        <img src="../assets/images/<?php echo htmlspecialchars($row[\'image_url\'] ?? \'default.jpg\'); ?>" alt="Food" id="food-img">
                        <div class="food-details">
                            <h4><?php echo htmlspecialchars($row[\'name\'] ?? \'Unknown\'); ?></h4>
                            <div class="price-row">
                                <span>৳<?php echo htmlspecialchars($row[\'price\'] ?? \'0\'); ?></span>
                                <form action="add_to_cart.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="menu_item_id" value="<?php echo htmlspecialchars($row[\'item_id\'] ?? 0); ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="plus-button btn btn-light">+</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php
                }';

    $new_loop = 'while ($row = mysqli_fetch_assoc($result)) {
                    $is_avail = isset($row[\'is_available\']) ? $row[\'is_available\'] : 1;
                    $gray_class = ($is_avail == 0) ? \'grayscale\' : \'\';
                    $btn_disabled = ($is_avail == 0) ? \'disabled\' : \'\';
                    $btn_type = ($is_avail == 0) ? \'button\' : \'submit\';
                    ?>
                    <div class="food-card <?php echo $gray_class; ?>">
                        <img src="../assets/images/<?php echo htmlspecialchars($row[\'image_url\'] ?? \'default.jpg\'); ?>" alt="Food" id="food-img">
                        <div class="food-details">
                            <h4><?php echo htmlspecialchars($row[\'name\'] ?? \'Unknown\'); ?></h4>
                            <div class="price-row">
                                <span>৳<?php echo htmlspecialchars($row[\'price\'] ?? \'0\'); ?></span>
                                <form action="add_to_cart.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="menu_item_id" value="<?php echo htmlspecialchars($row[\'item_id\'] ?? 0); ?>">
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
                }';

    $content = str_replace($old_loop, $new_loop, $content);

    // 4. Add floating widget before scripts
    // Ensure we don't add it twice if run multiple times
    if (strpos($content, 'class="floating-widget"') === false) {
        $content = preg_replace('/<script/', $floating_widget, $content, 1);
    }

    file_put_contents($path, $content);
}
echo "Pages updated successfully.\n";
?>
