<?php

session_start();

if (!isset($_SESSION)) {
    header('Location: login.php');
    exit();
}

?>