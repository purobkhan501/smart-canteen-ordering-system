<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

// Turn on error reporting for debugging (remove after fixing)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize all variables to avoid "undefined variable" errors
$today_revenue = 0;
$today_orders = 0;
$total_revenue = 0;
$total_orders = 0;
$completed_orders = 0;
$avg_order_value = 0;
$available_items = 0;
$recent_orders = [];
$special_items = [];
$discounts = [];
$feedbacks = [];
$categories = [];
$menu_items_by_cat = [];
$all_menu_items = [];

// Fetch available items count
$item_count_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_item WHERE is_available = 1");
if ($item_count_res && $row = mysqli_fetch_assoc($item_count_res)) {
    $available_items = $row['count'];
}

// Fetch orders and calculate totals
$res = mysqli_query($conn, "SELECT * FROM orders");
if ($res) {
    $today = date('Y-m-d');
    while ($row = mysqli_fetch_assoc($res)) {
        $total_orders++;
        $total_revenue += $row['total_amount'];
        if ($row['status'] == 'Picked Up') {
            $completed_orders++;
        }
        
        $order_date = isset($row['order_date']) ? date('Y-m-d', strtotime($row['order_date'])) : $today;
        if ($order_date == $today) {
            $today_orders++;
            $today_revenue += $row['total_amount'];
        }
    }
}
if ($today_orders > 0) {
    $avg_order_value = $today_revenue / $today_orders;
}

// Fetch categories
$cat_res = mysqli_query($conn, "SELECT * FROM category");
if ($cat_res) {
    while ($cat = mysqli_fetch_assoc($cat_res)) {
        $categories[$cat['category_id']] = $cat['name'];
    }
}

// Fetch all menu items grouped by category
$menu_res = mysqli_query($conn, "SELECT * FROM menu_item");
if ($menu_res) {
    while ($item = mysqli_fetch_assoc($menu_res)) {
        $menu_items_by_cat[$item['category_id']][] = $item;
        $all_menu_items[] = $item;
    }
}

// Fetch recent orders (last 10)
// Fetch recent orders (last 10)
$recent_orders = [];
$recent_res = mysqli_query($conn, "SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_id DESC LIMIT 10");
if ($recent_res) {
    while ($order = mysqli_fetch_assoc($recent_res)) {
        $recent_orders[] = $order;
    }
}

// Now fetch feedback summary for those orders
$order_feedback = [];
if (!empty($recent_orders)) {
    // Build an array of order IDs for the IN clause
    $order_ids = array_column($recent_orders, 'order_id');
    $ids_str = implode(',', $order_ids);
    
   $fb_summary_res = mysqli_query($conn, "SELECT oi.order_id, AVG(f.rating) as avg_rating, COUNT(f.feedback_id) as feedback_count 
                                       FROM order_item oi 
                                       LEFT JOIN feedback f ON oi.order_item_id = f.order_item_id 
                                       WHERE oi.order_id IN ($ids_str)
                                       GROUP BY oi.order_id");
if ($fb_summary_res) {
    while ($row = mysqli_fetch_assoc($fb_summary_res)) {
        $avg = $row['avg_rating'];
        $order_feedback[$row['order_id']] = [
            'avg_rating' => !is_null($avg) ? round($avg, 1) : 0,
            'count' => $row['feedback_count']
        ];
    }
}
}

// ==============================================
// CORRECTED: Fetch special items with pre‑order totals
// ==============================================
$sp_res = mysqli_query($conn, "SELECT * FROM menu_item WHERE is_special = 1");
if ($sp_res) {
    while ($sp = mysqli_fetch_assoc($sp_res)) {
        // Get total pre-ordered quantity (sum) for this item
        $item_id = $sp['item_id'];
        $sum_query = mysqli_query($conn, "SELECT SUM(quantity) as total FROM pre_orders WHERE item_id = $item_id");
        $sum_row = mysqli_fetch_assoc($sum_query);
        $sp['current_preorders'] = $sum_row['total'] ?? 0;
        $special_items[] = $sp;
    }
}
// ==============================================

// Fetch discounts
$disc_res = mysqli_query($conn, "SELECT d.*, m.name, m.price, m.image_url FROM discount d JOIN menu_item m ON d.item_id = m.item_id WHERE d.is_active = 1");
if ($disc_res) {
    while ($d = mysqli_fetch_assoc($disc_res)) {
        $discounts[] = $d;
    }
}

// Fetch feedback
$fb_res = mysqli_query($conn, "SELECT f.*, u.name as customer_name, m.name as item_name, m.image_url 
                               FROM feedback f 
                               JOIN users u ON f.user_id = u.user_id 
                               JOIN order_item oi ON f.order_item_id = oi.order_item_id
                               JOIN menu_item m ON oi.item_id = m.item_id
                               ORDER BY f.created_at DESC LIMIT 10");
if ($fb_res) {
    while ($fb = mysqli_fetch_assoc($fb_res)) {
        $feedbacks[] = $fb;
    }
}
// Fetch all users with order statistics
$users = [];
$user_res = mysqli_query($conn, "SELECT u.*, 
                                         COUNT(o.order_id) as order_count,
                                         COALESCE(SUM(o.total_amount), 0) as total_spent,
                                         MAX(o.order_date) as last_order_date
                                  FROM users u
                                  LEFT JOIN orders o ON u.user_id = o.user_id
                                  GROUP BY u.user_id
                                  ORDER BY u.created_at DESC");
if ($user_res) {
    while ($u = mysqli_fetch_assoc($user_res)) {
        $users[] = $u;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
<?php if (isset($_GET['success']) && $_GET['success'] == 'item_added'): ?>
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">Menu item added successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif (isset($_GET['success']) && $_GET['success'] == 'item_deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">Menu item deleted successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif (isset($_GET['warning']) && $_GET['warning'] == 'has_orders'): ?>
    <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">Item has existing orders – marked as unavailable instead of deleted.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif (isset($_GET['error']) && $_GET['error'] == 'add_failed'): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">Failed to add item. Please try again.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
    <header class="header-nav py-3 fixed-top shadow-sm">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center text-white">
                <a class="home-button btn btn-light me-3" href="../index.php"><i class="bi bi-arrow-left-circle fs-5"></i></a>
                <h5 class="mb-0 fw-bold"><i class="bi bi-shield-check"></i> Admin Dashboard</h5>
            </div>
            <a href="../includes/logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
        
        <div class="container-fluid px-4 mt-3">
            
            <ul class="nav nav-pills custom-nav-pills" id="pills-tab" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#overview"><i class="bi bi-speedometer2"></i> Overview</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#menu"><i class="bi bi-shield"></i> Menu</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#discounts"><i class="bi bi-percent"></i> Discounts</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#promotions"><i class="bi bi-gift"></i> Promotions</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#special-menu"><i class="bi bi-star"></i> Special Menu</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#analytics"><i class="bi bi-bar-chart"></i> Analytics</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#users"><i class="bi bi-people"></i> Users</button></li>
            </ul>
        </div>
    </header>

    <main class="main-content container-fluid px-4">
        <div class="tab-content" id="pills-tabContent">

            <!-- OVERVIEW TAB -->
            <div class="tab-pane fade show active" id="overview">
                <h6 class="section-title mb-4 fw-bold"><i class="bi bi-activity text-primary"></i> Today's Summary</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stat-box p-3">
                            <div class="icon-circle bg-light-blue mb-2"><i class="bi bi-currency-dollar text-success"></i></div>
                            <h2 class="fw-bold mb-0">৳<?php echo number_format($today_revenue, 2); ?></h2>
                            <p class="text-muted small">Today's Revenue</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box p-3">
                            <div class="icon-circle bg-light-blue mb-2"><i class="bi bi-bag text-primary"></i></div>
                            <h2 class="fw-bold mb-0"><?php echo $today_orders; ?></h2>
                            <p class="text-muted small">Today's Orders</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box p-3">
                            <div class="icon-circle bg-light-blue mb-2"><i class="bi bi-bar-chart-steps text-purple"></i></div>
                            <h2 class="fw-bold mb-0">৳<?php echo number_format($avg_order_value, 2); ?></h2>
                            <p class="text-muted small">Avg Order Value</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box p-3">
                            <div class="icon-circle bg-light-blue mb-2"><i class="bi bi-x-lg text-danger"></i></div>
                            <h2 class="fw-bold mb-0">0</h2>
                            <p class="text-muted small">Cancelled Today</p>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="content-box p-4 h-100">
                            <h6 class="fw-bold mb-5">Top Selling Items</h6>
                            <div class="text-center py-5 text-muted">No orders yet</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="content-box p-4 h-100">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">All-time Summary</h6>
                            <div class="summary-list">
                                <div class="d-flex justify-content-between mb-2"><span>Total Revenue</span><span class="fw-bold">৳<?php echo number_format($total_revenue, 2); ?></span></div>
                                <div class="d-flex justify-content-between mb-2"><span>Total Orders</span><span class="fw-bold"><?php echo $total_orders; ?></span></div>
                                <div class="d-flex justify-content-between mb-2"><span>Completed Orders</span><span class="fw-bold"><?php echo $completed_orders; ?></span></div>
                                <div class="d-flex justify-content-between mb-2"><span>Tips Collected</span><span class="fw-bold">৳0</span></div>
                                <div class="d-flex justify-content-between"><span>Available Items</span><span class="fw-bold"><?php echo $available_items; ?></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="content-box p-4">
                            <h6 class="fw-bold mb-3">Recent Orders</h6>
                            <?php if (empty($recent_orders)): ?>
                                <div class="text-center py-5 text-muted">No orders yet</div>
                            <?php else: ?>
                                <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Token</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Feedback</th>   <!-- New column -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_orders as $ro): 
                $fb = $order_feedback[$ro['order_id']] ?? null;
                $feedback_html = '';
                if ($fb && $fb['count'] > 0) {
                    $stars = '';
                    $full = floor($fb['avg_rating']);
                    $half = ($fb['avg_rating'] - $full) >= 0.5;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $full) {
                            $stars .= '<i class="bi bi-star-fill text-warning"></i>';
                        } elseif ($half && $i == $full+1) {
                            $stars .= '<i class="bi bi-star-half text-warning"></i>';
                        } else {
                            $stars .= '<i class="bi bi-star text-muted"></i>';
                        }
                    }
                    $feedback_html = '<span title="' . $fb['avg_rating'] . ' stars based on ' . $fb['count'] . ' ratings">' . $stars . '</span>';
                    // Optional: add a link to view detailed feedback
                    $feedback_html .= '<br><a href="#" class="small" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-order-id="' . $ro['order_id'] . '">View comments</a>';
                } else {
                    $feedback_html = '<span class="text-muted">No ratings</span>';
                }
            ?>
            <tr>
                <td>#<?php echo $ro['order_id']; ?></td>
                <td><?php echo htmlspecialchars($ro['customer_name']); ?></td>
                <td><span class="badge bg-dark"><?php echo htmlspecialchars($ro['token_number'] ?: 'N/A'); ?></span></td>
                <td><small><?php echo date('M d, h:i A', strtotime($ro['order_date'])); ?></small></td>
                <td class="fw-bold text-success">৳<?php echo number_format($ro['total_amount'], 0); ?></td>
                <td><span class="badge rounded-pill <?php 
                    echo $ro['status'] == 'Pending' ? 'bg-warning text-dark' : 
                        ($ro['status'] == 'Preparing' ? 'bg-info text-dark' : 
                        ($ro['status'] == 'Ready' ? 'bg-success' : 'bg-secondary')); 
                ?>"><?php echo $ro['status']; ?></span></td>
                <td><?php echo $feedback_html; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MENU TAB (simplified) -->
            <div class="tab-pane fade" id="menu">
    <!-- Form to add new menu item -->
    <div class="content-box p-4 mb-4">
        <h6 class="fw-bold mb-3">➕ Add New Menu Item</h6>
        <form action="add_menu_item.php" method="POST" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Item Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Price (৳)</label>
                <input type="number" name="price" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($categories as $cat_id => $cat_name): ?>
                        <option value="<?php echo $cat_id; ?>"><?php echo htmlspecialchars($cat_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Image URL (filename)</label>
                <input type="text" name="image_url" class="form-control" placeholder="e.g., burger.jpg">
            </div>
            <div class="col-md-1">
                <label class="form-label small fw-bold">Available</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="is_available" value="1" checked>
                </div>
            </div>
            <div class="col-md-1">
                <label class="form-label small fw-bold">Special</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="is_special" value="1">
                </div>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-success rounded-pill px-4"><i class="bi bi-plus-lg"></i> Add Item</button>
            </div>
        </form>
    </div>

    <!-- Existing menu items with delete option -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h5 class="fw-bold mb-0">Menu Availability</h5><small class="text-muted"><?php echo $available_items; ?> items available</small></div>
        <div><button type="submit" form="menuForm" class="btn btn-success rounded-pill px-4"><i class="bi bi-save me-2"></i>Save Changes</button></div>
    </div>

    <form id="menuForm" action="update_menu.php" method="POST">
        <?php foreach ($categories as $cat_id => $cat_name): ?>
        <div class="content-box p-4 mb-4">
            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><?php echo htmlspecialchars($cat_name); ?></h6>
            <div class="row g-3">
                <?php if (isset($menu_items_by_cat[$cat_id])): ?>
                    <?php foreach ($menu_items_by_cat[$cat_id] as $item): ?>
                    <div class="col-md-4">
                        <div class="menu-item-card p-2 d-flex align-items-center border rounded-4">
                            <img src="../assets/images/<?php echo htmlspecialchars($item['image_url'] ?: 'default.jpg'); ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-0 small fw-bold"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">৳<?php echo number_format($item['price'], 0); ?></small>
                            </div>
                            <div class="form-check form-switch me-2">
                                <input type="hidden" name="all_items[]" value="<?php echo $item['item_id']; ?>">
                                <input class="form-check-input" type="checkbox" name="available_items[]" value="<?php echo $item['item_id']; ?>" <?php echo $item['is_available'] ? 'checked' : ''; ?>>
                            </div>
                            <!-- Delete button -->
                            <form action="delete_menu_item.php" method="POST" onsubmit="return confirm('Delete <?php echo addslashes($item['name']); ?>? This action cannot be undone.');">
                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-3">No items</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="text-end mb-5">
            <button type="submit" class="btn btn-success btn-lg rounded-pill px-5"><i class="bi bi-save me-2"></i>Save All Changes</button>
        </div>
    </form>
</div>

            <!-- DISCOUNTS TAB (simplified) -->
            <div class="tab-pane fade" id="discounts">
                <div class="content-box p-4 mb-4"><h6 class="fw-bold mb-3">Active Discounts</h6>
                <?php if(empty($discounts)): ?><div class="text-center py-4 text-muted">No active discounts</div>
                <?php else: ?>
                <div class="row g-3"><?php foreach($discounts as $d): ?>
                    <div class="col-md-4"><div class="menu-item-card p-3 border rounded-4 text-center position-relative">
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">-<?php echo $d['percentage']; ?>%</span>
                        <img src="../assets/images/<?php echo htmlspecialchars($d['image_url'] ?? 'default.jpg'); ?>" style="width:80px;height:80px;object-fit:cover;" class="rounded mb-2">
                        <h6 class="mb-1 small fw-bold"><?php echo htmlspecialchars($d['name']); ?></h6>
                        <p class="small text-muted mb-2"><del>৳<?php echo $d['price']; ?></del> <strong class="text-success">৳<?php echo number_format($d['price'] - ($d['price'] * $d['percentage']/100), 0); ?></strong></p>
                        <form action="remove_discount.php" method="POST"><input type="hidden" name="discount_id" value="<?php echo $d['discount_id']; ?>"><button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash"></i> Remove</button></form>
                    </div></div>
                <?php endforeach; ?></div><?php endif; ?></div>
                <div class="content-box p-4"><div class="d-flex justify-content-between align-items-center mb-4"><h6 class="fw-bold mb-0">Add Discount to Item</h6><button class="btn-custom btn btn-purple rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addDiscountModal">+ Add Discount</button></div></div>
            </div>

            <!-- Add Discount Modal -->
            <div class="modal fade" id="addDiscountModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Set Discount</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="set_discount.php" method="POST"><div class="modal-body"><div class="mb-3"><label class="form-label">Select Menu Item</label><select name="item_id" class="form-select" required><option value="">-- Choose Item --</option><?php foreach($all_menu_items as $mi): ?><option value="<?php echo $mi['item_id']; ?>"><?php echo htmlspecialchars($mi['name']); ?> (৳<?php echo $mi['price']; ?>)</option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Discount Percentage (%)</label><input type="number" name="percentage" class="form-control" required min="1" max="100"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-purple">Save Discount</button></div></form></div></div></div>

            <!-- PROMOTIONS TAB (placeholder) -->
            <div class="tab-pane fade" id="promotions"><div class="content-box p-4"><h6 class="fw-bold">Active Promotions (0)</h6><div class="text-center py-4 text-muted">No promotional items yet</div></div></div>

            <!-- SPECIAL MENU TAB -->
            <div class="tab-pane fade" id="special-menu">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Special Menu Items</h5>
                        <small class="text-muted">Set minimum pre-orders and available date for each special item</small>
                    </div>
                    <button class="btn-custom btn btn-orange rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addSpecialModal">+ Add Special Item</button>
                </div>

                <?php if(empty($special_items)): ?>
                    <div class="content-box p-4 text-center text-muted">No special items active.</div>
                <?php else: ?>
                    <?php foreach($special_items as $sp): 
                        // $sp already contains 'current_preorders' from the earlier fetch
                        $min_orders = $sp['min_preorders'] ?? 10;
                        $available_date = $sp['preorder_available_date'] ?? date('Y-m-d', strtotime('+1 day'));
                        $pct = ($min_orders > 0) ? min(100, ($sp['current_preorders'] / $min_orders) * 100) : 0;
                    ?>
                    <div class="content-box p-4 mb-3">
                        <form action="update_special.php" method="POST" class="d-flex align-items-center flex-wrap gap-3">
                            <input type="hidden" name="item_id" value="<?php echo $sp['item_id']; ?>">
                            
                            <img src="../assets/images/<?php echo htmlspecialchars($sp['image_url'] ?? 'default.jpg'); ?>" style="width:80px; height:80px; object-fit:cover; border-radius: 10px;">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($sp['name']); ?></h6>
                                <span>৳<?php echo number_format($sp['price'], 0); ?></span>
                            </div>
                            
                            <div>
                                <label class="small fw-bold">Min Pre-orders</label>
                                <input type="number" name="min_preorders" class="form-control" value="<?php echo $min_orders; ?>" min="1" required style="width: 100px;">
                            </div>
                            <div>
                                <label class="small fw-bold">Available Date</label>
                                <input type="date" name="preorder_available_date" class="form-control" value="<?php echo $available_date; ?>" required>
                            </div>
                            
                            <!-- Display current pre-order progress -->
                            <div class="text-center" style="min-width: 150px;">
                                <div class="small"><?php echo $sp['current_preorders']; ?> / <?php echo $min_orders; ?> pre-orders</div>
                                <div class="progress" style="height: 6px; width: 100px;">
                                    <div class="progress-bar bg-warning" style="width: <?php echo $pct; ?>%;"></div>
                                </div>
                            </div>
                            
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo $sp['is_special'] ? 'checked' : ''; ?>>
                                <label class="form-check-label">Active</label>
                            </div>
                            
                            <div>
                                <button type="submit" name="save_settings" class="btn btn-sm btn-success"><i class="bi bi-save"></i> Save</button>
                                <button type="submit" name="delete_special" class="btn btn-sm btn-outline-danger ms-2" onclick="return confirm('Remove special mark from this item?');"><i class="bi bi-trash"></i> Delete</button>
                            </div>
                        </form>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Add Special Modal -->
            <div class="modal fade" id="addSpecialModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Mark Item as Special</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="add_special.php" method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Select Menu Item</label>
                                    <select name="item_id" class="form-select" required>
                                        <option value="">-- Choose Item --</option>
                                        <?php
                                        $normal_items = mysqli_query($conn, "SELECT * FROM menu_item WHERE is_special = 0");
                                        while($mi = mysqli_fetch_assoc($normal_items)):
                                        ?>
                                        <option value="<?php echo $mi['item_id']; ?>"><?php echo htmlspecialchars($mi['name']); ?> (৳<?php echo $mi['price']; ?>)</option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Minimum Pre-orders Required</label>
                                    <input type="number" name="min_preorders" class="form-control" required min="1" value="10">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Available Date (must be at least tomorrow)</label>
                                    <input type="date" name="preorder_available_date" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-orange">Mark as Special</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ANALYTICS TAB (placeholder) -->
            <div class="tab-pane fade" id="analytics">
                <div class="row g-3 mb-4">
                    <div class="col-md-3"><div class="stat-box p-3 text-center"><small class="text-orange d-block mb-1">Peak Hour</small><h4 class="fw-bold text-orange">N/A</h4><small class="text-muted">0 orders</small></div></div>
                    <div class="col-md-3"><div class="stat-box p-3 text-center"><small class="text-orange d-block mb-1">Completion Rate</small><h4 class="fw-bold text-orange">0%</h4><small class="text-muted">0 completed</small></div></div>
                    <div class="col-md-3"><div class="stat-box p-3 text-center"><small class="text-orange d-block mb-1">Cancelled Rate</small><h4 class="fw-bold text-orange">0%</h4><small class="text-muted">0 orders</small></div></div>
                    <div class="col-md-3"><div class="stat-box p-3 text-center"><small class="text-orange d-block mb-1">Total Orders</small><h4 class="fw-bold text-orange">0</h4><small class="text-muted">All time</small></div></div>
                </div>
                <div class="content-box p-4 mb-4"><h6 class="fw-bold mb-5">Peak Hour Analysis</h6><div class="text-center py-5"><i class="bi bi-bar-chart-line text-muted opacity-50" style="font-size:3rem;"></i><p class="text-muted">Place orders to see analytics</p></div></div>
                <div class="col-md-12 mb-4"><div class="content-box p-4"><h6 class="fw-bold">Customer Feedback</h6><?php if(empty($feedbacks)): ?><div class="text-center py-5 text-muted">No feedback yet</div><?php else: ?><div class="list-group list-group-flush mt-3"><?php foreach($feedbacks as $fb): ?><div class="list-group-item px-0 py-3 d-flex"><img src="../assets/images/<?php echo htmlspecialchars($fb['image_url'] ?? 'default.jpg'); ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px;" class="me-3"><div class="flex-grow-1"><div class="d-flex justify-content-between align-items-center mb-1"><h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($fb['customer_name']); ?> <span class="text-muted fw-normal fs-6">rated</span> <?php echo htmlspecialchars($fb['item_name']); ?></h6><small class="text-muted"><?php echo date('M d, g:i A', strtotime($fb['created_at'])); ?></small></div><div class="text-warning mb-1"><?php for($i=1;$i<=5;$i++): ?><i class="bi bi-star<?php echo ($i <= $fb['rating']) ? '-fill' : ''; ?>"></i><?php endfor; ?></div><?php if(!empty($fb['comment'])): ?><p class="mb-0 small text-secondary">"<?php echo htmlspecialchars($fb['comment']); ?>"</p><?php endif; ?></div></div><?php endforeach; ?></div><?php endif; ?></div></div>
            </div>
            <!-- USERS TAB -->
<div class="tab-pane fade" id="users">
    <?php if (isset($_GET['success']) && $_GET['success'] == 'staff_added'): ?>
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        Staff account added successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif (isset($_GET['error']) && $_GET['error'] == 'duplicate'): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
        Username or email already exists. Please use a different one.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif (isset($_GET['error']) && $_GET['error'] == 'add_failed'): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
        Failed to add staff. Please try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
    <div class="content-box p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <button class="btn btn-primary btn-sm rounded-pill me-2" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="bi bi-person-plus"></i> Add Staff </button>
    </div>
       </div>
            <h5 class="fw-bold mb-0"><i class="bi bi-people-fill"></i> All Users</h5>
            
            <div>
                <input type="text" id="userSearch" class="form-control form-control-sm" placeholder="Search by name, email, username..." style="width: 250px;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="userTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Last Order</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <?php
                        $is_deleted = $u['is_deleted'] == 1;
                        $status_badge = $is_deleted 
                            ? '<span class="badge bg-danger">Deleted</span>' 
                            : '<span class="badge bg-success">Active</span>';
                        $action_text = $is_deleted ? 'Restore' : 'Deactivate';
                        $action_class = $is_deleted ? 'btn-outline-success' : 'btn-outline-danger';
                        $last_order = $u['last_order_date'] ? date('M d, Y', strtotime($u['last_order_date'])) : 'Never';
                        ?>
                        <tr>
                            <td><?php echo $u['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($u['email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td>
                                <span class="badge <?php echo $u['role'] == 'admin' ? 'bg-danger' : ($u['role'] == 'staff' ? 'bg-info' : 'bg-primary'); ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>
                            <td><?php echo $u['order_count']; ?></td>
                            <td>৳<?php echo number_format($u['total_spent'], 0); ?></td>
                            <td><?php echo $last_order; ?></td>
                            <td><small><?php echo date('M d, Y', strtotime($u['created_at'])); ?></small></td>
                            <td><?php echo $status_badge; ?></td>
                            <td>
                                <button class="btn btn-sm <?php echo $action_class; ?> toggle-user-status" data-user-id="<?php echo $u['user_id']; ?>" data-current-status="<?php echo $u['is_deleted']; ?>">
                                    <?php echo $action_text; ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>
<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-badge"></i> Add New Staff</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addStaffForm" action="add_staff.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>

        </div>
    </main>
     <!-- Modal for detailed feedback -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Feedback for Order <span id="modalOrderId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="feedbackModalBody">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
// Load feedback details via AJAX when modal opens
document.getElementById('feedbackModal').addEventListener('show.bs.modal', function(event) {
    var button = event.relatedTarget;
    var orderId = button.getAttribute('data-order-id');
    document.getElementById('modalOrderId').innerText = orderId;
    fetch('get_order_feedback.php?order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            var html = '';
            if (data.success && data.feedback.length) {
                data.feedback.forEach(fb => {
                    var stars = '';
                    for (var i = 1; i <= 5; i++) {
                        stars += i <= fb.rating ? '★' : '☆';
                    }
                    html += '<div class="border-bottom mb-2 pb-2">';
                    html += '<strong>' + fb.item_name + '</strong><br>';
                    html += '<span class="text-warning">' + stars + '</span> ';
                    html += '<small class="text-muted">(' + fb.rating + ')</small><br>';
                    html += '<em>"' + (fb.comment ? escapeHtml(fb.comment) : 'No comment') + '"</em><br>';
                    html += '<small class="text-muted">' + fb.created_at + '</small>';
                    html += '</div>';
                });
            } else {
                html = '<p class="text-muted">No feedback found for this order.</p>';
            }
            document.getElementById('feedbackModalBody').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('feedbackModalBody').innerHTML = '<p class="text-danger">Error loading feedback.</p>';
        });
});

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// Search filter
document.getElementById('userSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#userTable tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Toggle user status (activate/deactivate) via AJAX
document.querySelectorAll('.toggle-user-status').forEach(button => {
    button.addEventListener('click', function() {
        let userId = this.dataset.userId;
        let currentStatus = this.dataset.currentStatus;
        let newStatus = currentStatus == '1' ? '0' : '1';
        let actionText = newStatus == '1' ? 'Deactivate' : 'Restore';
        
        if (confirm('Are you sure you want to ' + actionText + ' this user?')) {
            fetch('toggle_user_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'user_id=' + userId + '&is_deleted=' + newStatus
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    location.reload(); // Reload to reflect changes
                } else {
                    alert('Error updating user status. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error.');
            });
        }
    });
});
</script>
</body>
</html>