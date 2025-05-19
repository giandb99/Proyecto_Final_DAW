/**
 * Crea y devuelve una tarjeta de producto para mostrar en el catálogo.
 * Incluye imagen, nombre, descripción, precio (con o sin descuento), botones de favorito y detalles.
 * @param {object} producto - Objeto con los datos del producto.
 * @param {boolean} usuarioLogueado - Indica si el usuario está logueado para mostrar el botón de favorito.
 * @returns {HTMLElement} Elemento div con la tarjeta del producto.
 */
function crearTarjetaProducto(producto, usuarioLogueado) {
    // Se convierten los valores a números para evitar errores de NaN
    const precioOriginal = parseFloat(producto.precio) || 0;
    const descuento = parseFloat(producto.descuento) || 0;

    // Se calcula el precio final aplicando el descuento si existe
    const precioFinal = descuento
        ? precioOriginal - (precioOriginal * descuento / 100)
        : precioOriginal;

    // Se crea la tarjeta del producto
    const tarjeta = document.createElement('div');
    tarjeta.className = 'product-card';

    // Al hacer click en la tarjeta, se redirige al detalle del producto
    tarjeta.onclick = () => window.location.href = `detailProduct.php?id=${producto.id}`;

    // Se añade el contenido HTML a la tarjeta
    tarjeta.innerHTML = `
        <div class="relative">
            <img src="../../${producto.imagen || 'placeholder.svg'}" alt="${producto.nombre}">
            ${descuento ? `<div class="discount-tag">${descuento}% OFF</div>` : ''}
        </div>
        <div class="product-info">
            <div class="title-container">
                <h3 class="game-title">${producto.nombre}</h3>
            </div>
            <div class="description-container">
                <p class="description">${producto.descripcion}</p>
            </div>
            <div class="foot-container">
                <div class="price-container">
                    ${descuento ?
            `<span class="price">${precioFinal.toFixed(2)}€</span>
                        <span class="old-price">${precioOriginal.toFixed(2)}€</span>` :
            `<span class="price">${precioOriginal.toFixed(2)}€</span>`
        }
                </div>
                <div class="buttons-container">
                    ${usuarioLogueado ?
            // Si el usuario está logueado, puede agregar a favoritos
            `<button type="button" class="custom-btn btn-user" onclick="event.stopPropagation(); addToFav(${producto.id})">
                            <span><i id="fav-icon-${producto.id}" class="${producto.isFav ? 'fas fa-heart-broken' : 'far fa-heart'}"></i></span>
                        </button>` :
            // Si no está logueado, lo redirige al login/error
            `<button type="button" class="custom-btn btn-user" onclick="event.stopPropagation(); window.location.href='catalog.php?id=${producto.id}&agregar_favorito=error'">
                            <span><i class="far fa-heart"></i></span>
                        </button>`
        }
                    <button type="button" class="custom-btn btn-user">
                        <span>Ver detalles</span>
                    </button>
                </div>
            </div>
        </div>
    `;

    return tarjeta;
}

// Obtengo el formulario de filtros
const filterForm = document.getElementById('filter-form');

// Cada vez que se modifica un filtro, busco productos y actualizo el catálogo
filterForm.addEventListener('input', () => {
    const formData = new FormData(filterForm);
    formData.append('accion', 'buscar_productos');

    // Hago la petición al backend para buscar productos filtrados
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(productos => {
            const contenedor = document.querySelector('.catalog-items');
            contenedor.innerHTML = '';

            // Si no hay productos, muestro mensaje
            if (productos.length === 0) {
                contenedor.innerHTML = '<p class="no-products">No se encontraron productos con esos filtros.</p>';
            } else {
                // Si hay productos, creo y agrego cada tarjeta al contenedor
                productos.forEach(p => {
                    const tarjeta = crearTarjetaProducto(p, window.usuarioLogueado);
                    contenedor.appendChild(tarjeta);
                });
            }
        })
        .catch(err => {
            // Si ocurre un error, lo muestro y aviso al usuario
            console.error('Error al buscar productos:', err);
            document.querySelector('.catalog-items').innerHTML = '<p class="error">Hubo un error al cargar los productos.</p>';
        });
});

document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form'); // Obtengo el formulario de filtros
    const clearBtn = document.getElementById('clear-filters-btn'); // Obtengo el botón de limpiar filtros

    // Esta función verifica si hay filtros activos en el formulario.
    // Devuelve true si alguno de los campos de filtro tiene valor.
    function hayFiltrosActivos() {
        return (
            filterForm.genero.value ||
            filterForm.plataforma.value ||
            filterForm.precioMin.value ||
            filterForm.precioMax.value
        );
    }

    // Esta función muestra u oculta el botón de "Limpiar filtros"
    // dependiendo de si hay filtros activos o no.
    function toggleClearBtn() {
        clearBtn.style.display = hayFiltrosActivos() ? 'inline' : 'none';
    }

    // Si existen el formulario y el botón de limpiar filtros...
    if (filterForm && clearBtn) {
        toggleClearBtn(); // Inicializo el estado del botón al cargar la página

        // Cada vez que el usuario cambia un filtro, actualizo el estado del botón limpiar
        filterForm.addEventListener('input', toggleClearBtn);

        // Al hacer click en "Limpiar filtros":
        clearBtn.addEventListener('click', function (e) {
            e.preventDefault();
            // Reseteo todos los campos del formulario de filtros
            filterForm.genero.value = '';
            filterForm.plataforma.value = '';
            filterForm.precioMin.value = '';
            filterForm.precioMax.value = '';
            toggleClearBtn(); // Oculto el botón porque ya no hay filtros activos

            // Disparo el evento input para que se actualicen los productos mostrados
            const event = new Event('input', { bubbles: true });
            filterForm.dispatchEvent(event);
        });
    }
});