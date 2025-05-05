<?php

require_once '../../database/querys.php';

$producto = getProductById($_GET['id']);

if (!$producto) {
    echo '<p>Producto no encontrado</p>';
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/catalog.css">
    <link rel="stylesheet" href="../../styles/logout.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">

    </main>

    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/logout.js"></script>