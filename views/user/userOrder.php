<?php

require_once '../../database/querys.php';
require_once '../../database/connection.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

$pedidos = getOrderById($conn, $_SESSION['usuario']['id']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/userOrders.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <title>Document</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">
        <section class="checkout-container">
        </section>
    </main>

    <?php include '../elements/footer.php' ?>