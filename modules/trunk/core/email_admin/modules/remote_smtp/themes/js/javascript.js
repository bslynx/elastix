/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function displayConfig(){
    var xhr = objAjax();
    var selec1 = document.getElementById("status1");
    var selec2 = document.getElementById("status2");
    if(selec1.checked==true){
        alert("Checked");
    }else
        alert("Unchecked");
}

function controllerDisplayConfig(xhr)
{
    if(xhr.readyState==4)
    {
        if(xhr.status==200)
        {
            var val = xhr.responseText;
            var parser=new DOMParser();
            xmlDoc=parser.parseFromString(val,"text/xml");
            document.getElementById("detail").style.display = "block";

            //var db = xhr.responseText;
            //document.getElementById("configuration").innerHTML = db;
        }
    }
}

function objAjax()
{
    var xmlhttp=false;
    try 
    {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch(e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch(E) {
            xmlhttp = false;
	    }
    }
    if (!xmlhttp && typeof XMLHttpRequest!='undefined')
    {
        xmlhttp = new XMLHttpRequest();
    }
    return xmlhttp;
} 