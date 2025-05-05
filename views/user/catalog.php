<?php

require_once '../../database/querys.php';
session_start();

$usuarioLogueado = isset($_SESSION['usuario']['id']);
$productos = getCatalog();

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
                <h2 class="catalog-title">Juegos Populares</h2>
                <div class="catalog-items">
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <?php
                            $precioFinal = $producto['descuento'] ? $producto['precio'] - ($producto['precio'] * $producto['descuento'] / 100) : $producto['precio'];

                            // Verificamos si el producto ya está en favoritos del usuario
                            $isFav = false;
                            if (isset($_SESSION['usuario']['id'])) {
                                $favoritoId = getActiveFavListId($_SESSION['usuario']['id']);
                                $isFav = productIsAlreadyFavorite($favoritoId, $producto['id']);
                            }
                            ?>
                            <div class="product-card" onclick="window.location.href='product.php?id=<?= $producto['id'] ?>'">
                                <div class="relative">
                                    <img src="../../<?= htmlspecialchars($producto['imagen'] ?: 'placeholder.svg') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                    <?php if ($producto['descuento']): ?>
                                        <div class="discount-tag"><?= $producto['descuento'] ?>% OFF</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <div class="title-container">
                                        <h3 class="game-title"><?= htmlspecialchars($producto['nombre']) ?></h3>
                                        <div class="rating">
                                            <span class="star">⭐</span>
                                            <span><?= number_format($producto['valoracion_promedio'], 1) ?></span>
                                        </div>
                                    </div>
                                    <div class="description-container">
                                        <p class="description"><?= htmlspecialchars($producto['descripcion']) ?></p>
                                    </div>
                                    <div class="foot-container">
                                        <div class="price-container">
                                            <?php if ($producto['descuento']): ?>
                                                <span class="price">$<?= number_format($precioFinal, 2) ?></span>
                                                <span class="old-price">$<?= number_format($producto['precio'], 2) ?></span>
                                            <?php else: ?>
                                                <span class="price">$<?= number_format($producto['precio'], 2) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="buttons-container">
                                            <?php if ($usuarioLogueado): ?>
                                                <form id="favorito-form-<?= $producto['id'] ?>" method="post">
                                                    <input type="hidden" name="accion" value="agregar_favorito">
                                                    <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                                                    <button type="button" class="custom-btn btn-user" id="fav-btn-<?= $producto['id'] ?>" onclick="event.stopPropagation(); addToFav(<?= $producto['id'] ?>)">
                                                        <span><i id="fav-icon-<?= $producto['id'] ?>" class="<?= $isFav ? 'fas' : 'far' ?> fa-heart"></i></span>
                                                    </button>
                                                </form>
                                                <form id="carrito-form-<?= $producto['id'] ?>" method="post">
                                                    <input type="hidden" name="accion" value="agregar_carrito">
                                                    <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                                                    <input type="hidden" name="cantidad" value="1">
                                                    <button type="button" class="custom-btn btn-user" onclick="event.stopPropagation(); addToCart(<?= $producto['id'] ?>)">
                                                        <span>Agregar al carrito</span>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="custom-btn btn-user" onclick="event.stopPropagation(); window.location.href='catalog.php?agregar_favorito=error'">
                                                    <span><i id="fav-icon-<?= $producto['id'] ?>" class="far fa-heart"></i></span>
                                                </button>
                                                <button type="button" class="custom-btn btn-user" onclick="event.stopPropagation(); window.location.href='catalog.php?agregar_carrito=error'">
                                                    <span>Agregar al carrito</span>
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

    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/favs.js"></script>
    <script src="../../scripts/cart.js"></script>
    <script src="../../scripts/popup.js"></script>