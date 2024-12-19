<?php
session_start();
include 'cartcraft_db.php';

if (!isset($_POST['product_id'], $_POST['user_id'], $_POST['bid_amount'])) {
    $_SESSION['message'] = "Invalid bid request.";
    header("Location: customersBidProductDetail.php?id=" . $_POST['product_id']);
    exit;
}

date_default_timezone_set('Asia/Manila');

$product_id = $_POST['product_id'];
$user_id = $_POST['user_id'];
$bid_amount = $_POST['bid_amount'];

$sql = "SELECT start_date, end_date FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Product not found.";
    header("Location: customersBidProductDetail.php?id=$product_id");
    exit;
}

$product = $result->fetch_assoc();
$current_time = date('Y-m-d H:i:s');
$start_date = $product['start_date'];
$end_date = $product['end_date'];

if ($current_time < $start_date) {
    $_SESSION['message'] = "The auction has not started yet.";
    header("Location: customersBidProductDetail.php?id=$product_id");
    exit;
}

if ($current_time > $end_date) {
    $_SESSION['message'] = "The auction has ended.";
    header("Location: customersBidProductDetail.php?id=$product_id");
    exit;
}

$sql = "INSERT INTO bids (product_id, user_id, bid_amount) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $product_id, $user_id, $bid_amount);

if ($stmt->execute()) {
    $_SESSION['message'] = "Bid placed successfully!";
} else {
    $_SESSION['message'] = "Failed to place bid.";
}

header("Location: customersBidProductDetail.php?id=$product_id");
?>
