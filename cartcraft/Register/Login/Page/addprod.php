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
    $image_name = basename($_FILES["product_image"]["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $valid_extensions = array("jpg", "png", "jpeg", "gif");

    if (in_array($imageFileType, $valid_extensions)) {
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO products (artist_id, product_name, product_price, product_size, medium, quantity, description, product_image, product_type, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("issssisssss", $user_id, $product_name, $product_price, $product_size, $medium, $quantity, $description, $image_name, $product_type, $starting_date, $ending_date);
                
                if ($stmt->execute()) {
                    header("Location: artistProduct.php?success=Product added successfully!");
                    exit;
                } else {
                    echo "Execute Error: " . $stmt->error;
                }
            } else {
                echo "Prepare Error: " . $conn->error;
            }
        } else {
            echo "Error uploading your file.";
        }
    } else {
        echo "Invalid file type.";
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
    <link rel="stylesheet" href="css/addProduct.css">
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
                <a href="artistManageAccount.php">Manage Account</a>
                <a href="logout.php">Logout</a>
            </div>

        </nav>
    </header>

</nav>
<form action="addprod.php" method="POST" enctype="multipart/form-data">
    <div class="container">
        <div class="image-container">
            <div class="product-type-container">
                <label class="product-type-text">Category:</label>
                <div class="product-type-buttons">
                    <button type="button" class="product-type-button" data-type="f">Fixed</button>
                    <button type="button" class="product-type-button" data-type="b">For Bid</button>
                    <button type="button" class="product-type-button" data-type="c">Commission</button>
                </div>
            </div>

            <div class="image-upload" onclick="document.getElementById('product_image_input').click();">
                <input type="file" name="product_image" id="product_image_input" accept="image/*" required style="display: none;" onchange="displayImage(event)">
                
                <img src="image/defaultProdc.png" alt="Placeholder" class="product-image" id="displayed_image">
                <span class="upload-text">Add image of artwork here</span>
            </div>
        </div>

        <div class="info-container">
            <input type="text" name="product_name" id="product_name" class="centered-text" placeholder="NAME OF THE ARTWORK" required>
            <div class="info-grid">
                <label for="product_price" class="label" id="price_label">Price:</label>
                <input type="number" name="product_price" id="product_price" class="centered-text" required>


                <label for="product_size" class="label">Size:</label>
                <input type="text" name="product_size" id="product_size" class="centered-text" required>

                <label for="medium" class="label">Medium:</label>
                <input type="text" name="medium" id="medium" class="centered-text" required>

                <label for="quantity" class="label">Quantity:</label>
                <input type="number" name="quantity" id="quantity" class="centered-text" required>

                <label for="starting_date" class="label" id="starting_date_label">Starting Date & Time:</label>
                <input type="datetime-local" name="starting_date" id="starting_date" class="centered-text">

                <label for="ending_date" class="label" id="ending_date_label">End of Bidding:</label>
                <input type="datetime-local" name="ending_date" id="ending_date" class="centered-text">


                <label for="description" class="label" id="desc">Description:</label>
                <textarea name="description" id="description" class="descript" placeholder="Add the artwork's description here." required></textarea>
            </div>
            <input type="hidden" name="product_type" id="product_type" value="">
            <div class="button-group">
                <button type="button" class="cancel-button">Cancel</button>
                <button type="submit" class="accept-button">Add</button>
            </div>
        </div>
    </div>
</form>

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

    function displayImage(event) {
        const file = event.target.files[0]; 
        const reader = new FileReader(); 

        reader.onload = function(e) {

            document.getElementById('displayed_image').src = e.target.result;
            document.querySelector('.upload-text').style.display = 'none'; 
        };

        if (file) {
            reader.readAsDataURL(file); 
        }
    }

    let selectedProductType = '';

    const buttons = document.querySelectorAll('.product-type-button');
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            selectedProductType = button.getAttribute('data-type'); 
            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active'); 
            document.getElementById('product_type').value = selectedProductType; 
            console.log(`Selected product type: ${selectedProductType}`); 
        });
    }); 
</script>

<script>
document.getElementById('starting_date').style.display = 'none';
document.getElementById('starting_date_label').style.display = 'none';
document.getElementById('ending_date').style.display = 'none';
document.getElementById('ending_date_label').style.display = 'none';

buttons.forEach(button => {
    button.addEventListener('click', () => {
        selectedProductType = button.getAttribute('data-type');
        buttons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        document.getElementById('product_type').value = selectedProductType;

        if (selectedProductType === 'b') {
            document.getElementById('starting_date').style.display = 'block';
            document.getElementById('starting_date_label').style.display = 'block';
            document.getElementById('ending_date').style.display = 'block';
            document.getElementById('ending_date_label').style.display = 'block';
            document.getElementById('price_label').textContent = 'Starting Price:';
        } else { 
            document.getElementById('starting_date').style.display = 'none';
            document.getElementById('starting_date_label').style.display = 'none';
            document.getElementById('ending_date').style.display = 'none';
            document.getElementById('ending_date_label').style.display = 'none';
            document.getElementById('price_label').textContent = 'Price:'; 
        }
    });
});

</script>

</body>
</html>
