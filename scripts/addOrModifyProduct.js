document.addEventListener('DOMContentLoaded', function () {
    const plataformasCheckboxes = document.querySelectorAll('.plataforma input[type="checkbox"]');

    plataformasCheckboxes.forEach(checkbox => {
        const plataformaDiv = checkbox.closest('.plataforma');
        const stockInput = plataformaDiv.querySelector('.plataforma-stock');

        // Inicializar estado al cargar
        stockInput.disabled = !checkbox.checked;

        // Listener al hacer clic
        checkbox.addEventListener('change', function () {
            stockInput.disabled = !this.checked;
        });
    });
});