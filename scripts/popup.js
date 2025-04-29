// Se muestra un mensaje si el parámetro "logout" está presente en la URL
window.onload = function () {
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('logout') && urlParams.get('logout') === 'success') {
        // Creo el popup
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = `
            <p>Has cerrado sesión correctamente.</p>
            <button id="close-popup">Cerrar</button>
            `;

        document.body.appendChild(popup);

        // Cierre manual del popup
        document.getElementById('close-popup').onclick = function () { popup.remove(); };

        // Cierre automático del popup después de 5 segundos
        setTimeout(() => popup.remove(), 5000);
    }
};

// Se muestra un mensaje si el parámetro "debes iniciar sesión" está presente en la URL
window.onload = function () {
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('debes_iniciar_sesion') && urlParams.get('debes_iniciar_sesion') === 'error') {
        // Creo el popup
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = `
            <p>Debes iniciar sesión para agregar productos a tu lista de favoritos.</p>
            <button id="close-popup">Cerrar</button>
            `;

        document.body.appendChild(popup);

        // Creo un botón para el cierre manual del popup
        document.getElementById('close-popup').onclick = function () {
            popup.remove();
            window.location.href = 'login.php';
        };

        // Cierro automáticamente el popup después de 5 segundos
        setTimeout(() => {
            popup.remove();
            window.location.href = 'login.php';
        }, 5000);
    }
};