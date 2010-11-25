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

if (!function_exists('_tr')) {
    function _tr($s)
    {
        global $arrLang;
        return isset($arrLang[$s]) ? $arrLang[$s] : $s;
    }
}
if (!function_exists('load_language_module')) {
    function load_language_module($module_id, $ruta_base='')
    {
        $lang = get_language($ruta_base);
        include_once $ruta_base."modules/$module_id/lang/en.lang";
        $lang_file_module = $ruta_base."modules/$module_id/lang/$lang.lang";
        if ($lang != 'en' && file_exists("$lang_file_module")) {
            $arrLangEN = $arrLangModule;
            include_once "$lang_file_module";
            $arrLangModule = array_merge($arrLangEN, $arrLangModule);
        }

        global $arrLang;
        global $arrLangModule;
        $arrLang = array_merge($arrLang,$arrLangModule);
    }
}

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    global $arrConf;

    load_language_module($module_name);
    

    require_once "modules/$module_name/libs/PaloSantoBreaks.class.php";
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    // se conecta a la base
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    if(!empty($pDB->errMsg)) {
        $smarty->assign("mb_message", _tr("Error when connecting to database")."<br/>".$pDB->errMsg);
    }

    // Definición del formulario de nueva campaña
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE",_tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("DESACTIVATE", _tr("Desactivate"));

    $formCampos = array(
        "nombre"    =>    array(
                "LABEL"                  => _tr("Name Break"),
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "TEXT",
                "INPUT_EXTRA_PARAM"      => array("size" => "40"),
                "VALIDATION_TYPE"        => "text",
                "VALIDATION_EXTRA_PARAM" => "",
        ),
        "descripcion" => array(
                "LABEL"                  => _tr("Description Break"),
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

    if (!isset($_POST['nombre'])) $_POST['nombre']='';
    if (!isset($_POST['descripcion'])) $_POST['descripcion']='';
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl", _tr("New Break"),$_POST);
    return $contenidoModulo;
}

function saveBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    if(!$oForm->validateForm($_POST)) {
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores=$oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
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
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", $oBreak->errMsg);
        } 
    }

    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl", _tr("New Break"),$_POST);
    return $contenidoModulo;
}

function viewBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
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
    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", _tr("View Break"), $arrTmp); 
    return $contenidoModulo;
}


function editBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    // Tengo que recuperar los datos del break
    $oBreaks = new PaloSantoBreaks($pDB);
    $arrBreaks = $oBreaks->getBreaks($_GET['id'],'A');

    $arrTmp['nombre']       = $arrBreaks[0]['name'];
    $arrTmp['descripcion']  = $arrBreaks[0]['description'];

    $oForm = new paloForm($smarty, $formCampos);
    $oForm->setEditMode();
    $smarty->assign("id_break", $_POST['id_break']);
    
    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", _tr('Edit Break')." \"".$arrTmp['nombre']."\"", $arrTmp);
    return $contenidoModulo;
}

function updateBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    if(!$oForm->validateForm($_POST)) {
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores=$oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
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
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", $oBreak->errMsg);
        } 
    }
 
    $oForm->setEditMode();
    $smarty->assign("id_break", $_POST['id_break']);
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr('Edit Break')." \"".$_POST['nombre']."\"",$_POST);
    return $contenidoModulo;
}

function listBreaks($pDB, $smarty, $module_name, $local_templates_dir) {
    global $arrLang;

    $oBreaks = new PaloSantoBreaks($pDB);

    $arrBreaks = $oBreaks->getBreaks();

    $end = count($arrBreaks);

    $arrData = array();
    if (is_array($arrBreaks)) {
        foreach($arrBreaks as $break) {
            if( strcasecmp($break['name'],'hold') != 0){
                $arrTmp    = array();
                $arrTmp[0] = htmlentities($break['name'], ENT_COMPAT, "UTF-8");
                if($break['description']=="" || $break['description']==null)
                    $arrTmp[1] = "&nbsp;";
                else
                    $arrTmp[1] = htmlentities($break['description'], ENT_COMPAT, "UTF-8");
    
                if($break['status']=='I'){
                    $arrTmp[2] = _tr('Inactive');
                    $arrTmp[3] = "&nbsp;<a href='?menu=$module_name&action=activar&id=".$break['id']."'>"._tr('Activate')."</a>";
                }else{
                    $arrTmp[2] = _tr('Active');
                    $arrTmp[3] = "&nbsp;<a href='?menu=$module_name&action=view&id=".$break['id']."'>"._tr('View Break')."</a>";
                } 
                $arrData[] = $arrTmp;
            }
        }
    }

    $arrGrid = array("title"    => _tr("Breaks List"),
        "url"      => construirURL(array('menu' => $module_name), array('nav', 'start')),
        "icon"     => "images/list.png",
        "width"    => "99%",
        "start"    => ($end==0) ? 0 : 1,
        "end"      => $end,
        "total"    => $end,
        "columns"  => array(0 => array("name"      => _tr("Name Break"),
                                       "property1" => ""),
                            1 => array("name"      => _tr("Description Break"), 
                                       "property1" => ""),
                            2 => array("name"      => _tr("Status"), 
                                       "property1" => ""),
                            3 => array("name"     => _tr("Options"), 
                                       "property1" => "")));

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter("<input type='submit' name='submit_create_break' value='"._tr('Create New Break')."' class='button' />");

    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

function activateBreak($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm)
{   
     if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        return false;
    }
    $oBreaks = new PaloSantoBreaks($pDB);
    if($oBreaks->activateBreak($_GET['id'],'A'))
        header("Location: ?menu=$module_name");
    else
    {
        $smarty->assign("mb_title",_tr('Activate Error'));
        $smarty->assign("mb_message",_tr('Error when Activating the Break'));
    }
}
?>