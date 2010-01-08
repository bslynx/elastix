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
    $pDB1 = new paloDB($arrConf['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConf['dsn_conn_database2']);

    //actions
    $action = getAction();
    $content  = "";

    switch($action){
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

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Control Panel"], $_POST);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
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
    else
        return "report"; //cancel
}
?>
