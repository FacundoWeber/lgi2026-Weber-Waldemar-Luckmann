// Gestión de herramientas. Los datos se piden a api/herramientas.php.

const API = window.API_HERRAMIENTAS || '../api/herramientas.php';

const formularioHerramienta = document.getElementById('formulario-herramienta');
const cuerpoHerramientas = document.getElementById('cuerpo-herramientas');
const selectorCategoria = document.getElementById('categoria');
const campoBusqueda = document.getElementById('buscar-herramienta');
const mensajeFormulario = document.getElementById('mensaje-formulario');
const mensajeListado = document.getElementById('mensaje-listado');

let herramientas = [];
let categorias = [];

function mostrarMensaje(elemento, texto, esError = false) {
    if (!elemento) return;

    elemento.textContent = texto;
    elemento.classList.toggle('mensaje-error', esError);
    elemento.classList.toggle('mensaje-exito', !esError && texto !== '');
}

function escaparHTML(valor) {
    return String(valor).replace(/[&<>"']/g, caracter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[caracter]);
}

// Hace el pedido a la API y devuelve `datos`. Si falla, lanza un error con el
// mensaje que mandó el servidor.
async function pedirDatos(url, opciones = {}) {
    const respuesta = await fetch(url, opciones);
    let resultado;

    try {
        resultado = await respuesta.json();
    } catch {
        throw new Error('El servidor devolvió una respuesta que no es JSON.');
    }

    if (!respuesta.ok || !resultado.correcto) {
        throw new Error(resultado.mensaje || 'No se pudo completar la solicitud.');
    }

    return resultado.datos;
}

async function cargarHerramientas() {
    try {
        const busqueda = encodeURIComponent(campoBusqueda.value.trim());
        const datos = await pedirDatos(`${API}?accion=listar&buscar=${busqueda}`);

        herramientas = datos.herramientas;
        categorias = datos.categorias;

        mostrarCategorias();
        mostrarHerramientas();
        mostrarMensaje(mensajeListado, '');
    } catch (error) {
        mostrarMensaje(mensajeListado, error.message, true);
    }
}

function mostrarCategorias() {
    if (!selectorCategoria) return;

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
    if (!cuerpoHerramientas) return;

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

function limpiarFormulario() {
    if (!formularioHerramienta) return;

    formularioHerramienta.reset();
    document.getElementById('herramienta-id').value = '';
    document.getElementById('titulo-formulario').textContent = 'Agregar herramienta';
    document.getElementById('boton-guardar').textContent = 'Guardar herramienta';
    document.getElementById('boton-cancelar').hidden = true;
}

function completarFormulario(herramienta) {
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

if (formularioHerramienta) {
    formularioHerramienta.addEventListener('submit', async evento => {
        evento.preventDefault();
        mostrarMensaje(mensajeFormulario, '');

        const datos = {
            id: document.getElementById('herramienta-id').value,
            nombre: document.getElementById('nombre').value.trim(),
            categoria_id: selectorCategoria.value,
            precio_dia: document.getElementById('precio').value,
            stock: document.getElementById('stock').value,
            stock_minimo: document.getElementById('stock-minimo').value
        };

        if (datos.nombre === '') {
            mostrarMensaje(mensajeFormulario, 'El nombre es obligatorio.', true);
            return;
        }

        try {
            await pedirDatos(`${API}?accion=guardar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            mostrarMensaje(mensajeFormulario, 'Herramienta guardada correctamente.');
            limpiarFormulario();
            await cargarHerramientas();
        } catch (error) {
            mostrarMensaje(mensajeFormulario, error.message, true);
        }
    });
}

if (cuerpoHerramientas) {
    cuerpoHerramientas.addEventListener('click', async evento => {
        const boton = evento.target.closest('button[data-accion]');
        if (!boton) return;

        const id = Number(boton.dataset.id);
        const herramienta = herramientas.find(item => Number(item.id) === id);

        if (boton.dataset.accion === 'editar' && herramienta) {
            completarFormulario(herramienta);
            return;
        }

        if (boton.dataset.accion === 'estado') {
            const nuevoEstado = Number(boton.dataset.activo);
            const pregunta = nuevoEstado === 1
                ? '¿Querés volver a activar esta herramienta?'
                : '¿Querés desactivar esta herramienta?';

            if (!confirm(pregunta)) return;

            try {
                await pedirDatos(`${API}?accion=cambiar_estado`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, activo: nuevoEstado })
                });

                mostrarMensaje(mensajeListado, nuevoEstado === 1 ? 'Herramienta activada.' : 'Herramienta desactivada.');
                await cargarHerramientas();
            } catch (error) {
                mostrarMensaje(mensajeListado, error.message, true);
            }
        }
    });
}

const botonCancelar = document.getElementById('boton-cancelar');
if (botonCancelar) {
    botonCancelar.addEventListener('click', limpiarFormulario);
}

if (campoBusqueda) {
    campoBusqueda.addEventListener('input', cargarHerramientas);
}

if (formularioHerramienta) {
    cargarHerramientas();
}
