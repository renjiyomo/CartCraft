<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Navigation</title>
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <a href="#" class="logo">
            <img class="craft" src="image/craft.png" alt="Logo">
        </a>

        <nav class="navbar">
            <a href="dash.php">Dashboard</a>
            <a href="adminProduct.php">Products</a>
            <a href="usersList.php">User</a>
            <a href="artistsList.php">Artist</a>
            <a href="adminOrders.php">Orders</a>
            <a href="reports.php">Sales</a>
            
            <div class="profile-dropdown">
            <img src="image/<?php echo $profile_image; ?>" alt="profile_pic">
                <ul class="dropdown-content">
                    <li><a class="profile" ><?php echo $user_name; ?></a></li>
                    <li><a href="manageAccount.php">Manage Account</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <script>
        document.querySelector('.profile').addEventListener('click', function(event) {
            event.preventDefault();
            const dropdown = document.querySelector('.dropdown-content');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.dropdown-content');
            if (!event.target.closest('.profile-dropdown')) {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>
