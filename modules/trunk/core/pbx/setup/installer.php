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

createTrunkDB($DataBaseRoot);
if(!file_exists("$DataBaseRoot/trunk.db")){
    if(file_exists("$tmpDir/setup/trunk.db")){
      $cmd_mv    = "mv $tmpDir/setup/trunk.db $DataBaseRoot/";
      $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/trunk.db";
      exec($cmd_mv);
      exec($cmd_chown);
    }
    else{
      $cmd_mv = "mv $DataBaseRoot/trunk-pbx.db $DataBaseRoot/trunk.db";
      exec($cmd_mv);
    }
}


// creacion de la tabla provider_account
$provider_account = existDBTable("provider_account", "trunk.db", $DataBaseRoot);
if($provider_account['flagStatus']==0){
    $arrConsole = $provider_account['arrConsole'];
    $exists = isset($arrConsole) && isset($arrConsole[0])?true:false;
// antes verificar si hay datos en proveedores configurados sino existen solo se reemplaza la base
    if(!$exists){

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
        if(isset($result) & $result != ""){
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
    $result = existDBField("provider", "orden", "trunk.db", $DataBaseRoot);
    if($result['flagStatus']!=0)
      doUpdatesTrunkDB($DataBaseRoot);
}

function createTrunkDB($DataBaseRoot)
{
  $sql = <<<TEMP
BEGIN TRANSACTION;
CREATE TABLE attribute 
(
       id                INTEGER    PRIMARY KEY,
       type              VARCHAR(20),
       qualify           VARCHAR(20),
       insecure          VARCHAR(20),
       host              VARCHAR(20),
       fromuser          VARCHAR(20),
       fromdomain        VARCHAR(20),
       dtmfmode          VARCHAR(20),
       disallow          VARCHAR(20),
       context           VARCHAR(20),
       allow             VARCHAR(20),
       trustrpid         VARCHAR(20),
       sendrpid          VARCHAR(20),
       canreinvite       VARCHAR(20),
       id_provider       INTEGER,
       FOREIGN KEY(id_provider) REFERENCES provider(id)
);
INSERT INTO "attribute" VALUES(1, 'peer', 'yes', 'very', 'ippbx.net2phone.com', '', '', '', 'all', 'from-pstn', 'alaw&ulaw', '', '', 'no', 1);
INSERT INTO "attribute" VALUES(2, 'friend', 'yes', 'very', 'sip.camundanet.com', '', 'camundanet.com', 'rfc2833', 'all', 'from-pstn', 'gsm', '', '', 'no', 2);
INSERT INTO "attribute" VALUES(3, 'peer', 'yes', '', 'outbound1.vitelity.net', '', '', '', '', 'from-trunk', '', 'yes', 'yes', 'no', 3);
INSERT INTO "attribute" VALUES(4, 'friend', 'yes', 'very', 'sip1.starvox.com', '', '', 'rfc2833', '', 'from-pstn', '', '', '', '', 4);
INSERT INTO "attribute" VALUES(6, 'peer', 'yes', 'very', 'freephonie.net', '', 'freephonie.net', 'auto', 'all', 'from-trunk', 'alaw', '', '', 'no', 6);
INSERT INTO "attribute" VALUES(7, 'peer', 'yes', 'very', 'sip.ovh.net', '', 'sip.ovh.net', 'auto', 'all', 'from-trunk', 'alaw', '', '', 'no', 7);
INSERT INTO "attribute" VALUES(8, 'peer', 'yes', '', 'sip.voipdiscount.com', '', '', 'rfc2833', 'all', 'from-trunk', 'alaw', '', '', 'no', 8);
INSERT INTO "attribute" VALUES(9, 'peer', 'yes', 'very', 'gateway.circuitid.com', NULL, NULL, 'rfc2833', 'all', 'from-pstn', 'alaw&ulaw&gsm', 'no', 'no', 'no', 9);
INSERT INTO "attribute" VALUES(10, 'friend', 'yes', 'very', 'sip.vozelia.com', NULL, NULL, 'auto', 'all', 'from-pstn', 'alaw&ulaw&gsm', 'no', 'no', 'no', 10);
CREATE TABLE provider
(
       id                INTEGER    PRIMARY KEY,
       name              VARCHAR(20),
       domain            VARCHAR(20),
       type_trunk        VARCHAR(20),
       description       VARCHAR(20)
, orden integer);
INSERT INTO "provider" VALUES(1, 'Net2Phone', '', 'SIP', 'trunk type SIP', 1);
INSERT INTO "provider" VALUES(2, 'CamundaNET', '', 'SIP', 'trunk type SIP', 2);
INSERT INTO "provider" VALUES(3, 'Vitelity', '', 'SIP', 'trunk type SIP', 7);
INSERT INTO "provider" VALUES(4, 'StarVox', '', 'SIP', 'trunk type SIP', 6);
INSERT INTO "provider" VALUES(6, 'Freephonie', 'freephonie.net', 'SIP', 'trunk type SIP', 4);
INSERT INTO "provider" VALUES(7, 'OVH', 'sip.ovh.net', 'SIP', 'trunk type SIP', 5);
INSERT INTO "provider" VALUES(8, 'VoIPDiscount', 'sip.voipdiscount.com', 'SIP', 'trunk type SIP', 8);
INSERT INTO "provider" VALUES(9, 'CircuitID', '', 'SIP', 'trunk type SIP', 3);
INSERT INTO "provider" VALUES(10, 'Vozelia', '', 'SIP', 'trunk type SIP', 9);
CREATE TABLE trunk_bill
(
       trunk             VARCHAR(50)
);
CREATE TABLE provider_account
(
       id                INTEGER       PRIMARY KEY,
       account_name      VARCHAR(40),
       username          VARCHAR(40),
       password          VARCHAR(40),
       callerID          VARCHAR(40)   DEFAULT '',
       type              VARCHAR(20),
       qualify           VARCHAR(20),
       insecure          VARCHAR(20),
       host              VARCHAR(20),
       fromuser          VARCHAR(20),
       fromdomain        VARCHAR(20),
       dtmfmode          VARCHAR(20),
       disallow          VARCHAR(20),
       context           VARCHAR(20),
       allow             VARCHAR(20),
       trustrpid         VARCHAR(20),
       sendrpid          VARCHAR(20),
       canreinvite       VARCHAR(20),
       type_trunk        VARCHAR(20),
       status            VARCHAR(20)   DEFAULT 'activate',
       id_provider       INTEGER, id_trunk INTEGER,
       FOREIGN KEY(id_provider) REFERENCES provider(id)
);
COMMIT;
TEMP;
    file_put_contents("/tmp/trunk_dump.sql",$sql);
    $cmd_sqlite = "sqlite3 $DataBaseRoot/trunk-pbx.db '.read /tmp/trunk_dump.sql'";
    exec($cmd_sqlite);
    $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/trunk-pbx.db";
    exec($cmd_chown);
}

function doUpdatesTrunkDB($DataBaseRoot)
{
    $command  = "sqlite3 $DataBaseRoot/trunk.db 'ALTER TABLE provider_account ADD COLUMN id_trunk INTEGER'";
    exec($command);
    $command2 = "sqlite3 $DataBaseRoot/trunk.db 'ALTER TABLE provider ADD COLUMN orden INTEGER'";
    exec($command2);
    $command3 = "sqlite3 $DataBaseRoot/trunk.db 'UPDATE provider SET orden = 1 WHERE id=1'";
    exec($command3);
    $command4 = "sqlite3 $DataBaseRoot/trunk.db 'UPDATE provider SET orden = 2 WHERE id=2'";
    exec($command4);
    $command5 = "sqlite3 $DataBaseRoot/trunk.db 'UPDATE provider SET orden = 3 WHERE id=9'";
    exec($command5);
    $command6 = "sqlite3 $DataBaseRoot/trunk.db 'UPDATE provider SET orden = 4 WHERE id=6'";
    exec($command6);
    $command7 = "sqlite3 $DataBaseRoot/trunk.db 'UPDATE provider SET orden = 5 WHERE id=7'";
    exec($command7);
    $command8 = "sqlite3 $DataBaseRoot/trunk.db 'UPDATE provider SET orden = 6 WHERE id=4'";
    exec($command8);
    $command9 = "sqlite3 $DataBaseRoot/trunk.db 'UPDATE provider SET orden = 7 WHERE id=3'";
    exec($command9);
    $command10 = "sqlite3 $DataBaseRoot/trunk.db 'UPDATE provider SET orden = 8 WHERE id=8'";
    exec($command10);
    $command11 = "sqlite3 $DataBaseRoot/trunk.db 'UPDATE provider SET orden = 9 WHERE id=10'";
    exec($command11);
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
    $result = $pDB->fetchTable($query,true);
    if($result==FALSE){
        return false;
    }
    return $result;
}

function getTechnology($id, $pDB)
{
    $data   = array($id);
    $query  = "SELECT type_trunk FROM provider WHERE id = ?;";
    $result = $pDB->getFirstRowQuery($query,true,$data);
    if($result==FALSE){
        return null;
    }
    return $result['type_trunk'];
}
?>
