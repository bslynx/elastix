<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.5.2-3.1                                               |
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
  $Id: index.php,v 1.2 2009/07/27 13:10:24 dlopez Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoTrunk.class.php";//Trunks

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoReportedeTroncalesusadasporHoraeneldia.class.php";
    include_once "libs/paloSantoConfig.class.php";

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
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    $dsnAsteriskCdr = $arrConfig['AMPDBENGINE']['valor']."://".
                      $arrConfig['AMPDBUSER']['valor']. ":".
                      $arrConfig['AMPDBPASS']['valor']. "@".
                      $arrConfig['AMPDBHOST']['valor']."/asterisk";

    $pDB = new paloDB($arrConf['dsn_conn_database']);
    $pDB_asterisk = new paloDB($dsnAsteriskCdr);

    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        default:
            $content = reportReportedeTroncalesusadasporHoraeneldia($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $pDB_asterisk);
            break;
    }
    return $content;
}

function reportReportedeTroncalesusadasporHoraeneldia($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, &$pDB_asterisk)
{
    $pReportedeTroncalesusadasporHoraeneldia = new paloSantoReportedeTroncalesusadasporHoraeneldia($pDB);

    // PS se obtiene el arreglo con las trunks para mostrarlas en el filtro
    //$arrTrunk1 = getTrunk($pDB, $pDB_asterisk);//Trunks
     //diana
    //llamamos  funcion nueva
    $arrTrunk = obtener_nuevas_trunks($pDB, $pDB_asterisk);

    // valores del filtro
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $date_from = getParameter("date_from");
    $date_to = getParameter("date_to");

    // si la fecha no est�seteada en el filtro
    $_POST["date_from"] = isset($date_from)?$date_from:date("d M Y");
    $_POST["date_to"] = isset($date_to)?$date_to:date("d M Y");
    $date_from = isset($date_from)?date('Y-m-d',strtotime($date_from)):date("Y-m-d");
    $date_to = isset($date_to)?date('Y-m-d',strtotime($date_to)):date("Y-m-d");

    // para setear la trunk la primera vez
    $filter_value = getParameter("filter_value");
    if (!isset($filter_value)) {
        $trunk = array_shift(array_keys($arrTrunk));//Trunks
        $_POST["filter_value"] = $trunk;
        $filter_value = $trunk;
    }
    //validacion para que los filtros se queden seteados con el valor correcto, correccion de bug que se estaba dando en caso de pagineo
    $_POST["filter_value"] = $filter_value;


    $action = getParameter("nav");
    $start  = getParameter("start");
    $iscsv  = getParameter("exportcsv");

    // begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->enableExport();

    $limit  = 30;
    $offset = $oGrid->getOffsetValue();

   // se obtienen los datos que se van a mostrar
    $arrData = null;
    $arrResult =$pReportedeTroncalesusadasporHoraeneldia->ObtainReportedeTroncalesusadasporHoraeneldia($limit, $offset, $filter_field, $filter_value, $date_from, $date_to);

    $total = count($arrResult);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $end    = $oGrid->getEnd();

    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value&date_from=$date_from&date_to=$date_to";

    // se guarda la data en un arreglo que luego es enviado como par�etro para crear el reporte
    if(is_array($arrResult)){
        foreach($arrResult as $key => $value){ 
	    $arrTmp[0] = $value['time_period'];
	    $arrTmp[1] = isset($value['entered'])?$value['entered']:"0";
	    $arrTmp[2] = isset($value['terminada'])?$value['terminada']:"0"; //answered
	    $arrTmp[3] = isset($value['abandonada'])?$value['abandonada']:"0"; //abandoned
	    $arrTmp[4] = isset($value['en-cola'])?$value['en-cola']:"0"; //en-cola
	    $arrTmp[5] = isset($value['fin-monitoreo'])?$value['fin-monitoreo']:"0"; //no han sido monitoreadas hasta el final, por algn error del dialer
            $arrData[] = $arrTmp;
        }
    }
    // se crea el grid
    $arrGrid = array("title"    => $arrLang["Reporte de Troncales usadas por Hora en el dia"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
			0 => array("name"      => $arrLang["Time Period "],
                                   "property1" => ""),
			1 => array("name"      => $arrLang["Entered"],
                                   "property1" => ""),
			2 => array("name"      => $arrLang["Answered"],
                                   "property1" => ""),
			3 => array("name"      => $arrLang["Abandoned"],
                                   "property1" => ""),
			4 => array("name"      => $arrLang["In queue"],
                                   "property1" => ""),
			5 => array("name"      => $arrLang["Without monitoring "],
                                   "property1" => ""),
                                        )
                    );

    //begin section filter
    $arrFormFilterReportedeTroncalesusadasporHoraeneldia = createFieldFilter($arrLang, $arrTrunk);

    $oFilterForm = new paloForm($smarty, $arrFormFilterReportedeTroncalesusadasporHoraeneldia);
    $smarty->assign("SHOW", $arrLang["Show"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST, $_GET);
    //end section filter

    // se pregunta si la acci� es crear un csv con los datos del reporte 
    if($iscsv != 'yes'){
        $oGrid->showFilter(trim($htmlFilter));
        $content = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    }
    else{
        $fechaActual = date("d M Y");
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: application/octec-stream');
        $title = "\"".$fechaActual.".csv\"";
        header("Content-disposition: inline; filename={$title}");
        header('Content-Type: application/force-download');
	$content = $oGrid->fetchGridCSV($arrGrid, $arrData);
    }
    //end grid parameters

    return $content;
}


function createFieldFilter($arrLang, $arrTrunk){

    $arrFormElements = array(
            "filter_field" => array("LABEL"                  => $arrLang["Trunk"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "text",
                                    "INPUT_EXTRA_PARAM"      => "no",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),

            "filter_value" => array("LABEL"                  => "",
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrTrunk,
                                    "VALIDATION_TYPE"        => "",
                                    "VALIDATION_EXTRA_PARAM" => ""),

            "date_from"    => array("LABEL"                  => $arrLang["Start date"],
                                    "REQUIRED"               => "yes",
                                    "INPUT_TYPE"             => "DATE",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "ereg",
                                    "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),

            "date_to"      => array("LABEL"                  => $arrLang["End date"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "DATE",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "ereg",
                                    "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                    );
    return $arrFormElements;
}

function obtener_nuevas_trunks($pDB, $pDB_asterisk)
{
    $listaTrunks = array();
    $trunks = getTrunks($pDB_asterisk);    

    foreach ($trunks as $tuplaTrunk) {
        $listaTrunks[$tuplaTrunk[1]] = $tuplaTrunk[1];
    }
    return $listaTrunks;
}

if (!function_exists('getParameter')) {
function getParameter($parameter)
{
    if(isset($_POST[$parameter]))
        return $_POST[$parameter];
    else if(isset($_GET[$parameter]))
        return $_GET[$parameter];
    else
        return null;
}
}

function getAction()
{
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    else if(getParameter("new"))
        return "new";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "report";
}?>