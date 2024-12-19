<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

include 'cartcraft_db.php';

$order_id = $_GET['order_id'] ?? null;
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userImage = $user['image'];
$userName = $user['names'];

$sql_order = "
    SELECT co.*, a.names AS artist_name
    FROM commission_orders co
    LEFT JOIN artists a ON co.artists_id = a.artist_id
    WHERE co.commission_order_id = ? AND co.users_id = ?
";


$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $user_id);

$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

$orderReference = htmlspecialchars($order['order_reference_number']);


$orderDate = new DateTime($order['order_date']);
$orderShippedDate = new DateTime($order['order_shipped_time']);
$orderDeliveredDate = new DateTime($order['order_received']);

$expectedStartDate = $orderDate->modify('+6 days')->format('M d');
$expectedEndDate = $orderDate->modify('+1 day')->format('M d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/trackOrder.css">
    <link href="css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

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

<div class="track-header">TRACK ORDER</div>

<div class="track-order-container">

    <a class="back-history" href="customersPurchaseHistory.php?status=to_receive">← <span>To Receive</span></a>
    <p class="expected-delivery">Expected Delivery Date: <?= $expectedStartDate ?> - <?= $expectedEndDate ?></p>
    <div class="status-bar">

    <div class="status-icon <?= ($order['status'] == 'p' || $order['status'] == 's' || $order['status'] == 'd') ? 'active' : '' ?>">
        <i class="fa-solid fa-clipboard-list"></i>
        <p>Pending</p>
    </div>
    <div class="status-line <?= ($order['status'] == 's' || $order['status'] == 'd') ? 'active' : '' ?>"></div>

    <div class="status-icon <?= ($order['status'] == 's' || $order['status'] == 'd') ? 'active' : '' ?>">
        <i class="fa-solid fa-truck"></i>
        <p>Shipped</p>
    </div>
    <div class="status-line <?= ($order['status'] == 'd') ? 'active' : '' ?>"></div>

    <div class="status-icon <?= ($order['status'] == 'd') ? 'active' : '' ?>">
        <i class="fa-regular fa-circle-check"></i>
        <p>Delivered</p>
    </div>
</div>


<div class="order-details">
    <img src="image/<?= htmlspecialchars($order['product_image']) ?>" alt="<?= htmlspecialchars($order['product_name']) ?>">
    <div>
        <span>
            <p class="prod-name"><?= htmlspecialchars($order['product_name']) ?></p>
            <p class="ref-no"><?= $orderReference ?></p>
        </span>
        <button class="copy-button" onclick="copyOrderReference('<?= $orderReference ?>')">Copy</button>
    </div>
</div>

<div class="tracking-time-item">
    <?php if ($order['status'] == 'd' && $orderDeliveredDate) { ?>
        <div class="timeline-item <?= $order['status'] == 'd' ? '' : 'grey-text' ?>">
            <p><strong><?= $orderDeliveredDate->format('M d, Y h:i A') ?></strong></p>
            <div class="circle black"></div>
            <p>Your order has been delivered.</p>
        </div>
    <?php } ?>

    <?php if (($order['status'] == 's' || $order['status'] == 'd') && $orderShippedDate) { ?>
        <div class="timeline-item <?= $order['status'] == 's' ? '' : 'grey-text' ?>">
            <p><strong><?= $orderShippedDate->format('M d, Y h:i A') ?></strong></p>
            <div class="circle <?= $order['status'] == 's' ? 'black' : 'grey' ?>"></div>
            <p>Your order has been shipped.</p>
        </div>
    <?php } ?>

    <div class="timeline-item <?= $order['status'] == 'p' ? '' : 'grey-text' ?>">
        <p><strong><?= $orderDate->format('M d, Y h:i A') ?></strong></p>
        <div class="circle <?= $order['status'] == 'p' ? 'black' : 'grey' ?>"></div>
        <p>Order placed.</p>
    </div>
</div>


</div>

<script>

    function copyOrderReference(orderReference) {
        navigator.clipboard.writeText(orderReference).then(() => {
        });
    }

</script>

</body>
</html>