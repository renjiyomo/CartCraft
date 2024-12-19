<?php
session_start();
session_destroy();

header('Location: /cartcraft/Register/Login/login.php');
exit();
?>
