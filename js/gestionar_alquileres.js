
document.addEventListener("DOMContentLoaded", () => {

    const tabla = document.getElementById("lista-alquileres");
    const filtroCliente = document.getElementById("filtro-cliente");

    let alquileres = [];

    // Cargar clientes y alquileres
    async function inicializar() {

        try {

            await cargarClientes();
            await cargarAlquileres();

        } catch (error) {

            console.error("Error:", error);

            tabla.innerHTML = `
                <tr>
                    <td colspan="8">No se pudieron cargar los datos.</td>
                </tr>
            `;
        }
    }

    // Cargar clientes para el selector
    async function cargarClientes() {

        const respuesta = await fetch("../api/alquileres.php?accion=clientes");

        const datos = await respuesta.json();

        if (!respuesta.ok || !datos.success) {
            throw new Error(datos.mensaje || "Error al cargar clientes.");
        }

        datos.clientes.forEach(cliente => {

            const opcion = document.createElement("option");

            opcion.value = cliente.id;
            opcion.textContent = cliente.nombre;

            filtroCliente.appendChild(opcion);

        });
    }

    // Cargar alquileres
    async function cargarAlquileres() {

        const respuesta = await fetch("../api/alquileres.php?accion=listar");

        const datos = await respuesta.json();

        if (!respuesta.ok || !datos.success) {
            throw new Error(datos.mensaje || "Error al cargar alquileres.");
        }

        alquileres = datos.alquileres;

        mostrarAlquileres(alquileres);
    }

    // Mostrar alquileres
    function mostrarAlquileres(lista) {

        if (lista.length === 0) {

            tabla.innerHTML = `
                <tr>
                    <td colspan="8">No se encontraron alquileres.</td>
                </tr>
            `;

            return;
        }

        tabla.innerHTML = lista.map(alquiler => {

            const estado = alquiler.estado.toLowerCase();

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
                        <span class="estado estado-${estado}">
                            ${estado.charAt(0).toUpperCase() + estado.slice(1)}
                        </span>
                    </td>
                </tr>
            `;

        }).join("");
    }

    // Filtrar por cliente seleccionado
    function filtrarPorCliente() {

        const clienteId = filtroCliente.value;

        if (clienteId === "") {
            mostrarAlquileres(alquileres);
            return;
        }

        const clienteSeleccionado = filtroCliente.options[
            filtroCliente.selectedIndex
        ].textContent;

        const resultados = alquileres.filter(alquiler =>
            alquiler.cliente === clienteSeleccionado
        );

        mostrarAlquileres(resultados);
    }

    // Formatear fecha
    function formatearFecha(fecha) {

        if (!fecha) return "-";

        const [anio, mes, dia] = fecha.split("-");

        return `${dia}/${mes}/${anio}`;
    }

    // Formatear precio
    function formatearPrecio(precio) {

        return Number(precio).toLocaleString("es-AR", {
            style: "currency",
            currency: "ARS",
            minimumFractionDigits: 2
        });
    }

    // Escapar HTML
    function escaparHTML(valor) {

        return String(valor ?? "").replace(/[&<>"']/g, caracter => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        })[caracter]);
    }

    // Evento del selector
    filtroCliente.addEventListener("change", filtrarPorCliente);

    // Inicializar página
    inicializar();

});
