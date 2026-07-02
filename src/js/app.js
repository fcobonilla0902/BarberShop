let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
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
            mostrarAlerta('Fines de semana no permitidos', 'error', '.appointment-form');
        } else {
            cita.fecha = e.target.value;
        }
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
            mostrarAlerta('Hora no válida', 'error', '.appointment-form');
        } else {
            cita.hora = e.target.value;
        }
    })
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
    titulo.innerHTML = `<h2>Resumen de cita</h2><p>Verifica que la información sea correcta antes de reservar.</p>`;
    wrapper.appendChild(titulo);

    if(Object.values(cita).includes('') || cita.servicios.length === 0 ) {
        const alerta = document.createElement('DIV');
        alerta.classList.add('empty-state');
        alerta.textContent = 'Faltan servicios, fecha u hora para generar el resumen.';
        wrapper.appendChild(alerta);
        resumen.appendChild(wrapper);
        return;
    }

    const { nombre, fecha, hora, servicios } = cita;

    let subtotal = 0;

    const lista = document.createElement('DIV');
    lista.classList.add('summary-list');

    servicios.forEach(servicio => {
        const precio = Number(servicio.precio || 0);
        subtotal += precio;

        const item = document.createElement('DIV');
        item.classList.add('summary-row');
        item.innerHTML = `
            <div>
                <strong>${servicio.nombre}</strong>
                <span>${servicio.duracion_minutos || 30} min</span>
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

    const detalle = document.createElement('DIV');
    detalle.classList.add('ticket-preview');
    detalle.innerHTML = `
        <div><span>Cliente</span><strong>${nombre}</strong></div>
        <div><span>Fecha</span><strong>${fechaFormateada}</strong></div>
        <div><span>Hora</span><strong>${hora}</strong></div>
        <div><span>Total estimado</span><strong>$${subtotal.toFixed(2)} MXN</strong></div>
    `;
    wrapper.appendChild(detalle);

    const botonReservar = document.createElement('BUTTON');
    botonReservar.classList.add('btn', 'btn--primary', 'btn--full');
    botonReservar.textContent = 'Reservar Cita';
    botonReservar.onclick = reservarCita;

    wrapper.appendChild(botonReservar);
    resumen.appendChild(wrapper);
}

async function reservarCita() {
    const { fecha, hora, servicios, id } = cita;

    const idServicios = servicios.map( servicio => servicio.id );

    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);

    try {
        const url = `${location.origin}/api/citas`
        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });

        const resultado = await respuesta.json();

        if(resultado.resultado) {
            Swal.fire({
                icon: 'success',
                title: 'Cita Creada',
                text: 'Tu cita fue creada correctamente',
                button: 'OK'
            }).then(() => window.location.reload());
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Pendiente',
                text: resultado.mensaje || 'La creación de citas se implementará en el siguiente issue.'
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
