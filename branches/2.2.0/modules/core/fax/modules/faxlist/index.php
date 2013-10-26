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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $ */

include_once "libs/paloSantoFax.class.php";
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent($smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
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

    $accion = getAction();
    switch($accion){
        case "checkFaxStatus":
            $contenidoModulo = checkFaxStatus("faxListStatus",$smarty, $module_name, $local_templates_dir, $arrConf, $arrLang);
            break;
        default:
            $contenidoModulo = listFax($smarty, $module_name, $local_templates_dir);
            break;
    }
    return $contenidoModulo;
}

function listFax($smarty, $module_name, $local_templates_dir)
{
    global $arrLang;
    $arrData = array();
    $oFax    = new paloFax();
    $arrFax  = $oFax->getFaxList();

    $end = count($arrFax);
    $arrFaxStatus = $oFax->getFaxStatus();
 
    foreach($arrFax as $fax) {
        $arrTmp    = array();
        $arrTmp[0] = "&nbsp;<a href='?menu=faxnew&action=view&id=".$fax['id']."'>".$fax['name']."</a>";
        $arrTmp[1] = $fax['extension'];
        $arrTmp[2] = $fax['secret'];
        $arrTmp[3] = $fax['email'];
        $arrTmp[4] = $fax['clid_name'] . "&nbsp;";
        $arrTmp[5] = $fax['clid_number'] . "&nbsp;";
        $arrTmp[6] = $arrFaxStatus['ttyIAX' . $fax['dev_id']].' on ttyIAX' . $fax['dev_id'];
        $arrData[] = $arrTmp;
    }

    $session = getSession();
    $session['faxlist']['faxListStatus'] = $arrData;
    putSession($session);

    $arrGrid = array("title"    => $arrLang["Virtual Fax List"],
                     "icon"     => "/modules/$module_name/images/kfaxview.png",
                     "width"    => "99%",
                     "start"    => ($end==0) ? 0 : 1,
                     "end"      => $end,
                     "total"    => $end,
                     "columns"  => array(0 => array("name"      => $arrLang["Virtual Fax Name"],
                                                    "property1" => ""),
                                         1 => array("name"      => $arrLang["Fax Extension"], 
                                                    "property1" => ""),
                                         2 => array("name"      => $arrLang["Secret"],
                                                    "property1" => ""),
                                         3 => array("name"      => $arrLang["Destination Email"],
                                                    "property1" => ""),
                                         4 => array("name"      => $arrLang["Caller ID Name"],
                                                    "property1" => ""),
                                         5 => array("name"      => $arrLang["Caller ID Number"],
                                                    "property1" => ""),
                                         6 => array("name"      => $arrLang["Status"],
                                                    "property1" => "")
                                        )
                    );
    $oGrid = new paloSantoGrid($smarty);
    return $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
}

function checkFaxStatus($function, $smarty, $module_name, $local_templates_dir, $arrConf, $arrLang)
{
    $executed_time = 1; //en segundos
    $max_time_wait = 30; //en segundos
    $event_flag    = false;
    $data          = null;

    $i = 1;
    while(($i*$executed_time) <= $max_time_wait){
        $return = $function($smarty, $module_name, $local_templates_dir, $arrConf, $arrLang);
        $data   = $return['data'];
        if($return['there_was_change']){
            $event_flag = true;
            break;
        }
        $i++;
        sleep($executed_time); //cada $executed_time estoy revisando si hay algo nuevo....
    }
   return $data;
}

function faxListStatus($smarty, $module_name, $local_templates_dir, $arrConf, $arrLang)
{
    $oFax    = new paloFax();
    $arrFax  = $oFax->getFaxList();
    $status  = TRUE;
    $end = count($arrFax);
    $arrFaxStatus = $oFax->getFaxStatus();
    $arrData    = array();
    foreach($arrFax as $fax) {
        $arrData[$fax['extension']] = $arrFaxStatus['ttyIAX'.$fax['dev_id']].' on ttyIAX'.$fax['dev_id'];
    }

    $statusArr    = thereChanges($arrData);
    if(empty($statusArr))
        $status = FALSE;
    $jsonObject = new PaloSantoJSON();
    if($status){ //este status es true solo cuando el tecnico acepto al customer (al hacer click)
        //sleep(2); //por si acaso se desincroniza en la tabla customer el campo attended y llenarse los datos de id_chat y id_chat_time
        $msgResponse["faxes"] = $statusArr;
        $jsonObject->set_status("CHANGED");
        $jsonObject->set_message($msgResponse);
    }else{
        $jsonObject->set_status("NOCHANGED");
    }

    return array("there_was_change" => $status,
                 "data" => $jsonObject->createJSON());
}

function thereChanges($data){
    $session = getSession();
    $arrData = $session['faxlist']['faxListStatus'];
    $arraResult = array();
    foreach($arrData as $key => $value){
        $fax = $value[1];
        $status = $value[6];
        if(isset($data[$fax]) & $data[$fax] != $status){
            $arraResult[$fax] = $data[$fax];
            $arrData[$key][6] = $data[$fax];
        }
    }
    $session['faxlist']['faxListStatus'] = $arrData;
    putSession($session);
    return $arraResult;
}

function getSession()
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $tmp = $_SESSION;
        session_commit();
    }
    return $tmp;
}

function putSession($data)//data es un arreglo
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $_SESSION = $data;
        session_commit();
    }
}

function getAction()
{
    if(getParameter("action")=="checkFaxStatus")
        return "checkFaxStatus";
    else
        return "default";
}
?>