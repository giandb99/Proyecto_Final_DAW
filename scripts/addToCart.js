function addToCart(productoId) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=agregar_carrito&producto_id=${encodeURIComponent(productoId)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Podés mostrar un popup, actualizar contador o un toast
            console.log(`Producto ${productoId} agregado al carrito`);
            // Por ejemplo:
            showPopup('Producto agregado al carrito.');
        }  else {
            console.error("Error al agregar el producto al carrito: ", data.error);
        }
    })
    .catch(err => {
        console.error("Error al agregar el producto al carrito: ", err);
    });
}