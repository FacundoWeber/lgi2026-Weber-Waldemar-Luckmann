const formularioHerramienta = document.getElementById('formulario-herramienta');
const cuerpoHerramientas = document.getElementById('cuerpo-herramientas');
const selectorCategoria = document.getElementById('categoria');
const campoBusqueda = document.getElementById('buscar-herramienta');
const mensajeFormulario = document.getElementById('mensaje-formulario');
const mensajeListado = document.getElementById('mensaje-listado');

let herramientas = [];
let categorias = [];

async function pedirDatos(url, opciones = {}) {
    const respuesta = await fetch(url, opciones);
    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.correcto) {
        throw new Error(resultado.mensaje || 'No se pudo completar la solicitud.');
    }
    return resultado;
}

async function cargarHerramientas() {
    try {
        const busqueda = encodeURIComponent(campoBusqueda.value.trim());
        const resultado = await pedirDatos(`../api/herramientas.php?accion=listar&buscar=${busqueda}`);

        herramientas = resultado.datos.herramientas;
        categorias = resultado.datos.categorias;
        mostrarCategorias();
        mostrarHerramientas();
        mensajeListado.textContent = '';
    } catch (error) {
        mensajeListado.textContent = error.message;
    }
}

function mostrarCategorias() {
    const categoriaSeleccionada = selectorCategoria.value;
    selectorCategoria.innerHTML = '<option value="">Sin categoría</option>';

    categorias.forEach(categoria => {
        const opcion = document.createElement('option');
        opcion.value = categoria.id;
        opcion.textContent = categoria.nombre;
        selectorCategoria.appendChild(opcion);
    });

    selectorCategoria.value = categoriaSeleccionada;
}

function mostrarHerramientas() {
    cuerpoHerramientas.innerHTML = '';

    if (herramientas.length === 0) {
        cuerpoHerramientas.innerHTML = '<tr><td colspan="6">No se encontraron herramientas.</td></tr>';
        return;
    }

    herramientas.forEach(herramienta => {
        const fila = document.createElement('tr');
        const estado = Number(herramienta.activo) === 1;

        fila.innerHTML = `
            <td>${escaparHTML(herramienta.nombre)}</td>
            <td>${escaparHTML(herramienta.categoria || 'Sin categoría')}</td>
            <td>$ ${Number(herramienta.precio_dia).toFixed(2)}</td>
            <td>${herramienta.stock} (mín. ${herramienta.stock_minimo})</td>
            <td>${estado ? 'Activo' : 'Inactivo'}</td>
            <td>
                <button type="button" class="boton-tabla" data-accion="editar" data-id="${herramienta.id}">Editar</button>
                <button type="button" class="boton-tabla boton-secundario" data-accion="estado" data-id="${herramienta.id}" data-activo="${estado ? 0 : 1}">
                    ${estado ? 'Desactivar' : 'Activar'}
                </button>
            </td>`;
        cuerpoHerramientas.appendChild(fila);
    });
}

function escaparHTML(valor) {
    return String(valor).replace(/[&<>"']/g, caracter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[caracter]);
}

formularioHerramienta.addEventListener('submit', async evento => {
    evento.preventDefault();
    mensajeFormulario.textContent = '';

    const datos = {
        id: document.getElementById('herramienta-id').value,
        nombre: document.getElementById('nombre').value.trim(),
        categoria_id: selectorCategoria.value,
        precio_dia: document.getElementById('precio').value,
        stock: document.getElementById('stock').value,
        stock_minimo: document.getElementById('stock-minimo').value
    };

    try {
        const resultado = await pedirDatos('../api/herramientas.php?accion=guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });

        mensajeFormulario.textContent = resultado.mensaje;
        limpiarFormulario();
        await cargarHerramientas();
    } catch (error) {
        mensajeFormulario.textContent = error.message;
    }
});

cuerpoHerramientas.addEventListener('click', async evento => {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton) return;

    const id = Number(boton.dataset.id);
    const herramienta = herramientas.find(item => Number(item.id) === id);

    if (boton.dataset.accion === 'editar' && herramienta) {
        document.getElementById('herramienta-id').value = herramienta.id;
        document.getElementById('nombre').value = herramienta.nombre;
        selectorCategoria.value = herramienta.categoria_id || '';
        document.getElementById('precio').value = herramienta.precio_dia;
        document.getElementById('stock').value = herramienta.stock;
        document.getElementById('stock-minimo').value = herramienta.stock_minimo;
        document.getElementById('titulo-formulario').textContent = 'Modificar herramienta';
        document.getElementById('boton-guardar').textContent = 'Guardar cambios';
        document.getElementById('boton-cancelar').hidden = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    if (boton.dataset.accion === 'estado') {
        const nuevoEstado = Number(boton.dataset.activo);
        const confirmar = confirm(nuevoEstado === 1
            ? '¿Querés volver a activar esta herramienta?'
            : '¿Querés desactivar esta herramienta?');

        if (!confirmar) return;

        try {
            const resultado = await pedirDatos('../api/herramientas.php?accion=cambiar_estado', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, activo: nuevoEstado })
            });
            mensajeListado.textContent = resultado.mensaje;
            await cargarHerramientas();
        } catch (error) {
            mensajeListado.textContent = error.message;
        }
    }
});

function limpiarFormulario() {
    formularioHerramienta.reset();
    document.getElementById('herramienta-id').value = '';
    document.getElementById('titulo-formulario').textContent = 'Agregar herramienta';
    document.getElementById('boton-guardar').textContent = 'Guardar herramienta';
    document.getElementById('boton-cancelar').hidden = true;
}

document.getElementById('boton-cancelar').addEventListener('click', limpiarFormulario);
campoBusqueda.addEventListener('input', cargarHerramientas);

cargarHerramientas();
