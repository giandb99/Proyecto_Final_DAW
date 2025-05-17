<?php

require_once '../../database/querys.php';
require_once '../../database/connection.php';
session_start();

$usuarioLogueado = isset($_SESSION['usuario']['id']);
$carritoItems = [];
$resumenCarrito = ['total' => 0, 'descuento' => 0, 'subtotal' => 0];

if ($usuarioLogueado) {
    $conn = conexion();
    $carritoId = getActiveCartId($conn, $_SESSION['usuario']['id']);
    $carritoItems = getCartItems($conn, $carritoId);
    $resumenCarrito = getCartSummary($conn, $carritoId);
    cerrar_conexion($conn);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/cart.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/footer.css">
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
                <?php if (count($carritoItems) == 0): ?>
                    <div class="empty-cart">
                        <h2>Tu carrito está vacío</h2>
                        <p>¡No te preocupes! Descubre los mejores videojuegos en nuestro catálogo.</p>
                        <button type="button" class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                            <span>Explora nuestro catálogo <i class="fas fa-gamepad"></i></span>
                        </button>
                    </div>

                <?php elseif (count($carritoItems) > 0): ?>
                    <div class="cart-header">
                        <h1>Tu carrito de compras</h1>
                        <div class="cart-header-actions">
                            <p class="cart-subtitle">Revisa los productos en tu carrito y procede con tu compra.</p>
                            <button type="button" class="custom-btn btn-empty-cart" onclick="emptyCart()">
                                <span>Vaciar carrito</span>
                            </button>
                        </div>
                    </div>

                    <div class="cart-content">
                        <!-- Lista de productos en el carrito -->
                        <div class="cart-cards">
                            <?php foreach ($carritoItems as $producto): ?>
                                <div class="cart-card" id="cart-card-<?= $producto['id'] ?>">
                                    <div class="cart-card-image">
                                        <img src="../../<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                    </div>
                                    <div class="cart-card-details">
                                        <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                                        <p class="cart-card-platform">Plataforma: <?= htmlspecialchars($producto['plataforma_nombre']) ?></p>
                                    </div>
                                    <div class="cart-card-price">
                                        <h3><?= number_format($producto['precio_descuento'], 2) ?>€</h3>
                                        <select class="cart-qty-select" data-cart-item-id="<?= $producto['id'] ?>">
                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                <option value="<?= $i ?>" <?= $producto['cantidad'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                    </div>
                                    <button class="custom-btn btn-user" type="button" onclick="event.stopPropagation(); removeFromCart(<?= $producto['id'] ?>)">
                                        <span><i class="fas fa-trash"></i></span>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Resumen de la compra -->
                        <div class="cart-summary" id="cart-summary" data-carrito-id="<?= $carritoId ?>">
                            <div class="summary-box">
                                <h2>Resumen</h2>
                                <div class="summary-row">
                                    <span class="summary-label">Precio original:</span>
                                    <span class="summary-value" id="total-price"><?= number_format($resumenCarrito['total'], 2) ?>€</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Descuento:</span>
                                    <span class="summary-value" id="discount">- <?= number_format($resumenCarrito['descuento'], 2) ?>€</span>
                                </div>
                                <div class="summary-row summary-total">
                                    <span class="summary-label"><strong>Subtotal:</strong></span>
                                    <span class="summary-value" id="final-price"><strong><?= number_format($resumenCarrito['subtotal'], 2) ?>€</strong></span>
                                </div>
                                <div class="summary-actions">
                                    <button class="custom-btn btn-checkout" onclick="window.location.href='checkout.php'">
                                        <span>Proceder con el pago</span>
                                    </button>
                                    <button class="custom-btn btn-back" onclick="window.location.href='catalog.php'">
                                        <span>Seguir comprando</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

    </main>

    <!-- FOOTER -->
    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/cart.js"></script>
    <script src="../../scripts/popup.js"></script>