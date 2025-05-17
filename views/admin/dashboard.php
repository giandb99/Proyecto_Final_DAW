<?php

require_once '../../database/querys.php';
session_start();

// Solo permitir acceso a admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../user/logout.php');
    exit;
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
    <link rel="stylesheet" href="../../styles/dashboard.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
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

            <section class="cards">
                <div class="info-card">
                    <h3>Productos Activos</h3>
                    <p><?php echo $totalProductosActivos; ?></p>
                </div>
                <div class="info-card">
                    <h3>Pedidos</h3>
                    <p>esta semana</p>
                </div>
                <div class="info-card">
                    <h3>Ingresos</h3>
                    <p>€2,345.00</p>
                </div>
                <div class="info-card">
                    <h3>Usuarios</h3>
                    <p>registrados</p>
                </div>
            </section>
        </main>
    </div>

    <?php include '../elements/footer.php' ?>