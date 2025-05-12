<?php

/**
 * Función para establecer la conexión a la base de datos.
 * @author Gianfranco Lombardo
 * @version 1.0
 * @return mysqli Conexión a la base de datos.
 * @throws Exception Si no se puede conectar a la base de datos.
 */
function conexion() {
    
    // Configuración de la base de datos
    $servername = "localhost";
    $username = "root";
    $password = "1234";
    $dbname = "tienda_online";

    // Se crea la conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Se verifica si la conexión fue exitosa
    if ($conn->connect_error) {
        die("<p class='error'>Error de conexión: " . $conn->connect_error . "</p>");
    } else {
        return $conn;
    }
}

/**
 * Función para cerrar la conexión a la base de datos.
 * @author Gianfranco Lombardo
 * @version 1.0
 * @param mysqli $conn Conexión a la base de datos.
 * @return void
 */
function cerrar_conexion($conn) {

    // Si la conexión está abierta, se cierra
    if ($conn) {
        $conn->close();
    } else {
        echo "<p class='error'>No hay conexión abierta para cerrar.</p>";
    }
}

// Llamada a la función para probar la conexión
$conn = conexion();