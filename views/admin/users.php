<?php

session_start();
require_once '../../database/querys.php';
require_once '../../session_timeout.php';

// Solo permitir acceso a admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../user/logout.php');
    exit;
}

$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$usuarios = getAllUserDataPaginated($offset, $limit, $search);
$totalUsuarios = getTotalUsers($search);
$totalPages = ceil($totalUsuarios / $limit);

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Document</title>
</head>

<body>
    <div class="container">

        <?php include '../elements/sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1>Usuarios registrados en la plataforma</h1>
                <form class="search-container" method="GET" action="users.php">
                    <input type="text" id="search-input" name="search" placeholder="Buscar usuario por correo electrónico..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" id="search-button" class="custom-btn btn-icon-buscar"><span><i class="fas fa-search"></i></button></span>
                </form>
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
                                <td>#<?= $usuario['id'] ?></td>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['username']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['fecha_creacion']) ?></td>
                                <td><?= htmlspecialchars($usuario['ultimo_login']) ?></td>
                                <td><?= $usuario['activo'] ? 'Sí' : 'No' ?></td>
                                <td class="acciones">
                                    <?php if ($usuario['activo']): ?>
                                        <button class="btn-icon-desactivar" title="Desactivar Usuario" onclick="deactivateUser(<?= $usuario['id'] ?>)">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-icon-activar" title="Activar Usuario" onclick="activateUser(<?= $usuario['id'] ?>)">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <?php if ($totalPages > 1 && $search === ''): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="pagination-btn<?= $i == $page ? ' active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../../scripts/users.js"></script>
    <script src="../../scripts/popup.js"></script>

    <?php include '../elements/footer.php' ?>