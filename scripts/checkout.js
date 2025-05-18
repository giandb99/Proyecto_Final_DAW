document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.error-msg-container').forEach(el => {
        if (!el.textContent.trim()) {
            el.style.display = 'none';
        }
    });

    const loader = document.getElementById('payment-loader');
    const loaderMsg = document.getElementById('payment-loader-msg');
    const form = document.getElementById('checkout-form');
    const successIcon = document.getElementById('payment-success-icon');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            document.querySelectorAll('.error-msg-container').forEach(el => el.innerHTML = '');

            const formData = new FormData(form);
            formData.append('accion', 'procesar_pago');

            fetch('../../verifications/paginaIntermedia.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.exito) {
                        loader.style.display = 'flex';
                        loaderMsg.textContent = 'Procesando pago...';
                        if (successIcon) successIcon.style.display = 'none';

                        setTimeout(() => {
                            loaderMsg.textContent = 'Pago realizado con éxito';
                            if (successIcon) successIcon.style.display = 'block';
                            const spinner = document.querySelector('.loader');
                            if (spinner) spinner.style.display = 'none';

                            setTimeout(() => {
                                loaderMsg.textContent = 'Pedido realizado correctamente';

                                setTimeout(() => {
                                    window.location.href = data.redirect_url;
                                }, 2000);
                            }, 2000);
                        }, 2000);
                    } else {
                        const errorClienteDiv = document.getElementById('errores-cliente');
                        const errorTarjetaDiv = document.getElementById('errores-tarjeta');

                        if (errorClienteDiv) errorClienteDiv.innerHTML = '';
                        if (errorTarjetaDiv) errorTarjetaDiv.innerHTML = '';

                        if (data.errores_cliente && errorClienteDiv) {
                            errorClienteDiv.innerHTML = data.errores_cliente.map(e => `<p class="error-msg">${e}</p>`).join('');
                            errorClienteDiv.style.display = 'block';
                        } else {
                            errorClienteDiv.innerHTML = '';
                            errorClienteDiv.style.display = 'none';
                        }

                        if (data.errores_tarjeta && errorTarjetaDiv) {
                            errorTarjetaDiv.innerHTML = data.errores_tarjeta.map(e => `<p class="error-msg">${e}</p>`).join('');
                            errorTarjetaDiv.style.display = 'block';
                        } else {
                            errorTarjetaDiv.innerHTML = '';
                            errorTarjetaDiv.style.display = 'none';
                        }
                    }
                })
                .catch(() => {
                    alert('Error de conexión con el servidor.');
                });
        });
    }
});