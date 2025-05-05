function addToCart(productoId) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=agregar_carrito&producto_id=${encodeURIComponent(productoId)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log(`Producto ${productoId} agregado al carrito`);
            showPopup('Producto agregado al carrito.');
        } else {
            console.error("Error al agregar el producto al carrito: ", data.error);
        }
    })
    .catch(err => {
        console.error("Error al agregar el producto al carrito: ", err);
    });
}

function removeFromCart(productoId) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=eliminar_carrito&producto_id=${encodeURIComponent(productoId)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`#cart-card-${productoId}`);
            if (card) {
                card.remove();
            }
            showPopup('Producto eliminado del carrito.');
        } else {
            console.error("Error al eliminar el producto del carrito: ", data.error);
            const msg = data.error === 'db_error'
                ? 'No se pudo eliminar el producto del carrito.'
                : 'Ocurrió un error inesperado.';
            showPopup(msg);
        }
    })
    .catch(err => {
        console.error("Error al eliminar el producto del carrito: ", err);
    });
}