<?php

session_start(); // Inicio la sesión para manejar datos de usuario/logueo

// Incluyo los archivos necesarios para la conexión a la base de datos,
// las funciones de consulta y las validaciones
require_once '../database/connection.php';
require_once '../database/querys.php';
require_once 'validations.php';

// Verifico si la solicitud es POST antes de procesar cualquier acción
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? null; // Obtengo la acción enviada desde el formulario o AJAX

    // Procesar la acción recibida
    switch ($accion) {
        case 'registrar_usuario':
            // Obtengo los datos del usuario desde el formulario
            $username = $_POST['username'] ?? null;
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            $confirmPassword = $_POST['confirm_password'] ?? null;
            $errores = [];

            // Valido los datos básicos del usuario
            $validaciones = [
                validateData('string', $username, 'nombre de usuario'),
                validateData('email', $email, 'correo electrónico'),
                validateData('password', $password, 'contraseña')
            ];

            // Si alguna validación falla, agrego el mensaje de error al array
            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            // Verifico que las contraseñas coincidan
            if ($password !== $confirmPassword) {
                $errores[] = "Las contraseñas no coinciden.";
            }

            // Si hay errores, redirijo al registro con los mensajes
            if (!empty($errores)) {
                header("Location: ../views/user/register.php?errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            // Si todo está bien, creo el usuario en la base de datos
            $usuarioCreado = createUser($username, $username, $email, $password);
            exit;

        case 'iniciar_sesion':
            // Obtengo los datos del formulario de login
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            $checkbox = isset($_POST['admin']); // Si está marcado, es admin
            $errores = [];

            // Valido el email
            $validaciones = [
                validateData('email', $email, 'correo electrónico')
            ];

            // Si alguna validación falla, agrego el mensaje de error al array
            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            // Si hay errores de validación, redirijo al login
            if (!empty($errores)) {
                header("Location: ../views/user/login.php?errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            if (!$checkbox) {
                // Si no es admin, busco el usuario con rol "user"
                $user = getUserData($email, 'user');

                if ($user) {
                    if ($user['activo'] != 1) {
                        header("Location: ../views/user/login.php?errores=La+cuenta+está+desactivada.+Contacta+al+soporte.");
                        exit();
                    }
                    // Verifico la contraseña con hash
                    if (password_verify($password, $user['pass'])) {
                        login($user); // Guardo datos en sesión
                        header("Location: ../views/user/catalog.php?Inicio+exitoso");
                        exit();
                    } else {
                        header("Location: ../views/user/login.php?errores=Credenciales+inválidas");
                        exit();
                    }
                } else {
                    header("Location: ../views/user/login.php?errores=Credenciales+inválidas");
                    exit();
                }
            } else {
                // Si es admin, busco el usuario con rol admin
                $admin = getAdminData($email, 'admin');

                if ($admin) {
                    login($admin); // Guardo datos en sesión
                    header("Location: ../views/admin/dashboard.php?Inicio+exitoso");
                    exit();
                } else {
                    header("Location: ../views/user/login.php?errores=Credenciales+inválidas");
                    exit();
                }
            }
            break;

        case 'actualizar_perfil':
            header('Content-Type: application/json'); // La respuesta será JSON para AJAX

            $userId = $_SESSION['usuario']['id']; // ID del usuario logueado (de la sesión)
            $user = $_SESSION['usuario'];         // Datos actuales del usuario

            // Obtengo los datos enviados desde el formulario de perfil (con trim para limpiar espacios)
            $nombre = trim($_POST['nombre'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $fecha_nac = trim($_POST['fecha_nac'] ?? '');
            $cp = trim($_POST['cp'] ?? '');
            $imagen = $_FILES['imagen_perfil'] ?? null; // Imagen de perfil (si se subió)
            $errores = [];

            // Validaciones de todos los campos del perfil (usa funciones personalizadas)
            $validaciones = [
                validateData('string', $nombre, 'nombre'),
                validateData('string', $username, 'nombre de usuario'),
                validateData('email', $email, 'correo electrónico'),
                validateData('telefono', $telefono, 'teléfono'),
                validateData('string', $direccion, 'dirección'),
                validateData('fecha', $fecha_nac, 'fecha de nacimiento'),
                validateData('cp', $cp, 'código postal')
            ];

            // Si alguna validación falla, devuelvo el error y corto la ejecución
            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    echo json_encode(['exito' => false, 'mensaje' => $resultado]);
                    exit;
                }
            }

            // Procesar imagen de perfil (si se subió una nueva)
            $rutaImagenFinal = $user['imagen_perfil'] ?? null; // Por defecto, la actual

            if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
                // Valido la imagen (tipo, tamaño, etc.)
                $resultadoImagen = validateImage($imagen);
                if ($resultadoImagen !== true) {
                    echo json_encode(['exito' => false, 'mensaje' => $resultadoImagen]);
                    exit;
                }

                // Elimino todas las imágenes anteriores del usuario (por si cambia la foto)
                $pattern = '../images/profiles/perfil_' . $userId . '_*.*';
                foreach (glob($pattern) as $oldFile) {
                    if (file_exists($oldFile)) {
                        unlink($oldFile); // Borro cada archivo anterior
                    }
                }

                $nombreTemporal = $imagen['tmp_name']; // Archivo temporal subido
                $nombreOriginal = basename($imagen['name']); // Nombre original del archivo
                $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION); // Extensión del archivo

                // Creo un nombre único para la imagen (evita conflictos)
                $nombreArchivo = 'perfil_' . $userId . '_' . time() . '.' . $extension;
                $directorio = '../images/profiles/';
                $rutaDestino = $directorio . $nombreArchivo;

                // Si el directorio no existe, lo creo
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0755, true);
                }

                // Muevo la imagen subida a la carpeta final
                if (move_uploaded_file($nombreTemporal, $rutaDestino)) {
                    $rutaImagenFinal = 'images/profiles/' . $nombreArchivo; // Ruta relativa para guardar en BD
                } else {
                    echo json_encode(['exito' => false, 'mensaje' => 'Error al guardar la imagen.']);
                    exit;
                }
            } else {
                // Si no hay imagen nueva, mantengo la actual
                $rutaImagenFinal = $user['imagen_perfil'];
            }

            // Actualizo el perfil en la base de datos
            $conn = conexion();
            $success = updateUserProfile($conn, $userId, $nombre, $username, $email, $telefono, $direccion, $fecha_nac, $cp, $rutaImagenFinal);
            cerrar_conexion($conn);

            // Devuelvo el resultado como JSON (éxito o error)
            echo json_encode(['exito' => $success, 'mensaje' => $success ? 'Perfil actualizado correctamente.' : 'Error al actualizar el perfil.']);
            exit;

        case 'cambiar_contraseña':
            header('Content-Type: application/json'); // La respuesta será JSON para AJAX

            $userId = $_SESSION['usuario']['id']; // ID del usuario logueado
            $currentPassword = $_POST['current_password'] ?? ''; // Contraseña actual ingresada por el usuario
            $newPassword = $_POST['new_password'] ?? '';         // Nueva contraseña
            $confirmPassword = $_POST['confirm_password'] ?? ''; // Confirmación de la nueva contraseña

            // Validación de la nueva contraseña (usa función personalizada)
            $validaciones = [
                validateData('password', $newPassword, 'nueva contraseña')
            ];

            // Si alguna validación falla, devuelvo el error y corto la ejecución
            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    echo json_encode(['exito' => false, 'mensaje' => $resultado]);
                    exit;
                }
            }

            // Verificar que las contraseñas coincidan
            if ($newPassword !== $confirmPassword) {
                echo json_encode(['exito' => false, 'mensaje' => 'Las contraseñas no coinciden.']);
                exit;
            }

            $conn = conexion();
            $hashedPassword = getCurrentPassword($conn, $userId); // Traigo la contraseña actual (en hash o texto plano)

            // Detectar si la contraseña está en texto plano (no es hash)
            $isPlain = (strpos($hashedPassword, '$2y$') !== 0);

            // Si es admin y la contraseña es texto plano, comparar directo
            if ($_SESSION['usuario']['rol'] === 'admin' && $isPlain) {
                $isPasswordCorrect = ($currentPassword === $hashedPassword); // Comparo texto plano
                $isSameAsNew = ($newPassword === $hashedPassword);           // Verifico si la nueva es igual a la actual
            } else {
                $isPasswordCorrect = password_verify($currentPassword, $hashedPassword); // Comparo usando hash
                $isSameAsNew = password_verify($newPassword, $hashedPassword);           // Verifico si la nueva es igual a la actual (hash)
            }

            // Si la contraseña actual no es correcta, aviso y corto
            if (!$hashedPassword || !$isPasswordCorrect) {
                echo json_encode(['exito' => false, 'mensaje' => 'La contraseña actual es incorrecta.']);
                cerrar_conexion($conn);
                exit;
            }

            // Se valida que la nueva contraseña no sea igual a la actual
            if ($isSameAsNew) {
                echo json_encode(['exito' => false, 'mensaje' => 'La nueva contraseña no puede ser igual a la actual.']);
                cerrar_conexion($conn);
                exit;
            }

            // Encripto la nueva contraseña y la guardo en la base de datos
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $success = updatePassword($conn, $userId, $newHashedPassword);
            cerrar_conexion($conn);

            // Devuelvo el resultado como JSON (éxito o error)
            echo json_encode(['exito' => $success, 'mensaje' => $success ? 'Contraseña actualizada correctamente.' : 'Error al actualizar la contraseña.']);
            exit;

        case 'agregar_producto':
            // Se obtiene los datos del producto desde el formulario (POST)
            $nombre = $_POST['name'] ?? null;
            $descripcion = $_POST['description'] ?? null;
            $fecha_lanzamiento = $_POST['release_date'] ?? null;
            $precio = $_POST['price'] ?? null;
            $descuento = $_POST['discount'] ?? null;
            $plataformas = $_POST['plataformas'] ?? [];
            $generos = $_POST['generos'] ?? [];
            $stock_por_plataforma = $_POST['stock'] ?? [];
            $imagen = $_FILES['image'] ?? null;
            $errores = [];

            // Solo un admin puede agregar productos, obtengo su ID si corresponde
            $admin_id = ($_SESSION['usuario']['rol'] === 'admin') ? $_SESSION['usuario']['id'] : null;

            // Validación de datos obligatorios del producto
            $validaciones = [
                validateData('string', $nombre, 'nombre'),
                validateData('string', $descripcion, 'descripción'),
                validateData('fecha', $fecha_lanzamiento, 'fecha de lanzamiento'),
                validateData('numero', $precio, 'precio'),
            ];

            // Si alguna validación falla, agrego el mensaje de error al array
            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            // Se valida que se haya seleccionado al menos un género y una plataforma
            if (empty($generos)) {
                $errores[] = "Debes seleccionar al menos un género.";
            }
            if (empty($plataformas)) {
                $errores[] = "Debes seleccionar al menos una plataforma.";
            }

            // Se valida el stock para cada plataforma seleccionada
            foreach ($stock_por_plataforma as $plataformaId => $stock) {
                $validacionStock = validateData('numero', $stock, "stock para la plataforma ID {$plataformaId}");
                if ($validacionStock !== true) {
                    $errores[] = $validacionStock;
                }
            }

            // Se valida la imagen solo si se sube una
            if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
                $resultadoImagen = validateImage($imagen);
                if ($resultadoImagen !== true) {
                    $errores[] = $resultadoImagen;
                }
            }

            // Si hay errores, guardo los datos y errores en sesión y redirijo al formulario
            if (!empty($errores)) {
                $_SESSION['form_data'] = $_POST; // Guarda los datos enviados para repoblar el form
                $_SESSION['errores'] = $errores; // Guarda los errores para mostrarlos
                header("Location: ../views/admin/addOrModifyProduct.php");
                exit;
            }

            // Si todo está bien, creo el producto en la base de datos
            $productoCreado = createProduct(
                $nombre,
                $imagen,
                $descripcion,
                $fecha_lanzamiento,
                $generos,
                $precio,
                $descuento,
                $stock_por_plataforma,
                $plataformas,
                $admin_id,
                $admin_id
            );

            if ($productoCreado) {
                // Si se creó correctamente, redirijo a la lista de productos con mensaje de éxito
                header("Location: ../views/admin/products.php?exito=Producto+creado+correctamente");
                exit;
            }

            break;

        case 'activar_usuario':
            header('Content-Type: application/json'); // Respuesta en formato JSON para AJAX
            $usuarioId = intval($_POST['usuario_id']); // Obtengo el ID del usuario a activar

            // Intento activar el usuario en la base de datos
            if (activateUser($usuarioId)) {
                echo json_encode(['exito' => true, 'mensaje' => 'Usuario activado correctamente.']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo activar el usuario.']);
            }
            exit;

        case 'desactivar_usuario':
            // Indico que la respuesta será en formato JSON para AJAX
            header('Content-Type: application/json');

            // Obtengo el ID del usuario a desactivar desde el POST
            $usuarioId = intval($_POST['usuario_id']);

            // Obtengo el ID del usuario actualmente logueado en la sesión
            $sesionId = $_SESSION['usuario']['id'] ?? null;

            // Intento desactivar el usuario en la base de datos llamando a la función correspondiente
            if (deactivateUser($usuarioId)) {
                // Si el usuario que se desactiva es el mismo que está logueado, cierro la sesión
                if ($sesionId == $usuarioId) {
                    session_destroy(); // Solo cierra sesión si el usuario se desactiva a sí mismo
                }
                // Devuelvo una respuesta JSON de éxito
                echo json_encode(['exito' => true, 'mensaje' => 'Usuario desactivado correctamente.']);
            } else {
                // Si hubo un error al desactivar, devuelvo una respuesta JSON de error
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo desactivar el usuario.']);
            }
            // Finalizo la ejecución del script para evitar que se siga procesando
            exit;

        case 'modificar_producto':
            // Se obtienen los datos del producto desde el formulario (POST)
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['name'] ?? null;
            $descripcion = $_POST['description'] ?? null;
            $fecha_lanzamiento = $_POST['release_date'] ?? null;
            $precio = $_POST['price'] ?? null;
            $descuento = $_POST['discount'] ?? null;
            $plataformas = $_POST['plataformas'] ?? [];
            $generos = $_POST['generos'] ?? [];
            $stock_por_plataforma = $_POST['stock'] ?? [];
            $imagen = $_FILES['image'] ?? null;
            $errores = [];

            // Se obtiene el ID del usuario que está actualizando (admin)
            $actualizado_por = $_SESSION['usuario']['id'];

            // Validación de datos obligatorios del producto
            $validaciones = [
                validateData('string', $nombre, 'nombre'),
                validateData('string', $descripcion, 'descripción'),
                validateData('fecha', $fecha_lanzamiento, 'fecha de lanzamiento'),
                validateData('numero', $precio, 'precio'),
            ];

            // Si alguna validación falla, agrego el mensaje de error al array
            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            // Se valida que se haya seleccionado al menos un género y plataforma
            if (empty($generos)) {
                $errores[] = "Debes seleccionar al menos un género.";
            }
            if (empty($plataformas)) {
                $errores[] = "Debes seleccionar al menos una plataforma.";
            }

            // Se valida el stock por plataforma
            foreach ($stock_por_plataforma as $plataformaId => $stock) {
                $validacionStock = validateData('numero', $stock, "stock para la plataforma ID {$plataformaId}");
                if ($validacionStock !== true) {
                    $errores[] = $validacionStock;
                }
            }

            // Validación de imagen (si se sube una nueva)
            if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
                $resultadoImagen = validateImage($imagen);
                if ($resultadoImagen !== true) {
                    $errores[] = $resultadoImagen;
                } else {
                    // Se eliminan TODAS las imágenes anteriores del producto (cualquier extensión)
                    $pattern = '../images/products/' . $id . '_*.*';
                    foreach (glob($pattern) as $oldFile) {
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    // Guardar la nueva imagen con nombre único
                    $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
                    $nombreImagen = $id . '_' . time() . '.' . $extension;
                    $rutaDestino = '../images/products/' . $nombreImagen;
                    move_uploaded_file($imagen['tmp_name'], $rutaDestino);
                    $imagen = 'images/products/' . $nombreImagen;
                }
            } else {
                // Si no se sube una nueva imagen, mantenemos la imagen existente
                $productoExistente = getProductById($conn, $id);
                $imagen = $productoExistente['imagen'];
            }

            // Si hay errores, se redirige al formulario con los mensajes
            if (!empty($errores)) {
                header("Location: ../views/admin/addOrModifyProduct.php?id=" . $id . "&errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            // Se actualiza el producto en la base de datos
            modifyProduct(
                $id,
                $nombre,
                $imagen,
                $descripcion,
                $fecha_lanzamiento,
                $generos,              // Array de géneros
                $precio,
                $descuento,
                $stock_por_plataforma, // Array de stock por plataforma
                $plataformas,          // Array de plataformas
                $actualizado_por       // El usuario que está realizando la modificación
            );

            // se redirige a la página de productos con mensaje de éxito
            header("Location: ../views/admin/products.php?exito=Producto+modificado+correctamente");
            exit;

        case 'desactivar_producto':
            header('Content-Type: application/json'); // Respuesta en JSON para AJAX
            $id = intval($_POST['id']); // ID del producto a desactivar

            // Intento desactivar el producto en la base de datos
            if (deactivateProduct($id)) {
                echo json_encode(['exito' => true, 'mensaje' => 'Producto desactivado con éxito.']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'Hubo un error al desactivar el producto.']);
            }
            exit;

        case 'activar_producto':
            header('Content-Type: application/json'); // Respuesta en JSON para AJAX
            $id = intval($_POST['id']); // ID del producto a activar

            // Intento activar el producto en la base de datos
            if (activateProduct($id)) {
                echo json_encode(['exito' => true, 'mensaje' => 'Producto activado con éxito.']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'Hubo un error al activar el producto.']);
            }
            exit;

        case 'agregar_favorito':
            header('Content-Type: application/json'); // Respuesta en JSON para AJAX

            // Verifico que el usuario esté logueado
            if (!isset($_SESSION['usuario']['id'])) {
                echo json_encode(['success' => false, 'error' => 'unauthorized']);
                exit;
            }

            $usuarioId = $_SESSION['usuario']['id']; // ID del usuario logueado
            $productoId = $_POST['producto_id'] ?? null; // ID del producto a agregar/quitar de favoritos

            // Si no se envió el producto, devuelvo error
            if (!$productoId) {
                echo json_encode(['success' => false, 'error' => 'missing_product_id']);
                exit;
            }

            // Llama a la función que agrega o quita el favorito (toggle)
            $esFavorito = addOrRemoveFav($usuarioId, $productoId);
            echo json_encode(['success' => true, 'favorito' => $esFavorito]);
            break;

        case 'obtener_stock':
            header('Content-Type: application/json'); // Respuesta en JSON para AJAX

            $productoId = $_POST['producto_id'] ?? null;    // ID del producto a consultar
            $plataformaId = $_POST['plataforma_id'] ?? null; // ID de la plataforma a consultar

            // Si falta algún dato, devuelvo error
            if (!$productoId || !$plataformaId) {
                echo json_encode(['success' => false, 'error' => 'missing_data']);
                exit;
            }

            $conn = conexion();
            $stock = getAvailableStock($conn, $productoId, $plataformaId); // Consulto el stock disponible
            cerrar_conexion($conn);

            echo json_encode(['success' => true, 'stock' => $stock]);
            break;

        case 'agregar_producto_carrito':
            header('Content-Type: application/json'); // Respuesta en JSON para AJAX

            // Verifico que el usuario esté logueado
            if (!isset($_SESSION['usuario']['id'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debes iniciar sesión para agregar productos al carrito.']);
                exit;
            }

            $usuarioId = $_SESSION['usuario']['id']; // ID del usuario logueado
            $productoId = $_POST['producto_id'] ?? null; // ID del producto a agregar
            $plataformaId = $_POST['plataforma_id'] ?? null; // ID de la plataforma seleccionada
            $cantidad = 1; // Por defecto, se agrega 1 unidad

            // Si falta algún dato, devuelvo error
            if (!$productoId || !$plataformaId) {
                echo json_encode(['exito' => false, 'mensaje' => 'Datos inválidos.']);
                exit;
            }

            $conn = conexion();

            // Obtengo el precio final del producto (con descuento si corresponde)
            $precioUnitario = getDiscountedPrice($conn, $productoId);
            if ($precioUnitario === null) {
                echo json_encode(['exito' => false, 'mensaje' => 'Producto no encontrado o inválido.']);
                cerrar_conexion($conn);
                exit;
            }

            // Intento agregar el producto al carrito
            $resultado = addProductToCart($conn, $usuarioId, $productoId, $plataformaId, $cantidad, $precioUnitario);

            cerrar_conexion($conn);

            // Devuelvo el resultado (éxito, mensaje y stock restante)
            echo json_encode([
                'exito' => $resultado['exito'],
                'mensaje' => $resultado['mensaje'],
                'stock_restante' => $resultado['stock_restante'] ?? null
            ]);
            break;

        case 'actualizar_cantidad_carrito':
            header('Content-Type: application/json');

            $usuarioId = $_SESSION['usuario']['id'];
            $carritoItemId = $_POST['carrito_item_id'] ?? null; // ID del ítem del carrito a modificar
            $cantidad = intval($_POST['cantidad'] ?? 1);        // Nueva cantidad deseada

            // Valido que los datos sean correctos
            if (!$carritoItemId || $cantidad < 1 || $cantidad > 10) {
                echo json_encode(['exito' => false, 'mensaje' => 'Datos inválidos.']);
                exit;
            }

            $conn = conexion();
            // Obtengo el carrito y el ítem a modificar
            $carritoId = getActiveCartId($conn, $usuarioId);
            $item = getCartItemById($conn, $carritoItemId);

            if (!$item) {
                cerrar_conexion($conn);
                echo json_encode(['exito' => false, 'mensaje' => 'Producto no encontrado en el carrito.']);
                exit;
            }

            // Verifico el stock disponible para el producto/plataforma
            $stockDisponible = getAvailableStock($conn, $item['producto_id'], $item['plataforma_id']);
            $cantidadActual = $item['cantidad'];
            $diferencia = $cantidad - $cantidadActual; // Diferencia entre la nueva cantidad y la actual

            // Si se quiere aumentar y no hay suficiente stock, aviso
            if ($diferencia > 0 && $diferencia > $stockDisponible) {
                cerrar_conexion($conn);
                echo json_encode(['exito' => false, 'mensaje' => 'Stock insuficiente.']);
                exit;
            }

            // Actualizo cantidad y stock según corresponda
            $precioUnitario = getDiscountedPrice($conn, $item['producto_id']);
            if ($diferencia > 0) {
                // Reservo stock extra
                reserveProductStock($conn, $item['producto_id'], $item['plataforma_id'], $diferencia);
            } elseif ($diferencia < 0) {
                // Libero stock si se reduce la cantidad
                releaseProductStock($item['producto_id'], $item['plataforma_id'], abs($diferencia));
            }
            updateCartItem($conn, $carritoItemId, $cantidad, $precioUnitario);

            cerrar_conexion($conn);
            echo json_encode([
                'exito' => true,
                'mensaje' => 'Cantidad actualizada.',
                'cantidad' => $cantidad
            ]);
            exit;

        case 'eliminar_producto_carrito':
            header('Content-Type: application/json');

            $usuarioId = $_SESSION['usuario']['id'];
            $carritoItemId = $_POST['carrito_item_id'] ?? null;

            // Valido que el ID sea correcto
            if (!$carritoItemId || !is_numeric($carritoItemId)) {
                echo json_encode(['exito' => false, 'mensaje' => 'ID de producto inválido.']);
                exit;
            }

            $conn = conexion();
            $success = removeProductFromCart($conn, $carritoItemId, $usuarioId);
            cerrar_conexion($conn);

            if ($success) {
                echo json_encode(['exito' => true, 'mensaje' => 'Producto eliminado del carrito.']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo eliminar el producto del carrito.']);
            }
            exit;

        case 'obtener_resumen_carrito':
            header('Content-Type: application/json');

            $carritoId = $_POST['carrito_id'] ?? null; // ID del carrito a consultar

            // Valido que el ID sea correcto
            if (!$carritoId || !is_numeric($carritoId)) {
                echo json_encode(['exito' => false, 'mensaje' => 'ID de carrito inválido.']);
                exit;
            }

            $conn = conexion();
            $resumen = getCartSummary($conn, $carritoId); // Obtengo el resumen del carrito (productos, totales, etc)
            cerrar_conexion($conn);

            echo json_encode(['exito' => true, 'resumen' => $resumen]);
            exit;

        case 'vaciar_carrito':
            header('Content-Type: application/json');

            $usuarioId = $_SESSION['usuario']['id']; // ID del usuario logueado
            $conn = conexion();

            // Llamar a la función emptyCart para vaciar el carrito del usuario
            $success = emptyCart($conn, $usuarioId);
            cerrar_conexion($conn);

            if ($success) {
                echo json_encode(['exito' => true, 'mensaje' => 'El carrito ha sido vaciado.']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo vaciar el carrito.']);
            }
            exit;

        case 'procesar_pago':
            header('Content-Type: application/json');

            $usuarioId = $_SESSION['usuario']['id'] ?? null;

            // Obtengo los datos del formulario de pago
            $nombreCompleto = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
            $correo = isset($_POST['correo']) ? trim($_POST['correo']) : null;
            $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;
            $pais = isset($_POST['pais']) ? trim($_POST['pais']) : null;

            // Datos de la tarjeta
            $numeroTarjeta = isset($_POST['numero_tarjeta']) ? trim($_POST['numero_tarjeta']) : null;
            $nombreTarjeta = isset($_POST['nombre_tarjeta']) ? trim($_POST['nombre_tarjeta']) : null;
            $vencimiento = isset($_POST['vencimiento']) ? trim($_POST['vencimiento']) : null;

            $erroresCliente = [];
            $erroresTarjeta = [];

            // Validaciones cliente
            $clienteChecks = [
                validateData('string', $nombreCompleto, 'nombre completo'),
                validateData('email', $correo, 'correo electrónico'),
                validateData('string', $direccion, 'dirección'),
                validateData('string', $pais, 'país'),
            ];

            foreach ($clienteChecks as $res) {
                if ($res !== true) $erroresCliente[] = $res;
            }

            // Validaciones tarjeta
            $tarjetaChecks = [
                validateData('tarjeta_numero', $numeroTarjeta, 'número de tarjeta'),
                validateData('string', $nombreTarjeta, 'nombre en la tarjeta'),
                validateData('tarjeta_expiracion', $vencimiento, 'fecha de vencimiento'),
            ];

            foreach ($tarjetaChecks as $res) {
                if ($res !== true) $erroresTarjeta[] = $res;
            }

            // Si hay errores, devolverlos categorizados
            if (!empty($erroresCliente) || !empty($erroresTarjeta)) {
                echo json_encode([
                    'exito' => false,
                    'errores_cliente' => $erroresCliente,
                    'errores_tarjeta' => $erroresTarjeta
                ]);
                exit;
            }

            // Crear pedido
            $pedidoId = createOrder($usuarioId);
            if (!$pedidoId) {
                echo json_encode(['exito' => false, 'mensaje' => 'Error al crear el pedido.']);
                exit;
            }

            // Guardar facturación
            $resultadoFacturacion = addBilling(
                $conn,
                $usuarioId,
                $pedidoId,
                $nombreCompleto,
                $correo,
                $direccion,
                $pais,
                $numeroTarjeta,
                $vencimiento
            );

            if (!$resultadoFacturacion['success']) {
                echo json_encode(['exito' => false, 'mensaje' => $resultadoFacturacion['message']]);
                exit;
            }

            // Si todo fue exitoso, se redirige a la página de pedidos
            echo json_encode([
                'exito' => true,
                'redirect_url' => "../user/orderDetail.php?id=$pedidoId&mensaje=Pago+realizado+con+exito."
            ]);
            exit;

        case 'actualizar_estado_pedido':
            header('Content-Type: application/json');

            $pedidoId = intval($_POST['pedido_id']); // ID del pedido a actualizar
            $estado = $_POST['estado']; // Nuevo estado enviado desde el frontend

            // Según el estado recibido, llamo a la función correspondiente
            if ($estado === 'entregado') {
                $exito = markOrderShipped($pedidoId); // Marca el pedido como entregado
            } elseif ($estado === 'cancelado') {
                $exito = markOrderCancelled($pedidoId); // Marca el pedido como cancelado
            } else {
                $exito = false; // Estado no válido
            }

            // Devuelvo el resultado como JSON
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Estado actualizado correctamente.' : 'No se pudo actualizar el estado.'
            ]);
            exit;

        case 'buscar_productos':
            header('Content-Type: application/json');

            // Filtros recibidos desde el frontend (pueden venir vacíos)
            $nombre = trim($_POST['nombre'] ?? '');
            $genero = is_numeric($_POST['genero'] ?? '') ? (int)$_POST['genero'] : null;
            $plataforma = is_numeric($_POST['plataforma'] ?? '') ? (int)$_POST['plataforma'] : null;
            $precioMin = is_numeric($_POST['precioMin'] ?? '') ? (float)$_POST['precioMin'] : null;
            $precioMax = is_numeric($_POST['precioMax'] ?? '') ? (float)$_POST['precioMax'] : null;

            // Llama a la función que filtra productos según los parámetros recibidos
            $productos = getFilteredProducts($nombre, $genero, $plataforma, $precioMin, $precioMax);
            echo json_encode($productos);
            exit;

        case 'obtener_estadisticas':
            // Construyo el array de estadísticas para el dashboard (usuarios, productos, pedidos, ganancias, etc)
            $respuesta = [
                'usuarios' => [
                    'nuevos'  => obtenerUsuariosNuevosPorMes('fecha_creacion'),
                    'total'    => obtenerTotalGenerico('usuario', 'id', 'count', "rol = 'user'") ?? 0,
                    'activos'  => obtenerTotalGenerico('usuario', 'id', 'count', "rol = 'user' AND activo = 1") ?? 0,
                    'inactivos' => obtenerTotalGenerico('usuario', 'id', 'count', "rol = 'user' AND activo = 0") ?? 0,
                    'top'     => obtenerTopUsuariosCompradores(5),
                ],
                'productos' => [
                    'total'    => obtenerTotalGenerico('producto', 'id', 'count') ?? 0,
                    'activos'  => obtenerTotalGenerico('producto', 'id', 'count', "activo = 1") ?? 0,
                    'inactivos' => obtenerTotalGenerico('producto', 'id', 'count', "activo = 0") ?? 0,
                    'top'     => obtenerTopProductosVendidos(5),
                ],
                'pedidos' => [
                    'total'      => obtenerTotalGenerico('pedido', 'id', 'count') ?? 0,
                    'pendientes' => obtenerTotalGenerico('pedido', 'id', 'count', "estado = 'pendiente'") ?? 0,
                    'entregados' => obtenerTotalGenerico('pedido', 'id', 'count', "estado = 'entregado'") ?? 0,
                    'cancelados' => obtenerTotalGenerico('pedido', 'id', 'count', "estado = 'cancelado'") ?? 0,
                ],
                'ganancias' => [
                    'total'                  => obtenerTotalGenerico('pedido', 'precio_total', 'sum') ?? 0,
                    'ganancias_semanales'    => obtenerSumaUltimosDias('pedido', 'precio_total', 7, 'creado_en') ?? 0,
                    'ganancias_mensuales'    => obtenerSumaUltimosDias('pedido', 'precio_total', 30, 'creado_en') ?? 0,
                    'ganancias_ultimos_3_meses' => obtenerSumaUltimosDias('pedido', 'precio_total', 90, 'creado_en') ?? 0,
                    'ganancias_anuales'      => obtenerSumaUltimosDias('pedido', 'precio_total', 365, 'creado_en') ?? 0,
                    'mensual'                => obtenerSumaPorMes('pedido', 'precio_total', 'creado_en') ?? 0,
                ],
                'top_plataformas' => obtenerTopPlataformasVendidas(5)
            ];
            echo json_encode($respuesta);
            exit;

        default:
            // Si no se reconoce la acción, redirijo a una página de error
            header("Location: ../views/user/error.php");
            break;
    }
}
