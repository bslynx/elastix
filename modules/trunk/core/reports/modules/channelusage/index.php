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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:21 gcarrillo Exp $ */

include_once "libs/paloSantoGraph.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

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
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $smarty->assign("REPORT_TITLE",$arrLang["Channels Usage Report"]);

    $img_1 = getImage_Hit($module_name,2);
    $img_2 = getImage_Hit($module_name,3);
    $img_3 = getImage_Hit($module_name,4);
    $img_4 = getImage_Hit($module_name,5);
    $img_5 = getImage_Hit($module_name,6);
    $img_6 = getImage_Hit($module_name,7);

    $smarty->assign("img_1", $img_1);
    $smarty->assign("img_2", $img_2);
    $smarty->assign("img_3", $img_3);
    $smarty->assign("img_4", $img_4);
    $smarty->assign("img_5", $img_5);
    $smarty->assign("img_6", $img_6);

    return $smarty->fetch("$local_templates_dir/channelusage.tpl");
}

function getImage_Hit($module_name,$id)
{
    $arrParameters = array($id);
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoChannelUsage","channelsUsage",$arrParameters,"functionCallback");
    return $oPaloGraph->getGraph();
}
?>