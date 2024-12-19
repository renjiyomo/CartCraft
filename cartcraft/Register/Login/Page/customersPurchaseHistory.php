<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

include 'cartcraft_db.php';

$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$userImage = $user['image'];
$userName = $user['names'];

$orderId = isset($order['order_id']) ? $order['order_id'] : (isset($order['commission_order_id']) ? $order['commission_order_id'] : null);

$status = isset($_GET['status']) ? $_GET['status'] : 'all';

if ($status === 'to_pay') {
    // Query for "To Pay" status from commission_orders table
    $sql_orders = "
        SELECT co.*, p.product_image, p.product_size, p.product_id, a.names AS artist_name 
        FROM commission_orders co
        LEFT JOIN products p ON co.product_name = p.product_name
        LEFT JOIN artists a ON p.artist_id = a.artist_id
        WHERE co.users_id = ? AND co.total_payment_status = 'Pending'
        ORDER BY co.order_date DESC
    ";
} else {
    // Query for other statuses from orders table
    $status_condition = "";
    if ($status === 'to_ship') {
        $status_condition = "AND o.status = 'p'";
    } elseif ($status === 'to_receive') {
        $status_condition = "AND o.status = 's'";
    } elseif ($status === 'completed') {
        $status_condition = "AND o.status = 'd'";
    }

    $sql_orders = "
        SELECT o.*, p.product_image, p.product_size, p.product_id, a.names AS artist_name 
        FROM orders o
        LEFT JOIN products p ON o.product_name = p.product_name
        LEFT JOIN artists a ON p.artist_id = a.artist_id
        WHERE o.users_id = ? $status_condition
        ORDER BY o.order_date DESC
    ";
}

$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/purchaseHistory.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/all.min.css">
</head>
<body>

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
    <a href="customersPage.php">Home</a>
    <a href="customersProduct.php">Products</a>
    <a href="about.php">About</a>
    <a href="services.php">Service</a>
    <a href="cart.php" class="cart-icon">
        <i class="fa-solid fa-cart-shopping"></i>
    </a>

    <div class="profile-dropdown">
        <div class="profile">
            <img src="image/<?php echo $userImage; ?>" alt="profile_pic" class="profile-pic">
        </div>
        <ul class="dropdown-content">
            <li>
                <span class="profile-name"><?php echo htmlspecialchars($userName); ?></span>
            </li>
            <li><a href="customersManageAccount.php">Manage Account</a></li>
            <li><a href="customersPurchaseHistory.php">Purchase History</a></li>
            <li><a href="customersWishList.php">My Wishlist</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="mobile-account-links">
        <a href="customersManageAccount.php">Manage Account</a>
        <a href="customersPurchaseHistory.php">Purchase History</a>
        <a href="customersWishList.php">My Wishlist</a>
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

<div class="purchase-history">
    <h1>Purchase History</h1>
</div>

<div class="button-row">
    <div class="order-status">
        <a href="?status=all" class="<?= ($status === 'all') ? 'active' : '' ?>">All</a>
        <a href="?status=to_pay" class="<?= ($status === 'to_pay') ? 'active' : '' ?>">To Pay</a>
        <a href="?status=to_ship" class="<?= ($status === 'to_ship') ? 'active' : '' ?>">To Ship</a>
        <a href="?status=to_receive" class="<?= ($status === 'to_receive') ? 'active' : '' ?>">To Receive</a>
        <a href="?status=completed" class="<?= ($status === 'completed') ? 'active' : '' ?>">Completed</a>
    </div>
</div>

<div class="purchase-details">
    <?php if ($result_orders->num_rows > 0): ?>
        <?php while ($order = $result_orders->fetch_assoc()): ?>
            <?php
                $orderDate = new DateTime($order['order_date']);
                $expectedStartDate = $orderDate->modify('+6 days')->format('d M');
                $expectedEndDate = $orderDate->modify('+1 day')->format('d M');
            ?>
    <div class="purchase-detail">
    <div>
        <h3 class="artist-name"><?= htmlspecialchars($order['artist_name']) ?></h3>
        <p class="purchase-status">
            <?php 
            if ($order['status'] === 'p') {
                echo "Pending | To Ship";
            } elseif ($order['status'] === 's') {
                echo "Shipped | To Receive";
            } elseif ($order['status'] === 'd') {
                echo "Delivered | Completed";
            }
            ?>
        </p>
    </div>

    <div class="product-detail">
        <img src="image/<?= htmlspecialchars($order['product_image']) ?>" alt="Product Image">
        <div class="product-info">
            <p class="purchase-info-name"><?= htmlspecialchars($order['product_name']) ?></p>
            <p class="purchase-info"> <?= htmlspecialchars($order['product_size']) ?></p>
            <p class="purchase-info">x<?= htmlspecialchars($order['quantity']) ?></p>
        </div>
        <div class="product-price">
            <h4 class="price-item">₱ <?= number_format($order['product_price'], 2) ?></h4>
        </div>
    </div>

    <div class="total-prc">
        <h4 class="price-total">Total <?= htmlspecialchars($order['quantity']) ?> item: 
            <span class="total-red">₱ <?= number_format($order['total'], 2 ) ?></span>
        </h4>
    </div>

    <div class="delivery-info-row">
        <p class="expected-delivery">
            <strong>Expected Delivery:</strong> <?= $expectedStartDate ?> - <?= $expectedEndDate ?> 
            <?php if ($order['status'] === 'p'): ?>
                | Your package is being shipped out.
            <?php elseif ($order['status'] === 's'): ?>
                | Your order has been dispatched.
            <?php elseif ($order['status'] === 'd'): ?>
                | Your order has been delivered.
            <?php endif; ?>
        </p>
        <div class="order-buttons">
    <?php if ($order['status'] === 'p'): ?>
        <button class="order-received-btn" id="order-disable" style="opacity: 0.5; pointer-events: none;">Order Received</button>
    <?php elseif ($order['status'] === 's'): ?>
        <button class="order-received-btn" onclick="confirmOrderReceived(<?= $order['order_id'] ?>)">Order Received</button>
    <?php elseif ($order['status'] === 'd'): ?>
        <button class="product-detail-btn" onclick="viewProductDetail(<?= $order['product_id'] ?>)">Product Detail</button>
    <?php endif; ?>

    <?php if ($status === 'to_pay'): ?>
        <!-- Only show Commission Track Order button for 'to_pay' status -->
        <button class="track-order-btn" style="display: none;" onclick="trackOrder(<?= $order['order_id'] ?>)">Track Order</button>
        <a href="commissionTrackOrder.php?commission_order_id=<?= $order['commission_order_id'] ?>" class="track-order-btn">Commission Track Order</a>
    <?php else: ?>
        <!-- Show Track Order button for all other statuses -->
        <button class="track-order-btn" onclick="trackOrder(<?= $order['order_id'] ?>)">Track Order</button>
        <!-- Commission Track Order is hidden for all other statuses -->
        <button class="track-order-btn" style="display: none;" onclick="trackOrder(<?= $order['commission_order_id'] ?>)">Commission Track Order</button>
    <?php endif; ?>
</div>


    </div>
</div>



            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No orders found for this status.</p>
    <?php endif; ?>
</div>

<script>
function confirmOrderReceived(orderId) {
    if (confirm("Have you received your order?")) {

        fetch(`updateOrderReceived.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); 
                } else {
                    alert(data.message || "Failed to update the order status.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred. Please try again.");
            });
    }
}


function trackOrder(orderId) {
    window.location.href = `trackOrder.php?order_id=${orderId}`;
}

function viewProductDetail(productId) {
    window.location.href = `customersProductDetail.php?id=${productId}`;
}

</script>

</body>
</html>
