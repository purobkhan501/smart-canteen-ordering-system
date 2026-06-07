<?php
require_once '../includes/auth.php';
require_role('staff');
require_once '../includes/db.php';

// Fetch orders by status (unchanged)
$pending_orders = [];
$res = mysqli_query($conn, "SELECT * FROM orders WHERE status='Pending' ORDER BY order_id ASC");
if($res) { while($row = mysqli_fetch_assoc($res)) $pending_orders[] = $row; }

$preparing_orders = [];
$res = mysqli_query($conn, "SELECT * FROM orders WHERE status='Preparing' ORDER BY order_id ASC");
if($res) { while($row = mysqli_fetch_assoc($res)) $preparing_orders[] = $row; }

$ready_orders = [];
$res = mysqli_query($conn, "SELECT * FROM orders WHERE status='Ready' ORDER BY order_id ASC");
if($res) { while($row = mysqli_fetch_assoc($res)) $ready_orders[] = $row; }

$picked_today_count = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE status='Picked Up'");
if($res && $row = mysqli_fetch_assoc($res)) { $picked_today_count = $row['cnt']; }

// Fetch order items for active orders
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

// ========== NEW: Fetch categories and menu items for dynamic availability ==========
$categories = [];
$cat_res = mysqli_query($conn, "SELECT * FROM category ORDER BY category_id");
if ($cat_res) {
    while ($cat = mysqli_fetch_assoc($cat_res)) {
        $categories[$cat['category_id']] = $cat['name'];
    }
}

$items_by_cat = [];
$item_res = mysqli_query($conn, "SELECT * FROM menu_item ORDER BY name");
if ($item_res) {
    while ($item = mysqli_fetch_assoc($item_res)) {
        $items_by_cat[$item['category_id']][] = $item;
    }
}
// ===========================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - UIU Smart Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>

    <header class="dashboard-header pb-5">
        <div class="container-fluid py-4 px-4">
            <div class="d-flex justify-content-between align-items-center text-white mb-4">
                <div>
                    <a class="home-button btn btn-light" href="../index.php"><i class="bi bi-arrow-left-circle"></i></a>
                    <h4 class="d-inline-block ms-3 mb-0"><i class="bi bi-people"></i> Staff Dashboard <span class="badge bg-success rounded-pill status-dot">Live</span></h4>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-circle me-2"><i class="bi bi-arrow-clockwise"></i></a>
                    <a href="../includes/logout.php" class="btn btn-outline-danger btn-sm rounded-pill fw-bold"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>

            <div class="row g-3 stats-row">
                <div class="col-md-3"><div class="stat-card bg-primary-light"><h2><?php echo count($pending_orders); ?></h2><p>Pending</p></div></div>
                <div class="col-md-3"><div class="stat-card bg-primary-light"><h2><?php echo count($preparing_orders); ?></h2><p>Preparing</p></div></div>
                <div class="col-md-3"><div class="stat-card bg-primary-light"><h2><?php echo count($ready_orders); ?></h2><p>Ready</p></div></div>
                <div class="col-md-3"><div class="stat-card bg-primary-light"><h2><?php echo $picked_today_count; ?></h2><p>Picked Today</p></div></div>
            </div>

            <div class="nav-tabs-custom mt-4">
                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" id="pills-orders-tab" data-bs-toggle="pill" data-bs-target="#pills-orders">Orders</button></li>
                    <li class="nav-item"><button class="nav-link" id="pills-menu-tab" data-bs-toggle="pill" data-bs-target="#pills-menu">Menu Availability</button></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="container-fluid py-4 px-4">
        <div class="tab-content" id="pills-tabContent">
            
            <!-- Orders Tab (unchanged, works) -->
            <div class="tab-pane fade show active" id="pills-orders">
                <div class="row g-4">
                    <!-- Pending column -->
                    <div class="col-md-4">
                        <div class="order-column">
                            <div class="order-card d-flex justify-content-between p-2 mb-3"><span><i class="bi bi-clock"></i> Pending</span><span><?php echo count($pending_orders); ?></span></div>
                            <?php if (empty($pending_orders)): ?>
                                <div class="text-center py-5 bg-white rounded">No orders</div>
                            <?php else: ?>
                                <?php foreach ($pending_orders as $order): ?>
                                <div class="bg-white p-3 rounded mb-2 shadow-sm border-start border-4 border-warning">
                                    <div class="d-flex justify-content-between mb-2"><strong>Order #<?php echo $order['order_id']; ?></strong><span class="badge bg-warning text-dark">৳<?php echo $order['total_amount']; ?></span><?php if (!empty($order['token_number'])): ?><span class="badge bg-dark ms-1"><?php echo $order['token_number']; ?></span><?php endif; ?></div>
                                    <div class="mb-2"><?php if (isset($order_items[$order['order_id']])) foreach ($order_items[$order['order_id']] as $item) { echo '<div class="d-flex justify-content-between small text-muted border-bottom py-1"><span>'.$item['quantity'].'x '.htmlspecialchars($item['name']).'</span><form action="cancel_order_item.php" method="POST"><input type="hidden" name="order_item_id" value="'.$item['order_item_id'].'"><input type="hidden" name="order_id" value="'.$order['order_id'].'"><button type="submit" class="btn btn-link text-danger p-0 border-0"><i class="bi bi-x-circle-fill"></i></button></form></div>'; } ?></div>
                                    <div class="d-flex gap-2 mt-2">
                                        <form action="update_order.php" method="POST" class="w-100"><input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>"><input type="hidden" name="status" value="Preparing"><button type="submit" class="btn btn-sm btn-outline-warning w-100"><i class="bi bi-arrow-right"></i></button></form>
                                        <form action="update_order.php" method="POST" class="w-100"><input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>"><input type="hidden" name="status" value="Cancelled"><button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-x-circle"></i></button></form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Preparing column -->
                    <div class="col-md-4"><div class="order-column"><div class="order-card d-flex justify-content-between p-2 mb-3"><span><i class="bi bi-egg-fried"></i> Preparing</span><span><?php echo count($preparing_orders); ?></span></div><?php if (empty($preparing_orders)): ?><div class="text-center py-5 bg-white rounded">No orders</div><?php else: foreach ($preparing_orders as $order): ?><div class="bg-white p-3 rounded mb-2 shadow-sm border-start border-4 border-info"><div class="d-flex justify-content-between mb-2"><strong>Order #<?php echo $order['order_id']; ?></strong><span class="badge bg-info text-dark">৳<?php echo $order['total_amount']; ?></span><?php if (!empty($order['token_number'])): ?><span class="badge bg-dark ms-1"><?php echo $order['token_number']; ?></span><?php endif; ?></div><div class="mb-2"><?php if (isset($order_items[$order['order_id']])) foreach ($order_items[$order['order_id']] as $item) echo '<div class="d-flex justify-content-between small text-muted border-bottom py-1"><span>'.$item['quantity'].'x '.htmlspecialchars($item['name']).'</span><form action="cancel_order_item.php" method="POST"><input type="hidden" name="order_item_id" value="'.$item['order_item_id'].'"><input type="hidden" name="order_id" value="'.$order['order_id'].'"><button type="submit" class="btn btn-link text-danger p-0 border-0"><i class="bi bi-x-circle-fill"></i></button></form></div>'; ?></div><form action="update_order.php" method="POST"><input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>"><input type="hidden" name="status" value="Ready"><button type="submit" class="btn btn-sm btn-outline-info w-100">Move to Ready <i class="bi bi-arrow-right"></i></button></form></div><?php endforeach; endif; ?></div></div>
                    <!-- Ready column -->
                    <div class="col-md-4"><div class="order-column"><div class="order-card d-flex justify-content-between p-2 mb-3"><span><i class="bi bi-check-circle"></i> Ready</span><span><?php echo count($ready_orders); ?></span></div><?php if (empty($ready_orders)): ?><div class="text-center py-5 bg-white rounded">No orders</div><?php else: foreach ($ready_orders as $order): ?><div class="bg-white p-3 rounded mb-2 shadow-sm border-start border-4 border-success"><div class="d-flex justify-content-between mb-2"><strong>Order #<?php echo $order['order_id']; ?></strong><span class="badge bg-success">৳<?php echo $order['total_amount']; ?></span><?php if (!empty($order['token_number'])): ?><span class="badge bg-dark ms-1"><?php echo $order['token_number']; ?></span><?php endif; ?></div><div class="mb-2"><?php if (isset($order_items[$order['order_id']])) foreach ($order_items[$order['order_id']] as $item) echo '<div class="d-flex justify-content-between small text-muted border-bottom py-1"><span>'.$item['quantity'].'x '.htmlspecialchars($item['name']).'</span></div>'; ?></div><form action="update_order.php" method="POST"><input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>"><input type="hidden" name="status" value="Picked Up"><button type="submit" class="btn btn-sm btn-outline-success w-100">Mark Picked Up <i class="bi bi-check"></i></button></form></div><?php endforeach; endif; ?></div></div>
                </div>
            </div>

            <!-- NEW DYNAMIC MENU AVAILABILITY TAB (with AJAX toggles) -->
            <div class="tab-pane fade" id="pills-menu">
                <p class="text-muted small mb-4">Toggle items to mark them as available or unavailable. Changes take effect immediately for students.</p>
                <?php foreach ($categories as $cat_id => $cat_name): ?>
                    <div class="content-box p-3 mb-4 bg-white rounded shadow-sm">
                        <h6 class="fw-bold border-bottom pb-2 mb-3"><?php echo htmlspecialchars($cat_name); ?></h6>
                        <div class="row g-3">
                            <?php if (isset($items_by_cat[$cat_id])): ?>
                                <?php foreach ($items_by_cat[$cat_id] as $item): ?>
                                    <div class="col-md-4">
                                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white border rounded-3">
                                            <img src="../assets/images/<?php echo htmlspecialchars($item['image_url'] ?: 'default.jpg'); ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($cat_name); ?> • ৳<?php echo number_format($item['price'], 0); ?></small>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-availability" type="checkbox" data-item-id="<?php echo $item['item_id']; ?>" <?php echo $item['is_available'] ? 'checked' : ''; ?>>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-muted">No items in this category</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // AJAX to update availability when checkbox toggles
        document.querySelectorAll('.toggle-availability').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const itemId = this.dataset.itemId;
                const isAvailable = this.checked ? 1 : 0;
                fetch('update_availability.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'item_id=' + itemId + '&is_available=' + isAvailable
                })
                .then(response => response.text())
                .then(data => {
                    if (data !== 'success') {
                        alert('Error updating availability. Please try again.');
                        // revert checkbox to previous state
                        this.checked = !this.checked;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error. Please refresh and try again.');
                    this.checked = !this.checked;
                });
            });
        });
    </script>
</body>
</html>