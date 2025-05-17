<?php

$mensaje = isset($_GET['mensaje']) ? htmlspecialchars($_GET['mensaje']) : 'Ha ocurrido un error inesperado.';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/buttons.css">
    <link rel="stylesheet" href="../styles/error.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/nav.css">
    <link rel="stylesheet" href="../styles/scroll.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>FreeDays_Games - Error</title>
</head>

<body>
    <?php include 'elements/nav.php' ?>

    <main class="main-content">
        <div class="error-container">
            <div class="error-title">¡Error!</div>
            <div class="error-msg"><?= $mensaje ?></div>
            <button class="btn-home" onclick="window.history.back()">Volver</button>
            <br><br>
            <a href="./catalog.php" class="btn-home" style="background:#444;">Ir al catálogo</a>
        </div>
    </main>

    <!-- FOOTER -->
    <?php include 'elements/footer.php' ?>