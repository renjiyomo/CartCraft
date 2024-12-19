<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

include 'cartcraft_db.php';

// Validate and sanitize input
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

if ($order_id > 0 && $status === 'd') {
    // Update the order status
    $sql_update = "UPDATE orders SET status = ? WHERE order_id = ? AND users_id = ?";
    $stmt_update = $conn->prepare($sql_update); 
    $stmt_update->bind_param("sii", $status, $order_id, $_SESSION['user_id']);
    
    if ($stmt_update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update order status.']);
    }
    $stmt_update->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID or status.']);
}

$conn->close();
?>
