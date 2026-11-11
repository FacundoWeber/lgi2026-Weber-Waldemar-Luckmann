<?php
require_once __DIR__ . '/../includes/auth.php';
exigirRol('administrador');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de herramientas</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/herramientas.css">
</head>
<body>
<main class="panel panel-ancho">
    <h1>Gestión de herramientas</h1>
    <p>Desde aquí podés consultar, agregar, modificar y desactivar herramientas.</p>
    <p><a href="index.php">← Volver al panel</a></p>

    <section class="seccion-herramientas">
        <h2 id="titulo-formulario">Agregar herramienta</h2>
        <form id="formulario-herramienta">
            <input type="hidden" id="herramienta-id" name="id">

            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" maxlength="100" required>

            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria_id">
                <option value="">Sin categoría</option>
            </select>

            <div class="campos-dos">
                <div>
                    <label for="precio">Precio por día ($)</label>
                    <input type="number" id="precio" name="precio_dia" min="0" step="0.01" required>
                </div>
                <div>
                    <label for="stock">Stock disponible</label>
                    <input type="number" id="stock" name="stock" min="0" step="1" required>
                </div>
                <div>
                    <label for="stock-minimo">Stock mínimo</label>
                    <input type="number" id="stock-minimo" name="stock_minimo" min="0" step="1" required>
                </div>
            </div>

            <div class="acciones-formulario">
                <button type="submit" id="boton-guardar">Guardar herramienta</button>
                <button type="button" id="boton-cancelar" class="boton-secundario" hidden>Cancelar edición</button>
            </div>
            <p id="mensaje-formulario" class="mensaje" role="status"></p>
        </form>
    </section>

    <section class="seccion-herramientas">
        <h2>Listado de herramientas</h2>
        <label for="buscar-herramienta">Buscar por nombre o categoría</label>
        <input type="search" id="buscar-herramienta" placeholder="Escribí para buscar...">
        <p id="mensaje-listado" class="mensaje" role="status"></p>
        <div class="tabla-contenedor">
            <table class="tabla-herramientas">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio/día</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-herramientas">
                    <tr><td colspan="6">Cargando herramientas...</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script src="../js/herramientas.js"></script>
</body>
</html>
