<?php

require_once '../../database/querys.php';
session_start();

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
            <form action="addOrModifyProduct.php" method="POST">
                <input type="hidden" name="accion" value="agregar_producto">
                <button type="submit" class="custom-btn btn"><span>Agregar producto</span></button>
            </form>
            <form action="../../verifications/paginaIntermedia.php" method="POST">
                <input type="hidden" name="accion" value="eliminar_producto">
                <button type="submit" class="custom-btn btn"><span>Eliminar seleccionados</span></button>
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
                <?php obtenerProductosAdmin(); ?>
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