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
        <a class="tarjeta" href="<?= url('/vendedor/alquileres.php') ?>">
            <h2>Alquileres</h2>
            <p>Registrar alquileres y gestionar su estado.</p>
        </a>

        <a class="tarjeta" href="<?= url('/vendedor/clientes.php') ?>">
            <h2>Clientes</h2>
            <p>Alta, edición y activación de clientes.</p>
        </a>
    </div>
</section>
<?php mostrarPie(); ?>
