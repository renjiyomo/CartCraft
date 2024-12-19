<?php
include 'cartcraft_db.php';

if (isset($_GET['province'])) {
    $province = trim($_GET['province']);
    $response = ['success' => false, 'shipping_fee' => 150.00, 'total' => 0.00];

    // Fetch shipping fee based on province
    $sql = "SELECT shipping_fee FROM shipping_rates WHERE region = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $province);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $response['shipping_fee'] = $data['shipping_fee'];
    }

    // Calculate total
    $subtotal = 0; // Replace with actual subtotal calculation logic
    $response['total'] = $subtotal + $response['shipping_fee'];
    $response['success'] = true;

    echo json_encode($response);
    exit;
}
?>
