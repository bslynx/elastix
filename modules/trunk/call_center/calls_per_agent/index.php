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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:21 gcarrillo Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoDB.class.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoConfig.class.php";
    require_once "libs/misc.lib.php";

    //Incluir librería de lenguaje
    $arrLang=get_language();
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $arrLan_file="modules/$module_name/lang/$arrLang.lang";
    if (file_exists("$script_dir/$arrLan_file"))
        include_once($arrLan_file);
    else
        include_once("modules/$module_name/lang/en.lang");

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoCallPerAgent.class.php";
    global $arrConf;
    global $arrLang;
    $arrCallsAgentTmp  = 0;

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    //$dsn     = $arrConfig['AMPDBENGINE']['valor'] . "://" . $arrConfig['AMPDBUSER']['valor'] . ":" . $arrConfig['AMPDBPASS']['valor'] . "@" .
     //          $arrConfig['AMPDBHOST']['valor'] . "/asteriskcdrdb";
    $pDB     = new paloDB($cadena_dsn);
    $arrData = array();
    $oCallsAgent = new paloSantoCallsAgent($pDB);

    $smarty->assign("menu","calls_per_agent");
    $smarty->assign("Filter",$arrLan['Query']);
    if(isset($_GET['exportcsv']) && $_GET['exportcsv']=='yes') {

        $limit = "";
        $offset = 0;
        if(empty($_GET['date_start'])) {
            $date_start = date("Y-m-d") . " 00:00:00"; 
        } else {
            $date_start = translateDate($_GET['date_start']) . " 00:00:00";
        }
        if(empty($_GET['date_end'])) { 
            $date_end = date("Y-m-d") . " 23:59:59"; 
        } else {
            $date_end   = translateDate($_GET['date_end']) . " 23:59:59";
        }

        $field_name = array('field_name'    =>  $_GET['field_name'],
                                'field_name_1'    =>  $_GET['field_name_1']);
            
        $field_pattern = array('field_pattern' => $_GET['field_pattern'],
                                   'field_pattern_1'=> $_GET['field_pattern_1']);

        //$status = $_GET['status'];
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: application/octec-stream');
        //header('Content-Length: '.strlen($this->buffer));
        header('Content-disposition: inline; filename="calls_per_agent.csv"');
        header('Content-Type: application/force-download');
        //header('Content-Length: '.strlen($this->buffer));
        //header('Content-disposition: attachment; filename="'.$name.'"');

    } else {
    
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
                                 "field_name"  => array("LABEL"                  => $arrLan["Column"],
                                                        "REQUIRED"               => "no",
                                                        "INPUT_TYPE"             => "SELECT",
                                                        "MULTIPLE"               => NULL,
                                                        "SIZE"                   => NULL,
                                                        "INPUT_EXTRA_PARAM"      => array( "number"=> $arrLan["No.Agent"],
                                                                                           "queue"  => $arrLan["Queue"],
                                                                                           "type"   => $arrLan["Type"]),
                                                        "VALIDATION_TYPE"        => "ereg",
                                                        "VALIDATION_EXTRA_PARAM" => "^(number|queue|type)$"),
                                 "field_pattern" => array("LABEL"                  => $arrLan["Column"],
                                                        "REQUIRED"               => "no",
                                                        "INPUT_TYPE"             => "TEXT",
                                                        "INPUT_EXTRA_PARAM"      => "",
                                                        "VALIDATION_TYPE"        => "ereg",
                                                        "VALIDATION_EXTRA_PARAM" => "^[[:alnum:]@_\.,/\-]+$"),

                                "field_name_1"  => array("LABEL"                  => $arrLan["Column"],
                                                        "REQUIRED"               => "no",
                                                        "INPUT_TYPE"             => "SELECT",
                                                        "MULTIPLE"               => NULL,
                                                        "SIZE"                   => NULL,
                                                        "INPUT_EXTRA_PARAM"      => array( "number"=> $arrLan["No.Agent"],
                                                                                            "queue"   => $arrLan["Queue"],
                                                                                            "type"    => $arrLan["Type"]),
                                                        "VALIDATION_TYPE"        => "ereg",
                                                        "VALIDATION_EXTRA_PARAM" => "^(number|queue|type)$"),
                                "field_pattern_1" => array("LABEL"                  => $arrLan["Column"],
                                                        "REQUIRED"               => "no",
                                                        "INPUT_TYPE"             => "TEXT",
                                                        "INPUT_EXTRA_PARAM"      => "",
                                                        "VALIDATION_TYPE"        => "ereg",
                                                        "VALIDATION_EXTRA_PARAM" => "^[[:alnum:]@_\.,/\-]+$"),
                                 /*"status"  => array("LABEL"                  => $arrLang["Status"],
                                                        "REQUIRED"               => "no",
                                                        "INPUT_TYPE"             => "SELECT",
                                                        "MULTIPLE"               => NULL,
                                                        "SIZE"                   => NULL,
                                                        "INPUT_EXTRA_PARAM"      => array(
                                                                                    "ALL"         => "ALL",
                                                                                    "ANSWERED"         => "ANSWERED",
                                                                                    "BUSY"         => "BUSY",
                                                                                    "FAILED"     => "FAILED",
                                                                                    "NO ANSWER "  => "NO ANSWER"),
                                                        "VALIDATION_TYPE"        => "text",
                                                        "VALIDATION_EXTRA_PARAM" => ""),*/
                                 );
    
        $oFilterForm = new paloForm($smarty, $arrFormElements);
    
        // Por omision las fechas toman el sgte. valor (la fecha de hoy)
        $date_start = date("Y-m-d") . " 00:00:00"; 
        $date_end   = date("Y-m-d") . " 23:59:59";
        $field_name = "";
        $field_pattern = ""; 
        $status = "ALL"; 

        if(isset($_POST['filter'])) {
            if($oFilterForm->validateForm($_POST)) {
                // Exito, puedo procesar los datos ahora.
                $date_start = translateDate($_POST['date_start']) . " 00:00:00"; 
                $date_end   = translateDate($_POST['date_end']) . " 23:59:59";
                
                $field_name = array('field_name'    =>  $_POST['field_name'],
                                'field_name_1'    =>  $_POST['field_name_1']);
            
                $field_pattern = array('field_pattern' => $_POST['field_pattern'],
                                   'field_pattern_1'=> $_POST['field_pattern_1']);

               // $status = $_POST['status'];    
                $arrFilterExtraVars = array("date_start" => $_POST['date_start'], 
                                            "date_end" => $_POST['date_end'], 
                                            "field_name" => $_POST['field_name'], 
                                            "field_pattern" => $_POST['field_pattern'],
                                            "field_name_1" => $_POST['field_name_1'], "field_pattern_1" => $_POST['field_pattern_1']/*,
                                            "status" => $_POST['status']*/);
            } else {
                // Error
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $arrErrores=$oFilterForm->arrErroresValidacion;
                $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
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

            $field_name = array('field_name'    =>  $_GET['field_name'],
                                'field_name_1'    =>  $_GET['field_name_1']);
            
            $field_pattern = array('field_pattern' => $_GET['field_pattern'],
                                   'field_pattern_1'=> $_GET['field_pattern_1']);

            $status = $_GET['status'];
            $arrFilterExtraVars = array("date_start" => $_GET['date_start'], "date_end" => $_GET['date_end']);
            $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_GET);
        } else {
            $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", 
                          array('date_start' => date("d M Y"), 'date_end' => date("d M Y"),'field_name' => 'agent','field_pattern' => '','field_name_1' => 'agent','field_pattern_1' => '','status' => 'ALL' ));
        }
    
        // LISTADO
    
        $limit = 50;
        $offset = 0;
        

        $arrCallsAgentTmp  = $oCallsAgent->obtenerCallsAgent(null, $offset, $date_start, $date_end, $field_name, $field_pattern/*,$status*/);

        // Si se quiere avanzar a la sgte. pagina
        if(isset($_GET['nav']) && $_GET['nav']=="end") {
            $totalCallsAgents  = $arrCallsAgentTmp['NumRecords'];
            // Mejorar el sgte. bloque.
            if(($totalCallsAgents%$limit)==0) {
                $offset = $totalCallsAgents - $limit;
            } else {
                $offset = $totalCallsAgents - $totalCallsAgents%$limit;
            }
        }
    
        // Si se quiere avanzar a la sgte. pagina
        if(isset($_GET['nav']) && $_GET['nav']=="next") {
            $offset = $_GET['start'] + $limit - 1;
        }
    
        // Si se quiere retroceder
        if(isset($_GET['nav']) && $_GET['nav']=="previous") {
            $offset = $_GET['start'] - $limit - 1;
        }
    
        // Construyo el URL base
        if(isset($arrFilterExtraVars) && is_array($arrFilterExtraVars) && count($arrFilterExtraVars)>0) {
            $url = construirURL($arrFilterExtraVars, array("nav", "start")); 
        } else {
            $url = construirURL(array(), array("nav", "start")); 
        }
        $smarty->assign("url", $url);
    
    }

    // Bloque comun
    $arrCallsAgent  = $oCallsAgent->obtenerCallsAgent($limit, $offset, $date_start, $date_end, $field_name, $field_pattern/*,$status*/);

    $total = $arrCallsAgentTmp['NumRecords'];
    if(is_array($arrCallsAgent['Data']))
    {
        foreach($arrCallsAgent['Data'] as $cdr) {
            $arrTmp    = array();
            $arrTmp[0] = $cdr[0];
            $arrTmp[1] = $cdr[1];
            $arrTmp[2] = $cdr[2];
            $arrTmp[3] = $cdr[3];
            $arrTmp[4] = $cdr[4];
            $arrTmp[5] = $cdr[5];
            $arrTmp[6] = $cdr[6];
            $arrTmp[7] = $cdr[7];
            $arrData[] = $arrTmp;
        }

        $numRegistros = count($arrData);
        $sumCallAnswered = $sumDuration = 0;
        $avgPromedio = $timeMayor = "00:00:00";
        for($i=0;$i<$numRegistros;$i++){
            $sumCallAnswered = $sumCallAnswered + $arrData[$i][4];
            $sumDuration = $oCallsAgent->getTotalWaitTime($sumDuration,$arrData[$i][5]);
            $avgPromedio = $oCallsAgent->getTotalWaitTime($avgPromedio,$arrData[$i][6]);
            $timeMayor = $oCallsAgent->getFechaMayor($timeMayor,$arrData[$i][7]);
        }

        $arrTmp[0] = "<b>".$arrLan["Total"]."</b>";
        $arrTmp[1] = "";
        $arrTmp[2] = "";
        $arrTmp[3] = "";
	$arrTmp[4] = "<b>".$sumCallAnswered."</b>";
	$arrTmp[5] = "<b>".$sumDuration."</b>";
	$arrTmp[6] = "<b>".$oCallsAgent->getPromedioFecha($avgPromedio,$numRegistros)."</b>";
	$arrTmp[7] = "<b>".$timeMayor."</b>";
        $arrData[] = $arrTmp;
    }
    $arrGrid = array("title"    => $arrLan["Calls per Agent"],
                     "icon"     => "images/user.png",
                     "width"    => "99%",
                     "start"    => ($total==0) ? 0 : $offset + 1,
                     "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
                     "total"    => $total,
                     "columns"  => array(0 => array("name"      => $arrLan["No.Agent"],
                                                    "property" => ""),
                                         1 => array("name"      => $arrLan["Agent"],
                                                    "property" => ""),
                                         2 => array("name"	=> $arrLan["Type"],
                                         	     "property"	=> ""),
                                         3 => array("name"	=> $arrLan["Queue"],
                                                     "property"	=> ""),
                                         4 => array("name"	=> $arrLan["Calls answered"],
                                                     "property"	=> ""),
                                         5 => array("name"	=> $arrLan["Duration"],
                                                     "property"	=> ""),
                                         6 => array("name"	=> $arrLan["Average"],
                                         	     "property"	=> ""),
                                         7 => array("name"	=> $arrLan["Call longest"],
                                         	     "property"	=> ""),
                                        )
                    );

    // Creo objeto de grid
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->enableExport();

    if(isset($_GET['exportcsv']) && $_GET['exportcsv']=='yes') {
        return $oGrid->fetchGridCSV($arrGrid, $arrData);
    } else {
        $oGrid->showFilter($htmlFilter);
        return $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    }
}
?>
