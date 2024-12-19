<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'a') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

include 'cartcraft_db.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userImage = $user['image'];
$userName = $user['names'];

$totalProductsQuery = "SELECT COUNT(*) AS total_products FROM products";
$totalProductsResult = $conn->query($totalProductsQuery);
$totalProducts = $totalProductsResult->fetch_assoc()['total_products'];

$totalCustomersQuery = "SELECT COUNT(*) AS total_customers FROM users WHERE user_type = 'u'";
$totalCustomersResult = $conn->query($totalCustomersQuery);
$totalCustomers = $totalCustomersResult->fetch_assoc()['total_customers'];

$totalArtistsQuery = "SELECT COUNT(*) AS total_artists FROM artists WHERE user_type = 'c'";
$totalArtistsResult = $conn->query($totalArtistsQuery);
$totalArtists = $totalArtistsResult->fetch_assoc()['total_artists'];

$totalOrdersQuery = "SELECT COUNT(*) AS total_orders FROM orders";
$totalOrdersResult = $conn->query($totalOrdersQuery);
$totalOrders = $totalOrdersResult->fetch_assoc()['total_orders'];

$totalSalesQuery = "SELECT SUM(total) AS total_sales FROM orders WHERE status = 'd'";
$totalSalesResult = $conn->query($totalSalesQuery);
$totalSales = $totalSalesResult->fetch_assoc()['total_sales'];

$topArtistsQuery = "
    SELECT a.names, a.image, COUNT(o.order_id) AS products_sold
    FROM orders o
    JOIN artists a ON o.artists_id = a.artist_id
    WHERE o.status = 'd'
    GROUP BY o.artists_id
    ORDER BY products_sold DESC
    LIMIT 3";
$topArtistsResult = $conn->query($topArtistsQuery);
$topArtists = $topArtistsResult->fetch_all(MYSQLI_ASSOC);
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
            <a href="dash.php">Dashboard</a>
            <a href="adminProduct.php">Products</a>
            <a href="usersList.php">User</a>
            <a href="artistsList.php">Artist</a>
            <a href="adminOrders.php">Orders</a>
            <a href="reports.php">Sales</a>

            <div class="profile-dropdown">
                <div class="profile">
                    <img src="image/<?php echo $userImage; ?>" alt="profile_pic" class="profile-pic">
                </div>
                <ul class="dropdown-content">
                    <li>
                        <span class="profile-name"><?php echo htmlspecialchars($userName); ?></span>
                    </li>
                    <li><a href="manageAccount.php">Manage Account</a></li>
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
                <div class="metric">Total Products: <span>&nbsp;</span> <span class="try"><?php echo $totalProducts; ?></span></div>
                <div class="metric">Total Customers: <span>&nbsp;</span> <span class="try"><?php echo $totalCustomers; ?></span></div>
                <div class="metric">Total Artists: <span>&nbsp;</span> <span class="try"><?php echo $totalArtists; ?></span></div>
                <div class="metric">Total Orders: <span>&nbsp;</span> <span class="try"><?php echo $totalOrders; ?></span></div>
                <div class="metric">Total Sales: <span>&nbsp;</span> <span class="try">₱ <?php echo number_format($totalSales, 2); ?></span></div>
            </div>
        </section>
    </header>

    <section class="top-artists">
    <h2>TOP 3 ARTISTS IN THE MARKET</h2>
    <div class="artist-gallery-wrapper">
        <div class="artist-gallery" id="artistGallery">
            <?php foreach ($topArtists as $artist): ?>
                <div class="artist">
                    <img src="image/<?php echo htmlspecialchars($artist['image']); ?>" alt="<?php echo htmlspecialchars($artist['names']); ?>">
                    <div class="artist-name"><?php echo htmlspecialchars($artist['names']); ?></div>
                    <div class="total-sales">Total Products Sold: <?php echo $artist['products_sold']; ?></div>
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
