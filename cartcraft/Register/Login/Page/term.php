<?php
// Set the last updated date dynamically
$lastUpdated = "09/15/24";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CartCraft</title>
    <link rel="icon" href="image/cartcraftlogo.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff2e2;
            color: #333;
        }

        header {
            background-color: #fae8d2;
            padding: 10px 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2196F3;
        }

        h1 {
            font-size: 40px;
            margin-top: 40px;
            margin-bottom: 25px;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            background-color: #fae8d2;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 50px;
        }

        .last-updated {
            font-size: 14px;
            color: #777;
            margin-bottom: 20px;
        }

        .terms-section h2 {
            font-size: 25px;
            margin: 15px 0;
            color: #333;
        }

        .terms-section p {
            font-size: 20px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .terms-checkbox {
            margin: 20px;
            margin-left: 130px;
        }

        .terms-checkbox label {
            font-size: 20px;
            margin-left: 5px;
            color: #777;
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .cancel-btn, .continue-btn {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .cancel-btn {
            background-color: #f2d2ab;
            color: #333;
            text-decoration: none;
        }

        .continue-btn {
            background-color: #f2d2ab;
            color: #333;
            text-decoration: none;
        }

        .cancel-btn:hover {
            background-color: #e2a356;
        }

        .continue-btn:hover {
            background-color: #e2a356;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">cartCRAFT</div>
    </header>
    
    <h1>Terms of Service</h1>
    <div class="container">
        <main class="main-content">
            <p class="last-updated">LAST UPDATED: <?php echo $lastUpdated; ?></p>
            <section class="terms-section">
                <p>Welcome to CartCraft! By using our website and purchasing any products or services from us, you agree to the following Terms of Service. Please read them carefully. If you do not agree with any of these terms, you may not access or use our website or services.</p>
                <h2>1. General Information</h2>
                <p>CartCraft operates <a href="homepagechuchu.html" target="_blank">https://www.CartCraft.com/index.html</a>, an online platform for selling artwork, paints, and related services. By accessing or using our website, you agree to comply with and be bound by these Terms of Service, our Privacy Policy, and other policies or guidelines posted on the website.</p>
                <h2>2. Eligibility</h2>
                <p>By using our website, you confirm that you are at least 18 years old, or if you are a minor, you are accessing the website with the consent of your parent or legal guardian. You represent and warrant that all information provided by you is accurate and up-to-date.</p>
                <h2>3. Purchases and Payment</h2>
                <p>All orders placed on the website are subject to availability. We reserve the right to refuse or cancel any order for any reason. Prices for all products are listed on the website and are subject to change without notice.</p>
                <h2>4. Shipping and Delivery</h2>
                <p>Shipping costs and delivery times vary depending on your location and the product ordered. All shipping information will be provided during checkout.</p>
                <h2>5. Returns and Refunds</h2>
                <p>All sales of artwork are final, with no returns or refunds unless the product arrives damaged. You must contact us within 3 days with photographic evidence to arrange a replacement or refund.</p>
                <h2>6. Intellectual Property</h2>
                <p>All contents on the website, including images, text, and designs, are the property of CartCraft or its artists. You may not use, reproduce, or distribute any content without our express permission.</p>
                <div class="terms-checkbox">
                    <input type="checkbox" id="agree">
                    <label for="agree">I agree to the terms of service.</label>
                </div>
                <div class="button-group">
                    <a href="back.html" class="cancel-btn">CANCEL</a>
                    <a href="proceed.html" class="continue-btn">CONTINUE</a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
