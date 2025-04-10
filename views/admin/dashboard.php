<?php

// Incluimos el archivo que contiene las funciones de consulta a la base de datos
require_once '../../database/querys.php';

session_start();

if (!isset($_SESSION)) {
    header('Location: ../user/login.php');
    exit();
}

// Se obtienen los datos para los widgets de la página de administración
$totalProductosActivos = obtenerTotalProductosActivos();
$totalUsuarios = obtenerTotalUsuarios();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="../../styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>FreeDays_Games - Compra online de videojuegos y mucho más</title>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <h2 class="sidebar-title">Panel de Administración</h2>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Productos</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Usuarios</a></li>
                    <li><a href="finance.php"><i class="fas fa-dollar-sign"></i> Finanzas</a></li>
                    <li><a href="../user/preferences.php"><i class="fas fa-cog"></i> Ajustes</a></li>
                    <li><a href="../user/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
                </ul>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1>Panel de Administración</h1>
            </header>

            <section class="dashboard-cards">
                <div class="card">
                    <h3>Productos Activos</h3>
                    <p><?php echo $totalProductosActivos; ?></p>
                </div>
                <div class="card">
                    <h3>Pedidos</h3>
                    <p>esta semana</p>
                </div>
                <div class="card">
                    <h3>Ingresos</h3>
                    <p>€2,345.00</p>
                </div>
                <div class="card">
                    <h3>Usuarios</h3>
                    <p>registrados</p>
                </div>
            </section>
        </main>
    </div>

    <?php include '../elements/footer.php' ?>