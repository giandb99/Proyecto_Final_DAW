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
function createProduct($nombre, $imagen, $descripcion, $fecha_lanzamiento, $generos, $precio, $descuento, $stock, $plataformas, $creado_por, $actualizado_por)
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

    // Insertar el producto
    $query = $conn->prepare("
        INSERT INTO producto 
        (nombre, imagen, descripcion, fecha_lanzamiento, precio, descuento, creado_por, actualizado_por) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $query->bind_param(
        "ssssidii",
        $nombre,
        $rutaRelativa,
        $descripcion,
        $fecha_lanzamiento,
        $precio,
        $descuento,
        $creado_por,
        $actualizado_por
    );

    $result = $query->execute();

    if ($result) {
        $productoId = $conn->insert_id;

        // Insertar géneros relacionados
        $generoQuery = $conn->prepare("
            INSERT INTO producto_genero (producto_id, genero_id) 
            VALUES (?, ?)
        ");
        foreach ($generos as $genero_id) {
            $generoQuery->bind_param("ii", $productoId, $genero_id);
            $generoQuery->execute();
        }
        $generoQuery->close();

        // Insertar plataformas relacionadas
        $plataformaQuery = $conn->prepare("
            INSERT INTO producto_plataforma (producto_id, plataforma_id) 
            VALUES (?, ?)
        ");

        $stockQuery = $conn->prepare("
            INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible, stock_reservado)
            VALUES (?, ?, ?, 0)
        ");

        foreach ($plataformas as $plataforma_id) {
            $plataformaQuery->bind_param("ii", $productoId, $plataforma_id);
            $plataformaQuery->execute();

            // Obtener el stock específico para esta plataforma
            $stockDisponible = isset($stock[$plataforma_id]) ? $stock[$plataforma_id] : 0;

            $stockQuery->bind_param("iii", $productoId, $plataforma_id, $stockDisponible);
            $stockQuery->execute();
        }

        $plataformaQuery->close();
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
function modifyProduct($id, $nombre, $imagen, $descripcion, $fecha_lanzamiento, $generos, $precio, $descuento, $stock, $plataformas, $actualizado_por)
{
    $conn = conexion();

    // Actualiza la información del producto
    $query = $conn->prepare("
        UPDATE producto 
        SET nombre = ?, imagen = ?, descripcion = ?, fecha_lanzamiento = ?, precio = ?, descuento = ?, actualizado_por = ?
        WHERE id = ?
    ");

    $query->bind_param(
        "ssssdddi",
        $nombre,
        $imagen,
        $descripcion,
        $fecha_lanzamiento,
        $precio,
        $descuento,
        $actualizado_por,
        $id
    );

    $result = $query->execute();

    if ($result) {
        // Comparar géneros actuales con los enviados
        $generosActuales = getSelectedGenreIds($id);
        sort($generosActuales);
        $generosRecibidos = $generos;
        sort($generosRecibidos);

        if ($generosActuales !== $generosRecibidos) {
            // Eliminar los existentes
            $conn->query("DELETE FROM producto_genero WHERE producto_id = $id");

            // Agregar los nuevos
            foreach ($generosRecibidos as $genero_id) {
                addProductGenre($id, $genero_id);
            }
        }

        // Comparar plataformas actuales con las enviadas
        $plataformasActuales = getSelectedPlatformIds($id);
        sort($plataformasActuales);
        $plataformasRecibidas = $plataformas;
        sort($plataformasRecibidas);

        if ($plataformasActuales !== $plataformasRecibidas) {
            $conn->query("DELETE FROM producto_plataforma WHERE producto_id = $id");
            $conn->query("DELETE FROM producto_stock WHERE producto_id = $id");

            foreach ($plataformasRecibidas as $plataforma_id) {
                $stock_plataforma = $stock[$plataforma_id] ?? 0;
                addProductPlatform($id, $plataforma_id, $stock_plataforma);
            }
        }

        header("Location: ../views/admin/products.php?exito=Producto+modificado+con+éxito.");
    } else {
        header("Location: ../views/admin/addOrModifyProduct.php?error=Error+al+modificar+el+producto.");
    }

    $query->close();
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

    // Eliminamos el producto
    $query = $conn->prepare("DELETE FROM producto WHERE id = ?");
    $query->bind_param("i", $id);
    $resultado = $query->execute();

    $query->close();
    cerrar_conexion($conn);

    return $resultado;
}

function addProductGenre($productId, $genreId)
{
    $conn = conexion();
    $query = $conn->prepare("INSERT INTO producto_genero (producto_id, genero_id) VALUES (?, ?)");
    $query->bind_param("ii", $productId, $genreId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

function addProductPlatform($productId, $platformId, $stock)
{
    $conn = conexion();

    $query = $conn->prepare("INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (?, ?)");
    $query->bind_param("ii", $productId, $platformId);
    $query->execute();
    $query->close();

    // Agregar stock por plataforma
    $query = $conn->prepare("INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES (?, ?, ?)");
    $query->bind_param("iii", $productId, $platformId, $stock);
    $query->execute();
    $query->close();

    cerrar_conexion($conn);
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
function getAllProducts()
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT 
            producto.id,
            producto.nombre,
            producto.imagen,
            producto.precio,
            producto.descuento,
            COALESCE(ps.stock_disponible, 0) AS stock_disponible,
            GROUP_CONCAT(DISTINCT genero.nombre) AS genero,
            GROUP_CONCAT(DISTINCT plataforma.nombre) AS plataforma
        FROM producto
        LEFT JOIN producto_genero pg ON producto.id = pg.producto_id
        LEFT JOIN genero ON pg.genero_id = genero.id
        LEFT JOIN producto_plataforma pp ON producto.id = pp.producto_id
        LEFT JOIN plataforma ON pp.plataforma_id = plataforma.id
        LEFT JOIN producto_stock ps ON producto.id = ps.producto_id
        WHERE producto.activo = 1
        GROUP BY producto.id
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
        INNER JOIN producto_genero pg ON p.id = pg.producto_id
        INNER JOIN genero g ON pg.genero_id = g.id
        INNER JOIN producto_plataforma pp ON p.id = pp.producto_id
        INNER JOIN plataforma pl ON pp.plataforma_id = pl.id
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

function getAllGenres()
{
    $conn = conexion();
    $result = $conn->query("SELECT id, nombre FROM genero");
    $generos = [];

    while ($row = $result->fetch_assoc()) {
        $generos[] = $row;
    }

    cerrar_conexion($conn);
    return $generos;
}

function getSelectedGenreIds($productoId)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT genero_id FROM producto_genero WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $generos = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $generos[] = $row['genero_id']; // Agrega cada género al array
        }
    }

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión

    return $generos; // Retorna el array de géneros
}

function getAllPlatforms()
{
    $conn = conexion();
    $result = $conn->query("SELECT id, nombre FROM plataforma");
    $plataformas = [];

    while ($row = $result->fetch_assoc()) {
        $plataformas[] = $row;
    }

    cerrar_conexion($conn);
    return $plataformas;
}

function getSelectedPlatformIds($productoId)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT plataforma_id FROM producto_plataforma WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $plataformas = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plataformas[] = $row['plataforma_id']; // Agrega cada plataforma al array
        }
    }

    $query->close();
    cerrar_conexion($conn); // Cierra la conexión

    return $plataformas; // Retorna el array de plataformas
}

function geProductStockByPlataform($productoId)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT plataforma_id, stock_disponible FROM producto_stock WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $stock = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stock[$row['plataforma_id']] = $row['stock_disponible'];
        }
    }

    $query->close();
    cerrar_conexion($conn);

    return $stock;
}

function getPlatformsByProduct($productoId)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT pp.plataforma_id, p.nombre AS plataforma_nombre
        FROM producto_plataforma pp
        JOIN plataforma p ON pp.plataforma_id = p.id
        WHERE pp.producto_id = ?
    ");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();

    $plataformas = [];
    while ($row = $result->fetch_assoc()) {
        $plataformas[] = $row;
    }

    $query->close();
    cerrar_conexion($conn);
    return $plataformas;
}

function getRelatedProducts($producto_id, $limit = 10)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT genero_id
        FROM producto_genero 
        WHERE producto_id = ?
    ");
    $query->bind_param("i", $producto_id);
    $query->execute();
    $result = $query->get_result();
    $generos = [];

    while ($row = $result->fetch_assoc()) {
        $generos[] = $row['genero_id'];
    }

    $query->close();

    if (empty($generos)) return [];

    // Buscamos otros productos que compartan alguno de esos géneros
    $placeholders = implode(',', array_fill(0, count($generos), '?'));
    $types = str_repeat('i', count($generos) + 1);
    $params = array_merge($generos, [$producto_id]);

    $query = $conn->prepare("
        SELECT DISTINCT p.id, p.nombre, p.imagen, p.precio, p.descuento
        FROM producto p
        JOIN producto_genero pg ON p.id = pg.producto_id
        WHERE pg.genero_id IN ($placeholders)
        AND p.id != ?
        AND p.activo = 1
        ORDER BY p.creado_en DESC
        LIMIT $limit
    ");
    $query->bind_param($types, ...$params);
    $query->execute();
    $result = $query->get_result();
    $productosRelacionados = [];

    while ($row = $result->fetch_assoc()) {
        $productosRelacionados[] = $row;
    }

    $query->close();
    cerrar_conexion($conn);

    return $productosRelacionados;
}

/**
 * Función para obtener el ID de la combinación producto-plataforma.
 * @param mysqli $conn Conexión a la base de datos.
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @return int|null ID de la combinación producto-plataforma o null si no existe.
 */
function getProductPlataformId($conn, $productoId, $plataformaId)
{
    $query = $conn->prepare("SELECT 1 FROM producto_stock WHERE producto_id = ? AND plataforma_id = ?");
    $query->bind_param("ii", $productoId, $plataformaId);
    $query->execute();
    $result = $query->get_result();
    $query->close();
    return ($result->num_rows > 0);
}

/**
 * Función para obtener el precio con descuento de un producto.
 * @param mysqli $conn Conexión a la base de datos.
 * @param int $productoId ID del producto.
 * @return float|null Precio con descuento o null si no existe.
 */
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

/* ------------- STOCK -------------  */

function getAvailableStock($conn, $productoId, $plataformaId)
{
    $query = $conn->prepare("
        SELECT stock_disponible - IFNULL(stock_reservado, 0) AS stock_disponible
        FROM producto_stock
        WHERE producto_id = ? AND plataforma_id = ?
    ");
    $query->bind_param("ii", $productoId, $plataformaId);
    $query->execute();
    $result = $query->get_result();
    $query->close();

    if ($result->num_rows > 0) {
        return (int)$result->fetch_assoc()['stock_disponible'];
    }

    return null;
}

function reserveProductStock($conn, $productoId, $plataformaId, $cantidad)
{
    $query = $conn->prepare("
        UPDATE producto_stock
        SET 
            stock_reservado = stock_reservado + ?,
            stock_disponible = stock_disponible - ?
        WHERE producto_id = ? AND plataforma_id = ? AND stock_disponible >= ?
    ");
    $query->bind_param("iiiii", $cantidad, $cantidad, $productoId, $plataformaId, $cantidad);
    $query->execute();
    $exito = $query->affected_rows > 0;
    $query->close();
    return $exito;
}

// funcion que libera el stock reservado de un producto si se elimina del carrito
function releaseProductStock($productoId, $plataformaId, $cantidad)
{
    $conn = conexion();
    $query = $conn->prepare("
        UPDATE producto_stock
        SET 
            stock_reservado = GREATEST(stock_reservado - ?, 0),
            stock_disponible = stock_disponible + ?
        WHERE producto_id = ? AND plataforma_id = ?
    ");
    $query->bind_param("iiii", $cantidad, $cantidad, $productoId, $plataformaId);
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
    $query = $conn->prepare("SELECT id FROM carrito WHERE creado_por = ? AND activo = 1");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $result = $query->get_result();
    $query->close();
    $carritoId = ($result->num_rows > 0) ? $result->fetch_assoc()['id'] : null;
    return $carritoId;
}

function getCartItem($conn, $carritoId, $productoId, $plataformaId)
{
    $query = $conn->prepare("
        SELECT id, cantidad 
        FROM carrito_item 
        WHERE carrito_id = ? AND producto_id = ? AND plataforma_id = ?
    ");
    $query->bind_param("iii", $carritoId, $productoId, $plataformaId);
    $query->execute();
    $result = $query->get_result();
    $query->close();
    return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
}

function insertCartItem($conn, $carritoId, $productoId, $plataformaId, $cantidad, $precioUnitario)
{
    $precioTotal = $precioUnitario * $cantidad;
    $query = $conn->prepare("
        INSERT INTO carrito_item (carrito_id, producto_id, plataforma_id, cantidad, precio_total)
        VALUES (?, ?, ?, ?, ?)
    ");
    $query->bind_param("iiiid", $carritoId, $productoId, $plataformaId, $cantidad, $precioTotal);
    $query->execute();
    $query->close();
}

function updateCartItem($conn, $itemId, $nuevaCantidad, $precioUnitario)
{
    $precioTotal = $precioUnitario * $nuevaCantidad;
    $query = $conn->prepare("UPDATE carrito_item SET cantidad = ?, precio_total = ? WHERE id = ?");
    $query->bind_param("idi", $nuevaCantidad, $precioTotal, $itemId);
    $query->execute();
    $query->close();
}

function getCartItems($conn, $carritoId)
{
    $query = $conn->prepare("
        SELECT 
            ci.id AS item_id,
            ci.producto_id,
            p.nombre AS producto_nombre,
            p.imagen,
            ci.plataforma_id,
            pl.nombre AS plataforma_nombre,
            p.precio,
            p.descuento,
            ci.cantidad,
            ROUND(
                IF(p.descuento > 0, p.precio - (p.precio * p.descuento / 100), p.precio), 
                2
            ) AS precio_descuento,
            ROUND(
                IF(p.descuento > 0, (p.precio - (p.precio * p.descuento / 100)) * ci.cantidad, p.precio * ci.cantidad), 
                2
            ) AS precio_total_descuento
        FROM carrito_item ci
        JOIN producto p ON ci.producto_id = p.id
        JOIN plataforma pl ON ci.plataforma_id = pl.id
        WHERE ci.carrito_id = ?
    ");
    $query->bind_param("i", $carritoId);
    $query->execute();
    $result = $query->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $query->close();
    return $items;
}

function addProductToCart($conn, $usuarioId, $productoId, $plataformaId, $cantidad, $precioUnitario)
{
    $carritoId = getActiveCartId($conn, $usuarioId);
    if (!$carritoId) {
        return ['exito' => false, 'mensaje' => 'No se encontró un carrito activo.'];
    }

    $combinacionValida = getProductPlataformId($conn, $productoId, $plataformaId);
    if (!$combinacionValida) {
        return ['exito' => false, 'mensaje' => 'Combinación producto-plataforma no válida.'];
    }

    $stockDisponible = getAvailableStock($conn, $productoId, $plataformaId);
    if ($stockDisponible === null || $cantidad > $stockDisponible) {
        return ['exito' => false, 'mensaje' => 'Stock insuficiente.'];
    }

    $item = getCartItem($conn, $carritoId, $productoId, $plataformaId);
    if ($item) {
        $nuevaCantidad = $item['cantidad'] + $cantidad;
        if ($nuevaCantidad > $stockDisponible) {
            return ['exito' => false, 'mensaje' => 'Stock insuficiente al actualizar.'];
        }
        updateCartItem($conn, $item['id'], $nuevaCantidad, $precioUnitario);
    } else {
        insertCartItem($conn, $carritoId, $productoId, $plataformaId, $cantidad, $precioUnitario);
    }

    // Reservar stock
    if (!reserveProductStock($conn, $productoId, $plataformaId, $cantidad)) {
        return ['exito' => false, 'mensaje' => 'No se pudo reservar stock.'];
    }

    return [
        'exito' => true,
        'mensaje' => 'Producto agregado al carrito.',
        'stock_restante' => $stockDisponible - $cantidad
    ];
}

function removeProductFromCart($conn, $carritoId, $productoPlataformaId)
{
    $query = $conn->prepare("DELETE FROM carrito_item WHERE carrito_id = ? AND plataforma_id = ?");
    $query->bind_param("ii", $carritoId, $productoPlataformaId);
    $query->execute();
    $query->close();
}

function getCartSummary($conn, $carritoId)
{
    $totalOriginal = 0;
    $totalDescuento = 0;

    $query = $conn->prepare("
        SELECT 
            SUM(p.precio * ci.cantidad) AS total_original,
            SUM(
                IF(p.descuento > 0, (p.precio * p.descuento / 100) * ci.cantidad, 0)
            ) AS total_descuento
        FROM carrito_item ci
        JOIN producto p ON ci.producto_id = p.id
        -- Opcional: validar si el producto está disponible en esa plataforma
        JOIN producto_plataforma pp ON ci.producto_id = pp.producto_id AND ci.plataforma_id = pp.plataforma_id
        WHERE ci.carrito_id = ?
    ");
    $query->bind_param("i", $carritoId);
    $query->execute();
    $query->bind_result($totalOriginal, $totalDescuento);
    $query->fetch();
    $query->close();

    $totalOriginal = $totalOriginal ?? 0;
    $totalDescuento = $totalDescuento ?? 0;
    $subtotal = $totalOriginal - $totalDescuento;

    return [
        'total_original' => round($totalOriginal, 2),
        'total_descuento' => round($totalDescuento, 2),
        'subtotal' => round($subtotal, 2)
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
    $favoritoId = getActiveFavListId($usuarioId);

    if (!$favoritoId) {
        cerrar_conexion($conn);
        return [];
    }

    $query = $conn->prepare("
        SELECT p.id, p.nombre, p.precio, p.imagen
        FROM favorito_item fi
        JOIN producto p ON fi.producto_id = p.id
        WHERE fi.favorito_id = ?
    ");
    $query->bind_param("i", $favoritoId);
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
