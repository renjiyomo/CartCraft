<?php
session_start();
include 'cartcraft_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_details'])) {
    $user_id = $_SESSION['user_id'];
    $order_details = json_decode($_POST['order_details'], true);

    // Delivery Address
    $names = $_POST['name'];
    $emails = $_POST['emails'];
    $phone_number = $_POST['contact_no'];
    $street = $_POST['street'];
    $barangay = $_POST['barangay'];
    $municipality = $_POST['municipality'];
    $province = $_POST['province'];
    $zip_code = $_POST['zip_code'];

    // Payment Details
    $payment_method = $_POST['payment_method'];
    $gcash_reference_number = $account_names = $account_number = $gcash_date = $gcash_time = $gcash_proof = null;
    $card_holder = $email_address = $card_number = $expiration_date = $cvv_code = null;

    if ($payment_method === 'GCash') {
        $gcash_reference_number = $_POST['gcash_reference_number'];
        $account_names = $_POST['account_names'];
        $account_number = $_POST['account_number'];
        $gcash_date = $_POST['gcash_date'];
        $gcash_time = $_POST['gcash_time'];

        if (isset($_FILES['gcash_proof']) && $_FILES['gcash_proof']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'GcashProof/';
            $gcash_proof = $upload_dir . basename($_FILES['gcash_proof']['name']);
            move_uploaded_file($_FILES['gcash_proof']['tmp_name'], $gcash_proof);
        } else {
            echo "<h2>GCash proof upload failed. Please try again.</h2>";
            exit;
        }
    } elseif ($payment_method === 'Card') {
        $card_holder = $_POST['card_holder'];
        $email_address = $_POST['email_address'];
        $card_number = $_POST['card_number'];
        $expiration_date = $_POST['expiration_date'];
        $cvv_code = $_POST['cvv_code'];
    } else {
        echo "<h2>Invalid payment method selected. Please try again.</h2>";
        exit;
    }

    // Fetch Shipping Fee
    $shipping_fee = 150.00;
    $stmt = $conn->prepare("SELECT shipping_fee FROM shipping_rates WHERE province = ?");
    $stmt->bind_param("s", $province);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $shipping_data = $result->fetch_assoc();
        $shipping_fee = $shipping_data['shipping_fee'];
    }

    // Total Order Calculation
    $total_order_amount = 0;
    foreach ($order_details as $item) {
        $product_price = $item['price'];
        $quantity = $item['quantity'];
        $total_order_amount += $item['price'] * $item['quantity'];
    }
    $final_total = $total_order_amount + $shipping_fee; // Calculate final total before inserting orders

    $conn->begin_transaction();

    try {
        foreach ($order_details as $item) {
            $product_id = $item['product_id'];
            $product_name = $item['product_name'];
            $product_price = $item['price'];
            $quantity = $item['quantity'];
            $artist_name = $item['artist_name'];

            $order_reference_number = strtoupper(uniqid(''));

            $stock_stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ? AND quantity >= ?");
            $stock_stmt->bind_param("ii", $product_id, $quantity);
            $stock_stmt->execute();
            $stock_result = $stock_stmt->get_result();

            if ($stock_result->num_rows === 0) {
                throw new Exception("Insufficient stock for product: $product_name");
            }

            $artist_stmt = $conn->prepare("SELECT artist_id FROM artists WHERE names = ?");
            $artist_stmt->bind_param("s", $artist_name);
            $artist_stmt->execute();
            $artist_result = $artist_stmt->get_result();
            $artist = $artist_result->fetch_assoc();
            $artist_id = $artist['artist_id'];

            if ($payment_method === 'GCash') {
                $stmt = $conn->prepare("INSERT INTO orders (order_reference_number, users_id, artists_id, product_name, product_price, quantity, names, emails, phone_number, street, barangay, municipality, province, zip_code, payment_method, gcash_reference_number, account_names, account_number, gcash_date, gcash_time, gcash_proof, shipping_fee, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("siisdissssssssssssssssd", $order_reference_number, $user_id, $artist_id, $product_name, $product_price, $quantity, $names, $emails, $phone_number, $street, $barangay, $municipality, $province, $zip_code, $payment_method, $gcash_reference_number, $account_names, $account_number, $gcash_date, $gcash_time, $gcash_proof, $shipping_fee, $final_total);
            } elseif ($payment_method === 'Card') {
                $stmt = $conn->prepare("INSERT INTO orders (order_reference_number, users_id, artists_id, product_name, product_price, quantity, names, emails, phone_number, street, barangay, municipality, province, zip_code, payment_method, card_holder, email_address, card_number, expiration_date, cvv_code, shipping_fee, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("siisdisssssssssssssssd", $order_reference_number, $user_id, $artist_id, $product_name, $product_price, $quantity, $names, $emails, $phone_number, $street, $barangay, $municipality, $province, $zip_code, $payment_method, $card_holder, $email_address, $card_number, $expiration_date, $cvv_code, $shipping_fee, $final_total);
            }
            $stmt->execute();

            $update_stock_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
            $update_stock_stmt->bind_param("ii", $quantity, $product_id);
            $update_stock_stmt->execute();

            if ($conn->affected_rows <= 0) {
                throw new Exception("Failed to update stock for product: $product_name");
            }
        }

        $conn->commit();
        $success = true;
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/confirmOrder.css">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    
</head>
<body>
    <div class="container">
        <?php if (isset($success) && $success): ?>
            <h2>Order Placed Successfully!</h2>
            <div class="summary">
                <p><strong>Order Reference Number:</strong> <?= htmlspecialchars($order_reference_number) ?></p>
                <p><strong>Product Name:</strong> <?= htmlspecialchars($item['product_name'] ?? 'N/A') ?></p>
                <p><strong>Product Price:</strong> ₱<?= number_format($item['price'] ?? 0, 2) ?></p>
                <p><strong>Quantity:</strong> <?= htmlspecialchars($item['quantity'] ?? 1) ?></p>
                <p><strong>Shipping Fee:</strong> ₱<?= number_format($shipping_fee, 2) ?></p>
                <p><strong>Total Amount:</strong> ₱<?= number_format($final_total, 2) ?></p>
            </div>
            <div class="proceed-button">
                <a href="customersPurchaseHistory.php">Go to Orders</a>
            </div>
        <?php elseif (isset($error_message)): ?>
            <h2>Order Failed</h2>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>