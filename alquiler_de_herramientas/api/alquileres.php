<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/database.php";

try {

    // Verificar sesión del vendedor
    $vendedor_id = $_SESSION["usuario_id"] ?? null;

    if (!$vendedor_id) {
        throw new Exception("No hay un vendedor autenticado.");
    }


    // Recibir datos del JavaScript
    $datos = json_decode(file_get_contents("php://input"), true);

    if (!$datos) {
        throw new Exception("No se recibieron datos válidos.");
    }


    // Datos del alquiler
    $cliente_id = $datos["cliente_id"] ?? null;

    $fecha_inicio = $datos["fecha_inicio"] ?? null;

    $fecha_fin = $datos["fecha_fin"] ?? null;

    $herramientas = $datos["herramientas"] ?? [];


    // Validar datos
    if (!$cliente_id || !$fecha_inicio || !$fecha_fin) {

        throw new Exception("Faltan datos del alquiler.");

    }

    if (empty($herramientas)) {

        throw new Exception("Seleccioná al menos una herramienta.");

    }


    // Validar fechas
    $inicio = DateTime::createFromFormat("!Y-m-d", $fecha_inicio);

    $fin = DateTime::createFromFormat("!Y-m-d", $fecha_fin);

    if (
        !$inicio ||
        !$fin ||
        $inicio->format("Y-m-d") !== $fecha_inicio ||
        $fin->format("Y-m-d") !== $fecha_fin
    ) {
        throw new Exception("Las fechas ingresadas no son válidas.");
    }

    if ($fin < $inicio) {

        throw new Exception("La fecha de devolución no puede ser anterior al inicio.");

    }


    // Calcular días
    $dias = max(1, $inicio->diff($fin)->days);


    // Iniciar transacción
    $pdo->beginTransaction();


    // Verificar que el cliente exista
    $consultaCliente = $pdo->prepare("
        SELECT id
        FROM clientes
        WHERE id = ?
    ");

    $consultaCliente->execute([$cliente_id]);

    if (!$consultaCliente->fetch()) {

        throw new Exception("El cliente seleccionado no existe.");

    }


    // Calcular total y validar herramientas
    $total = 0;

    $detalles = [];


    $consultaHerramienta = $pdo->prepare("
        SELECT id, nombre, precio_dia, stock
        FROM herramientas
        WHERE id = ? AND activo = 1
        FOR UPDATE
    ");


    foreach ($herramientas as $item) {

        $id = (int)($item["id_herramienta"] ?? 0);

        $cantidad = (int)($item["cantidad"] ?? 0);


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


    // Preparar consultas
    $insertarDetalle = $pdo->prepare("
        INSERT INTO detalle_alquileres
        (alquiler_id, herramienta_id, cantidad, precio_dia, dias, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");


    $actualizarStock = $pdo->prepare("
        UPDATE herramientas
        SET stock = stock - ?
        WHERE id = ? AND stock >= ?
    ");


    // Registrar detalles y actualizar stock
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

        "total" => $total,

        "dias" => $dias

    ]);


} catch (Throwable $e) {

    if (isset($pdo) && $pdo->inTransaction()) {

        $pdo->rollBack();

    }

    http_response_code(400);

    echo json_encode([

        "success" => false,

        "message" => $e->getMessage()

    ]);

}
