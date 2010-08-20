<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-7                                               |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.1 2010-01-05 11:01:26 Bruno Macias V. bmacias@elastix.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoACL.class.php";
include_once "libs/paloSantoConfig.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoCalendar.class.php";
    include_once "modules/$module_name/libs/JSON.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "view_form":
            $content = viewForm_NewEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "save_new":
            $content = saveEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "edit":
            $content = viewForm_NewEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "delete":
            $content = deleteEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "save_edit":
            $content = saveEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "get_lang":
            $content = getLanguages($arrLang);
            break;
        case "get_data":
            $content = getDataCalendar($arrLang,$pDB,$module_name);
            break;
        case "get_contacts":
            $content = getContactEmails($arrConf);
            break;
        case "get_num_ext":
            $content = getNumExtesion($arrConf, $pDB, $arrLang);
            break;
        case "setData":
            $content = setDataCalendar($arrLang,$pDB,$arrConf);
            break;
        case "view_box":
            $content = viewBoxCalendar($arrConf,$arrLang,$pDB);
            break;
        case "new_box":
            $content = newBoxCalendar($arrConf,$arrLang,$pDB);
            break;
        case "delete_box":
            $content = deleteBoxCalendar($arrConf,$arrLang,$pDB);
            break;
        case "download_icals":
            $content = download_icals($arrLang,$pDB,$module_name);
            break;
        case "phone_numbers":

            // Include language file for EN, then for local, and merge the two.
            $arrLangModule = NULL;
            include_once("modules/address_book/lang/en.lang");
            $lang_file="modules/address_book/lang/$lang.lang";
            if (file_exists("$base_dir/$lang_file")) {
                $arrLanEN = $arrLangModule;
                include_once($lang_file);
                $arrLangModule = array_merge($arrLanEN, $arrLangModule);
            }
            $arrLang = array_merge($arrLang, $arrLangModule);

            $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
            $arrConfigAsterisk = $pConfig->leer_configuracion(false);
	        //solo para obtener los devices (extensiones) creadas.
            $dsnAsterisk = $arrConfigAsterisk['AMPDBENGINE']['valor']."://".
                           $arrConfigAsterisk['AMPDBUSER']['valor']. ":".
                           $arrConfigAsterisk['AMPDBPASS']['valor']. "@".
                           $arrConfigAsterisk['AMPDBHOST']['valor']."/asterisk";
            $pDB_addressbook = new paloDB($arrConf['dsn_conn_database3']);
            $pDB_acl = new paloDB($arrConf['dsn_conn_database1']);
            $html = report_adress_book($smarty, $module_name, $local_templates_dir, $pDB_addressbook, $pDB_acl, $arrLang, $dsnAsterisk);
$smarty->assign("CONTENT", $html);
$smarty->assign("THEMENAME", $arrConf['mainTheme']);
$smarty->assign("path", "");
$content = $smarty->display("$local_templates_dir/address_book_list.tpl");
            break;
        default: // view_form
            $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function getNameDayToday($arrLang)
{
    $arrDay = array(
        1 => $arrLang["Monday"],
        2 => $arrLang["Tuesday"],
        3 => $arrLang["Wednesday"],
        4 => $arrLang["Thursday"],
        5 => $arrLang["Friday"],
        6 => $arrLang["Saturday"],
        7 => $arrLang["Sunday"]
    );
    $today = date("N");
    return $arrDay[$today];
}

function viewCalendar($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pCalendar = new paloSantoCalendar($pDB);

    $arrForm = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrForm);

    $anio = getParameter("year");
    $mess = getParameter("month");
    $_DATA['year']  = isset($anio)?$anio:date("Y");
    $_DATA['month'] = isset($mess)?$mess:date("n");


    // para lo de new_event.tpl

    $date_ini = getParameter("date");
    $date_end = getParameter("to");
    $options_emails = "";
    $id_event = "";
    //begin, Form data persistence to errors and other events.
    //$action = getParameter("action");
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);
    $title = $arrLang["Add Event"];

    $visibility        = "visibility: hidden;";
    $visibility_repeat = "visibility: hidden;";
    $visibility_emails = "visibility: visible;";
    $visibility_alert  = "display: none;";
    $repeat_date = "";

    $today = date("D");
    $icalFile = $arrLang["Download"]." ".$_SESSION["elastix_user"]." ".$arrLang["Calendar"];
    $smarty->assign("repeat_date", $repeat_date);
    $smarty->assign("visibility", $visibility);
    $smarty->assign("visibility_repeat", $visibility_repeat);
    $smarty->assign("ID", $uid); //persistence id with input hidden in tpl
    $smarty->assign("add_phone",$arrLang["add_phone"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("Hour_ini", $arrLang["Hour_ini"]);
    $smarty->assign("Hour_end", $arrLang["Hour_end"]);
    $smarty->assign("Start_date", $arrLang["Start_date"]);
    $smarty->assign("Notification_Alert", $arrLang["Notification_Alert"]);
    $smarty->assign("new_recording", $arrLang["new_recording"]);
    $smarty->assign("End_date", $arrLang["End_date"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("module_name", $module_name);
    $smarty->assign("notification_email", $arrLang["notification_email"]);
    $smarty->assign("options_emails",$options_emails);
    $smarty->assign("id_event",$id_event);
    $smarty->assign("New_Event",$arrLang["New_Event"]);
    $smarty->assign("Date_event",$arrLang["Date_event"]);
    $smarty->assign("Hour_event",$arrLang["Hour_event"]);
    $smarty->assign("Call_alert",$arrLang["Call_alert"]);
    $smarty->assign("Su",$arrLang["Su"]);
    $smarty->assign("Mo",$arrLang["Mo"]);
    $smarty->assign("Tu",$arrLang["Tu"]);
    $smarty->assign("We",$arrLang["We"]);
    $smarty->assign("Th",$arrLang["Th"]);
    $smarty->assign("Fr",$arrLang["Fr"]);
    $smarty->assign("Sa",$arrLang["Sa"]);
    $smarty->assign("Email",$arrLang["Email"]);
    $smarty->assign("Contact",$arrLang["Contact"]);
    $smarty->assign("visibility_emails",$visibility_emails);
    $smarty->assign("Export_Calendar",$arrLang["Export_Calendar"]);
    $smarty->assign("ical",$icalFile);
    $smarty->assign("NEW", $arrLang["Add Event"]);
    $smarty->assign("SEARCH", $arrLang["Search"]);
    $smarty->assign("module_name", $module_name);
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("MONTH", $arrLang["Month"]);
    $smarty->assign("WEEK", $arrLang["Week"]);
    $smarty->assign("DAY", $arrLang["Day"]);
    $smarty->assign("visibility_alert", $visibility_alert);
    $smarty->assign("share_calendar", $arrLang["share_calendar"]);
    $smarty->assign("other_calendar", $arrLang["other_calendar"]);
    $smarty->assign("enter_emails", $arrLang["enter_emails"]);
    $smarty->assign("Send",$arrLang["Send"]);
    $smarty->assign("formatIcal",$arrLang["ical"]);
    $smarty->assign("LBL_EDIT", $arrLang["Edit Event"]);
    $smarty->assign("Here", $arrLang["Here"]);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Calendar"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name' name='formNewEvent' id='formNewEvent'>".$htmlForm."</form>";

    return $content;
}

function saveEvent($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang){
    $pCalendar = new paloSantoCalendar($pDB);

    $_DATA              = $_POST;
    $action             = getParameter("action");
    $id                 = getParameter("id_event");
    $event              = getParameter("event");
    $date_ini           = getParameter("date");
    $date_end           = getParameter("to");
    $hora               = date('H',strtotime($date_ini));//getParameter("hora1");
    $minuto             = date('i',strtotime($date_ini));//getParameter("minuto1");
    $hora2              = date('H',strtotime($date_end));//getParameter("hora2");
    $minuto2            = date('i',strtotime($date_end));//getParameter("minuto2");
    $repeat             = getParameter("it_repeat");
    $description        = getParameter("description");
    //$asterisk_calls     = getParameter("asterisk_call_me");  // puede ser on o off
    $asterisk_calls     = "";
    $call_to            = getParameter("call_to");
    $notification       = getParameter("notification");      // puede ser on o off 
    $notification_email = getParameter("notification_email"); // si es notification==off => no se toma en cuenta esta variable
    // checkbox days and select repeat each
    $sunday             = getParameter("Sunday");
    $monday             = getParameter("Monday");
    $tuesday            = getParameter("Tuesday");
    $wednesday          = getParameter("Wednesday");
    $thursday           = getParameter("Thursday");
    $friday             = getParameter("Friday");
    $saturday           = getParameter("Saturday");
    $each_repeat        = getParameter("repeat");
/////////////////////last calendar///////////////////
    $reminder           = getParameter("reminder");
/////////////////////////////////////////////////////
    $list               = getParameter("emails");
    $recording          = getParameter("recording");
    $pCalendar = new paloSantoCalendar($pDB);
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);
    $pDB3 = new paloDB($arrConf['dsn_conn_database1']);
    $ext = $pCalendar->obtainExtension($pDB3,$uid);
///////////////////last calendar///////////////////
    if(!isset($repeat) || $repeat == "")
        $repeat = "none";
    if(!isset($each_repeat) || $each_repeat == "")
        $each_repeat = 1;
///////////////////////////////////////////////////
    $event_type = 0;
    $checkbox_days = getCheckDays($sunday,$monday,$tuesday,$wednesday,$thursday,$friday,$saturday);
    $start_event = strtotime($date_ini);
    $end_event = strtotime($date_end);
    $end_event2 = $end_event;
    //validar si la primera fecha es menor que la segunda
    if($event != ""){
        if($start_event <= $end_event){
            if($reminder == "on"){
                $asterisk_calls = $reminder;
                if($asterisk_calls == "on"){ // si es on entonces el campo call_to es vacio
                    if($call_to==null || $call_to==""){
                        $link = "<a href='?menu=userlist'>".$arrLang['user_list']."</a>";
                        $smarty->assign("mb_message", $arrLang['error_ext'].$link);
                        $content = viewForm_NewEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                        return $content;
                    }
                }else{// se asigna una extension cualquiera
                    if($call_to==""){
                        $smarty->assign("mb_message", $arrLang['error_call_to']);
                        $content = viewForm_NewEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                        return $content;
                    }
                }
            }else{
                $call_to = "";
                $asterisk_calls = "off";
                $recording = "";
            }

            if($notification == "on"){ // si ingresa emails o contactos
                //se toma todo y se hace una concatenacion para generar un string 
                //primero se valida si existe algun mail que no tiene 
                $list = htmlspecialchars_decode($list); // codifica los caracteres especiales 
                $notification_email = $list;
            }else{
                $notification_email = "";
                $notification = "off";
            }

            $start = date('Y-m-d',$start_event);
            $end   = date('Y-m-d',$end_event);
////////////////////////////last calendar//////////////////////////////////////
            if(!isset($checkbox_days) || $checkbox_days==""){
                $checkbox_days = getConvertDay($start_event);
            }
//////////////////////////////////////////////////////////////////////////////
            $starttime = date('Y-m-d',$start_event)." ".$hora.":".$minuto;
            $endtime = date('Y-m-d',$end_event2)." ".$hora2.":".$minuto2;
            $day_repeat  = explode(',',$checkbox_days);
            $id_last = "";
            if($repeat == "none"){ //solo un dia
                $event_type = 1;
                $num_frec = 0;
                if(getParameter("save_edit"))
                   $id_last = $id;
                if(getParameter("save_new")){
                    $id_last = $pCalendar->getLastInsertIdEvent();
                   if($id_last==false)
                        $id_last = 1;
                   else 
                        $id_last += 1;
                }
                if($reminder == "on")
                    createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$num_frec,$asterisk_calls,$ext,$call_to,$pDB,$id_last,$arrLang,$arrConf,$recording);
            }
            if($repeat == "each_day"){ //dias que se repiten durante un numero de semanas
                $event_type = 5;
                $num_frec = 7;
                if(getParameter("save_edit"))
                   $id_last = $id;
                else if(getParameter("save_new")){
                        $id_last = $pCalendar->getLastInsertIdEvent();
                        if($id_last==false)
                            $id_last = 1;
                        else 
                            $id_last += 1;
                     }
                if($reminder == "on")
                    createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$num_frec,$asterisk_calls,$ext,$call_to,$pDB,$id_last,$arrLang,$arrConf,$recording);
            }
            if($repeat == "each_month"){ //dias que se repiten durante un numero de meses
                $event_type = 6;
                $num_frec = 30;
                if(getParameter("save_edit"))
                   $id_last = $id;
                else if(getParameter("save_new")){
                        $id_last = $pCalendar->getLastInsertIdEvent();
                        if($id_last==false)
                                $id_last = 1;
                        else 
                                $id_last += 1;
                     }
                if($reminder == "on")
                    createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$num_frec,$asterisk_calls,$ext,$call_to,$pDB,$id_last,$arrLang,$arrConf,$recording);
            }
            
            if(getParameter("save_edit")){ // si se va modificar un evento existente
                $val = $pCalendar->updateEvent($id,$start,$end,$starttime,$event_type,$event,$description,$asterisk_calls,$recording,$call_to,$notification,$notification_email,$endtime,$each_repeat,$checkbox_days);
                if($val == true){
                    $smarty->assign("mb_message", $arrLang['update_successful']);
                    $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                    sendMails($notification_email,$start,$end,$starttime,$event_type,$event,$description, $arrLang, "UPDATE",$endtime,$arrConf,$pDB);
                    return $content;
                }
                else{
                    $smarty->assign("mb_message", $arrLang['error_update']);
                    $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                    return $content;
                }
            }
            else{ // si se va a ingresar un nuevo evento
                $val = $pCalendar->insertEvent($uid,$start,$end,$starttime,$event_type,$event,$description,$asterisk_calls,$recording,$call_to,$notification,$notification_email,$endtime,$each_repeat,$checkbox_days);
    
                if($val == true){
                    $smarty->assign("mb_message", $arrLang['insert_successful']);
                    sendMails($notification_email,$start,$end,$starttime,$event_type,$event,$description, $arrLang, "NEW", $endtime,$arrConf,$pDB);
                    $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                    return $content;
                }
                else{
                    $smarty->assign("mb_message", $arrLang['error_insert']);
                    $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                    return $content;
                }
            }
        }else{
            $smarty->assign("mb_message", $arrLang['error_date']);
            $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            return $content;
        }
    }else{
        $smarty->assign("mb_message", $arrLang['error_eventName']);
        $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
        return $content;
    }
}

function viewForm_NewEvent($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pCalendar = new paloSantoCalendar($pDB);
    $arrForm = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrForm);
    $date_ini = getParameter("date");
    $date_end = getParameter("to");
    $hour1    = date('H',strtotime($date_ini));//getParameter("hora1");
    $minute1  = date('i',strtotime($date_ini));//getParameter("minuto1");
    $hour2    = date('H',strtotime($date_end));//getParameter("hora2");
    $minute2  = date('i',strtotime($date_end));//getParameter("minuto2");
    $options_emails = "";
    $id_event = "";
    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);
    $title = $arrLang["Add Event"];

    $visibility        = "visibility: hidden;";
    $visibility_repeat = "visibility: hidden;";
    $visibility_alert  = "display: none;";
    $repeat_date = "";

    $today = date("D");

    $_DATA['date'] = isset($date_ini)?$date_ini:date("d M Y");
    $_DATA['to']   = isset($date_end)?$date_end:date("d M Y");

    switch($today)
    {
        case "Sun":
            $_DATA['Sunday'] = "on";
            break;
        case "Mon":
            $_DATA['Monday'] = "on";
            break;
        case "Tue":
            $_DATA['Tuesday'] = "on";
            break;
        case "Wed":
            $_DATA['Wednesday'] = "on";
            break;
        case "Thu":
            $_DATA['Thursday'] = "on";
            break;
        case "Fri":
            $_DATA['Friday'] = "on";
            break;
        case "Sat":
            $_DATA['Saturday'] = "on";
            break;
        default: 
            $_DATA['Sunday'] = "on";
            break;
    }

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit") || getParameter("edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $id = getParameter("id_event");
        $id_event = $id;
        $data = $pCalendar->get_event_by_id($id);
        $type_event = $data['it_repeat'];
        $days_repeat = $data['days_repeat'];
        $data['it_repeat'] = returnEventToType($type_event, $arrLang);

        $title = $arrLang["View Event"];

        if($data['notification']=="on"){
            $options_emails = getEmails($data['emails_notification']);
            $visibility = "visibility: visible;";
        }

        if($type_event==5){
            $visibility_repeat = "visibility: visible;";
            $repeat_date = $arrLang["Weeks"];
        }

        if($type_event==6){
            $visibility_repeat = "visibility: visible;";
            $repeat_date = $arrLang["Months"];
        }

        if($days_repeat != ""){
            $arr = getDaysByCheck($days_repeat);
            $data = array_merge($data,$arr);
        }

        if(is_array($data) & count($data)>0)
            $_DATA = $data;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pCalendar->errMsg);
        }
    }

    if(getParameter("edit")){
        $id = getParameter("id_event");
        $id_event = $id;
        $data = $pCalendar->get_event_by_id($id);
        $title = $arrLang["Edit Event"];
        $type_event = $data['it_repeat'];
        $days_repeat = $data['days_repeat'];
        $data['it_repeat'] = returnEventToType($type_event, $arrLang);

        if($data['notification']=="on"){
            $options_emails = getEmails($data['emails_notification']);
            $visibility = "visibility: visible;";
        }

        if($type_event==5){ 
            $visibility_repeat = "visibility: visible;";
            $repeat_date = $arrLang["Weeks"];
        }

        if($type_event==6){ 
            $visibility_repeat = "visibility: visible;";
            $repeat_date = $arrLang["Months"];
        }

        if($days_repeat != ""){
            $arr = getDaysByCheck($days_repeat);
            $data = array_merge($data,$arr);
        }

        if(is_array($data) & count($data)>0){
            $_DATA = $data; 
            $_DATA['date'] = date("d M Y",strtotime($data['date']));
            $_DATA['to'] = date("d M Y",strtotime($data['to']));
        }else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pCalendar->errMsg);
        }
    }
     // para visualizar el email notification
    $smarty->assign("repeat_date", $repeat_date);
    $smarty->assign("visibility", $visibility);
    $smarty->assign("visibility_repeat", $visibility_repeat);
    $smarty->assign("ID", $uid); //persistence id with input hidden in tpl
    $smarty->assign("add_phone",$arrLang["add_phone"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("Hour_ini", $arrLang["Hour_ini"]);
    $smarty->assign("Hour_end", $arrLang["Hour_end"]);
    $smarty->assign("Start_date", $arrLang["Start_date"]);
    $smarty->assign("new_recording", $arrLang["new_recording"]);
    $smarty->assign("End_date", $arrLang["End_date"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("module_name", $module_name);
    $smarty->assign("notification_email", $arrLang["notification_email"]);
    $smarty->assign("options_emails",$options_emails);
    $smarty->assign("id_event",$id_event);
    $smarty->assign("New_Event",$arrLang["New_Event"]);
    $smarty->assign("Date_event",$arrLang["Date_event"]);
    $smarty->assign("Hour_event",$arrLang["Hour_event"]);
    $smarty->assign("Call_alert",$arrLang["Call_alert"]);
    $smarty->assign("Su",$arrLang["Su"]);
    $smarty->assign("Mo",$arrLang["Mo"]);
    $smarty->assign("Tu",$arrLang["Tu"]);
    $smarty->assign("We",$arrLang["We"]);
    $smarty->assign("Th",$arrLang["Th"]);
    $smarty->assign("Fr",$arrLang["Fr"]);
    $smarty->assign("Sa",$arrLang["Sa"]);
    $smarty->assign("visibility_alert", $visibility_alert);


    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_event.tpl",$title, $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'  name='formNewEvent' id='formNewEvent'>".$htmlForm."</form>";

    return $content;
}

function deleteEvent($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang){
    $pCalendar = new paloSantoCalendar($pDB);

    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id_event");
    $data = $pCalendar->getEventById($id);
    sendMails($data['emails_notification'],$data['startdate'],$data['enddate'],$data['starttime'],$data['eventtype'],$data['subject'],$data['description'], $arrLang,"DELETE", $data['endtime'],$arrConf,$pDB);
    $val = $pCalendar->deleteEvent($id);
    if($val == true){
        $smarty->assign("mb_message", $arrLang['delete_successful']);
        $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
        return $content;
    }
    else{
        $smarty->assign("mb_message", $arrLang['error_delete']);
        $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
        return $content;
    }
}

function sendMails($emails,$start,$end,$starttime,$event_type,$event,$description, $arrLang, $type, $endtime,$arrConf,$pDB){
    $pCalendar = new paloSantoCalendar($pDB);
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);
    $pDB3 = new paloDB($arrConf['dsn_conn_database1']);
    $user_name = $pCalendar->getNameUsers($uid,$pDB3);
    //obtain email FROM....
    $From = 'admin@example.com';
    $subject = $arrLang['New_Event']." :".$event;
    if($type == "NEW")
        $subject = $arrLang['New_Event']." :".$event;
    if($type == "UPDATE")
        $subject = $arrLang['Change_Event']." :".$event;
    if($type == "DELETE")
        $subject = $arrLang['Delete_Event']." :".$event;

    $event_type = returnTypeEvent($event_type, $arrLang);

    $startarray = explode(" ",$starttime);
    $endarray   = explode(" ",$endtime);
/*    $msg = " \n";
    $msg.= "$subject\n";
    $msg.= " \n";
    $msg.= $arrLang['Event'].": ".$event."\n";
    $msg.= $arrLang['Date'].": ".$start."\n";
    $msg.= $arrLang['To'].": ".$end."\n";
    $msg.= $arrLang['It repeat'].": ".$event_type."\n";
    $msg.= $arrLang['time'].": ".$startarray[1]." - ".$endarray[1]."\n";
    $msg.= $arrLang['Description'].": ".$description."\n";
    $msg.= "\n\n";
    $msg.= "";
*/
    $emails = str_replace('"',"",$emails);
    $arrEmails = explode(",",$emails);

//////////////////////////////////////////////////////////////////////////////////////////////

$msg = "
<html>
    <head>
        <title>$subject</title>
    </head>
    <body>
        <style>
            .title{
                background-color:#D1E6FA;
                color:#000000;
            }
            .tr{
                background-color:#F1F8FF;
            }
            .td1{
                font-weight: bold;
                color:#b9b2b2; 
                font-size: large;
                width:165px;
            }
            .footer{
                background-color:#EBF5FF;
                color:#b9b2b2;
                font-weight:bolder;
                font-size:12px;
            }
        </style>
        <div>
            <table width='600px'>
                <tr class='title'>
                    <td colspan='2'><center><h1>$subject</h1></center></td>
                </tr>
                <tr class='tr'>
                    <td class='td1'>".$arrLang['Event'].": </td>
                    <td>$event.</td>
                </tr>
                <tr class='tr'>
                    <td class='td1'>".$arrLang['Date'].": </td>
                    <td>".date("d M Y",strtotime($start)).".</td>
                </tr>
                <tr class='tr'>
                    <td class='td1'>".$arrLang['To'].": </td>
                    <td>".date("d M Y",strtotime($end)).".</td>
                </tr>
                <tr class='tr'>
                    <td class='td1'>".$arrLang['time'].": </stronge></td>
                    <td>".$startarray[1]." - ".$endarray[1].".</td>
                </tr>
                <tr class='tr'>
                    <td class='td1'>".$arrLang['Description'].": </td>
                    <td>$description.</td>
                </tr>
                <tr class='tr'>
                    <td class='td1'><stronge>".$arrLang['Organizer'].": </stronge></td>
                    <td><span>$user_name.</span></td>
                </tr>
                <tr class='footer'>
                    <td colspan='2'><center><span>".$arrLang['footer']."</span></center></td>
                </tr>
            </table>
        </div>
    </body>
</html>";
//                 <tr class='tr'>
//                     <td class='td1'>".$arrLang['It repeat'].": </td>
//                     <td>$event_type.</td>
//                 </tr>
//                 <tr class='tr'>
//                     <td class='td1'><stronge>".$arrLang['Confirm_event'].": </stronge></td>
//                     <td><a href='#' target='_blank'>".$arrLang['Confirm']."</a></td>
//                 </tr>
//////////////////////////////////////////////////////////////////////////////////////////////


    for($i=0; $i<count($arrEmails)-1; $i++){
        $To = $arrEmails[$i];

        $posini = strpos($To,"<");
        $posend = strpos($To,">");

        if($posini || $posend)
            $ToSend = substr($To,$posini+1,-1);
        else
            $ToSend = $To;

        $head  = "MIME-Version: 1.0\r\n"; 
        $head .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $head .= "From: $From\r\n";
        $head .= "To: $To\r\n";

        $val = mail($ToSend, $subject, $msg, $head);
    }
}

function getEmails($emails){
    //"eduardo cueva" <ecueva@palosanto.com>, <edu19432@hotmail.com>,
    $emails = htmlspecialchars_decode($emails);
    $emails = str_replace('"',"",$emails);
    $arrEmails = explode(",",$emails);

    $cad_emails = "";
    for($i=0; $i<count($arrEmails)-1; $i++)
        $cad_emails .= "<option value='registed' class='selected'>".htmlspecialchars($arrEmails[$i])."</option>";
    return $cad_emails;
}

function getEmailToTables($emails){
    //"eduardo cueva" <ecueva@palosanto.com>, <edu19432@hotmail.com>,
    $emails = htmlspecialchars_decode($emails);
    //$emails = str_replace('"',"",$emails);
    $arrEmails = explode(",",$emails);
    $i = 0;
    $cad_emails = array();
    for($i=0; $i<count($arrEmails)-1; $i++){
        //"eduardo cueva" <ecueva@palosanto.com>
        $arr_tmp = explode("\"",$arrEmails[$i]);
        $num_email  = "num_email".$i;
        $cont_email = "cont_email".$i;
        $name_email = "name_email".$i;

        $cad_emails[$num_email]  = $i+1;

        if(count($arr_tmp) > 1)
            $cad_emails[$cont_email] = $arr_tmp[1];
        else
            $cad_emails[$cont_email] = "-";

        $pos1 = stripos($arrEmails[$i],"<");
        $pos2 = stripos($arrEmails[$i],">");
        if($pos1 || $pos2)
            $cad_emails[$name_email] = substr($arrEmails[$i],($pos1)+1,($pos2-strlen($arrEmails[$i])));
        else
            $cad_emails[$name_email] = "-";
    }
    $cad_emails['size_emails'] = $i;
    return $cad_emails;
}

function returnTypeEvent($dig, $arrLang){
    $type = "";
    switch($dig){
        case "1":
            $type = $arrLang["No_Repeat"];
            break;
        case "5":
            $type = $arrLang["Each_Week"];
            break;
        case "6":
            $type = $arrLang["Each_Month"];
            break;
        default:
            $type = $arrLang["No_Repeat"];
            break;
    }
    return $type;
}

function returnEventToType($dig, $arrLang){
    $type = "";
    switch($dig){
        case "1":
            $type = "none";
            break;
        case "5":
            $type = "each_day";
            break;
        case "6":
            $type = "each_month";
            break;
        default:
            $type = "none";
            break;
    }
    return $type;
}

function Obtain_UID_From_User($user,$arrConf)
{
    global $arrConf;
    $pdbACL = new paloDB($arrConf['dsn_conn_database1']);
    $pACL = new paloACL($pdbACL);
    $uid = $pACL->getIdUser($user);
    if($uid!=FALSE)
        return $uid;
    else return -1;
}

function getCheckDays($sunday,$monday,$tuesday,$wednesday,$thursday,$friday,$saturday)
{
    $out = "";
    if($sunday == "on")    $out .= "Su,";
    if($monday == "on")    $out .= "Mo,";
    if($tuesday == "on")   $out .= "Tu,";
    if($wednesday == "on") $out .= "We,";
    if($thursday == "on")  $out .= "Th,";
    if($friday == "on")    $out .= "Fr,";
    if($saturday == "on")  $out .= "Sa,";
    return $out;
}

/////////////////////// the last calendar  ///////////////////////////////////////

function getConvertDay($start){
    $ini = date('D', $start);
    $out = "";
    switch($ini){
        case "Sun":
            $out .= "Su,";
            break;
        case "Mon":
            $out .= "Mo,";
            break;
        case "Tue":
            $out .= "Tu,";
            break;
        case "Wed":
            $out .= "We,";
            break;
        case "Thu":
            $out .= "Th,";
            break;
        case "Fri":
            $out .= "Fr,";
            break;
        case "Sat":
            $out .= "Sa,";
            break;
    }
    return $out;
}

//////////////////////////////////////////////////////////////////////////////////

function getDaysByCheck($days,$type=0){
    $arrDays = explode(',',$days);
    $arrOut  = "";
    for($i=0; $i<(count($arrDays)-1); $i++){
        if($type==0){
            switch($arrDays[$i]){
                case "Su":
                    $arrOut['Sunday'] = "on";
                    break;
                case "Mo":
                    $arrOut['Monday'] = "on";
                    break;
                case "Tu":
                    $arrOut['Tuesday'] = "on";
                    break;
                case "We":
                    $arrOut['Wednesday'] = "on";
                    break;
                case "Th":
                    $arrOut['Thursday'] = "on";
                    break;
                case "Fr":
                    $arrOut['Friday'] = "on";
                    break;
                case "Sa":
                    $arrOut['Saturday'] = "on";
                    break;
            }
        }else{
            switch($arrDays[$i]){
                case "Su":
                    $arrOut['Sunday_check'] = "on";
                    break;
                case "Mo":
                    $arrOut['Monday_check'] = "on";
                    break;
                case "Tu":
                    $arrOut['Tuesday_check'] = "on";
                    break;
                case "We":
                    $arrOut['Wednesday_check'] = "on";
                    break;
                case "Th":
                    $arrOut['Thursday_check'] = "on";
                    break;
                case "Fr":
                    $arrOut['Friday_check'] = "on";
                    break;
                case "Sa":
                    $arrOut['Saturday_check'] = "on";
                    break;
            }
        }
    }
    return $arrOut;
}

function getLanguages($arrLang)
{
    $userid = getParameter('userid');
    $json = new Services_JSON();
    return $json->encode($arrLang);
}

function viewBoxCalendar($arrConf,$arrLang,$pDB){
    $pCalendar = new paloSantoCalendar($pDB);
    $id        = getParameter('id_event');
    $action    = getParameter('action');
    $json = new Services_JSON();
    $data = "";

    if($action == "view_box"){
        $data = $pCalendar->get_event_by_id($id);
        $type_event = $data['it_repeat'];
        $days_repeat = $data['days_repeat'];
        $data['it_repeat'] = returnEventToType($type_event, $arrLang);
        $data['visibility'] = "visibility: hidden;";
        $data['visibility_repeat'] = "visibility: hidden;";
        $data['notification_status'] = $data['notification'];
        $title = "View Event";
        $data['title'] = $title;
        // convert times to (d M Y H:i) like (02 Feb 2010 15:25)
        $new_date_ini = $data['starttime'];
        $new_date_end = $data['endtime'];
        $data['date'] = date("d M Y H:i",strtotime($new_date_ini));
        $data['to'] = date("d M Y H:i",strtotime($new_date_end));

        if($data['notification']=="on"){
            $arrContacts = getEmailToTables($data['emails_notification']);
            $data['emails_notification'] = getEmails($data['emails_notification']);
            $data['visibility'] = "visibility: visible;";
            $data = array_merge($data,$arrContacts);
        }else
            $data['size_emails'] = 0;

        if($type_event==5){ 
            $data['visibility_repeat'] = "visibility: visible;";
        }

        if($type_event==6){ 
            $visibility_repeat = "visibility: visible;";
        }

        if($days_repeat != ""){
            $arr = getDaysByCheck($days_repeat,2);
            $data = array_merge($data,$arr);
        }
        $data = array_merge($data,$arrLang);
    }
    return $json->encode($data);
}

function download_icals($arrLang,&$pDB,$module_name){

    $arr_out = getAllDataCalendar($arrLang,$pDB,$module_name);

    header("Cache-Control: private");
    header("Pragma: cache");
    header('Content-Type: application/octec-stream');
    header('Content-disposition: inline; filename="icalout.ics"');
    header('Content-Type: application/force-download');

    /*array(
        'id'    => "1",
        'title' => "event title",
        'start' => "date ini - datetime",
        'end'   => "date end - datetime",
        'allDay'=> "false",
        'url' => "url"
     );*/
    $document_output = "BEGIN:VCALENDAR\nPRODID:-//Elastix Development Department// Elastix 2.0 //EN\nVERSION:2.0\n\n";
    for($i=0; $i<count($arr_out); $i++){
        $start_time = date("Ymd",strtotime($arr_out[$i]['start']))."T".date("Hi",strtotime($arr_out[$i]['start']))."00Z";
        $end_time = date("Ymd",strtotime($arr_out[$i]['end']))."T".date("Hi",strtotime($arr_out[$i]['end']))."00Z";

        $document_output.= "BEGIN:VEVENT\n";
        $document_output.= "DTSTAMP:$start_time\n";
        $document_output.= "CREATED:$start_time\n";
        $document_output.= "UID:$i-".$arr_out[$i]['id']."\n";
        $document_output.= "SUMMARY:".$arr_out[$i]['title']."\n";
        $document_output.= "CLASS:PUBLIC\n";
        $document_output.= "PRIORITY:5\n";
        $document_output.= "DTSTART:$start_time\n";
        $document_output.= "DTEND:$end_time\n";
        $document_output.= "TRANSP:OPAQUE\n";
        $document_output.= "SEQUENCE=0\n";
        $document_output.= "END:VEVENT\n\n";
    }
    $document_output .= "END:VCALENDAR";
    return $document_output;
    //echo($document_output);

}

function newBoxCalendar($arrConf,$arrLang,$pDB){
    $pCalendar = new paloSantoCalendar($pDB);
    $json = new Services_JSON();
    $data = "";

    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);
    $data['uid'] = $uid;
    $data['title'] = $arrLang["Add Event"];
    // convert times to (d M Y) like (02 Feb 2010)
    $data['now'] = date("d M Y H:i");
    $data['dayLe'] = date("D");

    $data['hour'] = date("H");
    $data['minute'] = date("i");

    $data = array_merge($data,$arrLang);

    return $json->encode($data);
}

function deleteBoxCalendar($arrConf,$arrLang,$pDB){
    $pCalendar = new paloSantoCalendar($pDB);
    $id = getParameter('id_event');
    $json = new Services_JSON();
    $data = $pCalendar->getEventById($id);
    $dir_outgoing = $arrConf['dir_outgoing'];
    $val = $pCalendar->deleteEvent($id);
    if($val == true){
        $data["error_delete_JSON"] = $arrLang['delete_successful'];
        $data["error_delete_status"] = "on"; 
        sendMails($data['emails_notification'],$data['startdate'],$data['enddate'],$data['starttime'],$data['eventtype'],$data['subject'],$data['description'], $arrLang,"DELETE", $data['endtime'],$arrConf,$pDB);
        exec("rm -f $dir_outgoing/event_{$id}*.call");
    }
    else{
        $data["error_delete_JSON"] = $arrLang['error_delete'];
        $data["error_delete_status"] = "off"; 
    }

    return $json->encode($data);
}

function getNumExtesion($arrConf,&$pDB,$arrLang){
    $pCalendar = new paloSantoCalendar($pDB);
    $uid = getParameter('userid');
    $pDB3 = new paloDB($arrConf['dsn_conn_database1']);
    $ext = $pCalendar->obtainExtension($pDB3,$uid);
    $json = new Services_JSON();
    if(empty($ext)) $ext = "empty";
    $arr = array("ext" => $ext);
    $arrLang = array_merge($arrLang,$arr);
    return $json->encode($arrLang);
}

function getAllDataCalendar($arrLang,&$pDB,$module_name){
    $pCalendar = new paloSantoCalendar($pDB);

    //$arrDates = $pCalendar->getAllEvents();
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);
    $arrDates = $pCalendar->getAllEventsByUid($uid);

    $arr = array();

    $j=0;
    $k=0;
    $arr = "";
    while($j < count($arrDates)){
        $event_type = $arrDates[$j]['eventtype'];
        $arr1 = "";

        if($event_type == 1){
            $arr1 = array(
                        'id'    => $arrDates[$j]['id'],
                        'title' => $arrDates[$j]['subject'],
                        'start' => $arrDates[$j]['starttime'],
                        'end'   => $arrDates[$j]['endtime'],
                        'allDay'=> false,
                        //'url' => "?menu=".$module_name."&action=view&id_event=".$arrDates[$j]['id']
                        'url' => "getDataAjaxForm('menu=".$module_name."&action=view_box&rawmode=yes&id_event=".$arrDates[$j]['id']."');"
                        );
            $arr[$k] = $arr1;
            $k += 1;
        }

        if($event_type == 5){
            $each_repeat = $arrDates[$j]['each_repeat'];
            $day_repeat  = explode(',',$arrDates[$j]['days_repeat']);
            $starttime      = $arrDates[$j]['starttime'];
            $endtime        = $arrDates[$j]['endtime'];
            $type = 7;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }

        if($event_type == 6){
            $each_repeat = $arrDates[$j]['each_repeat'];
            $day_repeat  = explode(',',$arrDates[$j]['days_repeat']);
            $starttime      = $arrDates[$j]['starttime'];
            $endtime        = $arrDates[$j]['endtime'];
            $type = 30;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }
        $j++;
    }
    return $arr;
}

function createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$type,$asterisk_call_me,$ext,$call_to,$pDB,$id_event,$arrLang,$arrConf,$recording){
    $day_start      = date("D",strtotime("$starttime"));
    $day_end        = date("D",strtotime("$endtime"));
    $hour_start     = date("H:i",strtotime("$starttime"));
    $hour_end       = date("H:i",strtotime("$endtime"));
    $day_start_dig  = convertDayToInt($day_start);
    $day_end_dig    = convertDayToInt($day_end);
    $FechaInicio = "";
    $dir_outgoing = $arrConf['dir_outgoing'];
    $sDirectorioBase = $arrConf['sDirectorioBase'];
    $last_day_tmp   = $starttime;
    $m = 0;
    $cont = 0;

    for($i=0; $i<$each_repeat; $i++){// vamos a escribir el numero de eventos que se repiten
        $l = 0;
        while($l < count($day_repeat)-1){// recorremos el arreglo de dias a repetir(Mo,Tu,Fr)
            $day_dig = convertDayToInt($day_repeat[$l]);
            if($i == 0){// si es la primera semana que se va a repetir debemos tomar en cuenta q dia se va a colocar primero deacuerdo a su prioridad

                if($day_start_dig <= $day_dig){// fecha inicial <= dia inicial (Su, Mo,..)
                    if((count($day_repeat)-1) == 1){// si se va a repetir un solo dia a la semana
                        $rest = $day_dig - $day_start_dig;
                        // si es el mismo dia entonces no se suma los n dias
                        //if($rest == 0)
                            $sum_days = $rest;
                        //else
                            //$sum_days = $rest;// + $type;
                        $start = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_start;
                        $end = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_end;
                    }else{// si se repite mas de un dia a la semana
                        $rest = $day_dig - $day_start_dig;
                         // si es el mismo dia entonces no se suma los n dias
                        //if($rest == 0)
                            //$sum_days = $rest;
                        //else
                            $sum_days = $rest;
                        $start = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_start;
                        $end = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_end;
                    }
                    $last_day_tmp = $start;
                    $FechaInicio = $start.":00";
                    // crea el archivo de audio
                    createAudioFiles($asterisk_call_me,$ext,$call_to,$pDB,$id_event,$arrLang,$dir_outgoing,$sDirectorioBase,$cont,$FechaInicio,$recording);
                }
                else{// ESPECIFICAR SI SOLO HAY UN DIA 
                    $m=1;
                }
            }else{
                $last_day = date("D",strtotime("$last_day_tmp"));
                $last_day = convertDayToInt($last_day);
                $sum = $day_dig - $last_day;
                if($i > 1 && $m == 1){
                    $m = 0;
                    $i--;
                }
                if((count($day_repeat)-1) == 1){
                     $start = date("Y-m-d",strtotime("$last_day_tmp + $type days"))." ".$hour_start;
                }
                else{
                    if($sum >= 0){
                        $start = date("Y-m-d",strtotime("$last_day_tmp + $sum days"))." ".$hour_start;
                    }else{
                        if($type == 30){
                            $sum += $type;
                            $start_tmp = date("D",strtotime("$last_day_tmp + $sum days"));
                            $new_day_tmp = convertDayToInt($start_tmp);// se vuelve a convertir en dias para verificar si el dia que cae en el mes es correcto ya que si no lo es entonces son meses con menos de 30 dias
                            $dayToSum = $new_day_tmp - $day_dig;
                            if($dayToSum >= 0){
                                $sum -= $dayToSum;
                            }else{
                                $sum = $dayToSum * (-1);
                            }
                        }
                        else{
                            $sum += $type;
                        }
                        $start = date("Y-m-d",strtotime("$last_day_tmp + $sum days"))." ".$hour_start;

                    }
                }
                $end = date("Y-m-d",strtotime("$start"))." ".$hour_end;
                if($end <= $endtime){
                    $FechaInicio = $start.":00";
                    // crea el archivo de audio
                    createAudioFiles($asterisk_call_me,$ext,$call_to,$pDB,$id_event,$arrLang,$dir_outgoing,$sDirectorioBase,$cont,$FechaInicio,$recording);
                }
                $last_day_tmp = $start;
            }

            $l++;
            $cont++;
        }
    }
}

function createAudioFiles($asterisk_call,$ext,$call_to,$pDB,$id_event,$arrLang,$dir_outgoing,$sDirectorioBase,$i,$FechaInicio,$recording){
    $pCalendar = new paloSantoCalendar($pDB);

    $result = "";
    $iRetries = 2;

    if($asterisk_call=="on"){
        //Obtener datos sobre quien esta usando el sistema
        //Channel, description, extension
        $result = $pCalendar->Obtain_Protocol($ext);
        $result['number'] = $result['id'];
    }
    else{
        if($call_to!=""){
            $result = $pCalendar->Obtain_Protocol($ext);
            $result['number'] = $call_to;
        }
    }

    if($result!=FALSE){
        $sContenido =   //"Channel: $sTrunk/$tuplaTelf[phone]\n".
                        //"Channel: {$result['dial']}\n".
                        "Channel: Local/{$result['number']}@from-internal\n".
                        "CallerID: Calendar Event <{$result['id']}>\n".
                        "MaxRetries: $iRetries\n".
                        "RetryTime: 60\n".
                        "WaitTime: 30\n".
                        "Context: calendar-event\n".
                        "Extension: *7899\n".
                        "Priority: 1\n".
                        "Set: FILE_CALL=custom/{$result['id']}/$recording\n".
                        "Set: ID_EVENT_CALL=$id_event\n";
    }

    if($sContenido!=""){
        $filename = "event_{$id_event}_{$i}.call";
        $filename_create = $dir_outgoing."/event_{$id_event}_{$i}.call";

        if(file_exists($filename_create)) //si existe se elimina el archivo
            unlink($filename_create);

        $hArchivo = fopen("$sDirectorioBase/$filename", 'w');
        if (!$hArchivo) {
            $bExito = FALSE; 
            //$pDB->errMsg = $arrLang["Can not create called file"]." $filename";
            break;
        }
        else {
            fwrite($hArchivo, $sContenido);
            fclose($hArchivo);
            system("touch -d '$FechaInicio' $sDirectorioBase/$filename");
            system("mv $sDirectorioBase/$filename $dir_outgoing/");
        }
    }

}

function getDataCalendar($arrLang,&$pDB,$module_name){
    $pCalendar = new paloSantoCalendar($pDB);
    $start = getParameter('start');
    $end = getParameter('end');
    $uid = getParameter('uid');
    $start_time = date('Y-m-d', $start);
    $end_time = date('Y-m-d', $end);

    $year = date('Y');
    $month = date('m');
    $day = date('d');

    $arrDates = $pCalendar->getEventByDate($start_time, $end_time, $uid);

    $arr = array();

    $j=0;
    $k=0;
    $arr = "";
    while($j < count($arrDates)){
        $event_type = $arrDates[$j]['eventtype'];
        $arr1 = "";

        if($event_type == 1){
            $arr1 = array(
                        'id'    => $arrDates[$j]['id'],
                        'title' => $arrDates[$j]['subject'],
                        'start' => $arrDates[$j]['starttime'],
                        'end'   => $arrDates[$j]['endtime'],
                        'allDay'=> false,
                        //'url' => "?menu=".$module_name."&action=view&id_event=".$arrDates[$j]['id']
                        'url' => "getDataAjaxForm('menu=".$module_name."&action=view_box&rawmode=yes&id_event=".$arrDates[$j]['id']."');"
                        );
            $arr[$k] = $arr1;
            $k += 1;
        }

        if($event_type == 5){
            $each_repeat = $arrDates[$j]['each_repeat'];
            $day_repeat  = explode(',',$arrDates[$j]['days_repeat']);
            $starttime      = $arrDates[$j]['starttime'];
            $endtime        = $arrDates[$j]['endtime'];
            $type = 7;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }

        if($event_type == 6){
            $each_repeat = $arrDates[$j]['each_repeat'];
            $day_repeat  = explode(',',$arrDates[$j]['days_repeat']);
            $starttime      = $arrDates[$j]['starttime'];
            $endtime        = $arrDates[$j]['endtime'];
            $type = 30;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }
        $j++;
    }
    $json = new Services_JSON();
    $arrLanJSON = $json->encode($arr);
    return $arrLanJSON;
}

function setDataCalendar($arrLang,$pDB,$arrConf){
    $action    = getParameter('action');
    $days      = getParameter('days');
    $minutes   = getParameter('minutes');
    $id        = getParameter('id');
    $dateIni   = getParameter('dateIni');
    $dateEnd   = getParameter('dateEnd');
    $pCalendar = new paloSantoCalendar($pDB);
    $Initial   = explode(" ",$dateIni);
    $Finally   = explode(" ",$dateEnd);
    $hour_ini = date("H:i",strtotime($Initial[4]));
    $hour_end = date("H:i",strtotime($Finally[4]));
    $event = $pCalendar->getEventById($id);
    $start = $event['startdate'];
    $end = $event['enddate'];
    $checkbox_days = "";
    /*if($days >= 0){
        $startdate = date("Y-m-d",strtotime("$start + $days days"));
        $enddate = date("Y-m-d",strtotime("$end + $days days"));
    }else{
        $num_days = explode("-",$days);
        $days = $num_days[1];
        $startdate = date("Y-m-d",strtotime("$start - $days days"));
        $enddate = date("Y-m-d",strtotime("$end - $days days"));
    }*/
    $startdate = date("Y-m-d",strtotime($dateIni));
    $enddate   = date("Y-m-d",strtotime($dateEnd));
    $starttime = $startdate." ".$hour_ini;
    $endtime   = $enddate." ".$hour_end;

    // obtain data to create audio files
    $arrResult = $pCalendar->getEventById($id);
    // first remove the old audio files
    $dir_outgoing = $arrConf['dir_outgoing'];
////////////////las calendar //////////////////////////////////////
    if(isset($arrResult['call_to']) && $arrResult['call_to'] != "")
////////////////////////////////////////////////////////////////////
        exec("rm -f $dir_outgoing/event_{$id}*.call");

    $uid = $arrResult['uid'];
    $pDB3 = new paloDB($arrConf['dsn_conn_database1']);
    $ext = $pCalendar->obtainExtension($pDB3,$uid);

    $each_repeat = $arrResult['each_repeat'];
    $day_repeat = explode(',',$arrResult['days_repeat']);


    if($arrResult['eventtype'] == 1)
        $num_frec = 0;
    else{ if($arrResult['eventtype'] == 5){
            $num_frec = 7;
          }else{
            $num_frec = 30;
          }
    }
/////////////////////// last calendar //////////////////////////////
    if($arrResult['eventtype'] == 1){
        $startdateTime = strtotime($startdate);
        $checkbox_days = getConvertDay($startdateTime);
        $day_repeat  = explode(',',$checkbox_days);
    }
////////////////////////////////////////////////////////////////////
    $asterisk_calls = $arrResult['asterisk_call'];
    //$ext = $arrResult['call_to'];
    $call_to = $arrResult['call_to'];
    $recording = $arrResult['recording'];


////////////////las calendar //////////////////////////////////////
    if(isset($arrResult['call_to']) && $arrResult['call_to'] != "")
////////////////////////////////////////////////////////////////////
        createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$num_frec,$asterisk_calls,$ext,$call_to,$pDB,$id,$arrLang,$arrConf,$recording);


    $val = $pCalendar->updateDateEvent($id,$startdate,$enddate,$starttime,$endtime, $checkbox_days);

    if($val)
        return $arrLang['update_successful'];
    else 
        return $arrLang['error_update'];
}

function updateAudioFiles($id_event,$arrLang,$dir_outgoing,$sDirectorioBase){
    $pCalendar = new paloSantoCalendar($pDB);
        $filename = "event_{$id_event}_{$i}.call";
        $filename_create = $dir_outgoing."/event_{$id_event}_{$i}.call";
        exec("rm -f $dir_outgoing/event_{$id}*.call");
        if(file_exists($filename_create)) //si existe se elimina el archivo
            unlink($filename_create);

}

function getContactEmails($arrConf)
{
    $userid = getParameter('userid');
    $parameters = explode("-", $userid);
    $tag = $parameters[1];
    $userid = $parameters[0];
    $pDB  = new paloDB($arrConf['dsn_conn_database']);
    $pDB1 = new paloDB($arrConf['dsn_conn_database3']);

    $pCalendar = new paloSantoCalendar($pDB);
    $salida = $pCalendar->getContactByTag($pDB1, $tag, $userid);

    /*for($i=0; $i<count($salida); $i++){
        //$salida[$i]['caption'] = htmlspecialchars_decode($salida[$i]['caption']);
        $email = $salida[$i]['caption'];
        $email = str_replace('&lt;',"<",$email);
        $email = str_replace('&gt;',">",$email);
        $salida[$i]['caption'] = $email;
    }*/

    // se instancia a JSON
    $json = new Services_JSON();
    return $json->encode($salida);
}

function getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,&$k,&$arr,$arrDates,$type,$module_name){
    $day_start      = date("D",strtotime("$starttime"));
    $day_end        = date("D",strtotime("$endtime"));
    $hour_start     = date("H:i",strtotime("$starttime"));
    $hour_end       = date("H:i",strtotime("$endtime"));
    $day_start_dig  = convertDayToInt($day_start);
    $day_end_dig    = convertDayToInt($day_end);
    $last_day_tmp   = $starttime;
    $m = 0;
    for($i=0; $i<$each_repeat; $i++){// vamos a escribir el numero de eventos que se repiten
        $l = 0;
        while($l < count($day_repeat)-1){// recorremos el arreglo de dias a repetir(Mo,Tu,Fr)
            $day_dig = convertDayToInt($day_repeat[$l]);
            if($i == 0){// si es la primera semana que se va a repetir debemos tomar en cuenta q dia se va a colocar primero deacuerdo a su prioridad

                if($day_start_dig <= $day_dig){// fecha inicial <= dia inicial (Su, Mo,..)
                    if((count($day_repeat)-1) == 1){// si se va a repetir un solo dia a la semana
                        $rest = $day_dig - $day_start_dig;
                        // si es el mismo dia entonces no se suma los n dias
                        //if($rest == 0)
                            $sum_days = $rest;
                        //else
                            //$sum_days = $rest;// + $type;
                        $start = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_start;
                        $end = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_end;
                    }else{// si se repite mas de un dia a la semana
                        $rest = $day_dig - $day_start_dig;
                         // si es el mismo dia entonces no se suma los n dias
                        //if($rest == 0)
                            //$sum_days = $rest;
                        //else
                            $sum_days = $rest;
                        $start = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_start;
                        $end = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_end;
                    }
                    $last_day_tmp = $start;
                    $arr1 = array(
                        'id'    => $arrDates[$j]['id'],
                        'title' => $arrDates[$j]['subject'],
                        'start' => $start,
                        'end'   => $end,
                        'allDay'=> false,
                        'url'   => "getDataAjaxForm('menu=".$module_name."&action=view_box&rawmode=yes&id_event=".$arrDates[$j]['id']."');"
                        );
                    $last_day_tmp = $start;
                    $arr[$k] = $arr1;
                    $k += 1;

                }
                else{// ESPECIFICAR SI SOLO HAY UN DIA 
                    $m=1;
                }
            }else{
                $last_day = date("D",strtotime("$last_day_tmp"));
                $last_day = convertDayToInt($last_day);
                $sum = $day_dig - $last_day;
                if($i > 1 && $m == 1){
                    $m = 0;
                    $i--;
                }
                if((count($day_repeat)-1) == 1){
                     $start = date("Y-m-d",strtotime("$last_day_tmp + $type days"))." ".$hour_start;
                }
                else{
                    if($sum >= 0){
                        $start = date("Y-m-d",strtotime("$last_day_tmp + $sum days"))." ".$hour_start;
                    }else{
                        if($type == 30){
                            $sum += $type;
                            $start_tmp = date("D",strtotime("$last_day_tmp + $sum days"));
                            $new_day_tmp = convertDayToInt($start_tmp);// se vuelve a convertir en dias para verificar si el dia que cae en el mes es correcto ya que si no lo es entonces son meses con menos de 30 dias
                            $dayToSum = $new_day_tmp - $day_dig;
                            if($dayToSum >= 0){
                                $sum -= $dayToSum;
                            }else{
                                $sum = $dayToSum * (-1);
                            }
                        }
                        else{
                            $sum += $type;
                        }
                        $start = date("Y-m-d",strtotime("$last_day_tmp + $sum days"))." ".$hour_start;

                    }
                }
                $end = date("Y-m-d",strtotime("$start"))." ".$hour_end;
                if($end <= $endtime){
                    $arr1 = array(
                        'id'    => $arrDates[$j]['id'],
                        'title' => $arrDates[$j]['subject'],
                        'start' => $start,
                        'end'   => $end,
                        'allDay'=> false,
                        'url'   => "getDataAjaxForm('menu=".$module_name."&action=view_box&rawmode=yes&id_event=".$arrDates[$j]['id']."');"
                        );
                    $arr[$k] = $arr1;
                    $k += 1;
                }
                $last_day_tmp = $start;
            }
            $l++;
        }
    }
}

function convertDayToInt($day)
{
    switch($day){
        case "Sun":
            return 0;
            break;
        case "Mon":
            return 1;
            break;
        case "Tue":
            return 2;
            break;
        case "Wed":
            return 3;
            break;
        case "Thu":
            return 4;
            break;
        case "Fri":
            return 5;
            break;
        case "Sat":
            return 6;
            break;
        case "Su":
            return 0;
            break;
        case "Mo":
            return 1;
            break;
        case "Tu":
            return 2;
            break;
        case "We":
            return 3;
            break;
        case "Th":
            return 4;
            break;
        case "Fr":
            return 5;
            break;
        case "Sa":
            return 6;
            break;
    }
}

function createFieldForm($arrLang)
{
    for($i=0; $i<60; $i++){
        if($i < 10) $arrMin["0$i"] = "0$i";
        else $arrMin[$i] = $i;
    }

    for($i=0; $i<24; $i++){
        if($i < 10) $arrHou["0$i"] = "0$i";
        else $arrHou[$i] = $i;
    }

    $arrRepeat= array(
        "none"      => $arrLang["No_Repeat"],
        "each_day"  => $arrLang["Each_Week"],
        "each_month"=> $arrLang["Each_Month"],
    );

    $repeat = "";
    for($i=1; $i<=30; $i++)
        $repeat[$i] = $i;

    $pCalendar = new paloSantoCalendar($pDB);
    $arrRecording = $pCalendar->Obtain_Recordings_Current_User();

    $arrFields = array(
            "event"   => array(      "LABEL"                  => $arrLang["Name"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:230px", "id" => "event"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "date"   => array(      "LABEL"                  => $arrLang["Date"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M", "style" => "width:80px"),
                                            "VALIDATION_TYPE"        => "",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            /*"hora1"   => array(      "LABEL"                  => "",
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrHou,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "minuto1"   => array(      "LABEL"                  => "",
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrMin,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "hora2"   => array(      "LABEL"                  => "",
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrHou,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "minuto2"   => array(      "LABEL"                  => "",
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrMin,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),*/
            "to"   => array(      "LABEL"                  => $arrLang["To"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M"),
                                            "VALIDATION_TYPE"        => "",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "hora2"   => array(      "LABEL"                  => "",
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrHou,
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "minuto2"   => array(      "LABEL"                  => "",
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrMin,
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "it_repeat"   => array(      "LABEL"                  => $arrLang["It repeat"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrRepeat,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "description"   => array(      "LABEL"                  => $arrLang["Description"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "COLS"                   => "30",
                                            "ROWS"                   => "4",
                                            "EDITABLE"               => "si",
                                            ),
/*            "asterisk_call_me"   => array(      "LABEL"                  => $arrLang["Asterisk Call Me"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),*/
            "call_to"   => array(      "LABEL"                  => $arrLang["Call to"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:70px","id"=>"call_to"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "recording"   => array(         "LABEL"                  => $arrLang["Recording"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrRecording,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "notification"   => array(      "LABEL"                  => $arrLang["notification"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "Monday"   => array(      "LABEL"                  => $arrLang["Monday"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "Tuesday"   => array(      "LABEL"                  => $arrLang["Tuesday"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "Wednesday"   => array(      "LABEL"                  => $arrLang["Wednesday"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "Thursday"   => array(      "LABEL"                  => $arrLang["Thursday"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "Friday"   => array(      "LABEL"                  => $arrLang["Friday"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "Saturday"   => array(      "LABEL"                  => $arrLang["Saturday"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "Sunday"   => array(      "LABEL"                  => $arrLang["Sunday"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "repeat"   => array(         "LABEL"                  => $arrLang["repeat"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $repeat,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "reminder"   => array(      "LABEL"                  => $arrLang["active_foneCall"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),

            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("action")=="new")
        return "view_form";
    else if(getParameter("action")=="save_edit")
        return "save_edit";
    else if(getParameter("action")=="set_data")
        return "setData";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("delete")) 
        return "delete";
    else if(getParameter("new_open")) 
        return "view_form";
    else if(getParameter("action")=="new_open") 
        return "view_form";
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("edit"))
        return "edit";
    else if(getParameter("action")=="edit")
        return "edit";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("action")=="view_edit")
        return "view_form";
    else if(getParameter("action")=="get_lang")
        return "get_lang";
    else if(getParameter("action")=="get_data")
        return "get_data";
    else if(getParameter("action")=="get_contacts")
        return "get_contacts";
    else if(getParameter("action")=="get_num_ext")
        return "get_num_ext";
    else if(getParameter("action")=="view_box")
        return "view_box";
    else if(getParameter("action")=="new_box")
        return "new_box";
    else if(getParameter("action")=="delete_box")
        return "delete_box";
    else if(getParameter("action")=="phone_numbers")
        return "phone_numbers";
    else if(getParameter("action")=="download_icals")
        return "download_icals";
    else
        return "report"; //cancel
}

function report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $dsnAsterisk)
{
    include_once "modules/address_book/libs/paloSantoAdressBook.class.php";

    if(isset($_POST['select_directory_type']) && $_POST['select_directory_type']=='External')
    {
        $smarty->assign("external_sel",'selected=selected');
        $directory_type = 'external';
    }
    else{
        $smarty->assign("internal_sel",'selected=selected');
        $directory_type = 'internal';
    }

    $arrComboElements = array(  "name"        =>$arrLang["Name"],
                                "telefono"    =>$arrLang["Phone Number"]);

    if($directory_type=='external')
        $arrComboElements["last_name"] = $arrLang["Last Name"];

    $arrFormElements = array(   "field" => array(   "LABEL"                  => $arrLang["Filter"],
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrComboElements,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),

                                "pattern" => array( "LABEL"          => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "INPUT_EXTRA_PARAM"      => ""),
                                );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("CSV", $arrLang["CSV"]);
    $smarty->assign("module_name", $module_name);

    $smarty->assign("Phone_Directory",$arrLang["Phone Directory"]);
    $smarty->assign("Internal",$arrLang["Internal"]);
    $smarty->assign("External",$arrLang["External"]);

    $field   = NULL;
    $pattern = NULL;

    if(isset($_POST['field']) and isset($_POST['pattern'])){
        $field      = $_POST['field'];
        $pattern    = $_POST['pattern'];
    }

    $startDate = $endDate = date("Y-m-d H:i:s");

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter_adress_book.tpl", "", $_POST);

    $padress_book = new paloAdressBook($pDB);
    $p_book  = new paloAdressBook($pDB_2);
    $id_user = $p_book->getIdUser($_SESSION["elastix_user"]);

    if($directory_type=='external')
        $total = $padress_book->getAddressBook(NULL,NULL,$field,$pattern,TRUE,$id_user);
    else
        $total = $padress_book->getDeviceFreePBX($dsnAsterisk, NULL,NULL,$field,$pattern,TRUE);

    $total_datos = $total[0]["total"];
    //Paginacion
    $limit  = 20;
    $total  = $total_datos;

    $oGrid  = new paloSantoGrid($smarty);
    $offset = $oGrid->getOffSet($limit,$total,(isset($_GET['nav']))?$_GET['nav']:NULL,(isset($_GET['start']))?$_GET['start']:NULL);

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;

    $url = "?menu=$module_name&filter=$pattern";
    $smarty->assign("url", $url);
    //Fin Paginacion

    if($directory_type=='external')
        $arrResult =$padress_book->getAddressBook($limit, $offset, $field, $pattern, FALSE, $id_user);
    else
        $arrResult =$padress_book->getDeviceFreePBX($dsnAsterisk, $limit,$offset,$field,$pattern);

    $arrData = null;
    if(is_array($arrResult) && $total>0){
        $arrMails = array();

        if($directory_type=='internal')
            $arrMails = $padress_book->getMailsFromVoicemail();

        foreach($arrResult as $key => $adress_book){
            if($directory_type=='external')
                $email = $adress_book['email'];
            else if(isset($arrMails[$adress_book['id']]))
                $email = $arrMails[$adress_book['id']];
            else $email = '';

            $arrTmp[0]  = ($directory_type=='external')?"{$adress_book['last_name']} {$adress_book['name']}":$adress_book['description'];
            $number = ($directory_type=='external')?$adress_book['telefono']:$adress_book['id'];
            $arrTmp[1]  = "<a href='javascript:return_phone_number(\"$number\", \"$directory_type\", \"{$adress_book['id']}\")'>$number</a>";
            $arrTmp[2]  = $email;
            $arrData[]  = $arrTmp;
        }
    }
    if($directory_type=='external')
        $name = "<input type='submit' name='delete' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the contact."]}');\" />";
    else $name = "";

    $arrGrid = array(   "title"    => $arrLang["Address Book"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "columns"  => array(0 => array("name"      => $arrLang["Name"],
                                                    "property1" => ""),
                                            1 => array("name"      => $arrLang["Phone Number"],
                                                    "property1" => ""),
                                            2=> array("name"      => $arrLang["Email"],
                                                    "property1" => ""),
                                        )
                    );

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form method='post' style='margin-bottom: 0pt;' action='?menu=$module_name&action=phone_numbers&rawmode=yes'>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    return $contenidoModulo;
}

?>
