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

function _moduleContent(&$smarty, $module_name){

    #incluir el archivo de idioma de acuerdo al que este seleccionado
    #si el archivo de idioma no existe incluir el idioma por defecto
    $lang=get_language();
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$script_dir/$lang_file"))
        include_once($lang_file);
    else
        include_once("modules/$module_name/lang/en.lang");
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    global $arrConf;
    global $arrLang;
    global $arrLangModule;
    require_once "modules/$module_name/libs/PaloSantoDontCalls.class.php";
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    // se conecta a la base
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    if(!empty($pDB->errMsg)) {
        $smarty->assign("mb_message", $arrLang["Error when connecting to database"]."<br/>".$pDB->errMsg);
    }

    $smarty->assign("MODULE_NAME", $arrLangModule["Add Number"]);
    $smarty->assign("label_file", $arrLangModule["Upload File"]);
    $smarty->assign("label_text", $arrLangModule["Add new Number"]);
    $smarty->assign("NAME_BUTTON_SUBMIT", $arrLangModule["SAVE"]);
    $smarty->assign("NAME_BUTTON_CANCEL", $arrLangModule["CANCEL"]);

    $formCampos = array();
    $oForm = new paloForm($smarty, $formCampos);

    if (isset($_POST['submit_Add_Call'])) {
        $contenidoModulo = AddCalls($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);

    } else if (isset($_POST['submit_new'])) {
        $contenidoModulo = newCalls($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if ( isset( $_POST['submit_Apply'] ) ) {
        $contenidoModulo = applyList($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if ( isset( $_POST['submit_delete'] ) ){
        $contenidoModulo = deleteCalls($pDB, $smarty, $module_name, $local_templates_dir);
    } else  if ( isset( $_POST['submit_cancel'] ) ){
        $contenidoModulo = listCalls($pDB, $smarty, $module_name, $local_templates_dir);
    }else{
        $contenidoModulo = listCalls($pDB, $smarty, $module_name, $local_templates_dir);
    }

    return $contenidoModulo;
}


function AddCalls($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLangModule;
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLangModule["Add Number"],$_POST);
    return $contenidoModulo;
}

function newCalls($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLangModule;
    $fContenido="";
    $msgResultado="";

    if (isset($_FILES['file_number'])) {
        if($_FILES['file_number']['name']!=""){
	    $file = $_FILES['file_number'];
	    $cargaDatos = new Cargar_File($file);
	    if( is_object($cargaDatos) )  {
		$nameFile=$cargaDatos->getFileName();
		$flag = $cargaDatos->guardarDatosCallsFromFile($pDB,$nameFile);
	    } else { 
		$smarty->assign("mb_title",$lang['Error']);
		$smarty->assign("mb_message",$lang['Error when is loading file']);
	    }
        }else{
            $msgResultado = $arrLangModule["Please select any file"];
        }
    }elseif( isset( $_POST["txt_new_number"] ) ){
        if( $_POST["txt_new_number"]!="" ){
            $new_number = $_POST["txt_new_number"];
            if(is_numeric($new_number) && $new_number>0){
                $msgResultado = registrarNuevoNumero($pDB,$new_number);
            }else{
                $msgResultado = $arrLangModule["Number phone is not numeric value"];
            }
        }else{
            $msgResultado = $arrLangModule["Please enter a number phone"];
        }
    }

    $oForm->setViewMode();

    if($msgResultado==""){
        header("Location: ?menu=dont_call_list");
    }else{
        $smarty->assign("mb_title",$arrLangModule['Result']);
        $smarty->assign("mb_message",$msgResultado);
    }
    $fContenido = $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLangModule['Load File'] ,null);
    return $fContenido;
}

function listCalls($pDB, $smarty, $module_name, $local_templates_dir) {
    global $arrLang;
    global $arrLangModule;
    $arrCalls=array();
    $oCalls = new PaloSantoDontCalls($pDB);
    $arrCalls = $oCalls->getCalls();
    $end = count($arrCalls);

    if (is_array($arrCalls) && count($arrCalls)>0) {
        foreach($arrCalls as $call) {
            $arrTmp    = array();
            $arrTmp[0] = construirCheck($call['id']);
            $arrTmp[1] = $call['caller_id'];
            $arrTmp[2] = $call['date_income'];
            if($call['status']=='I'){
                $arrTmp[3] = $arrLangModule['Inactive'];
            }else{
                $arrTmp[3] = $arrLangModule['Active'];
            } 
            $arrData[] = $arrTmp;
         }
    }else{
        $arrData=array();
    }

    $button_delete="<input class='button' type='submit' name='submit_delete'".
                    " value='{$arrLangModule["Remove"]}'>";

    $arrGrid = array("title"    => $arrLangModule["Phone List"],
        "icon"     => "images/list.png",
        "width"    => "99%",
        "start"    => ($end==0) ? 0 : 1,
        "end"      => $end,
        "total"    => $end,
        "columns"  => array(0 => array("name"      => $button_delete,
                                       "property1" => ""),
                            1 => array("name"      => $arrLangModule["Number Phone's"],
                                       "property1" => ""),
                            2 => array("name"      => $arrLangModule["Date Income"],
                                       "property1" => ""),
                            3 => array("name"     => $arrLangModule["Status"],
                                       "property1" => "")));

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
        "<input type='submit' name='submit_Add_Call' value='{$arrLangModule['Add']}' class='button'>&nbsp&nbsp&nbsp&nbsp".
        "<input type='submit' name='submit_Apply' value='{$arrLangModule['Apply']}' class='button'>");

    $abrir_form="<form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>";
    $cerrar_form="</form>";

    $contenidoModulo = $abrir_form.$oGrid->fetchGrid($arrGrid, $arrData,$arrLang).$cerrar_form;
    return $contenidoModulo;
}

function applyList($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm){
    $contenido="";
    $oCalls = new PaloSantoDontCalls($pDB);
    $oCalls->applyList();
    header("Location:?menu=dont_call_list");
    return $contenido;
}

function deleteCalls($pDB, $smarty, $module_name, $local_templates_dir){
    global $arrLangModule;

    $sContenido="";
    $arrIdCalls=array();
    $patronBusqueda = '^chk_[0-9]+$';
    foreach($_POST as $nombre => $valor) {
	if( ereg( $patronBusqueda , $nombre ) ) {
	    $arrIdCalls[] = $valor;
	}
    }
    if(count($arrIdCalls)<=0){
        $smarty->assign("mb_title",$arrLangModule['Result']);
        $smarty->assign("mb_message","No data selected");
    }else{
	$oCalls = new PaloSantoDontCalls($pDB);
	$bExito = $oCalls->deleteCalls($arrIdCalls);
	if($bExito){
	    header("Location: ?menu=dont_call_list");
	}else{
	    //$sContenido .=$insTpl->crearAlerta("error","Error",$oMasterSet->getMessage());
	}

    }
    return $sContenido;
}

function construirCheck($id){
    $html_chk = "<input type='checkbox' name='chk_{$id}' value='{$id}'/>";
    return $html_chk;
}

?>
