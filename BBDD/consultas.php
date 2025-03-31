<?php

require_once 'conexion.php';

// Función para obtener todos los juegos de la base de datos
function obtenerJuegos()
{
    $conn = conexion();

    // Nueva consulta con JOIN para calcular la media de las valoraciones
    $query = $conn->prepare("
        SELECT p.*, COALESCE(AVG(v.valoracion), 0) AS valoracion_promedio
        FROM producto p
        LEFT JOIN votos v ON p.id = v.producto_id
        GROUP BY p.id
    ");

    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $precioFinal = $row['descuento'] ? $row['precio'] - ($row['precio'] * $row['descuento'] / 100) : $row['precio'];

            echo '
            <div class="product-card" data-id="' . $row['id'] . '">
                <div class="relative">
                    <img src="' . htmlspecialchars($row['imagen']) . '" alt="' . htmlspecialchars($row['nombre']) . '">
                    ' . ($row['descuento'] ? '<div class="discount-tag">' . $row['descuento'] . '% OFF</div>' : '') . '
                </div>
                <div class="product-info">
                    <h2>' . htmlspecialchars($row['nombre']) . '</h2>
                    <p class="description">' . htmlspecialchars($row['descripcion']) . '</p>
                    <div class="rating">
                        <span class="star">⭐</span>
                        <span>' . number_format($row['valoracion_promedio'], 1) . '</span>
                    </div>
                    <div class="price-container">
                        <span class="price">$' . number_format($precioFinal, 2) . '</span>
                        ' . ($row['descuento'] ? '<span class="old-price">$' . number_format($row['precio'], 2) . '</span>' : '') . '
                    </div>
                    <button class="add-to-cart" onclick="agregarAlCarrito(' . $row['id'] . ')">Add to Cart</button>
                </div>
            </div>';
        }
    } else {
        echo '<p>No hay juegos disponibles.</p>';
    }

    $query->close();
    cerrar_conexion($conn);
}
