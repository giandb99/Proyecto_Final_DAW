/**
 * Función para obtener el stock de un producto en una plataforma específica
 * @param int productoId - ID del producto
 * @param int plataformaId - ID de la plataforma seleccionada
 * @param HTMLElement stockInfo - Elemento donde se mostrará la información del stock
 * @returns void
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

    // Detectar el cambio en la plataforma seleccionada
    plataformaSelect.addEventListener('change', function () {
        const plataformaId = plataformaSelect.value;
        const productoId = document.getElementById('product-form').dataset.productoId;

        // Llamar a la función que hace la petición AJAX
        if (plataformaId) {
            obtenerStock(productoId, plataformaId, stockInfo);
        } else {
            stockInfo.textContent = "Stock disponible: ";
        }
    });

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