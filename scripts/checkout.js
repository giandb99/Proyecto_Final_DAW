function processPayment() {
    const form = document.getElementById('checkoutForm');

    // Limpiar mensajes de error previos
    document.getElementById('errores-cliente').innerHTML = '';
    document.getElementById('errores-tarjeta').innerHTML = '';

    const formData = new FormData(form);
    formData.append('accion', 'procesar_pago');

    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.estado === 'ok') {
                // Mostrar mensaje de éxito y redirigir
                showPopup('¡Pago realizado con éxito!', 'success');
                setTimeout(() => {
                    window.location.href = 'pedidos.php';
                }, 2500);
            } else {
                // Mostrar errores en las secciones correspondientes
                if (data.errores.cliente) {
                    document.getElementById('errores-cliente').innerHTML = data.errores.cliente
                        .map(error => `<p class="error-msg">${error}</p>`)
                        .join('');
                }
                if (data.errores.tarjeta) {
                    document.getElementById('errores-tarjeta').innerHTML = data.errores.tarjeta
                        .map(error => `<p class="error-msg">${error}</p>`)
                        .join('');
                }
            }
        })
        .catch(error => {
            console.error('Error en el fetch:', error);
            showPopup('Error de red. Inténtalo más tarde.', 'error');
        });
}