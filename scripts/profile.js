function updateProfile(formData, inputs, saveButton, editButton) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                showPopup('Datos actualizados correctamente.');
                inputs.forEach(input => input.disabled = true);
                saveButton.disabled = true;
                editButton.disabled = false;
            } else {
                showPopup(data.mensaje || 'Error al actualizar los datos.');
            }
        })
        .catch(error => console.error('Error:', error));
}

function updatePassword(formData, passwordInputs, savePassButton, editPassButton) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                showPopup('Contraseña actualizada correctamente.');
                passwordInputs.forEach(input => input.disabled = true);
                savePassButton.disabled = true;
                editPassButton.disabled = false;
            } else {
                showPopup(data.mensaje || 'Error al actualizar la contraseña.');
            }
        })
        .catch(error => console.error('Error:', error));
}

document.addEventListener('DOMContentLoaded', () => {
    // Datos personales
    const editButton = document.getElementById('edit-button');
    const saveButton = document.getElementById('save-button');
    const inputs = document.querySelectorAll('#profile-form input');

    // Contraseña
    const editPassButton = document.getElementById('edit-button-pass');
    const savePassButton = document.getElementById('save-button-pass');
    const passwordInputs = document.querySelectorAll('#change-password-form input');

    // Datos personales
    editButton.addEventListener('click', () => {
        if (editButton.textContent.trim() === 'Editar') {
            inputs.forEach(input => input.disabled = false);
            saveButton.disabled = false;
            editButton.textContent = 'Cancelar';
        } else {
            inputs.forEach(input => input.disabled = true);
            saveButton.disabled = true;
            editButton.textContent = 'Editar';
        }
    });

    // Contraseña
    editPassButton.addEventListener('click', () => {
        if (editPassButton.textContent.trim() === 'Editar') {
            passwordInputs.forEach(input => input.disabled = false);
            savePassButton.disabled = false;
            editPassButton.textContent = 'Cancelar';
        } else {
            passwordInputs.forEach(input => input.disabled = true);
            savePassButton.disabled = true;
            editPassButton.textContent = 'Editar';
        }
    });

    document.getElementById('profile-form').addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        formData.append('accion', 'actualizar_perfil');

        updateProfile(formData, inputs, saveButton, editButton);
    });

    document.getElementById('change-password-form').addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        formData.append('accion', 'cambiar_contraseña');

        updatePassword(formData, passwordInputs, savePassButton, editPassButton);
    });
});

// Mostrar/ocultar contraseña
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (input.type === "password") {
                input.type = "text";
                this.querySelector('i').classList.remove('fa-eye');
                this.querySelector('i').classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                this.querySelector('i').classList.remove('fa-eye-slash');
                this.querySelector('i').classList.add('fa-eye');
            }
        });
    });
});