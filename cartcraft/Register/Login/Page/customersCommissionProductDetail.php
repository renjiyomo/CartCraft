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

$product_id = $_GET['id'];


if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action == 'accept') {
        $update_sql = "UPDATE products SET product_status = 'a' WHERE product_id = $product_id";
    } elseif ($action == 'decline') {
        $update_sql = "UPDATE products SET product_status = 'd' WHERE product_id = $product_id";
    }
    mysqli_query($conn, $update_sql);
    header("Location: adminProductDetail.php?id=$product_id");
    exit();
}

$sql = "
    SELECT p.*, a.names AS artist_name 
    FROM products p
    JOIN artists a ON p.artist_id = a.artist_id 
    WHERE p.product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

$stockQuantity = $product['quantity'];
$isOutOfStock = ($stockQuantity <= 0);


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

function getLikeCount($product_id, $conn) {
    $sql = "SELECT COUNT(*) AS like_count FROM likes WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['like_count'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/adminProductDetail.css">
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



<div class="container">
    <div class="image-container">
    <div class="product-type-text">
    <?php
    if ($product['product_status'] === 'p') {
        if ($product['product_type'] === 'f') {
            echo 'FIXED PRICE (PENDING)';
        } elseif ($product['product_type'] === 'c') {
            echo 'COMMISSION (PENDING)';
        } else {
            echo 'FOR BID (PENDING)';
        }
    } else {
        if ($product['product_type'] === 'f') {
            echo 'FIXED PRICE';
        } elseif ($product['product_type'] === 'c') {
            echo 'COMMISSION';
        } else {
            echo 'FOR BID';
        }
    }
    ?>
</div>
        <img src="image/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="product-image">

        <?php if ($product['product_status'] === 'p'): ?>
        <div class="action-buttons">
            <a href="adminProductDetail.php?id=<?php echo $product['product_id']; ?>&action=accept" class="accept-button">Accept</a>
            <a href="adminProductDetail.php?id=<?php echo $product['product_id']; ?>&action=decline" class="decline-button">Decline</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="info-container">

    <div class="like-container">
    <?php
    $sql_check = "SELECT * FROM likes WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $isLiked = $result->num_rows > 0;
    ?>
    <i class="<?php echo $isLiked ? 'fa-solid' : 'fa-regular'; ?> fa-heart" 
       id="like-button" 
       data-liked="<?php echo $isLiked ? 'true' : 'false'; ?>" 
       style="color: <?php echo $isLiked ? 'red' : 'black'; ?>; cursor: pointer;">
    </i>
    <span id="like-count"><?php echo getLikeCount($product_id, $conn); ?></span>
</div>

        <?php if ($product['product_type'] === 'b'): ?>
            <div class="auction-status" id="auction-status"></div> 
        <?php endif; ?>

        <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>

        <div class="info-grid">
        <p class="label">
    <?php echo $product['product_type'] === 'f' || $product['product_type'] === 'c' ? 'Price:' : 'Starting Price:'; ?>
</p>
<p class="centered-text">₱ <?php echo number_format($product['product_price'], 2); ?></p>


            <p class="label">Artist's Name:</p>
            <p class="centered-text"><?php echo htmlspecialchars($product['artist_name']); ?></p>

            <p class="label">Size:</p>
            <p class="centered-text"><?php echo htmlspecialchars($product['product_size']); ?></p>

            <p class="label">Medium:</p>
            <p class="centered-text"><?php echo htmlspecialchars($product['medium']); ?></p>
            
            <?php if ($product['product_type'] === 'b'): ?>
                <p class="label">Starting Date & Time:</p>
                <p class="centered-text">
                    <?php echo !empty($product['start_date']) ? date('F j, Y, g:i a', strtotime($product['start_date'])) : 'N/A'; ?>
                </p>

                <p class="label">End of Bidding:</p>
                <p class="centered-text">
                    <?php echo !empty($product['end_date']) ? date('F j, Y, g:i a', strtotime($product['end_date'])) : 'N/A'; ?>
                </p>
            <?php endif; ?>

            <p class="label">Quantity:</p>
                <?php if ($isOutOfStock): ?>
                    <p class="centered-text" style="color: red;">Out of Stock</p>
                <?php else: ?>
                    <div class="quantity-selector">
                        <button class="quantity-btn decrease" id="decrement" onclick="updateQuantity(-1)">-</button>
                        <input type="number" class="quantity" id="quantity-input" value="1" min="1" max="<?php echo $stockQuantity; ?>" readonly>
                        <button class="quantity-btn increase" id="increment" onclick="updateQuantity(1)">+</button>
                    </div>
                    <form action="commissionCheckouts.php" method="GET">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="quantity" id="form-quantity-input" value="1">
                        <button type="submit" class="button buy-now">Buy Now</button>
                    </form>
                <?php endif; ?>


            
        
            <?php if ($isOutOfStock): ?>
                <a class="button buy-now disabled" onclick="return false;">Buy Now</a>
                
            <?php else: ?>
            
                <form action="checkouts.php" method="GET">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <input style="display: none;" type="number" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" required>
                </form>

            <?php endif; ?>


            <p class="label" id="desc">Description:</p>
            <p class="descript"><?php echo htmlspecialchars($product['description']); ?></p>
        </div>
    </div>

    <script>
       function countdownTimer(startDate, endDate) {
            const auctionStatus = document.getElementById("auction-status");

            const now = new Date().getTime(); 
            const formatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            const start = new Date(formatter.format(new Date(startDate))).getTime(); 
            const end = new Date(formatter.format(new Date(endDate))).getTime(); 

            if (now >= start && now <= end) {
                auctionStatus.innerHTML = "Auction Open";
            } else if (now < start) {
                auctionStatus.innerHTML = "Auction Countdown";

                const interval = setInterval(() => {
                    const currentTime = new Date().getTime();
                    const distance = start - currentTime;

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    auctionStatus.innerHTML = ` ${days}d ${hours}h ${minutes}m ${seconds}s`;

                    if (distance < 0) {
                        clearInterval(interval);
                        auctionStatus.innerHTML = "Auction Open";
                    }
                }, 1000);
            } else {
                auctionStatus.innerHTML = "Auction Closed";
            }
        }

        const productType = "<?php echo $product['product_type']; ?>";

        if (productType === 'b') {
            const startDate = new Date("<?php echo $product['start_date']; ?>").toLocaleString('en-US', { timeZone: 'Asia/Manila' });
            const endDate = new Date("<?php echo $product['end_date']; ?>").toLocaleString('en-US', { timeZone: 'Asia/Manila' });

            if (startDate && endDate) {
                countdownTimer(startDate, endDate);
            }
        }
    </script>

    <script>
        const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', function () {

    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    this.textContent = type === 'password' ? '👁️' : '🙈';
});

function hideNotification() {
    document.querySelector('.overlay').style.display = 'none';
}
    </script>

    <script>
   document.getElementById('like-button').addEventListener('click', function () {
    const likeButton = this;
    const isLiked = likeButton.getAttribute('data-liked') === 'true';
    const productId = <?php echo $product_id; ?>;

    likeButton.classList.toggle('fa-regular', isLiked);
    likeButton.classList.toggle('fa-solid', !isLiked);
    likeButton.style.color = isLiked ? 'black' : 'red';
    likeButton.setAttribute('data-liked', !isLiked);

    fetch('handleLike.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            likeButton.style.color = data.liked ? 'red' : 'black';
            likeButton.setAttribute('data-liked', data.liked);
            const likeCount = document.getElementById('like-count');
            likeCount.textContent = parseInt(likeCount.textContent) + (data.liked ? 1 : -1);
        }
    })
    .catch(err => console.error('Error:', err));
});

    </script>

<script>
function updateQuantity(change) {
    const quantityInput = document.getElementById('quantity-input');
    const formQuantityInput = document.getElementById('form-quantity-input');
    const maxQuantity = <?php echo $stockQuantity; ?>;
    let currentQuantity = parseInt(quantityInput.value);

    currentQuantity += change;

    if (currentQuantity < 1) {
        currentQuantity = 1;
    } else if (currentQuantity > maxQuantity) {
        currentQuantity = maxQuantity;
    }

    quantityInput.value = currentQuantity;
    formQuantityInput.value = currentQuantity;
}
</script>

</div>

</body>
</html>
