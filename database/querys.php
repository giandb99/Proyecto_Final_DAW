<?php

require_once 'connection.php';

/* ------------- USUARIOS -------------  */

/**
 * Función para crear un nuevo usuario.
 * @param string $name Nombre del usuario.
 * @param string $username Nombre de usuario.
 * @param string $email Correo electrónico del usuario.
 * @param string $pass Contraseña del usuario.
 * @return bool Retorna true si el usuario fue creado exitosamente, false en caso contrario.
 */
function createUser($name, $username, $email, $pass)
{
    $conn = conexion();

    // Verificar si el nombre de usuario o el correo electrónico ya existen
    $errores = verifyUser($conn, $username, $email);
    if (!empty($errores)) {
        $query = http_build_query([
            'errores' => implode(', ', $errores),
            'username' => $username,
            'email' => $email
        ]);
        header("Location: ../views/user/register.php?$query");
        cerrar_conexion($conn);
        return false;
    }

    // Insertar nuevo usuario
    $query = $conn->prepare("INSERT INTO usuario (nombre, username, email, pass) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $name, $username, $email, $pass);
    $result = $query->execute();

    if ($result) {
        $usuarioId = $conn->insert_id;
        createFavList($usuarioId);
        createCart($usuarioId);
        header("Location: ../views/user/login.php?exito=Usuario+registrado+con+éxito.");
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

/**
 * Función para verificar si el nombre de usuario o el correo electrónico ya existen en la base de datos.
 * @param mysqli $conn Conexión a la base de datos.
 * @param string $username Nombre de usuario.
 * @param string $email Correo electrónico.
 * @return array Array con los errores encontrados.
 */
function verifyUser($conn, $username, $email)
{
    // Verificar si el username o el email ya existen
    $query = $conn->prepare("SELECT username, email FROM usuario WHERE username = ? OR email = ?");
    $query->bind_param("ss", $username, $email);
    $query->execute();
    $result = $query->get_result();

    $errores = [];
    while ($usuario = $result->fetch_assoc()) {
        if ($usuario['username'] === $username) {
            $errores[] = 'El nombre de usuario ya está registrado.';
        }
        if ($usuario['email'] === $email) {
            $errores[] = 'El correo electrónico ya está registrado.';
        }
    }

    $query->close();
    return $errores;
}

/**
 * Función para iniciar sesión y actualizar el último inicio de sesión del usuario.
 * @param int $id ID del usuario.
 * @return bool Retorna true si el usuario está activo, false en caso contrario.
 */
function login($usuario)
{
    // Se actualiza el campo ultimo_login con la fecha y hora actual
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

/**
 * Función para obtener los datos del usuario por su correo electrónico y contraseña.
 * @param string $email Correo electrónico del usuario.
 * @param string $pass Contraseña del usuario.
 * @return array|bool Retorna un array con los datos del usuario si existe, false en caso contrario.
 */
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

/**
 * Función para crear un nuevo producto.
 * @param string $nombre Nombre del producto.
 * @param array $imagen Imagen del producto.
 * @param string $descripcion Descripción del producto.
 * @param string $fecha_lanzamiento Fecha de lanzamiento del producto.
 * @param int $genero_id ID del género del producto.
 * @param float $precio Precio del producto.
 * @param float $descuento Descuento del producto.
 * @param int $stock Stock del producto.
 * @param int $plataforma_id ID de la plataforma del producto.
 * @param int $creado_por ID del usuario que creó el producto.
 * @param int $actualizado_por ID del usuario que actualizó el producto.
 * @return void Redirige a la página de productos o muestra un error.
 */
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
        $productoId = $conn->insert_id;

        // Insertar en producto_stock
        $stockQuery = $conn->prepare("
            INSERT INTO producto_stock (producto_id, stock_disponible, stock_reservado)
            VALUES (?, ?, 0)
        ");
        $stockQuery->bind_param("ii", $productoId, $stock);
        $stockQuery->execute();
        $stockQuery->close();

        header("Location: ../views/admin/products.php?exito=Producto+creado+con+éxito.");
    } else {
        header("Location: ../views/admin/addOrModifyProduct.php?error=Error+al+crear+el+producto.");
    }

    $query->close();
    cerrar_conexion($conn);
}

/**
 * Función para modificar un producto existente.
 * @param int $id ID del producto a modificar.
 * @param string $nombre Nombre del producto.
 * @param array $imagen Imagen del producto.
 * @param string $descripcion Descripción del producto.
 * @param string $fecha_lanzamiento Fecha de lanzamiento del producto.
 * @param int $genero_id ID del género del producto.
 * @param float $precio Precio del producto.
 * @param float $descuento Descuento del producto.
 * @param int $stock Stock del producto.
 * @param int $plataforma_id ID de la plataforma del producto.
 * @return void Redirige a la página de productos o muestra un error.
 */
function modifyProduct($id, $nombre, $imagen, $descripcion, $fecha_lanzamiento, $genero_id, $precio, $descuento, $stock, $plataforma_id)
{
    $conn = conexion();
    $query = $conn->prepare("
        UPDATE producto 
        SET nombre = ?, imagen = ?, descripcion = ?, fecha_lanzamiento = ?, genero_id = ?, precio = ?, descuento = ?, stock = ?, plataforma_id = ?
        WHERE id = ?
    ");
    $query->bind_param("sssssidiii", $nombre, $imagen, $descripcion, $fecha_lanzamiento, $genero_id, $precio, $descuento, $stock, $plataforma_id, $id);
    $result = $query->execute();
    $query->close();

    if ($result) {
        // Actualizar producto_stock
        $stockQuery = $conn->prepare("
            UPDATE producto_stock
            SET stock_disponible = ?
            WHERE producto_id = ?
        ");
        $stockQuery->bind_param("ii", $stock, $id);
        $stockQuery->execute();
        $stockQuery->close();

        header("Location: ../views/admin/products.php?exito=Producto+modificado+con+éxito.");
    } else {
        header("Location: ../views/admin/addOrModifyProduct.php?error=Error+al+modificar+el+producto.");
    }

    cerrar_conexion($conn);
}

/**
 * Función para eliminar un producto de la base de datos.
 * @param int $id ID del producto a eliminar.
 * @return bool Retorna true si se eliminó correctamente, false en caso contrario.
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
 * Función para obtener todos los productos activos de la base de datos.
 * @return array Array con los productos activos.
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
    return $productos; // Se devuelve el array de productos
}

/**
 * Función para obtener todos los productos de la base de datos.
 * @return array Array con todos los productos.
 */
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
            $products[] = $producto;
        }
    }

    $query->close();
    cerrar_conexion($conn);
    return $products;
}

/**
 * Función para obtener un producto por su ID.
 * @param int $id ID del producto.
 * @return array|bool Array con los datos del producto o false si no existe.
 */
function getProductById($id)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT 
            p.*, 
            g.nombre AS genero_nombre, 
            pl.nombre AS plataforma_nombre,
            ps.stock_disponible
        FROM producto p
        INNER JOIN genero g ON p.genero_id = g.id
        INNER JOIN plataforma pl ON p.plataforma_id = pl.id
        LEFT JOIN producto_stock ps ON p.id = ps.producto_id
        WHERE p.id = ?
    ");
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

/* ------------- STOCK -------------  */

function getAvailableStock($productoId)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT p.stock - IFNULL(ps.stock_reservado, 0) AS stock_disponible
        FROM producto p
        LEFT JOIN producto_stock ps ON p.id = ps.producto_id
        WHERE p.id = ?
    ");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $query->close();
    cerrar_conexion($conn);

    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['stock_disponible'];
    }

    return null;
}

function reserveProductStock($productoId, $cantidad)
{
    $conn = conexion();
    $query = $conn->prepare("
        INSERT INTO producto_stock (producto_id, stock_reservado, stock_disponible)
        VALUES (?, ?, 0)
        ON DUPLICATE KEY UPDATE stock_reservado = stock_reservado + VALUES(stock_reservado)
    ");
    $query->bind_param("ii", $productoId, $cantidad);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

// funcion que libera el stock reservado de un producto si se elimina del carrito
function releaseProductStock($productoId, $cantidad)
{
    $conn = conexion();
    $query = $conn->prepare("
        UPDATE producto_stock
        SET stock_reservado = stock_reservado - ?
        WHERE producto_id = ?
    ");
    $query->bind_param("ii", $cantidad, $productoId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/* ------------- CARRITO -------------  */

// funcion para crear el carrito del usuario
function createCart($usuarioId)
{
    $conn = conexion();
    $query = $conn->prepare("INSERT INTO carrito (creado_por, activo) VALUES (?, 1)");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

function getActiveCartId($conn, $usuarioId)
{
    $query = $conn->prepare("SELECT id FROM carrito WHERE creado_por = ? AND activo = 1 LIMIT 1");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $result = $query->get_result();
    $query->close();

    return ($result->num_rows > 0) ? $result->fetch_assoc()['id'] : null;
}

function getDiscountedPrice($conn, $productoId)
{
    $query = $conn->prepare("SELECT precio, descuento FROM producto WHERE id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $query->close();

    if ($result->num_rows > 0) {
        $producto = $result->fetch_assoc();
        $precio = $producto['precio'];
        $descuento = $producto['descuento'];
        if ($descuento && $descuento > 0) {
            return $precio - ($precio * $descuento / 100);
        } else {
            return $precio;
        }
    }
    return null;
}

function getCartItem($conn, $carritoId, $productoId)
{
    $query = $conn->prepare("SELECT id, cantidad FROM carrito_item WHERE carrito_id = ? AND producto_id = ?");
    $query->bind_param("ii", $carritoId, $productoId);
    $query->execute();
    $result = $query->get_result();
    $query->close();
    return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
}

function updateCartItem($conn, $itemId, $nuevaCantidad, $precioUnitario)
{
    $precioTotal = $precioUnitario * $nuevaCantidad;
    $query = $conn->prepare("UPDATE carrito_item SET cantidad = ?, precio_total = ? WHERE id = ?");
    $query->bind_param("idi", $nuevaCantidad, $precioTotal, $itemId);
    $query->execute();
    $query->close();
}

function insertCartItem($conn, $carritoId, $productoId, $cantidad, $precioUnitario)
{
    $precioTotal = $precioUnitario * $cantidad;
    $query = $conn->prepare("INSERT INTO carrito_item (carrito_id, producto_id, cantidad, precio_total) VALUES (?, ?, ?, ?)");
    $query->bind_param("iiid", $carritoId, $productoId, $cantidad, $precioTotal);
    $query->execute();
    $query->close();
}

function getCartItems($carritoId)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT ci.id, p.id AS producto_id, p.nombre, p.imagen, ci.cantidad, ci.precio_total
        FROM carrito_item ci
        JOIN producto p ON ci.producto_id = p.id
        WHERE ci.carrito_id = ?
    ");
    $query->bind_param("i", $carritoId);
    $query->execute();
    $result = $query->get_result();
    $query->close();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    cerrar_conexion($conn);
    return $items;
}

function addProductToCart($usuarioId, $productoId, $cantidad)
{
    $conn = conexion();

    $carritoId = getActiveCartId($conn, $usuarioId);
    if (!$carritoId) {
        cerrar_conexion($conn);
        return ['exito' => false, 'mensaje' => 'No se encontró un carrito activo.'];
    }

    $precio = getDiscountedPrice($conn, $productoId);
    if ($precio === null) {
        cerrar_conexion($conn);
        return ['exito' => false, 'mensaje' => 'Producto no encontrado.'];
    }

    $stockDisponible = getAvailableStock($productoId);
    if ($stockDisponible === null || $stockDisponible < $cantidad) {
        cerrar_conexion($conn);
        return ['exito' => false, 'mensaje' => 'No hay suficiente stock disponible.'];
    }

    $item = getCartItem($conn, $carritoId, $productoId);
    if ($item) {
        $nuevaCantidad = $item['cantidad'] + $cantidad;

        // Verificar si hay stock suficiente para aumentar
        if ($stockDisponible < ($nuevaCantidad - $item['cantidad'])) {
            cerrar_conexion($conn);
            return ['exito' => false, 'mensaje' => 'Stock insuficiente para la cantidad solicitada.'];
        }

        updateCartItem($conn, $item['id'], $nuevaCantidad, $precio);
    } else {
        insertCartItem($conn, $carritoId, $productoId, $cantidad, $precio);
    }

    cerrar_conexion($conn);

    reserveProductStock($productoId, $cantidad);
    return ['exito' => true, 'mensaje' => 'Producto agregado al carrito.'];
}

function removeProductFromCart($usuarioId, $productoId)
{
    $conn = conexion();

    // Obtener el ID del carrito activo del usuario
    $carritoId = getActiveCartId($conn, $usuarioId);
    if (!$carritoId) {
        error_log("No se encontró un carrito activo para el usuario $usuarioId");
        cerrar_conexion($conn);
        return false;
    }

    // Verificar si el producto está en el carrito
    $item = getCartItem($conn, $carritoId, $productoId);
    if (!$item) {
        error_log("El producto $productoId no está en el carrito $carritoId");
        cerrar_conexion($conn);
        return false;
    }

    $cantidad = $item['cantidad'];
    $itemId = $item['id'];

    // Eliminar el ítem del carrito
    $query = $conn->prepare("DELETE FROM carrito_item WHERE id = ?");
    $query->bind_param("i", $itemId);
    $deleteSuccess = $query->execute();
    $query->close();

    if (!$deleteSuccess) {
        error_log("Error al ejecutar la consulta DELETE para el producto $productoId en el carrito $carritoId");
        cerrar_conexion($conn);
        return false;
    }

    // Liberar stock reservado
    releaseProductStock($productoId, $cantidad);

    cerrar_conexion($conn);
    return true;
}

function getCartSummary($usuarioId)
{
    $conn = conexion();
    $carritoId = getActiveCartId($conn, $usuarioId);

    $query = $conn->prepare("
        SELECT 
            SUM(p.precio * ci.cantidad) AS total_original,
            SUM((p.precio * p.descuento / 100) * ci.cantidad) AS total_descuento
        FROM carrito_item ci
        INNER JOIN producto p ON ci.producto_id = p.id
        WHERE ci.carrito_id = ?
    ");
    $query->bind_param("i", $carritoId);
    $query->execute();
    $query->bind_result($totalOriginal, $totalDescuento);
    $query->fetch();
    $query->close();

    cerrar_conexion($conn);

    $totalOriginal = $totalOriginal ?? 0;
    $totalDescuento = $totalDescuento ?? 0;
    $totalFinal = $totalOriginal - $totalDescuento;

    return [
        'total' => $totalOriginal,
        'descuento' => $totalDescuento,
        'total_final' => $totalFinal
    ];
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

function getFavoriteProducts($usuarioId)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT p.id, p.nombre, p.precio, p.imagen, g.nombre AS genero, pl.nombre AS plataforma
        FROM favorito_item fi
        JOIN favorito f ON fi.favorito_id = f.id
        JOIN producto p ON fi.producto_id = p.id
        JOIN genero g ON p.genero_id = g.id
        JOIN plataforma pl ON p.plataforma_id = pl.id
        WHERE f.creado_por = ? AND f.activo = 1
    ");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $result = $query->get_result();
    $productos = [];

    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }

    $query->close();
    cerrar_conexion($conn);
    return $productos;
}

/* ------------- DASHBOARD -------------  */

//funcion para obtener el total de productos para el dashboard del admin
function obtenerTotalProductos()
{
    $conn = conexion();
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
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM usuario WHERE rol = 'user'");
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    $query->close();
    cerrar_conexion($conn);
    return $row['total']; // Retorna el total de usuarios
}
