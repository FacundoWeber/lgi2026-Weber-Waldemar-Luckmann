<?php
// Gestión de clientes. Por ahora es una pantalla vacía.

require_once __DIR__ . '/../includes/plantilla.php';

exigirRol('vendedor');

mostrarEncabezado('Clientes');
?>
<section class="panel panel-ancho">
    <h1>Clientes</h1>
    <p>Esta pantalla todavía no está implementada.</p>
    <p>Va a permitir buscar, agregar, editar y activar o desactivar clientes.</p>
    <p><a href="<?= url('/vendedor/index.php') ?>">Volver al panel</a></p>
</section>
<?php mostrarPie(); ?>
