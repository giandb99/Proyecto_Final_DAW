<?php

require_once '../../database/querys.php';
require_once '../../database/connection.php';
session_start();

// Obtener los datos del carrito
$conn = conexion();
$carritoId = getActiveCartId($conn, $_SESSION['usuario']['id']);
$carritoItems = getCartItems($conn, $carritoId);
$resumenCarrito = getCartSummary($conn, $carritoId);
cerrar_conexion($conn);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/checkout.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/alerts.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Pasarela de Pago</title>
</head>

<body>

    <?php include '../elements/nav.php' ?>

    <main class="main-content">
        <section class="checkout-container">

            <form class="checkout-form" id="checkout-form">
                <!-- Información del Cliente -->
                <div class="checkout-section">
                    <h1 class="checkout-title">Pasarela de Pago</h1>
                    <div class="cliente-section">

                        <h4>Datos del Cliente</h4>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <label for="nombre" class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Juan Pérez">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="correo" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" placeholder="Ej: juan@mail.com">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Ej: Calle Falsa 123">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="pais" class="form-label">País</label>
                                <input type="text" class="form-control" id="pais" name="pais" placeholder="Ej: España">
                            </div>
                        </div>
                        <!-- Contenedor para errores de datos del cliente -->
                        <div class="error-msg-container error-msg-checkout" id="errores-cliente"></div>
                    </div>

                    <!-- Información de la Tarjeta -->
                    <div class="tarjeta-section" id="tarjeta-section">
                        <h5>Datos de la Tarjeta</h5>
                        <div class="card-details">
                            <div class="col-12">
                                <label for="numero_tarjeta" class="form-label">Número de tarjeta</label>
                                <input type="text" class="form-control" id="numero_tarjeta" name="numero_tarjeta" placeholder="0000 0000 0000 0000">
                            </div>
                            <div class="col-12">
                                <label for="nombre_tarjeta" class="form-label">Nombre en la tarjeta</label>
                                <input type="text" class="form-control" id="nombre_tarjeta" name="nombre_tarjeta" placeholder="Como aparece en la tarjeta">
                            </div>
                            <div class="col-6">
                                <label for="vencimiento" class="form-label">Vencimiento</label>
                                <input type="text" class="form-control" id="vencimiento" name="vencimiento" placeholder="MM/AA">
                            </div>
                            <div class="col-6">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123">
                            </div>
                        </div>
                        <!-- Contenedor para errores de datos de la tarjeta -->
                        <div class="error-msg-container error-msg-checkout" id="errores-tarjeta"></div>
                    </div>
                </div>

                <!-- Resumen del Pedido -->
                <div class="order-summary">
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
                    <div class="checkout-summary">
                        <button type="submit" class="custom-btn btn-success">
                            <span>Pagar</span>
                        </button>
                        <button type="button" class="custom-btn btn-back" onclick="window.location.href='cart.php'">
                            <span>Cancelar</span>
                        </button>
                    </div>
                </div>
            </form>

            <div id="payment-loader" class="payment-loader-overlay" style="display:none;">
                <div class="payment-loader-content">
                    <div class="loader"></div>
                    <div id="payment-success-icon" style="display:none; font-size:3rem; color:#28a745;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p id="payment-loader-msg">Procesando pago...</p>
                </div>
            </div>

        </section>
    </main>

    <script src="../../scripts/nav.js"></script>
    <script src="../../scripts/popup.js"></script>
    <script src="../../scripts/checkout.js"></script>

    <!-- FOOTER -->
    <?php include '../elements/footer.php' ?>