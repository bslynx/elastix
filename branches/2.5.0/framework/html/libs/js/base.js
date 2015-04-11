function changeMenu()
{
	if ($('#miniMenu').is(':visible')) {
		// Cambiar de mini a menú completo
		$('#miniMenu').hide();
		$('#fullMenu').show();
		$('#tdMenuIzq').show();
	} else {
		// Cambiar de menú completo a mini
		$('#fullMenu').hide();
		$('#tdMenuIzq').hide();
		$('#miniMenu').show();
	}
}

    function openWindow(path) { popUp(path, 700, 460); }

    // Función de compatibilidad
    function confirmSubmit(message) { return confirm(message); }

    function popUp(path,width_value,height_value)
    {
        var features = 'width='+width_value+',height='+height_value+',resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
        var popupWin = window.open(path, "_cmdWin", features);
        popupWin.focus();
        //return true;
    }
    // End -->



var current_setTimeout = null;
function request(url,arrParams, recursive, callback)
{
    callback           = callback  || null;
    recursive          = recursive || null;

    /* Por Alex: muchos usuarios de la funcion request() crean un objeto de tipo
     * Array, y asignan propiedades para que sean usadas como parámetros en la
     * petición. Sin embargo, $.post() no acepta un Array, sino un objeto hash
     * ordinario. No se puede pasar el parámetro arrParams directamente a
     * $.post(), porque resulta en la ausencia de parámetros en la petición.
     * Por lo tanto, se tiene que iterar por las claves asignadas, y recolectar
     * el valor correspondiente en un hash ordinario. */
    var params = {};
    var empty_array = new Array();
    for (var k in arrParams) {
    	/* Por Alex: algunas bibliotecas Javascript (en particular Ember.js)
    	 * agregan mixin al Array, el cual a su vez agrega propiedades a todas
    	 * las instancias de Array. Estas propiedades deben ser excluidas de
    	 * las peticiones AJAX, o se presentan errores fatales de Javascript.
    	 * El filtro de abajo podría todavía fallar si la propiedad asignada
    	 * corresponde al mismo tipo de dato que una propiedad del mixin.
    	 * Para una propiedad que se asigna para request pero no está presente
    	 * en el mixin, typeof empty_array[k] debería evaluarse a "undefined".
    	 */
    	if (!(Array.prototype.isPrototypeOf(arrParams) && typeof arrParams[k] == typeof empty_array[k]))
    		params[k] = arrParams[k];
    }

    // Comienza petición por ajax
    $.post(url,
        params,
        function(dataResponse){
            var message        = dataResponse.message;
            var statusResponse = dataResponse.statusResponse;
            var error          = dataResponse.error;
            var stop_recursive = false;

			if(statusResponse == "ERROR_SESSION"){
				$.unblockUI();
				var r = confirm(error);
				if (r==true)
				  location.href = 'index.php';
				return;
			}

            if(callback)
                stop_recursive = callback(message,statusResponse,error);
            if(statusResponse){
                if(recursive & !stop_recursive){
                   current_setTimeout = setTimeout(function(){request(url,arrParams,recursive,callback)},2);
                   //la funcion espera 200ms para ejecutarse,pero la funcion actual si se termina de ejecutar,creando un hilo.
                }
            }
            else{
                //alert("hubo un problema de comunicacion...");
            }
        },
        'json');
    // Termina petición por ajax

}

function existsRequestRecursive()
{
    return (current_setTimeout)?true:false;
}

function clearResquestRecursive()
{
    clearTimeout(current_setTimeout);
}

function hide_message_error(){
    document.getElementById("message_error").style.display = 'none';
}

function ShowModalPopUP(title, width, height, html)
{
    $('.neo-modal-elastix-popup-content').html(html);
    $('.neo-modal-elastix-popup-title').text(title);

    var maskHeight = $(document).height();
    var maskWidth = $(window).width();

    $('.neo-modal-elastix-popup-blockmask').css({'width':maskWidth,'height':maskHeight});

    $('.neo-modal-elastix-popup-blockmask').fadeIn(600);
    $('.neo-modal-elastix-popup-blockmask').fadeTo("fast",0.8);

    var winH = $(window).height();
    var winW = $(window).width();

    var minpad = 10;
    var boxpadx = 25;
    var boxpady = 20;
    var vpad = (winH - height) / 2 - boxpady;
    var hpad = (winW - width) / 2 - boxpadx;
    if (vpad < minpad) vpad = minpad;
    if (hpad < minpad) hpad = minpad;
    $('.neo-modal-elastix-popup-content').css({
        'position':     'absolute',
        'top':          '40px',
        'bottom':       '20px',
        'left':         '20px',
        'right':        '20px'
    });
//    if (vpad == minpad || hpad == minpad) {
        $('.neo-modal-elastix-popup-content').css({
            'overflow-y':   'auto',
            'overflow-x':   'auto'
        });
/*    } else {
        $('.neo-modal-elastix-popup-content').css({
            'overflow-y':   'visible',
            'overflow-x':   'visible'
        });
    }
*/
    $('.neo-modal-elastix-popup-box').css({
        'height': winH - 2 * vpad - 2 * boxpady,
        'top': vpad,
        'width': winW - 2 * hpad - 2 * boxpadx,
        'left': hpad,
        'box-sizing': 'content-box' // para tema tenant
        });
    $('.neo-modal-elastix-popup-box').fadeIn(2000);
    $('.neo-modal-elastix-popup-close').click(function() {
        hideModalPopUP();
    });
}

function hideModalPopUP()
{
    $('.neo-modal-elastix-popup-box').fadeOut(10);
    $('.neo-modal-elastix-popup-blockmask').fadeOut(20);
    $('.neo-modal-elastix-popup-content').html("");
}

function isRegisteredServer()
{
    request("register.php", {
    	action:		'isRegistered',
    	rawmode:	'yes'
    }, false, function(arrData,statusResponse,error) {
        $('.register_link').css('color',arrData['color']);
        $('.register_link').text(arrData['label']);
    });
}

function showPopupCloudLogin(title, width, height)
{
    request("register.php", {
    	action:		'cloudlogin',
    	rawmode:	'yes'
    }, false,  function(arrData,statusResponse,error) {
        ShowModalPopUP(title,width,height,arrData['form']);

        if(arrData['registered']=="yes-all"){
            showLoading(arrData['msgloading']);
            getDataWebServer();
        }
    });
}

function getElastixKey()
{
    var arrAction         = new Array();
    arrAction['action']   = "isRegistered";
    arrAction["rawmode"]  = "yes";

    request("register.php", {
    	action:		'isRegistered',
    	rawmode:	'yes'
    }, false, function(arrData,statusResponse,error) {
        if (arrData["registered"]=="yes-all") {
        	hideModalPopUP();
        	var callback = $('#callback').val();
        	if (callback && callback !="") {
        		if(callback=="do_checkDependencies")
        			do_checkDependencies(arrData["sid"]);
        		else if(callback=="do_iniciarInstallUpdate")
        			do_iniciarInstallUpdate();
        	}
	    }
    });
}

function setAdminPassword()
{
    var title = $('#lblChangePass').val();
    var lblCurrentPass = $('#lblCurrentPass').val();
    var lblNewPass = $('#lblNewPass').val();
    var lblRetypeNewPass = $('#lblRetypePass').val();
    var btnChange = $('#btnChagePass').val();
    var height = 160;
    var width = 380;
    var html =
        "<table class='tabForm' style='font-size: 16px;' width='100%' >" +
            "<tr class='letra12'>" +
                "<td align='left'><b>"+lblCurrentPass+"</b></td>" +
                "<td align='left'><input type='password' id='curr_pass' name='curr_pass' value='' /></td>" +
            "</tr>" +
            "<tr class='letra12'>" +
                "<td align='left'><b>"+lblNewPass+"</b></td>" +
                "<td align='left'><input type='password' id='curr_pass_new' name='curr_pass_new' value='' /></td>" +
            "</tr>" +
            "<tr class='letra12'>" +
                "<td align='left'><b>"+lblRetypeNewPass+"</b></td>" +
                "<td align='left'><input type='password' id='curr_pass_renew' name='curr_pass_renew' value='' /></td>" +
            "</tr>" +
            "<tr class='letra12'>" +
                "<td align='center'  colspan='2'><input type='button' id='sendChanPass' name='sendChanPss' value='"+btnChange+"' onclick='saveNewPasswordElastix()' /></td>" +
            "</tr>" +
        "</table>";
    ShowModalPopUP(title,width,height,html);
}

function saveNewPasswordElastix()
{
	var arrAction = new Array();
	var oldPass   = $('#curr_pass').val();
	var newPass   = $('#curr_pass_new').val();
	var newPassRe = $('#curr_pass_renew').val();

	if(oldPass == ""){
	  var lable_err = $('#lblCurrentPassAlert').val();
	  alert(lable_err)
	  return
	}
	if(newPass == "" || newPassRe == ""){
	  var lable_err = $('#lblNewRetypePassAlert').val();
	  alert(lable_err);
	  return;
	}
	if(newPass != newPassRe){
	  var lable_err = $('#lblPassNoTMatchAlert').val();
	  alert(lable_err);
	  return;
	}

	request('index.php', {
    	menu:			'_elastixutils',
    	action:			'changePasswordElastix',
    	oldPassword:	oldPass,
    	newPassword:	newPass,
    	newRePassword:	newPassRe
	}, false, function(arrData,statusResponse,error) {
		alert(error);
	    if (statusResponse != "false") {
			hideModalPopUP();
		}
	});
}

function elastix_blockUI(msg)
{
	$.blockUI({
		message:	"<div style='margin: 10px;'><div align='center'><img src='images/loading2.gif' /></div><div align='center'><span style='font-size: 14px; '>"+msg+"</span></div></div>"
	});
}

// Procedimiento que carga el diálogo de lista de paquetes Elastix instalados
function showElastixPackageVersionDialog()
{
	request('index.php', {
    	menu:		'_elastixutils',
    	action:		'dialogRPM',
    	rawmode:	'yes'
    }, false, function(arrData,statusResponse,error) {
        ShowModalPopUP(arrData['title'],380,800,arrData['html']);

        // La plantilla tiene una referencia a script que llama a versionRPM
    });
}

$(document).ready(function(){
    //***Para los módulos con filtro se llama a la función pressKey
    if (document.getElementById("filter_value") ||
        document.getElementById("pageup") ||
        document.getElementById("neo-sticky-note-textarea")) {
        $('#pageup').keypress(keyPressed);
        $('#pagedown').keypress(keyPressed);
    }

    $('#viewDetailsRPMs').click(showElastixPackageVersionDialog);
    $('a.setadminpassword').click(setAdminPassword);
    $('#dialogaboutelastix').click(function() {
        request("register.php", {
            action:     'showAboutAs',
            rawmode:    'yes'
        }, false, function(arrData,statusResponse,error) {
            ShowModalPopUP(arrData['title'],380,100,arrData['html']);
        });
    });

	$( "#search_module_elastix" )
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( "autocomplete" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			autoFocus: true,
		    delay: 0,
			minLength: 0,
			source: function(request, response){
				//$("#neo-cmenu-showbox-search").removeClass("neo-display-none");
				$("#neo-cmenu-showbox-search").hover(
				  function() {
					$("#neo-cmenu-showbox-search").removeClass("neo-display-none");
				  },
				  function() {
					$("#neo-cmenu-showbox-search").removeClass("neo-display-none");}
				);
				$.ajax({
					url: 'index.php?menu=_elastixutils&action=search_module&rawmode=yes',
					dataType: "json",
					data: {
						name_module_search: ((request.term).split( /,\s*/ ) ).pop()
					},
					success: function( data ) {
						response( $.map( data, function( item ) {
							return {
								label: item.caption,
								value: item.value
							}
						}));
					}
				});
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			open: function() { // parche que resuelve el bug del panel de busqueda de modulo en PBX
				var top_var  = $('.ui-autocomplete').css("top");
				var left_var = $('.ui-autocomplete').css("left");
				if(top_var == "0px" & left_var == "0px"){
					var searchPosition = $('#search_module_elastix').position();
					var top = searchPosition.top + 53;
					if (/Chrome[\/\s](\d+\.\d+)/.test(navigator.userAgent))
						top = searchPosition.top + 50;
					$('.ui-autocomplete').css("top",top+"px");
					$('.ui-autocomplete').css("left","1054px");
					$('.ui-autocomplete').css("width","174px");
				}
			},
			close: function() {
				$('#neo-cmenu-showbox-search').one('click', function(e) {
					//$( "#search_module_elastix" ).autocomplete( "close" );
					$( "#search_module_elastix" ).val("");
					e.stopPropagation();
				});
				$('body').one('click', function(e) {
					$("#neo-cmenu-showbox-search").hover(
					  function() {
						$("#neo-cmenu-showbox-search").removeClass("neo-display-none");
					  },
					  function() {
						$("#neo-cmenu-showbox-search").addClass("neo-display-none");
					  }
					);
					$("#neo-cmenu-showbox-search").addClass("neo-display-none");
					e.stopPropagation();
				});
				//$("#neo-cmenu-showbox-search").addClass("neo-display-none");
			},
			/*change: function( event, ui ) {

			},*/
			select: function( event, ui ) {
				//$("#neo-cmenu-showbox-search").removeClass("neo-display-none");
				this.value = ui.item.label;
				document.location.href = "?menu="+ui.item.value;
				// enviando la redireccion al index.php
				return false;
			}
	});

    var menu = getParameterByName("menu");
        if (typeof  menu!== "undefined" && menu) {
            var lblmenu = menu.split("_");

            if(lblmenu["0"]=="a2b") {
                $('#myframe').load(function() {
                    $(".topmenu-right-button a",myframe.document).attr("target","_self");
            });
        }
    }

    // En la clase paloSantoForm.class.php, a los input radio se definio
    // que tengan un estilo defaulf de jquery, para ello se declara una
    // clase global para que hereden todos los input radio el nuevo estilo.
    $( ".radio_buttonset_elx" ).buttonset();

    // En el index.php del framework se hacía uso de smarty para
    // setear el estado del registro, ahora se hace desde javascript.
    isRegisteredServer();
});

//Si se presiona enter se hace un submit al formulario para que se aplica el filtro
function keyPressed(e)
{
    var keycode;
    if (window.event) keycode = window.event.keyCode;
    else if (e) keycode = e.which;
    else return true;

	if (keycode == 13) {
		$("form").submit();
		return false;
	}
}

//Capturar el valor del parametro dado del url
function getParameterByName(name) {
    var match = RegExp('[?&]' + name + '=([^&]*)')
                    .exec(window.location.search);
    return match && decodeURIComponent(match[1].replace(/\+/g, ' '));

}

// Recoger el valor del módulo activo a partir de elastix_framework_module_id
function getCurrentElastixModule()
{
	return $('#elastix_framework_module_id').val();
}
