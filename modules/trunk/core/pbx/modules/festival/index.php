<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4-5                                               |
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
  $Id: index.php,v 1.1 2011-04-14 11:04:34 Alberto Santos asantos@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoFestival.class.php";

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


    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = changeStatusFestival($smarty, $module_name, $local_templates_dir, $arrConf);
            break;
        case "update":
            $content = updateConfigurationFile($smarty, $module_name, $local_templates_dir, $arrConf);
        default: // view_form
            $content = viewFormFestival($smarty, $module_name, $local_templates_dir, $arrConf);
            break;
    }
    return $content;
}

function viewFormFestival($smarty, $module_name, $local_templates_dir, $arrConf)
{
    $pFestival = new paloSantoFestival();
    $arrFormFestival = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormFestival);
    $_DATA = $_POST;
    if($pFestival->isFestivalActivated())
        $_DATA["status"] = "on";
    else
        $_DATA["status"] = "off";
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("IMG", "modules/$module_name/images/text-to-speech-icon-large.png");
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Festival"), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function changeStatusFestival($smarty, $module_name, $local_templates_dir, $arrConf)
{
    $pFestival = new paloSantoFestival();
    $status    = getParameter("status");
    $message   = "";
    if($status=="active"){
        if(!$pFestival->isConfigurationFileCorrect()){
            if($pFestival->getError()!=""){
                $smarty->assign("mb_title",_tr("ERROR"));
                $smarty->assign("mb_message", $pFestival->getError());
                return viewFormFestival($smarty, $module_name, $local_templates_dir, $arrConf);
            }
            if(!$pFestival->setConfigurationFile()){
                $smarty->assign("mb_title",_tr("ERROR"));
                $smarty->assign("mb_message", $pFestival->getError());
                return viewFormFestival($smarty, $module_name, $local_templates_dir, $arrConf);
            }
            $message = _tr("The file /usr/share/elastix/elastix.scm was modified").". ";
        }
        if($pFestival->isFestivalActivated()){
            $smarty->assign("mb_title",_tr("ERROR"));
            $smarty->assign("mb_message", _tr("Festival is already activated"));
            return viewFormFestival($smarty, $module_name, $local_templates_dir, $arrConf);
        }
        if($pFestival->activateFestival()){
            $message .= _tr("Festival has been successfully activated");
            $smarty->assign("mb_title",_tr("Message"));
            $smarty->assign("mb_message", $message);
        }
        else{
            $message .= _tr("Festival could not be activated");
            $smarty->assign("mb_title",_tr("ERROR"));
            $smarty->assign("mb_message", $message);
        }
    }
    elseif($status=="disactive"){
        if(!$pFestival->isFestivalActivated()){
            $smarty->assign("mb_title",_tr("ERROR"));
            $smarty->assign("mb_message", _tr("Festival is already deactivated"));
            return viewFormFestival($smarty, $module_name, $local_templates_dir, $arrConf);
        }
        if($pFestival->deactivateFestival()){
            $smarty->assign("mb_title",_tr("Message"));
            $smarty->assign("mb_message", _tr("Festival has been successfully deactivated"));
        }
        else{
            $smarty->assign("mb_title",_tr("ERROR"));
            $smarty->assign("mb_message", _tr("Festival could not be deactivated"));
        }
    }
    return viewFormFestival($smarty, $module_name, $local_templates_dir, $arrConf);
}

function updateConfigurationFile($smarty, $module_name, $local_templates_dir, $arrConf)
{

}

function createFieldForm()
{
    $arrFields = array(
            "status"   => array(            "LABEL"                  => _tr("Status"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            ),

            );
    return $arrFields;
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