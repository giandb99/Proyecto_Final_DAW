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
            return is_numeric($dato) && $dato >= 0;
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
 *
 * @param array $imagen Información del archivo subido ($_FILES['nombre']).
 * @param array $formatosPermitidos Lista de extensiones permitidas (opcional).
 * @param int $tamanioMaximo Tamaño máximo permitido en bytes (opcional).
 * @return mixed Retorna `true` si la imagen es válida o un mensaje de error en caso contrario.
 */
function validarImagen($imagen) {
    // Verificar si se ha subido un archivo
    if ($imagen['error'] !== 0) {
        return false; // Error en la carga
    }

    // Verificar tipo de archivo
    $tipoImagen = mime_content_type($imagen['tmp_name']);
    if (!in_array($tipoImagen, ['image/jpeg', 'image/png', 'image/gif'])) {
        return false; // Si no es una imagen válida
    }

    // Verificar tamaño (por ejemplo, no mayor a 2MB)
    if ($imagen['size'] > 2 * 1024 * 1024) { // 2MB
        return false; // Imagen demasiado grande
    }

    return true;
}