<?php

session_start();
require_once '../../database/querys.php';
require_once '../../session_timeout.php';

$usuarioLogueado = isset($_SESSION['usuario']['id']);
$pedidos = $usuarioLogueado ? getOrdersByUserId($_SESSION['usuario']['id']) : [];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/userOrder.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Mis pedidos - FreeDays_Games</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">
        <?php if (!$usuarioLogueado): ?>
            <section class="orders-container">
                <div class="login-message">
                    <h1 class="section-title">Inicia sesión para ver tus pedidos</h1>
                    <p class="section-subtitle">Accede a tu cuenta para revisar tu historial de compras.</p>
                    <button type="button" class="custom-btn btn-user" onclick="window.location.href='login.php'">
                        <span>Iniciar sesión</span>
                    </button>
                </div>
            </section>
        <?php else: ?>
            <section class="orders-container">
                <?php if (count($pedidos) == 0): ?>
                    <div class="empty-orders">
                        <h2>No has realizado ningún pedido aún</h2>
                        <p>¡Explora nuestro catálogo y encontrá tus juegos favoritos!</p>
                        <button type="button" class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                            <span>Explora nuestro catálogo <i class="fas fa-gamepad"></i></span>
                        </button>
                    </div>

                <?php elseif (count($pedidos) > 0): ?>
                    <div class="orders-header">
                        <h1>Tu historial de pedidos</h1>
                        <p class="orders-subtitle">Aquí puedes ver todos los pedidos que realizaste.</p>
                    </div>
                    <div class="orders-content">
                        <div class="orders-list">
                            <?php foreach ($pedidos as $pedido): ?>
                                <div class="order-cards">
                                    <div class="order-card" onclick="window.location.href='orderDetail.php?id=<?= $pedido['pedido_id'] ?>'">
                                        <div class="order-products">
                                            <div class="order-status <?= strtolower($pedido['estado']) ?>">
                                                <?= strtoupper($pedido['estado']) ?>
                                            </div>
                                            <div class="products">
                                                <?php foreach ($pedido['productos'] as $producto): ?>
                                                    <div class="order-product-item">
                                                        <img src="../../<?= htmlspecialchars($producto['producto_imagen']) ?>" alt="<?= htmlspecialchars($producto['producto_nombre']) ?>" class="product-image">
                                                        <div class="order-product-details">
                                                            <span class="product-name"><?= htmlspecialchars($producto['producto_nombre']) ?></span>
                                                            <span class="product-platform">Plataforma: <?= htmlspecialchars($producto['plataforma_nombre']) ?></span>
                                                            <span class="product-qty">Cantidad: <?= $producto['cantidad'] ?></span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="order-total">
                                                <h2>Total</h2>
                                                <h2><?= number_format($pedido['precio_total'], 2) ?>€</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="order-info">
                                        <p><strong>Pedido #<?= $pedido['pedido_id'] ?> - Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['creado_en'])) ?></strong></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <script src="../../scripts/nav.js"></script>
    <script src="../../scripts/popup.js"></script>
    
    <?php include '../elements/footer.php' ?>