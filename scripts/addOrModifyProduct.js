document.addEventListener('DOMContentLoaded', function () {
    const plataformasCheckboxes = document.querySelectorAll('.plataforma input[type="checkbox"]');

    plataformasCheckboxes.forEach(checkbox => {
        const plataformaDiv = checkbox.closest('.plataforma');
        const stockInput = plataformaDiv.querySelector('.plataforma-stock');

        // Inicializo el estado del input de stock según si la plataforma está seleccionada
        stockInput.disabled = !checkbox.checked;

        // Al cambiar el estado del checkbox, habilito o deshabilito el input de stock
        checkbox.addEventListener('change', function () {
            stockInput.disabled = !this.checked;
        });
    });
});