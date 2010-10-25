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
$tmpDir = '/tmp/new_module/agenda';  # in this folder the load module extract the package content

if(!file_exists("$DataBaseRoot/calendar.db")){
    $cmd_mv    = "mv $tmpDir/setup/calendar.db $DataBaseRoot/";
    $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/calendar.db";
    exec($cmd_mv);
    exec($cmd_chown);
}
if(!file_exists("$DataBaseRoot/address_book.db")){
    $cmd_mv    = "mv $tmpDir/setup/address_book.db $DataBaseRoot/";
    $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/address_book.db";
    exec($cmd_mv);
    exec($cmd_chown);
}

$picture = existDBField("contact", "picture", $DataBaseRoot);
$address = existDBField("contact", "address", $DataBaseRoot);
$company = existDBField("contact", "company", $DataBaseRoot);
$notes   = existDBField("contact", "notes",   $DataBaseRoot);
$status  = existDBField("contact", "status",  $DataBaseRoot);

if($picture==1){ // hubo error ya que no existe uno de esos campos
	$sql = "ALTER TABLE contact ADD COLUMN picture varchar(50)";
	exec("sqlite3 $DataBaseRoot/address_book.db '$sql'",$arrConsole,$flagStatus);
}	
if($address==1){
	$sql = "ALTER TABLE contact ADD COLUMN address varchar(100)";
	exec("sqlite3 $DataBaseRoot/address_book.db '$sql'",$arrConsole,$flagStatus);
}
if($company==1){
	$sql = "ALTER TABLE contact ADD COLUMN company varchar(30)";
	exec("sqlite3 $DataBaseRoot/address_book.db '$sql'",$arrConsole,$flagStatus);
}
if($notes==1){
	$sql = "ALTER TABLE contact ADD COLUMN notes varchar(200)";
	exec("sqlite3 $DataBaseRoot/address_book.db '$sql'",$arrConsole,$flagStatus);
}
if($status==1){
	$sql = "ALTER TABLE contact ADD COLUMN status varchar(30)";
	exec("sqlite3 $DataBaseRoot/address_book.db '$sql'",$arrConsole,$flagStatus);
}


function existDBField($table, $field, $DataBaseRoot)
{
	$query = "select $field from $table;";
	exec("sqlite3 $DataBaseRoot/address_book.db '$query'",$arrConsole,$flagStatus);
	return $flagStatus;
}


?>
