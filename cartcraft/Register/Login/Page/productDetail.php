<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'a') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

include 'cartcraft_db.php';

date_default_timezone_set('Asia/Manila');

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

$current_time = time(); 
$start_time = strtotime($product['start_date']); 
$end_time = strtotime($product['end_date']);

$status_text = '';

if ($current_time < $start_time) {
    $status_text = "Auction Not Started";
} elseif ($current_time >= $start_time && $current_time <= $end_time) {
    $status_text = "Auction Open";
} else {
    $status_text = "Auction Closed";
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
</head>

<nav>
    <?php include 'Adminnavbar.php'; ?>
</nav>

<body>

<div class="container">
    <div class="image-container">
        <div class="product-type-text">
            <?php
            if ($product['product_status'] === 'p') {
                echo $product['product_type'] === 'f' ? 'FIXED PRICE (PENDING)' : 'FOR BID (PENDING)';
            } else {
                echo $product['product_type'] === 'f' ? 'FIXED PRICE' : 'FOR BID';
            }
            ?>
        </div>
        <img src="image/<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>" class="product-image">

        <?php if ($product['product_status'] === 'p'): ?>
        <div class="action-buttons">
            <a href="adminProductDetail.php?id=<?php echo $product['product_id']; ?>&action=accept" class="accept-button">Accept</a>
            <a href="adminProductDetail.php?id=<?php echo $product['product_id']; ?>&action=decline" class="decline-button">Decline</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="info-container">
    <?php if ($product['product_type'] === 'b'): ?>
        <div class="auction-status"><?php echo $status_text; ?></div> 
    <?php endif; ?>

    <h2><?php echo $product['product_name']; ?></h2>

    <div class="info-grid">
        <p class="label">
            <?php echo $product['product_type'] === 'f' ? 'Price:' : 'Starting Price:'; ?>
        </p>
        <p class="centered-text">₱ <?php echo number_format($product['product_price'], 2); ?></p>

        <p class="label">Artist's Name:</p>
        <p class="centered-text"><?php echo $product['artist_name']; ?></p>

        <p class="label">Size:</p>
        <p class="centered-text"><?php echo $product['product_size']; ?></p>

        <p class="label">Medium:</p>
        <p class="centered-text"><?php echo $product['medium']; ?></p>

        <p class="label">Quantity:</p>
        <p class="centered-text"><?php echo $product['quantity']; ?></p>

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
        <p class="descript"><?php echo $product['description']; ?></p>
    </div>
</div>

</div>

</body>
</html>
