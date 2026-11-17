<?php
// Gestión de alquileres. La página solo dibuja el HTML: los datos los trae
// js/gestionar_alquileres.js desde api/alquileres.php.

require_once __DIR__ . '/../includes/plantilla.php';

exigirRol('administrador');

mostrarEncabezado('Gestión de alquileres');
?>

<section class="panel panel-ancho">

    <h1>Gestión de alquileres</h1>
    <p>Consulta los alquileres registrados en el sistema y filtra por cliente.</p>
    <p><a href="<?= url('/admin/index.php') ?>">Volver al panel</a></p>

    <section class="seccion-herramientas">

        <h2>Filtros</h2>

        <label for="filtro-cliente">Filtrar por cliente</label>
        <select id="filtro-cliente">
            <option value="">Todos los clientes</option>
        </select>

    </section>

    <section class="seccion-herramientas">

        <h2>Listado de alquileres</h2>

        <p id="mensaje-listado" class="mensaje" role="status"></p>
        <div class="tabla-contenedor" style="border: 1px solid #dbe3ec; border-radius: 10px; padding: 12px; overflow-x: auto;">
            <div class="tabla-contenedor">
                <table id="tabla-alquileres">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Herramientas</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody id="lista-alquileres">
                        <tr>
                            <td colspan="8">Cargando alquileres...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </section>

</section>

<script src="../js/gestionar_alquileres.js"></script>

<?php
mostrarPie();
?>
```