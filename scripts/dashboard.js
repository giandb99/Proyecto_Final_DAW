document.addEventListener("DOMContentLoaded", async () => {
    let currentSlide = 0;
    const track = document.querySelector(".carousel-track");
    const slides = document.querySelectorAll(".carousel-slide");

    // Solicito las estadísticas al backend para mostrar en los gráficos del dashboard
    const data = await fetch("../../verifications/paginaIntermedia.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "accion=obtener_estadisticas"
    }).then(res => res.json())

    // Gráfico de usuarios activos vs inactivos
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

    // Gráfico de nuevos usuarios por mes
    new Chart(document.getElementById("usuariosNuevosChart"), {
        type: 'bar', // Tipo de gráfico: barras verticales
        data: {
            // Eje X: meses (por ejemplo: ["Enero", "Febrero", ...])
            labels: data.usuarios.nuevos.meses, // <-- Este array viene del backend (PHP), lo trae la petición fetch de arriba
            datasets: [{
                label: 'Nuevos usuarios', // Leyenda del dataset (aparece arriba del gráfico)
                // Eje Y: cantidad de usuarios nuevos por cada mes (por ejemplo: [10, 15, 8, ...])
                data: data.usuarios.nuevos.cantidades, // <-- Este array también lo provee el backend, mismo orden que los meses
                backgroundColor: '#00bcd4' // Color de las barras
            }]
        },
        options: {
            responsive: true, // El gráfico se adapta al tamaño del contenedor/pantalla
            plugins: {
                legend: { display: false } // Oculto la leyenda porque solo hay un dataset
            }
        }
    });

    // Gráfico de productos activos vs inactivos
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

    // Gráfico de productos más vendidos
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

    // Gráfico de plataformas más vendidas
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

    // Gráfico de usuarios con más compras
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

    // Gráfico de estado de los pedidos (pendientes, entregados, cancelados)
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

    // Gráfico de ganancias mensuales (línea)
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

    // Gráfico de ganancias por periodo (barra)
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

    /**
     * Actualiza el tamaño y los datos de todos los gráficos al cambiar de slide.
     */
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

    /**
     * Cambia el slide del carrusel y actualiza los gráficos visibles.
     */
    function updateSlide() {
        const offset = -currentSlide * 100;
        track.style.transform = `translateX(${offset}%)`;
        updateCharts();
    }

    // Botón para ir al slide anterior
    document.querySelector(".prev-slide").addEventListener("click", () => {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        updateSlide();
    });

    // Botón para ir al siguiente slide
    document.querySelector(".next-slide").addEventListener("click", () => {
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlide();
    });

    // Inicializo el carrusel en el primer slide
    updateSlide();
});