var elx_phone = null;
var elx_flag_changed_profile = false;
$(document).ready(function(){
    pull = $('#pull');
    menu = $('nav.elx_nav_main_menu > ul');
    menuHeight = menu.height();
    //leftdiv = $('#leftdiv');
    //centerdiv = $('#centerdiv');
    main_content_div = $('#main_content_elastix'); //div que contiene lo que cada modulo tiene 
    rightdiv = $('#rightdiv'); //panel lateral en donde aparece el chat
    pull3 = $('#icn_disp2'); //icono que despliega y oculta el chat
    
    /*despliegue del menu en pantallas pequeñas*/           
    /*$(this).on('click','#pull',function(e){
        e.preventDefault();
        menu.slideToggle();
    });*/
    
   /* $(this).on('click','nav.elx_nav_main_menu > ul li',function(e){
        var ul=$(this).children('ul')
        ul.css('display:block','opacity: 1','visibility: visible');
    });*/
    
    $(this).on('click','.elx-msg-area-close',function(e){
        $("#elx_msg_area").slideUp();  
    });
                            
    $(window).resize(function(){
        w = $(window).width();
        var tmpSize=0;
        if(w>=500){
            if(rightdiv.is(':hidden') == false){ //esta abierto
                $("#elx_chat_space").css("right","200px");
                tmpSize= tmpSize + 180;
            }
            tmpSize = w - tmpSize;
            main_content_div.css("width",tmpSize+"px");
            $("#elx_chat_space").show(1);
        }else{
            if(rightdiv.is(':hidden') == false){ //esta abierto
                $("#elx_chat_space").hide(1,function(){
                    $("#elx_chat_space").css("right","15px");
                });
            }else
                $("#elx_chat_space").css("right","15px");
            main_content_div.css("width",w+"px"); 
        }
        
        adjustTabChatToWindow(w);
        
        /*setea el estilo del menu una vez que se maximiza la pantalla*/   
        /*if(menu.is(':hidden')) 
            menu.removeAttr('style');*/
        
        //calulamos la altura maxima del div del chat donde estan los contactos
        if(rightdiv.is(':hidden') == false){
            adjustHeightElxListUser();
        }
        
        //se calcula el alto del contenido del modulo y se resta del alto del navegador cada
        //que se haga un resize, para que aparezca el scroll cuando sea necesario
        scrollContentModule()
    });

    //se calcula el alto del contenido del modulo y se resta del alto del navegador cada
    //que se haga un resize, para que aparezca el scroll cuando sea necesario
    scrollContentModule();
    
    /* evento que modifica el estilo de todos los paneles, al pulsar el icono para desplegar u ocultar 
    el panel lateral derecho (rightpanel)*/         
    $(this).on('click','#icn_disp2',function(e){
        var w = $(window).width();
        if( rightdiv.is(':hidden') ){ //estaba oculto y lo abrimos
            //es necesario modificar la el margin right del espacio del chat
            if(w>=500){
                $("#elx_chat_space").css("right",200+"px");
                //modificamos el tamaño del div principal
                tmpSize = w - 180;
                main_content_div.css("width",tmpSize+"px");
            }else{
                //escondemos las pestañas del chat activas
                $("#elx_chat_space").hide(10);
            }
            rightdiv.show(10,function(){
                adjustHeightElxListUser();
            });
            adjustTabChatToWindow(w);
        }else{ //estaba abierto y lo cerramos
            if(w>=500){
                //es necesario modificar la el margin right del espacio del chat
                $("#elx_chat_space").css("right",15+"px");
                //si esta abierto lo coultamos y modificamos el tamaño de la pantalla
                tmpSize = w;
                main_content_div.css("width",tmpSize+"px");
            }else{
                $("#elx_chat_space").show(10);
            }
            rightdiv.hide(10);
            $("#elx_im_list_contacts").css('height','');
            adjustTabChatToWindow(w);
        }
    });
    
   

    $(this).on('click','#boxclose',function(){
    //$('#boxclose').click(function(){
        $('#box').animate({'top':'-200px'},500,function(){
            $('#overlay').fadeOut('fast');
        });
    });
    
    $(this).on('click','.elx_li_contact',function(){
            var uri=$(this).attr('data-uri');
            var alias=$(this).attr('data-alias');
            var name=$(this).attr('data-name');
            //verifcamos si ya existe un chat abierto a este usuario
            //en caso de existir se lo crea
            if($(window).width()<500){
                rightdiv.hide(10);
            }
            var elx_tab_chat=startChatUser(uri,name,alias,'sent');
            elx_tab_chat.find('.elx_text_area_chat > textarea').focus();
            $("#elx_chat_space").show(10);
        }
    );
    
    // Acciones para controlar las ventanas de chat
    $(this).on('click', '.elx_close_chat', function() {
    	// Cerrar la ventana del chat (realmente la oculta, pero el div sigue presente)
    	
        $(this).parents(".elx_tab_chat").removeClass('elx_chat_active').addClass('elx_chat_close');
        //debemos comprobar si ahi pestañas minimizadas por falta de espacio
        //si existen entonces abrimos la ultima pestaña
        var chatMIn=$("#elx_chat_space_tabs > .elx_chat_min").last();
        if(chatMIn!=='undefined'){
            chatMIn.removeClass('elx_chat_min').addClass('elx_chat_active');
            removeElxUserNotifyChatMini(chatMIn);
        }
    });
    $(this).on('click', '.elx_min_chat', function() {
    	// Minimizar la ventana de chat    	
        $(this).removeClass("glyphicon-minus elx_min_chat").addClass("glyphicon-resize-vertical elx_max_chat");
        $(this).parents(".elx_header_tab_chat").next('.elx_body_tab_chat').css('display','none');
    });
    $(this).on('click', '.elx_max_chat', function() {
    	// Restaurar la ventana de chat    	
        $(this).removeClass("glyphicon-resize-vertical elx_max_chat").addClass("glyphicon-minus elx_min_chat");
        $(this).parents(".elx_header_tab_chat").next('.elx_body_tab_chat').css('display','block');
    });
    $(this).on('click', 'div.elx_header2_tab_chat > span.glyphicon-envelope', function() {
    	// Envío de correo al usuario del chat
    	elx_newEmail($(this).parents('.elx_tab_chat').data('alias'));
    });
    $(this).on('click', 'div.elx_header2_tab_chat > span.glyphicon-print', function() {
    	// Envío de fax al usuario del chat
    	showSendFax($(this).parents('.elx_tab_chat').data('alias'));
    });
    
    //accion que controla cuando damos enter en el text-area de una de la pestañas del chat
    $(this).on("keydown",".elx_chat_imput", function( event ) {
            // Ignore TAB and ESC.
            if (event.which == 9 || event.which == 27) {
                return false;
                // Enter pressed? so send chat.
            }else if ( event.which == 13 && $(this).val()!='') {
                event.preventDefault();
                //debemos mandar el mensaje y 
                //hacer que el texto del text area desaparezca y sea enviado la divdel chat al que corresponde
                var elx_txt_chat=$(this).val();
                var elx_tab_chat=$(this).parents('.elx_tab_chat:first');
                
                //addMessageElxChatTab(elx_tab_chat,'out',elx_txt_chat);
                $(this).val('');
                sendMessage(elx_txt_chat,elx_tab_chat.attr('data-alias'));
                // Ignore Enter when empty input.
            }else if (event.which == 13 && $(this).val() == "") {
                event.preventDefault();
                return false;
            }
        }
    );
    $(this).on("click",".elx_tab_chat", function( event ) {
        $(this).children('.elx_header_tab_chat').removeClass('elx_blink_chat');
        $(this).find('.elx_text_area_chat > textarea').focus();
    });
    getElastixContacts();
    
    //motificaciones en pestañas de chat minimizadas por falta de espacio
    $('#elx_notify_min_chat_box').on("click",function(event){
        var hidMinList=$('#elx_hide_min_list').val();
        if(hidMinList=='yes'){
            //se deben ocultar la lista de las conversaciones minimizadas por falta de espacio
            $("#elx_notify_min_chat_box").removeClass('elx_notify_min_chat_box_act');
            $('#elx_hide_min_list').val('no');
            $("#elx_list_min_chat").css('visibility','hidden');
        }else{
            //antes de mostrar la lista debemos calcular si el espacio que queda es suficiente para
            //mostrar los elementos de la lista
            //si no queda mucho espacio cambiamos la direccion de la lista al otro lado
            //se deben mostrar la lista de las conversaciones minimizadas por falta de espacio
            $("#elx_notify_min_chat_box").addClass('elx_notify_min_chat_box_act');
            $('#elx_hide_min_list').val('yes');
            var offElement=$("#elx_notify_min_chat_box").offset();
            var widthList = $("#elx_list_min_chat > div > .elx_list_min_chat_ul").width();
            if((offElement.left-40)>(widthList)){
                //existe suficiente espacio para mostrar la lista 
                $("#elx_list_min_chat").css('right','0px');
                $("#elx_list_min_chat").css('left','');
            }else{
                //no existe suficiente espacio para mostrar la lista 
                $("#elx_list_min_chat").css('left','0px');
                $("#elx_list_min_chat").css('right','');
            }
            $("#elx_list_min_chat").css('visibility','visible');
        }
    });
    $(this).on('click','.elx_min_name',function(event){
        $(this).children(".elx_min_chat_num").css('visibility','hidden');
        //se deben ocultar la lista de las conversaciones minimizadas por falta de espacio
        $("#elx_notify_min_chat_box").removeClass('elx_notify_min_chat_box_act');
        $('#elx_hide_min_list').val('no');
        $("#elx_list_min_chat").css('visibility','hidden');
        var alias=$(this).parents('.elx_list_min_chat_li:first').attr('data-alias');
        var elx_tab_chat=startChatUser(alias,name,alias,'sent');
        elx_tab_chat.find('.elx_text_area_chat > textarea').focus();
        elx_tab_chat.find('.elx_tab_tittle_icon > span:first').removeClass("glyphicon-resize-vertical elx_max_chat").addClass("glyphicon-minus elx_min_chat");
        elx_tab_chat.find('.elx_body_tab_chat').css('display','block');
    });
    $(this).on('click','.elx_min_remove',function(event){
        var liIcon=$(this).parents('.elx_list_min_chat_li:first');
        var alias=liIcon.attr('data-alias');
        liIcon.remove();
        var tabChat=getTabElxChat(alias);
        tabChat.removeClass('elx_chat_min').addClass('elx_chat_close');
        //disminuir la cuenta de las conversaciones minimizadas y en caso de no quedar niguna ocultar tab notificaciones
        removeElxUserNotifyChatMini(tabChat);
    });
    
    //ejecuta la accion de cambio de lenguaje del usuario del popup de profile
    $(this).on('change','#languageProfile',function(){
        var language = $("select[name='languageProfile'] option:selected").val();
        changeLanguageProfile(language);
    });
    
    //ejecuta la accion de eliminar la imagen que contiene el popup de profile del usuario
    $(this).on('click','#deleteImageProfile',function(){
            deleteImageProfile();
    });
    
    //ejecuta la funcion de cambio de imagen del popup de profile del usuario
    $(this).on('click','#picture',function(){
        changeImageProfile();
    });
    
    //muestra y oculta el div que contiene las cajas de textos para cambiar las contraseñas dentro del popup de profile
    $(this).on('click','#elx_link_change_passwd',function(){
        if($('#elx_data_change_passwd').hasClass('visible')== true){
            $("#elx_data_change_passwd").removeClass("visible").addClass("oculto");
        }else{
            $("#elx_data_change_passwd").removeClass("oculto").addClass("visible");
        }
    });
    
    //manda a recagar la pagina, luego de haber hecho algun cambio en el popup de profile del usuario
    $(this).on('click','.elx_close_popup_profile',function(){
        if(elx_flag_changed_profile==true){
            location.reload();
        }
    });
    
    
    //funcionpara habilitar los campos de cambio de contraseña, luego que escriban la contraseña actual
    $(this).on('click','#currentPasswordProfile',function(){
        $('#currentPasswordProfile').keyup(function(){
            if($('#currentPasswordProfile').val() != ''){
                $('#newPasswordProfile').removeAttr('disabled');
                $('#repeatPasswordProfile').removeAttr('disabled');
                $('#elx_save_change_passwd').removeAttr('disabled');
            }else{
                $('#newPasswordProfile').attr('disabled','disabled');
                $('#repeatPasswordProfile').attr('disabled','disabled');
                $('#elx_save_change_passwd').attr('disabled','disabled');
            }
        });
    });
    
    //despliega en efecto slider el menu oculto, en tamño < 480px;
    $(this).on('click','#elx-navbar-min',function(){
        if ($("#elx-slide-menu-mini").is(":hidden") ) {
            $("#elx-slide-menu-mini").slideDown("slow");
            setTimeout(function() { $("#elx-slide-menu-mini").css('overflow','visible'); }, 600);
            $("#rightdiv").animate({
                top: "88px"
            }, 600 );
        } else {
            $("#elx-slide-menu-mini").slideUp("slow");
            $("#rightdiv").animate({
                top: "55px"
            }, 600 );
        }
        
    });
    
    //oculta o muestra la opcion de subir archivo para la opción "sendfax"
    $(this).on('click','#elx-chk-attachment-file',function(){
        if($(this).is(':checked')) {
            $("#elx-body-fax-label").removeClass("visible").addClass("oculto");
            $("#elx-body-fax-content").removeClass("visible").addClass("oculto");
            $("#elx-attached-fax-file").removeClass("oculto").addClass("visible");
            $("#elx-notice-fax-file").removeClass("oculto").addClass("visible");
            $("textarea[name='faxContent']").val("")
        }else{
            $("#elx-body-fax-label").removeClass("oculto").addClass("visible");
            $("#elx-body-fax-content").removeClass("oculto").addClass("visible");
            $("#elx-attached-fax-file").removeClass("visible").addClass("oculto");
            $("#elx-notice-fax-file").removeClass("visible").addClass("oculto");
        }
    });
    
    //captura ingresado por el teclado y manda a consultar a la base los contactos del chat
    $(this).on('keyup','#im_search_filter',function(){
        searchElastixContacts();
    });
});
//se calcula el alto del contenido del modulo y se resta del alto del navegador cada
//que se haga un resize, para que aparezca el scroll cuando sea necesario
function scrollContentModule(){
    if( $('.elx-modules-content').length )
    {
        var height_browser = $(window).height();
        var offElement=$(".elx-modules-content").offset();
        $(".elx-modules-content").css("height",height_browser-offElement.top +"px");
    }  
}
function elxTitleAlert(message){
       
     $.titleAlert(message, {
        requireBlur:true,
        stopOnFocus:true,
        interval:600
    });
}
function adjustHeightElxListUser(){
    h = $("#b3_1").height();
    max_h=h-$("#head_rightdiv").height()-15;
    $("#elx_im_list_contacts").css('height',max_h+"px");
}
function changeModuleUF(moduleName){
    if(typeof(moduleName) == 'undefined' || moduleName === null) //nada que hacer no paso el modulo
        return false;
    
    var regexp_user = /^[\w-_]+$/;
    if (moduleName.match(regexp_user) == null) return false;
    
    showElastixUFStatusBar('Loading Module...');
    
    var arrAction = new Array();
    arrAction["changeModule"]  = "yes";
    arrAction["rawmode"] = "yes";
    arrAction["menu"] = moduleName;
    request("index.php",arrAction,false,
        function(arrData,statusResponse,error){
            hideElastixUFStatusBar();
            if(error!=''){
                alert(error);
            }else{
                //var cssFiles=arrData['CSS_HEAD'];
                
                //var jsFiles=arrData['JS_HEAD'];
                //cargamos los scripts del modulo
                var content="<input type='hidden' id='elastix_framework_module_id' value='"+moduleName+"' />";
                content +=arrData['JS_CSS_HEAD'];
                content +=arrData['data'];
                $("#module_content_framework").html(content);
                //se debe setear el contenido de la barra #main_opc en cada modulo
                //aun no se quien deberia hacer esto
                //$("#main_opc").html(arrData['CONTENT_OPT_MENU']);
            }
        }
    );
}

function showElastixUFStatusBar(msgbar){
    $("#notify_change_elastix").css('display','block');
    if(msgbar){
        $(".progress-bar-elastix").html(msgbar);
    }else{
        $(".progress-bar-elastix").html('Loading..');
    }
}
function hideElastixUFStatusBar(){
    $("#notify_change_elastix").css('display','none');
}
function showElxUFMsgBar(status,msgbar){
    if(status=='error'){
        $("#elx_msg_area_text").removeClass("alert-success").addClass("alert-danger");
    }else{
        $("#elx_msg_area_text").removeClass("alert-danger").addClass("alert-success");
    }
    $("#elx_msg_area_text").html(msgbar);
    $("#elx_msg_area").slideDown(); 
}
function getElastixContacts(){
    var hDiv=$('#rightdiv').height();
    $('#startingSession').css({'top':hDiv/2-10,'display':'block'});
    var arrAction = new Array();
    arrAction["action"]  = "getElastixAccounts";
    arrAction["menu"] = "_elastixutils";
    visibility="visible";
    request("index.php",arrAction,false,
        function(arrData,statusResponse,error){
            if(error!=''){
                errorRegisterChatBar(error);
            }else{
                //revisamos la informacion del contacto
                if(arrData['my_info'] !== 'undefined'){
                    //mandamos a crear la instancia del web phone para el usario
                    if(createUserAgent(arrData['my_info'])===false){
                        //argumentos faltantes
                        return false;
                    }
                    //setear el color de la presencia del usuario
                    var color_ps_user=getColorPresence(arrData['my_info']['st_code']);
                    $(".elx-content-photo").attr('border-color', color_ps_user);
                }else{
                    //error porque no tenemos los datos del configuración del usuario
                    errorRegisterChatBar('Missing Configurations..');
                    return false;
                }
                
                $('#startingSession').css('display','none');
                $('#b3_1').css('display','block');
                
                //contactos disponibles
                var arrType = new Array('ava','unava','not_found');
                //eliminamos el contenido del div, para poner el nuevo contenido (de la busqueda)
                $("#elx_ul_list_contacts").empty();
                for( var i=0; i<arrType.length; i++){
                    typeAcc=arrType[i];
                    if( typeof arrData[typeAcc] !== 'undefined'){
                        for( var x in arrData[typeAcc]){
                            var div=createDivContact(arrData[typeAcc][x]['idUser'],arrData[typeAcc][x]['display_name'],arrData[typeAcc][x]['uri'],arrData[typeAcc][x]['username'],arrData[typeAcc][x]['presence'],arrData[typeAcc][x]['st_code'], visibility);
                            $("#elx_ul_list_contacts").append(div);
                        }
                    }
                }
            }
        }
    );
}
/******************************************************************************************
* función que muestra el listado de los contactos del chat, segun el criterio de busqueda
******************************************************************************************/
function searchElastixContacts(){
    var pattern = $("input[name='im_search_filter']").val();
    
    $(".elx_contact .elx_im_name_user").each(function ()
    {
        var str = $(this).html();
        if (str.match(pattern)) {
            ($(this).parent()).parent().removeClass("oculto");
            ($(this).parent()).parent().addClass("visible");
        }else{
            ($(this).parent()).parent().removeClass("visible");
            ($(this).parent()).parent().addClass("oculto");
        }
        
    });
}

/**
 * Esta función construye una nueva instancia de una plantilla donde se muestra
 * la información del contacto SIP para chat, con los datos ya rellenados. La
 * plantilla base está definida en index_uf.tpl .
 * 
 * @param idUser			ID del usuario representado
 * @param display_name		Nombre completo del usuario representado
 * @param uri				URI del contacto SIP IM para el usuario (ya no se usa?)
 * @param alias				URI del contacto SIP telefónico para el usuario
 * @param presence			Descripción inicial del estado de presencia
 * @param presence_code		Código para el estado de presencia (ya no se usa?)
 * @param visibility		Clase que define estado inicial de visibilidad
 * @returns
 */
function createDivContact(idUser, display_name, uri, alias, presence, presence_code, visibility)
{
	var liContact = $('#elx_template_contact_status > li').clone()
		.addClass('elx_li_contact')
		.attr('data-uri', uri)
		.attr('data-alias', alias)
		.attr('data-name', display_name)
		.attr('data-idUser', idUser);
	liContact.find('div.elx_contact').addClass(visibility);
	liContact.find('.box_status_contact').css('background-color', 'grey');
	liContact.find('.elx_im_name_user').text(display_name);
	liContact.find('.extension_status').text(presence);
	return liContact;
}
/*
function createDivPersonal(){
    var divContact ="<div class='elx_personal_info'>";
    divContact +="<div class='elx_im_name_user'>"+display_name+"</div>";
    divContact +="<div id='elx_im_status_user' class='elx_im_status_user'><div class='box_status_contact' style='background-color:green'></div></div>";
    divContact +="</div>";
    return divContact;
}
*/
function getColorPresence(presence_code){
    /*-1 = Extension not found
    0 = Idle
    1 = In Use
    2 = Busy
    4 = Unavailable
    8 = Ringing
    16 = On Hold*/
    
    var color='';
    if(presence_code=='-1'){
       color='';
    }else if(presence_code=='0'){
       color='#8cbe29';
    }else if(presence_code=='1'){
        color='red';
    }else if(presence_code=='2'){
        color='black';
    }else if(presence_code=='4'){
        color='grey';
    }else if(presence_code=='8'){
        color='orange';
    }else if(presence=='16'){
        color='orange';
    }
    return color;
}
/******************************************************************
 * Funciones usadas para el crear el dispositivo sip del usuario
*******************************************************************/
/*function createUserAgent(UAParams){
    var configuration = new Array();
    var UAManParam = new Array('uri','password','ws_servers');
    var UAOpParam = new Array('display_name', 'authorization_user', 'register', 'register_expires', 'no_answer_timeout', 'trace_sip', 'stun_servers', 'turn_servers', 'use_preloaded_route', 'connection_recovery_min_interval', 'connection_recovery_max_interval', 'hack_via_tcp', 'hack_ip_in_contact');
    for( var i=0; i<UAManParam.length; i++){
        param=UAManParam[i];
        if(undefinedUAParam(UAParams[param])){
            errorRegisterChatBar("Missing Mandatory Param: '"+param+"'");
            return false;
        }else{
            configuration[param] = UAParams[param];
        }
    }
    for( var i=0; i<UAOpParam.length; i++){
        param=UAOpParam[i];
        if(!undefinedUAParam(UAParams[param])){
            configuration[param] = UAParams[param];
        }
    }
    elx_phone = new JsSIP.UA(configuration);
    elx_phone.on('newMessage', function(e){
        var text,
        message = e.data.message,
        request = e.data.request;
        uri = message.remote_identity;
        if(message.direction === 'incoming'){
            var display_name = request.from.display_name || request.from.uri.user;
            var elx_txt_chat = request.body;
            
            if(uri instanceof JsSIP.URI) {
                var uri2=uri.toAor().substr(4);
            }else{
                //revisar el formato de string y parsearlo para asegurarnos que tenemos
                //algo con el  fromato user@domain
                var uri2=uri.substr(4);
            }
            //verificamos si existe una conversacion abierta con el dispositivo
            //si no existe la creamos
            var elx_tab_chat=startChatUser(uri2,display_name,uri2,'receive');
            if(!elx_tab_chat.hasClass('elx_chat_min')){
                if(!elx_tab_chat.find('.elx_text_area_chat > textarea').is(':focus')){
                    //añadimos clase que torna header anaranjado para indicar que llego nuevo mensaje
                    elx_tab_chat.children('.elx_header_tab_chat').addClass('elx_blink_chat');
                }
            }
            elxTitleAlert("New Message "+display_name);
            addMessageElxChatTab(elx_tab_chat,'in',elx_txt_chat);
        }
    });
    elx_phone.on('unregistered', function(e){
        alert("Desconectado... " + elx_phone.configuration.display_name );    
    });
    elx_phone.on('registrationFailed', function(e) {
        if (! e.data.response) {
            console.info("SIP registration error:\n" + e.data.cause);
            errorRegisterChatBar("SIP registration error:\n" + e.data.cause);
        }else {
            console.info("SIP registration error:\n" + e.data.response.status_code.toString() + " " + e.data.response.reason_phrase)
            errorRegisterChatBar("SIP registration error:\n" + e.data.response.status_code.toString() + " " + e.data.response.reason_phrase);
        }
    });
    elx_phone.start();
}*/
var uri="";
var ua;
var sp;
function createUserAgent(UAParams){
//console.log(UAParams);

UAParam = UAParams;
var config = {
     uri: UAParams.elxuser_username,
     wsServers: UAParams.ws_servers,
     displayName: UAParams.display_name,
     password: UAParams.password,
     hackIpInContact: UAParams.hack_ip_in_contact,
     autostart: true,
     register: true,
     traceSip: true,
    
    };


    uri = UAParams.elxuser_username;
    ua = new SIP.UA(config);
     
    ua.on('connected', function () {
        console.log('Connected');
    });

    ua.on('registered', function () {
        console.log('(Registered)');
        if (sp == null) {
	        sp = new SIPPresence(ua);
	        sp.publishPresence();
	        $(".elx_li_contact").each(function(i, v) { sp.subscribeToRoster($(v).data('alias')) });
        }
    });

    ua.on('unregistered', function () {
        console.log('Unregistered');
    });

    ua.on('message', receiveMessage);

}

function getDisplayName(user)
{
 $(".margin_padding_0").each(function(i) {
      if (user == $(this).attr("data-alias")) {
           dname = $(this).attr("data-name");
      }
  })
 return dname;
}

function receiveMessage (e) {

  var remoteUri = e.remoteIdentity.uri.toString();
  remoteUser = remoteUri.split('sip:');
 
  uri2 = remoteUser[1];
  var elx_txt_chat = e.body;
            
  //verificamos si existe una conversacion abierta con el dispositivo
  //si no existe la creamos
  var elx_tab_chat=startChatUser(uri2,uri2,uri2,'receive');
        if(!elx_tab_chat.hasClass('elx_chat_min')){
                if(!elx_tab_chat.find('.elx_text_area_chat > textarea').is(':focus')){
                    //añadimos clase que torna header anaranjado para indicar que llego nuevo mensaje
                    elx_tab_chat.children('.elx_header_tab_chat').addClass('elx_blink_chat');
                }
        }
 
  addMessageElxChatTab(elx_tab_chat,'in',elx_txt_chat);

}

function undefinedUAParam(param){
    if(typeof param !== 'undefined'){
        if(param != '')
            return false;
    }
    return true;
}

function sendMessage(msg_txt,alias)
{    
    var elx_tab_chat=getTabElxChat(alias);
    
    addMessageElxChatTab(elx_tab_chat,'out',msg_txt);

    ua.message(alias, msg_txt).on('failed', function (response, cause) {
                elx_tab_chat.find(".elx_content_chat").find("div").last().css("color","red");
                if(elx_tab_chat!=false){
                    if (response)
                        var error_msg='<span style="color:red"> '+response.status_code.toString()+" "+response.reason_phrase+'</span>';
                    else
                        var error_msg='<span style="color:red"> '+cause+'</span>';
                        addMessageElxChatTab(elx_tab_chat,'in',error_msg);
                }else{
                    if (response)
                        alert(response.status_code.toString()+" "+response.reason_phrase);
                    else
                        alert(cause);
                }
    });
 
}
function inconmigMessage(){
    
}
function inconmigMessageNotify(){
    
}
function registerPhone()
{
    elx_phone.register();
}
function unregisterPhone()
{
    elx_phone.unregister();
}

/**
 * Función para buscar el div de la conversación del usuario, dado su alias.
 * Se devuelve el div del chat, si se encuentra, o false si no existe.
 */
function getTabElxChat(alias)
{
	var chatTab = $("#elx_chat_space_tabs > .elx_tab_chat[data-alias='" + alias + "'] :first");
	return chatTab.length > 0 ? chatTab : false;
}
//funcion que crea una nueva pestaña de chat
//con las opciones dadas
//devuelve el objeto jquery del div que continene el chat
function startChatUser(uri, name, alias, action)
{
    var can_add_chat = true;

    // Se intenta reutilizar la ventana activa de un chat previo
    var elx_tab_chat = getTabElxChat(alias);

    if (!elx_tab_chat) {
    	// Ventana para este usuario no existe, se debe de crear una nueva
        if (action=='receive') var name = getDisplayName(name);
        
        var content = $('#elx_template_tab_chat > .elx_tab_chat').clone()
        	.attr('data-alias', alias)
        	.attr('data-uri', uri);
        content.find('.elx_tab_chat_name_span').text(name);
    
        // Se agrega el nuevo chat a la lista de ventanas de chat
        $("#elx_chat_space_tabs").prepend(content);
        elx_tab_chat = $("#elx_chat_space_tabs > .elx_tab_chat:first");
        
        /* Llegado a este punto, la situación es igual que si se hubiese 
         * encontrado anteriormente la ventana, en estado minimizado. */
    }

    if (!elx_tab_chat.hasClass('elx_chat_active')) {
        /* La ventana solicitada esta minimizada o fue cerrada nateriormente. Se
         * procede a abrirla si se dispone de suficiente espacio. */
        can_add_chat = resizeElxChatTab($(window).width(), action);
        if (can_add_chat) {
            //funcion que maneja el hecho de que aparezca una venta del chat que estaba aculta
            if(elx_tab_chat.hasClass('elx_chat_close')){
                //si existia el tab pero tenia esta clase significa que se chateo en un momento pero
                //de ahi se cerro la ventana del chat por lo que la volvemos a abrir
                elx_tab_chat.removeClass('elx_chat_close').addClass('elx_chat_active');
                removeElxUserNotifyChatMini(elx_tab_chat);
            } else if(elx_tab_chat.hasClass('elx_chat_min')){
                elx_tab_chat.removeClass('elx_chat_min').addClass('elx_chat_active');
                removeElxUserNotifyChatMini(elx_tab_chat);
            }
        }
    }
    
    
    //se recibio un nuevo mensaje y la pestaña del chat del que envia el mensaje no puede
    //ser abierta por falta de espacio
    //debemos mostrar una notificacion del nuevo mensaje
    if(action=='receive' && !can_add_chat){
        addElxUserNotifyChatMini(elx_tab_chat);
        elx_tab_chat.removeClass('elx_chat_close').addClass('elx_chat_min');
        $("#elx_notify_min_chat_box").addClass('elx_notify_min_chat_box_act');
        //necesitamos subrayar dentro de la lista de los chats minimizados, el item que corresponde a quien mando el mensaje
        $('#elx_list_min_chat > div > .elx_list_min_chat_ul > .elx_list_min_chat_li').each(function() {
            if(alias == $(this).attr('data-alias')){
                $(this).find(".elx_min_name > .elx_min_chat_num").css('visibility','inherit');
            }
        });
    }

    return elx_tab_chat;
}

/**
 * Esta funcion revisa el ancho de la pantalla y dependiendo del mismo determina cual es el maximo 
 * número de pestañas que pueden estar activas
 * recibe como parámetros el tamaño de la pantalla y la acción que hace que sea necesario empezar 
 * un nueva conversación
 * Las acciones pueden ser:  sent o receive
 *      sí la acción es 'sent' => 1) si ya se ha alcanzado el máximo número de pestañas activas
 *                                   se procede a minimizar una de las pestañas activas para hacer espacio
 *                                   para la nueva pestaña, retornamos true
 *                                 2) si no se ha alcanzado el máximo número de pestañas activas retornamos true
 *      sí la acción es 'receive' => 1) si se alcanzo el máxmimo número de pestañas activas retornamos false
 *                                   2) si no se ha alcanzo el máxmimo número de pestañas activas retornamos true 
 **/
function resizeElxChatTab(elx_w,action){
    var max_tab=getMaxNumTabChat(elx_w); 
    
    //revisar el número de pestañas activas
    var num_act_chat=$("#elx_chat_space_tabs > .elx_chat_active").size();
    
    //al número actual de chats abiertos le sumamos uno del nuevo chat que vamos a abrir
    if((num_act_chat+1)>max_tab){
        if(action=='sent'){
            var elxTabChatToMin=$("#elx_chat_space_tabs > .elx_chat_active").first();
            elxTabChatToMin.removeClass('elx_chat_active').addClass('elx_chat_min');
            addElxUserNotifyChatMini(elxTabChatToMin);
            return true;
        }else{
            return false;
        }
    }else{
        return true;
    }
}
function getMaxNumTabChat(elx_w){
    //esta la venta del chat abierta
    var is_chat_open=!$("#rightdiv").is(':hidden');
    var max_tab=1; 
    //se definio que el máximo número de pestañas abiertas que podían haber sin importar 
    //tamaño sería 4
    if(is_chat_open){
        if(elx_w>1200){
            max_tab=4;
        }else if(elx_w>=960 && elx_w<1200){
            max_tab=3;
        }else if( elx_w>=730 && elx_w<960){
            max_tab=2;
        }
    }else{
        if(elx_w>=1010){
            max_tab=4;
        }else if( elx_w>=780 && elx_w<1010){
            max_tab=3;
        }else if(elx_w>=550 && elx_w<780){
            max_tab=2;
        }
    }
    //para tamaños menores de pantalla el número máximo de pestañas es 1
    
    return max_tab;
}
function errorRegisterChatBar(error){
    alert(error);
    $('#b3_1').css('display','none');
    $('#startingSession').html(error);
    $('#startingSession').css({'display':'block',margin:'5px'});
}
function addMessageElxChatTab(chatTab,direction,message){

    var elx_content_chat = chatTab.children('.elx_body_tab_chat:first').children('.elx_content_chat:first');
    if(direction=='out'){
        elx_who="<b>me: </b>";
    }else{
        
        var send_name=chatTab.find('.elx_tab_chat_name > .elx_tab_chat_name_span').text();
        if(send_name!=='undefined' && send_name!=''){
            //si el nombre del que esta enviando el mensaje contine espacios en blanco
            //lo cortamos para evitar que el nombre ocupe mucho espacio
            var space_pos=send_name.indexOf(" ");
            if(space_pos!=-1){
                elx_who="<b>"+send_name.substr(0,space_pos)+": </b>";
            }else{
                elx_who="<b>"+send_name+": </b>";
            }
        }else{
            elx_who="<b>receive: </b>";
        }

        elxTitleAlert("New Message "+send_name);
    }
    elx_content_chat.append("<div>"+elx_who+message+"</div>");
    elx_content_chat.scrollTop(1e4);
}
function addElxUserNotifyChatMini(elxTabChatToMin){
    alias=elxTabChatToMin.attr('data-alias');
    var exist=false;
    var numMinchat=0;
    //debemos comprabar si el nuevo tab que se quiere añadir ya se encuentra en la lista
    //si se encuentra entonces no lo añadimos
    $('#elx_list_min_chat > div > .elx_list_min_chat_ul > .elx_list_min_chat_li').each(function() {
        if(alias == $(this).attr('data-alias')){
            exist=true;
        }else
            numMinchat++;
    });
    
    if(!exist){
        name=elxTabChatToMin.find('.elx_tab_chat_name > .elx_tab_chat_name_span').html();
        var elx_chat_user = "<li class='elx_list_min_chat_li' data-alias='"+alias+"'>";
        elx_chat_user +="<span class='elx_min_span'>";
        elx_chat_user +="<div class='glyphicon glyphicon-remove elx_min_remove'></div>"; //imagen de x para cerrar
        elx_chat_user +="<div class='elx_min_name'><span class='elx_min_chat_num' style='visibility:hidden'>*</span><span class='elx_min_chat_name'>"+name+"</span></div>";
        elx_chat_user +="</span>";
        elx_chat_user +="</li>";
        
        $('#elx_list_min_chat > div > .elx_list_min_chat_ul').prepend(elx_chat_user);
    
        //actualizamos el numero de chat minimizados
        $("#elx_num_mim_chat").html(numMinchat+1);
        
        //si no se estaba mostrando el chat con las notificaciones lo mostramos
        $("#elx_notify_min_chat").removeClass('elx_nodisplay');
    }
}
function removeElxUserNotifyChatMini(elxTabChatToMin){
    alias=elxTabChatToMin.attr('data-alias');
    var numMinchat=0
    $('#elx_list_min_chat > div > .elx_list_min_chat_ul > .elx_list_min_chat_li').each(function() {
        if(alias == $(this).attr('data-alias')){
            $(this).remove();
        }else
            numMinchat++;
    });
    if(numMinchat==0){
        //ocultamos el div con las notificaciones
        $("#elx_notify_min_chat").addClass('elx_nodisplay');
        $("#elx_notify_min_chat_box").removeClass('elx_notify_min_chat_box_act');
        $('#elx_hide_min_list').val('no');
        $("#elx_list_min_chat").css('visibility','hidden');
    }else{
        //actualizazamos la informacion del numero de chat abiertos minimizados
        $("#elx_num_mim_chat").html(numMinchat);
    }
}
function adjustTabChatToWindow(elxw){ 
    //contralamos el número de pestañas activas abiertas de acuerdo al tamamaño de la pantalla
    var max_tab=getMaxNumTabChat(elxw); 
    //revisar el número de pestañas activas
    var num_act_chat=$("#elx_chat_space_tabs > .elx_chat_active").size();
    //si el número de pestañas activas es mayor que el máximo
    //entones procedemos a minimizar pestañas
    if(num_act_chat>max_tab){
        for(var i=0;i<(num_act_chat-max_tab);i++){
            var elxTabChatToMin=$("#elx_chat_space_tabs > .elx_chat_active").first();
            elxTabChatToMin.removeClass('elx_chat_active').addClass('elx_chat_min');
            addElxUserNotifyChatMini(elxTabChatToMin);
        }
    }else{
        //si existen pestañas minimizadas y el número de activas es menor que el máximo procedemos
        //a minizar pestañas
        if(num_act_chat < max_tab){
            for(var i=0;i<(max_tab-num_act_chat);i++){
                //si existen entonces abrimos la ultima pestaña
                var chatMIn=$("#elx_chat_space_tabs > .elx_chat_min").last();
                if(chatMIn!=='undefined'){
                    chatMIn.removeClass('elx_chat_min').addClass('elx_chat_active');
                    removeElxUserNotifyChatMini(chatMIn);
                }else{
                    break;
                }
            }
        }
    }
}


function elxGridData(moduleName, action, arrFilter, page){
    var currentNumPage=$("#elxGridNumPage").val();
    
    //validar si page es un numero
    if(isNaN(page)){
        page=1;
    }else{
        if (page % 1 != 0) {
            page=1;
        } 
    }

    
    if(page>currentNumPage){
        page=currentNumPage;
    }
    
    var arrAction = new Array();
    arrAction["menu"]=moduleName;
    arrAction["action"]=action;
    arrAction["page"]=page;
    arrAction["nav"]='bypage';
    
    for( var x in arrFilter){   
        arrAction[x]=arrFilter[x];
    }
    
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if (error != '' ){
                $("#message_area").slideDown();
                $("#msg-text").removeClass("alert-success").addClass("alert-danger");
                $("#msg-text").html(error['stringError']);
                // se recorre todos los elementos erroneos y se agrega la clase error (color rojo)
            }else{
                var content='';
                var grid=arrData['content'];
                for(var i=0;i<grid.length;i++){
                    content+='<tr>';
                    for(var j=0;j<grid[i].length;j++){
                        content +="<td>"+grid[i][j]+"</td>";
                    }
                    content+='</tr>';
                }
                $("#elx_data_grid > table > tbody").html(content);
                
                var url=arrData['url'];
                
                var newUrl=url+"&exportcsv=yes&rawmode=yes"; 
                
                $("#exportcsv > a").attr('href', newUrl);
                $("#exportspreadsheet > a").attr('href', newUrl);
                $("#exportpdf > a").attr('href', newUrl);
                
                if(arrData['numPage']==0){
                    arrData['numPage']=1;
                    page=1;
                }
                
                $("#elxGridNumPage").val(arrData['numPage']);
                $("#elxGridCurrent").val(page);
                
                var options = {
                    currentPage: page,
                    totalPages: $("#elxGridNumPage").val(),
                    }
                
                $('#elx_pager').bootstrapPaginator(options); 

            }
    });
}

/*funcion que muestra la ventana del popup para editar datos del perfil de usuaurio en sesion*/
function showProfile(){
    var arrAction = new Array();
    arrAction["menu"]="_elastixutils";
    arrAction["action"]="getUserProfile";
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error != ''){
                alert(error);
            }else{
                $("#elx_popup_content").html(arrData);
                var options = {
                    show: true
                    }
                $('#elx_general_popup').modal(options);
            }
        }
    );       
}

/*funcion que permte cambiar la contraseña al usuario*/
function saveNewPasswordProfile(){
    var oldPass   = $("input[name='currentPasswordProfile']").val();
    var newPass   = $("input[name='newPasswordProfile']").val();
    var newPassRe = $("input[name='newPasswordProfile']").val();
    var arrAction = new Array();
    arrAction["menu"]="_elastixutils";
    arrAction["action"]="changePasswordElastix";
    arrAction["oldPassword"]   = oldPass;
    arrAction["newPassword"]   = newPass;
    arrAction["newRePassword"] = newPassRe;
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error != ''){
                alert(error);
            }else{
                //alert("Changed");
            }
        }
    );       
}

/*funcion para fuardar el leguaje escogido por el usuario*/
function changeLanguageProfile(language){
    var arrAction = new Array();
    arrAction["menu"]="_elastixutils";
    arrAction["action"]="changeLanguageProfile";
    arrAction["newLanguage"]   = language;
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error != ''){
                alert(error);
            }else{
                elx_flag_changed_profile = true;
                //alert(arrData);
            }
        }
    );       
}

/*funcion para eliminar imagen del perfil de usuario*/
function deleteImageProfile(){
    var arrAction = new Array();
    arrAction["menu"]="_elastixutils";
    arrAction["action"]="deleteImageProfile";
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error != ''){
                alert(error);
            }else{
                elx_flag_changed_profile = true;
                resetImage(arrData);
            }
        }
    );    
}

/*funcion para cambiar la imagen del perfil de usuario*/
function changeImageProfile(){
    
    $('.picturePopupProfile').liteUploader(
    {
        script: '?menu=_elastixutils&action=changeImageProfile&rawmode=yes',
        allowedFileTypes: null,
        maxSizeInBytes: null,
        customParams: {
            'custom': 'tester'
        },
        before: function (files)
        {
            $('#previews').empty();
        },
        success: function (response)
        {   
            var response = $.parseJSON(response);
            if(response.error !== ''){
                alert(response.error);
            }else{
                elx_flag_changed_profile = true;
                resetImage(response.message);
            }
        }
    });   
}


function resetImage(url)
{
    $('#previews').empty();
    $('#previews').append($('<img>', {
        'id':'preview',
        'class':'img-responsive',
        'src': url  + '#' + new Date().getTime(),
        'width': 159
        }));
}

//llama a la función "showSendFax" que muestra la ventana del popup para enviar fax
//la función se encuentra dentro del módulo "my_fax"
function showSendFax(alias){
    var arrAction = new Array();
    arrAction["menu"]="my_fax";
    arrAction["action"]="showSendFax";
    if(alias){
        arrAction["alias"]=alias;
    }
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error != ''){
                alert(error);
            }else{
                $("#elx_popup_content").html(arrData);
                var options = {
                    show: true
                    }
                $('#elx_general_popup').modal(options);
                formSendFax();
            }
        }
    );       
}

/*llama a la función "sendFax dentro del módulo "my_fax""*/

function sendNewFax(){
    var arrAction = new Array();
    arrAction["menu"]="my_fax";
    arrAction["action"]="sendNewFax";
    arrAction["to"]=$("input[name='destinationFaxNumber']").val();
    if($('#elx-chk-attachment-file').is(':checked')) {
        arrAction["checked"]="true";
    }else{
        arrAction["body"]=$("textarea[name='faxContent']").val();
        arrAction["checked"]="false";
    }
    
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if (error != '' ){
                alert(error);
            }else{
                alert(arrData);
                $('#elx_general_popup').modal('hide');
            }
    });      
}

/*función para subir el archivo en el popup de "sendFax"*/
function formSendFax(){

    $('#faxFile').liteUploader(
    {
        script: '?menu=my_fax&action=faxAttachmentUpload&rawmode=yes',
        allowedFileTypes: null,
        maxSizeInBytes: null,
        customParams: {
            'custom': 'tester'
        },
        each: function (file, errors)
        {
            if (errors.length > 0)
            {
                alert('Error uploading your file');
            }

        },
        success: function (response)
        {
            var response = $.parseJSON(response);
            if(response.error !== ''){
                alert(response.error);
            }else{
                //alert(response.message);
            }
        }
    });
}

function elx_newEmail(alias){
    var arrAction = new Array();
    arrAction["menu"]="home";
    arrAction["action"]="get_templateEmail";
    if(alias){
        arrAction["destination"]=alias;
    }
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error != ''){
                alert(error);
            }else{
                //esto es importante hacer para asegurarmos que no haya 
                //oculto otro elemente con el mismo id
                $("#elx-compose-email").remove();
                $("#elx_popup_content").html(arrData['modulo']);
                $("#elx-compose-email").addClass("modal-content");
                $("#elx-compose-email").prepend("<div class='modal-header'><button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button><h3 id='myModalLabel'>Send Mail/Enviar Mail</h3></div>");
                $("#elx-compose-email").append("<div class='modal-footer'><button type='button' class='btn btn-primary' id='elx_attachButton'>Attach<input type='file' name='attachFileButton' id='attachFileButton'></button><button type='button' class='btn btn-primary' onclick='composeEmail(\"popup\")'>Send</button></div>");    
                emailAttachFile();
                var options = {
                    show: true
                    }
                $('#elx_general_popup').modal(options);
                richTextInit();
                
                //autocomplete mail list
                mailList(arrData['contacts']); 
            }
        }
    );
}

//funcion autocomplete para listar los contactos en la ventana popup de enviar email
function mailList(contacts){
    
    $('textarea[name=compose_to]')
      // don't navigate away from the field on tab when selecting an item
      .bind( "keydown", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB &&
            $( this ).data( "ui-autocomplete" ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        minLength: 0,
        source: function( request, response ) {
          // delegate back to autocomplete, but extract the last term
          response( $.ui.autocomplete.filter(
            contacts, extractLast( request.term ) ) );
        },
        focus: function() {
          // prevent value inserted on focus
          return false;
        },
        select: function( event, ui ) {
          var terms = split( this.value );
          // remove the current input
          terms.pop();
          // add the selected item
          terms.push( ui.item.value );
          // add placeholder to get the comma-and-space at the end
          terms.push( "" );
          this.value = terms.join( ", " );
          return false;
        },
        appendTo: '#compose-to',
      });
      
    setTimeout(function(){ 
        var ancho = $('textarea[name=compose_to]').width();
        ancho = ancho +"px";
        $('.ui-autocomplete').width(ancho);    
    }, 3000);
    
}

function split( val ) {
    return val.split( /,\s*/ );
}
function extractLast( term ) {
    return split( term ).pop();
}

$(window).resize(function(){
        var ancho = $('textarea[name=compose_to]').width();
        ancho = ancho +"px"; 
        $('.ui-autocomplete').width(ancho);
});
 
/**
 * La clase Presentity es una clase que modela el documento XML que contiene la
 * información de presencia RPID. La presencia rica puede estar activa (propiedad
 * open en TRUE) o inactiva. Se puede agregar una nota para describir con más
 * detalle el tipo de actividad en que se encuentra el usuario. Además se soporta
 * una lista de actividades (propiedad activities) cuyo contenido determina si
 * el usuario está disponible, ocupado, ausente, u otra cosa. 
 * 
 * La clase soporta generar el XML a partir de las propiedades, y además parsear
 * un documento XML y separar sus propiedades. La implementación se ha probado
 * con el documento XML publicado por Jitsi.
 */
function Presentity()
{
	// urn:ietf:params:xml:ns:pidf      http://tools.ietf.org/html/rfc3863
	// urn:ietf:params:xml:ns:pidf:rpid http://tools.ietf.org/html/rfc4480
	this.user = "user";
	this.domain = "example.com";
	this.activities = [];
	this.status_icon = null;
	this.open = true;
	this.note = "Online";
	
	/** Generación del documento XML a partir de las propiedades */
	this.toXML = function() {
		var xml_presence = $.parseXML(
			'<?xml version="1.0" encoding="UTF-8" standalone="no"?>' +
			'<presence xmlns="urn:ietf:params:xml:ns:pidf" xmlns:dm="urn:ietf:params:xml:ns:pidf:data-model" xmlns:rpid="urn:ietf:params:xml:ns:pidf:rpid"/>');
		var sipuri = 'sip:' + this.user + '@' + this.domain;

		/* A partir de aquí se usan funciones nativas de XMLDocument porque la
		 * abstracción de jQuery no permite agregar elementos con namespace
		 */
		var xmlpr = xml_presence.getElementsByTagName('presence')[0];
		xmlpr.setAttribute('entity', sipuri);
				
		var xmlpers = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:data-model", "person");
		xmlpers.setAttribute('id', 'p1401');
		var xmlactivities = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:rpid", "activities");

		// http://tools.ietf.org/html/rfc4480
		var knownactivities = ['appointment', 'away', 'breakfast', 'busy', 'dinner',
			'holiday', 'in-transit', 'looking-for-work', 'lunch', 'meal', 'meeting',
			'on-the-phone', 'performance', 'permanent-absence', 'playing', 'presentation',
			'shopping', 'sleeping', 'spectator', 'steering', 'travel', 'tv', 'unknown',
			'vacation', 'working', 'worship'];
		for (var i = 0; i < this.activities.length; i++) {
			var xmlactv
			if (-1 != knownactivities.indexOf(this.activities[i])) {
				xmlactv = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:rpid", this.activities[i]);
			} else {
				xmlactv = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:rpid", 'other');
				xmlactv.appendChild(xml_presence.createTextNode(this.activities[i]));
			}
			xmlactivities.appendChild(xmlactv);
		}
		xmlpers.appendChild(xmlactivities);
		if (this.status_icon != null) {
			var xmlicon = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:rpid", "status-icon");
			xmlicon.appendChild(xml_presence.createTextNode(this.status_icon));
			xmlpers.appendChild(xmlicon);
		}
		xmlpr.appendChild(xmlpers);
		

		var xmltuple = xml_presence.createElement('tuple');
		xmltuple.setAttribute('id', 't1072');

		var xmlstatus = xml_presence.createElement('status');
		var xmlbasic = xml_presence.createElement('basic');
		xmlbasic.appendChild(xml_presence.createTextNode(this.open ? 'open' : 'closed'));
		xmlstatus.appendChild(xmlbasic);
		xmltuple.appendChild(xmlstatus);
		var xmlcontact = xml_presence.createElement('contact');
		xmlcontact.appendChild(xml_presence.createTextNode(sipuri));
		xmltuple.appendChild(xmlcontact);
		var xmlnote = xml_presence.createElement('note');
		xmlnote.appendChild(xml_presence.createTextNode(this.note));
		xmltuple.appendChild(xmlnote);
		xmlpr.appendChild(xmltuple);
		
		return xml_presence;
	}
	
	/** Parseo de un XML y extracción de las propiedades */
	this.fromXML = function(xml_presence) {
		var xmlpr = xml_presence.getElementsByTagName('presence')[0];
		
		var sipuri = xmlpr.getAttribute('entity');
		var m = /^(sip:)?(\S+)@(\S+)$/.exec(sipuri);
		this.user = m[2];
		this.domain = m[3];
		
		this.activities = [];
		var xmlactivities = xml_presence.getElementsByTagNameNS("urn:ietf:params:xml:ns:pidf:rpid", "activities");
		if (xmlactivities.length > 0) for (var i = 0; i < xmlactivities[0].childNodes.length; i++) {
			var xmlactv = xmlactivities[0].childNodes[i];
			if (xmlactv.nodeType == xml_presence.ELEMENT_NODE) {
				var m = /^(\S+:)?(\S+)/.exec(xmlactv.nodeName);
				var nodeName = m[2];
			
				if (nodeName == 'other') {
					// Asume que el texto es el único contenido
					if (xmlactv.childNodes.length > 0)
						this.activities.push(xmlactv.childNodes[0].nodeValue);
				} else {
					this.activities.push(nodeName);
				}
			}
		}
		
		this.status_icon = null;
		var xmlicons = xml_presence.getElementsByTagNameNS("urn:ietf:params:xml:ns:pidf:rpid", "status-icon");
		if (xmlicons.length > 0) {
			// Asume que el texto es el único contenido
			if (xmlicons[0].childNodes.length > 0)
				this.status_icon = xmlicons[0].childNodes[0].nodeValue;
		}
		
		var xmltuple = xml_presence.getElementsByTagName('tuple');
		if (xmltuple.length > 0) {
		
			this.open = false;
			var xmlstatus = xmltuple[0].getElementsByTagName('status');
			if (xmlstatus.length > 0) {
				var xmlbasic = xmlstatus[0].getElementsByTagName('basic');
				if (xmlbasic.length > 0) {
					this.open = ('open' == xmlbasic[0].childNodes[0].nodeValue);
				}
			}
			this.note = "Offline";
			var xmlnote = xmltuple[0].getElementsByTagName('note');
			if (xmlnote.length > 0) {
				this.note = xmlnote[0].childNodes[0].nodeValue;
			}
		}
	}
	
	this.toString = function() {
		var xml = (new XMLSerializer()).serializeToString(this.toXML());
		xml = xml.replace(/ xmlns=""/g, '');
		return xml;
	}
	
	this.fromString = function(s) {
		return this.fromXML($.parseXML(s));
	}
}

function SIPPresence(ua)
{
	this.ua = ua;
	this.presentity = new Presentity();
	this.presentity.user = ua.configuration.authorizationUser;
	this.presentity.domain = ua.configuration.hostportParams;
	this.presenceETag = null;
	this.presenceTimer = null;
	this.publishRequest = null;
	this.roster = {}; // Subscripciones a lista de contactos
	this.subsWatch = null;	// Subscripción a presence.winfo
	
	this.getLocalContact = function() {
		return this.ua.configuration.authorizationUser + '@' + this.ua.configuration.hostportParams;
	}
	
	/**
	 * Método para iniciar la publicación de la presencia del usuario local.
	 * Para mitigar la situación de que no es posible cerrar la presencia de 
	 * forma síncrona, se establece la expiración de la presencia a 90 segundos,
	 * y se actualiza la presencia cada 60 segundos con un timer.
	 */
	this.publishPresence = function () {
	
		if (this.presenceETag == null) {
			// Verificar si se tiene un PUBLISH previo en el servidor
			$.get('index.php', {
				menu: '_elastixutils',
				action: 'getPublishETag'			
			}, function(data) {
				if (data['message'] != "") {
					//console.log('SE TIENE PUBLISH PREVIO!');
					this.presenceETag = data['message'];
				}
				this._publishPresence();
			}.bind(this));
		} else {
			this._publishPresence();
		}
	}
	this._publishPresence = function() {
		if (this.publishRequest == null) {
			var extrahdr = [
				'Event: presence',
				'Content-Type: application/pidf+xml',
				'Contact: ' + ua.contact.toString()
			];
			this.publishRequest = this.ua.request('PUBLISH', this.getLocalContact(), {
				body: this.presentity.toString(),
				extraHeaders: extrahdr
			});
			this.publishRequest.request.setHeader('Expires', '90');
			if (this.presenceETag != null) {
				this.publishRequest.request.setHeader('SIP-If-Match', this.presenceETag);
			}
			this.publishRequest.on('accepted', function (response, cause) {
				if (response.getHeader('Expires') != "0") {
					this.presenceETag = response.getHeader('Sip-Etag');
					// mandar this.presenceETag al servidor para recuperar 
					// SIP-If-Match luego de recargar la página
					$.post('index.php', {
						menu: '_elastixutils',
						action: 'setPublishETag',
						PublishETag: this.presenceETag
					}, function(data) {});
					
					this.publishRequest.request.setHeader('SIP-If-Match', this.presenceETag);
				} else {
					this.publishRequest = null;
					this.presenceETag = null;
					$.post('index.php', {
						menu: '_elastixutils',
						action: 'setPublishETag',
						PublishETag: ''
					}, function(data) {});
				}
			}.bind(this));
		} else {
			this.publishRequest.send();
		}

		if (null == this.presenceTimer)
			this.presenceTimer = window.setInterval(this.publishPresence.bind(this), 60 * 1000);
	}
	
	/**
	 * Método para retirar la presencia de la cuenta local, efectivamente indicado
	 * que se retira la sesión de chat.
	 */
	this.withdrawPresence = function () {
		
		if (null != this.presenceTimer) {
			window.clearInterval(this.presenceTimer);
			this.presenceTimer = null;
		}

		if (null == this.publishRequest) return;
		
		this.publishRequest.request.setHeader('Expires', '0');
		this.publishRequest.send();

		this._unsubscribeWithServerCheck(this.getLocalContact(), this.subsWatch);
		this.subsWatch = null;
		for (var contact in this.roster) this.unsubscribeFromRoster(contact);
		
	}
	
	/**
	 * Método privado que encapsula una subscripción a un contacto para que se
	 * verifique si hay un Call-ID previo para una subscripción previa a ese 
	 * mismo contacto. Si la hay, la subscripción resultante renueva la 
	 * subscripción anterior en lugar de crear una nueva subscripción.
	 */
	this._subscribeWithServerCheck = function(contact, event, checkCallback, notifyCallback) {
		var subscription = this.ua.subscribe(contact, event, {expires: 120});
		subscription.on('notify', notifyCallback);
		checkCallback(contact, subscription);
	}
	
	/**
	 * Método privado para deshacer una subscripción y anular el Call-ID de la
	 * subscripción que se destruye.
	 */
	this._unsubscribeWithServerCheck = function(contact, subscription) {
		if (subscription != null) subscription.unsubscribe();
	}
	
	/* Ejecutar la subscripción al evento presence.winfo . Este evento informa de
	 * quién desea observar al usuario local, y la política seguida aquí es la
	 * de autorizar de inmediato la escucha. */
	/*
	<?xml version="1.0"?>
	<watcherinfo xmlns="urn:ietf:params:xml:ns:watcherinfo" version="1" state="full">
	  <watcher-list resource="sip:avillacis@pbx.villacis.com" package="presence"/>
	</watcherinfo>
	
	<?xml version="1.0"?>
	<watcherinfo xmlns="urn:ietf:params:xml:ns:watcherinfo" version="2" state="partial">
	  <watcher-list resource="sip:avillacis@pbx.villacis.com" package="presence">
	    <watcher id="87590e851228150e5980f7ca45a1b9ce@0:0:0:0:0:0:0:0" event="subscribe" status="pending">sip:gmacas@pbx.villacis.com</watcher>
	  </watcher-list>
	</watcherinfo>

	*/
	this._subscribeWithServerCheck(this.getLocalContact(), 'presence.winfo', function(contact, subscription) {
		this.subsWatch = subscription;
	}.bind(this), function(notification) {
		var xmlwatch = $.parseXML(notification.request.body);

		$(xmlwatch).find('watcherinfo > watcher-list[package=presence] watcher[event=subscribe][status=pending]')
			.each(function(idx, value) {			
			//console.log("Aprobando ingreso a roster de contacto: " + $(value).text());
			this.subscribeToRoster($(value).text().replace(/^sip:/, ''));
		}.bind(this));		
	}.bind(this))
	
	/**
	 * Método para subscribirse a la presencia de un contacto, y recibir estado
	 * de presencia. Hasta ahora funciona con Jitsi. 
	 */
	this.subscribeToRoster = function(contact) {
		//console.log('Analizando ingreso de contacto ' + contact);
		if (this.roster[contact] != null) return;
		
		//console.log('Suscribiendo a contacto ' + contact);
		
		this._subscribeWithServerCheck(contact, 'presence', function(contact, subscription) {
			this.roster[contact] = subscription;
		}.bind(this), function (notification) {
			//console.log("RECIBIDO presence");
			//console.log(notification);

			var pres = new Presentity();
			pres.open = false;
			pres.note = 'Offline';
			pres.user = notification.request.from.uri.user;
			pres.domain = notification.request.from.uri.host;
			if ('' != notification.request.body) {
				pres.fromString(notification.request.body);
			}
			
			var newColor = 'grey';
			if (pres.open) {
				newColor = '#8cbe29';
				if (pres.activities.length > 0) newColor = 'orange';
				if (-1 != pres.activities.indexOf('busy')) newColor = 'red';
				if (-1 != pres.activities.indexOf('on-the-phone')) newColor = 'red';
			}
			
			this._updateContactStatus(pres.user + "@" + pres.domain, newColor, pres.note);
		}.bind(this))
	}
	
	/**
	 * Método para quitar la subscripción del contacto.
	 */
	this.unsubscribeFromRoster = function(contact) {
		if (this.roster[contact] == null) return;
		this._unsubscribeWithServerCheck(contact, this.roster[contact]);
		delete this.roster[contact];

		this._updateContactStatus(contact, 'grey', 'Offline');
	}
	
	this._updateContactStatus = function(contact, newColor, newNote) {
		var liContact = $(".elx_li_contact[data-alias='" + contact + "']");
		liContact.find('.box_status_contact').css('background-color', newColor);
		liContact.find('.extension_status').text(newNote);
	}
}
