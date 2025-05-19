/**
 * Función para manejar la lógica de agregar un producto al carrito.
 * También verifica el stock disponible antes de agregarlo.
 * @param {number} productoId - ID del producto.
 */
function addToCart(productoId) {
    const plataformaSelect = document.getElementById('plataforma-select');
    const stockInfo = document.getElementById('stock-info');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const plataformaId = plataformaSelect ? plataformaSelect.value : null;
    const cantidad = 1;

    // Verifico que se haya seleccionado una plataforma
    if (!plataformaId) {
        showPopup('Por favor, seleccione una plataforma.');
        return;
    }

    // Envío la solicitud para agregar el producto al carrito
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=agregar_producto_carrito&producto_id=${encodeURIComponent(productoId)}&plataforma_id=${encodeURIComponent(plataformaId)}&cantidad=${encodeURIComponent(cantidad)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Producto agregado al carrito.');
                // Actualizo el stock disponible en la interfaz
                stockInfo.textContent = `Stock disponible: ${data.stock_restante}`;
                if (data.stock_restante > 0) {
                    // Si hay stock, muestro el estado y habilito el botón
                    stockInfo.classList.add('stock-available');
                    if (addToCartBtn) {
                        addToCartBtn.disabled = false;
                        addToCartBtn.classList.remove('btn-disabled');
                    }
                } else {
                    // Si no hay stock, muestro el estado y deshabilito el botón
                    stockInfo.classList.remove('stock-available');
                    stockInfo.classList.add('stock-unavailable');
                    stockInfo.textContent = `Sin stock para esta plataforma`;
                    if (addToCartBtn) {
                        addToCartBtn.disabled = true;
                        addToCartBtn.classList.add('btn-disabled');
                    }
                }
            } else {
                showPopup(data.mensaje || 'No se pudo agregar al carrito.');
            }
        })
        .catch(err => {
            // Si ocurre un error en la petición, lo muestro
            console.error("Error al agregar al carrito:", err);
            showPopup('Ocurrió un error inesperado.');
        });
}

/**
 * Función para eliminar un producto del carrito.
 * @param {number} carritoItemId - ID del carrito_item a eliminar.
 */
function removeFromCart(carritoItemId) {
    // Confirmo con el usuario antes de eliminar
    if (!confirm('¿Estás seguro de que deseas eliminar este producto del carrito?')) {
        return;
    }

    // Envío la solicitud para eliminar el producto del carrito
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=eliminar_producto_carrito&carrito_item_id=${encodeURIComponent(carritoItemId)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Producto eliminado del carrito.');

                // Elimino el elemento del DOM
                const cartItemElement = document.getElementById(`cart-card-${carritoItemId}`);
                if (cartItemElement) {
                    cartItemElement.remove();
                }

                // Verifico si quedan productos en el carrito
                const hayProductos = document.querySelectorAll('.cart-card').length > 0;

                // Si no hay productos, muestro el mensaje de carrito vacío
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

                // Si hay productos, actualizo el resumen del carrito
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
            // Si ocurre un error en la petición, lo muestro
            console.error("Error al eliminar del carrito:", err);
            showPopup('Ocurrió un error inesperado.');
        });
}

/**
 * Función para vaciar todo el carrito.
 */
function emptyCart() {
    // Confirmo con el usuario antes de vaciar el carrito
    if (!confirm('¿Estás seguro de que deseas vaciar el carrito?')) {
        return;
    }

    // Envío la solicitud para vaciar el carrito
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=vaciar_carrito`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'El carrito ha sido vaciado.');

                // Muestro el mensaje de carrito vacío en la interfaz
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

                // Elimino el resumen del carrito si existe
                const summaryBox = document.querySelector('.summary-box');
                if (summaryBox) summaryBox.remove();

            } else {
                showPopup(data.mensaje || 'No se pudo vaciar el carrito.');
            }
        })
        .catch(err => {
            // Si ocurre un error en la petición, lo muestro
            console.error('Error al vaciar el carrito:', err);
            showPopup('Ocurrió un error inesperado.');
        });
}

/**
 * Función para actualizar el resumen del carrito en tiempo real.
 * @param {number} carritoId - ID del carrito.
 */
function updateCartSummary(carritoId) {
    // Envío la solicitud para obtener el resumen actualizado
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_resumen_carrito&carrito_id=${encodeURIComponent(carritoId)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                // Actualizo los valores del resumen en el DOM
                document.getElementById('total-price').textContent = `$${data.resumen.total.toFixed(2)}`;
                document.getElementById('discount').textContent = `- $${data.resumen.descuento.toFixed(2)}`;
                document.getElementById('final-price').innerHTML = `<strong>$${data.resumen.subtotal.toFixed(2)}</strong>`;
            } else {
                console.error('Error al actualizar el resumen del carrito:', data.mensaje);
            }
        })
        .catch(err => {
            // Si ocurre un error en la petición, lo muestro
            console.error('Error al obtener el resumen del carrito:', err);
            showPopup('Ocurrió un error inesperado.');
        });
}

/**
 * Función para actualizar la cantidad de un producto en el carrito.
 * @param {number} carritoItemId - ID del ítem en el carrito.
 * @param {number} nuevaCantidad - Nueva cantidad seleccionada.
 */
function updateCartItemQuantity(carritoItemId, nuevaCantidad) {
    // Envío la solicitud para actualizar la cantidad
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=actualizar_cantidad_carrito&carrito_item_id=${encodeURIComponent(carritoItemId)}&cantidad=${encodeURIComponent(nuevaCantidad)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Cantidad actualizada.');
                // Actualizo el resumen del carrito
                const resumenElement = document.getElementById('cart-summary');
                if (resumenElement) {
                    const carritoId = resumenElement.dataset.carritoId;
                    updateCartSummary(carritoId);
                }
                // Actualizo la cantidad en la tarjeta del producto
                const card = document.getElementById(`cart-card-${carritoItemId}`);
                if (card && data.cantidad !== undefined) {
                    const cantidad = card.querySelector('.cart-card-qty');
                    if (cantidad) {
                        cantidad.textContent = `Cantidad: ${parseInt(data.cantidad, 10)}`;
                    }
                }
            } else {
                // Si no se pudo actualizar, muestro el mensaje y recargo la página
                showPopup(data.mensaje || 'No se pudo actualizar la cantidad.');
                setTimeout(() => window.location.reload(), 1500);
            }
        })
        .catch(err => {
            // Si ocurre un error en la petición, lo muestro
            console.error('Error al actualizar cantidad:', err);
            showPopup('Ocurrió un error inesperado.');
        });
}

// Al cargar la página, agrego listeners a los select de cantidad para actualizar el carrito en tiempo real
document.addEventListener('DOMContentLoaded', () => {
    // Busco todos los selectores de cantidad en el carrito
    document.querySelectorAll('.cart-qty-select').forEach(select => {
        // Cuando el usuario cambia la cantidad, llamo a la función para actualizar
        select.addEventListener('change', function () {
            const carritoItemId = this.dataset.cartItemId;
            const nuevaCantidad = parseInt(this.value, 10);
            updateCartItemQuantity(carritoItemId, nuevaCantidad);
        });
    });
});