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
$tmpDir = '/tmp/new_module/distributed_dialplan';  # in this folder the load module extract the package content

if(!file_exists("$DataBaseRoot/elastixconnection.db")){
    $cmd_mv    = "mv $tmpDir/setup/elastixconnection.db $DataBaseRoot/";
    $cmd_chown = "chown asterisk.asterisk $DataBaseRoot/elastixconnection.db";
    exec($cmd_mv);
    exec($cmd_chown);
}
writeFilesAsterisk();
$cmd_mkdir = "mkdir -p $DocumentRoot/elastixConnection";
$cmd_mv    = "mv $tmpDir/setup/elastixconnection/* $DocumentRoot/elastixConnection";
$cmd_chown = "chown -R asterisk.asterisk $DocumentRoot/elastixConnection";
exec($cmd_mkdir);
exec($cmd_mv);
exec($cmd_chown);

function writeFilesAsterisk(){
	//configurando dundi.conf
	$file = "/etc/asterisk/dundi.conf";
	$contents = getFile($file);
	$general = "[general]\n#include dundi_general_custom_elastix.conf";
	$mappings = "[mappings]\n#include dundi_mappings_custom_elastix.conf\n#include dundi_peers_custom_elastix.conf";

	//verificar si ya estan incluidas las librerias
	$exist = strstr($contents,"#include dundi_general_custom_elastix.conf");
	if($exist==""){
		//creando archivos dundi
		$filename = "/etc/asterisk/dundi_general_custom_elastix.conf";
		$filename2= "/etc/asterisk/dundi_mappings_custom_elastix.conf";
		$filename3= "/etc/asterisk/dundi_peers_custom_elastix.conf";
		if(!file_exists($filename))
			exec("touch ".$filename);
		if(!file_exists($filename2))
			exec("touch ".$filename2);	
		if(!file_exists($filename3))
			exec("touch ".$filename3);		
		$new_contents = str_replace("[general]",$general,$contents);
		$new_contents = str_replace("[mappings]",$mappings,$new_contents);
		setFile($file, $new_contents);
	}
	
	// configurando extension.conf
	$var = "{DIALSTATUS}";
	$dundi = "
; ********************************************
; CONFIGURACION PARA DUNDi
[dundi-priv-canonical]
; Here we include the context that contains the extensions.
include => ext-local
; Here we include the context that contains the queues.
include => ext-queues
	
[dundi-priv-customers]
; If you have customers (or resell services) we can list them here
	
[dundi-priv-via-pstn]
; Here we include the context with our trunk to the PSTN,
; if we want the other teams can use our trunks
include => outbound-allroutes
	
[dundi-priv-local]
; In this context we unify the three contexts, we can use this as
; context of the trunks of dundi iax
include => dundi-priv-canonical
include => dundi-priv-customers
include => dundi-priv-via-pstn
	
[dundi-priv-lookup]
; This context is responsible for making the search for a number of dundi
; Before you do the search properly define our caller id.
; because if not we have a caller id as 'device<0000>'.
exten => _X.,1,Macro(user-callerid)
exten => _X.,n,Macro(dundi-priv,$"."{"."EXTEN})
exten => _X.,n,GotoIf($['$".$var."' = "."'BUSY'"."]?100)
exten => _X.,n,Goto(bad-number,$"."{"."EXTEN},1)
exten => _X.,100,Playtones(congestion)
exten => _X.,101,Congestion(10)
	
[macro-dundi-priv]
; This is the macro is called from the context [dundi-priv-lookup]
; It also avoids having loops in the consultations dundi.
exten => s,1,Goto($"."{"."ARG1},1)
switch => DUNDi/priv
; ********************************************";
	
	$file = "/etc/asterisk/extensions_custom.conf";
	$contents = getFile($file);
	$exist = strstr($contents,"[dundi-priv-lookup]");
	if($exist==""){
		$contents = $contents . $dundi;
		setFile($file, $contents);
	}

	// configuracion de iax_custom.conf
	$iax = "
[dundi]
type=user
dbsecret=dundi/secret
context=ext-local
disallow=all
allow=ulaw
allow=g726";
	
	$file = "/etc/asterisk/iax_custom.conf";
	$contents = getFile($file);
	$exist = strstr($contents,"[dundi]");
	if($exist==""){
		$contents = $contents . $iax;
		setFile($file, $contents);
	}
	
	//configurando extension.conf definiendo contextos
	$buscar = "include => from-internal-xfer\ninclude => bad-number";
	$reemplazar ="include => from-internal-xfer\n; include => bad-number\ninclude => dundi-priv-lookup";

	$file = "/etc/asterisk/extensions.conf";
	$contents = getFile($file);
	$new_contents = str_replace($buscar,$reemplazar,$contents);
	setFile($file, $new_contents);
}


function getFile($file){
	$handle = fopen($file, "r+");
	$contents = fread($handle, filesize($file));
	fclose($handle);
	return $contents;
}

function setFile($file, $toReplace){
	$fh = fopen($file, "w");
		if($fh){
			fwrite($fh, $toReplace);
		}
	fclose($fh);
}
?>
