document.addEventListener("DOMContentLoaded", () => {

    const tablaHerramientas = document.getElementById("tabla-herramientas");
    const selectCliente = document.getElementById("cliente_id");
    const inputFechaInicio = document.getElementById("fecha_inicio");
    const inputFechaFin = document.getElementById("fecha_fin");

    const API_URL = "../api/herramientas.php";
    const API_CLIENTES_URL = "../api/clientes.php";
    const API_ALQUILER_URL = "../api/alquileres.php";

    let herramientasCache = [];
    let carritoAlquiler = {};

    // Cargar clientes
    function cargarClientes() {

        fetch(API_CLIENTES_URL)
            .then(response => {

                if (!response.ok) {
                    throw new Error("Error al cargar clientes");
                }

                return response.json();

            })
            .then(data => {

                selectCliente.innerHTML = `
                    <option value="">Seleccionar cliente</option>
                `;

                data.forEach(cliente => {

                    const opcion = document.createElement("option");

                    opcion.value = cliente.id;
                    opcion.textContent = cliente.nombre;

                    selectCliente.appendChild(opcion);

                });

            })
            .catch(error => {

                console.error("Error al cargar clientes:", error);

                alert("No se pudieron cargar los clientes.");

            });
    }


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

                herramientasCache = data.filter(
                    h => Number(h.activo) === 1
                );

                renderizarHerramientas();

            })
            .catch(error => {

                console.error("Error al cargar herramientas:", error);

                tablaHerramientas.innerHTML = `
                    <tr>
                        <td colspan="5">
                            Error al cargar las herramientas.
                        </td>
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
                    <td colspan="5">
                        No hay herramientas disponibles.
                    </td>
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


        // Mostrar total por día
        const filaTotal = document.createElement("tr");

        filaTotal.innerHTML = `
            <td colspan="3">
                <strong>Total por día:</strong>
            </td>

            <td colspan="2">
                <strong>$ ${totalAlquiler.toFixed(2)}</strong>
            </td>
        `;

        tablaHerramientas.appendChild(filaTotal);

    }


    // Agregar herramienta
    window.incrementarAlquiler = function(id) {

        const herramienta = herramientasCache.find(
            h => Number(h.id) === id
        );

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

        // Obtener datos del formulario
        const clienteId = selectCliente.value;

        const fechaInicio = inputFechaInicio.value;

        const fechaFin = inputFechaFin.value;


        // Validar cliente
        if (!clienteId) {

            alert("Seleccioná un cliente.");

            return;
        }


        // Validar fechas
        if (!fechaInicio || !fechaFin) {

            alert("Seleccioná las fechas del alquiler.");

            return;
        }


        if (fechaFin < fechaInicio) {

            alert("La fecha de devolución no puede ser anterior al inicio.");

            return;
        }


        // Obtener herramientas seleccionadas
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


        // Enviar alquiler al servidor
        fetch(API_ALQUILER_URL, {

            method: "POST",

            headers: {

                "Content-Type": "application/json"

            },

            body: JSON.stringify({

                cliente_id: parseInt(clienteId),

                fecha_inicio: fechaInicio,

                fecha_fin: fechaFin,

                herramientas: herramientasSeleccionadas

            })

        })

        .then(response => {

            return response.json().then(resultado => ({

                ok: response.ok,

                resultado: resultado

            }));

        })

        .then(({ ok, resultado }) => {

            if (!ok || !resultado.success) {

                throw new Error(
                    resultado.message || "Error al registrar el alquiler."
                );

            }


            alert(
                "¡Alquiler registrado correctamente!\n" +
                "Total: $ " + Number(resultado.total).toFixed(2)
            );


            // Limpiar carrito
            carritoAlquiler = {};


            // Recargar herramientas y stock
            cargarHerramientas();


            // Limpiar fechas y cliente
            selectCliente.value = "";

            inputFechaInicio.value = "";

            inputFechaFin.value = "";

        })

        .catch(error => {

            console.error("Error al registrar alquiler:", error);

            alert(error.message || "Ocurrió un error al registrar el alquiler.");

        });

    };


    // Inicializar
    cargarClientes();

    cargarHerramientas();

});