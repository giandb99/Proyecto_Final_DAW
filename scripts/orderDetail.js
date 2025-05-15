window.onload = function () {
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
                alert('No se pudo generar el PDF.');
            }
        });
    }
};
