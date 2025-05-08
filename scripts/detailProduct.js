/**
 * Función para manejar la lógica de agregar un producto al carrito.
 * También verifica el stock disponible antes de agregarlo.
 * @param {number} productoId - ID del producto.
 */
function addToCart(productoId) {
    const plataformaSelect = document.getElementById('plataforma-select');
    const cantidadSelect = document.getElementById('cantidad-select');
    const stockInfo = document.getElementById('stock-info');

    const plataformaId = plataformaSelect ? plataformaSelect.value : null;
    const cantidad = cantidadSelect ? cantidadSelect.value : 1;

    if (!plataformaId) {
        showPopup('Por favor, seleccione una plataforma.');
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=agregar_carrito&producto_id=${encodeURIComponent(productoId)}&plataforma_id=${encodeURIComponent(plataformaId)}&cantidad=${encodeURIComponent(cantidad)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Producto agregado al carrito.');
                stockInfo.textContent = `Stock disponible: ${data.stock_restante || '0'}`;
            } else {
                showPopup(data.mensaje || 'No se pudo agregar al carrito.');
            }
        })
        .catch(err => {
            console.error("Error al agregar al carrito:", err);
            showPopup('Ocurrió un error inesperado.');
        });
}

/**
 * Función para obtener el stock de un producto en una plataforma específica.
 * @param {number} productoId - ID del producto.
 * @param {number} plataformaId - ID de la plataforma seleccionada.
 * @param {HTMLElement} stockInfo - Elemento donde se mostrará la información del stock.
 */
function obtenerStock(productoId, plataformaId, stockInfo) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_stock&producto_id=${encodeURIComponent(productoId)}&plataforma_id=${encodeURIComponent(plataformaId)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data && data.stock !== undefined) {
                stockInfo.textContent = `Stock disponible: ${data.stock}`;
            } else {
                stockInfo.textContent = "Stock no disponible.";
            }
        })
        .catch(error => {
            console.error('Error al obtener el stock:', error);
            stockInfo.textContent = "Error al obtener el stock.";
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const plataformaSelect = document.getElementById('plataforma-select');
    const stockInfo = document.getElementById('stock-info');
    const productoId = document.getElementById('product-form').dataset.productoId;    

    // Detectar el cambio en la plataforma seleccionada
    plataformaSelect.addEventListener('change', function () {
        const plataformaId = plataformaSelect.value;

        // Llamar a la función que hace la petición AJAX para obtener el stock
        if (plataformaId) {
            obtenerStock(productoId, plataformaId, stockInfo);
            stockInfo.style.color = "lightgreen";
        } else {
            stockInfo.textContent = "Stock disponible: Seleccione una plataforma";
            stockInfo.style.color = "red";
        }
    });

    // Configuración del carrusel
    if (typeof $ !== 'undefined' && $('.slick-carousel').length > 0) {
        $('.slick-carousel').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            infinite: true,
            arrows: true,
            dots: false,
            autoplay: false,
            responsive: [
                { breakpoint: 1024, settings: { slidesToShow: 3 } },
                { breakpoint: 768, settings: { slidesToShow: 2 } },
                { breakpoint: 480, settings: { slidesToShow: 1 } }
            ]
        });
    } else {
        console.warn('jQuery o Slick no están disponibles, o no hay carrusel en esta página.');
    }
});