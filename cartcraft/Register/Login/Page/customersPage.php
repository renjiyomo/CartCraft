<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
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


$topLikedProductsQuery = "
    SELECT p.product_id, p.product_name, p.product_image, a.names AS artist_name, COUNT(l.like_id) AS total_likes
FROM likes l
JOIN products p ON l.product_id = p.product_id
JOIN artists a ON p.artist_id = a.artist_id
WHERE p.product_status = 'a'
GROUP BY l.product_id
ORDER BY total_likes DESC
LIMIT 6";

$topLikedProductsResult = $conn->query($topLikedProductsQuery);
$mostLikedProducts = $topLikedProductsResult->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/customersPage.css">
    <link rel="stylesheet" href="css/customersNav.css">
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

<div class="fullscreen-bg">
    <h1>“Great things are<br>
    done by a series of<br>small things brought <br> together”</h1>
    <p class="artistName"> - Vincent van Gogh </p>
    <a class="explore-btn" href="customersProduct.php">
        <button class="explore-button">Explore</button>
    </a>
</div>

<section id="mostLikedProducts" class="most-liked-products">
    <h2>TRENDING ART WORKS</h2>
    <div class="product-grid">
        <?php foreach ($mostLikedProducts as $product): ?>
            <div class="product">
            <a href="customersProductDetail.php?id=<?php echo htmlspecialchars($product['product_id']); ?>">
            <img src="image/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </a>
                <div class="product-name">"<?php echo htmlspecialchars($product['product_name']); ?>" by <?php echo htmlspecialchars($product['artist_name']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

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

<script>
    const header = document.querySelector('.header');

window.addEventListener('scroll', () => {
    if (window.scrollY > 0) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

</script>

</body>
</html>

<?php
$conn->close();
?>
