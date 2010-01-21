/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function showAllParameters(){
    document.getElementById("all").style.display = "block";
    //document.getElementById("host_").innerHTML = "<td align='left' width='50%'><b>Host: </b></td> <td align='left' width='50%'><input type='text' size='20' name='host'></td>";
}

function hideAllParameters(){
    document.getElementById("all").style.display = "none";
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