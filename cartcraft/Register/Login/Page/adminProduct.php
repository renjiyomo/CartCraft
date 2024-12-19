<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'a') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

include 'cartcraft_db.php';

$productType = 'f'; 
$productStatus = 'a'; 
$selectedMedium = '';
$title = 'FIXED PRICE';

$userId = $_SESSION['user_id'] ?? null;
$userType = $_SESSION['user_type'] ?? null;
$userName = '';
$userImage = 'default.jpg'; 

if ($userId && $userType) {
    $query = ($userType === 'a') ? "SELECT * FROM users WHERE user_id = '$userId'" : "SELECT * FROM artists WHERE artist_id = '$userId'";
    
    $resultUser = mysqli_query($conn, $query);
    if ($resultUser) {
        $userData = mysqli_fetch_assoc($resultUser);
        $userName = $userData['names'];
        $userImage = $userData['image'];
    }
}

if (isset($_GET['type'])) {
    $productType = $_GET['type'];
    $title = ($productType == 'f') ? 'FIXED PRICE' : (($productType == 'b') ? 'FOR BID' : 'COMMISSION');
}

if (isset($_GET['status'])) {
    $productStatus = ($_GET['status'] == 'pending') ? 'p' : 'a'; 
}

if (isset($_GET['medium'])) {
    $selectedMedium = $_GET['medium'];
}

$sql = "SELECT * FROM products WHERE product_type = '$productType' AND product_status = '$productStatus'";
if (!empty($selectedMedium)) {
    $sql .= " AND medium = '$selectedMedium'";
}
$result = mysqli_query($conn, $sql);

$mediumSql = "SELECT DISTINCT medium FROM products";
$mediumResult = mysqli_query($conn, $mediumSql);
$mediums = [];
while ($row = mysqli_fetch_assoc($mediumResult)) {
    $mediums[] = $row['medium'];
}

$suggestions = [];
if (isset($_GET['query'])) {
    $query = mysqli_real_escape_string($conn, $_GET['query']);

    $sql = "
        SELECT product_id, product_name AS suggestion, product_image FROM products WHERE product_name LIKE '%$query%' 
        UNION 
        SELECT product_id, medium AS suggestion, product_image FROM products WHERE medium LIKE '%$query%'
    ";

    $resultSuggestions = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($resultSuggestions)) {
        $suggestions[] = $row; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/product.css">
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

    <div class="header-prod">
        <h1 class="products-text">PRODUCTS</h1>
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Search..." id="search-input">
            <div id="suggestions" class="suggestions-dropdown">
                <?php foreach ($suggestions as $suggestion): ?>
                    <div class="suggestion-item" data-product-id="<?php echo $suggestion['product_id']; ?>">
                        <img src="image/<?php echo $suggestion['product_image']; ?>" alt="<?php echo htmlspecialchars($suggestion['suggestion']); ?>">
                        <?php echo htmlspecialchars($suggestion['suggestion']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <i class="fa fa-search search-icon"></i>
        </div>
    </div>

    <div class="fixed-price-text">
        <h3><?php echo $title; ?></h3> 
    </div>

    <div class="button-row">
        <div class="left-buttons">
            <a href="?type=f&status=<?php echo $productStatus; ?>&medium=<?php echo $selectedMedium; ?>" class="action-button <?php if ($productType == 'f') echo 'selected-button'; ?>">Fixed</a> 
            <a href="?type=b&status=<?php echo $productStatus; ?>&medium=<?php echo $selectedMedium; ?>" class="action-button <?php if ($productType == 'b') echo 'selected-button'; ?>">For Bid</a>
            <a href="?type=c&status=<?php echo $productStatus; ?>&medium=<?php echo $selectedMedium; ?>" class="action-button <?php if ($productType == 'c') echo 'selected-button'; ?>">Commission</a>
        </div>
        <div class="right-buttons">
            <a href="?status=<?php echo $productStatus == 'p' ? 'all' : 'pending'; ?>&type=<?php echo $productType; ?>&medium=<?php echo $selectedMedium; ?>" class="status-button">
                <?php echo $productStatus == 'p' ? 'All' : 'Pending'; ?>
            </a>
            <select class="action-select" id="featured-select" onchange="window.location.href='?type=<?php echo $productType; ?>&status=<?php echo $productStatus; ?>&medium=' + this.value;">
                <option value="">Featured Selection</option>
                <?php foreach ($mediums as $medium): ?>
                    <option value="<?php echo htmlspecialchars($medium); ?>" <?php if ($medium == $selectedMedium) echo 'selected'; ?>><?php echo htmlspecialchars($medium); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="image-row">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="product-container">
                <div class="image-frame">
                    <a href="adminProductDetail.php?id=<?php echo $row['product_id']; ?>">
                        <img src="image/<?php echo $row['product_image']; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-image">
                    </a>
                </div>
                <p class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></p>
                <?php if ($productType != 'b'): ?>
                    <p class="product-price">₱ <?php echo number_format($row['product_price'], 2); ?></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        const searchInput = document.getElementById('search-input');
        const suggestionsDropdown = document.getElementById('suggestions');

        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                const query = this.value;
                if (query.length >= 1) {
                    window.location.href = '?query=' + encodeURIComponent(query) + '&type=<?php echo $productType; ?>&status=<?php echo $productStatus; ?>&medium=<?php echo $selectedMedium; ?>';
                }
            }
        });

        searchInput.addEventListener('input', function() {
            const query = this.value;
            if (query.length >= 1) {
                suggestionsDropdown.style.display = 'block'; 
            } else {
                suggestionsDropdown.style.display = 'none'; 
            }
        });

        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !suggestionsDropdown.contains(event.target)) {
                suggestionsDropdown.style.display = 'none'; 
            }
        });

        const suggestionItems = document.querySelectorAll('.suggestion-item');
        suggestionItems.forEach(item => {
            item.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                window.location.href = 'adminProductDetail.php?id=' + productId;
            });
        });
    </script>


  
</body>
</html>
