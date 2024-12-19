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

$product_id = $_GET['id'] ?? null;
if ($product_id) {
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_size = $_POST['product_size'];
    $medium = $_POST['medium'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    $product_type = $_POST['product_type'];
    $starting_date = ($product_type === 'b') ? $_POST['starting_date'] : NULL;
    $ending_date = ($product_type === 'b') ? $_POST['ending_date'] : NULL;

    $target_dir = "image/";
    $image_name = $product['product_image'];

    if (!empty($_FILES["product_image"]["name"])) {
        $image_name = basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $valid_extensions = array("jpg", "png", "jpeg", "gif");

        if (in_array($imageFileType, $valid_extensions)) {
            if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                echo "Error uploading your file.";
                exit;
            }
        } else {
            echo "Invalid file type.";
            exit;
        }
    }

    $sql = "UPDATE products SET product_name = ?, product_price = ?, product_size = ?, medium = ?, quantity = ?, description = ?, product_image = ?, product_type = ?, start_date = ?, end_date = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssisssssi", $product_name, $product_price, $product_size, $medium, $quantity, $description, $image_name, $product_type, $starting_date, $ending_date, $product_id);

    if ($stmt && $stmt->execute()) {
        header("Location: artistProduct.php?success=Product updated successfully!");
        exit;
    } else {
        echo "Execute Error: " . $stmt->error;
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
    <link rel="stylesheet" href="css/editProd.css">
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

<form action="editProduct.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
    <div class="container">
        <div class="image-container">
            <div class="product-type-container">
                <label class="product-type-text">Category:</label>
                <div class="product-type-buttons">
                    <button type="button" class="product-type-button" data-type="f" <?php echo ($product['product_type'] == 'f' ? 'class="active"' : ''); ?>>Fixed</button>
                    <button type="button" class="product-type-button" data-type="b" <?php echo ($product['product_type'] == 'b' ? 'class="active"' : ''); ?>>For Bid</button>
                    <button type="button" class="product-type-button" data-type="c" <?php echo ($product['product_type'] == 'c' ? 'class="active"' : ''); ?>>Commission</button>
                </div>
            </div>

            <div class="image-upload" onclick="document.getElementById('product_image_input').click();">
                <input type="file" name="product_image" id="product_image_input" accept="image/*" style="display: none;" onchange="displayImage(event)">
                <img src="image/<?php echo $product['product_image']; ?>" alt="Current Image" class="product-image" id="displayed_image">
            </div>
        </div>

        <div class="info-container">
            <input type="text" name="product_name" id="product_name" class="centered-name-text" value="<?php echo htmlspecialchars($product['product_name']); ?>" placeholder="NAME OF THE ARTWORK" required>
            <div class="info-grid">
                <label for="product_price" class="label" id="price_label">Price:</label>
                <input type="number" name="product_price" id="product_price" class="centered-text" value="<?php echo htmlspecialchars($product['product_price']); ?>" required>

                <label for="product_size" class="label">Size:</label>
                <input type="text" name="product_size" id="product_size" class="centered-text" value="<?php echo htmlspecialchars($product['product_size']); ?>" required>

                <label for="medium" class="label">Medium:</label>
                <input type="text" name="medium" id="medium" class="centered-text" value="<?php echo htmlspecialchars($product['medium']); ?>" required>

                <label for="quantity" class="label">Quantity:</label>
                <input type="number" name="quantity" id="quantity" class="centered-text" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>

                <label for="starting_date" class="label" id="starting_date_label">Starting Date & Time:</label>
                <input type="datetime-local" name="starting_date" id="starting_date" class="centered-text" value="<?php echo $product['start_date']; ?>">

                <label for="ending_date" class="label" id="ending_date_label">End of Bidding:</label>
                <input type="datetime-local" name="ending_date" id="ending_date" class="centered-text" value="<?php echo $product['end_date']; ?>">

                <label for="description" class="label" id="desc">Description:</label>
                <textarea name="description" id="description" class="descript" placeholder="Add the artwork's description here." required><?php echo htmlspecialchars($product['description']); ?></textarea>

                <input type="hidden" name="product_type" id="hidden_product_type" value="<?php echo htmlspecialchars($product['product_type']); ?>">
                <div class="button-group">

                    <button type="button" class="cancel-button" onclick="window.location.href='artistProductDetail.php?id=<?php echo $product_id; ?>'">Cancel</button>
                    <button type="submit" class="submit-btn">Update</button>

                </div>
            </div>
        </div>
    </div>
</form>

<script>
    const typeButtons = document.querySelectorAll('.product-type-button');
    const startingDateLabel = document.getElementById('starting_date_label');
    const startingDateInput = document.getElementById('starting_date');
    const endingDateLabel = document.getElementById('ending_date_label');
    const endingDateInput = document.getElementById('ending_date');

    typeButtons.forEach(button => {
        button.addEventListener('click', function () {
            typeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const selectedType = this.getAttribute('data-type');
            document.getElementById('hidden_product_type').value = selectedType;

            if (selectedType === 'b') {
                startingDateLabel.style.display = 'block';
                startingDateInput.style.display = 'block';
                endingDateLabel.style.display = 'block';
                endingDateInput.style.display = 'block';
            } else {
                startingDateLabel.style.display = 'none';
                startingDateInput.style.display = 'none';
                endingDateLabel.style.display = 'none';
                endingDateInput.style.display = 'none';
            }
        });
    });

    document.addEventListener("DOMContentLoaded", () => {
        const productType = <?php echo json_encode($product['product_type']); ?>;
        document.querySelector(`.product-type-button[data-type="${productType}"]`).click();
    });

    function displayImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            document.getElementById('displayed_image').src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</body>
</html>
