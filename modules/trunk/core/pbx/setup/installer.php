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

require_once "/var/www/html/libs/paloSantoDB.class.php";

$DocumentRoot = (isset($_SERVER['argv'][1]))?$_SERVER['argv'][1]:"/var/www/html";
$DataBaseRoot = "/var/www/db";
$tmpDir = '/tmp/new_module/pbx';  # in this folder the load module extract the package content

if(!file_exists("$DataBaseRoot/endpoint.db")){
    $cmd_mv    = "mv $tmpDir/setup/endpoint.db $DataBaseRoot/";
    $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/endpoint.db";
    exec($cmd_mv);
    exec($cmd_chown);
}
if(!file_exists("$DataBaseRoot/control_panel_design.db")){
    $cmd_mv    = "mv $tmpDir/setup/control_panel_design.db $DataBaseRoot/";
    $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/control_panel_design.db";
    exec($cmd_mv);
    exec($cmd_chown);
}
if(!file_exists("$DataBaseRoot/trunk.db")){
    $cmd_mv    = "mv $tmpDir/setup/trunk.db $DataBaseRoot/";
    $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/trunk.db";
    exec($cmd_mv);
    exec($cmd_chown);
}


// creacion de la tabla provider_account
$provider_account = existDBTable("provider_account", "trunk.db", $DataBaseRoot);
if($provider_account['flagStatus']==0){
    $arrConsole = $provider_account['arrConsole'];
    $exists = isset($arrConsole[0]) & $arrConsole=='provider_account';
// antes verificar si hay datos en proveedores configurados sino existen solo se reemplaza la base
    if(!$exists){
        $cmd_mv    = "mv $tmpDir/setup/trunk.db $DataBaseRoot/trunk-pbx.db";
        $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/trunk-pbx.db";
        exec($cmd_mv);
        exec($cmd_chown);

        $pDB    = new paloDB("sqlite3:////var/www/db/trunk.db");
        $pDBNew = new paloDB("sqlite3:////var/www/db/trunk-pbx.db");

        $query  = "SELECT
                        t.name        AS account_name,
                        t.username    AS username,
                        t.password    AS password,
                        a.type        AS type,
                        a.qualify     AS qualify,
                        a.insecure    AS insecure,
                        a.host        AS host,
                        a.fromuser    AS fromuser,
                        a.fromdomain  AS fromdomain,
                        a.dtmfmode    AS dtmfmode,
                        a.disallow    AS disallow,
                        a.context     AS context,
                        a.allow       AS allow,
                        a.trustrpid   AS trustrpid,
                        a.sendrpid    AS sendrpid,
                        a.canreinvite AS canreinvite,
                        p.id          AS id_provider
                   FROM 
                        trunk t, 
                        attribute a, 
                        provider p
                   WHERE 
                        t.id_provider = p.id AND 
                        a.id_trunk = t.id;";
        $result = $pDB->fetchTable($query, true);
        //recorriendo el $result
        foreach($result as $key => $value)
        {
            $data[0]  = $value['account_name'];
            $data[1]  = $value['username'];
            $data[2]  = $value['password'];
            $data[3]  = $value['type'];
            $data[4]  = $value['qualify'];
            $data[5]  = $value['insecure'];
            $data[6]  = $value['host'];
            $data[7]  = $value['fromuser'];
            $data[8]  = $value['fromdomain'];
            $data[9]  = $value['dtmfmode'];
            $data[10] = $value['disallow'];
            $data[11] = $value['context'];
            $data[12] = $value['allow'];
            $data[13] = $value['trustrpid'];
            $data[14] = $value['sendrpid'];
            $data[15] = $value['canreinvite'];
            $data[16] = getTechnology($value['id_provider'], $pDBNew);
            $data[17] = $value['id_provider'];
            if($value['username'] != "" && $value['password'] != "")
                insertAccount($data, $pDBNew);
        }

        // para la tabla trunk_bill
        $query  = "SELECT COUNT(*) AS size FROM trunk_bill;";
        $result = $pDB->getFirstRowQuery($query, true);
        if($result['size'] > 0){
            $result2 = getTrunkBills($pDB);
            foreach($result2 as $key2 => $value2){
                $trunkName = $value2['trunk'];
                insertTrunlBill($trunkName, $pDBNew);
            }
        }
        exec("mv $DataBaseRoot/trunk.db $DataBaseRoot/trunk-old.db");
        exec("mv $DataBaseRoot/trunk-pbx.db $DataBaseRoot/trunk.db");
    }
}

function existDBField($table, $field, $db_name, $DataBaseRoot)
{
    $query = "select $field from $table;";
    exec("sqlite3 $DataBaseRoot/$db_name '$query'",$arrConsole,$flagStatus);
    $result['flagStatus'] = $flagStatus;
    $result['arrConsole'] = $arrConsole;
    return $result;
}

function existDBTable($table, $db_name, $DataBaseRoot)
{
    exec("sqlite3 $DataBaseRoot/$db_name '.tables $table'",$arrConsole,$flagStatus);
    $result['flagStatus'] = $flagStatus;
    $result['arrConsole'] = $arrConsole;
    return $result;
}

function insertAccount($data, $pDB)
{
    $query = "INSERT INTO provider_account(account_name,username,password,type,qualify,insecure,host,fromuser,fromdomain,dtmfmode,disallow,context,allow,trustrpid,sendrpid,canreinvite,type_trunk,id_provider) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
    $result = $pDB->genQuery($query, $data);
    if($result==FALSE){
        return false;
    }
    return true;
}

function insertTrunlBill($trunkName, $pDB)
{
    $data = array($trunkName);
    $query = "INSERT INTO trunk_bill(trunk) VALUES(?);";
    $result = $pDB->genQuery($query, $data);
    if($result==FALSE){
        return false;
    }
    return true;
}

function getTrunkBills($pDB)
{
    $query = "SELECT * FROM trunk_bill;";
    $result = $pDB->genQuery($query);
    if($result==FALSE){
        return false;
    }
    return $result;
}

function getTechnology($id, $pDB)
{
    $data   = array($id);
    $query  = "SELECT type_trunk FROM provider id = ?;";
    $result = $pDB->getFirstRowQuery($query,true,$data);
    if($result==FALSE){
        return null;
    }
    return $result;
}
?>
