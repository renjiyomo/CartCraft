<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

include 'cartcraft_db.php';

date_default_timezone_set('Asia/Manila');

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


$highestBidSql = "SELECT MAX(bid_amount) AS highest_bid, user_id FROM bids WHERE product_id = ?";
$stmt = $conn->prepare($highestBidSql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$highestBidData = $result->fetch_assoc();
$highestBid = $highestBidData['highest_bid'] ?? $product['product_price'];
$highestBidUserId = $highestBidData['user_id'] ?? null;

$currentTime = new DateTime();
$endDate = new DateTime($product['end_date']);
$auctionEnded = $currentTime > $endDate;

$isHighestBidder = $highestBidUserId == $user_id;

$isUserHighestBidder = false;

$bidderSql = "SELECT user_id FROM bids WHERE product_id = ? ORDER BY bid_amount DESC LIMIT 1";
$stmt = $conn->prepare($bidderSql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$highestBidder = $result->fetch_assoc()['user_id'] ?? null;
$isUserHighestBidder = $highestBidder == $user_id;

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
                    <div class="quantity-bid">
                        <p  class="centered-text" id="quantity-input" ><?php echo $stockQuantity; ?></p>
                    </div>
                <?php endif; ?>
        
                <?php if ($isOutOfStock): ?>
                    <a class="button buy-now disabled" onclick="return false;">Pay Now</a>
                <?php else: ?>
                    <form action="biddingCheckout.php" method="GET">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <button type="submit" class="button buy-now" <?php echo (!$auctionEnded || !$isHighestBidder) ? 'disabled' : ''; ?>>Pay Now</button>
                    </form>
                <?php endif; ?>

            <p class="label" id="desc">Description:</p>
            <p class="descript"><?php echo htmlspecialchars($product['description']); ?></p>
        </div>
    </div>
            </div>
    
    <div class="bid-container">
        <div class="bid-info">
            <p class="starting-bid"><strong>Starting Bid:</strong> ₱ <?php echo number_format($product['product_price'], 2); ?></p>
        <div class="current-bid-info">    
            <p class="cur-high"><strong>Current Highest Bid:</strong> 
                ₱ <?php 
                $highestBidSql = "SELECT MAX(bid_amount) AS highest_bid FROM bids WHERE product_id = ?";
                $stmt = $conn->prepare($highestBidSql);
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $highestBid = $result->fetch_assoc()['highest_bid'] ?? $product['product_price'];
                echo number_format($highestBid, 2);
                ?>
            </p>
             <p class="bid-der"><strong>Bidder:</strong> 
                <?php 
                    $bidderSql = "SELECT u.names FROM bids b JOIN users u ON b.user_id = u.user_id WHERE b.product_id = ? ORDER BY b.bid_amount DESC LIMIT 1";
                    $stmt = $conn->prepare($bidderSql);
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $bidderName = $result->fetch_assoc()['names'] ?? 'N/A';
                    echo htmlspecialchars($bidderName);
                ?>
            </p>
        </div>

        <p class="time-remain">
            <strong>Time remaining:</strong> <span class="time-remaining"></span>
        </p>
</div>

    <div class="bid-actions">
        <h3>Place Your Bid</h3>
        <div class="bid-buttons">
            <button>₱ 1,000.00</button>
            <button>₱ 5,000.00</button>
            <button>₱ 10,000.00</button>
            <button>₱ 20,000.00</button>
            <button>₱ 50,000.00</button>
            <button>₱ 100,000.00</button>
            <button>₱ 500,000.00</button>
            <button>₱ 1,000,000.00</button>
            <button>₱ 10,000,000.00</button>
        </div>
        <div class="custom-bid">
            <form class="custom-price" action="placeBid.php" method="POST" id="bid-form">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input class="place-bid" type="number" name="bid_amount" placeholder="Enter your bid" min="<?php echo $highestBid + 1; ?>" required>
                <button type="submit" class="submit-bid">+</button>
            </form>
        </div>


    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    function countdownTimer(startDate, endDate) {
        const auctionStatus = document.getElementById("auction-status");
        const timeRemaining = document.querySelector(".time-remaining");
        const now = new Date().getTime();

        const start = new Date(startDate).getTime();
        const formatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

        const end = new Date(endDate).getTime();

        if (now < start) {
            const diff = start - now;
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            auctionStatus.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
            if (timeRemaining) timeRemaining.innerHTML = "Opens on " + new Date(startDate).toLocaleString();
            disableBidding();
        } else if (now > end) {
            auctionStatus.innerHTML = "Auction Closed";
            if (timeRemaining) timeRemaining.innerHTML = "Auction has ended.";
            disableBidding();
            document.querySelector(".buy-now").disabled = !<?php echo json_encode($isUserHighestBidder); ?>;
        } else {
            auctionStatus.innerHTML = "Auction Open";
            if (timeRemaining) {
                const diff = end - now;
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                timeRemaining.innerHTML = `${days}d : ${hours}h : ${minutes}m : ${seconds}s`;
            }
        }
    }

    function disableBidding() {
        const bidForm = document.getElementById("bid-form");
        if (bidForm) bidForm.style.display = "none";

        const bidButtons = document.querySelectorAll(".bid-buttons button");
        bidButtons.forEach(button => button.disabled = true);
    }

    const startDate = "<?php echo $product['start_date']; ?>";
    const endDate = "<?php echo $product['end_date']; ?>";

    if (startDate && endDate) {
        countdownTimer(startDate, endDate);
        setInterval(() => countdownTimer(startDate, endDate), 1000);
    }
});

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

<script>
    document.querySelector('.submit-bid').addEventListener('click', function () {
    const bidInput = document.querySelector('.custom-bid input');
    const bidAmount = parseFloat(bidInput.value);
    const productId = <?php echo $product_id; ?>;

    if (!bidAmount || bidAmount <= 0) {
        alert('Please enter a valid bid amount.');
        return;
    }

    fetch('placeBid.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&bid_amount=${bidAmount}`,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);

            document.querySelector('.bid-info strong:nth-child(2)').textContent = `₱ ${bidAmount.toFixed(2)}`;

        } else {
            alert(data.message);  /
        }
    })
    .catch(err => console.error('Error:', err));
});

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const bidButtons = document.querySelectorAll(".bid-buttons button");
    const bidInput = document.querySelector(".custom-bid input[name='bid_amount']");

    bidButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const buttonPrice = parseFloat(button.textContent.replace(/[^\d.-]/g, ""));
            const currentInputValue = parseFloat(bidInput.value) || 0;
            bidInput.value = (currentInputValue + buttonPrice).toFixed(2);
        });
    });
});
</script>



</div>

</body>
</html>
