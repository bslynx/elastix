<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.6-6                                               |
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
  $Id: index.php,v 1.1 2009-08-26 09:08:29 Oscar Navarrete onavarrete@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoEmail.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoEmaillist.class.php";

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
        case "save_newList":
            $content = saveNewEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "save_newMember":
            $content = saveNewEmailMember($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "new_emaillist":
            $content = viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "new_memberlist":
            $content = viewFormMemberlist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "delete_list":
            $content = delete_emailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "delete_member":
            $content = delete_memberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        default: 
            $content = reportEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function reportEmaillist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmaillist = new paloSantoEmaillist($pDB);
    $filter_field = "";
    $filter_value = "";
    $action = getParameter("nav");
    $start  = getParameter("start");
    
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $limit  = 20;
    $pEmaillist->ejecucion();
    if (isset($_POST['domain'])){ 
        $id_domain=$_POST['domain'];
        $arrResult =$pEmaillist->getEmailListByDomainDB($id_domain);
        $totalEmaillist = $pEmaillist->getNumEmaillist($id_domain);
        $total  = $totalEmaillist;
    }else{
        $id_domain="";
        $arrResult=null;
        $total  = 0;
    }
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&id_domain=$id_domain";

    $arrData = null;

    if(is_array($arrResult) && count($arrResult)>0){
        foreach($arrResult as $key => $value){
            $arrTmp[0]  = "<input type='checkbox' name='EmaillistID_{$value['id']}'  />";
            $arrTmp[1] = "<a href='?menu=$module_name&action=show&id=".$value['id']."'>{$value['listname']}</a>";
            $arrMember = $pEmaillist->getMembersByListDB($value['id']);
            $arrTmp[2] = count($arrMember);
            $arrTmp[3] = "<a href='?menu=$module_name&action=new_memberlist&id=".$value['id']."&namelist=".$value['listname']."'>Add Members</a>";
    
            $arrData[] = $arrTmp;
        }
    }

    $buttonDelete = "<input type='submit' name='delete_list' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the email list."]}');\" />";

    $arrGrid = array("title"    => $arrLang["Email list"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
            0 => array("name"      => $buttonDelete,
                                   "property1" => ""),
            1 => array("name"      => $arrLang["Name"],
                                   "property1" => ""),
            2 => array("name"      => $arrLang["Number of Accounts"],
                                   "property1" => ""),
            3 => array("name"      => $arrLang["Action"],
                                   "property1" => ""),
                                        )
                    );
    //begin section filter
    $pEmail = new paloEmail($pDB);
    $arrDominios    = array("0"=>'-- '.$arrLang["Select a domain"].' --');
    $arrDomains = $pEmail->getDomains();
    foreach($arrDomains as $domain) {
        $arrDominios[$domain[0]] = $domain[1];
    }

    $arrFormFilterEmaillist = createFieldFilter($arrLang, $arrDominios);
    $oFilterForm = new paloForm($smarty, $arrFormFilterEmaillist);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("NEW_EMAILLIST", $arrLang["New Email list"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    //end grid parameters

    return $contenidoModulo;
}

function createFieldFilter($arrLang, $arrDominios){

     $arrFormElements = array(
            "domain"   => array("LABEL"          => $arrLang["Domain"],
                                    "REQUIRED"               => "yes",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrDominios,
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => "",
                                    "EDITABLE"               => "si", ),
                );
    return $arrFormElements;
}


function viewFormEmaillist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmaillist = new paloSantoEmaillist($pDB);
    $pEmail = new paloEmail($pDB);
    $arrDominios    = array("0"=>'-- '.$arrLang["Select a domain"].' --');
    $arrDomains = $pEmail->getDomains();
    foreach($arrDomains as $domain) {
        $arrDominios[$domain[0]]    = $domain[1];
    }

    $arrFormEmaillist = createFieldForm($arrLang, $arrDominios);
    $oForm = new paloForm($smarty,$arrFormEmaillist);
    
    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    if($pEmaillist->checkFileMm_cfg()=="New"){
        $smarty->assign("StatusNew", 1);
    }

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataEmaillist = $pEmaillist->getEmaillistById($id);
        if(is_array($dataEmaillist) & count($dataEmaillist)>0)
            $_DATA = $dataEmaillist;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pEmaillist->errMsg);
        }
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Email list"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewEmaillist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmaillist = new paloSantoEmaillist($pDB);
    $pEmail = new paloEmail($pDB);
    $arrDominios    = array("0"=>'-- '.$arrLang["Select a domain"].' --');
    $arrDomains = $pEmail->getDomains();
    foreach($arrDomains as $domain) {
        $arrDominios[$domain[0]]    = $domain[1];
    }

    $arrFormEmaillist = createFieldForm($arrLang, $arrDominios);
    $oForm = new paloForm($smarty,$arrFormEmaillist);

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        return $content = viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }elseif($_POST['password']!=$_POST['passwordconfirm']){
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $smarty->assign("mb_message", $arrLang["The Input Password and Password Confirm aren't equals.. Please Try Again"]);
        return $content = viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);

    }else{
        $dataEmailList = array();
        $data = array();

        $domainData = $pEmail->getDomains($_POST['domain']);
        if(!empty($_POST['emailmailman'])){
            $pEmaillist->execConfigMailMan_1("palosanto",$_POST['emailmailman'],$_POST['passwdmailman'], $domainData['0']['1']);
        }
        $dataEmailList['id_domain'] = $pDB->DBCAMPO($_POST['domain']);
        $dataEmailList['listname'] = $pDB->DBCAMPO($_POST['namelist']);
        $dataEmailList['password'] = $pDB->DBCAMPO($_POST['password']);
        $dataEmailList['mailadmin'] = $pDB->DBCAMPO($_POST['emailadmin']);

        $pEmaillist->addNewMailList($_POST['emailadmin'], $_POST['password'], $_POST['namelist']);
        $result = $pEmaillist->addEmailListDB($dataEmailList);
        
        header("Location: ?menu=$module_name&action=new_emaillist");
    }
}

function createFieldForm($arrLang, $arrDominios)
{
    $arrFields = array(
            "emailmailman"   => array(      "LABEL"                  => $arrLang["Email Mailmam"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "passwdmailman"   => array(      "LABEL"                  => $arrLang["Password Mailman"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),

            "domain"   => array(      "LABEL"                  => $arrLang["Domain Name"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrDominios,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),

            "namelist"   => array(      "LABEL"                  => $arrLang["Name"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "password"   => array(      "LABEL"                  => $arrLang["Password"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "passwordconfirm"   => array(      "LABEL"                  => $arrLang["Password Confirm"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "emailadmin"   => array(      "LABEL"                  => $arrLang["Email Admin"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            );
    return $arrFields;
}

function viewFormMemberlist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmaillist = new paloSantoEmaillist($pDB);

    $arrFormEmaillist = createFieldFormMember($arrLang);
    $oForm = new paloForm($smarty,$arrFormEmaillist);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $emallistname     = getParameter("namelist");
    $smarty->assign("IDEMAILLIST", $id); //persistence id with input hidden in tpl
    $smarty->assign("EMAILLIST", $emallistname);    

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataEmaillist = $pEmaillist->getEmaillistById($id);
        if(is_array($dataEmaillist) & count($dataEmaillist)>0)
            $_DATA = $dataEmaillist;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pEmaillist->errMsg);
        }
    }

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form_member.tpl",$arrLang["Email Member"], $_DATA);
    $arrResult = $pEmaillist->getMembersByListDB($id);
    if(count($arrResult)>0){
        $htmlForm .= reportEmailMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang, $id, count($arrResult));
    }

    $content = "<form method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewEmailMember($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmaillist = new paloSantoEmaillist($pDB);
    $arrFormEmailmember = createFieldFormMember($arrLang, $arrDominios);
    $oForm = new paloForm($smarty,$arrFormEmailmember);

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        return $content = viewFormMemberlist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }
    else{
        $id_emaillist     = getParameter("id_emaillist");
        $emaillistname     = getParameter("name_emaillist");
    exec("echo '-$id_emaillist--$emaillistname-' > /tmp/oscar");
        $dataEmailMember = array();
        $dataEmailMember['id_emaillist'] = $pDB->DBCAMPO($id_emaillist);
        $dataEmailMember['mailmember'] = $pDB->DBCAMPO($_POST['emailmember']);

        $pEmaillist->addNewMember($emaillistname, $_POST['emailmember']);
        $result = $pEmaillist->addEmailMemberDB($dataEmailMember);
        
        header("Location: ?menu=$module_name&action=new_memberlist&id=$id_emaillist&namelist=$emaillistname");
    }

}


function reportEmailMemberList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang, $id, $size)
{
    $pEmaillist = new paloSantoEmaillist($pDB);

    $filter_field = "";
    $filter_value = "";
    $action = getParameter("nav");
    $start  = getParameter("start");
    
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);

    $limit  = 20;
    $oGrid->setLimit($limit);
    $oGrid->pagingShow(false);
    $oGrid->setTotal($size);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name";

    $arrData = null;
    $arrResult =$pEmaillist->getMembersByListDB($id);
    
    if(is_array($arrResult) && count($arrResult)>0){
        foreach($arrResult as $key => $value){
            $arrTmp[0]  = "<input type='checkbox' name='MailMembID_{$value['id']}'  />";
            $arrTmp[1] = $value['mailmember'];
                $arrData[] = $arrTmp;
        }
    }

    $buttonDelete = "<input type='submit' name='delete_member' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the Member."]}');\" />";

    $arrGrid = array("title"    => $arrLang["Email List Members"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($size==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $size,
                        "url"      => $url,
                        "columns"  => array(
            0 => array("name"      => $buttonDelete,
                                   "property1" => ""),
            1 => array("name"      => $arrLang["Name"],
                                   "property1" => ""),
                                        )
                    );

    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    //end grid parameters
    return $contenidoModulo;
}


function createFieldFormMember($arrLang)
{
    $arrFields = array(
        "emailmember"   => array(      "LABEL"                  => $arrLang["Email Member"],
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
        );
    return $arrFields;
}


function delete_emailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang)
{
    $pEmaillist = new paloSantoEmaillist($pDB);

    foreach($_POST as $key => $values){
        if(substr($key,0,12) == "EmaillistID_")
        {
            $emailListId = substr($key, 12);
            $result = $pEmaillist->getEmaillistById($emailListId);
            $listName = $result['listname'];
            $pEmaillist->deleteEmailListDB($emailListId);
            //en paralelo con comando de mailman tamb
            $pEmaillist->deleteEmailListMM($listName);
        }
    }
    header("Location: ?menu=$module_name&action=report");
}


function delete_memberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang)
{
    $pEmaillist = new paloSantoEmaillist($pDB);
    
    $id     = getParameter("id_emaillist");
    $emallistname     = getParameter("name_emaillist");

    foreach($_POST as $key => $values){
        if(substr($key,0,11) == "MailMembID_")
        {
            $MemberId = substr($key, 11);
            $result = $pEmaillist->getMemberlistByIdDB($MemberId);
            $mailMember = $result['mailmember'];
            $pEmaillist->deleteEmailMemberDB($MemberId);
            //en paralelo la ejecucion con comando de mailman tamb
            $pEmaillist->deleteEmailMemberMM(trim($emallistname), trim($mailMember));
        }
    }
    header("Location: ?menu=$module_name&action=new_memberlist&id=$id&namelist=$emallistname");
    
}

function getAction()
{
    if(getParameter("save_newList")) //Get parameter by POST (submit)
        return "save_newList";
    if(getParameter("save_newMember")) //Get parameter by POST (submit)
        return "save_newMember";
    else if(getParameter("new_emaillist"))
        return "new_emaillist";
    else if(getParameter("delete_member"))
        return "delete_member";
    else if(getParameter("delete_list")) 
        return "delete_list";
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("action")=="new_memberlist")
        return "new_memberlist";
    else
        return "report"; //cancel
}
?>
