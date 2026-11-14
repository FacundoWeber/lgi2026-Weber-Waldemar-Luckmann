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

        <div class="tarjeta tarjeta-pendiente">
            <h2>Categorías</h2>
            <p>ABM de categorías. <span class="etiqueta">Pendiente</span></p>
        </div>

        <div class="tarjeta tarjeta-pendiente">
            <h2>Usuarios</h2>
            <p>Alta y baja de vendedores. <span class="etiqueta">Pendiente</span></p>
        </div>
    </div>
</section>
<?php mostrarPie(); ?>
