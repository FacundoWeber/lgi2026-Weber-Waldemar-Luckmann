<?php
// Panel del administrador.

require_once __DIR__ . '/../includes/plantilla.php';

$usuario = exigirRol('administrador');

mostrarEncabezado('Panel de administración');
?>
<section class="panel panel-ancho">
    <h1>Hola, <?= htmlspecialchars($usuario['nombre']) ?></h1>
    <p>Desde este panel se administra el catálogo de herramientas y los usuarios del sistema.</p>

    <div class="tarjetas">
        <a class="tarjeta" href="<?= url('/admin/herramientas.php') ?>">
            <h2>Herramientas</h2>
            <p>Alta, edición, búsqueda y activación de herramientas.</p>
        </a>

        <a class="tarjeta" href="<?= url('/admin/gestionar_alquileres.php') ?>">
            <h2>Alquileres</h2>
            <p>Consulta y seguimiento de los alquileres realizados.</p>
        </a>

        <div class="tarjeta tarjeta-pendiente">
            <h2>Usuarios</h2>
            <p>Alta y baja de vendedores. <span class="etiqueta">Pendiente</span></p>
        </div>
    </div>
</section>
<?php mostrarPie(); ?>