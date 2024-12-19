<?php

@include 'cartcraft_db.php';
session_start();

if (isset($_POST['submit_customer'])) {
    $names = mysqli_real_escape_string($conn, $_POST['customer_names']);
    $email = mysqli_real_escape_string($conn, $_POST['customer_email']);
    $pass = $_POST['customer_password'];
    $cpass = $_POST['cpassword'];
    $phone_number = mysqli_real_escape_string($conn, $_POST['customer_phone']); // New phone number field

    $select = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {
        $error[] = 'User already exists!';
    } else {
        if ($pass != $cpass) {
            $error[] = 'Passwords do not match!';
        } else {
            $insert = "INSERT INTO users(names, email, password, phone_number) 
                       VALUES('$names','$email', '$pass', '$phone_number')";
            if (mysqli_query($conn, $insert)) {
                header('location: login/login.php');
                exit();
            } else {
                $error[] = 'Registration failed!';
            }
        }
    }
}


if (isset($_POST['submit_artist'])) {
    $names = mysqli_real_escape_string($conn, $_POST['artist_name']);
    $email = mysqli_real_escape_string($conn, $_POST['artist_email']);
    $pass = $_POST['artist_password'];
    $cpass = $_POST['artist_confirm_password'];
    $contact = mysqli_real_escape_string($conn, $_POST['artist_contact']);
    $specialization = mysqli_real_escape_string($conn, $_POST['artist_specialization']);
    $front_valid_id = $_FILES['artist_front_valid_id']['name'];
    $front_valid_id_tmp_name = $_FILES['artist_front_valid_id']['tmp_name'];
    $front_valid_id_folder = 'uploads/' . $front_valid_id;

    $back_valid_id = $_FILES['artist_back_valid_id']['name'];
    $back_valid_id_tmp_name = $_FILES['artist_back_valid_id']['tmp_name'];
    $back_valid_id_folder = 'uploads/' . $back_valid_id;

    $select = "SELECT * FROM artists WHERE email = '$email'";
    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {
        $error[] = 'Artist already exists!';
    } else {
        if ($pass != $cpass) {
            $error[] = 'Passwords do not match!';
        } else {
            if (!is_dir('uploads')) {
                mkdir('uploads');
            }
            
            if (move_uploaded_file($front_valid_id_tmp_name, $front_valid_id_folder) && 
                move_uploaded_file($back_valid_id_tmp_name, $back_valid_id_folder)) {
            
                $insert = "INSERT INTO artists(names, email, password, phone_number, specialization, front_valid_id, back_valid_id) 
                            VALUES('$names','$email', '$pass', '$contact', '$specialization', '$front_valid_id', '$back_valid_id')";
                if (mysqli_query($conn, $insert)) {
                    header('location: signup.php');
                    exit();
                } else {
                    $error[] = 'Registration failed!';
                }
            }            
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
    <link rel="stylesheet" href="signup.css">
</head>
<body>



<div class="form-box">
<h3>SIGN UP</h3>
<div class="header">
    <div class="row">
        <button id="customerBtn" onclick="toggleForm('customer')">CUSTOMER</button>
        <button id="artistBtn" onclick="toggleForm('artist')">ARTIST</button>
    </div>
</div>

    <div id="customerForm" class="form-section">
    
    <form action="" method="POST">

        <div class="form-group">
            <input type="text" id="customer_name" name="customer_names" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
            <input type="tel" id="customer_phone" name="customer_phone" placeholder="Contact Number (e.g., +639456823067)" pattern="[+]{0,1}[0-9]{1,15}" required>
        </div>

        <div class="form-group">
            <input type="email" id="customer_email" name="customer_email" placeholder="Email" required>
        </div>

        <div class="form-group">
            <input type="password" id="customer_password" name="customer_password" placeholder="Password" required>
        </div>
        
        <div class="form-group">
            <input type="password" id="customer_confirm_password" name="cpassword" placeholder="Confirm Password" required>
        </div>

        <div class="form-group">
            <button type="submit" name="submit_customer">REGISTER</button>
        </div>

        <div class="form-group login-text">
            <p>Already have an account? <a href="/cartcraft/Register/Login/login.php">Login now</a></p>
        </div>
    </form>
</div>

<div id="artistForm" class="form-section">

    <form action="" method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <input type="text" id="artist_name" name="artist_name" placeholder="Full Name" required>
        </div>

        <div class="form-group">
            <input type="email" id="artist_email" name="artist_email" placeholder="Email" required>
        </div>

        <div class="form-group">
            <input type="password" id="artist_password" name="artist_password" placeholder="Password" required>
        </div>

        <div class="form-group">
            <input type="password" id="artist_confirm_password" name="artist_confirm_password" placeholder="Confirm Password" required>
        </div>

        <div class="form-group">
            <input type="tel" id="artist_contact" name="artist_contact" placeholder="Contact Number (e.g., +639456823067)" pattern="[+]{0,1}[0-9]{1,15}" required>
        </div>

        <div class="form-group">
            <input list="specialization-options" id="artist_specialization" name="artist_specialization" placeholder="Specialization" required>
            <datalist id="specialization-options">
                <option value="Painter">
                <option value="Sculptor">
                <option value="Charcoal/Pencil Drawing">
                <option value="Digital Artist">
                <option value="Photographer">
            </datalist>
        </div>

        <div class="form-group">
            <label for="artist_valid_id">Upload Valid ID:</label>
            <h5>Front ID</h5>
            <input type="file" id="artist_front_valid_id" name="artist_front_valid_id" accept="image/*,application/pdf" required>
            <h5>Back ID</h5>
            <input type="file" id="artist_back_valid_id" name="artist_back_valid_id" accept="image/*,application/pdf" required>
            <a class="note" >*NOTE: List of valid ID - ACR/ICR, Driver's License, GSIS e-Card, Pasport, Postal ID, PRC ID, School ID, Senior Citizen Card, SSS Card, Votr's ID, PhilSys ID, PWD ID.</a> 
        </div>

        <div class="form-group">
            <button type="submit" name="submit_artist">REGISTER</button>
        </div>

        <div class="form-group login-text">
            <p>Already have an account? <a href="/cartcraft/Register/Login/login.php">Login now</a></p>
        </div>
    </form>
</div>
</div>

<script>

    window.onload = function() {
        toggleForm('customer');
    };

    function toggleForm(formType) {
        const customerForm = document.getElementById('customerForm');
        const artistForm = document.getElementById('artistForm');
        const customerBtn = document.getElementById('customerBtn');
        const artistBtn = document.getElementById('artistBtn');

        if (formType === 'customer') {
            customerForm.classList.add('active');
            artistForm.classList.remove('active');
            customerBtn.classList.add('active');
            artistBtn.classList.remove('active');
        } else {
            artistForm.classList.add('active');
            customerForm.classList.remove('active');
            artistBtn.classList.add('active');
            customerBtn.classList.remove('active');
        }
    }
</script>

</body>
</html>
