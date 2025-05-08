<?php

require_once '../../database/querys.php';
session_start();

$usuarioLogueado = isset($_SESSION['usuario']['id']);

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    echo '<p>ID inválido</p>';
    exit;
}

$producto = getProductById($id);
$plataformas = getPlatformsByProduct($producto['id']);
$productos_relacionados = getRelatedProducts($producto['id']);

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
    <link rel="stylesheet" href="../../styles/detailProduct.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">
        <section class="container">
            <div class="back-button-container">
                <a href="javascript:history.back()" class="back-button">
                    <span><i class="fas fa-arrow-left"></i> Volver</span>
                </a>
            </div>

            <div class="product-detail-container">
                <?php
                // Verificamos si el producto ya está en favoritos del usuario
                $isFav = false;
                if (isset($_SESSION['usuario']['id'])) {
                    $favoritoId = getActiveFavListId($_SESSION['usuario']['id']);
                    $isFav = productIsAlreadyFavorite($favoritoId, $producto['id']);
                }
                ?>

                <div class="product-image-container">
                    <div class="product-image" style="background-image: url('../../<?= htmlspecialchars($producto['imagen'] ?: 'placeholder.svg') ?>');"></div>
                </div>

                <div class="product-info-container">
                    <h1 class="product-title"><?= $producto['nombre'] ?></h1>
                    <p class="product-description"><?= $producto['descripcion'] ?></p>

                    <div class="product-price-container">
                        <?php if ($producto['descuento'] > 0): ?>
                            <div class="product-price-original">
                                $<?= number_format($producto['precio'], 2) ?>
                            </div>
                            <div class="product-price-final">
                                $<?= number_format($producto['precio'] - ($producto['precio'] * ($producto['descuento'] / 100)), 2) ?>
                            </div>
                            <div class="product-badge-discount">
                                -<?= $producto['descuento'] ?>%
                            </div>
                        <?php else: ?>
                            <div class="product-price-final no-discount">
                                $<?= number_format($producto['precio'], 2) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form id="product-form" data-producto-id="<?= $producto['id'] ?>">
                        <label for="plataforma-select">Plataforma:</label>
                        <select id="plataforma-select" name="plataforma_id" required>
                            <option value="">Seleccione una plataforma</option>
                            <?php foreach ($plataformas as $plataforma): ?>
                                <option value="<?= $plataforma['plataforma_id'] ?>"><?= htmlspecialchars($plataforma['plataforma_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <div class="product-details">
                            <p class="stock-display" id="stock-info">Stock disponible: Seleccione una plataforma</p>
                            <p>Fecha de lanzamiento: <?= date("d/m/Y", strtotime($producto['fecha_lanzamiento'])) ?></p>
                        </div>

                        <label for="cantidad-select">Cantidad:</label>
                        <select id="cantidad-select" name="cantidad" required>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>

                    <?php if ($usuarioLogueado): ?>
                        <div class="buttons-container">
                            <button type="button" id="add-to-fav-btn" class="custom-btn btn-user" onclick="addToFav(<?= $producto['id'] ?>)">
                                <span><i id="fav-icon-<?= $producto['id'] ?>" class="<?= $isFav ? 'fas fa-heart-broken' : 'far fa-heart' ?>"></i></span>
                            </button>

                            <button type="button" id="add-to-cart-btn" class="custom-btn btn-user" onclick="addToCart(<?= $producto['id'] ?>)">
                                <span><i class="fas fa-cart-plus"></i> Agregar al carrito</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="buttons-container">
                            <button type="button" id="add-to-fav-btn" class="custom-btn btn-user" onclick="window.location.href='detailProduct.php?id=<?= $producto['id'] ?>&agregar_favorito=error'">
                                <span><i id="fav-icon-<?= $producto['id'] ?>" class="far fa-heart"></i></span>
                            </button>
                            <button type="button" id="add-to-cart-btn" class="custom-btn btn-user" onclick="window.location.href='detailProduct.php?id=<?= $producto['id'] ?>&agregar_carrito=error'">
                                <span><i class="fas fa-cart-plus"></i> Agregar al carrito</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <?php if (!empty($productos_relacionados)): ?>
                    <div class="related-products-container">
                        <h2 class="related-title">Productos Relacionados</h2>
                        <div class="related-carousel slick-carousel">
                            <?php foreach ($productos_relacionados as $relacionados): ?>
                                <div class="related-card" onclick="window.location.href='detailProduct.php?id=<?= $relacionados['id'] ?>'">
                                    <img src="../../<?= htmlspecialchars($relacionados['imagen'] ?: 'placeholder.svg') ?>" alt="<?= htmlspecialchars($relacionados['nombre']) ?>">
                                    <h3><?= htmlspecialchars($relacionados['nombre']) ?></h3>
                                    <p class="price">
                                        <?php if ($relacionados['descuento']): ?>
                                            <span class="original-price">$<?= number_format($relacionados['precio'], 2) ?></span>
                                            <span class="discounted-price">
                                                $<?= number_format($relacionados['precio'] * (1 - $relacionados['descuento'] / 100), 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="discounted-price">$<?= number_format($relacionados['precio'], 2) ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/popup.js"></script>
    <script src="../../scripts/detailProduct.js"></script>
    <script src="../../scripts/favs.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>