document.addEventListener('DOMContentLoaded', () => {
    const editButton = document.getElementById('edit-button');
    const saveButton = document.getElementById('save-button');
    const inputs = document.querySelectorAll('#profile-form input');

    editButton.addEventListener('click', () => {
        inputs.forEach(input => input.disabled = false);
        saveButton.disabled = false;
        editButton.disabled = true;
    });

    document.getElementById('profile-form').addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        formData.append('accion', 'actualizar_perfil');

        fetch('../../verifications/paginaIntermedia.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    alert('Datos actualizados correctamente.');
                    inputs.forEach(input => input.disabled = true);
                    saveButton.disabled = true;
                    editButton.disabled = false;
                } else {
                    alert(data.mensaje || 'Error al actualizar los datos.');
                }
            })
            .catch(error => console.error('Error:', error));
    });

    document.getElementById('change-password-form').addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        formData.append('accion', 'cambiar_contraseña');

        fetch('../../verifications/paginaIntermedia.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    alert('Contraseña actualizada correctamente.');
                    e.target.reset();
                } else {
                    alert(data.mensaje || 'Error al actualizar la contraseña.');
                }
            })
            .catch(error => console.error('Error:', error));
    });
});