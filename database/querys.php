<?php

require_once 'connection.php';

/* ------------- USUARIOS -------------  */

/**
 * Función para crear un nuevo usuario.
 * 
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

    // Hashear la contraseña antes de almacenarla
    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $query = $conn->prepare("INSERT INTO usuario (nombre, username, email, pass) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $name, $username, $email, $hashedPassword);
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
        'imagen_perfil' => !empty($usuario['imagen_perfil']) ? $usuario['imagen_perfil'] : null
    ];
}

function getAllUserData()
{
    $conn = conexion();
    $query = $conn->prepare("SELECT * FROM usuario where rol = 'user'");
    $query->execute();
    $result = $query->get_result();
    $usuarios = [];

    if ($result->num_rows > 0) {
        while ($usuario = $result->fetch_assoc()) {
            $usuarios[] = $usuario;
        }
    }

    $query->close();
    cerrar_conexion($conn);
    return $usuarios;
}

function getAllUserDataPaginated($offset = 0, $limit = 20, $search = '')
{
    $conn = conexion();
    $search = "%$search%";
    $query = $conn->prepare("SELECT * FROM usuario WHERE rol = 'user' AND email LIKE ? ORDER BY id ASC LIMIT ?, ?");
    $query->bind_param("sii", $search, $offset, $limit);
    $query->execute();
    $result = $query->get_result();
    $usuarios = [];

    while ($usuario = $result->fetch_assoc()) {
        $usuarios[] = $usuario;
    }

    $query->close();
    cerrar_conexion($conn);
    return $usuarios;
}

function getTotalUsers($search = '')
{
    $conn = conexion();
    $search = "%$search%";
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM usuario WHERE rol = 'user' AND email LIKE ?");
    $query->bind_param("s", $search);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    cerrar_conexion($conn);
    return $result['total'];
}

/**
 * Función para obtener los datos del usuario por su correo electrónico y rol.
 * 
 * @param string $email Correo electrónico del usuario.
 * @param string $rol Rol del usuario (por defecto "user").
 * @return array|bool Retorna un array con los datos del usuario si existe, false en caso contrario.
 */
function getUserData($email, $rol = 'user')
{
    $conn = conexion();
    $query = $conn->prepare("SELECT * FROM usuario WHERE email = ? AND rol = ? AND activo = 1");
    $query->bind_param("ss", $email, $rol);
    $query->execute();
    $result = $query->get_result();
    $datos = $result->fetch_assoc();
    $query->close();
    cerrar_conexion($conn);
    return $datos ?: false;
}

function getUserDataById($conn, $userId)
{
    $query = $conn->prepare("SELECT * FROM usuario WHERE id = ?");
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();
    $query->close();
    return $user;
}

function updateUserProfile($conn, $userId, $nombre, $username, $email, $telefono, $direccion, $fecha_nac, $cp, $imagen_perfil)
{
    $query = $conn->prepare("
        UPDATE usuario
        SET nombre=?, username=?, email=?, telefono=?, direccion=?, fecha_nac=?, cp=?, imagen_perfil=?
        WHERE id=?
    ");
    $query->bind_param(
        "ssssssssi",
        $nombre,
        $username,
        $email,
        $telefono,
        $direccion,
        $fecha_nac,
        $cp,
        $imagen_perfil,
        $userId
    );

    $result = $query->execute();
    $query->close();
    return $result;
}

function getCurrentPassword($conn, $userId)
{
    $hashedPassword = null;
    $query = $conn->prepare("SELECT pass FROM usuario WHERE id = ?");
    $query->bind_param("i", $userId);
    $query->execute();
    $query->bind_result($hashedPassword);
    $query->fetch();
    $query->close();
    return $hashedPassword;
}

function updatePassword($conn, $userId, $newHashedPassword)
{
    $query = $conn->prepare("UPDATE usuario SET pass = ? WHERE id = ?");
    $query->bind_param("si", $newHashedPassword, $userId);
    $success = $query->execute();
    $query->close();
    return $success;
}

/**
 * Función para activar un usuario en la base de datos.
 * @param int $usuarioId - ID del usuario a activar.
 * @return bool - Retorna true si se activó correctamente, false en caso contrario.
 */
function activateUser($usuarioId)
{
    $conn = conexion();
    $query = $conn->prepare("UPDATE usuario SET activo = 1 WHERE id = ? AND rol = 'user'");
    $query->bind_param("i", $usuarioId);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);
    return $resultado;
}

/**
 * Función para desactivar un usuario en la base de datos.
 * @param int $usuarioId - ID del usuario a desactivar.
 * @return bool - Retorna true si se desactivó correctamente, false en caso contrario.
 */
function deactivateUser($usuarioId)
{
    $conn = conexion();
    $query = $conn->prepare("UPDATE usuario SET activo = 0 WHERE id = ? AND rol = 'user'");
    $query->bind_param("i", $usuarioId);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);
    return $resultado;
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
 * 
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
    $hayImagen = $imagen && isset($imagen['tmp_name']) && $imagen['error'] === UPLOAD_ERR_OK;
    $rutaRelativa = 'default.jpg';

    if ($hayImagen) {
        $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
        $nombreImagen = 'producto_' . time() . '.' . $extension;
        $rutaRelativa = 'images/products/' . $nombreImagen;
        $rutaAbsoluta = '../' . $rutaRelativa;

        if (!file_exists('../images/products/')) {
            mkdir('../images/products/', 0777, true);
        }

        if (!move_uploaded_file($imagen['tmp_name'], $rutaAbsoluta)) {
            $rutaRelativa = 'default.jpg';
        }
    }

    $conn = conexion();

    // Insertar el producto (si $rutaRelativa es null, MySQL usará el valor por defecto)
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

        $query->close();
        cerrar_conexion($conn);
        return $productoId;
    } else {
        $query->close();
        cerrar_conexion($conn);
        return false;
    }
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
 * @param int $actualizado_por ID del usuario que actualizó el producto.
 * @return void Redirige a la página de productos o muestra un error.
 */
function modifyProduct($id, $nombre, $imagen, $descripcion, $fecha_lanzamiento, $generos, $precio, $descuento, $stock, $plataformas, $actualizado_por)
{
    $conn = conexion();

    // Actualiza la información principal del producto
    $query = $conn->prepare("
        UPDATE producto 
        SET nombre = ?, imagen = ?, 
        descripcion = ?, fecha_lanzamiento = ?, 
        precio = ?, descuento = ?, actualizado_por = ?
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
    $query->close();

    if ($result) {
        $generosActuales = getSelectedGenreIds($id);
        sort($generosActuales);
        sort($generos);

        if ($generosActuales !== $generos) {
            $conn->query("DELETE FROM producto_genero WHERE producto_id = $id");
            foreach ($generos as $genero_id) {
                addProductGenre($id, $genero_id);
            }
        }

        // Comparar plataformas actuales con las enviadas
        $plataformasActuales = getSelectedPlatformIds($id);
        $plataformasAEliminar = array_diff($plataformasActuales, $plataformas);
        $plataformasANuevas = array_diff($plataformas, $plataformasActuales);
        $plataformasExistentes = array_intersect($plataformas, $plataformasActuales);

        // Se recorre la lista de plataformas a eliminar
        foreach ($plataformasAEliminar as $plataforma_id) {
            deleteProductPlatform($id, $plataforma_id);

            // Eliminar el stock del producto para la plataforma
            deleteProductStock($id, $plataforma_id);

            // Eliminar producto del carrito 
            deleteProductFromCart($id, $plataforma_id);
        }

        // Agregar plataformas nuevas
        foreach ($plataformasANuevas as $plataforma_id) {
            $nuevoStock = $stock[$plataforma_id] ?? 0;
            addProductPlatform($id, $plataforma_id, $nuevoStock);
        }

        // Actualizar stock disponible para las plataformas existentes (sin tocar el reservado)
        foreach ($plataformasExistentes as $plataforma_id) {
            $nuevoStock = $stock[$plataforma_id] ?? 0;
            updateProductStock($nuevoStock, $id, $plataforma_id);
        }

        cerrar_conexion($conn);
        return true;
    } else {
        cerrar_conexion($conn);
        return false;
    }
}

function deleteProductPlatform($productoId, $plataformaId)
{
    $conn = conexion();
    $query = $conn->prepare("DELETE FROM producto_plataforma WHERE producto_id = ? AND plataforma_id = ?");
    $query->bind_param("ii", $productoId, $plataformaId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

function deleteProductStock($productoId, $plataformaId)
{
    $conn = conexion();
    $query = $conn->prepare("DELETE FROM producto_stock WHERE producto_id = ? AND plataforma_id = ?");
    $query->bind_param("ii", $productoId, $plataformaId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

function deleteProductFromCart($productoId, $plataformaId)
{
    $conn = conexion();
    $query = $conn->prepare("DELETE FROM carrito_item WHERE producto_id = ? AND plataforma_id = ?");
    $query->bind_param("ii", $productoId, $plataformaId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Función para eliminar un producto de la base de datos.
 * @param int $id ID del producto a eliminar.
 * @return bool Retorna true si se eliminó correctamente, false en caso contrario.
 */
function deactivateProduct($id)
{
    $conn = conexion();
    $query = $conn->prepare("UPDATE producto SET activo = 0 WHERE id = ?");
    $query->bind_param("i", $id);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);

    deleteProductFromCartAllPlatforms($id);
    deleteProductFromFavorites($id);
    return $resultado;
}

function activateProduct($id)
{
    $conn = conexion();
    $query = $conn->prepare("UPDATE producto SET activo = 1 WHERE id = ?");
    $query->bind_param("i", $id);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);
    return $resultado;
}

function updateProductStock($stock, $productoId, $plataformaId)
{
    $conn = conexion();
    $query = $conn->prepare("UPDATE producto_stock SET stock_disponible = ? WHERE producto_id = ? AND plataforma_id = ?");
    $query->bind_param("iii", $stock, $productoId, $plataformaId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
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
 * Función para obtener productos con soporte de paginación.
 * @param int $offset Número de productos a omitir (inicio).
 * @param int $limit Número máximo de productos a devolver.
 * @return array Array con los productos.
 */
function getAllProducts($offset = 0, $limit = 20, $search = '')
{
    $conn = conexion();
    $search = "%$search%";
    $query = $conn->prepare("
        SELECT 
            producto.id,
            producto.nombre,
            producto.precio,
            producto.descuento,
            producto.activo,
            GROUP_CONCAT(DISTINCT genero.nombre) AS genero,
            GROUP_CONCAT(DISTINCT plataforma.nombre) AS plataforma
        FROM producto
        LEFT JOIN producto_genero pg ON producto.id = pg.producto_id
        LEFT JOIN genero ON pg.genero_id = genero.id
        LEFT JOIN producto_plataforma pp ON producto.id = pp.producto_id
        LEFT JOIN plataforma ON pp.plataforma_id = plataforma.id
        LEFT JOIN producto_stock ps ON producto.id = ps.producto_id
        WHERE producto.nombre LIKE ?
        GROUP BY producto.id
        ORDER BY producto.id ASC
        LIMIT ?, ?
    ");

    $query->bind_param("sii", $search, $offset, $limit);
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
 * Función para obtener el total de productos activos.
 * @return int Total de productos activos.
 */
function getTotalProducts($search = '')
{
    $conn = conexion();
    $search = "%$search%";
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM producto WHERE activo = 1 AND nombre LIKE ?");
    $query->bind_param("s", $search);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    cerrar_conexion($conn);
    return $result['total'];
}

/**
 * Función para obtener un producto por su ID.
 * @param int $id ID del producto.
 * @return array|bool Array con los datos del producto o false si no existe.
 */
function getProductById($conn, $productoId)
{
    $query = $conn->prepare("
        SELECT 
            p.id, p.nombre, p.descripcion, p.imagen, 
            p.precio, p.descuento, p.fecha_lanzamiento
        FROM producto p
        WHERE p.id = ?
    ");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $producto = $result->fetch_assoc();
    $query->close();
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

function getGenresByProduct($conn, $productoId)
{
    $query = $conn->prepare("
        SELECT g.id, g.nombre
        FROM producto_genero pg
        JOIN genero g ON pg.genero_id = g.id
        WHERE pg.producto_id = ?
    ");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $generos = $result->fetch_all(MYSQLI_ASSOC);
    $query->close();
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

function getPlatformsByProduct($conn, $productoId)
{
    $query = $conn->prepare("
        SELECT 
            pl.id AS plataforma_id,
            pl.nombre AS plataforma_nombre,
            ps.stock_disponible
        FROM producto_plataforma pp
        JOIN plataforma pl ON pp.plataforma_id = pl.id
        LEFT JOIN producto_stock ps 
            ON ps.producto_id = pp.producto_id 
            AND ps.plataforma_id = pp.plataforma_id
        WHERE pp.producto_id = ?
    ");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $plataformas = $result->fetch_all(MYSQLI_ASSOC);
    $query->close();
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
        ORDER BY p.id DESC
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

function getAvailableStock($conn, $productoId, $plataformaId)
{
    $query = $conn->prepare("
        SELECT stock_disponible
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
    if ($cantidad <= 0) {
        return false;
    }

    $query = $conn->prepare("
        UPDATE producto_stock
        SET 
            stock_reservado = stock_reservado + ?,
            stock_disponible = stock_disponible - ?
        WHERE producto_id = ? AND plataforma_id = ? AND stock_disponible >= ?
    ");

    if (!$query) {
        return false;
    }

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

function consumeReservedStock($conn, $productoId, $plataformaId, $cantidad)
{
    if ($cantidad <= 0) {
        return false;
    }

    $query = $conn->prepare("
        UPDATE producto_stock
        SET stock_reservado = GREATEST(stock_reservado - ?, 0)
        WHERE producto_id = ? AND plataforma_id = ?
    ");

    if (!$query) {
        return false;
    }

    $query->bind_param("iii", $cantidad, $productoId, $plataformaId);
    $query->execute();
    $exito = $query->affected_rows > 0;
    $query->close();

    return $exito;
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

function getCartItems($conn, $carritoId)
{
    $query = $conn->prepare("
        SELECT 
            ci.id AS id,
            ci.producto_id,
            p.nombre AS nombre,
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

/**
 * Función para obtener los datos de un carrito_item por su ID.
 * 
 * @param mysqli $conn Conexión a la base de datos.
 * @param int $carritoItemId ID del carrito_item.
 * @return array|null Retorna un array con los datos del carrito_item o null si no existe.
 */
function getCartItemById($conn, $carritoItemId)
{
    $query = $conn->prepare("
        SELECT producto_id, plataforma_id, cantidad 
        FROM carrito_item 
        WHERE id = ?
    ");
    $query->bind_param("i", $carritoItemId);
    $query->execute();
    $result = $query->get_result();
    $item = $result->fetch_assoc();
    $query->close();

    return $item ?: null;
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

function removeProductFromCart($conn, $carritoItemId, $userId)
{
    $carritoId = getActiveCartId($conn, $userId);
    if (!$carritoId) {
        return false;
    }

    $item = getCartItemById($conn, $carritoItemId, $carritoId);
    if (!$item) {
        return false;
    }

    $productoId = $item['producto_id'];
    $plataformaId = $item['plataforma_id'];
    $cantidad = $item['cantidad'];

    // Consulta para eliminar el producto del carrito
    $query = $conn->prepare("DELETE FROM carrito_item WHERE id = ? AND carrito_id = ?");
    $query->bind_param("ii", $carritoItemId, $carritoId);
    $query->execute();
    $success = $query->affected_rows > 0;
    $query->close();

    if ($success) {
        releaseProductStock($productoId, $plataformaId, $cantidad);
    }

    return $success;
}

function deleteProductFromCartAllPlatforms($productoId)
{
    $conn = conexion();
    $query = $conn->prepare("DELETE FROM carrito_item WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Función para vaciar el carrito y devolver el stock reservado al stock disponible.
 * 
 * @param mysqli $conn Conexión a la base de datos.
 * @param int $userId ID del usuario.
 * @return bool Retorna true si se vació correctamente, false en caso contrario.
 */
function emptyCart($conn, $userId)
{
    // Obtener el ID del carrito activo
    $carritoId = getActiveCartId($conn, $userId);
    if (!$carritoId) {
        return false; // No hay carrito activo
    }

    // Obtener los productos del carrito
    $items = getCartItems($conn, $carritoId);

    // Liberar el stock reservado para cada producto
    foreach ($items as $item) {
        $productoId = $item['producto_id'];
        $plataformaId = $item['plataforma_id'];
        $cantidad = $item['cantidad'];

        releaseProductStock($productoId, $plataformaId, $cantidad);
    }

    // Vaciar el carrito
    $query = $conn->prepare("DELETE FROM carrito_item WHERE carrito_id = ?");
    $query->bind_param("i", $carritoId);
    $query->execute();
    $success = $query->affected_rows > 0;
    $query->close();

    return $success;
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
        'total' => round($totalOriginal, 2),
        'descuento' => round($totalDescuento, 2),
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
 * 
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
 * 
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
 * 
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

function deleteProductFromFavorites($productoId)
{
    $conn = conexion();
    $query = $conn->prepare("DELETE FROM favorito_item WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
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

/* ------------- CHECKOUT / PEDIDOS -------------  */

function createOrder($usuarioId)
{
    $conn = conexion();

    try {
        $conn->begin_transaction();

        $carritoId = getActiveCartId($conn, $usuarioId);
        if (!$carritoId) {
            throw new Exception("No se encontró un carrito activo.");
        }

        $items = getCartItems($conn, $carritoId);
        if (empty($items)) {
            throw new Exception("El carrito está vacío.");
        }

        $precioTotal = 0;
        $descuentoTotal = 0;
        foreach ($items as $item) {
            $precioTotal += $item['precio_total_descuento'];
            $descuentoTotal += ($item['precio'] - $item['precio_descuento']) * $item['cantidad'];
        }

        $sqlPedido = $conn->prepare("
            INSERT INTO pedido (usuario_id, precio_total, descuento, creado_por) 
            VALUES (?, ?, ?, ?)
        ");
        $sqlPedido->bind_param("iddi", $usuarioId, $precioTotal, $descuentoTotal, $usuarioId);

        if (!$sqlPedido->execute()) {
            throw new Exception("Error al insertar el pedido: " . $sqlPedido->error);
        }

        $pedidoId = $conn->insert_id;

        $pedidoItem = $conn->prepare("
            INSERT INTO pedido_item (pedido_id, producto_id, plataforma_id, cantidad, precio_total) 
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $pedidoItem->bind_param(
                "iiiid",
                $pedidoId,
                $item['producto_id'],
                $item['plataforma_id'],
                $item['cantidad'],
                $item['precio_total_descuento']
            );

            consumeReservedStock($conn, $item['producto_id'], $item['plataforma_id'], $item['cantidad']);

            if (!$pedidoItem->execute()) {
                throw new Exception("Error al insertar ítem del pedido: " . $pedidoItem->error);
            }
        }

        $vaciarCarrito = $conn->prepare("DELETE FROM carrito_item WHERE carrito_id = ?");
        $vaciarCarrito->bind_param("i", $carritoId);

        if (!$vaciarCarrito->execute()) {
            throw new Exception("Error al vaciar el carrito: " . $vaciarCarrito->error);
        }

        $conn->commit();
        return $pedidoId;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en createOrder: " . $e->getMessage());
        return false;
    } finally {
        cerrar_conexion($conn);
    }
}

function addBilling($conn, $usuarioId, $pedidoId, $nombre, $email, $direccion, $pais, $numero_tarjeta = null, $vencimiento_tarjeta = null)
{
    try {
        $ultimos4 = null;
        if (!empty($numero_tarjeta)) {
            $ultimos4 = substr(preg_replace('/\D/', '', $numero_tarjeta), -4);
        }

        $query = $conn->prepare("
            INSERT INTO facturacion (
                usuario_id,
                pedido_id,
                nombre_completo,
                email,
                direccion,
                pais,
                numero_tarjeta,
                vencimiento_tarjeta
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $query->bind_param(
            "iissssss",
            $usuarioId,
            $pedidoId,
            $nombre,
            $email,
            $direccion,
            $pais,
            $ultimos4,
            $vencimiento_tarjeta
        );

        if ($query->execute()) {
            return ['success' => true, 'message' => 'Facturación registrada correctamente.'];
        } else {
            return ['success' => false, 'message' => 'Error al insertar la facturación: ' . $query->error];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Excepción: ' . $e->getMessage()];
    }
}

function markOrderShipped($pedidoId)
{
    $conn = conexion();
    $query = $conn->prepare("UPDATE pedido SET estado = 'entregado' WHERE id = ?");
    $query->bind_param("i", $pedidoId);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);
    return $resultado;
}

function markOrderCancelled($pedidoId)
{
    $conn = conexion();
    $query = $conn->prepare("UPDATE pedido SET estado = 'cancelado' WHERE id = ?");
    $query->bind_param("i", $pedidoId);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);
    return $resultado;
}

function getOrdersByUserId($usuarioId)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT 
            p.id AS pedido_id,
            p.precio_total,
            p.estado,
            p.creado_en,
            pi.producto_id,
            pr.nombre AS producto_nombre,
            pr.precio AS producto_precio,
            pr.imagen AS producto_imagen,
            pl.nombre AS plataforma_nombre,
            pi.cantidad,
            pi.precio_total AS precio_total_producto
        FROM pedido p
        INNER JOIN pedido_item pi ON p.id = pi.pedido_id
        INNER JOIN producto pr ON pi.producto_id = pr.id
        INNER JOIN plataforma pl ON pi.plataforma_id = pl.id
        WHERE p.usuario_id = ?
        ORDER BY p.creado_en DESC
    ");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $result = $query->get_result();

    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        if (!isset($pedidos[$row['pedido_id']])) {
            $pedidos[$row['pedido_id']] = [
                'pedido_id' => $row['pedido_id'],
                'precio_total' => $row['precio_total'],
                'estado' => $row['estado'],
                'creado_en' => $row['creado_en'],
                'productos' => []
            ];
        }
        $pedidos[$row['pedido_id']]['productos'][] = [
            'producto_nombre' => $row['producto_nombre'],
            'producto_imagen' => $row['producto_imagen'],
            'plataforma_nombre' => $row['plataforma_nombre'],
            'cantidad' => $row['cantidad'],
            'precio_total_producto' => $row['precio_total_producto']
        ];
    }

    $query->close();
    cerrar_conexion($conn);
    return array_values($pedidos);
}

function getOrderFullDetails($pedidoId)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT 
            p.id AS pedido_id,
            p.precio_total,
            p.descuento,
            p.estado,
            p.creado_en,
            u.nombre AS usuario_nombre,
            u.email AS facturacion_correo,
            f.nombre_completo AS facturacion_nombre,
            f.direccion AS facturacion_direccion,
            f.pais AS facturacion_pais,
            f.numero_tarjeta,
            f.vencimiento_tarjeta,
            pi.producto_id,
            pr.nombre AS producto_nombre,
            pl.nombre AS plataforma_nombre,
            pi.cantidad,
            pi.precio_total AS producto_precio_total
        FROM pedido p
        JOIN usuario u ON p.usuario_id = u.id
        LEFT JOIN facturacion f ON p.id = f.pedido_id
        JOIN pedido_item pi ON p.id = pi.pedido_id
        JOIN producto pr ON pi.producto_id = pr.id
        JOIN plataforma pl ON pi.plataforma_id = pl.id
        WHERE p.id = ?
    ");
    $query->bind_param("i", $pedidoId);
    $query->execute();
    $result = $query->get_result();

    $pedido = null;
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        if (!$pedido) {
            // Solo una vez, los datos generales del pedido y facturación
            $pedido = [
                'pedido_id' => $row['pedido_id'],
                'precio_total' => $row['precio_total'],
                'descuento' => $row['descuento'],
                'estado' => $row['estado'],
                'creado_en' => $row['creado_en'],
                'usuario_nombre' => $row['usuario_nombre'],
                'facturacion_correo' => $row['facturacion_correo'],
                'facturacion_nombre' => $row['facturacion_nombre'],
                'facturacion_direccion' => $row['facturacion_direccion'],
                'facturacion_pais' => $row['facturacion_pais'],
                'numero_tarjeta' => $row['numero_tarjeta'],
                'vencimiento_tarjeta' => $row['vencimiento_tarjeta'],
            ];
        }
        // Agregar cada producto
        $productos[] = [
            'producto_id' => $row['producto_id'],
            'producto_nombre' => $row['producto_nombre'],
            'plataforma_nombre' => $row['plataforma_nombre'],
            'cantidad' => $row['cantidad'],
            'precio_total' => $row['producto_precio_total'],
        ];
    }

    $query->close();
    cerrar_conexion($conn);

    return [
        'pedido' => $pedido,
        'productos' => $productos
    ];
}

function getAllOrdersPaginated($offset = 0, $limit = 20)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT 
            p.id AS pedido_id,
            p.precio_total,
            p.estado,
            p.creado_en,
            u.nombre AS usuario_nombre
        FROM pedido p
        JOIN usuario u ON p.usuario_id = u.id
        ORDER BY p.creado_en ASC
        LIMIT ?, ?
    ");
    $query->bind_param("ii", $offset, $limit);
    $query->execute();
    $result = $query->get_result();

    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }

    $query->close();
    cerrar_conexion($conn);
    return $pedidos;
}

function getTotalOrders()
{
    $conn = conexion();
    $result = $conn->query("SELECT COUNT(*) AS total FROM pedido");
    $row = $result->fetch_assoc();
    cerrar_conexion($conn);
    return $row['total'];
}

function getOrderById($pedidoId)
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT 
            p.id AS pedido_id,
            p.precio_total,
            p.estado,
            p.creado_en,
            u.nombre AS usuario_nombre
        FROM pedido p
        JOIN usuario u ON p.usuario_id = u.id
        WHERE p.id = ?
        ORDER BY p.creado_en DESC
    ");
    $query->bind_param("i", $pedidoId);
    $query->execute();
    $result = $query->get_result();

    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }

    $query->close();
    cerrar_conexion($conn);
    return $pedidos;
}

/* ------------- DASHBOARD -------------  */

function obtenerTotalProductos()
{
    $conn = conexion();
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM producto");
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    $query->close();
    cerrar_conexion($conn);
    return $row['total'];
}

function obtenerTotalProductosActivos()
{
    $conn = conexion();
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM producto WHERE activo = 1");
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    $query->close();
    cerrar_conexion($conn);
    return $row['total'];
}

function obtenerTotalUsuarios()
{
    $conn = conexion();
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM usuario WHERE rol = 'user'");
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    $query->close();
    cerrar_conexion($conn);
    return $row['total'];
}
