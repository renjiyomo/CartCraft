<?php
session_start();

if (!isset($_SESSION['artist_id']) || $_SESSION['user_type'] != 'c') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

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

// Total products by the logged-in artist
$totalProductsQuery = "SELECT COUNT(*) AS total_products FROM products WHERE artist_id = ?";
$stmt = $conn->prepare($totalProductsQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalProductsResult = $stmt->get_result();
$totalProducts = $totalProductsResult->fetch_assoc()['total_products'];

// Total orders for the logged-in artist
$totalOrdersQuery = "SELECT COUNT(*) AS total_orders FROM orders WHERE artists_id = ?";
$stmt = $conn->prepare($totalOrdersQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalOrdersResult = $stmt->get_result();
$totalOrders = $totalOrdersResult->fetch_assoc()['total_orders'];

$totalSalesQuery = "SELECT SUM(total) AS total_sales FROM orders WHERE artists_id = ? AND status = 'd'";
$stmt = $conn->prepare($totalSalesQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalSalesResult = $stmt->get_result();
$totalSales = $totalSalesResult->fetch_assoc()['total_sales'];

$topProductsQuery = "
    SELECT p.product_name, p.product_image, COUNT(o.order_id) AS products_sold
    FROM orders o
    JOIN products p ON o.product_name = p.product_name
    WHERE o.artists_id = ? AND o.status = 'd'
    GROUP BY p.product_id
    ORDER BY products_sold DESC
    LIMIT 3";
$stmt = $conn->prepare($topProductsQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$topProductsResult = $stmt->get_result();
$topProducts = $topProductsResult->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/dash.css">
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


<header>
    <section class="dashboard-content">
        <div class="welcome-message">
            <div class="welcome-text">WELCOME BACK, <?php echo htmlspecialchars($userName); ?></div>
        </div>
        <div class="metrics">
            <div class="metric">Total Products: <span class="try"><?php echo $totalProducts; ?></span></div>
            <div class="metric">Total Orders: <span class="try"><?php echo $totalOrders; ?></span></div>
            <div class="metric">Total Sales: <span class="try">₱ <?php echo number_format($totalSales, 2); ?></span></div>
        </div>
    </section>
</header>

<section class="top-artists">
    <h2>MY TOP 3 PRODUCTS</h2>
    <div class="artist-gallery-wrapper">
        <div class="artist-gallery" id="artistGallery">
            <?php foreach ($topProducts as $product): ?>
                <div class="artist">
                    <img src="image/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <div class="artist-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                    <div class="total-sales">Total Products Sold: <?php echo $product['products_sold']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="swipe-dots" id="swipeDots"></div>
</section>


</body>

<footer class="section__container footer__container" id="footer">
    <div class="footer__col">
      <h4>Creator</h4>
      <a href="#footer">Arevalo, Kristine Zyra Mae</a>
      <a href="#footer">Bautista, Madel Jandra</a>
      <a href="#footer">Serrano, Mark Erick</a>
    </div>

    <div class="footer__col">
      <h4>Bicol University</h4>
      <a href="#footer">Campus: Polangui</a>
      <a href="#footer">Course: BSIS</a>
      <a href="#footer">Year&Block: 3A</a>
    </div>

  </footer>

  <div class="footer__bar">
    Copyright © 2024 CARTCRAFT. All rights reserved.
  </div>


</html>

<?php
$conn->close();
?>
