var module_name = "calendar";

$(document).ready(function(){
    
    $('#select2').fcbkcomplete({
        json_url: "index.php?menu="+module_name+"&action=get_contacts&rawmode=yes&userid=",
        cache: true,
        filter_case: true,
        filter_hide: true,
        firstselected: true,
        //onremove: "testme",
        //onselect: "testme",
        filter_selected: true,
        newel: true
    });

      $('#formNewEvent').submit(
        function(){
            //the event'name is filled
            if(!getStatusEvent()){
                connectJSON("error_eventName");
                return false;
            }
            //date1 <= date2
            if(getDatesValid()){
                connectJSON("error_date");
                return false;
            }
            //h1:m1 < h2:m2
            if(!isCorrectTime()){
                connectJSON("error_hour");
                return false;
            }

            //description is filled
            if(!getStatusDescription()){
                connectJSON("error_description");
                return false;
            }

            if(getStatusItRepeat()){
                if(getNumCheckboxDays() == 0){
                    connectJSON("error_repeat");
                    return false;
                }
            }

            if(!existRecording()){
                connectJSON("error_recording");
                return false;
            }

            //asterisk_call_me is check 
            if(!getStatusCallsNotification()){// si en on => true
                if(!validCallsTo()){
                    connectJSON("call_to_error");
                    return false;
                }
            }else{//on
                verifyNumExtesion();/// validar para que no siempre se retorne debido a que siempre va a preguntar si existe o no estension para el usuario
                var titles = document.getElementById('label_call');
                if(titles.childNodes.length > 0)    return false;
            }

            //es valido el contenido de notification_email
            if(getStatusNotification()){
                var result = obtainEmails();
                if(result == false){
                    connectJSON("error_notification_emails");
                    return false;
                }
                if(result == "error_email"){
                    connectJSON("email_no_valid");
                    return false;
                }
            }
        return true;
    });

    $('#cancel').click(function(){
        $('#box').hide();
        $('#title_box').html("");
    });

    $('.close_box, #cancel').click(function(){
        $('#box').hide();
        $('#title_box').html("");
    });

    $('#edit').click(function(){
        $('#new_box').attr("style","display:none;");
        $('#edit_box').attr("style","display:block;");
        $('#view_box').attr("style","display:none;");
        $('#email_to').attr("style","visibility:visible;");
        $('.del_contact').attr("style","visibility:visible;");
        var estado = $('#notification').val();
        if(estado == "on")
            $('#notification_email').attr("style","visibility:visible;");

        var event_name        = document.getElementById('event');
        var description_event = document.getElementsByName('description')[0];
        var date_ini          = document.getElementById('f-calendar-field-1');
        var date_end          = document.getElementById('f-calendar-field-2');
        var date_ini_hour1    = document.getElementsByName('hora1')[0];
        var date_ini_minute1  = document.getElementsByName('minuto1')[0];
        var date_end_hour2    = document.getElementsByName('hora2')[0];
        var date_end_minute2  = document.getElementsByName('minuto2')[0];
        var it_repeat_event   = document.getElementsByName('it_repeat')[0];
        var repeat_name       = document.getElementsByName('repeat')[0];
        var recording_event   = document.getElementsByName('recording')[0];
        var type_repeat_event = document.getElementById('type_repeat');
        var call_to_event     = document.getElementById('call_to');
        var chkoldSunday      = document.getElementsByName('chkoldSunday')[0];
        var chkoldMonday      = document.getElementsByName('chkoldMonday')[0];
        var chkoldTuesday     = document.getElementsByName('chkoldTuesday')[0];
        var chkoldWednesday   = document.getElementsByName('chkoldWednesday')[0];
        var chkoldThursday    = document.getElementsByName('chkoldThursday')[0];
        var chkoldFriday      = document.getElementsByName('chkoldFriday')[0];
        var chkoldSaturday    = document.getElementsByName('chkoldSaturday')[0];
        var chkoldasterisk    = document.getElementsByName('chkoldasterisk_call_me')[0];
        var inputAsteriskCall = document.getElementById('asterisk_call_me');
        var inputCallTo       = document.getElementById('call_to');
        var chkoldnoti        = document.getElementsByName('chkoldnotification')[0];
        var id_event_input    = document.getElementById('id_event');
        var uid               = document.getElementById('id');
                //disabled all input and select
        RemoveAttributeDisable(event_name);
        RemoveAttributeDisable(description_event);
        RemoveAttributeDisable(date_ini);
        RemoveAttributeDisable(date_end);
        RemoveAttributeDisable(date_ini_hour1);
        RemoveAttributeDisable(date_ini_minute1);
        RemoveAttributeDisable(date_end_hour2);
        RemoveAttributeDisable(date_end_minute2);
        RemoveAttributeDisable(it_repeat_event);
        RemoveAttributeDisable(repeat_name);
        RemoveAttributeDisable(recording_event);
        RemoveAttributeDisable(chkoldSunday);
        RemoveAttributeDisable(chkoldMonday);
        RemoveAttributeDisable(chkoldTuesday);
        RemoveAttributeDisable(chkoldWednesday);
        RemoveAttributeDisable(chkoldThursday);
        RemoveAttributeDisable(chkoldFriday);
        RemoveAttributeDisable(chkoldSaturday);
        RemoveAttributeDisable(chkoldasterisk);
        RemoveAttributeDisable(inputCallTo);
        RemoveAttributeDisable(chkoldnoti);
    });

    // funcion para cambiar deacuerdo a la seleccion week or month en new event
    $('select[name=it_repeat]').change(function () {
        var txt = $("select[name=it_repeat] option:selected").attr('value');
        if(txt == "each_day" || txt == "each_month"){
            $('.repeat').attr("style","visibility: visible;");
            var order = 'menu='+module_name+'&action=get_lang&rawmode=yes';
            var message = "";
            $.post("index.php", order,
                function(theResponse){
                    message = JSONtoString(theResponse);
                    var txt = $("select[name=it_repeat] option:selected").attr('value');
                    if(txt == "each_day")
                        $('#type_repeat').text(message['Weeks']);
                    if(txt == "each_month")
                        $('#type_repeat').text(message['Months']);
            });
        }
        else{
            $('.repeat').attr("style","visibility: hidden;");

        }
    });

    $('#delete').click(function(){ //hace un submit sin pasar por el submit validador
        var id_event = $('#id_event').val();
        var order = "menu="+module_name+"&action=delete_box&id_event="+id_event+"&rawmode=yes";
        $.post("index.php", order,
                function(theResponse){
                    var message = JSONtoString(theResponse);
                    var error = message['error_delete_JSON'];
                    var status_error = message['error_delete_status'];
                    if(status_error == "on"){
                        //then close box
                        $('#box').hide();
                        $('#title_box').html("");
                        document.formNewEvent.submit();
                        alert(error);
                    }else{
                        alert(error);
                    }
        });
    });

    $('#box').draggable();

    $('[id^=event_day_]').sortable({
        connectWith: '.ul_class',
        opacity: 0.6,
        receive: function(evt, ui) {
            var li_id_origen  = $(ui.item).attr("id").split("_")[1]; //id event
            var ul_id_destine = $(this).attr("id").split("_")[2]; // number of day
            var year   = document.getElementById("year").value;
            var month  = document.getElementById("month").value;

            var order = 'menu='+module_name+'&action=save_edit&rawmode=yes&id_event='+li_id_origen+'&day_no='+ul_id_destine+'&month='+month+'&year='+year;
            $.post("index.php", order, function(theResponse){
                //$("#contentRight").html(theResponse);
                alert(theResponse);
            });
        }
    });

    /*$('[id^=ev_]').dblclick(function(evt){
        var ul_id_evt = $(this).attr("id").split("_")[1]; //id event

        var order = 'menu='+module_name+'&action=view&rawmode=yes&id_event='+ul_id_evt;
        $.post("index.php", order, function(theResponse){
            $("#response_jquery").html(theResponse);
            //alert(theResponse);
        });
    });

    $("[id^=day_]").click(function(evt){
        var day_no = $(this).attr("id").split("_")[1]; //number of day
        var year   = document.getElementById("year").value;
        var month  = document.getElementById("month").value;

        var order = 'menu='+module_name+'&action=new_open&rawmode=yes&day_no='+day_no+'&month='+month+'&year='+year;
        $.post("index.php", order, function(theResponse){
            //fillFaceboxFromAjax(theResponse);//$("#contentRight").html(theResponse);
            alert(theResponse);
        });
    */
    $("#datepicker").datepicker({
        firstDay: 0,
        //showOtherMonths: true,
        //yearRange: '2010:2099',
        changeYear: true,
        changeMonth: true,
        showButtonPanel: true,
        onChangeMonthYear: function(year, month, inst){
            var order = 'menu='+module_name+'&action=calendar_monthly&rawmode=yes&year='+year+'&month='+month;
            $.post("index.php", order, function(theResponse){
                //var cc = document.getElementById("calendar_content");
                //cc.innerHTML = theResponse;
                //$("#calendar_content").html(theResponse);
                var cal = $.fullCalendar;
                //alert("hola    " + cal.views.month.prev());
            });
        }/*,
        onSelect: function(dateText, inst){
            alert("dateText: " + dateText);
        }*/
    });

// checkbox notificacion
    $('table tr #noti :checkbox').click(
        function(){
            var estado = $('#notification').val();
            if(estado == 'on'){
                $('#notification_email').attr("style","visibility: visible;");
            }
            else{
                $('#notification_email').attr("style","visibility: hidden;");
            }
    });

//  checkbox asterisk_call_me
    $('table tr #asterisk_call :checkbox').click(
        function(){
            var estado = $('#asterisk_call_me').val();
            if(estado == 'off'){
                //$('#check').show();
                $('#call_to').val("");
                $('#label_call').html("");
                $('#add_phone').show();
            }
            else{
                //$('#check').hide();
                verifyNumExtesion();
                $('#add_phone').hide();
            }
    });

});

// function llamado(day_no)
// {
//     var year   = document.getElementById("year").value;
//     var month  = document.getElementById("month").value;
//     var order = 'menu='+module_name+'&action=new_open&rawmode=yes&day_no='+day_no+'&month='+month+'&year='+year;
// 
// 
// //     $.post("index.php", order, function(theResponse){
// //         fillFaceboxFromAjax(theResponse);//$("#contentRight").html(theResponse);
// //         return theResponse;
// //     });
// }

function change_year(year)
{
    var month  = document.getElementById("month").value;
    window.open('index.php?menu='+module_name+'&action=report&year='+year.value+'&month='+month,'_self');
}

function change_month(month)
{
    var year   = document.getElementById("year").value;
     window.open('index.php?menu='+module_name+'&action=report&year='+year+'&month='+month.value,'_self');
}

function makeactive(tab)
{
    document.getElementById("tab_daily").className = "";
    document.getElementById("tab_weekly").className = "";
    document.getElementById("tab_monthly").className = "";
    document.getElementById("tab_"+tab).className = "active";

    var order = 'menu='+module_name+'&action=calendar_'+tab+'&rawmode=yes';
    $.post("index.php", order, function(theResponse){
            $("#calendar_content").html(theResponse);
    });
}

function testme(item)
{   if ($.browser.mozilla) 
    {
        console.log(item);
    }
    else
    {
        alert(item);
    }
}

function popup_phone_number(url_popup){
    var ancho = 600;
    var alto = 400;
    my_window = window.open(url_popup,"my_window","width="+ancho+",height="+alto+",location=yes,status=yes,resizable=yes,scrollbars=yes,fullscreen=no,toolbar=yes");
    my_window.moveTo((screen.width-ancho)/2,(screen.height-alto)/2);
    my_window.document.close();
}

function return_phone_number(number, type, id)
{
    window.opener.document.getElementById("call_to").value = number;
    window.opener.document.getElementById("phone_type").value = type;
    window.opener.document.getElementById("phone_id").value = id;
    window.close();
}

// true => email_no_valid   false => empty field
function obtainEmails(){
    //format ("name" <dd@ema.com>, "name2" <ff@ema.com>, )
    var id_emails = document.getElementById("emails");
    var total_emails = "";
    var cad = "";
    var email = "";
    var error = "error_email";
    var lista = document.getElementById("lstholder");
    //recorriendo los li el ultimo no es tomado en cuneta ya que es null
    for(var i = 0; i<lista.childNodes.length-1; i++){
        cad = lista.childNodes[i].firstChild.nodeValue;
        email = quitSimbols(cad);
        if(email==true){
            id_emails.value = "";
            return error;
        }
        total_emails += email+", ";
    }
    total_emails = total_emails + obtainTablesEmails();
    id_emails.value = total_emails;
    //obtain emails by table_emails
    if(total_emails=="")    return false;
    return total_emails;
}

    function existRecording(){
        var recording = document.getElementsByName("recording")[0];
        if(recording.childNodes.length > 0)
            return true;
        else 
            return false;
    }

// this function quit the simbols < or > and return only email
    function quitSimbols(cad){
        var i = cad.indexOf("<");
        var j = cad.indexOf(">");
        var email = cad.substring(i+1,j);
        var names = cad.substring(0,i-1);
        var sal = "\""+trim(names)+"\" "+"&lt;"+trim(email)+"&gt;";
        var format = "\""+"\" "+"&lt;"+"&gt;";
        if(sal == format){
            if(validarEmail(cad)){
                sal = "&lt;"+cad+"&gt;";
            }else{
                return true;
            }
        }
        return sal;
    }

    function validarEmail(valor) {
        if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(valor)){
            return true; //valido
        } else {
            return false; //no valido
        }
    }

    function trim(stringToTrim) {
        return stringToTrim.replace(/^\s+|\s+$/g,"");
    }


// get status of notification checkbox
    function getStatusNotification(){
        var id = document.getElementById('notification');
        var text_value = id.value;
        if(text_value == "on")
            return true;
        else
            return false;
    }
// get status of call me checkbox
    function getStatusCallsNotification(){
        var id = document.getElementById('asterisk_call_me');
        var text_value = id.value;
        if(text_value == "on")
            return true;
        else
            return false;
    }

//get num of day checkbox choosen
    function getStatusItRepeat(){
        var txt = $("select[name=it_repeat] option:selected").attr('value');
        if(txt == "each_day" || txt == "each_month")
            return true;
        else 
            return false;
    }

//get num of day checkbox choosen
    function getNumCheckboxDays(){
        var count     = 0;
        var sunday    = document.getElementById("Sunday").value;
        var monday    = document.getElementById("Monday").value;
        var tuesday   = document.getElementById("Tuesday").value;
        var wednesday = document.getElementById("Wednesday").value;
        var thursday  = document.getElementById("Thursday").value;
        var friday    = document.getElementById("Friday").value;
        var saturday  = document.getElementById("Saturday").value;

        if(sunday == "on")  count ++;
        if(monday == "on")  count ++;
        if(tuesday == "on")  count ++;
        if(wednesday == "on")  count ++;
        if(thursday == "on")  count ++;
        if(friday == "on")  count ++;
        if(saturday == "on")  count ++;

        return count;
    }

    function getStatusEvent(){
        var id = document.getElementById('event');
        var text_value = id.value;
        if(text_value != "")
            return true;
        else
            return false;
    }

    function isCorrectTime(){
        var hora1   = document.getElementsByName('hora1')[0];
        var minuto1 = document.getElementsByName('minuto1')[0];
        var hora2   = document.getElementsByName('hora2')[0];
        var minuto2 = document.getElementsByName('minuto2')[0];

        var indhour1 = hora1.selectedIndex;
        var valHour1 = hora1.options[indhour1].value;

        var indMinu1 = minuto1.selectedIndex;
        var valMinu1 = minuto1.options[indMinu1].value;

        var indhour2 = hora2.selectedIndex;
        var valHour2 = hora2.options[indhour2].value;

        var indMinu2 = minuto2.selectedIndex;
        var valMinu2 = minuto2.options[indMinu2].value;

        var starttime = valHour1+":"+valMinu1;
        var endtime   = valHour2+":"+valMinu2;
        if(endtime > starttime)
            return true;
        else 
            return false;

    }

    function getDatesValid(){
        var date1 = document.getElementById('f-calendar-field-1').value;
        var date2 = document.getElementById('f-calendar-field-2').value;
        strDate1 = new Date(date1);
        strDate2 = new Date(date2);
        if(strDate1 > strDate2)
            return true;
        else
            return false;
    }

    function getStatusDescription(){
        var id = document.getElementsByName('description')[0];
        var text_value = id.value;
        if(text_value != "") return true;
        else    return false;
    }

// valid number for asterisk_calls
    function validCallsTo(){
        var id = document.getElementById('call_to');
        var titles = document.getElementById('label_call');
        titles.innerHTML = "";
        var text_value = id.value;
        if(text_value == "") return false;
        if(isInteger(text_value))   return true;
        else    return false;
    }

    function isInteger(s)
    {   var i;
        for (i = 0; i < s.length; i++)
        {
            // Check that current character is number.
            var c = s.charAt(i);
            if (((c < "0") || (c > "9"))) return false;
        }
        // All characters are numbers.
        return true;
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

    function verifyNumExtesion() {
        var id    = document.getElementById("id").value;
        var order = 'menu='+module_name+'&action=get_num_ext&userid='+id+'&rawmode=yes';
        var message = "";
        $.post("index.php", order,
                function(theResponse){
                    message = JSONtoString(theResponse);
                    var ext = message['ext'];
                    var titles = document.getElementById('label_call');
                    var call_to = document.getElementById('call_to');
                    if(ext == "empty")
                        titles.innerHTML = message['error_ext'];
                    else{
                        titles.innerHTML = "";
                        call_to.value = ext;
                    }
            });
    }

    function getDataAjaxForm(order){
        //alert(order);
        $('#new_box').attr("style","display:none;");
        $('#edit_box').attr("style","display:none;");
        $('#view_box').attr("style","display:none;");
        $('#title_box').html("");
        $('#box').show();
        $('.loading').show();
        $.post("index.php", order,
                function(theResponse){
                    var content = $('#table_box');
                    $('.loading').hide();
                    var box = $('#box');
                    var message = JSONtoString(theResponse);          //response JSON to array
                    var recording = message['recording'];             //recording name
                    var event = message['event'];                     //name's event
                    var desc_event = message['description'];          //description's event
                    var start = message['date'];                      //start date event
                    var end = message['to'];                          //end date event
                    var hour1 = message['hora1'];                     //initial hour date event
                    var minute1 = message['minuto1'];                 //initial minute date event
                    var hour2 = message['hora2'];                     //end hour date event
                    var minute2 = message['minuto2'];                 //end minute date event
                    var it_repeat = message['it_repeat'];             //type repeat event(no repeat, week, month)
                    var repeat = message['repeat'];                   //num of weeks,months to repeat
                    var title_box = message['title'];                 //title box(view event,edit event)
                    var notificacion = message['notification'];       //notification (on, off)
                    var call_to = message['call_to'];                 //number to call
                    var email_noti = message['emails_notification'];  //emails to notify
                    var visibility_noti = message['visibility'];      //visible or not emails_notification
                    var visibility_rep = message['visibility_repeat'];//visible or not days_repeat

             /***********************      var by DOM      **************************/
                    var title_evt         = document.getElementById('title_box');
                    var event_name        = document.getElementById('event');
                    var description_event = document.getElementsByName('description')[0];
                    var date_ini          = document.getElementById('f-calendar-field-1');
                    var date_end          = document.getElementById('f-calendar-field-2');
                    var date_ini_hour1    = document.getElementsByName('hora1')[0];
                    var date_ini_minute1  = document.getElementsByName('minuto1')[0];
                    var date_end_hour2    = document.getElementsByName('hora2')[0];
                    var date_end_minute2  = document.getElementsByName('minuto2')[0];
                    var it_repeat_event   = document.getElementsByName('it_repeat')[0];
                    var repeat_name       = document.getElementsByName('repeat')[0];
                    var recording_event   = document.getElementsByName('recording')[0];
                    var type_repeat_event = document.getElementById('type_repeat');
                    var call_to_event     = document.getElementById('call_to');
                    var chkoldSunday      = document.getElementsByName('chkoldSunday')[0];
                    var chkoldMonday      = document.getElementsByName('chkoldMonday')[0];
                    var chkoldTuesday     = document.getElementsByName('chkoldTuesday')[0];
                    var chkoldWednesday   = document.getElementsByName('chkoldWednesday')[0];
                    var chkoldThursday    = document.getElementsByName('chkoldThursday')[0];
                    var chkoldFriday      = document.getElementsByName('chkoldFriday')[0];
                    var chkoldSaturday    = document.getElementsByName('chkoldSaturday')[0];
                    var inputSunday       = document.getElementById('Sunday');
                    var inputMonday       = document.getElementById('Monday');
                    var inputTuesday      = document.getElementById('Tuesday');
                    var inputWednesday    = document.getElementById('Wednesday');
                    var inputThursday     = document.getElementById('Thursday');
                    var inputFriday       = document.getElementById('Friday');
                    var inputSaturday     = document.getElementById('Saturday');
                    var chkoldasterisk    = document.getElementsByName('chkoldasterisk_call_me')[0];
                    var inputAsteriskCall = document.getElementById('asterisk_call_me');
                    var inputCallTo       = document.getElementById('call_to');
                    var chkoldnoti        = document.getElementsByName('chkoldnotification')[0];
                    var inputNotification = document.getElementById('notification');
                    var id                = document.getElementById('id');
                    var id_event_input    = document.getElementById('id_event');
                    var email_to          = document.getElementById('email_to');
                    var tabla_grilla      = document.getElementById('grilla');
                    //var emails_noti       = document.getElementById('select2');
             /**********************************************************************/
                    if(type_repeat_event.firstChild)
                        type_repeat_event.removeChild(type_repeat_event.firstChild);
                    if(title_box == "View Event"){
                        var i = 0; //cont
                        //show buttons for view even
                        $('#view_box').attr("style","display:block;");

                        //disabled all input and select
                        event_name.setAttribute("disabled","disabled");
                        description_event.setAttribute("disabled","disabled");
                        date_ini.setAttribute("disabled","disabled");
                        date_end.setAttribute("disabled","disabled");
                        date_ini_hour1.setAttribute("disabled","disabled");
                        date_ini_minute1.setAttribute("disabled","disabled");
                        date_end_hour2.setAttribute("disabled","disabled");
                        date_end_minute2.setAttribute("disabled","disabled");
                        it_repeat_event.setAttribute("disabled","disabled");
                        repeat_name.setAttribute("disabled","disabled");
                        recording_event.setAttribute("disabled","disabled");
                        chkoldSunday.setAttribute("disabled","disabled");
                        chkoldMonday.setAttribute("disabled","disabled");
                        chkoldTuesday.setAttribute("disabled","disabled");
                        chkoldWednesday.setAttribute("disabled","disabled");
                        chkoldThursday.setAttribute("disabled","disabled");
                        chkoldFriday.setAttribute("disabled","disabled");
                        chkoldSaturday.setAttribute("disabled","disabled");
                        chkoldasterisk.setAttribute("disabled","disabled");
                        inputCallTo.setAttribute("disabled","disabled");
                        chkoldnoti.setAttribute("disabled","disabled");

                        // add title
                        var title_name = document.createTextNode(message['View Event']);
                        title_evt.appendChild(title_name);

                        //fill event name
                        event_name.value = event;

                        //fill event description
                        description_event.value = desc_event;

                        //fill date init event
                        date_ini.value = start;

                        //fill date end event
                        date_end.value = end;

                        //removing all attributes selected and checked
                        RemoveAttributeSelect(date_ini_hour1);
                        RemoveAttributeSelect(date_ini_minute1);
                        RemoveAttributeSelect(date_end_hour2);
                        RemoveAttributeSelect(date_end_minute2);
                        RemoveAttributeSelect(it_repeat_event);
                        RemoveAttributeSelect(repeat_name);
                        RemoveAttributeCheck(chkoldSunday);
                        RemoveAttributeCheck(chkoldMonday);
                        RemoveAttributeCheck(chkoldTuesday);
                        RemoveAttributeCheck(chkoldWednesday);
                        RemoveAttributeCheck(chkoldThursday);
                        RemoveAttributeCheck(chkoldFriday);
                        RemoveAttributeCheck(chkoldSaturday);
                        RemoveAttributeCheck(chkoldasterisk);
                        RemoveAttributeCheck(chkoldnoti);

                        //setting input_check
                        inputSunday.value="off";
                        inputMonday.value="off";
                        inputTuesday.value="off";
                        inputWednesday.value="off";
                        inputThursday.value="off";
                        inputFriday.value="off";
                        inputSaturday.value="off";

                        //fill email_to
                        $('#notification_email').attr("style","visibility:hidden;");
                        $('#email_to').attr("style","visibility:visible;");
                        // fill tr and td in table contacts email with DOM
                        var size_emails = message['size_emails'];
                        var src_img_delete = "modules/"+module_name+"/images/delete.png";
                        $('#grilla').html("");
                        // fill labels to table emails
                        // create tr and td for title table emails and textnodes
                        if(message['notification_status'] == "on"){
                            var tr_titles             = document.createElement("tr");
                            var td_spaces1            = document.createElement("td");
                            var td_spaces2            = document.createElement("td");
                            var td_contact_title      = document.createElement("td");
                            var td_email_title        = document.createElement("td");
                            var td_contact_title_text = document.createTextNode(message['Contact']);
                            var td_email_title_text   = document.createTextNode(message['Email'])
    
                            // set attributes
                            tr_titles.setAttribute("class","letra12");
                            td_contact_title.setAttribute("style","color:#666666; font-weight:bold;font-size:12px;");
                            td_contact_title.setAttribute("align","center");
                            td_email_title.setAttribute("style","color:#666666; font-weight:bold;font-size:12px;");
                            td_email_title.setAttribute("align","center");
    
                            // append tds, trs, textnodes
                            td_email_title.appendChild(td_email_title_text);
                            td_contact_title.appendChild(td_contact_title_text);
                            tr_titles.appendChild(td_spaces1);
                            tr_titles.appendChild(td_contact_title);
                            tr_titles.appendChild(td_email_title);
                            tr_titles.appendChild(td_spaces2);
                            tabla_grilla.appendChild(tr_titles);

                            for(i = 0; i<size_emails; i++){
                                //create tr and tds
                                var tr_email   = document.createElement("tr");
                                var td_num     = document.createElement("td");
                                var td_contact = document.createElement("td");
                                var td_email   = document.createElement("td");
                                var td_delete  = document.createElement("td");
                                //create <a> for link delete
                                var a_delete   = document.createElement("a");
                                //create <img> for link delete 
                                var img_delete = document.createElement("img");
                                //create textnode &nbsp;&nbsp;&nbsp;&nbsp;
                                var spaces = document.createTextNode(" ");

                                // obtain emails var
                                var num_email  = "num_email" + i;
                                var cont_email = "cont_email" + i;
                                var name_email = "name_email" + i;

                                // set attributes to tr_email
                                tr_email.setAttribute("class","letra12");
                                // set attributes to td_num
                                td_num.setAttribute("align","center");
                                td_contact.setAttribute("align","center");
                                td_email.setAttribute("align","center");
                                td_delete.setAttribute("align","center");
                                td_delete.setAttribute("style","visibility:hidden;");
                                td_delete.setAttribute("class","del_contact");
                                // set attributes to <a>
                                a_delete.setAttribute("class","delete_email");
                                // set attributes to <img>
                                img_delete.setAttribute("src",src_img_delete);
                                img_delete.setAttribute("align","absmiddle");
                                img_delete.setAttribute("onclick","del_email_tab("+i+");");
    
                                // create textnode num, contact, email
                                var td_num_text = document.createTextNode(message[num_email]);
                                var td_contact_text = document.createTextNode(message[cont_email]);
                                var td_email_text = document.createTextNode(message[name_email]);
    
                                // append textnodes num, contact, email, a, img
                                td_num.appendChild(td_num_text);
                                td_contact.appendChild(td_contact_text);
                                td_email.appendChild(td_email_text);
                                a_delete.appendChild(spaces);
                                a_delete.appendChild(img_delete);
                                td_delete.appendChild(a_delete);
    
                                //append td to tr
                                tr_email.appendChild(td_num);
                                tr_email.appendChild(td_contact);
                                tr_email.appendChild(td_email);
                                tr_email.appendChild(td_delete);
                                tabla_grilla.appendChild(tr_email);
                            }
                        }

                        //fill start and end hour
                        for(i = 0; i<date_ini_hour1.childNodes.length; i++){
                            // to hour1
                            if(date_ini_hour1.childNodes[i].firstChild.nodeValue == hour1)
                                date_ini_hour1.childNodes[i].setAttribute('selected', 'selected');
                            // to hour2
                            if(date_end_hour2.childNodes[i].firstChild.nodeValue == hour2)
                                date_end_hour2.childNodes[i].setAttribute('selected', 'selected');
                        }

                        // fill start and end minutes
                        for(i = 0; i<date_ini_minute1.childNodes.length; i++){
                            // to minute1
                            if(date_ini_minute1.childNodes[i].firstChild.nodeValue == minute1)
                                date_ini_minute1.childNodes[i].setAttribute('selected', 'selected');
                            //to minute2
                            if(date_end_minute2.childNodes[i].firstChild.nodeValue == minute2)
                                date_end_minute2.childNodes[i].setAttribute('selected', 'selected');
                        }

                        // fill select it_repeat
                        for(i = 0; i<it_repeat_event.childNodes.length; i++){
                            if(it_repeat_event.childNodes[i].getAttribute("value") == it_repeat){
                                it_repeat_event.childNodes[i].setAttribute('selected', 'selected');
                                if(it_repeat == "none"){
                                    $('.repeat').attr("style","visibility: hidden;");
                                    //removing type_repeat_event_name
                                    if(type_repeat_event.firstChild)
                                        type_repeat_event.removeChild(type_repeat_event.firstChild);
                                }else{
                                    if(it_repeat == "each_day"){
                                        var text_value_repeat_time = document.createTextNode(message['Weeks']);
                                        type_repeat_event.appendChild(text_value_repeat_time);
                                    }else{
                                        var text_value_repeat_time = document.createTextNode(message['Weeks']);
                                        type_repeat_event.appendChild(text_value_repeat_time);
                                    }
                                    $('.repeat').attr("style","visibility: visible;");
                                    for(var j = 0; j<repeat_name.childNodes.length; j++){
                                        if(repeat_name.childNodes[j].firstChild.nodeValue == repeat)
                                            repeat_name.childNodes[j].setAttribute('selected', 'selected');
                                    }
                                    // put the corrects day
                                    if(message['Sunday_check']){
                                        chkoldSunday.setAttribute("checked","checked");
                                        inputSunday.value="on";
                                    }
                                    if(message['Monday_check']){
                                        chkoldMonday.setAttribute("checked","checked");
                                        inputMonday.value="on";
                                    }
                                    if(message['Tuesday_check']){
                                        chkoldTuesday.setAttribute("checked","checked");
                                        inputTuesday.value="on";
                                    }
                                    if(message['Wednesday_check']){
                                        chkoldWednesday.setAttribute("checked","checked");
                                        inputWednesday.value="on";
                                    }
                                    if(message['Thursday_check']){
                                        chkoldThursday.setAttribute("checked","checked");
                                        inputThursday.value="on";
                                    }
                                    if(message['Friday_check']){
                                        chkoldFriday.setAttribute("checked","checked");
                                        inputFriday.value="on";
                                    }
                                    if(message['Saturday_check']){
                                        chkoldSaturday.setAttribute("checked","checked");
                                        inputSaturday.value="on";
                                    }
                                }
                                // fill checkbox my extension
                                if(message['asterisk_call_me'] == "on"){
                                    chkoldasterisk.setAttribute("checked","checked");
                                    inputAsteriskCall.value = "on";
                                }else{
                                    inputAsteriskCall.value = "off";
                                }

                                // fill input call_to
                                inputCallTo.value = message['call_to'];

                                // fill input uid hidden
                                id.value = message['uid'];

                                // fill input id hidden
                                id_event_input.value = message['id'];

                                // hide the messages
                                $('#add_phone').attr("style","display: none;");
                                $('.new_box_rec').attr("style","display: none;");

                                // fill checkbox notification emails
                                if(message['notification_status'] == "on"){
                                    chkoldnoti.setAttribute("checked","checked");
                                    inputNotification.value = "on";
                                }else{
                                    inputNotification.value = "off";
                                    $('#select2').html("");
                                }
                            }
                        }

                        //

                    }

                    /*if(title_box == "Edit Event"){

                    }*/
            });
    }

    function displayNewEvent(){
        $('#new_box').attr("style","display:none;");
        $('#edit_box').attr("style","display:none;");
        $('#view_box').attr("style","display:none;");
        var order = "menu="+module_name+"&action=new_box&rawmode=yes";
        $('#title_box').html("");
        $('#box').show();
        $('.loading').show();
        $.post("index.php", order,
                function(theResponse){
                    var content = $('#table_box');
                    $('.loading').hide();
                    var box = $('#box');
                    var message = JSONtoString(theResponse);          //response JSON to array


             /***********************      var by DOM      **************************/
                    var title_evt         = document.getElementById('title_box');
                    var event_name        = document.getElementById('event');
                    var description_event = document.getElementsByName('description')[0];
                    var date_ini          = document.getElementById('f-calendar-field-1');
                    var date_end          = document.getElementById('f-calendar-field-2');
                    var date_ini_hour1    = document.getElementsByName('hora1')[0];
                    var date_ini_minute1  = document.getElementsByName('minuto1')[0];
                    var date_end_hour2    = document.getElementsByName('hora2')[0];
                    var date_end_minute2  = document.getElementsByName('minuto2')[0];
                    var it_repeat_event   = document.getElementsByName('it_repeat')[0];
                    var repeat_name       = document.getElementsByName('repeat')[0];
                    var recording_event   = document.getElementsByName('recording')[0];
                    var type_repeat_event = document.getElementById('type_repeat');
                    var call_to_event     = document.getElementById('call_to');
                    var chkoldSunday      = document.getElementsByName('chkoldSunday')[0];
                    var chkoldMonday      = document.getElementsByName('chkoldMonday')[0];
                    var chkoldTuesday     = document.getElementsByName('chkoldTuesday')[0];
                    var chkoldWednesday   = document.getElementsByName('chkoldWednesday')[0];
                    var chkoldThursday    = document.getElementsByName('chkoldThursday')[0];
                    var chkoldFriday      = document.getElementsByName('chkoldFriday')[0];
                    var chkoldSaturday    = document.getElementsByName('chkoldSaturday')[0];
                    var inputSunday       = document.getElementById('Sunday');
                    var inputMonday       = document.getElementById('Monday');
                    var inputTuesday      = document.getElementById('Tuesday');
                    var inputWednesday    = document.getElementById('Wednesday');
                    var inputThursday     = document.getElementById('Thursday');
                    var inputFriday       = document.getElementById('Friday');
                    var inputSaturday     = document.getElementById('Saturday');
                    var chkoldasterisk    = document.getElementsByName('chkoldasterisk_call_me')[0];
                    var inputAsteriskCall = document.getElementById('asterisk_call_me');
                    var inputCallTo       = document.getElementById('call_to');
                    var chkoldnoti        = document.getElementsByName('chkoldnotification')[0];
                    var inputNotification = document.getElementById('notification');
                    var id_event_input    = document.getElementById('id_event');
                    var uid               = document.getElementById('id');
                    //var emails_noti       = document.getElementById('select2');
                    var email_to          = document.getElementById('email_to');
             /**********************************************************************/

             /****************seteando variables ************************************/
                    if(type_repeat_event.firstChild)
                        type_repeat_event.removeChild(type_repeat_event.firstChild);
                    //show buttons for new event
                    $('#new_box').attr("style","display:block;");
                    $('#email_to').attr("style","visibility:hidden;");
                    //setting input_check
                    inputSunday.value="off";
                    inputMonday.value="off";
                    inputTuesday.value="off";
                    inputWednesday.value="off";
                    inputThursday.value="off";
                    inputFriday.value="off";
                    inputSaturday.value="off";

                    //disabled all input and select
                    RemoveAttributeDisable(event_name);
                    RemoveAttributeDisable(description_event);
                    RemoveAttributeDisable(date_ini);
                    RemoveAttributeDisable(date_end);
                    RemoveAttributeDisable(date_ini_hour1);
                    RemoveAttributeDisable(date_ini_minute1);
                    RemoveAttributeDisable(date_end_hour2);
                    RemoveAttributeDisable(date_end_minute2);
                    RemoveAttributeDisable(it_repeat_event);
                    RemoveAttributeDisable(repeat_name);
                    RemoveAttributeDisable(recording_event);
                    RemoveAttributeDisable(chkoldSunday);
                    RemoveAttributeDisable(chkoldMonday);
                    RemoveAttributeDisable(chkoldTuesday);
                    RemoveAttributeDisable(chkoldWednesday);
                    RemoveAttributeDisable(chkoldThursday);
                    RemoveAttributeDisable(chkoldFriday);
                    RemoveAttributeDisable(chkoldSaturday);
                    RemoveAttributeDisable(chkoldasterisk);
                    RemoveAttributeDisable(inputCallTo);
                    RemoveAttributeDisable(chkoldnoti);

                    //removing all attributes selected and checked
                    RemoveAttributeSelect(date_ini_hour1);
                    RemoveAttributeSelect(date_ini_minute1);
                    RemoveAttributeSelect(date_end_hour2);
                    RemoveAttributeSelect(date_end_minute2);
                    RemoveAttributeSelect(it_repeat_event);
                    RemoveAttributeSelect(repeat_name);
                    RemoveAttributeCheck(chkoldSunday);
                    RemoveAttributeCheck(chkoldMonday);
                    RemoveAttributeCheck(chkoldTuesday);
                    RemoveAttributeCheck(chkoldWednesday);
                    RemoveAttributeCheck(chkoldThursday);
                    RemoveAttributeCheck(chkoldFriday);
                    RemoveAttributeCheck(chkoldSaturday);
                    RemoveAttributeCheck(chkoldasterisk);
                    RemoveAttributeCheck(chkoldnoti);

                    // hide the sections email_to
                    $('#notification_email').attr("style","visibility:visible;");
                    $('#email_to').attr("style","display:none;");
                    //$('#email_to').html("");

                    // add title
                    var title_name = document.createTextNode(message['New_Event']);
                    title_evt.appendChild(title_name);
                    event_name.value="";
                    description_event.value="";
                    date_ini.value = message['now'];
                    date_end.value = message['now'];
                    uid.value = message['uid'];
                    date_ini_hour1.childNodes[0].setAttribute('selected', 'selected');
                    date_ini_minute1.childNodes[0].setAttribute('selected', 'selected');
                    date_end_hour2.childNodes[0].setAttribute('selected', 'selected');
                    date_end_minute2.childNodes[0].setAttribute('selected', 'selected');
                    it_repeat_event.childNodes[0].setAttribute('selected', 'selected');
                    repeat_name.childNodes[0].setAttribute('selected', 'selected');
                    $('#type_repeat_event').html("");
                    call_to_event.value = "";
                    recording_event.childNodes[0].setAttribute('selected', 'selected');
                    $('.repeat').attr("style","visibility: hidden;");
                    $('#notification_email').attr("style","visibility: hidden;");
                    inputNotification.value = "off";
                    inputAsteriskCall.value = "off";

                    //check day
                    if(message['dayLe'] == "Sun"){
                        chkoldSunday.setAttribute('checked', 'checked');
                        inputSunday.value = "on";
                    }

                    switch(message['dayLe']){
                        case "Sun":
                            chkoldSunday.setAttribute('checked', 'checked');
                            inputSunday.value = "on";
                            break;
                        case "Mon":
                            chkoldMonday.setAttribute('checked', 'checked');
                            inputMonday.value = "on";
                            break;
                        case "Tue":
                            chkoldTuesday.setAttribute('checked', 'checked');
                            inputTuesday.value = "on";
                            break;
                        case "Wed":
                            chkoldWednesday.setAttribute('checked', 'checked');
                            inputWednesday.value = "on";
                            break;
                        case "Thu":
                            chkoldThursday.setAttribute('checked', 'checked');
                            inputThursday.value = "on";
                            break;
                        case "Fri":
                            chkoldFriday.setAttribute('checked', 'checked');
                            inputFriday.value = "on";
                            break;
                        case "Sat":
                            chkoldSaturday.setAttribute('checked', 'checked');
                            inputSaturday.value = "on";
                            break;
                        default:
                            chkoldSunday.setAttribute('checked', 'checked');
                            inputSunday.value = "on";
                            break;
                    }
            });
    }

    function RemoveAttributeSelect(selectObject){
        for(var j = 0; j<selectObject.childNodes.length; j++){
            selectObject.childNodes[j].removeAttribute('selected');
        }
    }

    function RemoveAttributeCheck(selectObject){
        selectObject.removeAttribute('checked');
    }

    function RemoveAttributeDisable(selectObject){
        selectObject.removeAttribute('disabled');
    }

    function obtainTablesEmails(){
        //("eduardo cueva" <ecueva@palosanto.com>, <edu19432@hotmail.com>, )
        var table = document.getElementById('grilla');
        var add_text = "";
        for(var i=0; i<table.childNodes.length; i++){
            if(i>0){
                var contact = table.childNodes[i].childNodes[1].firstChild.nodeValue;
                var email = table.childNodes[i].childNodes[2].firstChild.nodeValue;
                if(contact == "-"){
                    add_text += "<"+email+">, ";
                }else{
                    add_text += "\""+contact+"\" "+"<"+email+">, ";
                }
            }
        }
        return add_text;
    }

    function del_email_tab(ind){
        ind++;
        var before_td = 0;
        var band = 0;
        var img = "";
        var on_click_value = 0;
        var table = document.getElementById("grilla");
        for(var i=0; i<table.childNodes.length; i++){
            if(i>0){
                var tr = table.childNodes[i];
                var id = table.childNodes[i].firstChild.firstChild.nodeValue;

                if(id == ind){
                    band = 1;
                    table.removeChild(tr);
                }

            }
        }

        for(var i=0; i<table.childNodes.length; i++){
            if(i>0){
                table.childNodes[i].firstChild.firstChild.nodeValue = i;
                var del_i = i - 1;
                var img2 = table.childNodes[i].childNodes[3].firstChild.childNodes[1];
                img2.setAttribute("onclick","del_email_tab("+del_i+");");
            }
        }
    }