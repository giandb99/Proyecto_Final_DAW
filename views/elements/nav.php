<?php

$nombreBuscado = isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '';

?>

<nav class="navbar">
    <div class="navbar-left">
        <a class="logo" href="catalog.php" title="Inicio">FreeDays_Games</a>
    </div>

    <div class="navbar-right">
        <form id="navbar-search-form" onsubmit="return false;" class="<?= $nombreBuscado ? 'show' : '' ?>">
            <span id="navbar-clear-btn" class="navbar-clear-btn" style="display: <?= $nombreBuscado ? 'inline' : 'none' ?>;">
                <i class="fas fa-times"></i>
            </span>
            <input type="text" name="nombre" id="navbar-search-input" placeholder="Buscar..." autocomplete="off" value="<?= $nombreBuscado ?>" />
        </form>
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

<script>
    const toggleBtn = document.getElementById('search-toggle');
    const searchForm = document.getElementById('navbar-search-form');
    const searchInput = document.getElementById('navbar-search-input');
    const clearBtn = document.getElementById('navbar-clear-btn');

    toggleBtn.addEventListener('click', (e) => {
        e.preventDefault();
        searchForm.classList.toggle('show');
        if (searchForm.classList.contains('show')) {
            searchInput.focus();
        }
    });

    let searchTimeout;

    // Detecta si estamos en catalog.php
    const isCatalog = window.location.pathname.endsWith('/catalog.php');

    searchInput.addEventListener('input', function() {
        if (clearBtn) {
            clearBtn.style.display = this.value.length > 0 ? 'inline' : 'none';
        }
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            searchInput.focus();
            // Dispara el evento input para limpiar la búsqueda en tiempo real o redirigir
            const event = new Event('input', {
                bubbles: true
            });
            searchInput.dispatchEvent(event);
        });
    }

    if (isCatalog) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            searchForm.classList.add('show');

            searchTimeout = setTimeout(() => {
                const filterForm = document.getElementById('filter-form');
                if (filterForm) {
                    const nombreHidden = document.getElementById('filter-nombre-hidden');
                    if (nombreHidden) nombreHidden.value = query;
                    const event = new Event('input', {
                        bubbles: true
                    });
                    filterForm.dispatchEvent(event);
                }
                if (query.length === 0 && window.location.search.includes('nombre=')) {
                    window.history.replaceState({}, '', window.location.pathname);
                }
            }, 300);
        });
    } else {
        // En otras páginas, redirige al catálogo
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            searchForm.classList.add('show');

            searchTimeout = setTimeout(() => {
                let url = new URL(window.location.origin + '/Proyecto_Final_DAW/views/user/catalog.php');
                if (query.length > 0) url.searchParams.set('nombre', query);
                window.location.href = url;
            }, 400);
        });
    }

    // Al cargar, si hay búsqueda previa, deja el input abierto y el cursor al final
    if (searchInput.value.trim().length > 0) {
        searchForm.classList.add('show');
        setTimeout(() => {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }, 100);
    }
</script>