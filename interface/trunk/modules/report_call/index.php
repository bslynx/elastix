<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.4-1                                                |
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
  $Id: index.php,v 1.1 2009-01-06 09:01:38 bmacias bmacias@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoGraph.class.php";
include_once "libs/misc.lib.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoReportCall.class.php";
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
                      $arrConfig['AMPDBHOST']['valor']."/asteriskcdrdb";

    $pDB_cdr = new paloDB($dsnAsteriskCdr);//asteriskcdrdb -> CDR
    $pDB_billing = new paloDB("sqlite3:////var/www/db/rate.db"); //sqlite3 -> rate.db

    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        default:
            $content = reportReportCall($smarty, $module_name, $local_templates_dir, $pDB_cdr, $pDB_billing, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function reportReportCall($smarty, $module_name, $local_templates_dir, &$pDB_cdr, &$pDB_billing , $arrConf, $arrLang)
{
    $pReportCall = new paloSantoReportCall($pDB_cdr, $pDB_billing);

    //PARAMETERS
    $type          = getParameter("option_fil");
    $value_tmp     = getParameter("value_fil");
    $date_ini_tmp  = getParameter("date_from");
    $date_end_tmp  = getParameter("date_to");
    $order_by_tmp  = getParameter("order_by");
    $action = getParameter("nav");
    $start  = getParameter("start");

    $value     = isset($value_tmp)   ?$value_tmp:"";
    $order_by  = isset($order_by_tmp)?$order_by_tmp:1;
    $date_from = isset($date_ini_tmp)?$date_ini_tmp:date("d M Y");
    $date_to   = isset($date_end_tmp)?$date_end_tmp:date("d M Y");

    $date_ini  = translateDate($date_from)." 00:00:00";
    $date_end  = translateDate($date_to)." 23:59:59";

    //**********************************

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);

    $limit  = 30;
    $total  = $pReportCall->ObtainNumberDevices($type,$value);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&option_fil=$type&value_fil=$value&date_from=$date_from&date_to=$date_to";
    $smarty->assign("order_by", $order_by);

    $arrData = null;
    $arrResult = $pReportCall->ObtainReportCall($limit,$offset,$date_ini,$date_end,$type,$value,$order_by);//--------------------------------

    $rut_img = "\"modules/report_call/images/graphReport.php";

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $val){
            $ext = $val['extension'];

            $arrTmp[0] = $ext;
            $arrTmp[1] = $val['user_name'];
            $arrTmp[2] = $val['num_incoming_call'];
            $arrTmp[3] = $val['num_outgoing_call'];
            $arrTmp[4] = $val['duration_incoming_call'];
            $arrTmp[5] = $val['duration_outgoing_call'];
            $arrTmp[6] = "<a href='javascript: popup_ventana($rut_img?ext=$ext&dini=$date_ini&dfin=$date_end\");'>".
                    "".$arrLang['More Details']."</a>";

            $arrData[] = $arrTmp;
        }
    }

    $img = "<img src='images/flecha.png' border='0' align='absmiddle'>";
    $leyend_1 = ($order_by == 1) ?"<font color=\"blue\">".$arrLang["Extension"]."</font>": $arrLang["Extension"];
    $leyend_2 = ($order_by == 2) ?"<font color=\"blue\">".$arrLang["User name"]."</font>":$arrLang["User name"];
    $leyend_3 = ($order_by == 3) ?"<font color=\"blue\">".$arrLang["Num. Incoming Call"]."</font>":$arrLang["Num. Incoming Call"];
    $leyend_4 = ($order_by == 4) ?"<font color=\"blue\">".$arrLang["Num. Outgoing Call"]."</font>":$arrLang["Num. Outgoing Call"];
    $leyend_5 = ($order_by == 5) ?"<font color=\"blue\">".$arrLang["Sec. Incoming Call"]."</font>":$arrLang["Sec. Incoming Call"];
    $leyend_6 = ($order_by == 6) ?"<font color=\"blue\">".$arrLang["Sec. Outgoing Call"]."</font>":$arrLang["Sec. Outgoing Call"];

    $arrGrid = 
        array("title"    => $arrLang["Report Call"],
              "icon"     => "images/list.png",
              "width"    => "100%",
              "start"    => ($total==0) ? 0 : $offset + 1,
              "end"      => $end,
              "total"    => $total,
              "url"      => $url."&order_by=$order_by",
              "columns"  => array(
		            0 => array("name"      => $leyend_1."&nbsp;<a href='$url&order_by=1' >".$img."</a>",
                               "property1" => ""),
		            1 => array("name"      => $leyend_2."&nbsp;<a href='$url&order_by=2' >".$img."</a>",
                               "property1" => ""),
		            2 => array("name"      => $leyend_3."&nbsp;<a href='$url&order_by=3'>".$img."</a>",
                               "property1" => ""),
                    3 => array("name"      => $leyend_4."&nbsp;<a href='$url&order_by=4' >".$img."</a>",
                               "property1" => ""),
                    4 => array("name"      => $leyend_5."&nbsp;<a href='$url&order_by=5' >".$img."</a>",
                               "property1" => ""),
                    5 => array("name"      => $leyend_6."&nbsp;<a href='$url&order_by=6' >".$img."</a>",
                               "property1" => ""),
                    6 => array("name"      => "Option",
                               "property1" => ""),
                                        )
                    );

    //begin section filter
    $arrFormFilterReportCall = createFieldForm($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterReportCall);
    $_POST['option_fil'] = $type;
    $_POST['value_fil'] = $value;
    $_POST['date_from'] = $date_from;
    $_POST['date_to']   = $date_to;
    $smarty->assign("SHOW", $arrLang["Show"]);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";

    return $contenidoModulo;
}

function createFieldForm($arrLang){
    $arrFormElements = array(
            "option_fil"=> array( "LABEL"                  => $arrLang["Filter by"],
                                  "REQUIRED"               => "no",
                                  "INPUT_TYPE"             => "SELECT",
                                  "INPUT_EXTRA_PARAM"      => array("Extention"=>$arrLang["Extention"],"User"=>$arrLang["User"]),
                                  "VALIDATION_TYPE"        => "text",
                                  "EDITABLE"               => "yes",
                                  "VALIDATION_EXTRA_PARAM" => ""),
            "value_fil" => array( "LABEL"                  => "",
                                  "REQUIRED"               => "no",
                                  "INPUT_TYPE"             => "TEXT",
                                  "INPUT_EXTRA_PARAM"      => "",
                                  "VALIDATION_TYPE"        => "numeric",
                                  "VALIDATION_EXTRA_PARAM" => ""),
            "date_from" => array( "LABEL"                  => $arrLang["Start date"],
                                  "REQUIRED"               => "yes",
                                  "INPUT_TYPE"             => "DATE",
                                  "INPUT_EXTRA_PARAM"      => array("FORMAT" => "%d %b %Y"),
                                  "VALIDATION_TYPE"        => "ereg",
                                  "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
            "date_to"   => array( "LABEL"                  => $arrLang["End date"],
                                  "REQUIRED"               => "yes",
                                  "INPUT_TYPE"             => "DATE",
                                  "INPUT_EXTRA_PARAM"      => array("FORMAT" => "%d %b %Y"),
                                  "VALIDATION_TYPE"        => "ereg",
                                  "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                    );
    return $arrFormElements;
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
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "report";
}
?>