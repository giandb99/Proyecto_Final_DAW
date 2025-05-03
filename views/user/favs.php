<?php

require_once '../../database/querys.php';
session_start();

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
    <link rel="stylesheet" href="../../styles/catalog.css">
    <link rel="stylesheet" href="../../styles/logout.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">

        <?php if (!$usuarioLogueado): ?>
            <div class="login-message">
                <div>
                    <h1>Debes iniciar sesión para acceder a esta página</h1>
                    <button type="button" class="custom-btn btn-user" onclick="window.location.href='login.php'">
                        <span>Iniciar sesión</span>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="welcome-message">
                <h1>Bienvenido <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></h1>
                <p>Explora tus juegos favoritos y añade más a tu colección.</p>
            </div>
            <div class="favorites-container">
                <h2 class="favorites-title">Tus Favoritos</h2>
                <?php if ($totalFavoritos === 0): ?>
                    <h3>No tienes ningún juego en tu lista de favoritos.</h3>
                    <div>
                        <button type="button" class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                            <span>Explorá nuestros productos <i class="fas fa-gamepad"></i></span>
                        </button>
                    </div>
                <?php else: ?>
                    <h3 id="favorites-count">
                        Tienes <?= $totalFavoritos ?> juego<?= $totalFavoritos === 1 ? '' : 's' ?> en tu lista de favoritos.
                    </h3>
                <?php endif; ?>
                <div class="favorites-grid">
                    <?php foreach ($productosFavoritos as $producto): ?>
                        <div class="favorite-card" onclick="window.location.href='product.php?id=<?= $producto['id'] ?>'">
                            <img src="../../<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                            <div class="card-info">
                                <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                                <p><?= htmlspecialchars($producto['plataforma']) ?> - <?= htmlspecialchars($producto['genero']) ?></p>
                                <div class="card-actions">
                                    <span class="price">$<?= number_format($producto['precio'], 2) ?></span>
                                    <div class="buttons">
                                        <form id="favorito-form-<?= $producto['id'] ?>" method="post">
                                            <input type="hidden" name="accion" value="agregar_favorito">
                                            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                                            <button class="custom-btn btn-user" type="button" onclick="event.stopPropagation(); addToFav(<?= $producto['id'] ?>)">
                                                <span><i id="fav-icon-<?= $producto['id'] ?>" class="fas fa-heart-broken"></i></span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/addToFav.js"></script>
    <script src="../../scripts/logout.js"></script>