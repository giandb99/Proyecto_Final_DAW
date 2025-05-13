<?php

session_start();
require_once '../database/connection.php';
require_once '../database/querys.php';
require_once 'validations.php';

// Verificar si la solicitud es POST para procesar acciones específicas
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? null; // Obtener la acción enviada por el formulario

    // Procesar la acción según su valor
    switch ($accion) {
        case 'registrar_usuario':
            // Obtener los datos del usuario
            $username = $_POST['username'] ?? null;
            $name = $_POST['username'] ?? null;
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            $confirmPassword = $_POST['confirm_password'] ?? null;
            $errores = [];

            // Validaciones
            $validaciones = [
                validateData('string', $username, 'nombre de usuario'),
                validateData('email', $email, 'correo electrónico'),
                validateData('password', $password, 'contraseña')
            ];

            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            // Verificar que las contraseñas coincidan
            if ($password !== $confirmPassword) {
                $errores[] = "Las contraseñas no coinciden.";
            }

            // Redirigir si hay errores
            if (!empty($errores)) {
                header("Location: ../views/user/register.php?errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            // Crear el usuario
            $usuarioCreado = createUser($name, $username, $email, $password);
            exit;

        case 'iniciar_sesion':
            // Se capturan los datos del formulario
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            $checkbox = isset($_POST['admin']); // Verificar si el checkbox de admin está marcado
            $errores = [];

            // Validaciones
            $validaciones = [
                validateData('email', $email, 'correo electrónico')
            ];

            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            // Si hay errores de validación, redirigir
            if (!empty($errores)) {
                header("Location: ../views/user/login.php?errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            if (!$checkbox) {
                // Si no es admin, buscar al usuario con rol "user"
                $user = getUserData($email, 'user');

                if ($user) {
                    // Verificar las contraseñas
                    if (password_verify($password, $user['pass'])) {
                        login($user);
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
                $admin = getAdminData($email, $password);

                if ($admin) {
                    login($admin);
                    header("Location: ../views/admin/dashboard.php?Inicio+exitoso");
                    exit();
                } else {
                    header("Location: ../views/user/login.php?errores=Credenciales+inválidas");
                    exit();
                }
            }
            break;

        case 'actualizar_perfil':
            header('Content-Type: application/json');

            if (!isset($_SESSION['usuario']['id'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debes iniciar sesión.']);
                exit;
            }

            $userId = $_SESSION['usuario']['id'];
            $nombre = $_POST['nombre'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $direccion = $_POST['direccion'] ?? '';
            $fecha_nac = $_POST['fecha_nac'] ?? '';
            $cp = $_POST['cp'] ?? '';
            $imagenPerfil = null;

            if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
                $rutaRelativa = 'images/profiles/' . basename($_FILES['imagen_perfil']['name']);
                $rutaAbsoluta = '../../' . $rutaRelativa;

                if (move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $rutaAbsoluta)) {
                    $imagenPerfil = $rutaRelativa;
                } else {
                    echo json_encode(['exito' => false, 'mensaje' => 'Error al subir la imagen.']);
                    exit;
                }
            }

            $conn = conexion();
            $success = updateUserProfile($conn, $userId, $nombre, $username, $email, $telefono, $direccion, $fecha_nac, $cp, $imagenPerfil);
            cerrar_conexion($conn);

            echo json_encode(['exito' => $success, 'mensaje' => $success ? 'Perfil actualizado correctamente.' : 'Error al actualizar el perfil.']);
            exit;

        case 'cambiar_contraseña':
            header('Content-Type: application/json');

            if (!isset($_SESSION['usuario']['id'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debes iniciar sesión.']);
                exit;
            }

            $userId = $_SESSION['usuario']['id'];
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($newPassword !== $confirmPassword) {
                echo json_encode(['exito' => false, 'mensaje' => 'Las contraseñas no coinciden.']);
                exit;
            }

            $conn = conexion();
            $hashedPassword = getCurrentPassword($conn, $userId);

            if (!$hashedPassword || !password_verify($currentPassword, $hashedPassword)) {
                echo json_encode(['exito' => false, 'mensaje' => 'La contraseña actual es incorrecta.']);
                cerrar_conexion($conn);
                exit;
            }

            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $success = updatePassword($conn, $userId, $newHashedPassword);
            cerrar_conexion($conn);

            echo json_encode(['exito' => $success, 'mensaje' => $success ? 'Contraseña actualizada correctamente.' : 'Error al actualizar la contraseña.']);
            exit;

        case 'agregar_producto':
            // Obtener los datos del producto
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

            // Validaciones de acceso
            if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
                header("Location: ../views/user/login.php?error=Acceso+denegado");
                exit;
            }

            $admin_id = ($_SESSION['usuario']['rol'] === 'admin') ? $_SESSION['usuario']['id'] : null;

            // Validación de datos
            $validaciones = [
                validateData('string', $nombre, 'nombre'),
                validateData('string', $descripcion, 'descripción'),
                validateData('fecha', $fecha_lanzamiento, 'fecha de lanzamiento'),
                validateData('numero', $precio, 'precio'),
            ];

            // Validar que se haya seleccionado al menos un género y plataforma
            if (empty($generos)) {
                $errores[] = "Debes seleccionar al menos un género.";
            }

            if (empty($plataformas)) {
                $errores[] = "Debes seleccionar al menos una plataforma.";
            }

            // Validar el stock por plataforma
            foreach ($stock_por_plataforma as $plataformaId => $stock) {
                $validacionStock = validateData('numero', $stock, "stock para la plataforma ID {$plataformaId}");
                if ($validacionStock !== true) {
                    $errores[] = $validacionStock;
                }
            }

            // Validación de imagen
            $resultadoImagen = validateImage($imagen);
            if ($resultadoImagen !== true) {
                $errores[] = $resultadoImagen;
            }

            // Si hay errores, redirigir al formulario
            if (!empty($errores)) {
                $_SESSION['form_data'] = $_POST; // Guarda los datos enviados
                $_SESSION['errores'] = $errores; // Guarda los errores
                header("Location: ../views/admin/addOrModifyProduct.php");
                exit;
            }

            // Crear el producto
            $productoCreado = createProduct(
                $nombre,
                $imagen,
                $descripcion,
                $fecha_lanzamiento,
                $generos,  // Ahora pasamos el array de géneros
                $precio,
                $descuento,
                $stock_por_plataforma,  // Pasamos el array de stock por plataforma
                $plataformas,  // Ahora pasamos el array de plataformas
                $admin_id,
                $admin_id
            );

            // Aquí deberías crear la lógica que asocie el producto con los géneros, plataformas y stock
            if ($productoCreado) {
                // Crear relaciones con géneros
                foreach ($generos as $generoId) {
                    // Suponiendo que hay una función para agregar géneros
                    addProductGenre($productoCreado['id'], $generoId);
                }

                // Crear relaciones con plataformas y stock
                foreach ($plataformas as $plataformaId) {
                    $stock = $stock_por_plataforma[$plataformaId] ?? 0;
                    // Suponiendo que hay una función para agregar plataformas y stock
                    addProductPlatform($productoCreado['id'], $plataformaId, $stock);
                }

                // Redirigir a la página de éxito o de lista de productos
                header("Location: ../views/admin/products.php?success=Producto+agregado+correctamente");
                exit;
            }

            break;

        case 'activar_usuario':
            header('Content-Type: application/json');
            $usuarioId = intval($_POST['usuario_id']);

            if (activateUser($usuarioId)) {
                echo json_encode(['exito' => true, 'mensaje' => 'Usuario activado correctamente.']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo activar el usuario.']);
            }
            exit;

        case 'desactivar_usuario':
            header('Content-Type: application/json');
            $usuarioId = intval($_POST['usuario_id']);

            if (deactivateUser($usuarioId)) {
                echo json_encode(['exito' => true, 'mensaje' => 'Usuario desactivado correctamente.']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo desactivar el usuario.']);
            }
            exit;

        case 'modificar_producto':
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['name'] ?? null;
            $descripcion = $_POST['description'] ?? null;
            $fecha_lanzamiento = $_POST['release_date'] ?? null;
            $precio = $_POST['price'] ?? null;
            $descuento = $_POST['discount'] ?? null;
            $plataformas = $_POST['plataformas'] ?? []; // Plataformas seleccionadas (array vacío por defecto)
            $generos = $_POST['generos'] ?? []; // Géneros seleccionados (array vacío por defecto)
            $stock_por_plataforma = $_POST['stock'] ?? []; // Stock por plataforma (array vacío por defecto)
            $imagenArchivo = $_FILES['image'] ?? null;
            $errores = [];

            // Validación de acceso
            if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
                header("Location: ../views/user/login.php?error=Acceso+denegado");
                exit;
            }

            // Obtener el nombre del usuario que está actualizando
            $actualizado_por = $_SESSION['usuario']['id'];  // Asumiendo que el nombre está en la sesión del usuario

            // Validación de datos
            $validaciones = [
                validateData('string', $nombre, 'nombre'),
                validateData('string', $descripcion, 'descripción'),
                validateData('fecha', $fecha_lanzamiento, 'fecha de lanzamiento'),
                validateData('numero', $precio, 'precio'),
            ];

            // Validar que se haya seleccionado al menos un género y plataforma
            if (empty($generos)) {
                $errores[] = "Debes seleccionar al menos un género.";
            }

            if (empty($plataformas)) {
                $errores[] = "Debes seleccionar al menos una plataforma.";
            }

            // Validar el stock por plataforma
            foreach ($stock_por_plataforma as $plataformaId => $stock) {
                $validacionStock = validateData('numero', $stock, "stock para la plataforma ID {$plataformaId}");
                if ($validacionStock !== true) {
                    $errores[] = $validacionStock;
                }
            }

            // Validación de imagen
            if ($imagenArchivo && $imagenArchivo['error'] === UPLOAD_ERR_OK) {
                $resultadoImagen = validateImage($imagenArchivo);
                if ($resultadoImagen !== true) {
                    $errores[] = $resultadoImagen;
                } else {
                    $nombreImagen = '_' . basename($imagenArchivo['name']);
                    $rutaDestino = '../images/products/' . $nombreImagen;
                    move_uploaded_file($imagenArchivo['tmp_name'], $rutaDestino);
                    $imagen = 'images/products/' . $nombreImagen;
                }
            } else {
                // Si no se sube una nueva imagen, mantenemos la imagen existente
                $productoExistente = getProductById($conn, $id);
                $imagen = $productoExistente['imagen'];
            }

            // Si hay errores, redirigir al formulario
            if (!empty($errores)) {
                header("Location: ../views/admin/addOrModifyProduct.php?id=" . $id . "&errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            // Actualizar el producto
            modifyProduct(
                $id,
                $nombre,
                $imagen,
                $descripcion,
                $fecha_lanzamiento,
                $generos, // Pasamos el array de géneros
                $precio,
                $descuento,
                $stock_por_plataforma, // Pasamos el array de stock por plataforma
                $plataformas, // Pasamos el array de plataformas
                $actualizado_por // El usuario que está realizando la modificación
            );

            // Redirigir a la página de productos con mensaje de éxito
            header("Location: ../views/admin/products.php?exito=Producto+modificado+correctamente");
            exit;

            break;

        case 'eliminar_producto':
            header('Content-Type: application/json');
            $id = intval($_POST['id']);

            if (deleteProduct($id)) {
                echo json_encode(['exito' => true, 'mensaje' => 'Producto eliminado con éxito.']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'Hubo un error al eliminar el producto.']);
            }
            exit;

        case 'agregar_favorito':
            header('Content-Type: application/json');

            if (!isset($_SESSION['usuario']['id'])) {
                echo json_encode(['success' => false, 'error' => 'unauthorized']);
                exit;
            }

            $usuarioId = $_SESSION['usuario']['id'];
            $productoId = $_POST['producto_id'] ?? null;

            if (!$productoId) {
                echo json_encode(['success' => false, 'error' => 'missing_product_id']);
                exit;
            }

            $esFavorito = addOrRemoveFav($usuarioId, $productoId);
            echo json_encode(['success' => true, 'favorito' => $esFavorito]);
            break;

        case 'obtener_stock':
            header('Content-Type: application/json');

            $productoId = $_POST['producto_id'] ?? null;
            $plataformaId = $_POST['plataforma_id'] ?? null;

            if (!$productoId || !$plataformaId) {
                echo json_encode(['success' => false, 'error' => 'missing_data']);
                exit;
            }

            $conn = conexion();
            $stock = getAvailableStock($conn, $productoId, $plataformaId);
            cerrar_conexion($conn);

            echo json_encode(['success' => true, 'stock' => $stock]);
            break;

        case 'agregar_producto_carrito':
            header('Content-Type: application/json');

            if (!isset($_SESSION['usuario']['id'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debes iniciar sesión para agregar productos al carrito.']);
                exit;
            }

            $usuarioId = $_SESSION['usuario']['id'];
            $productoId = $_POST['producto_id'] ?? null;
            $plataformaId = $_POST['plataforma_id'] ?? null;
            $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;

            if (!$productoId || !$plataformaId || !is_numeric($cantidad) || $cantidad <= 0) {
                echo json_encode(['exito' => false, 'mensaje' => 'Datos inválidos.']);
                exit;
            }

            $conn = conexion();

            // Obtener el precio final del producto
            $precioUnitario = getDiscountedPrice($conn, $productoId);
            if ($precioUnitario === null) {
                echo json_encode(['exito' => false, 'mensaje' => 'Producto no encontrado o inválido.']);
                cerrar_conexion($conn);
                exit;
            }

            // Intentar agregar el producto al carrito
            $resultado = addProductToCart($conn, $usuarioId, $productoId, $plataformaId, $cantidad, $precioUnitario);

            cerrar_conexion($conn);

            echo json_encode([
                'exito' => $resultado['exito'],
                'mensaje' => $resultado['mensaje'],
                'stock_restante' => $resultado['stock_restante'] ?? null
            ]);
            break;

        case 'eliminar_producto_carrito':
            header('Content-Type: application/json');

            if (!isset($_SESSION['usuario']['id'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debes iniciar sesión para eliminar productos del carrito.']);
                exit;
            }

            $usuarioId = $_SESSION['usuario']['id'];
            $carritoItemId = $_POST['carrito_item_id'] ?? null;

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

            $carritoId = $_POST['carrito_id'] ?? null;

            if (!$carritoId || !is_numeric($carritoId)) {
                echo json_encode(['exito' => false, 'mensaje' => 'ID de carrito inválido.']);
                exit;
            }

            $conn = conexion();
            $resumen = getCartSummary($conn, $carritoId);
            cerrar_conexion($conn);

            echo json_encode(['exito' => true, 'resumen' => $resumen]);
            exit;

        case 'vaciar_carrito':
            header('Content-Type: application/json');

            if (!isset($_SESSION['usuario']['id'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'Debes iniciar sesión para vaciar el carrito.']);
                exit;
            }

            $usuarioId = $_SESSION['usuario']['id'];
            $conn = conexion();

            // Llamar a la función emptyCart
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
            if (!$usuarioId) {
                echo json_encode(['estado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
                exit;
            }
            
            $metodoPago = $_POST['metodo_pago'] ?? null;
            $nombreCompleto = $_POST['nombre'] ?? null;
            $correo = $_POST['correo'] ?? null;
            $direccion = $_POST['direccion'] ?? null;
            $ciudad = $_POST['ciudad'] ?? null;
            $provincia = $_POST['provincia'] ?? null;
            $codigoPostal = $_POST['codigo_postal'] ?? null;
            $pais = $_POST['pais'] ?? null;
            $numeroTarjeta = $_POST['numero_tarjeta'] ?? null;
            $nombreTarjeta = $_POST['nombre_tarjeta'] ?? null;
            $vencimiento = $_POST['vencimiento'] ?? null;
            $errores = [];

            $validaciones = [
                validateData('string', $nombreCompleto, 'nombre completo'),
                validateData('email', $correo, 'correo electrónico'),
                validateData('string', $direccion, 'dirección'),
                validateData('string', $ciudad, 'ciudad'),
                validateData('string', $provincia, 'provincia'),
                validateData('string', $codigoPostal, 'código postal'),
                validateData('string', $pais, 'país'),
                validateData('string', $metodoPago, 'método de pago')
            ];

            if ($metodoPago === 'tarjeta') {
                $validaciones[] = validateData('tarjeta_numero', $numeroTarjeta, 'número de tarjeta');
                $validaciones[] = validateData('tarjeta_nombre', $nombreTarjeta, 'nombre en la tarjeta');
                $validaciones[] = validateData('tarjeta_expiracion', $vencimiento, 'fecha de vencimiento');
            }

            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            if (!empty($errores)) {
                echo json_encode(['estado' => 'error', 'errores' => $errores]);
                exit;
            }

            $pedidoId = createOrder($usuarioId);
            if (!$pedidoId) {
                echo json_encode(['estado' => 'error', 'mensaje' => 'No se pudo crear el pedido.']);
                exit;
            }

            $resultadoFacturacion = addBilling(
                $conn,
                $usuarioId,
                $pedidoId,
                $nombreCompleto,
                $correo,
                $direccion,
                $pais,
                $metodoPago,
                $numeroTarjeta,
                $vencimiento
            );

            if (!$resultadoFacturacion['success']) {
                echo json_encode(['estado' => 'error', 'mensaje' => $resultadoFacturacion['message']]);
                exit;
            }

            echo json_encode(['estado' => 'ok', 'mensaje' => 'Pago procesado y facturación registrada.']);
            exit;

        default:
            header("Location: error.php");
            break;
    }
}
