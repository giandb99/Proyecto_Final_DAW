<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/register.css">
    <title>Registro</title>
</head>

<body>
    <!-- Formulario para registrar a un nuevo usuario -->
    <form class="register-form" action="../../verifications/paginaIntermedia.php" method="post">
        <h2 class="register-title">Crea una cuenta</h2>

        <!-- Campo oculto para la acción del formulario -->
        <input type="hidden" name="accion" value="registrar_usuario">

        <!-- Contenedor para los campos del formulario -->
        <div class="register">
            <input class="register-input" type="text" name="username" id="username" placeholder="Nombre de usuario" required>
            <input class="register-input" type="text" name="email" id="email" placeholder="Correo electrónico">
            <input class="register-input" type="password" name="password" id="password" placeholder="Contraseña" minlength="6">
            <input class="register-input" type="password" name="confirm_password" id="confirm_password"
                placeholder="Confirmar contraseña">

            <!-- Botón para enviar el formulario -->
            <button class="register-button" type="submit">Registrarse</button>

            <!-- Enlace a la página de inicio de sesión si el usuario ya tiene cuenta -->
            <label class="login-link" onclick="window.location.href='login.php'">¿Ya tienes una cuenta? Inicia sesión</label>
        </div>
    </form>
    
</body>

</html>