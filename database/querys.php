<?php

require_once 'connection.php';

/* ------------- USUARIOS -------------  */

// Función para registrar un nuevo usuario
function createUser($name, $username, $email, $pass)
{
    $conn = conexion();

    // Si verificar usuario devuelve true, significa que el usuario ya existe
    if (getUserData($email, $pass)) {
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

    if ($result) {
        $usuarioId = $conn->insert_id;
        createFavList($usuarioId);
        header("Location: ../views/user/login.php?exito=Usuario+registrado+con+éxito."); // Redirigir a la página de inicio de sesión
    } else {
        $query = http_build_query([
            'error' => 'Ocurrió un error al registrar el usuario.',
            'username' => $username,
            'email' => $email
        ]);
        header("Location: ../views/user/register.php?$query");
    }

    $query->close();
    cerrar_conexion($conn);
}

function login($usuario)
{
    // Actualizamos el campo ultimo_login con la fecha y hora actual
    $conn = conexion();
    $query = $conn->prepare("UPDATE usuario SET ultimo_login = NOW() WHERE id = ?");
    $query->bind_param("i", $usuario['id']);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);

    $_SESSION['usuario'] = [
        'id' => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'username' => $usuario['username'],
        'email' => $usuario['email'],
        'telefono' => $usuario['telefono'],
        'direccion' => $usuario['direccion'],
        'fecha_nac' => $usuario['fecha_nac'],
        'cp' => $usuario['cp'],
        'rol' => $usuario['rol'],
        'fecha_creacion' => $usuario['fecha_creacion'],
        'ultimo_login' => $usuario['ultimo_login'],
        'activo' => $usuario['activo'],
        'imagen_perfil' => !empty($usuario['imagen_perfil']) ? true : false
    ];
}

function getUserData($email, $pass)
{
    $conn = conexion();

    $query = $conn->prepare("SELECT * FROM usuario WHERE email = ? AND pass = ? AND rol = 'user'");
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
function getAdminData($email, $pass)
{
    $conn = conexion();

    $query = $conn->prepare("SELECT * FROM usuario WHERE email = ? AND pass = ? AND rol = 'admin'");
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
function createProduct($nombre, $imagen, $descripcion, $fecha_lanzamiento, $genero_id, $precio, $descuento, $stock, $plataforma_id, $creado_por, $actualizado_por)
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

function modifyProduct($id, $nombre, $imagen, $descripcion, $fecha_lanzamiento, $genero_id, $precio, $descuento, $stock, $plataforma_id)
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

/**
 * Función para eliminar un producto por su ID.
 *
 * Elimina un registro de la tabla `productos` basado en el ID proporcionado.
 *
 * @param int $id El ID del producto a eliminar.
 * @return bool Retorna `true` si la eliminación fue exitosa, `false` en caso contrario.
 */
function deleteProduct($id)
{
    $conn = conexion();

    $query = $conn->prepare("DELETE FROM producto WHERE id = ?");
    $query->bind_param("i", $id);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);

    return $resultado; // Retorna `true` si se eliminó correctamente
}

/**
 * Esta función consulta la base de datos para obtener todos los juegos y sus detalles,
 * incluyendo la media de las valoraciones.
 * Luego, genera el HTML para mostrar cada juego en una tarjeta.
 * @return void
 */
function getCatalog()
{
    $conn = conexion();

    $query = $conn->prepare("
        SELECT p.*, COALESCE(AVG(v.valoracion), 0) AS valoracion_promedio
        FROM producto p
        LEFT JOIN votos v ON p.id = v.producto_id
        WHERE p.activo = 1
        GROUP BY p.id
    ");

    $query->execute();
    $result = $query->get_result();
    $productos = [];

    if ($result->num_rows > 0) {
        while ($producto = $result->fetch_assoc()) {
            $productos[] = $producto;
        }
    }

    $query->close();
    cerrar_conexion($conn);
    return $productos; // Retorna el array de productos
}

function getAllProdutcs()
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
    $products = [];

    if ($result->num_rows > 0) {
        while ($producto = $result->fetch_assoc()) {
            $products[] = $producto; // Agrega cada producto al array
        }
    }

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión
    return $products; // Retorna el array de productos
}

/**
 * Función para obtener un juego por su ID.
 * @param int $id ID del juego a buscar.
 * @return array|null Array con los datos del juego o null si no se encuentra.
 */
function getProductById($id)
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

function getGenres()
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

function getPlatforms()
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
function addToCart($userId, $productId)
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

// funcion para crear la lista de favoritos
function createFavList($usuarioId)
{
    $conn = conexion();
    $query = $conn->prepare("INSERT INTO favorito (creado_por, activo) VALUES (?, 1)");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Función para obtener el ID de la lista de favoritos de un usuario.
 * @param int $usuarioId ID del usuario
 * @return int|null Retorna el ID de la lista de favoritos o null si no existe.
 */
function getActiveFavListId($usuarioId)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT id FROM favorito WHERE creado_por = ? AND activo = 1 LIMIT 1");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $query->bind_result($favoritoId);
    $query->fetch();
    $query->close();
    cerrar_conexion($conn);
    return $favoritoId;
}

/**
 * Funcion que verifica si un producto ya está en la lista de favoritos del usuario
 * @param int $favoritoId ID de la lista de favoritos
 * @param int $productoId ID del producto
 * @return bool Retorna true si el producto ya está en favoritos, false en caso contrario
 */
function productIsAlreadyFavorite($favoritoId, $productoId)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT 1 FROM favorito_item WHERE favorito_id = ? AND producto_id = ?");
    $query->bind_param("ii", $favoritoId, $productoId);
    $query->execute();
    $query->store_result();
    $existe = $query->num_rows > 0;
    $query->close();
    cerrar_conexion($conn);

    return $existe;
}

/**
 * Función para agregar o eliminar un producto de la lista de favoritos del usuario.
 * @param int $usuarioId ID del usuario
 * @param int $productoId ID del producto
 * @return bool Retorna true si se agregó a favoritos, false si se eliminó.
 */
function addOrRemoveFav($usuarioId, $productoId)
{
    $favoritoId = getActiveFavListId($usuarioId);

    if (productIsAlreadyFavorite($favoritoId, $productoId)) {
        // Eliminar de favoritos
        $conn = conexion();
        $query = $conn->prepare("DELETE FROM favorito_item WHERE favorito_id = ? AND producto_id = ?");
        $query->bind_param("ii", $favoritoId, $productoId);
        $query->execute();
        $query->close();
        cerrar_conexion($conn);
        return false;
    } else {
        // Agregar a favoritos
        $conn = conexion();
        $query = $conn->prepare("INSERT INTO favorito_item (favorito_id, producto_id) VALUES (?, ?)");
        $query->bind_param("ii", $favoritoId, $productoId);
        $query->execute();
        $query->close();
        cerrar_conexion($conn);
        return true;
    }
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
