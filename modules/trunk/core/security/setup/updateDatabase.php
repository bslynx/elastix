<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0                                               |
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
  $Id: updateDatabase.php,v 1.2 2011-10-04 17:00:00 Alberto Santos asantos@palosanto.com Exp $ */

$documentRoot = "/var/www/html";
$databasePath = "/var/www/db";
include_once "$documentRoot/libs/paloSantoDB.class.php";
$pDB = new paloDB("sqlite3:///$databasePath/iptables.db");
$query = "select id,sport,dport,protocol from filter";
$rules = $pDB->fetchTable($query,true);
if($rules === false){
  echo $pDB->errMsg."\n";
  return 1;
}
$number=1;
foreach($rules as $rule){
    if(isset($rule["sport"]) && $rule["sport"] != "" && $rule["sport"] != "ANY"){
	if(is_null(changePortNumberToId($rule["sport"],$rule["protocol"],$rule["id"],$pDB,$number,"sport")))
	    return 1;
    }
    if(isset($rule["dport"]) && $rule["dport"] != "" && $rule["dport"] != "ANY"){
	if(is_null(changePortNumberToId($rule["dport"],$rule["protocol"],$rule["id"],$pDB,$number,"dport")))
	    return 1;
    }
}

function changePortNumberToId($port,$protocol,$idRule,&$pDB,&$number,$type)
{
    $query = "select id from port where details=?";
    $result = $pDB->getFirstRowQuery($query,true,array($port));
    if($result === false){
	echo $pDB->errMsg."\n";
	return null;
    }
    if(count($result) == 0){
	$id = getNextid($pDB);
	if(is_null($id))
	    return null;
	$query = "insert into port values(?,?,?,?,?)";
	$insert = $pDB->genQuery($query,array($id,"Unknown Port $number",$protocol,$port,"Port listed in rules but not found in port table"));
	if($insert === false){
	    echo $pDB->errMsg."\n";
	    return null;
	}
	$number++;
    }
    else
	$id = $result["id"];
    $query = "update filter set $type=? where id=?";
    $update = $pDB->genQuery($query,array($id,$idRule));
    if($update === false){
	echo $pDB->errMsg."\n";
	return null;
    }
    else
	return true;
}

function getNextid(&$pDB)
{
    $query = "select max(id) as id from port";
    $result = $pDB->getFirstRowQuery($query,true);
    if($result === false){
	echo $pDB->errMsg."\n";
	return null;
    }
    if(count($result) == 0)
	return 1;
    else
	return 1 + $result["id"];
}
?>