<nav class="navbar">
    <a class="logo" href="../user/catalog.php">FreeDays_Games</a>    
    <div class="navbar-buttons">
        <a href="catalog.php" class="navbar-button"><i class="fas fa-home"></i></a>
        <a href="cart.php" class="navbar-button"><i class="fas fa-shopping-cart"></i></a>
        <a href="favs.php" class="navbar-button"><i class="fas fa-heart"></i></a>
        <!-- Si el usuario inicio sesion el icono de login se cambia por el del logout -->
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="logout.php" class="navbar-button"><i class="fas fa-sign-out-alt"></i></a></li>
        <?php else: ?>
            <a href="login.php" class="navbar-button"><i class="fas fa-user"></i></a>
        <?php endif; ?>
    </div>
</nav>