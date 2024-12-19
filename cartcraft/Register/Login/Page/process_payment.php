<?php
session_start();
include 'cartcraft_db.php'; // Update the path as needed

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect input data
    $names = htmlspecialchars($_POST['names']);
    $email = htmlspecialchars($_POST['email']);
    $phone_number = htmlspecialchars($_POST['phone_number']);
    $street = htmlspecialchars($_POST['street']);
    $barangay = htmlspecialchars($_POST['barangay']);
    $municipality = htmlspecialchars($_POST['municipality']);
    $province = htmlspecialchars($_POST['province']);
    $zip_code = htmlspecialchars($_POST['zip_code']);
    $payment_method = htmlspecialchars($_POST['payment_method']);
    $total = htmlspecialchars($_POST['total']);

    // Process payment-specific details
    $gcash_reference_number = null;
    $account_names = null;
    $account_number = null;
    $card_holder = null;
    $email_address = null;
    $card_number = null;
    $expiration_date = null;
    $cvv_code = null;

    if ($payment_method === 'GCash') {
        $gcash_reference_number = htmlspecialchars($_POST['gcash_ref_no']);
        $account_names = htmlspecialchars($_POST['gcash_account_name']);
        $account_number = htmlspecialchars($_POST['gcash_account_number']);
    } elseif ($payment_method === 'Card Payment') {
        $card_holder = htmlspecialchars($_POST['card_holder']);
        $email_address = htmlspecialchars($_POST['card_email']);
        $card_number = htmlspecialchars($_POST['card_number']);
        $expiration_date = htmlspecialchars($_POST['expiration_date']);
        $cvv_code = htmlspecialchars($_POST['cvv_code']);
    }

    // Insert order into the database
    $sql = "
        INSERT INTO orders (
            users_id, artists_id, product_name, product_price, quantity, names,
            phone_number, street, barangay, municipality, province, zip_code, 
            order_date, status, payment_method, gcash_reference_number, account_names,
            account_number, card_holder, email_address, card_number, expiration_date,
            cvv_code, total
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'p', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing SQL: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "iisdssssssssssssssssssss",
        $user_id,
        $artist_id, // Update dynamically if needed
        $product_name, // From previous checkout step
        $product_price, // From previous checkout step
        $quantity, // From previous checkout step
        $names,
        $phone_number,
        $street,
        $barangay,
        $municipality,
        $province,
        $zip_code,
        $payment_method,
        $gcash_reference_number,
        $account_names,
        $account_number,
        $card_holder,
        $email_address,
        $card_number,
        $expiration_date,
        $cvv_code,
        $total
    );

    // Execute query
    if ($stmt->execute()) {
        echo "Order placed successfully!";
        header("Location: confirmation.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
