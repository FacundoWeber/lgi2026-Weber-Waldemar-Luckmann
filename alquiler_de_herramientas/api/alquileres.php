<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../conexion.php";

try {

    // Recibir datos del JavaScript
    $datos = json_decode(file_get_contents("php://input"), true);

    if (!$datos || empty($datos["herramientas"])) {
        throw new Exception("No se recibieron herramientas.");
    }

    // Datos del alquiler
    $cliente_id = $datos["cliente_id"] ?? null;
    $vendedor_id = $datos["vendedor_id"] ?? null;

    $fecha_inicio = $datos["fecha_inicio"] ?? date("Y-m-d");
    $fecha_fin = $datos["fecha_fin"] ?? null;

    if (!$cliente_id || !$vendedor_id || !$fecha_fin) {
        throw new Exception("Faltan datos del cliente, vendedor o fecha de devolución.");
    }

    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);

    if ($fin < $inicio) {
        throw new Exception("La fecha de devolución no puede ser anterior al inicio.");
    }

    // Calcular días
    $dias = max(1, $inicio->diff($fin)->days);

    $pdo->beginTransaction();

    $total = 0;
    $detalles = [];

    // Preparar consulta de herramientas
    $consultaHerramienta = $pdo->prepare("
        SELECT id, nombre, precio_dia, stock
        FROM herramientas
        WHERE id = ? AND activo = 1
        FOR UPDATE
    ");

    // Consultas para registrar detalles
    $insertarDetalle = $pdo->prepare("
        INSERT INTO detalle_alquileres
        (alquiler_id, herramienta_id, cantidad, precio_dia, dias, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // Descontar stock
    $actualizarStock = $pdo->prepare("
        UPDATE herramientas
        SET stock = stock - ?
        WHERE id = ? AND stock >= ?
    ");

    foreach ($datos["herramientas"] as $item) {

        $id = (int)$item["id_herramienta"];
        $cantidad = (int)$item["cantidad"];

        if ($id <= 0 || $cantidad <= 0) {
            throw new Exception("Herramienta o cantidad inválida.");
        }

        $consultaHerramienta->execute([$id]);

        $herramienta = $consultaHerramienta->fetch(PDO::FETCH_ASSOC);

        if (!$herramienta) {
            throw new Exception("Una herramienta no existe o está inactiva.");
        }

        if ($herramienta["stock"] < $cantidad) {
            throw new Exception(
                "Stock insuficiente para: " . $herramienta["nombre"]
            );
        }

        $precio = (float)$herramienta["precio_dia"];

        $subtotal = $precio * $cantidad * $dias;

        $total += $subtotal;

        $detalles[] = [
            "id" => $id,
            "cantidad" => $cantidad,
            "precio" => $precio,
            "subtotal" => $subtotal
        ];
    }

    // Registrar alquiler
    $insertarAlquiler = $pdo->prepare("
        INSERT INTO alquileres
        (cliente_id, vendedor_id, fecha_inicio, fecha_fin, estado, total)
        VALUES (?, ?, ?, ?, 'activo', ?)
    ");

    $insertarAlquiler->execute([
        $cliente_id,
        $vendedor_id,
        $fecha_inicio,
        $fecha_fin,
        $total
    ]);

    $alquiler_id = $pdo->lastInsertId();

    // Registrar detalles y descontar stock
    foreach ($detalles as $detalle) {

        $insertarDetalle->execute([
            $alquiler_id,
            $detalle["id"],
            $detalle["cantidad"],
            $detalle["precio"],
            $dias,
            $detalle["subtotal"]
        ]);

        $actualizarStock->execute([
            $detalle["cantidad"],
            $detalle["id"],
            $detalle["cantidad"]
        ]);

        if ($actualizarStock->rowCount() !== 1) {
            throw new Exception("No se pudo actualizar el stock.");
        }
    }

    // Confirmar transacción
    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Alquiler registrado correctamente.",
        "alquiler_id" => $alquiler_id,
        "total" => $total
    ]);

} catch (Exception $e) {

    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
