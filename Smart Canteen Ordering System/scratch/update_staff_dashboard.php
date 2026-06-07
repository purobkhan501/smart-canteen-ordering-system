<?php
$path = __DIR__ . '/../staff/dashboard.php';
$content = file_get_contents($path);

// 1. Add fetching of order items
$fetch_items = <<<'EOD'
// Fetch items for all active orders
$active_order_ids = array_merge(
    array_column($pending_orders, 'order_id'),
    array_column($preparing_orders, 'order_id'),
    array_column($ready_orders, 'order_id')
);
$order_items = [];
if (!empty($active_order_ids)) {
    $ids_str = implode(',', $active_order_ids);
    $res = mysqli_query($conn, "SELECT oi.*, m.name FROM order_item oi JOIN menu_item m ON oi.item_id = m.item_id WHERE oi.order_id IN ($ids_str)");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $order_items[$row['order_id']][] = $row;
        }
    }
}
?>
EOD;

$content = preg_replace('/\?>/', $fetch_items, $content, 1);

// 2. Add logout button
$logout_btn = <<<'EOD'
<div>
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-circle me-2"><i class="bi bi-arrow-clockwise"></i></a>
                    <a href="../includes/logout.php" class="btn btn-outline-danger btn-sm rounded-pill fw-bold"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
EOD;
$content = preg_replace(
    '/<button class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-arrow-clockwise"><\/i><\/button>/',
    $logout_btn,
    $content
);

// 3. Add items list inside order cards.
$items_html = <<<'EOD'
                                    <div class="mb-2">
                                        <?php 
                                        if (isset($order_items[$order['order_id']])) {
                                            foreach ($order_items[$order['order_id']] as $item) {
                                                echo '<div class="d-flex justify-content-between align-items-center small text-muted border-bottom border-light py-1">';
                                                echo '<span>' . $item['quantity'] . 'x ' . htmlspecialchars($item['name']) . '</span>';
                                                echo '<form action="cancel_order_item.php" method="POST" style="margin:0;"><input type="hidden" name="order_item_id" value="'.$item['order_item_id'].'"><input type="hidden" name="order_id" value="'.$order['order_id'].'"><button type="submit" class="btn btn-link text-danger p-0 border-0" title="Cancel this item"><i class="bi bi-x-circle-fill"></i></button></form>';
                                                echo '</div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div class="d-flex gap-2 mt-2">
EOD;
$content = str_replace('<div class="d-flex gap-2 mt-2">', $items_html, $content);

$items_html2 = <<<'EOD'
                                    <div class="mb-2">
                                        <?php 
                                        if (isset($order_items[$order['order_id']])) {
                                            foreach ($order_items[$order['order_id']] as $item) {
                                                echo '<div class="d-flex justify-content-between align-items-center small text-muted border-bottom border-light py-1">';
                                                echo '<span>' . $item['quantity'] . 'x ' . htmlspecialchars($item['name']) . '</span>';
                                                echo '<form action="cancel_order_item.php" method="POST" style="margin:0;"><input type="hidden" name="order_item_id" value="'.$item['order_item_id'].'"><input type="hidden" name="order_id" value="'.$order['order_id'].'"><button type="submit" class="btn btn-link text-danger p-0 border-0" title="Cancel this item"><i class="bi bi-x-circle-fill"></i></button></form>';
                                                echo '</div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <form action="update_order.php" method="POST">
EOD;
$content = str_replace('<form action="update_order.php" method="POST">', $items_html2, $content);

file_put_contents($path, $content);
echo "Staff dashboard updated successfully.\n";
?>
