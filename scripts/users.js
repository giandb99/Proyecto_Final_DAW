/**
 * Función para desactivar un usuario.
 * Envía una solicitud al backend para marcar el usuario como inactivo.
 * Si la operación es exitosa, muestra un popup y recarga la página.
 * @param {number} userId - ID del usuario a desactivar.
 */
function deactivateUser(userId) {
    // Confirmo con el usuario antes de desactivar
    if (!confirm('¿Estás seguro de que deseas desactivar este usuario?')) {
        return;
    }

    // Envío la solicitud al backend para desactivar el usuario
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=desactivar_usuario&usuario_id=${encodeURIComponent(userId)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                // Si la operación fue exitosa, muestro mensaje y recargo la página
                showPopup(data.mensaje || 'Usuario desactivado correctamente.');
                setTimeout(() => location.reload(), 2000);
            } else {
                // Si hubo un error, muestro el mensaje de error
                showPopup(data.mensaje || 'No se pudo desactivar el usuario.');
            }
        })
        .catch(err => {
            // Si ocurre un error de red, lo muestro en consola y en un popup
            console.error('Error al desactivar el usuario:', err);
            showPopup('Ocurrió un error inesperado.');
        });
}

/**
 * Función para activar un usuario.
 * Envía una solicitud al backend para marcar el usuario como activo.
 * Si la operación es exitosa, muestra un popup y recarga la página.
 * @param {number} userId - ID del usuario a activar.
 */
function activateUser(userId) {
    // Confirmo con el usuario antes de activar
    if (!confirm('¿Estás seguro de que deseas activar este usuario?')) {
        return;
    }

    // Envío la solicitud al backend para activar el usuario
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=activar_usuario&usuario_id=${encodeURIComponent(userId)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                // Si la operación fue exitosa, muestro mensaje y recargo la página
                showPopup(data.mensaje || 'Usuario activado correctamente.');
                setTimeout(() => location.reload(), 2000);
            } else {
                // Si hubo un error, muestro el mensaje de error
                showPopup(data.mensaje || 'No se pudo activar el usuario.');
            }
        })
        .catch(err => {
            // Si ocurre un error de red, lo muestro en consola y en un popup
            console.error('Error al activar el usuario:', err);
            showPopup('Ocurrió un error inesperado.');
        });
}