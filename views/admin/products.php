<?php

require_once '../../database/querys.php';
session_start();

// Configuración de la paginación
$limit = 20; // Número máximo de productos por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Obtener el término de búsqueda
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Obtener productos y el total de productos
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
                    <button type="submit" id="search-button" class="btn-icon-buscar"><i class="fas fa-search"></i></button>
                </form>
                <button type="button" class="custom-btn btn" onclick="window.location.href='addOrModifyProduct.php'"><span>Agregar producto</span></button>
            </div>

            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Imagen</th>
                        <th>Precio</th>
                        <th>Descuento</th>
                        <th>Plataforma</th>
                        <th>Género</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr data-product-id="<?= $producto['id'] ?>">
                                <td><?= $producto['id'] ?></td>
                                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                <td><img src="../../<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen" class="tabla-img"></td>
                                <td><?= number_format($producto['precio'], 2) ?>€</td>
                                <td><?= $producto['descuento'] ?? '0' ?>%</td>
                                <td><?= htmlspecialchars($producto['plataforma']) ?> </td>
                                <td><?= htmlspecialchars($producto['genero']) ?> </td>
                                <td class="acciones">
                                    <button onclick="window.location.href='addOrModifyProduct.php?id=<?= $producto['id'] ?>'" class="btn-icon-modificar" title="Modificar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="submit" class="btn-icon-eliminar" onclick="deleteProduct(<?= $producto['id'] ?>)" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10">No hay productos disponibles.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="pagination-btn">Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="pagination-btn">Siguiente</a>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../elements/footer.php'; ?>

    <script src="../../scripts/sidebar.js"></script>
    <script src="../../scripts/products.js"></script>
    <script src="../../scripts/popup.js"></script>