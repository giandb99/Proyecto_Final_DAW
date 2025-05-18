<?php

require_once '../../database/querys.php';
session_start();

$conn = conexion();
$userId = $_SESSION['usuario']['id'];
$user = getUserDataById($conn, $userId);
cerrar_conexion($conn);

$isAdmin = ($_SESSION['usuario']['rol'] === 'admin');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/profile.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/scroll.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/nav.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Perfil de <?= $isAdmin ? 'Administrador' : 'Usuario' ?></title>
</head>

<body>
    <div class="container">
        <?php if ($isAdmin): ?>
            <?php include '../elements/sidebar.php'; ?>
        <?php else: ?>
            <?php include '../elements/nav.php'; ?>
        <?php endif; ?>

        <main class="<?= $isAdmin ? 'main-content-admin' : 'main-content' ?>">
            <div class="<?= $isAdmin ? 'profile-wrapper-admin' : 'profile-wrapper' ?>">
                <?php if (!$isAdmin): ?>
                    <div class="back-button-container">
                        <a href="javascript:history.back()" class="back-button">
                            <span><i class="fas fa-arrow-left"></i> Volver</span>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="forms-container">
                    <section class="<?= $isAdmin ? 'profile-container-admin' : 'profile-container' ?>">
                        <div class="profile-header">
                            <h1><?= $isAdmin ? 'Datos Personales (Admin)' : 'Mi Perfil' ?></h1>
                            <?php if (!empty($user['imagen_perfil'])): ?>
                                <img src="../../<?= htmlspecialchars($user['imagen_perfil']) ?>" alt="Foto de perfil" class="profile-img">
                            <?php endif; ?>
                        </div>
                        <form id="profile-form" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="nombre">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="username">Usuario:</label>
                                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="email">Correo Electrónico:</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="telefono">Teléfono:</label>
                                <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($user['telefono']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="direccion">Dirección:</label>
                                <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($user['direccion']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="fecha_nac">Fecha de Nacimiento:</label>
                                <input type="date" id="fecha_nac" name="fecha_nac" value="<?= htmlspecialchars($user['fecha_nac']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="cp">Código Postal:</label>
                                <input type="text" id="cp" name="cp" value="<?= htmlspecialchars($user['cp']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="imagen_perfil">Foto de perfil:</label>
                                <input type="file" id="imagen_perfil" name="imagen_perfil" accept="image/*" disabled>
                            </div>
                            <div class="form-actions">
                                <button type="button" id="edit-button" class="custom-btn-profile btn-edit">Editar</button>
                                <button type="submit" id="save-button" class="custom-btn-profile btn-save" disabled>Guardar</button>
                            </div>
                        </form>
                    </section>

                    <section class="<?= $isAdmin ? 'change-password-container-admin' : 'change-password-container' ?>">
                        <h2>Cambiar Contraseña</h2>
                        <form id="change-password-form">
                            <div class="form-group">
                                <label for="current_password">Contraseña Actual:</label>
                                <div class="password-wrapper">
                                    <input type="password" id="current_password" name="current_password" disabled>
                                    <span class="toggle-password" data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Nueva Contraseña:</label>
                                <div class="password-wrapper">
                                    <input type="password" id="new_password" name="new_password" disabled>
                                    <span class="toggle-password" data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                                <div class="password-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password" disabled>
                                    <span class="toggle-password" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" id="edit-button-pass" class="custom-btn-profile btn-edit">Editar</button>
                                <button type="submit" id="save-button-pass" class="custom-btn-profile btn-save" disabled>Actualizar Contraseña</button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script src="../../scripts/nav.js"></script>
    <script src="../../scripts/profile.js"></script>
    <script src="../../scripts/popup.js"></script>

    <?php include '../elements/footer.php'; ?>