/**
 * Actualiza los datos personales del usuario.
 * Envía los datos del formulario al backend y, si la operación es exitosa,
 * deshabilita los inputs y muestra un mensaje de éxito.
 * 
 * @param {FormData} formData - Datos del formulario a enviar.
 * @param {NodeList} inputs - Inputs del formulario de perfil.
 * @param {HTMLElement} saveButton - Botón de guardar cambios.
 * @param {HTMLElement} editButton - Botón de editar/cancelar.
 */
function updateProfile(formData, inputs, saveButton, editButton) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup('Datos actualizados correctamente.');
                // Deshabilito todos los campos de entrada después de guardar
                inputs.forEach(input => input.disabled = true);
                // Deshabilito el botón de guardar
                saveButton.disabled = true;
                // Habilito el botón de editar y cambio el texto a "Editar"
                editButton.disabled = false;
                editButton.textContent = 'Editar';
            } else {
                // Si hubo un error, muestro el mensaje recibido
                showPopup(data.mensaje || 'Error al actualizar los datos.');
            }
        })
        .catch(error => console.error('Error:', error));
}

/**
 * Actualiza la contraseña del usuario.
 * Envía los datos del formulario al backend y, si la operación es exitosa,
 * limpia y deshabilita los campos de contraseña y muestra un mensaje de éxito.
 * 
 * @param {FormData} formData - Datos del formulario de cambio de contraseña.
 * @param {NodeList} passwordInputs - Inputs del formulario de contraseña.
 * @param {HTMLElement} savePassButton - Botón de guardar contraseña.
 * @param {HTMLElement} editPassButton - Botón de editar/cancelar contraseña.
 */
function updatePassword(formData, passwordInputs, savePassButton, editPassButton) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                showPopup('Contraseña actualizada correctamente.');
                // Limpio y deshabilito los campos de contraseña tras guardar
                passwordInputs.forEach(input => {
                    input.disabled = true; // Deshabilito el input
                    input.value = '';      // Limpio el valor del input
                });
                // Deshabilito el botón de guardar contraseña
                savePassButton.disabled = true;
                // Habilito el botón de editar y cambio el texto a "Editar"
                editPassButton.disabled = false;
                editPassButton.textContent = 'Editar';
            } else {
                // Si hubo un error, muestro el mensaje recibido
                showPopup(data.mensaje || 'Error al actualizar la contraseña.');
            }
        })
        .catch(error => console.error('Error:', error));
}

/**
 * Esta función desactiva la cuenta del usuario actual.
 * Envía una solicitud al backend para desactivar la cuenta.
 * Si la operación es exitosa, muestra un popup y redirige al catálogo tras un breve retraso.
 * Si falla, muestra el mensaje de error correspondiente.
 * 
 * @param {number} userId - ID del usuario a desactivar.
 */
function deleteAccount(userId) {
    // Confirmación antes de proceder
    if (confirm('¿Estás seguro de que deseas desactivar tu cuenta? Esta acción es reversible contactando al soporte.')) {
        
        // Realiza la petición al backend para desactivar la cuenta
        fetch('../../verifications/paginaIntermedia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'accion=desactivar_usuario&usuario_id=' + encodeURIComponent(userId)
        })
            .then(res => res.json()) // Convierte la respuesta a JSON
            .then(data => {
                if (data.exito) {
                    // Si fue exitoso, muestra el popup de éxito
                    showPopup('Tu cuenta ha sido desactivada correctamente.');
                    setTimeout(() => {
                        window.location.href = 'catalog.php';
                    }, 2000); // Espera 2 segundos antes de redirigir al catálogo
                } else {
                    showPopup(data.mensaje || 'No se pudo desactivar la cuenta.'); // Si hubo un error, muestra el mensaje recibido
                }
            })
            .catch(() => showPopup('Error al intentar desactivar la cuenta.')); // Manejo de error de red
    }
}

// Asocia el evento al botón solo si existe
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('.delete-account-btn');
    if (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            desactivarCuentaUsuario();
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // Elementos para datos personales
    const editButton = document.getElementById('edit-button'); // Botón para editar/cancelar datos personales
    const saveButton = document.getElementById('save-button'); // Botón para guardar cambios
    const inputs = document.querySelectorAll('#profile-form input'); // Inputs del formulario de perfil

    // Elementos para contraseña
    const editPassButton = document.getElementById('edit-button-pass'); // Botón para editar/cancelar contraseña
    const savePassButton = document.getElementById('save-button-pass'); // Botón para guardar contraseña
    const passwordInputs = document.querySelectorAll('#change-password-form input'); // Inputs del formulario de contraseña

    // Lógica para editar/cancelar datos personales
    editButton.addEventListener('click', () => {
        if (editButton.textContent.trim() === 'Editar') {
            // Si el botón dice "Editar", habilito los campos para edición
            inputs.forEach(input => input.disabled = false);
            saveButton.disabled = false; // Habilito el botón de guardar
            editButton.textContent = 'Cancelar'; // Cambio el texto a "Cancelar"
        } else {
            // Si el botón dice "Cancelar", deshabilito los campos y restauro el texto
            inputs.forEach(input => input.disabled = true);
            saveButton.disabled = true;
            editButton.textContent = 'Editar';
        }
    });

    // Lógica para editar/cancelar contraseña
    editPassButton.addEventListener('click', () => {
        if (editPassButton.textContent.trim() === 'Editar') {
            // Si el botón dice "Editar", habilito los campos de contraseña
            passwordInputs.forEach(input => input.disabled = false);
            savePassButton.disabled = false; // Habilito el botón de guardar contraseña
            editPassButton.textContent = 'Cancelar'; // Cambio el texto a "Cancelar"
        } else {
            // Si el botón dice "Cancelar", deshabilito y limpio los campos
            passwordInputs.forEach(input => {
                input.disabled = true;
                input.value = '';
            });
            savePassButton.disabled = true;
            editPassButton.textContent = 'Editar';
        }
    });

    // Envío del formulario de perfil
    document.getElementById('profile-form').addEventListener('submit', (e) => {
        e.preventDefault(); // Evito el envío tradicional
        const formData = new FormData(e.target); // Obtengo los datos del formulario
        formData.append('accion', 'actualizar_perfil'); // Agrego la acción
        updateProfile(formData, inputs, saveButton, editButton); // Llamo a la función de actualización
    });

    // Envío del formulario de cambio de contraseña
    document.getElementById('change-password-form').addEventListener('submit', (e) => {
        e.preventDefault(); // Evito el envío tradicional
        const formData = new FormData(e.target); // Obtengo los datos del formulario
        formData.append('accion', 'cambiar_contraseña'); // Agrego la acción
        updatePassword(formData, passwordInputs, savePassButton, editPassButton); // Llamo a la función de actualización
    });
});

// Mostrar/ocultar contraseña al hacer click en el icono del ojo
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target); // Obtengo el input asociado
            if (input.type === "password") {
                // Si está en modo password, lo cambio a texto y cambio el icono
                input.type = "text";
                this.querySelector('i').classList.remove('fa-eye');
                this.querySelector('i').classList.add('fa-eye-slash');
            } else {
                // Si está en modo texto, lo cambio a password y cambio el icono
                input.type = "password";
                this.querySelector('i').classList.remove('fa-eye-slash');
                this.querySelector('i').classList.add('fa-eye');
            }
        });
    });
});