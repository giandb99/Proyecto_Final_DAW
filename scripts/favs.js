function addToFav(productoId) {

    // Envio la solicitud al backend para agregar o eliminar de favoritos
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=agregar_favorito&producto_id=${encodeURIComponent(productoId)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Obtengo el icono de favorito correspondiente al producto
            const icon = document.getElementById(`fav-icon-${productoId}`);
            // Obtengo el texto al lado del icono (si existe)
            const spanText = icon?.nextSibling;

            if (icon) {
                // Limpio todas las clases previas del icono
                icon.classList.remove('far', 'fas', 'fa-heart', 'fa-heart-broken');

                if (data.favorito) {
                    // Si ahora es favorito, muestro el icono de "eliminar de favoritos"
                    icon.classList.add('fas', 'fa-heart-broken');
                    if (spanText?.nodeType === 3) spanText.nodeValue = ' Eliminar de favoritos';
                } else {
                    // Si se quitó de favoritos, muestro el icono de "agregar a favoritos"
                    icon.classList.add('far', 'fa-heart');
                    if (spanText?.nodeType === 3) spanText.nodeValue = ' Agregar a favoritos';
                }

                // Animación de pulso al icono
                icon.classList.add('heart-pulse');
                setTimeout(() => icon.classList.remove('heart-pulse'), 300);
            }

            // Si estamos en favs.php y se quitó de favoritos, elimino la tarjeta del producto
            if (window.location.pathname.includes('favs.php') && !data.favorito) {
                const card = document.querySelector(`.favorite-card[onclick*="id=${productoId}"]`);
                if (card) card.remove();

                // Actualizo el contador de favoritos o muestro mensaje si está vacío
                const counter = document.getElementById('favorites-count');
                if (counter) {
                    const remaining = document.querySelectorAll('.favorite-card').length;
                    if (remaining === 0) {
                        // Si no quedan favoritos, muestro mensaje de lista vacía
                        counter.parentElement.innerHTML = `
                            <div class="empty-fav">
                                <h2>Tu lista de favoritos está vacía</h2>
                                <p>¡No te preocupes! Descubre los mejores videojuegos en nuestro catálogo.</p>
                                <button type="button" class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                                    <span>Explora nuestro catálogo <i class="fas fa-gamepad"></i></span>
                                </button>
                            </div>
                        `;
                    } else {
                        // Si quedan favoritos, actualizo el contador
                        counter.textContent = `Tienes ${remaining} juego${remaining === 1 ? '' : 's'} en tu lista de favoritos.`;
                    }
                }
            }

        } else {
            // Si hubo un error en la respuesta del backend, lo muestro en consola
            console.error("Error al agregar/eliminar el producto a favoritos: ", data.error);
        }
    })
    .catch(error => console.error("Error en la solicitud:", error));
}