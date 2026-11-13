<?php

//header('Content-Type: application/json; charset=utf-8');

require_once("conexion.php");

// Recibir los datos JSON enviados desde JavaScript
$datos = json_decode(file_get_contents('php://input'), true);

if (!$datos) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No se recibieron datos válidos.'
    ]);
    exit;
}

// Obtener los datos
$nombre = trim($datos['nombre'] ?? '');
$dni = trim($datos['dni'] ?? '');
$telefono = trim($datos['telefono'] ?? '');

// Validar los campos
if (
    $nombre === '' ||
    $dni === '' ||
    $telefono === ''
) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Todos los campos son obligatorios.'
    ]);
    exit;
}

// Preparar la consulta
$sql = "INSERT INTO clientes
        (nombre, dni, telefono)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error al preparar la consulta.'
    ]);
    exit;
}

// Vincular los parámetros


$stmt->bind_param(
    "sss",
    $nombre,
    $dni,
    $telefono
);

// Ejecutar la consulta
if ($stmt->execute()) {
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Cliente creado correctamente.',
        'id' => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No se pudo insertar el cliente.'
    ]);
}

$stmt->close();
$conn->close();

?>