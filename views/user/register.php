<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/register.css">
    <link rel="stylesheet" href="../../styles/alerts.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Registro</title>
</head>

<body>
    <!-- Formulario para registrar a un nuevo usuario -->
    <form class="register-form" action="../../verifications/paginaIntermedia.php" method="post">
        <div class="register-title-container">
            <h2 class="register-title">Crea una cuenta</h2>
            <button class="btn-home" type="button" onclick="window.location.href='./catalog.php'"><i class="fas fa-home"></i></button>
        </div>

        <!-- Campo oculto para la acción del formulario -->
        <input type="hidden" name="accion" value="registrar_usuario">

        <!-- Contenedor para los campos del formulario -->
        <div class="register">
            <input class="register-input" type="text" name="username" id="username" placeholder="Nombre de usuario"
                value="<?= htmlspecialchars($_GET['username'] ?? '') ?>">
            <input class="register-input" type="text" name="email" id="email" placeholder="Correo electrónico"
                value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
            <input class="register-input" type="password" name="password" id="password" placeholder="Contraseña">
            <input class="register-input" type="password" name="confirm_password" id="confirm_password" placeholder="Confirmar contraseña">

            <?php if (isset($_GET['errores'])): ?>
                <div class="error-msg-container">
                    <?php foreach (explode(", ", $_GET['errores']) as $error): ?>
                        <p class="error-msg"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Botón para enviar el formulario -->
            <button class="custom-btn btn" type="submit"><span>Registrarse</span></button>

            <!-- Enlace a la página de inicio de sesión si el usuario ya tiene cuenta -->
            <label class="login-link" onclick="window.location.href='login.php'">¿Ya tienes una cuenta? Inicia sesión</label>
        </div>
    </form>

</body>

</html>