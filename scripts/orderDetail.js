document.addEventListener('DOMContentLoaded', function () {
    const downloadBtn = document.querySelector('.download-pdf-btn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function (e) {
            e.preventDefault();

            const pedidoId = this.getAttribute('data-pedido');
            const content = document.querySelector('.pdf-pedido');
            if (content) {
                const clone = content.cloneNode(true);
                const btn = clone.querySelector('.download-pdf-btn');
                if (btn) btn.remove();

                html2pdf().set({
                    margin: 10,
                    filename: 'pedido_' + pedidoId + '.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                }).from(clone).save();
            } else {
                showPopup('No se pudo generar el PDF.');
            }
        });
    }
});

function actualizarEstadoPedido(pedidoId, nuevoEstado) {
    fetch('../../verifications/paginaIntermedia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=actualizar_estado_pedido&pedido_id=${encodeURIComponent(pedidoId)}&estado=${encodeURIComponent(nuevoEstado)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.exito) {
            showPopup(data.mensaje || 'Estado actualizado correctamente.');
            location.reload(); // Recarga para ver el nuevo estado
        } else {
            showPopup(data.mensaje || 'No se pudo actualizar el estado.');
        }
    })
    .catch(() => showPopup('Error de conexión.'));
}