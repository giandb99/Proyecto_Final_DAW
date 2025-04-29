<?php

require_once '../../database/querys.php';
session_start();

$productos = getAllProdutcs();

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

<body>
    <div class="container">
        <?php include '../elements/sidebar.php'; ?>

        <main class="main-content">

            <div class="barra-superior">
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
                        <th>Stock</th>
                        <th>Plataforma</th>
                        <th>Género</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?= $producto['id'] ?></td>
                                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                <td><img src="../../<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen" class="tabla-img"></td>
                                <td><?= number_format($producto['precio'], 2) ?>€</td>
                                <td><?= $producto['descuento'] ?? '0' ?>%</td>
                                <td><?= $producto['stock'] ?></td>
                                <td><?= htmlspecialchars($producto['plataforma']) ?></td>
                                <td><?= htmlspecialchars($producto['genero']) ?></td>
                                <td class="acciones">
                                    <button onclick="window.location.href='addOrModifyProduct.php?id=<?= $producto['id'] ?>'" class="btn-icon-modificar" title="Modificar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form action="../../verifications/paginaIntermedia.php" method="POST">
                                        <input type="hidden" name="accion" value="eliminar_producto">
                                        <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                                        <button type="submit" class="btn-icon-eliminar" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
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
        </main>
    </div>

    <?php include '../elements/footer.php' ?>
</body>

</html>