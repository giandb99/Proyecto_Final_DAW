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

            // Validar el nombre del usuario
            if (validarDato('string', $username) !== true) {
                $errores[] = "El nombre es inválido.";
            }

            // Validar el nombre
            if (validarDato('string', $name) !== true) {
                $errores[] = "El nombre es inválido.";
            }

            // Validar el correo electrónico
            if (validarDato('email', $email) !== true) {
                $errores[] = "El correo es inválido.";
            }

            // Validar la contraseña
            if (validarDato('password', $password) !== true) {
                $errores[] = "La contraseña debe tener al menos 8 caracteres, una letra y un número.";
            }

            //Valido que las contraseñas sean iguales
            if ($password != $confirmPassword) {
                $errores[] = "Las contraseñas no coinciden";
            }

            // Si hay errores, redirigir de vuelta con los errores
            if (!empty($errores)) {
                // Redirigir a la página de registro con los errores en la URL
                header("Location: ../views/user/register.php?errores=" . urlencode(implode(", ", $errores)));
                exit;
            }

            // Crear el usuario
            $usuarioCreado = registrarUsuario($name, $username, $email, $password);
            exit;

        case 'iniciar_sesion':
            // Se inicia la sesión para almacenar datos del usuario
            session_start();

            // Se captura los datos del formulario
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
            $checkbox = isset($_POST['admin']);

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
                    header("Location: ../views/user/login.php?error=Credenciales+inválidas");
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
                    header("Location: ../views/user/login.php?error=Credenciales+inválidas");
                    exit();
                }
            }
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
