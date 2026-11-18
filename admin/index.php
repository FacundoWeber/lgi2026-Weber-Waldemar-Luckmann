<?php
// Panel del administrador: resumen del sistema.

require_once __DIR__ . '/../includes/plantilla.php';
require_once __DIR__ . '/../config/database.php';

$usuario = exigirRol('administrador');

// Números del panel.
$resumen = [
    'clientes' => 0,
    'disponibles' => 0,
    'alquiladas' => 0,
    'pendientes' => 0,
    'ingresos' => 0.0,
];

$errorResumen = false;

try {
    $conexion = obtenerConexion();

    // Clientes habilitados.
    $resumen['clientes'] = (int) $conexion
        ->query('SELECT COUNT(*) FROM clientes WHERE activo = 1')
        ->fetchColumn();

    // Unidades en stock de las herramientas activas.
    $resumen['disponibles'] = (int) $conexion
        ->query('SELECT COALESCE(SUM(stock), 0) FROM herramientas WHERE activo = 1')
        ->fetchColumn();

    // Unidades que hoy están en alquileres activos.
    $resumen['alquiladas'] = (int) $conexion
        ->query("
            SELECT COALESCE(SUM(d.cantidad), 0)
            FROM detalle_alquiler d
            INNER JOIN alquileres a ON a.id = d.alquiler_id
            WHERE a.estado = 'activo'
        ")
        ->fetchColumn();

    // Alquileres que todavía no se devolvieron.
    $resumen['pendientes'] = (int) $conexion
        ->query("SELECT COUNT(*) FROM alquileres WHERE estado = 'activo'")
        ->fetchColumn();

    // Total facturado en el mes en curso.
    $resumen['ingresos'] = (float) $conexion
        ->query("
            SELECT COALESCE(SUM(total), 0)
            FROM alquileres
            WHERE estado <> 'cancelado'
            AND YEAR(fecha_inicio) = YEAR(CURDATE())
            AND MONTH(fecha_inicio) = MONTH(CURDATE())
        ")
        ->fetchColumn();
} catch (PDOException $error) {
    $errorResumen = true;
}

mostrarEncabezado('Panel de administración');
?>
<section class="panel panel-ancho">
    <h1>Hola, <?= htmlspecialchars($usuario['nombre']) ?></h1>
    <p>Resumen del sistema al <?= date('d/m/Y') ?>.</p>

    <?php if ($errorResumen): ?>
        <p class="mensaje mensaje-error">No se pudo leer el resumen. Revisá la base de datos.</p>
    <?php endif; ?>

    <div class="tarjetas tarjetas-datos">
        <a class="tarjeta" href="<?= url('/vendedor/clientes.php') ?>">
            <span class="numero"><?= $resumen['clientes'] ?></span>
            <h2>Clientes activos</h2>
            <p>Personas habilitadas para alquilar.</p>
        </a>

        <a class="tarjeta" href="<?= url('/admin/herramientas.php') ?>">
            <span class="numero"><?= $resumen['disponibles'] ?></span>
            <h2>Herramientas en stock</h2>
            <p>Unidades disponibles para alquilar.</p>
        </a>

        <a class="tarjeta" href="<?= url('/admin/gestionar_alquileres.php') ?>">
            <span class="numero"><?= $resumen['alquiladas'] ?></span>
            <h2>Herramientas alquiladas</h2>
            <p>Unidades que están hoy en la calle.</p>
        </a>

        <a class="tarjeta" href="<?= url('/admin/gestionar_alquileres.php') ?>">
            <span class="numero"><?= $resumen['pendientes'] ?></span>
            <h2>Pendientes de devolución</h2>
            <p>Alquileres activos sin devolver.</p>
        </a>

        <div class="tarjeta">
            <span class="numero">$ <?= number_format($resumen['ingresos'], 2, ',', '.') ?></span>
            <h2>Ingresos del mes</h2>
            <p>Total de los alquileres del mes en curso.</p>
        </div>
    </div>

    <h2>Administración</h2>

    <div class="tarjetas">
        <a class="tarjeta" href="<?= url('/admin/herramientas.php') ?>">
            <h2>Herramientas</h2>
            <p>Alta, edición, búsqueda y activación de herramientas.</p>
        </a>

        <a class="tarjeta" href="<?= url('/admin/gestionar_alquileres.php') ?>">
            <h2>Alquileres</h2>
            <p>Consulta y seguimiento de los alquileres realizados.</p>
        </a>

        <a class="tarjeta" href="<?= url('/admin/informes.php') ?>">
            <h2>Informes</h2>
            <p>Herramientas más pedidas, clientes frecuentes y actividad por mes.</p>
        </a>
    </div>
</section>
<?php mostrarPie(); ?>
