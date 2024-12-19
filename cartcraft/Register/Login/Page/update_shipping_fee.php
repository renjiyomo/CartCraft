<?php
include 'cartcraft_db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['province'], $_POST['subtotal'])) {
    $province = trim($_POST['province']);
    $subtotal = floatval($_POST['subtotal']);
    $response = ['success' => false];

    $stmt = $conn->prepare("SELECT shipping_fee FROM shipping_rates WHERE province = ?");
    $stmt->bind_param("s", $province);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $shipping_fee = floatval($data['shipping_fee']);
        $total = $subtotal + $shipping_fee;

        $response = [
            'success' => true,
            'shipping_fee' => $shipping_fee,
            'total' => $total
        ];
    }

    echo json_encode($response);
}
?>
