<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/login.css">
    <title>Iniciar Sesión</title>
</head>

<body>

    <!-- Formulario de inicio de sesión -->
    <form class="login-form" action="../../verifications/paginaIntermedia.php" method="POST">
        <h2>Iniciar Sesión</h2>

        <!-- Campo oculto para la acción del formulario -->
        <input type="hidden" name="accion" value="iniciar_sesion">

        <div class="login">
            <input class="login-input" type="text" name="email" id="email" placeholder="Correo electrónico">
            <input class="login-input" type="password" name="password" id="password" placeholder="Contraseña">

            <!-- Opción para iniciar sesión como administrador -->
            <div class="admin">
                <input class="checkbox" type="checkbox" name="admin" id="admin">
                <label class="admin-check" for="admin">Continuar como administrador</label>
            </div>

            <!-- Botón para enviar el formulario -->
            <button class="login-button" type="submit">Iniciar Sesión</button>

            <!-- Enlace a la página de registro si el usuario no tiene cuenta -->
            <label class="register-link" onclick="window.location.href='register.php'">¿No tienes cuenta? Registrate ahora</label>
        </div>
    </form>

</body>

</html>