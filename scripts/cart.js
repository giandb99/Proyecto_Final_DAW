function addToCart(productoId) {
    const plataformaSelect = document.getElementById('plataforma-select');
    const cantidadSelect = document.getElementById('cantidad-select');

    const plataformaId = plataformaSelect ? plataformaSelect.value : null;
    const cantidad = cantidadSelect ? cantidadSelect.value : 1;

    if (!plataformaId) {
        showPopup('Por favor, seleccione una plataforma.');
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=agregar_carrito&producto_id=${encodeURIComponent(productoId)}&plataforma_id=${encodeURIComponent(plataformaId)}&cantidad=${encodeURIComponent(cantidad)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            showPopup(data.mensaje || 'Producto agregado al carrito.');
        } else {
            showPopup(data.mensaje || 'No se pudo agregar al carrito.');
        }
    })
    .catch(err => {
        console.error("Error al agregar al carrito:", err);
        showPopup('Ocurrió un error inesperado.');
    });
}