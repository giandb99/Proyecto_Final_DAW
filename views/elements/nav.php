<nav class="navbar">
    <a class="logo" href="../user/catalog.php">FreeDays_Games</a>    
    <div class="navbar-buttons">
        <a href="catalog.php" class="navbar-button" title="Inicio"><i class="fas fa-home"></i></a>
        <a href="cart.php" class="navbar-button" title="Carrito"><i class="fas fa-shopping-cart"></i></a>
        <a href="favs.php" class="navbar-button" title="Favoritos"><i class="fas fa-heart"></i></a>
        <!-- Si el usuario inició sesión, mostrar iconos adicionales -->
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="profile.php" class="navbar-button" title="Perfil"><i class="fas fa-user-cog"></i></a>
            <a href="logout.php" class="navbar-button" title="Cerrar sesión"><i class="fas fa-sign-out-alt"></i></a>
        <?php else: ?>
            <a href="login.php" class="navbar-button" title="Iniciar sesión"><i class="fas fa-user"></i></a>
        <?php endif; ?>
    </div>
</nav>