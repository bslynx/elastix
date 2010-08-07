var module_name = "addons_avalaibles";
var module_name2 = "addons_installed";

var there_install = false;

$(document).ready(function(){
    $('.install').click(function(){
        if(!there_install){
            var name_rpm = $(this).attr("id"); 
            //$('#'+name_rpm).parent().parent().children(':first-child').children(':first-child').attr("style","visibility:visible;"); // accede a la imagen de loading y la muestra
            $('#'+name_rpm).parent().parent().children(':first-child').children(':first-child').attr("style","display: none;");
            $('#start_'+name_rpm).attr("style","display: block;");
            $('#'+name_rpm).parent().parent().children(':first-child').children(':first-child').next().attr("style","visibility:visible;"); // muestra el contenido del span text alert
            //$('#'+name_rpm).parent().parent().children(':last-child').attr("style","visibility:hidden;"); // accedo 
            $('#'+name_rpm).parent().parent().children(':first-child').next().attr("style","visibility:hidden;"); // oculto la descripcion del addons
            //oculto el boton install
            $('#'+name_rpm).attr("style","display: none;");

            var data_exp = $('.'+name_rpm).text();
            var order = 'menu='+module_name+'&name_rpm='+name_rpm+'&action=install&data_exp='+data_exp+'&rawmode=yes';
            $.post('index.php',order,function(theResponse){
                    var message = JSONtoString(theResponse);
                    if(message['response'] == "there_install"){ //si existe una instalacion en progreso
                        there_install = true;
                        connectJSON("process_installing");
                        window.open("index.php?menu="+module_name2,"_self");
                    }
                    else if(message['response'] == "OK"){ // listo para instalar
                        there_install = true;
                        name_rpm = message['name_rpm'];
                        getStatusInstall(name_rpm);
                    }
                    else if(message['response'] == "error"){ // error no install
                        there_install = false;
                        connectJSON("error_start_install");
                    }
            });
        }
        else
           connectJSON("process_installing");
    });
    // proceso ajax q invoca a instalar los paquetes.....
    getPackagesCache();
});

function getStatusInstall(name_rpm){
    var data_exp = $('.'+name_rpm).text(); // se recibe los datos q seran insertados en la db
    var order = 'menu='+module_name+'&action=get_status&data_exp='+data_exp+'&rawmode=yes';
    
    $.post("index.php", order,
        function(theResponse){
            response = JSONtoString(theResponse);
            var resp = response['response'];
            $('#action_install').val(resp);
////////////////////////////////////////////////////
            var status_action = response['status_action'];
            var name_rpm = response['name_rpm'];

            //$('#'+name_rpm).parent().parent().children(':first-child').children(':first-child').next().text(status_action);
////////////////////////////////////////////////////
            if(resp == "OK"){
                changeStatus(response['name_rpm'],response['view_details']); // listo para instalar
            }
            else
                getStatusInstall(name_rpm);
    });
}

function changeStatus(name_rpm, view_details ){
    // hide loading.gif
    $('.loading').attr("style","visibility:hidden;");
    $('.text_alert').attr("style","visibility:hidden;");
    $('.text_alert').val('none');

    // show td to description
    //$('#'+name_rpm).parent().parent().children(':last-child').attr("style","visibility:visible;");
    // change url install for view details
    $('#'+name_rpm).parent().parent().children(':first-child').next().attr("style","visibility:visible;");

    url_redirect = "index.php?menu="+module_name2;
    //$('#'+name_rpm).parent().parent().children(':last-child').children(':last-child').attr("href",url_redirect);

    //$('#'+name_rpm).parent().parent().children(':last-child').children(':last-child').text(view_details);
    $('#'+name_rpm).parent().parent().children(':first-child').next().children(':last-child').text(view_details);
    

    // seria bueno que se redireccione al modulo addons_installed, despues de 10 segundos si el usuario no lo ha hecho aun.
    //setTimeout("true",10000);
    window.open(url_redirect,"_self");
}

// implement JSON.stringify serialization
    function StringtoJSON(obj) {
        var t = typeof (obj);
        if (t != "object" || obj === null) {
            // simple data type
            if (t == "string") obj = '"'+obj+'"';
            return String(obj);
        }
        else {
            // recurse array or object  
            var n, v, json = [], arr = (obj && obj.constructor == Array);  
            for (n in obj) {  
                v = obj[n]; t = typeof(v);  
                if (t == "string") v = '"'+v+'"';  
                else if (t == "object" && v !== null) v = JSON.stringify(v);
                    json.push((arr ? "" : '"' + n + '":') + String(v));  
            }
            return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
        }
    }

// implement JSON.parse de-serialization  
    function JSONtoString(str) {
        if (str === "") str = '""';
        eval("var p=" + str + ";");
        return p;
    }

// uso de JSON para obtener el arreglo lang.php
    function connectJSON(mensaje_error) {
        var order = 'menu='+module_name+'&action=get_lang&rawmode=yes';
        var message = "";
        $.post("index.php", order,
                function(theResponse){
                    message = JSONtoString(theResponse);
                    alert(message[mensaje_error]);
        });
    }

    function getPackagesCache(){
        var order = 'menu='+module_name+'&action=getPackagesCache&rawmode=yes';
        $.post("index.php", order,
            function(theResponse){
                message = JSONtoString(theResponse);
                if(message['response'] == "there_install"){ //si existe una instalacion en progreso
                    there_install = true;
                    connectJSON("process_installing");
                    //window.open("index.php?menu="+module_name2,"_self");
                }
                else if(message['response'] == "status_confirm"){
                    var con = confirm(message['msg']);
                    if(con){
                        getStatusInstall(message['name_rpm']);
                    }
                }
                else if(message['response'] == "OK"){ // listo para instalar
                    there_install = true;
                    //name_rpm = message['name_rpm'];
                    getStatusCache();
                }
                else if(message['response'] == "error"){ // error no install
                    there_install = false;
                    connectJSON("error_start_install");
                }else if(message['response'] == "noFillDataCache"){// ya esta actualizada la data
                    there_install = false;
                    var arr_data = message['data_cache'];
                    var link_img = "modules/"+module_name+"/images/warning.png";
                    //elastix-developer id
                    //status_elastix-developer id
                    for(var i=0; i<arr_data.length; i++){
                        var rpm_name = arr_data[i]["name_rpm"];
                        var status = arr_data[i]["status"];
                        var observation = arr_data[i]["observation"];
                        var id_status = "status_"+rpm_name;
                        if(document.getElementById(rpm_name)){
                            if(status == "1"){ // se muestra el boton de instalar
                                $("#"+rpm_name).attr("style","display: block;");
                                // se oculta el loading
                                $("#"+rpm_name).parent().children(':first-child').attr('style','display: none;');
                                // cambiando la clase
                                $("#status_"+rpm_name).attr("class","text_install");
                            }else{ // se debe mostrar el error como descripcion
                                // cambiando el loading por img error
                                $("#"+rpm_name).parent().children(':first-child').attr('src',link_img);
                                $("#status_"+rpm_name).attr("class","text_alert");
                            }
                            // cambiando la observacion
                            $("#status_"+rpm_name).text(observation);
                        }
                    }
                }
        });
    }

    function getStatusCache(){
        var order = 'menu='+module_name+'&action=getStatusCache&rawmode=yes';
        $.post("index.php", order,
            function(theResponse){
                response = JSONtoString(theResponse);
    ////////////////////////////////////////////////////
                //var status_action = response['status_action'];
                //var name_rpm = response['name_rpm'];
                var resp = response['response'];
                //$('#'+name_rpm).parent().parent().children(':first-child').children(':first-child').next().text(status_action);
    ////////////////////////////////////////////////////
                if(resp == "OK"){
                    // aqui se muestran los botones de install y los errores que pudieron haber
                    changeStatusButtonInstall(response)
                }else if(resp == "error"){
                    //alert("uno o algunos paquetes no se pueden instalar");
                    changeStatusButtonInstall(response)
                }
                else
                    getStatusCache();
        });
    }

    function changeStatusButtonInstall(response){
		var resp = response['response'];
        var order = 'menu='+module_name+'&action=get_lang&rawmode=yes';
        if(resp == "OK"){
            $.post("index.php", order,
                function(theResponse){
                    message = JSONtoString(theResponse);
                    $("div[id^='img_']").each(function(){
                        var id = $(this).attr('id');
                        $(this).children(':first-child').attr("style","display: none;");
                        $(this).children(':last-child').attr("style","display: block;");
                        //var id_button = id.replace("img_","");
                        //var name_button = message['Install'];
                        //var button = "<input type='button' id='"+id_button+"' class='install' value='"+name_button+"' name='"+id_button+"' />";
                        //$(this).html(button);
                    });
                    $("div[id^='status_']").each(function(){
                        $(this).attr("class","text_install");
                        var ready = message['Ok'];
                        $(this).text(ready);
                    });
            });
            there_install = false;
        }else{
/*
            $("div[id^='img_']").each(function(){
                var id = $(this).attr('id');
                alert(id+" error");
            });
*/
            var arr_data = response['data_cache'];
            var link_img = "modules/"+module_name+"/images/warning.png";
            //elastix-developer id
            //status_elastix-developer id
            for(var i=0; i<arr_data.length; i++){
                var rpm_name = arr_data[i]["name_rpm"];
                var status = arr_data[i]["status"];
                var observation = arr_data[i]["observation"];
                var id_status = "status_"+rpm_name;
                if(document.getElementById(rpm_name)){
                    if(status == "1"){ // se muestra el boton de instalar
                        $("#"+rpm_name).attr("style","display: block;");
                        // se oculta el loading
                        $("#"+rpm_name).parent().children(':first-child').attr('style','display: none;');
                        // cambiando la clase
                        $("#status_"+rpm_name).attr("class","text_install");
                    }else{ // se debe mostrar el error como descripcion
                        // cambiando el loading por img error
                        $("#"+rpm_name).parent().children(':first-child').attr('src',link_img);
                        $("#status_"+rpm_name).attr("class","text_alert");
                    }
                    // cambiando la observacion
                    $("#status_"+rpm_name).text(observation);
                }
            }
            
            there_install = false;
        }
    }


