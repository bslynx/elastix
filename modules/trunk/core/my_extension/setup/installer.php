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

$DocumentRoot = (isset($_SERVER['argv'][1]))?$_SERVER['argv'][1]:"/var/www/html";
$DataBaseRoot = "/var/www/db";
$tmpDir = '/tmp/new_module/addons';  # in this folder the load module extract the package content

if(!file_exists("$DataBaseRoot/addons.db")){
    $cmd_mv    = "mv $tmpDir/setup/addons.db $DataBaseRoot/";
    $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/addons.db";
    exec($cmd_mv);
    exec($cmd_chown);
}

exec("sqlite3 $DataBaseRoot/addons.db '.tables addons_cache'",$arrConsole,$flagStatus);

if($flagStatus==0){
  $exists = isset($arrConsole[0]) & $arrConsole=='addons_cache';
  if(!$exists){
        $sql = "CREATE TABLE addons_cache(
                  name_rpm         varchar(20),
                  status           int,
                  observation      varchar(100)
                );";
        exec("sqlite3 $DataBaseRoot/addons.db '$sql'",$arrConsole,$flagStatus);
  }
}

exec("sqlite3 $DataBaseRoot/addons.db '.tables action_tmp'",$arrConsole,$flagStatus);

if($flagStatus==0){
  $exists = isset($arrConsole[0]) & $arrConsole=='action_tmp';
  if(!$exists){
        $sql = "CREATE TABLE action_tmp (name_rpm varchar(20), action_rpm varchar(20), data_exp varchar(100), user varchar(20));";
        exec("sqlite3 $DataBaseRoot/addons.db '$sql'",$arrConsole,$flagStatus);
  }
exit($flagStatus);
}
?>
