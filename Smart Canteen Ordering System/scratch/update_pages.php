<?php
$files = [
    'breakfast.php' => 'Breakfast',
    'snacks.php' => 'Snacks',
    'rice.php' => 'Lunch', // Using Lunch since Rice isn't in DB, they map to Lunch meals
    'drinks.php' => 'Drinks',
    'deals.php' => 'Deals'
];

foreach ($files as $file => $cat) {
    $path = __DIR__ . '/../student/' . $file;
    $content = file_get_contents($path);

    // 1. Add PHP requires at the top
    $content = preg_replace(
        '/<!DOCTYPE html>/',
        "<?php\nrequire_once '../includes/auth.php';\nrequire_login();\nrequire_once '../includes/db.php';\n?>\n<!DOCTYPE html>",
        $content,
        1
    );

    // 2. Replace static cart icon with dynamic one
    $cart_dynamic = '<a href="view_cart.php" class="cart-icon btn btn-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                </svg>
                <?php
                $cart_count = 0;
                if(isset($_SESSION[\'cart\'])){
                    foreach($_SESSION[\'cart\'] as $qty) {
                        $cart_count += $qty;
                    }
                }
                if($cart_count > 0) {
                    echo "<span class=\'badge bg-danger rounded-pill\'>$cart_count</span>";
                }
                ?>
            </a>';

    $content = preg_replace(
        '/<button type="button" class="cart-icon btn btn-light">\s*<svg.*?<\/svg>\s*<\/button>/s',
        $cart_dynamic,
        $content
    );

    // 3. Replace food grid with dynamic fetching block
    $grid_dynamic = '<div class="food-grid">
            <?php
            $sql = "SELECT m.* FROM menu_item m JOIN category c ON m.category_id = c.category_id WHERE c.name = \''.$cat.'\' AND m.is_available = 1";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
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
                }
            } else {
                echo "<p class=\'text-muted\'>No food items available in this category.</p>";
            }
            ?>
        </div>
    </div>

    <script';

    $content = preg_replace(
        '/<div class="food-grid">.*?<\/div>\s*<\/div>\s*<script/s',
        $grid_dynamic,
        $content
    );

    file_put_contents($path, $content);
}
echo "Done.";
?>
