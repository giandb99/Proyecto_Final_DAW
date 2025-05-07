<?php
require_once '../../database/querys.php';

$conn = conexion();
$usuarioId = 2; // Cambia esto por un ID válido
$productoId = 4; // Cambia esto por un ID válido
$plataformaId = 2; // Cambia esto por un ID válido
$cantidad = 1; // Cambia esto por una cantidad válida
$precioUnitario = 59.99; // Cambia esto por un precio válido

$resultado = addProductToCart($conn, $usuarioId, $productoId, $plataformaId, $cantidad, $precioUnitario);
print_r($resultado);
cerrar_conexion($conn);