document.addEventListener("DOMContentLoaded", async () => {
    let currentSlide = 0;
    const track = document.querySelector(".carousel-track");
    const slides = document.querySelectorAll(".carousel-slide");

    const data = await fetch("../../verifications/paginaIntermedia.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "accion=obtener_estadisticas"
    }).then(res => res.json());

    new Chart(document.getElementById("usuariosChart"), {
        type: 'doughnut',
        data: {
            labels: ['Activos', 'Inactivos'],
            datasets: [{
                data: [data.usuarios.activos, data.usuarios.inactivos],
                backgroundColor: ['#4CAF50', '#F44336']
            }]
        }
    });

    new Chart(document.getElementById("usuariosNuevosChart"), {
        type: 'bar',
        data: {
            labels: data.usuarios.nuevos.meses,
            datasets: [{
                label: 'Nuevos usuarios',
                data: data.usuarios.nuevos.cantidades,
                backgroundColor: '#00bcd4'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });

    new Chart(document.getElementById("productosChart"), {
        type: 'bar',
        data: {
            labels: ['Activos', 'Inactivos'],
            datasets: [{
                label: 'Productos',
                data: [data.productos.activos, data.productos.inactivos],
                backgroundColor: ['#2196F3', '#F44336']
            }]
        }
    });

    new Chart(document.getElementById("topProductosChart"), {
        type: 'bar',
        data: {
            labels: data.productos.top.nombres,
            datasets: [{
                label: 'Más vendidos',
                data: data.productos.top.cantidades,
                backgroundColor: '#42a5f5'
            }]
        }
    });
    console.log(data.productos.top);

    new Chart(document.getElementById("topPlataformasChart"), {
        type: 'bar',
        data: {
            labels: data.top_plataformas.nombres,
            datasets: [{
                label: 'Plataformas más vendidas',
                data: data.top_plataformas.cantidades,
                backgroundColor: '#ffb300'
            }]
        }
    });

    new Chart(document.getElementById("topUsuariosChart"), {
        type: 'bar',
        data: {
            labels: data.usuarios.top.nombres,
            datasets: [{
                label: 'Usuarios con más compras',
                data: data.usuarios.top.cantidades,
                backgroundColor: '#26a69a'
            }]
        }
    });

    new Chart(document.getElementById("pedidosEstadoChart"), {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'Entregados', 'Cancelados'],
            datasets: [{
                data: [
                    data.pedidos.pendientes,
                    data.pedidos.entregados,
                    data.pedidos.cancelados
                ],
                backgroundColor: ['#fbc02d', '#388e3c', '#d84315']
            }]
        }
    });

    new Chart(document.getElementById("gananciasMensualesChart"), {
        type: 'line',
        data: {
            labels: data.ganancias.mensual.meses,
            datasets: [{
                label: 'Ganancias',
                data: data.ganancias.mensual.valores,
                borderColor: '#673AB7',
                fill: false
            }]
        }
    });

    new Chart(document.getElementById("gananciasPeriodoChart"), {
        type: 'bar',
        data: {
            labels: ['Semana', 'Mes', '3 Meses', 'Año'],
            datasets: [{
                label: 'Ganancias',
                data: [
                    data.ganancias.ganancias_semanales,
                    data.ganancias.ganancias_mensuales,
                    data.ganancias.ganancias_ultimos_3_meses,
                    data.ganancias.ganancias_anuales
                ],
                backgroundColor: ['#1976d2', '#8e24aa', '#0097a7', '#689f38']
            }]
        }
    });

    function updateCharts() {
        [
            usuariosChart,
            usuariosNuevosChart,
            productosChart,
            stockEstadoChart,
            topProductosChart,
            topPlataformasChart,
            topUsuariosChart,
            pedidosEstadoChart,
            gananciasMensualesChart,
            gananciasPeriodoChart
        ].forEach(chart => {
            chart.resize();
            chart.update();
        });
    }

    function updateSlide() {
        const offset = -currentSlide * 100;
        track.style.transform = `translateX(${offset}%)`;
        updateCharts();
    }

    document.querySelector(".prev-slide").addEventListener("click", () => {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        updateSlide();
    });

    document.querySelector(".next-slide").addEventListener("click", () => {
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlide();
    });

    updateSlide();
});