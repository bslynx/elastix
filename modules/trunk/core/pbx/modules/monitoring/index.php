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
  $Id: index.php,v 1.3 2007/09/05 00:26:21 gcarrillo Exp $
  $Id: index.php,v 1.3 2008/04/14 09:22:21 afigueroa Exp $  
  $Id: index.php,v 2.0 2010/02/03 09:00:00 onavarre Exp $  */

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "modules/$module_name/libs/paloSantoMonitoring.class.php";
    //include_once "libs/paloSantoACL.class.php";
    require_once "libs/misc.lib.php";
    
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
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn_agi_manager['password'] = $arrConfig['AMPMGRPASS']['valor'];
    $dsn_agi_manager['host'] = $arrConfig['AMPDBHOST']['valor'];
    $dsn_agi_manager['user'] = 'admin';

        //solo para obtener los devices (extensiones) creadas.
    $dsnAsteriskCdr = $arrConfig['AMPDBENGINE']['valor']."://".
                   $arrConfig['AMPDBUSER']['valor']. ":".
                   $arrConfig['AMPDBPASS']['valor']. "@".
                   $arrConfig['AMPDBHOST']['valor']."/asteriskcdrdb";
    //conexion resource
    $pDBCDR     = new paloDB($dsnAsteriskCdr);    
    
    $pDB = new paloDB($arrConf['elastix_dsn']['acl']);

    if (!empty($pDB->errMsg)) {
        echo "ERROR DE DB: $pDB->errMsg <br>";
    }
    //actions
    $accion = getAction(); //
    $content = ""; //

    switch($accion){
        case "submit_eliminar":
            $content = delete_cdr($smarty, $module_name, $local_templates_dir, $pDB, $pDBCDR, $arrConf, $arrLang);
            break;

        default:
            $content = reportRecordingsCdr($smarty, $module_name, $local_templates_dir, $pDB, $pDBCDR, $arrConf, $arrLang);
            break;
    }
    return $content;    
}


function reportRecordingsCdr($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBCDR, $arrConf, $arrLang)
{
    include_once "libs/paloSantoACL.class.php";    
    //segun el usuario que esta logoneado consulto si tiene asignada extension para buscar los voicemails
    $arrData = array();
    $pACL = new paloACL($pDB);
    $pMonitor = new paloSantoMonitoring($pDBCDR); //

    if (!empty($pACL->errMsg)) {
        echo "ERROR DE ACL: $pACL->errMsg <br>";
    }
    $arrVoiceData = array();
    $llamadas = array();
    $inicio = $fin = $total = 0;
    $extension = $pACL->getUserExtension($_SESSION['elastix_user']);
    $esAdministrador = $pACL->isUserAdministratorGroup($_SESSION['elastix_user']);
    
    $tmpExtension=$extension;
    if($esAdministrador)
        $extension="[[:digit:]]+";

    $date_start = date("Y-m-d") . " 00:00:00";
    $date_end   = date("Y-m-d") . " 23:59:59";
    
    $smarty->assign("menu","monitoring");
    $smarty->assign("Filter",$arrLang['Filter']);
    $arrFormFilterMonitoring = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterMonitoring);    

    $origen = getParameter("source");
    $destino = getParameter("destination");
    if(isset($_POST['filter'])) {
        if(!$oFilterForm->validateForm($_POST)) {
            // Error
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $arrErrores=$oFilterForm->arrErroresValidacion;
            $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
            $strErrorMsg .= "";
            $smarty->assign("mb_message", $strErrorMsg);
    
        } else {
            // Exito, puedo procesar los datos ahora.
            $date_start = translateDate($_POST['date_start']) . " 00:00:00"; 
            $date_end   = translateDate($_POST['date_end']) . " 23:59:59";
            $arrFilterExtraVars = array("date_start" => $_POST['date_start'], "date_end" => $_POST['date_end'] );
        }
        $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);

    }else if (isset($_GET['date_start']) AND isset($_GET['date_end'])) {
        $date_start = translateDate($_GET['date_start']) . " 00:00:00";
        $date_end   = translateDate($_GET['date_end']) . " 23:59:59";
        $arrFilterExtraVars = array("date_start" => $_GET['date_start'], "date_end" => $_GET['date_end']);
        $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_GET);
    } else {
        $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", 
        array('date_start' => date("d M Y"), 'date_end' => date("d M Y")));
    }

    //si tiene extension consulto sino, muestro un mensaje de que no tiene asociada extension
    if (!is_null($extension) && $extension!=""){
        $path = "/var/spool/asterisk/monitor";
        $archivos = array();

        if(file_exists($path)) {
            if ($handle = opendir($path)) {
                $bExito=true;
                while (false !== ($file = readdir($handle))) {
                //no tomar en cuenta . y ..
                    if ($file!="." && $file!="..")
                    {
                        $date = $pMonitor->Files_Between_Dates($file, $extension, $date_start, $date_end, $esAdministrador);
                        if($date!=false)
                            $archivos[] = array(0 => $file, 1 => $date);
                    }
                }
                closedir($handle);
            }
        }else {
            // No vale la ruta
        }

        //Ordenamiento por fechas en orden descendente (nuevos primero)
        $fechas = array();
        //$horas  = array();
        foreach ($archivos as $llave => $valor)
            $fechas[$llave]  = $valor[1];
        array_multisort($fechas,SORT_DESC,$archivos);

        //Paginacion
        $oGrid  = new paloSantoGrid($smarty);
        $action = getParameter("nav"); 
        $start  = getParameter("start"); 
        $limit  = 20; 
        $total  = count($archivos);
        $oGrid->setLimit($limit);
        $oGrid->setTotal($total); 
        $oGrid->calculatePagination($action,$start);
        
        $offset = $oGrid->getOffsetValue();
        $end    = $oGrid->getEnd();

        // Construyo el URL base
        if(isset($arrFilterExtraVars) && is_array($arrFilterExtraVars) and count($arrFilterExtraVars)>0) {
            $url = construirURL($arrFilterExtraVars, array("nav", "start"));
        } else {
            $url = construirURL(array(), array("nav", "start")); 
        }
        $smarty->assign("url", $url);
        //Fin Paginacion

        $llamadas = $pMonitor->getCallsByRecording($offset, $end, $archivos, $pDBCDR, $extension, $esAdministrador, $origen, $destino);

        if($tmpExtension=="" || is_null($tmpExtension))//validacion solo para usuarios del grupo administrator
            $smarty->assign("mb_message", "<b>".$arrLang["You don't have extension number associated with user"]."</b>");
        
        foreach ($llamadas as $llamada){
            $fecha = date("Y-m-d",strtotime($llamada['calldate']));
            $hora = date("H:i:s",strtotime($llamada['calldate']));

            $pathRecordFile="$path/".$llamada['archivo'];
            $arrTmp[0] = "<input type='checkbox' name='".utf8_encode("rcd-".$llamada['archivo'])."' />";
            $arrTmp[1] = $fecha;
            $arrTmp[2] = $hora;
            $arrTmp[3] = empty($llamada['src'])?'-':$llamada['src'];
            $arrTmp[4] = $llamada['dst'];
            $arrTmp[5] = "<label title='".$llamada['duration']." seconds' style='color:green'><u>".SecToHHMMSS( $llamada['duration'] )."</u></label>";
            $arrTmp[6] = $llamada['type'];
            $recordingLink = "<a href='#' onClick=\"javascript:popUp('libs/popup.php?action=display_record&record_file=" . base64_encode($pathRecordFile) ."',350,100); return false;\">{$arrLang['Listen']}</a>&nbsp;";
            $recordingLink .= "<a href='libs/audio.php?recording=".base64_encode($pathRecordFile)."'>{$arrLang['Download']}</a>";
            $arrTmp[7] = $recordingLink;
            $arrData[] = $arrTmp;
        }
    } //fin if (!is_null(extension))
    else {
        $smarty->assign("mb_message", "<b>".$arrLang["You don't have extension number associated with user"]."</b>");
    }

    $arrGrid = array("title"    => $arrLang["Monitorig List"],
                    "icon"     => "images/record.png",
                    "width"    => "99%",
                    "start"    => ($total==0) ? 0 : $offset + 1,
                    "end"      => $end,
                    "total"    => $total,
                    "columns"  => array(0 => array("name"      => "<input type='submit' onClick=\"return confirmSubmit('{$arrLang["Are you sure you wish to delete recordings?"]}');\" name='submit_eliminar' value='{$arrLang["Delete"]}' class='button' />",
                                                "property1" => ""),
                                        1 => array("name"      => $arrLang["Date"],
                                                "property1" => ""),
                                        2 => array("name"      => $arrLang["Time"],
                                                "property1" => ""),
                                        3 => array("name"      => $arrLang["Source"],
                                                "property1" => ""),
                                        4 => array("name"      => $arrLang["Destination"],
                                                "property1" => ""),
                                        5 => array("name"      => $arrLang["Duration"],
                                                "property1" => ""),
                                        6 => array("name"      => $arrLang["Type"],
                                                "property1" => ""),
                                        7 => array("name"      => $arrLang["Message"],
                                                "property1" => ""),
                                    )
                );

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    //end grid parameters

    return $contenidoModulo;

}

function createFieldFilter($arrLang)
{
    $arrFormElements = array("date_start"  => array("LABEL"                  => $arrLang["Start Date"],
                                    "REQUIRED"               => "yes",
                                    "INPUT_TYPE"             => "DATE",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "ereg",
                                    "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                        "date_end"    => array("LABEL"                  => $arrLang["End Date"],
                                    "REQUIRED"               => "yes",
                                    "INPUT_TYPE"             => "DATE",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "ereg",
                                    "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                        "source"   => array(      "LABEL"                  => $arrLang["Source"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                    ),
                        "destination"   => array(      "LABEL"                  => $arrLang["Destination"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                    ),
                );
    return $arrFormElements;
}

function delete_cdr($smarty, $module_name, $local_templates_dir, $pDB, $pDBCDR, $arrConf, $arrLang)
{ 
    $pMonitor = new paloSantoMonitoring($pDBCDR);

    $pMonitor->borrarRecordings($_POST);
    if($oFilterForm->validateForm($_POST)) {
        // Exito, puedo procesar los datos ahora.
        $date_start = translateDate($_POST['date_start']) . " 00:00:00"; 
        $date_end   = translateDate($_POST['date_end']) ." 23:59:59";
        $arrFilterExtraVars = array("date_start" => $_POST['date_start'], "date_end" => $_POST['date_end'] );
    }
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
}

// function borrarRecordings()
// {
//     $path = "/var/spool/asterisk/monitor";
// 
//     if(is_array($_POST) && count($_POST) > 0){
//         foreach($_POST as $name => $on){
//             if(substr($name,0,4)=='rcd-'){
//                 $file = substr($name,4);
//                 $file = str_replace("_",".",$file);
//                 unlink("$path/$file");
//             }
//         }
//     }
// }

function SecToHHMMSS($sec)
{
    $HH = 0;$MM = 0;$SS = 0;
    $segundos = $sec;

    if( $segundos/3600 >= 1 ){ $HH = (int)($segundos/3600);$segundos = $segundos%3600;} if($HH < 10) $HH = "0$HH";
    if(  $segundos/60 >= 1  ){ $MM = (int)($segundos/60);  $segundos = $segundos%60;  } if($MM < 10) $MM = "0$MM";
    $SS = $segundos; if($SS < 10) $SS = "0$SS";

    return "$HH:$MM:$SS";
}

function getAction()
{
    if(getParameter("submit_eliminar"))
        return "submit_eliminar";
    else
        return "filter";
}
?>