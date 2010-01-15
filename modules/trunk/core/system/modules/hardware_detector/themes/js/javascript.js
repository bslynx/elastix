/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    $(".move").draggable({
        zIndex:     20,
        ghosting:   false,
        opacity:    0.7,
        //handle: '#layer1_handle1'
    });

    $('#editArea1').click(function() {
        $("#layer1").show(); 
    });
    $('#close1').click(function() {
        $("#layer1").hide();
    });

    $('#editArea2').click(function() {
        $("#layer2").show(); 
    });
    $('#close2').click(function() {
        $("#layer2").hide();
    });

    $('#editArea3').click(function() {
        $("#layer3").show(); 
    });
    $('#close3').click(function() {
        $("#layer3").hide();
    });

    $('#editArea4').click(function() {
        $("#layer4").show(); 
    });
    $('#close4').click(function() {
        $("#layer4").hide();
    });

    $('#editArea5').click(function() {
        $("#layer5").show(); 
    });
    $('#close5').click(function() {
        $("#layer5").hide();
    });

    $('#editArea6').click(function() {
        $("#layer6").show(); 
    });
    $('#close6').click(function() {
        $("#layer6").hide();
    });

    $('#editArea7').click(function() {
        $("#layer7").show(); 
    });
    $('#close7').click(function() {
        $("#layer7").hide();
    });

    $('#editArea8').click(function() {
        $("#layer8").show(); 
    });
    $('#close8').click(function() {
        $("#layer8").hide();
    });

    $('#editArea9').click(function() {
        $("#layer9").show(); 
    });
    $('#close9').click(function() {
        $("#layer9").hide();
    });

    $('#editArea10').click(function() {
        $("#layer10").show(); 
    });
    $('#close10').click(function() {
        $("#layer10").hide();
    });
});

function saveSpanConfiguration(idSpan){
    var xhr = objAjax();
    var arrSpanConf = new Array();
    
    var tmsource = document.getElementById("tmsource_"+idSpan);
    var tmsource_escogida = tmsource.options[tmsource.selectedIndex].text;
    //arrSpanConf[0] = tmsource.options[tmsource.selectedIndex].text;

    var lnbuildout = document.getElementById("lnbuildout_"+idSpan);
    var lnbuildout_escogida = lnbuildout.options[lnbuildout.selectedIndex].text;
    //arrSpanConf[1] = lnbuildout.options[lnbuildout.selectedIndex].text;

    var framing = document.getElementById("framing_"+idSpan);
    var framing_escogida = framing.options[framing.selectedIndex].text;
    //arrSpanConf[2] = framing.options[framing.selectedIndex].text;

    var coding = document.getElementById("coding_"+idSpan);
    var coding_escogida = coding.options[coding.selectedIndex].text;
    //arrSpanConf[3] = coding.options[coding.selectedIndex].text;
    
    xhr.open("GET","modules/hardware_detector/libs/controller.php?action=setConfig&idSpan="+idSpan+"&tmsource="+tmsource_escogida+"&lnbuildout="+lnbuildout_escogida+"&framing="+framing_escogida+"&coding="+coding_escogida,true);
    xhr.onreadystatechange = function()
    {
        controllerDisplayConfig(xhr);
    }
    xhr.send(null); 

    return;
}

function controllerDisplayConfig(xhr)
{
    if(xhr.readyState==4)
    {
        if(xhr.status==200)
        {
            alert("Span configuration saved succesful");
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