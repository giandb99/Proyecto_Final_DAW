<?php
session_start();
// Incluir archivos necesarios para consultas, conexiones y validaciones
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
            $stock = $_POST['stock'] ?? null;
            $plataforma = $_POST['plataform'] ?? null;
            $genero = $_POST['gender'] ?? null;
            $imagen = $_FILES['image'] ?? null;
            $errores = [];

            if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
                header("Location: ../views/user/login.php?error=Acceso+denegado");
                exit;
            }

            $admin_id = ($_SESSION['usuario']['rol'] === 'admin') ? $_SESSION['usuario']['id'] : null; // Obtener el ID del administrador de la sesión

            $validaciones = [
                validateData('string', $nombre, 'nombre'),
                validateData('string', $descripcion, 'descripción'),
                validateData('fecha', $fecha_lanzamiento, 'fecha de lanzamiento'),
                validateData('numero', $precio, 'precio'),
                validateData('numero', $stock, 'stock'),
                validateData('numero', $plataforma, 'plataforma'),
                validateData('numero', $genero, 'género'),
            ];

            foreach ($validaciones as $campo => $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            $resultadoImagen = validateImage($imagen);
            if ($resultadoImagen !== true) {
                $errores[] = $resultadoImagen;
            }

            if (!empty($errores)) {
                $_SESSION['form_data'] = $_POST; // Guarda los datos enviados
                $_SESSION['errores'] = $errores; // Guarda los errores
                header("Location: ../views/admin/addOrModifyProduct.php");
                exit;
            }

            $productoCreado = createProduct(
                $nombre,
                $imagen,
                $descripcion,
                $fecha_lanzamiento,
                $genero,
                $precio,
                $descuento,
                $stock,
                $plataforma,
                $admin_id,
                $admin_id
            );

            break;

        case 'modificar_producto':
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['name'] ?? null;
            $descripcion = $_POST['description'] ?? null;
            $fecha_lanzamiento = $_POST['release_date'] ?? null;
            $precio = $_POST['price'] ?? null;
            $descuento = $_POST['discount'] ?? null;
            $stock = $_POST['stock'] ?? null;
            $genero_id = $_POST['gender'] ?? null;
            $plataforma_id = $_POST['plataform'] ?? null;
            $imagenArchivo = $_FILES['image'] ?? null;
            $errores = [];

            $validaciones = [
                validateData('string', $nombre, 'nombre'),
                validateData('string', $descripcion, 'descripción'),
                validateData('fecha', $fecha_lanzamiento, 'fecha de lanzamiento'),
                validateData('numero', $precio, 'precio'),
                validateData('numero', $stock, 'stock'),
                validateData('numero', $plataforma_id, 'plataforma'),
                validateData('numero', $genero_id, 'género'),
            ];

            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

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
                $productoExistente = getProductById($id);
                $imagen = $productoExistente['imagen'];
            }

            if (!empty($errores)) {
                header("Location: ../views/admin/addOrModifyProduct.php?id=" . $id . "&errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            modifyProduct($id, $nombre, $imagen, $descripcion, $fecha_lanzamiento, $genero_id, $precio, $descuento, $stock, $plataforma_id);
            exit;

        case 'eliminar_producto':

            // Obtener el ID del producto
            $id = $_POST['id'] ?? null;

            // Verificar si el ID es válido
            if (!validateData('numero', $id)) {
                header("Location: ../views/admin/products.php?error=ID+de+producto+inválido.");
                exit;
            }

            // Eliminar el producto usando la función que creamos
            $productoEliminado = deleteProduct($id);

            if ($productoEliminado) {
                // Redirigir al catálogo con un mensaje de éxito
                header("Location: ../views/admin/products.php?exito=Producto+eliminado+con+éxito.");
                exit;
            } else {
                // Redirigir con mensaje de error si no se pudo eliminar el producto
                header("Location: ../views/admin/products.php?error=Hubo+un+error+al+eliminar+el+producto.");
                exit;
            }
            break;

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

        case 'agregar_carrito':
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

            $resultado = addProductToCart($usuarioId, $productoId, 1);
            echo json_encode(['success' => true]);
            break;

        // case 'guardar_preferencias':

        //     // Obtener las preferencias del usuario
        //     $idioma = $_POST['idioma'] ?? null;
        //     $moneda = $_POST['moneda'] ?? null;
        //     $tema = $_POST['tema'] ?? null;

        //     exit;

        default:
            // Si no se reconoce la acción, redirigir a una página de error
            header("Location: error.php");
            break;
    }
}
