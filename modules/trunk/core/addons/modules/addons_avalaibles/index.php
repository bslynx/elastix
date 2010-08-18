<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-15                                             |
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

    switch($action) {
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
        case "getPackages":
            $content = getStatusUpdateCache($arrConf, $pDB, $arrLang);
            break;
        case "getStatusCache":
            $content = getStatusCache($pDB, $arrConf, $arrLang);
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
                $pAddonsModules->clearActionTMP(); //TODO: falta validar
                $pAddonsModules->setActionTMP($name_rpm, 'install', $data_exp);
            }
            else
                $arrSal['response'] = "error";
        }
        else
            $arrSal['response'] = "error";
    }
    else{
        if($pAddonsModules->existsActionTMP()){
            if($arrStatus['action'] == "confirm")
                $arrSal['response'] = "status_confirm";
            else
                $arrSal['response'] = "there_install"; //retornar que existe una instalacion
            $arrDataTMP = $pAddonsModules->getActionTMP();
            $arrSal['name_rpm'] = $arrDataTMP['name_rpm'];
        }
        else
            $arrSal['response'] = "error";
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
            $arrDataTMP = $pAddonsModules->getActionTMP();
            $arrSal['name_rpm'] = $arrDataTMP['name_rpm'];
            $arrSal['view_details'] = $arrLang['view_details'];
            //$_SESSION['elastix_addons']['data_install'] = $datatoInsert;
        }
        else
            $arrSal['response'] = "error";
    }
    else{
        if($arrStatus['action'] == "reporefresh")
            $arrSal['status_action'] = $arrLang['Status'].": ".$arrLang['reporefresh'];
        if($arrStatus['action'] == "depsolving")
            $arrSal['status_action'] = $arrLang['Status'].": ".$arrLang['depsolving'];
        if(!isset($arrSal['status_action']) || $arrSal['status_action']=="")
            $arrSal['status_action'] = $arrLang['Status'].": ".$arrLang['downloading'];
        $arrSal['response'] = $arrStatus['action'];
        $arrDataTMP = $pAddonsModules->getActionTMP();
        $arrSal['name_rpm'] = $arrDataTMP['name_rpm'];
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
        $smarty->assign("mb_title", $arrLang["ERROR"].": ");
        $smarty->assign("mb_message",$arrLang["The system can not connect to the Web Service resource. Please check your Internet connection."]);
        return ;
    }

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    try {
        $totalAvailables = $client->getNumAddonsAvailables("2.0.0", "name", $addons_search);
    } catch (SoapFault $e) {
        $smarty->assign("mb_title", $arrLang["ERROR"].": ");
        $smarty->assign("mb_message",$arrLang["The system can not connect to the Web Service resource. Please check your Internet connection."]);
        return ;
    }
    $limit  = 20;
    $total  = $totalAvailables;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->pagingShow(true); // show paging section.
    $oGrid->setTplFile("$local_templates_dir/_list.tpl");

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&amp;filter_value=$addons_search";

    $arrData = null;
    try {
        $arrResult =$client->getAddonsAvailables("2.0.0", $limit, $offset, "name", $addons_search);
    } catch (SoapFault $e) {
        $smarty->assign("mb_title", $arrLang["ERROR"].": ");
        $smarty->assign("mb_message",$arrLang["The system can not connect to the Web Service resource. Please check your Internet connection."]);
        return ;
    }

    if(is_array($arrResult) && $total>0){
        $smarty->assign('ETIQUETA_INSTALL', $arrLang['Install']);
        foreach($arrResult as $key => $value){
            if(!$pAvailables->exitAddons($value)){
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
                //$arrTmp[2] = "<input type='button' value='$arrLang[Install]' class='install' id='$value[name_rpm]' name='installButton' style='visibility: hidden;' />";
                $arrTmp[2] = "<div id='img_$value[name_rpm]' align='center' >".
                                "<img alt='' src='modules/addons_avalaibles/images/loading.gif' class='loadingAjax' style='display: block;' />".
                                "<div id='start_$value[name_rpm]' style='display: none;'>".
                                    "<div class='text_starting'>$arrLang[Starting]</div>".
                                    "<div>".
                                        "<img alt='' src='modules/addons_avalaibles/images/starting.gif' class='startingAjax' />".
                                    "</div>".
                                "</div>".
                                "<input type='button' value='$arrLang[Install]' class='install' id='$value[name_rpm]' name='installButton' style='display: none;' />".
                            "</div>";
                $arrTmp[3] = "<div id='status_$value[name_rpm]' class='text_downloading' align='center' >$arrLang[Loading]</div>";
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
                                   "property1" => ""),
            2 => array("name"      => "",
                                   "property1" => ""),
            3 => array("name"      => "",
                                   "property1" => ""))
                    );

    $smarty->assign("Search", $arrLang["Search"]);
    $smarty->assign("module_name", $module_name);

    $content = "<form  method='post' style='margin-bottom:0;' action=\"$url\">".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";

    return $content;
}

// funcion que ejecuta el demunio YUM para verificar los ultimos rpms a instalar
function getPackagesCache($arrConf, &$pDB, $arrLang){

    $client = new SoapClient($arrConf['url_webservice']);
    $packages = $client->getAllAddons("2.0.0");

    $pAddonsModules = new paloSantoAddonsModules($pDB);

    $arrStatus = $pAddonsModules->getStatus($arrConf);

    if(isset($arrStatus['action']) && ($arrStatus['action'] == "none" && $arrStatus['status'] == "idle")){
        //$salida = $pAddonsModules->addAddon($arrConf, $packages);
        $salida = $pAddonsModules->testAddAddon($arrConf, $packages);
        if(ereg("OK Processing",$salida)){
            $arrStatus = $pAddonsModules->getStatus($arrConf);
            if($arrStatus['status'] != "error"){
                $arrSal['response'] = "OK";
            }
            else
                $arrSal['response'] = "error";
                $arrSal['data_cache'] = array();
        }
        else {
            $arrSal['response'] = "error";
            $arrSal['data_cache'] = array();
        }
    }
    else{
        if($pAddonsModules->existsActionTMP()){
            $arrDataTMP = $pAddonsModules->getActionTMP();
            $arrSal['name_rpm'] = $arrDataTMP['name_rpm'];
            $tmp = explode("|",$arrDataTMP['data_exp']);

            if($arrStatus['action'] == "confirm"){
                $arrSal['response'] = "status_confirm";
                $arrSal['msg'] = $arrLang["There is a facility that awaits confirmation to install NAME, the user who initiated the installation was"]." ($arrDataTMP[user]).";
                $arrSal['msg'] = str_replace("NAME",$tmp[0]." version: $tmp[2]-$tmp[3]",$arrSal['msg']);
            }
            else{
               $arrSal['response'] = "there_install"; //retornar que existe una instalacion
               $arrSal['msg'] = $tmp[0]."version $tmp[2]-$tmp[3]";
            }
        }
        else if($arrStatus['status'] == "error")
            $arrSal['response'] = "error";
        else
            $arrSal['response'] = "OK";
    }

    $arrSal['status_action'] = $arrLang['Status'].": ".$arrStatus['status'];
    //$json = new Services_JSON();
    //return $json->encode($arrSal);
    return $arrSal;
}

// funcion que verifica si y se hizo la descarga en cache de los rpm anstes de instalar
function getStatusCache(&$pDB, $arrConf, $arrLang){
    $pAddonsModules = new paloSantoAddonsModules($pDB);
    sleep(5);
    $arrStatus = $pAddonsModules->getStatus($arrConf);
    $json = new Services_JSON();

    //if($arrStatus['action'] == "confirm"){
    if($arrStatus['action'] == "none" & $arrStatus['status'] == "idle"){ // if testadd ya realizado la descaraga en cache
        //$salida = $pAddonsModules->clearAddon($arrConf);
        //if(ereg("OK",$salida)){
            $arrSal['response'] = "OK";
            $client = new SoapClient($arrConf['url_webservice']);
            $packages = $client->getAllAddons("2.0.0");

            $arr_packages = explode(" ",$packages);
            $arr_RPMs = array();
            foreach ($arr_packages as $sNombreRPM) {
                $arr_RPMs[$sNombreRPM] = array('status' => 'OK', 'observation' => 'OK');
            }
            $pAddonsModules->fillDataCache($arr_packages, $arr_RPMs);
        /*}
        else
            $arrSal['response'] = "error";*/
    } elseif ($arrStatus['status'] != 'error') {
        if($arrStatus['action'] == "reporefresh")
            $arrSal['status_action'] = $arrLang['Status'].": ".$arrLang['reporefresh'];
        if($arrStatus['action'] == "depsolving")
            $arrSal['status_action'] = $arrLang['Status'].": ".$arrLang['depsolving'];
        if(!isset($arrSal['status_action']) || $arrSal['status_action']=="")
            $arrSal['status_action'] = $arrLang['Status'].": ".$arrLang['downloading'];
        $arrSal['response'] = $arrStatus['action'];
    } else {
        // Ha ocurrido un error 
        $pAddonsModules->clearAddon($arrConf);
        $arrSal['response'] = "error";
        
        // Separar los mensajes que referencian a un paquete objetivo
        $listaErr = array();
        foreach ($arrStatus['errmsg'] as $sErrMsg) {
            $regs = NULL;
            if (preg_match('/^TARGET (\S+) REQUIRES (.+)$/', $sErrMsg, $regs)) {
                $listaErr[$regs[1]][] = $regs[2];
            }
        }

        $client = new SoapClient($arrConf['url_webservice']);
        $packages = $client->getAllAddons("2.0.0");

        $arr_packages = explode(" ",$packages);
        $arr_RPMs = array();
        foreach ($arr_packages as $sNombreRPM) {
            // TODO: internacionalizar
            if (isset($listaErr[$sNombreRPM])) {
                $arr_RPMs[$sNombreRPM] = array(
                    'status' => 'FAIL', 
                    'observation' => 'Addon '.$sNombreRPM.' requires '.implode(', ', $listaErr[$sNombreRPM]));
            } else {
                $arr_RPMs[$sNombreRPM] = array('status' => 'OK', 'observation' => 'OK');
            }
        }
        $pAddonsModules->fillDataCache($arr_packages, $arr_RPMs);
        $arrSal['data_cache'] = $pAddonsModules->getDataCache();
    }
    return $json->encode($arrSal);
}

// funcion que verifica si se debe o no actualizar la lista de rpm a instalar
function getStatusUpdateCache($arrConf, &$pDB, $arrLang){
    $pAddonsModules = new paloSantoAddonsModules($pDB);
    $json = new Services_JSON();
    if(isset($_SESSION['elastix_addons']['last_update'])){
        $timeLast = $_SESSION['elastix_addons']['last_update'];
        $timeNew = time();
        if(($timeNew - $timeLast) > 7200){ //si es mayor a 5 minutos al fina1 son 2h -> 7200
            $_SESSION['elastix_addons']['last_update'] = $timeNew;
            $arrSal = getPackagesCache($arrConf, $pDB, $arrLang);
            return $json->encode($arrSal);
        }
        else{ // no se actualiza.... se toma esta en cache
            $arrSal['response'] = "noFillDataCache";
            $arrData = $pAddonsModules->getDataCache();
            if(is_array($arrData) && count($arrData) > 0){
                $arrSal['data_cache'] = $arrData;
                $arrSal['status_action'] = "";
                return $json->encode($arrSal);
            }
            else{ // La session existe pero no hay cache local de los addons
                $_SESSION['elastix_addons']['last_update'] = time();
                $arrSal = getPackagesCache($arrConf, $pDB, $arrLang);
                return $json->encode($arrSal);
            }
        }
    }else{
        $_SESSION['elastix_addons']['last_update'] = time();
        $arrSal = getPackagesCache($arrConf, $pDB, $arrLang);
        return $json->encode($arrSal);
    }
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
    else if(getParameter("action")=="getPackagesCache")
        return "getPackages";
    else if(getParameter("action")=="getStatusCache")
        return "getStatusCache";
    else
        return "report"; //cancel
}
?>
