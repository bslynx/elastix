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
  $Id: index.php,v 1.2 2007/07/07 22:50:39 admin Exp $ */


include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent($smarty, $module_name)
{
    require_once "libs/misc.lib.php";

    //include module files
    include_once "modules/$module_name/libs/paloSantoDataApplets.class.php";
    include_once "modules/$module_name/libs/paloSantoSysInfo.class.php";
    include_once "modules/$module_name/libs/paloSantoDashboard.class.php";
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

    $oPalo = new paloSantoSysInfo();
    $pDataApplets = new paloSantoDataApplets($module_name,$arrConf);

    $action = getParameter("action");
    switch($action){
        case "saveRegister":
            $hwd = getParameter("hwd");
            $num_serie = getParameter("num_serie");
            $vendor = getParameter("vendor");

            ini_set("soap.wsdl_cache_enabled", "0");
            $client = new SoapClient($arrConf['dir_WebServices']);

            $client->registerHardware($vendor,$num_serie);
            return $oPalo->registerCard($hwd, $num_serie,$vendor);
            break;
        case "getRegister":
            $hwd = getParameter("hwd");
            return $oPalo->getDataCardRegister($hwd);
            break;
        case "updateOrder":
            $ids_applet = getParameter("ids_applet");
            return $oPalo->setApplets_UserOrder($ids_applet);
            break;
        case "image":
            $sImg = getParameter('image');
            executeImage($module_name, $sImg);
            return '';
        case "loadAppletData":
            $content = loadAppletData($pDataApplets);
            return $content;
            break;
        default:
            break;
    }


    $arrPaneles = $oPalo->getAppletsActivated($_SESSION["elastix_user"]);
    $AppletsPanels = createApplesTD($arrPaneles, $pDataApplets);

    $smarty->assign("module_name",  $module_name);
    $smarty->assign("AppletsPanels",$AppletsPanels);

    $action = getParameter("save_new");
    if(isset($action))
     $app = saveApplets_Admin();
    else $app = showApplets_Admin();
    $smarty->assign("APPLET_ADMIN",$app);

    return $smarty->fetch("file:$local_templates_dir/applets.tpl");
}

function createApplesTD($arrPaneles, $pDataApplets){
	$str1 = "<td id='td_columns1' class='column'>";
	$str2 = "<td id='td_columns2' class='column'>";
	$idApplet = "";
	for($i=0; $i<count($arrPaneles); $i++){
		$applestUser = $arrPaneles[$i];
		if(($i%2)==0){
			$str1 .= getApplet($applestUser, $pDataApplets);
		}else{
			$str2 .= getApplet($applestUser, $pDataApplets);
		}
	}
	$str1 .= "</td>";
	$str2 .= "</td>";
	$str = $str1.$str2;
	return $str;
}

function getApplet($applestUser, $pDataApplets)
{
  //  $nameFunction = "getData$applestUser[code]";
  //  $content = $pDataApplets->$nameFunction();

    $pDataApplets->setIcon($applestUser['icon']);
    $pDataApplets->setTitle(_tr($applestUser['name']));
    return $pDataApplets->drawApplet($applestUser['aau_id'],$applestUser['code']);
}

function loadAppletData($pDataApplets)
{
    $jsonObject = new PaloSantoJSON();
    $code = getParameter("code");
    $function = "getData$code";
    $message = array();
    $message["data"] = $pDataApplets->$function();
    $message["code"] = $code;
    $jsonObject->set_message($message);
    return $jsonObject->createJSON();
}

function executeImage($module_name, $sImg)
{
    $listaImgs = array(
        'CallsMemoryCPU'                                =>  array(null, 'functionCallback'),
        'ObtenerInfo_CPU_Usage'                         =>  array(null, null),
        'ObtenerInfo_MemUsage'                          =>  array(null, null),
        'ObtenerInfo_SwapUsage'                         =>  array(null, null),
        'ObtenerInfo_Particion'                         =>  array(array('percent'), null),
    );
    if (isset($listaImgs[$sImg])) {
        $arrParameters = array();
        if (is_array($listaImgs[$sImg][0])) foreach ($listaImgs[$sImg][0] as $k) {
            $arrParameters[] = isset($_GET[$k]) ? $_GET[$k] : '';
        }
        $callback = is_null($listaImgs[$sImg][1]) ? '' : $listaImgs[$sImg][1];
        displayGraph($module_name, 'paloSantoSysInfo', $sImg, $arrParameters, $callback);
    }
}


////////////////////// Begin Funciones para Applets Admin /////////////////////////////////
function showApplets_Admin()
{
    global $smarty;
    global $arrLang;
    global $arrConf;
    $module_name = "dashboard"; //$_SESSION["menu"];

    $oPalo = new paloSantoSysInfo();
    $oForm = new paloForm($smarty,array());

    $arrApplets = $oPalo->getApplets_User($_SESSION["elastix_user"]);

    $smarty->assign("applets",$arrApplets);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("Applet", $arrLang["Applet"]);
    $smarty->assign("Activated", $arrLang["Activated"]);
    $smarty->assign("IMG", "images/list.png");

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $htmlForm = $oForm->fetchForm("$local_templates_dir/applet_admin.tpl",$arrLang["Applet Admin"], $_POST);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveApplets_Admin()
{
    global $smarty;
    global $arrLang;
    $arrIDs_DAU = null;
    $module_name = "dashboard"; //$_SESSION["menu"];

    if(is_array($_POST) & count($_POST)>0){
        foreach($_POST as $key => $value){
            if(substr($key,0,7) == "chkdau_")
                $arrIDs_DAU[] = substr($key,7);
        }
    }

    $oPalo = new paloSantoSysInfo();
    $ok = $oPalo->setApplets_User($arrIDs_DAU, $_SESSION["elastix_user"]);

    if(!$ok){
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $smarty->assign("mb_message", $pprueba_applets->errMsg);
    }
    //return showApplets_Admin();
    header("Location: /index.php?menu=$module_name");
}
////////////////////// End Funciones para Applets Admin /////////////////////////////////
?>
