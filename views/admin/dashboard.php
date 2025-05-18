<?php

require_once '../../database/querys.php';
session_start();

// Solo permitir acceso a admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../user/logout.php');
    exit;
}

// Se obtienen los datos para los widgets usando funciones genéricas
$totalUsuarios        = obtenerTotalGenerico('usuario', 'id', 'count', "rol = 'user'");
$usuariosActivos      = obtenerTotalGenerico('usuario', 'id', 'count', "rol = 'user' AND activo = 1");
$usuariosInactivos    = obtenerTotalGenerico('usuario', 'id', 'count', "rol = 'user' AND activo = 0");

$totalProductos       = obtenerTotalGenerico('producto', 'id', 'count');
$productosActivos     = obtenerTotalGenerico('producto', 'id', 'count', "activo = 1");
$productosInactivos   = obtenerTotalGenerico('producto', 'id', 'count', "activo = 0");

$totalPedidos         = obtenerTotalGenerico('pedido', 'id', 'count');
$pedidosPendientes    = obtenerTotalGenerico('pedido', 'id', 'count', "estado = 'pendiente'");
$pedidosEntregados    = obtenerTotalGenerico('pedido', 'id', 'count', "estado = 'entregado'");
$pedidosCancelados    = obtenerTotalGenerico('pedido', 'id', 'count', "estado = 'cancelado'");

$gananciasTotales     = obtenerTotalGenerico('pedido', 'precio_total', 'sum');
$gananciasSemana      = obtenerSumaUltimosDias('pedido', 'precio_total', 7, 'creado_en');
$gananciasMes         = obtenerSumaUltimosDias('pedido', 'precio_total', 30, 'creado_en');
$ganancias3Meses      = obtenerSumaUltimosDias('pedido', 'precio_total', 90, 'creado_en');
$gananciasAnio        = obtenerSumaUltimosDias('pedido', 'precio_total', 365, 'creado_en');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/dashboard.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>
    <div class="container">

        <?php include '../elements/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-header">
                <h1>Panel de Administración</h1>
            </header>

            <section class="dashboard-summary">
                <div class="summary-item users">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="summary-title">Usuarios</div>
                        <div class="summary-value"><?php echo $totalUsuarios; ?></div>
                        <div class="summary-detail">
                            <span class="active"><i class="fas fa-user-check"></i> <?php echo $usuariosActivos; ?></span>
                            <span class="inactive"><i class="fas fa-user-times"></i> <?php echo $usuariosInactivos; ?></span>
                        </div>
                    </div>
                </div>
                <div class="summary-item products">
                    <div class="icon"><i class="fas fa-gamepad"></i></div>
                    <div>
                        <div class="summary-title">Productos</div>
                        <div class="summary-value"><?php echo $totalProductos; ?></div>
                        <div class="summary-detail">
                            <span class="active"><i class="fas fa-check-circle"></i> <?php echo $productosActivos; ?></span>
                            <span class="inactive"><i class="fas fa-times-circle"></i> <?php echo $productosInactivos; ?></span>
                        </div>
                    </div>
                </div>
                <div class="summary-item orders">
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    <div>
                        <div class="summary-title">Pedidos</div>
                        <div class="summary-value"><?php echo $totalPedidos; ?></div>
                        <div class="summary-detail">
                            <span class="pending"><i class="fas fa-clock"></i> <?php echo $pedidosPendientes; ?></span>
                            <span class="delivered"><i class="fas fa-truck"></i> <?php echo $pedidosEntregados; ?></span>
                            <span class="cancelled"><i class="fas fa-ban"></i> <?php echo $pedidosCancelados; ?></span>
                        </div>
                    </div>
                </div>
                <div class="summary-item earnings">
                    <div class="icon"><i class="fas fa-euro-sign"></i></div>
                    <div>
                        <div class="summary-title">Ganancias</div>
                        <div class="summary-value">€<?php echo number_format($gananciasTotales, 2, ',', '.'); ?></div>
                        <div class="summary-detail">
                            <span class="week">Semana: €<?php echo number_format($gananciasSemana, 2, ',', '.'); ?></span>
                            <span class="month">Mes: €<?php echo number_format($gananciasMes, 2, ',', '.'); ?></span>
                            <span class="3months">3 Meses: €<?php echo number_format($ganancias3Meses, 2, ',', '.'); ?></span>
                            <span class="year">Año: €<?php echo number_format($gananciasAnio, 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dashboard-carousel">
                <div class="carousel-container">
                    <div class="carousel-track">
                        <div class="carousel-slide">
                            <h3>Usuarios Activos vs Inactivos</h3>
                            <canvas id="usuariosChart"></canvas>
                        </div>
                        <div class="carousel-slide">
                            <h3>Nuevos Usuarios por Mes</h3>
                            <canvas id="usuariosNuevosChart"></canvas>
                        </div>
                        <div class="carousel-slide">
                            <h3>Productos Activos vs Inactivos</h3>
                            <canvas id="productosChart"></canvas>
                        </div>
                        <div class="carousel-slide">
                            <h3>Top Productos Más Vendidos</h3>
                            <canvas id="topProductosChart"></canvas>
                        </div>
                        <div class="carousel-slide">
                            <h3>Top Plataformas Más Vendidas</h3>
                            <canvas id="topPlataformasChart"></canvas>
                        </div>
                        <div class="carousel-slide">
                            <h3>Top Usuarios Compradores</h3>
                            <canvas id="topUsuariosChart"></canvas>
                        </div>
                        <div class="carousel-slide">
                            <h3>Pedidos por Estado</h3>
                            <canvas id="pedidosEstadoChart"></canvas>
                        </div>
                        <div class="carousel-slide">
                            <h3>Ganancias por Mes</h3>
                            <canvas id="gananciasMensualesChart"></canvas>
                        </div>
                        <div class="carousel-slide">
                            <h3>Ganancias por Periodo</h3>
                            <canvas id="gananciasPeriodoChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="carousel-controls">
                    <button class="prev-slide"><i class="fas fa-chevron-left"></i></button>
                    <button class="next-slide"><i class="fas fa-chevron-right"></i></button>
                </div>
            </section>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../scripts/dashboard.js"></script>

    <?php include '../elements/footer.php' ?>