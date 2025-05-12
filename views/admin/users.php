<?php

require_once '../../database/querys.php';
session_start();

$usuarios = getAllUserData();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/users.css">
    <link rel="stylesheet" href="../../styles/sidebar.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Document</title>
</head>

<body>
    <div class="container">
        <?php include '../elements/sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1>Usuarios registrados en la plataforma</h1>
            </div>

            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Nombre de usuario</th>
                        <th>Correo electrónico</th>
                        <th>Fecha de registro</th>
                        <th>Ultimo acceso</th>
                        <th>Activo</th>
                        <th>Activar/Desactivar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr data-user-id="<?= $usuario['id'] ?>">
                                <td><?= $usuario['id'] ?></td>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['username']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['fecha_creacion']) ?></td>
                                <td><?= htmlspecialchars($usuario['ultimo_login']) ?></td>
                                <td><?= $usuario['activo'] ? 'Sí' : 'No' ?></td>
                                <td class="acciones">
                                    <button class="btn-icon-activar" title="Activar Usuario" onclick="activateUser(<?= $usuario['id'] ?>)">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                    <button class="btn-icon-desactivar" title="Desactivar Usuario" onclick="deactivateUser(<?= $usuario['id'] ?>)">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <?php include '../elements/footer.php' ?>

    <script src="../../scripts/users.js"></script>
    <script src="../../scripts/popup.js"></script>
    <script src="../../scripts/sidebar.js"></script>