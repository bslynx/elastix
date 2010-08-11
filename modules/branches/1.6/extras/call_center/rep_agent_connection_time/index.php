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
  $Id: index.php,v 1.1.1.1 2009/07/27 09:10:19 dlopez Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoQueue.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoReporteGeneraldeTiempoConexionAgentesPorDia.class.php";
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

    //conexion resource
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    $dsnAsteriskCdr = $arrConfig['AMPDBENGINE']['valor']."://".
                      $arrConfig['AMPDBUSER']['valor']. ":".
                      $arrConfig['AMPDBPASS']['valor']. "@".
                      $arrConfig['AMPDBHOST']['valor']."/asterisk";

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);
    $pDB_asterisk = new paloDB($dsnAsteriskCdr);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];


    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        default:
            $content = reportReporteGeneraldeTiempoConexionAgentesPorDia($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $pDB_asterisk);
            break;
    }
    return $content;
}

function reportReporteGeneraldeTiempoConexionAgentesPorDia($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, &$pDB_asterisk)
{
    $pReporteGeneraldeTiempoConexionAgentesPorDia = new paloSantoReporteGeneraldeTiempoConexionAgentesPorDia($pDB);

    //palosanto se obtiene el arreglo con las colas para mostrarlas en el filtro
    $arrQueue = getQueue($pDB, $pDB_asterisk);

//palosanto
    // valores del filtro cola y fechas
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $date_from = getParameter("date_from");
    $date_to = getParameter("date_to");
    //detallado o general
    $filter_field_tipo = getParameter("filter_field_tipo");
    $filter_value_tipo = getParameter("filter_value_tipo");

    // si la fecha no est�seteada en el filtro
    $_POST["date_from"] = isset($date_from)?$date_from:date("d M Y");
    $_POST["date_to"] = isset($date_to)?$date_to:date("d M Y");
    $date_from = isset($date_from)&&$date_from!=""?date('Y-m-d',strtotime($date_from)):date("Y-m-d");
    $date_to = isset($date_to)&&$date_to!=""?date('Y-m-d',strtotime($date_to)):date("Y-m-d");

    // para setear la cola la primera vez
    $filter_value = getParameter("filter_value");
    if (!isset($filter_value)) {
        $queue = array_shift(array_keys($arrQueue));
        $_POST["filter_value"] = $queue;
        $_GET["filter_value"] = $queue;
        $filter_value = $queue;
    }
    //validacion para que los filtros se queden seteados con el valor correcto, correccion de bug que se estaba dando en caso de pagineo
    $_POST["filter_value"] = $filter_value;
    $_POST["filter_value_tipo"] = $filter_value_tipo;

//palosanto fin

    $action = getParameter("nav");
    $start  = getParameter("start");
    $iscsv  = getParameter("exportcsv");

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);

    $oGrid->enableExport();

        //begin section filter
    $arrFormFilterReporteGeneraldeTiempoConexionAgentesPorDia = createFieldFilter($arrLang, $arrQueue);
    $oFilterForm = new paloForm($smarty, $arrFormFilterReporteGeneraldeTiempoConexionAgentesPorDia);
    $smarty->assign("SHOW", $arrLang["Show"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $totalReporteGeneraldeTiempoConexionAgentesPorDia = $pReporteGeneraldeTiempoConexionAgentesPorDia->ObtainNumReporteGeneraldeTiempoConexionAgentesPorDia($filter_field, $filter_value, $filter_field_tipo, $filter_value_tipo, $date_from, $date_to);

    $limit  = 20;
    $total  = $totalReporteGeneraldeTiempoConexionAgentesPorDia;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();

    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value&filter_field_tipo=$filter_field_tipo&filter_value_tipo=$filter_value_tipo&date_from=$date_from&date_to=$date_to";

//palosanto le enviamos la cola
     $arrData = null;
    $arrResult =$pReporteGeneraldeTiempoConexionAgentesPorDia->ObtainReporteGeneraldeTiempoConexionAgentesPorDia($limit, $offset, $filter_field, $filter_value,  $filter_field_tipo, $filter_value_tipo, $date_from, $date_to);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
	    $arrTmp[0] = $value['number_agent'];
	    $arrTmp[1] = $value['name'];
	    $arrTmp[2] = $value['first_conecction'];
	    $arrTmp[3] = ($value['last_conecction']=='-'?'<center><b>'.$value['last_conecction'].'</b></center>':$value['last_conecction']);
            $arrTmp[4] = $value['tiempo_total_sesion'];
	    $arrTmp[5] = is_null($value['tiempo_llamadas'])?'0':$value['tiempo_llamadas'];
	    $arrTmp[6] = number_format($value['porcentaje_servicio'],2);
	    $arrTmp[7] = $value['estado'];
            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => $arrLang["Reporte General de Tiempo Conexion Agentes Por Dia"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
			0 => array("name"      => $arrLang["Number Agent"],
                                   "property1" => ""),
			1 => array("name"      => $arrLang["Agent Name"],
                                   "property1" => ""),
			2 => array("name"      => $arrLang["First Conecction"],
                                   "property1" => ""),
			3 => array("name"      => $arrLang["Last Conecction"],
                                   "property1" => ""),
			4 => array("name"      => $arrLang["Total time of session"],
                                   "property1" => ""),
                        5 => array("name"      => $arrLang["Time Total Calls"],
                                   "property1" => ""),
			6 => array("name"      => $arrLang["Service %"],
                                   "property1" => ""),
			7 => array("name"      => $arrLang["Status"],
                                   "property1" => ""),
                                        )
                    );

//palsosanto : para csv
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


function createFieldFilter($arrLang, $arrQueue){
    $arrFilter = array(
	    "general" => $arrLang["General"],
	    "detallado" => $arrLang["Details"],
                    );

    $arrFormElements = array(
            "filter_field" => array("LABEL"                  => $arrLang["Queue"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "text",
                                    "INPUT_EXTRA_PARAM"      => "no",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),

            "filter_value" => array("LABEL"                  => "",
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrQueue,
                                    "VALIDATION_TYPE"        => "",
                                    "VALIDATION_EXTRA_PARAM" => ""),

//palosanto para detallado y general
            "filter_field_tipo" => array("LABEL"                  => $arrLang["Type"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "text",
                                    "INPUT_EXTRA_PARAM"      => "no",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),

            "filter_value_tipo" => array("LABEL"                  => "",
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrFilter,
                                    "VALIDATION_TYPE"        => "",
                                    "VALIDATION_EXTRA_PARAM" => ""),

//palosanto fecha

            "date_from"    => array("LABEL"                  => $arrLang["Start date"],
                                    "REQUIRED"               => "yes",
                                    "INPUT_TYPE"             => "DATE",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "ereg",
                                    "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),


            "date_to"      => array("LABEL"                  => $arrLang["End date"],
                                    "REQUIRED"               => "yes",
                                    "INPUT_TYPE"             => "DATE",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "ereg",
                                    "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                    );

    return $arrFormElements;
}

//palosanto para obteer colas
function getQueue($pDB, $pDB_asterisk){
    $arrQueue=array();
    $oQueue  = new paloQueue($pDB_asterisk);
    $PBXQueues = $oQueue->getQueue();
    if (is_array($PBXQueues)) {
        foreach($PBXQueues as $key => $value) {
            $query = "SELECT id, queue from queue_call_entry where queue='".$value[0]."'";
            $result=$pDB->getFirstRowQuery($query, true);
            if (is_array($result) && count($result)>0) {
                $arrQueue[$result['id']] =  $result['queue'];
            }
        }
    }
    return $arrQueue;
}


function getParameter($parameter)
{
    if(isset($_POST[$parameter]))
        return $_POST[$parameter];
    else if(isset($_GET[$parameter]))
        return $_GET[$parameter];
    else
        return null;
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