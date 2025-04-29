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

            // Cambiar clase del ícono
            icon.classList.remove('far', 'fas');
            icon.classList.add(data.favorito ? 'fas' : 'far');

            // Agregar animación
            icon.classList.add('heart-pulse');

            // Quitar animación después de que termine para que pueda repetirse
            setTimeout(() => icon.classList.remove('heart-pulse'), 300);
        } else {
            alert("Debes iniciar sesión para agregar productos a favoritos.");
        }
    });
}