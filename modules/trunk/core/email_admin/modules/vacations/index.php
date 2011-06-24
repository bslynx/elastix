<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4-23                                               |
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
  $Id: index.php,v 1.1 2011-06-07 12:06:28 Eduardo Cueva ecueva@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoACL.class.php";
include_once "modules/antispam/libs/paloSantoAntispam.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoVacations.class.php";

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
    $pDB    = new paloDB($arrConf['dsn_conn_database']);
    $pDBACL = new paloDB($arrConf['elastix_dsn']['acl']);
    
    //actions
    $action = getAction();
    $content = "";

    switch($action){
	case "activate":
	    $content = activateEmailVacations($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
	    break;
	case "disactivate":
	    $content = disactivateEmailVacations($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
	    break;
	case "showAllEmails":
	    $content = showAllEmails($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
	    break;
        default: // view_form
            $content = viewFormVacations($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function viewFormVacations($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBACL, $arrConf, $arrLang)
{
    $pVacations  = new paloSantoVacations($pDB);
    $objAntispam = new paloSantoAntispam($arrConf['path_postfix'], $arrConf['path_spamassassin'],$arrConf['file_master_cf'], $arrConf['file_local_cf']);
    $arrFormVacations = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormVacations);
    $pACL = new paloACL($pDBACL);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $link_emails = "";
    
    $userAccount = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $idUserInt = $pACL->getIdUser($userAccount);
    $email = $pVacations->getAccountByIdUser($idUserInt, $pDBACL);
    if($pACL->isUserAdministratorGroup($userAccount)){
	   $link_emails = "<a href='javascript: popup_get_emails(\"?menu=$module_name&action=showAllEmails&rawmode=yes\");' name='getEmails' id='getEmails' style='cursor: pointer;'>"._tr("Choose other email account")."</a>";
    }
    if(isset($email) && $email!=""){
	$_DATA['email'] = $email;
	$rowsVacations = $pVacations->getMessageVacationByUser($email);
	if(isset($rowsVacations) && $rowsVacations!=""){
	    $_DATA['subject'] = $rowsVacations['subject'];
	    $_DATA['body'] = $rowsVacations['body'];
	    $id = $rowsVacations['id'];
	}else{
	    $_DATA['subject'] = _tr("Auto-Reply: Out of the office");
	    $_DATA['body'] = _tr("I'm sorry I am currently out on vacations, your email will be replied as soon as I get back to the office.\n\n----\nBest Regards.");
	}
    }
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl
    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataVacations = $pVacations->getVacationsById($id);
        if(is_array($dataVacations) & count($dataVacations)>0)
            $_DATA = $dataVacations;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pVacations->errMsg);
        }
    }
    $statusSieve = $pVacations->verifySieveStatus($arrLang);
    if(!$statusSieve['response']){
	$smarty->assign("mb_title", _tr("Alert"));
        $smarty->assign("mb_message",$statusSieve['message']);
    }

    $activate = "disabled";
    $scripts = $objAntispam->existScriptSieve($email, "scriptTest.sieve");

    if($scripts['actived'] != ""){
	if(preg_match("/vacations.sieve/",$scripts['actived']))
	    $activate = "enabled";
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("SAVE_MESSAGE", _tr("Save Message"));
    $smarty->assign("DISACTIVATE_MESSAGE", _tr("Disable Message Vacations"));
    $smarty->assign("ACTIVATE_MESSAGE", _tr("Enable Message Vacations"));
    $smarty->assign("activate", $activate);
    $smarty->assign("link_emails", $link_emails);
    $smarty->assign("title_popup", _tr("Choose other email account"));

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Vacations"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function activateEmailVacations($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBACL, $arrConf, $arrLang)
{
    $pVacations = new paloSantoVacations($pDB);
    $pACL = new paloACL($pDBACL);
    $objAntispam = new paloSantoAntispam($arrConf['path_postfix'], $arrConf['path_spamassassin'],$arrConf['file_master_cf'], $arrConf['file_local_cf']);
    $id         = getParameter("id");
    $email      = getParameter("email");
    $subject    = getParameter("subject");
    $body       = getParameter("body");
    $result     = "";

    $userAccount = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $idUserInt = $pACL->getIdUser($userAccount);
    $emails = $pVacations->getAccountByIdUser($idUserInt, $pDBACL);

    if($email != $emails){
	if(!$pACL->isUserAdministratorGroup($userAccount)){
	    $smarty->assign("mb_title", _tr("Error"));
	    $smarty->assign("mb_message",$statusSieve['Email is not correct. Please write the email assigned to your elastix account.']);
	    return viewFormVacations($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
	}
    }

    $statusSieve = $pVacations->verifySieveStatus($arrLang);
    if(!$statusSieve['response']){
	$smarty->assign("mb_title", _tr("Alert"));
        $smarty->assign("mb_message",$statusSieve['message']);
	return viewFormVacations($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
    }
    $pDB->beginTransaction();
    $scripts = $objAntispam->existScriptSieve($email, "scriptTest.sieve");
    $spamCapture = false;// si CapturaSpam=OFF y Vacations=OFF
    if($scripts['actived'] != ""){// hay un script activo
	if(preg_match("/scriptTest.sieve/",$scripts['actived'])) // si CapturaSpam=ON y Vacations=OFF
	    $spamCapture = true;// si CapturaSpam=ON y Vacations=OFF
    }

    $band = $pVacations->existMessage($id, $email);
    $res = "";
    if($band){//actualizacion
	$res = $pVacations->updateMessageByUser($email, $subject, $body, $id, "yes");
    }else{// insersion
	$res = $pVacations->insertMessageByUser($email, $subject, $body, "yes");
    }

    if($res)
	$result = $pVacations->uploadVacationScript($email, $subject, $body, $objAntispam, $spamCapture, $arrLang);
    else
	$result = false;

    if($result){
	$pDB->commit();
	$smarty->assign("mb_message",_tr("Email's Vacations have been enabled"));
    }else{
	$pDB->rollBack();
	$msgError = $pVacations->errMsg;
	$smarty->assign("mb_message", $msgError);
    }
    return viewFormVacations($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
}

function disactivateEmailVacations($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBACL, $arrConf, $arrLang)
{
    $pVacations  = new paloSantoVacations($pDB);
    $pACL = new paloACL($pDBACL);
    $objAntispam = new paloSantoAntispam($arrConf['path_postfix'], $arrConf['path_spamassassin'],$arrConf['file_master_cf'], $arrConf['file_local_cf']);
    $id         = getParameter("id");
    $email      = getParameter("email");
    $subject    = getParameter("subject");
    $body       = getParameter("body");
    $result 	= "";

    $userAccount = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $idUserInt = $pACL->getIdUser($userAccount);
    $emails = $pVacations->getAccountByIdUser($idUserInt, $pDBACL);

    if($email != $emails){
	if(!$pACL->isUserAdministratorGroup($userAccount)){
	    $smarty->assign("mb_title", _tr("Error"));
	    $smarty->assign("mb_message",$statusSieve['Email is not correct. Please write the email assigned to your elastix account.']);
	    return viewFormVacations($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
	}
    }

    $statusSieve = $pVacations->verifySieveStatus($arrLang);
    if(!$statusSieve['response']){
	$smarty->assign("mb_title", _tr("Alert"));
        $smarty->assign("mb_message",$statusSieve['message']);
	return viewFormVacations($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
    }
    $pDB->beginTransaction();

    

    $scripts = $objAntispam->existScriptSieve($email, "scriptTest.sieve");
    $spamCapture = false;// si CapturaSpam=OFF y Vacations=OFF
    if($scripts['actived'] != ""){// hay un script activo
	if(preg_match("/vacations.sieve/",$scripts['actived']) && $scripts['status']) // si CapturaSpam=? y Vacations=ON
	    $spamCapture = true;// si CapturaSpam=ON y Vacations=OFF

	$band = $pVacations->existMessage($id, $email);
	$res = "";
	if($band){//actualizacion
	    $res = $pVacations->updateMessageByUser($email, $subject, $body, $id, "no");
	}else{// insersion
	    $res = $pVacations->insertMessageByUser($email, $subject, $body, "no");
	}
	if($res)
	    $result = $pVacations->deleteVacationScript($email, $objAntispam, $spamCapture, $arrLang);
	else
	    $result = false;
    }

    if($result){
	$pDB->commit();
	$smarty->assign("mb_message",_tr("Email's Vacations have been disabled"));
    }else{
	$msgError = $pVacations->errMsg;
	$pDB->rollBack();
	$smarty->assign("mb_message", $msgError);
    }
    return viewFormVacations($smarty, $module_name, $local_templates_dir, $pDB, $pDBACL, $arrConf, $arrLang);
}

function showAllEmails($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBACL, $arrConf, $arrLang)
{
    $pVacations    = new paloSantoVacations($pDB);
    $oGrid         = new paloSantoGrid($smarty);
    $pACL          = new paloACL($pDBACL);
    $id            = getParameter("id");
    $filter_field  = getParameter("filter_field");
    $filter_value  = getParameter("filter_value");

    $userAccount = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";

    $url = array(
        "menu"         =>  $module_name,
        "filter_field" =>  $filter_field,
        "filter_value" =>  $filter_value
    );

    if(!$pACL->isUserAdministratorGroup($userAccount)){
	  return _tr("User isn't allowed to view this content.");
    }else{
	  $totalEmail = $pVacations->getNumVacations($filter_field, $filter_value, $arrLang);
	  $url = array_merge($url, array('rawmode' => 'yes'));

	  $oGrid->setURL($url);
	  $oGrid->setTitle(_tr("Emails Account"));

	  $limit  = 10;
	  $total  = $totalEmail;
	  $oGrid->setLimit($limit);
	  $oGrid->setTotal($total);
	  //$oGrid->enableExport(false);   // enable csv export.
	  $oGrid->pagingShow(true); // show paging section.

	  $offset = $oGrid->calculateOffset();
	  $arrData = null;

	  $arrResult =$pVacations->getVacations($limit, $offset, $filter_field, $filter_value, $arrLang);
	  $tmpIDs = 1;
	  if(is_array($arrResult) && $total>0){
	      foreach($arrResult as $key => $value){
		  $tmpAccountId = $tmpIDs."Id";
		  $arrTmp[0] = "<a href='javascript:getAccount(\"".$value['username']."\",\"$tmpAccountId\");' class='getAccount' id='$tmpAccountId' >".$value['username']."</a>";
		  if($value['vacation']=="yes")
		      $arrTmp[1] = _tr("yes");
		  else
		      $arrTmp[1] = _tr("no");
		  if(!isset($value['subject']) || $value['subject'] == "")
		      $value['subject'] = _tr("Auto-Reply: Out of the office");
		  if(!isset($value['body']) || $value['body'] == "")
		      $value['body'] = _tr("I'm sorry I am currently out on vacations, your email will be replied as soon as I get back to the office.\n\n----\nBest Regards.");
		  $arrTmp[2] = "&nbsp;<div style='display: none;'>".$value['subject']."</div>";
		  $arrTmp[3] = "&nbsp;<div style='display: none;'>".$value['body']."</div>";
		  $arrTmp[4] = "&nbsp;<div style='display: none;'>".$value['vacation']."</div>";
		  $arrData[] = $arrTmp;
		  $tmpIDs++;
	      }
	  }

	  $arrColumns = array(_tr("Account"),_tr("Vacations Activated"), "", "", "");
	  $oGrid->setColumns($arrColumns);

	  $oGrid->setData($arrData);
	  $size = count($arrData);

	  //begin section filter
	  $arrFormFilter = createFieldFilter($arrLang);
	  $oFilterForm = new paloForm($smarty, $arrFormFilter);
	  $smarty->assign("SHOW", $arrLang["Show"]);

	  $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/emailsGrid.tpl","",$_POST);
	  //end section filter

	  $oGrid->showFilter(trim($htmlFilter));

	  $content = $oGrid->fetchGrid();
	  //end grid parameters
    }
    return $content;
}


function createFieldForm($arrLang)
{

    $arrFields = array(
            "email"   => array(      "LABEL"                  => $arrLang["email address"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id"=>"email","readonly"=>"readonly","style"=>"width: 200px;"),
                                            "VALIDATION_TYPE"        => "email",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "subject"   => array(      "LABEL"                  => $arrLang["Subject"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id"=>"subject", "style" => "width: 370px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "body"   => array(      "LABEL"                  => $arrLang["Body"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"      => array("id"=>"body","style"=>"width: 368px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "COLS"                   => "0",
                                            "ROWS"                   => "4",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),

            );
    return $arrFields;
}

function createFieldFilter($arrLang){
    $arrFilter = array(
	    "username" => $arrLang["Account"],
	    "vacation" => $arrLang["Vacations"],
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
    else if(getParameter("activate"))
        return "activate";
    else if(getParameter("disactivate"))      //Get parameter by GET (command pattern, links)
        return "disactivate";
    else if(getParameter("action") == "showAllEmails")
	return "showAllEmails";
    else
        return "report"; //cancel
}
?>