<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-18                                               |
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
  $Id: index.php,v 2.0 2010/02/03 09:00:00 onavarre Exp $ 
  $Id: index.php,v 2.1 2010-03-22 05:03:48 Eduardo Cueva ecueva@palosanto.com Exp $ */
//include elastix framework

// exten => s,n,Set(CDR(userfield)=audio:${CALLFILENAME}.${MIXMON_FORMAT})   extensions_additional
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoMonitoring.class.php";
    include_once "libs/paloSantoACL.class.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);

    // Include language file for EN, then for local, and merge the two.
    include_once("modules/$module_name/lang/en.lang");
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) {
        $arrLanEN = $arrLangModule;
        include_once($lang_file);
        $arrLangModule = array_merge($arrLanEN, $arrLangModule);
    }

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
    $arrConf['dsn_conn_database'] = generarDSNSistema('asteriskuser', 'asteriskcdrdb');
    $pDB = new paloDB($arrConf['dsn_conn_database']);
    $pDBACL = new paloDB($arrConf['elastix_dsn']['acl']);

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case 'delete':
            $content = deleteRecord($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
            break;
        case 'download':
            $content = downloadFile($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
            break;
        case "display_record":
            $content = display_record($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
            break;
        default:
            $content = reportMonitoring($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function reportMonitoring($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBACL, $arrConf, $arrLang)
{
    $pMonitoring = new paloSantoMonitoring($pDB);
    $pACL = new paloACL($pDBACL);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $action = getParameter("nav");
    $start  = getParameter("start");
    $as_csv = getParameter("exportcsv");
    $date_ini = getParameter("date_start");
    $date_end = getParameter("date_end");
    $path_record = $arrConf['records_dir'];
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";

    $_POST['date_start'] = isset($date_ini)?$date_ini:date("d M Y");
    $_POST['date_end']   = isset($date_end)?$date_end:date("d M Y");

    if (!empty($pACL->errMsg)) {
        echo "ERROR DE ACL: $pACL->errMsg <br>";
    }

    $extension = $pACL->getUserExtension($user);
    $esAdministrador = $pACL->isUserAdministratorGroup($user);

    $date_initial = date('Y-m-d',strtotime($_POST['date_start']))." 00:00:00"; 
    $date_final   = date('Y-m-d',strtotime($_POST['date_end']))." 23:59:59";

    $_DATA = $_POST;
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    if($esAdministrador)
        $totalMonitoring = $pMonitoring->getNumMonitoring($filter_field, $filter_value, null, $date_initial, $date_final);
    else
        $totalMonitoring = $pMonitoring->getNumMonitoring($filter_field, $filter_value, $extension, $date_initial, $date_final);

    $limit  = 20;
    $total  = $totalMonitoring;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->enableExport();   // enable csv export.
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url = array(
        'menu'          =>  $module_name,
        'filter_field'  =>  $filter_field,
        'filter_value'  =>  $filter_value,
        'date_start'    =>  $_POST['date_start'],
        'date_end'      =>  $_POST['date_end'],
    );

    $arrData = null;

    if($esAdministrador)
        $arrResult =$pMonitoring->getMonitoring($limit, $offset, $filter_field, $filter_value, null, $date_initial, $date_final);
    else
        $arrResult =$pMonitoring->getMonitoring($limit, $offset, $filter_field, $filter_value, $extension, $date_initial, $date_final);

    if($user != "admin" & ($extension=="" || is_null($extension))){
        $smarty->assign("mb_message", "<b>".$arrLang["no_extension"]."</b>");
    }else{

        if($extension=="" || is_null($extension))
            $smarty->assign("mb_message", "<b>".$arrLang["no_extension"]."</b>");

        if(is_array($arrResult) && $total>0){
            $src = "";
            $dst = "";
            foreach($arrResult as $key => $value){
                $arrTmp[0] = "<input type='checkbox' name='id_".$value['uniqueid']."' />";
	            $arrTmp[1] = date('d M Y',strtotime($value['calldate']));
	            $arrTmp[2] = date('H:i:s',strtotime($value['calldate']));
                if(!isset($value['src']) || $value['src']=="")
                    $src = "<font color='gray'>".$arrLang["unknown"]."</font>";
                else
                    $src = $value['src'];
                if(!isset($value['dst']) || $value['dst']=="")
                    $dst = "<font color='gray'>".$arrLang["unknown"]."</font>";
                else
                    $dst = $value['dst'];
	            $arrTmp[3] = $src;
	            $arrTmp[4] = $dst;
	            $arrTmp[5] = "<label title='".$value['duration']." seconds' style='color:green'>".SecToHHMMSS( $value['duration'] )."</label>";

                //$file = base64_encode($value['userfield']);
                $file = $value['uniqueid'];
                switch($value['userfield'][6]){
                    case "O":
                        $arrTmp[6] = $arrLang["Outgoing"];
                        break;
                    case "g":
                        $arrTmp[6] = $arrLang["Group"];
                        break;
                    case "q":
                        $arrTmp[6] = $arrLang["Queue"];
                        break;
                    default :
                        $arrTmp[6] = $arrLang["Incoming"];
                        break;
                }
                $recordingLink = "<a  href=\"javascript:popUp('index.php?menu=$module_name&action=display_record&id=$file&rawmode=yes',350,100);\">{$arrLang['Listen']}</a>&nbsp;";

                $recordingLink .= "<a href='?menu=$module_name&action=download&id=$file&rawmode=yes' >{$arrLang['Download']}</a>";

	            $arrTmp[7] = $recordingLink;
                $arrData[] = $arrTmp;
            }
        }
    }


    $arrGrid = array("title"    => $arrLang["Monitoring"],
                        "icon"     => "images/record.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
            0 => array("name"      => "<input type='submit' onClick=\"return confirmSubmit('{$arrLang["message_alert"]}');\" name='submit_eliminar' value='{$arrLang["Delete"]}' class='button' />",
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

    //begin section filter
    $arrFormFilterMonitoring = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterMonitoring);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("user", $user);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    if($as_csv == 'yes'){
        $name_csv = "Monitoring_".date("d-M-Y").".csv";
        header("Cache-Control: private");
        header("Pragma: cache");
        header("Content-Type: application/octec-stream");
        header("Content-disposition: inline; filename={$name_csv}");
        header("Content-Type: application/force-download");
        $content = $oGrid->fetchGridCSV($arrGrid, $arrData);
    }
    else{
        $oGrid->showFilter(trim($htmlFilter));
        $content = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    }
    //end grid parameters

    return $content;
}

function downloadFile($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang){
    $record = getParameter("id");
    $path_record = $arrConf['records_dir'];
    if (isset($record) && preg_match("/^[[:digit:]]+\.[[:digit:]]+$/",$record)) {
        $pMonitoring = new paloSantoMonitoring($pDB);
        $filebyUid = $pMonitoring->getAudioByUniqueId($record);
        $file = $filebyUid['userfield'];
        $file = str_replace("audio:","",$file);
        $path = $path_record.$file;

    // See if the file exists
        if (!is_file($path)) { 
            die("<b>404 ".$arrLang["no_file"]." </b>");
        }

    // Gather relevent info about file
        $size = filesize($path);
        $name = basename($path);

    //$extension = strtolower(substr(strrchr($name,"."),1));
        $extension=substr(strtolower($name), -3); 

    // This will set the Content-Type to the appropriate setting for the file
        $ctype ='';
        switch( $extension ) {

            case "mp3": $ctype="audio/mpeg"; break;
            case "wav": $ctype="audio/x-wav"; break;
            case "Wav": $ctype="audio/x-wav"; break;
            case "WAV": $ctype="audio/x-wav"; break;
            case "gsm": $ctype="audio/x-gsm"; break;
            // not downloadable
            default: die("<b>404 ".$arrLang["no_file"]." </b>"); break ;
        }

    // need to check if file is mislabeled or a liar.
        $fp=fopen($path, "rb");
        if ($size && $ctype && $fp) {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: wav file");
            header("Content-Type: " . $ctype);
            header("Content-Disposition: attachment; filename=" . $name);
            header("Content-Transfer-Encoding: binary");
            header("Content-length: " . $size);
            fpassthru($fp);
        }
    }else{
        die("<b>404 ".$arrLang["no_file"]." </b>");
    }
}

function display_record($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang){
    $action = getParameter("action");
    $file = getParameter("id");
    $path_record = $arrConf['records_dir'];
        $sContenido="";
        switch($action){
            case "display_record":
                $sContenido=<<<contenido
                    <embed src='index.php?menu=$module_name&action=download&id=$file&rawmode=yes' width=300, height=20 autoplay=true loop=false></embed><br>
contenido;
                break;
        }

        $smarty->assign("CONTENT", $sContenido);
        $smarty->display("_common/popup.tpl");
}

function deleteRecord($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBACL, $arrConf, $arrLang)
{
    $pMonitoring = new paloSantoMonitoring($pDB);
    $_DATA['date_start'] = isset($date_ini)?$date_ini:date("d M Y");
    $_DATA['date_end']   = isset($date_end)?$date_end:date("d M Y");
    $path_record = $arrConf['records_dir'];
    foreach($_POST as $key => $values){
        if(substr($key,0,3) == "id_")
        {
            $ID = substr($key, 3);
            $ID = str_replace("_",".",$ID);
            $recordName = $pMonitoring->getRecordName($ID);

            if($pMonitoring->deleteRecordFile($ID)){
                $record = substr($recordName,6);
                $path = $path_record.$record;
                exec("rm -rf $path");
            }
        }
    }
    $_POST = $_DATA;
    $content = reportMonitoring($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
    return $content;
}

function SecToHHMMSS($sec)
{
    $HH = 0;$MM = 0;$SS = 0;
    $segundos = $sec;

    if( $segundos/3600 >= 1 ){ $HH = (int)($segundos/3600);$segundos = $segundos%3600;} if($HH < 10) $HH = "0$HH";
    if(  $segundos/60 >= 1  ){ $MM = (int)($segundos/60);  $segundos = $segundos%60;  } if($MM < 10) $MM = "0$MM";
    $SS = $segundos; if($SS < 10) $SS = "0$SS";

    return "$HH:$MM:$SS";
}

function createFieldFilter($arrLang){
    $arrFilter = array(
	    "src" => $arrLang["Source"],
	    "dst" => $arrLang["Destination"],
	    "userfield" => $arrLang["Type"],
                    );

    $arrFormElements = array(
            "date_start"  => array("LABEL"                  => $arrLang["Start_Date"],
                                              "REQUIRED"               => "yes",
                                              "INPUT_TYPE"             => "DATE",
                                              "INPUT_EXTRA_PARAM"      => "",
                                              "VALIDATION_TYPE"        => "ereg",
                                              "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
            "date_end"    => array("LABEL"                  => $arrLang["End_Date"],
                                "REQUIRED"               => "yes",
                                "INPUT_TYPE"             => "DATE",
                                "INPUT_EXTRA_PARAM"      => "",
                                "VALIDATION_TYPE"        => "ereg",
                                "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
            "filter_field" => array("LABEL"                  => $arrLang["Search"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrFilter,
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
            "filter_value" => array("LABEL"                  => "",
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
}


function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("action")=="display_record")
        return "display_record";
    else if(getParameter("submit_eliminar")) 
        return "delete";
    else if(getParameter("action")=="download")
        return "download";
    else if(getParameter("action")=="view")   //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("action")=="view_edit")
        return "view_form";
    else
        return "report"; //cancel
}
?>
