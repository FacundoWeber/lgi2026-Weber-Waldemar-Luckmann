<?php
// Gestión de vendedores. Por ahora es una pantalla vacía.

require_once __DIR__ . '/../includes/plantilla.php';

exigirRol('administrador');

mostrarEncabezado('Gestión de vendedores');
?>
<section class="panel panel-ancho">
    <h1>Gestión de vendedores</h1>
    <p>Esta pantalla todavía no está implementada.</p>
    <p>Tiene que listar, agregar, editar y activar o desactivar usuarios con rol vendedor.</p>
    <p><a href="<?= url('/admin/index.php') ?>">Volver al panel</a></p>
</section>
<?php mostrarPie(); ?>
