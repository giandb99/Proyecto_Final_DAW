/**
 * Función para eliminar un producto.
 * @param {number} productId - ID del producto a eliminar.
 */
function deleteProduct(productId) {
    if (!confirm('¿Estás seguro de que deseas eliminar este producto?')) {
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=eliminar_producto&id=${encodeURIComponent(productId)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Producto eliminado con éxito.');
                const productRow = document.querySelector(`tr[data-product-id="${productId}"]`);
                if (productRow) {
                    productRow.remove();
                }
            } else {
                showPopup(data.mensaje || 'No se pudo eliminar el producto.');
            }
        })
        .catch(error => {
            console.error('Error al eliminar el producto:', error);
            showPopup('Ocurrió un error inesperado.');
        });
}