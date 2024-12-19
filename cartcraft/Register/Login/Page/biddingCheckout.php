<?php
session_start();
include 'cartcraft_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

$product_id = $_GET['product_id'] ?? null;
$quantity = $_GET['quantity'] ?? 1; // Default to 1 if not provided

if ($product_id) {
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

$product = null; // Initialize the product variable

// Fetch product details if product_id is passed
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']); // Sanitize input
    $product_sql = "SELECT p.product_id, p.product_name, p.product_price, p.product_size, p.product_image, p.description, a.names AS artist_name 
                    FROM products p 
                    JOIN artists a ON p.artist_id = a.artist_id 
                    WHERE p.product_id = ?";
    $stmt = $conn->prepare($product_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<p>Product not found.</p>";
        exit;
    }
    

$highest_bid_sql = "SELECT MAX(bid_amount) AS highest_bid FROM bids WHERE product_id = ?";
$stmt = $conn->prepare($highest_bid_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$bid_result = $stmt->get_result();

if ($bid_result && $bid_result->num_rows > 0) {
    $highest_bid_data = $bid_result->fetch_assoc();
    $highest_bid = $highest_bid_data['highest_bid'] ?? 0;
} else {
    $highest_bid = 0;
}
} else {
echo "<p>No product selected for checkout.</p>";
exit;
}


$default_shipping_fee = 150.00;
$shipping_fee = $default_shipping_fee;

$selected_items = [];


$subtotal = $highest_bid * $quantity;


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

        <div class="checkout-items">
    <div class="checkout-item">
        <div class="product-image-container">
            <img id="item-image" class="product-image" src="image/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>
        <?php if ($product): ?>
        <div class="product-details">
            <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
            <p>Size: <?php echo htmlspecialchars($product['product_size']); ?></p>
            <p>Quantity: <?php echo htmlspecialchars($quantity); ?></p>
        </div>
        <div class="product-price">
                    <p class="prod-price">₱<?php echo number_format($highest_bid, 2); ?></p>
                </div>
        <?php else: ?>
        <p>Product not found.</p>
    <?php endif; ?>
    </div>
</div>
            <div class="checkout-summary">
                
                <form action="order_confirm.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="order_details" value="<?php echo htmlspecialchars(json_encode($selected_items)); ?>">
            
                    <input type="hidden" name="order_details" value='<?php echo json_encode([[
                        'product_id' => $product['product_id'],
                        'product_name' => $product['product_name'],
                        'price' => $highest_bid,
                        'quantity' => $quantity,
                        'artist_name' => $product['artist_name'],
                    ]]); ?>'>

                    
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
                                <span id="file-placeholder" class="proof-payment">Proof of Payment</span>
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
                            <input type="text" id="card_holder" placeholder="Card Holder" name="card_holder">
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
                        <p class="shipping-fee">Shipping Fee: ₱<?php echo number_format($shipping_fee, 2); ?></p>
                        <p class="total-pay">Total: ₱<?php echo number_format($total, 2); ?></p>
                    </div>


                    

                    <button type="submit" class="confirm-btn">Proceed Payment</button>
                </form>

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
                    // Format numbers with commas
                    const formatter = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'PHP',
                        minimumFractionDigits: 2
                    });

                    // Update the shipping fee
                    const shippingFeeElement = document.querySelector('.total-box .shipping-fee');
                    shippingFeeElement.textContent = `Shipping Fee: ${formatter.format(response.shipping_fee)}`;

                    // Update the total
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
