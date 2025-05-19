/**
 * Función para desactivar un usuario.
 * @param {number} userId - ID del usuario a desactivar.
 */
function deactivateUser(userId) {
    if (!confirm('¿Estás seguro de que deseas desactivar este usuario?')) {
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=desactivar_usuario&usuario_id=${encodeURIComponent(userId)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Usuario desactivado correctamente.');
                setTimeout(() => location.reload(), 2000);
            } else {
                showPopup(data.mensaje || 'No se pudo desactivar el usuario.');
            }
        })
        .catch(err => {
            console.error('Error al desactivar el usuario:', err);
            showPopup('Ocurrió un error inesperado.');
        });
}

function activateUser(userId) {
    if (!confirm('¿Estás seguro de que deseas activar este usuario?')) {
        return;
    }

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=activar_usuario&usuario_id=${encodeURIComponent(userId)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                showPopup(data.mensaje || 'Usuario activado correctamente.');
                setTimeout(() => location.reload(), 2000);
            } else {
                showPopup(data.mensaje || 'No se pudo activar el usuario.');
            }
        })
        .catch(err => {
            console.error('Error al activar el usuario:', err);
            showPopup('Ocurrió un error inesperado.');
        });
}