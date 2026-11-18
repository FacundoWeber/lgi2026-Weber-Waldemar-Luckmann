
// RUTA DE LA API
const API = '../api/clientes.php';

const formulario = document.querySelector('#agregar_cliente');
const formularioBusqueda = document.querySelector('#busqueda');
const btnActualizar = document.querySelector('#btnActualizar');
const tabla = document.querySelector('#tabla_clientes');

// LISTAR CLIENTES
async function cargarClientes() {
    try {
        const respuesta = await fetch(`${API}?accion=listar`);

        if (!respuesta.ok) {
            throw new Error('Error al listar clientes');
        }

        const clientes = await respuesta.json();

        mostrarClientes(clientes);

    } catch (error) {
        console.error('Error al listar clientes:', error);
    }
}

// MOSTRAR CLIENTES EN LA TABLA
function mostrarClientes(clientes) {
    const total = document.querySelector('#totalClientes');

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

    total.textContent = clientes.length;
}

// AGREGAR CLIENTES
async function agregarCliente(nombre, dni, telefono) {
    try {
        const respuesta = await fetch(API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'guardar',
                nombre,
                dni,
                telefono
            })
        });

        const resultado = await respuesta.json();
        console.log(resultado);

        if (!respuesta.ok || resultado.error) {
            throw new Error(resultado.mensaje || 'No se pudo agregar');
        }

        await cargarClientes();

    } catch (error) {
        console.error('Error al guardar:', error);
    }
}

// EVENTO PARA AGREGAR
formulario.addEventListener('submit', async function(e) {
    e.preventDefault();

    const nombre = document.querySelector('#nombre').value;
    const dni = document.querySelector('#dni').value;
    const telefono = document.querySelector('#telefono').value;

    await agregarCliente(nombre, dni, telefono);

    formulario.reset();
});

// ACTUALIZAR CLIENTES
async function actualizarCliente(id, nombre, dni, telefono) {
    try {
        const respuesta = await fetch(API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'cambiar_estado',
                id,
                nombre,
                dni,
                telefono
            })
        });

        const resultado = await respuesta.json();
        console.log(resultado);

        if (!respuesta.ok || resultado.error) {
            throw new Error(resultado.mensaje || 'No se pudo actualizar');
        }

        await cargarClientes();

    } catch (error) {
        console.error('Error al actualizar:', error);
    }
}

// EVENTO PARA ACTUALIZAR
btnActualizar.addEventListener('click', async function() {
    const id = document.querySelector('#idSeleccionar').value;
    const nombre = document.querySelector('#nombre').value;
    const dni = document.querySelector('#dni').value;
    const telefono = document.querySelector('#telefono').value;

    if (!id) {
        alert('Primero seleccioná un cliente.');
        return;
    }

    await actualizarCliente(id, nombre, dni, telefono);

    formulario.reset();
});

// BUSCAR CLIENTES
let temporizadorBusqueda;
async function buscarClientes(letra) {
    try {
        const respuesta = await fetch(
            `${API}?accion=buscar&letra=${encodeURIComponent(letra)}`
        );

        const clientes = await respuesta.json();

        mostrarClientes(clientes);

    } catch (error) {
        console.error('Error al buscar:', error);
    }
}
///BUSCAR POR DEBOUNCE
const inputBusqueda = document.querySelector('#cliente_busqueda');

inputBusqueda.addEventListener('input', function() {

    clearTimeout(temporizadorBusqueda);

    temporizadorBusqueda = setTimeout(async function() {

        const letra = inputBusqueda.value.trim();

        if (letra === '') {
            await cargarClientes();
            return;
        }

        await buscarClientes(letra);

    }, 500);

});

// EVENTO PARA BUSCAR
formularioBusqueda.addEventListener('submit', async function(e) {
    e.preventDefault();

    const letra = document.querySelector('#cliente_busqueda').value;

    await buscarClientes(letra);
});

// SELECCIONAR CLIENTES
tabla.addEventListener('click', function(e) {
    if (e.target.classList.contains('btnSeleccionar')) {
        const fila = e.target.closest('tr');
        const celdas = fila.querySelectorAll('td');

        document.querySelector('#idSeleccionar').value = celdas[0].textContent;
        document.querySelector('#nombre').value = celdas[1].textContent;
        document.querySelector('#dni').value = celdas[2].textContent;
        document.querySelector('#telefono').value = celdas[3].textContent;
    }
});

// CARGA INICIAL
cargarClientes();
