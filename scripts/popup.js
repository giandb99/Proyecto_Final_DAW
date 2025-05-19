// Se muestra un popup con un mensaje si el parámetro "logout" está presente en la URL
window.onload = function () {
    const urlParams = new URLSearchParams(window.location.search);

    // Si el usuario cerró sesión correctamente, muestro popup de éxito
    if (urlParams.has('logout') && urlParams.get('logout') === 'success') {
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = `
            <p>Has cerrado sesión correctamente.</p>
            <button id="close-popup">Cerrar</button>
        `;
        document.body.appendChild(popup);
        document.getElementById('close-popup').onclick = function () { popup.remove(); };
        setTimeout(() => popup.remove(), 5000);
    }

    // Si intenta agregar a favoritos sin estar logueado, muestro popup y redirijo a login
    if (urlParams.has('agregar_favorito') && urlParams.get('agregar_favorito') === 'error') {
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = `
            <p>Debes iniciar sesión para agregar productos a tu lista de favoritos.</p>
            <button id="close-popup">Cerrar</button>
        `;

        document.body.appendChild(popup);
        document.getElementById('close-popup').onclick = function () {
            popup.remove();
            window.location.href = 'login.php';
        };

        setTimeout(() => {
            popup.remove();
            window.location.href = 'login.php';
        }, 5000);
    }

    // Si intenta agregar al carrito sin estar logueado, muestro popup y redirijo a login
    if (urlParams.has('agregar_carrito') && urlParams.get('agregar_carrito') === 'error') {
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = `
            <p>Debes iniciar sesión para agregar productos al carrito.</p>
            <button id="close-popup">Cerrar</button>
        `;

        document.body.appendChild(popup);
        document.getElementById('close-popup').onclick = function () {
            popup.remove();
            window.location.href = 'login.php';
        };
        setTimeout(() => {
            popup.remove();
            window.location.href = 'login.php';
        }, 5000);
    }

    // Si se creó un producto correctamente, muestro popup y redirijo a products.php
    if (urlParams.has('exito') && urlParams.get('exito') === 'Producto creado correctamente') {
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = `
            <p>Producto agregado correctamente.</p>
            <button id="close-popup">Cerrar</button>
        `;
        document.body.appendChild(popup);
        document.getElementById('close-popup').onclick = function () {
            popup.remove();
            window.location.href = 'products.php';
        };
        setTimeout(() => {
            popup.remove();
            window.location.href = 'products.php';
        }, 5000);
    }

    // Si se modificó un producto correctamente, muestro popup y redirijo a products.php
    if (urlParams.has('exito') && urlParams.get('exito') === 'Producto modificado correctamente') {
        const popup = document.createElement('div');
        popup.className = 'popup';
        popup.innerHTML = `
            <p>Producto modificado correctamente.</p>
            <button id="close-popup">Cerrar</button>
        `;
        document.body.appendChild(popup);
        document.getElementById('close-popup').onclick = function () {
            popup.remove();
            window.location.href = 'products.php';
        };
        setTimeout(() => {
            popup.remove();
            window.location.href = 'products.php';
        }, 5000);
    }
};

/**
 * Muestra un popup dinámico con el mensaje recibido.
 * El popup se cierra automáticamente a los 5 segundos o al hacer click en "Cerrar".
 * @param {string} message - Mensaje a mostrar en el popup.
 */
function showPopup(message) {
    const popup = document.createElement('div');
    popup.className = 'popup popup-dynamic';

    popup.innerHTML = `
        <p>${message}</p>
        <button id="close-popup">Cerrar</button>
    `;

    document.body.appendChild(popup);

    // Al hacer click en el botón, cierro el popup
    document.getElementById('close-popup').onclick = function () { popup.remove(); };

    // El popup se cierra automáticamente a los 5 segundos
    setTimeout(() => popup.remove(), 5000);
}