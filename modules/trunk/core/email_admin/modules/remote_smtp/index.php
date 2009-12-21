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
  $Id: index.php,v 1.1 2009-08-07 01:08:56 Oscar Navarrete onavarrete@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    $smarty->assign("NewMaincf", 1);
    $smarty->assign("Modified", 0);

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoEmailRelay.class.php";

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
        case "edit":
            $content = viewFormEmailRelay($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "commit":
            $content = saveNewEmailRelay($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        default: // view_form
            $content = viewFormEmailRelay($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function viewFormEmailRelay($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmailRelay = new paloSantoEmailRelay($pDB);
    $arrFormEmailRelay = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormEmailRelay);
    
    $fh = $pEmailRelay->readFileMainCF();//read the file etc/postfix/main.cf
    $result = $pEmailRelay->saveFileMainCf($fh, $pDB);
    $_DATA  = $_POST;
    $action = getParameter("action");

    $arr_MainCf = $pEmailRelay->getMainConfigByAll2();
    if(is_array($arr_MainCf) && count($arr_MainCf)>0){
        foreach($arr_MainCf as $key => $value){
            if($value['name']=="myhostname" || $value['name']=="#myhostname"){
                $hostname = $value['value'];
                $arrData['host_name'] = $value['value'];
            }
            if($value['name']=="relayhost" || $value['name']=="#relayhost"){
//                 $host= split('[.]',$value['value'],3);
//                 $port= split('[:]',$value['value']);
                $port= split('[:]',$value['value']);
                $arrData['smtp_server'] = isset($_POST['smtp_server'])?$_POST['smtp_server']:$port[0];
                $smarty->assign("SMTP_server", 1);
                $smarty->assign("SMTP_SERVER", $value['value']);
                if(!empty($port[1])){
                    $arrData['port'] = isset($_POST['port'])?$_POST['port']:$port[1];
                }else{
                    $arrData['port'] = isset($_POST['port'])?$_POST['port']:"none";
                }
                $smarty->assign("Port", 1);
                $smarty->assign("PORT", $value['value']);
            }

//             if($value['name']=="smtp_sasl_auth_enable"){
//                 $smtp_sasl_auth_enable = $value['value'];
//                 $smarty->assign("Smtp_sasl_auth_enable", 1);
//                 $smarty->assign("SMTP_SASL_AUTH_ENABLE", $value['value']);
//             }
//             if($value['name']=="smtp_sasl_password_maps"){
//                 $smtp_sasl_password_maps = $value['value'];
//                 $smarty->assign("Smtp_sasl_password_maps", 1);
//                 $smarty->assign("SMTP_SASL_PASSWORD_MAPS", $value['value']);
//             }
//             if($value['name']=="smtp_sasl_security_options"){
//                 $smtp_sasl_security_options = $value['value'];
//                 $smarty->assign("Smtp_sasl_security_options", 1);
//                 $smarty->assign("SMTP_SASL_SECURITY_OPTIONS", $value['value']);
//             }
//             if($value['name']=="smtpd_tls_auth_only"){               
//                 $smtpd_tls_auth_only = $value['value'];
//                 $smarty->assign("Smtpd_tls_auth_only", 1);
//                 $smarty->assign("SMTPD_TLS_AUTH_ONLY", $value['value']);
//             }
//             if($value['name']=="smtp_use_tls"){                
//                 $smtp_use_tls = $value['value'];
//                 $smarty->assign("Smtp_use_tls", 1);
//                 $smarty->assign("SMTP_USE_TLS", $value['value']);
//             }
//             if($value['name']=="smtpd_use_tls"){
//                 $smtpd_use_tls = $value['value'];
//                 $smarty->assign("Smtpd_use_tls", 1);
//                 $smarty->assign("SMTPD_USE_TLS", $value['value']);
//             }
//             if($value['name']=="smtp_tls_note_starttls_offer"){
//                 $smtp_tls_note_starttls_offer = $value['value'];
//                 $smarty->assign("Smtp_tls_note_starttls_offer", 1);
//                 $smarty->assign("SMTPD_TLS_NOTE_STARTTLS_OFFER", $value['value']);
//             }
//             if($value['name']=="smtpd_tls_key_file"){
//                 $smtpd_tls_key_file = $value['value'];
//                 $smarty->assign("Smtpd_tls_key_file", 1);
//                 $smarty->assign("SMTPD_TLS_KEY_FILE", $value['value']);
//             }
//             if($value['name']=="smtpd_tls_cert_file"){                
//                 $smtpd_tls_cert_file = $value['value'];
//                 $smarty->assign("Smtpd_tls_cert_file", 1);
//                 $smarty->assign("SMTPD_TLS_CERT_FILE", $value['value']);
//             }
//             if($value['name']=="smtp_tls_CAfile"){
//                 $smtp_tls_CAfile = $value['value'];
//                 $smarty->assign("Smtp_tls_CAfile", 1);
//                 $smarty->assign("SMTPD_TLS_CAfile", $value['value']);
//             }
//             if($value['name']=="smtpd_tls_loglevel"){
//                 $smtpd_tls_loglevel = $value['value'];
//                 $smarty->assign("Smtpd_tls_loglevel", 1);
//                 $smarty->assign("SMTPD_TLS_LOGLEVEL", $value['value']);
//             }
//             if($value['name']=="smtpd_tls_received_header"){                
//                 $smtpd_tls_received_header = $value['value'];
//                 $smarty->assign("Smtpd_tls_received_header", 1);
//                 $smarty->assign("SMTPD_TLS_RECEIVED_HEADER", $value['value']);
//             }
//             if($value['name']=="smtpd_tls_session_cache_timeout"){
//                 $smtpd_tls_session_cache_timeout = $value['value'];
//                 $smarty->assign("Smtpd_tls_session_cache_timeout", 1);
//                 $smarty->assign("SMTPD_TLS_SESSION_CACHE_TIMEOUT", $value['value']);
//             }
//             if($value['name']=="tls_random_source"){
//                 $tls_random_source = $value['value'];
//                 $smarty->assign("Tls_random_source", 1);
//                 $smarty->assign("TLS_RANDOM_SOURCE", $value['value']);
//             }
//             if($value['name']=="tls_daemon_random_source"){
//                 $tls_daemon_random_source = $value['value'];
//                 $smarty->assign("Tls_daemon_random_source", 1);
//                 $smarty->assign("TLS_DAEMON_RANDON_SOURCE", $value['value']);
//             }
//Sin Gmail
//             if($value['name']=="broken_sasl_auth_clients"){
//                 $broken_sasl_auth_clients = $value['value'];
//                 $smarty->assign("Broken_sasl_auth_clients", 1);
//                 $smarty->assign("BROKEN_SASL_AUTH_CLIENTS", $value['value']);
//             }
//             if($value['name']=="smtpd_sasl_auth_enable"){
//                 $smtpd_sasl_auth_enable = $value['value'];
//                 $smarty->assign("Smtpd_sasl_auth_enable", 1);
//                 $smarty->assign("SMTPD_SASL_AUTH_ENABLE", $value['value']);
//             }
//             if($value['name']=="smtp_always_send_ehlo"){
//                 $smtp_always_send_ehlo = $value['value'];
//                 $smarty->assign("Smtp_always_send_ehlo", 1);
//                 $smarty->assign("SMTPD_ALWAYS_SEND_EHLO", $value['value']);
//             }
        }
        //si existe mas de cuatro registros entonces el archivo main.cf ya ha sido configurado
        //caso contrario es nuevo sin modificacion
        if( count($arr_MainCf) > 4){
            $smarty->assign("Modified", 1);
            $fhssl = $pEmailRelay->readFileSSL();
            
        }else{
            $pEmailRelay->init();
            $smarty->assign("NewMaincf", 0);
            $smarty->assign("Port", 1);
        }
    }
    
//     $id=1;
//     $emailAuthenticateData=$pEmailRelay->getEmailRelayAuthenticateById($id);
//     if(!empty($emailAuthenticateData)){
//         $arrData['smtpd_password'] = isset($_POST['smtpd_password'])?$_POST['smtpd_password']:$emailAuthenticateData['smtpd_password'];
//         $arrData['smtpd_country_name'] = isset($_POST['smtpd_country_name'])?$_POST['smtpd_country_name']:$emailAuthenticateData['smtpd_country'];
//         $arrData['smtpd_province_name'] = isset($_POST['smtpd_province_name'])?$_POST['smtpd_province_name']:$emailAuthenticateData['smtpd_province'];
//         $arrData['smtpd_locality_name'] = isset($_POST['smtpd_locality_name'])?$_POST['smtpd_locality_name']:$emailAuthenticateData['smtpd_locality'];
//         $arrData['smtpd_organization_name'] = isset($_POST['smtpd_organization_name'])?$_POST['smtpd_organization_name']:$emailAuthenticateData['smtpd_organization'];
//         $arrData['smtpd_organizational_unit_name'] = isset($_POST['smtpd_organizational_unit_name'])?$_POST['smtpd_organizational_unit_name']:$emailAuthenticateData['smtpd_organizational_unit'];
//         $arrData['smtpd_common_name'] = isset($_POST['smtpd_common_name'])?$_POST['smtpd_common_name']:$emailAuthenticateData['smtpd_common'];
//     }else{
//         $arrData['smtpd_password'] = isset($_POST['smtpd_password'])?getParameter('smtpd_password'):"none";
//         $arrData['smtpd_country_name'] = isset($_POST['smtpd_country_name'])?getParameter('smtpd_country_name'):"none";
//         $arrData['smtpd_province_name'] = isset($_POST['smtpd_province_name'])?getParameter('smtpd_province_name'):"none";
//         $arrData['smtpd_locality_name'] = isset($_POST['smtpd_locality_name'])?getParameter('smtpd_locality_name'):"none";
//         $arrData['smtpd_organization_name'] = isset($_POST['smtp_organization_name'])?getParameter('smtpd_organization_name'):"none";
//         $arrData['smtpd_organizational_unit_name'] = isset($_POST['smtpd_organizational_unit_name'])?getParameter('smtpd_organizational_unit_name'):"none";
//         $arrData['smtpd_common_name'] = isset($_POST['smtpd_common_name'])?getParameter('smtpd_common_name'):"none";
//     }

    if(isset($_POST["edit"])){
        $oForm->setEditMode();
        $smarty->assign("Commit", 1);
        $smarty->assign("SAVE",$arrLang["Save"]);

        $smarty->assign("NewMaincf", 0);
//         $smarty->assign("Smtp_sasl_auth_enable", 0);
//         $smarty->assign("Smtp_sasl_password_maps", 0);
//         $smarty->assign("Smtp_sasl_security_options", 0);
//         $smarty->assign("Smtpd_tls_auth_only", 0);
//         $smarty->assign("Smtp_use_tls", 0);
//         $smarty->assign("Smtpd_use_tls", 0);
//         $smarty->assign("Smtp_tls_note_starttls_offer", 0);
//         $smarty->assign("Smtpd_tls_key_file", 0);
//         $smarty->assign("Smtpd_tls_cert_file", 0);
//         $smarty->assign("Smtp_tls_CAfile", 0);
//         $smarty->assign("Smtpd_tls_loglevel", 0);
//         $smarty->assign("Smtpd_tls_received_header", 0);
//         $smarty->assign("Smtpd_tls_session_cache_timeout", 0);
//         $smarty->assign("Tls_random_source", 0);
//         $smarty->assign("Tls_daemon_random_source", 0);
//Sin Gmail
//         $smarty->assign("Broken_sasl_auth_clients", 0);
//         $smarty->assign("Smtpd_sasl_auth_enable", 0);
//         $smarty->assign("Smtp_always_send_ehlo", 0);
        if(!empty($fhssl)){
            $arr_account = $pEmailRelay->getDataSsl($fhssl); 
            $arrData['user'] = $arr_account['user'];
            $arrData['password'] = $arr_account['password'];
        }
    }else{
        $oForm->setViewMode();
        $smarty->assign("Edit", 1); 
        if(!empty($smtp_sasl_auth_enable)){
//             $arrData['smtp_sasl_auth_enable'] = $smtp_sasl_auth_enable;
//             $arrData['smtp_sasl_password_maps'] = $smtp_sasl_password_maps;
//             $arrData['smtp_sasl_security_options'] = $smtp_sasl_security_options;
//             $arrData['smtpd_tls_auth_only'] = $smtpd_tls_auth_only;
//             $arrData['smtp_use_tls'] = $smtp_use_tls;
//             $arrData['smtp_tls_note_starttls_offer'] = $smtp_tls_note_starttls_offer;
//             $arrData['smtpd_tls_key_file'] = $smtpd_tls_key_file;
//             $arrData['smtpd_tls_cert_file'] = $smtpd_tls_cert_file;
//             $arrData['smtp_tls_CAfile'] = $smtp_tls_CAfile;
//             $arrData['smtpd_tls_loglevel'] = $smtpd_tls_loglevel;
//             $arrData['smtpd_tls_received_header'] = $smtpd_tls_received_header;
//             $arrData['smtpd_tls_session_cache_timeout'] = $smtpd_tls_session_cache_timeout;
//             $arrData['tls_random_source'] = $tls_random_source;
//             $arrData['tls_daemon_random_source'] = $tls_daemon_random_source;
        }
//         if(!empty($broken_sasl_auth_clients)){
//             $arrData['broken_sasl_auth_clients'] = $broken_sasl_auth_clients;
//             $arrData['smtpd_sasl_auth_enable'] = $smtpd_sasl_auth_enable;
//             $arrData['smtp_always_send_ehlo'] = $smtp_always_send_ehlo;
//         }
        if(!empty($fhssl)){
            $arr_account = $pEmailRelay->getDataSsl($fhssl); 
            $arrData['user'] = $arr_account['user'];
            $arrData['password'] = "xxxxx";
        }else{
            $arrData['user'] = "none";
            $arrData['password'] = "1234";
        }

    }

    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Remote SMTP Delivery"], $arrData);
    $content = "<form method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}


function saveNewEmailRelay($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pEmailRelay = new paloSantoEmailRelay($pDB);
    $arrFormEmailRelay = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormEmailRelay);

    if(!$oForm->validateForm($_POST)){
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }
        $smarty->assign("mb_message", $strErrorMsg);

        $smarty->assign("SAVE", $arrLang["Save"]);
        $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
        $smarty->assign("IMG", "images/list.png");
        $smarty->assign("CANCEL", $arrLang["Cancel"]);

        $smarty->assign("Commit", 1);

        $arr_MainCf = $pEmailRelay->getMainConfigByAll2();
        if(count($arr_MainCf) > 4) $smarty->assign("Modified", 1);

        $smarty->assign("SMTP_server", 1);
        $smarty->assign("Port", 1);
//         $smarty->assign("Smtp_sasl_auth_enable", 1);
//         $smarty->assign("Smtp_sasl_password_maps", 1);
//         $smarty->assign("Smtp_sasl_security_options", 1);
//         $smarty->assign("Smtpd_tls_auth_only", 1);
//         $smarty->assign("Smtpd_use_tls", 1);
//         $smarty->assign("Smtp_tls_note_starttls_offer", 1);
//         $smarty->assign("Smtpd_tls_key_file", 1);
//         $smarty->assign("Smtpd_tls_cert_file", 1);
//         $smarty->assign("Smtp_tls_CAfile", 1);
//         $smarty->assign("Smtpd_tls_loglevel", 1);
//         $smarty->assign("Smtpd_tls_received_header", 1);
//         $smarty->assign("Smtpd_tls_session_cache_timeout", 1);
//         $smarty->assign("Tls_random_source", 1);
//         $smarty->assign("Tls_daemon_random_source", 1);
//sin Gmail
//         $smarty->assign("Broken_sasl_auth_clients", 1);
//         $smarty->assign("Smtpd_sasl_auth_enable", 1);
//         $smarty->assign("Smtp_always_send_ehlo", 1);

        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Remote SMTP Delivery"], $_POST);
        return $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    }
    else{
        $data_step1 = array();
        $data_step2 = array();
        
        $host_name = $_POST['host_name'];
        $smtp_server = $_POST['smtp_server'];//$host
        $port = $_POST['port'];
        $user = $_POST['user'];
        $password = $_POST['password'];

        $result = $pEmailRelay->getMainConfigByAll();

/*        if(!empty($_POST['smtp_sasl_auth_enable'])) $data_step1['smtp_sasl_auth_enable'] = $_POST['smtp_sasl_auth_enable'];
        else*/ $data_step1['smtp_sasl_auth_enable'] = "yes";

/*        if(!empty($_POST['smtp_sasl_password_maps'])) $data_step1['smtp_sasl_password_maps'] = $_POST['smtp_sasl_password_maps'];
        else*/ $data_step1['smtp_sasl_password_maps'] = "hash:/etc/postfix/sasl/passwd";

/*        if(!empty($_POST['smtp_sasl_security_options'])) $data_step1['smtp_sasl_security_options'] = $_POST['smtp_sasl_security_options'];
        else*/ $data_step1['smtp_sasl_security_options'] = "";

        if(getParameter("control")=='1'){
            $pEmailRelay->execConfigPosfix_1Mod($smtp_server, $port, $user, $password);
        }else{
            $pEmailRelay->execConfigPosfix_1($smtp_server, $port, $user, $password);  
        }
        
        $pEmailRelay->replaceFileMainCF($host_name, $smtp_server, $port, $data_step1, $result);
        
        $data = array();

//         $data['smtpd_password'] = $pDB->DBCAMPO($_POST['smtpd_password']);
//         $data['smtpd_country'] = $pDB->DBCAMPO($_POST['smtpd_country_name']);
//         $data['smtpd_province'] = $pDB->DBCAMPO($_POST['smtpd_province_name']);
//         $data['smtpd_locality'] = $pDB->DBCAMPO($_POST['smtpd_locality_name']);
//         $data['smtpd_organization'] = $pDB->DBCAMPO($_POST['smtpd_organization_name']);
//         $data['smtpd_organizational_unit'] = $pDB->DBCAMPO($_POST['smtpd_organizational_unit_name']);
//         $data['smtpd_common'] = $pDB->DBCAMPO($_POST['smtpd_common_name']);

//         if(!empty($_POST['smtpd_tls_auth_only'])) $data_step2['smtpd_tls_auth_only'] = $_POST['smtpd_tls_auth_only'];
//         else $data_step2['smtpd_tls_auth_only'] = "no";
//         
//         if(!empty($_POST['smtp_use_tls'])) $data_step2['smtp_use_tls'] = $_POST['smtp_use_tls'];
//         else $data_step2['smtp_use_tls'] = "yes";
//         
//         if(!empty($_POST['smtpd_use_tls'])) $data_step2['smtpd_use_tls'] = $_POST['smtpd_use_tls'];
//         else $data_step2['smtpd_use_tls'] = "yes";
// 
//         if(!empty($_POST['smtp_tls_note_starttls_offer'])) $data_step2['smtp_tls_note_starttls_offer'] = $_POST['smtp_tls_note_starttls_offer'];
//         else $data_step2['smtp_tls_note_starttls_offer'] = "yes";
// 
//         if(!empty($_POST['smtpd_tls_key_file'])) $data_step2['smtpd_tls_key_file'] = $_POST['smtpd_tls_key_file'];
//         else $data_step2['smtpd_tls_key_file'] = "/etc/postfix/tls/smtpd.key";
//         
//         if(!empty($_POST['smtpd_tls_cert_file'])) $data_step2['smtpd_tls_cert_file'] = $_POST['smtpd_tls_cert_file'];
//         else $data_step2['smtpd_tls_cert_file'] = "/etc/postfix/tls/smtpd.crt";
//         
//         if(!empty($_POST['smtp_tls_CAfile'])) $data_step2['smtp_tls_CAfile'] = $_POST['smtp_tls_CAfile'];
//         else $data_step2['smtp_tls_CAfile'] = "/etc/postfix/tls/cacert.pem";
//         
//         if(!empty($_POST['smtpd_tls_loglevel'])) $data_step2['smtpd_tls_loglevel'] = $_POST['smtpd_tls_loglevel'];
//         else $data_step2['smtpd_tls_loglevel'] = "1";
//         
//         if(!empty($_POST['smtpd_tls_received_header'])) $data_step2['smtpd_tls_received_header'] = $_POST['smtpd_tls_received_header'];
//         else $data_step2['smtpd_tls_received_header'] = "yes";
//         
//         if(!empty($_POST['smtpd_tls_session_cache_timeout'])) $data_step2['smtpd_tls_session_cache_timeout'] = $_POST['smtpd_tls_session_cache_timeout'];
//         else $data_step2['smtpd_tls_session_cache_timeout'] = "3600s";
//         
//         if(!empty($_POST['tls_random_source'])) $data_step2['tls_random_source'] = $_POST['tls_random_source'];
//         else $data_step2['tls_random_source'] = "dev:/dev/urandom";
//         
//         if(!empty($_POST['tls_daemon_random_source'])) $data_step2['tls_daemon_random_source'] = $_POST['tls_random_source'];
//         else $data_step2['tls_daemon_random_source'] = "dev:/dev/urandom";
//Sin Gmail
/*        if(!empty($_POST['broken_sasl_auth_clients'])) $data_step2['broken_sasl_auth_clients'] = $_POST['broken_sasl_auth_clients'];
        else*/ $data_step2['broken_sasl_auth_clients'] = "yes";

/*        if(!empty($_POST['smtpd_sasl_auth_enable'])) $data_step2['smtpd_sasl_auth_enable'] = $_POST['smtpd_sasl_auth_enable'];
        else*/ $data_step2['smtpd_sasl_auth_enable'] = "no";

/*        if(!empty($_POST['smtp_always_send_ehlo'])) $data_step2['smtp_always_send_ehlo'] = $_POST['smtp_always_send_ehlo'];
        else*/ $data_step2['smtp_always_send_ehlo'] = "yes";

        $pEmailRelay->replaceFileMainCF_2($data_step2, $result);
//         $id=1;
//         if(getParameter("control")=='1'){
//             $pEmailRelay->execConfigPosfix_2Mod($_POST['smtpd_password'], $_POST['smtpd_country_name'], $_POST['smtpd_province_name'], $_POST['smtpd_locality_name'], $_POST['smtpd_organization_name'], $_POST['smtpd_organizational_unit_name'], $_POST['smtpd_common_name']);
//             $pEmailRelay->updateEmailRelayAuthenticate($data, array("id"=>$id));
//         }else{
//             $pEmailRelay->execConfigPosfix_2($_POST['smtpd_password'], $_POST['smtpd_country_name'], $_POST['smtpd_province_name'], $_POST['smtpd_locality_name'], $_POST['smtpd_organization_name'], $_POST['smtpd_organizational_unit_name'], $_POST['smtpd_common_name']);
//             $pEmailRelay->addEmailRelayAuthenticate($data);
//         }
        
        $pEmailRelay->execConfigPosfix_3();
    
        $smarty->assign("mb_title", $arrLang["Result transaction"]);
        $smarty->assign("mb_message", "Configured successful");

        header("Location: ?menu=$module_name&action=edit");
    }
    
}

function createFieldForm($arrLang)
{
    $arrOpYesNo = array('' => 'None', 'yes' => 'Yes', 'no' => 'No');
    $arrOptions = array('1' => '1', '2' => '2', '3' => '3');

    $arrFields = array(
            "host_name"   => array(      "LABEL"                  => $arrLang["Host Name"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:250px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "smtp_server"   => array(      "LABEL"                  => $arrLang["Remote SMTP Server"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "port"   => array(      "LABEL"                  => $arrLang["Port"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "user"   => array(      "LABEL"                  => $arrLang["User (Email Account)"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "ereg",
                                            "VALIDATION_EXTRA_PARAM" => "([a-z0-9_]).([a-z]).com"
                                            ),
            "password"   => array(      "LABEL"                  => $arrLang["Password (Email Account)"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
//             "smtpd_password"   => array(      "LABEL"                  => $arrLang["Pass phrase for smtpd"],
//                                             "REQUIRED"               => "yes",
//                                             "INPUT_TYPE"             => "PASSWORD",
//                                             "INPUT_EXTRA_PARAM"      => "",
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtpd_country_name"   => array(      "LABEL"   => $arrLang["Country Name (2 letter code)"],
//                                             "REQUIRED"               => "yes",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"2"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtpd_province_name"   => array(      "LABEL"   => $arrLang["State or Province Name"],
//                                             "REQUIRED"               => "yes",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//              "smtpd_locality_name"   => array(      "LABEL"   => $arrLang["Locality Name"],
//                                             "REQUIRED"               => "yes",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtpd_organization_name"   => array(      "LABEL"   => $arrLang["Organization Name"],
//                                             "REQUIRED"               => "yes",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtpd_organizational_unit_name"   => array(      "LABEL"   => $arrLang["Organizational Unit Name"],
//                                             "REQUIRED"               => "yes",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtpd_common_name"   => array(      "LABEL"   => $arrLang["Common Name"],
//                                             "REQUIRED"               => "yes",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtp_sasl_auth_enable"   => array( "LABEL"     => $arrLang["Smtp sasl auth enable"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtp_sasl_password_maps"   => array(  "LABEL"   => $arrLang["Smtp sasl password maps"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtp_sasl_security_options"   => array(  "LABEL"     => $arrLang["Smtp sasl security options"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtpd_tls_auth_only"   => array( "LABEL"     => $arrLang["Smtpd tls auth only"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtp_use_tls"   => array(      "LABEL"     => $arrLang["Smtp use tls"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtpd_use_tls"   => array(      "LABEL"     => $arrLang["Smtpd use tls"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtp_tls_note_starttls_offer"   => array( "LABEL"     => $arrLang["Smtp tls note starttls offer"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtpd_tls_key_file"   => array(   "LABEL"   => $arrLang["Smtpd tls key file"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtpd_tls_cert_file"   => array(   "LABEL"   => $arrLang["Smtpd tls cert file"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtp_tls_CAfile"   => array(    "LABEL"   => $arrLang["Smtp tls CAfile"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "smtpd_tls_loglevel"   => array(  "LABEL"   => $arrLang["Smtpd tls loglevel"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOptions,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtpd_tls_received_header"   => array(  "LABEL"   => $arrLang["Smtpd tls received header"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtpd_tls_session_cache_timeout"   => array( "LABEL"   => $arrLang["Smtpd tls session cache timeout"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "tls_random_source"   => array(      "LABEL"   => $arrLang["Tls random source"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//             "tls_daemon_random_source"   => array(      "LABEL"   => $arrLang["Tls daemon random source"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "TEXT",
//                                             "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => ""
//                                             ),
//Sin Gmail
//             "broken_sasl_auth_clients"   => array(  "LABEL"   => $arrLang["Broken sasl auth clients"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtpd_sasl_auth_enable"   => array(  "LABEL"   => $arrLang["Smtpd sasl auth enable"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
//             "smtp_always_send_ehlo"   => array(  "LABEL"   => $arrLang["Smtp always send ehlo"],
//                                             "REQUIRED"               => "no",
//                                             "INPUT_TYPE"             => "SELECT",
//                                             "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
//                                             "VALIDATION_TYPE"        => "text",
//                                             "VALIDATION_EXTRA_PARAM" => "",
//                                             "EDITABLE"               => "si",
//                                             ),
            );
    return $arrFields;
}

function getParameter($parameter)
{
    if(isset($_POST[$parameter]))
        return $_POST[$parameter];
    else if(isset($_GET[$parameter]))
        return $_GET[$parameter];
    else
        return null;
}

function getAction()
{
    if(getParameter("edit"))
        return "edit";
    if(getParameter("commit")) //Get parameter by POST (submit)
        return "commit";
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