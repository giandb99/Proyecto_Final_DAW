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

$productos = getAllProducts($offset, $limit, $search);
$totalProductos = getTotalProducts($search);
$totalPages = ceil($totalProductos / $limit);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/products.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Productos</title>
</head>

<body>
    <div class="container">

        <?php include '../elements/sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1>Productos disponibles en la plataforma</h1>
                <form class="search-container" method="GET" action="products.php">
                    <input type="text" id="search-input" name="search" placeholder="Buscar producto..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" id="search-button" class="custom-btn btn-icon-buscar"><span><i class="fas fa-search"></i></button></span>
                </form>
                <button type="button" class="custom-btn btn" onclick="window.location.href='addOrModifyProduct.php'"><span>Agregar producto</span></button>
            </div>

            <table class="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Descuento</th>
                        <th>Plataforma</th>
                        <th>Género</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr data-product-id="<?= $producto['id'] ?>">
                                <td><?= $producto['id'] ?></td>
                                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                <td><?= number_format($producto['precio'], 2) ?>€</td>
                                <td><?= $producto['descuento'] ?? '0' ?>%</td>
                                <td><?= htmlspecialchars($producto['plataforma']) ?> </td>
                                <td><?= htmlspecialchars($producto['genero']) ?> </td>
                                <td><?= $producto['activo'] ? 'Sí' : 'No' ?></td>
                                <td class="acciones">
                                    <button onclick="window.location.href='addOrModifyProduct.php?id=<?= $producto['id'] ?>'" class="btn-icon-modificar" title="Modificar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <?php if ($producto['activo']): ?>
                                        <button type="submit" class="btn-icon-eliminar" onclick="deleteProduct(<?= $producto['id'] ?>)" title="Desactivar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn-icon-activar" onclick="activateProduct(<?= $producto['id'] ?>)" title="Activar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center;">No hay productos disponibles.</td>
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

    <script src="../../scripts/popup.js"></script>
    <script src="../../scripts/products.js"></script>

    <?php include '../elements/footer.php'; ?>