/**
 * Función para manejar la lógica de agregar un producto al carrito.
 * También verifica el stock disponible antes de agregarlo.
 * @param {number} productoId - ID del producto.
 */
function addToCart(productoId) {
    const plataformaSelect = document.getElementById('plataforma-select');
    const stockInfo = document.getElementById('stock-info');

    const plataformaId = plataformaSelect ? plataformaSelect.value : null;
    const cantidad = 1;

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

                const hayProductos = document.querySelectorAll('.cart-card').length > 0;

                if (!hayProductos) {
                    const cartContainer = document.querySelector('.cart-container');
                    cartContainer.innerHTML = `
                        <div class="empty-cart">
                            <h2>Tu carrito está vacío</h2>
                            <p>¡No te preocupes! Descubre los mejores videojuegos en nuestro catálogo.</p>
                            <button type="button" class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                                <span>Explora nuestro catálogo <i class="fas fa-gamepad"></i></span>
                            </button>
                        </div>`;
                    cartContainer.style.flexDirection = 'row';
                }

                const resumenElement = document.getElementById('cart-summary');
                if (resumenElement && hayProductos) {
                    const carritoId = resumenElement.dataset.carritoId;
                    updateCartSummary(carritoId);
                }
            } else {
                showPopup(data.mensaje || 'No se pudo eliminar el producto del carrito.');
            }
        })
        .catch(err => {
            console.error("Error al eliminar del carrito:", err);
            showPopup('Ocurrió un error inesperado.');
        });
}

function emptyCart() {
    if (!confirm('¿Estás seguro de que deseas vaciar el carrito?')) {
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=vaciar_carrito`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'El carrito ha sido vaciado.');

                const cartContent = document.querySelector('.cart-container');
                if (cartContent) {
                    cartContent.innerHTML = `
                    <div class="empty-cart">
                        <h2>Tu carrito está vacío</h2>
                        <p>¡No te preocupes! Descubre los mejores videojuegos en nuestro catálogo.</p>
                        <button type="button" class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                            <span>Explora nuestro catálogo <i class="fas fa-gamepad"></i></span>
                        </button>
                    </div>`;
                }

                const summaryBox = document.querySelector('.summary-box');
                if (summaryBox) summaryBox.remove();

            } else {
                showPopup(data.mensaje || 'No se pudo vaciar el carrito.');
            }
        })
        .catch(err => {
            console.error('Error al vaciar el carrito:', err);
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
                document.getElementById('final-price').innerHTML = `<strong>$${data.resumen.subtotal.toFixed(2)}</strong>`;
            } else {
                console.error('Error al actualizar el resumen del carrito:', data.mensaje);
            }
        })
        .catch(err => {
            console.error('Error al obtener el resumen del carrito:', err);
        });
}

function updateCartItemQuantity(carritoItemId, nuevaCantidad) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=actualizar_cantidad_carrito&carrito_item_id=${encodeURIComponent(carritoItemId)}&cantidad=${encodeURIComponent(nuevaCantidad)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Cantidad actualizada.');
                // Actualizar resumen
                const resumenElement = document.getElementById('cart-summary');
                if (resumenElement) {
                    const carritoId = resumenElement.dataset.carritoId;
                    updateCartSummary(carritoId);
                }
                // Actualizar cantidad del producto en la tarjeta
                const card = document.getElementById(`cart-card-${carritoItemId}`);
                if (card && data.cantidad !== undefined) {
                    const cantidad = card.querySelector('.cart-card-qty');
                    if (cantidad) {
                        cantidad.textContent = `Cantidad: ${parseInt(data.cantidad, 10)}`;
                    }
                }
            } else {
                showPopup(data.mensaje || 'No se pudo actualizar la cantidad.');
                setTimeout(() => window.location.reload(), 1500);
            }
        })
        .catch(err => {
            console.error('Error al actualizar cantidad:', err);
            showPopup('Ocurrió un error inesperado.');
        });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.cart-qty-select').forEach(select => {
        select.addEventListener('change', function () {
            const carritoItemId = this.dataset.cartItemId;
            const nuevaCantidad = parseInt(this.value, 10);
            updateCartItemQuantity(carritoItemId, nuevaCantidad);
        });
    });
});