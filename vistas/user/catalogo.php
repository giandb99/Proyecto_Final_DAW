<?php

// Incluimos el archivo que contiene las funciones de consulta a la base de datos
require_once '../../BBDD/consultas.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../estilos/catalogo.css">
    <link rel="stylesheet" href="../../estilos/header.css">
    <link rel="stylesheet" href="../../estilos/footer.css">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>
    <nav class="navbar">
        <div class="logo">GameVerse</div>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="#">Tienda</a></li>
            <li><a href="#">Biblioteca</a></li>
            <li><a href="#">Comunidad</a></li>
            <li><a href="#">Soporte</a></li>
        </ul>
    </nav>

    <h1>Tienda de Juegos</h1>
    <div class="catalog-container">
        <?php obtenerJuegos(); ?>
    </div>

    <script src="js/script.js"></script>

    <footer class="footer">
        <p>&copy; 2025 FreeDays_Games. Todos los derechos reservados.</p>
    </footer>

</body>

</html>