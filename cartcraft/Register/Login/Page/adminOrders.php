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

$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'GCash';

$dateFilter = isset($_POST['filter_date']) ? $_POST['filter_date'] : null;
$monthFilter = isset($_POST['filter_month']) ? $_POST['filter_month'] : null;
$statusFilter = isset($_POST['status']) ? $_POST['status'] : '';

$whereClauses = ["orders.payment_method = '$paymentMethod'"];
if (!empty($dateFilter)) {
    $whereClauses[] = "DATE(order_date) = '$dateFilter'";
}
if (!empty($monthFilter)) {
    $whereClauses[] = "DATE_FORMAT(order_date, '%Y-%m') = '$monthFilter'";
}
if (!empty($statusFilter)) {
    $whereClauses[] = "orders.status = '$statusFilter'";
}

$whereSQL = implode(' AND ', $whereClauses);

$sql = "SELECT orders.*, artists.names AS artist_name, 
        CONCAT(orders.street, ', ', orders.barangay, ', ', orders.municipality, ', ', orders.province, ', ', orders.zip_code) AS full_address 
        FROM orders 
        LEFT JOIN artists ON orders.artists_id = artists.artist_id 
        WHERE $whereSQL";

$result = mysqli_query($conn, $sql);

$salesQuery = "SELECT SUM(total) as total_sales FROM orders WHERE $whereSQL";
$salesResult = mysqli_query($conn, $salesQuery);
$salesRow = mysqli_fetch_assoc($salesResult);
$totalSales = $salesRow['total_sales'] ? $salesRow['total_sales'] : 0;

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/adminOrders.css">
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
        <h1>ORDERS</h1>

        <div class="table-container">
            <div class="table-header">
            <form method="POST" action="">
                <label for="payment_method">Payment Method: </label>
                <select name="payment_method" id="payment_method" onchange="this.form.submit()">
                    <option value="GCash" <?php if ($paymentMethod == 'GCash') echo 'selected'; ?>>GCash</option>
                    <option value="Card" <?php if ($paymentMethod == 'Card') echo 'selected'; ?>>Card Payment</option>
                </select>

                <label for="filter_date">Select Date: </label>
                <input id="order-date" type="date" name="filter_date" id="filter_date" value="<?php echo isset($_POST['filter_date']) ? $_POST['filter_date'] : ''; ?>">

                <label for="filter_month">Select Month: </label>
                <input id="order-month" type="month" name="filter_month" id="filter_month" value="<?php echo isset($_POST['filter_month']) ? $_POST['filter_month'] : ''; ?>">

                <label for="status">Status: </label>
                <select id="payment_method" name="status" id="status">
                    <option value="">All</option>
                    <option value="p" <?php if (isset($_POST['status']) && $_POST['status'] == 'p') echo 'selected'; ?>>Pending</option>
                    <option value="s" <?php if (isset($_POST['status']) && $_POST['status'] == 's') echo 'selected'; ?>>Shipped</option>
                    <option value="d" <?php if (isset($_POST['status']) && $_POST['status'] == 'd') echo 'selected'; ?>>Delivered</option>
                </select>
                <button class="filter-btn" type="submit">Filter</button>
            </form>

            <button id="downloadOrdersDocBtn" class="filter-btn">Download Orders Info</button>
            

                <div class="total-orders">Total Orders: <?php echo mysqli_num_rows($result); ?></div>
            
            </div>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Artist</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Customer Name</th>
                            <th>Contact No</th>
                            <th>Address</th>
                            <th>Order Date</th>
                            <th>Status</th>

                            <?php if ($paymentMethod == 'GCash'): ?>
                                <th>GCash Reference No</th>
                                <th>Account Name</th>
                                <th>Account No.</th>
                            <?php elseif ($paymentMethod == 'Card'): ?>
                                <th>Card Holder</th>
                                <th>Email Address</th>
                                <th>Card Number</th>
                                <th>Expiration Date</th>
                                <th>CVV Code</th>
                            <?php endif; ?>
                            
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['artist_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
    echo "<td>₱ " . htmlspecialchars($row['product_price']) . "</td>";
    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
    echo "<td>" . htmlspecialchars($row['names']) . "</td>";
    echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
    echo "<td>" . htmlspecialchars($row['full_address']) . "</td>";  // Display full address in a single column
    echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
    echo "<td>" . ($row['status'] == 'p' ? 'PENDING' : ($row['status'] == 's' ? 'SHIPPED' : 'DELIVERED')) . "</td>";

    if ($row['payment_method'] == 'GCash') {
        echo "<td>" . htmlspecialchars($row['gcash_reference_number']) . "</td>";
        echo "<td>" . htmlspecialchars($row['account_names']) . "</td>";
        echo "<td>" . htmlspecialchars($row['account_number']) . "</td>";
    } elseif ($row['payment_method'] == 'Card') {
        echo "<td>" . htmlspecialchars($row['card_holder']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email_address']) . "</td>";
        echo "<td>" . htmlspecialchars($row['card_number']) . "</td>";
        echo "<td>" . htmlspecialchars($row['expiration_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['cvv_code']) . "</td>";
    }

    echo "<td>₱ " . htmlspecialchars($row['total']) . "</td>";
    echo "</tr>";
}
?>


                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders found for the selected payment method.</p>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <div class="total-sales">Total Sales: ₱ <?php echo number_format($totalSales, 2); ?></div>
        </div>
    </div>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

<script>
document.getElementById('downloadOrdersDocBtn').addEventListener('click', function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'legal'); 

    doc.setFontSize(18);
    doc.text('Orders Information', 180, 20, { align: 'center' });

    let y = 40;
    const marginX = 10; 
    const pageWidth = 330; 
    const cellPadding = 2; 
    const rowHeight = 15; 
    const maxY = 200; 

    const headers = [];
    document.querySelectorAll('.user-table thead th').forEach(th => headers.push(th.textContent));

    const rows = [];
    document.querySelectorAll('.user-table tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => row.push(td.textContent));
        rows.push(row);
    });

    const colWidth = pageWidth / headers.length; 

    function drawHeader() {
    doc.setFontSize(7);
    headers.forEach((header, i) => {
        const x = marginX + i * colWidth;
        const textX = x + colWidth / 2; 
        doc.rect(x, y - rowHeight, colWidth, rowHeight); 
        doc.text(header, textX, y - rowHeight + rowHeight / 2, {
            align: 'center',
            baseline: 'middle',
        });
    });
}


    drawHeader();

    rows.forEach((row, rowIndex) => {
        if (y + rowHeight > maxY) {
            doc.addPage(); 
            y = 40;
            drawHeader(); 
        }
        row.forEach((cell, colIndex) => {
            const x = marginX + colIndex * colWidth;
            const textX = x + colWidth / 2;
            doc.rect(x, y, colWidth, rowHeight); 

            const textY = y + rowHeight / 2; 
            const textLines = doc.splitTextToSize(cell, colWidth - cellPadding * 2);
            
            textLines.forEach((line, lineIndex) => {
                doc.text(line, x + cellPadding, y + (rowHeight / textLines.length) * (lineIndex + 0.5), {
     
                    baseline: 'middle',
                });
            });
        });
        y += rowHeight; 
    });

    doc.save('OrdersInfo.pdf');
});


</script>


</html>

<?php
mysqli_close($conn);
?>
