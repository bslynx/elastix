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
  $Id: index.php,v 1.1.1.1 2009/07/27 09:10:19 dlopez Exp $ */

include_once "libs/paloSantoQueue.class.php";

function _moduleContent(&$smarty, $module_name)
{
     include_once "libs/paloSantoGrid.class.php";
     include_once "libs/paloSantoDB.class.php";
     include_once "libs/paloSantoForm.class.php";
     include_once "libs/paloSantoConfig.class.php";
     require_once "libs/misc.lib.php";

     //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoTiempoConexiondeAgentes.class.php";

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
    global $arrLan;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLan);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);
    $pDB_asterisk = new paloDB($arrConf['dsn_conn_database_asterisk']);


    $pDB     = new paloDB($cadena_dsn);
    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        default:
            $content = reportrep_tiempoconexiondeagentes($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $arrLan, $pDB_asterisk);
            break;
    }

    return $content;
}


function reportrep_tiempoconexiondeagentes($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, $arrLan, &$pDB_asterisk)
{

    $arrData = array();
    $oCallsAgent = new paloSantoTiempoConexiondeAgentes($pDB);
    $smarty->assign("menu","tiempo_conexion_agentes");
    $smarty->assign("Filter",$arrLang['Show']);


//para la cola
    // se obtiene el arreglo con las colas para mostrarlas en el filtro
    $arrQueue = getQueue($pDB, $pDB_asterisk);

    // valores del filtro
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");

    $filter_pattern = getParameter("filter_pattern");

    // para setear la cola la primera vez
    $filter_value = getParameter("filter_value");
    if (!isset($filter_value)) {
        $queue = array_shift(array_keys($arrQueue));
        $_POST["filter_value"] = $queue;
        $filter_value = $queue;
    }

    //Esto es para validar cuando recien se entra al modulo, para q aparezca seteado un numero de agente en el textbox
    if(!isset($filter_pattern)){
        $agente = $oCallsAgent->obtener_agente();
        $_POST['file_pattern'] = $agente;
        $filter_pattern = $agente;
    }


    $arrFormElements = createFieldFilter($arrLang, $arrLan, $arrQueue);
    $oFilterForm = new paloForm($smarty, $arrFormElements);

    // Por omision las fechas toman el sgte. valor (la fecha de hoy)
    $date_start = date("Y-m-d") . " 00:00:00"; 
    $date_end   = date("Y-m-d") . " 23:59:59";
    $status = "ALL"; 

    if(isset($_POST['submit'])) {
            if($oFilterForm->validateForm($_POST)) {
                // Exito, puedo procesar los datos ahora.
                $date_start = translateDate($_POST['date_start']) . " 00:00:00"; 
                $date_end   = translateDate($_POST['date_end']) . " 23:59:59";

                $arrFilterExtraVars = array("date_start" => $_POST['date_start'], 
                                            "date_end" => $_POST['date_end'], 
                                            "filter_pattern" => $_POST['filter_pattern'],
                                            "filter_value" => $_POST['filter_value'],);
            } else {
                // Error
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $arrErrores=$oFilterForm->arrErroresValidacion;
                $strErrorMsg = "<b>{$arrLang['Required field']}:</b><br>";
                foreach($arrErrores as $k=>$v) {
                    $strErrorMsg .= "$k, ";
                }
                $strErrorMsg .= "";
                $smarty->assign("mb_message", $strErrorMsg);
            }
            $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    
    } else if(isset($_GET['date_start']) AND isset($_GET['date_end'])) {
            $date_start = translateDate($_GET['date_start']) . " 00:00:00";
            $date_end   = translateDate($_GET['date_end']) . " 23:59:59";
            $arrFilterExtraVars = array("date_start" => $_GET['date_start'], "date_end" => $_GET['date_end']);
            $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_GET);
    } else {
            $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", 
                          array('date_start' => date("d M Y"), 'date_end' => date("d M Y"),'filter_pattern' => $agente,'filter_field'=>'', 'filter_value'=>'','status' => 'ALL' ));
    }


    $action = getParameter("nav");
    $start  = getParameter("start");
    $iscsv  = getParameter("exportcsv");

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->enableExport();
    $limit  = 20;

    $oGrid->setLimit($limit);    
    $offset = $oGrid->getOffsetValue();
    $arrCallsAgent  = $oCallsAgent->Obtainrep_tiempoConexionAgentes($limit, $offset, $arrLan, $filter_field, $filter_value, $filter_pattern, $date_start, $date_end);
    $total = count($arrCallsAgent['Data']);

    $oGrid->setTotal($total);
    $oGrid->calculatePagination($action,$start);
    $end    = $oGrid->getEnd();

    //esto es solo para el caso csv
    if(isset($_POST['date_start']) && isset($_POST['date_end'])){
        $date_inicio = $_POST['date_start'];
        $date_fin = $_POST['date_end'];
    }else{
        $date_inicio = date('d M Y',strtotime($date_start));
        $date_fin = date('d M Y',strtotime($date_end));
    }

    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value&filter_pattern=$filter_pattern&date_start=".$date_inicio."&date_end=".$date_fin;

    $arrTmp = array();

//Armamos el arreglo para la vista
    $arr_data = array();
    if(is_array($arrCallsAgent['Data']) && count($arrCallsAgent['Data'])>0)
    { 
            //idiomas para los lables de la primera columna
            $arr_data[0]['label1'] = "<b>".strtoupper($arrLan['Agent name'])."</b>";
            $arr_data[1]['label1'] = "<b>".strtoupper($arrLan['Conecction Data'])."</b>";
            $arr_data[2]['label1'] = $arrLan['First Conecction'];
            $arr_data[3]['label1'] = $arrLan['Last Conecction'];
            $arr_data[4]['label1'] = $arrLan['Time Conecction'];
            $arr_data[5]['label1'] = $arrLan['Count Conecction'];
            $arr_data[6]['label1'] = "<b>".strtoupper($arrLan['Calls Entry'])."</b>";
            $arr_data[7]['label1'] = $arrLan['Count Calls Entry'];
            $arr_data[8]['label1'] = $arrLan['Calls/h'];
            $arr_data[9]['label1'] = $arrLan['Time Call Entry'];
            $arr_data[10]['label1'] = $arrLan['Average Calls Entry'];
            $arr_data[11]['label1'] = "<b>".strtoupper($arrLan['Reason No Ready'])."</b>";
            $arr_data[12]['label1'] = "<u><b>".$arrLan['Break']."</b></u>";

            // para mostrar todos los breaks del agente
            $ind=13;
            foreach($arrCallsAgent['Data'] as $key=>$datos) {
                if ($key!=0 && !is_numeric($datos[0])) {
                    $arr_data[$ind]['label1'] = utf8_decode($datos[0]);
                    $arr_data[$ind]['data1'] = $datos[1];
                    $arr_data[$ind]['label2'] = $datos[2];
                    $arr_data[$ind]['data2'] = number_format($datos[3], 2)." %";
                    $ind++; 
                }
            }

            //Cabeceras Breaks
            $arr_data[12]['label1'] = "<u><b>".$arrLan['Break']."</b></u>";
            $arr_data[12]['data1'] = "<u><b>".$arrLan['Count']."</b></u>";
            $arr_data[12]['label2'] = "<u><b>".$arrLan['Hour']."</b></u>";
            $arr_data[12]['data2'] = "<u><b>".$arrLan['Porcent compare whit time not ready']."</b></u>";

            //Nombre del agente
            $arr_data[0]['data1'] = isset($arrCallsAgent['Data'][0]['4'])?utf8_decode($arrCallsAgent['Data'][0]['4']):"";

            //Conexiones
            $arr_data[2]['data1'] = isset($arrCallsAgent['Data'][0]['0'])?$arrCallsAgent['Data'][0]['0']:"";
            $arr_data[3]['data1'] = isset($arrCallsAgent['Data'][0]['1'])?$arrCallsAgent['Data'][0]['1']:"";
            $arr_data[4]['data1'] = isset($arrCallsAgent['Data'][0]['2'])?$arrCallsAgent['Data'][0]['2']:"";
            $arr_data[5]['data1'] = isset($arrCallsAgent['Data'][0]['3'])?$arrCallsAgent['Data'][0]['3']:"";

            //Llamadas entrantes
            //validamos que no sean breaks
            if(isset($arrCallsAgent['Data'][1]['0']) && !is_numeric($arrCallsAgent['Data'][1]['0'])){
                $arr_data[7]['data1'] = "";
                $arr_data[8]['data1'] = "";
                $arr_data[9]['data1'] = "";
                $arr_data[10]['data1'] = "";
            }else{
                //validamos para poner llamadas monitoreadas y no monitoreadas
                $arr_data[7]['data1'] = isset($arrCallsAgent['Data'][1]['0'])?$arrCallsAgent['Data'][1]['4']."  ".$arrLan['Call']."s   (".$arrCallsAgent['Data'][1]['0']." ".$arrLan['Monitored'].",  ".($arrCallsAgent['Data'][1]['4']-$arrCallsAgent['Data'][1]['0'])." ".$arrLan['Unmonitored'].")":"";

                $arr_data[8]['data1'] = isset($arrCallsAgent['Data'][1]['1'])?number_format($arrCallsAgent['Data'][1]['1'], 2):"";
                $arr_data[9]['data1'] = isset($arrCallsAgent['Data'][1]['2'])?$arrCallsAgent['Data'][1]['2']:"";
                $arr_data[10]['data1'] = isset($arrCallsAgent['Data'][1]['3'])?$arrCallsAgent['Data'][1]['3']."    (".$arrLan['Monitored only'].")":"";
            }

    } else {
        $arr_data[]["label1"]="<b>".$arrLang["There aren't records to show"]."</b>";
    }
//fin de armar arreglo

    $total = $arrCallsAgent['NumRecords'];

    if(is_array($arr_data))
    {
        foreach($arr_data as $ind=>$dato) {
            $arrTmp[0] = $dato["label1"];
            $arrTmp[1] = isset($dato["data1"])?$dato["data1"]:"";
            $arrTmp[2] = isset($dato["label2"])?$dato["label2"]:"";
            $arrTmp[3] = isset($dato["data2"])?$dato["data2"]:"";
            $arrData[] = $arrTmp;
        }
    }

    // se crea el grid
    $arrGrid = array("title"    => $arrLang["Time conecction of agents"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
                                         0 => array("name"      => "",
                                                    "property" => ""),
                                         1 => array("name"      => "",
                                                    "property" => ""),
                                         2 => array("name"      => "",
                                                    "property" => ""),
                                         3 => array("name"      => "",
                                                    "property" => ""),
                                        )
                    );

    // Creo objeto de grid
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->enableExport();

    $oGrid->showFilter($htmlFilter);

    // se pregunta si la acci� es crear un csv con los datos del reporte 
    if($iscsv != 'yes'){
        $oGrid->showFilter(trim($htmlFilter));
        return $content = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";

    }
    else{
        $fechaActual = date("d M Y");
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: application/octec-stream');
        $title = "\"".$fechaActual.".csv\"";
        header("Content-disposition: inline; filename={$title}");
        header('Content-Type: application/force-download');
	return $content = $oGrid->fetchGridCSV($arrGrid, $arrData);
    }
    //end grid parameters

}

function createFieldFilter($arrLang, $arrLan, $arrQueue){

    

    $arrFormElements = array("date_start"  => array("LABEL"                  => $arrLan['Start Date'],
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "DATE",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "ereg",
                                        "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                                 "date_end"    => array("LABEL"                  => $arrLan["End Date"],
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "DATE",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "ereg",
                                        "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),

                                 "filter_pattern" => array("LABEL"                  => $arrLan["No.Agent"],
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "ereg",
                                        "VALIDATION_EXTRA_PARAM" => "^[[:alnum:]@_\.,/\-]+$"),


                                //COLA
                                "filter_field" => array("LABEL"                  => $arrLan["Queue"],
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

                                 );
    return $arrFormElements;
}


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
    if(getParameter("submit")) //Get parameter by POST (submit)
        return "submit";
    else if(getParameter("new"))
        return "new";
    else if(getParameter("action")=="submit") //Get parameter by GET (command pattern, links)
        return "submit";
    else
        return "report";
}
?>
