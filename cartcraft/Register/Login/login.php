<?php
session_start();
include 'cartcraft_db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['login_email']);
    $password = mysqli_real_escape_string($conn, $_POST['login_password']);

    $query_users = "SELECT * FROM users WHERE email='$email'";
    $result_users = mysqli_query($conn, $query_users);

    if ($result_users && mysqli_num_rows($result_users) == 1) {
        $user = mysqli_fetch_assoc($result_users);

        if ($password === $user['password']) {
            if ($user['user_status'] === 'b') {
                $error_message = "Your account has been banned.";
            } else {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_type'] = $user['user_type'];

                if ($user['user_type'] == 'a') {
                    header("Location: Page/dash.php");
                } elseif ($user['user_type'] == 'u') {
                    header("Location: Page/customersPage.php");
                }
                exit;
            }
        } else {
            $error_message = "Invalid password!";
        }
    } else {
        $query_artists = "SELECT * FROM artists WHERE email='$email'";
        $result_artists = mysqli_query($conn, $query_artists);

        if ($result_artists && mysqli_num_rows($result_artists) == 1) {
            $artist = mysqli_fetch_assoc($result_artists);

            if ($password === $artist['password']) {
                if ($artist['user_status'] === 'a' || $artist['user_status'] === 'i') {
                    $_SESSION['artist_id'] = $artist['artist_id'];
                    $_SESSION['user_type'] = $artist['user_type'];

                    header("Location: Page/artistsPage.php");
                    exit;
                } else {
                    switch ($artist['user_status']) {
                        case 'b':
                            $error_message = "Your account has been banned. Please contact support.";
                            break;
                        case 'p':
                            $error_message = "Your account is pending for approval.";
                            break;
                        case 'd':
                            $error_message = "Your account application has been declined. Please contact support.";
                            break;
                        default:
                            $error_message = "Unable to log in. Please contact support.";
                    }
                }
            } else {
                $error_message = "Invalid password!";
            }
        } else {
            $error_message = "Invalid email!";
        }
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
    <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="form-box">
    <div class="form-section active">
        <h3>SIGN IN</h3>
        <form action="" method="POST">

            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <div class="form-group">
                <input type="email" id="login_email" name="login_email" placeholder="Email" required>
            </div>

            <div class="form-group">
                <input type="password" id="login_password" name="login_password" placeholder="Password" required>
            </div>

            <p class="forgot"><a href="/forgot-password">Forgot your password?</a></p>

            <div class="form-group">
                <button type="submit">LOGIN</button>
            </div>
            <div class="form-group login-text">

                <p>Don't have an account? <a href="/cartcraft/Register/signup.php">Sign up now</a></p>

            <div class="contact-support">
                <a href="Page/customerService.php">Contact Customer Service</a>
            </div>

            </div>
            
        
            
        </form>
    </div>

</div>
</body>
</html>
