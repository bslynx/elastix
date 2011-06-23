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
  $Id: index.php,v 1.2 2007/08/10 01:32:53 gcarrillo Exp $
  $Id: index.php,v 1.3 2011/06/21 17:30:33 Eduardo Cueva ecueva@palosanto.com Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoEmail.class.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/cyradm.php";
    include_once "configs/email.conf.php";

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

    $pDB = new paloDB($arrConf['dsn_conn_database']);

    $error="";
    $errMsg = "";
    $contenidoModulo = "";
    $arrData = array();
    

    $virtual_postfix = FALSE; // indica si se debe escribir el archivo /etc/postfix/virtual

    $bMostrarListado=TRUE;

    $content = "";
    $accion = getAction();
    switch($accion)
    {
        case "new":
            $content = viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
	case "save":
	    $content = saveAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    break;
	case "delete":
	    $content = deleteAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    break;
	case "edit":
	    $content = viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    break;
	case "apply_changes":
	    $content = saveAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    break;
	case "view":
	    $content = viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    break;
        default:
            $content = viewFormAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }

    return $content;
}


function viewFormAccount($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmail = new paloEmail($pDB);
    $oGrid = new paloSantoGrid($smarty);
    $id_domain=0;
    
    if (isset($_POST['domain'])) $id_domain=$_POST['domain'];
    if (isset($_GET['id_domain'])) $id_domain=$_GET['id_domain'];

    $_POST['domain']=$id_domain;
    
    $arrDominios    = array("0"=>'-- '.$arrLang["Select a domain"].' --');

    $arrDomains = $pEmail->getDomains();
    foreach($arrDomains as $domain) {
	$arrDominios[$domain[0]] = $domain[1];
    }

    $arrFormElements = createFieldFormAccount($arrLang, $arrDominios);
    
    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("CREATE_ACCOUNT", $arrLang["Create Account"]);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/accounts_filter.tpl", "", $_POST);


    $oGrid->pagingShow(true);
    $url = array("menu" => $module_name);
    $oGrid->setURL($url);
    $oGrid->setTitle(_tr("Email Account List"));

    $total = 0;
    $limit  = 20;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    

////////////////////////////////////////////////////////////////////////////////////////////////////////

    $arrData = array();

    $end=0;
    if ($id_domain>0){
	$arrAccounts = $pEmail->getAccountsByDomain($id_domain);
//username, password, id_domain, quota

	$end = count($arrAccounts);
	//$configPostfix2 = isPostfixToElastix2();// in misc.lib.php
	foreach($arrAccounts as $account) {
	    $arrTmp    = array();
	    $username=$account[0];
	    /*$arrAlias=$pEmail->getAliasAccount($username);
	    $direcciones='';
	    if(is_array($arrAlias) && count($arrAlias)>0){
		foreach($arrAlias as $fila){
		    $direcciones.=(empty($direcciones))?'':'<br>';
		    $direcciones.=$fila['1'];
		}
	    }
	    $id_domain=$account[2];
	    $arrTmp[0]=$direcciones;*/
	    $arrTmp[0] = "&nbsp;<a href='?menu=email_accounts&action=view&username=$username'>$username</a>";
	    $arrTmp[1] = obtener_quota_usuario($username);
	    $link_agregar_direccion="<a href='?action=add_address&id_domain=$id_domain&username=$username'>Add Address</a>";
	    $link_modificar_direccion="<a href='?action=edit_addresses&id_domain=$id_domain&username=$username'>Addresses</a>";
	    //$arrTmp[3]=$link_agregar_direccion."&nbsp;&nbsp; ".$link_modificar_direccion;
	    $arrData[] = $arrTmp;
	}
    }
    $smarty->assign("id_domain",$id_domain);

    $oGrid->setData($arrData);
    $arrColumns = array(_tr("Account Name"),_tr("Used Space"),);
    $oGrid->setColumns($arrColumns);
    $oGrid->setData($arrData);
    $oGrid->showFilter(trim($htmlFilter));
    $content = $oGrid->fetchGrid();
    return $content;
}

function viewDetailAccount($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmail = new paloEmail($pDB);
    $arrFormElements = createFieldFormNewAccount($arrLang);
    $oForm    = new paloForm($smarty, $arrFormElements);
    //- TODO: Tengo que validar que el id sea valido, si no es valido muestro un mensaje de error
    $typeForm    = $arrLang["View Account"];
    $arrTmp      = array();
    $userName    = getParameter('username');
    $quota       = getParameter("quota");
    $id_domain   = getParameter("id_domain");
    $domain_name = getParameter("domain");
    $address     = getParameter("address");

    if(getParameter("action") == "view"){
	$oForm->setViewMode(); // Esto es para activar el modo "preview"
    }elseif(getParameter("submit_create_account") || getParameter("save")){
	//nothing
	$typeForm = _tr("Create Account");
	//obtener el nombre del dominio
	$domain_name = isset($domain_name)?$domain_name:$id_domain;
	$id_domain = $domain_name;
	$arrDomain= $pEmail->getDomains($domain_name);
	$domain_name="@".$arrDomain[0][1];
	$smarty->assign("domain_name", $domain_name);
	$smarty->assign("domainName", $arrDomain[0][1]);
	$arrTmp['address']   = isset($address)?$address:"";
	$arrTmp['password1'] = "";
	$arrTmp['password2'] = "";
	$arrTmp['quota']     = isset($quota)?$quota:"";
	$smarty->assign("old_quota", $arrTmp['quota']);
    }else{
	$oForm->setEditMode();
	$typeForm = _tr("Edit Account");
    }

    if($oForm->modo == "view" || $oForm->modo == "edit"){
	$arrAccount = $pEmail->getAccount($userName);
	//username, password, id_domain, quota
	$arrTmp['username']  = $arrAccount[0][0];
	$arrTmp['password1'] = "";
	$arrTmp['password2'] = "";
	$arrTmp['quota']     = isset($quota)?$quota:$arrAccount[0][3];
	$id_domain           = $arrAccount[0][2];
	$smarty->assign("username", $userName);
	$smarty->assign("id_domain", $id_domain);
	$smarty->assign("old_quota", $arrTmp['quota']);
    }

    $smarty->assign("id_domain", $id_domain);
    $smarty->assign("account_name_label", $arrLang['Account Name']);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("APPLY_CHANGES", $arrLang["Apply changes"]);
    $smarty->assign("CONFIRM_CONTINUE", $arrLang["Are you sure you wish to continue?"]);
    $content = $oForm->fetchForm("$local_templates_dir/form_account.tpl", $typeForm, $arrTmp); // hay que pasar el arreglo
    return $content;
}


function saveAccount($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmail = new paloEmail($pDB);
    $arrFormElements = createFieldFormNewAccount($arrLang);
    $noCambioPass = FALSE;
    $oForm = new paloForm($smarty, $arrFormElements);
    $password1   = getParameter("password1");
    $password2   = getParameter("password2");
    $id_domain   = getParameter("id_domain");
    $userName    = getParameter("username");
    $domain_name = getParameter("domain_name");

    $error = "";
    $bExito = FALSE;

    if (empty($password1) && empty($password2)){
	$noCambioPass = TRUE;
	$password1 = $password2 = 'x';
    }

    $oForm->setEditMode();

    if(!$oForm->validateForm($_POST)) {
	// Manejo de Error
	$smarty->assign("mb_title", $arrLang["Validation Error"]);
	$arrErrores=$oForm->arrErroresValidacion;
	$strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
	foreach($arrErrores as $k=>$v) {
	    $strErrorMsg .= "$k, ";
	}
	$strErrorMsg .= "";
	$smarty->assign("mb_message", $strErrorMsg);
	$content = viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    } else {
	if($noCambioPass) $password1 = $password2 = '';
	if($password1 != $password2) {
	    // Error claves
	    $smarty->assign("mb_title", $arrLang["Error"]);
	    $smarty->assign("mb_message", $arrLang["The passwords don't match"]);
	    $content = viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	}else{
	    $pDB->beginTransaction();
	    if(getParameter("save"))
		$bExito = create_email_account($pDB,$domain_name,$error);
	    else
		$bExito = edit_email_account($pDB,$error);
	    if (!$bExito || ($bExito && !empty($error))){
		$smarty->assign("mb_message", _tr("Error applying changes").". ".$error);
		$pDB->rollBack();
		$content = viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    }
	    else{
		$pDB->commit();
		$smarty->assign("mb_message", _tr("Changes Applied successfully"));
		$content = viewFormAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    }
	}
	/////////////////////////////////
    }

    $smarty->assign("id_domain", $id_domain);
    $smarty->assign("username", $userName);
    $smarty->assign("old_quota", getParameter("quota"));
    $smarty->assign("account_name_label", $arrLang['Account Name']);

    return $content;
}


function deleteAccount($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmail   = new paloEmail($pDB);
    $username = getParameter("username");
    $virtual  = FALSE;
    $pDB->beginTransaction();
    $errMsg = "";
    $bExito = $pEmail->eliminar_cuenta($pDB,$username,$errMsg, $virtual);
    if (!$bExito){
	$pDB->rollBack();
	$smarty->assign("mb_message", _tr("Error appliying changes").". ".$errMsg);
	$content = viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }
    else{
	$pDB->commit();
	$smarty->assign("mb_message", _tr("Account deleted successfully"));
	$content = viewFormAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }
    return $content;
}


//funciones separadas

function create_email_account($pDB,$domain_name,&$errMsg)
{
    $bReturn=FALSE;
    global $arrLang;
    $virtual = FALSE;
    $pEmail = new paloEmail($pDB);
    //creo la cuenta
    // -- valido que el usuario no exista
    // -- si no existe creo el usuario en el sistema con sasldbpasswd2
    // -- inserto el usuario en la base de datos
    // -- si hay error al insertarlo en la bd lo elimino del sistema
    // -- creo el mailbox para la cuenta (si hay error deshacer lo realizado)
    $username = "";
    $configPostfix2 = isPostfixToElastix2();// in misc.lib.php

    if($configPostfix2)
        $username=$_POST['address'].'@'.$domain_name;
    else
        $username=$_POST['address'].'.'.$domain_name;

    $arrAccount=$pEmail->getAccount($username);

    if (is_array($arrAccount) && count($arrAccount)>0 ){
       //YA EXISTE ESA CUENTA
        $errMsg=$arrLang["The e-mail address already exists"].": $_POST[address]@$domain_name";
        return FALSE;
    }

    $email=$_POST['address'].'@'.$domain_name;
    //crear la cuenta de usuario en el sistema

    $bExito = $pEmail->crear_usuario_correo_sistema($email,$username,$_POST['password1'],$errMsg, $virtual);
    if(!$bExito) return FALSE;
    //inserto la cuenta de usuario en la bd
    $bExito = $pEmail->createAccount($_POST['id_domain'],$username,$_POST['password1'],$_POST['quota']);
    if ($bExito){
        //crear el mailbox para la nueva cuenta
        $bReturn = crear_mailbox_usuario($pDB,$email,$username,$errMsg);
    }else{
        //tengo que borrar el usuario creado en el sistema
        $bReturn = $pEmail->eliminar_usuario_correo_sistema($username,$email,$errMsg);
        $errMsg = (isset($arrLang[$pEmail->errMsg]))?$arrLang[$pEmail->errMsg]:$pEmail->errMsg;
        if($bReturn && $virtual){
            $bReturn = $pEmail->eliminar_virtual_sistema($email,$errMsg);
            $errMsg = (isset($arrLang[$pEmail->errMsg]))?$arrLang[$pEmail->errMsg]:$pEmail->errMsg;
        }
    }
    return $bReturn;
}

function crear_mailbox_usuario($db,$email,$username,&$error_msg){
    global $CYRUS;
    global $arrLang;
    $pEmail = new paloEmail($db);
    $cyr_conn = new cyradm;
    $error=$cyr_conn->imap_login();
    $virtual = FALSE;
    if ($error===FALSE){
        $error_msg.="IMAP login error: $error <br>";
    }
    else{
        $seperator  = '/';
        $bValido=$cyr_conn->createmb("user" . $seperator . $username);
        if(!$bValido)
            $error_msg.="Error creating user:".$cyr_conn->getMessage()."<br>";
        else{
            $bValido=$cyr_conn->setacl("user" . $seperator . $username, $CYRUS['ADMIN'], "lrswipcda");
            if(!$bValido)
                $error_msg.="error:".$cyr_conn->getMessage()."<br>";
            else{
                $bValido = $cyr_conn->setmbquota("user" . $seperator . $username, $_POST['quota']);
                if(!$bValido)
                    $error_msg.="error".$cyr_conn->getMessage()."<br>";
            }
        }
    }
    if($error_msg!=""){
        //Si hay error se trata de borrar la fila ingresada
        $bValido=$pEmail->deleteAccount($username);
        if(!$bValido){
            $error_msg=(isset($arrLang[$pEmail->errMsg]))?$arrLang[$pEmail->errMsg]:$pEmail->errMsg;
            return FALSE;
        }
        //borrar la cuenta del sistema
        $bReturn = $pEmail->eliminar_usuario_correo_sistema($username,$email,$error_msg);
        if($bReturn){
	    if($virtual){
		$bReturn = $pEmail->eliminar_virtual_sistema($email,$errMsg);
		$errMsg = (isset($arrLang[$pEmail->errMsg]))?$arrLang[$pEmail->errMsg]:$pEmail->errMsg;
		if(!$bReturn)
		    return FALSE;
	    }
        }else
            return FALSE;
    }
    else{
        $bValido=$pEmail->createAliasAccount($username,$email);
        if(!$bValido){
            $error_msg=$arrLang["The account was created but could not add record for the e-mail in alias table"];
            return FALSE;
        }
    }
    return TRUE;
}


function obtener_quota_usuario($username)
{
    global $CYRUS;
    global $arrLang;
    $cyr_conn = new cyradm;
    $cyr_conn->imap_login();

    $quota = $cyr_conn->getquota("user/" . $username);
    $tamano_usado=$arrLang["Could not query used disc space"];
    if(is_array($quota) && count($quota)>0){
        if ($quota['used'] != "NOT-SET"){
            $q_used  = $quota['used'];
            $q_total = $quota['qmax'];
            if (! $q_total == 0){
                $q_percent = number_format((100*$q_used/$q_total),2);
                $tamano_usado="$quota[used] Kb / $quota[qmax] Kb ($q_percent%)";
            } 
            else {
                $tamano_usado=$arrLang["Could not obtain used disc space"];
            }
        } else {
            $tamano_usado=$arrLang["Size is not set"];
        }
    }
    return $tamano_usado;
}


function edit_email_account($pDB,$error)
{
    global $CYRUS;
    global $arrLang;
    $bExito=TRUE;
    $error_pwd='';
    $virtual = FALSE;
    $pEmail = new paloEmail($pDB);
    if (isset($_POST['password1']) && trim($_POST['password1'])!="")
    {
        $username=$_POST['username'];
        $bool = $pEmail->crear_usuario_correo_sistema($username,$username,$_POST['password1'],$error,$virtual); //False al final para indicar que no cree virtual
        if(!$bool){
          $error_pwd=$arrLang["Password could not be changed"];
          $bExito=FALSE;
        }
    }
    if($_POST['old_quota']!=$_POST['quota']){
        $cyr_conn = new cyradm;
        $cyr_conn->imap_login();
        $bContinuar=$cyr_conn->setmbquota("user" . "/".$_POST['username'], $_POST['quota']);
        if ($bContinuar){
           //actualizar en la base de datos
            $bExito=$pEmail->updateAccount($_POST['username'], $_POST['quota']);
            if (!$bExito){
                $error=(isset($arrLang[$pEmail->errMsg]))?$arrLang[$pEmail->errMsg]:$pEmail->errMsg;
                $bExito=FALSE;
            }
        }else{
            $error=$cyr_conn->getMessage();
            $bExito=FALSE;
        }
    }
    if ($bExito && !empty($error_pwd))
        $error=$error_pwd;

    return $bExito;
}

function createFieldFormNewAccount($arrLang)
{
    $arrFields = array(
                             "address"       => array("LABEL"                   => $arrLang["Email Address"],
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^([a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*)$"),
                             "quota"   => array("LABEL"                  => $arrLang["Quota (Kb)"],
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "password1"   => array("LABEL"                  => $arrLang["Password"],
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "PASSWORD",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "password2"   => array("LABEL"                  => $arrLang["Retype password"],
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "PASSWORD",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                         );

    return $arrFields;
}

function createFieldFormAccount($arrLang, $arrDominios)
{
    $arrFields = array(
		"domain"  => array("LABEL"                  => $arrLang["Domain"],
				      "REQUIRED"               => "no",
				      "INPUT_TYPE"             => "SELECT",
				      "INPUT_EXTRA_PARAM"      => $arrDominios,
				      "VALIDATION_TYPE"        => "integer",
				      "VALIDATION_EXTRA_PARAM" => ""),
		);
    return $arrFields;
}
function getAction()
{
    if(getParameter("submit_create_account")) //Get parameter by POST (submit)
        return "new";
    if(getParameter("save"))
        return "save";
    else if(getParameter("delete"))
        return "delete";
    else if(getParameter("show"))
	return "report";
    else if(getParameter("edit"))
	return "edit";
    else if(getParameter("apply_changes"))
        return "apply_changes";
    else if(getParameter("cancel"))
        return "report";
    else if(getParameter("action")=="view") //Get parameter by GET (command pattern, links)
        return "view";
    else
        return "report";
}

?>
