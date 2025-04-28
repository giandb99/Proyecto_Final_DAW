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