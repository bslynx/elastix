<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.6-3                                               |
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
  $Id: index.php,v 1.1 2009-06-08 03:06:39 Oscar Navarrete onavarrete@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoControlPanel.class.php";

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
    $arrConf['dsn_conn_database1'] = generarDSNSistema('asteriskuser', 'asterisk');
    $pDB1 = new paloDB($arrConf['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConf['dsn_conn_database2']);

    //actions
    $action = getAction();
    $content  = "";

    switch($action){
        case "call":
            $content = callAction($pDB1, $pDB2);
            break;
        case "voicemail":
            $content = voicemailAction($pDB1,$pDB2);
            break;
        case "hangup":
            $content = hangupAction($pDB1, $pDB2);
            break;
        case "refresh":
            $content = waitingChanges("refreshAction", $pDB1, $pDB2);
            break;
        case "savechange":
            $content = savechangeAction($pDB1, $pDB2);
            break;
        case "savechange2":
            $content = savechange2Action($pDB1, $pDB2);
            break;
        case "saveresize":
            $content = saveresizeAction($pDB1, $pDB2);
            break;
        case "loadArea":
            $content = loadAreaAction($pDB1, $pDB2);
            break;
        case "loadArea2":
            $content = loadArea2Action($pDB1, $pDB2);
            break;
        case "saveEdit":
            $content = saveEditAction($pDB1, $pDB2);
            break;
        case "addExttoQueue":
            $content = addExttoQueueAction($pDB1, $pDB2);
            break;
        default: // view_form
            $content = viewFormControlPanel($smarty, $module_name, $local_templates_dir, $pDB1, $pDB2, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function viewFormControlPanel($smarty, $module_name, $local_templates_dir, &$pDB1, &$pDB2, $arrConf, $arrLang)
{
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $oForm = new paloForm($smarty,array());
    $arrDevices = $pControlPanel->getAllDevicesARRAY();
    $arrAreas = $pControlPanel->getDesignArea();
    $arrQueues  = $pControlPanel->getAllQueuesARRAY2();
    $arrDAHDITrunks  = $pControlPanel->getDAHDITrunksARRAY();
    $arrSIPTrunks = $pControlPanel->getSIPTrunksARRAY();
    $arrConferences = $pControlPanel->getConferences();
    $smarty->assign("module_name",$module_name);
    $smarty->assign("arrDevicesExten", isset($arrDevices[1])?$arrDevices[1]:null);
    $smarty->assign("arrDevicesArea1", isset($arrDevices[2])?$arrDevices[2]:null);
    $smarty->assign("arrDevicesArea2", isset($arrDevices[3])?$arrDevices[3]:null);
    $smarty->assign("arrDevicesArea3", isset($arrDevices[4])?$arrDevices[4]:null);
    $smarty->assign("lengthExten", isset($arrDevices[1])?count($arrDevices[1]):null);
    $smarty->assign("lengthArea2", isset($arrDevices[2])?count($arrDevices[2]):null);
    $smarty->assign("lengthArea3", isset($arrDevices[3])?count($arrDevices[3]):null);
    $smarty->assign("lengthArea4", isset($arrDevices[4])?count($arrDevices[4]):null);
    $smarty->assign("arrQueues", isset($arrQueues)?$arrQueues:null);
    $smarty->assign("arrTrunks", $arrDAHDITrunks);
    $smarty->assign("lengthQueues", isset($arrQueues)?count($arrQueues):null);
    $smarty->assign("lengthTrunks", isset($arrDAHDITrunks)?count($arrDAHDITrunks):null);
    $smarty->assign("lengthTrunksSIP", isset($arrSIPTrunks)?count($arrSIPTrunks):null);
    $smarty->assign("arrTrunksSIP", $arrSIPTrunks);
    $smarty->assign("arrConferences", $arrConferences);
    $smarty->assign("lengthConferences", isset($arrConferences)?count($arrConferences):null);
    $i=1;
    foreach($arrAreas as $key => $value){
        $smarty->assign("nameA$i", $value['a.name']);
        $smarty->assign("descripArea$i", $value['a.description']);
        $smarty->assign("height$i", $value['a.height']);
        $smarty->assign("width$i", $value['a.width']);
        $smarty->assign("size$i", $value['a.no_column']);
        $i++;
    }
    //New Feauters
    $totalQueues = 0;
    $arrNumQueues = $pControlPanel->getAsterisk_QueueInfo();
    foreach($arrNumQueues as $key=>$value){
        $totalQueues += $value;
    }
    $smarty->assign("total_queues",$totalQueues);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Control Panel"], $_POST);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function waitingChanges($function, &$pDB1, &$pDB2)
{
    $executed_time =  2; //en segundos
    $max_time_wait = 30; //en segundos
    $data          = null;

    $i = 1;
    while(($i*$executed_time) <= $max_time_wait){
        $return = $function($pDB1, $pDB2);
        $data   = $return['data'];

        //wlog("chat_server/index.php: waitingChanges-$function --> espera número $i, ¿hubo cambio?=$return[there_was_change]");
        if($return['there_was_change']){
            break;
        }
        $i++;
        sleep($executed_time); //cada $executed_time estoy revisando si hay algo nuevo....
    }
   return $data;
}

function callAction(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $number_org = getParameter('extStart');
    $number_dst = getParameter('extFinish');
    if (!is_null($number_org) & !is_null($number_dst)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $pControlPanel->makeCalled($number_org, $number_dst);
    }
    $jsonObject->set_message("");
    return $jsonObject->createJSON();
}

function voicemailAction(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $number_org = getParameter('extStart');
    if (!is_null($number_org)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $number_dst = "*98";
        $pControlPanel->makeCalled($number_org, $number_dst);
    }
    $jsonObject->set_message("");
    return $jsonObject->createJSON();
}

function hangupAction(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $number_org = getParameter('extStart');
    if (!is_null($number_org)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $pControlPanel->hangupCalled($number_org);
    }
    $jsonObject->set_message("");
    return $jsonObject->createJSON();
}

function refreshAction(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $message = $pControlPanel->getAllDevicesXML();
    $arrPrev = array();
    $data = array();

    $session = getChatSession();

    if(isset($session['operator_panel']['prev']))
        $arrPrev = $session['operator_panel']['prev'];
    else{
        $session['operator_panel']['prev'] = $message;
        putChatSession($session);
    }
    $diff = getDifferences($message,$arrPrev);
    if(count($diff) > 0){
        $status = true;
        $i=0;
        foreach($diff as $key => $value){
            foreach($value as $key2 => $value2){
                if($key2 == "activity" || $key2 == "parties"){
                    $data[$i]['Tipo'] = "Conference";
                    $data[$i]['key'] = $message[$key]["numconf"];   
                }/*elseif($key2 == "statusConf"){
                    $data[$i]['Tipo'] = "Conference";
                    $data[$i]['key'] = $arrPrev[$key]["numconf"];
                }*/elseif($key2 == "speak_time"){
                    if($message[$key]["context"] == "macro-dialout-trunk" && $message[$key]["trunk"] != " "){
                         $data[$i]['Tipo'] = "Trunk";
                         $data[$i]['key'] = $message[$key]["user"]."_".$message[$key]["trunk"];
                    }else{
                        $data[$i]['Tipo'] = "Extension";
                        $data[$i]['key'] = $message[$key]["user"];
                    }
                }elseif($key2 == "waiting"){
                    $data[$i]['Tipo'] = "Queue";
                    $data[$i]['key'] = $message[$key]["queueNumber"];
                }
                else{
                    $data[$i]['Tipo'] = "Extension";
                    $data[$i]['key'] = $message[$key]["user"];
                }
                $data[$i]['data'] = array($key2 => $value2);
                $i++;
            }
            if(isset($arrPrev[$key]["trunk"]) && isset($message[$key]["trunk"]))
                if($arrPrev[$key]["trunk"] != " " && $message[$key]["trunk"] == " "){
                    $data[$i]['Tipo'] = "Trunk";
                    $data[$i]['key'] = $arrPrev[$key]["trunk"];
                    $data[$i]['data'] = array("statusTrunk" => "off");
                    $i++;
                }
            if(isset($arrPrev[$key]["numconf"]) && isset($message[$key]["numconf"]) && isset($arrPrev[$key]["parties"]))
                if($arrPrev[$key]["numconf"] != " " && $message[$key]["numconf"] == " " && $arrPrev[$key]["parties"] == "1"." "._tr("Participant")){
                    $data[$i]['Tipo'] = "Conference";
                    $data[$i]['key'] = $arrPrev[$key]["numconf"];
                    $data[$i]['data'] = array("statusConf" => "off");
                    $i++;
                }
        }
        
        $jsonObject->set_status("CHANGED");
        $jsonObject->set_message($data);
    }
    else{
        $status = false;
        $jsonObject->set_message(array());
    }
  // writeLOG("access.log", print_r($data,true));
    $result = array("there_was_change" => $status, "data" => $jsonObject->createJSON());
    return $result;
}

function getDifferences($message,$arrPrev)
{
    $result  = array();
    $session = getChatSession();

    if(count($arrPrev > 0)){
        foreach($message as $key => $value){
            $tmp = array_diff($value,$arrPrev[$key]);
            if(count($tmp)>0)
                $result[$key] = $tmp;
        }
        if(count($result)>0){
            $session['operator_panel']['prev'] = $message;
            putChatSession($session);
        }
        return $result;
    }
    else{
        $session['operator_panel']['prev'] = $message;
        putChatSession($session);
        return $message;
    }
}

function savechangeAction(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $number_org = getParameter('extStart');
    $id_area    = getParameter('area');
    //if (!is_null($number_org) & !is_null($id_area)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $pControlPanel->saveChangeArea($number_org,$id_area);
    //}
    $jsonObject->set_message("");
    return $jsonObject->createJSON();
}


function savechange2Action(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $number_org = getParameter('extStart');
    $number_dst = getParameter('extFinish');
    //if (!is_null($number_org) & !is_null($number_dst)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $pControlPanel->saveChangeArea2($number_org,$number_dst);
    //}
    $jsonObject->set_message("");
    return $jsonObject->createJSON();
}


function saveresizeAction(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $id_area = getParameter('area');
    $type    = getParameter('type');
    $height  = getParameter('height');
    $width   =  getParameter('width');

    if($width>747)
        $num=3;
    elseif($width>559 && $width<748)
        $num=3;
    elseif($width>370 && $width<560)
        $num=2;
    elseif($width>184 && $width<371)
        $num=1;

    if($type!="alsoResize")
        $pControlPanel->updateResizeArea($height,$width,$num,$id_area);
    else
        $pControlPanel->updateResizeArea2($height,$width,$num,$id_area);
    return $jsonObject->createJSON();
}

function loadAreaAction(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $jsonObject->set_message($pControlPanel->getAllAreasXML());
    return $jsonObject->createJSON();
}

function loadArea2Action(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $jsonObject->set_message("");
    return $jsonObject->createJSON();
}

function saveEditAction(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $id_area = getParameter('area');
    $description = getParameter('description');

    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $jsonObject->set_message($pControlPanel->updateDescriptionArea($description,$id_area));
    return $jsonObject->createJSON();
}

function addExttoQueueAction(&$pDB1, &$pDB2)
{
    $jsonObject = new PaloSantoJSON();
    $number_org = getParameter('extStart');
    $queue      = getParameter('queue');

    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $pControlPanel->queueAddMember($queue, $number_org);
    $jsonObject->set_message("");
    return $jsonObject->createJSON();
}

function getChatSession()
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $tmp = $_SESSION;
        session_commit();
    }
    return $tmp;
}

function putChatSession($data)//data es un arreglo
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $_SESSION = $data;
        session_commit();
    }
}

function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("delete")) 
        return "delete";
    else if(getParameter("new_open")) 
        return "view_form";
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("action")=="view_edit")
        return "view_form";
    else if(getParameter("action")=="call")
        return "call";
    else if(getParameter("action")=="voicemail")
        return "voicemail";
    else if(getParameter("action")=="hangup")
        return "hangup";
    else if(getParameter("action")=="refresh")
        return "refresh";
    else if(getParameter("action")=="savechange")
        return "savechange";
    else if(getParameter("action")=="savechange2")
        return "savechange2";
    else if(getParameter("action")=="saveresize")
        return "saveresize";
    else if(getParameter("action")=="loadArea")
        return "loadArea";
    else if(getParameter("action")=="loadArea2")
        return "loadArea2";
    else if(getParameter("action")=="saveEdit")
        return "saveEdit";
    else if(getParameter("action")=="addExttoQueue")
        return "addExttoQueue";
    else
        return "report"; //cancel
}
?>