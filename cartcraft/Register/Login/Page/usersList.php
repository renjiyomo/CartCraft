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

$sql = "SELECT user_id, names, email, phone_number, date_registered, user_status FROM users";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/users.css">
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
        <h1>USERS</h1>
        <div class="table-container">
            <div class="table-header">
                <div class="total-users">
                    <?php 
                    $total_users = mysqli_num_rows($result);
                    echo "Total Users: $total_users";
                    ?>
                </div>

                <div class="add-admin-container">
                    <button id="addAdminBtn" class="add-admin-btn">Add Admin</button>
                </div>

            </div>

            <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Date Registered</th>
                    <th>Total Orders</th>
                    <th>Total Spend</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : 
                    $user_id = $row['user_id'];

                    $order_sql = "SELECT COUNT(*) AS total_orders, SUM(product_price * quantity) AS total_spend 
                                  FROM orders WHERE users_id = $user_id AND status = 'd'";
                    $order_result = mysqli_query($conn, $order_sql);
                    $order_data = mysqli_fetch_assoc($order_result);

                    $total_orders = $order_data['total_orders'] ?? 0;
                    $total_spend = $order_data['total_spend'] ? '₱' . number_format($order_data['total_spend'], 2) : '₱0.00';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['names']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['date_registered']); ?></td>
                    <td><?php echo $total_orders; ?></td>
                    <td><?php echo $total_spend; ?></td>
                    <td><?php echo $row['user_status'] == 'a' ? 'Active' : ($row['user_status'] == 'i' ? 'Inactive' : 'Banned'); ?></td>
                    <td>
                        <?php if ($row['user_status'] == 'a') { ?>
                            <button class="status-btn inactive" onclick="changeStatus(<?php echo $row['user_id']; ?>, 'i')">Inactive</button>
                            <button class="status-btn banned" onclick="changeStatus(<?php echo $row['user_id']; ?>, 'b')">Banned</button>
                        <?php } elseif ($row['user_status'] == 'i') { ?>
                            <button class="status-btn active" onclick="changeStatus(<?php echo $row['user_id']; ?>, 'a')">Active</button>
                            <button class="status-btn banned" onclick="changeStatus(<?php echo $row['user_id']; ?>, 'b')">Banned</button>
                        <?php } else { ?>
                            <button class="status-btn active" onclick="changeStatus(<?php echo $row['user_id']; ?>, 'a')">Active</button>
                            <button class="status-btn inactive" onclick="changeStatus(<?php echo $row['user_id']; ?>, 'i')">Inactive</button>
                        <?php } ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    </div>

    <div id="addAdminModal" class="modal hidden">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Add Admin</h2>
        <form action="addAdmin.php" method="POST">

            <input type="text" id="names" placeholder="Full Name" name="names" required>
            
            <input type="email" id="email" placeholder="Email" name="email" required>
            
            <input type="password" id="password" placeholder="Password" name="password" required>
            
            <input type="text" id="phone_number" placeholder="Contact Number" name="phone_number" required>
            
            <input type="hidden" name="user_type" value="a">
            <button type="submit" class="submit-btn">Add Admin</button>
        </form>
    </div>
</div>
    

<script>
document.getElementById('addAdminBtn').addEventListener('click', function() {
    document.getElementById('addAdminModal').style.display = 'flex';
});

document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('addAdminModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('addAdminModal')) {
        document.getElementById('addAdminModal').style.display = 'none';
    }
});
</script>

<script>
    function changeStatus(userId, status) {
        if (confirm("Are you sure you want to change the user's status?")) {
            fetch('updateUserStatus.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Status updated successfully!");
                    location.reload(); // Refresh the table
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred. Please try again.");
            });
        }
    }
    </script>

</body>
</html>

<?php
mysqli_close($conn);
?>
