<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-15                                               |
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
  $Id: index.php,v 1.1 2010-03-08 12:03:02 Bruno Macias bomv.27@gmail.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/addons_installed/libs/paloSantoAddonsModules.class.php";
    include_once "modules/addons_installed/libs/JSON.php";
    $smarty->assign('MODULE_NAME', $module_name);

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
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "install":
            $content = installAddons($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "get_status":
            $content = getStatus($pDB, $arrConf, $arrLang);
            break;
        case "get_lang":
            $content = getLang($arrLang);
            break;
        case "confirm":
            $content = getConfirm($pDB, $arrConf, $arrLang);
            break;
        default:
            $content = reportAvailables($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function installAddons($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $name_rpm = getParameter("name_rpm");
    $data_exp = getParameter("data_exp");
    $pAddonsModules = new paloSantoAddonsModules($pDB);
    $arrSal['response'] = false;
    //$_SESSION['elastix_addons']['data_install'] = $data_exp;

    $arrStatus = $pAddonsModules->getStatus($arrConf);

    if($arrStatus['action'] == "none" && $arrStatus['status'] == "idle"){
        $salida = $pAddonsModules->addAddon($arrConf, $name_rpm);
        if(ereg("OK Processing",$salida)){
            $arrStatus = $pAddonsModules->getStatus($arrConf);
            if($arrStatus['status'] != "error"){
                $arrSal['response'] = "OK";
                $arrSal['name_rpm'] = $name_rpm;
                $_SESSION['elastix_addons']['name_rpm'] = $name_rpm;
            }
            else
                $arrSal['response'] = "error";
        }
        else
            $arrSal['response'] = "error";
    }
    else{
        $arrSal['response'] = "there_install"; //retornar que existe una instalacion
        $arrSal['name_rpm'] = $_SESSION['elastix_addons']['name_rpm'];
    }
    $arrSal['installing'] = $arrLang['installing'];
    $json = new Services_JSON();
    return $json->encode($arrSal);
}

function getStatus($pDB, $arrConf, $arrLang){
    $pAddonsModules = new paloSantoAddonsModules($pDB);
    $datatoInsert = getParameter("data_exp");
    sleep(10);
    $arrStatus = $pAddonsModules->getStatus($arrConf);

    $json = new Services_JSON();
    $arrSal['response'] = false;
    if($arrStatus['action'] == "confirm"){
        $salida = $pAddonsModules->confirmAddon($arrConf);
        if(ereg("OK Starting transaction...",$salida)){
            $arrSal['response'] = "OK";
            $arrSal['name_rpm'] = $_SESSION['elastix_addons']['name_rpm'];
            $arrSal['view_details'] = $arrLang['view_details'];
            $_SESSION['elastix_addons']['data_install'] = $datatoInsert;
        }
        else
            $arrSal['response'] = "error";
    }
    else{
        if($arrStatus['action'] == "reporefresh")
            $arrSal['status_action'] = $arrLang['reporefresh'];
        if($arrStatus['action'] == "depsolving")
            $arrSal['status_action'] = $arrLang['depsolving'];
        if(!isset($arrSal['status_action']) || $arrSal['status_action']=="")
            $arrSal['status_action'] = $arrLang['downloading'];
        $arrSal['response'] = $arrStatus['action'];
        $arrSal['name_rpm'] = $_SESSION['elastix_addons']['name_rpm'];

    }
    return $json->encode($arrSal);
}

function getLang($arrLang){
    $json = new Services_JSON();
    return $json->encode($arrLang);
}

function getConfirm($pDB, $arrConf, $arrLang){
    $pAddonsModules = new paloSantoAddonsModules($pDB);
    $arrStatus = $pAddonsModules->confirmAddon($arrConf);
    $json = new Services_JSON();
    $arr['status'] = $arrStatus;
    $arr['view_details'] = $arrLang['view_details'];
    return $json->encode($arr);
}

function reportAvailables($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pAvailables   = new paloSantoAddonsModules($pDB);
    $addons_search = getParameter("addons_search");
    $action = getParameter("nav");
    $start  = getParameter("start");

    $module_name2 = "addons_installed";

    ini_set("soap.wsdl_cache_enabled", "0");
    try {
        $client = new SoapClient($arrConf['url_webservice']);
    } catch (SoapFault $e) {
        return "<b><span style=\"color: #FF0000;\">".$e->getMessage()."</span></b>";
    }

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    try {
        $totalAvailables = $client->getNumAddonsAvailables("2.0.0", "name", $addons_search);
    } catch (SoapFault $e) {
        return "<b><span style=\"color: #FF0000;\">".$e->getMessage()."</span></b>";
    }
    $limit  = 5;
    $total  = $totalAvailables;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->pagingShow(true); // show paging section.
    $oGrid->setTplFile("$local_templates_dir/_list.tpl");

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&filter_value=$addons_search";

    $arrData = null;
    try {
        $arrResult =$client->getAddonsAvailables("2.0.0", $limit, $offset, "name", $addons_search);
    } catch (SoapFault $e) {
        return "<b><span style=\"color: #FF0000;\">".$e->getMessage()."</span></b>";
    }

    if(is_array($arrResult) && $total>0){
        $smarty->assign('ETIQUETA_INSTALL', $arrLang['Install']);
        foreach($arrResult as $key => $value){
            if(!$pAvailables->exitAddons($value['name_rpm'])){
                $smarty->assign(array(
                    'ETIQUETA_DOWNLOADING'  =>  $arrLang['downloading'],
                    'URL_IMAGEN_PAQUETE'    =>  "$arrConf[url_images]/$value[name_rpm].jpeg",
                    'DESCRIPCION_PAQUETE'   =>  $value['description'],
                    'PAQUETE_RPM'           =>  $value['name_rpm'],
                    'PAQUETE_NOMBRE'        =>  $value['name'],
                    'PAQUETE_VERSION'       =>  $value['version'],
                    'PAQUETE_RELEASE'       =>  $value['release'],
                    'PAQUETE_CREADOR'       =>  $value['developed_by'],
                ));
                $arrTmp[0] = $smarty->fetch("$local_templates_dir/imagen_paquete.tpl");
                $arrTmp[1] = $smarty->fetch("$local_templates_dir/info_paquete.tpl");
                $arrData[] = $arrTmp;
            }
        }
    }


    $arrGrid = array("title"    => $arrLang["Availables"],
                        "icon"     => "images/list.png",
                        "width"    => "100%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
			0 => array("name"      => "",
                                   "property1" => ""),
            1 => array("name"      => "",
                                   "property1" => ""))
                    );

    $smarty->assign("Search", $arrLang["Search"]);
    $smarty->assign("module_name", $module_name);

    $content = "<form  method='POST' style='margin-bottom:0;' action=\"$url\">".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";

    return $content;
}


function getAction()
{
    if(getParameter("action")=="confirm")
        return "confirm";
    else if(getParameter("action")=="get_status")      //Get parameter by GET (command pattern, links)
        return "get_status";
    else if(getParameter("action")=="install")
        return "install";
    else if(getParameter("action")=="get_lang")
        return "get_lang";
    else
        return "report"; //cancel
}
?>
