<?php
session_start();
include 'cartcraft_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
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

$sql = "
SELECT c.product_id, p.product_name, p.product_image AS image, p.product_price AS price, 
       p.product_size, p.quantity AS stock_quantity, c.quantity, a.names AS artist_name
FROM cart AS c
JOIN products AS p ON c.product_id = p.product_id
JOIN artists AS a ON p.artist_id = a.artist_id
WHERE c.user_id = ?
ORDER BY c.date_added DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['sizes'] = explode(',', $row['product_size']);
        $cart_items[] = $row;
    }
}

$stmt->close();

$cart_items_by_artist = [];
foreach ($cart_items as $item) {
    $cart_items_by_artist[$item['artist_name']][] = $item;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/all.min.css">
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
    <a href="customersPage.php">Home</a>
    <a href="customersProduct.php">Products</a>
    <a href="about.php">About</a>
    <a href="services.php">Service</a>
    <a href="cart.php" class="cart-icon">
        <i class="fa-solid fa-cart-shopping"></i>
    </a>

    <div class="profile-dropdown">
        <div class="profile">
            <img src="image/<?php echo $userImage; ?>" alt="profile_pic" class="profile-pic">
        </div>
        <ul class="dropdown-content">
            <li>
                <span class="profile-name"><?php echo htmlspecialchars($userName); ?></span>
            </li>
            <li><a href="customersManageAccount.php">Manage Account</a></li>
            <li><a href="customersPurchaseHistory.php">Purchase History</a></li>
            <li><a href="customersWishList.php">My Wishlist</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="mobile-account-links">
        <a href="customersManageAccount.php">Manage Account</a>
        <a href="customersPurchaseHistory.php">Purchase History</a>
        <a href="customersWishList.php">My Wishlist</a>
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


<div class="cart-container">
<div class="shopping-cart-header">
        <span class="arrow" onclick="window.location.href='customersProduct.php';">&#8592;</span> 
        <h2 class="shopping">Shopping cart (<?php echo $result->num_rows; ?>)</h2>

        <span class="edit-btn" id="edit-btn" onclick="toggleEdit()">Edit</span>
    </div>

    <?php if (count($cart_items) > 0): ?>
        <form method="POST" action="checkout.php">
            <div class="cart-items">

                <div class="cart-item-header">
                    <input type="checkbox" id="select-all">
                    <label class="all-text" for="select-all">All</label>
                </div>

                <?php foreach ($cart_items_by_artist as $artist_name => $products): ?>

                    <div class="cart-item">
                        <div class="cart-item-header">
                            <input type="checkbox" class="artist-checkbox" data-artist="<?php echo htmlspecialchars($artist_name); ?>">
                            <span class="artist-name"><?php echo htmlspecialchars($artist_name); ?></span>
                        </div>

                        <?php foreach ($products as $item): ?>
                            <div class="cart-item-body">
                            <input 
                                type="checkbox" 
                                class="product-checkbox" 
                                name="selected_products[]" 
                                value="<?php echo $item['product_id']; ?>" 
                                data-artist="<?php echo htmlspecialchars($artist_name); ?>" 
                                data-price="<?php echo $item['price']; ?>" 
                                data-quantity="<?php echo $item['quantity']; ?>"
                                <?php echo $item['stock_quantity'] == 0 ? 'disabled' : ''; ?>
                            >
                                <div class="product-image-container">
                                    <a href="customersProductDetail.php?id=<?php echo $item['product_id']; ?>">
                                        <img src="image/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-image">
                                    </a>
                                </div>
                                <div class="product-details">
                                <a href="customersProductDetail.php?id=<?php echo $item['product_id']; ?>">
                                    <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                </a>
                                    <div class="size-selector">
                                        <select name="size[<?php echo $item['product_id']; ?>]">
                                            <?php foreach ($item['sizes'] as $size): ?>
                                                <option value="<?php echo htmlspecialchars(trim($size)); ?>"><?php echo htmlspecialchars(trim($size)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="price">₱<?php echo number_format($item['price'], 2); ?></div>
                                </div>
                                
                                <div>
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <button type="button" name="remove" class="remove-btn" style="display: none;" onclick="removeItem(<?php echo $item['product_id']; ?>)">Remove</button>
                                            </div>

                                <?php if ($item['stock_quantity'] == 0): ?>
                                    <p class="out-of-stock">Out of stock</p>
                                <?php else: ?>
                                <div class="quantity-selector">
                                    <button type="button" id="decrement" class="quantity-btn decrease" data-product-id="<?php echo $item['product_id']; ?>" data-price="<?php echo $item['price']; ?>">-</button>
                                    <span class="quantity" id="quantity-<?php echo $item['product_id']; ?>"><?php echo $item['quantity']; ?></span>
                                    <button type="button" id="increment" class="quantity-btn increase" data-product-id="<?php echo $item['product_id']; ?>" data-price="<?php echo $item['price']; ?>">+</button>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <div class="overall-subtotal">
                    <h3 class="sub-tot">Subtotal: <span id="overall-subtotal">0.00</span></h3>
                </div>
            </div>
            <button type="submit" class="checkout-btn">Proceed to Checkout</button>
        </form>
    <?php else: ?>
        <p>Your cart is empty. <a href="customersProduct.php">Continue Shopping</a></p>
    <?php endif; ?>
</div>

<script>

document.getElementById('select-all').addEventListener('change', function() {
    const isChecked = this.checked;
    document.querySelectorAll('.artist-checkbox, .product-checkbox:not([disabled])').forEach(checkbox => {
        checkbox.checked = isChecked;
    });
    updateSubtotal();
});


document.querySelectorAll('.artist-checkbox').forEach(artistCheckbox => {
    artistCheckbox.addEventListener('change', function() {
        const artist = this.getAttribute('data-artist');
        const isChecked = this.checked;

        document.querySelectorAll(`.product-checkbox[data-artist="${artist}"]:not([disabled])`).forEach(productCheckbox => {
            productCheckbox.checked = isChecked;
        });
        updateSubtotal();
    });
});


document.querySelectorAll('.product-checkbox').forEach(productCheckbox => {
    productCheckbox.addEventListener('change', function() {
        const artist = this.getAttribute('data-artist');
        const allProductsChecked = [...document.querySelectorAll(`.product-checkbox[data-artist="${artist}"]:not([disabled])`)]
            .every(checkbox => checkbox.checked);

        document.querySelector(`.artist-checkbox[data-artist="${artist}"]`).checked = allProductsChecked;

        const allChecked = [...document.querySelectorAll('.artist-checkbox, .product-checkbox:not([disabled])')]
            .every(checkbox => checkbox.checked);
        document.getElementById('select-all').checked = allChecked;
    });
    updateSubtotal();
});

</script>

<script>
document.querySelectorAll('.quantity-btn').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.getAttribute('data-product-id');
        const quantityElement = document.getElementById('quantity-' + productId);
        const action = this.classList.contains('increase') ? 'increase' : 'decrease';
        
        fetch('update_cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ product_id: productId, action: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                quantityElement.textContent = data.new_quantity;
                document.querySelector(`.product-checkbox[value="${productId}"]`).setAttribute('data-quantity', data.new_quantity);
            } else {
                alert(data.message);
            }
            updateSubtotal();
        });
    });
});

</script>

<script>
function updateSubtotal() {
    let subtotal = 0;

    document.querySelectorAll('.product-checkbox:checked:not([disabled])').forEach(checkbox => {
        const price = parseFloat(checkbox.getAttribute('data-price'));
        const quantity = parseInt(document.getElementById('quantity-' + checkbox.value).textContent, 10);
        subtotal += price * quantity;
    });

    const formattedSubtotal = subtotal.toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
    document.getElementById('overall-subtotal').textContent = formattedSubtotal;
}

document.getElementById('select-all').addEventListener('change', function() {
    const isChecked = this.checked;
    document.querySelectorAll('.artist-checkbox, .product-checkbox').forEach(checkbox => {
        checkbox.checked = isChecked;
    });
    updateSubtotal();
});

document.querySelectorAll('.artist-checkbox').forEach(artistCheckbox => {
    artistCheckbox.addEventListener('change', function() {
        const artist = this.getAttribute('data-artist');
        const isChecked = this.checked;

        document.querySelectorAll(`.product-checkbox[data-artist="${artist}"]`).forEach(productCheckbox => {
            productCheckbox.checked = isChecked;
        });
        updateSubtotal();
    });
});

document.querySelectorAll('.product-checkbox').forEach(productCheckbox => {
    productCheckbox.addEventListener('change', updateSubtotal);
});


</script>

<script>
       function toggleEdit() {
    const removeButtons = document.querySelectorAll('.remove-btn');
    const editButton = document.getElementById('edit-btn');

    removeButtons.forEach(button => {
        if (button.style.display === 'none' || button.style.display === '') {
            button.style.display = 'inline-block'; 
        } else {
            button.style.display = 'none'; 
        }
    });

    editButton.textContent = editButton.textContent.trim() === 'Edit' ? 'Done' : 'Edit';
}

function removeItem(productId) {
    fetch('remove_from_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const productElement = document.querySelector(`[value="${productId}"]`).closest('.cart-item-body');
            const artistElement = productElement.closest('.cart-item'); 

            productElement.remove(); 

            const remainingProducts = artistElement.querySelectorAll('.cart-item-body');
            if (remainingProducts.length === 0) {
                artistElement.remove(); 
            }

            updateSubtotal();
        } else {
            alert('Error removing item.');
        }
    });
}


</script>


</body>
</html>
