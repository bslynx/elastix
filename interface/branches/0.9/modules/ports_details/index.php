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
  $Id: new_campaign.php $ */

require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoTrunk.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoGrid.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    global $arrConf;
    global $arrLang;
    
    require_once "modules/$module_name/libs/PaloSantoPortsDetails.class.php";
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $contenidoModulo = listPorts($smarty, $module_name, $local_templates_dir);

    return $contenidoModulo;
}

function listPorts($smarty, $module_name, $local_templates_dir) {

    global $arrLang;
    $oPortsDetails = new PaloSantoPortsDetails();
    $oGrid = new paloSantoGrid($smarty); 
    $contenidoModulo = "";

    $smarty->assign("HARDWARE_DETECT",$arrLang['Hardware Detect']);
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("MENSAJE_CONFIRM",$arrLang['Hardware Detect']);
    $smarty->assign("CARD",$arrLang['Card']);
    $smarty->assign("CARD_NO_MOSTRAR",'ZTDUMMY/1');
    $smarty->assign("PORT_NOT_FOUND",$arrLang['Ports not Founds']);
    $smarty->assign("NO_PUERTO",$arrLang['No. Port']);
    $arrPortsDetails = $oPortsDetails->getPorts();

    if(is_array($arrPortsDetails) && count($arrPortsDetails) >0 && $arrPortsDetails!=null){
        $arrGrid = array("title"    => $arrLang['Card'],
            "icon"     => "images/pci.png",
            "width"    => "99%"
            );
        $contenidoModulo .= llenarTpl($local_templates_dir,$smarty,$arrGrid, $arrPortsDetails);    
    }
    else{
        $smarty->assign("mb_title",$arrLang['ERROR']);
        $smarty->assign("mb_message",$oPortsDetails->errMsg);
    }
    return $contenidoModulo;
}

function llenarTpl($local_templates_dir,$smarty,$arrGrid, $arrData)
{
    $smarty->assign("title", $arrGrid['title']);
    $smarty->assign("icon",  $arrGrid['icon']);
    $smarty->assign("width", $arrGrid['width']);
    $smarty->assign("arrData", $arrData);
    return $smarty->fetch($local_templates_dir."/listPorts.tpl");
}
?>