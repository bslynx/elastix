<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |f
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
require_once "libs/paloSantoDB.class.php";
require_once "libs/paloSantoGrid.class.php";
require_once "libs/misc.lib.php";

if (!function_exists('_tr')) {
    function _tr($s)
    {
        global $arrLang;
        return isset($arrLang[$s]) ? $arrLang[$s] : $s;
    }
}

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    global $arrLang;
    global $arrConfig;
  
    include_once "modules/$module_name/configs/default.config.php";
    include_once "modules/$module_name/libs/paloSantoReportsBreak.class.php";

    // Obtengo la ruta del template a utilizar para generar el filtro.
    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir = (isset($config['templates_dir']))?$config['templates_dir']:'themes';
    $local_templates_dir = "$base_dir/modules/$module_name/".$templates_dir.'/'.$config['theme'];

    // Obtengo el idioma actual utilizado en la aplicacion.
    $Language = get_language();
    $script_dir = dirname($_SERVER['SCRIPT_FILENAME']);

    // Include language file for EN, then for local, and merge the two.
    $arrLangModule = NULL;
    include_once("modules/$module_name/lang/en.lang");
    $arrLangModule_file="modules/$module_name/lang/$Language.lang";
    if (file_exists("$script_dir/$arrLangModule_file")) {
        $arrLanEN = $arrLangModule;
        include_once($arrLangModule_file);
        $arrLangModule = array_merge($arrLanEN, $arrLangModule);
    }
    $arrLang = array_merge($arrLang, $arrLangModule);

    // Abrir conexión a la base de datos
    $pDB = new paloDB($cadena_dsn);
    if (!is_object($pDB->conn) || $pDB->errMsg!="") {
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message", _tr("Error when connecting to database")." ".$pDB->errMsg);
        return NULL;
    }

    // Cadenas estáticas a asignar
    $smarty->assign(array(
        "btn_consultar" =>  _tr('query'),
        "module_name"   =>  $module_name,
    ));
    
    // Verificar si se requiere generar archivo CSV
    $bExportarCSV = (isset( $_GET['exportcsv'] ) && $_GET['exportcsv'] == 'yes');
    
    // Obtener rango de fechas de consulta. Si no existe, se asume día de hoy
    $sFechaInicio = date('d M Y');
    if (isset($_GET['txt_fecha_init'])) $sFechaInicio = $_GET['txt_fecha_init'];
    if (isset($_POST['txt_fecha_init'])) $sFechaInicio = $_POST['txt_fecha_init'];
    $sFechaFinal = date('d M Y');
    if (isset($_GET['txt_fecha_end'])) $sFechaFinal = $_GET['txt_fecha_end'];
    if (isset($_POST['txt_fecha_end'])) $sFechaFinal = $_POST['txt_fecha_end'];
    $arrFilterExtraVars = array(
        "txt_fecha_init"    => $sFechaInicio,
        "txt_fecha_end"     => $sFechaFinal,
    );
    
    // Especificación del formulario de validación
    $arrFormElements = array
    (
        "txt_fecha_init"  => array
        (
            "LABEL"                     => _tr('Start Date'),
            "REQUIRED"                  => "yes",
            "INPUT_TYPE"                => "DATE",
            "INPUT_EXTRA_PARAM"         => "",
            "VALIDATION_TYPE"           => "ereg",
            "VALIDATION_EXTRA_PARAM"    => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"
        ),
        "txt_fecha_end"  => array
        (
            "LABEL"                     => _tr('End Date'),
            "REQUIRED"                  => "yes",
            "INPUT_TYPE"                => "DATE",
            "INPUT_EXTRA_PARAM"         => "",
            "VALIDATION_TYPE"           => "ereg",
            "VALIDATION_EXTRA_PARAM"    => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"
        ),
    );
    $oFilterForm = new paloForm($smarty, $arrFormElements);
    
    // Validación de las fechas recogidas
    if (!$oFilterForm->validateForm($arrFilterExtraVars)) {
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores=$oFilterForm->arrErroresValidacion;
        $strErrorMsg = '<b>'._tr('The following fields contain errors').'</b><br/>';
        foreach($arrErrores as $k => $v) {
            $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);

        $arrFilterExtraVars = array(
            "txt_fecha_init"    => date('d M Y'),
            "txt_fecha_end"     => date('d M Y'),
        );        
    }
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/form.tpl", "", $arrFilterExtraVars);
    $smarty->assign("url", construirURL($arrFilterExtraVars));

    // Obtener fechas en formato yyyy-mm-dd
    $sFechaInicio = translateDate($arrFilterExtraVars['txt_fecha_init']);
    $sFechaFinal = translateDate($arrFilterExtraVars['txt_fecha_end']);

    // Construir la matriz de datos para los breaks de los agentes
    $oReportsBreak = new paloSantoReportsBreak($pDB);
    $datosBreaks = $oReportsBreak->getReportesBreak($sFechaInicio, $sFechaFinal);
    $mapa = array();    // Columna del break dado su ID
    $arrColumnas = array(
        array('name'=> _tr('Agent Number'), 'property1'  => '' ),
        array('name'=> _tr('Agent Name'),   'property1'  => '' )
    );
    $sTagInicio = (!$bExportarCSV) ? '<b>': '';
    $sTagFinal = ($sTagInicio != '') ? '</b>' : '';
    $filaTotales = array($sTagInicio._tr('Total').$sTagFinal, '');
    foreach ($datosBreaks['breaks'] as $idBreak => $sNombreBreak) {
        $mapa[$idBreak] = count($arrColumnas);
        $arrColumnas[] = array('name' => $sNombreBreak, 'property1'  => '' );
        $filaTotales[] = 0; // Total de segundos usado por todos los agentes en este break
    }
    $mapa['TOTAL'] = count($arrColumnas);
    $filaTotales[] = 0; // Total de segundos usado por todos los agentes en todos los breaks
    $arrColumnas[] = array('name' => _tr('Total'), 'property1'  => '' );
    $arrData = array();
    foreach ($datosBreaks['reporte'] as $infoAgente) {
        $filaAgente = array(
            $infoAgente['numero_agente'],
            $infoAgente['nombre_agente'],
        );
        $iTotalAgente = 0;  // Total de segundos usados por agente en breaks

        // Valor inicial de todos los breaks es 0 segundos
        foreach (array_keys($datosBreaks['breaks']) as $idBreak) {
            $filaAgente[$mapa[$idBreak]] = '00:00:00';
        }
        
        // Asignar duración del break para este agente y break
        foreach ($infoAgente['breaks'] as $tuplaBreak) {
            $sTagInicio = (!$bExportarCSV && $tuplaBreak['duracion'] > 0) ? '<font color="green">': '';
            $sTagFinal = ($sTagInicio != '') ? '</font>' : '';
            $filaAgente[$mapa[$tuplaBreak['id_break']]] = $sTagInicio.formatoSegundos($tuplaBreak['duracion']).$sTagFinal;
            $iTotalAgente += $tuplaBreak['duracion'];
            $filaTotales[$mapa[$tuplaBreak['id_break']]] += $tuplaBreak['duracion'];
            $filaTotales[$mapa['TOTAL']] += $tuplaBreak['duracion'];
        }

        // Total para todos los breaks de este agente
        $filaAgente[$mapa['TOTAL']] = formatoSegundos($iTotalAgente);

        $arrData[] = $filaAgente;
    }
    $sTagInicio = (!$bExportarCSV) ? '<b>': '';
    $sTagFinal = ($sTagInicio != '') ? '</b>' : '';
    foreach ($mapa as $iPos) $filaTotales[$iPos] = $sTagInicio.formatoSegundos($filaTotales[$iPos]).$sTagFinal;
    $arrData[] = $filaTotales;

    // defino la cabecera del grid
    $offset = 0;
    $total = count($datosBreaks['reporte']) + 1;
    $limit = $total;

    $arrGrid = array("title"    =>  $arrLangModule['Reports Break'],
	    "icon"     => "images/list.png",
	    "width"    => "99%",
	    "start"    => ($total==0) ? 0 : $offset + 1,
	    "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
	    "total"    => $total,
	    "columns"  => $arrColumnas
	    );
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->enableExport();
    $oGrid->showFilter($htmlFilter);
    if ($bExportarCSV) {
        $title = $sFechaInicio."-".$sFechaFinal;
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: text/csv; charset=utf-8; header=present');
        header("Content-disposition: attachment; filename=\"".$title.".csv\"");
    }
    return $bExportarCSV 
        ? $oGrid->fetchGridCSV($arrGrid, $arrData) 
        : $oGrid->fetchGrid($arrGrid, $arrData, $arrLang);
}

// Formatear segundos como hora:min:seg
function formatoSegundos($iSeg)
{
    $iHora = $iMinutos = $iSegundos = 0;
    $iSegundos = $iSeg % 60; $iSeg = ($iSeg - $iSegundos) / 60;
    $iMinutos = $iSeg % 60; $iSeg = ($iSeg - $iMinutos) / 60;
    $iHora = $iSeg;
    return sprintf('%02d:%02d:%02d', $iHora, $iMinutos, $iSegundos);
}
?>
