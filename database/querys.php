<?php

require_once 'connection.php';

// Función para registrar un nuevo usuario
function registrarUsuario($username, $email, $pass)
{
    $conn = conexion();

    // Si verificar usuario devuelve true, significa que el usuario ya existe
    if (verificarUsuario($email, $pass)) {
        cerrar_conexion($conn);
        return false; // El usuario ya existe
    }

    // Se inserta el nuevo usuario en la base de datos
    $query = $conn->prepare("INSERT INTO usuario (username, email, pass) VALUES (?, ?, ?)");
    $query->bind_param("sss", $username, $email, $pass);

    $result = $query->execute();
    $query->close();
    cerrar_conexion($conn);

    if ($result) {
        header("Location: ../views/user/login.php"); // Redirigir a la página de inicio de sesión
    } else {
        header("Location: ../views/user/register.php"); // Redirigir a la página de inicio de sesión
    }
}

// Función para verificar si un usuario existe en la base de datos
function verificarUsuario($email, $pass)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT * FROM usuario WHERE email = ? AND pass = ?");
    $query->bind_param("ss", $email, $pass);
    $query->execute();
    $result = $query->get_result();

    $exists = $result->num_rows > 0;

    $query->close();
    cerrar_conexion($conn);

    return $exists;
}

// Función para verificar si un usuario es administrador
function verificarAdmin($email, $pass)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT * FROM usuario WHERE email = ? AND pass = ? AND admin = 1");
    $query->bind_param("ss", $email, $pass);
    $query->execute();
    $result = $query->get_result();

    $isAdmin = $result->num_rows > 0;

    $query->close();
    cerrar_conexion($conn);

    return $isAdmin;
}

// Función para crear un nuevo producto
function crearProducto($nombre, $descripcion, $precio, $descuento, $imagen)
{
    $conn = conexion();

    // Se prepara la consulta para insertar el nuevo producto
    $query = $conn->prepare("INSERT INTO producto (nombre, descripcion, precio, descuento, imagen) VALUES (?, ?, ?, ?, ?)");
    $query->bind_param("ssdds", $nombre, $descripcion, $precio, $descuento, $imagen);

    // Se ejecuta la consulta y se cierra la conexión
    if ($query->execute()) {
        echo "Producto creado con éxito.";
    } else {
        echo "Error al crear el producto: " . $query->error;
    }

    $query->close();
    cerrar_conexion($conn);
}

// Función para modificar un producto existente
function modificarProducto($id, $nombre, $descripcion, $precio, $descuento, $imagen)
{
    $conn = conexion();

    // Se prepara la consulta para modificar el producto
    $query = $conn->prepare("UPDATE producto SET nombre = ?, descripcion = ?, precio = ?, descuento = ?, imagen = ? WHERE id = ?");
    $query->bind_param("ssddsi", $nombre, $descripcion, $precio, $descuento, $imagen, $id);

    // Se ejecuta la consulta y se cierra la conexión
    if ($query->execute()) {
        echo "Producto actualizado con éxito.";
    } else {
        echo "Error al modificar el producto: " . $query->error;
    }

    $query->close();
    cerrar_conexion($conn);
}

// Función para eliminar un producto por su ID
function eliminarProducto($id)
{
    $conn = conexion();

    // Se prepara la consulta para eliminar el producto
    $query = $conn->prepare("DELETE FROM producto WHERE id = ?");
    $query->bind_param("i", $id);

    // Se ejecuta la consulta y se cierra la conexión
    if ($query->execute()) {
        echo "<script class='alert'>Producto eliminado con éxito.</script>";
    } else {
        echo "<script class='alert'>Error al eliminar el producto: " . $query->error . "</script>";
    }

    $query->close();
    cerrar_conexion($conn);
}

/**
 * Esta función consulta la base de datos para obtener todos los juegos y sus detalles,
 * incluyendo la media de las valoraciones.
 * Luego, genera el HTML para mostrar cada juego en una tarjeta.
 * @return void
 */
function obtenerProductosClientes()
{
    $conn = conexion();

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
            <div class="product-card">
                <div class="relative">
                    <img src="' . htmlspecialchars($row['imagen'] ?: 'placeholder.svg') . '" alt="' . htmlspecialchars($row['nombre']) . '">
                    ' . ($row['descuento'] ? '<div class="discount-tag">' . $row['descuento'] . '% OFF</div>' : '') . '
                </div>
                <div class="product-info">
                    <div class="price-container">
                        <h3 class="game-title">' . htmlspecialchars($row['nombre']) . '</h3>
                        <div class="rating">
                            <span class="star">⭐</span>
                            <span>' . number_format($row['valoracion_promedio'], 1) . '</span>
                        </div>
                    </div>
                    <p class="description">' . htmlspecialchars($row['descripcion']) . '</p>
                    <div class="price-container">';
                        if ($row['descuento']) {
                            echo '
                            <span class="price">$' . number_format($precioFinal, 2) . '</span>
                            <span class="old-price">$' . number_format($row['precio'], 2) . '</span>';
                        } else {
                            echo '<span class="price">$' . number_format($row['precio'], 2) . '</span>';
                        }
                    echo '</div>
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


/**
 * Función para obtener todos los productos para la vista de administración.
 * @return
 */
function obtenerProductosAdmin(){
    $conn = conexion();

    // Se prepara la consulta para obtener todos los productos
    $query = $conn->prepare("SELECT * FROM producto");
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '
            <div class="product-card" data-id="' . $row['id'] . '">
                <img src="' . htmlspecialchars($row['imagen']) . '" alt="' . htmlspecialchars($row['nombre']) . '">
                <h2>' . htmlspecialchars($row['nombre']) . '</h2>
                <p class="description">' . htmlspecialchars($row['descripcion']) . '</p>
                <span class="price">$' . number_format($row['precio'], 2) . '</span>
                <button class="edit-button" onclick="editarProducto(' . $row['id'] . ')">Editar</button>
                <button class="delete-button" onclick="eliminarProducto(' . $row['id'] . ')">Eliminar</button>
            </div>';
        }
    } else {
        echo '<p>No hay productos disponibles.</p>';
    }

    $query->close();
    cerrar_conexion($conn);
}

/**
 * Función para obtener un juego por su ID.
 * @param int $id ID del juego a buscar.
 * @return array|null Array con los datos del juego o null si no se encuentra.
 */
function obtenerJuegoPorId($id)
{
    $conn = conexion();
    
    // Se prepara la consulta
    $query = $conn->prepare("SELECT * FROM producto WHERE id = ?");
    $query->bind_param("i", $id); // Se usa "i" para indicar que el parámetro es un entero
    $query->execute();

    $result = $query->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Retorna el juego encontrado
    } else {
        return null; // Retorna null si no se encuentra el juego
    }

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión
}