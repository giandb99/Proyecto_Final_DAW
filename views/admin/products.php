<?php

require_once '../../database/querys.php';
session_start();

$productos = obtenerTodosLosProductos();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/listProductsAdmin.css">
    <link rel="stylesheet" href="../../styles/dashboard.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Document</title>
</head>

<body class="dashboard-container">

    <!-- Sidebar o Botón para volver -->
    <?php include '../elements/sidebar.php'; ?>

    <main class="main-content">

        <div class="barra-superior">
            <a href="newProduct.php" class="btn-agregar">+ Agregar producto</a>
            <form action="../../verifications/paginaIntermedia.php" method="POST" class="form-desactivar">
                <input type="hidden" name="action" value="desactivar_producto">
                <button type="submit" class="btn-desactivar">Desactivar seleccionados</button>
            </form>
        </div>

        <form action="../../verifications/paginaIntermedia.php" method="POST">
            <input type="hidden" name="action" value="desactivar_producto">

            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Imagen</th>
                        <th>Precio</th>
                        <th>Descuento</th>
                        <th>Stock</th>
                        <th>Plataforma</th>
                        <th>Género</th>
                        <th>Acciones</th>
                        <th><input type="checkbox" id="checkAll"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?= $producto['id'] ?></td>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td><img src="<?= $producto['imagen'] ?>" alt="Imagen" class="tabla-img"></td>
                            <td><?= $producto['precio'] ?>€</td>
                            <td><?= $producto['descuento'] ?? '0' ?>%</td>
                            <td><?= $producto['stock'] ?></td>
                            <td><?= htmlspecialchars($producto['plataforma']) ?></td>
                            <td><?= htmlspecialchars($producto['genero']) ?></td>
                            <td class="acciones">
                                <a href="newProduct.php?id=<?= $producto['id'] ?>" class="btn-icon ver" title="Ver">&#128065;</a>
                                <a href="eliminar_producto.php?id=<?= $producto['id'] ?>" class="btn-icon eliminar" title="Eliminar">&#10060;</a>
                            </td>
                            <td><input type="checkbox" name="productos_seleccionados[]" value="<?= $producto['id'] ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </main>

    <script>
        // Marcar todos los checkboxes
        document.getElementById("checkAll").addEventListener("change", function() {
            const checkboxes = document.querySelectorAll("input[name='productos_seleccionados[]']");
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>

    <?php include '../elements/footer.php' ?>