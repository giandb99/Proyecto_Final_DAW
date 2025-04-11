<?php

// Incluimos el archivo que contiene las funciones de consulta a la base de datos
require_once '../../database/querys.php';

session_start();

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
        <section class="container">
    
            <!-- Contenedor para buscar productos. Incluye una barra de busqueda, filtros segun su género y un filtro slider para el precio 
            <div class="search-container">
                <h2 class="search-title">Buscar Productos</h2>
                <div class="search-bar">
                    <input type="text" placeholder="Buscar...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                <div class="filters">
                    <h2>Filtros</h2>
                    <div class="filter-genre">
                        <label for="genre">Género:</label>
                        <select id="genre" name="genre">
                            <option value="">Todos</option>
                            <option value="accion">Acción</option>
                            <option value="aventura">Aventura</option>
                            <option value="deportes">Deportes</option>
                            <option value="estrategia">Estrategia</option>
                        </select>
                    </div>
                    <div class="filter-price">
                        <label for="price">Precio:</label>
                        <input type="range" id="price" name="price" min="0" max="200" step="5">
                        <span id="price-value">$0 - $200</span>
                    </div>
                </div>
            </div> -->
    
            <div class="catalog-container">
                <h2 class="catalog-title">Juegos Populares</h2>
                <div class="catalog-items">
                    <?php obtenerProductosClientes(); ?>
                </div>
            </div>
    
        </section>
    </main>

    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/logout.js"></script>