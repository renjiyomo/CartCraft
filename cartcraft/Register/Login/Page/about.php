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

$title = "Terms of Service";
$lastUpdated = "09/15/24";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/about.css">
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
    
    <main>
        <h1>CartCraft: Curated Art for Every Taste</h1>
        <section class="info-main">
            <div class="abt">
            <p>CARTCRAFT is an art business through a website. It provides a platform for artists of all kinds—painters, sculptors and people who even do creative crafts—to showcase their work. We also have in-house artists and artworks beside the artists that entrusted us their skills and artworks.</p>
            </div>
        </section>

        <section class="info-section">
            <div class="info-card">
                <h2>Mission</h2>
                <p>Our mission is to inspire creativity and make the arts accessible to everyone. Also to empower and uplift emerging artists in the Philippines by providing an online platform that enables them to promote, sell, and gain recognition for their unique artistic creations. We are dedicated to supporting young talent, including people with disabilities, and creating an accessible space for art collectors and enthusiasts to discover and purchase original artworks. We aim to bridge the gap between artists and buyers, fostering a thriving art community that values creativity, inclusivity, and the artistic journey.</p>
            </div>
            <div class="info-card">
                <h2>Vision</h2>
                <p>We envision a thriving art community in the Philippines where all artists, including those with disabilities, are empowered to showcase and sell their work. Our platform will be a go-to space for art lovers to discover unique creations, helping to elevate the value of art and support artists in building sustainable careers. Through inclusivity and creativity, we have vision to foster a culture that appreciates and respects the artistic journey.</p>
            </div>
        </section>
    </main>

    <h2>Terms of Service</h2>
    <div class="container">
        <main class="main-content">
            <p class="last-updated">LAST UPDATED: <?php echo $lastUpdated; ?></p>
            <section class="terms-section">
                <p>Welcome to CartCraft! By using our website and purchasing any products or services from us, you agree to the following Terms of Service. Please read them carefully. If you do not agree with any of these terms, you may not access or use our website or services.</p>
                <h3>1. General Information</h3>
                <p>CartCraft operates <a href="customersPage.php" target="_blank">www.CartCraft.com</a>, an online platform for selling artwork, paints, and related services. By accessing or using our website, you agree to comply with and be bound by these Terms of Service, our Privacy Policy, and other policies or guidelines posted on the website.</p>
                <h3>2. Eligibility</h3>
                <p>By using our website, you confirm that you are at least 18 years old, or if you are a minor, you are accessing the website with the consent of your parent or legal guardian. You represent and warrant that all information provided by you is accurate and up-to-date.</p>
                <h3>3. Purchases and Payment</h3>
                <p>All orders placed on the website are subject to availability. We reserve the right to refuse or cancel any order for any reason. Prices for all products are listed on the website and are subject to change without notice.</p>
                <h3>4. Shipping and Delivery</h3>
                <p>Shipping costs and delivery times vary depending on your location and the product ordered. All shipping information will be provided during checkout.</p>
                <h3>5. Returns and Refunds</h3>
                <p>All sales of artwork are final, with no returns or refunds unless the product arrives damaged. You must contact us within 3 days with photographic evidence to arrange a replacement or refund.</p>
                <h3>6. Intellectual Property</h3>
                <p>All contents on the website, including images, text, and designs, are the property of CartCraft or its artists. You may not use, reproduce, or distribute any content without our express permission.</p>
            </section>
        </main>
    </div>
</body>
</html>
