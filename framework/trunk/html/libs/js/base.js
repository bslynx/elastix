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

$(document).ready(function(){
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
// implement JSON.parse de-serialization  
    function JSONRPMtoString(str) {
        if (str === "") str = '""';
        eval("var p=" + str + ";");
        return p;
    }

