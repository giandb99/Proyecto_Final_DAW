/**
 * Función para obtener el stock de un producto en una plataforma específica.
 * @param {number} productoId - ID del producto.
 * @param {number} plataformaId - ID de la plataforma seleccionada.
 * @param {HTMLElement} stockInfo - Elemento donde se mostrará la información del stock.
 */
function obtenerStock(productoId, plataformaId, stockInfo) {
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_stock&producto_id=${encodeURIComponent(productoId)}&plataforma_id=${encodeURIComponent(plataformaId)}`
    })
        .then(response => response.json())
        .then(data => {
            stockInfo.classList.remove('stock-available', 'stock-unavailable');
            if (data.success) {
                const stock = data.stock;
                if (stock > 0) {
                    stockInfo.textContent = `Stock disponible: ${stock}`;
                    stockInfo.classList.add('stock-available');
                    if (addToCartBtn) {
                        addToCartBtn.disabled = false;
                        addToCartBtn.classList.remove('btn-disabled');
                    }
                } else {
                    stockInfo.textContent = `Sin stock para esta plataforma`;
                    stockInfo.classList.add('stock-unavailable');
                    if (addToCartBtn) {
                        addToCartBtn.disabled = true;
                        addToCartBtn.classList.add('btn-disabled');
                    }
                }
            } else {
                stockInfo.textContent = "Error al obtener el stock";
                stockInfo.classList.add('stock-unavailable');
                if (addToCartBtn) {
                    addToCartBtn.disabled = true;
                    addToCartBtn.classList.add('btn-disabled');
                }
            }
        })
        .catch(error => {
            console.error("Error al obtener el stock:", error);
            stockInfo.textContent = "Error de conexión";
            stockInfo.classList.add('stock-unavailable');
            if (addToCartBtn) {
                addToCartBtn.disabled = true;
                addToCartBtn.classList.add('btn-disabled');
            }
        });
}

document.addEventListener("DOMContentLoaded", () => {
    const selectPlataforma = document.getElementById("plataforma-select");
    const stockInfo = document.getElementById("stock-info");
    const productoId = document.getElementById("product-form").dataset.productoId;

    selectPlataforma.addEventListener("change", () => {
        const plataformaId = selectPlataforma.value;
        if (plataformaId) {
            obtenerStock(productoId, plataformaId, stockInfo);
        } else {
            stockInfo.textContent = "Stock disponible: Seleccione una plataforma";
            stockInfo.classList.remove('stock-available', 'stock-unavailable');
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