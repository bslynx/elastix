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
	$general = "[general] \n #include dundi_general_custom_elastix.conf";
	$mappings = "[mappings] \n #include dundi_mappings_custom_elastix.conf \n #include dundi_peers_custom_elastix.conf";

	//verificar si ya estan incluidas las librerias
	$exist = strstr($contents,"#include dundi_general_custom_elastix.conf");
	if($exist==""){
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
	; Aqui incluimos el contexto que contiene las extensiones.
	include => ext-local
	; Aqui incluimos el contexto que contiene las colas de atención o queues.
	include => ext-queues
	
	[dundi-priv-customers]
	; Si tenemos clientes (o revendemos servicios) podemos listarlos aqui
	
	[dundi-priv-via-pstn]
	; Aqui podemos incluir el contexto con nuestras troncales hacia la PSTN,
	; si queremos que los demas equipos puedan usar nuestras troncales
	include => outbound-allroutes
	
	[dundi-priv-local]
	; En este contexto unificamos los tres contextos, este lo podemos usar como
	; contexto de la troncal iax de dundi
	include => dundi-priv-canonical
	include => dundi-priv-customers
	include => dundi-priv-via-pstn
	
	[dundi-priv-lookup]
	; Este contexto se encarga de hacer la busqueda de un numero por dundi
	; Antes de hacer la busqueda definimos apropiadamente nuestro caller id.
	; ya que si no tendremos un caller id como 'device<0000>'.
	exten => _X.,1,Macro(user-callerid)
	exten => _X.,n,Macro(dundi-priv,$"."{"."EXTEN})
	exten => _X.,n,GotoIf($['$".$var."' = "."'BUSY'"."]?100)
	exten => _X.,n,Goto(bad-number,$"."{"."EXTEN},1)
	exten => _X.,100,Playtones(congestion)
	exten => _X.,101,Congestion(10)
	
	[macro-dundi-priv]
	; Esta es la macro que llamamos desde el contexto [dundi-priv-lookup]
	; Tambien evita que hayan loops en las consultas dundi.
	exten => s,1,Goto($"."{"."ARG1},1)
	switch => DUNDi/priv
	; ********************************************
	";
	
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
	$reemplazar ="include => from-internal-xfer\ninclude => dundi-priv-lookup";

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
