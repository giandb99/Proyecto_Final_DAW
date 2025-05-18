<?php

$nombreBuscado = isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '';

?>

<nav class="navbar">
    <div class="navbar-left">
        <a class="logo" href="catalog.php" title="Inicio">FreeDays_Games</a>
    </div>

    <div class="navbar-right">
        <div class="navbar-search-container">
            <form id="navbar-search-form" onsubmit="return false;" class="<?= $nombreBuscado ? 'show' : '' ?>">
                <span id="navbar-clear-btn" class="navbar-clear-btn" style="display: <?= $nombreBuscado ? 'inline' : 'none' ?>;">
                    <i class="fas fa-times"></i>
                </span>
                <input type="text" name="nombre" id="navbar-search-input" placeholder="Buscar..." autocomplete="off" value="<?= $nombreBuscado ?>" />
            </form>
        </div>

        <a href="#" id="search-toggle" class="navbar-button" title="Buscar"><i class="fas fa-search"></i></a>
        <a href="cart.php" class="navbar-button" title="Carrito"><i class="fas fa-shopping-cart"></i></a>
        <a href="favs.php" class="navbar-button" title="Favoritos"><i class="fas fa-heart"></i></a>
        <a href="userOrder.php" class="navbar-button" title="Mis pedidos"><i class="fas fa-clipboard-list"></i></a>
        <!-- Si el usuario inició sesión, mostrar iconos adicionales -->
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="profile.php" class="navbar-button" title="Perfil"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="navbar-button" title="Cerrar sesión"><i class="fas fa-sign-out-alt"></i></a>
        <?php else: ?>
            <a href="login.php" class="navbar-button" title="Iniciar sesión"><i class="fas fa-user"></i></a>
        <?php endif; ?>
    </div>
</nav>
