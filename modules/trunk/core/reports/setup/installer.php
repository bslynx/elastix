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
$tmpDir = '/tmp/new_module/reports';  # in this folder the load module extract the package content

if(!file_exists("$DataBaseRoot/rate.db")){
    $cmd_mv    = "mv $tmpDir/setup/rate.db $DataBaseRoot/";
    $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/rate.db";
    exec($cmd_mv);
    exec($cmd_chown);
}

$estado         = existDBField("rate", "estado", "rate.db", $DataBaseRoot);
$fecha_creacion = existDBField("rate", "fecha_creacion", "rate.db", $DataBaseRoot);
$fecha_cierre   = existDBField("rate", "fecha_cierre", "rate.db", $DataBaseRoot);
$hided_digits   = existDBField("rate", "hided_digits", "rate.db", $DataBaseRoot);
$idParent       = existDBField("rate", "idParent", "rate.db", $DataBaseRoot);

if($estado==1){ // hubo error ya que no existe uno de esos campos
	echo "Creating column estado in table rate of rate.db....\n";
    $sql = "ALTER TABLE rate ADD COLUMN estado VARCHAR(20) DEFAULT 'activo'";
	exec("sqlite3 $DataBaseRoot/rate.db '$sql'",$arrConsole,$flagStatus);
}

if($fecha_creacion==1){ // hubo error ya que no existe uno de esos campos
	echo "Creating column fecha_creacion in table rate of rate.db....\n";
	$sql = "ALTER TABLE rate ADD COLUMN fecha_creacion DATETIME DEFAULT '2005-01-01 10:00:00'";
	exec("sqlite3 $DataBaseRoot/rate.db \"$sql\" ",$arrConsole,$flagStatus);
}

if($fecha_cierre==1){ // hubo error ya que no existe uno de esos campos
	echo "Creating column fecha_cierre in table rate of rate.db....\n";
	$sql = "ALTER TABLE rate ADD COLUMN fecha_cierre DATETIME";
	exec("sqlite3 $DataBaseRoot/rate.db '$sql'",$arrConsole,$flagStatus);
}

if($hided_digits==1){ // hubo error ya que no existe uno de esos campos
	echo "Creating column hided_digits in table rate of rate.db....\n";
	$sql = "ALTER TABLE rate ADD COLUMN hided_digits INTEGER DEFAULT 0";
	exec("sqlite3 $DataBaseRoot/rate.db '$sql'",$arrConsole,$flagStatus);
}

if($idParent==1){ // hubo error ya que no existe uno de esos campos
	echo "Creating column idParent in table rate of rate.db....\n";
	$sql = "ALTER TABLE rate ADD COLUMN idParent INTEGER DEFAULT 0";
	exec("sqlite3 $DataBaseRoot/rate.db '$sql'",$arrConsole,$flagStatus);
}

function existDBField($table, $field, $db_name, $DataBaseRoot)
{
        $query = "select $field from $table;";
        exec("sqlite3 $DataBaseRoot/$db_name '$query'",$arrConsole,$flagStatus);
        return $flagStatus;
}

?>
