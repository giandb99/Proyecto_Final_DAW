document.addEventListener('DOMContentLoaded', function () {
    // Obtengo el botón para descargar el PDF del pedido
    const downloadBtn = document.querySelector('.download-pdf-btn');

    // Si el botón existe, le agrego un evento de clic y configuro la generación del PDF
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function (e) {
            e.preventDefault();

            // Obtengo el ID del pedido desde el atributo data-pedido
            const pedidoId = this.getAttribute('data-pedido');

            // Selecciono el contenido que quiero convertir a PDF
            const content = document.querySelector('.pdf-pedido');

            if (content) {
                // Clono el contenido para no modificar el DOM original
                const clone = content.cloneNode(true);
                // Elimino el botón de descarga del PDF en el clon para que no salga en el PDF
                const btn = clone.querySelector('.download-pdf-btn');
                if (btn) btn.remove();

                // Configuro y genero el PDF usando html2pdf
                html2pdf().set({
                    margin: 10,
                    filename: 'pedido_' + pedidoId + '.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                }).from(clone).save();
            } else {
                // Si no se encuentra el contenido, muestro un mensaje de error
                showPopup('No se pudo generar el PDF.');
            }
        });
    }
});

/**
 * Actualiza el estado de un pedido en el backend y recarga la página para reflejar el cambio.
 * @param {number} pedidoId - ID del pedido a actualizar.
 * @param {string} nuevoEstado - Nuevo estado a asignar al pedido.
 */
function actualizarEstadoPedido(pedidoId, nuevoEstado) {

    // Envío la solicitud al backend para actualizar el estado del pedido
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=actualizar_estado_pedido&pedido_id=${encodeURIComponent(pedidoId)}&estado=${encodeURIComponent(nuevoEstado)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            showPopup(data.mensaje || 'Estado actualizado correctamente.');
            location.reload(); // Recargo la página para reflejar el cambio
        } else {
            // Si hubo un error, muestro el mensaje correspondiente
            showPopup(data.mensaje || 'No se pudo actualizar el estado.');
        }
    })
    .catch(() => showPopup('Error de conexión.'));
}