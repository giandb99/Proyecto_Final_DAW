<?php

require_once 'connection.php';

/* ------------- USUARIOS -------------  */

/**
 * Esta función crea un nuevo usuario en la base de datos.
 * 
 * @param string $name Nombre completo del usuario.
 * @param string $username Nombre de usuario único.
 * @param string $email Correo electrónico único.
 * @param string $pass Contraseña en texto plano.
 * @return void
 */
function createUser($name, $username, $email, $pass)
{
    $conn = conexion();

    // Se verifica si el nombre de usuario o el correo electrónico ya existen
    $errores = verifyUser($conn, $username, $email);
    if (!empty($errores)) {
        // Si hay errores, redirijo al registro mostrando los mensajes y datos ingresados
        $query = http_build_query([
            'errores' => implode(', ', $errores),
            'username' => $username,
            'email' => $email
        ]);
        header("Location: ../views/user/register.php?$query");
        cerrar_conexion($conn);
        return false;
    }

    // Se hashea la contraseña antes de almacenarla
    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

    // Se inserta el nuevo usuario en la base de datos
    $query = $conn->prepare("INSERT INTO usuario (nombre, username, email, pass) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $name, $username, $email, $hashedPassword);
    $result = $query->execute();

    if ($result) {
        // Si se creó el usuario, creo su lista de favoritos y carrito
        $usuarioId = $conn->insert_id;
        createFavList($usuarioId);
        createCart($usuarioId);
        // Redirijo al login con mensaje de éxito
        header("Location: ../views/user/login.php?exito=Usuario+registrado+con+éxito.");
    } else {
        // Si falla, redirijo al registro mostrando el error
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
 * Esta funcion verifica si el nombre de usuario o el correo electrónico ya existen en la base de datos.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param string $username Nombre de usuario a verificar.
 * @param string $email Correo electrónico a verificar.
 * @return array Devuelve un array con los mensajes de error encontrados.
 */
function verifyUser($conn, $username, $email)
{
    // Se prepara la consulta para buscar coincidencias de username o email
    $query = $conn->prepare("SELECT username, email FROM usuario WHERE username = ? OR email = ?");
    $query->bind_param("ss", $username, $email);
    $query->execute();
    $result = $query->get_result();
    $errores = [];

    // Se recorren los resultados y se agregan los errores correspondientes
    while ($usuario = $result->fetch_assoc()) {
        if ($usuario['username'] === $username) {
            $errores[] = 'El nombre de usuario ya está registrado.';
        }
        if ($usuario['email'] === $email) {
            $errores[] = 'El correo electrónico ya está registrado.';
        }
    }

    // Se cierra la consulta y se retorna el array de errores
    $query->close();
    return $errores;
}

/**
 * Esta función inicia sesión para el usuario y actualiza su último acceso.
 *
 * @param array $usuario Datos del usuario autenticado.
 * @return void
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

    // Se guardan los datos del usuario en la sesión
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

/**
 * Esta función obtiene todos los usuarios con rol 'user' de la base de datos.
 *
 * @return array Devuelvo un array con los datos de todos los usuarios encontrados.
 */
function getAllUserData()
{
    $conn = conexion();
    // Preparo la consulta para seleccionar todos los usuarios cuyo rol sea 'user'
    $query = $conn->prepare("SELECT * FROM usuario where rol = 'user'");
    $query->execute();
    $result = $query->get_result();
    $usuarios = [];

    // Si hay resultados, recorro cada usuario y lo agrego al array
    if ($result->num_rows > 0) {
        while ($usuario = $result->fetch_assoc()) {
            $usuarios[] = $usuario;
        }
    }

    // Cierro la consulta y la conexión antes de devolver los datos
    $query->close();
    cerrar_conexion($conn);
    return $usuarios;
}

/**
 * Esta función obtiene los datos de un usuario activo según su email y rol.
 *
 * @param string $email Correo electrónico del usuario.
 * @param string $rol Rol del usuario (por defecto 'user').
 * @return array|false Devuelvo los datos del usuario o false si no existe.
 */
function getUserData($email, $rol = 'user')
{
    $conn = conexion();
    // Se prepara la consulta para buscar el usuario activo por email y rol
    $query = $conn->prepare("SELECT * FROM usuario WHERE email = ? AND rol = ? AND activo = 1");
    $query->bind_param("ss", $email, $rol);
    $query->execute();
    $result = $query->get_result();
    $datos = $result->fetch_assoc();
    $query->close();
    cerrar_conexion($conn);
    // Si no se encuentra, devuelvo false
    return $datos ?: false;
}

/**
 * Esta función obtiene los datos de un usuario según su ID.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $userId ID del usuario a buscar.
 * @return array|null Devuelvo los datos del usuario o null si no existe.
 */
function getUserDataById($conn, $userId)
{
    // Preparo la consulta para buscar el usuario por su ID
    $query = $conn->prepare("SELECT * FROM usuario WHERE id = ?");
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();
    // Cierro la consulta antes de devolver los datos
    $query->close();
    return $user;
}

/**
 * Esta función obtiene los usuarios con rol 'user' de forma paginada y permite buscar por email.
 *
 * @param int $offset Número de registros a saltar (para la paginación).
 * @param int $limit Cantidad máxima de registros a devolver.
 * @param string $search Texto de búsqueda para filtrar por email.
 * @return array Devuelvo un array con los usuarios encontrados según los parámetros.
 */
function getAllUserDataPaginated($offset = 0, $limit = 20, $search = '')
{
    $conn = conexion();
    // Se arma el patrón de búsqueda para el email
    $search = "%$search%";
    // Se prepara la consulta para obtener usuarios con rol 'user' y filtrar por email, ordenados y paginados
    $query = $conn->prepare("SELECT * FROM usuario WHERE rol = 'user' AND email LIKE ? ORDER BY id ASC LIMIT ?, ?");
    $query->bind_param("sii", $search, $offset, $limit);
    $query->execute();
    $result = $query->get_result();
    $usuarios = [];

    // Se recorren los resultados y se agregan al array
    while ($usuario = $result->fetch_assoc()) {
        $usuarios[] = $usuario;
    }

    // Se cierra la consulta y la conexión antes de devolver los datos
    $query->close();
    cerrar_conexion($conn);
    return $usuarios;
}

/**
 * Esta función obtiene el total de usuarios con rol 'user' que coinciden con el filtro de email.
 *
 * @param string $search Texto de búsqueda para filtrar por email.
 * @return int Devuelvo la cantidad total de usuarios encontrados.
 */
function getTotalUsers($search = '')
{
    $conn = conexion();
    // Se arma el patrón de búsqueda para el email
    $search = "%$search%";
    // Se prepara la consulta para contar los usuarios con rol 'user' y filtro de email
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM usuario WHERE rol = 'user' AND email LIKE ?");
    $query->bind_param("s", $search);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    cerrar_conexion($conn);
    return $result['total'];
}

/**
 * Esta función actualiza el perfil de un usuario con los datos proporcionados.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $userId ID del usuario a actualizar.
 * @param string $nombre Nombre completo.
 * @param string $username Nombre de usuario.
 * @param string $email Correo electrónico.
 * @param string $telefono Teléfono.
 * @param string $direccion Dirección.
 * @param string $fecha_nac Fecha de nacimiento.
 * @param string $cp Código postal.
 * @param string $imagen_perfil Ruta o nombre de la imagen de perfil.
 * @return bool Devuelvo true si la actualización fue exitosa, false si no.
 */
function updateUserProfile($conn, $userId, $nombre, $username, $email, $telefono, $direccion, $fecha_nac, $cp, $imagen_perfil)
{
    // Preparo la consulta para actualizar los datos del usuario
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

    // Ejecuto la consulta y retorno el resultado
    $result = $query->execute();
    $query->close();
    return $result;
}

/**
 * Esta función obtiene la contraseña actual (hasheada) de un usuario según su ID.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $userId ID del usuario.
 * @return string Devuelvo la contraseña hasheada.
 */
function getCurrentPassword($conn, $userId)
{
    $hashedPassword = '';
    // Preparo la consulta para obtener la contraseña del usuario
    $query = $conn->prepare("SELECT pass FROM usuario WHERE id = ?");
    $query->bind_param("i", $userId);
    $query->execute();
    $query->bind_result($hashedPassword);
    $query->fetch();
    $query->close();
    return $hashedPassword;
}

/**
 * Esta función actualiza la contraseña de un usuario.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $userId ID del usuario.
 * @param string $newHashedPassword Nueva contraseña ya hasheada.
 * @return bool Devuelvo true si la actualización fue exitosa, false si no.
 */
function updatePassword($conn, $userId, $newHashedPassword)
{
    // Preparo la consulta para actualizar la contraseña
    $query = $conn->prepare("UPDATE usuario SET pass = ? WHERE id = ?");
    $query->bind_param("si", $newHashedPassword, $userId);
    $success = $query->execute();
    $query->close();
    return $success;
}

/**
 * Esta función activa un usuario con rol 'user' según su ID.
 *
 * @param int $usuarioId ID del usuario a activar.
 * @return bool Devuelvo true si la activación fue exitosa, false si no.
 */
function activateUser($usuarioId)
{
    $conn = conexion();
    // Preparo la consulta para activar el usuario
    $query = $conn->prepare("UPDATE usuario SET activo = 1 WHERE id = ? AND rol = 'user'");
    $query->bind_param("i", $usuarioId);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);
    return $resultado;
}

/**
 * Esta función desactiva un usuario con rol 'user' según su ID.
 *
 * @param int $usuarioId ID del usuario a desactivar.
 * @return bool Devuelvo true si la desactivación fue exitosa, false si no.
 */
function deactivateUser($usuarioId)
{
    $conn = conexion();
    // Preparo la consulta para desactivar el usuario
    $query = $conn->prepare("UPDATE usuario SET activo = 0 WHERE id = ? AND rol = 'user'");
    $query->bind_param("i", $usuarioId);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);
    return $resultado;
}

/* ------------- ADMIN -------------  */

/**
 * Esta función obtiene los datos de un usuario administrador activo según su email y rol.
 *
 * @param string $email Correo electrónico del administrador.
 * @param string $rol Rol del usuario (por defecto 'admin').
 * @return array|false Devuelvo los datos del administrador o false si no existe.
 */
function getAdminData($email, $rol = 'admin')
{
    $conn = conexion();
    // Se prepara la consulta para buscar el administrador activo por email y rol
    $query = $conn->prepare("SELECT * FROM usuario WHERE email = ? AND rol = ? AND activo = 1");
    $query->bind_param("ss", $email, $rol);
    $query->execute();
    $result = $query->get_result();
    $datos = $result->fetch_assoc();
    $query->close();
    cerrar_conexion($conn);
    // Si no se encuentra, devuelvo false
    return $datos ?: false;
}

/* ------------- PRODUCTOS -------------  */

/**
 * Esta función crea un nuevo producto en la base de datos, sube la imagen si corresponde,
 * y asocia los géneros, plataformas y stock inicial.
 *
 * @param string $nombre Nombre del producto.
 * @param array $imagen Datos del archivo de imagen subido.
 * @param string $descripcion Descripción del producto.
 * @param string $fecha_lanzamiento Fecha de lanzamiento.
 * @param array $generos Array de IDs de géneros asociados.
 * @param float $precio Precio base del producto.
 * @param float $descuento Porcentaje de descuento.
 * @param array $stock Array asociativo plataforma_id => stock_disponible.
 * @param array $plataformas Array de IDs de plataformas asociadas.
 * @param int $creado_por ID del usuario que crea el producto.
 * @param int $actualizado_por ID del usuario que actualiza el producto.
 * @return int|false Devuelvo el ID del producto creado o false si falla.
 */
function createProduct($nombre, $imagen, $descripcion, $fecha_lanzamiento, $generos, $precio, $descuento, $stock, $plataformas, $creado_por, $actualizado_por)
{
    // Se verifica si se subió una imagen válida
    $hayImagen = $imagen && isset($imagen['tmp_name']) && $imagen['error'] === UPLOAD_ERR_OK;
    $rutaRelativa = 'default.jpg';

    if ($hayImagen) {
        // Se obtiene la extensión y se genera un nombre único para la imagen
        $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
        $nombreImagen = 'producto_' . time() . '.' . $extension;
        $rutaRelativa = 'images/products/' . $nombreImagen;
        $rutaAbsoluta = '../' . $rutaRelativa;

        // Se crea la carpeta si no existe
        if (!file_exists('../images/products/')) {
            mkdir('../images/products/', 0777, true);
        }

        // Se mueve la imagen subida a la carpeta destino
        if (!move_uploaded_file($imagen['tmp_name'], $rutaAbsoluta)) {
            $rutaRelativa = 'default.jpg'; // Si falla la subida, se usa la imagen por defecto
        }
    }

    $conn = conexion();

    // Se inserta el producto en la base de datos
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

        // Se insertan los géneros asociados al producto
        $generoQuery = $conn->prepare("
            INSERT INTO producto_genero (producto_id, genero_id) 
            VALUES (?, ?)
        ");
        foreach ($generos as $genero_id) {
            $generoQuery->bind_param("ii", $productoId, $genero_id);
            $generoQuery->execute();
        }
        $generoQuery->close();

        // Se insertan las plataformas asociadas al producto
        $plataformaQuery = $conn->prepare("
            INSERT INTO producto_plataforma (producto_id, plataforma_id) 
            VALUES (?, ?)
        ");

        // Se inserta el stock inicial por plataforma
        $stockQuery = $conn->prepare("
            INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible, stock_reservado)
            VALUES (?, ?, ?, 0)
        ");

        foreach ($plataformas as $plataforma_id) {
            // Relaciono el producto con la plataforma
            $plataformaQuery->bind_param("ii", $productoId, $plataforma_id);
            $plataformaQuery->execute();

            // Se obtiene el stock específico para esta plataforma (si no está definido, se pone 0)
            $stockDisponible = isset($stock[$plataforma_id]) ? $stock[$plataforma_id] : 0;
            // Inserto el stock inicial para la plataforma
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
 * Esta función modifica los datos de un producto existente, incluyendo sus géneros, plataformas y stock.
 *
 * @param int $id ID del producto a modificar.
 * @param string $nombre Nombre del producto.
 * @param string $imagen Ruta o nombre de la imagen.
 * @param string $descripcion Descripción del producto.
 * @param string $fecha_lanzamiento Fecha de lanzamiento.
 * @param array $generos Array de IDs de géneros asociados.
 * @param float $precio Precio base del producto.
 * @param float $descuento Porcentaje de descuento.
 * @param array $stock Array asociativo plataforma_id => stock_disponible.
 * @param array $plataformas Array de IDs de plataformas asociadas.
 * @param int $actualizado_por ID del usuario que actualiza el producto.
 * @return bool Devuelvo true si la actualización fue exitosa, false si no.
 */
function modifyProduct($id, $nombre, $imagen, $descripcion, $fecha_lanzamiento, $generos, $precio, $descuento, $stock, $plataformas, $actualizado_por)
{
    $conn = conexion();

    // Actualizo la información principal del producto
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
        // Obtengo los géneros actuales y los ordeno para comparar
        $generosActuales = getSelectedGenreIds($id);
        sort($generosActuales);
        sort($generos);

        // Si los géneros cambiaron, los actualizo
        if ($generosActuales !== $generos) {
            $conn->query("DELETE FROM producto_genero WHERE producto_id = $id");
            foreach ($generos as $genero_id) {
                addProductGenre($id, $genero_id);
            }
        }

        // Comparo plataformas actuales con las enviadas
        $plataformasActuales = getSelectedPlatformIds($id);
        $plataformasAEliminar = array_diff($plataformasActuales, $plataformas);
        $plataformasANuevas = array_diff($plataformas, $plataformasActuales);
        $plataformasExistentes = array_intersect($plataformas, $plataformasActuales);

        // Recorro la lista de plataformas a eliminar
        foreach ($plataformasAEliminar as $plataforma_id) {
            // Elimino la plataforma del producto
            deleteProductPlatform($id, $plataforma_id);

            // Elimino el stock del producto para la plataforma
            deleteProductStock($id, $plataforma_id);

            // Elimino el producto del carrito 
            deleteProductFromCart($id, $plataforma_id);
        }

        // Agrego plataformas nuevas
        foreach ($plataformasANuevas as $plataforma_id) {
            $nuevoStock = $stock[$plataforma_id] ?? 0;
            addProductPlatform($id, $plataforma_id, $nuevoStock);
        }

        // Actualizo stock disponible para las plataformas existentes (sin tocar el reservado)
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

/**
 * Esta función elimina la relación entre un producto y una plataforma específica.
 *
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @return void
 */
function deleteProductPlatform($productoId, $plataformaId)
{
    $conn = conexion();
    $query = $conn->prepare("DELETE FROM producto_plataforma WHERE producto_id = ? AND plataforma_id = ?");
    $query->bind_param("ii", $productoId, $plataformaId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Esta función elimina un producto de los carritos para una plataforma específica.
 *
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @return void
 */
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
 * Esta función desactiva un producto (lo marca como inactivo) y lo elimina de todos los carritos y favoritos.
 *
 * @param int $id ID del producto a desactivar.
 * @return bool Devuelvo true si la desactivación fue exitosa, false si no.
 */
function deactivateProduct($id)
{
    $conn = conexion();
    $query = $conn->prepare("UPDATE producto SET activo = 0 WHERE id = ?");
    $query->bind_param("i", $id);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);

    // Elimino el producto de todos los carritos y favoritos
    deleteProductFromCartAllPlatforms($id);
    deleteProductFromFavorites($id);
    return $resultado;
}

/**
 * Esta función activa un producto (lo marca como activo).
 *
 * @param int $id ID del producto a activar.
 * @return bool Devuelvo true si la activación fue exitosa, false si no.
 */
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

/**
 * Esta función agrega una relación entre un producto y un género.
 *
 * @param int $productId ID del producto.
 * @param int $genreId ID del género.
 * @return void
 */
function addProductGenre($productId, $genreId)
{
    $conn = conexion();
    $query = $conn->prepare("INSERT INTO producto_genero (producto_id, genero_id) VALUES (?, ?)");
    $query->bind_param("ii", $productId, $genreId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Esta función agrega una relación entre un producto y una plataforma, y registra el stock inicial.
 *
 * @param int $productId ID del producto.
 * @param int $platformId ID de la plataforma.
 * @param int $stock Stock disponible para esa plataforma.
 * @return void
 */
function addProductPlatform($productId, $platformId, $stock)
{
    $conn = conexion();

    // Inserto la relación producto-plataforma
    $query = $conn->prepare("INSERT INTO producto_plataforma (producto_id, plataforma_id) VALUES (?, ?)");
    $query->bind_param("ii", $productId, $platformId);
    $query->execute();
    $query->close();

    // Agrego el stock inicial para esa plataforma
    $query = $conn->prepare("INSERT INTO producto_stock (producto_id, plataforma_id, stock_disponible) VALUES (?, ?, ?)");
    $query->bind_param("iii", $productId, $platformId, $stock);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Esta función obtiene el catálogo de productos activos y lo filtra por nombre si se indica.
 *
 * @param string $nombre Nombre del producto a buscar (opcional).
 * @return array Devuelvo un array con los productos filtrados.
 */
function getCatalog($nombre = '')
{
    $conn = conexion();
    $query = $conn->prepare("
        SELECT p.* FROM producto p
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
    // Devuelvo los productos filtrados por nombre usando la función auxiliar
    return getFilteredProducts($nombre);
}

/**
 * Esta función obtiene todos los productos con paginación y búsqueda por nombre.
 *
 * @param int $offset Número de registros a saltar (para la paginación).
 * @param int $limit Cantidad máxima de registros a devolver.
 * @param string $search Texto de búsqueda para filtrar por nombre.
 * @return array Devuelvo un array con los productos encontrados.
 */
function getAllProducts($offset = 0, $limit = 20, $search = '')
{
    $conn = conexion();
    $search = "%$search%";

    // Preparo la consulta para obtener los productos, sus géneros y plataformas asociados 
    // Se usan LEFT JOIN para no excluir productos que no tengan género o plataforma asignados
    // Se agrupan los géneros y plataformas usando GROUP_CONCAT para mostrar todos en una sola fila por producto
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
 * Esta función obtiene el total de productos activos que coinciden con el filtro de nombre.
 *
 * @param string $search Texto de búsqueda para filtrar por nombre.
 * @return int Devuelvo la cantidad total de productos encontrados.
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
 * Esta función obtiene los datos de un producto según su ID.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $productoId ID del producto a buscar.
 * @return array|null Devuelvo los datos del producto o null si no existe.
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

/**
 * Esta función obtiene todos los géneros disponibles.
 *
 * @return array Devuelvo un array con todos los géneros.
 */
function getAllGenres()
{
    $conn = conexion();
    // Consulto todos los géneros de la tabla
    $result = $conn->query("SELECT id, nombre FROM genero");
    $generos = [];

    // Recorro los resultados y los agrego al array
    while ($row = $result->fetch_assoc()) {
        $generos[] = $row;
    }

    cerrar_conexion($conn);
    return $generos;
}

/**
 * Esta función obtiene los géneros asociados a un producto específico.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $productoId ID del producto.
 * @return array Devuelvo un array con los géneros del producto.
 */
function getGenresByProduct($conn, $productoId)
{
    // Preparo la consulta para obtener los géneros del producto
    // Se hace un JOIN para obtener el nombre del género
    // Se filtra por el ID del producto
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

/**
 * Esta función obtiene los IDs de los géneros asociados a un producto.
 *
 * @param int $productoId ID del producto.
 * @return array Devuelvo un array con los IDs de los géneros.
 */
function getSelectedGenreIds($productoId)
{
    $conn = conexion();
    // Preparo la consulta para obtener los IDs de los géneros del producto
    $query = $conn->prepare("SELECT genero_id FROM producto_genero WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $generos = [];

    // Recorro los resultados y agrego cada ID al array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $generos[] = $row['genero_id'];
        }
    }

    $query->close();
    cerrar_conexion($conn);
    return $generos;
}

/**
 * Esta función obtiene todas las plataformas disponibles.
 *
 * @return array Devuelvo un array con todas las plataformas.
 */
function getAllPlatforms()
{
    $conn = conexion();
    // Consulto todas las plataformas de la tabla
    $result = $conn->query("SELECT id, nombre FROM plataforma");
    $plataformas = [];

    // Recorro los resultados y los agrego al array
    while ($row = $result->fetch_assoc()) {
        $plataformas[] = $row;
    }

    cerrar_conexion($conn);
    return $plataformas;
}

/**
 * Esta función obtiene las plataformas asociadas a un producto, incluyendo el stock disponible por plataforma.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $productoId ID del producto.
 * @return array Devuelvo un array con las plataformas y su stock.
 */
function getPlatformsByProduct($conn, $productoId)
{
    // Preparo la consulta para obtener las plataformas y el stock del producto
    // Se hace un JOIN para obtener el nombre de la plataforma
    // Se hace un LEFT JOIN para obtener el stock disponible por plataforma
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
    $plataformas = $result->fetch_all(MYSQLI_ASSOC); // Obtengo todas las plataformas asociadas al producto
    $query->close();
    return $plataformas; // Retorno las plataformas con su stock
}

/**
 * Esta función obtiene los IDs de las plataformas asociadas a un producto.
 *
 * @param int $productoId ID del producto.
 * @return array Devuelvo un array con los IDs de las plataformas.
 */
function getSelectedPlatformIds($productoId)
{
    $conn = conexion();
    // Preparo la consulta para obtener los IDs de las plataformas del producto
    $query = $conn->prepare("SELECT plataforma_id FROM producto_plataforma WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $plataformas = [];

    // Recorro los resultados y agrego cada ID al array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plataformas[] = $row['plataforma_id']; // Agrego cada plataforma al array
        }
    }

    $query->close();
    cerrar_conexion($conn);
    return $plataformas; // Retorno el array de plataformas
}

/**
 * Esta función obtiene productos relacionados según los géneros del producto actual.
 *
 * @param int $producto_id ID del producto base.
 * @param int $limit Cantidad máxima de productos relacionados a devolver.
 * @return array Devuelvo un array con los productos relacionados.
 */
function getRelatedProducts($producto_id, $limit = 10)
{
    $conn = conexion();
    // Obtengo los géneros asociados al producto actual
    $query = $conn->prepare("
        SELECT genero_id
        FROM producto_genero 
        WHERE producto_id = ?
    ");
    $query->bind_param("i", $producto_id);
    $query->execute();
    $result = $query->get_result();
    $generos = [];

    // Recorro los resultados y agrego cada género al array
    while ($row = $result->fetch_assoc()) {
        $generos[] = $row['genero_id'];
    }
    $query->close();

    // Si no hay géneros asociados, retorno un array vacío
    if (empty($generos)) return [];

    // Armo los placeholders para la consulta dinámica según la cantidad de géneros
    $placeholders = implode(',', array_fill(0, count($generos), '?'));
    // Defino los tipos de los parámetros (todos enteros)
    $types = str_repeat('i', count($generos) + 1);
    // Combino los géneros con el ID del producto actual para excluirlo de la búsqueda
    $params = array_merge($generos, [$producto_id]);

    // Busco productos distintos que compartan al menos un género, excluyendo el producto actual
    // y limitando la cantidad de resultados según el parámetro $limit
    // Uso un JOIN para relacionar los productos con sus géneros
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
    // Uso el operador de expansión para pasar los parámetros dinámicamente
    $query->bind_param($types, ...$params);
    $query->execute();
    $result = $query->get_result();
    $productosRelacionados = []; // Creo un array para almacenar los productos relacionados

    // Recorro los resultados y los agrego al array
    while ($row = $result->fetch_assoc()) {
        $productosRelacionados[] = $row;
    }

    $query->close();
    cerrar_conexion($conn);
    // Retorno los productos relacionados encontrados
    return $productosRelacionados;
}

/**
 * Esta función calcula el precio final de un producto aplicando el descuento si corresponde.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $productoId ID del producto.
 * @return float|null Devuelvo el precio con descuento o null si no existe.
 */
function getDiscountedPrice($conn, $productoId)
{
    // Consulto el precio y descuento del producto
    $query = $conn->prepare("SELECT precio, descuento FROM producto WHERE id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $query->close();

    if ($result->num_rows > 0) {
        $producto = $result->fetch_assoc();
        $precio = $producto['precio']; // Obtengo el precio base
        $descuento = $producto['descuento']; // Obtengo el descuento
        // Si hay descuento, lo aplico
        if ($descuento && $descuento > 0) {
            return $precio - ($precio * $descuento / 100); // Calculo el precio con descuento
        } else {
            return $precio; // Si no hay descuento, devuelvo el precio base
        }
    }
    return null;
}

/* ------------- STOCK -------------  */

/**
 * Esta función actualiza el stock disponible de un producto para una plataforma específica.
 *
 * @param int $stock Nuevo stock disponible.
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @return void
 */
function updateProductStock($stock, $productoId, $plataformaId)
{
    $conn = conexion();
    $query = $conn->prepare("UPDATE producto_stock SET stock_disponible = ? WHERE producto_id = ? AND plataforma_id = ?");
    $query->bind_param("iii", $stock, $productoId, $plataformaId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Esta función elimina el stock de un producto para una plataforma específica.
 *
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @return void
 */
function deleteProductStock($productoId, $plataformaId)
{
    $conn = conexion();
    $query = $conn->prepare("DELETE FROM producto_stock WHERE producto_id = ? AND plataforma_id = ?");
    $query->bind_param("ii", $productoId, $plataformaId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Esta función verifica si existe una combinación producto-plataforma en la tabla de stock.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @return bool Devuelvo true si existe, false si no.
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
 * Esta función obtiene el stock disponible de un producto para todas sus plataformas.
 *
 * @param int $productoId ID del producto.
 * @return array Devuelvo un array asociativo plataforma_id => stock_disponible.
 */
function geProductStockByPlataform($productoId)
{
    $conn = conexion();
    // Consulto el stock disponible por plataforma para el producto dado
    $query = $conn->prepare("SELECT plataforma_id, stock_disponible FROM producto_stock WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $result = $query->get_result();
    $stock = [];

    // Recorro los resultados y armo el array asociativo plataforma_id => stock_disponible
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Agrego el stock disponible para cada plataforma
            $stock[$row['plataforma_id']] = $row['stock_disponible'];
        }
    }

    $query->close();
    cerrar_conexion($conn);
    return $stock;
}

/**
 * Esta función obtiene el stock disponible de un producto para una plataforma específica.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @return int|null Devuelvo el stock disponible o null si no existe.
 */
function getAvailableStock($conn, $productoId, $plataformaId)
{
    // Consulto el stock disponible para la combinación producto-plataforma
    $query = $conn->prepare("
        SELECT stock_disponible
        FROM producto_stock
        WHERE producto_id = ? AND plataforma_id = ?
    ");
    $query->bind_param("ii", $productoId, $plataformaId);
    $query->execute();
    $result = $query->get_result();
    $query->close();

    // Si existe, devuelvo el stock, si no, null
    if ($result->num_rows > 0) {
        return (int)$result->fetch_assoc()['stock_disponible'];
    }

    return null;
}

/**
 * Esta función reserva stock de un producto para una plataforma específica.
 * Se suma la cantidad al stock reservado y se resta del stock disponible, solo si hay suficiente stock.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @param int $cantidad Cantidad a reservar.
 * @return bool Devuelvo true si la reserva fue exitosa, false si no.
 */
function reserveProductStock($conn, $productoId, $plataformaId, $cantidad)
{
    if ($cantidad <= 0) {
        // No se puede reservar una cantidad no positiva
        return false;
    }

    // Actualizo el stock: sumo a reservado y resto a disponible solo si hay suficiente stock disponible
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
    $exito = $query->affected_rows > 0; // Verifico si se actualizó alguna fila
    $query->close();
    return $exito;
}

/**
 * Esta función libera stock reservado de un producto para una plataforma específica.
 * Se resta la cantidad al stock reservado (sin bajar de cero) y se suma al stock disponible.
 *
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @param int $cantidad Cantidad a liberar.
 * @return void
 */
function releaseProductStock($productoId, $plataformaId, $cantidad)
{
    $conn = conexion();
    // Actualizo el stock: resto a reservado (sin bajar de cero) y sumo a disponible
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

/**
 * Esta función descuenta definitivamente el stock reservado de un producto para una plataforma.
 * Se resta la cantidad al stock reservado (sin bajar de cero), al confirmar una compra.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @param int $cantidad Cantidad a consumir.
 * @return bool Devuelvo true si la operación fue exitosa, false si no.
 */
function consumeReservedStock($conn, $productoId, $plataformaId, $cantidad)
{
    if ($cantidad <= 0) {
        // No se puede consumir una cantidad no positiva
        return false;
    }

    // Resto la cantidad al stock reservado (sin bajar de cero)
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
    $exito = $query->affected_rows > 0; // Verifico si se actualizó alguna fila
    $query->close();
    return $exito;
}

/* ------------- CARRITO -------------  */

/**
 * Esta función crea un carrito vacío para un usuario.
 *
 * @param int $usuarioId ID del usuario para el que se crea el carrito.
 * @return void
 */
function createCart($usuarioId)
{
    $conn = conexion();
    // Inserto un nuevo carrito activo para el usuario
    $query = $conn->prepare("INSERT INTO carrito (creado_por, activo) VALUES (?, 1)");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Esta función obtiene el ID del carrito activo de un usuario.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $usuarioId ID del usuario.
 * @return int|null Devuelvo el ID del carrito activo o null si no existe.
 */
function getActiveCartId($conn, $usuarioId)
{
    // Busco el carrito activo del usuario
    $query = $conn->prepare("SELECT id FROM carrito WHERE creado_por = ? AND activo = 1");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $result = $query->get_result();
    $query->close();
    // Si existe, devuelvo el ID, si no, null
    $carritoId = ($result->num_rows > 0) ? $result->fetch_assoc()['id'] : null;
    return $carritoId;
}

/**
 * Esta función obtiene todos los ítems de un carrito, incluyendo información del producto,
 * la plataforma, el precio con descuento y el precio total con descuento.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $carritoId ID del carrito del usuario.
 * @return array Devuelvo un array con los ítems del carrito y sus detalles.
 */
function getCartItems($conn, $carritoId)
{
    // Preparo la consulta para obtener los ítems del carrito junto con datos del producto y plataforma
    // Se hace un JOIN entre las tablas carrito_item, producto y plataforma
    // para obtener toda la información necesaria de cada ítem
    // Se calcula el precio con descuento y el precio total con descuento
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
    $items = $result->fetch_all(MYSQLI_ASSOC); // Obtengo todos los ítems del carrito con sus detalles
    $query->close();
    return $items;
}

/**
 * Esta función obtiene un ítem específico del carrito según el producto y la plataforma.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $carritoId ID del carrito.
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @return array|null Devuelvo el ítem encontrado o null si no existe.
 */
function getCartItem($conn, $carritoId, $productoId, $plataformaId)
{
    // Preparo la consulta para buscar el ítem en el carrito
    $query = $conn->prepare("
        SELECT id, cantidad 
        FROM carrito_item 
        WHERE carrito_id = ? AND producto_id = ? AND plataforma_id = ?
    ");
    $query->bind_param("iii", $carritoId, $productoId, $plataformaId);
    $query->execute();
    $result = $query->get_result();
    $query->close();
    // Si existe, devuelvo el ítem; si no, null
    return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
}

/**
 * Esta función obtiene un ítem del carrito por su ID.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $carritoItemId ID del ítem del carrito.
 * @return array|null Devuelvo los datos del ítem o null si no existe.
 */
function getCartItemById($conn, $carritoItemId)
{
    // Preparo la consulta para buscar el ítem por su ID
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
    // Devuelvo el ítem si existe, si no, null
    return $item ?: null;
}

/**
 * Esta función inserta un nuevo ítem en el carrito.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $carritoId ID del carrito.
 * @param int $productoId ID del producto.
 * @param int $plataformaId ID de la plataforma.
 * @param int $cantidad Cantidad del producto.
 * @param float $precioUnitario Precio unitario del producto.
 * @return void
 */
function insertCartItem($conn, $carritoId, $productoId, $plataformaId, $cantidad, $precioUnitario)
{
    $precioTotal = $precioUnitario * $cantidad; // Calculo el precio total para la cantidad
    // Inserto el nuevo ítem en el carrito
    $query = $conn->prepare("
        INSERT INTO carrito_item (carrito_id, producto_id, plataforma_id, cantidad, precio_total)
        VALUES (?, ?, ?, ?, ?)
    ");
    $query->bind_param("iiiid", $carritoId, $productoId, $plataformaId, $cantidad, $precioTotal);
    $query->execute();
    $query->close();
}

/**
 * Esta función actualiza la cantidad y el precio total de un ítem del carrito.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $itemId ID del ítem del carrito a actualizar.
 * @param int $nuevaCantidad Nueva cantidad del producto en el carrito.
 * @param float $precioUnitario Precio unitario del producto (puede incluir descuento).
 * @return void
 */
function updateCartItem($conn, $itemId, $nuevaCantidad, $precioUnitario)
{
    $precioTotal = $precioUnitario * $nuevaCantidad; // Calculo el nuevo precio total según la cantidad
    // Actualizo la cantidad y el precio total del ítem en el carrito
    $query = $conn->prepare("UPDATE carrito_item SET cantidad = ?, precio_total = ? WHERE id = ?");
    $query->bind_param("idi", $nuevaCantidad, $precioTotal, $itemId);
    $query->execute();
    $query->close();
}

/**
 * Esta función agrega un producto al carrito del usuario, validando stock y combinaciones.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $usuarioId ID del usuario.
 * @param int $productoId ID del producto a agregar.
 * @param int $plataformaId ID de la plataforma seleccionada.
 * @param int $cantidad Cantidad a agregar.
 * @param float $precioUnitario Precio unitario del producto (puede incluir descuento).
 * @return array Devuelvo un array con el resultado de la operación y mensaje.
 */
function addProductToCart($conn, $usuarioId, $productoId, $plataformaId, $cantidad, $precioUnitario)
{
    // Obtengo el ID del carrito activo del usuario
    $carritoId = getActiveCartId($conn, $usuarioId);
    // Si no hay carrito activo, retorno un mensaje de error
    if (!$carritoId) {
        return ['exito' => false, 'mensaje' => 'No se encontró un carrito activo.'];
    }

    // Verifico que la combinación producto-plataforma sea válida
    $combinacionValida = getProductPlataformId($conn, $productoId, $plataformaId);
    // Si no es válida, retorno un mensaje de error
    if (!$combinacionValida) {
        return ['exito' => false, 'mensaje' => 'Combinación producto-plataforma no válida.'];
    }

    // Consulto el stock disponible para esa combinación
    $stockDisponible = getAvailableStock($conn, $productoId, $plataformaId);
    // Si no hay stock disponible o la cantidad solicitada supera el stock, retorno un mensaje de error
    if ($stockDisponible === null || $cantidad > $stockDisponible) {
        return ['exito' => false, 'mensaje' => 'Stock insuficiente.'];
    }

    // Verifico si el producto ya está en el carrito para esa plataforma
    $item = getCartItem($conn, $carritoId, $productoId, $plataformaId);
    if ($item) {
        // Si existe, actualizo la cantidad y el precio total
        $nuevaCantidad = $item['cantidad'] + $cantidad;
        updateCartItem($conn, $item['id'], $nuevaCantidad, $precioUnitario);
    } else {
        // Si no existe, lo inserto como nuevo ítem
        insertCartItem($conn, $carritoId, $productoId, $plataformaId, $cantidad, $precioUnitario);
    }

    // Reservo el stock (resta del disponible y suma al reservado)
    // Si la reserva falla, retorno un mensaje de error sin afectar el carrito
    if (!reserveProductStock($conn, $productoId, $plataformaId, $cantidad)) {
        return ['exito' => false, 'mensaje' => 'No se pudo reservar stock.'];
    }

    // Devuelvo éxito, mensaje y el stock restante para esa combinación
    return [
        'exito' => true,
        'mensaje' => 'Producto agregado al carrito.',
        'stock_restante' => $stockDisponible - $cantidad
    ];
}

/**
 * Esta función elimina un producto del carrito del usuario y libera el stock reservado.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $carritoItemId ID del ítem del carrito a eliminar.
 * @param int $userId ID del usuario dueño del carrito.
 * @return bool Devuelvo true si se eliminó correctamente, false si no.
 */
function removeProductFromCart($conn, $carritoItemId, $userId)
{
    // Obtengo el ID del carrito activo del usuario
    $carritoId = getActiveCartId($conn, $userId);
    // Si no hay carrito activo, retorno false
    if (!$carritoId) {
        return false;
    }

    // Obtengo los datos del ítem a eliminar (producto, plataforma, cantidad)
    $item = getCartItemById($conn, $carritoItemId, $carritoId);
    // Si no existe el ítem, retorno false
    if (!$item) {
        return false;
    }

    $productoId = $item['producto_id']; // Obtengo el ID del producto
    $plataformaId = $item['plataforma_id']; // Obtengo el ID de la plataforma
    $cantidad = $item['cantidad']; // Obtengo la cantidad del ítem

    // Elimino el ítem del carrito
    $query = $conn->prepare("DELETE FROM carrito_item WHERE id = ? AND carrito_id = ?");
    $query->bind_param("ii", $carritoItemId, $carritoId);
    $query->execute();
    $success = $query->affected_rows > 0;
    $query->close();

    // Si se eliminó correctamente, libero el stock reservado
    if ($success) {
        releaseProductStock($productoId, $plataformaId, $cantidad);
    }

    return $success;
}

/**
 * Elimina un producto de todos los carritos, sin importar la plataforma.
 *
 * @param int $productoId ID del producto a eliminar del carrito.
 * @return void
 */
function deleteProductFromCartAllPlatforms($productoId)
{
    $conn = conexion();
    // Preparo la consulta para eliminar todas las entradas de carrito_item que coincidan con el producto
    $query = $conn->prepare("DELETE FROM carrito_item WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Esta función vacía el carrito de un usuario, liberando el stock reservado.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $userId ID del usuario cuyo carrito se desea vaciar.
 * @return bool Retorna true si se vació correctamente el carrito, false si no había carrito activo.
 */
function emptyCart($conn, $userId)
{
    // Obtengo el ID del carrito activo del usuario
    $carritoId = getActiveCartId($conn, $userId);
    // Si no hay carrito activo, retorno false
    if (!$carritoId) {
        return false;
    }

    // Recupero todos los ítems actuales del carrito
    $items = getCartItems($conn, $carritoId);

    // Recorro cada ítem para liberar el stock reservado
    foreach ($items as $item) {
        $productoId = $item['producto_id'];
        $plataformaId = $item['plataforma_id'];
        $cantidad = $item['cantidad'];

        // Libero el stock reservado para esta combinación de producto y plataforma
        releaseProductStock($productoId, $plataformaId, $cantidad);
    }

    // Elimino todos los ítems del carrito
    $query = $conn->prepare("DELETE FROM carrito_item WHERE carrito_id = ?");
    $query->bind_param("i", $carritoId);
    $query->execute();
    $success = $query->affected_rows > 0;
    $query->close();
    // Devuelvo true si el carrito fue vaciado, false en caso contrario
    return $success;
}

/**
 * Esta función calcula el total original, el total descontado y el subtotal de un carrito.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $carritoId ID del carrito a analizar.
 * @return array Devuelve un arreglo asociativo con las claves: 'total', 'descuento' y 'subtotal'.
 */
function getCartSummary($conn, $carritoId)
{
    // Inicializo los totales en cero
    $totalOriginal = 0;
    $totalDescuento = 0;

    // Preparo la consulta para calcular los totales del carrito
    // Se hace un JOIN entre carrito_item y producto para obtener el precio y descuento
    // Se usa un JOIN adicional con producto_plataforma para asegurar que la combinación es válida
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
    $query->bind_result($totalOriginal, $totalDescuento); // Obtengo los resultados y los asigno a variables
    $query->fetch();
    $query->close();

    // Si no hay resultados, retorno 0 para ambos totales
    $totalOriginal = $totalOriginal ?? 0;
    $totalDescuento = $totalDescuento ?? 0;

    // Calculo el subtotal (total sin descuentos)
    $subtotal = $totalOriginal - $totalDescuento;

    // Retorno un array con los totales redondeados a 2 decimales
    return [
        'total' => round($totalOriginal, 2),
        'descuento' => round($totalDescuento, 2),
        'subtotal' => round($subtotal, 2)
    ];
}

/* ------------- FAVORITOS -------------  */

/**
 * Esta función crea una lista de favoritos activa para el usuario especificado.
 *
 * @param int $usuarioId ID del usuario que crea la lista de favoritos.
 */
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
 * Esta función obtiene el ID de la lista de favoritos activa del usuario.
 *
 * @param int $usuarioId ID del usuario del cual se quiere obtener la lista activa.
 * @return int|null Devuelve el ID de la lista de favoritos activa o null si no existe.
 */
function getActiveFavListId($usuarioId)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT id FROM favorito WHERE creado_por = ? AND activo = 1 LIMIT 1");
    $query->bind_param("i", $usuarioId);
    $query->execute();
    $query->bind_result($favoritoId); // Asigno el resultado a la variable
    $query->fetch();
    $query->close();
    cerrar_conexion($conn);
    return $favoritoId; // Retorno el ID de la lista activa o null si no existe
}

/**
 * Esta función verifica si un producto ya está en la lista de favoritos.
 *
 * @param int $favoritoId ID de la lista de favoritos.
 * @param int $productoId ID del producto a verificar.
 * @return bool Devuelve true si el producto ya está en la lista, false si no.
 */
function productIsAlreadyFavorite($favoritoId, $productoId)
{
    $conn = conexion();
    $query = $conn->prepare("SELECT 1 FROM favorito_item WHERE favorito_id = ? AND producto_id = ?");
    $query->bind_param("ii", $favoritoId, $productoId);
    $query->execute();
    $query->store_result(); // Almaceno el resultado para contar filas
    $existe = $query->num_rows > 0; // Verifico si hay al menos una coincidencia
    $query->close();
    cerrar_conexion($conn);
    return $existe; // Retorno true si existe, false si no
}

/**
 * Esta función agrega o elimina un producto de la lista de favoritos del usuario.
 * Si el producto ya está en la lista, se elimina. Si no está, se agrega.
 *
 * @param int $usuarioId ID del usuario que realiza la acción.
 * @param int $productoId ID del producto a agregar o quitar de favoritos.
 * @return bool Devuelve true si se agregó, false si se eliminó.
 */
function addOrRemoveFav($usuarioId, $productoId)
{
    // Obtengo el ID de la lista de favoritos activa del usuario
    $favoritoId = getActiveFavListId($usuarioId);

    // Verifico si el producto ya está en la lista
    if (productIsAlreadyFavorite($favoritoId, $productoId)) {
        // El producto ya es favorito, así que lo elimino
        $conn = conexion();
        $query = $conn->prepare("DELETE FROM favorito_item WHERE favorito_id = ? AND producto_id = ?");
        $query->bind_param("ii", $favoritoId, $productoId);
        $query->execute();
        $query->close();
        cerrar_conexion($conn);
        return false;
    } else {
        // El producto no es favorito, así que lo agrego
        $conn = conexion();
        $query = $conn->prepare("INSERT INTO favorito_item (favorito_id, producto_id) VALUES (?, ?)");
        $query->bind_param("ii", $favoritoId, $productoId);
        $query->execute();
        $query->close();
        cerrar_conexion($conn);
        return true;
    }
}

/**
 * Esta función elimina un producto de todos los registros de listas de favoritos donde esté presente.
 * 
 * @param int $productoId ID del producto que se desea eliminar de los favoritos.
 */
function deleteProductFromFavorites($productoId)
{
    $conn = conexion();
    $query = $conn->prepare("DELETE FROM favorito_item WHERE producto_id = ?");
    $query->bind_param("i", $productoId);
    $query->execute();
    $query->close();
    cerrar_conexion($conn);
}

/**
 * Esta función obtiene todos los productos que el usuario tiene marcados como favoritos.
 * 
 * @param int $usuarioId ID del usuario del cual se desea obtener los productos favoritos.
 * @return array Retorna un array con los productos favoritos del usuario.
 */
function getFavoriteProducts($usuarioId)
{
    $conn = conexion();

    // Se obtiene el ID de la lista de favoritos activa del usuario
    $favoritoId = getActiveFavListId($usuarioId);

    if (!$favoritoId) {
        // Si no tiene una lista activa, se retorna un array vacío
        cerrar_conexion($conn);
        return [];
    }

    // Se seleccionan los datos básicos de los productos favoritos
    // Se usa un JOIN de la tabla favorito_item con la tabla producto
    // para obtener los detalles de cada producto
    $query = $conn->prepare("
        SELECT p.id, p.nombre, p.precio, p.imagen
        FROM favorito_item fi
        JOIN producto p ON fi.producto_id = p.id
        WHERE fi.favorito_id = ?
    ");
    $query->bind_param("i", $favoritoId);
    $query->execute();
    $result = $query->get_result();

    // Se recorren los resultados y se almacenan en un array
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }

    $query->close();
    cerrar_conexion($conn);
    return $productos;
}

/* ------------- CHECKOUT / PEDIDOS -------------  */

/**
 * Esta función crea un nuevo pedido a partir del carrito activo del usuario.
 * Se calcula el total y descuento, se insertan los ítems del pedido, 
 * se consume el stock reservado, y finalmente se vacía el carrito.
 * 
 * @param int $usuarioId ID del usuario que realiza el pedido.
 * @return int|false Retorna el ID del pedido creado o false si ocurrió un error.
 */
function createOrder($usuarioId)
{
    $conn = conexion();

    try {
        // Se inicia la transacción
        $conn->begin_transaction();

        // Se obtiene el ID del carrito activo del usuario
        $carritoId = getActiveCartId($conn, $usuarioId);
        if (!$carritoId) {
            throw new Exception("No se encontró un carrito activo.");
        }

        // Se obtienen los ítems del carrito
        $items = getCartItems($conn, $carritoId);
        if (empty($items)) {
            throw new Exception("El carrito está vacío.");
        }

        // Se calculan los totales: precio con descuento aplicado y total de descuentos
        $precioTotal = 0;
        $descuentoTotal = 0;
        // Recorro los ítems para calcular el total y el descuento
        foreach ($items as $item) {
            $precioTotal += $item['precio_total_descuento']; // Precio total con descuento
            $descuentoTotal += ($item['precio'] - $item['precio_descuento']) * $item['cantidad']; // Descuento total
        }

        // Se inserta el nuevo pedido
        $sqlPedido = $conn->prepare("
            INSERT INTO pedido (usuario_id, precio_total, descuento, creado_por) 
            VALUES (?, ?, ?, ?)
        ");
        $sqlPedido->bind_param("iddi", $usuarioId, $precioTotal, $descuentoTotal, $usuarioId);

        // Se ejecuta la consulta y se verifica si hubo error
        if (!$sqlPedido->execute()) {
            throw new Exception("Error al insertar el pedido: " . $sqlPedido->error);
        }

        // Se obtiene el ID del nuevo pedido generado y se prepara la inserción de los ítems
        $pedidoId = $conn->insert_id;
        $pedidoItem = $conn->prepare("
            INSERT INTO pedido_item (pedido_id, producto_id, plataforma_id, cantidad, precio_total) 
            VALUES (?, ?, ?, ?, ?)
        ");

        // Se insertan los ítems uno por uno, consumiendo también el stock reservado
        foreach ($items as $item) {
            $pedidoItem->bind_param(
                "iiiid",
                $pedidoId,
                $item['producto_id'],
                $item['plataforma_id'],
                $item['cantidad'],
                $item['precio_total_descuento']
            );

            // Se consume el stock reservado para el ítem
            consumeReservedStock($conn, $item['producto_id'], $item['plataforma_id'], $item['cantidad']);

            // Se ejecuta la inserción del ítem y se verifica si hubo error
            if (!$pedidoItem->execute()) {
                throw new Exception("Error al insertar ítem del pedido: " . $pedidoItem->error);
            }
        }

        // Se vacía el carrito una vez completado el pedido
        $vaciarCarrito = $conn->prepare("DELETE FROM carrito_item WHERE carrito_id = ?");
        $vaciarCarrito->bind_param("i", $carritoId);

        // Se ejecuta la eliminación de los ítems del carrito y se verifica si hubo error
        if (!$vaciarCarrito->execute()) {
            throw new Exception("Error al vaciar el carrito: " . $vaciarCarrito->error);
        }

        $conn->commit();
        return $pedidoId;
    } catch (Exception $e) {
        // Se revierte la transacción en caso de error
        $conn->rollback();
        error_log("Error en createOrder: " . $e->getMessage());
        return false;
    } finally {
        cerrar_conexion($conn);
    }
}

/**
 * Esta función registra los datos de facturación de un pedido, guardando solo los últimos 4 dígitos de la tarjeta.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $usuarioId ID del usuario que realiza la compra.
 * @param int $pedidoId ID del pedido asociado.
 * @param string $nombre Nombre completo para la facturación.
 * @param string $email Correo electrónico de facturación.
 * @param string $direccion Dirección de facturación.
 * @param string $pais País de facturación.
 * @param string|null $numero_tarjeta Número de tarjeta (opcional, solo se guardan los últimos 4 dígitos).
 * @param string|null $vencimiento_tarjeta Fecha de vencimiento de la tarjeta (opcional).
 * @return array Devuelvo un array con el resultado de la operación y un mensaje.
 */
function addBilling($conn, $usuarioId, $pedidoId, $nombre, $email, $direccion, $pais, $numero_tarjeta = null, $vencimiento_tarjeta = null)
{
    try {
        $ultimos4 = null;
        // Si se ingresó un número de tarjeta, se obtienen solo los últimos 4 dígitos (quitando cualquier carácter no numérico)
        if (!empty($numero_tarjeta)) {
            $ultimos4 = substr(preg_replace('/\D/', '', $numero_tarjeta), -4);
        }

        // Se prepara la consulta para insertar los datos de facturación
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
        // Se vinculan los parámetros a la consulta
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

        // Se ejecuta la consulta y se retorna el resultado
        if ($query->execute()) {
            return ['success' => true, 'message' => 'Facturación registrada correctamente.'];
        } else {
            return ['success' => false, 'message' => 'Error al insertar la facturación: ' . $query->error];
        }
    } catch (Exception $e) {
        // Si ocurre una excepción, se retorna el mensaje de error
        return ['success' => false, 'message' => 'Excepción: ' . $e->getMessage()];
    }
}

/**
 * Esta función marca un pedido como "entregado" y registra la fecha de envío.
 *
 * @param int $pedidoId ID del pedido a marcar como entregado.
 * @return bool Devuelvo true si la actualización fue exitosa, false si no.
 */
function markOrderShipped($pedidoId)
{
    $conn = conexion();
    // Actualizo el estado del pedido a 'entregado' y registro la fecha de envío como NOW()
    $query = $conn->prepare("UPDATE pedido SET estado = 'entregado', fecha_envio = NOW() WHERE id = ?");
    $query->bind_param("i", $pedidoId);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);
    return $resultado;
}

/**
 * Esta función marca un pedido como "cancelado".
 *
 * @param int $pedidoId ID del pedido a cancelar.
 * @return bool Devuelvo true si la actualización fue exitosa, false si no.
 */
function markOrderCancelled($pedidoId)
{
    $conn = conexion();
    // Actualizo el estado del pedido a 'cancelado'
    $query = $conn->prepare("UPDATE pedido SET estado = 'cancelado' WHERE id = ?");
    $query->bind_param("i", $pedidoId);
    $resultado = $query->execute();
    $query->close();
    cerrar_conexion($conn);
    return $resultado;
}

/**
 * Esta función obtiene todos los pedidos de un usuario, agrupando los productos de cada pedido.
 *
 * @param int $usuarioId ID del usuario.
 * @return array Devuelvo un array con los pedidos y sus productos.
 */
function getOrdersByUserId($usuarioId)
{
    $conn = conexion();
    // Preparo la consulta para traer los pedidos y sus productos
    // Se hace un JOIN entre las tablas de pedido, pedido_item, producto y plataforma
    // para obtener toda la información necesaria
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
    // Recorro cada fila del resultado de la consulta de pedidos y productos
    while ($row = $result->fetch_assoc()) {
        // Si el pedido aún no está en el array $pedidos, lo creo con sus datos generales y un array vacío de productos
        if (!isset($pedidos[$row['pedido_id']])) {
            $pedidos[$row['pedido_id']] = [
                'pedido_id' => $row['pedido_id'],
                'precio_total' => $row['precio_total'],
                'estado' => $row['estado'],
                'creado_en' => $row['creado_en'],
                'productos' => []
            ];
        }
        // Agrego el producto actual al array de productos del pedido correspondiente
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
    // Devuelvo los pedidos como array indexado
    return array_values($pedidos);
}

/**
 * Esta función obtiene todos los detalles de un pedido, incluyendo usuario, facturación y productos.
 *
 * @param int $pedidoId ID del pedido.
 * @return array Devuelvo un array con los datos generales y los productos del pedido.
 */
function getOrderFullDetails($pedidoId)
{
    $conn = conexion();
    // Preparo la consulta para traer todos los datos del pedido y sus productos, incluyendo datos de usuario y facturación
    // Se hace un JOIN entre las tablas de pedido, usuario, facturación, pedido_item, producto y plataforma
    // para obtener toda la información necesaria
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
    $pedido = null; // Inicializo el pedido como null

    $productos = [];
    // Recorro cada fila del resultado de la consulta
    while ($row = $result->fetch_assoc()) {
        // Si el pedido aún no está definido, lo creo con sus datos generales
        // y la información de facturación
        if (!$pedido) {
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
        // Agrego el producto actual al array de productos del pedido
        // con su información específica
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

    // Devuelvo un array con el pedido y sus productos
    return [
        'pedido' => $pedido,
        'productos' => $productos
    ];
}

/**
 * Esta función obtiene todos los pedidos paginados, junto con el nombre del usuario.
 * Se usa para mostrar una lista de pedidos en el panel de administración, permitiendo paginar
 * los resultados y ver información básica de cada pedido y su usuario.
 *
 * @param int $offset Número de registros a saltar.
 * @param int $limit Cantidad máxima de registros a devolver.
 * @return array Devuelvo un array con los pedidos paginados.
 */
function getAllOrdersPaginated($offset = 0, $limit = 20)
{
    $conn = conexion();
    // Preparo la consulta para obtener los pedidos y sus datos básicos
    // Se hace un JOIN entre las tablas de pedido y usuario para obtener el nombre del usuario
    // Se ordena por la fecha de creación del pedido en orden ascendente
    // Se limita la cantidad de resultados devueltos según los parámetros de paginación
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
    // Enlazo los parámetros de paginación (offset y limit)
    $query->bind_param("ii", $offset, $limit);
    $query->execute();
    $result = $query->get_result();

    $pedidos = [];
    // Recorro los resultados y agrego cada pedido al array
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }

    $query->close();
    cerrar_conexion($conn);
    return $pedidos;
}

/**
 * Esta función obtiene el total de pedidos registrados en la base de datos.
 *
 * @return int Devuelvo la cantidad total de pedidos.
 */
function getTotalOrders()
{
    $conn = conexion();
    // Consulto la cantidad total de registros en la tabla pedido
    $result = $conn->query("SELECT COUNT(*) AS total FROM pedido");
    $row = $result->fetch_assoc(); // Obtengo el resultado como array asociativo
    cerrar_conexion($conn);
    return $row['total']; // Retorno el valor del campo 'total'
}

/**
 * Esta función obtiene los datos básicos de un pedido por su ID.
 * Se usa para mostrar información resumida de un pedido específico, incluyendo el nombre del usuario.
 *
 * @param int $pedidoId ID del pedido que quiero consultar.
 * @return array Devuelvo un array con los datos del pedido encontrado.
 */
function getOrderById($pedidoId)
{
    $conn = conexion();
    // Preparo la consulta para obtener los datos del pedido por su ID
    // Se hace un JOIN entre las tablas de pedido y usuario para obtener el nombre del usuario
    // Se ordena por la fecha de creación del pedido en orden descendente
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
    // Recorro los resultados y agrego cada pedido al array (aunque normalmente será uno solo)
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }

    $query->close();
    cerrar_conexion($conn);
    return $pedidos;
}

/* ------------- FILTROS DE BUSQUEDA ------------- */

/**
 * Esta función obtiene los productos filtrados según los parámetros recibidos.
 * Se arma dinámicamente la consulta SQL para filtrar por nombre, género, plataforma y rango de precio,
 * devolviendo solo productos activos y calculando el precio final con descuento aplicado.
 *
 * @param string $nombre Nombre del producto a buscar (opcional).
 * @param int|null $generoId ID del género para filtrar (opcional).
 * @param int|null $plataformaId ID de la plataforma para filtrar (opcional).
 * @param float|null $precioMin Precio mínimo para filtrar (opcional).
 * @param float|null $precioMax Precio máximo para filtrar (opcional).
 * @return array Devuelvo un array con los productos filtrados según los parámetros.
 */
function getFilteredProducts($nombre = '', $generoId = null, $plataformaId = null, $precioMin = null, $precioMax = null)
{
    $conn = conexion();

    // Armo la consulta base para traer los productos y calcular el precio final con descuento
    // Se hace un LEFT JOIN con las tablas de género, plataforma y stock para obtener toda la información necesaria
    // Se usa LEFT JOIN para que no se excluyan productos que no tengan género o plataforma asignados
    $query = "
        SELECT p.*, (p.precio - (p.precio * IFNULL(p.descuento, 0) / 100)) AS precio_final
        FROM producto p
        LEFT JOIN producto_genero pg ON pg.producto_id = p.id
        LEFT JOIN producto_plataforma pp ON pp.producto_id = p.id
        LEFT JOIN producto_stock ps ON ps.producto_id = p.id
    ";

    $conditions = []; // Inicializo un array para las condiciones de filtrado
    $types = ''; // Inicializo una cadena para los tipos de parámetros
    $params = []; // Inicializo un array para los parámetros de la consulta

    // Solo traigo productos activos
    $conditions[] = "p.activo = 1";

    // Si se indica un nombre, filtro por coincidencia parcial
    if (!empty($nombre)) {
        $conditions[] = "p.nombre LIKE ?";
        $types .= 's';
        $params[] = "%$nombre%";
    }

    // Si se indica un género, filtro por ese género
    if ($generoId !== null) {
        $conditions[] = "pg.genero_id = ?";
        $types .= 'i';
        $params[] = $generoId;
    }

    // Si se indica una plataforma, filtro por esa plataforma
    if ($plataformaId !== null) {
        $conditions[] = "pp.plataforma_id = ?";
        $types .= 'i';
        $params[] = $plataformaId;
    }

    // Si se indica un precio mínimo, filtro por precio final mayor o igual
    if ($precioMin !== null) {
        $conditions[] = "(p.precio - (p.precio * IFNULL(p.descuento, 0) / 100)) >= ?";
        $types .= 'd';
        $params[] = $precioMin;
    }

    // Si se indica un precio máximo, filtro por precio final menor o igual
    if ($precioMax !== null) {
        $conditions[] = "(p.precio - (p.precio * IFNULL(p.descuento, 0) / 100)) <= ?";
        $types .= 'd';
        $params[] = $precioMax;
    }

    // Si hay condiciones, las uno con AND
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    // Agrupo por ID de producto para evitar duplicados
    $query .= " GROUP BY p.id";

    // Preparo la consulta
    $stmt = $conn->prepare($query);

    // Si hay parámetros, los uno a la consulta
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    // Ejecuto la consulta y armo el array de productos
    $stmt->execute();
    $result = $stmt->get_result();
    $productos = [];

    // Recorro los resultados y agrego cada producto al array
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }

    $stmt->close();
    cerrar_conexion($conn);
    return $productos; // Retorno el array de productos filtrados
}

/* ------------- DASHBOARD -------------  */

/**
 * Esta función obtiene un total genérico (count o sum) de una tabla, con opción de filtro.
 * Se usa para obtener estadísticas rápidas de cualquier tabla y campo.
 *
 * @param string $tabla Nombre de la tabla.
 * @param string $campo Campo a contar o sumar (por defecto 'id').
 * @param string $tipo Tipo de operación: 'count' o 'sum'.
 * @param string $where Condición WHERE opcional.
 * @return int Devuelvo el total calculado o 0 si no hay resultados.
 */
function obtenerTotalGenerico($tabla, $campo = 'id', $tipo = 'count', $where = '')
{
    $conn = conexion();
    
    // Armo la consulta según el tipo solicitado
    // Si el tipo no es 'sum', lo cambio a 'count' por defecto
    $sql = ($tipo === 'sum')
        ? "SELECT SUM($campo) AS total FROM $tabla"
        : "SELECT COUNT($campo) AS total FROM $tabla";

    // Si hay condición, la agrego
    if ($where) $sql .= " WHERE $where";
    $result = $conn->query($sql); // Ejecuto la consulta
    $row = $result->fetch_assoc(); // Obtengo el resultado como array asociativo
    cerrar_conexion($conn);
    return $row['total'] ?? 0; // Retorno el total o 0 si no hay resultado
}

/**
 * Esta función obtiene la suma de un campo de una tabla para los últimos X días.
 * Se usa para estadísticas de ventas, ingresos, etc. en un rango reciente.
 *
 * @param string $tabla Nombre de la tabla.
 * @param string $campo Campo a sumar.
 * @param int $dias Cantidad de días hacia atrás.
 * @param string $fechaCampo Nombre del campo de fecha (por defecto 'creado_en').
 * @return float Devuelvo la suma calculada o 0 si no hay resultados.
 */
function obtenerSumaUltimosDias($tabla, $campo, $dias, $fechaCampo = 'creado_en')
{
    $conn = conexion();
    // Armo la consulta para sumar el campo en los últimos X días
    $sql = "SELECT SUM($campo) AS total FROM $tabla WHERE $fechaCampo >= DATE_SUB(CURDATE(), INTERVAL $dias DAY)";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    cerrar_conexion($conn);
    return $row['total'] ?? 0; // Retorno la suma o 0 si no hay resultado
}

/**
 * Esta función obtiene la suma de un campo agrupada por mes, opcionalmente en los últimos X días.
 * Se usa para mostrar gráficos de evolución mensual.
 *
 * @param string $tabla Nombre de la tabla.
 * @param string $campo Campo a sumar.
 * @param string $fechaCampo Campo de fecha (por defecto 'creado_en').
 * @param int|null $dias Si se indica, filtra solo los últimos X días.
 * @return array Devuelvo un array con los nombres de los meses, los valores y el total.
 */
function obtenerSumaPorMes($tabla, $campo, $fechaCampo = 'creado_en', $dias = null)
{
    $conn = conexion();
    // Armo la consulta para sumar el campo agrupado por mes
    $sql = "
        SELECT 
            MONTH($fechaCampo) AS mes,
            SUM($campo) AS total
        FROM $tabla
    ";

    // Si se indica un rango de días, lo agrego a la consulta
    if ($dias !== null) {
        $sql .= " WHERE $fechaCampo >= DATE_SUB(CURDATE(), INTERVAL $dias DAY)";
    }

    // Agrupo por mes y ordeno
    $sql .= " GROUP BY mes ORDER BY mes";
    $result = $conn->query($sql);
    $meses = []; // Creo un array para almacenar los nombres de los meses
    $valores = []; // Creo un array para almacenar los valores
    $total = 0; // Inicializo el total en 0

    // Defino los nombres de los meses en español
    $meses_es = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];

    // Recorro los resultados y armo los arrays de meses y valores
    while ($row = $result->fetch_assoc()) {
        $mesNombre = $meses_es[(int)$row['mes']] ?? $row['mes']; // Obtengo el nombre del mes
        $meses[] = $mesNombre; // Agrego el nombre del mes al array
        $valores[] = (float)$row['total']; // Agrego el total al array de valores
        $total += $row['total']; // Acumulo el total
    }
    cerrar_conexion($conn);
    return ['meses' => $meses, 'valores' => $valores, 'total' => $total]; // Retorno un array con los meses, valores y el total
}

/**
 * Esta función obtiene la cantidad de usuarios nuevos por mes.
 * Se usa para mostrar la evolución de registros de usuarios.
 *
 * @param string $fechaCampo Campo de fecha de creación (por defecto 'creado_en').
 * @return array Devuelvo un array con los nombres de los meses y las cantidades.
 */
function obtenerUsuariosNuevosPorMes($fechaCampo = 'creado_en')
{
    $conn = conexion();

    // Armo la consulta para contar los usuarios nuevos por mes. Se filtran por rol 'user'
    $sql = "SELECT MONTH($fechaCampo) AS mes, COUNT(*) AS cantidad FROM usuario WHERE rol = 'user' GROUP BY mes ORDER BY mes";
    $result = $conn->query($sql);
    $meses = []; // Creo un array para almacenar los nombres de los meses
    $cantidades = []; // Creo un array para almacenar las cantidades

    // Defino los nombres de los meses en español
    $meses_es = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];
    // Recorro los resultados y armo los arrays de meses y cantidades
    while ($row = $result->fetch_assoc()) {
        $meses[] = $meses_es[(int)$row['mes']]; // Guardo el nombre del mes en el array
        $cantidades[] = (int)$row['cantidad']; // Agrego la cantidad al array
    }
    cerrar_conexion($conn);
    return ['meses' => $meses, 'cantidades' => $cantidades]; // Retorno un array con los meses y cantidades
}

/**
 * Esta función obtiene los productos más vendidos, ordenados por cantidad.
 * Se usa para mostrar rankings o gráficos de productos populares.
 *
 * @param int $limite Cantidad máxima de productos a devolver.
 * @return array Devuelvo un array con los nombres y cantidades vendidas.
 */
function obtenerTopProductosVendidos($limite = 5)
{
    $conn = conexion();
    
    // Armo la consulta para sumar la cantidad vendida por producto
    // Se hace un JOIN entre pedido_item y producto para obtener el nombre del producto
    $sql = "SELECT p.nombre, SUM(pi.cantidad) AS total_vendidos
            FROM pedido_item pi
            JOIN producto p ON pi.producto_id = p.id
            GROUP BY pi.producto_id
            ORDER BY total_vendidos DESC
            LIMIT $limite";
    $result = $conn->query($sql);
    $nombres = []; // Creo un array para almacenar los nombres de los productos
    $cantidades = []; // Creo un array para almacenar las cantidades vendidas

    // Recorro los resultados y armo los arrays de nombres y cantidades
    while ($row = $result->fetch_assoc()) {
        $nombres[] = $row['nombre']; // Guardo el nombre del producto en el array
        $cantidades[] = (int)$row['total_vendidos']; // Guardo la cantidad vendida en el array
    }
    cerrar_conexion($conn);
    return ['nombres' => $nombres, 'cantidades' => $cantidades]; // Retorno un array con los nombres y cantidades
}

/**
 * Esta función obtiene las plataformas más vendidas, ordenadas por cantidad.
 * Se usa para mostrar en el grafico para que plataformas se vendieron más productos. 
 *
 * @param int $limite Cantidad máxima de plataformas a devolver.
 * @return array Devuelvo un array con los nombres y cantidades vendidas.
 */
function obtenerTopPlataformasVendidas($limite = 5)
{
    $conn = conexion();
    // Armo la consulta para sumar la cantidad vendida de productos por plataforma
    // Se hace un JOIN entre pedido_item y plataforma para obtener el nombre de la plataforma
    $sql = "SELECT pl.nombre, SUM(pi.cantidad) AS total_vendidos
            FROM pedido_item pi
            JOIN plataforma pl ON pi.plataforma_id = pl.id
            GROUP BY pi.plataforma_id
            ORDER BY total_vendidos DESC
            LIMIT $limite";
    $result = $conn->query($sql);
    $nombres = [];
    $cantidades = [];

    // Recorro los resultados y armo los arrays de nombres y cantidades
    while ($row = $result->fetch_assoc()) {
        $nombres[] = $row['nombre']; // Guardo el nombre de la plataforma en el array
        $cantidades[] = (int)$row['total_vendidos']; // Guardo la cantidad vendida en el array
    }
    cerrar_conexion($conn);
    return ['nombres' => $nombres, 'cantidades' => $cantidades]; // Retorno un array con los nombres y cantidades
}

/**
 * Esta función obtiene los usuarios que más compras realizaron.
 * Se usa para mostrar rankings de compradores frecuentes.
 *
 * @param int $limite Cantidad máxima de usuarios a devolver.
 * @return array Devuelvo un array con los nombres y la cantidad de compras.
 */
function obtenerTopUsuariosCompradores($limite = 5)
{
    $conn = conexion();
    // Armo la consulta para contar la cantidad de compras por usuario
    // Se hace un JOIN entre pedido y usuario para obtener el nombre del usuario
    $sql = "SELECT u.nombre, COUNT(p.id) AS total_compras
            FROM pedido p
            JOIN usuario u ON p.usuario_id = u.id
            GROUP BY p.usuario_id
            ORDER BY total_compras DESC
            LIMIT $limite";
    $result = $conn->query($sql);
    $nombres = [];
    $cantidades = [];

    // Recorro los resultados y armo los arrays de nombres y cantidades
    while ($row = $result->fetch_assoc()) {
        $nombres[] = $row['nombre']; // Guardo el nombre del usuario en el array
        $cantidades[] = (int)$row['total_compras']; // Guardo la cantidad de compras en el array
    }
    cerrar_conexion($conn);
    return ['nombres' => $nombres, 'cantidades' => $cantidades]; // Retorno un array con los nombres y cantidades
}