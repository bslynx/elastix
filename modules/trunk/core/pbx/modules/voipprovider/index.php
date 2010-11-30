<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.3-2                                               |
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
  $Id: index.php,v 1.2 2010-11-29 15:09:50 Eduardo Cueva ecueva@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";
include_once "libs/paloSantoConfig.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoVoIPProvider.class.php";
    include_once "libs/paloSantoACL.class.php";

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

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    $dsn_agi_manager['password'] = $arrConfig['AMPMGRPASS']['valor'];
    $dsn_agi_manager['host'] = $arrConfig['AMPDBHOST']['valor'];
    $dsn_agi_manager['user'] = 'admin';

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = saveNewVoIPProvider($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $dsn_agi_manager);
            break;
        case "view_new":
            $content = newFormVoIPProviderAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "view_edit":
            $content = editFormVoIPProviderAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $dsn_agi_manager);
            break;
        case "save_edit":
            $content = editFormVoIPProviderAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $dsn_agi_manager);
            break;
        case "delete":
            $content = deleteVoIPProviderAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $dsn_agi_manager);
            break;
        case "getInfoProvider":
            $content = getInfoVoIPProviderAccount($module_name, $pDB, $arrConf, $arrLang);
            break;
		case "activate":
			$content = activateVoIPProviderAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $dsn_agi_manager);
            break;
        default: // view_form
            $content = viewFormVoIPProvider($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function viewFormVoIPProvider($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pVoIPProvider = new paloSantoVoIPProvider($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $action = getParameter("nav");
    $start  = getParameter("start");
    $as_csv = getParameter("exportcsv");
    $filter_valueTMP = $filter_value;
    $allowSelection = array("provider", "account_name");
    if(isset($filter_value) & $filter_value !=""){
        if(!in_array($filter_field, $allowSelection))
            $filter_field = "provider";
        $filter_value    = $pDB->DBCAMPO('%'.$filter_value.'%');
    }

    $url = array(
        'menu'          =>  $module_name,
        'filter_field'  =>  $filter_field,
        'filter_value'  =>  $filter_valueTMP,
    );

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $totalVoipProviders = $pVoIPProvider->getNumVoIPProvider($filter_field, $filter_value);

    $limit  = 20;
    $total  = $totalVoipProviders;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->enableExport();   // enable csv export.
    $oGrid->pagingShow(true); // show paging section.
    $oGrid->setNameFile_Export("VoIP_Provider");

    $arrData = null;
    if($oGrid->isExportAction()) {
        $limit = $total;
        $offset = 0;
        $end = 0;

        $arrResult =$pVoIPProvider->getVoIPProviderData($limit, $offset, $filter_field, $filter_value);

        if(is_array($arrResult) && $total>0){
            foreach($arrResult as $key => $value){
                $arrTmp[0] = $value['account_name'];
                if(isset($value['id_provider']) && $value['id_provider'] != ""){
                    $name = $pVoIPProvider->getVoIPProviderById($value['id_provider']);
                    $arrTmp[1] = $name['name'];
                }else
                    $arrTmp[1] = $arrLang["Custom"];
                $arrTmp[2] = $value['type_trunk'];
                if($value['status'] == "activate")
                    $arrTmp[3] = $arrLang['Enable'];
                else
                    $arrTmp[3] = $arrLang['Disable'];
                $arrData[] = $arrTmp;
            }
        }

        $arrColumns  = array(
            0 => array("name"      => $arrLang["Account Name"],
                                   "property1" => ""),
            1 => array("name"      => $arrLang["VoIP Provider"],
                                   "property1" => ""),
            2 => array("name"      => $arrLang["Type Trunk"],
                                   "property1" => ""),
            3 => array("name"      => $arrLang["Status"],
                                   "property1" => ""));
    }
    else{
        $oGrid->calculatePagination($action,$start);
        $offset = $oGrid->getOffsetValue();
        $end    = $oGrid->getEnd();

        $arrResult =$pVoIPProvider->getVoIPProviderData($limit, $offset, $filter_field, $filter_value);

        if(is_array($arrResult) && $total>0){
            foreach($arrResult as $key => $value){
                $arrTmp[0] = "<input type='checkbox' name='account_{$value['id']}'  />";
                $arrTmp[1] = $value['account_name'];
                if(isset($value['id_provider']) && $value['id_provider'] != ""){
                    $name = $pVoIPProvider->getVoIPProviderById($value['id_provider']);
                    $arrTmp[2] = $name['name'];
                }else
                    $arrTmp[2] = $arrLang["Custom"];
                $arrTmp[3] = $value['type_trunk'];
                if($value['status'] == "activate")
                    $arrTmp[4] = "<a href=?menu=$module_name&action=activate&id={$value['id']}>{$arrLang['Disable']}</a>";
                else
                    $arrTmp[4] = "<a href=?menu=$module_name&action=activate&id={$value['id']}>{$arrLang['Enable']}</a>";
                $arrTmp[5] = "<a href=?menu=$module_name&action=view_edit&id={$value['id']}>{$arrLang['Edit']}</a>";
                $arrData[] = $arrTmp;
            }
        }

        $arrColumns  = array(
            0 => array("name"      => "<input type='submit' name='delete' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the accounts selected."]}');\" />",
                                   "property1" => ""),
            1 => array("name"      => $arrLang["Account Name"],
                                   "property1" => ""),
            2 => array("name"      => $arrLang["VoIP Provider"],
                                   "property1" => ""),
            3 => array("name"      => $arrLang["Type Trunk"],
                                   "property1" => ""),
            4 => array("name"      => $arrLang["Status"],
                                   "property1" => ""),
            5 => array("name"      => $arrLang["Edit"],
                                   "property1" => ""));
    }

    $arrGrid = array("title"    => $arrLang["VoIP Provider"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => $arrColumns,
                    );


    //begin section filter
    $arrFormFilterprueba = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterprueba);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("NEW_ACCOUNT", $arrLang["New Account"]);
    $smarty->assign("Module_name", $module_name);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $content = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    //end grid parameters

    return $content;
}

function newFormVoIPProviderAccount($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pVoIPProvider = new paloSantoVoIPProvider($pDB);
    $arrFormVoIPProvider = createFieldForm($arrLang, $pVoIPProvider);
    $oForm = new paloForm($smarty,$arrFormVoIPProvider);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl
    $smarty->assign("Module_name", $module_name);

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataVoIPProvider = $pVoIPProvider->getVoIPProviderAccountById($id);
        if(is_array($dataVoIPProvider) & count($dataVoIPProvider)>0){
            $name = $pVoIPProvider->getVoIPProviderById($dataVoIPProvider['id_provider']);
            $_DATA['type_provider_voip'] = $name;
            $_DATA = $dataVoIPProvider;
        }else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pVoIPProvider->errMsg);
        }
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("General_Setting", $arrLang["General_Setting"]);
    $smarty->assign("PEER_Details", $arrLang["PEER_Details"]);
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["VoIP Provider"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function editFormVoIPProviderAccount($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, $dsn_agi_manager)
{
    $pVoIPProvider = new paloSantoVoIPProvider($pDB);
    $arrFormVoIPProvider = createFieldForm($arrLang, $pVoIPProvider);
    $oForm = new paloForm($smarty,$arrFormVoIPProvider);

    //begin, Form data persistence to errors and other events.
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl
    $smarty->assign("Module_name", $module_name);
    $smarty->assign("General_Setting", $arrLang["General_Setting"]);
    $smarty->assign("PEER_Details", $arrLang["PEER_Details"]);
    $dataVoIPProvider = $pVoIPProvider->getVoIPProviderAccountById($id);
    $name = $pVoIPProvider->getVoIPProviderById($dataVoIPProvider['id_provider']);
    $_DATA  = $_POST;
    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        if(is_array($dataVoIPProvider) & count($dataVoIPProvider)>0){
            $_DATA = $dataVoIPProvider;
            $_DATA['type_provider_voip'] = $name;
        }else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pVoIPProvider->errMsg);
        }
    }

    if(getParameter("save_edit")){
        if($pVoIPProvider->validateFormEmpty($_POST)) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $strErrorMsg  = "<b>{$arrLang['Some or someone necesary fields are empty please check and complete']}</b><br/>";
            $smarty->assign("mb_message", $strErrorMsg);
            $_DATA['type_provider_voip'] = $name;
            $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
            $smarty->assign("SAVE", $arrLang["Save"]);
            $smarty->assign("CANCEL", $arrLang["Cancel"]);
            $smarty->assign("IMG", "images/list.png"); 
            $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", $arrLang["VoIP Provider"], $_POST);
            $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
            //return $contenidoModulo;
        }else{
            $type_provider = isset($_DATA['type_provider_voip'])?$_DATA['type_provider_voip']:"";
            $account_name  = isset($_DATA["account_name"])?$_DATA["account_name"]:"";
            $username      = isset($_DATA["username"])?$_DATA["username"]:"";
            $secret        = isset($_DATA["secret"])?$_DATA["username"]:"";
            $type          = isset($_DATA["type"])?$_DATA["type"]:"";
            $qualify       = isset($_DATA["qualify"])?$_DATA["qualify"]:"";
            $insecure      = isset($_DATA["insecure"])?$_DATA["insecure"]:"";
            $host          = isset($_DATA["host"])?$_DATA["host"]:"";
            $fromuser      = isset($_DATA["fromuser"])?$_DATA["fromuser"]:"";
            $fromdomain    = isset($_DATA["fromdomain"])?$_DATA["fromdomain"]:"";
            $dtmfmode      = isset($_DATA["dtmfmode"])?$_DATA["dtmfmode"]:"";
            $disallow      = isset($_DATA["disallow"])?$_DATA["disallow"]:"";
            $context       = isset($_DATA["context"])?$_DATA["context"]:"";
            $allow         = isset($_DATA["allow"])?$_DATA["allow"]:"";
            $trustrpid     = isset($_DATA["trustrpid"])?$_DATA["trustrpid"]:"";
            $sendrpid      = isset($_DATA["sendrpid"])?$_DATA["sendrpid"]:"";
            $canreinvite   = isset($_DATA["canreinvite"])?$_DATA["canreinvite"]:"";
            $technology    = isset($_DATA["technology"])?$_DATA["technology"]:"";
			$statusAct     = isset($_DATA["status"])?$_DATA["status"]:"";
            if($technology=="")
                $technology = $dataVoIPProvider['technology'];
            $data = array($account_name,$username,$secret,$type,$qualify,$insecure,$host,$fromuser,$fromdomain,$dtmfmode,$disallow,$context,$allow,$trustrpid,$sendrpid,$canreinvite,$technology,$statusAct,$id);
    
            $status = $pVoIPProvider->updateAccount($data);

            if(!$status){
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $strErrorMsg  = "<b>{$arrLang['Internal Error']}</b><br/>";
                $smarty->assign("mb_message", $strErrorMsg);
                $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
                $smarty->assign("SAVE", $arrLang["Save"]);
                $smarty->assign("EDIT", $arrLang["Edit"]);
                $smarty->assign("CANCEL", $arrLang["Cancel"]);
                $smarty->assign("IMG", "images/list.png"); 
                $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", $arrLang["VoIP Provider"], $_POST);
                $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
                return $contenidoModulo;
            }
            //escritura en archivos de asterisk
            $pVoIPProvider->setAsteriskFiles($dsn_agi_manager);
            header("Location: ?menu=$module_name&action=view_form");
        }
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Save"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["VoIP Provider"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function getInfoVoIPProviderAccount($module_name, &$pDB, $arrConf, $arrLang)
{
    $jsonObject      = new PaloSantoJSON();
    $pVoIPProvider   = new paloSantoVoIPProvider($pDB);
    $nameProvider    = getParameter("type_provider");
    $response        = $pVoIPProvider->getInfoVoIPProvidersByName($nameProvider);
    $pACL            = new paloACL($arrConf['ACLdb']);
    $user            = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $esAdministrador = $pACL->isUserAdministratorGroup($user);

    if($esAdministrador){
        $msgResponse['type']        = $response['type'];
        $msgResponse['qualify']     = $response['qualify'];
        $msgResponse['insecure']    = $response['insecure'];
        $msgResponse['host']        = $response['host'];
        $msgResponse['fromuser']    = $response['fromuser'];
        $msgResponse['fromdomain']  = $response['fromdomain'];
        $msgResponse['dtmfmode']    = $response['dtmfmode'];
        $msgResponse['disallow']    = $response['disallow'];
        $msgResponse['context']     = $response['context'];
        $msgResponse['allow']       = $response['allow'];
        $msgResponse['trustrpid']   = $response['trustrpid'];
        $msgResponse['sendrpid']    = $response['sendrpid'];
        $msgResponse['canreinvite'] = $response['canreinvite'];
        $msgResponse['type_trunk']  = $response['type_trunk'];
    }else{
        $msgResponse = array();
    }

    $jsonObject->set_message($msgResponse);
    return $jsonObject->createJSON();
}

function saveNewVoIPProvider($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, $dsn_agi_manager)
{
    $pVoIPProvider = new paloSantoVoIPProvider($pDB);
    $arrFormVoIPProvider = createFieldForm($arrLang, $pVoIPProvider);
    $oForm = new paloForm($smarty,$arrFormVoIPProvider);

    if($pVoIPProvider->validateFormEmpty($_POST)) {
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $strErrorMsg  = "<b>{$arrLang['Some or someone necesary fields are empty please check and complete']}</b><br/>";
        $smarty->assign("mb_message", $strErrorMsg);

        $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
        $smarty->assign("SAVE", $arrLang["Save"]);
        $smarty->assign("CANCEL", $arrLang["Cancel"]);
        $smarty->assign("IMG", "images/list.png"); 

        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", $arrLang["VoIP Provider"], $_POST);
        $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
        return $contenidoModulo;

    }else {
        $data_trunk = array();
        $data_attribute = array();

        $type_provider = isset($_POST['type_provider_voip'])?$_POST['type_provider_voip']:"";
        $account_name  = isset($_POST["account_name"])?$_POST["account_name"]:"";
        $username      = isset($_POST["username"])?$_POST["username"]:"";
        $secret        = isset($_POST["secret"])?$_POST["username"]:"";
        $type          = isset($_POST["type"])?$_POST["type"]:"";
        $qualify       = isset($_POST["qualify"])?$_POST["qualify"]:"";
        $insecure      = isset($_POST["insecure"])?$_POST["insecure"]:"";
        $host          = isset($_POST["host"])?$_POST["host"]:"";
        $fromuser      = isset($_POST["fromuser"])?$_POST["fromuser"]:"";
        $fromdomain    = isset($_POST["fromdomain"])?$_POST["fromdomain"]:"";
        $dtmfmode      = isset($_POST["dtmfmode"])?$_POST["dtmfmode"]:"";
        $disallow      = isset($_POST["disallow"])?$_POST["disallow"]:"";
        $context       = isset($_POST["context"])?$_POST["context"]:"";
        $allow         = isset($_POST["allow"])?$_POST["allow"]:"";
        $trustrpid     = isset($_POST["trustrpid"])?$_POST["trustrpid"]:"";
        $sendrpid      = isset($_POST["sendrpid"])?$_POST["sendrpid"]:"";
        $canreinvite   = isset($_POST["canreinvite"])?$_POST["canreinvite"]:"";
        $technology    = isset($_POST["technology"])?$_POST["technology"]:"";

        if($type_provider!="custom"){
            $id_provider = $pVoIPProvider->getIdVoIPProvidersByName($type_provider);
            $technology  = $id_provider['type_trunk'];
        }else{
            $id_provider = null;
        }

        $data = array($account_name,$username,$secret,$type,$qualify,$insecure,$host,$fromuser,$fromdomain,$dtmfmode,$disallow,$context,$allow,$trustrpid,$sendrpid,$canreinvite,$technology, $id_provider['id']);

        $status = $pVoIPProvider->insertAccount($data);

        if(!$status){
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $strErrorMsg  = "<b>{$arrLang['Internal Error']}</b><br/>";
            $smarty->assign("mb_message", $strErrorMsg);
            $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
            $smarty->assign("SAVE", $arrLang["Save"]);
            $smarty->assign("CANCEL", $arrLang["Cancel"]);
            $smarty->assign("IMG", "images/list.png"); 
            $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", $arrLang["VoIP Provider"], $_POST);
            $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
            return $contenidoModulo;
        }

        //escritura en archivos de asterisk
        $pVoIPProvider->setAsteriskFiles($dsn_agi_manager);

        header("Location: ?menu=$module_name&action=view_form");
    }

}

function deleteVoIPProviderAccount($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, $dsn_agi_manager)
{
    $pVoIPProvider = new paloSantoVoIPProvider($pDB);
    $pACL            = new paloACL($arrConf['ACLdb']);
    $user            = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $esAdministrador = $pACL->isUserAdministratorGroup($user);
    if($esAdministrador){
        $result = "";
        foreach($_POST as $key => $values){
            if(substr($key,0,8) == "account_")
            {
                $tmpID = substr($key, 8);
                $pVoIPProvider->deleteAccount($tmpID);
            }
        }
        //escritura en archivos de asterisk
        $pVoIPProvider->setAsteriskFiles($dsn_agi_manager);
    }else{
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $smarty->assign("mb_message", $arrLang["User is not allowed to do this operation"]);
    }
    return viewFormVoIPProvider($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);

}

function activateVoIPProviderAccount($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, $dsn_agi_manager)
{
	$pVoIPProvider   = new paloSantoVoIPProvider($pDB);
    $pACL            = new paloACL($arrConf['ACLdb']);
    $user            = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $esAdministrador = $pACL->isUserAdministratorGroup($user);
    if($esAdministrador){
		$id      = getParameter("id");
		$arrData = $pVoIPProvider->getVoIPProviderAccountById($id);
		$status  = "";
		if($arrData['status']=="desactivate")
			$status = "activate";
		else
			$status = "desactivate";
			
		$sal = $pVoIPProvider->changeStatus($id, $status);
		if($sal){
			$pVoIPProvider->setAsteriskFiles($dsn_agi_manager);
		}else{
			$smarty->assign("mb_title", $arrLang["ERROR"]);
			$smarty->assign("mb_message", $arrLang["Internal Error"]);
		}
	}else{
		$smarty->assign("mb_title", $arrLang["Validation Error"]);
        $smarty->assign("mb_message", $arrLang["User is not allowed to do this operation"]);
	}
	return viewFormVoIPProvider($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
}

function createFieldForm($arrLang, $pVoIPProvider)
{
    $arrProviders = array("custom" => $arrLang["Custom"]);
    $result = $pVoIPProvider->getVoIPProviders();//Obtiene la lista para ser seteado en el listbox
    foreach($result as $values){
        $arrProviders[$values['name']] = $values['name'];
    }

    $arrSelectForm = array("no" => $arrLang["no"], "yes" => $arrLang["yes"]);

	$arrStatus     = array("activate" => $arrLang["Enable"], "desactivate" => $arrLang["Disable"]);
	
    $arrSelectTech = array("SIP" => "SIP", "IAX2" => "IAX2");
	
	$arrSelectType = array("friend" => "friend", "peer" => "peer");

    $arrSelectCareInvite = array("no" => $arrLang["no"], "yes" => $arrLang["yes"], "nonat" => "nonat", "update" => "update");

    $arrSelectInsecure   = array("very" => "very", "yes" => "yes", "no" => "no", "invite" => "invite", "port" => "port");

    $arrSelectdtmf = array("rfc2833" => "rfc2833", "inband" => "inband", "info" => "info");

    $arrFields = array(
            "type_provider_voip"   => array(      "LABEL"            => $arrLang["VoIP Provider"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrProviders,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "",
                                            ),
			"status"   => array(      "LABEL"          				 => $arrLang["Status"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrStatus,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "",
                                            ),
            "account_name"   => array(      "LABEL"                  => $arrLang["Account Name"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "account_name", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "username"   => array(      "LABEL"                  => $arrLang["Username"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "username", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "secret"   => array(      "LABEL"                  => $arrLang["Secret"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "secret", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "type"   => array(      "LABEL"                  => $arrLang["Type"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrSelectType,//array("id" => "type", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "qualify"   => array(      "LABEL"                  => $arrLang["Qualify"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrSelectForm,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "insecure"   => array(      "LABEL"                  => $arrLang["Insecure"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrSelectInsecure,//array("id" => "insecure", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "host"   => array(      "LABEL"                  => $arrLang["Host"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "host", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "fromuser"   => array(      "LABEL"                  => $arrLang["Fromuser"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "fromuser", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "fromdomain"   => array(      "LABEL"                  => $arrLang["Fromdomain"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "fromdomain", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "dtmfmode"   => array(      "LABEL"                  => $arrLang["Dtmfmode"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrSelectdtmf,//array("id" => "dtmfmode", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "disallow"   => array(      "LABEL"                  => $arrLang["Disallow"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "disallow", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "context"   => array(      "LABEL"                  => $arrLang["Context"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "context", "size" => "30"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "allow"   => array(      "LABEL"                  => $arrLang["Allow"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "allow", "size" => "30"),
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "trustrpid"   => array(      "LABEL"                  => $arrLang["Trustrpid"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrSelectForm,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "sendrpid"   => array(      "LABEL"                  => $arrLang["Sendrpid"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrSelectForm,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "canreinvite"   => array(      "LABEL"                  => $arrLang["Canreinvite"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrSelectCareInvite,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "technology"   => array(      "LABEL"                  => $arrLang["Technology"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrSelectTech,
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            );
    return $arrFields;
}

function createFieldFilter($arrLang){
    $arrFilter = array(
        "account_name" => $arrLang["Account Name"],
        "provider" => $arrLang["VoIP Provider"],
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


function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("delete")) 
        return "delete";
    else if(getParameter("new_account")) 
        return "view_new";
    else if(getParameter("action")=="getInfoProvider")      //Get parameter by GET (command pattern, links)
        return "getInfoProvider";
    else if(getParameter("action")=="view_edit")
        return "view_edit";
	else if(getParameter("action")=="activate")
        return "activate";
    else
        return "report"; //cancel
}
?>
