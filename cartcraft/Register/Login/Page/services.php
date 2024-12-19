<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'u') {
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

define('FAQS', [
    [
        'question' => 'Where are you located?',
        'answer' => 'We are located in Zone 3 San Francisco, Libon Albay 4505.'
    ],
    [
        'question' => 'How can I contact you?',
        'answer' => 'You can contact us via email at cartcraft2024@gmail.com or phone at +63456823067. You can also follow us on Facebook and Instagram.'
    ],
    [
        'question' => 'How do I buy art from your website?',
        'answer' => 'Simply browse our collection, select the artwork you love, and follow the checkout process. We accept secure payments through card payment and GCash.'
    ],
    [
        'question' => 'What payment method do you accept?',
        'answer' => 'We accept payments via credit/debit cards and GCash.'
    ],
    [
        'question' => 'Are your artworks originals?',
        'answer' => 'Yes, all our artworks are original and authenticated.'
    ],
    [
        'question' => 'Can I commission custom artwork?',
        'answer' => 'Yes, you can commission custom artwork, depending on the artists availability.'
    ],
    [
        'question' => 'Do you ship internationally?',
        'answer' => 'No, our shipping address is only here in the Philippines.'
    ],
    [
        'question' => 'How long will it take for my order to arrive?',
        'answer' => 'Our expected delivery is 6-7 days, but it also depends on your location.'
    ],
    [
        'question' => 'What should I do if my artwork arrives damaged?',
        'answer' => 'Please take a video while opening the package and contact us immediately through our provided contact details.'
    ],
    [
        'question' => 'What is your return policy?',
        'answer' => 'We accept returns within 3 days of delivery for a full refund, provided the artwork is in its original condition. Custom and commissioned pieces are non-refundable.'
    ],
    [
        'question' => 'How can I learn more about an artist or their artwork?',
        'answer' => 'You can find more information about an artist or their artwork on our websites artist profiles or by contacting us directly.'
    ],
    [
        'question' => 'How do i commission an artwork?',
        'answer' => 'You can commission artwork by reaching out to us via email or phone to discuss your requirements, and we will connect you with the artist.'
    ],
    [
        'question' => 'How long does it take to complete a commissioned piece?',
        'answer' => 'The time required to complete a commissioned piece depends on the artist and the complexity of the work. We will provide an estimated timeline during the commissioning process.'
    ],
]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/services.css">
    <link rel="stylesheet" href="css/style.css">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const questions = document.querySelectorAll('.faq-question');
            const searchBar = document.getElementById('faq-search');

            questions.forEach(question => {
                question.addEventListener('click', () => {
                    const answer = question.nextElementSibling;
                    const isVisible = answer.style.display === 'block';
                    answer.style.display = isVisible ? 'none' : 'block';
                    const symbol = question.querySelector('.symbol');
                    symbol.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
                });
            });

            searchBar.addEventListener('input', function () {
                const query = searchBar.value.toLowerCase();
                questions.forEach(question => {
                    const parent = question.parentElement;
                    if (question.textContent.toLowerCase().includes(query)) {
                        parent.style.display = '';
                    } else {
                        parent.style.display = 'none';
                    }
                });
            });
        });
    </script>
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

    <div class="container" id="contact">
        <div class="contact-form">
            <h2>Get in Touch</h2>
            <form action="#" method="POST">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
                <button class="contact-btn" type="submit">Send now</button>
            </form>
        </div>
        <div class="contact-info">
            <h2>Contact Information</h2>

            <p class="contact-icon"><i class="fa-solid fa-envelope"></i> Email <span class="contact-link"><a href="mailto:cartcraft2024@gmail.com"> cartcraft2024@gmail.com </a></span> </p>
    
            <p class="contact-icon"><i class="fa-solid fa-phone-volume"></i> Phone <span class="contact-link"> +639456823067</span></p>

            <p class="contact-icon"><i class="fa-solid fa-location-dot"></i> Address <span class="contact-link"><a href="https://maps.app.goo.gl/TehAastwugnuJzf36" target="_blank"> 
            Zone 3 San Francisco, Libon, Albay 4505 </a> </span></p>
        

        <div class="social-links">
            <a href="https://web.facebook.com/mark.erick.serrano" target="_blank" aria-label="Facebook">
            <i class="fab fa-facebook"></i></a>
            <a href="https://www.instagram.com/renjiyokona?igsh=MW94aGRjc3J3M3E0dA==" target="_blank" aria-label="Instagram">
            <i class="fab fa-instagram"></i></a>
        </div>
        </div>
    </div>

    <div class="faq-container" id="faq">
        <h1>How can we help?</h1>
        <div class="search-bar-container">
    <input type="text" id="faq-search" class="search-bar" placeholder="Search FAQs...">
    <span class="search-icon">
        <i class="fas fa-search"></i>
    </span>
</div>

<div class="freq">
    <h3>Frequently Ask Questions:</h3>
</div>

<div class="freq-item">
<?php foreach (FAQS as $faq): ?>
            <div class="faq-item">
                
            <button class="faq-question">
                <li><?= htmlspecialchars($faq['question']) ?></li>
                <span class="symbol">&#x2304;</span>
            </button>

            <div class="faq-answer">
                <?= htmlspecialchars($faq['answer']) ?>
            </div>

            </div>
        <?php endforeach; ?>
</div>

    </div>

</body>
</html>

