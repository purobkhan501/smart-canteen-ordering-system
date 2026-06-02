<?php
require_once '../includes/auth.php';
require_role('staff');
require_once '../includes/db.php';

// Fetch orders by status
$pending_orders = [];
$res = mysqli_query($conn, "SELECT * FROM orders WHERE status='Pending' ORDER BY order_id ASC");
if($res) { while($row = mysqli_fetch_assoc($res)) $pending_orders[] = $row; }

$preparing_orders = [];
$res = mysqli_query($conn, "SELECT * FROM orders WHERE status='Preparing' ORDER BY order_id ASC");
if($res) { while($row = mysqli_fetch_assoc($res)) $preparing_orders[] = $row; }

$ready_orders = [];
$res = mysqli_query($conn, "SELECT * FROM orders WHERE status='Ready' ORDER BY order_id ASC");
if($res) { while($row = mysqli_fetch_assoc($res)) $ready_orders[] = $row; }

// Picked up today
$picked_today_count = 0;
$today = date('Y-m-d');
// Assuming there is a created_at or updated_at column. We'll just count picked up orders for now.
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE status='Picked Up'");
if($res && $row = mysqli_fetch_assoc($res)) { $picked_today_count = $row['cnt']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - UIU Smart Canteen</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/staff.css">
</head>
<body>

    <header class="dashboard-header pb-5">
        <div class="container-fluid py-4 px-4">
            <div class="d-flex justify-content-between align-items-center text-white mb-4">
                <div>
                    <a class="home-button btn btn-light" href="../index.php" role="button"> <i class="bi bi-arrow-left-circle"></i></a>
                    <h4 class="d-inline-block ms-3 mb-0"><i class="bi bi-people"></i> Staff Dashboard <span class="badge bg-success rounded-pill status-dot">Live</span></h4>
                </div>
                <button class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-arrow-clockwise"></i></button>
            </div>

            <div class="row g-3 stats-row">
                <div class="col-md-3">
                    <div class="stat-card bg-primary-light">
                        <h2><?php echo count($pending_orders); ?></h2>
                        <p>Pending</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-primary-light">
                        <h2><?php echo count($preparing_orders); ?></h2>
                        <p>Preparing</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-primary-light">
                        <h2><?php echo count($ready_orders); ?></h2>
                        <p>Ready</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-primary-light">
                        <h2><?php echo $picked_today_count; ?></h2>
                        <p>Picked Today</p>
                    </div>
                </div>
            </div>

            <div class="nav-tabs-custom mt-4">
                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="pills-orders-tab" data-bs-toggle="pill" data-bs-target="#pills-orders">Orders</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="pills-menu-tab" data-bs-toggle="pill" data-bs-target="#pills-menu">Menu Availability</button>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="container-fluid py-4 px-4">
        <div class="tab-content" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-orders">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="order-column">
                            <div class="order-card d-flex justify-content-between p-2 mb-3">
                                <span><i class="bi bi-clock"></i> Pending</span>
                                <span><?php echo count($pending_orders); ?></span>
                            </div>
                            <?php if (empty($pending_orders)): ?>
                                <div class="text-center py-5 bg-white rounded">No orders</div>
                            <?php else: ?>
                                <?php foreach ($pending_orders as $order): ?>
                                <div class="bg-white p-3 rounded mb-2 shadow-sm border-start border-4 border-warning">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Order #<?php echo $order['order_id']; ?></strong>
                                        <span class="badge bg-warning text-dark">৳<?php echo $order['total_amount']; ?></span>
                                        <?php if (!empty($order['token_number'])): ?><span class="badge bg-dark ms-1"><?php echo $order['token_number']; ?></span><?php endif; ?>
                                    </div>
                                    <form action="update_order.php" method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="hidden" name="status" value="Preparing">
                                        <button type="submit" class="btn btn-sm btn-outline-warning w-100">Move to Preparing <i class="bi bi-arrow-right"></i></button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="order-column">
                            <div class="order-card d-flex justify-content-between p-2 mb-3">
                                <span><i class="bi bi-egg-fried"></i> Preparing</span>
                                <span><?php echo count($preparing_orders); ?></span>
                            </div>
                            <?php if (empty($preparing_orders)): ?>
                                <div class="text-center py-5 bg-white rounded">No orders</div>
                            <?php else: ?>
                                <?php foreach ($preparing_orders as $order): ?>
                                <div class="bg-white p-3 rounded mb-2 shadow-sm border-start border-4 border-info">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Order #<?php echo $order['order_id']; ?></strong>
                                        <span class="badge bg-info text-dark">৳<?php echo $order['total_amount']; ?></span>
                                        <?php if (!empty($order['token_number'])): ?><span class="badge bg-dark ms-1"><?php echo $order['token_number']; ?></span><?php endif; ?>
                                    </div>
                                    <form action="update_order.php" method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="hidden" name="status" value="Ready">
                                        <button type="submit" class="btn btn-sm btn-outline-info w-100">Move to Ready <i class="bi bi-arrow-right"></i></button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="order-column">
                            <div class="order-card d-flex justify-content-between p-2 mb-3">
                                <span><i class="bi bi-check-circle"></i> Ready</span>
                                <span><?php echo count($ready_orders); ?></span>
                            </div>
                            <?php if (empty($ready_orders)): ?>
                                <div class="text-center py-5 bg-white rounded">No orders</div>
                            <?php else: ?>
                                <?php foreach ($ready_orders as $order): ?>
                                <div class="bg-white p-3 rounded mb-2 shadow-sm border-start border-4 border-success">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Order #<?php echo $order['order_id']; ?></strong>
                                        <span class="badge bg-success">৳<?php echo $order['total_amount']; ?></span>
                                        <?php if (!empty($order['token_number'])): ?><span class="badge bg-dark ms-1"><?php echo $order['token_number']; ?></span><?php endif; ?>
                                    </div>
                                    <form action="update_order.php" method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="hidden" name="status" value="Picked Up">
                                        <button type="submit" class="btn btn-sm btn-outline-success w-100">Mark Picked Up <i class="bi bi-check"></i></button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-menu">
                <p class="text-muted small mb-4">Toggle items to mark them as available or unavailable. Unavailable items appear grayed out in the student menu.</p>
                <div class="row g-3" id="menu-items-container">

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/parata-dalvaji.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Parata with Dal-Vaji</h6>
                                <small class="text-muted">Breakfast • ৳40</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/egg-fry.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Egg Fry</h6>
                                <small class="text-muted">Breakfast • ৳20</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/singara.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Singara</h6>
                                <small class="text-muted">Snacks • ৳10</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/jilapi.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Jilapi</h6>
                                <small class="text-muted">Snacks • ৳10</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/puri.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Dal Puri</h6>
                                <small class="text-muted">Snacks • ৳10</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/cake.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Cake</h6>
                                <small class="text-muted">Snacks • ৳40</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/burger.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Burger</h6>
                                <small class="text-muted">Fastfood • ৳70</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/chicken-roll.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Chicken Roll</h6>
                                <small class="text-muted">Fastfood • ৳35</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/noodles.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Noodles</h6>
                                <small class="text-muted">Fastfood • ৳35</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/shawarma.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Shawarma</h6>
                                <small class="text-muted">Fastfood • ৳80</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/sandwich.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">SandWich</h6>
                                <small class="text-muted">Fastfood • ৳45</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/pizza.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Pizza Slice</h6>
                                <small class="text-muted">Fastfood • ৳50</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/hot-dog.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Hot Dog</h6>
                                <small class="text-muted">Fastfood • ৳60</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/fried-rice.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Fried Rice</h6>
                                <small class="text-muted">Rice • ৳85</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/rice-chicken.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Rice with Chicken</h6>
                                <small class="text-muted">Rice • ৳105</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/chicken-khichuri.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Chicken Khichuri</h6>
                                <small class="text-muted">Rice • ৳75</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/dim-khichuri.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Dim Khichuri</h6>
                                <small class="text-muted">Rice • ৳60</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/water.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Water</h6>
                                <small class="text-muted">Drinks • ৳20</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/mojo.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Mojo</h6>
                                <small class="text-muted">Drinks • ৳20</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/7up.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">& UP</h6>
                                <small class="text-muted">Drinks • ৳20</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/fanta.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Fanta</h6>
                                <small class="text-muted">Drinks • ৳20</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/tea.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Tea</h6>
                                <small class="text-muted">Drinks • ৳10</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/lemon-tea.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Lemon Tea</h6>
                                <small class="text-muted">Drinks • ৳10</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/coffee.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Coffee</h6>
                                <small class="text-muted">Drinks • ৳25</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="menu-item-card d-flex align-items-center p-2 shadow-sm bg-white">
                            <img src="../assets/images/black-coffee.jpg" class="rounded me-3" alt="food" id="img">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Black Coffee</h6>
                                <small class="text-muted">Drinks • ৳25</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input custom-switch" type="checkbox" checked>
                            </div>
                        </div>
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
