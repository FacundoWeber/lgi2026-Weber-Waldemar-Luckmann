//Implementar auth? si es que se necesita para algo
<?php
require_once __DIR__ . '/../includes/auth.php';
exigirRol('vendedor');
// TODO: registrar alquiler, elegir cliente/herramientas y calcular total.
?>
<h1>Registrar alquiler</h1><p>Completar esta sección.</p><a href="index.php">Volver</a>


document.addEventListener("DOMContentLoaded", () => {

    const tablaHerramientas = document.getElementById("tabla-herramientas");
    const API_URL = "api/herramientas.php";
    const API_ALQUILER_URL = "api/alquileres.php";

    let herramientasCache = [];
    let carritoAlquiler = {};

    // Cargar herramientas
    function cargarHerramientas() {
        fetch(API_URL)
            .then(response => response.json())
            .then(data => {
                herramientasCache = data;
                renderizarHerramientas();
            })
            .catch(error => console.error("Error al cargar herramientas:", error));
    }

    // Mostrar herramientas y carrito
    function renderizarHerramientas() {
        tablaHerramientas.innerHTML = "";

        if (herramientasCache.length === 0) {
            tablaHerramientas.innerHTML =
                `<tr><td colspan="5">No hay herramientas disponibles.</td></tr>`;
            return;
        }

        let totalAlquiler = 0;

        herramientasCache.forEach(herramienta => {

            const stock = parseInt(herramienta.Stock) || 0;
            const precio = parseFloat(herramienta.Precio) || 0;
            const cantidad = carritoAlquiler[herramienta.Id] || 0;

            const subtotal = precio * cantidad;
            totalAlquiler += subtotal;

            const fila = document.createElement("tr");

            fila.innerHTML = `
                <td>${herramienta.Nombre}</td>
                <td>$ ${precio.toFixed(2)}</td>
                <td>${stock}</td>
                <td>${cantidad}</td>
                <td>
                    <button type="button"
                        onclick="incrementarAlquiler(${herramienta.Id})">+</button>

                    <button type="button"
                        onclick="disminuirAlquiler(${herramienta.Id})">-</button>
                </td>
            `;

            tablaHerramientas.appendChild(fila);
        });

        const filaTotal = document.createElement("tr");

        filaTotal.innerHTML = `
            <td colspan="3"><strong>Total:</strong></td>
            <td colspan="2">
                <strong>$ ${totalAlquiler.toFixed(2)}</strong>
            </td>
        `;

        tablaHerramientas.appendChild(filaTotal);
    }

    // Agregar herramienta
    window.incrementarAlquiler = function(id) {

        const herramienta = herramientasCache.find(h => h.Id == id);
        if (!herramienta) return;

        const stock = parseInt(herramienta.Stock) || 0;
        const cantidadActual = carritoAlquiler[id] || 0;

        if (cantidadActual + 1 > stock) {
            alert("No hay suficiente stock disponible.");
            return;
        }

        carritoAlquiler[id] = cantidadActual + 1;

        renderizarHerramientas();
    };

    // Quitar herramienta
    window.disminuirAlquiler = function(id) {

        if (!carritoAlquiler[id]) return;

        carritoAlquiler[id]--;

        if (carritoAlquiler[id] === 0) {
            delete carritoAlquiler[id];
        }

        renderizarHerramientas();
    };

    // Confirmar alquiler
    window.finalizarAlquiler = function() {

        const herramientasSeleccionadas = Object.keys(carritoAlquiler).map(id => ({
            id_herramienta: parseInt(id),
            cantidad: carritoAlquiler[id]
        }));

        if (herramientasSeleccionadas.length === 0) {
            alert("Seleccioná al menos una herramienta.");
            return;
        }

        if (!confirm("¿Deseás confirmar el alquiler?")) {
            return;
        }

        fetch(API_ALQUILER_URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                herramientas: herramientasSeleccionadas
            })
        })
        .then(response => response.json())
        .then(resultado => {

            if (resultado.success) {
                alert("¡Alquiler registrado correctamente!");

                carritoAlquiler = {};
                cargarHerramientas();

            } else {
                alert("Error al registrar el alquiler: " + resultado.message);
            }

        })
        .catch(error => console.error("Error al registrar alquiler:", error));
    };

    cargarHerramientas();

});

//TODO:Implementar alquiler
//TODO:Disponibilidad a la fecha?
//TODO:Registrar
