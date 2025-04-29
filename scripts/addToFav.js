function addToFavs(productoId) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=agregar_favorito&producto_id=${encodeURIComponent(productoId)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const icon = document.getElementById(`fav-icon-${productoId}`);
            if (!icon) return;

            // Se cambia la clase del ícono
            icon.classList.remove('far', 'fas');
            icon.classList.add(data.favorito ? 'fas' : 'far');

            // Se agrega la animación
            icon.classList.add('heart-pulse');

            // Se quita la animación después de que termine para que pueda repetirse
            setTimeout(() => icon.classList.remove('heart-pulse'), 300);
        } else {
            console.error("Error al agregar a favoritos:", data.error);
        }
    })
    .catch(err => {
        console.error("Error al agregar a favoritos:", err);
    });
}