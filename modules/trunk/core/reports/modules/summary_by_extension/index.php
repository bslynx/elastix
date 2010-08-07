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
include_once "libs/paloSantoGraph.class.php";//lib paloGrapf

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
    $pDB_billing = new paloDB("sqlite3:///$arrConf[elastix_dbdir]/rate.db"); //sqlite3 -> rate.db

    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        case 'graph':
            $content = graphLinks($smarty, $module_name, $local_templates_dir);
            break;
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
    $order_type_tmp= getParameter("order_type");
    $action = getParameter("nav");
    $start  = getParameter("start");

    $value     = isset($value_tmp)   ?$value_tmp:"";
    $order_by  = isset($order_by_tmp)?$order_by_tmp:1;
    $order_type= isset($order_type_tmp)?$order_type_tmp:"asc";
    $date_from = isset($date_ini_tmp)?$date_ini_tmp:date("d M Y");
    $date_to   = isset($date_end_tmp)?$date_end_tmp:date("d M Y");

    $date_ini  = translateDate($date_from)." 00:00:00";
    $date_end  = translateDate($date_to)." 23:59:59";

    //**********************************

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);

    $limit  = 40;
    $total  = $pReportCall->ObtainNumberDevices($type,$value);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&option_fil=$type&value_fil=$value&date_from=$date_from&date_to=$date_to";
    $ulr_paging = $url."&order_by=$order_by&order_type=$order_type";

    $smarty->assign("order_by", $order_by);
    $smarty->assign("order_type", $order_type);

    $arrData = null;
    $arrResult = $pReportCall->ObtainReportCall($limit,$offset,$date_ini,$date_end,$type,$value,$order_by,$order_type);

    $order_type = ($order_type == "desc")?"asc":"desc";

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $val){
            $ext = $val['extension'];

            $arrTmp[0] = $ext;
            $arrTmp[1] = $val['user_name'];
            $arrTmp[2] = $val['num_incoming_call'];
            $arrTmp[3] = $val['num_outgoing_call'];
            $arrTmp[4] = "<label style='color: green;' title='{$val['duration_incoming_call']} {$arrLang['seconds']}'>".$pReportCall->Sec2HHMMSS($val['duration_incoming_call'])."</label>";
            $arrTmp[5] = "<label style='color: green;' title='{$val['duration_outgoing_call']} {$arrLang['seconds']}'>".$pReportCall->Sec2HHMMSS($val['duration_outgoing_call'])."</label>";
            $arrTmp[6] = "<a href='javascript: popup_ventana(\"?menu=$module_name&action=graph&rawmode=yes&ext=$ext&dini=$date_ini&dfin=$date_end&num_in=$val[num_incoming_call]&num_out=$val[num_outgoing_call]\");'>".
                    "".$arrLang['Call Details']."</a>";

            $arrData[] = $arrTmp;
        }
    }

    $img = "<img src='images/flecha_$order_type.png' border='0' align='absmiddle'>";
    $style_link_off = "style='text-decoration: none;color:black'";
    $style_link_on  = "style='text-decoration: none;color:blue'";

    $leyend_1 = "<a $style_link_off href='$url&order_by=1&order_type=asc'>{$arrLang["Extension"]}</a>";
    $leyend_2 = "<a $style_link_off href='$url&order_by=2&order_type=asc'>{$arrLang["User name"]}</a>";
    $leyend_3 = "<a $style_link_off href='$url&order_by=3&order_type=asc'>{$arrLang["Num. Incoming Calls"]}</a>";
    $leyend_4 = "<a $style_link_off href='$url&order_by=4&order_type=asc'>{$arrLang["Num. Outgoing Calls"]}</a>";
    $leyend_5 = "<a $style_link_off href='$url&order_by=5&order_type=asc'>{$arrLang["Sec. Incoming Calls"]}</a>";
    $leyend_6 = "<a $style_link_off href='$url&order_by=6&order_type=asc'>{$arrLang["Sec. Outgoing Calls"]}</a>";


    if($order_by == 1)      $leyend_1 = "<a $style_link_on href='$url&order_by=1&order_type=$order_type'>{$arrLang["Extension"]}&nbsp;$img</a>";  
    else if($order_by == 2) $leyend_2 = "<a $style_link_on href='$url&order_by=2&order_type=$order_type'>{$arrLang["User name"]}&nbsp;$img</a>";
    else if($order_by == 3) $leyend_3 = "<a $style_link_on href='$url&order_by=3&order_type=$order_type'>{$arrLang["Num. Incoming Calls"]}&nbsp;$img</a>";  
    else if($order_by == 4) $leyend_4 = "<a $style_link_on href='$url&order_by=4&order_type=$order_type'>{$arrLang["Num. Outgoing Calls"]}&nbsp;$img</a>";  
    else if($order_by == 5) $leyend_5 = "<a $style_link_on href='$url&order_by=5&order_type=$order_type'>{$arrLang["Sec. Incoming Calls"]}&nbsp;$img</a>";  
    else if($order_by == 6) $leyend_6 = "<a $style_link_on href='$url&order_by=6&order_type=$order_type'>{$arrLang["Sec. Outgoing Calls"]}&nbsp;$img</a>";  
    
    $arrGrid = 
        array("title"    => $arrLang["Summary by Extension"],
              "icon"     => "images/list.png",
              "width"    => "100%",
              "start"    => ($total==0) ? 0 : $offset + 1,
              "end"      => $end,
              "total"    => $total,
              "url"      => $ulr_paging,
              "columns"  => array(
		            0 => array("name"      => $leyend_1,
                               "property1" => ""),
		            1 => array("name"      => $leyend_2,
                               "property1" => ""),
		            2 => array("name"      => $leyend_3,
                               "property1" => ""),
                    3 => array("name"      => $leyend_4,
                               "property1" => ""),
                    4 => array("name"      => $leyend_5,
                               "property1" => ""),
                    5 => array("name"      => $leyend_6,
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
                                  "INPUT_EXTRA_PARAM"      => array("Ext"=>$arrLang["Extension"],"User"=>$arrLang["User"]),
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

function graphLinks($smarty, $module_name, $local_templates_dir)
{
    $ext      = $_GET['ext'];
    $date_ini = $_GET['dini'];
    $date_end = $_GET['dfin'];
    $num_in   = $_GET['num_in'];
    $num_out  = $_GET['num_out'];

    $imagen1 = reportTop10Incoming($module_name, $date_ini, $date_end, $ext, $num_in);//PLOT3D
    $imagen2 = reportTop10Outgoing($module_name, $date_ini, $date_end, $ext, $num_out);//PLOT3D
    
    return "<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>
        <tr>
          <td align='center'>$imagen1</td>
        </tr>
        <br/>
        <tr>
          <td align='center'>$imagen2</td>
        </tr>
      </table>";
}

function reportTop10Outgoing($module_name, $date_ini_tmp, $date_end_tmp, $ext, $num_out)//PLOT3D
{
    $arrParameterCallbyGraph = array($date_ini_tmp, $date_end_tmp, $ext, $num_out);
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoReportCall","callbackTop10Salientes",$arrParameterCallbyGraph);
    return $oPaloGraph->getGraph("../../../");
}

function reportTop10Incoming($module_name, $date_ini_tmp, $date_end_tmp, $ext, $num_in)//PLOT3D
{
    $arrParameterCallbyGraph = array($date_ini_tmp, $date_end_tmp, $ext, $num_in);
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoReportCall","callbackTop10Entrantes",$arrParameterCallbyGraph);
    return $oPaloGraph->getGraph("../../../");
}

function getAction()
{
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else if(getParameter("action")=="graph") //Get parameter by GET (command pattern, links)
        return "graph";
    else
        return "report";
}
?>
