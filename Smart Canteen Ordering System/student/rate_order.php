<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

$user_id = intval($_SESSION['user_id']);
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Verify order belongs to user and is Picked Up
$check_sql = "SELECT * FROM orders WHERE order_id = $order_id AND user_id = $user_id AND status = 'Picked Up'";
$check_res = mysqli_query($conn, $check_sql);
if (!$check_res || mysqli_num_rows($check_res) == 0) {
    header("Location: my_orders.php");
    exit();
}

$order_items = [];
$items_sql = "SELECT oi.*, m.name, m.image_url, 
              (SELECT rating FROM feedback f WHERE f.order_item_id = oi.order_item_id LIMIT 1) as existing_rating
              FROM order_item oi 
              JOIN menu_item m ON oi.item_id = m.item_id 
              WHERE oi.order_id = $order_id";
$items_res = mysqli_query($conn, $items_sql);
if ($items_res) {
    while ($row = mysqli_fetch_assoc($items_res)) {
        $order_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Order #<?php echo $order_id; ?> - UIU Smart Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_style.css">
    <style>
        .star-rating {
            direction: rtl;
            display: inline-block;
            padding: 20px;
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            color: #bbb;
            font-size: 1.5rem;
            padding: 0;
            cursor: pointer;
            transition: all .3s ease-in-out;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input[type="radio"]:checked ~ label {
            color: #f2b600;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a class="home-button btn btn-light" href="my_orders.php"><i class="bi bi-arrow-left-circle"></i></a>
            <h2>Rate Order #<?php echo $order_id; ?></h2>
        </div>
    </div>

    <div class="container mt-4">
        <div class="bg-white p-4 rounded shadow-sm">
            <h5 class="mb-4">How was your food?</h5>
            
            <?php foreach ($order_items as $item): ?>
            <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                <img src="../assets/images/<?php echo htmlspecialchars($item['image_url'] ?? 'default.jpg'); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" class="me-3">
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($item['name']); ?></h6>
                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                </div>
                <div>
                    <?php if ($item['existing_rating']): ?>
                        <div class="text-warning fs-5">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="bi bi-star<?php echo ($i <= $item['existing_rating']) ? '-fill' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <small class="text-success d-block text-end mt-1"><i class="bi bi-check-circle"></i> Rated</small>
                    <?php else: ?>
                        <form action="submit_feedback.php" method="POST" class="text-end">
                            <input type="hidden" name="order_item_id" value="<?php echo $item['order_item_id']; ?>">
                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                            <div class="star-rating mb-2">
                                <input type="radio" id="star5_<?php echo $item['order_item_id']; ?>" name="rating" value="5" required/><label for="star5_<?php echo $item['order_item_id']; ?>" class="bi bi-star-fill"></label>
                                <input type="radio" id="star4_<?php echo $item['order_item_id']; ?>" name="rating" value="4"/><label for="star4_<?php echo $item['order_item_id']; ?>" class="bi bi-star-fill"></label>
                                <input type="radio" id="star3_<?php echo $item['order_item_id']; ?>" name="rating" value="3"/><label for="star3_<?php echo $item['order_item_id']; ?>" class="bi bi-star-fill"></label>
                                <input type="radio" id="star2_<?php echo $item['order_item_id']; ?>" name="rating" value="2"/><label for="star2_<?php echo $item['order_item_id']; ?>" class="bi bi-star-fill"></label>
                                <input type="radio" id="star1_<?php echo $item['order_item_id']; ?>" name="rating" value="1"/><label for="star1_<?php echo $item['order_item_id']; ?>" class="bi bi-star-fill"></label>
                            </div>
                            <div>
                                <textarea name="comment" class="form-control form-control-sm mb-2" placeholder="Leave a comment (optional)"></textarea>
                                <button type="submit" class="btn btn-sm btn-orange w-100">Submit Rating</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="text-center mt-4">
                <a href="my_orders.php" class="btn btn-outline-secondary px-5 rounded-pill">Done</a>
            </div>
        </div>
    </div>
</body>
</html>
