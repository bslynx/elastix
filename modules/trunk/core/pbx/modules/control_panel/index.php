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
            $content = refreshAction($pDB1, $pDB2);
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
    $arrTrunks  = $pControlPanel->getAllTrunksARRAY();

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
    $smarty->assign("arrTrunks", $arrTrunks);
    $smarty->assign("lengthQueues", isset($arrQueues)?count($arrQueues):null);
    $smarty->assign("lengthTrunks", isset($arrTrunks)?count($arrTrunks):null);
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

function callAction(&$pDB1, &$pDB2)
{
    $number_org = getParameter('extStart');
    $number_dst = getParameter('extFinish');
    if (!is_null($number_org) & !is_null($number_dst)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $pControlPanel->makeCalled($number_org, $number_dst);
    }
    return "";
}

function voicemailAction(&$pDB1, &$pDB2)
{
    $number_org = getParameter('extStart');
    if (!is_null($number_org)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $number_dst = "*98";
        $pControlPanel->makeCalled($number_org, $number_dst);
    }
    return "";
}

function hangupAction(&$pDB1, &$pDB2)
{
    $number_org = getParameter('extStart');
    if (!is_null($number_org)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $pControlPanel->hangupCalled($number_org);
    }
    return "";
}

function refreshAction(&$pDB1, &$pDB2)
{
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    return $pControlPanel->getAllDevicesXML();
}

function savechangeAction(&$pDB1, &$pDB2)
{
    $number_org = getParameter('extStart');
    $id_area    = getParameter('area');
    //if (!is_null($number_org) & !is_null($id_area)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $pControlPanel->saveChangeArea($number_org,$id_area);
    //}
    return "";
}


function savechange2Action(&$pDB1, &$pDB2)
{
    $number_org = getParameter('extStart');
    $number_dst = getParameter('extFinish');
    //if (!is_null($number_org) & !is_null($number_dst)){
        $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
        $pControlPanel->saveChangeArea2($number_org,$number_dst);
    //}
    return "";
}


function saveresizeAction(&$pDB1, &$pDB2)
{
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
    return "";
}

function loadAreaAction(&$pDB1, &$pDB2)
{
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    return $pControlPanel->getAllAreasXML();
}

function loadArea2Action(&$pDB1, &$pDB2)
{
    return "";
}

function saveEditAction(&$pDB1, &$pDB2)
{
    $id_area = getParameter('area');
    $description = getParameter('description');

    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    return $pControlPanel->updateDescriptionArea($description,$id_area);
}

function addExttoQueueAction(&$pDB1, &$pDB2)
{
    $number_org = getParameter('extStart');
    $queue      = getParameter('queue');

    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $pControlPanel->queueAddMember($queue, $number_org);
    return "";
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