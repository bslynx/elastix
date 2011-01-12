$(document).ready(function(){
    $(".move").draggable({
        zIndex:     20,
        ghosting:   false,
        opacity:    0.7,
        //handle: '#layer1_handle1'
    });

    $('#editArea2').click(function() {
        var fieldDescrip1 = document.getElementById("headArea1");
        html = "<div class='div_content_bubble'><table align='center'>" +
                "<tr>" +
                    "<td colspan='2' style='font-size: 11px'>" +
                        "<font style='color:red'>Display Settings</font>" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td><label style='font-size: 11px; color: gray;'>Name:</label></td>" +
                    "<td><input type='text' value='' name='descrip1' id='descrip1' /></td>" +
                "</tr> <tr>" +
                    "<td align='center' colspan='2'>" +
                        "<input type='button' value='Save' class='boton'onclick='saveDescriptionArea1()'/>" +
                    "</td>" +
                "</tr>" +
            "</table></div>";

        document.getElementById("layerCM_content").innerHTML = html;
        var dataDescrip1 = fieldDescrip1.firstChild.nodeValue.split(" -- ")[0];
        document.getElementById("descrip1").value = dataDescrip1;
        $("#layerCM").show(); 
    });

    $('#editArea3').click(function() {
        var fieldDescrip2 = document.getElementById("headArea2");
        var dataDescrip2 = fieldDescrip2.firstChild.nodeValue.split(" -- ")[0];
        html = "<div class='div_content_bubble'><table align='center'>" +
                "<tr>" +
                    "<td colspan='2' style='font-size: 11px'>" +
                        "<font style='color:red'>Display Settings</font>" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td><label style='font-size: 11px; color: gray;'>Name:</label></td>" +
                    "<td><input type='text' value='' name='descrip2' id='descrip2' /></td>" +
                "</tr> <tr>" +
                    "<td align='center' colspan='2'>" +
                        "<input type='button' value='Save' class='boton'onclick='saveDescriptionArea2()'/>" +
                    "</td>" +
                "</tr>" +
            "</table></div>";

        document.getElementById("layerCM_content").innerHTML = html;
        document.getElementById("descrip2").value = dataDescrip2;
        $("#layerCM").show(); 
    });

    $('#editArea4').click(function() {
        var fieldDescrip3 = document.getElementById("headArea3");
        var dataDescrip3 = fieldDescrip3.firstChild.nodeValue.split(" -- ")[0];
        html = "<div class='div_content_bubble'><table align='center'>" +
                "<tr>" +
                    "<td colspan='2' style='font-size: 11px'>" +
                        "<font style='color:red'>Display Settings</font>" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td><label style='font-size: 11px; color: gray;'>Name:</label></td>" +
                    "<td><input type='text' value='' name='descrip3' id='descrip3' /></td>" +
                "</tr> <tr>" +
                    "<td align='center' colspan='2'>" +
                        "<input type='button' value='Save' class='boton'onclick='saveDescriptionArea3()'/>" +
                    "</td>" +
                "</tr>" +
            "</table></div>";

        document.getElementById("layerCM_content").innerHTML = html;
        document.getElementById("descrip3").value = dataDescrip3;
        $("#layerCM").show(); 
    });
    
    $(".phone_box").draggable({
        zIndex:     990,
        revert: true,
        cursor: 'crosshair',
        start: function(event, ui) {
            //$(this).css('background-color','#ddddff');
        },
        out: function(event, ui) {
            //$(this).css('background-color', null);
        },
        drag: function(event, ui) {
        }
    });

    $(".phone_box").droppable({
        over: function(event, ui) {
            //$(this).css('background-color', '#A3C1F9');//cambia color
        },
        out: function(event, ui) {
            //$(this).css('background-color', null);
        },
        drop: function(event, ui) {
            var idStart = ($(ui.draggable).attr("id")).split("_");
            var idFinish = ($(this).attr("id")).split("_");
            var arrAction              = new Array();
                arrAction["action"]    = "call";
                arrAction["rawmode"]   = "yes";
                arrAction["extStart"]  =  idStart[1];
                arrAction["extFinish"] =  idFinish[1];
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                        $("#contentRight").html(arrData);
                    }
            );
        }
    });

    $(".conference_box").droppable({
        over: function(event, ui) {
            //$(this).css('background-color', '#A3C1F9');//cambia color
        },
        out: function(event, ui) {
            //$(this).css('background-color', null);
        },
        drop: function(event, ui) {
            var idStart = ($(ui.draggable).attr("id")).split("_");
            var idFinish = ($(this).attr("id")).split("_");
            var arrAction              = new Array();
                arrAction["action"]    = "call";
                arrAction["rawmode"]   = "yes";
                arrAction["extStart"]  =  idStart[1];
                arrAction["extFinish"] =  idFinish[1];
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                        $("#contentRight").html(arrData);
                    }
            );
        }
    });

    $(".queu_box").droppable({
        over: function(event, ui) {
            //$(this).css('background-color', '#A3C1F9');//cambia color
        },
        out: function(event, ui) {
            //$(this).css('background-color', null);
        },
        drop: function(event, ui) {
            var idStart = ($(ui.draggable).attr("id")).split("_");
            var idFinish = ($(this).attr("id")).split("_");
            var arrAction              = new Array();
                arrAction["action"]    = "call";
                arrAction["rawmode"]   = "yes";
                arrAction["extStart"]  =  idStart[1];
                arrAction["extFinish"] =  idFinish[1];
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                        $("#contentRight").html(arrData);
                    }
            );
        }
    });

    //Evento Doble clic Accion Hangup//
    $(".phone_box").dblclick(function(ev)
    {
        var extStart = ($(this).attr("id")).split("_");
        var arrAction              = new Array();
            arrAction["action"]    = "hangup";
            arrAction["rawmode"]   = "yes";
            arrAction["extStart"]  =  extStart[1];
            request("index.php",arrAction,false,
               function(arrData,statusResponse,error)
               {
                    $("#contentRight").html(arrData);
               }
            );        
    });

    
    $(".mail_box").droppable({
        over: function(event, ui)
        {
            //$(this).css('background-color', '#F5F6BE');//cambia color
        },
        out: function(event, ui)
        {
            //$(this).css('background-color', null);
        },
        drop: function(event, ui)
        {
            var idStart = ($(ui.draggable).attr("id")).split("_");
            //var idFinish = ($(this).attr("id")).split("_");
            var arrAction              = new Array();
                arrAction["action"]    = "voicemail";
                arrAction["rawmode"]   = "yes";
                arrAction["extStart"]  =  idStart[1];
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            $("#contentRight").html(arrData);
                    }
                );
        }
    });

    $(".item_box").draggable({
        zIndex:     989,
        revert: true,
        cursor: 'crosshair',
        start: function(event, ui) {
            //$(this).css('background-color','#ddddff');
        }
    });

//     $(".item_box").droppable({
//         accept: ".item_box",
//         drop: function(event, ui) {
//             $(this).append($(ui.draggable));
//         }
//     });
    //NUEVA AGREGACION
//     $(".item_box").droppable({
//         accept: ".item_box",
//         drop: function(event, ui) {
//             $(this).append($(ui.draggable));
//         }
//     });

    $(".areaDrop").droppable({
        accept: ".item_box",//#lista_local
        drop: function(event, ui) {
            $(this).append($(ui.draggable));
            var idStart = ($(ui.draggable).attr("id")).split("_");
            var idFinish = ($(this).attr("id")).split("_");
            var arrAction              = new Array();
                arrAction["action"]    = "savechange2";
                arrAction["rawmode"]   = "yes";
                arrAction["extStart"]  =  idStart[1];
                arrAction["extFinish"] =  idFinish[1];
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            $("#contentRight").html(arrData);
                    }
                );
        }
    });


    $(".areaDropSub1").droppable({
        accept: ".item_box",
        drop: function(event, ui) {
            $(this).append($(ui.draggable));
            var idStart = ($(ui.draggable).attr("id")).split("_");
            var arrAction              = new Array();
                arrAction["action"]    = "savechange";
                arrAction["rawmode"]   = "yes";
                arrAction["extStart"]  =  idStart[1];
                arrAction["area"]      = 2;
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            $("#contentRight").html(arrData);
                    }
                );
        }
    });

    $(".areaDropSub2").droppable({
        accept: ".item_box",
        drop: function(event, ui)
        {
            $(this).append($(ui.draggable));
            var idStart = ($(ui.draggable).attr("id")).split("_");
            var arrAction              = new Array();
                arrAction["action"]    = "savechange";
                arrAction["rawmode"]   = "yes";
                arrAction["extStart"]  =  idStart[1];
                arrAction["area"]      = 3;
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            $("#contentRight").html(arrData);
                    }
                );
        }
    });

    $(".areaDropSub3").droppable({
        accept: ".item_box",
        drop: function(event, ui)
        {
            $(this).append($(ui.draggable));
            var idStart = ($(ui.draggable).attr("id")).split("_");
            var arrAction              = new Array();
                arrAction["action"]    = "savechange";
                arrAction["rawmode"]   = "yes";
                arrAction["extStart"]  =  idStart[1];
                arrAction["area"]      = 4;
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            $("#contentRight").html(arrData);
                    }
                );
        }
    });

    $(".phone_boxqueue").droppable({
        //accept: ".phone_box",
        drop: function(event, ui)
        {
            var idStart = ($(ui.draggable).attr("id")).split("_");
            var queue = ($(this).attr("id")).split("_");
            var arrAction              = new Array();
                arrAction["action"]    = "addExttoQueue";
                arrAction["rawmode"]   = "yes";
                arrAction["extStart"]  = idStart[1];
                arrAction["queue"]     = queue[1];
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            $("#contentRight").html(arrData);
                    }
                );
        }
    });
    $('#closeCM').click(function() {
            $("#layerCM").hide();
        });
    $('#layerCM').draggable();
});

/////////////////////////////////////////////////////////////
////////////CODE FOR THE ACCORDION AND RESIZABLE/////////////
/////////////////////////////////////////////////////////////
$(function(){
    /* 
    * Apply the FAQ plug-in to jQuery object <dl>
    * Parameter 1: (optional): the index [integer] of a <dt> to open on load
    */
    $('#faq').faq();//parametro de 0 a 3 o ninguno para todos
    //$('#accordion').accordion();

    $("[id^=sortable-]").sortable({
        connectWith: ".sortable",
        receive: listChanged,
        opacity: 0.6, cursor: 'move'
    });

//     $(".state1").dblclick(switchLists);
//     $(".state2").dblclick(switchLists);

    function listChanged(e,ui) {
        ui.item.toggleClass("state1"); 
        ui.item.toggleClass("state2");
    }

    function switchLists(e) {
        // determine which list they are in
        // this works if you only have 2 related lists.
        // otherwise you will need to specify the target list
        // the other list is one that has the connect with property but isn't
        // the current target's parent
        var otherList = $($(e.currentTarget).parent().sortable("option","connectWith")).not($(e.currentTarget).parent());
    
        // if the current list has no items, add a hidden one to keep style in place
        // when saving you will need to filter out items that have
        // display set to none to accommodate this scenario
        if ($(e.currentTarget).siblings().length == 0) {
            $(e.currentTarget).clone().appendTo($(e.currentTarget).parent()).css("display","none");
        }
        otherList.append(e.currentTarget);
        otherList.children().removeClass($(e.currentTarget).attr("class"));
        otherList.children().addClass(otherList.children().attr("class"));
        
        // remove any hidden siblings perhaps left over
        otherList.children(":hidden").remove();
    }
    
    $("#contentExtension").resizable({
        autoHide: true,
        //maxWidth: 576,
        minWidth: 185,//372//560//576
        minHeight: 160,
        alsoResize: '#content',
        stop: function(event, ui) {
            var heightsize = $("#contentExtension").height();
            var widthsize = $("#contentExtension").width();
            var arrAction              = new Array();
                arrAction["action"]    = "saveresize";
                arrAction["rawmode"]   = "yes";
                arrAction["height"]    = heightsize;
                arrAction["width"]     = widthsize;
                arrAction["area"]      = 1;
                arrAction["type"]      = "alsoResize";
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            reFresh();
                    }
                );
        }
    });

    $("#contentTrunks").resizable({
        autoHide: true,
        minHeight: 100,
        //maxWidth: 576, //394
        minWidth: 380,
        alsoResize: '#content',
        stop: function(event, ui) {
            var heightsize = $("#contentTrunks").height();
            var widthsize = $("#contentTrunks").width();
            var arrAction              = new Array();
                arrAction["action"]    = "saveresize";
                arrAction["rawmode"]   = "yes";
                arrAction["height"]    = heightsize;
                arrAction["width"]     = widthsize;
                arrAction["area"]      = 6;
                arrAction["type"]      = "alsoResize";
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                           
                            reFresh();
                    }
                );
        }
    });

    $("#contentTrunksSIP").resizable({
        autoHide: true,
        minHeight: 100,
        //maxWidth: 576, //394
        minWidth: 380,
        alsoResize: '#content',
        stop: function(event, ui) {
            var heightsize = $("#contentTrunksSIP").height();
            var widthsize = $("#contentTrunksSIP").width();
            var arrAction              = new Array();
                arrAction["action"]    = "saveresize";
                arrAction["rawmode"]   = "yes";
                arrAction["height"]    = heightsize;
                arrAction["width"]     = widthsize;
                arrAction["area"]      = 7;
                arrAction["type"]      = "alsoResize";
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            reFresh();
                    }
                );
        }
    });

    $("#contentArea1").resizable({
        autoHide: true,
        minHeight: 100,
        //maxWidth: 394,
        minWidth: 380,//394
        alsoResize: '.areaDropSub',
        stop: function(event, ui) {
            var heightsize = $("#contentArea1").height();
            var widthsize = $("#contentArea1").width();
            var arrAction              = new Array();
                arrAction["action"]    = "saveresize";
                arrAction["rawmode"]   = "yes";
                arrAction["height"]    = heightsize;
                arrAction["width"]     = widthsize;
                arrAction["area"]      = 2;
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            reFresh();
                    }
                );
        }
    });

    $("#contentArea2").resizable({ 
        autoHide: true,
        minHeight: 100,
        //maxWidth: 394,
        minWidth: 380,//394
        alsoResize: '.areaDropSub',
        stop: function(event, ui) {
            var heightsize = $("#contentArea2").height();
            var widthsize = $("#contentArea2").width();
            var arrAction              = new Array();
                arrAction["action"]    = "saveresize";
                arrAction["rawmode"]   = "yes";
                arrAction["height"]    = heightsize;
                arrAction["width"]     = widthsize;
                arrAction["area"]      = 3;
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            reFresh();
                    }
                );
        }
    });

    $("#contentArea3").resizable({ 
        autoHide: true,
        minHeight: 100,
        //maxWidth: 394,
        minWidth: 380,//394
        alsoResize: '.areaDropSub',
        stop: function(event, ui) {
            var heightsize = $("#contentArea3").height();
            var widthsize = $("#contentArea3").width();
            var arrAction              = new Array();
                arrAction["action"]    = "saveresize";
                arrAction["rawmode"]   = "yes";
                arrAction["height"]    = heightsize;
                arrAction["width"]     = widthsize;
                arrAction["area"]      = 4;
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            reFresh();
                    }
                );
        }
    });

    $("#contentQueues").resizable({
        autoHide: true,
        minHeight: 100, /*120*/
        //maxWidth: 394,
        minWidth: 380,//394
        stop: function(event, ui) {
            var heightsize = $("#contentQueues").height();
            var widthsize = $("#contentQueues").width();
            var arrAction              = new Array();
                arrAction["action"]    = "saveresize";
                arrAction["rawmode"]   = "yes";
                arrAction["height"]    = heightsize;
                arrAction["width"]     = widthsize;
                arrAction["area"]      = 5;
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            reFresh();
                    }
                );
        }
    });

    $("#contentConferences").resizable({
        autoHide: true,
        minHeight: 100, /*120*/
        //maxWidth: 394,
        minWidth: 380,//394
        stop: function(event, ui) {
            var heightsize = $("#contentConferences").height();
            var widthsize = $("#contentConferences").width();
            var arrAction              = new Array();
                arrAction["action"]    = "saveresize";
                arrAction["rawmode"]   = "yes";
                arrAction["height"]    = heightsize;
                arrAction["width"]     = widthsize;
                arrAction["area"]      = 8;
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error)
                    {
                            reFresh();
                    }
                );
        }
    });

});

function actualizar()
{
    var arrAction              = new Array();
        arrAction["action"]    = "refresh";
        arrAction["rawmode"]   = "yes";
        request("index.php",arrAction,true,
            function(arrData,statusResponse,error)
            {
                    if(statusResponse == "CHANGED"){
                        for(key in arrData){
                            for(key2 in arrData[key]["data"]){
                                if(key2 == "speak_time"){
                                    if(arrData[key]["Tipo"] == "Trunk")
                                        reloadSpeakTime(arrData[key]["key"],arrData[key]["data"][key2],1);
                                    else
                                        reloadSpeakTime(arrData[key]["key"],arrData[key]["data"][key2],0);
                                }
                                else if(key2 == "call_dstn")
                                    reloadCallDstn(arrData[key]["key"],arrData[key]["data"][key2]);
                                else if(key2 == "status")
                                     reloadStatus(arrData[key]["key"],arrData[key]["data"][key2]);
                                else if(key2 == "voicemail")
                                     reloadVoicemail(arrData[key]["key"],arrData[key]["data"][key2]);
                                else if(key2 == "state_call")
                                     reloadStateCall(arrData[key]["key"],arrData[key]["data"][key2]);
                                else if(key2 == "parties")
                                     reloadParties(arrData[key]["key"],arrData[key]["data"][key2]);
                                else if(key2 == "activity")
                                     reloadActivity(arrData[key]["key"],arrData[key]["data"][key2]);
                                else if(key2 == "statusConf")
                                     reloadStatusConf(arrData[key]["key"],arrData[key]["data"][key2]);
                                else if(key2 == "statusTrunk")
                                     reloadStatusTrunk(arrData[key]["key"],arrData[key]["data"][key2]);
                                else if(key2 == "waiting")
                                     reloadWaiting(arrData[key]["key"],arrData[key]["data"][key2]);
                            }
                        }
                        //reloadDevices(arrData);
                    }
            }
        );
}

function reloadWaiting(queue,waiting){
     var divQueue = document.getElementById("queue_" + queue);
     var spanQueue = divQueue.getElementsByTagName("span");
    spanQueue[0].firstChild.nodeValue = waiting;
}

function reloadStatusTrunk(trunk, statusTrunk){
    if(statusTrunk== "off"){
        var divTrun = document.getElementById("tru_" + trunk);
        var subdivTrun = divTrun.getElementsByTagName("div");
        var spanTrun = subdivTrun[1].getElementsByTagName("span");
        spanTrun[0].firstChild.nodeValue = "";
        spanTrun[1].firstChild.nodeValue = "";
    }   
}

function reloadStatusConf(conference, statusConf){
    if(statusConf == "off"){
        var divConf = document.getElementById("conference_" + conference);
        var spanConf = divConf.getElementsByTagName("span");
        spanConf[0].firstChild.nodeValue = "";
        spanConf[1].firstChild.nodeValue = "";
    }   
}

function reloadActivity(conference, activity){
    var divConf = document.getElementById("conference_" + conference);
    var spanConf = divConf.getElementsByTagName("span");
    spanConf[1].firstChild.nodeValue = activity;
}

function reloadParties(conference, parties){
     var divConf = document.getElementById("conference_" + conference);
     var spanConf = divConf.getElementsByTagName("span");
     spanConf[0].firstChild.nodeValue = parties;
}

function reloadStateCall(exten, state_call){
    var div = document.getElementById("ext_" + exten);

    var subdiv = div.getElementsByTagName("div");
    var img = subdiv[3].getElementsByTagName("img");

    if(state_call=="Ringing"){
            img[0].setAttribute("src","modules/control_panel/images/phoneRinging.gif");
    }if(state_call=="Up"){
            img[0].setAttribute("src","modules/control_panel/images/icon_upPhone.png");
    }if(state_call=="Down"){
            var span = subdiv[1].getElementsByTagName("span");
            span[0].firstChild.nodeValue = "";
            span[1].firstChild.nodeValue = "";
            img[0].setAttribute("src","modules/control_panel/images/phhonez0.png");
    }
}

function reloadVoicemail(exten, voicemail){
    var div = document.getElementById("ext_" + exten);

    var subdiv = div.getElementsByTagName("div");
    if(voicemail=="1"){
            var a = subdiv[2].getElementsByTagName("a");
            var img = a[0].getElementsByTagName("img");
            img[0].setAttribute("src","modules/control_panel/images/mail.png");
        }else{
            subdiv[2].innerHTML = "";
        }
}

function reloadStatus(exten, status_){
     var div = document.getElementById("ext_" + exten);
     if(status_ =='on'){
            div.setAttribute("class","item_box");
        }else{
            div.setAttribute("class","item_box item_boxOff");
        }
}

function reloadCallDstn(exten,call_dstn){
    var div = document.getElementById("ext_" + exten);

    var subdiv = div.getElementsByTagName("div");
    var span = subdiv[1].getElementsByTagName("span");
    span[0].firstChild.nodeValue = call_dstn;
}

function reloadSpeakTime(exten,speak_time,bandera){
     if(bandera == 0){
        var div = document.getElementById("ext_" + exten);
        var subdiv = div.getElementsByTagName("div");
        var span = subdiv[1].getElementsByTagName("span");
        span[1].firstChild.nodeValue = speak_time;
     }else{
        var tmp = exten.split("_");
        var div = document.getElementById("ext_" + tmp[0]);
        var subdiv = div.getElementsByTagName("div");
        var span = subdiv[1].getElementsByTagName("span");
        span[1].firstChild.nodeValue = speak_time;
        var divTrun = document.getElementById("tru_" + tmp[1]);
        var subdivTrun = divTrun.getElementsByTagName("div");
        var spanTrun = subdivTrun[1].getElementsByTagName("span");
        spanTrun[0].innerHTML = tmp[0];
        spanTrun[1].innerHTML = speak_time;
    }
}

function reloadDevices(arrRefresh){
     for(key in arrRefresh){
        var user = arrRefresh[key]["user"];
        var speak_time = arrRefresh[key]["speak_time"];
        var call_dstn = arrRefresh[key]["call_dstn"];
        var status_ = arrRefresh[key]["status"];
        var voicemail = arrRefresh[key]["voicemail"];
        var state_call = arrRefresh[key]["state_call"];
        var voicemail_cnt = arrRefresh[key]["voicemail_cnt"];
        var context = arrRefresh[key]["context"];
        var trunk = arrRefresh[key]["trunk"];
        var numconf = arrRefresh[key]["numconf"];
        var parties = arrRefresh[key]["parties"];
        var activity = arrRefresh[key]["activity"];
     
        var div = document.getElementById("ext_" + user);

        var subdiv = div.getElementsByTagName("div");
        var span = subdiv[1].getElementsByTagName("span");

        if(status_ =='on'){
            div.setAttribute("class","item_box");
        }else{
            div.setAttribute("class","item_box item_boxOff");
        }

        if(voicemail=="1"){
            var a = subdiv[2].getElementsByTagName("a");
            var img = a[0].getElementsByTagName("img");
            img[0].setAttribute("src","modules/control_panel/images/mail.png");
        }else{
            subdiv[2].innerHTML = "";
        }

        var img = subdiv[3].getElementsByTagName("img");

        if(state_call=="Ringing"){
            img[0].setAttribute("src","modules/control_panel/images/phoneRinging.gif");
        }if(state_call=="Up"){
            img[0].setAttribute("src","modules/control_panel/images/icon_upPhone.png");
        }if(state_call=="Down"){
            img[0].setAttribute("src","modules/control_panel/images/phhonez0.png");
        }

        if(call_dstn!=null && speak_time!=null){
            span[0].firstChild.nodeValue = call_dstn;
            span[1].firstChild.nodeValue = speak_time;
        }

        if(context=="macro-dialout-trunk"){
            var divTrun = document.getElementById("tru_" + trunk);
            var subdivTrun = divTrun.getElementsByTagName("div");
            var spanTrun = subdivTrun[1].getElementsByTagName("span");
            spanTrun[0].innerHTML = user;
            spanTrun[1].innerHTML = speak_time;
        }else{
        
            var divTrun = document.getElementById("trunks");
            var spanTrun = divTrun.getElementsByTagName("span");
            //alert(spanTrun.length);
            spanTrun[0].firstChild.nodeValue = " ";
            spanTrun[1].firstChild.nodeValue = " ";
        }
        if(numconf != " "){  
            var divConf = document.getElementById("conference_" + numconf);
            var spanConf = divConf.getElementsByTagName("span");
            spanConf[0].firstChild.nodeValue = parties;
            spanConf[1].firstChild.nodeValue = activity;
        }
        else{
            var divConf = document.getElementById("conference_2525");
            var spanConf = divConf.getElementsByTagName("span");
            spanConf[0].firstChild.nodeValue = "";
            spanConf[1].firstChild.nodeValue = "";
            var divConf = document.getElementById("conference_23");
            var spanConf = divConf.getElementsByTagName("span");
            spanConf[0].firstChild.nodeValue = "";
            spanConf[1].firstChild.nodeValue = "";
        }
    }
   /* parser=new DOMParser();
    xmlDoc=parser.parseFromString(xmlRefresh,"text/xml");

    var db=xmlDoc.getElementsByTagName("items");
    var item_box=db[0].getElementsByTagName("item_box");
    for(var i=0;i<item_box.length;i++)
    {
        var user = item_box[i].getElementsByTagName("user")[0];
        var speak_time = item_box[i].getElementsByTagName("speak_time")[0];
        var call_dstn = item_box[i].getElementsByTagName("call_dstn")[0];
        var status_ = item_box[i].getElementsByTagName("status")[0];
        var voicemail = item_box[i].getElementsByTagName("voicemail")[0];
        var state_call = item_box[i].getElementsByTagName("state_call")[0];
        var voicemail_cnt = item_box[i].getElementsByTagName("voicemail_cnt")[0];
        var context = item_box[i].getElementsByTagName("context")[0];
        var trunk = item_box[i].getElementsByTagName("trunk")[0];
        var numconf = item_box[i].getElementsByTagName("numconf")[0];
        var parties = item_box[i].getElementsByTagName("parties")[0];
        var activity = item_box[i].getElementsByTagName("activity")[0];
    
        var div = document.getElementById("ext_" + user.firstChild.nodeValue);

        var subdiv = div.getElementsByTagName("div");
        var span = subdiv[1].getElementsByTagName("span");

        if(status_.firstChild.nodeValue=='on'){
            div.setAttribute("class","item_box");
        }else{
            div.setAttribute("class","item_box item_boxOff");
        }

        if(voicemail.firstChild.nodeValue=="1"){
            var a = subdiv[2].getElementsByTagName("a");
            var img = a[0].getElementsByTagName("img");
            img[0].setAttribute("src","modules/control_panel/images/mail.png");
        }else{
            subdiv[2].innerHTML = "";
        }

        var img = subdiv[3].getElementsByTagName("img");

        if(state_call.firstChild.nodeValue=="Ringing"){
            img[0].setAttribute("src","modules/control_panel/images/phoneRinging.gif");
        }if(state_call.firstChild.nodeValue=="Up"){
            img[0].setAttribute("src","modules/control_panel/images/icon_upPhone.png");
        }if(state_call.firstChild.nodeValue=="Down"){
            img[0].setAttribute("src","modules/control_panel/images/phhonez0.png");
        }

        if(call_dstn!=null && speak_time!=null){
            span[0].firstChild.nodeValue = call_dstn.firstChild.nodeValue;
            span[1].firstChild.nodeValue = speak_time.firstChild.nodeValue;
        }

        if(context.firstChild.nodeValue=="macro-dialout-trunk"){
            var divTrun = document.getElementById("tru_" + trunk.firstChild.nodeValue);
            var subdivTrun = divTrun.getElementsByTagName("div");
            var spanTrun = subdivTrun[1].getElementsByTagName("span");
            spanTrun[0].innerHTML = user.firstChild.nodeValue;
            spanTrun[1].innerHTML = speak_time.firstChild.nodeValue;
        }else{
        
            var divTrun = document.getElementById("trunks");
            var spanTrun = divTrun.getElementsByTagName("span");
            //alert(spanTrun.length);
            spanTrun[0].firstChild.nodeValue = " ";
            spanTrun[1].firstChild.nodeValue = " ";
        }
        if(numconf.firstChild.nodeValue != " "){  
            var divConf = document.getElementById("conference_" + numconf.firstChild.nodeValue);
            var spanConf = divConf.getElementsByTagName("span");
            spanConf[0].firstChild.nodeValue = parties.firstChild.nodeValue;
            spanConf[1].firstChild.nodeValue = activity.firstChild.nodeValue;
        }
        else{
            var divConf = document.getElementById("conference_2525");
            var spanConf = divConf.getElementsByTagName("span");
            spanConf[0].firstChild.nodeValue = "";
            spanConf[1].firstChild.nodeValue = "";
            var divConf = document.getElementById("conference_23");
            var spanConf = divConf.getElementsByTagName("span");
            spanConf[0].firstChild.nodeValue = "";
            spanConf[1].firstChild.nodeValue = "";
        }
    }*/
}

//actualizar();

function loadSizeArea2()
{
    var arrAction              = new Array();
        arrAction["action"]    = "loadArea2";
        arrAction["rawmode"]   = "yes";
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                    loadArea2(arrData);
            }
        );
}

function loadSizeArea()
{
    var arrAction              = new Array();
        arrAction["action"]    = "loadArea";
        arrAction["rawmode"]   = "yes";
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                    loadArea(arrData);
            }
        );
}

// function loadArea(xmlLoad){
//     parser=new DOMParser();
//     xmlDoc=parser.parseFromString(xmlLoad,"text/xml");
// 
//     var db=xmlDoc.getElementsByTagName("areas");
//     var area_box=db[0].getElementsByTagName("area_box");
//     alert("Presione [Enter] o de clic en [Aceptar] para recargar Areas");
//     for(var i=0;i<area_box.length;i++)
//     {
//         var namearea = area_box[i].getElementsByTagName("name")[0];
//         //var heightsize = area_box[i].getElementsByTagName("height")[0];
//         //var widthsize = area_box[i].getElementsByTagName("width")[0];
//         //var no_items = area_box[i].getElementsByTagName("no_items")[0];
//         
//         var area = document.getElementById("content"+namearea.firstChild.nodeValue);
//         //area.style.height = heightsize.firstChild.nodeValue+"px";
//         //area.style.width = widthsize.firstChild.nodeValue+"px";
//         //var headArea = document.getElementById("head"+namearea.firstChild.nodeValue);
// 
//         if(namearea.firstChild.nodeValue=="Extension"){
//             var heightA1 = document.getElementById("heightA1").value;
//             area.style.height = heightA1+"px";
//             var widthA1 = document.getElementById("widthA1").value;
//             area.style.width = widthA1+"px";
//             var content = document.getElementById("content");
//             content.style.width = widthA1+"px";
//             //var content = document.getElementById("content");
//             content.style.height = "auto";
//             //headArea.style.width = widthA1+"px";
//             var tool = document.getElementById("tool");
//             tool.style.width = widthA1+"px";
//         }
//         if(namearea.firstChild.nodeValue=="Trunks"){
//             var heightA6 = document.getElementById("heightA6").value;
//             area.style.height = heightA6+"px";
//             var widthA6 = document.getElementById("widthA6").value;
//             area.style.width = widthA6+"px";
//         }
//         if(namearea.firstChild.nodeValue=="Area1"){
//             
//             var widthA2 = document.getElementById("widthA2").value;
//             area.style.width = widthA2+"px";
//             var heightA2 = document.getElementById("heightA2").value;
//             area.style.height = heightA2+"px";
//             //headArea.style.width = widthA2+"px";
//         }
//         if(namearea.firstChild.nodeValue=="Area2"){
//             
//             var widthA3 = document.getElementById("widthA3").value;
//             area.style.width = widthA3+"px";
//             var heightA3 = document.getElementById("heightA3").value;
//             area.style.height = heightA3+"px";
//             //headArea.style.width = widthA3+"px";
//         }
//         if(namearea.firstChild.nodeValue=="Area3"){
//             
//             var widthA4 = document.getElementById("widthA4").value;
//             area.style.width = widthA4+"px";
//             var heightA4 = document.getElementById("heightA4").value;
//             area.style.height = heightA4+"px";
//             //headArea.style.width = widthA4+"px";
//         }
//         if(namearea.firstChild.nodeValue=="Queues"){
//             
//             var widthA5 = document.getElementById("widthA5").value;
//             area.style.width = widthA5+"px";
//             var heightA5 = document.getElementById("heightA5").value;
//             area.style.height = heightA5+"px";
//             //headArea.style.width = widthA5+"px";
//         }
//         
//     }
// }


function loadArea(xmlLoad){
    parser=new DOMParser();
    xmlDoc=parser.parseFromString(xmlLoad,"text/xml");


    var db=xmlDoc.getElementsByTagName("areas");
    var area_box=db[0].getElementsByTagName("area_box");
    //alert("Presione [Enter] o de clic en [Aceptar] para recargar Areas");
    for(var i=0;i<area_box.length;i++)
    {
        var namearea = area_box[i].getElementsByTagName("name")[0];
        var heightsize = area_box[i].getElementsByTagName("height")[0];
        var widthsize = area_box[i].getElementsByTagName("width")[0];
        var d = document.getElementById("contentConferences");
       
        var area = document.getElementById("content"+namearea.firstChild.nodeValue);

        if(namearea.firstChild.nodeValue=="Extension"){
            area.style.height = heightsize.firstChild.nodeValue+"px";
            area.style.width = widthsize.firstChild.nodeValue+"px";
            var content = document.getElementById("content");
            content.style.width = widthsize.firstChild.nodeValue+"px";
            
            content.style.height = "auto";

            var tool = document.getElementById("tool");
            tool.style.width = widthsize.firstChild.nodeValue+"px";
        }
        if(namearea.firstChild.nodeValue=="Trunks"){ 
            area.style.height = heightsize.firstChild.nodeValue+"px";
            area.style.width = widthsize.firstChild.nodeValue+"px";

        }
        if(namearea.firstChild.nodeValue=="TrunksSIP"){ 
            area.style.height = heightsize.firstChild.nodeValue+"px";
            area.style.width = widthsize.firstChild.nodeValue+"px";
        }
        if(namearea.firstChild.nodeValue=="Area1"){
            area.style.width = widthsize.firstChild.nodeValue+"px";
            area.style.height = heightsize.firstChild.nodeValue+"px";
            
        }
        if(namearea.firstChild.nodeValue=="Area2"){
            area.style.width = widthsize.firstChild.nodeValue+"px";
            area.style.height = heightsize.firstChild.nodeValue+"px";

        }
        if(namearea.firstChild.nodeValue=="Area3"){
            area.style.width = widthsize.firstChild.nodeValue+"px";
            area.style.height = heightsize.firstChild.nodeValue+"px";
            
        }
        if(namearea.firstChild.nodeValue=="Queues"){
            area.style.width = widthsize.firstChild.nodeValue+"px";
            area.style.height = heightsize.firstChild.nodeValue+"px";
            
        }
        if(namearea.firstChild.nodeValue=="Conferences"){
            area.style.width = widthsize.firstChild.nodeValue+"px";
            area.style.height = heightsize.firstChild.nodeValue+"px";
        }
        
    }

    actualizar();
}

function loadArea2(xmlLoad){
    //alert("Presione [Enter] o de clic en [Aceptar] para recargar Areas");
    if(document.getElementById("nameArea1").value=="Extension"){

        var area1 = document.getElementById("content"+document.getElementById("nameArea1").value);
        var heightA1 = document.getElementById("heightA1").value;
        area1.style.height = heightA1+"px";
        var widthA1 = document.getElementById("widthA1").value;
        area1.style.width = widthA1+"px";
        var content = document.getElementById("content");
        content.style.width = widthA1+"px";
        content.style.height = "auto";
        var tool = document.getElementById("tool");
        tool.style.width = widthA1+"px";
    }
    if(document.getElementById("nameArea2").value=="Area1"){

        var area2 = document.getElementById("content"+document.getElementById("nameArea2").value);
        var widthA2 = document.getElementById("widthA2").value;
        area2.style.width = widthA2+"px";
        var heightA2 = document.getElementById("heightA2").value;
        area2.style.height = heightA2+"px";
    }
    if(document.getElementById("nameArea3").value=="Area2"){
        
        var area3 = document.getElementById("content"+document.getElementById("nameArea3").value);
        var widthA3 = document.getElementById("widthA3").value;
        area3.style.width = widthA3+"px";
        var heightA3 = document.getElementById("heightA3").value;
        area3.style.height = heightA3+"px";
    }
    if(document.getElementById("nameArea4").value=="Area3"){
        
        var area4 = document.getElementById("content"+document.getElementById("nameArea4").value);
        var widthA4 = document.getElementById("widthA4").value;
        area4.style.width = widthA4+"px";
        var heightA4 = document.getElementById("heightA4").value;
        area4.style.height = heightA4+"px";
    }
    if(document.getElementById("nameArea5").value=="Queues"){
        
        var area5 = document.getElementById("content"+document.getElementById("nameArea5").value);
        var widthA5 = document.getElementById("widthA5").value;
        area5.style.width = widthA5+"px";
        var heightA5 = document.getElementById("heightA5").value;
        area5.style.height = heightA5+"px";
    }
    
    if(document.getElementById("nameArea6").value=="Trunks"){
        var area6 = document.getElementById("content"+document.getElementById("nameArea6").value);
        var heightA6 = document.getElementById("heightA6").value;
        area6.style.height = heightA6+"px";
        var widthA6 = document.getElementById("widthA6").value;
        area6.style.width = widthA6+"px";
    }
    if(document.getElementById("nameArea7").value=="TrunksSIP"){
        var area7 = document.getElementById("content"+document.getElementById("nameArea7").value);
        var heightA7 = document.getElementById("heightA7").value;
        area7.style.height = heightA7+"px";
        var widthA7 = document.getElementById("widthA7").value;
        area7.style.width = widthA7+"px";
    }
    if(document.getElementById("nameArea8").value=="Conferences"){
        var area8 = document.getElementById("content"+document.getElementById("nameArea8").value);
        var widthA8 = document.getElementById("widthA8").value;
        area8.style.width = widthA8+"px";
        var heightA8 = document.getElementById("heightA8").value;
        area8.style.height = heightA8+"px";
    }
}


function saveDescriptionArea1(){
    var descripA1 =document.getElementById("descrip1").value;
    var arrAction                  = new Array();
        arrAction["action"]        = "saveEdit";
        arrAction["rawmode"]       = "yes";
        arrAction["description"]   = descripA1;
        arrAction["area"]          = 2;
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                    controlSaveDescripion1(arrData);
            }
        );
}
function controlSaveDescripion1(message) {
    alert(message);
    $("#layerCM").hide();
    var headArea2 = document.getElementById("headArea1");
    var lengthA2 = headArea2.firstChild.nodeValue.split(" -- ")[1];
    headArea2.firstChild.nodeValue = ""+document.getElementById("descrip1").value+" -- "+lengthA2+"";
}


function saveDescriptionArea2() {
    var descripA2 =document.getElementById("descrip2").value;
    var arrAction                  = new Array();
        arrAction["action"]        = "saveEdit";
        arrAction["rawmode"]       = "yes";
        arrAction["description"]   = descripA2;
        arrAction["area"]          = 3;
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                    controlSaveDescripion2(arrData);
            }
        );
}
function controlSaveDescripion2(message) {
    alert(message);
    $("#layerCM").hide();
    var headArea3 = document.getElementById("headArea2");
    var lengthA3 = headArea3.firstChild.nodeValue.split(" -- ")[1];
    headArea3.firstChild.nodeValue = ""+document.getElementById("descrip2").value+" -- "+lengthA3+"";
}


function saveDescriptionArea3() {
    var descripA3 =document.getElementById("descrip3").value;
    var arrAction                  = new Array();
        arrAction["action"]        = "saveEdit";
        arrAction["rawmode"]       = "yes";
        arrAction["description"]   = descripA3;
        arrAction["area"]          = 4;
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                    controlSaveDescripion3(arrData);
            }
        );
}
function controlSaveDescripion3(message) {
    alert(message);
    $("#layerCM").hide();
    var headArea4 = document.getElementById("headArea3");
    var lengthA4 = headArea4.firstChild.nodeValue.split(" -- ")[1];
    headArea4.firstChild.nodeValue = ""+document.getElementById("descrip3").value+" -- "+lengthA4+"";
}


loadSizeArea();

function reFresh() {
    location.reload(true)
}

function actualizarQueues()
{
    var arrAction                  = new Array();
        arrAction["action"]        = "refreshQueues";
        arrAction["rawmode"]       = "yes";
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                    reloadQueues(arrData);
            }
        );
}
