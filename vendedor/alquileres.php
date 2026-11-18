<?php
// Registro de alquileres. Los clientes y las herramientas los carga
// js/alquileres.js desde la API.

require_once __DIR__ . '/../includes/plantilla.php';

exigirRol('vendedor');

mostrarEncabezado('Alquileres', ['/css/alquileres.css']);
?>
<section class="panel panel-ancho">

    <h1>Registrar alquiler</h1>

    <!-- DATOS DEL ALQUILER -->
    <section class="seccion-alquileres">

        <h2>Datos del alquiler</h2>

        <div class="campos-alquileres">

            <div>
                <label for="cliente_id">Cliente</label>
                <select id="cliente_id" required>
                    <option value="">Seleccionar cliente</option>
                </select>
            </div>

            <div>
                <label for="fecha_inicio">Fecha de inicio</label>
                <input type="date" id="fecha_inicio" required>
            </div>

            <div>
                <label for="fecha_fin">Fecha de devolución</label>
                <input type="date" id="fecha_fin" required>
            </div>

        </div>

    </section>

    <!-- HERRAMIENTAS DISPONIBLES -->
    <section class="seccion-alquileres">

        <h2>Herramientas disponibles</h2>

        <div class="tabla-alquileres-contenedor">

            <table class="tabla-alquileres">

                <thead>
                    <tr>
                        <th>Herramienta</th>
                        <th>Precio</th>
                        <th>Stock disponible</th>
                        <th>Cantidad seleccionada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="tabla-herramientas">
                    <tr>
                        <td colspan="5">Cargando herramientas...</td>
                    </tr>
                </tbody>

            </table>

        </div>

    </section>

    <!-- CONFIRMAR ALQUILER -->
    <div class="acciones-alquiler">

        <button type="button" class="btn-alquilar" onclick="finalizarAlquiler()">
            Confirmar alquiler
        </button>

    </div>

</section>
<?php mostrarPie(['/js/alquileres.js']); ?>
