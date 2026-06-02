<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

// Fetch analytics
$today = date('Y-m-d');
$total_revenue = 0;
$total_orders = 0;
$completed_orders = 0;
$today_revenue = 0;
$today_orders = 0;
$avg_order_value = 0;
$available_items = 0;
$item_count_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_item WHERE is_available = 1");
if ($item_count_res && $row = mysqli_fetch_assoc($item_count_res)) {
    $available_items = $row['count'];
}

// Since we don't know the exact date column (created_at vs date), we'll do a safe fallback
// We'll just fetch all orders and aggregate in PHP to avoid schema issues
$res = mysqli_query($conn, "SELECT * FROM orders");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $total_orders++;
        $total_revenue += $row['total_amount'];
        if ($row['status'] == 'Picked Up') {
            $completed_orders++;
        }
        
        // Use 'order_date' as verified in the schema
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

// Fetch all categories
$categories = [];
$cat_res = mysqli_query($conn, "SELECT * FROM category");
if ($cat_res) {
    while ($cat = mysqli_fetch_assoc($cat_res)) {
        $categories[$cat['category_id']] = $cat['name'];
    }
}

// Fetch all menu items grouped by category
$menu_items_by_cat = [];
$menu_res = mysqli_query($conn, "SELECT * FROM menu_item");
if ($menu_res) {
    while ($item = mysqli_fetch_assoc($menu_res)) {
        $menu_items_by_cat[$item['category_id']][] = $item;
    }
}
// Fetch recent orders (last 10)
$recent_orders = [];
$recent_res = mysqli_query($conn, "SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_id DESC LIMIT 10");
if ($recent_res) {
    while ($order = mysqli_fetch_assoc($recent_res)) {
        $recent_orders[] = $order;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>

    <header class="header-nav py-3 fixed-top shadow-sm">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center text-white">
                <a class="home-button btn btn-light me-3" href="../index.php" role="button"> <i class="bi bi-arrow-left-circle fs-5"></i></a>
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
            </ul>
        </div>
    </header>

    <main class="main-content container-fluid px-4">
        <div class="tab-content" id="pills-tabContent">

            
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_orders as $ro): ?>
                                            <tr>
                                                <td>#<?php echo $ro['order_id']; ?></td>
                                                <td><?php echo htmlspecialchars($ro['customer_name']); ?></td>
                                                <td><span class="badge bg-dark"><?php echo htmlspecialchars($ro['token_number'] ?: 'N/A'); ?></span></td>
                                                <td><small><?php echo date('M d, h:i A', strtotime($ro['order_date'])); ?></small></td>
                                                <td class="fw-bold text-success">৳<?php echo number_format($ro['total_amount'], 0); ?></td>
                                                <td>
                                                    <span class="badge rounded-pill <?php 
                                                        echo $ro['status'] == 'Pending' ? 'bg-warning text-dark' : 
                                                            ($ro['status'] == 'Preparing' ? 'bg-info text-dark' : 
                                                            ($ro['status'] == 'Ready' ? 'bg-success' : 'bg-secondary')); 
                                                    ?>">
                                                        <?php echo $ro['status']; ?>
                                                    </span>
                                                </td>
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



            <div class="tab-pane fade" id="menu">
                <form action="update_menu.php" method="POST">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-0">Menu Availability</h5>
                            <small class="text-muted"><?php echo $available_items; ?> items total</small>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="search-box">
                                <input type="text" class="form-control rounded-pill" placeholder="Search items...">
                            </div>
                            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm"><i class="bi bi-save me-2"></i>Save Changes</button>
                        </div>
                    </div>
                    
                    <?php foreach ($categories as $cat_id => $cat_name): ?>
                    <div class="content-box p-4 mb-4">
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><?php echo htmlspecialchars($cat_name); ?></h6>
                        <div class="row g-3 mb-4">
                            <?php 
                            if (isset($menu_items_by_cat[$cat_id])): 
                                foreach ($menu_items_by_cat[$cat_id] as $item):
                            ?>
                            <div class="col-md-4">
                                <div class="menu-item-card p-2 d-flex align-items-center border rounded-4 <?php echo $item['is_available'] ? '' : 'opacity-75 bg-light'; ?>">
                                    <img src="../assets/images/<?php echo htmlspecialchars($item['image_url'] ?: 'default.jpg'); ?>" class="rounded me-3" id="img1" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 small fw-bold"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <small class="text-muted">৳<?php echo number_format($item['price'], 0); ?></small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="all_items[]" value="<?php echo $item['item_id']; ?>">
                                        <input class="form-check-input" type="checkbox" name="available_items[]" value="<?php echo $item['item_id']; ?>" <?php echo $item['is_available'] ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                endforeach; 
                            else:
                            ?>
                            <div class="col-12 text-center text-muted py-3">No items in this category</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="text-end mb-5">
                        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow"><i class="bi bi-save me-2"></i>Save All Changes</button>
                    </div>
                </form>
            </div>
            </div>




            <div class="tab-pane fade" id="discounts">
                <div class="content-box p-4 mb-4">
                    <h6 class="fw-bold mb-3">Active Discounts</h6>
                    <div class="text-center py-4 text-muted">No active discounts</div>
                </div>
                <div class="content-box p-4">
                    <h6 class="fw-bold mb-4">Add Discount to Item</h6>
                    <div class="row g-3">

                        <div class="col-md-4">
                            <div class="menu-item-card p-3 border rounded-4 text-center">
                                <img src="../assets/images/parata-dalvaji.jpg" class="rounded mb-2" id="img2">
                                <h6 class="mb-1 small fw-bold">Parata with Dal-Vaji</h6>
                                <p class="small text-muted mb-2">৳40</p>
                                <button class="btn-custom btn btn-outline-purple btn-sm w-100">+ Set Discount</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="menu-item-card p-3 border rounded-4 text-center">
                                <img src="../assets/images/egg-fry.jpg" class="rounded mb-2" id="img2">
                                <h6 class="mb-1 small fw-bold">Egg Fry</h6>
                                <p class="small text-muted mb-2">৳20</p>
                                <button class="btn-custom btn btn-outline-purple btn-sm w-100">+ Set Discount</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="menu-item-card p-3 border rounded-4 text-center">
                                <img src="../assets/images/singara.jpg" class="rounded mb-2" id="img2">
                                <h6 class="mb-1 small fw-bold">singara</h6>
                                <p class="small text-muted mb-2">৳10</p>
                                <button class="btn-custom btn btn-outline-purple btn-sm w-100">+ Set Discount</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="menu-item-card p-3 border rounded-4 text-center">
                                <img src="../assets/images/jilapi.jpg" class="rounded mb-2" id="img2">
                                <h6 class="mb-1 small fw-bold">Jilapi</h6>
                                <p class="small text-muted mb-2">৳10</p>
                                <button class="btn-custom btn btn-outline-purple btn-sm w-100">+ Set Discount</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="menu-item-card p-3 border rounded-4 text-center">
                                <img src="../assets/images/puri.jpg" class="rounded mb-2" id="img2">
                                <h6 class="mb-1 small fw-bold">Dal Puri</h6>
                                <p class="small text-muted mb-2">৳10</p>
                                <button class="btn-custom btn btn-outline-purple btn-sm w-100">+ Set Discount</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="menu-item-card p-3 border rounded-4 text-center">
                                <img src="../assets/images/cake.jpg" class="rounded mb-2" id="img2">
                                <h6 class="mb-1 small fw-bold">Cake</h6>
                                <p class="small text-muted mb-2">৳40</p>
                                <button class="btn-custom btn btn-outline-purple btn-sm w-100">+ Set Discount</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="menu-item-card p-3 border rounded-4 text-center">
                                <img src="../assets/images/burger.jpg" class="rounded mb-2" id="img2">
                                <h6 class="mb-1 small fw-bold">Chicken Roll</h6>
                                <p class="small text-muted mb-2">৳70</p>
                                <button class="btn-custom btn btn-outline-purple btn-sm w-100">+ Set Discount</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="menu-item-card p-3 border rounded-4 text-center">
                                <img src="../assets/images/chicken-roll.jpg" class="rounded mb-2" id="img2">
                                <h6 class="mb-1 small fw-bold">Chicken Roll</h6>
                                <p class="small text-muted mb-2">৳35</p>
                                <button class="btn-custom btn btn-outline-purple btn-sm w-100">+ Set Discount</button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="menu-item-card p-3 border rounded-4 text-center">
                                <img src="../assets/images/noodles.jpg" class="rounded mb-2" id="img2">
                                <h6 class="mb-1 small fw-bold">Noodles</h6>
                                <p class="small text-muted mb-2">৳35</p>
                                <button class="btn-custom btn btn-outline-purple btn-sm w-100">+ Set Discount</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>




            <div class="tab-pane fade" id="promotions">
                <div class="content-box p-4 mb-4">
                    <h6 class="fw-bold">Active Promotions (0)</h6>
                    <div class="text-center py-4 text-muted">No promotional items yet</div>
                </div>
                <div class="content-box p-4">
                    <h6 class="fw-bold mb-4">Add Promotional Item</h6>
                    <form class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-bold">Item Name *</label><input type="text" class="form-control" placeholder="e.g. Special Combo Meal"></div>
                        <div class="col-md-6"><label class="form-label small fw-bold">Price (৳) *</label><input type="number" class="form-control" placeholder="e.g. 120"></div>
                        <div class="col-md-6"><label class="form-label small fw-bold">Category *</label>
                            <select class="form-select">
                                <option>Fast Food</option>
                                <option>Breakfast</option>
                                <option>Snacks</option>
                                <option>Rice</option>
                                <option>Drinks</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label small fw-bold">Promo Label</label><input type="text" class="form-control" placeholder="🎉 Special"></div>
                        <div class="col-md-12"><label class="form-label small fw-bold">Image URL *</label><input type="url" class="form-control" placeholder="https://..."></div>
                        <div class="col-md-12 mt-4"><button class="btn-custom btn btn-purple px-4 py-2"><i class="bi bi-plus"></i> Add Promotional Item</button></div>
                    </form>
                </div>
            </div>





            <div class="tab-pane fade" id="special-menu">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Special Pre-Order Menu</h5>
                        <small class="text-muted">Items available only when enough pre-orders are received</small>
                    </div>
                    <button class="btn-custom btn btn-orange rounded-pill px-4">+ Add Special Item</button>
                </div>
                <div class="content-box p-4 d-flex align-items-center mb-3">
                    <img src="../assets/images/Kacchi.jpg" class="rounded-4 me-4 shadow-sm" id="img3">
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Kacchi Biriyani</h6>
                        <p class="small text-muted mb-2">Premium Mutton Kacchi.Only made if 10+ pre-orders received!</p>
                        <div class="d-flex gap-3 small">
                            <span class="text-danger fw-bold">৳200</span>
                            <span><i class="bi bi-calendar"></i> 2026-05-7</span>
                            <span class="badge bg-success-light text-success px-3">Active</span>
                        </div>

                        <div class="pre-order-stats">
                            <span>0 / 10 pre-orders</span>
                        </div>
                        <div class="progress-bar">
                            <progress value="0" max="100" id="progress-bar"></progress>
                        </div>

                    </div>
                    <div class="text-end">
                        <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" checked></div>
                        <button class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                    </div>

                    
                </div>
            </div>




            <div class="tab-pane fade" id="analytics">
                <div class="row g-3 mb-4">
                    <div class="col-md-3"><div class="stat-box p-3 text-center"><small class="text-orange d-block mb-1">Peak Hour</small><h4 class="fw-bold text-orange">N/A</h4><small class="text-muted">0 orders</small></div></div>
                    <div class="col-md-3"><div class="stat-box p-3 text-center"><small class="text-orange d-block mb-1">Completion Rate</small><h4 class="fw-bold text-orange">0%</h4><small class="text-muted">0 completed</small></div></div>
                    <div class="col-md-3"><div class="stat-box p-3 text-center"><small class="text-orange d-block mb-1">Cancelled Rate</small><h4 class="fw-bold text-orange">0%</h4><small class="text-muted">0 orders</small></div></div>
                    <div class="col-md-3"><div class="stat-box p-3 text-center"><small class="text-orange d-block mb-1">Total Orders</small><h4 class="fw-bold text-orange">0</h4><small class="text-muted">All time</small></div></div>
                </div>
                <div class="content-box p-4 mb-4 h-100">
                    <h6 class="fw-bold mb-5">Peak Hour Analysis</h6>
                    <div class="text-center py-5">
                        <div class="mb-2"><i class="bi bi-bar-chart-line text-muted opacity-50" style="font-size: 3rem;"></i></div>
                        <p class="text-muted">Place orders to see analytics</p>
                    </div>
                </div>


                <div class="col-md-12 mb-4">
                    <div class="content-box p-4">
                        <h6 class="fw-bold">Revenue by Category</h6>
                        <div class="text-center py-5 text-muted">No completed orders yet</div>
                    </div>
                </div>

                <div class="col-md-12 mb-4">
                    <div class="content-box p-4">
                        <h6 class="fw-bold">Customer Feedback</h6>
                        <div class="text-center py-5 text-muted">No feedback yet</div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>
