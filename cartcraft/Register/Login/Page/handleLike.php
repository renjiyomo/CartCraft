<?php
session_start();
include 'cartcraft_db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

$sql_check = "SELECT * FROM likes WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $sql_delete = "DELETE FROM likes WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'liked' => false]);
} else {
    $sql_insert = "INSERT INTO likes (user_id, product_id, date_time_liked) VALUES (?, ?, CURRENT_TIMESTAMP)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'liked' => true]);
}
?>
