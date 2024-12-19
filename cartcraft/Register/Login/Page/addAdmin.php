<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'a') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

include 'cartcraft_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $names = $_POST['names'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone_number = $_POST['phone_number'];
    $user_type = $_POST['user_type'];

    $sql = "INSERT INTO users (names, email, password, phone_number, user_type, date_registered, user_status) VALUES (?, ?, ?, ?, ?, NOW(), 'a')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $names, $email, $password, $phone_number, $user_type);

    if ($stmt->execute()) {
        header("Location: usersList.php?success=Admin added successfully");
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
