<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: new_campaign.php $ */

require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoTrunk.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoGrid.class.php";
require_once "libs/xajax/xajax.inc.php";

/*
  BASE CAMPAIGN
CREATE TABLE break (
    id             INTEGER PRIMARY KEY,
    name           VARCHAR(250) NOT NULL,
    description    VARCHAR(250)

, status varchar(1) Not NULL default 'A');

*/
function _moduleContent(&$smarty, $module_name)
{
    #incluir el archivo de idioma de acuerdo al que este seleccionado
    #si el archivo de idioma no existe incluir el idioma por defecto
    $lang=get_language();
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);

    // Include language file for EN, then for local, and merge the two.
    $arrLan = NULL;
    include_once("modules/$module_name/lang/en.lang");
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$script_dir/$lang_file")) {
        $arrLanEN = $arrLan;
        include_once($lang_file);
        $arrLan = array_merge($arrLanEN, $arrLangModule);
    }

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    global $arrConf;
    global $arrLang;

    require_once "modules/$module_name/libs/PaloSantoBreaks.class.php";
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    // se conecta a la base
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    if(!empty($pDB->errMsg)) {
        $smarty->assign("mb_message", $arrLang["Error when connecting to database"]."<br/>".$pDB->errMsg);
    }

    // Definición del formulario de nueva campaña
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("APPLY_CHANGES", $arrLang["Apply changes"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE",$arrLang["Delete"]);
    $smarty->assign("CONFIRM_CONTINUE", $arrLang["Are you sure you wish to continue?"]);
    $smarty->assign("DESACTIVATE", $arrLan["Desactivate"]);

    $formCampos = array(
        "nombre"    =>    array(
                "LABEL"                  => $arrLan["Name Break"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "TEXT",
                "INPUT_EXTRA_PARAM"      => array("size" => "40"),
                "VALIDATION_TYPE"        => "text",
                "VALIDATION_EXTRA_PARAM" => "",
        ),
        "descripcion" => array(
                "LABEL"                  => $arrLan["Description Break"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "TEXTAREA",
                "INPUT_EXTRA_PARAM"      => "",
                "VALIDATION_TYPE"        => "text",
                "VALIDATION_EXTRA_PARAM" => "",
                "ROWS"                   => "2",
                "COLS"                   => "33"
        ),
    );
    $oForm = new paloForm($smarty, $formCampos);

    $xajax = new xajax();
    $xajax->registerFunction("desactivateBreak");
    $xajax->processRequests();
    $smarty->assign("xajax_javascript",$xajax->printJavascript("libs/xajax/"));
    
    if (isset($_POST['submit_create_break'])) {
        $contenidoModulo = newBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_POST['save'])) {
        $contenidoModulo = saveBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_POST['edit'])) {
        $contenidoModulo = editBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_POST['apply_changes'])) {
        $contenidoModulo = updateBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="view") {
        $contenidoModulo = viewBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="activar") {
        $contenidoModulo = activateBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else {
        $contenidoModulo = listBreaks($pDB, $smarty, $module_name, $local_templates_dir);
    }
    return $contenidoModulo;
}


function newBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {

    global $arrLang;
    global $arrLan;
    if (!isset($_POST['nombre'])) $_POST['nombre']='';
    if (!isset($_POST['descripcion'])) $_POST['descripcion']='';
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New Break"],$_POST);
    return $contenidoModulo;
}

function saveBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;
    if(!$oForm->validateForm($_POST)) {
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores=$oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }
        $strErrorMsg .= "";
        $smarty->assign("mb_message", $strErrorMsg);
    } else {
        $oBreak = new PaloSantoBreaks($pDB);
        $exito  = $oBreak->createBreak(
                            $_POST['nombre'],
                            $_POST['descripcion']);

        if ($exito) {
            header("Location: ?menu=$module_name");
        } else {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $oBreak->errMsg);
        } 
    }

    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New Break"],$_POST);
    return $contenidoModulo;
}

function viewBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;

    $oForm->setViewMode(); // Esto es para activar el modo "preview"

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
        return false;

    $oBreaks = new PaloSantoBreaks($pDB);
    $arrBreaks = $oBreaks->getBreaks($_GET['id'],'A');
    // Conversion de formato
    $arrTmp['nombre']       = $arrBreaks[0]['name'];
    if($arrBreaks[0]['description']=="" || $arrBreaks[0]['description']==null)
        $arrTmp['descripcion'] = "&nbsp;";
    else
        $arrTmp['descripcion'] = $arrBreaks[0]['description'];

    $smarty->assign("id_break", $_GET['id']);
    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["View Break"], $arrTmp); 
    return $contenidoModulo;
}


function editBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;
    // Tengo que recuperar los datos del break
    $oBreaks = new PaloSantoBreaks($pDB);
    $arrBreaks = $oBreaks->getBreaks($_GET['id'],'A');

    $arrTmp['nombre']       = $arrBreaks[0]['name'];
    $arrTmp['descripcion']  = $arrBreaks[0]['description'];

    $oForm = new paloForm($smarty, $formCampos);
    $oForm->setEditMode();
    $smarty->assign("id_break", $_POST['id_break']);
    
    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan['Edit Break']." \"".$arrTmp['nombre']."\"", $arrTmp);
    return $contenidoModulo;
}

function updateBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;
    if(!$oForm->validateForm($_POST)) {
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores=$oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }
        $strErrorMsg .= "";
        $smarty->assign("mb_message", $strErrorMsg);
        $oForm->setEditMode();
    } else {
        $oBreak = new PaloSantoBreaks($pDB);
        $exito  = $oBreak->updateBreak(
                            $_POST['id_break'],
                            $_POST['nombre'],
                            $_POST['descripcion']);

        if ($exito) {
            header("Location: ?menu=$module_name&action=view&id=".$_POST['id_break']);
        } else {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $oBreak->errMsg);
        } 
    }
 
    $oForm->setEditMode();
    $smarty->assign("id_break", $_POST['id_break']);
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl",$arrLan['Edit Break']." \"".$_POST['nombre']."\"",$_POST);
    return $contenidoModulo;
}

function listBreaks($pDB, $smarty, $module_name, $local_templates_dir) {

    global $arrLang;
    global $arrLan;
    $oBreaks = new PaloSantoBreaks($pDB);

    $arrBreaks = $oBreaks->getBreaks();

    $end = count($arrBreaks);

    $arrData = array();
    if (is_array($arrBreaks)) {
        foreach($arrBreaks as $break) {
            if( strcasecmp($break['name'],'hold') != 0){
                $arrTmp    = array();
                $arrTmp[0] = $break['name'];
                if($break['description']=="" || $break['description']==null)
                    $arrTmp[1] = "&nbsp;";
                else
                    $arrTmp[1] = $break['description'];
    
                if($break['status']=='I'){
                    $arrTmp[2] = $arrLan['Inactive'];
                    $arrTmp[3] = "&nbsp;<a href='?menu=$module_name&action=activar&id=".$break['id']."'>{$arrLan['Activate']}</a>";
                }else{
                    $arrTmp[2] = $arrLan['Active'];
                    $arrTmp[3] = "&nbsp;<a href='?menu=$module_name&action=view&id=".$break['id']."'>{$arrLan['View Break']}</a>";
                } 
                $arrData[] = $arrTmp;
            }
        }
    }

    $arrGrid = array("title"    => $arrLan["Breaks List"],
        "icon"     => "images/list.png",
        "width"    => "99%",
        "start"    => ($end==0) ? 0 : 1,
        "end"      => $end,
        "total"    => $end,
        "columns"  => array(0 => array("name"      => $arrLan["Name Break"],
                                       "property1" => ""),
                            1 => array("name"      => $arrLan["Description Break"], 
                                       "property1" => ""),
                            2 => array("name"      => $arrLan["Status"], 
                                       "property1" => ""),
                            3 => array("name"     => $arrLang["Options"], 
                                       "property1" => "")));

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
              "<form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>" .
              "<input type='submit' name='submit_create_break' value='{$arrLan['Create New Break']}' class='button'></form>");

    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

function activateBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm)
{   global $arrLang;
    global $arrLan;
     if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        return false;
    }
    $oBreaks = new PaloSantoBreaks($pDB);
    if($oBreaks->activateBreak($_GET['id'],'A'))
        header("Location: ?menu=$module_name");
    else
    {
        $smarty->assign("mb_title",$arrLan['Activate Error']);
        $smarty->assign("mb_message",$arrLan['Error when Activating the Break']);
    }
}
?>
