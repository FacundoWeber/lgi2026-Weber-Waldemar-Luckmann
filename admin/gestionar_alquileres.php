
<?php
// Gestión de alquileres.
// Los datos se cargan desde js/gestionar_alquileres.js
// mediante api/alquileres.php.

require_once __DIR__ . '/../includes/plantilla.php';

exigirRol('administrador');

mostrarEncabezado('Gestión de alquileres');
?>

<section class="panel panel-ancho">

    <h1>Gestión de alquileres</h1>
    <p>Consulta y administra los alquileres registrados en el sistema.</p>
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
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="lista-alquileres">
                    <tr>
                        <td colspan="9">Cargando alquileres...</td>
                    </tr>
                </tbody>
            </table>

        </div>

    </section>

</section>

<!-- Modal para editar un alquiler -->
<div id="modal-editar-alquiler" class="modal" hidden>

    <div class="modal-contenido">

        <h2>Editar detalles del alquiler</h2>

        <p id="mensaje-edicion" class="mensaje" role="status"></p>

        <form id="form-editar-alquiler">

            <input type="hidden" id="editar-alquiler-id">

            <div class="campo-formulario">
                <label for="editar-cliente">Cliente</label>
                <select id="editar-cliente" required>
                    <option value="">Seleccionar cliente</option>
                </select>
            </div>

            <div class="campo-formulario">
                <label for="editar-fecha-inicio">Fecha de inicio</label>
                <input type="date" id="editar-fecha-inicio" required>
            </div>

            <div class="campo-formulario">
                <label for="editar-fecha-fin">Fecha de devolución</label>
                <input type="date" id="editar-fecha-fin" required>
            </div>

            <div class="campo-formulario">
                <label for="editar-estado">Estado</label>
                <select id="editar-estado" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="activo">Activo</option>
                    <option value="devuelto">Devuelto</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>

            <div class="campo-formulario">

                <label>Herramientas del alquiler</label>

                <div id="lista-herramientas-edicion">
                    <!-- Las herramientas se agregan desde JavaScript -->
                </div>

                <button type="button" id="btn-agregar-herramienta">
                    Agregar herramienta
                </button>

            </div>

            <div class="campo-formulario">
                <strong>Total estimado:</strong>
                <span id="editar-total">$0,00</span>
            </div>

            <div class="acciones-formulario">

                <button type="submit">
                    Guardar cambios
                </button>

                <button type="button" id="btn-desactivar-alquiler">
                    Desactivar alquiler
                </button>

                <button type="button" id="btn-cerrar-modal">
                    Cerrar
                </button>

            </div>

        </form>

    </div>

</div>

<script src="../js/gestionar_alquileres.js"></script>

<?php
mostrarPie();
?>
