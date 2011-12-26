var module_name = "addons_availables";

// Variables para paginación
var addonlist_offset = null;
var addonlist_limit = null;
var addonlist_total = null;

// Variable para manejar el llamado a la función do_checkStatus()
var intervalCheckStatus = null;

var dialogo_progreso_abierto = false;
var transaction_in_progress = false;
var checking_dependencies = false;
var refer = document.URL;

/* El siguiente objeto es el estado de la interfaz del Addon Market. Al comparar
 * este objeto con los cambios de estado producto de la revisión periódica del
 * demonio actualizador, consigue detectar los cambios requeridos a la interfaz 
 * sin tener que recurrir a llamadas repetidas al servidor.
 * Este objeto se actualiza en do_checkStatus() */
var estadoCliente = 
{
	// Este estado inicializado representa una sistema ocioso
	name_rpm:	null,	// NULL para sistema ocioso, o el RPM que se opera
	fraccion:	0		// Fracción de completado, rango 0 hasta 1000. 
};

$(document).ready(function() {
	$('#imgPrimero').click(function () {
		if (addonlist_offset != null && addonlist_offset > 0)
			do_listarAddons(0);
	});
	$('#imgAnterior').click(function () {
		if (addonlist_offset != null && addonlist_offset >= addonlist_limit)
			do_listarAddons(addonlist_offset - addonlist_limit);
	});
	$('#imgSiguiente').click(function () {
		if (addonlist_offset != null && addonlist_offset + addonlist_limit < addonlist_total)
			do_listarAddons(addonlist_offset + addonlist_limit);
	});
	$('#imgFinal').click(function () {
		if (addonlist_offset != null) {
			var offsetfinal = addonlist_total - (addonlist_total % addonlist_limit);
			if (offsetfinal == addonlist_total)
				offsetfinal -= addonlist_limit;
			if (offsetfinal > addonlist_offset)
				do_listarAddons(offsetfinal);
		}
	});
	
	do_listarAddons(null);
});

/**
 * Procedimiento para listar los addons vía AJAX. Inmediatamente luego de que
 * se recibe una respuesta correcta, se invoca de inmediato do_checkStatus() que
 * actualiza la interfaz para que se corresponda al estado del sistema de 
 * actualización.
 * 
 * @param int	offset	Offset desde el cual iniciar el listado
 * 
 * @return void
 */
function do_listarAddons(list_offset)
{
	loadingAddons();
	var filter_by = $('#filter_by').val();
	var filter_nameRpm = $('#filter_namerpm').val();
	addonlist_offset = null;
	addonlist_limit = null;
	addonlist_total = null;
	$.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'listarAddons',
		offset:		list_offset,
		filter_by:	filter_by,
		filter_nameRpm: filter_nameRpm
	},
	function (respuesta) {
		if (respuesta['action'] == 'error') {
			mostrar_mensaje(respuesta['message'],true);
		} else {
			$('#addonlist')
				.empty()
				.append(respuesta['addonlist_html']);
			addonlist_offset = respuesta['offset'];
			addonlist_limit = respuesta['limit'];
			addonlist_total = respuesta['total'];
			if(addonlist_total == 0)
			    $('#addonlist_start_range').text(0);
			else
			    $('#addonlist_start_range').text(addonlist_offset + 1);
			$('#addonlist_total').text(addonlist_total);
			$('#addonlist_end_range').text(
					(addonlist_offset + addonlist_limit >= addonlist_total) 
					? addonlist_total 
					: addonlist_offset + addonlist_limit);

			$('.neo-addons-row-button-buy-left, .neo-addons-row-button-buy-right').unbind('click');
			$('.neo-addons-row-button-install-left, .neo-addons-row-button-install-right').unbind('click');
			$('.neo-addons-row-button-uninstall-left, .neo-addons-row-button-uninstall-right').unbind('click');
			$('.neo-addons-row-moreinfo').unbind('click');
			$('.neo-progress-bar-close').unbind('click');
			// TODO: trial
			$('.neo-addons-row-button-buy-left, .neo-addons-row-button-buy-right').click(function (e) {
				estadoCliente.name_rpm = $(this).parent().children('#name_rpm').val();
				do_checkDependencies(estadoCliente.name_rpm);
			});
			$('.neo-addons-row-button-install-left, .neo-addons-row-button-install-right, .neo-addons-row-button-trial-left, .neo-addons-row-button-trial-right').click(function () {
				estadoCliente.name_rpm = $(this).parent().children('#name_rpm').val();
				do_iniciarInstallUpdate(estadoCliente.name_rpm);
			});
			$('.neo-addons-row-button-uninstall-left, .neo-addons-row-button-uninstall-right').click(function () {
				estadoCliente.name_rpm = $(this).parent().children('#name_rpm').val();
				do_iniciarUninstall(estadoCliente.name_rpm);
			});
			$('.neo-addons-row-moreinfo').click(function () {
				var url_moreinfo = $(this).parent().children('#url_moreinfo').val();
				window.open(url_moreinfo);
			});
			$('.neo-progress-bar-close').click(function () {
				var answer = confirm("Are you sure you want to cancel this transaction?");
				if(answer)
				    cancelTransaction();
			});
			//alert(screen.availHeight);
			// Iniciar la revisión del status de la instalación
			do_checkStatus();
		}
	});
}

function checkServerID(name_rpm)
{
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'getServerKey'
	},
	function (respuesta) {
		if(respuesta["server_key"])
		    do_iniciarCompra(name_rpm);
		else{
		    var link = $('#'+name_rpm+'_link').val();
		    $('#link_tmp').val(link+refer+"&serverkey=");
		    showPopupElastix('registrar','Register',600,400);
		}
	});
}

function keyPressed(e)
{
    var keycode;
    if (window.event) keycode = window.event.keyCode;
    else if (e) keycode = e.which;
    else return true;
    if(keycode == 13){
	do_listarAddons(null);
	return false;
    }
}

function mostrar_mensaje(s, is_error)
{
	if(is_error){
	   $('#neo-addons-error-message').addClass('ui-state-error');
	   $('#neo-addons-error-message').removeClass('ui-state-focus');
	   $('.ui-icon').addClass('ui-icon-alert');
	   $('.ui-icon').removeClass('ui-icon-info'); 
	}
	else{
	   $('#neo-addons-error-message').addClass('ui-state-focus');
	   $('#neo-addons-error-message').removeClass('ui-state-error');
	   $('.ui-icon').addClass('ui-icon-info');
	   $('.ui-icon').removeClass('ui-icon-alert');
	}
	$('#neo-addons-error-message-text').text(s);
	$('#neo-addons-error-message').show('slow', 'linear', function() {
		setTimeout(function() {
			$('#neo-addons-error-message').fadeOut();
		}, 10000);
	});
}

function do_iniciarCompra(name_rpm)
{
	var link = $('#'+name_rpm+'_link').val();
	location.href = link+refer;
}

function do_iniciarInstallUpdate(name_rpm)
{
	$.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'iniciarInstallUpdate',
		name_rpm:	name_rpm
	},
	function (respuesta) {
		if(respuesta["db_error"])
		    mostrar_mensaje(respuesta["db_error"],true);
		else{
		    transaction_in_progress = true;
		    mostrar_dialogo_progreso();
		    $(".neo-progress-bar-title").text("Installing/Updating "+name_rpm);
		    intervalCheckStatus = setInterval("do_checkStatus()",1000);
		}
	});
}

function do_iniciarUninstall(name_rpm)
{
	$.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'iniciarUninstall',
		name_rpm:	name_rpm
	},
	function (respuesta) {
		if(respuesta["db_error"])
		    mostrar_mensaje(respuesta["db_error"],true);
		else{
		    transaction_in_progress = true;
		    mostrar_dialogo_progreso();
		    $(".neo-progress-bar-title").text("Uninstalling "+name_rpm);
		    intervalCheckStatus = setInterval("do_checkStatus()",1000);
		}
	});
}

function neo_upgrade_progress_bar(percentage)
{
    $('.neo-progress-bar-percentage').css('width',percentage+'%');
    $('.neo-progress-bar-percentage-tag').html(percentage+'%');
    $('.neo-progress-bar-progress').css('width',percentage+'%');
}

function mostrar_dialogo_progreso()
{
    var maskHeight = $(document).height();
    var maskWidth = $(window).width();
 
    $('.neo-modal-blockmask').css({'width':maskWidth,'height':maskHeight});
     
    $('.neo-modal-blockmask').fadeIn(600);   
    $('.neo-modal-blockmask').fadeTo("fast",0.8);

    var winH = $(window).height();
    var winW = $(window).width();
           
    $('.neo-modal-box').css('top',  winH/2-$('.neo-modal-box').height()/2);
    $('.neo-modal-box').css('left', winW/2-$('.neo-modal-box').width()/2);
 
    $('.neo-modal-box').fadeIn(2000);
    dialogo_progreso_abierto = true;
}

function ocultar_dialogo_progreso()
{
	$('.neo-modal-box').fadeOut(10);
	$('.neo-modal-blockmask').fadeOut(20);
	neo_upgrade_progress_bar(0);
	dialogo_progreso_abierto = false;
}

function loadingAddons()
{
     var loadingHTML = "<div style='text-align: center; padding: 40px;'>"
			    +"<img src='images/loading.gif' />"
		      +"</div>";
     $('#addonlist').empty().append(loadingHTML);
}

function neo_upgrade_progress_bar(percentage)
{
	$('.neo-progress-bar-percentage').css('width',percentage+'%');
	$('.neo-progress-bar-percentage-tag').html(percentage+'%');
	$('.neo-progress-bar-progress').css('width',percentage+'%');
}

function do_checkStatus()
{
	$.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'checkStatus'
	},
	function (respuesta) {
	      if(respuesta["db_error"]){ // Error en la base de datos local
		  ocultar_dialogo_progreso();
		  mostrar_mensaje(respuesta["db_error"],true);
		  do_clearYum();
		  transaction_in_progress = false;
		  checking_dependencies = false;
		  clearInterval(intervalCheckStatus);
	      }
	      else if(respuesta["status"] == "error"){ // Error al realizar yum, pueden ser errores de dependencias
		  var error = "";
		  for(key in respuesta["error_description"])
		      error += respuesta["error_description"][key]+"\n";
		  ocultar_dialogo_progreso();
		  mostrar_mensaje(error,true);
		  do_clearYum();
		  transaction_in_progress = false;
		  checking_dependencies = false;
		  clearInterval(intervalCheckStatus);
	      }
	      else if(respuesta["status"] == "busy"){ // Transacción en progreso
		  if(!dialogo_progreso_abierto)
		      mostrar_dialogo_progreso();
		  if(!intervalCheckStatus)
		      intervalCheckStatus = setInterval("do_checkStatus()",1000);
		  if(respuesta["action"]){
		      if(respuesta["action"] == "Checking Dependencies")
			checking_dependencies = true;
		  }
		  $('#feedback').text(respuesta["info"]);
		  $('.neo-progress-bar-title').text(respuesta["title"]);
		  neo_upgrade_progress_bar(respuesta["percentage"]);
		  transaction_in_progress = true;
	      }
	      else if(respuesta["status"] == "idle"){ // Sistema ocioso
		  do_deleteActionTmp();
		  ocultar_dialogo_progreso();
		  clearInterval(intervalCheckStatus);
		  $('#feedback').text('');
		  if(transaction_in_progress){
		//      do_invalidarCacheAddons();
		      if(respuesta["transaction_status"])
			  mostrar_mensaje(respuesta["transaction_status"],false);
		      if(!checking_dependencies)
			  do_listarAddons(null);
		      transaction_in_progress = false;
		  }
		  if(checking_dependencies){
		      checkServerID(estadoCliente.name_rpm);
		      checking_dependencies = false;
		  }
		  estadoCliente.name_rpm = null;
	      }
	});
}

function do_clearYum()
{
      $.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'clearYum'
	},
	function (respuesta) {
	});
}

function do_deleteActionTmp()
{
      $.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'deleteActionTmp'
	},
	function (respuesta) {
	});
}

function cancelTransaction()
{
    $.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'cancelTransaction'
	},
	function (respuesta) {
	    if(respuesta["error"])
		alert(respuesta["error"]);
	    else if(respuesta["db_error"])
		alert(respuesta["db_error"]);
	});
}

/*
function do_invalidarCacheAddons()
{
      $.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'invalidarCacheAddons'
	},
	function (respuesta) {
	     do_listarAddons(null);
	});
}*/

function do_checkDependencies(name_rpm)
{
      $.post('index.php?menu=' + module_name + '&rawmode=yes', {
		menu:		module_name, 
		rawmode:	'yes',
		action:		'checkDependencies',
		name_rpm:	name_rpm
	},
	function (respuesta) {
	     if(respuesta["db_error"])
		    mostrar_mensaje(respuesta["db_error"],true);
		else{
		    transaction_in_progress = true;
		    checking_dependencies = true;
		    mostrar_dialogo_progreso();
		    $(".neo-progress-bar-title").text("Checking Dependencies "+name_rpm);
		    intervalCheckStatus = setInterval("do_checkStatus()",1000);
		}
	});
}