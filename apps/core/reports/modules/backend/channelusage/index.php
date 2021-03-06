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

include_once "libs/paloSantoGraphImage.lib.php";

function _moduleContent(&$smarty, $module_name)
{
    //folder path for custom templates
    $local_templates_dir = getWebDirModule($module_name);

    $smarty->assign("title",_tr("Channels Usage Report"));
    $smarty->assign("icon","web/apps/$module_name/images/reports_channel_usage.png");

    if (isset($_GET['image'])) {
        $_GET['image'] = (int)$_GET['image'];
        displayGraph($module_name, "paloSantoChannelUsage", "channelsUsage",array($_GET['image']),"functionCallback");
    } else {
        $listaGraficos = array(
            'img_1' =>  2,
            'img_2' =>  3,
            'img_3' =>  4,
            'img_4' =>  5,
            'img_5' =>  6,
            'img_6' =>  7,
        );
        foreach (array_keys($listaGraficos) as $k)
            $listaGraficos[$k] = "<img alt=\"{$listaGraficos[$k]}\" src=\"?menu=$module_name&amp;image={$listaGraficos[$k]}&rawmode=yes\" />";
        $smarty->assign($listaGraficos);
        return $smarty->fetch("$local_templates_dir/channelusage.tpl");
    }
}
?>