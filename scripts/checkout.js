document.addEventListener('DOMContentLoaded', function () {

    // Oculto los contenedores de error si están vacíos al cargar la página
    document.querySelectorAll('.error-msg-container').forEach(el => {
        if (!el.textContent.trim()) {
            el.style.display = 'none';
        }
    });

    // Referencias a elementos del DOM para el loader, mensajes y formulario
    const loader = document.getElementById('payment-loader');
    const loaderMsg = document.getElementById('payment-loader-msg');
    const form = document.getElementById('checkout-form');
    const successIcon = document.getElementById('payment-success-icon');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Evito el envío tradicional del formulario

            // Limpio mensajes de error anteriores
            document.querySelectorAll('.error-msg-container').forEach(el => el.innerHTML = '');

            // Preparo los datos del formulario para enviar al backend
            const formData = new FormData(form);
            formData.append('accion', 'procesar_pago');

            // Envío la solicitud al backend para procesar el pago
            fetch('../../verifications/paginaIntermedia.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.exito) {

                        // Si el pago fue exitoso, muestro el loader y mensajes de éxito
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
                                    window.location.href = data.redirect_url; // Redirijo al usuario a la página indicada por el backend
                                }, 2000); // Redirijo después de 2 segundos
                            }, 2000); // Muestro el mensaje de éxito después de 2 segundos
                        }, 2000); // Simulo un tiempo de carga de 2 segundos
                    } else {

                        // Si hay errores, los muestro en los contenedores correspondientes
                        const errorClienteDiv = document.getElementById('errores-cliente');
                        const errorTarjetaDiv = document.getElementById('errores-tarjeta');

                        // Limpio mensajes de error anteriores
                        if (errorClienteDiv) errorClienteDiv.innerHTML = '';
                        if (errorTarjetaDiv) errorTarjetaDiv.innerHTML = '';

                        // Muestro errores de datos del cliente si existen
                        if (data.errores_cliente && errorClienteDiv) {
                            errorClienteDiv.innerHTML = data.errores_cliente.map(e => `<p class="error-msg">${e}</p>`).join('');
                            errorClienteDiv.style.display = 'block';
                        } else {
                            errorClienteDiv.innerHTML = '';
                            errorClienteDiv.style.display = 'none';
                        }

                        // Muestro errores de la tarjeta si existen
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
                    // Si ocurre un error de red o conexión, muestro un mensaje de error
                    showPopup('Error de conexión con el servidor.');
                });
        });
    }
});