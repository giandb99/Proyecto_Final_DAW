function addToFav(productoId) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=agregar_favorito&producto_id=${encodeURIComponent(productoId)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const icon = document.getElementById(`fav-icon-${productoId}`);
                if (icon) {
                    // Cambiar el ícono con animación
                    icon.classList.remove('far', 'fas');
                    icon.classList.add(data.favorito ? 'fas' : 'far');
                    icon.classList.add('heart-pulse');
                    setTimeout(() => icon.classList.remove('heart-pulse'), 300);
                }

                // Si estamos en favs.php y se quitó de favoritos, eliminar la tarjeta
                if (window.location.pathname.includes('favs.php') && !data.favorito) {
                    const card = document.querySelector(`.favorite-card[onclick*="id=${productoId}"]`);
                    if (card) {
                        card.remove();
                    }

                    // Actualizamos el contador de favoritos
                    const counter = document.getElementById('favorites-count');
                    if (counter) {
                        const remaining = document.querySelectorAll('.favorite-card').length;
                        if (remaining === 0) {
                            // Y si no queda ninguno, mostramos el mensaje y el botón que redirige al catálogo
                            counter.parentElement.innerHTML = `
                                <h3>No tienes ningún juego en tu lista de favoritos.</h3>
                                <div>
                                    <button type="button" class="custom-btn btn-user" onclick="window.location.href='catalog.php'">
                                        <span>Explorá nuestros productos <i class="fas fa-gamepad"></i></span>
                                    </button>
                                </div>
                            `;
                        } else {
                            counter.textContent = `Tienes ${remaining} juego${remaining === 1 ? '' : 's'} en tu lista de favoritos.`;
                        }
                    }
                }

            } else {
                console.error("Error al agregar/eliminar el producto a favoritos: ", data.error);
            }
        })
        .catch(error => console.error("Error en la solicitud:", error));
}
