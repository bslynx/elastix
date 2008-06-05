<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0                                                  |
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
*/

// require_once files framework elastix.
require_once "libs/paloSantoForm.class.php";
require_once "libs/xajax/xajax.inc.php";
require_once "libs/paloSantoDB.class.php";
require_once("libs/paloSantoACL.class.php");

function _moduleContent(&$smarty,$module_name){
    // require_once files this module
    require_once "modules/$module_name/libs/paloSantoUserInfo.class.php";
    require_once "modules/$module_name/configs/default.conf.php";

    //call to global array ()
    global $arrConf;
    global $arrLang;

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir  = "$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //start ajax resource
    $contenido = startXajaxRefresh($local_templates_dir,$module_name);
    return $contenido;
}

/** Start Implementation ajax*/
function startXajaxRefresh($local_templates_dir,$module_name)
{
    $xajax = new xajax();
    $xajax->registerFunction("refreshUserInformation");
    $xajax->processRequests();

    $id_xajax_content = 
    "<div id='xajax_content'> </div>
     <script type='text/javascript'> 
        function ejecutarAjax()
        {
            xajax_refreshUserInformation('$local_templates_dir','$module_name');
            setTimeout(ejecutarAjax(),10000);
        }
        ejecutarAjax();
     </script>";
     $contenido = $xajax->printJavascript("libs/xajax/");
    return $contenido.$id_xajax_content;
}

function refreshUserInformation($local_templates_dir,$module_name)
{
    $respuesta = new xajaxResponse();
    $contenido = getUserInformation($local_templates_dir,$module_name);
    $respuesta->addAssign("xajax_content","innerHTML",$contenido);
//     $respuesta->addAlert("Holas");
    return $respuesta;
}
/** End Implementation ajax*/

function getUserInformation($local_templates_dir,$module_name)
{
    global $arrConf;
    global $arrLang;
    global $smarty;

    $callsRows   =$arrLang["Error at read yours calls."];
    $faxRows     =$arrLang["Error at read yours faxes."];
    $voiceMails  =$arrLang["Error at read yours voicemails."];
    $mails       =$arrLang["Error at read yours mails."];
    $systemStatus=$arrLang["Error at read status system."]; 

    $pDB = conectionAsteriskCDR();
    if($pDB){
        $objUserInfo = new paloSantoUserInfo($pDB);
        $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);

        if(is_array($arrData) && count($arrData)>0){
            $extension = $arrData['extension'];
            $email     = "{$arrData['login']}.{$arrData['domain']}";
            $passw     = $arrData['password'];
            $numRegs   = 5;

            $callsRows   = $objUserInfo->getLastCalls($extension,$numRegs);
            $faxRows	 = $objUserInfo->getLastFaxes($extension,$numRegs);
            $voiceMails	 = $objUserInfo->getVoiceMails($extension,$numRegs);
            $mails	 = $objUserInfo->getMails($email,$passw,$numRegs);
            $systemStatus= $objUserInfo->getSystemStatus($email,$passw);
        }
    }

    $smarty->assign("userInf",$arrLang["User Info"]);
    $smarty->assign("calls",$arrLang["Calls"]);
    $smarty->assign("emails",$arrLang["Em@ils"]);
    $smarty->assign("faxes",$arrLang["Faxes"]);
    $smarty->assign("voicemails",$arrLang["Voicem@ils"]);
    //$smarty->assign("im",$arrLang["IM"]);
    $smarty->assign("im","&nbsp;");
    $smarty->assign("system",$arrLang["System"]);
    $smarty->assign("callsRows",$callsRows);
    $smarty->assign("faxRows",$faxRows);
    $smarty->assign("voiceMails",$voiceMails);
    $smarty->assign("mails",$mails);
    $smarty->assign("systemStatus",$systemStatus);

    $oForm = new paloForm($smarty,array());
    $contenido = $oForm->fetchForm($local_templates_dir."/user_inf.tpl",$arrLang["User Info"]);
    return $contenido;
}

function conectionAsteriskCDR()
{
    include_once "libs/paloSantoConfig.class.php";
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    $dsnAsteriskCDR = $arrConfig['AMPDBENGINE']['valor']."://".
                      $arrConfig['AMPDBUSER']['valor']. ":".
                      $arrConfig['AMPDBPASS']['valor']. "@".
                      $arrConfig['AMPDBHOST']['valor']."/asteriskcdrdb";
    $pDB = new paloDB($dsnAsteriskCDR);

    if(!empty($pDB->errMsg)) 
        return false;
    else
        return $pDB;
}
?>