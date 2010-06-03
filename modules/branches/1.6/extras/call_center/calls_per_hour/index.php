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
require_once "libs/misc.lib.php";
require_once "libs/paloSantoConfig.class.php";
require_once "libs/paloSantoGrid.class.php";

function _moduleContent(&$smarty, $module_name)
{
    require_once "modules/$module_name/libs/paloSantoCallsHour.class.php";

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
        $arrLan = array_merge($arrLanEN, $arrLan);
    }
    
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    global $arrConf;
    global $arrLang;
    global $arrConfig;

    //folder path for custom templates
    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir = (isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir = "$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    // Conexión a la base de datos CallCenter
    $pDB = new paloDB($arrConf['cadena_dsn']);

    // Mostrar pantalla correspondiente
    $contenidoModulo = '';
    $sAction = 'list_campaign';
    if (isset($_GET['action'])) $sAction = $_GET['action'];
    switch ($sAction) {
    case 'list_histogram':
    default:
        $contenidoModulo = listHistogram($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    }

    return $contenidoModulo;
}

function sumar($a, $b) { return $a + $b; }

function listHistogram($pDB, $smarty, $module_name, $local_templates_dir)
{
    global $arrLang;
    global $arrLan;

    // Tipo de llamada
    $comboTipos = array(
        "E" => $arrLan["Ingoing"],
        "S" => $arrLan["Outgoing"]
    );
    $sTipoLlamada = 'E';
    if (isset($_GET['tipo'])) $sTipoLlamada = $_GET['tipo'];
    if (isset($_POST['tipo'])) $sTipoLlamada = $_POST['tipo'];
    if (!in_array($sTipoLlamada, array_keys($comboTipos))) $sTipoLlamada = 'E';
    $_POST['tipo'] = $sTipoLlamada; // Para llenar el formulario
    $smarty->assign('TIPO', $_POST['tipo']);
    
    // Estado de la llamada
    $comboEstados = array(
        'T' =>  $arrLan['All'],
        'E' =>  $arrLan['Completed'],
        'A' =>  $arrLan['Abandoned'],
    );
    if ($sTipoLlamada == 'S') $comboEstados['N'] = $arrLan['No answer/Short call'];
    $sEstadoLlamada = 'T';
    if (isset($_GET['estado'])) $sEstadoLlamada = $_GET['estado'];
    if (isset($_POST['estado'])) $sEstadoLlamada = $_POST['estado'];
    if (!in_array($sEstadoLlamada, array_keys($comboEstados))) $sEstadoLlamada = 'E';
    $_POST['estado'] = $sEstadoLlamada; // Para llenar el formulario
    $smarty->assign('ESTADO', $_POST['estado']);
    
    // Rango de fechas
    $sFechaInicial = $sFechaFinal = date('Y-m-d');
    if (isset($_GET['fecha_ini'])) $sFechaInicial = date('Y-m-d', strtotime($_GET['fecha_ini']));
    if (isset($_POST['fecha_ini'])) $sFechaInicial = date('Y-m-d', strtotime($_POST['fecha_ini']));
    if (isset($_GET['fecha_fin'])) $sFechaFinal = date('Y-m-d', strtotime($_GET['fecha_fin']));
    if (isset($_POST['fecha_fin'])) $sFechaFinal = date('Y-m-d', strtotime($_POST['fecha_fin']));
    $_POST['fecha_ini'] = date('d M Y', strtotime($sFechaInicial));
    $_POST['fecha_fin'] = date('d M Y', strtotime($sFechaFinal));
    $smarty->assign('FECHA_INI', $sFechaInicial);
    $smarty->assign('FECHA_FIN', $sFechaFinal);

    // Recuperar la lista de llamadas
    $oCalls = new paloSantoCallsHour($pDB);
    $arrCalls = $oCalls->getCalls($sTipoLlamada, $sEstadoLlamada, $sFechaInicial, $sFechaFinal);

    // TODO: manejar error al obtener llamadas
    if (!is_array($arrCalls)) {
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $smarty->assign("mb_message", $oCalls->errMsg);
        $arrCalls = array();
    }

    // Lista de colas a elegir para gráfico. Sólo se elige de las colas devueltas 
    // por la lista de datos.
    $listaColas = array_keys($arrCalls);
    $comboColas = array(
        ''  =>  $arrLan['All'],
    );
    if (count($listaColas) > 0) 
        $comboColas += array_combine($listaColas, $listaColas);
    $sColaElegida = NULL;
    if (isset($_GET['queue'])) $sColaElegida = $_GET['queue'];
    if (isset($_POST['queue'])) $sColaElegida = $_POST['queue'];
    if (!in_array($sColaElegida, $listaColas)) $sColaElegida = '';
    $_POST['queue'] = $sColaElegida; // Para llenar el formulario
    $smarty->assign('QUEUE', $_POST['queue']);

    $smarty->assign('url', construirURL(array(
        'tipo'      =>  $sTipoLlamada,
        'estado'    =>  $sEstadoLlamada,
        'queue'     =>  $sColaElegida,
        'fecha_ini' =>  $sFechaInicial,
        'fecha_fin' =>  $sFechaFinal,
    )));

    // Construir el arreglo como debe mostrarse en la tabla desglose
    $arrData = array();
    $arrTodos = array_fill(0, 24, 0);
    foreach ($arrCalls as $sQueue => $hist) {
        $arrData[] = array_merge(
            array($sQueue),
            $hist,
            array(array_sum($hist))
        );
        $arrTodos = array_map('sumar', $arrTodos, $hist);
    }
    $arrData[] = array_merge(
        array($arrLan['All']),
        $arrTodos,
        array(array_sum($arrTodos))
    );

    $smarty->assign('MODULE_NAME', $module_name);
    $smarty->assign('LABEL_FIND', $arrLan['Find']);
    $formFilter = getFormFilter($comboTipos, $comboEstados, $comboColas);
    $oForm = new paloForm($smarty, $formFilter);

    //Llenamos las cabeceras
    $arrGrid = array("title"    => $arrLan["Calls per hour"],
        "icon"     => "images/list.png",
        "width"    => "99%",
        "start"    => 0,
        "end"      => 0,
        "total"    => 0,
        "columns"  => array(0 => array("name"      => $arrLan["Cola"],
                                       "property1" => ""),
                            // 1..24 se llenan con el bucle de abajo
                            25 => array("name"     => $arrLan["Total Calls"], 
                                       "property1" => ""),

                        ));
    for ($i = 1; $i <= 24; $i++) {
        $arrGrid['columns'][$i] = array('name' => sprintf('%02d:00', $i - 1), 'property1' => '');
    }
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
        $oForm->fetchForm(
            "$local_templates_dir/filter-calls.tpl", 
            NULL,
            $_POST)
    );
    $oGrid->enableExport();
    if (isset($_GET['exportcsv']) && $_GET['exportcsv'] == 'yes') {
        $fechaActual = date("Y-m-d");
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: text/csv; charset=UTF-8; header=present');
        $title = "\"calls-per-hour-".$fechaActual.".csv\"";
        header("Content-disposition: attachment; filename={$title}");
        return $oGrid->fetchGridCSV($arrGrid, $arrData);
    } else {
        return $oGrid->fetchGrid($arrGrid, $arrData, $arrLang);
    }
}

function getFormFilter($arrDataTipo, $arrDataEstado, $arrDataQueues)
{
    global $arrLan;

    $formCampos = array(
        "fecha_ini"       => array(
            "LABEL"                  => $arrLan["Date Init"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
            "INPUT_EXTRA_PARAM"      => array("TIME" => false, "FORMAT" => "%d %b %Y"),
            "VALIDATION_TYPE"        => 'ereg',
            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]{2}[[:space:]]+[[:alpha:]]{3}[[:space:]]+[[:digit:]]{4}$'
        ),
        "fecha_fin"       => array(
            "LABEL"                  => $arrLan["Date End"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
            "INPUT_EXTRA_PARAM"      => array("TIME" => false, "FORMAT" => "%d %b %Y"),
            "VALIDATION_TYPE"        => 'ereg',
            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]{2}[[:space:]]+[[:alpha:]]{3}[[:space:]]+[[:digit:]]{4}$'
        ),
        "tipo" => array(
            "LABEL"                  => $arrLan["Tipo"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrDataTipo,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
        "estado" => array(
            "LABEL"                  => $arrLan["Estado"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrDataEstado,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
        "queue" => array(
            "LABEL"                  => $arrLan["Cola"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrDataQueues,
            "VALIDATION_TYPE"        => "numeric",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
    );

    return $formCampos;
}

?>
