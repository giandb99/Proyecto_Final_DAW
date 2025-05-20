<?php

session_start();
require_once '../../database/querys.php';
require_once '../../session_timeout.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Pedido no válido.</p>";
    exit;
}

$pedidoId = intval($_GET['id']);

$datos = getOrderFullDetails($pedidoId);

if (!$datos['pedido'] || empty($datos['productos'])) {
    echo "<p>No se encontraron detalles para este pedido.</p>";
    exit;
}

$pedido = $datos['pedido'];
$detalles = $datos['productos'];

$isAdmin = ($_SESSION['usuario']['rol'] === 'admin');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/orderDetail.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Detalle del Pedido #<?= $pedido['pedido_id'] ?></title>
</head>

<body>

    <div class="container">
        <?php if ($isAdmin): ?>
            <?php include '../elements/sidebar.php'; ?>
        <?php else: ?>
            <?php include '../elements/nav.php'; ?>
        <?php endif; ?>

        <main class="<?= $isAdmin ? 'main-content-admin' : 'main-content' ?>">
            <section class="<?= $isAdmin ? 'order-details-container-admin' : 'order-details-container' ?>">

                <div class="padded-section">
                    <div class="back-button-container">
                        <?php if ($isAdmin): ?>
                            <a href="../admin/orders.php" class="back-button">
                                <span><i class="fas fa-arrow-left"></i> Volver</span>
                            </a>
                            <div class="admin-buttons">
                                <button class="custom-btn order-status-btn" onclick="actualizarEstadoPedido(<?= $pedido['pedido_id'] ?>, 'entregado')"><span>Marcar como entregado</span></button>
                                <button class="custom-btn order-status-btn" onclick="actualizarEstadoPedido(<?= $pedido['pedido_id'] ?>, 'cancelado')"><span>Marcar como cancelado</span></button>
                            </div>
                        <?php else: ?>
                            <a href="../user/userOrder.php" class="back-button">
                                <span><i class="fas fa-arrow-left"></i> Volver</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="padded-section">
                    <div class="pdf-pedido factura-box">
                        <div class="factura-header">
                            <h2 class="title"><i class="fas fa-file-invoice"></i> Detalle del Pedido #<?= $pedido['pedido_id'] ?></h2>
                            <?php if (!$isAdmin): ?>
                                <a href="#" class="download-pdf-btn" data-pedido="<?= $pedido['pedido_id'] ?>">
                                    <i class="fas fa-file-pdf"></i> Descargar PDF
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="factura-encabezado">
                            <div class="factura-columna">
                                <div><strong><i class="fas fa-user"></i></strong>Cliente: <?= htmlspecialchars($pedido['usuario_nombre']) ?></div>
                                <div><strong><i class="fas fa-map-marker-alt"></i></strong>Dirección: <?= htmlspecialchars($pedido['facturacion_direccion']) ?></div>
                                <div><strong><i class="fas fa-flag"></i></strong>País: <?= htmlspecialchars($pedido['facturacion_pais']) ?></div>
                                <div><strong><i class="fas fa-envelope"></i></strong>Email: <?= htmlspecialchars($pedido['facturacion_correo']) ?></div>
                            </div>
                            <div class="factura-columna">
                                <div>
                                    <strong><i class="fas fa-info-circle"></i>Estado: </strong>
                                    <?php
                                    $estado = strtolower($pedido['estado']);
                                    $clase = "badge-estado";
                                    if ($estado == "pendiente") $clase .= " pendiente";
                                    elseif ($estado == "entregado") $clase .= " entregado";
                                    elseif ($estado == "cancelado") $clase .= " cancelado";
                                    ?>
                                    <span class="<?= $clase ?>"><?= ucfirst($pedido['estado']) ?></span><br>
                                </div>
                                <div><strong><i class="fas fa-calendar-alt"></i>Fecha: </strong> <?= date("d/m/Y H:i", strtotime($pedido['creado_en'])) ?></div>
                                <?php if ($pedido['numero_tarjeta']): ?>
                                    <div><strong><i class="fas fa-credit-card"></i>Tarjeta: </strong> **** **** **** <?= htmlspecialchars($pedido['numero_tarjeta']) ?></div>
                                    <div><strong><i class="fas fa-hourglass-half"></i>Vencimiento:</strong> <?= htmlspecialchars($pedido['vencimiento_tarjeta']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="factura-tabla">
                            <table>
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-gamepad"></i> Producto</th>
                                        <th><i class="fas fa-desktop"></i> Plataforma</th>
                                        <th><i class="fas fa-sort-numeric-up"></i> Cantidad</th>
                                        <th><i class="fas fa-dollar-sign"></i> Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalles as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['producto_nombre']) ?></td>
                                            <td><?= htmlspecialchars($item['plataforma_nombre']) ?></td>
                                            <td><?= $item['cantidad'] ?></td>
                                            <td><?= number_format($item['precio_total'], 2) ?>€</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="factura-total">
                            <i class="fas fa-coins"></i> <strong>Total pagado: <?= number_format($pedido['precio_total'], 2) ?>€</strong>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="../../scripts/nav.js"></script>
    <script src="../../scripts/popup.js"></script>
    <script src="../../scripts/orderDetail.js"></script>

    <?php include '../elements/footer.php'; ?>