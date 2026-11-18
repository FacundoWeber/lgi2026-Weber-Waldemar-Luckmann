<?php
// Informes: actividad del sistema entre dos fechas.

require_once __DIR__ . '/../includes/plantilla.php';

exigirRol('administrador');

mostrarEncabezado('Informes');
?>
<section class="panel panel-ancho">

    <h1>Informes</h1>
    <p>Actividad del sistema entre dos fechas.</p>

    <form id="filtro-informes">
        <div class="campos-informe">
            <div>
                <label for="desde">Desde</label>
                <input type="date" id="desde" value="<?= date('Y-m-01') ?>">
            </div>

            <div>
                <label for="hasta">Hasta</label>
                <input type="date" id="hasta" value="<?= date('Y-m-t') ?>">
            </div>

            <div>
                <button type="submit">Generar</button>
            </div>
        </div>
    </form>

    <p id="mensaje" class="mensaje" role="status"></p>

    <h2>Herramientas más alquiladas</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>Herramienta</th>
                <th>Alquileres</th>
                <th>Unidades</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="informe-herramientas">
            <tr><td colspan="4">Cargando...</td></tr>
        </tbody>
    </table>

    <h2>Clientes más frecuentes</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Alquileres</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="informe-clientes">
            <tr><td colspan="3">Cargando...</td></tr>
        </tbody>
    </table>

    <h2>Alquileres por estado</h2>
    <div class="grafico">
        <canvas id="grafico-estados"></canvas>
    </div>
    <table class="tabla">
        <thead>
            <tr>
                <th>Estado</th>
                <th>Alquileres</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="informe-estados">
            <tr><td colspan="3">Cargando...</td></tr>
        </tbody>
    </table>

    <h2>Actividad por mes</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>Mes</th>
                <th>Alquileres</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="informe-meses">
            <tr><td colspan="3">Cargando...</td></tr>
        </tbody>
    </table>

</section>
<?php mostrarPie(['/js/chart.umd.min.js', '/js/informes.js']); ?>
