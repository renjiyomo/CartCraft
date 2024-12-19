<?php
session_start();
include 'cartcraft_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$action = $data['action'];
$user_id = $_SESSION['user_id'];

$cartStmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$cartStmt->bind_param("ii", $user_id, $product_id);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();
$cartItem = $cartResult->fetch_assoc();

$productStmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
$productStmt->bind_param("i", $product_id);
$productStmt->execute();
$productResult = $productStmt->get_result();
$productItem = $productResult->fetch_assoc();

$cart_quantity = (int)$cartItem['quantity'];
$product_stock = (int)$productItem['quantity'];

if ($action === 'increase') {
    if ($cart_quantity < $product_stock) {
        $cart_quantity++;
    } else {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
        exit;
    }
} elseif ($action === 'decrease') {
    if ($cart_quantity > 1) {
        $cart_quantity--;
    } else {
        echo json_encode(['success' => false, 'message' => 'Cannot decrease below 1']);
        exit;
    }
}

$updateCartStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
$updateCartStmt->bind_param("iii", $cart_quantity, $user_id, $product_id);
$updateCartStmt->execute();

echo json_encode(['success' => true, 'new_quantity' => $cart_quantity]);
?>
