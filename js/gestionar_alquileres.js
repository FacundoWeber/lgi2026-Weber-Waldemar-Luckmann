
document.addEventListener("DOMContentLoaded", () => {

    const tabla = document.getElementById("lista-alquileres");
    const filtroCliente = document.getElementById("filtro-cliente");

    const modal = document.getElementById("modal-editar-alquiler");
    const formulario = document.getElementById("form-editar-alquiler");

    const mensajeListado = document.getElementById("mensaje-listado");
    const mensajeEdicion = document.getElementById("mensaje-edicion");

    const selectCliente = document.getElementById("editar-cliente");
    const fechaInicio = document.getElementById("editar-fecha-inicio");
    const fechaFin = document.getElementById("editar-fecha-fin");
    const selectEstado = document.getElementById("editar-estado");

    const listaHerramientas = document.getElementById("lista-herramientas-edicion");
    const totalElemento = document.getElementById("editar-total");

    const btnAgregar = document.getElementById("btn-agregar-herramienta");
    const btnDesactivar = document.getElementById("btn-desactivar-alquiler");
    const btnCerrar = document.getElementById("btn-cerrar-modal");

    let alquileres = [];
    let herramientasDisponibles = [];

    // =====================================================
    // FUNCIONES GENERALES
    // =====================================================

    async function solicitar(url, opciones = {}) {

        const respuesta = await fetch(url, opciones);
        const datos = await respuesta.json();

        if (!respuesta.ok || !datos.success) {
            throw new Error(
                datos.mensaje || datos.message || "Ocurrió un error."
            );
        }

        return datos;
    }

    function escaparHTML(valor) {

        return String(valor ?? "").replace(/[&<>"']/g, caracter => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        })[caracter]);
    }

    function formatearFecha(fecha) {

        if (!fecha) return "-";

        const [anio, mes, dia] = fecha.split("-");

        return `${dia}/${mes}/${anio}`;
    }

    function formatearPrecio(precio) {

        return Number(precio).toLocaleString("es-AR", {
            style: "currency",
            currency: "ARS",
            minimumFractionDigits: 2
        });
    }

    function mostrarMensaje(elemento, mensaje) {
        elemento.textContent = mensaje;
    }

    // =====================================================
    // CARGAR DATOS
    // =====================================================

    async function inicializar() {

        try {

            await cargarClientes();
            await cargarAlquileres();

        } catch (error) {

            console.error(error);

            tabla.innerHTML = `
                <tr>
                    <td colspan="9">No se pudieron cargar los datos.</td>
                </tr>
            `;

            mostrarMensaje(mensajeListado, error.message);
        }
    }

    async function cargarClientes() {

        const respuesta = await fetch("../api/clientes.php?accion=listar");

        const clientes = await respuesta.json();

        if (!respuesta.ok || !Array.isArray(clientes)) {
            throw new Error("Error al cargar los clientes.");
        }

        filtroCliente.innerHTML =
            '<option value="">Todos los clientes</option>';

        selectCliente.innerHTML =
            '<option value="">Seleccionar cliente</option>';

        clientes.forEach(cliente => {

            const opcionFiltro = document.createElement("option");
            opcionFiltro.value = cliente.id;
            opcionFiltro.textContent = cliente.nombre;

            filtroCliente.appendChild(opcionFiltro);

            const opcionEdicion = opcionFiltro.cloneNode(true);
            selectCliente.appendChild(opcionEdicion);
        });
    }

    async function cargarAlquileres() {

        const datos = await solicitar(
            "../api/alquileres.php?accion=listar"
        );

        alquileres = datos.alquileres;

        filtrarPorCliente();
    }

    // =====================================================
    // MOSTRAR ALQUILERES
    // =====================================================

    function mostrarAlquileres(lista) {

        if (lista.length === 0) {

            tabla.innerHTML = `
                <tr>
                    <td colspan="9">No se encontraron alquileres.</td>
                </tr>
            `;

            return;
        }

        tabla.innerHTML = lista.map(alquiler => {

            const estado = String(alquiler.estado).toLowerCase();

            return `
                <tr>
                    <td>${alquiler.id}</td>
                    <td>${escaparHTML(alquiler.cliente)}</td>
                    <td>${escaparHTML(alquiler.vendedor)}</td>
                    <td>${formatearFecha(alquiler.fecha_inicio)}</td>
                    <td>${formatearFecha(alquiler.fecha_fin)}</td>
                    <td>${alquiler.herramientas}</td>
                    <td>${formatearPrecio(alquiler.total)}</td>
                    <td>
                        <span class="estado estado-${escaparHTML(estado)}">
                            ${escaparHTML(
                                estado.charAt(0).toUpperCase() + estado.slice(1)
                            )}
                        </span>
                    </td>
                    <td>
                        <button
                            type="button"
                            class="btn-editar"
                            data-id="${alquiler.id}">
                            Editar
                        </button>
                    </td>
                </tr>
            `;

        }).join("");
    }

    function filtrarPorCliente() {

        const clienteId = filtroCliente.value;

        if (!clienteId) {
            mostrarAlquileres(alquileres);
            return;
        }

        const resultados = alquileres.filter(alquiler =>
            Number(alquiler.cliente_id) === Number(clienteId)
        );

        mostrarAlquileres(resultados);
    }

    // =====================================================
    // ABRIR MODAL
    // =====================================================

    async function abrirEdicion(id) {

        try {

            mostrarMensaje(mensajeEdicion, "Cargando datos...");

            formulario.reset();
            listaHerramientas.innerHTML = "";

            modal.hidden = false;

            const datos = await solicitar(
                `../api/alquileres.php?accion=detalle&id=${id}`
            );

            const alquiler = datos.alquiler;

            document.getElementById("editar-alquiler-id").value = alquiler.id;

            selectCliente.value = alquiler.cliente_id;
            fechaInicio.value = alquiler.fecha_inicio;
            fechaFin.value = alquiler.fecha_fin;
            selectEstado.value = alquiler.estado;

            herramientasDisponibles = datos.herramientas_disponibles;

            datos.detalles.forEach(detalle => {
                agregarFilaHerramienta(detalle);
            });

            actualizarTotal();

            mostrarMensaje(mensajeEdicion, "");

        } catch (error) {

            mostrarMensaje(mensajeEdicion, error.message);
        }
    }

    // =====================================================
    // HERRAMIENTAS DEL MODAL
    // =====================================================

    function agregarFilaHerramienta(detalle = null) {

        const fila = document.createElement("div");

        fila.className = "fila-herramienta-edicion";

        const opciones = herramientasDisponibles.map(herramienta => {

            const seleccionado =
                detalle &&
                Number(detalle.id_herramienta) === Number(herramienta.id);

            return `
                <option
                    value="${herramienta.id}"
                    data-precio="${herramienta.precio_dia}"
                    ${seleccionado ? "selected" : ""}>
                    ${escaparHTML(herramienta.nombre)}
                </option>
            `;

        }).join("");

        let opcionAnterior = "";

        if (
            detalle &&
            !herramientasDisponibles.some(h =>
                Number(h.id) === Number(detalle.id_herramienta)
            )
        ) {
            opcionAnterior = `
                <option
                    value="${detalle.id_herramienta}"
                    data-precio="${detalle.precio_dia}"
                    selected>
                    ${escaparHTML(detalle.nombre)} (inactiva)
                </option>
            `;
        }

        fila.innerHTML = `
            <div class="campo-formulario">

                <label>Herramienta</label>

                <select class="select-herramienta" required>
                    <option value="">Seleccionar herramienta</option>
                    ${opcionAnterior}
                    ${opciones}
                </select>

            </div>

            <div class="campo-formulario">

                <label>Cantidad</label>

                <input
                    type="number"
                    class="cantidad-herramienta"
                    min="1"
                    value="${detalle ? detalle.cantidad : 1}"
                    required>

            </div>

            <button
                type="button"
                class="btn-quitar-herramienta">
                Quitar
            </button>
        `;

        listaHerramientas.appendChild(fila);

        const select = fila.querySelector(".select-herramienta");
        const cantidad = fila.querySelector(".cantidad-herramienta");

        // Conservar el precio pactado de herramientas anteriores.
        if (detalle) {
            select.dataset.precioPactado = detalle.precio_dia;
        }

        select.addEventListener("change", () => {
            delete select.dataset.precioPactado;
            actualizarTotal();
        });

        cantidad.addEventListener("input", actualizarTotal);

        fila.querySelector(".btn-quitar-herramienta")
            .addEventListener("click", () => {
                fila.remove();
                actualizarTotal();
            });

        actualizarTotal();
    }

    function actualizarTotal() {

        const dias = calcularDias();

        let total = 0;

        document.querySelectorAll(".fila-herramienta-edicion")
            .forEach(fila => {

                const select = fila.querySelector(".select-herramienta");
                const cantidad = fila.querySelector(".cantidad-herramienta");

                const opcion = select.selectedOptions[0];

                if (!opcion || !opcion.value) return;

                const precio = Number(
                    select.dataset.precioPactado ??
                    opcion.dataset.precio ??
                    0
                );

                total += precio * Number(cantidad.value || 0) * dias;
            });

        totalElemento.textContent = formatearPrecio(total);
    }

    function calcularDias() {

        if (!fechaInicio.value || !fechaFin.value) return 1;

        const inicio = new Date(fechaInicio.value + "T00:00:00");
        const fin = new Date(fechaFin.value + "T00:00:00");

        const diferencia = Math.round(
            (fin - inicio) / (1000 * 60 * 60 * 24)
        );

        return Math.max(1, diferencia);
    }

    // =====================================================
    // GUARDAR CAMBIOS
    // =====================================================

    async function guardarCambios(evento) {
        if (fechaFin.value < fechaInicio.value) {
            alert("La fecha de devolución no puede ser anterior a la fecha de inicio.");
            return;
        }

        evento.preventDefault();

        try {

            const cantidades = new Map();

            document.querySelectorAll(".fila-herramienta-edicion")
                .forEach(fila => {

                    const id = Number(
                        fila.querySelector(".select-herramienta").value
                    );

                    const cantidad = Number(
                        fila.querySelector(".cantidad-herramienta").value
                    );

                    if (id && cantidad > 0) {
                        cantidades.set(
                            id,
                            (cantidades.get(id) || 0) + cantidad
                        );
                    }
                });

            const herramientas = Array.from(
                cantidades,
                ([id, cantidad]) => ({
                    id_herramienta: id,
                    cantidad: cantidad
                })
            );

            const datos = {
                accion: "editar",
                id: Number(document.getElementById("editar-alquiler-id").value),
                cliente_id: Number(selectCliente.value),
                fecha_inicio: fechaInicio.value,
                fecha_fin: fechaFin.value,
                estado: selectEstado.value,
                herramientas
            };

            const respuesta = await solicitar(
                "../api/alquileres.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(datos)
                }
            );

            mostrarMensaje(mensajeEdicion, respuesta.mensaje);

            await cargarAlquileres();

            setTimeout(cerrarModal, 700);

        } catch (error) {

            console.error("Error al editar:", error);
            mostrarMensaje(mensajeEdicion, error.message);
        }
    }

    // =====================================================
    // DESACTIVAR ALQUILER
    // =====================================================

    async function desactivarAlquiler() {

        const id = Number(
            document.getElementById("editar-alquiler-id").value
        );

        if (!confirm("¿Seguro que querés desactivar este alquiler?")) {
            return;
        }

        try {

            const respuesta = await solicitar(
                "../api/alquileres.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        accion: "desactivar",
                        id
                    })
                }
            );

            mostrarMensaje(mensajeEdicion, respuesta.mensaje);

            await cargarAlquileres();

            setTimeout(cerrarModal, 700);

        } catch (error) {

            mostrarMensaje(mensajeEdicion, error.message);
        }
    }

    // =====================================================
    // CERRAR MODAL
    // =====================================================

    function cerrarModal() {

        modal.hidden = true;
        formulario.reset();
        listaHerramientas.innerHTML = "";

        mostrarMensaje(mensajeEdicion, "");
    }

    // =====================================================
    // EVENTOS
    // =====================================================

    filtroCliente.addEventListener("change", filtrarPorCliente);

    tabla.addEventListener("click", evento => {

        const boton = evento.target.closest(".btn-editar");

        if (boton) {
            abrirEdicion(Number(boton.dataset.id));
        }
    });

    btnAgregar.addEventListener("click", () => {
        agregarFilaHerramienta();
    });

    btnDesactivar.addEventListener("click", desactivarAlquiler);

    btnCerrar.addEventListener("click", cerrarModal);

    formulario.addEventListener("submit", guardarCambios);

    fechaInicio.addEventListener("change", actualizarTotal);
    fechaFin.addEventListener("change", actualizarTotal);

    // Inicializar.
    inicializar();

});
