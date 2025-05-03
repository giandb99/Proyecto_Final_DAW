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
    
    if (urlParams.has('agregar_favorito') && urlParams.get('agregar_favorito') === 'error') {
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

    if (urlParams.has('agregar_carrito') && urlParams.get('agregar_carrito') === 'error') {
        // Creo el popup
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = `
            <p>Debes iniciar sesión para agregar productos al carrito.</p>
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

function showPopup(message) {
    const popup = document.createElement('div');
    popup.className = 'popup popup-dynamic'; // clase extra opcional para diferenciar

    popup.innerHTML = `
        <p>${message}</p>
        <button id="close-popup">Cerrar</button>
    `;

    document.body.appendChild(popup);

    // Cierre manual
    document.getElementById('close-popup').onclick = function () {
        popup.remove();
    };

    // Cierre automático
    setTimeout(() => popup.remove(), 5000);
}