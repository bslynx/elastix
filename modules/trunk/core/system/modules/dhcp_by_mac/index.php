<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.6-12                                               |
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
  $Id: index.php,v 1.1 2009-11-12 04:11:04 Oscar Navarrete onavarrete.palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoDHCP_Configuration.class.php";

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
    $accion = getAction();
    $content = "";

    switch($accion){
        case "new_dhcpconft":
            $content = viewFormDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $edit="false");
            break;
        case "view_dhcpconf":
            $content = viewFormDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $edit="false");
            break;
        case "edit_dhcpconf":
            $content = viewFormDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $edit="true");
            break;
        case "update_dhacp":
            $content = saveDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, true);
            break;
        case "save_dhcp":
            $content = saveDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "delete_dhcpConf":
            $content = deleteDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        default:
            $content = reportDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function reportDHCP_Configuration($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pDHCP_Configuration = new paloSantoDHCP_Configuration($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $action = getParameter("nav");
    $start  = getParameter("start");
    
    $arrPrueba = $pDHCP_Configuration->saveFileDhcpConfig($pDB);

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $totalDHCP_Configuration = $pDHCP_Configuration->ObtainNumDHCP_Configuration($filter_field, $filter_value);

    $limit  = 20;
    $total  = $totalDHCP_Configuration;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value";

    $arrData = null;
    $arrResult =$pDHCP_Configuration->ObtainDHCP_Configuration($limit, $offset, $filter_field, $filter_value);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
        $arrTmp[0]  = "<input type='checkbox' name='DhcpConfID_{$value['id']}' />";
        $arrTmp[1] = "<a href='?menu=$module_name&action=view_dhcpconf&id=".$value['id']."'>".$value['hostname']."</a>";;
        $arrTmp[2] = $value['ipaddress'];
        $arrTmp[3] = $value['macaddress'];
            $arrData[] = $arrTmp;
        }
    }

    $buttonDelete = "<input type='submit' name='delete_dhcpConf' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the DHCP configuration."]}');\" />";

    $arrGrid = array("title"    => $arrLang["DHCP By MAC"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
                0 => array("name"      => $buttonDelete,
                                    "property1" => ""),
                1 => array("name"      => $arrLang["Host Name"],
                                    "property1" => ""),
                2 => array("name"      => $arrLang["Ip Address"],
                                    "property1" => ""),
                3 => array("name"      => $arrLang["Mac Address"],
                                    "property1" => ""),
                            )
                    );

    //begin section filter
    $arrFormFilterDHCP_Configuration = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterDHCP_Configuration);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("NEW_DHCPCONF", $arrLang["New Dhcp Config"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    //end grid parameters

    return $contenidoModulo;
}


function createFieldFilter($arrLang){
    $arrFilter = array(
        "hostname" => $arrLang["Host Name"],
        "ipaddress" => $arrLang["Ip Address"],
        "macaddress" => $arrLang["Mac Address"],
                    );

    $arrFormElements = array(
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


function viewFormDHCP_Configuration($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, $edit="true")
{
    $pDHCP_Configuration = new paloSantoDHCP_Configuration($pDB);

    $arrFormDHCP_Configuration = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormDHCP_Configuration);
    
    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl
    
    if($action=="view_dhcpconf"){
        $oForm->setViewMode();
    }else if($edit=="true"){
        $oForm->setEditMode();
    }

    //end, Form data persistence to errors and other events.
    if($action=="view_dhcpconf" || $edit=="true"){ // the action is to view or view_edit.
        $dataDhcpConfig = $pDHCP_Configuration->getDhcpConfigById($id);
        if(is_array($dataDhcpConfig) & count($dataDhcpConfig)>0)
            $_DATA = $dataDhcpConfig;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pDHCP_Configuration->errMsg);
        }
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["DHCP By MAC"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}


function saveDHCP_Configuration($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, $update=FALSE){
    $pDHCP_Configuration = new paloSantoDHCP_Configuration($pDB);
    $arrFormDHCP_Configuration = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormDHCP_Configuration);   

    if(!$oForm->validateForm($_POST)) {
        // Falla la validación básica del formulario
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }
        $smarty->assign("mb_message", $strErrorMsg);

        $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
        $smarty->assign("SAVE", $arrLang["Save"]);
        $smarty->assign("CANCEL", $arrLang["Cancel"]);
        $smarty->assign("IMG", "images/list.png");

        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", $arrLang["DHCP Configuration"], $_POST);
        $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
        return $contenidoModulo;

    }else if($pDHCP_Configuration->valitadeDuplicateDhcpConfig2($_POST) && !$update) {
        $arrDuplicates = $pDHCP_Configuration->getDuplicateDhcpConfig($_POST);
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $strErrorMsg = "<b>{$arrLang['The following fields are duplicates or already exists']}:</b><br/>";
  
        if(is_array($arrDuplicates) && count($arrDuplicates) > 0){
            foreach($arrDuplicates as $k=>$v) {
                if($v) $strErrorMsg .= "$k, ";
            }
        }
        $smarty->assign("mb_message", $strErrorMsg);

        $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
        $smarty->assign("SAVE", $arrLang["Save"]);
        $smarty->assign("CANCEL", $arrLang["Cancel"]);
        $smarty->assign("IMG", "images/list.png");        

        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", $arrLang["DHCP Configuration"], $_POST);
        $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
        return $contenidoModulo;

    }else {
        $arrDhcpPost = array();
        $hostname = getParameter("hostname");
        if(ereg("([a-zA-Z]+)[[:space:]]([a-zA-Z]+)", $hostname, $arrReg))
            $arrDhcpPost['hostname'] = $arrReg[1]."_".$arrReg[2];
        else $arrDhcpPost['hostname'] = getParameter("hostname");

        $arrDhcpPost['ipaddress'] = getParameter("ipaddress");
        $arrDhcpPost['macaddress'] = getParameter("macaddress");
        
        if($update){
            $id = getParameter("id");
            $arrDhcpDB = $pDHCP_Configuration->getDhcpConfigById($id);
            $pDHCP_Configuration->updateFileDhcpConfig($arrDhcpPost, $arrDhcpDB);
        }else{
            $numDhcpConf = $pDHCP_Configuration->ObtainNumDHCP_Configuration("", "");
            $pDHCP_Configuration->addNewDhcpConfig($arrDhcpPost, $numDhcpConf);
        }
    
        header("Location: ?menu=$module_name&action=show");
    }
}

function deleteDHCP_Configuration($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang){
    $pDHCP_Configuration = new paloSantoDHCP_Configuration($pDB);
    foreach($_POST as $key => $values){
        if(substr($key,0,11) == "DhcpConfID_")
        {
            $dhcpConfId = substr($key, 11);
            $arrDhcpDB = $pDHCP_Configuration->getDhcpConfigById($dhcpConfId);
            $pDHCP_Configuration->deleteDhcpConfig($arrDhcpDB);
        }
    }

    header("Location: ?menu=$module_name&action=show");
}

function createFieldForm($arrLang)
{
    $arrFields = array(
            "hostname"   => array(      "LABEL"                  => $arrLang["Host Name"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "ipaddress"   => array(      "LABEL"                  => $arrLang["Ip Address"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "ip",
                                            //"VALIDATION_EXTRA_PARAM" => "([0-9]){1,3}.([0-9]+){1,3}.([0-9]+){1,3}.([0-9]+){1,3}$"
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "macaddress"   => array(      "LABEL"                  => $arrLang["Mac Address"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "ereg",//AGREGAR MAC VALIDACION AL FRAMEWORD PALOSANTOVALIDAR
                                            "VALIDATION_EXTRA_PARAM" => "([a-fA-F0-9]{2}):([a-fA-F0-9]{2}):([a-fA-F0-9]{2}):([a-fA-F0-9]{2}):([a-fA-F0-9]{2}):([a-fA-F0-9]{2})$"
                                            ),
            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    else if(getParameter("new_dhcpconft"))
        return "new_dhcpconft";
    else if(getParameter("edit_dhcpconf"))
        return "edit_dhcpconf";
    else if(getParameter("delete_dhcpConf"))
        return "delete_dhcpConf";
    else if(getParameter("action")=="view_dhcpconf")
        return "view_dhcpconf";
    else if(getParameter("update_dhacp"))
        return "update_dhacp";
    else if(getParameter("save_dhcp"))
        return "save_dhcp";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "report";
}

?>
