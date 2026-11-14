<?php
// Panel del cliente. La consigna no pide que el cliente inicie sesión, así que
// esta pantalla queda para más adelante.

require_once __DIR__ . '/../includes/plantilla.php';

exigirRol('cliente');

mostrarEncabezado('Panel de cliente');
?>
<section class="panel panel-ancho">
    <h1>Panel de cliente</h1>
    <p>Interfaz prevista para una etapa posterior.</p>
</section>
<?php mostrarPie(); ?>
