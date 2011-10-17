    <!-- Begin
    var ie4 = (document.all) ? true : false;
    var ns4 = (document.layers) ? true : false;
    var ns6 = (document.getElementById && !document.all) ? true : false;
    var bshowMenu = 1;

    function changeMenu() {

      layerMenu='fullMenu';
      layerMenuMini='miniMenu';
      layerMenuIzq='tdMenuIzq';

      if(bshowMenu==1) {
          bshowMenu=0;
      } else {
          bshowMenu=1;
      }

      if (ie4) {
          if(bshowMenu==1) {
              document.all[layerMenu].style.visibility = "visible";
              document.all[layerMenu].style.position = "";
              if(document.all[layerMenuIzq]) {
                  document.all[layerMenuIzq].style.visibility = "visible";
                  document.all[layerMenuIzq].style.position = "";
              }
              document.all[layerMenuMini].style.visibility = "hidden";
              document.all[layerMenuMini].style.position = "absolute";
          } else {
              document.all[layerMenu].style.visibility = "hidden";
              document.all[layerMenu].style.position = "absolute";
              if(document.all[layerMenuIzq]) {
                  document.all[layerMenuIzq].style.visibility = "hidden";
                  document.all[layerMenuIzq].style.position = "absolute";
              }
              document.all[layerMenuMini].style.visibility = "visible";
              document.getElementById([layerMenuMini]).style.display = "";
              document.all[layerMenuMini].style.position = "";
          }
      }
      if (ns4) {
          if(bshowMenu==1) {
              document.layers[layerMenu].visibility = "show";
              if(document.layers[layerMenuIzq]) {
                  document.layers[layerMenuIzq].visibility = "show";
              }
              document.layers[layerMenuMini].visibility = "hide";
          } else {
              document.layers[layerMenu].visibility = "hide";
              if(document.layers[layerMenuIzq]) {
                  document.layers[layerMenuIzq].visibility = "hide";
              }
              document.layers[layerMenuMini].visibility = "show";
          }
      }
      if (ns6) {
          if(bshowMenu==1) {
              document.getElementById([layerMenu]).style.display = "";
              document.getElementById([layerMenu]).style.position = "";
              if(document.getElementById([layerMenuIzq])!=null) {
                  document.getElementById([layerMenuIzq]).style.display = "";
                  document.getElementById([layerMenuIzq]).style.position = "";
              }
              document.getElementById([layerMenuMini]).style.display = "none";
              document.getElementById([layerMenuMini]).style.position = "absolute";
          } else {
              document.getElementById([layerMenu]).style.display = "none";
              document.getElementById([layerMenu]).style.position = "absolute";
              if(document.getElementById([layerMenuIzq])!=null) {
                  document.getElementById([layerMenuIzq]).style.display = "none";
                  document.getElementById([layerMenuIzq]).style.position = "absolute";
              }
              document.getElementById([layerMenuMini]).style.display = "";
              document.getElementById([layerMenuMini]).style.position = "";
          }
      }
    }

    function openWindow(path)
    {
        var features = 'width=700,height=460,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
        var popupWin = window.open(path, "_cmdWin", features);
        popupWin.focus();
        //return true;
    }

    function confirmSubmit(message)
    {
        var agree=confirm(message);
        if (agree)
            return true ;
        else
	    return false ;
    }
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
    var queryString    = array2QueryString(arrParams);
    callback           = callback  || null;
    recursive          = recursive || null;

    // Comienza petición por ajax
    $.post(url,
        queryString,
        function(dataResponse){
            var message        = dataResponse.message;
            var statusResponse = dataResponse.statusResponse;
            var error          = dataResponse.error;
            var stop_recursive = false;

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

function array2QueryString(arrayParams)//formato: arr["action"]="iniciar";arr["param1"]="mensaje1"
{
    var queryString="";
    var tamanio=0;
    var i=0;
    for(var key in arrayParams){
                tamanio++;
    }
    for(var key in arrayParams){
        if(i==tamanio-1)
            queryString+=key+"="+arrayParams[key];
        else
            queryString+=key+"="+arrayParams[key]+"&";
        i++;
    }
    return queryString;
}

function hide_message_error(){
    document.getElementById("message_error").style.display = 'none';
}

/*********************** new begin* *******************************/
function showPopupElastix(id,titles,widths,heights){
    //var arrAction              = new Array();
    //arrAction["action"]        = "registration";
    //arrAction["rawmode"]       = "yes";
    var arrAction = "action=registration&rawmode=yes";
    $.post("register.php",arrAction,
        function(arrData,statusResponse,error)
        {
            jBoxPopupAero(id ,titles, widths, heights, arrData);
        }
    );
}

function jBoxPopupAero(id ,titulo, ancho, alto, html){
    var div = "<div id='"+id+"' style='position: absolute;'></div>";
    $('#PopupElastix').append(div);
    $('body').data(id , null);
    $("#"+id).html(html);

    $("#"+id).AeroWindow({
        WindowTitle:          titulo,
        //WindowDesktopIconFile:
        WindowDesktopIcon:    false,
        WindowPositionTop:    'center',
        WindowPositionLeft:   'center',
        WindowWidth:          ancho,
        WindowHeight:         alto,
        WindowAnimation:      'easeOutCubic'
    });
    getDataWebServer();
}

function registration(){
    var contactName = $('#contactNameReg').val();
    var email       = $('#emailReg').val();
    var phone       = $('#phoneReg').val();
    var company     = $('#companyReg').val();
    var address     = $('#addressReg').val();
    var city        = $('#cityReg').val();
    var country     = $('#countryReg option:selected').val();
    var idPartner   = $('#idPartnerReg').val();

    error = false;
    txtError = "Please fill the correct values in fields: \n";
    if(!(/^[\w\_\-\.\s\xF1\xD1]+$/.test(contactName)) || contactName == ""){ /*solo letras*/
        error = true;
        txtError += "* Contact Name: Only text \n";
    }
    if(!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) || email == ""){ /*solo email*/
        error = true;
        txtError += "* Email: Only format email \n";
    }
    if(!(/^[0-9\(\)\+\-]+\d$/.test(phone)) || phone == ""){ /*numeros y letras*/
        error = true;
        txtError += "* Phone: text or number \n";
    }
    if(!(/^[A-Za-z0-9\_\-\.\s\xF1\xD1]+$/.test(company)) || company == ""){
        error = true;
        txtError += "* Company: text \n";
    }
    /*if(!(/^[A-Za-z\_\-\.\s\xF1\xD1]+$/.test(address)) || address == ""){
        error = true;
        txtError += "* Address: text \n";
    }*/
    if(!(/^[A-Za-z\_\-\.\s\xF1\xD1]+$/.test(city)) || city == ""){
        error = true;
        txtError += "* City: text \n";
    }
    if(!(/^.+$/.test(country)) || country == "none"){
        error = true;
        txtError += "* Country: Selected a country \n";
    }
    /*if(idPartner == ""){
        error = true;
        txtError += "* Id Partner: text \n";
    }*/
    if(error)
        alert(txtError);
    else{
		$('#tdButtons').hide();
        $('#tdloaWeb').attr("style", "padding-left: 5px; display: block;");
        var arrAction = "action=saveregister&contactNameReg="+contactName+"&emailReg="+email+"&phoneReg="+phone+"&companyReg="+company+"&addressReg="+address+"&cityReg="+city+"&countryReg="+country+"&idPartnerReg="+idPartner+"&rawmode=yes";
        $.post("register.php",arrAction,
            function(arrData,statusResponse,error)
            {
				var response = JSONRPMtoString(arrData);
				var registerText   = $('#lblRegisterCm').val();
				var registeredText = $('#lblRegisteredCm').val();
                alert(response["message"]);
				if(response["statusResponse"]=="TRUE"){
					$('#registrar').hide();
					$('.register_link').css('color','#008800');
					$('.register_link').text(registeredText);
					getElastixKey();
				}else{
					$('#tdButtons').show();
					$('.register_link').css('color','#FF0000');
					$('.register_link').text(registerText);
					$('#tdloaWeb').attr("style", "padding-left: 5px; display: none;");
				}
            }
        );
    }
}

function getDataWebServer()
{
    var arrAction = "action=getDataRegisterServer&rawmode=yes";
    $('#btnAct').hide();
    $('.tdIdServer').hide();
    $.post("register.php",arrAction,
	function(arrData,statusResponse,error)
	{
	    $('#getinfo').hide();
	    if(arrData != null){
		var response = JSONRPMtoString(arrData);
		var status = response['statusResponse'];
		if(status == "OK"){
		    $('#btnAct').show();
		    $('.tdIdServer').show();
		    $('#msnTextErr').hide();
		    $('#contactNameReg').val(response['message']['contactNameReg']);
		    $('#emailReg').val(response['message']['emailReg']);
		    $('#phoneReg').val(response['message']['phoneReg']);
		    $('#companyReg').val(response['message']['companyReg']);
		    $('#addressReg').val(response['message']['addressReg']);
		    $('#cityReg').val(response['message']['cityReg']);
		    $('#countryReg').val(response['message']['countryReg']);
		    $('#identitykey').text(response['message']['identitykeyReg']);
		}else{
		    if(response['error'] != "no registrado"){
			$('#btnAct').show();
			$('.tdIdServer').hide();
			if(response['statusResponse'] == "error"){
			    $('#msnTextErr').show();
			    $('#msnTextErr').text(response['error']);
			    $('#btnAct').hide();
			}else if(response['statusResponse'] == "error-update"){
                            $('#msnTextErr').show();
                            $('#msnTextErr').text(response['error']);
                        }

			if(response['message'] != null){
			    if(response['message']['contactNameReg'])
				$('#contactNameReg').val(response['message']['contactNameReg']);
			    if(response['message']['emailReg'])
				$('#emailReg').val(response['message']['emailReg']);
			    if(response['message']['phoneReg'])
				$('#phoneReg').val(response['message']['phoneReg']);
			    if(response['message']['companyReg'])
				$('#companyReg').val(response['message']['companyReg']);
			    if(response['message']['addressReg'])
				$('#addressReg').val(response['message']['addressReg']);
			    if(response['message']['cityReg'])
				$('#cityReg').val(response['message']['cityReg']);
			    if(response['message']['countryReg'])
				$('#countryReg').val(response['message']['countryReg']);
			    if(response['message']['identitykeyReg'])
				$('#identitykey').text(response['message']['identitykeyReg']);
			}

		    }else if(response['error'] == "no registrado")
			      $('#btnAct').show();
		}
	    }
	}
    );
}

function getElastixKey(){
    var arrAction = "action=getServerKey&rawmode=yes&menu=addons";
    $.post("index.php",arrAction,
	function(arrData,statusResponse,error)
	{
	    var message = JSONRPMtoString(arrData);
	    var serverKey = message["serverKey"];
	    if(serverKey && serverKey != ""){
		var link = $('#link_tmp').val();
		if(link && link !=""){
		    link += serverKey;
		    window.open(link);
		}
	    }
	}
    );
}

/************************************ new end ******************************************/

$(document).ready(function(){
    //***Para los módulos con filtro se llama a la función pressKey
    if(document.getElementById("filter_value"))
	document.onkeypress = keyPressed;
    //*****************************************/
    $(".close_image_box").click(function(){
            $("#boxRPM").attr("style","display: none;");
            $("#fade_overlay").attr("style","display: none;");
        });

    $("#viewDetailsRPMs").click(function(){
        $("#changeMode").attr("style", "visibility: hidden;");
        $("#boxRPM").attr("style","display: block;");
        $("#fade_overlay").attr("style","display: block;");
        $("#loadingRPM").attr("style","display: block;");
        $("#tdTa").attr("style","display: none;");
        $("#tdRpm").attr("style","display: block;");
        $("#tableRMP").html("");
        $("#tdTa").val("");
        var lbltextMode = $("#lblTextMode").val();
        $("#changeMode").text("("+lbltextMode+")");
        $("#txtMode").val("");
        var order = "action=versionRPM&rawmode=yes";
        $.post("index.php", order, function(theResponse){
            $("#loadingRPM").attr("style","display: none;");
            $("#boxRPM").attr("style","display: block;");
            $("#fade_overlay").attr("style","display: block;");
            $("#changeMode").attr("style", "visibility: visible;");
            var message = JSONRPMtoString(theResponse);
            var html = ""; 
            var html2 = "";
            var key = "";
            var key2 = "";
            var message2 = "";
            var i = 0;
            var cont = 0;
            for(key in message){
                html += "<tr class='letra12'>" +
                            "<td class='letra12 tdRPMNamesCol'>&nbsp;&nbsp;<b>Name</b></td>" +
                            "<td class='letra12 tdRPMNamesCol'>&nbsp;&nbsp;<b>Package Name</b></td>" +
                            "<td class='letra12 tdRPMNamesCol'>&nbsp;&nbsp;<b>Version</b></td>" +
                            "<td class='letra12 tdRPMNamesCol'>&nbsp;&nbsp;<b>Release</b></td>" +
                        "</tr>" +
                        "<tr class='letra12'>" +
                            "<td class='letra12 tdRPMDetail' colspan='4' align='left'>&nbsp;&nbsp;" + key + "</td>" +
                        "</tr>";
                /*html2 += "Name|Package Name|Version|Release\n";*/
                cont = cont + 2;
                html2 += "\n " + key+"\n";
                message2 = message[key];
                if(key == "Kernel"){
                    for(i = 0; i<message2.length; i++){
                        var arryVersions = (message2[i][1]).split("-",2);
                        html += "<tr class='letra12'>" +
                                    "<td class='letra12'>&nbsp;&nbsp;</td>" +
                                    "<td class='letra12'>&nbsp;&nbsp;" + message2[i][0] + "(" + message2[i][2] + ")</td>" +
                                    "<td class='letra12'>&nbsp;&nbsp;" + arryVersions[0] + "</td>" +
                                    "<td class='letra12'>&nbsp;&nbsp;" + arryVersions[1] + "</td>" +
                                "</tr>";
                        html2+= "   " + message2[i][0] + "(" + message2[i][2] + ")-"+arryVersions[0] + "-"+arryVersions[1] + "\n";
                        cont++;
                    }
                }else{
                    for(i = 0; i<message2.length; i++){
                        html += "<tr class='letra12'>" +
                                    "<td class='letra12'>&nbsp;&nbsp;</td>" +
                                    "<td class='letra12'>&nbsp;&nbsp;" + message2[i][0] + "</td>" +
                                    "<td class='letra12'>&nbsp;&nbsp;" + message2[i][1] + "</td>" +
                                    "<td class='letra12'>&nbsp;&nbsp;" + message2[i][2] + "</td>" +
                                "</tr>";
                        html2+= "   " + message2[i][0] + "-" + message2[i][1] + "-" + message2[i][2] + "\n";
                        cont++;
                    }
                }

            }
            cont = cont + 2;
            $("#txtMode").attr("rows", cont);
            $("#tableRMP").html(html);
            $("#txtMode").val(html2);
        });
    });

    $("#fade_overlay").click(function(){
        $("#boxRPM").attr("style","display: none;");
        $("#fade_overlay").attr("style","display: none;");
    });

    $("#changeMode").click(function(){
        var viewTbRpm = $("#tdRpm").attr("style");
        if(viewTbRpm == "display: block;"){
            //change lbltextMode
            var lblhtmlMode = $("#lblHtmlMode").val();
            $("#changeMode").text("("+lblhtmlMode+")");
            
            $("#tdRpm").attr("style","display: none;");
            $("#tdTa").attr("style","display: block;");
        }else{
            //change lblHtmlMode
            var lbltextMode = $("#lblTextMode").val();
            $("#changeMode").text("("+lbltextMode+")");
            $("#tdRpm").attr("style","display: block;");
            $("#tdTa").attr("style","display: none;");
        }
    });
});

//Si se presiona enter se hace un submit al formulario para que se aplica el filtro
function keyPressed(e)
{
    var keycode;
    if (window.event) keycode = window.event.keyCode;
    else if (e) keycode = e.which;
    else return true;
    if(keycode == 13){
	$("form").submit();
	return false;
    }
}

// implement JSON.parse de-serialization  
    function JSONRPMtoString(str) {
        if (str === "") str = '""';
        eval("var p=" + str + ";");
        return p;
    }

