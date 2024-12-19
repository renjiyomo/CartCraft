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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artist_id'])) {
    $artistId = $_POST['artist_id'];

    // Update artist status to 'active'
    $updateQuery = "UPDATE artists SET user_status = 'a' WHERE artist_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('i', $artistId);
    if ($stmt->execute()) {
        header('Location: artistsList.php');
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

// Handle decline action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decline_artist_id'])) {
    $declineArtistId = $_POST['decline_artist_id'];

    // Update artist status to 'declined'
    $declineQuery = "UPDATE artists SET user_status = 'd' WHERE artist_id = ?";
    $stmt = $conn->prepare($declineQuery);
    $stmt->bind_param('i', $declineArtistId);
    if ($stmt->execute()) {
        header('Location: artistsList.php');
        exit();
    } else {
        echo "Error declining artist: " . mysqli_error($conn);
    }
}

// Handle ban action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ban_artist_id'])) {
    $banArtistId = $_POST['ban_artist_id'];

    // Update artist status to 'banned'
    $banQuery = "UPDATE artists SET user_status = 'b' WHERE artist_id = ?";
    $stmt = $conn->prepare($banQuery);
    $stmt->bind_param('i', $banArtistId);
    if ($stmt->execute()) {
        header('Location: artistsList.php');
        exit();
    } else {
        echo "Error banning artist: " . mysqli_error($conn);
    }
}

$totalActiveArtistsQuery = "SELECT COUNT(*) AS total FROM artists WHERE user_status = 'a'";
$resultActive = mysqli_query($conn, $totalActiveArtistsQuery);
$totalActiveArtists = mysqli_fetch_assoc($resultActive)['total'];

$totalPendingArtistsQuery = "SELECT COUNT(*) AS total FROM artists WHERE user_status = 'p'";
$resultPending = mysqli_query($conn, $totalPendingArtistsQuery);
$totalPendingArtists = mysqli_fetch_assoc($resultPending)['total'];

$artistQuery = "SELECT artist_id, names, email, phone_number, specialization, date_registered, user_status, front_valid_id, back_valid_id FROM artists WHERE user_status IN ('a', 'p', 'b', 'd')";
$artistResult = mysqli_query($conn, $artistQuery);
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

<div class="container">
    <h1>ARTISTS</h1>
    <div class="table-container">
    <div class="table-header">
    <div class="filter-btn-container">
        <button id="togglePendingBtn" class="filter-btn">Show Pending Artists</button>
        <button id="toggleBannedDeclinedBtn" class="filter-btn">Show Banned and Declined Artists</button>
        <button id="downloadDocBtn" class="filter-btn">Download Artist Info</button>
    </div>
    <div class="total-users">
        Total Active Artists: <?php echo $totalActiveArtists; ?> | 
        Total Pending Artists: <?php echo $totalPendingArtists; ?>
    </div>
</div>


        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Date Registered</th>
                    <th>Specialization</th>
                    <th>Total Products</th>
                    <th>Status</th>
                    <th>Valid ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="artistTableBody">
                <?php while ($artist = mysqli_fetch_assoc($artistResult)): 
                    $artistId = $artist['artist_id'];
                    $productQuery = "SELECT COUNT(*) AS total_products FROM products WHERE artist_id = $artistId";
                    $productResult = mysqli_query($conn, $productQuery);
                    $totalProducts = mysqli_fetch_assoc($productResult)['total_products'];
                ?>
                    <tr class="artist-row" data-status="<?php echo $artist['user_status']; ?>">
                        <td><?php echo htmlspecialchars($artist['names']); ?></td>
                        <td><?php echo htmlspecialchars($artist['email']); ?></td>
                        <td><?php echo htmlspecialchars($artist['phone_number']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($artist['date_registered'])); ?></td>
                        <td><?php echo htmlspecialchars($artist['specialization']); ?></td>
                        <td><?php echo $totalProducts; ?></td>
                        <td><?php 
                            switch($artist['user_status']) {
                                case 'a': echo 'Active'; break;
                                case 'p': echo 'Pending'; break;
                                case 'b': echo 'Banned'; break;
                                case 'd': echo 'Declined'; break;
                            } ?></td>
                          <td>
                            <button class="detail-btn" data-front-id="<?php echo htmlspecialchars($artist['front_valid_id']); ?>" data-back-id="<?php echo htmlspecialchars($artist['back_valid_id']); ?>">Detail</button>
                        </td>
                        <td>
                            <?php if ($artist['user_status'] == 'p'): ?>
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="artist_id" value="<?php echo $artistId; ?>">
                                    <button type="submit" class="accept-btn">Accept</button>
                                </form>
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="decline_artist_id" value="<?php echo $artistId; ?>">
                                    <button type="submit" class="decline-btn">Decline</button>
                                </form>
                            <?php elseif ($artist['user_status'] == 'a'): ?>
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="ban_artist_id" value="<?php echo $artistId; ?>">
                                    <button type="submit" class="ban-btn">Ban</button>
                                </form>
                            <?php elseif ($artist['user_status'] == 'b'): ?>
                                BANNED
                            <?php elseif ($artist['user_status'] == 'd'): ?>
                                DECLINED
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="imageModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 class="valid_id">VALID ID IMAGES</h2>
        <div class="image-container">
            <div class="image-wrapper">
                <label for="frontImage" class="image-label">Front ID</label>
                <img id="frontImage" class="valid" alt="Front ID">
            </div>
            <div class="image-wrapper">
                <label for="backImage" class="image-label">Back ID</label>
                <img id="backImage" class="valid" alt="Back ID">
            </div>
        </div>

            <form action="">
                <button id="downloadIdDocBtn" class="download-btn">Download Valid IDs</button>
            </form>


    </div>
</div>

<script>
    const pendingBtn = document.getElementById('togglePendingBtn');
    const toggleBannedDeclinedBtn = document.getElementById('toggleBannedDeclinedBtn');
    let showingPending = false;
    let showingBannedDeclined = false;

    pendingBtn.addEventListener('click', function() {
        const rows = document.querySelectorAll('.artist-row');
        showingPending = !showingPending;

        if (showingPending) {
            rows.forEach(row => {
                if (row.dataset.status === 'p') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            pendingBtn.textContent = 'Show Active Artists';
        } else {
            rows.forEach(row => {
                if (row.dataset.status === 'a') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            pendingBtn.textContent = 'Show Pending Artists';
        }
    });

    toggleBannedDeclinedBtn.addEventListener('click', function() {
        const rows = document.querySelectorAll('.artist-row');
        showingBannedDeclined = !showingBannedDeclined;

        console.log('Banned/Declined Toggle Clicked'); 

        if (showingBannedDeclined) {
            rows.forEach(row => {
                console.log(row.dataset.status);
                if (row.dataset.status === 'b' || row.dataset.status === 'd') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            toggleBannedDeclinedBtn.textContent = 'Show Active Artists';
        } else {
            rows.forEach(row => {
                if (row.dataset.status === 'a') {
                    row.style.display = ''; 
                } else {
                    row.style.display = 'none'
                }
            });
            toggleBannedDeclinedBtn.textContent = 'Show Banned and Declined Artists';
        }
    });

    const detailButtons = document.querySelectorAll('.detail-btn');
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const frontId = button.getAttribute('data-front-id');
            const backId = button.getAttribute('data-back-id');
            document.getElementById('frontImage').src = 'image/' + frontId;
            document.getElementById('backImage').src = 'image/' + backId;
            document.getElementById('imageModal').style.display = 'block';
        });
    });

    const modal = document.getElementById('imageModal');
    const closeModal = document.getElementsByClassName('close')[0];

    closeModal.onclick = function() {
        modal.style.display = 'none';
    };

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
</script>

<script src="js/downloadArtist.js"></script>


<script>
    document.getElementById('downloadIdDocBtn').addEventListener('click', function () {

    const frontIdSrc = document.getElementById('frontImage').src;
    const backIdSrc = document.getElementById('backImage').src;

    console.log('Front ID Src:', frontIdSrc);
    console.log('Back ID Src:', backIdSrc);

    if (!frontIdSrc || !backIdSrc) {
        alert("Invalid image sources. Please ensure both ID images are loaded.");
        return;
    }

    const docContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page {
                    size: A4;
                    margin: 20mm;
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                }
                .content {
                    text-align: center;
                }
                h2 {
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .image-wrapper {
                    margin: 10mm 0;
                    text-align: center;
                }
                img {
                    width: 45%; /* Scale width to 45% */
                    height: 45%; /* Scale height to 45% */
                    display: block;
                    margin: 0 auto;
                }
            </style>
        </head>
        <body>
            <div class="content">
                <h2>Artist Valid ID</h2>
                <div class="image-wrapper">
                    <p><strong>Front ID</strong></p>
                    <img src="${frontIdSrc}" alt="Front Valid ID">
                </div>
                <div class="image-wrapper">
                    <p><strong>Back ID</strong></p>
                    <img src="${backIdSrc}" alt="Back Valid ID">
                </div>
            </div>
        </body>
        </html>
    `;

    const blob = new Blob([docContent], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'ValidIDs-Artist.doc';
    a.click();
    URL.revokeObjectURL(url);
});

</script>

</body>
</html>
