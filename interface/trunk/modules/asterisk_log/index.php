<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.1-4                                               |
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
  $Id: default.conf.php,v 1.1 2008-06-13 04:06:20 Alex Villacís Lasso Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    //include elastix framework
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoForm.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoAsteriskLogs.class.php";
    global $arrConf;
    global $arrLang;

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $accion = getAction();

    $content = "";
    switch($accion)
    {
        default:
            $content = report_AsteriskLogs($smarty, $module_name, $local_templates_dir, $arrLang);
            break;
    }

    return $content;
}

function report_AsteriskLogs($smarty, $module_name, $local_templates_dir, $arrLang)
{
    $arrFormElements = array(
            "filter"            => array(   "LABEL"                  => $arrLang["Date"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => NULL,
                                            "VALIDATION_TYPE"        => "ereg",
                                            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]{4}-[[:digit:]]{2}-[[:digit:]]{2}$'),
            "offset"            => array(   "LABEL"                  => $arrLang["offset"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "hidden",
                                            "INPUT_EXTRA_PARAM"      => NULL,
                                            "VALIDATION_TYPE"        => "ereg",
                                            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]+$'),
                                );

    $smarty->assign("SHOW", $arrLang["Show"]);
    $field_pattern = getParameter("filter");

    $pAsteriskLogs = new paloSantoAsteriskLogs($pDB);

    $listaFechas = $pAsteriskLogs->astLog->listarFechas();
    
    if (!ereg($arrFormElements['filter']['VALIDATION_EXTRA_PARAM'], $field_pattern))
        $field_pattern = $listaFechas[count($listaFechas) - 1];
    $_POST['filter'] = $field_pattern;
    $total_datos = $pAsteriskLogs->ObtainNumAsteriskLogs($field_pattern);
    $comboFechas = array();
    foreach ($listaFechas as $f) $comboFechas[$f] = $f;

    $arrFormElements['filter']['INPUT_EXTRA_PARAM'] = $comboFechas;
    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);

    $oGrid  = new paloSantoGrid($smarty);

/*
    //Paginacion
    $limit  = 15 * 128;
    $total  = $total_datos[0];

    $offset = $oGrid->getOffSet(
        $limit,
        $total,
        (isset($_GET['nav'])) ? $_GET['nav'] : NULL,
        (isset($_GET['start'])) ? $_GET['start'] : NULL);

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;

    $url = "?menu=$module_name&filter=$field_pattern";
    $smarty->assign("url", $url);
    //Fin Paginacion

    $arrResult =$pAsteriskLogs->ObtainAsteriskLogs($limit, $offset, $field_pattern);
    $posLog = $pAsteriskLogs->astLog->obtenerPosicionMensaje();
    $arrData = null;
    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
            $arrTmp[0] = $value['fecha'];
            $arrTmp[1] = $value['tipo'];
            $arrTmp[2] = $value['linea'];

            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => "Asterisk Logs",
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "columns"  => array(0 => array("name"      => 'Date',
                                                    "property1" => ""),

                                            1 => array("name"      => 'Type',
                                                    "property1" => ""),
                                            2 => array("name"      => 'Message',
                                                    "property1" => "")
                                        )
                    );

*/
    $iNumLineasPorPagina = 30;
    $iEstimadoBytesPagina = $iNumLineasPorPagina * 128;

    $iOffsetVerdadero = getParameter('offset');
    if (is_null($iOffsetVerdadero) || !ereg('^[[:digit:]]+$', $iOffsetVerdadero)) {
        $iOffsetVerdadero = 0;
    }
    $totalBytes = $total_datos[0];
    if ($iOffsetVerdadero >= $totalBytes) $iOffsetVerdadero = 0;

    if (isset($_GET['filter']) && isset($_POST['filter']) && $_GET['filter'] != $_POST['filter'])
        $iOffsetVerdadero = 0;

    /* Para ubicarse en la página, se obtiene la región 5 páginas estimadas hacia delante y 
       5 páginas estimadas hacia atrás desde el offset indicado.
     */
    $inicioRango = $iOffsetVerdadero - 5 * $iEstimadoBytesPagina;
    if ($inicioRango < 0) $inicioRango = 0;
    $arrResult =$pAsteriskLogs->ObtainAsteriskLogs(10 * $iEstimadoBytesPagina, $inicioRango, $field_pattern);
    
    /* Localizar la línea del offset verdadero, así como los offsets de las páginas previa y siguiente */
    for ($iPos = 0; $iPos < count($arrResult); $iPos++) {
        if ($arrResult[$iPos]['offset'] >= $iOffsetVerdadero) break;
    }
    $iPosPrevio = $iPos - $iNumLineasPorPagina;
    $iPosSiguiente = $iPos + $iNumLineasPorPagina;
    if ($iPosPrevio < 0) $iPosPrevio = 0;
    if ($iPosSiguiente > count($arrResult) - 1) $iPosSiguiente = count($arrResult) - 1;
    $iOffsetPrevio = $arrResult[$iPosPrevio]['offset'];
    $iOffsetSiguiente = $arrResult[$iPosSiguiente]['offset'];

    $offset = $iOffsetVerdadero;
    if (isset($_GET['nav'])) switch ($_GET['nav']) {
    case 'start':
        $offset = 0;
        break;
    case 'end':
        /* Caso especial: se debe tomar la última sección del log */
        $inicioRango = $totalBytes - 5 * $iEstimadoBytesPagina;
        if ($inicioRango < 0) $inicioRango = 0;
        $arrResult =$pAsteriskLogs->ObtainAsteriskLogs(10 * $iEstimadoBytesPagina, $inicioRango, $field_pattern);
        if (count($arrResult) <= $iNumLineasPorPagina)
            $offset = $arrResult[0]['offset'];
        else $offset = $arrResult[count($arrResult) - $iNumLineasPorPagina]['offset'];
        break;
    case 'next':
        $offset = $iOffsetSiguiente;
        break;
    case 'previous':
        $offset = $iOffsetPrevio;
        break;
    }

    $url = "?menu=$module_name&filter=$field_pattern&offset=$offset";
    $smarty->assign("url", $url);
    //Fin Paginacion

    $arrResult =$pAsteriskLogs->ObtainAsteriskLogs(10 * $iEstimadoBytesPagina, $offset, $field_pattern);
    $arrResult = array_slice($arrResult, 0, $iNumLineasPorPagina);
    $posLog = $pAsteriskLogs->astLog->obtenerPosicionMensaje();
    $arrData = null;
    if(is_array($arrResult) && $totalBytes>0){
        foreach($arrResult as $key => $value){
            $arrTmp[0] = $value['fecha'];
            $arrTmp[1] = $value['tipo'];
            $arrTmp[2] = $value['origen'];
            $arrTmp[3] = $value['linea'];

            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => "Asterisk Logs",
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($totalBytes==0) ? 0 : 1 + (int)($offset / 128),
                        "end"      => (int)($offset / 128) + $iNumLineasPorPagina,
                        "total"    => (int)($totalBytes / 128),
                        "columns"  => array(0 => array("name"      => 'Date',
                                                    "property1" => ""),

                                            1 => array("name"      => 'Type',
                                                    "property1" => ""),
                                            2 => array("name"      => 'Source',
                                                    "property1" => ""),
                                            3 => array("name"      => 'Message',
                                                    "property1" => "")
                                        )
                    );



    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = 
        "<form  method='POST' style='margin-bottom:0;' action=$url>".
        $oGrid->fetchGrid($arrGrid, $arrData,$arrLang).
        "</form>";

    return $contenidoModulo;
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
}
?>
