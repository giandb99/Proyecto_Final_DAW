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
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>
    <nav class="navbar">
        <div class="logo">GameVerse</div>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="#">Carrito</a></li>
            <li><a href="#">Favoritos</a></li>
        </ul>
    </nav>

    <h1>Tienda de Juegos</h1>
    <div class="catalog-container">
        <?php obtenerProductosAdmin(); ?>
    </div>

    <script src="js/script.js"></script>

    <footer class="footer">
        <p>&copy; 2025 FreeDays_Games. Todos los derechos reservados.</p>
    </footer>

</body>

</html>