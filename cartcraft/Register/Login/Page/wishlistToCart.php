<?php
session_start();
include('cartcraft_db.php');

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$quantity_to_add = 1;

$product_query = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
$product_query->bind_param("i", $product_id);
$product_query->execute();
$product_result = $product_query->get_result();

if ($product_result->num_rows > 0) {
    $product = $product_result->fetch_assoc();
    $available_stock = intval($product['quantity']);

    $cart_query = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $cart_query->bind_param("ii", $user_id, $product_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();

    if ($cart_result->num_rows > 0) {
        $cart_item = $cart_result->fetch_assoc();
        $current_cart_quantity = intval($cart_item['quantity']);

        if ($current_cart_quantity + $quantity_to_add > $available_stock) {
            $_SESSION['message'] = "Sorry, you exceed the quantity of available stock in your Cart";
        } else {
            $new_quantity = $current_cart_quantity + $quantity_to_add;
            $update_cart_query = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $update_cart_query->bind_param("iii", $new_quantity, $user_id, $product_id);
            if ($update_cart_query->execute()) {
                $_SESSION['message'] = "Quantity updated in cart";
            }
        }
    } else {
        if ($quantity_to_add > $available_stock) {
            $_SESSION['message'] = "Quantity exceeds available stock";
        } else {
            $insert_cart_query = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, date_added) VALUES (?, ?, ?, NOW())");
            $insert_cart_query->bind_param("iii", $user_id, $product_id, $quantity_to_add);
            if ($insert_cart_query->execute()) {
                $_SESSION['message'] = "Product added to cart";
            }
        }
    }
} else {
    $_SESSION['message'] = "Product not found.";
}

header("Location: customersWishlist.php?id=$product_id");
exit();
?>
