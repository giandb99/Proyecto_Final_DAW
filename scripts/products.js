/**
 * Función para desactivar un producto.
 * @param {number} productId - ID del producto a desactivar.
 */
function deleteProduct(productId) {
    if (!confirm('¿Estás seguro de que deseas desactivar este producto?')) {
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=desactivar_producto&id=${encodeURIComponent(productId)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Producto desactivado con éxito.');
                setTimeout(() => location.reload(), 2000);
            } else {
                showPopup(data.mensaje || 'No se pudo desactivar el producto.');
            }
        })
        .catch(error => {
            console.error('Error al desactivar el producto:', error);
            showPopup('Ocurrió un error inesperado.');
        });
}

function activateProduct(productId) {
    if (!confirm('¿Estás seguro de que deseas activar este producto?')) {
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=activar_producto&id=${encodeURIComponent(productId)}`
    })
        .then(response => response.json())
        .then(data => {
            showPopup(data.mensaje || 'Producto activado.');
            if (data.exito) {
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(error => {
            console.error('Error al activar el producto:', error);
            showPopup('Ocurrió un error inesperado.');
        });
}