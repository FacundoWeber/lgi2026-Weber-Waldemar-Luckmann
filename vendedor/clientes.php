
<?php
// Gestión de clientes

require_once __DIR__ . '/../includes/plantilla.php';

exigirRol('vendedor');

mostrarEncabezado('Clientes');
?>

<section class="panel panel-ancho">

    <h1>Listado de clientes</h1>

    <!-- BÚSQUEDA -->
    <div class="busqueda">
        <h4>Búsqueda</h4>

        <form id="busqueda">
            <label for="cliente_busqueda">Buscar Cliente:</label>

            <input
                type="text"
                id="cliente_busqueda"
                name="Cliente"
                placeholder="Buscar"
            >

            <button type="submit">Buscar</button>
        </form>
    </div>

    <!-- TABLA -->
    <h2>Listado de Clientes</h2>

    <table class="tabla-clientes" border="4">

        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Activo</th>
                <th>Deuda</th>
                <th>Seleccionar</th>
            </tr>
        </thead>

        <tbody id="tabla_clientes"></tbody>

    </table>

    <p>
        Total de clientes:
        <strong id="totalClientes">0</strong>
    </p>

    <!-- FORMULARIO -->
    <section class="formulario">

        <h3>Clientes</h3>

        <form id="agregar_cliente">

            <label>ID:</label>
            <input type="text" id="idSeleccionar" name="id" placeholder="ID"><br>

            <label>Nombre:</label>
            <input type="text" id="nombre" name="nombre" placeholder="Nombre" required><br>

            <label>DNI:</label>
            <input type="text" id="dni" name="dni" placeholder="DNI" required><br>

            <label>Teléfono:</label>
            <input type="text" id="telefono" name="telefono" placeholder="Teléfono" required><br><br>

            <label>Email:</label>
            <input type="text" id="email" name="email" placeholder="Email" required><br><br>

            <label>Activo:</label>
            <input type="number" id="activo" name="activo" min="0" max="1" value="1" required><br><br>

            <label>Deuda:</label>
            <input type="number" id="deuda" name="deuda" step="0.01" value="0" required><br><br>

            <button type="submit" id="btnAgregar" class="btnAgregar">
                Agregar Cliente
            </button>

            <button type="button" id="btnActualizar" class="btnActualizar">
                Actualizar Cliente
            </button>

        </form>

    </section>

    <p>
        <a href="<?= url('/vendedor/index.php') ?>">Volver al panel</a>
    </p>

</section>

<script type="module" src="<?= version('/js/clientes.js') ?>"></script>

<?php mostrarPie(); ?>

