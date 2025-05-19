<?php

/**
 * Función para validar los diferentes tipo de datos de entrada.
 * 
 * @param string $tipo Tipo de dato a validar (email, telefono, nombre, direccion,).
 * @param string $dato Dato a validar.
 * @return bool true si el dato es válido, false en caso contrario.
 * @throws Exception Si el tipo de dato no es válido.
 */
function validateData($tipo, $valor, $campoNombre = '')
{
    switch ($tipo) {
        case 'string':
            // Valida que el campo no esté vacío
            if (empty($valor)) {
                return "El campo '{$campoNombre}' no puede estar vacío.";
            }
            return true;

        case 'fecha':
            // Valida que la fecha esté en formato YYYY-MM-DD y no sea futura
            if (empty($valor)) {
                return "El campo '{$campoNombre}' no puede estar vacío.";
            } else if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                return "La fecha debe estar en formato YYYY-MM-DD.";
            } else {
                $fechaNacimiento = DateTime::createFromFormat('Y-m-d', $valor);
                $fechaActual = new DateTime();
                if ($fechaNacimiento > $fechaActual) {
                    return "La fecha no puede ser futura.";
                }
            }
            return true;

        case 'telefono':
            // Valida que el teléfono tenga exactamente 9 dígitos
            if (empty($valor)) {
                return "El campo '{$campoNombre}' no puede estar vacío.";
            } else if (!preg_match('/^\d{9}$/', $valor)) {
                return "El campo '{$campoNombre}' debe contener exactamente 9 dígitos.";
            }
            return true;

        case 'cp':
            // Valida que el código postal tenga exactamente 5 dígitos
            if (empty($valor)) {
                return "El campo '{$campoNombre}' no puede estar vacío.";
            } else if (!preg_match('/^\d{5}$/', $valor)) {
                return "El campo '{$campoNombre}' debe contener exactamente 5 dígitos.";
            }
            return true;

        case 'numero':
            // Valida que sea un número válido y mayor a cero
            if (!is_numeric($valor) || $valor < 0) {
                return "El campo '{$campoNombre}' debe ser un número válido y mayor a cero.";
            }
            return true;

        case 'email':
            // Valida que el email no esté vacío y tenga formato válido
            if (empty($valor)) {
                return "El correo electrónico no puede estar vacío.";
            } else if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                return "El correo electrónico no tiene un formato válido.";
            }
            return true;

        case 'password':
            // Valida que la contraseña tenga mínimo 6 caracteres, al menos una letra y un número
            if (empty($valor)) {
                return "La contraseña no puede estar vacía.";
            } else if (strlen($valor) < 6) {
                return "La contraseña debe tener al menos 6 caracteres.";
            } else if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $valor)) {
                return "La contraseña debe contener al menos una letra y un número.";
            }
            return true;

        case 'tarjeta_numero':
            // Valida que el número de tarjeta tenga exactamente 16 dígitos (solo números)
            $numero = preg_replace('/\D/', '', $valor);
            if (strlen($numero) !== 16) {
                return "El número de tarjeta debe tener exactamente 16 dígitos.";
            }
            return true;

        case 'tarjeta_nombre':
            // Valida que el nombre de la tarjeta no esté vacío y solo tenga letras y espacios
            if (empty($valor)) {
                return "El campo '{$campoNombre}' no puede estar vacío.";
            } else if (!preg_match('/^[A-ZÁÉÍÓÚÑ ]{2,}$/i', $valor)) {
                return "El campo '{$campoNombre}' contiene caracteres inválidos.";
            }
            return true;

        case 'tarjeta_expiracion':
            // Valida que la fecha de expiración esté en formato MM/YY y no esté vencida
            if (!preg_match('/^(0[1-9]|1[0-2])[\/\-]\d{2}$/', $valor)) {
                return "La fecha de expiración debe estar en formato MM/YY.";
            } else {
                list($mes, $anio) = preg_split('/[\/\-]/', $valor);
                $anio = (int)('20' . $anio);
                $mes = (int)$mes;

                $fechaActual = new DateTime();
                $fechaExpiracion = DateTime::createFromFormat('Y-m', "$anio-$mes")->modify('last day of this month');

                if ($fechaExpiracion < $fechaActual) {
                    return "La tarjeta ya está vencida.";
                }
            }
            return true;

        default:
            // Si el tipo no es válido, lanza una excepción
            return "Tipo de validación desconocido.";
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
function validateImage($imagen)
{
    // Verifica si se ha subido un archivo correctamente
    if (!isset($imagen) || $imagen['error'] !== 0) {
        return "Error al subir la imagen.";
    }

    // Verifica tipo de archivo
    $tipoImagen = mime_content_type($imagen['tmp_name']);
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

    if (!in_array($tipoImagen, $tiposPermitidos)) {
        return "El archivo no es una imagen válida (solo se permiten JPG, PNG o GIF).";
    }

    // Verifica tamaño (máx 5MB)
    if ($imagen['size'] > 5 * 1024 * 1024) {
        return "La imagen supera el tamaño máximo permitido de 5MB.";
    }

    return true;
}