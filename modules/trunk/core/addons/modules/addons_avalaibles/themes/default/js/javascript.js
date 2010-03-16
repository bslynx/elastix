var module_name = "addons_avalaibles";
var module_name2 = "addons_installed";

var there_install = false;

$(document).ready(function(){
    $('.install').click(function(){
        if(!there_install){
            var name_rpm = $(this).attr("id");
            $('#'+name_rpm).parent().parent().children(':first-child').children(':first-child').attr("style","visibility:visible;");
            $('#'+name_rpm).parent().parent().children(':first-child').children(':first-child').next().attr("style","visibility:visible;");
            $('#'+name_rpm).parent().parent().children(':last-child').attr("style","visibility:hidden;");

            var data_exp = $('.'+name_rpm).text();
            var order = 'menu='+module_name+'&name_rpm='+name_rpm+'&action=install&data_exp='+data_exp+'&rawmode=yes';
            $.post('index.php',order,function(theResponse){
                    var message = JSONtoString(theResponse);
                    if(message['response'] == "there_install"){
                        there_install = true;
                        connectJSON("process_installing");
                        window.open("index.php?menu="+module_name2,"_self");
                    }
                    else if(message['response'] == "OK"){
                        there_install = true;
                        getStatusInstall();
                    }
                    else if(message['response'] == "error"){
                        there_install = false;
                        connectJSON("error_start_install");
                    }
            });
        }
        else
           connectJSON("process_installing");
    });
});

function getStatusInstall(){
    var order = 'menu='+module_name+'&action=get_status&rawmode=yes';

    $.post("index.php", order,
        function(theResponse){
            response = JSONtoString(theResponse);
            var resp = response['response'];
            $('#action_install').val(resp);
////////////////////////////////////////////////////
            var name_rpm = response['name_rpm'];
            $('#'+name_rpm).parent().parent().children(':first-child').children(':first-child').next().text(resp);
////////////////////////////////////////////////////
            if(resp == "OK"){
                changeStatus(response['name_rpm'],response['view_details']);
            }
            else
                getStatusInstall();
    });
}

function changeStatus(name_rpm, view_details ){
    // hide loading.gif
    $('.loading').attr("style","visibility:hidden;");
    $('.text_alert').attr("style","visibility:hidden;");
    $('.text_alert').val('none');
    // show td to description
    $('#'+name_rpm).parent().parent().children(':last-child').attr("style","visibility:visible;");
    // change url install for view details
    url_redirect = "index.php?menu="+module_name2;
    $('#'+name_rpm).parent().parent().children(':last-child').children(':last-child').attr("href",url_redirect);
    $('#'+name_rpm).parent().parent().children(':last-child').children(':last-child').text(view_details);

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

