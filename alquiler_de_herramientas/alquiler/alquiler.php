<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Alquiler de herramientas</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f4f4f4;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #333;
            color: white;
        }

        button {
            padding: 8px 14px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        input, select {
            padding: 8px;
            margin: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn-alquilar {
            background-color: #28a745;
            color: white;
            margin-top: 20px;
            padding: 12px 20px;
        }

        .btn-mas {
            background-color: #007bff;
            color: white;
        }

        .btn-menos {
            background-color: #dc3545;
            color: white;
        }

        button:hover {
            opacity: 0.8;
        }

    </style>

</head>

<body>

    <h1>Alquiler de herramientas</h1>

    <h2>Datos del alquiler</h2>

    <!-- Seleccionar cliente -->
    <label for="cliente_id">Cliente:</label>

    <select id="cliente_id">
        <option value="">Seleccionar cliente</option>
    </select>

    <br><br>

    <!-- Fechas del alquiler -->
    <label for="fecha_inicio">Fecha de inicio:</label>

    <input type="date" id="fecha_inicio">

    <label for="fecha_fin">Fecha de devolución:</label>

    <input type="date" id="fecha_fin">


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
                <td colspan="5">Cargando herramientas...</td>
            </tr>

        </tbody>

    </table>


    <div style="text-align: center;">

        <button type="button"
                class="btn-alquilar"
                onclick="finalizarAlquiler()">

            Confirmar alquiler

        </button>

    </div>


    <script src="../js/alquiler.js"></script>

</body>

</html>