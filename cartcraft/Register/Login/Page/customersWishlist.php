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



$sql = "SELECT p.product_id, p.product_name, p.product_price, p.product_image, 
               p.quantity, p.product_type, 
               CASE WHEN p.quantity > 0 THEN 'Available' ELSE 'Out of Stock' END AS stock_status 
        FROM likes l 
        JOIN products p ON l.product_id = p.product_id 
        WHERE l.user_id = ? 
        ORDER BY l.date_time_liked DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (isset($_SESSION['message'])) {
    echo "
    <div class='overlay'>
        <div class='notification-modal'>
            <p>" . $_SESSION['message'] . "</p>
            <button class='close-btn' onclick='hideNotification()'>OK</button>
        </div>
    </div>";
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/wishlist.css">
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

<div class="wishlist-container">
    <div class="wishlist-header">
        <h1><i class="fa-solid fa-heart"></i> My Wishlist</h1>
    </div>
    <table class="wishlist-table">
        <thead>
            <tr>
                <th></th>
                <th>Product Image</th>
                <th>Product Name</th>
                <th>Product Price</th>
                <th>Stock Status</th>
                <th></th>
            </tr>
        </thead>
         <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr id="wishlist-item-<?php echo $row['product_id']; ?>">
                    <td>
                        <div class="heart-icon">
                                <i class="fa-solid fa-heart like-button" 
                                data-product-id="<?php echo $row['product_id']; ?>"></i>
                        </div>
                    </td>
                    <td>
                        <div class="wishlist-item">
                            
                        <a href="<?php echo ($row['product_type'] == 'b') ? 'customersBidProductDetail.php' : 'customersProductDetail.php'; ?>?id=<?php echo $row['product_id']; ?>">
                            <img src="image/<?php echo $row['product_image']; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-image">
                        </a>
                        </div>
                    </td>

                    <td class="prod-detail"><?php echo $row['product_name']; ?></td>
                    <td class="prod-detail-price">₱<?php echo number_format($row['product_price'], 2); ?></td>
                    <td class="prod-detail"><?php echo $row['stock_status']; ?></td>
                    <td class="prod-detail-button">
                        <?php if ($row['stock_status'] == 'Available'): ?>
                            <a href="wishlistToCart.php?id=<?php echo $row['product_id']; ?>" class="button add-to-cart">Add to Cart</a>
                        <?php else: ?>
                            <button class="add-to-cart" disabled>Add to Cart</button>
                        <?php endif; ?>
                    </td>

                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
 

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');
            const isLiked = this.classList.contains('fa-solid');

            fetch('handleLike.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.classList.toggle('fa-solid', !isLiked);
                    this.classList.toggle('fa-regular', isLiked);
                    this.style.color = !isLiked ? 'red' : 'black';
                }
            })
            .catch(err => console.error('Error:', err));
        });
    });
});
</script>

<script>
function hideNotification() {
    const overlay = document.querySelector('.overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}
</script>


</body>
</html>
