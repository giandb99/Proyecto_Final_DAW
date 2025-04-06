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
    <link rel="stylesheet" href="../../styles/header.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>
    <nav class="navbar">
        <div class="logo">FreeDays_Games</div>
        <ul>
            <li><a href="catalog.php">Inicio</a></li>
            <li><a href="cart.php">Carrito</a></li>
            <li><a href="favs.php">Favoritos</a></li>
        </ul>
    </nav>

    <section class="container">
        <div class="catalog-container">
            <h2 class="catalog-title">Juegos Populares</h2>
            <div class="catalog-items">
                <?php obtenerProductosClientes(); ?>
            </div>
        </div>
    </section>

    <script src="js/script.js"></script>

    <footer class="footer">
        <p>&copy; 2025 FreeDays_Games. Todos los derechos reservados.</p>
    </footer>

</body>

</html>