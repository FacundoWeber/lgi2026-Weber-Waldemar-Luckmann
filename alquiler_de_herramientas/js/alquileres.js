document.addEventListener("DOMContentLoaded", () => {

    const tablaHerramientas = document.getElementById("tabla-herramientas");

    const API_URL = "../api/herramientas.php";
    const API_ALQUILER_URL = "../api/alquileres.php";

    let herramientasCache = [];
    let carritoAlquiler = {};

    // Cargar herramientas
    function cargarHerramientas() {

        fetch(API_URL)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error en la respuesta del servidor");
                }
                return response.json();
            })
            .then(data => {

                // Filtrar herramientas activas
                herramientasCache = data.filter(h => Number(h.activo) === 1);

                renderizarHerramientas();

            })
            .catch(error => {
                console.error("Error al cargar herramientas:", error);

                tablaHerramientas.innerHTML = `
                    <tr>
                        <td colspan="5">Error al cargar las herramientas.</td>
                    </tr>
                `;
            });
    }

    // Mostrar herramientas
    function renderizarHerramientas() {

        tablaHerramientas.innerHTML = "";

        if (herramientasCache.length === 0) {

            tablaHerramientas.innerHTML = `
                <tr>
                    <td colspan="5">No hay herramientas disponibles.</td>
                </tr>
            `;

            return;
        }

        let totalAlquiler = 0;

        herramientasCache.forEach(herramienta => {

            const id = parseInt(herramienta.id);
            const nombre = herramienta.nombre;
            const stock = parseInt(herramienta.stock) || 0;
            const precio = parseFloat(herramienta.precio_dia) || 0;

            const cantidad = carritoAlquiler[id] || 0;

            const subtotal = precio * cantidad;

            totalAlquiler += subtotal;

            const fila = document.createElement("tr");

            fila.innerHTML = `
                <td>${nombre}</td>

                <td>$ ${precio.toFixed(2)}</td>

                <td>${stock}</td>

                <td>${cantidad}</td>

                <td>
                    <button type="button"
                        class="btn-mas"
                        onclick="incrementarAlquiler(${id})"
                        ${cantidad >= stock ? "disabled" : ""}>
                        +
                    </button>

                    <button type="button"
                        class="btn-menos"
                        onclick="disminuirAlquiler(${id})"
                        ${cantidad === 0 ? "disabled" : ""}>
                        -
                    </button>
                </td>
            `;

            tablaHerramientas.appendChild(fila);

        });

        // Mostrar total
        const filaTotal = document.createElement("tr");

        filaTotal.innerHTML = `
            <td colspan="3"><strong>Total por día:</strong></td>

            <td colspan="2">
                <strong>$ ${totalAlquiler.toFixed(2)}</strong>
            </td>
        `;

        tablaHerramientas.appendChild(filaTotal);
    }

    // Agregar herramienta
    window.incrementarAlquiler = function(id) {

        const herramienta = herramientasCache.find(h => Number(h.id) === id);

        if (!herramienta) return;

        const stock = parseInt(herramienta.stock) || 0;

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

                cliente_id: 1,
                vendedor_id: 1,

                fecha_inicio: "2026-11-11",
                fecha_fin: "2026-11-13",

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

        .catch(error => {

            console.error("Error al registrar alquiler:", error);

            alert("Ocurrió un error al registrar el alquiler.");

        });

    };

    // Inicializar
    cargarHerramientas();

});