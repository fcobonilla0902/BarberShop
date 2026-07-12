let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    notas: '',
    servicios: []
}

document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    mostrarSeccion();
    tabs();
    botonesPaginador();
    paginaSiguiente();
    paginaAnterior();

    consultarAPI();

    idCliente();
    nombreCliente();
    seleccionarFecha();
    seleccionarHora();
    capturarNotas();

    mostrarResumen();
}

function mostrarSeccion() {
    const seccionAnterior = document.querySelector('.mostrar');
    if(seccionAnterior) {
        seccionAnterior.classList.remove('mostrar');
    }

    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    if(seccion) {
        seccion.classList.add('mostrar');
    }

    const tabAnterior = document.querySelector('.actual');
    if(tabAnterior) {
        tabAnterior.classList.remove('actual');
    }

    const tab = document.querySelector(`[data-paso="${paso}"]`);
    if(tab) {
        tab.classList.add('actual');
    }
}

function tabs() {
    const botones = document.querySelectorAll('.tabs button');
    botones.forEach( boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            paso = parseInt(e.target.dataset.paso);
            mostrarSeccion();
            botonesPaginador();
        });
    });
}

function botonesPaginador() {
    const paginaAnterior = document.querySelector('#anterior');
    const paginaSiguiente = document.querySelector('#siguiente');

    if(!paginaAnterior || !paginaSiguiente) return;

    if(paso === 1) {
        paginaAnterior.classList.add('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    } else if (paso === 3) {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.add('ocultar');
        mostrarResumen();
    } else {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    }

    mostrarSeccion();
}

function paginaAnterior() {
    const paginaAnterior = document.querySelector('#anterior');
    if(!paginaAnterior) return;

    paginaAnterior.addEventListener('click', function() {
        if(paso <= pasoInicial) return;
        paso--;
        botonesPaginador();
    })
}

function paginaSiguiente() {
    const paginaSiguiente = document.querySelector('#siguiente');
    if(!paginaSiguiente) return;

    paginaSiguiente.addEventListener('click', function() {
        if(paso >= pasoFinal) return;

        if(paso === 1 && cita.servicios.length === 0) {
            mostrarAlerta('Selecciona al menos un servicio', 'error', '.appointment-main');
            return;
        }

        if(paso === 2 && (!cita.fecha || !cita.hora)) {
            mostrarAlerta('Selecciona fecha y hora', 'error', '.appointment-form');
            return;
        }

        paso++;
        botonesPaginador();
    })
}

async function consultarAPI() {
    try {
        const url = `${location.origin}/api/servicios`
        const resultado = await fetch(url);
        const servicios = await resultado.json();
        mostrarServicios(servicios);
    } catch (error) {
        console.log(error);
    }
}

function mostrarServicios(servicios) {
    const contenedor = document.querySelector('#servicios');
    if(!contenedor) return;

    contenedor.innerHTML = '';

    servicios.forEach( servicio => {
        const { id, nombre, precio, categoria, duracion_minutos, iva_porcentaje } = servicio;

        const categoriaServicio = document.createElement('SPAN');
        categoriaServicio.classList.add('service-chip');
        categoriaServicio.textContent = categoria || 'Servicio';

        const nombreServicio = document.createElement('H3');
        nombreServicio.classList.add('nombre-servicio');
        nombreServicio.textContent = nombre;

        const metaServicio = document.createElement('P');
        metaServicio.classList.add('service-meta');
        metaServicio.textContent = `${duracion_minutos || 30} min · IVA ${iva_porcentaje || 16}%`;

        const precioServicio = document.createElement('P');
        precioServicio.classList.add('precio-servicio');
        precioServicio.textContent = `$${Number(precio).toFixed(2)} MXN`;

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('servicio');
        servicioDiv.dataset.idServicio = id;
        servicioDiv.onclick = function() {
            seleccionarServicio(servicio);
        }

        servicioDiv.appendChild(categoriaServicio);
        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(metaServicio);
        servicioDiv.appendChild(precioServicio);

        contenedor.appendChild(servicioDiv);
    });
}

function seleccionarServicio(servicio) {
    const { id } = servicio;
    const { servicios } = cita;
    const divServicio = document.querySelector(`[data-id-servicio="${id}"]`);

    if(servicios.some( agregado => agregado.id === id ) ) {
        cita.servicios = servicios.filter( agregado => agregado.id !== id );
        divServicio.classList.remove('seleccionado');
    } else {
        cita.servicios = [...servicios, servicio];
        divServicio.classList.add('seleccionado');
    }

    actualizarMiniResumen();
}

function idCliente() {
    const input = document.querySelector('#id');
    if(input) cita.id = input.value;
}

function nombreCliente() {
    const input = document.querySelector('#nombre');
    if(input) cita.nombre = input.value;
}

function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    if(!inputFecha) return;

    inputFecha.addEventListener('input', function(e) {
        const dia = new Date(e.target.value).getUTCDay();

        if([6, 0].includes(dia)) {
            e.target.value = '';
            cita.fecha = '';
            mostrarAlerta('Fines de semana no disponibles', 'error', '.appointment-form');
        } else {
            cita.fecha = e.target.value;
        }

        actualizarMiniResumen();
    });
}

function seleccionarHora() {
    const inputHora = document.querySelector('#hora');
    if(!inputHora) return;

    inputHora.addEventListener('input', function(e) {
        const horaCita = e.target.value;
        const hora = horaCita.split(":")[0];

        if(hora < 10 || hora > 18) {
            e.target.value = '';
            cita.hora = '';
            mostrarAlerta('Selecciona un horario entre 10:00 y 18:00', 'error', '.appointment-form');
        } else {
            cita.hora = e.target.value;
        }

        actualizarMiniResumen();
    })
}

function capturarNotas() {
    const inputNotas = document.querySelector('#notas');
    if(!inputNotas) return;

    inputNotas.addEventListener('input', function(e) {
        cita.notas = e.target.value;
    });
}

function actualizarMiniResumen() {
    const resumen = document.querySelector('.appointment-summary');
    if(!resumen) return;

    const total = cita.servicios.reduce((acc, servicio) => acc + Number(servicio.precio || 0), 0);
    const duracion = cita.servicios.reduce((acc, servicio) => acc + Number(servicio.duracion_minutos || 0), 0);

    let resumenServicios = resumen.querySelector('.mini-selected-services');

    if(!resumenServicios) {
        resumenServicios = document.createElement('DIV');
        resumenServicios.classList.add('mini-selected-services');
        resumen.appendChild(resumenServicios);
    }

    if(cita.servicios.length === 0) {
        resumenServicios.innerHTML = '';
        return;
    }

    resumenServicios.innerHTML = `
        <div class="summary-item">
            <span>Servicios</span>
            <strong>${cita.servicios.length} seleccionado(s)</strong>
        </div>
        <div class="summary-item">
            <span>Duración estimada</span>
            <strong>${duracion} min</strong>
        </div>
        <div class="summary-item">
            <span>Total estimado</span>
            <strong>$${total.toFixed(2)} MXN</strong>
        </div>
    `;
}

function mostrarAlerta(mensaje, tipo, elemento, desaparece = true) {
    const alertaPrevia = document.querySelector('.alerta-js');
    if(alertaPrevia) {
        alertaPrevia.remove();
    }

    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta', 'alerta-js', tipo);

    const referencia = document.querySelector(elemento);
    if(!referencia) return;

    referencia.appendChild(alerta);

    if(desaparece) {
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
}

function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');
    if(!resumen) return;

    while(resumen.firstChild) {
        resumen.removeChild(resumen.firstChild);
    }

    const wrapper = document.createElement('DIV');
    wrapper.classList.add('summary-card-final');

    const titulo = document.createElement('DIV');
    titulo.classList.add('section-heading');
    titulo.innerHTML = `<h2>Resumen</h2><p>Revisa la cita antes de confirmar.</p>`;
    wrapper.appendChild(titulo);

    if(!cita.nombre || !cita.fecha || !cita.hora || cita.servicios.length === 0 ) {
        const alerta = document.createElement('DIV');
        alerta.classList.add('empty-state');
        alerta.textContent = 'Faltan servicios, fecha u hora para generar el resumen.';
        wrapper.appendChild(alerta);
        resumen.appendChild(wrapper);
        return;
    }

    const { nombre, fecha, hora, servicios } = cita;

    let subtotal = 0;
    let duracionTotal = 0;

    const lista = document.createElement('DIV');
    lista.classList.add('summary-list');

    servicios.forEach(servicio => {
        const precio = Number(servicio.precio || 0);
        const duracion = Number(servicio.duracion_minutos || 0);
        subtotal += precio;
        duracionTotal += duracion;

        const item = document.createElement('DIV');
        item.classList.add('summary-row');
        item.innerHTML = `
            <div>
                <strong>${servicio.nombre}</strong>
                <span>${duracion} min · IVA ${servicio.iva_porcentaje || 16}%</span>
            </div>
            <b>$${precio.toFixed(2)} MXN</b>
        `;

        lista.appendChild(item);
    });

    wrapper.appendChild(lista);

    const fechaObj = new Date(fecha + 'T00:00:00');
    const fechaFormateada = fechaObj.toLocaleDateString('es-MX', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    const horaFin = calcularHoraFin(hora, duracionTotal);

    const detalle = document.createElement('DIV');
    detalle.classList.add('ticket-preview');
    detalle.innerHTML = `
        <div><span>Cliente</span><strong>${nombre}</strong></div>
        <div><span>Fecha</span><strong>${fechaFormateada}</strong></div>
        <div><span>Hora inicio</span><strong>${hora}</strong></div>
        <div><span>Hora fin estimada</span><strong>${horaFin}</strong></div>
        <div><span>Duración total</span><strong>${duracionTotal} min</strong></div>
        <div><span>Total estimado</span><strong>$${subtotal.toFixed(2)} MXN</strong></div>
    `;
    wrapper.appendChild(detalle);

    const botonReservar = document.createElement('BUTTON');
    botonReservar.classList.add('btn', 'btn--primary', 'btn--full');
    botonReservar.textContent = 'Confirmar cita';
    botonReservar.onclick = reservarCita;

    wrapper.appendChild(botonReservar);
    resumen.appendChild(wrapper);
}

function calcularHoraFin(horaInicio, minutos) {
    const [h, m] = horaInicio.split(':').map(Number);
    const fecha = new Date();
    fecha.setHours(h, m, 0, 0);
    fecha.setMinutes(fecha.getMinutes() + minutos);

    return fecha.toTimeString().substring(0, 5);
}

async function reservarCita() {
    const { fecha, hora, servicios, id, notas } = cita;

    const idServicios = servicios.map( servicio => servicio.id );

    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('cliente_id', id);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios.join(','));
    datos.append('observaciones', notas || '');

    try {
        const url = `${location.origin}/api/citas`
        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });

        const resultado = await respuesta.json();

        if(resultado.resultado) {
            const citaCreada = resultado.cita || {};

            Swal.fire({
                icon: 'success',
                title: 'Cita confirmada',
                html: `
                    <p>${resultado.mensaje}</p>
                    <p><strong>Horario:</strong> ${citaCreada.hora_inicio?.substring(0,5) || hora} - ${citaCreada.hora_fin?.substring(0,5) || ''}</p>
                `,
                confirmButtonText: 'Ver mis citas'
            }).then(() => {
                window.location.href = '/mis-citas';
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No se pudo reservar',
                text: resultado.mensaje || 'Revisa los datos de la cita.'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error al guardar la cita'
        })
    }
}
