<?php

session_start();
require_once '../../database/querys.php';
require_once '../../session_timeout.php';

$usuarioLogueado = isset($_SESSION['usuario']['id']);

$nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';
$productos = getCatalog($nombre);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/catalog.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">
        <section class="container">
            <div class="catalog-container">

                <section class="search-bar">
                    <form id="filter-form">
                        <input type="hidden" name="nombre" id="filter-nombre-hidden" value="<?= isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '' ?>">
                        <select name="genero" id="genero-select">
                            <option value="">Todos los géneros</option>
                            <?php foreach (getAllGenres() as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= $g['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="plataforma" id="plataforma-select">
                            <option value="">Todas las plataformas</option>
                            <?php foreach (getAllPlatforms() as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" class="price-input" name="precioMin" placeholder="Precio mínimo" min="0" />
                        <input type="number" class="price-input" name="precioMax" placeholder="Precio máximo" min="0" />
                        <a href="#" id="clear-filters-btn" class="clear-filters-link" style="display:none;">Limpiar filtros</a>
                    </form>
                </section>

                <h2 class="catalog-title">Juegos Populares</h2>
                <div class="catalog-items" id="catalog-items">
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <?php
                            $precioFinal = $producto['descuento'] ? $producto['precio'] - ($producto['precio'] * $producto['descuento'] / 100) : $producto['precio'];

                            // Se verifica si el producto ya está en favoritos del usuario
                            $isFav = false;
                            if (isset($_SESSION['usuario']['id'])) {
                                $favoritoId = getActiveFavListId($_SESSION['usuario']['id']);
                                $isFav = productIsAlreadyFavorite($favoritoId, $producto['id']);
                            }
                            ?>
                            <div class="product-card" onclick="window.location.href='detailProduct.php?id=<?= $producto['id'] ?>'">
                                <div class="relative">
                                    <img src="../../<?= htmlspecialchars($producto['imagen'] ?: 'placeholder.svg') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                    <?php if ($producto['descuento']): ?>
                                        <div class="discount-tag"><?= $producto['descuento'] ?>% OFF</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <div class="title-container">
                                        <h3 class="game-title"><?= htmlspecialchars($producto['nombre']) ?></h3>
                                    </div>
                                    <div class="description-container">
                                        <p class="description"><?= htmlspecialchars($producto['descripcion']) ?></p>
                                    </div>
                                    <div class="foot-container">
                                        <div class="price-container">
                                            <?php if ($producto['descuento']): ?>
                                                <span class="price"><?= number_format($precioFinal, 2) ?>€</span>
                                                <span class="old-price"><?= number_format($producto['precio'], 2) ?>€</span>
                                            <?php else: ?>
                                                <span class="price"><?= number_format($producto['precio'], 2) ?>€</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="buttons-container">
                                            <?php if ($usuarioLogueado): ?>
                                                <button type="button" id="add-to-fav-btn" class="custom-btn btn-user" onclick="event.stopPropagation(); addToFav(<?= $producto['id'] ?>)">
                                                    <span><i id="fav-icon-<?= $producto['id'] ?>" class="<?= $isFav ? 'fas fa-heart-broken' : 'far fa-heart' ?>"></i></span>
                                                </button>

                                                <button type="button" class="custom-btn btn-user">
                                                    <span>Ver detalles</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" id="add-to-fav-btn" class="custom-btn btn-user" onclick="event.stopPropagation(); window.location.href='catalog.php?id=<?= $producto['id'] ?>&agregar_favorito=error'">
                                                    <span><i id="fav-icon-<?= $producto['id'] ?>" class="far fa-heart"></i></span>
                                                </button>

                                                <button type="button" class="custom-btn btn-user">
                                                    <span>Ver detalles</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-products">Lo sentimos, no hay productos disponibles.<br>Vuelva más tarde</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    
    <script src="../../scripts/nav.js"></script>
    <script src="../../scripts/favs.js"></script>
    <script src="../../scripts/popup.js"></script>
    <script src="../../scripts/catalog.js"></script>

    <?php include '../elements/footer.php' ?>