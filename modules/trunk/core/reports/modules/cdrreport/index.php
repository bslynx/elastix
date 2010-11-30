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

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoDB.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoCDR.class.php";
require_once "libs/misc.lib.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    load_language_module($module_name);

    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    // DSN para consulta de cdrs
    $dsn = generarDSNSistema('asteriskuser', 'asteriskcdrdb');
    $pDB     = new paloDB($dsn);
    $oCDR    = new paloSantoCDR($pDB);

    $pDBACL = new paloDB($arrConf['elastix_dsn']['acl']);
    if (!empty($pDBACL->errMsg)) {
        return "ERROR DE DB: $pDBACL->errMsg";
    }
    $pACL = new paloACL($pDBACL);
    if (!empty($pACL->errMsg)) {
        return "ERROR DE ACL: $pACL->errMsg";
    }

    // Para usuarios que no son administradores, se restringe a los CDR de la
    // propia extensión
    $sExtension = $pACL->isUserAdministratorGroup($_SESSION['elastix_user']) 
        ? '' 
        : $pACL->getUserExtension($_SESSION['elastix_user']);

    // Cadenas estáticas en la plantilla
    $smarty->assign(array(
        "Filter"    =>  _tr("Filter"),
        "Delete"    =>  _tr("Delete"),
        "Delete_Warning"    =>  _tr("Are you sure you wish to delete CDR(s) Report(s)?"),
    ));

    $arrFormElements = array(
        "date_start"  => array("LABEL"                  => _tr("Start Date"),
                            "REQUIRED"               => "yes",
                            "INPUT_TYPE"             => "DATE",
                            "INPUT_EXTRA_PARAM"      => "",
                            "VALIDATION_TYPE"        => "ereg",
                            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
        "date_end"    => array("LABEL"                  => _tr("End Date"),
                            "REQUIRED"               => "yes",
                            "INPUT_TYPE"             => "DATE",
                            "INPUT_EXTRA_PARAM"      => "",
                            "VALIDATION_TYPE"        => "ereg",
                            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
        "field_name"  => array("LABEL"                  => _tr("Field Name"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "SELECT",
                            "INPUT_EXTRA_PARAM"      => array( "dst"         => _tr("Destination"),
                                                               "src"         => _tr("Source"),
                                                               "channel"     => _tr("Src. Channel"),
                                                               "accountcode" => _tr("Account Code"),
                                                               "dstchannel"  => _tr("Dst. Channel")),
                            "VALIDATION_TYPE"        => "ereg",
                            "VALIDATION_EXTRA_PARAM" => "^(dst|src|channel|dstchannel|accountcode)$"),
        "field_pattern" => array("LABEL"                  => _tr("Field"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "TEXT",
                            "INPUT_EXTRA_PARAM"      => "",
                            "VALIDATION_TYPE"        => "ereg",
                            "VALIDATION_EXTRA_PARAM" => "^[\*|[:alnum:]@_\.,/\-]+$"),
        "status"  => array("LABEL"                  => _tr("Status"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "SELECT",
                            "INPUT_EXTRA_PARAM"      => array(
                                                        "ALL"         => _tr("ALL"),
                                                        "ANSWERED"    => _tr("ANSWERED"),
                                                        "BUSY"        => _tr("BUSY"),
                                                        "FAILED"      => _tr("FAILED"),
                                                        "NO ANSWER "  => _tr("NO ANSWER")),
                            "VALIDATION_TYPE"        => "text",
                            "VALIDATION_EXTRA_PARAM" => ""),
        );

    $oFilterForm = new paloForm($smarty, $arrFormElements);

    // Parámetros base y validación de parámetros
    $url = array('menu' => $module_name);
    $paramFiltroBase = $paramFiltro = array(
        'date_start'    => date("d M Y"), 
        'date_end'      => date("d M Y"),
        'field_name'    => 'dst',
        'field_pattern' => '',
        'status'        => 'ALL' 
    );
    foreach (array_keys($paramFiltro) as $k) {
        if (isset($_GET[$k])) $paramFiltro[$k] = $_GET[$k];
        if (isset($_POST[$k])) $paramFiltro[$k] = $_POST[$k];
    }
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $paramFiltro);
    if (!$oFilterForm->validateForm($paramFiltro)) {
        $smarty->assign(array(
            'mb_title'      =>  _tr('Validation Error'),
            'mb_message'    =>  '<b>'._tr('The following fields contain errors').':</b><br/>'.
                                implode(', ', array_keys($oFilterForm->arrErroresValidacion)),
        ));
        $paramFiltro = $paramFiltroBase;
        unset($_POST['delete']);    // Se aborta el intento de borrar CDRs, si había uno.
    }

    // Ejecutar el borrado, si se ha validado.
    if (isset($_POST['delete'])) {
        $r = $oCDR->Delete_All_CDRs(
            $paramFiltro['date_start'],
            $paramFiltro['date_end'],
            $paramFiltro['field_name'],
            $paramFiltro['field_pattern'],
            $paramFiltro['status'],
            "", NULL, $sExtension);
        if (!$r) $smarty->assign(array(
            'mb_title'      =>  _tr('ERROR'),
            'mb_message'    =>  $oCDR->errMsg,
        ));
    }
    
    $url = array_merge($url, $paramFiltro);
    $paramFiltro['date_start'] = translateDate($paramFiltro['date_start']).' 00:00:00';
    $paramFiltro['date_end'] = translateDate($paramFiltro['date_end']).' 23:59:59';

    // Generación del reporte
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->enableExport();
    if (isset($_GET['exportcsv']) || isset($_GET['exportspreadsheet']) || isset($_GET['exportpdf'])) {
        $limit = "";
        $offset = 0;
    } else {
        $limit = 50;
        $arrCDR = $oCDR->obtenerCDRs($limit, 0, 
            $paramFiltro['date_start'],
            $paramFiltro['date_end'],
            $paramFiltro['field_name'],
            $paramFiltro['field_pattern'],
            $paramFiltro['status'],
            "", NULL, $sExtension);
        $total = $arrCDR['NumRecords'][0];
        $offset = $oGrid->getOffSet($limit, $total,
            isset($_GET['nav']) ? $_GET['nav'] : NULL,
            isset($_GET['start']) ? $_GET['start'] : NULL);
    }
    $arrCDR = $oCDR->obtenerCDRs($limit, $offset, 
        $paramFiltro['date_start'],
        $paramFiltro['date_end'],
        $paramFiltro['field_name'],
        $paramFiltro['field_pattern'],
        $paramFiltro['status'],
        "", NULL, $sExtension);
    $total = $arrCDR['NumRecords'][0];

    $arrData = array();
    if (!is_array($arrCDR)) {
        $smarty->assign(array(
            'mb_title'      =>  _tr('ERROR'),
            'mb_message'    =>  $oCDR->errMsg,
        ));
    } else {
        function _formatCDR($cdr) {
            $iDuracion = $cdr[8];
            $iSec = $iDuracion % 60; $iDuracion = (int)(($iDuracion - $iSec) / 60);
            $iMin = $iDuracion % 60; $iDuracion = (int)(($iDuracion - $iMin) / 60);
            $sTiempo = "{$cdr[8]}s";
            if ($cdr[8] >= 60) {
                $sTiempo .= '(';
                if ($iDuracion > 0) $sTiempo .= " ({$iDuracion}h {$iMin}m {$iSec}s)";
                elseif ($iMin > 0)  $sTiempo .= " ({$iMin}m {$iSec}s)";
            }
            return array($cdr[0], $cdr[1], $cdr[2], $cdr[3], $cdr[9], $cdr[4], $cdr[5], $sTiempo);
        }
        $arrData = array_map('_formatCDR', $arrCDR['Data']);
    }

    $arrGrid = array("title"    => _tr("CDR Report List"),
                     "url"      => $url,
                     "icon"     => "images/user.png",
                     "width"    => "99%",
                     "start"    => ($total == 0) ? 0 : $offset + 1,
                     "end"      => ($offset + $limit) <= $total ? $offset + $limit : $total,
                     "total"    => $total,
                     "columns"  => array(
                                         0 => array("name"      => _tr("Date"),
                                                    "property1" => ""),
                                         1 => array("name"      => _tr("Source"),
                                                    "property1" => ""),
                                         2 => array("name"      => _tr("Destination"),
                                                    "property1" => ""),
                                         3 => array("name"      => _tr("Src. Channel"),
                                                    "property"  => ""),
                                         4 => array("name"      => _tr("Account Code"),
                                                    "property"  => ""),
                                         5 => array("name"      => _tr("Dst. Channel"),
                                                    "property"  => ""),
                                         6 => array("name"      => _tr("Status"),
                                                    "property"  => ""),
                                         7 => array("name"      => _tr("Duration"),
                                                    "property"  => ""),
                                        )
                    );

    $oGrid->showFilter($htmlFilter);
    global $arrLang;
    return $oGrid->fetchGrid($arrGrid, $arrData, $arrLang);
}
?>
