// Obtengo los elementos principales del buscador en la barra de navegación
const toggleBtn = document.getElementById('search-toggle');
const searchForm = document.getElementById('navbar-search-form');
const searchInput = document.getElementById('navbar-search-input');
const clearBtn = document.getElementById('navbar-clear-btn');

// Al hacer click en el botón de búsqueda, muestro/oculto el input y le doy foco si se muestra
toggleBtn.addEventListener('click', (e) => {
    e.preventDefault();
    searchForm.classList.toggle('show');
    if (searchForm.classList.contains('show')) {
        searchInput.focus();
    }
});

let searchTimeout; // Variable para controlar el tiempo de espera entre búsquedas

// Detecta si estamos en catalog.php para cambiar el comportamiento de la búsqueda
const isCatalog = window.location.pathname.endsWith('/catalog.php');

// Muestra u oculta el botón de limpiar según si hay texto en el input
searchInput.addEventListener('input', function () {
    if (clearBtn) {
        clearBtn.style.display = this.value.length > 0 ? 'inline' : 'none';
    }
});

// Al hacer click en el botón de limpiar, borro el input y disparo el evento input para limpiar la búsqueda
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
    // En catalog.php, la búsqueda filtra productos en tiempo real usando el formulario de filtros
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout); // Cancelo el timeout anterior si el usuario sigue escribiendo
        const query = this.value.trim();
        searchForm.classList.add('show'); // Aseguro que el input esté visible

        searchTimeout = setTimeout(() => {
            const filterForm = document.getElementById('filter-form');
            if (filterForm) {
                // Actualizo el campo oculto de nombre y disparo el evento input para filtrar productos
                const nombreHidden = document.getElementById('filter-nombre-hidden');
                if (nombreHidden) nombreHidden.value = query;
                const event = new Event('input', {
                    bubbles: true
                });
                filterForm.dispatchEvent(event);
            }
            // Si se borra la búsqueda y había un parámetro en la URL, limpio la URL
            if (query.length === 0 && window.location.search.includes('nombre=')) {
                window.history.replaceState({}, '', window.location.pathname);
            }
        }, 300); // Espero 300ms antes de lanzar la búsqueda para evitar peticiones excesivas
    });
} else {
    // En otras páginas, la búsqueda redirige al catálogo con el parámetro de búsqueda
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        searchForm.classList.add('show');

        searchTimeout = setTimeout(() => {
            // Construyo la URL del catálogo con el parámetro de búsqueda si hay texto
            let url = new URL(window.location.origin + '/Proyecto_Final_DAW/views/user/catalog.php');
            if (query.length > 0) url.searchParams.set('nombre', query);
            window.location.href = url;
        }, 400); // Espero 400ms antes de redirigir para evitar recargas excesivas
    });
}

// Al cargar la página, si hay búsqueda previa, dejo el input abierto y el cursor al final
if (searchInput.value.trim().length > 0) {
    searchForm.classList.add('show');
    setTimeout(() => {
        searchInput.focus();
        searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
    }, 100);
}