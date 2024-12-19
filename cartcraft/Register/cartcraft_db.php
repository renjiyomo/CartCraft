<?php

$conn = mysqli_connect('localhost', 'root', '', 'cart_craft');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
