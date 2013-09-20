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
	case "export":
	    $content = exportAccounts($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
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
    


   // $oGrid->pagingShow(true);
    $url = array("menu" => $module_name);
    $oGrid->setURL($url);
    $oGrid->setTitle(_tr("Email Account List"));

    //$total = 0;
    //$limit  = 20;
    //$oGrid->setLimit($limit);
    // $oGrid->setTotal($total);
    //$offset = $oGrid->calculateOffset();

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
    $smarty->assign("LINK", "?menu=$module_name&action=export&domain=$id_domain&rawmode=yes");
    $smarty->assign("EXPORT", _tr("Export Accounts"));

    $oGrid->setData($arrData);
    $arrColumns = array(_tr("Account Name"),_tr("Used Space"),);
    $oGrid->setColumns($arrColumns);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/accounts_filter.tpl", "", $_POST);
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

    if(getParameter("option_create_account") && getParameter("option_create_account")=="by_file"){
	$smarty->assign("check_file", "checked");
	$smarty->assign("DISPLAY_SAVE_ACCOUNT", "style=display:none;");
    }
    else{
	$smarty->assign("check_record", "checked");
	$smarty->assign("DISPLAY_FILE_UPLOAD", "style=display:none;");
    }
    if(getParameter("action") == "view"){
	$oForm->setViewMode(); // Esto es para activar el modo "preview"
    }elseif(getParameter("submit_create_account") || getParameter("save")){
	//nothing
	$typeForm = _tr("Create Account");
	//obtener el nombre del dominio
	$domain_name = isset($domain_name)?$domain_name:$id_domain;
	$id_domain = $domain_name;
	$arrDomain= $pEmail->getDomains($domain_name);
	if(!is_array($arrDomain) || count($arrDomain)==0 || $domain_name==0){
	    $smarty->assign("mb_title", $arrLang["Error"]);
	    $smarty->assign("mb_message", _tr("You must select a domain to create an account"));
	    $content = viewFormAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    return $content;
	}
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
    $smarty->assign("account", $arrLang["Account"]);
    $smarty->assign("file_upload", $arrLang["File Upload"]);
    $smarty->assign("file_Label", $arrLang["File Upload"]);
    $smarty->assign("INFO", $arrLang["The format of the file must be csv (file.csv), like the following"].":<br /><br /><b>".$arrLang["Username1,Password1,Quota1(Kb)"]."</b><br /><b>".$arrLang["Username2,Password2,Quota2(Kb)"]."</b><br /><br />".$arrLang["The value of Quota(Kb) must be a number, like 1000 or 2000, etc"]);
    $content = $oForm->fetchForm("$local_templates_dir/form_account.tpl", $typeForm, $arrTmp); // hay que pasar el arreglo
    return $content;
}


function saveAccount($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $domain = getParameter("domain_name");
    $pEmail = new paloEmail($pDB);
    if(getParameter("option_create_account") && getParameter("option_create_account")=="by_file"){
	if(isset($_FILES["file_accounts"])){
	    if($_FILES["file_accounts"]["name"] != ""){
		$smarty->assign("file_accounts_name", $_FILES['file_accounts']['name']);
		if (!preg_match("/^(\w|-|\.|\(|\)|\s)+\.(csv)$/",$_FILES['file_accounts']['name'])){
		    $smarty->assign("mb_title", $arrLang['ERROR'].":");
		    $smarty->assign("mb_message", $arrLang["Possible file upload attack. The file must end in .csv"]);
		    return viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
		}
		if(!move_uploaded_file($_FILES['file_accounts']['tmp_name'], "/tmp/$_FILES[file_accounts][name]")){
		    $smarty->assign("mb_title", $arrLang['ERROR'].":");
		    $smarty->assign("mb_message", $arrLang["Possible file upload attack. The file must end in .csv"]);
		    return viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
		}
		$handler = fopen("/tmp/$_FILES[file_accounts][name]","r");
		$arrErrorAccounts = array();
		$arrAccounts = array();
		if($handler !== false){
		    while(($data = fgetcsv($handler,10000)) !== false){
			if(count($data) >= 3){
			    $_POST["address"] = $data[0];
			    $_POST["password1"] = $data[1];
			    $_POST["password2"] = $data[1];
			    $_POST["quota"] = (int)$data[2];
			    $quotaIsTooGreat = false;
			    if($_POST["quota"] > 5242880){
				$quotaIsTooGreat = true;
				$_POST["quota"] = 5242880;
			    }
			    $configPostfix2 = isPostfixToElastix2();// in misc.lib.php
			    if($configPostfix2)
				$username=$_POST['address'].'@'.$domain;
			    else
				$username=$_POST['address'].'.'.$domain;
			    $arrAccount=$pEmail->getAccount($username);
			    if (is_array($arrAccount) && count($arrAccount)>0 )
				$arrErrorAccounts[] = $data[0]."@$domain : ".$arrLang["The e-mail address already exists"];
			    else{
				if(saveOneAccount($smarty, $pDB, $arrLang, true)){
				    if($quotaIsTooGreat)
					$arrAccounts[] = $data[0]."@$domain : ".$arrLang["The quota was reduced to the maximum of 5242880KB, if you want to more than this, edit this account"];
				    else
					$arrAccounts[] = $data[0]."@$domain";
				}
				else
				    $arrErrorAccounts[] = $data[0]."@$domain : ".$arrLang["Error saving the account"];
			    }
			}
			else
			    $arrErrorAccounts[] = $data[0]."@$domain : ".$arrLang["At least three parameters are needed"];
		    }
		}
		else{
		    $smarty->assign("mb_title", $arrLang['ERROR'].":");
		    $smarty->assign("mb_message", $arrLang["The file could not be opened"]);
		    return viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
		}
		$message = "";
		if(count($arrAccounts)>0){
		    $message .= "<b>".$arrLang["The following accounts were created"].":</b><br />";
		    foreach($arrAccounts as $account)
			$message .= htmlentities($account)."<br />";
		}
		if(count($arrErrorAccounts)>0){
		    $message .= "<b>".$arrLang["The following accounts could not be created"].":</b><br />";
		    foreach($arrErrorAccounts as $errAccounts)
			$message .= $errAccounts."<br />";
		}
		$smarty->assign("mb_message",$message);
		unlink("/tmp/$_FILES[file_accounts][name]");
		return viewFormAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    }
	    else{
		$smarty->assign("mb_title", $arrLang['ERROR'].":");
		$smarty->assign("mb_message", $arrLang["Error reading the file"]);
		return viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	    }
	}
	else{
	    $smarty->assign("mb_title", $arrLang['ERROR'].":");
	    $smarty->assign("mb_message", $arrLang["Error reading the file"]);
	    return viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	}
    }
    else{
	if(saveOneAccount($smarty, $pDB, $arrLang, false))
	    return viewFormAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
	else
	    return viewDetailAccount($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    }
}

function saveOneAccount($smarty, &$pDB, $arrLang, $isFromFile)
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
    $address	 = getParameter("address");
    $quota	 = getParameter("quota");
    $error = "";
    $bExito = FALSE;

    if (empty($password1) && empty($password2)){
	$noCambioPass = TRUE;
	$password1 = $password2 = 'x';
    }

    $oForm->setEditMode();

    if(!$oForm->validateForm($_POST)) {
	// Manejo de Error
	if(!$isFromFile){
	    $arrErrores=$oForm->arrErroresValidacion;
	    $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
	    foreach($arrErrores as $k=>$v) {
		$strErrorMsg .= "$k, ";
	    }
	    $strErrorMsg .= "";
	    $smarty->assign("mb_title", $arrLang["Validation Error"]);
	    $smarty->assign("mb_message", $strErrorMsg);
	}
	$content = false;
    }elseif(!preg_match("/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*$/",$address) && isset($address) && $address!=""){ 
	if(!$isFromFile){
	    $smarty->assign("mb_title", $arrLang["Validation Error"]);
	    $smarty->assign("mb_message", $arrLang["Wrong format for username"]);
	}
	$content = false;
    }elseif($quota <= 0){
	if(!$isFromFile){
	    $smarty->assign("mb_title", $arrLang["Validation Error"]);
	    $smarty->assign("mb_message", $arrLang["Quota must be greater than 0"]);
	}
	$content = false;
    }
    else{
	if($noCambioPass) $password1 = $password2 = '';
	if($password1 != $password2) {
	    // Error claves
	    if(!$isFromFile){
		$smarty->assign("mb_title", $arrLang["Error"]);
		$smarty->assign("mb_message", $arrLang["The passwords don't match"]);
	    }
	    $content = false;
	}else{
	    $pDB->beginTransaction();
	    if(getParameter("save"))
		$bExito = create_email_account($pDB,$domain_name,$error);
	    else
		$bExito = edit_email_account($pDB,$error);
	    if (!$bExito || ($bExito && !empty($error))){
		if(!$isFromFile)
		    $smarty->assign("mb_message", _tr("Error applying changes").". ".$error);
		$pDB->rollBack();
		$configPostfix2 = isPostfixToElastix2();// in misc.lib.php
		if($configPostfix2)
		    $username=$_POST['address'].'@'.$domain_name;
		else
		    $username=$_POST['address'].'.'.$domain_name;
		$pEmail->eliminar_cuenta($pDB,$username,"",false);
		$content = false;
	    }
	    else{
		$pDB->commit();
		if(!$isFromFile)
		    $smarty->assign("mb_message", _tr("Changes Applied successfully"));
		$content = true;
	    }
	}
	/////////////////////////////////
    }

    if(!$isFromFile){
	$smarty->assign("id_domain", $id_domain);
	$smarty->assign("username", $userName);
	$smarty->assign("old_quota", getParameter("quota"));
	$smarty->assign("account_name_label", $arrLang['Account Name']);
    }
    return $content;
}

function exportAccounts($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmail = new paloEmail($pDB);
    $id_domain = getParameter("domain");
    $arrAccounts = $pEmail->getAccountsByDomain($id_domain);
    $domainName = $pEmail->getDomains($id_domain);
    if(isset($domainName[0][1]))
	$domainName = $domainName[0][1];
    else
	$domainName = "no_domain";
    $text = "";
    if(is_array($arrAccounts) && count($arrAccounts)>0){
	foreach($arrAccounts as $account){
	    if($text != "")
		$text .= "\n";
	    $user = explode("@",$account[0]);
	    $text .= $user[0].",".$account[1].",".$account[3];
	}
    }
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: csv file");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment; filename=$domainName"."_accounts.csv");
    header("Content-Transfer-Encoding: binary");
    header("Content-length: ".strlen($text));
    echo $text;
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
                $tamano_usado="$quota[used] KB / $quota[qmax] KB ($q_percent%)";
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
                                                    "VALIDATION_EXTRA_PARAM" => "^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*$"),
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
				      "VALIDATION_EXTRA_PARAM" => "",
				      "ONCHANGE"	       => "javascript:submit();"),
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
    else if(getParameter("edit"))
	return "edit";
    else if(getParameter("apply_changes"))
        return "apply_changes";
    else if(getParameter("cancel"))
        return "report";
    else if(getParameter("action")=="view") //Get parameter by GET (command pattern, links)
        return "view";
    else if(getParameter("action")=="export")
	return "export";
    else
        return "report";
}

?>