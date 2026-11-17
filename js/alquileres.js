document.addEventListener("DOMContentLoaded", () => {

    // ELEMENTOS HTML
    const tablaHerramientas = document.getElementById("tabla-herramientas");
    const selectCliente = document.getElementById("cliente_id");
    const inputFechaInicio = document.getElementById("fecha_inicio");
    const inputFechaFin = document.getElementById("fecha_fin");

    // URL DE LAS API
    const API_URL = "../api/herramientas.php?accion=listar";
    const API_CLIENTES_URL = "../api/clientes.php?accion=listar";
    const API_ALQUILER_URL = "../api/alquileres.php";
    

    // VARIABLES
    let herramientasCache = [];
    let carritoAlquiler = {};
    let enviandoAlquiler = false;


    // CARGAR CLIENTES
    function cargarClientes() {

        fetch(API_CLIENTES_URL)
            .then(response => {

                if (!response.ok) {
                    throw new Error("Error al cargar clientes.");
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


// CARGAR HERRAMIENTAS
function cargarHerramientas() {

    fetch(API_URL)
        .then(async response => {

            const texto = await response.text();

            let resultado;

            try {
                resultado = JSON.parse(texto);
            } catch (error) {
                console.error("Respuesta recibida de herramientas.php:", texto);
                throw new Error("El servidor devolvió una respuesta que no es JSON.");
            }

        if (!response.ok || !resultado.correcto) {
            throw new Error(
                resultado.mensaje || "Error al cargar herramientas."
            );
        }

            return resultado;

        })
        .then(resultado => {

        herramientasCache = resultado.datos.herramientas.filter(
            herramienta => Number(herramienta.activo) === 1
        );

            renderizarHerramientas();

        })
        .catch(error => {

            console.error("Error al cargar herramientas:", error);

            tablaHerramientas.textContent = "";

            const fila = document.createElement("tr");
            const celda = document.createElement("td");

            celda.colSpan = 5;
            celda.textContent = "Error al cargar las herramientas.";

            fila.appendChild(celda);
            tablaHerramientas.appendChild(fila);

        });
}


    // MOSTRAR HERRAMIENTAS
    function renderizarHerramientas() {

        tablaHerramientas.innerHTML = "";

        if (herramientasCache.length === 0) {

            const fila = document.createElement("tr");
            const celda = document.createElement("td");

            celda.colSpan = 5;
            celda.textContent = "No hay herramientas disponibles.";

            fila.appendChild(celda);
            tablaHerramientas.appendChild(fila);

            return;
        }

        let totalAlquiler = 0;

        herramientasCache.forEach(herramienta => {

            const id = Number(herramienta.id);
            const nombre = herramienta.nombre;
            const stock = Number(herramienta.stock) || 0;
            const precio = Number(herramienta.precio_dia) || 0;

            const cantidad = carritoAlquiler[id] || 0;

            const subtotal = precio * cantidad;

            totalAlquiler += subtotal;

            const fila = document.createElement("tr");

            // Nombre
            const celdaNombre = document.createElement("td");
            celdaNombre.textContent = nombre;

            // Precio
            const celdaPrecio = document.createElement("td");
            celdaPrecio.textContent = "$ " + precio.toFixed(2);

            // Stock
            const celdaStock = document.createElement("td");
            celdaStock.textContent = stock;

            // Cantidad seleccionada
            const celdaCantidad = document.createElement("td");
            celdaCantidad.textContent = cantidad;

            // Acciones
            const celdaAcciones = document.createElement("td");

            const btnMas = document.createElement("button");

            btnMas.type = "button";
            btnMas.className = "btn-mas";
            btnMas.textContent = "+";
            btnMas.disabled = cantidad >= stock;

            btnMas.addEventListener("click", () => {
                incrementarAlquiler(id);
            });

            const btnMenos = document.createElement("button");

            btnMenos.type = "button";
            btnMenos.className = "btn-menos";
            btnMenos.textContent = "-";
            btnMenos.disabled = cantidad === 0;

            btnMenos.addEventListener("click", () => {
                disminuirAlquiler(id);
            });

            celdaAcciones.appendChild(btnMas);
            celdaAcciones.appendChild(btnMenos);

            // Agregar celdas
            fila.appendChild(celdaNombre);
            fila.appendChild(celdaPrecio);
            fila.appendChild(celdaStock);
            fila.appendChild(celdaCantidad);
            fila.appendChild(celdaAcciones);

            tablaHerramientas.appendChild(fila);

        });


        // TOTAL POR DÍA
        const filaTotal = document.createElement("tr");

        const celdaTitulo = document.createElement("td");
        celdaTitulo.colSpan = 3;

        const textoTotal = document.createElement("strong");
        textoTotal.textContent = "Total por día:";

        celdaTitulo.appendChild(textoTotal);

        const celdaTotal = document.createElement("td");
        celdaTotal.colSpan = 2;

        const valorTotal = document.createElement("strong");
        valorTotal.textContent = "$ " + totalAlquiler.toFixed(2);

        celdaTotal.appendChild(valorTotal);

        filaTotal.appendChild(celdaTitulo);
        filaTotal.appendChild(celdaTotal);

        tablaHerramientas.appendChild(filaTotal);

    }


    // AGREGAR HERRAMIENTA
    function incrementarAlquiler(id) {

        const herramienta = herramientasCache.find(
            h => Number(h.id) === id
        );

        if (!herramienta) return;

        const stock = Number(herramienta.stock) || 0;
        const cantidadActual = carritoAlquiler[id] || 0;

        if (cantidadActual >= stock) {

            alert("No hay suficiente stock disponible.");

            return;
        }

        carritoAlquiler[id] = cantidadActual + 1;

        renderizarHerramientas();

    }


    // QUITAR HERRAMIENTA
    function disminuirAlquiler(id) {

        if (!carritoAlquiler[id]) return;

        carritoAlquiler[id]--;

        if (carritoAlquiler[id] === 0) {
            delete carritoAlquiler[id];
        }

        renderizarHerramientas();

    }


    // OBTENER FECHA LOCAL ACTUAL
    function obtenerFechaActual() {

        const hoy = new Date();

        const anio = hoy.getFullYear();
        const mes = String(hoy.getMonth() + 1).padStart(2, "0");
        const dia = String(hoy.getDate()).padStart(2, "0");

        return `${anio}-${mes}-${dia}`;

    }


    // CONFIRMAR ALQUILER
    window.finalizarAlquiler = async function() {

        if (enviandoAlquiler) return;

        // DATOS DEL FORMULARIO
        const clienteId = selectCliente.value;
        const fechaInicio = inputFechaInicio.value;
        const fechaFin = inputFechaFin.value;

        // VALIDAR CLIENTE
        if (!clienteId) {

            alert("Seleccioná un cliente.");

            return;
        }

        // VALIDAR FECHAS
        if (!fechaInicio || !fechaFin) {

            alert("Seleccioná las fechas del alquiler.");

            return;
        }

        // VALIDAR FECHA DE INICIO
        if (fechaInicio < obtenerFechaActual()) {

            alert("La fecha de inicio no puede ser anterior a hoy.");

            return;
        }

        // VALIDAR FECHA DE DEVOLUCIÓN
        if (fechaFin < fechaInicio) {

            alert("La fecha de devolución no puede ser anterior al inicio.");

            return;
        }

        // OBTENER HERRAMIENTAS SELECCIONADAS
        const herramientasSeleccionadas = Object.keys(carritoAlquiler).map(id => ({

            id_herramienta: Number(id),
            cantidad: carritoAlquiler[id]

        }));

        // VALIDAR CARRITO
        if (herramientasSeleccionadas.length === 0) {

            alert("Seleccioná al menos una herramienta.");

            return;
        }

        // CONFIRMAR
        if (!confirm("¿Deseás confirmar el alquiler?")) {
            return;
        }

        enviandoAlquiler = true;

        try {

            // ENVIAR ALQUILER AL SERVIDOR
            const response = await fetch(API_ALQUILER_URL, {

                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({

                    cliente_id: Number(clienteId),
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    herramientas: herramientasSeleccionadas

                })

            });

            const resultado = await response.json();

            if (!response.ok || !resultado.success) {

                throw new Error(
                    resultado.message || "Error al registrar el alquiler."
                );

            }

            // ÉXITO
            alert(
                "¡Alquiler registrado correctamente!\n" +
                "Total: $ " + Number(resultado.total).toFixed(2)
            );

            // LIMPIAR CARRITO
            carritoAlquiler = {};

            // LIMPIAR FORMULARIO
            selectCliente.value = "";
            inputFechaInicio.value = "";
            inputFechaFin.value = "";

            // ACTUALIZAR HERRAMIENTAS Y STOCK
            cargarHerramientas();

        }
        catch (error) {

            console.error("Error al registrar alquiler:", error);

            alert(
                error.message ||
                "Ocurrió un error al registrar el alquiler."
            );

        }
        finally {

            enviandoAlquiler = false;

        }

    };


    // INICIALIZAR
    cargarClientes();
    cargarHerramientas();

});
