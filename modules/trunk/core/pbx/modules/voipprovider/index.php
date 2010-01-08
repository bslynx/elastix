<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.5.2-2                                               |
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
  $Id: index.php,v 1.1 2009-09-29 05:09:50 Oscar Navarrete onavarrete@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoVoIPProvider.class.php";

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
        case "save_new":
            $content = saveNewVoIPProvider($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
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
    $arrFormVoIPProvider = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormVoIPProvider);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataVoIPProvider = $pVoIPProvider->getVoIPProviderById($id);
        if(is_array($dataVoIPProvider) & count($dataVoIPProvider)>0)
            $_DATA = $dataVoIPProvider;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pVoIPProvider->errMsg);
        }
    }
    $arrProviders = array("none" => $arrLang["none"]);
    $result = $pVoIPProvider->getVoIPProviders();
    foreach($result as $values){
        $arrProviders[$values['name']] = $values['name'];
    }
    $smarty->assign("arrProviders", $arrProviders);//for the combobox
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["VoIP Provider"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewVoIPProvider($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pVoIPProvider = new paloSantoVoIPProvider($pDB);
    $arrFormVoIPProvider = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormVoIPProvider);

    if($_POST['type_provider']=="none") {
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $strErrorMsg = "<b>{$arrLang['Please select a type of VoIp Provider']}</b><br/>";
        $smarty->assign("mb_message", $strErrorMsg);
        return $content = viewFormVoIPProvider($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);

    }else if($pVoIPProvider->validateFormEmpty($_POST)) {
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $strErrorMsg = "<b>{$arrLang['Some or someone necesary fields are empty please check and complete']}</b><br/>";
        $smarty->assign("mb_message", $strErrorMsg);

        $arrProviders = array("none" => $arrLang["none"]);
        $result = $pVoIPProvider->getVoIPProviders();//Obtiene la lista para ser seteado en el listbox
        foreach($result as $values){
            $arrProviders[$values['name']] = $values['name'];
        }
        $smarty->assign("arrProviders", $arrProviders);
        $smarty->assign("type_provider_tmp", $_POST['type_provider']);
        $smarty->assign("username_post", $_POST['username']);
        $smarty->assign("secret_post", $_POST['secret']);

        $smarty->assign("type_post", $_POST['type']);
        $smarty->assign("qualify_post", $_POST['qualify']);
        $smarty->assign("insecure_post", $_POST['insecure']);
        $smarty->assign("host_post", $_POST['host']);
        $smarty->assign("fromuser_post", $_POST['fromuser']);
        $smarty->assign("fromdomain_post", $_POST['fromdomain']);
        $smarty->assign("dtmfmode_post", $_POST['dtmfmode']);
        $smarty->assign("disallow_post", $_POST['disallow']);
        $smarty->assign("context_post", $_POST['context']);
        $smarty->assign("allow_post", $_POST['allow']);
        $smarty->assign("trustrpid_post", $_POST['trustrpid']);
        $smarty->assign("sendrpid_post", $_POST['sendrpid']);
        $smarty->assign("canreinvite_post", $_POST['canreinvite']);

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
        $type_provider = getParameter("type_provider");

        if(!empty($_POST["username"])) { 
            $data_trunk['username'] = $pDB->DBCAMPO(getParameter("username"));
            $username = getParameter("username");
        }else $data_trunk['username'] = $pDB->DBCAMPO("");
        if(!empty($_POST["secret"])) { 
            $data_trunk['password'] = $pDB->DBCAMPO(getParameter("secret"));
            $secret = getParameter("secret");
        }else $data_trunk['password'] = $pDB->DBCAMPO("");

        if($_POST["type"]!=" ") $data_attribute['type'] = $pDB->DBCAMPO(getParameter("type")); else $data_attribute['type'] = null;
        if($_POST["qualify"]!=" ") $data_attribute['qualify'] = $pDB->DBCAMPO(getParameter("qualify")); else $data_attribute['qualify'] = null;
        if($_POST["insecure"]!=" ") $data_attribute['insecure'] = $pDB->DBCAMPO(getParameter("insecure")); else $data_attribute['insecure'] = null;
        if($_POST["host"]!=" "){ 
            $data_attribute['host'] = $pDB->DBCAMPO(getParameter("host"));
            $host = getParameter("host");
        }else $data_attribute['host'] = null;
        if($_POST["fromuser"]!=" ") $data_attribute['fromuser'] = $pDB->DBCAMPO(getParameter("fromuser")); else $data_attribute['fromuser'] = null;
        if($_POST["fromdomain"]!=" ") $data_attribute['fromdomain'] = $pDB->DBCAMPO(getParameter("fromdomain")); else $data_attribute['fromdomain'] = null;
        if($_POST["dtmfmode"]!=" ") $data_attribute['dtmfmode'] = $pDB->DBCAMPO(getParameter("dtmfmode")); else $data_attribute['dtmfmode'] = null;
        if($_POST["disallow"]!=" ") $data_attribute['disallow'] = $pDB->DBCAMPO(getParameter("disallow")); else $data_attribute['disallow'] = null;
        if($_POST["context"]!=" ") $data_attribute['context'] = $pDB->DBCAMPO(getParameter("context")); else $data_attribute['context'] = null;
        if($_POST["allow"]!=" ") $data_attribute['allow'] = $pDB->DBCAMPO(getParameter("allow")); else $data_attribute['allow'] = null;
        if($_POST["trustrpid"]!=" ") $data_attribute['trustrpid'] = $pDB->DBCAMPO(getParameter("trustrpid")); else $data_attribute['trustrpid'] = null;
        if($_POST["sendrpid"]!=" ") $data_attribute['sendrpid'] = $pDB->DBCAMPO(getParameter("sendrpid")); else $data_attribute['sendrpid'] = null;
        if($_POST["canreinvite"]!=" ") $data_attribute['canreinvite'] = $pDB->DBCAMPO(getParameter("canreinvite")); else $data_attribute['canreinvite'] = null;
        
        if($type_provider=="Net2Phone"){
            $type_trunk = "sip";
            $data_provider['id_trunk'] = 1;
            $pVoIPProvider->updateTrunkParameter($data_trunk, array("id"=>1));
            $pVoIPProvider->updateTrunkAttribute($data_attribute, array("id"=>1));

        }else if($type_provider=="camundaNET"){
            $type_trunk = "sip";
            $data_provider['id_trunk'] = 2;
            $pVoIPProvider->updateTrunkParameter($data_trunk, array("id"=>2));
            $pVoIPProvider->updateTrunkAttribute($data_attribute, array("id"=>2));

        }else if($type_provider=="Vitelity"){
            $type_trunk = "sip";
            $data_provider['id_trunk'] = 3;
            $pVoIPProvider->updateTrunkParameter($data_trunk, array("id"=>3));
            $pVoIPProvider->updateTrunkAttribute($data_attribute, array("id"=>3));

        }else if($type_provider=="NuFoneIAX"){
            $type_trunk = "iax2";
            $data_provider['id_trunk'] = 4;
            $pVoIPProvider->updateTrunkParameter($data_trunk, array("id"=>4));
            $pVoIPProvider->updateTrunkAttribute($data_attribute, array("id"=>4));

        }else if($type_provider=="StarVox"){
            $type_trunk = "sip";
            $data_provider['id_trunk'] = 5;
            $pVoIPProvider->updateTrunkParameter($data_trunk, array("id"=>5));
            $pVoIPProvider->updateTrunkAttribute($data_attribute, array("id"=>5));
        }

        $find1 = $pVoIPProvider->findTrunkInExtensionCustom($type_provider);
        $find2 = $pVoIPProvider->findTrunkInLocalPrefixes($type_provider);//Nuevo Entrada de parametro
        if($type_trunk=="sip") {
            $find3 = $pVoIPProvider->findTrunkInSipCustom($type_provider);
            $find4 = $pVoIPProvider->findTrunkInSipRegistrations($host);
        }else{
            $find3 = $pVoIPProvider->findTrunkInIaxCustom($type_provider);
            $find4 = $pVoIPProvider->findTrunkInIaxRegistrations($host);
        }
        //exec("echo '$find1' > /tmp/oscar");
        if($find1=="false") {
            $pVoIPProvider->AddConfFileExtensionCustom($type_provider, $type_trunk);
        }
        if($find2=="false") {
            $pVoIPProvider->addConfFileLocalPrefixes($type_provider);//Nuevo Entrada de parametro
        }//Falta hacer el update de las reglas (No considerar)
        
        if($find4=="false") {
            if($type_trunk=="sip") $pVoIPProvider->addConfFileSipRegistrations($username, $secret, $host);//duda
            else $pVoIPProvider->addConfFileIaxRegistrations($username, $secret, $host);
        }else{
            if($type_trunk=="sip") $pVoIPProvider->updateFileSipRegistrations($username, $secret, $host);
            else $pVoIPProvider->updateFileIaxRegistrations($username, $secret, $host);
        }
        if($find3=="false"){
            if($type_trunk=="sip") $pVoIPProvider->addConfFileSipCustom($type_provider);
            else $pVoIPProvider->addConfFileIaxCustom($type_provider);
        }else{
            if($type_trunk=="sip") $pVoIPProvider->updateFileSipCustom($type_provider);//funcion por revisar AUN
            else $pVoIPProvider->updateFileIaxCustom($type_provider);
        }
        
        header("Location: ?menu=$module_name&action=view_form");
    }

}

function createFieldForm($arrLang)
{
    $arrFields = array(
            "type_provider_voip"   => array(      "LABEL"           => $arrLang["VoIP Provider"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "",
                                            ),
            "username"   => array(      "LABEL"                  => $arrLang["Username"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "secret"   => array(      "LABEL"                  => $arrLang["Secret"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "configuration"   => array(      "LABEL"                  => $arrLang["Configuration"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "type"   => array(      "LABEL"                  => $arrLang["Type"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "qualify"   => array(      "LABEL"                  => $arrLang["Qualify"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "insecure"   => array(      "LABEL"                  => $arrLang["Insecure"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "host"   => array(      "LABEL"                  => $arrLang["Host"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "fromuser"   => array(      "LABEL"                  => $arrLang["Fromuser"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "fromdomain"   => array(      "LABEL"                  => $arrLang["Fromdomain"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "dtmfmode"   => array(      "LABEL"                  => $arrLang["Dtmfmode"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "disallow"   => array(      "LABEL"                  => $arrLang["Disallow"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "context"   => array(      "LABEL"                  => $arrLang["Context"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "allow"   => array(      "LABEL"                  => $arrLang["Allow"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "trustrpid"   => array(      "LABEL"                  => $arrLang["Trustrpid"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "sendrpid"   => array(      "LABEL"                  => $arrLang["Sendrpid"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "canreinvite"   => array(      "LABEL"                  => $arrLang["Canreinvite"],
                                            "REQUIRED"               => "",
                                            "INPUT_TYPE"             => "",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("delete")) 
        return "delete";
    else if(getParameter("new_open")) 
        return "view_form";
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("action")=="view_edit")
        return "view_form";
    else
        return "report"; //cancel
}
?>
