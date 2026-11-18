
<?php

require_once __DIR__ . '/_comun.php';

// La API solo responde a usuarios con sesión iniciada.
exigirRolApi('vendedor');

header("Content-Type: application/json; charset=UTF-8");

$conexion = new mysqli("localhost", "root", "", "alquiler_herramientas");

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}

$conexion->set_charset("utf8mb4");

// Determinar la acción
$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = json_decode(file_get_contents("php://input"), true);
    $accion = $datos['accion'] ?? '';
}

switch ($accion) {

    // LISTAR CLIENTES
    case 'listar':

        $sql = "SELECT * FROM clientes ORDER BY id ASC";

        $resultado = $conexion->query($sql);

        if (!$resultado) {
            http_response_code(500);

            echo json_encode([
                "error" => "Error en la consulta SQL",
                "detalle" => $conexion->error
            ]);

            break;
        }

        $clientes = [];

        while ($fila = $resultado->fetch_assoc()) {
            $clientes[] = $fila;
        }

        echo json_encode($clientes);

        break;


    // AGREGAR CLIENTE
    case 'guardar':

        $nombre = $datos['nombre'] ?? '';
        $dni = $datos['dni'] ?? '';
        $telefono = $datos['telefono'] ?? '';

        $sql = "INSERT INTO clientes (nombre, dni, telefono)
                VALUES (?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $nombre, $dni, $telefono);

        if ($stmt->execute()) {
            echo json_encode(["mensaje" => "Cliente agregado correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $stmt->error]);
        }

        $stmt->close();
        break;


    // ACTUALIZAR CLIENTE
    case 'cambiar_estado':

        $id = $datos['id'] ?? 0;
        $nombre = $datos['nombre'] ?? '';
        $dni = $datos['dni'] ?? '';
        $telefono = $datos['telefono'] ?? '';

        $sql = "UPDATE clientes
                SET nombre = ?, dni = ?, telefono = ?
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $dni, $telefono, $id);

        if ($stmt->execute()) {
            echo json_encode(["mensaje" => "Cliente actualizado correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $stmt->error]);
        }

        $stmt->close();
        break;
    ///BUSCAR CLIENTES

    case 'buscar':

        $letra = trim($_GET['letra'] ?? '');

        $sql = "SELECT * FROM clientes
                WHERE nombre LIKE CONCAT(?, '%')
                ORDER BY id ASC";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $letra);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $clientes = [];

        while ($fila = $resultado->fetch_assoc()) {
            $clientes[] = $fila;
        }

        echo json_encode($clientes);

        $stmt->close();
        break;


    default:
        http_response_code(400);
        echo json_encode(["error" => "Acción no válida"]);
        break;
}

$conexion->close();

?>
