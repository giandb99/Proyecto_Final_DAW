<?php

require_once '../../database/querys.php';
session_start();

// Solo permitir acceso a admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../user/logout.php');
    exit;
}

$formData = $_SESSION['form_data'] ?? [];
$errores = $_SESSION['errores'] ?? [];

unset($_SESSION['form_data'], $_SESSION['errores']);

$id = $_GET['id'] ?? null;
$producto = null;
$modoEdicion = false;

if ($id) {
    $producto = getProductById($conn, $id);
    $modoEdicion = true;

    if (!$producto) {
        header('Location: listProductsAdmin.php');
        exit;
    }
}

$generos = getAllGenres();
$plataformas = getAllPlatforms();
$generosSeleccionados = $modoEdicion ? getSelectedGenreIds($producto['id']) : [];
$plataformasSeleccionadas = $modoEdicion ? getSelectedPlatformIds($producto['id']) : [];
$stockPorPlataforma = geProductStockByPlataform($producto['id'] ?? null);
$exito = $_GET['exito'] ?? null;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/addOrModifyProduct.css">
    <link rel="stylesheet" href="../../styles/alerts.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title><?= $modoEdicion ? 'Modificar Producto' : 'Agregar Producto' ?></title>
</head>

<body>
    <div class="container">

        <?php include '../elements/sidebar.php'; ?>

        <main class="main-content">
            <form class="product-form" action="../../verifications/paginaIntermedia.php" method="post" enctype="multipart/form-data">
                <div class="product-container">
                    <h2><?= $modoEdicion ? 'Modificar producto' : 'Agregar nuevo producto' ?></h2>

                    <input type="hidden" name="accion" value="<?= $modoEdicion ? 'modificar_producto' : 'agregar_producto' ?>">
                    <?php if ($modoEdicion): ?>
                        <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                    <?php endif; ?>

                    <input type="text" id="name" name="name" placeholder="Nombre del producto"
                        value="<?= htmlspecialchars($formData['name'] ?? ($modoEdicion ? $producto['nombre'] : '')) ?>">

                    <textarea id="description" name="description" placeholder="Descripción"><?= htmlspecialchars($formData['description'] ?? ($modoEdicion ? $producto['descripcion'] : '')) ?></textarea>

                    <input type="date" id="release_date" name="release_date" placeholder="Fecha de lanzamiento"
                        value="<?= htmlspecialchars($formData['release_date'] ?? ($modoEdicion ? $producto['fecha_lanzamiento'] : '')) ?>">

                    <input type="number" id="price" name="price" placeholder="Precio" step="0.01" min="0"
                        value="<?= htmlspecialchars($formData['price'] ?? ($modoEdicion ? $producto['precio'] : '')) ?>">

                    <input type="number" id="discount" name="discount" step="1" min="0" max="100" placeholder="Descuento"
                        value="<?= htmlspecialchars($formData['discount'] ?? ($modoEdicion ? $producto['descuento'] : '')) ?>">

                    <fieldset class="generos-section">
                        <legend>Géneros</legend>
                        <?php foreach ($generos as $genero):
                            $generoId = $genero['id'];
                            $checked = in_array($generoId, $generosSeleccionados);
                        ?>
                            <label class="genero-label">
                                <input type="checkbox" name="generos[]" value="<?= $generoId ?>" <?= $checked ? 'checked' : '' ?>>
                                <?= htmlspecialchars($genero['nombre']) ?>
                            </label>
                        <?php endforeach; ?>

                    </fieldset>

                    <fieldset class="plataformas-section">
                        <legend>Plataformas</legend>
                        <?php foreach ($plataformas as $plataforma):
                            $plataformaId = $plataforma['id'];
                            $checked = in_array($plataformaId, $plataformasSeleccionadas);
                        ?>
                            <div class="plataforma" style="display: flex; align-items: center; justify-content: space-between;">
                                <label class="plataforma-label">
                                    <input type="checkbox" name="plataformas[]" value="<?= $plataformaId ?>" <?= $checked ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($plataforma['nombre']) ?>
                                </label>
                                <input class="plataforma-stock" type="number" name="stock[<?= $plataformaId ?>]"
                                    <?= !$checked ? 'disabled' : '' ?>
                                    value="<?= htmlspecialchars($stockPorPlataforma[$plataformaId] ?? 0) ?>" min="0">

                            </div>
                        <?php endforeach; ?>
                    </fieldset>

                    <label for="imagen"><?= $modoEdicion ? 'Imagen actual del producto:' : 'Imagen del producto:' ?></label>
                    <?php if ($modoEdicion && !empty($producto['imagen'])): ?>
                        <div class="current-image">
                            <img src="../../<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen actual del producto" style="width: 100%;">
                        </div>
                        <p class="image-advice">Si seleccionas una nueva imagen, reemplazarás la actual.</p>
                    <?php endif; ?>

                    <input type="file" id="image" name="image" accept="image/*">

                    <?php if (!empty($errores)): ?>
                        <div class="error-msg-container">
                            <?php foreach ($errores as $error): ?>
                                <p class="error-msg"><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($exito): ?>
                        <p class="success-msg"><?= htmlspecialchars($exito) ?></p>
                    <?php endif; ?>

                    <button type="submit" class="custom-btn btn">
                        <span><?= $modoEdicion ? 'Confirmar cambios' : 'Agregar producto' ?></span>
                    </button>
                </div>
            </form>
        </main>
    </div>

    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/addOrModifyProduct.js"></script>