<?php
// Panel del vendedor (operador).

require_once __DIR__ . '/../includes/plantilla.php';

$usuario = exigirRol('vendedor');

mostrarEncabezado('Panel de vendedor');
?>
<section class="panel panel-ancho">
    <h1>Hola, <?= htmlspecialchars($usuario['nombre']) ?></h1>
    <p>Desde este panel se registran los alquileres y se gestionan los clientes.</p>

    <div class="tarjetas">
        <div class="tarjeta tarjeta-pendiente">
            <h2>Alquileres</h2>
            <p>Registrar alquiler, cambiar de estado y filtrar. <span class="etiqueta">Pendiente</span></p>
        </div>

        <div class="tarjeta tarjeta-pendiente">
            <h2>Clientes</h2>
            <p>Alta, edición y activación de clientes. <span class="etiqueta">Pendiente</span></p>
            <a href="<?= url('/vendedor/clientes.php') ?>" class="btn btn-primary">Gestionar Clientes</a>
        </div>
    </div>
</section>
<?php mostrarPie(); ?>
