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
            $checkbox = isset($_POST['admin']);
            $errores = [];

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
                $user = getUserData($email, $password);

                if ($user) {
                    login($user);
                    // Si hay productos en el carrito guardados antes de loguearse
                    if (isset($_SESSION['carrito_temp'])) {
                        $_SESSION['carrito'] = $_SESSION['carrito_temp'];
                        unset($_SESSION['carrito_temp']);
                    }

                    header("Location: ../views/user/catalog.php?Inicio+exitoso");
                    exit();
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

            $id = $_POST['id'] ?? null;

            if (!validateData('numero', $id)) {
                echo json_encode(['exito' => false, 'mensaje' => 'ID de producto inválido.']);
                exit;
            }

            $productoEliminado = deleteProduct($id);

            if ($productoEliminado) {
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

        default:
            header("Location: error.php");
            break;
    }
}
