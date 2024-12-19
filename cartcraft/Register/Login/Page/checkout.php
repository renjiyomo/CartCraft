<?php
session_start();
include 'cartcraft_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

$default_shipping_fee = 150.00;
$shipping_fee = $default_shipping_fee;

$selected_items = [];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {
    $selected_products = $_POST['selected_products'];

    if (!empty($selected_products)) {
        $product_ids = implode(',', array_map('intval', $selected_products));
        $sql = "
        SELECT p.product_id, p.product_name, p.product_image AS image, p.product_price AS price, 
            c.quantity, a.names AS artist_name, p.product_size  
        FROM cart AS c
        JOIN products AS p ON c.product_id = p.product_id
        JOIN artists AS a ON p.artist_id = a.artist_id
        WHERE c.user_id = ? AND p.product_id IN ($product_ids)
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $selected_items = $result->fetch_all(MYSQLI_ASSOC);
    }
} else {
    header("Location: cart.php");
    exit;
}

$subtotal = !empty($selected_items) ? array_reduce($selected_items, function($sum, $item) {
    return $sum + ($item['price'] * $item['quantity']);
}, 0) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['province'])) {
    $province = trim($_POST['province']);
    $shipping_rate_sql = "SELECT shipping_fee FROM shipping_rates WHERE region = ?";
    $stmt = $conn->prepare($shipping_rate_sql);
    $stmt->bind_param("s", $province);
    $stmt->execute();
    $shipping_result = $stmt->get_result();
    
    if ($shipping_result && $shipping_result->num_rows > 0) {
        $shipping_data = $shipping_result->fetch_assoc();
        $shipping_fee = $shipping_data['shipping_fee'];
    }
}

$total = $subtotal + $shipping_fee;

$gcash_sql = "SELECT gcash_name, gcash_number, gcash_qr_code FROM users WHERE user_id = 3";
$gcash_result = $conn->query($gcash_sql);

if ($gcash_result && $gcash_result->num_rows > 0) {
    $gcash_details = $gcash_result->fetch_assoc();
} else {
    $gcash_details = [
        'gcash_name' => 'N/A',
        'gcash_number' => 'N/A',
        'gcash_qr_code' => 'default_qr.png',
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="css/all.min.css">
    
    <script>
        function togglePaymentFields() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            document.getElementById('gcash-fields').style.display = paymentMethod === 'GCash' ? 'block' : 'none';
            document.getElementById('card-fields').style.display = paymentMethod === 'Card' ? 'block' : 'none';
        }

        window.onload = function () {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (paymentMethod) {
                togglePaymentFields();
            }
        };
    </script>
</head>
<body>
    <div class="checkout-container">
        <h2>Payment</h2>

        <?php if (!empty($selected_items)): ?>
            <div class="checkout-items">
                <?php foreach ($selected_items as $item): ?>
                    <div class="checkout-item">
                        <div class="product-image-container">
                            <img id="item-image" class="product-image" src="image/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        </div>
                        <div class="product-details">
                            <h3 class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <p  style="display: none;">Artist: <?php echo htmlspecialchars($item['artist_name']); ?></p>
                            
                            <p><?php echo htmlspecialchars($item['product_size']); ?></p>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p style="display: none;">Total: ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        </div>
                        <div class="product-price">
                            <p class="prod-price">₱ <?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="checkout-summary">
                
                <form action="confirm_order.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="order_details" value="<?php echo htmlspecialchars(json_encode($selected_items)); ?>">

                    
                    <div class="form-container">
                        <h3 class="deliver-title">Delivery Address</h3>
                        <div class="form-group full-row">
                            <input type="text" id="name" name="name" placeholder="Enter your name" required>
                        </div>
                        <div class="form-group row">
                            <input type="email" id="emails" name="emails" placeholder="Email Address">
                            <input type="text" id="contact_no" name="contact_no" placeholder="Contact Number" required>
                        </div>
                        <div class="form-group row">
                            <input type="text" id="street" name="street" placeholder="Street" required>
                            <input type="text" id="barangay" name="barangay" placeholder="Barangay" required>
                        </div>
                        <div class="form-group row">
                            <input type="text" id="municipality" name="municipality" placeholder="Municipality" required>
                            <select class="prov-ince" id="province" name="province" onchange="updateShippingFee()" required>
                                <option value="">Province</option>
                                <?php
                                $province_sql = "SELECT province FROM shipping_rates";
                                $province_result = $conn->query($province_sql);

                                if ($province_result && $province_result->num_rows > 0) {
                                    while ($row = $province_result->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['province']) . '">' . htmlspecialchars($row['province']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <input type="text" id="zip_code" name="zip_code" placeholder="Zip Code" required>
                        </div>
                    </div>


                    <h3 class="payment-title">Complete your Payment</h3>
                    <div class="form-method">

                        <label id="gcash-method" class="payment-method">
                            <input type="radio" name="payment_method" value="GCash" onclick="togglePaymentFields()" required checked> GCash
                        </label>
                        <label id="card-method" class="payment-method">
                            <input type="radio" name="payment_method" value="Card" onclick="togglePaymentFields()" required> Card Payment
                        </label>

                    </div>

                    <div class="form-gcash" id="gcash-fields" style="display:none;">
                        
                        <div class="gcash-details">
                            <p>Gcash No.:<strong></strong> <?php echo htmlspecialchars($gcash_details['gcash_number']); ?></p>
                            <p><strong></strong> <?php echo htmlspecialchars($gcash_details['gcash_name']); ?></p>
                        </div>

                        <div class="gcash-container">

                            <div class="gcash-details-left">
                                <div class="form-group">
                                    <input class="gcash-input" type="text" id="account_names" placeholder="Account Name" name="account_names">
                                </div>
                                <div class="form-group">
                                    <input class="gcash-input" type="text" id="account_number" placeholder="Account Number" name="account_number">
                                </div>
                                <div class="form-group">
                                    <input class="gcash-input" type="text" id="gcash_reference_number" placeholder="Ref No." name="gcash_reference_number">
                                </div>
                            </div>

                            <div class="gcash-qr-right">
                                <img src="GcashQR/<?php echo htmlspecialchars($gcash_details['gcash_qr_code']); ?>" alt="GCash QR Code" class="gcash-qr-image">
                            </div>

                        </div>

                        <div class="form-group row">
                            <input type="date" id="gcash_date" placeholder="Date" name="gcash_date">
                            <input type="time" id="gcash_time" placeholder="Time" name="gcash_time">
                        </div>

                        <div class="form-group">
                            <label for="gcash_proof" class="file-label">
                                <span id="file-placeholder">Proof of Payment</span>
                                <i class="fa-solid fa-circle-plus" style="font-size: 18px; margin-left: 8px; color: #f2d2ab; cursor: pointer;"></i>
                                <input 
                                    class="gcash_proof" 
                                    type="file" 
                                    id="gcash_proof" 
                                    name="gcash_proof" 
                                    accept="image/*" 
                                    onchange="updateFileName(this)"
                                >
                            </label>
                        </div>
                    </div>

                    <div class="form-card" id="card-fields" style="display:none;">

                        <div class="form-group long-row">
                            <input type="text" id="card_holder" placeholder="Card Holder Name" name="card_holder">
                        </div>

                        <div class="form-group long-row">
                            <input type="email" id="email_address" placeholder="Email Address" name="email_address">
                        </div>

                        <div class="form-group long-row">
                            <input type="text" id="card_number" placeholder="Card Number" name="card_number">
                        </div>

                        <div class="form-group row">
                            <input type="text" id="expiration_date" name="expiration_date" placeholder="Expiration Date MM/DD">
                            <input type="text" id="cvv_code" placeholder="CVV Code" name="cvv_code">
                        </div>
                    </div>
                    
                    <div class="total-box">
                        <p class="subtotal">Subtotal: ₱<?php echo number_format($subtotal, 2); ?></p>
                        <p class="shipping-fee">Total Shipping Fee: ₱<?php echo number_format($shipping_fee, 2); ?></p>
                        <p class="total-pay">Total: ₱<?php echo number_format($total, 2); ?></p>
                    </div>

                    <button type="submit" class="confirm-btn">Proceed with Payment</button>

                </form>
                
                <div class="proceed-note">
                        <p class="pay-note">By continuing, you accept to our <a class="terms-service" href="termsOFservices.php">Terms of Services and Policy.</a></p>
                        <p class="pay-note">Please note that payment are non-refundable.</p>
                </div>

            </div>
        <?php else: ?>
            <p>No items selected for checkout. <a href="cart.php">Return to Cart</a></p>
        <?php endif; ?>
    </div>

    <script>
      window.onload = function () {
    const paymentMethods = document.querySelectorAll('.payment-method');

    function updateSelection() {
        paymentMethods.forEach(method => method.classList.remove('selected'));
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (selectedMethod) {
            selectedMethod.parentElement.classList.add('selected');
        }
    }

    paymentMethods.forEach(method => {
        method.addEventListener('click', updateSelection);
    });

    document.querySelector('input[value="GCash"]').checked = true;
    togglePaymentFields();
    updateSelection();
};
    </script>

    <script>
        function updateFileName(input) {
    const placeholder = document.getElementById('file-placeholder');
    if (input.files && input.files.length > 0) {
        placeholder.textContent = input.files[0].name;
    } else {
        placeholder.textContent = 'Proof of Payment';
    }
}
    </script>

    <script>
function updateShippingFee() {
    const province = document.getElementById('province').value;

    if (province) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_shipping_fee.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
 
                    const formatter = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'PHP',
                        minimumFractionDigits: 2
                    });

                    const shippingFeeElement = document.querySelector('.total-box .shipping-fee');
                    shippingFeeElement.textContent = `Shipping Fee: ${formatter.format(response.shipping_fee)}`;

                    const totalElement = document.querySelector('.total-box .total-pay');
                    totalElement.textContent = `Total: ${formatter.format(response.total)}`;
                }
            }
        };
        xhr.send(`province=${encodeURIComponent(province)}&subtotal=${encodeURIComponent(<?php echo $subtotal; ?>)}`);
    }
}
    </script>

</body>
</html>
