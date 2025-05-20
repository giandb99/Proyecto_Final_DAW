<?php

session_start();
require_once '../../database/querys.php';
require_once '../../session_timeout.php';

$usuarioLogueado = isset($_SESSION['usuario']['id']);
$productosFavoritos = getFavoriteProducts($usuarioLogueado ? $_SESSION['usuario']['id'] : null);
$totalFavoritos = count($productosFavoritos);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/favs.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">
        <?php if (!$usuarioLogueado): ?>
            <section class="favorites-container">
                <div class="login-message">
                    <h1 class="section-title">Inicia sesión para ver tus favoritos</h1>
                    <p class="section-subtitle">Accede a tu cuenta para ver y gestionar tus juegos guardados.</p>
                    <button type="button" class="custom-btn btn-user" onclick="window.location.href='login.php'">
                        <span>Iniciar sesión</span>
                    </button>
                </div>
            </section>
        <?php else: ?>

            <section class="favorites-container">
                <?php if ($totalFavoritos > 0): ?>
                    <div class="favorites-header">
                        <h1>Tu lista de favoritos</h1>
                        <p class="favorites-subtitle">Revisa los productos que has guardado como favoritos.</p>
                        <h3 id="favorites-count">
                            Tienes <?= $totalFavoritos ?> juego<?= $totalFavoritos === 1 ? '' : 's' ?> en tu lista de favoritos.
                        </h3>
                    </div>
                <?php endif; ?>

                <div class="favorites-content">
                    <?php if ($totalFavoritos === 0): ?>
                        <div class="empty-fav">
                            <h2>Tu lista de favoritos está vacía</h2>
                            <p>¡No te preocupes! Descubre los mejores videojuegos en nuestro catálogo.</p>
                            <button type="button" class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                                <span>Explora nuestro catálogo <i class="fas fa-gamepad"></i></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="favorites-grid">
                            <?php foreach ($productosFavoritos as $producto): ?>
                                <div class="favorite-card" onclick="window.location.href='detailProduct.php?id=<?= $producto['id'] ?>'">
                                    <img src="../../<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                    <div class="card-info">
                                        <div class="card-actions">
                                            <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                                            <button class="custom-btn btn-user" type="button" onclick="event.stopPropagation(); addToFav(<?= $producto['id'] ?>)">
                                                <span><i id="fav-icon-<?= $producto['id'] ?>" class="fas fa-heart-broken"></i></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <script src="../../scripts/nav.js"></script>
    <script src="../../scripts/favs.js"></script>
    <script src="../../scripts/popup.js"></script>
    
    <?php include '../elements/footer.php' ?>