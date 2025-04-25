<?php

session_start();

$exito = $_GET['exito'] ?? null;
$errores = isset($_GET['errores']) ? explode(', ', urldecode($_GET['errores'])) : [];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/login.css">
    <link rel="stylesheet" href="../../styles/alerts.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Iniciar Sesión</title>
</head>

<body>
    <!-- Formulario de inicio de sesión -->
    <form class="login-form" action="../../verifications/paginaIntermedia.php" method="POST">
        <div class="login-title-container">
            <h2 class="login-title">Iniciar Sesión</h2>
            <button class="btn-home" type="button" onclick="window.location.href='./catalog.php'"><i class="fas fa-home"></i></button>
        </div>

        <!-- Campo oculto para la acción del formulario -->
        <input type="hidden" name="accion" value="iniciar_sesion">

        <div class="login">
            <input class="login-input" type="text" name="email" id="email" placeholder="Correo electrónico">
            <input class="login-input" type="password" name="password" id="password" placeholder="Contraseña">

            <?php if (!empty($errores)): ?>
                <div class="error-msg-container">
                    <?php foreach ($errores as $error): ?>
                        <p class="error-msg"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($exito): ?>
                <div class="success-msg-container">
                    <p class="success-msg"><?php echo htmlspecialchars($exito); ?></p>
                </div>
            <?php endif; ?>

            <!-- Opción para iniciar sesión como administrador -->
            <div class="admin">
                <input class="checkbox" type="checkbox" name="admin" id="admin">
                <label class="admin-check" for="admin">Continuar como administrador</label>
            </div>

            <!-- Botón para enviar el formulario -->
            <button class="custom-btn btn" type="submit"><span>Iniciar Sesión</span></button>

            <!-- Enlace a la página de registro si el usuario no tiene cuenta -->
            <label class="register-link" onclick="window.location.href='register.php'">¿No tienes cuenta? Registrate ahora</label>
        </div>
    </form>

</body>

</html>