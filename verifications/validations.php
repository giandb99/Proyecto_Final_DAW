<?php

/**
 * Función para validar los diferentes tipo de datos de entrada.
 * @param string $tipo Tipo de dato a validar (email, telefono, nombre, direccion,).
 * @param string $dato Dato a validar.
 * @return bool true si el dato es válido, false en caso contrario.
 * @throws Exception Si el tipo de dato no es válido.
 */
function validateData($tipo, $valor, $campoNombre = '')
{
    switch ($tipo) {
        case 'string':
            if (empty($valor)) {
                return "El campo '{$campoNombre}' no puede estar vacío.";
            }
            return true;

        case 'fecha':
            if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $valor)) {
                return "La fecha debe estar en formato DD-MM-YYYY.";
            }
            return true;

        case 'numero':
            if (!is_numeric($valor) || $valor < 0) {
                return "El campo '{$campoNombre}' debe ser un número válido y mayor a cero.";
            }
            return true;

        case 'email':
            if (empty($valor)) {
                return "El correo electrónico no puede estar vacío.";
            } else if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                return "El correo electrónico no tiene un formato válido.";
            }
            return true;

        case 'password':
            if (empty($valor)) {
                return "La contraseña no puede estar vacía.";
            } else if (strlen($valor) < 6) {
                return "La contraseña debe tener al menos 6 caracteres.";
            } else if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $valor)) {
                return "La contraseña debe contener al menos una letra y un número.";
            }
            return true;

        case 'tarjeta_numero':
            $numero = preg_replace('/\D/', '', $valor);
            if (strlen($numero) < 13 || strlen($numero) > 19) {
                return "El número de tarjeta debe tener entre 13 y 19 dígitos.";
            } else if (!luhnCheck($numero)) {
                return "El número de tarjeta no es válido.";
            }
            return true;

        case 'tarjeta_nombre':
            if (empty($valor)) {
                return "El nombre del titular no puede estar vacío.";
            } else if (!preg_match('/^[A-ZÁÉÍÓÚÑ ]{2,}$/i', $valor)) {
                return "El nombre del titular contiene caracteres inválidos.";
            }
            return true;

        case 'tarjeta_expiracion':
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

/**
 * Verifica un número de tarjeta con el algoritmo de Luhn.
 *
 * @param string $numero Número de tarjeta (solo dígitos).
 * @return bool true si es válido, false si no.
 */
function luhnCheck($numero)
{
    $suma = 0;
    $par = false;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $digito = (int)$numero[$i];
        if ($par) {
            $digito *= 2;
            if ($digito > 9) {
                $digito -= 9;
            }
        }
        $suma += $digito;
        $par = !$par;
    }
    return ($suma % 10) === 0;
}