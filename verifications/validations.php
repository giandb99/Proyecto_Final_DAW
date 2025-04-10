<?php

/**
 * Función para validar los diferentes tipo de datos de entrada.
 * @param string $tipo Tipo de dato a validar (email, telefono, nombre, direccion,).
 * @param string $dato Dato a validar.
 * @return bool true si el dato es válido, false en caso contrario.
 * @throws Exception Si el tipo de dato no es válido.
 */
function validarDato($tipo, $dato){
    
    // Validación de los datos según su tipo
    switch ($tipo) {
        case 'telefono':
            return preg_match('/^\d{9,10}$/', $dato) === 1;
        case 'email':
            return filter_var($dato, FILTER_VALIDATE_EMAIL) !== false;
        case 'string':
            return is_string($dato) && !empty($dato);
        case 'numero':
            return is_numeric($dato) && $dato > 0;
        case 'url':
            return filter_var($dato, FILTER_VALIDATE_URL) !== false;
        case 'fecha':
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $dato) === 1;
        case 'hora':
            return preg_match('/^\d{2}:\d{2}:\d{2}$/', $dato) === 1;
        case 'password':
            return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $dato) === 1; // Al menos 8 caracteres, al menos una letra y un número
        case 'direccion':
            return preg_match('/^[a-zA-Z0-9\s,.-]+$/', $dato) === 1;
        default:
            return false; // Tipo no válido
    }
}

/**
 * Función para validar imágenes.
 * @param array $imagen Array que contiene la información de la imagen.
 * @return bool true si la imagen es válida, false en caso contrario.
 * @throws Exception Si el tipo de archivo no es permitido o hay un error al subir la imagen.
 */
function validarImagen($imagen) {
    // Verifica si la imagen es válida
    if (isset($imagen['name']) && $imagen['error'] == 0) {
        $tipoArchivo = pathinfo($imagen['name'], PATHINFO_EXTENSION);
        $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Verifica el tipo de archivo
        if (in_array($tipoArchivo, $tiposPermitidos)) {
            return true;
        } else {
            throw new Exception("Tipo de archivo no permitido. Solo se permiten imágenes JPG, JPEG, PNG y GIF.");
        }
    } else {
        throw new Exception("Error al subir la imagen.");
    }
}