/**
 * Función para obtener el stock de un producto en una plataforma específica.
 * @param {number} productoId - ID del producto.
 * @param {number} plataformaId - ID de la plataforma seleccionada.
 * @param {HTMLElement} stockInfo - Elemento donde se mostrará la información del stock.
 */
function obtenerStock(productoId, plataformaId, stockInfo) {
    const addToCartBtn = document.getElementById('add-to-cart-btn');

    // Envío la solicitud al backend para obtener el stock del producto en la plataforma seleccionada
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_stock&producto_id=${encodeURIComponent(productoId)}&plataforma_id=${encodeURIComponent(plataformaId)}`
    })
        .then(res => res.json())
        .then(data => {
            // Limpio clases previas de estado de stock
            stockInfo.classList.remove('stock-available', 'stock-unavailable');
            if (data.success) {
                const stock = data.stock;
                if (stock > 0) {
                    // Si hay stock, lo muestro y habilito el botón de agregar al carrito
                    stockInfo.textContent = `Stock disponible: ${stock}`;
                    stockInfo.classList.add('stock-available');
                    if (addToCartBtn) {
                        addToCartBtn.disabled = false;
                        addToCartBtn.classList.remove('btn-disabled');
                    }
                } else {
                    // Si no hay stock, muestro mensaje y deshabilito el botón
                    stockInfo.textContent = `Sin stock para esta plataforma`;
                    stockInfo.classList.add('stock-unavailable');
                    if (addToCartBtn) {
                        addToCartBtn.disabled = true;
                        addToCartBtn.classList.add('btn-disabled');
                    }
                }
            } else {
                // Si hubo un error en la respuesta, muestro mensaje y deshabilito el botón
                stockInfo.textContent = "Error al obtener el stock";
                stockInfo.classList.add('stock-unavailable');
                if (addToCartBtn) {
                    addToCartBtn.disabled = true;
                    addToCartBtn.classList.add('btn-disabled');
                }
            }
        })
        .catch(error => {
            // Si ocurre un error de red, muestro mensaje y deshabilito el botón
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

    // Cuando el usuario cambia la plataforma seleccionada, consulto el stock
    selectPlataforma.addEventListener("change", () => {
        const plataformaId = selectPlataforma.value;
        if (plataformaId) {
            obtenerStock(productoId, plataformaId, stockInfo);
        } else {
            // Si no hay plataforma seleccionada, muestro mensaje por defecto
            stockInfo.textContent = "Stock disponible: Seleccione una plataforma";
            stockInfo.classList.remove('stock-available', 'stock-unavailable');
        }
    });

    // Configuración del carrusel de productos relacionados usando Slick Carousel
    if (typeof $ !== 'undefined' && $('.slick-carousel').length > 0) {

        // Inicializo el carrusel de Slick
        $('.slick-carousel').slick({ 
            slidesToShow: 4,        // Número de elementos a mostrar
            slidesToScroll: 1,      // Número de elementos a desplazar al hacer scroll
            infinite: true,         // Carrusel infinito
            arrows: true,           // Mostrar flechas de navegación
            dots: false,            // Mostrar puntos de navegación
            autoplay: false,        // Desactivar autoplay
            responsive: [           // Configuración responsiva
                { breakpoint: 1024, settings: { slidesToShow: 3 } },
                { breakpoint: 768, settings: { slidesToShow: 2 } },
                { breakpoint: 480, settings: { slidesToShow: 1 } }
            ]
        });
    } else {

        // Si no está disponible jQuery o Slick, muestro advertencia en consola
        console.warn('jQuery o Slick no están disponibles, o no hay carrusel en esta página.');
    }
});