<?php

require_once '../../database/querys.php';
session_start();

$plataformas = obtenerPlataformas();
$generos = obtenerGeneros();

$exito = $_GET['exito'] ?? null;
$error = $_GET['error'] ?? null;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/newProduct.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Document</title>
</head>

<body class="container">

    <?php include '../elements/sidebar.php'; ?>

    <main class="main-content">

        <form class="product-form" action="../../verifications/paginaIntermedia.php" method="post" enctype="multipart/form-data">
            <div class="product-container">
                <h2>Agregar nuevo producto</h2>

                <input type="hidden" name="accion" value="agregar_producto">

                <input type="text" id="name" name="name" placeholder="Nombre del producto">
                <textarea id="description" name="description" placeholder="Descripción"></textarea>
                <input type="date" id="release_date" name="release_date" placeholder="Fecha de lanzamiento">
                <input type="number" id="price" name="price" placeholder="Precio" step="0.01" min="0">
                <input type="number" id="discount" name="discount" step="5" min="0" max="100" placeholder="Descuento">
                <input type="number" id="stock" name="stock" placeholder="Stock">

                <label for="plataforma">Plataforma:</label>
                <select id="plataform" name="plataform">
                    <option value="">Seleccione una plataforma</option>
                    <?php foreach ($plataformas as $plataforma): ?>
                        <option value="<?= $plataforma['id']; ?>"><?= htmlspecialchars($plataforma['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="genero">Género:</label>
                <select id="gender" name="gender">
                    <option value="">Seleccione un género</option>
                    <?php foreach ($generos as $genero): ?>
                        <option value="<?= $genero['id']; ?>"><?= htmlspecialchars($genero['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="imagen">Imagen del producto:</label>
                <input type="file" id="image" name="image" accept="image/*">

                <?php if ($error): ?>
                    <p class="error-msg"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <?php if ($exito): ?>
                    <p class="success-msg"><?php echo htmlspecialchars($exito); ?></p>
                <?php endif; ?>

                <button type="submit" class="custom-btn btn-agregar-producto"><span>Agregar producto</span></button>
            </div>
        </form>

    </main>

    <?php include '../elements/footer.php' ?>