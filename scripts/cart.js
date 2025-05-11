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
        body: `accion=agregar_producto_carrito&producto_id=${encodeURIComponent(productoId)}&plataforma_id=${encodeURIComponent(plataformaId)}&cantidad=${encodeURIComponent(cantidad)}`
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
 * Función para eliminar un producto del carrito.
 * @param {number} carritoItemId - ID del carrito_item a eliminar.
 */
function removeFromCart(carritoItemId) {
    if (!confirm('¿Estás seguro de que deseas eliminar este producto del carrito?')) {
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=eliminar_producto_carrito&carrito_item_id=${encodeURIComponent(carritoItemId)}`
    })
        .then(res => {
            if (!res.ok) {
                throw new Error('Error en la respuesta del servidor.');
            }
            return res.json();
        })
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Producto eliminado del carrito.');
                
                const cartItemElement = document.getElementById(`cart-card-${carritoItemId}`);
                if (cartItemElement) {
                    cartItemElement.remove();
                }

                const carritoId = document.getElementById('cart-summary').dataset.carritoId; // Asegúrate de tener el carritoId en el DOM
                updateCartSummary(carritoId);
            } else {
                showPopup(data.mensaje || 'No se pudo eliminar el producto del carrito.');
            }
        })
        .catch(err => {
            console.error("Error al eliminar del carrito:", err);
            showPopup('Ocurrió un error inesperado.');
        });
}

/**
 * Función para actualizar el resumen del carrito en tiempo real.
 * @param {number} carritoId - ID del carrito.
 */
function updateCartSummary(carritoId) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_resumen_carrito&carrito_id=${encodeURIComponent(carritoId)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                // Actualizar los valores del resumen en el DOM
                document.getElementById('total-price').textContent = `$${data.resumen.total.toFixed(2)}`;
                document.getElementById('discount').textContent = `- $${data.resumen.descuento.toFixed(2)}`;
                document.getElementById('final-price').textContent = `$${data.resumen.subtotal.toFixed(2)}`;
            } else {
                console.error('Error al actualizar el resumen del carrito:', data.mensaje);
            }
        })
        .catch(err => {
            console.error('Error al obtener el resumen del carrito:', err);
        });
}