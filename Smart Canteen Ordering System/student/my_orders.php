<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

$user_id = intval($_SESSION['user_id']);
$orders = [];
$sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_id DESC";
$result = mysqli_query($conn, $sql);
if ($result) { while ($row = mysqli_fetch_assoc($result)) $orders[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - UIU Smart Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_style.css">
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a class="home-button btn btn-light" href="dashboard.php"><i class="bi bi-arrow-left-circle"></i></a>
            <h2>My Orders</h2>
        </div>
    </div>

    <div class="container mt-4">
        <?php if (empty($orders)): ?>
            <div class="alert alert-info text-center">No orders yet. <a href="dashboard.php">Browse Menu</a></div>
        <?php else: ?>
            <?php foreach ($orders as $o): ?>
            <div class="bg-white p-3 rounded shadow-sm mb-3 border-start border-4 
                <?php echo $o['status'] == 'Pending' ? 'border-warning' : ($o['status'] == 'Preparing' ? 'border-info' : ($o['status'] == 'Ready' ? 'border-success' : 'border-secondary')); ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?php echo $o['order_id']; ?></strong>
                        <span class="badge bg-dark ms-2"><?php echo htmlspecialchars($o['token_number'] ?? 'N/A'); ?></span>
                    </div>
                    <span class="badge rounded-pill 
                        <?php echo $o['status'] == 'Pending' ? 'bg-warning text-dark' : ($o['status'] == 'Preparing' ? 'bg-info text-dark' : ($o['status'] == 'Ready' ? 'bg-success' : 'bg-secondary')); ?>">
                        <?php echo $o['status']; ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($o['order_date'])); ?></small>
                    <div>
                        <span class="fw-bold text-success me-3">৳<?php echo number_format($o['total_amount'], 0); ?></span>
                        <?php if ($o['status'] != 'Picked Up'): ?>
                        <a href="order_tracking.php?order_id=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">Track <i class="bi bi-geo-alt"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
