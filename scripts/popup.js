// Se muestra un mensaje si el parámetro "logout" está presente en la URL
window.onload = function () {
    const urlParams = new URLSearchParams(window.location.search);

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

function showPopup(message) {
    const popup = document.createElement('div');
    popup.className = 'popup popup-dynamic';

    popup.innerHTML = `
        <p>${message}</p>
        <button id="close-popup">Cerrar</button>
    `;

    document.body.appendChild(popup);
    document.getElementById('close-popup').onclick = function () { popup.remove(); };
    setTimeout(() => popup.remove(), 5000);
}