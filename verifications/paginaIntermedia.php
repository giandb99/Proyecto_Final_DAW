<?php

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
                validarDato('string', $username, 'nombre de usuario'),
                validarDato('email', $email, 'correo electrónico'),
                validarDato('password', $password, 'contraseña')
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
            $usuarioCreado = registrarUsuario($name, $username, $email, $password);
            exit;

        case 'iniciar_sesion':
            // Se inicia la sesión para almacenar datos del usuario
            session_start();

            // Se capturan los datos del formulario
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            $checkbox = isset($_POST['admin']);
            $errores = [];

            $validaciones = [
                validarDato('email', $email, 'correo electrónico')
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
                $user = obtenerDatosUsuario($email, $password);

                if ($user) {
                    $_SESSION['usuario'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'rol' => 'user'
                    ];

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
                $admin = obtenerDatosAdmin($email, $password);

                if ($admin) {
                    $_SESSION['usuario'] = [
                        'id' => $admin['id'],
                        'username' => $admin['username'],
                        'email' => $admin['email'],
                        'rol' => 'admin'
                    ];

                    header("Location: ../views/admin/dashboard.php?Inicio+exitoso");
                    exit();
                } else {
                    header("Location: ../views/user/login.php?errores=Credenciales+inválidas");
                    exit();
                }
            }
            break;

        case 'agregar_producto':
            session_start();

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
                validarDato('string', $nombre, 'nombre'),
                validarDato('string', $descripcion, 'descripción'),
                validarDato('fecha', $fecha_lanzamiento, 'fecha de lanzamiento'),
                validarDato('numero', $precio, 'precio'),
                validarDato('numero', $stock, 'stock'),
                validarDato('numero', $plataforma, 'plataforma'),
                validarDato('numero', $genero, 'género'),
            ];

            foreach ($validaciones as $campo => $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            $resultadoImagen = validarImagen($imagen);
            if ($resultadoImagen !== true) {
                $errores[] = $resultadoImagen;
            }

            if (!empty($errores)) {
                header("Location: ../views/admin/newProduct.php?errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            $productoCreado = crearProducto(
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
            session_start();

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
                validarDato('string', $nombre, 'nombre'),
                validarDato('string', $descripcion, 'descripción'),
                validarDato('fecha', $fecha_lanzamiento, 'fecha de lanzamiento'),
                validarDato('numero', $precio, 'precio'),
                validarDato('numero', $stock, 'stock'),
                validarDato('numero', $plataforma_id, 'plataforma'),
                validarDato('numero', $genero_id, 'género'),
            ];

            foreach ($validaciones as $resultado) {
                if ($resultado !== true) {
                    $errores[] = $resultado;
                }
            }

            if ($imagenArchivo && $imagenArchivo['error'] === UPLOAD_ERR_OK) {
                $resultadoImagen = validarImagen($imagenArchivo);
                if ($resultadoImagen !== true) {
                    $errores[] = $resultadoImagen;
                } else {
                    $nombreImagen = '_' . basename($imagenArchivo['name']);
                    $rutaDestino = '../images/products/' . $nombreImagen;
                    move_uploaded_file($imagenArchivo['tmp_name'], $rutaDestino);
                    $imagen = 'images/products/' . $nombreImagen;
                }
            } else {
                $productoExistente = obtenerProductoPorId($id);
                $imagen = $productoExistente['imagen'];
            }

            if (!empty($errores)) {
                header("Location: ../views/admin/addOrModifyProduct.php?id=" . $id . "&errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            modificarProducto($id, $nombre, $imagen, $descripcion, $fecha_lanzamiento, $genero_id, $precio, $descuento, $stock, $plataforma_id);
            exit;

        case 'eliminar_producto':
            
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

        // case 'agregar_producto':
        //     // Validar los campos del producto
        //     $nombre = $_POST['product_name'] ?? null;
        //     $descripcion = $_POST['description'] ?? null;
        //     $precio = $_POST['price'] ?? null;
        //     $imagen = $_FILES['image'] ?? null;
        //     $errores = [];

        //     // Validación del nombre del producto
        //     if (validarDato('string', $nombre) !== true) {
        //         $errores[] = "El nombre del producto es inválido.";
        //     }

        //     // Validación de la descripción
        //     if (validarDato('string', $descripcion) !== true) {
        //         $errores[] = "La descripción del producto es inválida.";
        //     }

        //     // Validación del precio
        //     if (validarDato('numero', $precio) !== true) {
        //         $errores[] = "El precio debe ser un número positivo.";
        //     }

        //     // Validación de la imagen (solo si se ha subido una)
        //     if ($imagen && !validarImagen($imagen)) {
        //         $errores[] = "La imagen es inválida o no se subió correctamente.";
        //     }

        //     // Si hay errores, redirigir de vuelta al formulario con los errores
        //     if (!empty($errores)) {
        //         // Redirigir a agregarProducto.php con los errores en la URL
        //         header("Location: ../Interfaces/agregarProducto.php?errores=" . urlencode(implode(", ", $errores)));
        //         exit;  // Asegurarse de que el script no continúe
        //     }

        //     // Crear el producto si no hay errores
        //     $productoCreado = crearProducto($nombre, $descripcion, $precio, $imagen);
        //     exit;

        // case 'eliminar_producto':

        //     // Obtener el ID del producto
        //     $id = $_POST['id'] ?? null;

        //     // Verificar si el ID es válido
        //     if (!validarDato('numero', $id)) {
        //         header("Location: ../Interfaces/catalogoAdmin.php?error=ID+de+producto+inválido.");
        //         exit;
        //     }

        //     // Eliminar el producto usando la función que creamos
        //     $productoEliminado = eliminarProducto($id);

        //     if ($productoEliminado) {
        //         // Redirigir al catálogo con un mensaje de éxito
        //         header("Location: ../Interfaces/catalogoAdmin.php?exito=Producto+eliminado+con+éxito.");
        //         exit;
        //     } else {
        //         // Redirigir con mensaje de error si no se pudo eliminar el producto
        //         header("Location: ../Interfaces/catalogoAdmin.php?error=Hubo+un+error+al+eliminar+el+producto.");
        //         exit;
        //     }

        // case 'modificar_producto':
        //     if (isset($_POST['id'])) {
        //         $id = intval($_POST['id']); // Asegúrate de convertir el ID a entero

        //         // Redirige a la interfaz de modificación con el ID del producto
        //         header("Location: ../Interfaces/modificarProducto.php?id=$id");
        //         exit;
        //     } else {
        //         header("Location: ../Interfaces/catalogoAdmin.php?error=ID+no+especificado+para+modificación.");
        //         exit;
        //     }
        //     break;

        // case 'confirmar_modificacion':
        //     if (isset($_POST['id'])) {
        //         $id = intval($_POST['id']); // Asegúrate de convertir el ID a entero
        //         $nombre = $_POST['product_name'] ?? null;
        //         $descripcion = $_POST['description'] ?? null;
        //         $precio = $_POST['price'] ?? null;
        //         $imagen = $_FILES['image'] ?? null;

        //         // Obtener la imagen actual si no se envió una nueva
        //         $imagenActual = $_POST['current_image'] ?? null;

        //         // Validar los datos del producto
        //         $errores = [];

        //         if (validarDato('string', $nombre) !== true) {
        //             $errores[] = "El nombre del producto es inválido.";
        //         }

        //         if (validarDato('string', $descripcion) !== true) {
        //             $errores[] = "La descripción del producto es inválida.";
        //         }

        //         if (validarDato('numero', $precio) !== true) {
        //             $errores[] = "El precio debe ser un número positivo.";
        //         }

        //         // Validación de la imagen (solo si se ha subido una nueva)
        //         if ($imagen && !validarImagen($imagen)) {
        //             $errores[] = "La imagen es inválida o no se subió correctamente.";
        //         }

        //         // Si hay errores, redirigir de vuelta al formulario con los errores
        //         if (!empty($errores)) {
        //             // Redirigir a modificarProducto.php con los errores en la URL
        //             header("Location: ../Interfaces/modificarProducto.php?id=$id&errores=" . urlencode(implode(", ", $errores)));
        //             exit;  // Asegurarse de que el script no continúe
        //         }

        //         // Si no hay errores, procesar la actualización del producto
        //         if ($imagen && $imagen['error'] == 0) {
        //             // Se proporciona una nueva imagen
        //             $productoModificado = modificarProducto($id, $nombre, $descripcion, $precio, $imagen);
        //         } else {
        //             // No se proporciona una nueva imagen, se mantiene la actual
        //             $productoModificado = modificarProducto($id, $nombre, $descripcion, $precio, $imagenActual);
        //         }

        //         // Verificar si el producto fue modificado exitosamente
        //         if ($productoModificado) {
        //             // Redirigir a la página de éxito (catalogoAdmin.php en este caso)
        //             header("Location: ../Interfaces/catalogoAdmin.php?exito=Producto+modificado+exitosamente.");
        //         } else {
        //             // Si algo falló en la modificación, redirigir de vuelta a la página de modificar producto con un mensaje de error
        //             header("Location: ../Interfaces/modificarProducto.php?id=$id&error=Hubo+un+error+al+modificar+el+producto.");
        //         }
        //         exit;  // Asegurarse de que el script no continúe
        //     } else {
        //         header("Location: ../Interfaces/catalogoAdmin.php?error=ID+no+especificado+para+modificación.");
        //         exit;
        //     }
        //     break;
