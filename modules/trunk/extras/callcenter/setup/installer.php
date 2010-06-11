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
  $Id:  $ */
$DocumentRoot = "/var/www/html";

require_once("$DocumentRoot/libs/paloSantoInstaller.class.php");
require_once("$DocumentRoot/libs/paloSantoDB.class.php");

$tmpDir = '/tmp/new_module/callcenter';  # in this folder the load module extract the package content
#generar el archivo db de campañas
$return=1;
$path_script_db="$tmpDir/setup/call_center.sql";
$datos_conexion['user']     = "asterisk";
$datos_conexion['password'] = "asterisk";
$datos_conexion['locate']   = "";
$oInstaller = new Installer();

if (file_exists($path_script_db))
{
    //STEP 1: Create database call_center
    $return=0;
    $return=$oInstaller->createNewDatabaseMySQL($path_script_db,"call_center",$datos_conexion);
    quitarColumnaSiExiste($pDB, 'call_center', 'agent', 'queue');
    crearColumnaSiNoExiste($pDB, 'call_center', 'calls', 
        'dnc', 
        "ADD COLUMN dnc int(1) NOT NULL DEFAULT '0'");
    crearColumnaSiNoExiste($pDB, 'call_center', 'call_entry', 
        'id_campaign', 
        "ADD COLUMN id_campaign  int unsigned, ADD FOREIGN KEY (id_campaign) REFERENCES campaign_entry (id)");
    crearColumnaSiNoExiste($pDB, 'call_center', 'calls', 
        'date_init', 
        "ADD COLUMN date_init  date, ADD COLUMN date_end  date, ADD COLUMN time_init  time, ADD COLUMN time_end  time");
    crearColumnaSiNoExiste($pDB, 'call_center', 'calls', 
        'agent', 
        "ADD COLUMN agent varchar(32)");
    crearColumnaSiNoExiste($pDB, 'call_center', 'call_entry', 
        'trunk', 
        "ADD COLUMN trunk varchar(20) NOT NULL");
    crearColumnaSiNoExiste($pDB, 'call_center', 'calls', 
        'failure_cause', 
        "ADD COLUMN failure_cause int(10) unsigned default null, ADD COLUMN failure_cause_txt varchar(32) default null");
    $pDB->disconnect();
}

exit($return);

function quitarColumnaSiExiste($pDB, $sDatabase, $sTabla, $sColumna)
{
    $sPeticionSQL = <<<EXISTE_COLUMNA
SELECT COUNT(*)
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
EXISTE_COLUMNA;
    $r = $pDB->getFirstRowQuery($sPeticionSQL, FALSE, array($sDatabase, $sTabla, $sColumna));
    if (!is_array($r)) {
        fputs(STDERR, "ERR: al verificar tabla $sTabla.$sColumna - ".$pDB->errMsg."\n");
        return;
    }
    if ($r[0] > 0) {
        fputs(STDERR, "INFO: Se encuentra $sTabla.$sColumna en base de datos $sDatabase, se ejecuta:\n");
        $sql = "ALTER TABLE $sTabla DROP COLUMN $sColumna";
        fputs(STDERR, "\t$sql\n");
        $r = $pDB->genQuery($sql);
        if (!$r) fputs(STDERR, "ERR: ".$pDB->errMsg."\n");
    } else {
        fputs(STDERR, "INFO: No existe $sTabla.$sColumna en base de datos $sDatabase. No se hace nada.\n");
    }
}

function crearColumnaSiNoExiste($pDB, $sDatabase, $sTabla, $sColumna, $sColumnaDef)
{
    $sPeticionSQL = <<<EXISTE_COLUMNA
SELECT COUNT(*) 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
EXISTE_COLUMNA;
    $r = $pDB->getFirstRowQuery($sPeticionSQL, FALSE, array($sDatabase, $sTabla, $sColumna));
    if (!is_array($r)) {
        fputs(STDERR, "ERR: al verificar tabla $sTabla.$sColumna - ".$pDB->errMsg."\n");
        return;
    }
    if ($r[0] <= 0) {
        fputs(STDERR, "INFO: No se encuentra $sTabla.$sColumna en base de datos $sDatabase, se ejecuta:\n");
        $sql = "ALTER TABLE $sTabla $sColumnaDef";
        fputs(STDERR, "\t$sql\n");
        $r = $pDB->genQuery($sql);
        if (!$r) fputs(STDERR, "ERR: ".$pDB->errMsg."\n");
    } else {
        fputs(STDERR, "INFO: Ya existe $sTabla.$sColumna en base de datos $sDatabase.\n");
    }
}
?>
