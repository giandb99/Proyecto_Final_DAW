<?php

// Incluimos el archivo que contiene las funciones de consulta a la base de datos
require_once '../../database/querys.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/catalog.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>
    
    <?php include '../elements/nav.php' ?>

    <section class="container">
        <div class="catalog-container">
            <h2 class="catalog-title">Juegos Populares</h2>
            <div class="catalog-items">
                <?php obtenerProductosClientes(); ?>
            </div>
        </div>
    </section>

    <?php include '../elements/footer.php' ?>