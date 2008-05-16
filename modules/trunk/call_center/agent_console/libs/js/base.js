// funcion que ejecuta cada 4 segundos una funcion ajax que consulta a la base
// para verificar llamadas entrantes
function refrescar() {
    var prefijo_objeto;
    var pestania = document.getElementById('pestania').value;
    //seleccionar_pestania(pestania);

    if (document.getElementById('control').value == "1") {
        document.getElementById('control').value = 0;
        prefijo_objeto = document.getElementById('prefijo_objeto').value;

        nueva_llamada = new Array();
        nueva_llamada['llamada'] = document.getElementById('nueva_llamada').value;
        nueva_llamada['script'] = document.getElementById('nuevo_script').value;
        nueva_llamada['form'] = document.getElementById('nuevo_form').value;
//alert("dentro del if")
        if(document.getElementById('select_form')){ //si existe el objeto
//alert("if");
            var id_form = document.getElementById('select_form').value;
            xajax_notificaLlamada(pestania, prefijo_objeto, nueva_llamada, id_form);
        }
        else {
//alert("else");
            xajax_notificaLlamada(pestania, prefijo_objeto, nueva_llamada);
        }
    }//else alert("no entro");
    setTimeout("refrescar()",4000);
}

/* funcion que cambia el estilo de la pestania que llega por parametro, para que se vea como seleccionada, mientras que a las demas les pone estilo de no seleccionada */
function seleccionar_pestania_no(nombre_pestania) {
    document.getElementById('TAB_PESTANIA_LLAMADA').className = "headlink";
    document.getElementById('TAB_PESTANIA_SCRIPT').className = "headlink";
    document.getElementById('TAB_PESTANIA_FORMULARIO').className = "headlink";
    // la pestania que se activa
    nombre_pestania = "TAB_PESTANIA_"+nombre_pestania;
    document.getElementById(nombre_pestania).className = "headlinkon";
}

// setea en un oculto el id de la pestaña que fue seleccionada
function activar_no(pestania) {
    document.getElementById('pestania').value = pestania;
    refrescar();
}

/* se llama a una funcion ajax que se encarga de colgar la llamada */
function colgar() {
    document.getElementById('control').value = 0;
    if (document.getElementById('tipo_llamada').value == "ENTRANTE") {
        xajax_colgarLlamadaEntrante();
    } else if (document.getElementById('tipo_llamada').value == "SALIENTE") {
        xajax_colgarLlamada();
    }
}

function conectar_extension () { 
    var num_agent = document.getElementById('input_agent_user').value;
    var extension = document.getElementById('input_extension').value;
    var contador=0;
//alert("antes de llamar a la función ajax.\nnum_agent="+num_agent+"; extension="+extension);
    xajax_loginAgente(extension, num_agent);
    if (num_agent!="" && extension!="0" && document.getElementById('pregunta_logoneo').value==1) {
        document.getElementById('reloj').src = "images/hourglass.gif";
        wait_login(contador);
    }
}

function wait_login() {
    //contador++;
    var num_agent = document.getElementById('input_agent_user').value;
    var extension = document.getElementById('input_extension').value;
    var error_igual_numero_agente = document.getElementById('error_igual_numero_agente').value;
    if (document.getElementById('status_login').value == 0 && error_igual_numero_agente==0) {
        xajax_wait_login(extension, num_agent);
        setTimeout("wait_login()",1500);
    } else if (document.getElementById('status_login').value == 1) {
        document.frm_login_agent.submit();
    } else {
        document.getElementById('reloj').src = "images/1x1.gif";
    }
}



function pausar_llamadas(tipo_break)
{   
    document.getElementById('div_list').style.display ='none';
    xajax_pausar_llamadas(tipo_break);    
}



function guardar_informacion_cliente() {
    var prefijo_objeto = document.getElementById('prefijo_objeto').value;
    var data_objetos = getObjetosFormulario(prefijo_objeto);
    var i=0, informacion="";
    for (i=0; i<data_objetos.length; i++) {
        informacion = informacion+data_objetos[i];
    }
    if (informacion == "") {
        alert("No se ha ingresado ningún dato para guardar.");
    }

    xajax_guardar_informacion_cliente(data_objetos);
}

function getObjetosFormulario(prefijo_objeto) {
    var long_prefijo_objeto = prefijo_objeto.length;
    var long_objeto;
    var cant_obj = document.frm_agent_console.length;
    var nombre_objeto, value_objeto, id;
    data_objeto = new Array();
    var indice = 0;

    for (i=0; i<cant_obj; i++) { 
        nombre_obj = document.frm_agent_console.elements[i].name;
        long_objeto = nombre_obj.length;
        prefijo_nombre_obj = nombre_obj.substring(0,long_prefijo_objeto);
        id = nombre_obj.substring(long_prefijo_objeto,long_objeto);
        if (prefijo_nombre_obj == prefijo_objeto) {
            nombre_objeto = nombre_obj;
            value_objeto = document.getElementById(nombre_objeto).value;
            data_objeto[indice] = Array(id,value_objeto);
            indice++;
        }
    }
    return data_objeto;
}




/* ------------------------------------------------------------------------- */

// setea en un oculto el id de la pestaña que fue seleccionada
function activar(pestania) {
    document.getElementById('TABLA_LLAMADA').style.display = "none";
    document.getElementById('TABLA_SCRIPT').style.display = "none";
    document.getElementById('TABLA_FORMULARIO').style.display = "none";
    document.getElementById('TABLA_'+pestania).style.display = "";
    seleccionar_pestania(pestania);
    document.getElementById('pestania').value = pestania;
    refrescar();
}

/* funcion que cambia el estilo de la pestania que llega por parametro, para que se vea como seleccionada, mientras que a las demas les pone estilo de no seleccionada */
function seleccionar_pestania(nombre_pestania) {
    document.getElementById('TAB_PESTANIA_LLAMADA').className = "headlink";
    document.getElementById('TAB_PESTANIA_SCRIPT').className = "headlink";
    document.getElementById('TAB_PESTANIA_FORMULARIO').className = "headlink";
    // la pestania que se activa
    nombre_pestania = "TAB_PESTANIA_"+nombre_pestania;
    document.getElementById(nombre_pestania).className = "headlinkon";
}


/*  funciones para el menu desplegable del boton break*/
function mostrar_lista()
{
    estado_break = document.getElementById('pause').className;
    if(estado_break == 'boton_break'){
        document.getElementById('div_list').style.display ='';
    }
    else{
        document.getElementById('div_list').style.display ='none';
    }
}

/*  funciones para el formulario*/
function mostrarFormularioSeleccionado(id_formulario_seleccionado) {
    var id_formularios = document.getElementById("id_formularios").value;
    var arr_formularios = id_formularios.split("-");
    var i=0;
    for(i=0; i<arr_formularios.length; i++) {
        document.getElementById(arr_formularios[i]).style.display = "none";
    }
    document.getElementById(id_formulario_seleccionado).style.display = "";
}


/*Funciones para capturar el manejo del evento al cerrar el navegador*/
var even = null;
var fue_unload = false;

if(navigator.appName=='Netscape'){ //si los navegadores son mozilla o netscape
    window.addEventListener('click',afuera,true); //para evento en navegador mozilla
    window.addEventListener('beforeunload',ConfirmarCierre,true);
}
else{
    alert('Aun no implenetado para iexplore lo de capturar evento al cerrar el navegador');
}

function afuera(e)
{
    even = e;
    if(!fue_unload)
        even = null;
}
function ConfirmarCierre(ev)
{
    if (window.XMLHttpRequest){
        if(!even){
            xajax_evento_cerrar_navegador();
            setTimeout('fue_unload = false', 2000);
        }
        else fue_unload = true; 
    }
}
/*Fin Funciones para capturar el manejo del evento al cerrar el navegador*/


/*Funciones para Transferencia de llamadas*/

/*
    Esta funcion muestra un menu desplegable l presionar el boton Transferir.
*/
function mostrar_lista_transferencia() {
    clase = document.getElementById('transfer').className;
    if(clase == 'boton_tranfer_activo') {
        document.getElementById('div_transfer_list').style.display='';
    } else if (clase == 'boton_tranfer_inactivo') {
        document.getElementById('div_transfer_list').style.display='none'
    }
}

/*
    Esta funcion ejecuta una llamada a un procedimiento AJAX que se encarga de
    transferir la llamada actual.
*/
function transferirLlamadaCiega(extension) {
    xajax_transferirLlamadaCiega(extension);
    document.getElementById('div_transfer_list').style.display ='none';
}

function consultarTransferenciaLlamada(extension){
    xajax_consultarTransferenciaLlamada(extension);
}


/*Fin Funciones para Transferencia de llamadas*/

/* Funciones para el Envio de llamadas*/
function mostrar_teclado() {
//alert("holaaaaaaaaaaa");
    clase = document.getElementById('marcar2').className;
    if(clase == 'boton_marcar_activo') {
//alert("activo");
        document.getElementById('div_marcar_list').style.display='';
    } else if (clase == 'boton_marcar_activo') {
//alert("inactivo");
        document.getElementById('div_marcar_list').style.display='none'
    }
}

function capturarValor(btn) {
    valor = document.getElementById(btn.id).value;
    texto = document.getElementById('txtMarcar').value;
    document.getElementById('txtMarcar').value = texto + valor ;
}

function marcarLLamada() {
    texto = document.getElementById('txtMarcar').value;
    document.getElementById('txtMarcar').value = '';
//alert(texto);
    xajax_marcarLlamada(texto);
    document.getElementById('div_marcar_list').style.display='none';
}

function cancelarMarcado() {
    document.getElementById('txtMarcar').value = '';
    document.getElementById('div_marcar_list').style.display ='none';
}



/* FinFunciones para el Envio de llamadas*/



/*Funciones para el cronometro*/
var crono;
function breakCronometro(objLabel,fecha)
{
    var nodo = document.getElementById(objLabel).firstChild;
    fecha = new Date(fecha);

    nodo.data =((fecha.getHours()<10)?"0":""  )+fecha.getHours()+":";
    nodo.data+=((fecha.getMinutes()<10)?"0":"")+fecha.getMinutes()+":";
    nodo.data+=((fecha.getSeconds()<10)?"0":"")+fecha.getSeconds();

    fecha.setTime(fecha.getTime()+1000); 
    crono = setTimeout( "breakCronometro('"+ objLabel +"','" + fecha + "');",1000);
}
function breakCronometroStop() 
{
    clearTimeout( crono );
} 
function breakCronometroReset(fecha) 
{
    fecha.setYear(0);
    fecha.setMonth(0);
    fecha.setDate(0);
    fecha.setHours(0);
    fecha.setMinutes(0);
    fecha.setSeconds(0);
    return fecha;
}

function breakCronometroSet(anio,mes,dia,hora,minuto,segundo) 
{
    var fecha = new Date();
    fecha.setYear(anio);
    fecha.setMonth(mes);
    fecha.setDate(dia);
    fecha.setHours(hora);
    fecha.setMinutes(minuto);
    fecha.setSeconds(segundo);
    return fecha;
}

var accionAnterior = null;
function estado_cronometro(accion,datetime_inicio)
{//alert(accion + " " + datetime_inicio);
    var estado = document.getElementById('celda_estatus_actual').className;
    var cronometro = document.getElementById('celda_cronometro');
    var inbreak = document.getElementById('pause').className;
    var tipo_break = document.getElementById('input_break').value;

   if(estado == 'fondo_estatus_break' && accionAnterior!=accion){
        cronometro.setAttribute('style','background-color:#BD0000');
        if(inbreak == 'boton_unbreak' && accion=='enBreak'){
            breakCronometroStop();
            breakCronometro('cronometro',datetime_inicio);
            accionAnterior = accion;
        }
        else if(inbreak == 'boton_break' && accion=='unBreak'){
            breakCronometroStop();
            accionAnterior = accion;
        }
    }
    else if(estado == 'fondo_estatus_llamada' && accionAnterior!=accion){
        cronometro.setAttribute('style','background-color:#06640D'); 
        breakCronometroStop();
        breakCronometro('cronometro',datetime_inicio);
        accionAnterior = accion;
    }
    else  if(estado == 'fondo_estatus_no_llamada'){ //accion noLlamada 
        cronometro.setAttribute('style','background-color:#094895');
        breakCronometroStop();
        accionAnterior = accion;
    }
}
/*Fin Funciones para el cronometro*/

function window_open(url,nombre_ventana) {
    window.open(url,nombre_ventana);
}