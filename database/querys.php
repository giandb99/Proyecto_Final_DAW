<?php

require_once 'connection.php';

/* ------------- USUARIOS -------------  */

// Función para registrar un nuevo usuario
function registrarUsuario($username, $email, $pass)
{
    $conn = conexion();

    // Si verificar usuario devuelve true, significa que el usuario ya existe
    if (verificarUsuario($email, $pass)) {
        header("Location: ../views/user/register.php?error=El+correo+electrónico+ya+está+registrado.");
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
        header("Location: ../views/user/login.php?exito=Usuario+registrado+con+éxito."); // Redirigir a la página de inicio de sesión
    } else {
        header("Location: ../views/user/register.php?error=El+correo+electrónico+ya+está+registrado."); // Redirigir a la página de inicio de sesión
    }
}

// Función para verificar si un usuario existe en la base de datos
function verificarUsuario($email, $pass)
{
    $conn = conexion();

    // Se prepara la consulta para verificar si el usuario existe
    $query = $conn->prepare("SELECT id FROM usuario WHERE email = ? AND pass = ? AND rol = 'user'");
    $query->bind_param("ss", $email, $pass);
    $query->execute();
    $result = $query->get_result();

    $exists = $result->num_rows > 0;

    $query->close();
    cerrar_conexion($conn);

    return $exists;
}

/* ------------- ADMIN -------------  */

// Función para verificar si un usuario es administrador
function verificarAdmin($email, $pass)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT id FROM usuario WHERE email = ? AND pass = ? AND rol = 'admin'");
    $query->bind_param("ss", $email, $pass);
    $query->execute();
    $result = $query->get_result();

    $isAdmin = $result->num_rows > 0;

    $query->close();
    cerrar_conexion($conn);

    return $isAdmin;
}

/* ------------- PRODUCTOS -------------  */

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
                    <div class="title-container">
                        <h3 class="game-title">' . htmlspecialchars($row['nombre']) . '</h3>
                        <div class="rating">
                            <span class="star">⭐</span>
                            <span>' . number_format($row['valoracion_promedio'], 1) . '</span>
                        </div>
                    </div>
                    <div class="description-container"><p class="description">' . htmlspecialchars($row['descripcion']) . '</p></div>
                    <div class="foot-container">
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
 * Función para obtener un juego por su ID.
 * @param int $id ID del juego a buscar.
 * @return array|null Array con los datos del juego o null si no se encuentra.
 */
function obtenerProductoPorId($id)
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

/* ------------- CARRITO -------------  */

// agregar producto al carrito
function agregarProductoAlCarrito($userId, $productId)
{
    $conn = conexion();

    // Se verifica si el carrito del usuario ya existe
    $query = $conn->prepare("SELECT id FROM carrito WHERE creado_por = ? AND activo = 1");
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $carrito = $result->fetch_assoc();
        $carritoId = $carrito['id'];
    } else {
        // Si no existe, se crea un nuevo carrito
        $query = $conn->prepare("INSERT INTO carrito (creado_por) VALUES (?)");
        $query->bind_param("i", $userId);
        $query->execute();
        $carritoId = $conn->insert_id; // Se obtiene el ID del nuevo carrito
    }

    // Se agrega el producto al carrito
    $query = $conn->prepare("INSERT INTO carrito_item (carrito_id, producto_id, cantidad, precio_total) VALUES (?, ?, 1, (SELECT precio FROM producto WHERE id = ?))");
    $query->bind_param("iii", $carritoId, $productId, $productId);
    $result = $query->execute();

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión

    return $result; // Se retorna el resultado de la operación
}

/* ------------- FAVORITOS -------------  */

// agregar producto a favoritos
function agregarProductoAFavoritos($userId, $productId)
{
    $conn = conexion();

    // Se verifica si la lista de favoritos del usuario ya existe
    $query = $conn->prepare("SELECT id FROM favorito WHERE creado_por = ? AND activo = 1");
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $favorito = $result->fetch_assoc();
        $favoritoId = $favorito['id'];
    } else {
        // Si no existe, se crea una nueva lista de favorito
        $query = $conn->prepare("INSERT INTO favorito (creado_por) VALUES (?)");
        $query->bind_param("i", $userId);
        $query->execute();
        $favoritoId = $conn->insert_id; // Se obtiene el ID de la nueva lista de favorito
    }

    // Se agrega el producto a favorito
    $query = $conn->prepare("INSERT INTO favorito_item (favorito_id, producto_id) VALUES (?, ?)");
    $query->bind_param("ii", $favoritoId, $productId);
    $result = $query->execute();

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión

    return $result; // Se retorna el resultado de la operación
}

/* ------------- DASHBOARD -------------  */

//funcion para obtener el total de productos para el dashboard del admin
function obtenerTotalProductos()
{
    $conn = conexion();

    // Se prepara la consulta para contar el total de productos
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM producto");
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    $query->close();
    cerrar_conexion($conn);

    return $row['total']; // Retorna el total de productos
}

//funcion para obtener el total de productos para el dashboard del admin
function obtenerTotalProductosActivos()
{
    $conn = conexion();

    // Se prepara la consulta para contar el total de productos
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM producto WHERE activo = 1");
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    $query->close();
    cerrar_conexion($conn);

    return $row['total']; // Retorna el total de productos
}

// funcion para obtener el total de usuarios registrados para el dashboard del admin
function obtenerTotalUsuarios()
{
    $conn = conexion();

    // Se prepara la consulta para contar el total de usuarios
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM usuario WHERE rol = 'user'");
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    $query->close();
    cerrar_conexion($conn);

    return $row['total']; // Retorna el total de usuarios
}

