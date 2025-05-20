<?php

session_start();
require_once '../../database/querys.php';
require_once '../../session_timeout.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../user/logout.php');
    exit;
}

$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '' && is_numeric($search)) {
    $pedidos = getOrderById(intval($search));
    $totalPages = 1;
} else {
    $pedidos = getAllOrdersPaginated($offset, $limit);
    $totalPedidos = getTotalOrders();
    $totalPages = ceil($totalPedidos / $limit);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/orders.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Pedidos</title>
</head>

<body>
    <div class="container">

        <?php include '../elements/sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1>Pedidos realizados en la plataforma</h1>
                <form class="search-container" method="GET" action="orders.php">
                    <input type="text" id="search-input" name="search" placeholder="Buscar pedido por ID..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" id="search-button" class="custom-btn btn-icon-buscar"><span><i class="fas fa-search"></i></span></button>
                </form>
            </div>

            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Usuario</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pedidos)): ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($pedido['pedido_id']) ?></td>
                                <td><?= htmlspecialchars($pedido['usuario_nombre'] ?? '-') ?></td>
                                <td><?= number_format($pedido['precio_total'], 2) ?>€</td>
                                <td class="<?php
                                            $estado = strtolower($pedido['estado']);
                                            if ($estado === 'cancelado') {
                                                echo 'estado-cancelado';
                                            } elseif ($estado === 'entregado') {
                                                echo 'estado-entregado';
                                            } elseif ($estado === 'pendiente') {
                                                echo 'estado-pendiente';
                                            } else {
                                                echo 'estado-otro';
                                            }
                                            ?>">
                                    <?= strtoupper($pedido['estado']) ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($pedido['creado_en'])) ?></td>
                                <td>
                                    <a href="../user/orderDetail.php?id=<?= $pedido['pedido_id'] ?>" class="btn-detail" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No hay pedidos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <?php if ($totalPages > 1 && $search === ''): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="pagination-btn<?= $i == $page ? ' active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php include '../elements/footer.php' ?>