<?php

/**
 * Establece una conexión con la base de datos MySQL.
 *
 * @return mysqli Objeto de conexión a la base de datos.
 * @throws Exception Si ocurre un error al conectar con la base de datos.
 */
function conexion() {
    
    // Configuración de la base de datos
    $servername = "localhost"; // Nombre del servidor de la base de datos
    $username = "root";        // Usuario de la base de datos
    $password = "";            // Contraseña del usuario (vacía por defecto en XAMPP)
    // $password = "1234";     // --> en clase usar esta contraseña
    $dbname = "tienda_online"; // Nombre de la base de datos

    // Se crea la conexión a la base de datos usando MySQLi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Se verifica si la conexión fue exitosa
    if ($conn->connect_error) {
        // Si hay error, se muestra un mensaje y se detiene la ejecución
        die("<p class='error'>Error de conexión: " . $conn->connect_error . "</p>");
    } else {
        // Si la conexión es exitosa, se retorna el objeto de conexión
        return $conn;
    }
}

/**
 * Cierra la conexión a la base de datos si está abierta.
 *
 * @param mysqli $conn Objeto de conexión a la base de datos.
 * @return void
 */
function cerrar_conexion($conn) {

    // Si la conexión está abierta, se cierra
    if ($conn) {
        $conn->close(); // Cierra la conexión
    } else {
        // Si no hay conexión, se muestra un mensaje de error
        echo "<p class='error'>No hay conexión abierta para cerrar.</p>";
    }
}

// Llamada a la función para probar la conexión (esto es solo para pruebas, se recomienda eliminar en producción)
$conn = conexion();