<?php
// Informes del sistema. Todos usan JOIN y GROUP BY.
// GET ?accion=informes&desde=AAAA-MM-DD&hasta=AAAA-MM-DD

require_once __DIR__ . '/_comun.php';

exigirRolApi('administrador');

$accion = $_GET['accion'] ?? '';

$desde = $_GET['desde'] ?? date('Y-m-01');
$hasta = $_GET['hasta'] ?? date('Y-m-t');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
    responder(false, 'Las fechas no son válidas.', [], 400);
}

if ($desde > $hasta) {
    $cambio = $desde;
    $desde = $hasta;
    $hasta = $cambio;
}

try {
    $conexion = obtenerConexion();

    if ($accion === 'informes') {

        // Herramientas más alquiladas en el período.
        $consulta = $conexion->prepare("
            SELECT h.nombre,
                   COUNT(d.id) AS alquileres,
                   COALESCE(SUM(d.cantidad), 0) AS unidades,
                   COALESCE(SUM(d.subtotal), 0) AS total
            FROM detalle_alquiler d
            INNER JOIN herramientas h ON h.id = d.herramienta_id
            INNER JOIN alquileres a ON a.id = d.alquiler_id
            WHERE a.estado <> 'cancelado'
              AND a.fecha_inicio BETWEEN ? AND ?
            GROUP BY h.id, h.nombre
            ORDER BY unidades DESC, alquileres DESC
        ");
        $consulta->execute([$desde, $hasta]);
        $herramientas = $consulta->fetchAll();

        // Clientes más frecuentes en el período.
        $consulta = $conexion->prepare("
            SELECT c.nombre,
                   COUNT(a.id) AS alquileres,
                   COALESCE(SUM(a.total), 0) AS total
            FROM alquileres a
            INNER JOIN clientes c ON c.id = a.cliente_id
            WHERE a.estado <> 'cancelado'
              AND a.fecha_inicio BETWEEN ? AND ?
            GROUP BY c.id, c.nombre
            ORDER BY alquileres DESC, total DESC
        ");
        $consulta->execute([$desde, $hasta]);
        $clientes = $consulta->fetchAll();

        // Cantidad y total por estado.
        $consulta = $conexion->prepare("
            SELECT estado,
                   COUNT(*) AS alquileres,
                   COALESCE(SUM(total), 0) AS total
            FROM alquileres
            WHERE fecha_inicio BETWEEN ? AND ?
            GROUP BY estado
            ORDER BY alquileres DESC
        ");
        $consulta->execute([$desde, $hasta]);
        $estados = $consulta->fetchAll();

        // Actividad por mes.
        $consulta = $conexion->prepare("
            SELECT DATE_FORMAT(fecha_inicio, '%Y-%m') AS mes,
                   COUNT(*) AS alquileres,
                   COALESCE(SUM(total), 0) AS total
            FROM alquileres
            WHERE estado <> 'cancelado'
              AND fecha_inicio BETWEEN ? AND ?
            GROUP BY mes
            ORDER BY mes DESC
        ");
        $consulta->execute([$desde, $hasta]);
        $meses = $consulta->fetchAll();

        responder(true, 'Informes generados.', [
            'desde' => $desde,
            'hasta' => $hasta,
            'herramientas' => $herramientas,
            'clientes' => $clientes,
            'estados' => $estados,
            'meses' => $meses,
        ]);
    }

    responder(false, 'Acción no válida.', [], 400);
} catch (PDOException $error) {
    responder(false, 'No se pudieron generar los informes.', [], 500);
}
