
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alquiler de herramientas</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/alquileres.css">
</head>

<body>

    <main class="contenido">

        <div class="panel panel-ancho">

            <h1>Alquiler de herramientas</h1>

            <!-- DATOS DEL ALQUILER -->
            <section class="seccion-alquileres">

                <h2>Datos del alquiler</h2>

                <div class="campos-alquileres">

                    <div>
                        <label for="cliente_id">Cliente:</label>
                        <select id="cliente_id" required>
                            <option value="">Seleccionar cliente</option>
                        </select>
                    </div>

                    <div>
                        <label for="fecha_inicio">Fecha de inicio:</label>
                        <input type="date" id="fecha_inicio" required>
                    </div>

                    <div>
                        <label for="fecha_fin">Fecha de devolución:</label>
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
                                <td colspan="5">
                                    Cargando herramientas...
                                </td>
                            </tr>
                        </tbody>

                    </table>

                </div>

            </section>

            <!-- CONFIRMAR ALQUILER -->
            <div style="text-align: center;">

                <button
                    type="button"
                    class="btn-alquilar"
                    onclick="finalizarAlquiler()">

                    Confirmar alquiler

                </button>

            </div>

        </div>

    </main>

    <script src="../js/alquileres.js"></script>

</body>
</html>
