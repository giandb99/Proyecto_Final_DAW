document.addEventListener('DOMContentLoaded', function () {
    const loader = document.getElementById('payment-loader');
    const loaderMsg = document.getElementById('payment-loader-msg');
    const form = document.getElementById('checkout-form');
    const successIcon = document.getElementById('payment-success-icon');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            console.log('Submit interceptado');
            loader.style.display = 'flex';
            loaderMsg.textContent = 'Procesando pago...';
            if (successIcon) successIcon.style.display = 'none';
            setTimeout(() => {
                loaderMsg.textContent = 'Pago realizado con éxito';
                if (successIcon) successIcon.style.display = 'block';
                // Oculta la ruedita:
                const spinner = document.querySelector('.loader');
                if (spinner) spinner.style.display = 'none';
                setTimeout(() => {
                    loaderMsg.textContent = 'Pedido realizado correctamente';
                    setTimeout(() => {
                        form.submit();
                    }, 2000);
                }, 2000);
            }, 2000);
        });
    }
});