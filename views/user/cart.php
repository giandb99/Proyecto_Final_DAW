<?php

require_once '../../database/querys.php';
require_once '../../database/connection.php';
session_start();

$usuarioLogueado = isset($_SESSION['usuario']['id']);
$carritoItems = [];
$resumenCarrito = ['total' => 0, 'descuento' => 0, 'total_final' => 0];

if ($usuarioLogueado) {
    $conn = conexion();
    $carritoId = getActiveCartId($conn, $_SESSION['usuario']['id']);
    cerrar_conexion($conn);

    $carritoItems = getCartItems($carritoId);
    $resumenCarrito = getCartSummary($_SESSION['usuario']['id']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/cart.css">
    <link rel="stylesheet" href="../../styles/logout.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">

        <?php if (!$usuarioLogueado): ?>
            <section class="cart-container">
                <div class="login-message">
                    <h1 class="section-title">Inicia sesión para ver tu carrito</h1>
                    <p class="section-subtitle">Accede a tu cuenta para ver y gestionar tu carrito.</p>
                    <button type="button" class="custom-btn btn-user" onclick="window.location.href='login.php'">
                        <span>Iniciar sesión</span>
                    </button>
                </div>
            </section>
        <?php else: ?>

            <section class="cart-container">
                <?php if (count($carritoItems) > 0): ?>
                    <div class="cart-header">
                        <h1>Tu carrito de compras</h1>
                        <p class="cart-subtitle">Revisa los productos en tu carrito y procede con tu compra.</p>
                    </div>
                <?php endif; ?>

                <!-- Productos en el carrito -->
                <div class="cart-content">
                    <!-- Si el carrito está vacío -->
                    <?php if (count($carritoItems) == 0): ?>
                        <div class="empty-cart">
                            <h2>Tu carrito está vacío</h2>
                            <p>¡No te preocupes! Descubre los mejores videojuegos en nuestro catálogo.</p>
                            <button type="button" class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                                <span>Explora nuestro catálogo <i class="fas fa-gamepad"></i></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="cart-items">
                            <?php foreach ($carritoItems as $item): ?>
                                <div class="cart-item" id="cart-item-<?= $item['id'] ?>">
                                    <div class="cart-item-image">
                                        <img src="../../<?= htmlspecialchars($item['imagen']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>">
                                    </div>
                                    <div class="cart-item-details">
                                        <h3><?= htmlspecialchars($item['nombre']) ?></h3>
                                        <p>Cantidad: <?= $item['cantidad'] ?></p>
                                        <p>Precio: $<?= number_format($item['precio_total'], 2) ?></p>
                                    </div>
                                    <button class="custom-btn btn-user" onclick="removeItem(<?= $item['id'] ?>)">
                                        <span>Eliminar</span>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Resumen de la compra -->
                        <div class="cart-summary">
                            <div class="summary-box">
                                <h2>Resumen</h2>
                                <p>Precio original: <span id="total-price">$<?= number_format($resumenCarrito['total'], 2) ?></span></p>
                                <p>Descuento: <span id="discount">- $<?= number_format($resumenCarrito['descuento'], 2) ?></span></p>
                                <p><strong>Subtotal: <span id="final-price">$<?= number_format($resumenCarrito['total_final'], 2) ?></span></strong></p>
                            </div>

                            <div class="summary-actions">
                                <button class="custom-btn btn-user" onclick="window.location.href='checkout.php'">
                                    <span>Proceder al pago</span>
                                </button>
                                <button class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                                    <span>Seguir comprando</span>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <!-- FOOTER -->
    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/logout.js"></script>
    <script src="../../scripts/cart.js"></script>