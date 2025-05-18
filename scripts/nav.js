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

searchInput.addEventListener('input', function () {
    if (clearBtn) {
        clearBtn.style.display = this.value.length > 0 ? 'inline' : 'none';
    }
});

if (clearBtn) {
    clearBtn.addEventListener('click', function () {
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
    searchInput.addEventListener('input', function () {
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
    searchInput.addEventListener('input', function () {
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