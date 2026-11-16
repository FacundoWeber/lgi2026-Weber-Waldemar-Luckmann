<!DOCTYPE html>
<html lang="es">
<link rel="stylesheet" href="../css/alquileres.css">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alquiler de herramientas</title>
</head>

<body>

    <h1>Alquiler de herramientas</h1>


    <!-- DATOS DEL ALQUILER -->

    <h2>Datos del alquiler</h2>


    <!-- Seleccionar cliente -->

    <label for="cliente_id">Cliente:</label>

    <select id="cliente_id" required>

        <option value="">Seleccionar cliente</option>

    </select>


    <br><br>


    <!-- Fecha de inicio -->

    <label for="fecha_inicio">Fecha de inicio:</label>

    <input
        type="date"
        id="fecha_inicio"
        required
    >


    <!-- Fecha de devolución -->

    <label for="fecha_fin">Fecha de devolución:</label>

    <input
        type="date"
        id="fecha_fin"
        required
    >



    <!-- HERRAMIENTAS DISPONIBLES -->

    <h2>Herramientas disponibles</h2>


    <table>

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



    <!-- CONFIRMAR ALQUILER -->

    <div style="text-align: center;">

        <button
            type="button"
            class="btn-alquilar"
            onclick="finalizarAlquiler()">

            Confirmar alquiler

        </button>

    </div>



    <!-- JAVASCRIPT -->

    <script src="../js/alquileres.js"></script>

</body>

</html>