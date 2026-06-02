<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$is_new_order = isset($_GET['new']) && $_GET['new'] == '1';

// Fetch order details
$order = null;
if ($order_id > 0) {
    $sql = "SELECT * FROM orders WHERE order_id = $order_id AND user_id = " . intval($_SESSION['user_id']);
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
    }
}

if (!$order) {
    echo "<script>alert('Order not found!'); window.location.href='dashboard.php';</script>";
    exit();
}

// Fetch order items
$order_items = [];
$items_sql = "SELECT oi.*, mi.name, mi.image_url FROM order_item oi 
              JOIN menu_item mi ON oi.item_id = mi.item_id 
              WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_sql);
if ($items_result) {
    while ($row = mysqli_fetch_assoc($items_result)) {
        $order_items[] = $row;
    }
}

// Get token info for popup
$token_number = $order['token_number'] ?? 'N/A';
$total_amount = $order['total_amount'] ?? 0;

// Map status to step number for progress
$status_map = [
    'Pending' => 1,
    'Preparing' => 2,
    'Ready' => 3,
    'Picked Up' => 4
];
$current_step = $status_map[$order['status']] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking - UIU Smart Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f5; min-height: 100vh; }
        
        .tracking-header {
            background: linear-gradient(135deg, #AC2C0C 0%, #EB9604 100%);
            padding: 20px 0;
            color: white;
        }
        .tracking-header a { color: white; text-decoration: none; }

        /* Token Popup Modal */
        .token-modal-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: none;
            border-radius: 24px;
            overflow: hidden;
        }
        .token-glow {
            font-size: 4rem;
            font-weight: 800;
            color: #4ade80;
            text-shadow: 0 0 30px rgba(74, 222, 128, 0.5);
            letter-spacing: 4px;
        }
        .token-label {
            color: #94a3b8;
            font-size: 0.85rem;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .token-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #4ade80, transparent);
            margin: 15px 0;
        }
        .confetti-emoji { font-size: 2.5rem; }

        /* Progress Tracker */
        .tracker-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 40px 0;
        }
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 60px;
            right: 60px;
            height: 4px;
            background: #e2e8f0;
            z-index: 0;
        }
        .progress-line {
            position: absolute;
            top: 30px;
            left: 60px;
            height: 4px;
            background: linear-gradient(90deg, #4ade80, #22c55e);
            z-index: 1;
            transition: width 1s ease;
            border-radius: 2px;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        .step-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: #e2e8f0;
            color: #94a3b8;
            transition: all 0.5s ease;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .step.active .step-icon {
            background: linear-gradient(135deg, #4ade80, #22c55e);
            color: white;
            animation: pulse-ring 2s infinite;
        }
        .step.completed .step-icon {
            background: #22c55e;
            color: white;
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(74, 222, 128, 0); }
            100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
        }
        .step-label {
            margin-top: 12px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #94a3b8;
        }
        .step.active .step-label,
        .step.completed .step-label { color: #1e293b; }
        .step-time {
            font-size: 0.75rem;
            color: #cbd5e1;
            margin-top: 4px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status-Pending { background: #fef3c7; color: #92400e; }
        .status-Preparing { background: #dbeafe; color: #1e40af; }
        .status-Ready { background: #d1fae5; color: #065f46; }
        .status-Picked\ Up { background: #e0e7ff; color: #3730a3; }

        .status-dot-anim {
            width: 8px; height: 8px;
            background: currentColor;
            border-radius: 50%;
            animation: blink 1s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* Order Items Card */
        .items-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .item-row {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .item-row:last-child { border-bottom: none; }
        .item-row img {
            width: 50px; height: 50px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
        }

        /* Live indicator */
        .live-dot {
            display: inline-block;
            width: 10px; height: 10px;
            background: #22c55e;
            border-radius: 50%;
            margin-right: 6px;
            animation: blink 1.5s infinite;
        }
    </style>
</head>
<body>

<!-- Token Popup Modal -->
<?php if ($is_new_order): ?>
<div class="modal fade" id="tokenModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content token-modal-content text-center text-white p-4">
            <div class="modal-body py-4">
                <div class="confetti-emoji mb-2">🎉</div>
                <h4 class="fw-bold mb-1">Order Placed Successfully!</h4>
                <p class="text-muted mb-3" style="color: #94a3b8 !important;">Your food is being processed</p>
                
                <div class="token-divider"></div>
                
                <p class="token-label mt-3 mb-2">YOUR TOKEN NUMBER</p>
                <div class="token-glow"><?php echo htmlspecialchars($token_number); ?></div>
                
                <div class="token-divider"></div>
                
                <div class="d-flex justify-content-around mt-3 mb-3">
                    <div>
                        <small style="color: #94a3b8;">Order ID</small>
                        <div class="fw-bold">#<?php echo $order_id; ?></div>
                    </div>
                    <div>
                        <small style="color: #94a3b8;">Total</small>
                        <div class="fw-bold text-warning">৳<?php echo number_format($total_amount, 0); ?></div>
                    </div>
                </div>
                
                <p class="small mb-3" style="color: #64748b;">Please remember your token number for pickup</p>
                
                <button class="btn btn-success btn-lg rounded-pill px-5 fw-bold" data-bs-dismiss="modal">
                    <i class="bi bi-check-circle me-2"></i>Got It — Track My Order
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Header -->
<div class="tracking-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="dashboard.php"><i class="bi bi-arrow-left-circle fs-4"></i></a>
                <div>
                    <h5 class="mb-0 fw-bold">Order #<?php echo $order_id; ?></h5>
                    <small class="opacity-75">Token: <?php echo htmlspecialchars($token_number); ?></small>
                </div>
            </div>
            <div>
                <span class="live-dot"></span>
                <small class="fw-semibold">Live Tracking</small>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-4">
    
    <!-- Status Badge -->
    <div class="text-center mb-4">
        <span class="status-badge status-<?php echo $order['status']; ?>" id="statusBadge">
            <span class="status-dot-anim"></span>
            <span id="statusText"><?php echo $order['status']; ?></span>
        </span>
    </div>

    <!-- Progress Tracker -->
    <div class="tracker-container mb-4" id="trackerContainer">
        <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2"></i>Order Progress</h6>
        
        <div class="progress-steps">
            <?php
            $line_width_map = [1 => '0%', 2 => '33%', 3 => '66%', 4 => '100%'];
            $line_width = $line_width_map[$current_step];
            
            // Calculate the total width available (right edge - left edge = total - 120px margin)
            ?>
            <div class="progress-line" id="progressLine" style="width: calc((100% - 120px) * <?php echo $current_step <= 1 ? 0 : ($current_step - 1) / 3; ?>);"></div>
            
            <div class="step <?php echo $current_step >= 1 ? ($current_step > 1 ? 'completed' : 'active') : ''; ?>" data-step="1">
                <div class="step-icon"><i class="bi bi-receipt"></i></div>
                <span class="step-label">Order Placed</span>
                <span class="step-time"><?php echo date('h:i A', strtotime($order['order_date'])); ?></span>
            </div>
            <div class="step <?php echo $current_step >= 2 ? ($current_step > 2 ? 'completed' : 'active') : ''; ?>" data-step="2">
                <div class="step-icon"><i class="bi bi-fire"></i></div>
                <span class="step-label">Preparing</span>
                <span class="step-time" id="prepTime"><?php echo $current_step >= 2 ? 'In progress' : 'Waiting'; ?></span>
            </div>
            <div class="step <?php echo $current_step >= 3 ? ($current_step > 3 ? 'completed' : 'active') : ''; ?>" data-step="3">
                <div class="step-icon"><i class="bi bi-check2-all"></i></div>
                <span class="step-label">Ready</span>
                <span class="step-time" id="readyTime"><?php echo $current_step >= 3 ? 'Ready!' : 'Waiting'; ?></span>
            </div>
            <div class="step <?php echo $current_step >= 4 ? 'completed' : ''; ?>" data-step="4">
                <div class="step-icon"><i class="bi bi-bag-check"></i></div>
                <span class="step-label">Picked Up</span>
                <span class="step-time" id="pickupTime"><?php echo $current_step >= 4 ? 'Done!' : 'Waiting'; ?></span>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="items-card">
        <h6 class="fw-bold mb-3"><i class="bi bi-bag me-2"></i>Order Items</h6>
        <?php foreach ($order_items as $oi): ?>
        <div class="item-row">
            <img src="../assets/images/<?php echo htmlspecialchars($oi['image_url'] ?? 'default.jpg'); ?>" alt="">
            <div class="flex-grow-1">
                <div class="fw-semibold"><?php echo htmlspecialchars($oi['name']); ?></div>
                <small class="text-muted">৳<?php echo $oi['unit_price']; ?> × <?php echo $oi['quantity']; ?></small>
            </div>
            <div class="fw-bold">৳<?php echo $oi['unit_price'] * $oi['quantity']; ?></div>
        </div>
        <?php endforeach; ?>
        
        <hr>
        <div class="d-flex justify-content-between">
            <span class="fw-bold">Total</span>
            <span class="fw-bold text-success fs-5">৳<?php echo number_format($total_amount, 0); ?></span>
        </div>
    </div>

    <!-- Back to Menu -->
    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Back to Menu
        </a>
        <a href="my_orders.php" class="btn btn-outline-primary rounded-pill px-4 ms-2">
            <i class="bi bi-list-check me-2"></i>All My Orders
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
<?php if ($is_new_order): ?>
// Show token modal on page load
document.addEventListener('DOMContentLoaded', function() {
    var tokenModal = new bootstrap.Modal(document.getElementById('tokenModal'));
    tokenModal.show();
});
<?php endif; ?>

// Poll for status updates every 5 seconds
const orderId = <?php echo $order_id; ?>;
let currentStep = <?php echo $current_step; ?>;

function updateTracker(step, status) {
    if (step === currentStep) return;
    currentStep = step;

    const steps = document.querySelectorAll('.step');
    const progressLine = document.getElementById('progressLine');
    const statusBadge = document.getElementById('statusBadge');
    const statusText = document.getElementById('statusText');
    
    // Update progress line
    const linePercent = (step - 1) / 3;
    progressLine.style.width = 'calc((100% - 120px) * ' + linePercent + ')';
    
    // Update steps
    steps.forEach((el, i) => {
        el.classList.remove('active', 'completed');
        if (i + 1 < step) {
            el.classList.add('completed');
        } else if (i + 1 === step) {
            el.classList.add('active');
        }
    });
    
    // Update status badge
    statusBadge.className = 'status-badge status-' + status;
    statusText.textContent = status;

    // Update time labels
    if (step >= 2) document.getElementById('prepTime').textContent = 'In progress';
    if (step >= 3) {
        document.getElementById('prepTime').textContent = 'Done';
        document.getElementById('readyTime').textContent = 'Ready!';
    }
    if (step >= 4) {
        document.getElementById('readyTime').textContent = 'Done';
        document.getElementById('pickupTime').textContent = 'Done!';
    }
}

function pollStatus() {
    fetch('get_order_status.php?order_id=' + orderId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const statusMap = {'Pending': 1, 'Preparing': 2, 'Ready': 3, 'Picked Up': 4};
                const step = statusMap[data.status] || 1;
                updateTracker(step, data.status);
            }
        })
        .catch(() => {});
}

// Poll every 5 seconds
setInterval(pollStatus, 5000);
</script>

</body>
</html>
