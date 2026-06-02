<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/db.php';

header('Content-Type: application/json');

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$user_id = intval($_SESSION['user_id']);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit();
}

$sql = "SELECT status, token_number FROM orders WHERE order_id = $order_id AND user_id = $user_id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $order = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'status' => $order['status'],
        'token_number' => $order['token_number']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
}
?>
