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
  $Id: data_fom $ */

require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoTrunk.class.php";
include_once "libs/paloSantoGrid.class.php";
require_once "libs/misc.lib.php";
require_once "libs/xajax/xajax.inc.php";
//require "configs/db.conf.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    global $arrConf;
    global $arrLang;
//global $cadena_dsn;
//echo "dsn=".$arrConf['cadena_dsn'];

    #incluir el archivo de idioma de acuerdo al que este seleccionado
    #si el archivo de idioma no existe incluir el idioma por defecto
    $lang=get_language();

    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$script_dir/$lang_file"))
        include_once($lang_file);
    else
        include_once("modules/$module_name/lang/en.lang");

    require_once "modules/$module_name/libs/paloSantoDataForm.class.php";
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];


    // Definición del formulario de nueva formulario
    $smarty->assign("MODULE_NAME", $module_name);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("APPLY_CHANGES", $arrLang["Apply changes"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DESCATIVATE", $arrLan["Desactivate"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CONFIRM_CONTINUE", $arrLang["Are you sure you wish to continue?"]);
    $smarty->assign("new_field", $arrLan["New Field"]);
    $smarty->assign("add_field", $arrLan["Add Field"]);
    $smarty->assign("update_field",$arrLan['Update Field']); 
    $smarty->assign("CONFIRM_DELETE", $arrLan["Are you sure you wish to delete form?"]);
   
// print_r($_POST);

    //Definicion de campos
    $formCampos = array(
        'form_nombre'    =>    array(
            "LABEL"                => $arrLan["Form Name"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => array("size" => "60"),
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "",
        ),
        'form_description'    =>    array(
            "LABEL"                => $arrLan["Form Description"],
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXTAREA",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "",
            "COLS"                   => "33",
            "ROWS"                   => "2",
        ),
        'field_nombre'    =>    array(
            "LABEL"                => $arrLan["Field Name"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXTAREA",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "",
            "COLS"                   => "50",
            "ROWS"                   => "2",
        ),
//         "cvs_column"       => array(
//             "LABEL"                  => $arrLan["CVS Column"],
//             "REQUIRED"               => "no",
//             "INPUT_TYPE"             => "TEXT",
//             "INPUT_EXTRA_PARAM"      => "",
//             "VALIDATION_TYPE"        => 'ereg',
//             "VALIDATION_EXTRA_PARAM" =>  '(({)([[:alpha:]]{3,})(}))$'
//         ),
//         "number_column"       => array(
//             "LABEL"                  => $arrLan["Column Number"],
//             "REQUIRED"               => "yes",
//             "INPUT_TYPE"             => "TEXT",
//             "INPUT_EXTRA_PARAM"      => "",
//             "VALIDATION_TYPE"        => 'numeric',
//             "VALIDATION_EXTRA_PARAM" => ''
//         ),
//         "number_line"   => array(
//             "LABEL"                  => $arrLan["Lines Number"],
//             "REQUIRED"               => "yes",
//             "INPUT_TYPE"             => "TEXT",
//             "INPUT_EXTRA_PARAM"      => "",
//             "VALIDATION_TYPE"        => 'numeric',
//             "VALIDATION_EXTRA_PARAM" => '',
//         ),
//         "validation"   => array(
//             "LABEL"                  => $arrLan["Field Validation"],
//             "REQUIRED"               => "yes",
//             "INPUT_TYPE"             => "SELECT",
//             "INPUT_EXTRA_PARAM"      => $arrValidation,
//             "VALIDATION_TYPE"        => "text",
//             "VALIDATION_EXTRA_PARAM" => ""
//         ),
        "order" => array(
            "LABEL"                  => $arrLan["Order"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => array("size" => "3"),
            "VALIDATION_TYPE"        => "numeric",
            "VALIDATION_EXTRA_PARAM" => ""
        ), 
    );
    $smarty->assign("type",$arrLang['Type']);    
    $smarty->assign("select_type","type"); 
    $smarty->assign("option_type",$arrConfig['arr_type']); 
    $smarty->assign("item_list",$arrLan['List Item']);    
    $smarty->assign("agregar",$arrLan["Add Item"]); 
    $smarty->assign("quitar",$arrLan['Remove Item']); 
    $oForm = new paloForm($smarty, $formCampos);     
// print_r($_SESSION['ayuda']);
    $xajax = new xajax();
    $xajax->registerFunction("agregar_campos_formulario");
    $xajax->registerFunction("cancelar_formulario_ingreso");
    $xajax->registerFunction("guardar_formulario");
    $xajax->registerFunction("eliminar_campos_formulario");
    $xajax->registerFunction("editar_campo_formulario");
    $xajax->registerFunction("update_campo_formulario");
    $xajax->registerFunction("cancel_campo_formulario");
    $xajax->registerFunction("desactivar_formulario");

    $xajax->processRequests();
    $smarty->assign("xajax_javascript",$xajax->printJavascript("libs/xajax/"));


    $pDB = new paloDB($arrConf['cadena_dsn']);
    if (!is_object($pDB->conn) || $pDB->errMsg!="") {
        $smarty->assign("mb_message", $arrLang["Error when connecting to database"]." ".$pDB->errMsg);
    }
    if (isset($_POST['submit_create_form'])) {
        $contenidoModulo = new_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm); 
    } else if (isset($_POST['edit'])) {
        $contenidoModulo = edit_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_POST['delete'])) {
        $contenidoModulo = delete_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="view") {
        $contenidoModulo = view_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm); 
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="activar") {
        $contenidoModulo = activar_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm); 
    } else {
        $contenidoModulo = listadoForm($pDB, $smarty, $module_name, $local_templates_dir); 
    }

    return $contenidoModulo;
}


function new_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {

    global $arrLang;
    global $arrLan;
    $oDataForm = new paloSantoDataForm($pDB);
    $id_nuevo_formulario = $oDataForm->proximo_id_formulario();
    $smarty->assign("id_formulario_actual",$id_nuevo_formulario); // obtengo el id para crear el nuevo formulario
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/form.tpl", $arrLan["New Form"],$_POST);  
    return $contenidoModulo;
}

function view_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;

    $oForm->setViewMode(); // Esto es para activar el modo "preview"

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        return false;
    }
    $oDataForm = new paloSantoDataForm($pDB);
    $arrDataForm = $oDataForm->getFormularios($_GET['id']);
    $arrFieldForm = $oDataForm->obtener_campos_formulario($_GET['id']);
    
    // Conversion de formato
    $arrTmp['form_nombre']       = $arrDataForm[0]['nombre'];
    $arrTmp['form_description']    = $arrDataForm[0]['descripcion'];  

    $smarty->assign("id_formulario_actual", $_GET['id']);
    $smarty->assign("style_field","style='display:none;'");
    $html_campos = html_campos_formulario($arrFieldForm,false);
    $smarty->assign("solo_contenido_en_vista",$html_campos);
    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/form.tpl", $arrLan["View Form"], $arrTmp); // hay que pasar el arreglo
    return $contenidoModulo;
}

function edit_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;

    // Tengo que recuperar los datos del formulario
    $oDataForm = new paloSantoDataForm($pDB);
    $arrDataForm = $oDataForm->getFormularios($_GET['id']); 
    $arrFieldForm = $oDataForm->obtener_campos_formulario($_GET['id']);

    $arrTmp['form_nombre']       = $arrDataForm[0]['nombre'];
    $arrTmp['form_description']    = $arrDataForm[0]['descripcion'];   

    $oForm = new paloForm($smarty, $formCampos);
    $oForm->setEditMode();
    $smarty->assign("id_formulario_actual", $_GET['id']);
    $html_campos = html_campos_formulario($arrFieldForm);
    $smarty->assign("solo_contenido_en_vista",$html_campos);
    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/form.tpl", $arrLan['Edit Form']." \"".$arrTmp['form_nombre']."\"", $arrTmp);
    return $contenidoModulo;
}

function listadoForm($pDB, $smarty, $module_name, $local_templates_dir) {

    global $arrLang;
    global $arrLan;
    $oDataForm = new paloSantoDataForm($pDB);
    // preguntando por el estado del filtro
    if (!isset($_POST['cbo_estado']) || $_POST['cbo_estado']=="") {
        $_POST['cbo_estado'] = "A";
    }
    $arrDataForm = $oDataForm->getFormularios(NULL, $_POST['cbo_estado']);
    $end = count($arrDataForm);

    $arrData = array();
    if (is_array($arrDataForm)) {
        foreach($arrDataForm as $DataForm) {
            $arrTmp    = array();
            $arrTmp[0] = $DataForm['nombre'];
            if(!isset($DataForm['descripcion']) || $DataForm['descripcion']=="")
                $DataForm['descripcion']="&nbsp;";
            $arrTmp[1] = $DataForm['descripcion'];
            if($DataForm['estatus']=='I'){
                $arrTmp[2] = $arrLang['Inactive'];
                $arrTmp[3] = "&nbsp;<a href='?menu=$module_name&action=activar&id=".$DataForm['id']."'>{$arrLan['Activate']}</a>";
            }
            else{
                $arrTmp[2] = $arrLang['Active'];
                $arrTmp[3] = "&nbsp;<a href='?menu=$module_name&action=view&id=".$DataForm['id']."'>{$arrLang['View']}</a>";
            }
            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => $arrLan["Form List"],
        "icon"     => "images/list.png",
        "width"    => "99%",
        "start"    => ($end==0) ? 0 : 1,
        "end"      => $end,
        "total"    => $end,
        "columns"  => array(0 => array("name"      => $arrLan["Form Name"],
                                       "property1" => ""),
                            1 => array("name"      => $arrLan["Form Description"], 
                                       "property1" => ""),
                            2 => array("name"      => $arrLang["Status"], 
                                       "property1" => ""),
                            3 => array("name"      => $arrLang["Options"], 
                                       "property1" => "")));

    $estados = array("all"=>"Todos", "A"=>"Activo", "I"=>"Inactivo");
    $combo_estados = "<select name='cbo_estado' id='cbo_estado' onChange='submit();'>".combo($estados,$_POST['cbo_estado'])."</select>";
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
              "<form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>" .
              "<table width='100%' border='0'><tr>".
              "<td><input type='submit' name='submit_create_form' value='{$arrLan['Create New Form']}' class='button'></td>".
              "<td class='letra12' align='right'>Estado&nbsp;$combo_estados</td>".
              "</tr></table>".
              "</form>");
//print_r($arrData);
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

function activar_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm)
{
    global $arrLang;
    global $arrLan;
     if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        return false;
    }
    $oDataForm = new paloSantoDataForm($pDB);
    if($oDataForm->activar_formulario($_GET['id']))
        header("Location: ?menu=$module_name");
    else
    {
        $smarty->assign("mb_title",$arrLan['Activate Error']);
        $smarty->assign("mb_message",$arrLan['Error when Activating the form']);
    }
}

function delete_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;
    if (!isset($_POST['id_formulario']) || !is_numeric($_POST['id_formulario'])) {
        return false;
    }

    $oDataForm = new paloSantoDataForm($pDB);
    if($oDataForm->delete_form($_POST['id_formulario'])) {
        if ($oDataForm->errMsg!="") {
            $smarty->assign("mb_title",$arrLang['Validation Error']);
            $smarty->assign("mb_message",$oDataForm->errMsg);
        } else {
            header("Location: ?menu=form_designer");
        }
    } else {
        $msg_error = ($oDataForm->errMsg!="")?"<br>".$oDataForm->errMsg:"";
        $smarty->assign("mb_title",$arrLang['Delete Error']);
        $smarty->assign("mb_message",$arrLan['Error when deleting the Form'].$msg_error);
    }
    $sContenido = view_form($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    return $sContenido;
}
?>