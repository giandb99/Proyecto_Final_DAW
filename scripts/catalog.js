function crearTarjetaProducto(producto, usuarioLogueado) {
    // Convertimos precio a número (float)
    const precioOriginal = parseFloat(producto.precio) || 0;
    const descuento = parseFloat(producto.descuento) || 0;

    const precioFinal = descuento
        ? precioOriginal - (precioOriginal * descuento / 100)
        : precioOriginal;

    const tarjeta = document.createElement('div');
    tarjeta.className = 'product-card';
    tarjeta.onclick = () => window.location.href = `detailProduct.php?id=${producto.id}`;

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
            `<button type="button" class="custom-btn btn-user" onclick="event.stopPropagation(); addToFav(${producto.id})">
                <span><i id="fav-icon-${producto.id}" class="${producto.isFav ? 'fas fa-heart-broken' : 'far fa-heart'}"></i></span>
             </button>` :
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


const filterForm = document.getElementById('filter-form');

filterForm.addEventListener('input', () => {
    const formData = new FormData(filterForm);
    formData.append('accion', 'buscar_productos');

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(productos => {
            const contenedor = document.querySelector('.catalog-items');
            contenedor.innerHTML = '';

            if (productos.length === 0) {
                contenedor.innerHTML = '<p class="no-products">No se encontraron productos con esos filtros.</p>';
            } else {
                productos.forEach(p => {
                    const tarjeta = crearTarjetaProducto(p, window.usuarioLogueado);
                    contenedor.appendChild(tarjeta);
                });
            }
        })
        .catch(err => {
            console.error('Error al buscar productos:', err);
            document.querySelector('.catalog-items').innerHTML = '<p class="error">Hubo un error al cargar los productos.</p>';
        });
});

document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const clearBtn = document.getElementById('clear-filters-btn');

    if (filterForm && clearBtn) {
        clearBtn.addEventListener('click', function() {
            filterForm.reset();

            // Dispara el evento input para recargar el catálogo sin filtros
            const event = new Event('input', { bubbles: true });
            filterForm.dispatchEvent(event);
        });
    }
});