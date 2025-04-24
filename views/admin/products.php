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
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Document</title>
</head>

<body class="container">

    <?php include '../elements/sidebar.php'; ?>

    <main class="main-content">

        <div class="barra-superior">
            <form action="newProduct.php" method="POST">
                <input type="hidden" name="accion" value="agregar_producto">
                <button type="submit" class="custom-btn btn"><span>Agregar producto</span></button>
            </form>
            <form action="../../verifications/paginaIntermedia.php" method="POST">
                <input type="hidden" name="accion" value="desactivar_producto">
                <button type="submit" class="custom-btn btn"><span>Desactivar seleccionados</span></button>
            </form>
        </div>

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
                        <td><img src="../../<?= $producto['imagen'] ?>" alt="Imagen" class="tabla-img"></td>
                        <td><?= $producto['precio'] ?>€</td>
                        <td><?= $producto['descuento'] ?? '0' ?>%</td>
                        <td><?= $producto['stock'] ?></td>
                        <td><?= htmlspecialchars($producto['plataforma']) ?></td>
                        <td><?= htmlspecialchars($producto['genero']) ?></td>
                        <td class="acciones">
                            <form action="modifyProduct.php" method="POST">
                                <input type="hidden" name="accion" value="modificar_producto">
                                <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                                <button type="submit" class="btn-icon-modificar" title="Modificar"><i class="fas fa-pen"></i></button>
                            </form>
                            <form action="../../verifications/paginaIntermedia.php" method="POST">
                                <input type="hidden" name="accion" value="desactivar_producto">
                                <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                                <button type="submit" class="btn-icon-eliminar" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                        <td><input type="checkbox" name="productos_seleccionados[]" value=" ?= $producto['id'] ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <script>
        // Marcar todos los checkboxes
        document.getElementById("checkAll").addEventListener("change", function() {
            const checkboxes = document.querySelectorAll("input[name='productos_seleccionados[]']");
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>

    <?php include '../elements/footer.php' ?>