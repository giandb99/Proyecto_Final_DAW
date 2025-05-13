<?php

require_once '../../database/querys.php';
require_once '../../database/connection.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

// Obtener los datos del carrito
$conn = conexion();
$carritoId = getActiveCartId($conn, $_SESSION['usuario']['id']);
$carritoItems = getCartItems($conn, $carritoId);
$resumenCarrito = getCartSummary($conn, $carritoId);
cerrar_conexion($conn);

// Si el carrito está vacío, redirigir al catálogo
if (empty($carritoItems)) {
    header('Location: catalog.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/checkout.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Pasarela de Pago</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">

        <section class="checkout-container">
            <h1 class="checkout-title">Pasarela de Pago</h1>

            <form class="checkout-form" action="../verifications/checkoutHandler.php" method="POST">
                <!-- Información del Cliente -->
                <div class="checkout-section">
                    <h4>Datos del Cliente</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Juan Pérez" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="correo" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" placeholder="Ej: juan@mail.com" required>
                        </div>
                    </div>
                </div>

                <!-- Resumen del Pedido -->
                <section class="order-summary">
                    <h2>Resumen del Pedido</h2>
                    <ul class="order-list">
                        <?php foreach ($carritoItems as $producto): ?>
                            <li>
                                <span><?= htmlspecialchars($producto['nombre']) ?> (x<?= $producto['cantidad'] ?>)</span>
                                <span>$<?= number_format($producto['cantidad'] * $producto['precio_descuento'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="order-total">
                        <span>Total:</span>
                        <span>$<?= number_format($resumenCarrito['subtotal'], 2) ?></span>
                    </div>
                </section>

                <!-- Método de Pago -->
                <div class="checkout-section">
                    <h4>Método de Pago</h4>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metodo_pago" id="tarjeta" value="tarjeta" checked>
                        <label class="form-check-label" for="tarjeta">Tarjeta de crédito / débito</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="metodo_pago" id="transferencia" value="transferencia">
                        <label class="form-check-label" for="transferencia">Transferencia bancaria</label>
                    </div>
                </div>

                <!-- Información de la Tarjeta -->
                <div class="checkout-section" id="tarjeta-section">
                    <h5>Datos de la Tarjeta</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="numero_tarjeta" class="form-label">Número de tarjeta</label>
                            <input type="text" class="form-control" id="numero_tarjeta" name="numero_tarjeta" placeholder="0000 0000 0000 0000" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombre_tarjeta" class="form-label">Nombre en la tarjeta</label>
                            <input type="text" class="form-control" id="nombre_tarjeta" name="nombre_tarjeta" placeholder="Como aparece en la tarjeta" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="vencimiento" class="form-label">Vencimiento</label>
                            <input type="text" class="form-control" id="vencimiento" name="vencimiento" placeholder="MM/AA" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" required>
                        </div>
                    </div>
                </div>

                <!-- Confirmación -->
                <div class="checkout-summary">
                    <p class="checkout-total">Total a pagar: $<?= number_format($resumenCarrito['subtotal'], 2) ?></p>
                    <button type="submit" class="btn btn-success">Confirmar Pago</button>
                </div>
            </form>
        </section>


    </main>

    <!-- FOOTER -->
    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/checkout.js"></script>
    <script src="../../scripts/popup.js"></script>