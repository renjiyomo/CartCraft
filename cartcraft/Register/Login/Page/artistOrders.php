<?php
session_start();
if (!isset($_SESSION['artist_id']) || $_SESSION['user_type'] != 'c') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

date_default_timezone_set('Asia/Manila');
include 'cartcraft_db.php';

$user_id = $_SESSION['artist_id'];
$sql = "SELECT * FROM artists WHERE artist_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userImage = $user['image'];
$userName = $user['names'];

$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'GCash';
$orderStatus = isset($_POST['order_status']) ? $_POST['order_status'] : 'p';

$sql = "SELECT * FROM orders WHERE payment_method = ? AND status = ? AND artists_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $paymentMethod, $orderStatus, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$salesQuery = "SELECT SUM(total) as total_sales FROM orders WHERE payment_method = ? AND artists_id = ?";
$salesStmt = $conn->prepare($salesQuery);
$salesStmt->bind_param("si", $paymentMethod, $user_id);
$salesStmt->execute();
$salesResult = $salesStmt->get_result();
$salesRow = $salesResult->fetch_assoc();
$totalSales = $salesRow['total_sales'] ? $salesRow['total_sales'] : 0;

if (isset($_POST['mark_shipped'])) {
    $order_id = $_POST['order_id'];
    $current_time = date("Y-m-d H:i:s");

    $updateStatusQuery = "UPDATE orders SET status = 's', order_shipped_time = ? WHERE order_id = ?";
    $updateStmt = $conn->prepare($updateStatusQuery);
    $updateStmt->bind_param("si", $current_time, $order_id);
    if ($updateStmt->execute()) {
        echo "<script>window.location.href = 'artistOrders.php';</script>";
    } else {
        echo "<script>alert('Failed to update order status. Please try again.');</script>";
    }
}


if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/artistOrders.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<nav>
    <header class="header">
        <a href="#" class="logo">
            <img class="craft" src="image/craft.png" alt="Logo">
        </a>

        <div class="burger" id="burger">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>

        <nav class="navbar" id="navbar">
            <a href="artistsPage.php">Home</a>
            <a href="artistProduct.php">Products</a>
            <a href="artistOrders.php">Orders</a>
            <a href="artistReports.php">Sales</a>

            <div class="profile-dropdown">
                <div class="profile">
                    <img src="image/<?php echo $userImage; ?>" alt="profile_pic" class="profile-pic">
                </div>
                <ul class="dropdown-content">
                    <li>
                        <span class="profile-name"><?php echo htmlspecialchars($userName); ?></span>
                    </li>
                    <li><a href="artistManageAccount.php">Manage Account</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>

            <div class="mobile-account-links">
                <a href="manageAccount.php">Manage Account</a>
                <a href="logout.php">Logout</a>
            </div>

        </nav>
    </header>
    <script>
        const burger = document.getElementById('burger');
        const navbar = document.getElementById('navbar');

        function checkScreenSize() {
            if (window.innerWidth > 1024) {
                navbar.classList.remove('hidden');
                navbar.classList.remove('active');
            } else {
                navbar.classList.add('hidden');
            }
        }

        burger.addEventListener('click', () => {
            navbar.classList.toggle('active');
            navbar.classList.toggle('hidden');
            burger.classList.toggle('active');
        });

        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();

        document.querySelector('.profile').addEventListener('click', function(event) {
            event.preventDefault();
            const dropdown = document.querySelector('.dropdown-content');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.dropdown-content');
            const isClickInside = event.target.closest('.profile-dropdown');

            if (!isClickInside) {
                dropdown.style.display = 'none';
            }
        });

        navbar.addEventListener('click', function(event) {
            if (event.target.tagName === 'A') {
                navbar.classList.remove('active');
                burger.classList.remove('active');
            }
        });
    </script>
</nav>

<body>
    <div class="container">
        <h1>ORDERS</h1>

        <div class="table-container">
        <div class="table-header">
        <form method="POST" action="">
    <div class="order-status-buttons">
        <button type="submit" name="order_status" value="p" class="<?php echo $orderStatus == 'p' ? 'active' : ''; ?>">Pending</button>
        <button type="submit" name="order_status" value="s" class="<?php echo $orderStatus == 's' ? 'active' : ''; ?>">Shipped</button>
        <button type="submit" name="order_status" value="d" class="<?php echo $orderStatus == 'd' ? 'active' : ''; ?>">Delivered</button>
        <div class="total-orders">Total Orders: <?php echo mysqli_num_rows($result); ?></div>
    </div>
    
      
    <div class="payment-sales-container">
    <div class="payment-method-container">
        <label for="payment_method">Select Payment Method:</label>
        <select name="payment_method" id="payment_method" onchange="this.form.submit()">
            <option value="GCash" <?php if ($paymentMethod == 'GCash') echo 'selected'; ?>>GCash</option>
            <option value="Card" <?php if ($paymentMethod == 'Card') echo 'selected'; ?>>Card Payment</option>
        </select>
    </div>

    <div class="total-sales">
    <h2>Total Sales: ₱ <?php echo number_format($totalSales, 2); ?></h2>
</div>
</div>

</form>
</div>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="user-table">
                <thead>
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Qty</th>
        <th>Customer Name</th>
        <th>Contact No</th>
        <th>Address</th>
        <th>Order Date</th>
        <th>Status</th>

        <?php if ($paymentMethod == 'GCash') : ?>
            <th>GCash Reference No</th>
            <th>Account Name</th>
            <th>Account No.</th>
        <?php elseif ($paymentMethod == 'Card') : ?>
            <th>Card Holder</th>
            <th>Email Address</th>
            <th>Card Number</th>
            <th>Expiration Date</th>
            <th>CVV Code</th>
        <?php endif; ?>
        <th>Total</th>
        <?php if ($orderStatus == 'p') : ?>
            <th>Action</th>
        <?php endif; ?>
    </tr>
</thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($result)) : ?>
    <tr>
        <td><?php echo $row['product_name']; ?></td>
        <td>₱ <?php echo $row['product_price']; ?></td>
        <td><?php echo $row['quantity']; ?></td>
        <td><?php echo $row['names']; ?></td>
        <td><?php echo $row['phone_number']; ?></td>
        <td>
            <?php 
                echo htmlspecialchars($row['street']) . ', ' .
                     htmlspecialchars($row['barangay']) . ', ' .
                     htmlspecialchars($row['municipality']) . ', ' .
                     htmlspecialchars($row['province']) . ', ' .
                     htmlspecialchars($row['zip_code']);
            ?>
        </td>
        <td><?php echo $row['order_date']; ?></td>
        <td><?php echo ($row['status'] == 'p' ? 'PENDING' : ($row['status'] == 's' ? 'SHIPPED' : 'DELIVERED')); ?></td>


        <?php if ($row['payment_method'] == 'GCash') : ?>
            <td><?php echo $row['gcash_reference_number']; ?></td>
            <td><?php echo $row['account_names']; ?></td>
            <td><?php echo $row['account_number']; ?></td>
        <?php elseif ($row['payment_method'] == 'Card') : ?>
            <td><?php echo $row['card_holder']; ?></td>
            <td><?php echo $row['email_address']; ?></td>
            <td><?php echo $row['card_number']; ?></td>
            <td><?php echo $row['expiration_date']; ?></td>
            <td><?php echo $row['cvv_code']; ?></td>
        <?php endif; ?>
        
        <td>₱ <?php echo $row['total']; ?></td>

        <?php if ($orderStatus == 'p') : ?>
            <td>
                <form method="POST" action="">
                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                    <button class="mark-ship" type="submit" name="mark_shipped">Shipped</button>
                </form>
            </td>
        <?php endif; ?>

    </tr>
<?php endwhile; ?>
</tbody>

                </table>
            <?php else: ?>
                <p class="no-orders">No orders found for this selection.</p>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>
