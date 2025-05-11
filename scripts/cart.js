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

function removeFromCart(productoId) {
    if (!confirm('¿Estás seguro de que deseas eliminar este producto del carrito?')) {
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=eliminar_producto_carrito&producto_id=${encodeURIComponent(productoId)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Producto eliminado del carrito.');
            } else {
                showPopup(data.mensaje || 'No se pudo eliminar del carrito.');
            }
        })
        .catch(err => {
            console.error("Error al eliminar del carrito:", err);
            showPopup('Ocurrió un error inesperado.');
        });
}