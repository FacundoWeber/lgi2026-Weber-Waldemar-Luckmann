
<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/database.php";

$pdo = null;

try {

    $pdo = obtenerConexion();

    $usuario = $_SESSION['usuario'] ?? null;

    if (!$usuario || empty($usuario['id'])) {
        http_response_code(401);
        throw new Exception("No hay un usuario autenticado.");
    }

    // =====================================================
    // FUNCIONES AUXILIARES
    // =====================================================

    function responderJSON($datos) {
        echo json_encode($datos);
        exit;
    }

    function validarFechasAlquiler($fechaInicio, $fechaFin) {

        $inicio = DateTime::createFromFormat("!Y-m-d", $fechaInicio);
        $fin = DateTime::createFromFormat("!Y-m-d", $fechaFin);

        if (
            !$inicio ||
            !$fin ||
            $inicio->format("Y-m-d") !== $fechaInicio ||
            $fin->format("Y-m-d") !== $fechaFin
        ) {
            throw new Exception("Las fechas ingresadas no son válidas.");
        }

        if ($fin < $inicio) {
            throw new Exception(
                "La fecha de devolución no puede ser anterior al inicio."
            );
        }

        return max(1, $inicio->diff($fin)->days);
    }

    // =====================================================
    // CONSULTAS GET DEL ADMINISTRADOR
    // =====================================================

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        if (($usuario['rol'] ?? null) !== 'administrador') {
            http_response_code(403);
            throw new Exception(
                "No tenés permisos para consultar los alquileres."
            );
        }

        $accion = $_GET['accion'] ?? '';

        // -------------------------------------------------
        // LISTAR CLIENTES
        // -------------------------------------------------

        if ($accion === 'clientes') {

            $consulta = $pdo->prepare("
                SELECT id, nombre
                FROM clientes
                ORDER BY nombre ASC
            ");

            $consulta->execute();

            responderJSON([
                "success" => true,
                "clientes" => $consulta->fetchAll(PDO::FETCH_ASSOC)
            ]);
        }

        // -------------------------------------------------
        // LISTAR ALQUILERES
        // -------------------------------------------------

        if ($accion === 'listar') {

            $consulta = $pdo->prepare("
                SELECT
                    a.id,
                    a.cliente_id,
                    c.nombre AS cliente,
                    u.nombre AS vendedor,
                    a.fecha_inicio,
                    a.fecha_fin,
                    COUNT(DISTINCT d.herramienta_id) AS herramientas,
                    a.total,
                    a.estado

                FROM alquileres a

                INNER JOIN clientes c
                    ON a.cliente_id = c.id

                INNER JOIN usuarios u
                    ON a.vendedor_id = u.id

                LEFT JOIN detalle_alquiler d
                    ON a.id = d.alquiler_id

                WHERE a.estado IN ('activo', 'pendiente')

                GROUP BY a.id

                ORDER BY a.id DESC
            ");

            $consulta->execute();

            responderJSON([
                "success" => true,
                "alquileres" => $consulta->fetchAll(PDO::FETCH_ASSOC)
            ]);
        }

        // -------------------------------------------------
        // CONSULTAR DETALLE DE UN ALQUILER
        // -------------------------------------------------

        if ($accion === 'detalle') {

            $id = (int)($_GET['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("ID de alquiler inválido.");
            }

            $consulta = $pdo->prepare("
                SELECT
                    id,
                    cliente_id,
                    fecha_inicio,
                    fecha_fin,
                    estado,
                    total
                FROM alquileres
                WHERE id = ?
                AND estado IN ('activo', 'pendiente')
            ");

            $consulta->execute([$id]);

            $alquiler = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$alquiler) {
                throw new Exception(
                    "El alquiler no existe o ya no está disponible para editar."
                );
            }

            // Herramientas que pertenecen al alquiler.
            $consultaDetalles = $pdo->prepare("
                SELECT
                    d.herramienta_id AS id_herramienta,
                    h.nombre,
                    d.cantidad,
                    d.precio_dia,
                    d.subtotal

                FROM detalle_alquiler d

                INNER JOIN herramientas h
                    ON d.herramienta_id = h.id

                WHERE d.alquiler_id = ?
            ");

            $consultaDetalles->execute([$id]);

            $detalles = $consultaDetalles->fetchAll(PDO::FETCH_ASSOC);

            // Herramientas activas disponibles para agregar.
            $consultaHerramientas = $pdo->prepare("
                SELECT id, nombre, precio_dia, activo
                FROM herramientas
                WHERE activo = 1
                ORDER BY nombre ASC
            ");

            $consultaHerramientas->execute();

            responderJSON([
                "success" => true,
                "alquiler" => $alquiler,
                "detalles" => $detalles,
                "herramientas_disponibles" =>
                    $consultaHerramientas->fetchAll(PDO::FETCH_ASSOC)
            ]);
        }

        http_response_code(400);
        throw new Exception("Acción GET no válida.");
    }

    // =====================================================
    // VERIFICAR MÉTODO POST
    // =====================================================

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception("Método no permitido.");
    }

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        throw new Exception("No se recibieron datos válidos.");
    }

    $accion = $datos['accion'] ?? '';

    // =====================================================
    // OPERACIONES DEL ADMINISTRADOR
    // =====================================================

    if (($usuario['rol'] ?? null) === 'administrador') {

        // -------------------------------------------------
        // DESACTIVAR ALQUILER
        // -------------------------------------------------

        if ($accion === 'desactivar') {

            $id = (int)($datos['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("ID de alquiler inválido.");
            }

            $pdo->beginTransaction();

            $consulta = $pdo->prepare("
                SELECT id, estado
                FROM alquileres
                WHERE id = ?
                AND estado IN ('activo', 'pendiente')
                FOR UPDATE
            ");

            $consulta->execute([$id]);

            $alquiler = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$alquiler) {
                throw new Exception(
                    "El alquiler no existe o ya fue cancelado."
                );
            }

            // Si estaba activo, devolver las cantidades al stock.
            if ($alquiler['estado'] === 'activo') {

                $consultaDetalles = $pdo->prepare("
                    SELECT herramienta_id, cantidad
                    FROM detalle_alquiler
                    WHERE alquiler_id = ?
                ");

                $consultaDetalles->execute([$id]);

                $actualizarStock = $pdo->prepare("
                    UPDATE herramientas
                    SET stock = stock + ?
                    WHERE id = ?
                ");

                foreach (
                    $consultaDetalles->fetchAll(PDO::FETCH_ASSOC)
                    as $detalle
                ) {

                    $actualizarStock->execute([
                        $detalle['cantidad'],
                        $detalle['herramienta_id']
                    ]);
                }
            }

            // No se elimina el registro: se cambia su estado.
            $cancelar = $pdo->prepare("
                UPDATE alquileres
                SET estado = 'cancelado'
                WHERE id = ?
            ");

            $cancelar->execute([$id]);

            $pdo->commit();

            responderJSON([
                "success" => true,
                "mensaje" => "Alquiler desactivado correctamente."
            ]);
        }

        // -------------------------------------------------
        // EDITAR ALQUILER
        // -------------------------------------------------

        if ($accion === 'editar') {

            $id = (int)($datos['id'] ?? 0);
            $clienteId = (int)($datos['cliente_id'] ?? 0);
            $fechaInicio = $datos['fecha_inicio'] ?? '';
            $fechaFin = $datos['fecha_fin'] ?? '';
            $estadoNuevo = $datos['estado'] ?? '';
            $herramientas = $datos['herramientas'] ?? [];

            if ($id <= 0 || $clienteId <= 0) {
                throw new Exception("Faltan datos del alquiler.");
            }

            if (!in_array($estadoNuevo, [
                'pendiente',
                'activo',
                'devuelto',
                'cancelado'
            ], true)) {
                throw new Exception("El estado seleccionado no es válido.");
            }

            if (!is_array($herramientas) || empty($herramientas)) {
                throw new Exception("Seleccioná al menos una herramienta.");
            }

            $dias = validarFechasAlquiler($fechaInicio, $fechaFin);

            // Agrupar cantidades de herramientas repetidas.
            $cantidades = [];

            foreach ($herramientas as $item) {

                $herramientaId = (int)($item['id_herramienta'] ?? 0);
                $cantidad = (int)($item['cantidad'] ?? 0);

                if ($herramientaId <= 0 || $cantidad <= 0) {
                    throw new Exception(
                        "Hay una herramienta o cantidad inválida."
                    );
                }

                if (!isset($cantidades[$herramientaId])) {
                    $cantidades[$herramientaId] = 0;
                }

                $cantidades[$herramientaId] += $cantidad;
            }

            $pdo->beginTransaction();

            // Bloquear el alquiler que se está editando.
            $consultaAlquiler = $pdo->prepare("
                SELECT id, estado
                FROM alquileres
                WHERE id = ?
                AND estado IN ('activo', 'pendiente')
                FOR UPDATE
            ");

            $consultaAlquiler->execute([$id]);

            $alquilerAnterior = $consultaAlquiler->fetch(PDO::FETCH_ASSOC);

            if (!$alquilerAnterior) {
                throw new Exception(
                    "El alquiler no existe o ya no se puede editar."
                );
            }

            // Verificar que exista el cliente.
            $consultaCliente = $pdo->prepare("
                SELECT id
                FROM clientes
                WHERE id = ?
            ");

            $consultaCliente->execute([$clienteId]);

            if (!$consultaCliente->fetch()) {
                throw new Exception("El cliente seleccionado no existe.");
            }

            // Obtener los detalles anteriores.
            $consultaAnteriores = $pdo->prepare("
                SELECT herramienta_id, cantidad, precio_dia
                FROM detalle_alquiler
                WHERE alquiler_id = ?
            ");

            $consultaAnteriores->execute([$id]);

            $anteriores = [];

            foreach (
                $consultaAnteriores->fetchAll(PDO::FETCH_ASSOC)
                as $detalle
            ) {

                $anteriores[(int)$detalle['herramienta_id']] = $detalle;
            }

            // Liberar stock anterior si el alquiler estaba activo.
            if ($alquilerAnterior['estado'] === 'activo') {

                $devolverStock = $pdo->prepare("
                    UPDATE herramientas
                    SET stock = stock + ?
                    WHERE id = ?
                ");

                foreach ($anteriores as $herramientaId => $detalle) {

                    $devolverStock->execute([
                        $detalle['cantidad'],
                        $herramientaId
                    ]);
                }
            }

            // Validar las nuevas herramientas y calcular el total.
            $total = 0;
            $detallesNuevos = [];

            $consultaHerramienta = $pdo->prepare("
                SELECT id, nombre, precio_dia, stock, activo
                FROM herramientas
                WHERE id = ?
                FOR UPDATE
            ");

            foreach ($cantidades as $herramientaId => $cantidad) {

                $consultaHerramienta->execute([$herramientaId]);

                $herramienta = $consultaHerramienta->fetch(PDO::FETCH_ASSOC);

                if (!$herramienta) {
                    throw new Exception("Una herramienta seleccionada no existe.");
                }

                $yaExistia = isset($anteriores[$herramientaId]);

                if ((int)$herramienta['activo'] !== 1 && !$yaExistia) {
                    throw new Exception(
                        "La herramienta " . $herramienta['nombre'] . " está inactiva."
                    );
                }

                $precio = $yaExistia
                    ? (float)$anteriores[$herramientaId]['precio_dia']
                    : (float)$herramienta['precio_dia'];

                // Recuperar virtualmente el stock que ya estaba reservado.
                $stockDisponible = (int)$herramienta['stock'];

                if ($alquilerAnterior['estado'] === 'activo' && $yaExistia) {
                    $stockDisponible += (int)$anteriores[$herramientaId]['cantidad'];
                }

                if (
                    $estadoNuevo === 'activo' &&
                    $stockDisponible < $cantidad
                ) {
                    throw new Exception(
                        "Stock insuficiente para: " .
                        $herramienta['nombre'] .
                        ". Disponible: " . $stockDisponible .
                        ", solicitado: " . $cantidad
                    );
                }

                $subtotal = $precio * $cantidad * $dias;

                $total += $subtotal;

                $detallesNuevos[] = [
                    'id' => $herramientaId,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $subtotal
                ];
            }

            // Actualizar los datos generales del alquiler.
            $actualizarAlquiler = $pdo->prepare("
                UPDATE alquileres
                SET
                    cliente_id = ?,
                    fecha_inicio = ?,
                    fecha_fin = ?,
                    estado = ?,
                    total = ?
                WHERE id = ?
            ");

            $actualizarAlquiler->execute([
                $clienteId,
                $fechaInicio,
                $fechaFin,
                $estadoNuevo,
                $total,
                $id
            ]);

            // Reemplazar los detalles anteriores.
            $eliminarDetalles = $pdo->prepare("
                DELETE FROM detalle_alquiler
                WHERE alquiler_id = ?
            ");

            $eliminarDetalles->execute([$id]);

            $insertarDetalle = $pdo->prepare("
                INSERT INTO detalle_alquiler
                (
                    alquiler_id,
                    herramienta_id,
                    cantidad,
                    precio_dia,
                    dias,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $descontarStock = $pdo->prepare("
                UPDATE herramientas
                SET stock = stock - ?
                WHERE id = ?
                AND stock >= ?
            ");

            foreach ($detallesNuevos as $detalle) {

                $insertarDetalle->execute([
                    $id,
                    $detalle['id'],
                    $detalle['cantidad'],
                    $detalle['precio'],
                    $dias,
                    $detalle['subtotal']
                ]);

                // Solo los alquileres activos reservan stock.
                if ($estadoNuevo === 'activo') {

                    $descontarStock->execute([
                        $detalle['cantidad'],
                        $detalle['id'],
                        $detalle['cantidad']
                    ]);

                    if ($descontarStock->rowCount() !== 1) {
                        throw new Exception(
                            "No se pudo actualizar el stock."
                        );
                    }
                }
            }

            $pdo->commit();

            responderJSON([
                "success" => true,
                "mensaje" => "Alquiler actualizado correctamente.",
                "total" => $total
            ]);
        }

        http_response_code(400);
        throw new Exception("Acción de administrador no válida.");
    }

    // =====================================================
    // REGISTRAR ALQUILER (VENDEDOR)
    // =====================================================

    if (($usuario['rol'] ?? null) !== 'vendedor') {
        http_response_code(403);
        throw new Exception(
            "No tenés permisos para registrar alquileres."
        );
    }

    $clienteId = $datos['cliente_id'] ?? null;
    $fechaInicio = $datos['fecha_inicio'] ?? null;
    $fechaFin = $datos['fecha_fin'] ?? null;
    $herramientas = $datos['herramientas'] ?? [];

    if (!$clienteId || !$fechaInicio || !$fechaFin) {
        throw new Exception("Faltan datos del alquiler.");
    }

    if (!is_array($herramientas) || empty($herramientas)) {
        throw new Exception("Seleccioná al menos una herramienta.");
    }

    $dias = validarFechasAlquiler($fechaInicio, $fechaFin);

    // Agrupar cantidades repetidas.
    $cantidades = [];

    foreach ($herramientas as $item) {

        $herramientaId = (int)($item['id_herramienta'] ?? 0);
        $cantidad = (int)($item['cantidad'] ?? 0);

        if ($herramientaId <= 0 || $cantidad <= 0) {
            throw new Exception("Herramienta o cantidad inválida.");
        }

        if (!isset($cantidades[$herramientaId])) {
            $cantidades[$herramientaId] = 0;
        }

        $cantidades[$herramientaId] += $cantidad;
    }

    $pdo->beginTransaction();

    // Verificar cliente.
    $consultaCliente = $pdo->prepare("
        SELECT id
        FROM clientes
        WHERE id = ?
    ");

    $consultaCliente->execute([$clienteId]);

    if (!$consultaCliente->fetch()) {
        throw new Exception("El cliente seleccionado no existe.");
    }

    // Consultar y validar herramientas.
    $total = 0;
    $detalles = [];

    $consultaHerramienta = $pdo->prepare("
        SELECT id, nombre, precio_dia, stock
        FROM herramientas
        WHERE id = ?
        AND activo = 1
        FOR UPDATE
    ");

    foreach ($cantidades as $herramientaId => $cantidad) {

        $consultaHerramienta->execute([$herramientaId]);

        $herramienta = $consultaHerramienta->fetch(PDO::FETCH_ASSOC);

        if (!$herramienta) {
            throw new Exception(
                "Una herramienta no existe o está inactiva."
            );
        }

        if ($herramienta['stock'] < $cantidad) {
            throw new Exception(
                "Stock insuficiente para: " . $herramienta['nombre']
            );
        }

        $precio = (float)$herramienta['precio_dia'];
        $subtotal = $precio * $cantidad * $dias;

        $total += $subtotal;

        $detalles[] = [
            'id' => $herramientaId,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'subtotal' => $subtotal
        ];
    }

    // Registrar alquiler.
    $insertarAlquiler = $pdo->prepare("
        INSERT INTO alquileres
        (
            cliente_id,
            vendedor_id,
            fecha_inicio,
            fecha_fin,
            estado,
            total
        )
        VALUES (?, ?, ?, ?, 'activo', ?)
    ");

    $insertarAlquiler->execute([
        $clienteId,
        $usuario['id'],
        $fechaInicio,
        $fechaFin,
        $total
    ]);

    $alquilerId = $pdo->lastInsertId();

    $insertarDetalle = $pdo->prepare("
        INSERT INTO detalle_alquiler
        (
            alquiler_id,
            herramienta_id,
            cantidad,
            precio_dia,
            dias,
            subtotal
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $descontarStock = $pdo->prepare("
        UPDATE herramientas
        SET stock = stock - ?
        WHERE id = ?
        AND stock >= ?
    ");

    foreach ($detalles as $detalle) {

        $insertarDetalle->execute([
            $alquilerId,
            $detalle['id'],
            $detalle['cantidad'],
            $detalle['precio'],
            $dias,
            $detalle['subtotal']
        ]);

        $descontarStock->execute([
            $detalle['cantidad'],
            $detalle['id'],
            $detalle['cantidad']
        ]);

        if ($descontarStock->rowCount() !== 1) {
            throw new Exception("No se pudo actualizar el stock.");
        }
    }

    $pdo->commit();

    responderJSON([
        "success" => true,
        "message" => "Alquiler registrado correctamente.",
        "alquiler_id" => $alquilerId,
        "total" => $total,
        "dias" => $dias
    ]);

} catch (Throwable $e) {

    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (http_response_code() < 400) {
        http_response_code(400);
    }

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
        "mensaje" => $e->getMessage()
    ]);
}
