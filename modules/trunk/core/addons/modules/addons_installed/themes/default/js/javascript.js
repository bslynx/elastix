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
            else {
                getPercent();
            }
    });
}

/******************************************************************************************** 
VALUES OF response (this the response of server in format JSON)
        valueActual        : Object. It content all information about actual downloading package by each mini progressBar. The values included are:
            "action"       : It the action actual, It can be "install" or "none",
            "name"         : The name of package to install,
            "lon_total"    : Size of package in bytes,
            "lon_downl"    : Size of package downloaded in bytes,
            "status_pa"    : The status of request, It can be "downloading" or "not_install" or "waiting",
            "porcent_ins"  : Percent value of this package but no all.
        valueTotal         : Percent total of installation,
        status             : Status of intallation about addons. If all if fine this can be  "progress",
        action             : The action do in that instant, it can be "downloading" or "insatalling",
        process_installed  : The current process can be "process_installed"

*********************************************************************************************/
function process(response)
{
    var valueActual = response['valueActual'];
    var valueTotal  = response['valueTotal'];
    // if no preocess to install
	if (response['status'] == "not_install")
		return;
    // if the process to install is finished
    if(response['action'] != "none") {
        var ctl_percent = document.getElementById('percentTotal');
        if (ctl_percent != null) {
        	ctl_percent.firstChild.nodeValue=valueTotal;
        } else {
            var url_redirect = "index.php?menu="+module_name;
            window.open(url_redirect,"_self");
        	return;
        }
    }
    // if exists a process install in progress
    if(valueActual != "none"){
        // obtain each package by Actual progressbar 
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

            lon_total_lb.firstChild.nodeValue  = " "+lon_downl+" bytes";
            lon_downl_lb.firstChild.nodeValue  = lon_total+" bytes";
            status_pa_lb.firstChild.nodeValue  = status_pa;
            percent_pa_lb.firstChild.nodeValue = percentActual;

            // fill the progressBar actual(no main progressBar)
            $('#progressBarActual'+i).progressbar('value', parseInt(percentActual));
        }
        // fill the main progressBar by the correcta value
        $('#progressBarTotal').progressbar('value', parseInt(valueTotal));
    }
}

function updateAddon(name_rpm)
{
    var order = 'menu='+module_name+'&name_rpm='+name_rpm+'&action=update&rawmode=yes';
    $.post('index.php',order,function(theResponse){
            var message = JSONtoString(theResponse);
            if(message['response'] == "OK")
                //getStatusInstall();
                confirmOperation();
            else if(message['response'] == "error"){
                connectJSON("error_start_install");
            }
    });
}

function removeAddon(name_rpm)
{
    var order = 'menu='+module_name+'&name_rpm='+name_rpm+'&action=remove&rawmode=yes';
    $.post('index.php',order,function(theResponse){
            var message = JSONtoString(theResponse);
            if(message['response'] == "OK")
                //getStatusInstall();
                confirmOperation();
            else if(message['response'] == "error"){
                connectJSON("error_start_install");
            }
    });
}

function confirmOperation(){
    var order = 'menu='+module_name2+'&action=confirm&rawmode=yes';

    $.post("index.php", order,
        function(theResponse){
            response = JSONtoString(theResponse);
            var resp = response['response'];

            if(resp == "OK")
                //window.open("index.php?menu="+module_name,"_self");
                getStatusInstall();
            else
                confirmOperation();
    });
}

function getStatusInstall(){
    var order = 'menu='+module_name2+'&action=get_status&rawmode=yes';

    $.post("index.php", order,
        function(theResponse){
            var response = JSONtoString(theResponse);
            var resp = response['response'];

            if(resp == "OK")
                window.open("index.php?menu="+module_name,"_self");
            else if (resp == "not_install") {
            	// Nada que hacer
			} else {
            	process(response);
                getStatusInstall();
            }
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

