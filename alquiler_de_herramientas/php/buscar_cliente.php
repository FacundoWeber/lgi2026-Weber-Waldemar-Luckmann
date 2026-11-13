<?php
header('Content-Type: application/json; charset=utf-8');

require_once("conexion.php");

$letra = trim($_GET['letra'] ?? '');

$sql = "SELECT id, nombre, dni, telefono
        FROM clientes
        WHERE nombre LIKE CONCAT(?, '%')
        ORDER BY nombre";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $letra);
$stmt->execute();

$resultado = $stmt->get_result();

$clientes = [];

while ($fila = $resultado->fetch_assoc()) {
    $clientes[] = $fila;
}

echo json_encode($clientes);

$stmt->close();
$conn->close();
?>