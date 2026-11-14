<?php
// Registro de alquileres. Por ahora es una pantalla vacía.

require_once __DIR__ . '/../includes/plantilla.php';

exigirRol('vendedor');

mostrarEncabezado('Alquileres');
?>
<section class="panel panel-ancho">
    <h1>Alquileres</h1>
    <p>Esta pantalla todavía no está implementada.</p>
    <p>Va a permitir elegir un cliente y una o más herramientas, calcular el total por día y registrar el alquiler.</p>
    <p><a href="<?= url('/vendedor/index.php') ?>">Volver al panel</a></p>
</section>
<?php mostrarPie(); ?>
