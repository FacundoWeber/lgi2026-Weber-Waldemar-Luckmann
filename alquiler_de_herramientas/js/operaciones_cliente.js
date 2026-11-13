// Carga la tabla clientes
const tabla = document.querySelector('#tabla_clientes');
const tablaTotal = document.querySelector('#tablaTotal');
async function cargarClientes() {
    try {
        const respuesta = await fetch('php/listar_clientes.php');

        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status}`);
        }
        const clientes = await respuesta.json();
        console.log(clientes);
        // Cantidad de clientes
        document.querySelector('#totalClientes').textContent = clientes.length;
        tabla.innerHTML = '';
        clientes.forEach(cliente => {
            const fila = document.createElement('tr');

            fila.innerHTML = `
                <td>${cliente.id}</td>
                <td>${cliente.nombre}</td>
                <td>${cliente.dni}</td>
                <td>${cliente.telefono}</td>
                <td>
                    <button
                        type="button"
                        class="btnSeleccionar"
                        data-id="${cliente.id}">
                        Seleccionar
                    </button>
                </td>
            `;
            tabla.appendChild(fila);
        });

    } catch (error) {
        console.error('Error al cargar los clientes:', error);
         alert(error.message);
    }
}
cargarClientes();
///seleccionar cliente
let clienteSeleccionado = null;
tabla.addEventListener('click', (event) => {

    const boton = event.target.closest('.btnSeleccionar');
    clienteSeleccionado = boton.dataset.id;
    if (!boton) return;

    const fila = boton.closest('tr');
    const celdas = fila.querySelectorAll('td');

    document.querySelector('#idSeleccionar').value =
        celdas[0].textContent;

    document.querySelector('#nombre').value =
        celdas[1].textContent;

    document.querySelector('#dni').value =
        celdas[2].textContent;

    document.querySelector('#telefono').value =
        celdas[3].textContent;
});
///agregar
const form = document.querySelector('#agregar_cliente');
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const datos = {
        nombre: document.querySelector('#nombre').value.trim(),
        dni: document.querySelector('#dni').value.trim(),
        telefono: document.querySelector('#telefono').value.trim()
    };

    try {
        const response = await fetch('php/agregar_cliente.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.ok) {
            alert('Cliente creado correctamente.');

            form.reset();

            // Actualizar la tabla sin recargar la página
            tabla.innerHTML = '';
            await cargarClientes();
        } else {
            alert(resultado.mensaje || 'No se pudo crear el cliente.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión o respuesta del servidor.');
    }
});
/// Actualizar
const btnActualizar = document.querySelector('#btnActualizar');
btnActualizar.addEventListener('click', async () => {

    const datos = {
        id: document.querySelector('#idSeleccionar').value.trim(),
        nombre: document.querySelector('#nombre').value.trim(),
        dni: document.querySelector('#dni').value.trim(),
        telefono: document.querySelector('#telefono').value.trim()
    };

    if (!datos.id) {
        alert('Primero seleccioná un cliente para actualizar.');
        return;
    }

    try {
        const response = await fetch('php/actualizar_cliente.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.ok) {
            alert('Cliente modificado correctamente.');

            form.reset();

            tabla.innerHTML = '';
            await cargarClientes();
        } else {
            alert(resultado.mensaje || 'No se pudo modificar el cliente.');
        }

    } catch (error) {
        console.error('Error al actualizar:', error);
        alert('Error de conexión o respuesta del servidor.');
    }
});

/// Búsqueda
const campoNombre = document.querySelector('#cliente_busqueda');

async function buscarClientes() {
    const letra = campoNombre.value.trim();

    try {
        const respuesta = await fetch(
            `php/buscar_cliente.php?letra=${encodeURIComponent(letra)}`
        );

        if (!respuesta.ok) {
            throw new Error('No se pudo realizar la búsqueda.');
        }

        const clientes = await respuesta.json();

        tabla.innerHTML = '';

        clientes.forEach(cliente => {
            const fila = document.createElement('tr');

            fila.innerHTML = `
                <td>${cliente.id}</td>
                <td>${cliente.nombre}</td>
                <td>${cliente.dni}</td>
                <td>${cliente.telefono}</td>
                <td>
                    <button type="button"
                        class="btnSeleccionar"
                        data-id="${cliente.id}">
                        Seleccionar
                    </button>
                </td>
            `;

            tabla.appendChild(fila);
        });

    } catch (error) {
        console.error('Error al buscar clientes:', error);
    }
}

function debounce(funcion, demora) {
    let temporizador;

    return (...argumentos) => {
        clearTimeout(temporizador);

        temporizador = setTimeout(() => {
            funcion(...argumentos);
        }, demora);
    };
}

const buscarConDebounce = debounce(buscarClientes, 500);

campoNombre.addEventListener('input', buscarConDebounce);
