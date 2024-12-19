<?php

session_start();

require 'cartcraft_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'a') {
    header("Location: /cartcraft/Register/Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userImage = $user['image'];
$userName = $user['names'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $names = $_POST['names'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone_number = $_POST['phone_number'];
    $street = $_POST['street'] ?: '';
    $barangay = $_POST['barangay'] ?: ''; 
    $municipality = $_POST['municipality'] ?: '';
    $province = $_POST['province'] ?: ''; 
    $zip_code = $_POST['zip_code'] ?: '';

    if (!empty($_FILES['profile_image']['name'])) {
        $profileImage = $_FILES['profile_image']['name'];
        $target_dir = "image/";
        $target_file = $target_dir . basename($profileImage);
        

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            echo "<script>alert('Image uploaded successfully');</script>";
        } else {
            echo "<script>alert('Error uploading image');</script>";
        }
    } else {
        $profileImage = $user['image']; 
    }

    $update_sql = "UPDATE users SET names = ?, email = ?, password = ?, phone_number = ?, street = ?, barangay = ?, municipality = ?, province = ?, zip_code = ?, image = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    $update_stmt->bind_param("ssssssssssi", $names, $email, $password, $phone_number, $street, $barangay, $municipality, $province, $zip_code, $profileImage, $user_id);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('Profile updated successfully!');</script>";
        header("Location: manageAccount.php");
        exit();
    } else {
        echo "<script>alert('Error updating profile.');</script>";
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
    <link rel="stylesheet" href="css/manage.css">
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

<div class="manage">
    <h2>MANAGE ACCOUNT</h2>
    <div class="container">
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-image-container">
                <label for="profileImageInput">
                    <div class="profile-image" style="background-image: url('image/<?php echo htmlspecialchars($user['image']) . '?' . time(); ?>');"></div>
                </label>
                <input type="file" id="profileImageInput" name="profile_image" accept="image/*" style="display: none;">
            </div>

            <h4>Edit Profile</h4>
            <label for="names">Name:</label>
            <input type="text" id="names" name="names" value="<?php echo htmlspecialchars($user['names']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <div class="password-container">
            <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                <span class="password-toggle" id="togglePassword">&#128065;</span>
            </div>

            <label for="phone_number">Phone Number:</label>
            <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>

            <label for="street">Street:</label>
            <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($user['street']); ?>">

            <label for="barangay">Barangay:</label>
            <input type="text" id="barangay" name="barangay" value="<?php echo htmlspecialchars($user['barangay']); ?>">

            <label for="municipality">Municipality:</label>
            <input type="text" id="municipality" name="municipality" value="<?php echo htmlspecialchars($user['municipality']); ?>">

            <label for="province">Province:</label>
            <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($user['province']); ?>">

            <label for="zip_code">Zip Code:</label>
            <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code']); ?>">

            <div class="button-container">
                <button type="submit" class="save-button">Save Changes</button>
                <a href="dash.php" class="cancel-button">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('profileImageInput').addEventListener('change', function() {
        const [file] = this.files;
        if (file) {
            const profileImageDiv = document.querySelector('.profile-image');
            profileImageDiv.style.backgroundImage = `url(${URL.createObjectURL(file)})`;
        }
    });
</script>


<script>
    const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

togglePassword.addEventListener('click', function () {
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;

    this.textContent = type === 'password' ? '👁️' : '🙈';
});

</script>


</body>
</html>
