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
    WHERE p.product_id = $product_id
";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);
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

<body>

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

            <p class="label">Quantity:</p>
            <p class="centered-text"><?php echo htmlspecialchars($product['quantity']); ?></p>

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

    </script>
</div>


</body>
</html>
