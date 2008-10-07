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
  $Id: index.php,v 1.2 2007/09/07 23:05:29 gcarrillo Exp $ */

include_once("../libs/misc.lib.php");
include_once "../configs/default.conf.php";

session_name("elastixSession");
session_start();

// load smarty
require_once("../libs/smarty/libs/Smarty.class.php");
$smarty = new Smarty();

$smarty->template_dir = "../themes/" . $arrConf['mainTheme'];
$smarty->compile_dir =  "../var/templates_c/";
$smarty->config_dir =   "../configs/";
$smarty->cache_dir =    "../var/cache/";

$smarty->assign("THEMENAME", $arrConf['mainTheme']);
$smarty->assign("titulo", "Help Window");
$smarty->assign("id_nodo", $_GET['id_nodo']);
$smarty->assign("name_nodo", $_GET['name_nodo']);
$smarty->display("_common/help.tpl");
?>
