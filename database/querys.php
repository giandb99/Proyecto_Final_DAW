<?php

require_once 'connection.php';

/* ------------- USUARIOS -------------  */

// Función para registrar un nuevo usuario
function registrarUsuario($name, $username, $email, $pass)
{
    $conn = conexion();

    // Si verificar usuario devuelve true, significa que el usuario ya existe
    if (obtenerDatosUsuario($email, $pass)) {
        $query = http_build_query([
            'error' => 'El correo electrónico ya está registrado.',
            'username' => $username,
            'email' => $email
        ]);
        header("Location: ../views/user/register.php?$query");
        cerrar_conexion($conn);
        return false; // Salir de la función si el usuario ya existe
    }

    // Se inserta el nuevo usuario en la base de datos
    $query = $conn->prepare("INSERT INTO usuario (nombre, username, email, pass) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $name, $username, $email, $pass);

    $result = $query->execute();
    $query->close();
    cerrar_conexion($conn);

    if ($result) {
        header("Location: ../views/user/login.php?exito=Usuario+registrado+con+éxito."); // Redirigir a la página de inicio de sesión
    } else {
        $query = http_build_query([
            'error' => 'Ocurrió un error al registrar el usuario.',
            'username' => $username,
            'email' => $email
        ]);
        header("Location: ../views/user/register.php?$query");
    }
}

function obtenerDatosUsuario($email, $pass)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT id, username, email FROM usuario WHERE email = ? AND pass = ? AND rol = 'user'");
    $query->bind_param("ss", $email, $pass);
    $query->execute();
    $result = $query->get_result();
    $datos = $result->fetch_assoc();
    $query->close();
    cerrar_conexion($conn);

    return $datos ?: false;
}

/* ------------- ADMIN -------------  */

// Función para obtener los datos del administrador
function obtenerDatosAdmin($email, $pass)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT id, username, email FROM usuario WHERE email = ? AND pass = ? AND rol = 'admin'");
    $query->bind_param("ss", $email, $pass);
    $query->execute();
    $result = $query->get_result();
    $datos = $result->fetch_assoc();
    $query->close();
    cerrar_conexion($conn);

    return $datos ?: false;
}

/* ------------- PRODUCTOS -------------  */

// Función para crear un nuevo producto
function crearProducto($nombre, $imagen, $descripcion, $fecha_lanzamiento, $genero_id, $precio, $descuento, $stock, $plataforma_id, $creado_por, $actualizado_por)
{
    // Ruta relativa (para guardar en la BD)
    $rutaRelativa = 'images/products/' . basename($imagen['name']);
    $rutaAbsoluta = '../' . $rutaRelativa;

    // Verifica si el directorio existe
    if (!file_exists('../images/products/')) {
        mkdir('../images/products/', 0777, true);
    }

    // Mueve la imagen al servidor
    if (!move_uploaded_file($imagen['tmp_name'], $rutaAbsoluta)) {
        header("Location: ../views/admin/addOrModifyProduct.php?error=Hubo+un+problema+al+guardar+la+imagen.");
        exit;
    }

    $conn = conexion();

    $query = $conn->prepare("
        INSERT INTO producto 
        (nombre, imagen, descripcion, fecha_lanzamiento, genero_id, precio, descuento, stock, plataforma_id, creado_por, actualizado_por) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $query->bind_param(
        "ssssiddiisi",
        $nombre,
        $rutaRelativa,
        $descripcion,
        $fecha_lanzamiento,
        $genero_id,
        $precio,
        $descuento,
        $stock,
        $plataforma_id,
        $creado_por,
        $actualizado_por
    );

    if ($query->execute()) {
        header("Location: ../views/admin/products.php?exito=Producto+creado+con+éxito.");
    } else {
        header("Location: ../views/admin/addOrModifyProduct.php?error=Error+al+crear+el+producto.");
    }

    $query->close();
    cerrar_conexion($conn);
}


function modificarProducto($id, $nombre, $imagen, $descripcion, $fecha_lanzamiento, $genero_id, $precio, $descuento, $stock, $plataforma_id)
{
    $conn = conexion();

    $query = $conn->prepare("
        UPDATE producto 
        SET nombre = ?, imagen = ?, descripcion = ?, fecha_lanzamiento = ?, genero_id = ?, precio = ?, descuento = ?, stock = ?, plataforma_id = ?
        WHERE id = ?
    ");

    $query->bind_param("sssssidiii", $nombre, $imagen, $descripcion, $fecha_lanzamiento, $genero_id, $precio, $descuento, $stock, $plataforma_id, $id);

    if ($query->execute()) {
        header("Location: ../views/admin/products.php?exito=Producto+modificado+con+éxito.");
    } else {
        header("Location: ../views/admin/addOrModifyProduct.php?error=Error+al+modificar+el+producto.");
    }

    $query->close();
    cerrar_conexion($conn);
}

// // Función para eliminar un producto por su ID
// function eliminarProducto($id)
// {
//     $conn = conexion();

//     // Se prepara la consulta para eliminar el producto
//     $query = $conn->prepare("DELETE FROM producto WHERE id = ?");
//     $query->bind_param("i", $id);

//     // Se ejecuta la consulta y se cierra la conexión
//     if ($query->execute()) {
//         echo "<script class='alert'>Producto eliminado con éxito.</script>";
//     } else {
//         echo "<script class='alert'>Error al eliminar el producto: " . $query->error . "</script>";
//     }

//     $query->close();
//     cerrar_conexion($conn);
// }

// function desactivarProducto($id)
// {
//     $conn = conexion();

//     // Se prepara la consulta para desactivar el producto
//     $query = $conn->prepare("UPDATE producto SET activo = 0 WHERE id = ?");
//     $query->bind_param("i", $id);

//     // Se ejecuta la consulta y se cierra la conexión
//     if ($query->execute()) {
//         echo "<script class='alert'>Producto desactivado con éxito.</script>";
//     } else {
//         echo "<script class='alert'>Error al desactivar el producto: " . $query->error . "</script>";
//     }

//     $query->close();
//     cerrar_conexion($conn);
// }

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
            <div class="product-card" onclick="window.location.href=\'product.php?id=' . $row['id'] . '\'">
                <div class="relative">
                    <img src="../../' . htmlspecialchars($row['imagen'] ?: 'placeholder.svg') . '" alt="' . htmlspecialchars($row['nombre']) . '">
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
                        <div class="buttons-container">
                            <button class="add-to-favorites" onclick="event.stopPropagation(); agregarAFavoritos(' . $row['id'] . ')"><i class="far fa-heart"></i></button> 
                            <button class="add-to-cart" onclick="event.stopPropagation(); agregarAlCarrito(' . $row['id'] . ')">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>';
        }
    } else {
        echo '<p class="no-products">Lo sentimos, no hay productos disponibles.<br>Vuelva más tarde</p>';
    }

    $query->close();
    cerrar_conexion($conn);
}

function obtenerProductosAdmin()
{
    $conn = conexion();

    $query = $conn->prepare("
        SELECT 
            producto.id,
            producto.nombre,
            producto.imagen,
            producto.precio,
            producto.descuento,
            producto.stock,
            genero.nombre AS genero,
            plataforma.nombre AS plataforma
        FROM producto
        JOIN genero ON producto.genero_id = genero.id
        JOIN plataforma ON producto.plataforma_id = plataforma.id
        WHERE producto.activo = 1
        ORDER BY producto.id ASC
    ");

    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        while ($producto = $result->fetch_assoc()) {
            echo '
            <tr>
                <td>' . $producto['id'] . '</td>
                <td>' . htmlspecialchars($producto['nombre']) . '</td>
                <td><img src="../../' . htmlspecialchars($producto['imagen']) . '" alt="Imagen" class="tabla-img"></td>
                <td>' . number_format($producto['precio'], 2) . '€</td>
                <td>' . ($producto['descuento'] ?? '0') . '%</td>
                <td>' . $producto['stock'] . '</td>
                <td>' . htmlspecialchars($producto['plataforma']) . '</td>
                <td>' . htmlspecialchars($producto['genero']) . '</td>
                <td class="acciones">
                    <button onclick="window.location.href=\'addOrModifyProduct.php?id=' . $producto['id'] . '\'" class="btn-icon-modificar" title="Modificar"><i class="fas fa-pen"></i></button>
                    <form action="../../verifications/paginaIntermedia.php" method="POST">
                        <input type="hidden" name="accion" value="desactivar_producto">
                        <input type="hidden" name="id" value="' . $producto['id'] . '">
                        <button type="submit" class="btn-icon-eliminar" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                    </form>
                </td>
                <td><input type="checkbox" name="productos_seleccionados[]" value="' . $producto['id'] . '"></td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="10">No hay productos disponibles.</td></tr>';
    }

    $query->close();
    cerrar_conexion($conn);
}

function obtenerTodosLosProductos()
{
    $conn = conexion();

    // Se prepara la consulta
    // Se hace JOIN con genero y plataforma para obtener los nombres
    $query = $conn->prepare("
        SELECT 
            producto.id,
            producto.nombre,
            producto.imagen,
            producto.precio,
            producto.descuento,
            producto.stock,
            genero.nombre AS genero,
            plataforma.nombre AS plataforma
        FROM producto
        JOIN genero ON producto.genero_id = genero.id
        JOIN plataforma ON producto.plataforma_id = plataforma.id
        WHERE producto.activo = 1 ORDER BY producto.id ASC
    ");

    $query->execute();

    $result = $query->get_result();
    $productos = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row; // Agrega cada producto al array
        }
    }

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión

    return $productos; // Retorna el array de productos
}

/**
 * Función para obtener un juego por su ID.
 * @param int $id ID del juego a buscar.
 * @return array|null Array con los datos del juego o null si no se encuentra.
 */
function obtenerProductoPorId($id)
{
    $conn = conexion();

    $query = $conn->prepare("SELECT * FROM producto WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();

    $producto = $result->fetch_assoc();

    $query->close();
    cerrar_conexion($conn);

    return $producto;
}

/**
 * Función para obtener productos por género.
 * @param int $generoId ID del género a buscar.
 * @return array Array con los productos encontrados.
 */
function obtenerProductoPorGenero($generoId)
{
    $conn = conexion();

    // Se prepara la consulta
    $query = $conn->prepare("SELECT * FROM producto WHERE genero_id = ? AND activo = 1");
    $query->bind_param("i", $generoId); // Se usa "i" para indicar que el parámetro es un entero
    $query->execute();

    $result = $query->get_result();
    $productos = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row; // Agrega cada producto al array
        }
    }

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión

    return $productos; // Retorna el array de productos
}

function obtenerProductosPorPlataforma($plataformaId)
{
    $conn = conexion();

    // Se prepara la consulta
    $query = $conn->prepare("SELECT * FROM producto WHERE plataforma_id = ? AND activo = 1");
    $query->bind_param("i", $plataformaId); // Se usa "i" para indicar que el parámetro es un entero
    $query->execute();

    $result = $query->get_result();
    $productos = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row; // Agrega cada producto al array
        }
    }

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión

    return $productos; // Retorna el array de productos
}

function obtenerGeneros()
{
    $conn = conexion();
    $query = $conn->prepare("SELECT * FROM genero WHERE activo = 1");
    $query->execute();
    $result = $query->get_result();
    $generos = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $generos[] = $row; // Agrega cada género al array
        }
    }

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión

    return $generos; // Retorna el array de géneros
}

function obtenerPlataformas()
{
    $conn = conexion();
    $query = $conn->prepare("SELECT * FROM plataforma WHERE activo = 1");
    $query->execute();
    $result = $query->get_result();
    $plataformas = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plataformas[] = $row; // Agrega cada plataforma al array
        }
    }

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión

    return $plataformas; // Retorna el array de plataformas
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
