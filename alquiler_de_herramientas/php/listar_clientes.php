<?php

$conexion = new mysqli("localhost", "root", "", "alquiler_herramientas");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$sql = "SELECT * FROM clientes ORDER BY id ASC";

$resultado = $conexion->query($sql);

$clientes = [];

while ($fila = $resultado->fetch_assoc()) {
    $clientes[] = $fila;
}

header("Content-Type: application/json");

echo json_encode($clientes);

$conexion->close();
?>