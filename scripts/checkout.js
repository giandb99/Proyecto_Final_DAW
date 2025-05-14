document.addEventListener('DOMContentLoaded', function () {
    const loader = document.getElementById('payment-loader');
    const loaderMsg = document.getElementById('payment-loader-msg');
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');

    if (mensaje) {
        loader.style.display = 'flex';
        loaderMsg.textContent = 'Procesando pago...';

        setTimeout(() => {
            loaderMsg.textContent = 'Pago realizado con éxito';
            setTimeout(() => {
                loaderMsg.textContent = 'Pedido realizado correctamente';
                setTimeout(() => {
                    window.location.href = 'userOrder.php';
                }, 2000);
            }, 2000);
        }, 3000);
    }
});