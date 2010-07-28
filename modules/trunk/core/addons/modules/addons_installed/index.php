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
  $Id: index.php,v 1.1 2010-03-06 11:03:53 Eduardo Cueva ecueva@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoAddonsModules.class.php";
    include_once "modules/$module_name/libs/JSON.php";

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
    //$pDB = new paloDB($arrConf['dsn_conn_database']);
    $pDB = new paloDB($arrConf['dsn_conn_database']);



    //actions
    $action = getAction();
    $content = "";

    $pAddonsModules = new paloSantoAddonsModules($pDB);

    switch($action){
        case "progressbar":
            $content = getProgressBar($smarty, $module_name, $pDB, $arrConf, $arrLang);
            break;
        case "check_update":
            $content = checkUpdates($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "update":
            $content = updateAddons($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "remove":
            $content = removeAddons($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "confirm":
            $content = getconfirm($module_name, $pDB, $arrConf, $arrLang);
            break;
        default: // view_form
            $content = viewFormAddonsModules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function viewFormAddonsModules($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    //obtain rpm array to install

    $pAddonsModules = new paloSantoAddonsModules($pDB);

    //report
    $action = getParameter("nav");
    $start  = getParameter("start");
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);

    $totalAddons = $pAddonsModules->getNumAddonsInstalled();
    $limit  = 20;
    $total  = $totalAddons;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->pagingShow(true); // show paging section.
    $oGrid->setTplFile("$local_templates_dir/_list.tpl");

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name";

    $arrData = null;
    $arrResult = $pAddonsModules->getAddonsInstalled($limit, $offset);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
            $strUp = "";
            $arrTmp[0] = $value['name'];
            $arrTmp[1] = $value['version'];
            $arrTmp[2] = $value['release'];
            if($value['update_st'])
                $strUp = "<a href='javascript:void(0);' onclick=\"javascript:updateAddon('$value[name_rpm]');\">$arrLang[Update]</a>";
            $arrTmp[3] = "$strUp&nbsp;&nbsp;&nbsp;<a href='javascript:void(0);' onclick=\"javascript:removeAddon('$value[name_rpm]');\">$arrLang[Delete]</a>";
            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => $arrLang["addons_installed"],
                        "icon"     => "images/list.png",
                        "width"    => "100%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
            0 => array("name"      => $arrLang["name"],
                                   "property1" => ""),
            1 => array("name"      => $arrLang["version"],
                                   "property1" => ""),
            2 => array("name"      => $arrLang["release"],
                                   "property1" => ""),
            3 => array("name"      => $arrLang["Options"],
                                   "property1" => ""),
                                        )
                    );

    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("Installed", $arrLang["Installed"]);
    $smarty->assign("Updates", $arrLang["Updates"]);
    $smarty->assign("Downloaded", $arrLang["Downloaded"]);
    $smarty->assign("Availables", $arrLang["Availables"]);
    $smarty->assign("Admin", $arrLang["Admin"]);
    $smarty->assign("actual_progress", $arrLang["Actual Progress"]);
    $smarty->assign("total_progress", $arrLang["Total Progress"]);

    $smarty->assign("module_name", $module_name);
    $output = $pAddonsModules->getStatus($arrConf);

    $show_progress = 1;
    if($output['status']=="idle" && $output['action']=="none") $show_progress = 0;

    $divs_packages = createProgressBarsSecondary($output,$module_name,$arrLang);
    $smarty->assign("divs_packages", $divs_packages);
    $htmlForm = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    $htmlForm = "<form  method='post' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    $smarty->assign("ADDONS_INSTALLED",$htmlForm);
    $smarty->assign("SHOW_PROGRESS",$show_progress);

    $oForm = new paloForm($smarty,array());
    $content = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Addons Modules"], $_POST);

    return $content;
}

function createProgressBarsSecondary($output,$module_name,$arrLang){
    $percentTotal = 0;

    if(isset($output['porcent_total_ins']))
        $percentTotal = $output['porcent_total_ins'];

    $Percent_loaded = $arrLang["Percent_loaded"];

    if($percentTotal <=0 || $percentTotal==null)
        $percentTotal = 0;
    $html  = "<div id='progressBarTotal' style='height:2em;'>\n";
    $html .= "</div>\n";
    $html .= "<div>";
    $html .= "<div id='percent_loaded'><b>$Percent_loaded: </b><span id='percentTotal'>$percentTotal</span><span>%</span></div>\n";
    $html .= "<div id='img_view_details'><a id='link_detail' href='#'>".$arrLang["view_details"]."<img id='imgShow' src='modules/$module_name/images/flecha_down.gif' alt='image' /></a></div>\n";
    $html .= "<div id='details'>\n";
    if(!isset($output['package'])){
        $html .= "<div class='margin_bars' align='center'><span>".$arrLang['no_process']."</span></div></div>\n";
        return $html;
    }

    for($i=0; $i<count($output['package']); $i++){
        $name = $output['package'][$i]['name'];
        $action = $output['package'][$i]['action'];
        $lon_total = isset($output['package'][$i]['lon_total']) ? $output['package'][$i]['lon_total'] : 0;
        $lon_downl = isset($output['package'][$i]['lon_downl']) ? $output['package'][$i]['lon_downl'] : 0;
        $status_pa = $output['package'][$i]['status_pa'];

        $status_pa = $arrLang[$status_pa];
        $porcent_ins = isset($output['package'][$i]['porcent_ins']) ? $output['package'][$i]['porcent_ins'] : 0;
        $html .= "<div class='margin_bars'>\n";
        $html .= "<div id='progressBarActual$i'>\n";
        $html .= "</div>\n";
        $html .= "<div class='first_div'><b>".$arrLang['name'].": </b><span>$name. </span> </div>\n";
        $html .= "<div class='second_div'><b>".$arrLang['action'].": </b><span>$action. </span></div>\n";
        $html .= "<div class='third_div'><b>".$arrLang['Downloaded']."</b><span id='lon_downl$i'> $lon_downl bytes </span><b> ".$arrLang['of']." </b><span id='lon_total$i'> $lon_total bytes. </span></div>\n";
        $html .= "<div class='fourth_div'><b>".$arrLang['status'].": </b><span id='status_pa$i'> $status_pa. </div>\n";
        $html .= "<div class='fifth_div'></span><b> $Percent_loaded: </b><span id='percentTotal$i'>$porcent_ins</span><span>%</span></div></div>\n";
    }
    $html .= "</div>\n";
    return $html;
}

function getProgressBar($smarty, $module_name, $pDB, $arrConf, $arrLang){
    $pAddonsModules = new paloSantoAddonsModules($pDB);
    $arrStatus = $pAddonsModules->getStatus($arrConf);

    if (isset($_SESSION['elastix_addons'])) { // hay instalacion en progreso
        $valueActual = "none";
        $valueTotal = "0";
        if(isset($arrStatus['package']))
            $valueActual = $arrStatus['package'];
        if(isset($arrStatus['porcent_total_ins']))
            $valueTotal = $arrStatus['porcent_total_ins'];

        $arr['valueActual'] = $valueActual;
        $arr['valueTotal']  = $valueTotal;
        $arr['status']  = "progress";
        $arr['action']  = $arrStatus['action'];
        $arr['process_installed'] = $arrLang['process_installed'];
        if($arrStatus['status'] == "idle" && $arrStatus['action'] == "none"){
            if(isset($_SESSION['elastix_addons']['name_rpm'])){
                $data_exp = $_SESSION['elastix_addons']['data_install'];
                if(isset($data_exp) && $data_exp != ""){
                    $arrDataInsert = explode("|",$data_exp);
                    $pAddonsModules->insertAddons($arrDataInsert[0],$arrDataInsert[1],$arrDataInsert[2],$arrDataInsert[3]);
                }
                unset($_SESSION['elastix_addons']['data_install']);
                unset($_SESSION['elastix_addons']['name_rpm']);
                unset($_SESSION['elastix_addons']['action_rpm']);
                unset($_SESSION['elastix_addons']);
                $arr['status'] = "finished";
                $arr['response'] = "OK";
                
                // Refrescar el estado de actualización
                $addons_installed = $pAddonsModules->getCheckAddonsInstalled();

                try {
                    $client = new SoapClient($arrConf['url_webservice']);
                    $arrAddons = $client->getCheckAddonsUpdate($addons_installed);
                    $arrAddons = explode(",",$arrAddons);
                    $pAddonsModules->updateInDB($arrAddons);
                } catch (SoapFault $e) {
                    $smarty->assign("mb_title", $arrLang["ERROR"].": ");
                    $smarty->assign("mb_message",$arrLang["The system can not connect to the Web Service resource. Please check your Internet connection."]);
                }
            } else {
                $arr['status'] = "not_install";
                $arr['response'] = "not_install";
            }
        }
        sleep(4);
    }
    else {
        $arr['status'] = "not_install";
        $arr['response'] = "not_install";
    }

    $json = new Services_JSON();
    return $json->encode($arr);
}

function checkUpdates($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pAddonsModules = new paloSantoAddonsModules($pDB);
    $addons_installed = $pAddonsModules->getCheckAddonsInstalled();

    ini_set("soap.wsdl_cache_enabled", "0");
    $client = new SoapClient($arrConf['url_webservice']);
    $arrAddons = $client->getCheckAddonsUpdate($addons_installed);

    //se deben mostrar los links de updates para mostrar
    $arrAddons = explode(",",$arrAddons);
    $pAddonsModules->updateInDB($arrAddons);

    return viewFormAddonsModules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
}


function updateAddons($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $name_rpm = getParameter("name_rpm");
    $data_exp = getParameter("data_exp");
    $pAddonsModules = new paloSantoAddonsModules($pDB);
    $arrSal['response'] = false;
    $_SESSION['elastix_addons']['data_install'] = $data_exp;

    $arrStatus = $pAddonsModules->getStatus($arrConf);

    if($arrStatus['action'] == "none" && $arrStatus['status'] == "idle"){
        $salida = $pAddonsModules->updateAddon($arrConf, $name_rpm);
        if(ereg("OK Processing",$salida)){
            $arrStatus = $pAddonsModules->getStatus($arrConf);
            if($arrStatus['status'] != "error"){
                $arrSal['response'] = "OK";
                $_SESSION['elastix_addons']['name_rpm'] = $name_rpm;
                $_SESSION['elastix_addons']['action_rpm'] = 'update';
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

function removeAddons($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $name_rpm = getParameter("name_rpm");
    $pAddonsModules = new paloSantoAddonsModules($pDB);
    $arrSal['response'] = false;
    $_SESSION['elastix_addons']['data_install'] = '';

    $arrStatus = $pAddonsModules->getStatus($arrConf);

    if($arrStatus['action'] == "none" && $arrStatus['status'] == "idle"){
        $salida = $pAddonsModules->removeAddon($arrConf, $name_rpm);
        if(ereg("OK Processing",$salida)){
            $arrStatus = $pAddonsModules->getStatus($arrConf);
            if($arrStatus['status'] != "error"){
                $arrSal['response'] = "OK";
                $_SESSION['elastix_addons']['name_rpm'] = $name_rpm;
                $_SESSION['elastix_addons']['action_rpm'] = 'remove';
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

function getconfirm($module_name, $pDB, $arrConf, $arrLang){
    $pAddonsModules = new paloSantoAddonsModules($pDB);

    $arrStatus = $pAddonsModules->getStatus($arrConf);
    if ($arrStatus['status'] == 'idle' && $arrStatus['action'] == 'confirm') {
        $sRespuesta = $pAddonsModules->confirmAddon($arrConf);
        if (preg_match('/^OK /', $sRespuesta)) {
            $arrStatus['response'] = 'OK';
        }
    } else {
        // Todavía está resolviendo dependencias...
        sleep(4);
    }
//    $arr['status'] = $arrStatus;
    $json = new Services_JSON();
    return $json->encode($arrStatus);
}

function getAction()
{
    if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("action")=="progressbar")
        return "progressbar";
    else if(getParameter("action")=="view_edit")
        return "view_form";
    else if(getParameter("action")=="get_status")
        return "progressbar";
    else if(getParameter("action")=="confirm")
        return "confirm";
    else if(getParameter("action")=="update")
        return "update";
    else if(getParameter("action")=="remove")
        return "remove";
    else if(getParameter("check_update"))
        return "check_update";
    else
        return "report"; //cancel
}
?>
