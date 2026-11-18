// Informes: pide los datos a la API y arma las tablas.

const API_INFORMES = '../api/informes.php';

const formulario = document.querySelector('#filtro-informes');
const mensaje = document.querySelector('#mensaje');

const tablas = {
    herramientas: document.querySelector('#informe-herramientas'),
    clientes: document.querySelector('#informe-clientes'),
    estados: document.querySelector('#informe-estados'),
    meses: document.querySelector('#informe-meses')
};

// Colores de cada estado en el gráfico.
const coloresEstado = {
    activo: '#2457a7',
    devuelto: '#12703c',
    cancelado: '#b42318',
    pendiente: '#c77700'
};

let graficoEstados = null;

// Evita que un nombre rompa el HTML.
function escapar(texto) {
    return String(texto ?? '').replace(/[&<>"]/g, function (caracter) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[caracter];
    });
}

function dinero(valor) {
    return '$ ' + Number(valor).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function mesCorto(mes) {
    const partes = String(mes).split('-');

    return partes[1] + '/' + partes[0];
}

function fechaCorta(fecha) {
    const partes = String(fecha).split('-');

    return partes[2] + '/' + partes[1] + '/' + partes[0];
}

function sinDatos(columnas) {
    return '<tr><td colspan="' + columnas + '">Sin datos en el período.</td></tr>';
}

function mostrarHerramientas(lista) {
    if (lista.length === 0) {
        tablas.herramientas.innerHTML = sinDatos(4);
        return;
    }

    tablas.herramientas.innerHTML = lista.map(function (fila) {
        return `
            <tr>
                <td>${escapar(fila.nombre)}</td>
                <td>${fila.alquileres}</td>
                <td>${fila.unidades}</td>
                <td>${dinero(fila.total)}</td>
            </tr>
        `;
    }).join('');
}

function mostrarClientes(lista) {
    if (lista.length === 0) {
        tablas.clientes.innerHTML = sinDatos(3);
        return;
    }

    tablas.clientes.innerHTML = lista.map(function (fila) {
        return `
            <tr>
                <td>${escapar(fila.nombre)}</td>
                <td>${fila.alquileres}</td>
                <td>${dinero(fila.total)}</td>
            </tr>
        `;
    }).join('');
}

function mostrarEstados(lista) {
    if (lista.length === 0) {
        tablas.estados.innerHTML = sinDatos(3);
        return;
    }

    tablas.estados.innerHTML = lista.map(function (fila) {
        return `
            <tr>
                <td>${escapar(fila.estado)}</td>
                <td>${fila.alquileres}</td>
                <td>${dinero(fila.total)}</td>
            </tr>
        `;
    }).join('');
}

function mostrarMeses(lista) {
    if (lista.length === 0) {
        tablas.meses.innerHTML = sinDatos(3);
        return;
    }

    tablas.meses.innerHTML = lista.map(function (fila) {
        return `
            <tr>
                <td>${mesCorto(fila.mes)}</td>
                <td>${fila.alquileres}</td>
                <td>${dinero(fila.total)}</td>
            </tr>
        `;
    }).join('');
}

function mostrarGraficoEstados(lista) {
    const lienzo = document.querySelector('#grafico-estados');

    if (!lienzo || typeof Chart === 'undefined') {
        return;
    }

    const etiquetas = lista.map(function (fila) {
        return fila.estado;
    });
    const totales = lista.map(function (fila) {
        return Number(fila.total);
    });
    const colores = etiquetas.map(function (estado) {
        return coloresEstado[estado] || '#6c757d';
    });

    if (graficoEstados) {
        graficoEstados.data.labels = etiquetas;
        graficoEstados.data.datasets[0].data = totales;
        graficoEstados.data.datasets[0].backgroundColor = colores;
        graficoEstados.update();
        return;
    }

    graficoEstados = new Chart(lienzo, {
        type: 'doughnut',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Facturado',
                data: totales,
                backgroundColor: colores
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function (contexto) {
                            return contexto.label + ': ' + dinero(contexto.parsed);
                        }
                    }
                }
            }
        }
    });
}

async function generarInformes() {
    const desde = document.querySelector('#desde').value;
    const hasta = document.querySelector('#hasta').value;

    if (!desde || !hasta) {
        mensaje.textContent = 'Elegí las dos fechas.';
        mensaje.className = 'mensaje mensaje-error';
        return;
    }

    try {
        const respuesta = await fetch(
            API_INFORMES + '?accion=informes&desde=' + desde + '&hasta=' + hasta
        );
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.correcto) {
            throw new Error(resultado.mensaje || 'No se pudieron generar los informes.');
        }

        const datos = resultado.datos;

        mostrarHerramientas(datos.herramientas);
        mostrarClientes(datos.clientes);
        mostrarEstados(datos.estados);
        mostrarGraficoEstados(datos.estados);
        mostrarMeses(datos.meses);

        mensaje.textContent = 'Informe del ' + datos.desde + ' al ' + datos.hasta + '.';
        mensaje.className = 'mensaje mensaje-exito';

        document.querySelector('#rango-impreso').textContent =
            'Del ' + fechaCorta(datos.desde) + ' al ' + fechaCorta(datos.hasta);
    } catch (error) {
        mensaje.textContent = error.message;
        mensaje.className = 'mensaje mensaje-error';
    }
}

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    generarInformes();
});

generarInformes();
