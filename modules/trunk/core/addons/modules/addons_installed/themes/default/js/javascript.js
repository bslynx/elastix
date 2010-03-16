var module_name = "addons_installed";
var module_name2 = "addons_avalaibles";
var stat = false;

$(document).ready(function(){
    $("[id^=progressBar]").progressbar({value: 0});
    getPercent();
    $('#details').hide();
    $('#link_detail').click(function(){
        var image = $('#imgShow').attr("src");
        if(image=="modules/"+module_name+"/images/flecha_up.gif"){
            $('#imgShow').attr("src","modules/"+module_name+"/images/flecha_down.gif");
            $('#details').hide('slow');
        }else{
            $('#imgShow').attr("src","modules/"+module_name+"/images/flecha_up.gif");
            $('#details').show('slow');
        }
    });
});

function getPercent()
{
    var order = 'menu='+module_name+'&action=progressbar&rawmode=yes';
    $.post("index.php", order,
        function(theResponse){
            var response = JSONtoString(theResponse);
            process(response);

            if(response['status']=="finished"){
                var url_redirect = "index.php?menu="+module_name;
                window.open(url_redirect,"_self");
            }
            else if(response['status']=="not_install"){
                // nada que hacer
            }
            else
                getPercent();
    });
}

function process(response)
{
    var valueActual = response['valueActual'];
    var valueTotal  = response['valueTotal'];

    if(response['action'] != "none")
        document.getElementById('percentTotal').firstChild.nodeValue=valueTotal;

    if(valueActual != "none"){
        for(var i=0; i<valueActual.length; i++){
            var percentActual = valueActual[i]['porcent_ins'];
            var lon_total = valueActual[i]['lon_total'];
            var lon_downl = valueActual[i]['lon_downl'];
            var status_pa = valueActual[i]['status_pa'];
            // setting textnodes
            var lon_total_lb  = document.getElementById('lon_downl'+i);
            var lon_downl_lb  = document.getElementById('lon_total'+i);
            var status_pa_lb  = document.getElementById('status_pa'+i);
            var percent_pa_lb = document.getElementById('percentTotal'+i);

            lon_total_lb.firstChild.nodeValue  = lon_downl;
            lon_downl_lb.firstChild.nodeValue  = lon_total;
            status_pa_lb.firstChild.nodeValue  = status_pa;
            percent_pa_lb.firstChild.nodeValue = percentActual;

            $('#progressBarActual'+i).progressbar('value', percentActual);
        }
        $('#progressBarTotal').progressbar('value', valueTotal);
    }
}

function updateAddon(name_rpm)
{
    var order = 'menu='+module_name+'&name_rpm='+name_rpm+'&action=update&rawmode=yes';
    $.post('index.php',order,function(theResponse){
            var message = JSONtoString(theResponse);
            if(message['response'] == "OK")
                getStatusInstall();
            else if(message['response'] == "error"){
                connectJSON("error_start_install");
            }
    });
}

function getStatusInstall(){
    var order = 'menu='+module_name2+'&action=get_status&rawmode=yes';

    $.post("index.php", order,
        function(theResponse){
            response = JSONtoString(theResponse);
            var resp = response['response'];

            if(resp == "OK")
                window.open("index.php?menu="+module_name,"_self");
            else
                getStatusInstall();
    });
}

// implement JSON.parse de-serialization  
function JSONtoString(str) {
    if (str === "") str = '""';
    eval("var p=" + str + ";");
    return p;
}

// uso de JSON para obtener el arreglo lang.php
function connectJSON(mensaje_error) {
    var order = 'menu='+module_name2+'&action=get_lang&rawmode=yes';
    var message = "";
    $.post("index.php", order,
            function(theResponse){
                message = JSONtoString(theResponse);
                alert(message[mensaje_error]);
        });
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

