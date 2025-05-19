/**
 * Función para desactivar un producto.
 * Envía una solicitud al backend para marcar el producto como inactivo.
 * Si la operación es exitosa, muestra un popup y recarga la página.
 * @param {number} productId - ID del producto a desactivar.
 */
function deleteProduct(productId) {
    // Confirmo con el usuario antes de desactivar el producto
    if (!confirm('¿Estás seguro de que deseas desactivar este producto?')) {
        return;
    }

    // Envío la solicitud al backend para desactivar el producto
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=desactivar_producto&id=${encodeURIComponent(productId)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                // Si la operación fue exitosa, muestro mensaje y recargo la página
                showPopup(data.mensaje || 'Producto desactivado con éxito.');
                setTimeout(() => location.reload(), 2000);
            } else {
                // Si hubo un error, muestro el mensaje de error
                showPopup(data.mensaje || 'No se pudo desactivar el producto.');
            }
        })
        .catch(error => {
            // Si ocurre un error de red, lo muestro en consola y en un popup
            console.error('Error al desactivar el producto:', error);
            showPopup('Ocurrió un error inesperado.');
        });
}

/**
 * Función para activar un producto.
 * Envía una solicitud al backend para marcar el producto como activo.
 * Si la operación es exitosa, muestra un popup y recarga la página.
 * @param {number} productId - ID del producto a activar.
 */
function activateProduct(productId) {
    // Confirmo con el usuario antes de activar el producto
    if (!confirm('¿Estás seguro de que deseas activar este producto?')) {
        return;
    }

    // Envío la solicitud al backend para activar el producto
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=activar_producto&id=${encodeURIComponent(productId)}`
    })
        .then(res => res.json())
        .then(data => {
            // Muestro el mensaje del backend (éxito o error)
            showPopup(data.mensaje || 'Producto activado.');
            if (data.exito) {
                // Si la operación fue exitosa, recargo la página
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(error => {
            // Si ocurre un error de red, lo muestro en consola y en un popup
            console.error('Error al activar el producto:', error);
            showPopup('Ocurrió un error inesperado.');
        });
}