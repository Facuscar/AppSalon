let paso = 1;

const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: []
}

document.addEventListener('DOMContentLoaded', function(){
iniciarApp();
});

function iniciarApp (){
    mostrarSeccion(); //La llamamos la primera vez para que cargue el primer tab
    tabs(); //Cambia la seccion cuando se presionen los tabs
    botonesPaginador(); //Muestra u oculta los botones del paginador
    paginaSiguiente();
    paginaAnterior();

    consultarAPI(); //Consulta la API que generamos con PHP

    idCliente();
    nombreCliente(); //Añade el nombre del cliente al objeto de cita
    seleccionarFecha(); //Añade la fecha de la cita al cliente
    seleccionarHora(); //Añade la hora de la cita en el objeto

    mostrarResumen(); //Muestra el resumen de la cita
}

function mostrarSeccion(){
    //Selecciona la clase que se mostraba anteriormente y se la oculta
    const seccionAnterior = document.querySelector('.mostrar');
    if(seccionAnterior){
        seccionAnterior.classList.remove('mostrar');
    }
    
    //Selecciona la sección a la que se le dió click y luego se le agrega la clase para que lo muestrre
    const seccion = document.querySelector(`#paso-${paso}`);
    seccion.classList.add('mostrar');

    //Remueve el actual del tab anterior
    const tabAnterior = document.querySelector('.actual');
    tabAnterior.classList.remove('actual');

    //Resalta el tab actual
    const tab = document.querySelector(`[data-paso="${paso}"]`);
    tab.classList.add('actual');
}

function tabs(){
    const botones = document.querySelectorAll('.tabs button');
    botones.forEach( boton => {
        boton.addEventListener('click', function(e){
            e.preventDefault();
            paso = parseInt ( e.target.dataset.paso );
            mostrarSeccion();
            botonesPaginador();
        });
    });
}

function botonesPaginador(){
    const paginaSiguiente = document.querySelector('#siguiente');
    const paginaAnterior = document.querySelector('#anterior');

    if(paso === 1){
        paginaAnterior.classList.add('ocultar');
    } else{
        paginaAnterior.classList.remove('ocultar');
    }

    if(paso === 3){
        paginaSiguiente.classList.add('ocultar');
        mostrarResumen();
    } else{
        paginaSiguiente.classList.remove('ocultar');
    }
}

function paginaSiguiente(){
    const paginaAnterior =  document.querySelector("#siguiente");
    paginaAnterior.addEventListener('click', () => {
        paso++;
        mostrarSeccion();
        botonesPaginador();
    });
}

function paginaAnterior(){
    const paginaAnterior =  document.querySelector("#anterior");
    paginaAnterior.addEventListener('click', () => {
        paso--;
        mostrarSeccion();
        botonesPaginador();
    });
}

async function consultarAPI(){

    try{
        const url = 'http://localhost:3000/api/servicios';
        const resultado = await fetch(url);
        const servicios = await resultado.json();
        mostrarServicios(servicios);
    } catch (error){
        console.log(error);
    }
}

function mostrarServicios(servicios){
    servicios.forEach( servicio =>{
        const {id, nombre, precio} = servicio;

        const nombreServicio = document.createElement('P');
        nombreServicio.classList.add('nombre-servicio');
        nombreServicio.textContent = nombre;

        const precioServicio = document.createElement('P');
        precioServicio.classList.add('precio-servicio');
        precioServicio.textContent = `$${precio}`;

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('servicio');
        servicioDiv.dataset.idServicio = id;
        servicioDiv.onclick = function (){
            seleccionarServicio(servicio);
        }

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);

        document.querySelector('#servicios').appendChild(servicioDiv);
    });
}

function seleccionarServicio(servicio){
    const {servicios} = cita;
    const {id} = servicio;

    //Comprobar si un servicio ya fue agregado
    if(servicios.some(agregado => agregado.id === id)){
        //Eliminarlo 
        cita.servicios = servicios.filter( agregado => agregado.id !== id )
    } else{
        //Agregarlo
        cita.servicios = [...servicios, servicio];
    }

    

    const divServicio = document.querySelector(`[data-id-servicio="${id}"]`);
    divServicio.classList.toggle('seleccionado');
}

function idCliente(){
    const id = document.querySelector('#id').value;

    cita.id = id;
}

function nombreCliente(){
 const nombre = document.querySelector('#nombre').value;

 cita.nombre = nombre;
}

function seleccionarFecha(){
    const inputFecha = document.querySelector('#fecha');
    inputFecha.addEventListener('input',function(e){
        const dia = new Date(e.target.value).getUTCDay();
        if( [6, 0].includes(dia)){
            mostrarAlerta("Los sábados y domingos permanecemos cerrados", "error", ".formulario");
            e.target.value = '';
            return;
        } else{
            cita.fecha = e.target.value;
            
        }
    });
}

function mostrarAlerta(mensaje, tipo, referencia, desaparece=true){
    const alertaPrevia = document.querySelector('.alerta');
    if(alertaPrevia){
        alertaPrevia.remove();
    }

    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add(tipo);
    alerta.classList.add('alerta');

    const elemento = document.querySelector(referencia);
    elemento.appendChild(alerta);

    if(desaparece){
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
    
}

function seleccionarHora(){
    const inputHora = document.querySelector('#hora');
    inputHora.addEventListener('input', function(e){
        const horaCita = e.target.value;
        const hora = horaCita.split(':')[0];
        if (hora<9 || hora > 18){
            mostrarAlerta('Abrimos de 9 a 18hs', 'error', ".formulario");
            e.target.value = '';
        } else{
            cita.hora = e.target.value;
        }
    });
}

function mostrarResumen(){
    const resumen = document.querySelector('.contenido-resumen');

    //Limpiar el contenido de resumen
    while(resumen.firstChild){
        resumen.removeChild(resumen.firstChild);
    }

    if(Object.values(cita).includes('')){
        mostrarAlerta('Faltan ingresar la fecha u hora del turno', 'error', '.contenido-resumen', false);
    } else if(cita.servicios.length === 0){
        mostrarAlerta('Faltan ingresar al menos un servicio', 'error', '.contenido-resumen', false);
    } else{
        //Formatear el div de resumen
        const {nombre, fecha, hora, servicios} = cita;
        const nombreCliente = document.createElement('P');
        nombreCliente.innerHTML = `<span>Nombre:</span> ${nombre}`;

        //Formatear la fecha en español
        const fechaObj = new Date(fecha);
        const mes = fechaObj.getMonth();
        const dia = fechaObj.getDate() + 2;
        const year = fechaObj.getFullYear();

        const fechaUTC = new Date( Date.UTC(year, mes, dia));
        const opciones = {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};
        const fechaFormateada = fechaUTC.toLocaleDateString('es-AR', opciones);


        const fechaCliente = document.createElement('P');
        fechaCliente.innerHTML = `<span>Fecha:</span> ${fechaFormateada}`;

        const horaCliente = document.createElement('P');
        horaCliente.innerHTML = `<span>Hora:</span> ${hora} Horas.`;

        

        //Se itera para mostrar los servicios
        servicios.forEach(servicio => {
            const {id, precio, nombre} = servicio;
            const contenedorServicio = document.createElement('DIV');
            contenedorServicio.classList.add('contenedor-servicio');

            const textoServicio = document.createElement('P');
            textoServicio.textContent = nombre;

            const precioServicio = document.createElement('P');
            precioServicio.innerHTML = `<span>Precio:</span> $${precio}`;

            contenedorServicio.appendChild(textoServicio);
            contenedorServicio.appendChild(precioServicio);

            resumen.appendChild(contenedorServicio);
        });


        //Boton para crear una cita
        const botonReservar = document.createElement('BUTTON');
        botonReservar.classList.add('boton');
        botonReservar.textContent = 'Reservar turno';
        botonReservar.onclick = reservarCita;

        resumen.appendChild(nombreCliente);
        resumen.appendChild(fechaCliente);
        resumen.appendChild(horaCliente);
        resumen.appendChild(botonReservar);
    }
}

async function reservarCita(){
    const {nombre, fecha, hora, servicios, id} = cita;

    const idServicios = servicios.map(servicio => servicio.id);

    const datos = new FormData();
    
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);


    try{
    //Peticion a la API
    const url = 'http://localhost:3000/api/citas';

    const respuesta = await fetch(url, {
        method: 'POST',
        body: datos
    });

    const resultado = await respuesta.json();
    if(resultado.resultado){
        Swal.fire({
            icon: 'success',
            title: "Turno reservado",
            text: 'Tu turno fue reservado correctamente',
            button: 'Genial'
        }).then(() => {
            setTimeout(() => {
                window.location.reload();   
            }, 1500);
            
        })
    }
    } catch(error){
        Swal.fire({
            icon: 'error',
            title: "Error",
            text: 'Hubo un problema al intentar reservar tu turno',
            button: 'De acuerdo'
        });
    }
  
}