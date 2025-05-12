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

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const productRows = document.querySelectorAll('.tabla-productos tbody tr');

    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();

        productRows.forEach(row => {
            const productName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const platform = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
            const genre = row.querySelector('td:nth-child(7)').textContent.toLowerCase();

            if (productName.includes(searchTerm) || platform.includes(searchTerm) || genre.includes(searchTerm)) {
                row.style.display = ''; // Mostrar fila
            } else {
                row.style.display = 'none'; // Ocultar fila
            }
        });
    });
});