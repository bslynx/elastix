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

    $('#editArea11').click(function() {
        $("#layer10").show(); 
    });
    $('#close11').click(function() {
        $("#layer10").hide();
    });

    $('#editArea12').click(function() {
        $("#layer10").show(); 
    });
    $('#close12').click(function() {
        $("#layer10").hide();
    });

    $('#editArea13').click(function() {
        $("#layer10").show(); 
    });
    $('#close13').click(function() {
        $("#layer10").hide();
    });

    $('#editArea14').click(function() {
        $("#layer10").show(); 
    });
    $('#close14').click(function() {
        $("#layer10").hide();
    });

    $('#editArea15').click(function() {
        $("#layer10").show(); 
    });
    $('#close15').click(function() {
        $("#layer10").hide();
    });
    /*Manufacturer*/
    
    $('#editMan1').click(function() {
        $("#layerCM1").show(); 
    });
    $('#closeCM1').click(function() {
        $("#layerCM1").hide();
    });

    $('#editMan2').click(function() {
        $("#layerCM2").show(); 
    });
    $('#closeCM2').click(function() {
        $("#layerCM2").hide();
    });

    $('#editMan3').click(function() {
        $("#layerCM3").show(); 
    });
    $('#closeCM3').click(function() {
        $("#layerCM3").hide();
    });

    $('#editMan4').click(function() {
        $("#layerCM4").show(); 
    });
    $('#closeCM4').click(function() {
        $("#layerCM4").hide();
    });

    $('#editMan5').click(function() {
        $("#layerCM5").show(); 
    });
    $('#closeCM5').click(function() {
        $("#layerCM5").hide();
    });

    $('#editMan6').click(function() {
        $("#layerCM6").show(); 
    });
    $('#closeCM6').click(function() {
        $("#layerCM6").hide();
    });

    $('#editMan7').click(function() {
        $("#layerCM7").show(); 
    });
    $('#closeCM7').click(function() {
        $("#layerCM7").hide();
    });

    $('#editMan8').click(function() {
        $("#layerCM8").show(); 
    });
    $('#closeCM8').click(function() {
        $("#layerCM8").hide();
    });

    $('#editMan9').click(function() {
        $("#layerCM9").show(); 
    });
    $('#closeCM9').click(function() {
        $("#layerCM9").hide();
    });

    $('#editMan10').click(function() {
        $("#layerCM10").show(); 
    });
    $('#closeCM10').click(function() {
        $("#layerCM10").hide();
    });

    $('#editMan11').click(function() {
        $("#layerCM10").show(); 
    });
    $('#closeCM11').click(function() {
        $("#layerCM10").hide();
    });
    
    $('#editMan12').click(function() {
        $("#layerCM10").show(); 
    });
    $('#closeCM12').click(function() {
        $("#layerCM10").hide();
    });

    $('#editMan13').click(function() {
        $("#layerCM10").show(); 
    });
    $('#closeCM13').click(function() {
        $("#layerCM10").hide();
    });

    $('#editMan14').click(function() {
        $("#layerCM10").show(); 
    });
    $('#closeCM14').click(function() {
        $("#layerCM10").hide();
    });

    $('#editMan15').click(function() {
        $("#layerCM10").show(); 
    });
    $('#closeCM15').click(function() {
        $("#layerCM10").hide();
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


function saveCardSpecification(idCard){
    var xhr = objAjax();
    var arrSpanConf = new Array();
    
    var manufacturer = document.getElementById("manufacturer_"+idCard);
    var manufacturer_selected = manufacturer.options[manufacturer.selectedIndex].text;

    var num_serie = document.getElementById("noSerie_"+idCard).value;
    if(manufacturer_selected=="Otros"){
        var manufacturer_other = document.getElementById("manufacturer_other_"+idCard).value;
        xhr.open("GET","modules/hardware_detector/libs/controller.php?action=setDataCard&idCard="+idCard+"&manufacturer="+manufacturer_other+"&num_serie="+num_serie,true);
    }else    
        xhr.open("GET","modules/hardware_detector/libs/controller.php?action=setDataCard&idCard="+idCard+"&manufacturer="+manufacturer_selected+"&num_serie="+num_serie,true);
    
    xhr.onreadystatechange = function()
    {
        controllerCardManufacturer(xhr);
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

function controllerCardManufacturer(xhr)
{
    if(xhr.readyState==4)
    {
        if(xhr.status==200)
        {
            var idCard = xhr.responseText;
            alert("Card Manufacturer saved succesful");
            var span = document.getElementById("editMan"+idCard);
            span.removeChild(span.getElementsByTagName("img")[0]);
            
            imgtag = document.createElement("input");
            imgtag.setAttribute("class", "icon");
            imgtag.setAttribute("src", "/var/www/html/modules/hardware_detector/images/call2.png");
            span.appendChild(imgtag);
        }
    }
}

function addTextBox(idCard){
    var xhr = objAjax();
    var arrSpanConf = new Array();
    var manufacturer = document.getElementById("manufacturer_"+idCard);
    var manufacturer_selected = manufacturer.options[manufacturer.selectedIndex].text;
    
    if(manufacturer_selected=="Otros"){
        var select_td = document.getElementById("select_"+idCard);
        inputtag = document.createElement("input");
        inputtag.setAttribute("type", "text");
        inputtag.setAttribute("name", "manufacturer_other_"+idCard);
        inputtag.setAttribute("id", "manufacturer_other_"+idCard);
        inputtag.setAttribute("size", "12");
        select_td.appendChild(inputtag);
    }else{
        var select_td = document.getElementById("select_"+idCard);
        var kids = select_td.childNodes;
        select_td.removeChild(kids[1]);
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