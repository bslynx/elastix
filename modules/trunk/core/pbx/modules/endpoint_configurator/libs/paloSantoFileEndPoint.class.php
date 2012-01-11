<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                 |
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
  $Id: paloSantoFileEndPoint.class.php,v 1.1 2008/01/22 15:05:57 asantos@palosanto.com Alberto Santos Exp $ */

if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
}

class PaloSantoFileEndPoint
{
    var $directory;
    var $errMsg;
    var $ipAdressServer;

    function PaloSantoFileEndPoint($dir,$endpoint_mask=NULL){
        $this->directory = $dir;
	if(is_null($endpoint_mask))
	    $this->ipAdressServer = $_SERVER['SERVER_ADDR'];
	else{
	    $pNetwork = new paloNetwork();
	    $pInterfaces = $pNetwork->obtener_interfases_red();
	    $endpoint_mask = explode("/",$endpoint_mask);
	    $endpoint_network = $pNetwork->getNetAdress($endpoint_mask[0],$endpoint_mask[1]);
	    foreach($pInterfaces as $interface){
		$mask = $pNetwork->maskToDecimalFormat($interface["Mask"]);
		$network = $pNetwork->getNetAdress($interface["Inet Addr"],$mask);
		if($network == $endpoint_network){
		    $this->ipAdressServer = $interface["Inet Addr"];
		    break;
		}
	    }
	    if(!isset($this->ipAdressServer))
		$this->ipAdressServer = $_SERVER['SERVER_ADDR'];
	}
    }

    function AsteriskManagerAPI($action, $parameters, $return_data=false) 
    {
        $astman_host = "127.0.0.1";
        $astman_user = 'admin';
        $astman_pwrd = obtenerClaveAMIAdmin();

        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("$astman_host", "$astman_user" , "$astman_pwrd")) {
            $this->errMsg = _tr("Error when connecting to Asterisk Manager");
        } else{
            $salida = $astman->send_request($action, $parameters);
            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                if($return_data) return $salida;
                else return explode("\n", $salida["Response"]);
            }else return false;
        }
        return false;
    }

    /*
        La funcion createFiles nos permite crear los archivos de configuracion de un EndPoint
        Para ello recibimos un arreglo con los datos necesarios para crear estos archivos,
        Entre los datos tenemos el nombre del vendor, nombre de archivo, mac address.
     */
    function createFiles($ArrayData)
    {
        include_once "vendors/{$ArrayData['vendor']}.cfg.php";
	$return = false;
        switch($ArrayData['vendor']){
            case 'Polycom':
                //Header Polycom
                $contentHeader = HeaderFilePolycom($ArrayData['data']['filename']);

                if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentHeader)){
                    //Archivo Principal
                    $contentFilePolycom = PrincipalFilePolycom($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret']);

                    if($this->createFileConf($this->directory, $ArrayData['data']['filename']."reg.cfg", $contentFilePolycom))
                        $return = true;
                    else $return = false;
                }else $return = false;

                break;

            case 'Linksys':
                $contentFileLinksys = PrincipalFileLinksys($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, "spa".$ArrayData['data']['filename'].".cfg", $contentFileLinksys)){
                    if(conexionHTTP($ArrayData['data']['ip_endpoint'], $this->ipAdressServer, $ArrayData['data']['filename']))
                        $return = true;
                    else $return = false;
                }
                else $return = false;

                break;

            case 'Aastra':
                $contentFileAastra = PrincipalFileAastra($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, strtoupper($ArrayData['data']['filename']).".cfg", $contentFileAastra) )
                    $return = true;
                else $return = false;

                break;

            case 'Cisco':
                 $contentFileCisco = PrincipalFileCisco($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer, $this->find_version() );
                if($this->createFileConf($this->directory, strtoupper("SIP".$ArrayData['data']['filename']).".cnf", $contentFileCisco))
                    $return = true;
                else $return = false;

                break;

            case 'Atcom':
                if($ArrayData['data']['model'] == "AT320"){
		    if($ArrayData['data']['tech'] == "iax2")
			$contentFileAtcom = PrincipalFileAtcom320IAX($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['filename']);
		    else
			$contentFileAtcom = PrincipalFileAtcom320SIP($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['filename']);
                    $result = $this->telnet($ArrayData['data']['ip_endpoint'], "", "12345678", $contentFileAtcom);
                    if($result) $return = true;
                    else $return = false;
                }
                else if($ArrayData['data']['model'] == "AT530" || $ArrayData['data']['model'] == "AT620" || $ArrayData['data']['model'] == "AT610" || $ArrayData['data']['model'] == "AT640"){
                    if(isset($ArrayData['data']['arrParameters']['versionCfg']))
                        $version = $ArrayData['data']['arrParameters']['versionCfg'];
                    else
                        $version = "2.0002";
		    if($ArrayData['data']['tech'] == "iax2")
			$contentFileAtcom = PrincipalFileAtcom530IAX($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['filename'], $version);
		    else
			$contentFileAtcom = PrincipalFileAtcom530SIP($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['filename'], $version);
                    if($this->createFileConf($this->directory,"atc".$ArrayData['data']['filename'].".cfg", $contentFileAtcom))
                    {
                        $arrComandos = arrAtcom530($this->ipAdressServer, $ArrayData['data']['filename']);
                        $result = $this->telnet($ArrayData['data']['ip_endpoint'], "admin", "admin", $arrComandos);
                        if($result) $return = true;
                        else $return = false;
                    }else $return = false;
                }

                break;

            case 'Snom':
                $contentFileSnom = PrincipalFileSnom($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, "snom".$ArrayData['data']['model']."-".strtoupper($ArrayData['data']['filename']).".htm", $contentFileSnom))
                    $return = true;
                else $return = false;

                break;

            case 'Grandstream':
        	$contentFileGrandstream = PrincipalFileGrandstream($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['model']);
                if($this->createFileConf($this->directory, "gxp".$ArrayData['data']['filename'], $contentFileGrandstream)) {
                    //ex: . /tftpboot/GS_CFG_GEN/bin/encode.sh 000945531b3b /tftpboot/gxp_config_1.1.6.46.template.cfg /tftpboot/cfg000945531b3b
                    exec("/tftpboot/GS_CFG_GEN/bin/encode.sh {$ArrayData['data']['filename']} /tftpboot/gxp{$ArrayData['data']['filename']} /tftpboot/cfg{$ArrayData['data']['filename']}",$arrConsole,$flagStatus);
                    if($flagStatus == 0)
                        $return = true;
                }
                else $return = false;

                break;

            case 'Zultys':
                //Common file Zultys models ZIP 2x1 and ZIP 2x2
                $contentCommon = CommonFileZultys($ArrayData['data']['model'],$this->ipAdressServer);
                if($this->createFileConf($this->directory,"{$ArrayData['data']['model']}_common.cfg",$contentCommon)){
                    //Archivo Principal
                    $contentFileZultys = PrincipalFileZultys($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret']);
                    if($this->createFileConf("{$this->directory}/{$ArrayData['data']['model']}",strtoupper($ArrayData['data']['filename']).".cfg",$contentFileZultys))
                        $return = true;
                    else $return = false;
                }
                else $return = false;

                break;

            case 'AudioCodes':
                $contentAudioCodes = PrincipalFileAudioCodes($ArrayData['data']['id_device'],$ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['model'],$ArrayData['data']['filename']);
                if($this->createFileConf($this->directory, $ArrayData['data']['model']."_".$ArrayData['data']['filename'].".cfg", $contentAudioCodes))
                    $return = true;
                else $return = false;
            break;

            case 'Yealink':
               if($ArrayData['data']['model'] == "SIP-T20/T20P" || $ArrayData['data']['model'] == "SIP-T22/T22P" || $ArrayData['data']['model'] == "SIP-T26/T26P" || $ArrayData['data']['model'] == "SIP-T28/T28P" ){
                    $contentFileYealink =PrincipalFileYealink($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                        if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentFileYealink)){
                            $parameters  = array('Command'=>'sip notify reboot-yealink '.$ArrayData['data']['ip_endpoint']);
                            $result      = $this->AsteriskManagerAPI('Command',$parameters);
                            if(!$result)
                                $return = false;
                            $return = true;
                        }
                        $return = false;
                }
                break;

            case 'LG-ERICSSON':
                if($ArrayData['data']['model'] == "IP8802A"){
                    $contentFileLG_Ericsson = PrincipalFileLG_IP8802A($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'], $this->ipAdressServer);
                    if($this->createFileConf($this->directory, $ArrayData['data']['filename'], $contentFileLG_Ericsson)){
                        $parameters  = array('Command'=>'sip notify reboot-yealink '.$ArrayData['data']['ip_endpoint']);
                            $result      = $this->AsteriskManagerAPI('Command',$parameters);
                            if(!$result)
                                $return = false;
                            $return = true;
                    }
                    $return = false;
                }
                break;
        }
	if(isset($_SESSION['endpoint_configurator']['extensions_registered'][$ArrayData['data']['ip_endpoint']])){
	    if(is_array($_SESSION['endpoint_configurator']['extensions_registered'][$ArrayData['data']['ip_endpoint']]) && count($_SESSION['endpoint_configurator']['extensions_registered'][$ArrayData['data']['ip_endpoint']]) > 0){
		foreach($_SESSION['endpoint_configurator']['extensions_registered'][$ArrayData['data']['ip_endpoint']] as $extension){
		    $tmp = explode(":",$extension);
		    $tech = strtolower($tmp[0]);
		    $number = $tmp[1];
		    $parameters  = array('Command'=>"$tech unregister $number");
                    $result      = $this->AsteriskManagerAPI('Command',$parameters);
		}
	    }
	}
	return $return;
    }

    function buildPattonConfFile($arrData,$tone_set)
    {
	include_once "vendors/Patton.cfg.php";
	$config = getPattonConfiguration($arrData,$tone_set);
	if(!$this->createFileConf($this->directory,$arrData["mac"]."_Patton.cfg",$config))
	    return false;
	$arrCommands = getPattonCommands($arrData,$this->ipAdressServer);
	$result = $this->checkTelnetCredentials($arrData["ip_address"],$arrData["telnet_username"],$arrData["telnet_password"],2);
	if($result === true){
	    if(!$this->telnet($arrData["ip_address"],"","",$arrCommands,2)){
		$this->errMsg = _tr("Unable to telnet to ").$arrData["ip_address"];
		return false;
	    }
	    else
		return true;
	}
	else
	    return $result;
    }

    function checkTelnetCredentials($ip,$user,$password,$sw)
    {
	if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
            fputs($fsock, "$user\r");
	    $this->read($fsock,$sw);
	    fputs($fsock, "$password\r");
	    $result = $this->read($fsock,$sw);
	    if(preg_match("/Authentication failed/",$result)){
		$this->errMsg = _tr("The username or password are incorrect");
		return null;
	    }
	    else
		return true;
	}
	else{
	    $this->errMsg = _tr("Unable to telnet to ").$ip;
	    return false;
	}
    }

    /*
        Esta funcion nos permite crear un archivo de configuracion
        Recibe el directorio, nombre de archivo, contenido del archivo.
     */
    function createFileConf($tftpBootPath, $nameFileConf, $contentConf)
    {
        global $arrLang;
        if(!is_dir($tftpBootPath)) mkdir($tftpBootPath,0755,true);

        if (file_exists("$tftpBootPath/$nameFileConf") && !is_writable("$tftpBootPath/$nameFileConf")) {
            unlink("$tftpBootPath/$nameFileConf");
        }
        $fd = fopen ("$tftpBootPath/$nameFileConf", "w");
        if ($fd){
            fputs($fd,$contentConf,strlen($contentConf)); // write config file
        fclose ($fd);
            return true;
        }
        $this->errMsg = $arrLang['Unable write the file'].": $nameFileConf";
        return false;
    }


    /*
        La funcion deleteFiles nos permite eliminar los archivos de configuracion de un
        EndPoint. Para ello recibimos un arreglo con los datos necesarios para eliminar
        estos archivos. Los datos recibidos son el nombre del vendor, nombre de archivo.
     */
    function deleteFiles($ArrayData)
    {
        switch($ArrayData['vendor']){
            case 'Polycom':
                if($this->deleteFileConf($this->directory, $ArrayData['data']['filename']."reg.cfg")){
                    return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
                } else return false;
                break;

            case 'Linksys':
                return $this->deleteFileConf($this->directory, "spa".$ArrayData['data']['filename'].".cfg");
                break;

            case 'Aastra':
                return $this->deleteFileConf($this->directory, strtoupper($ArrayData['data']['filename']).".cfg");
                break;

            case 'Cisco':
                return $this->deleteFileConf($this->directory, strtoupper("SIP".$ArrayData['data']['filename']).".cnf");
                break;

            case 'Atcom':
                return $this->deleteFileConf($this->directory, "atc".$ArrayData['data']['filename'].".cfg");
                break;

            case 'Snom':
                return $this->deleteFileConf($this->directory, "snom".$ArrayData['data']['model']."-".strtoupper($ArrayData['data']['filename']).".htm");
                break;

            case 'Grandstream':
		if($this->deleteFileConf($this->directory, "cfg".$ArrayData['data']['filename'])){
                    return $this->deleteFileConf($this->directory, "gxp".$ArrayData['data']['filename']);
                }else return false;
                break;

            case 'Zultys':
                return $this->deleteFileConf("{$this->directory}/{$ArrayData['data']['model']}", strtoupper($ArrayData['data']['filename']).".cfg");
                break;

            case 'AudioCodes':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['model']."_".$ArrayData['data']['filename'].".cfg");
            break;

            case 'Yealink':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
            break;

            case 'LG-ERICSSON':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename']);
            break;
        }
    }

    /*
        Esta funcion nos permite eliminar un archivo de configuracion
        Recibe el directorio, nombre de archivo.
     */
    function deleteFileConf($tftpBootPath, $nameFileConf)
    {
        global $arrLang;

        if (file_exists("$tftpBootPath/$nameFileConf")) {
            if(!unlink("$tftpBootPath/$nameFileConf")){
                $this->errMsg = $arrLang['Unable delete the file'].": $nameFileConf";
                return false;
            }
            return true;
        }
    }

    function createFilesGlobal($vendor)
    {
        include_once "vendors/{$vendor}.cfg.php";

        switch($vendor){
            case 'Polycom':
                //PASO 1: Creo los directorios Polycom.
                if(mkdirFilePolycom($this->directory)){
                    $contentFilePolycom = serverFilePolycom($this->ipAdressServer);

                    //PASO 2: Creo el archivo server.cfg
                    if($this->createFileConf($this->directory, "server.cfg", $contentFilePolycom)){
                        $contentFilePolycom = sipFilePolycom($this->ipAdressServer);

                        //PASO 3: Creo el archivo sip.cfg
                        return $this->createFileConf($this->directory, "sip.cfg", $contentFilePolycom);
                    } else return false;
                } else return false;

                break;

            case 'Linksys':
                //Creando archivos de ejemplo.
                $contentFileLinksys = templatesFileLinksys($this->ipAdressServer);
                $this->createFileConf($this->directory, "spaxxxxxxxxxxxx.template.cfg", $contentFileLinksys);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Aastra':
                //Creando archivos de ejemplo.
                $contentFileAatra = templatesFileAastra($this->ipAdressServer);
                $this->createFileConf($this->directory, "aastra.cfg", $contentFileAatra);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Cisco':
                //Creando archivos de ejemplo.
                $contentFileCisco = defaultFileCisco($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer, $this->find_version());
                $this->createFileConf($this->directory, "SIPDefault.cnf", $contentFileCisco);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Atcom':
                //Creando archivos de ejemplo.
                $contentFileAtcom = templatesFileAtcom($this->ipAdressServer);
                $this->createFileConf($this->directory, "atcxxxxxxxxxxxx.template.cfg", $contentFileAtcom);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Snom':
                //Creando archivos de ejemplo.
                //SNOM reguires a separate file for each model. The file contents of each file
                //is the same.
                $contentFileSnom = generalSettingsFileSnom($this->ipAdressServer);
                $this->createFileConf($this->directory, "snom300.htm", $contentFileSnom);
                $this->createFileConf($this->directory, "snom320.htm", $contentFileSnom);
                $this->createFileConf($this->directory, "snom360.htm", $contentFileSnom);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Grandstream':
                //Creando archivos de ejemplo.
                $contentFileAatra = templatesFileGrandstream($this->ipAdressServer);
                $this->createFileConf($this->directory, "gxp_config_1.1.6.46.template", $contentFileAatra);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Zultys':
                //Creando archivos de ejemplo.
                $contentFileZultys = templatesFileZultys("ZIP2x1",$this->ipAdressServer);
                $this->createFileConf($this->directory, "ZIP2x1_common.template.cfg", $contentFileZultys);
                 $contentFileZultys = templatesFileZultys("ZIP2x2",$this->ipAdressServer);
                $this->createFileConf($this->directory, "ZIP2x2_common.template.cfg", $contentFileZultys);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;
            case 'AudioCodes':
                $contentAudioCodes = templatesFileAudioCodes($this->ipAdressServer);
                $this->createFileConf($this->directory, "AudioCodes.template", $contentAudioCodes);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Yealink':
                //Creando archivos de ejemplo.
                $contentFileYealink = templatesFileYealink($this->ipAdressServer);
                $this->createFileConf($this->directory, "y000000000000.cfg", $contentFileYealink);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'LG-ERICSSON':
                $contentFileLG_Ericsson = templatesFileLG_Ericsson($this->ipAdressServer);
                $this->createFileConf($this->directory, "l000000000000", $contentFileLG_Ericsson);
                return true;
                break;
        }
    }

    function telnet($ip, $user, $password, $arrComandos, $sw=1)
    {
        if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
            if(is_array($arrComandos) && count($arrComandos)>0)
            {
                if($user!="" && $user!=null){
                    fputs($fsock, "$user\r");
                    $this->read($fsock,$sw);
                }
                if($password!="" && $password!=null){
                    fputs($fsock, "$password\r");
                    $this->read($fsock,$sw);
                }
                foreach($arrComandos as $comando => $valor)
                {
                    $line = $comando;
                    if($valor!="")
                        $line = "$comando $valor";
                    fputs($fsock, "$line\r");
                    $this->read($fsock,$sw);
                }
            }
            fclose($fsock);
            return true;
        }else return false;
    }

    function read($fsock, $sw=1 ,$seg=1)
    {
	$s = ""; 
	if($sw==1){
	  $s = fread($fsock,1024);
	}
	else if($sw==2){
	  stream_set_blocking($fsock, TRUE);
	  stream_set_timeout($fsock,$seg);
	  $info = stream_get_meta_data($fsock);
	  while (true) {
	    $char = fgetc($fsock);
	    if(empty($char) && $info['timed_out']) break;
	    $s .= "$char";
	    $info = stream_get_meta_data($fsock);
	  }
	}
	return $s;
    }


    function updateArrParameters($vendor, $model, $arrParametersOld)
    {
        switch($vendor){
            case 'Polycom':
                break;

            case 'Linksys':
                break;

            case 'Aastra':
                break;

            case 'Cisco':
                break;

            case 'Atcom':
                if($model == 'AT530' || $model == 'AT620' || $model == 'AT610' || $model == 'AT640'){
                    if(isset($arrParametersOld['versionCfg'])){
                        $arrParametersOld['versionCfg'] = $arrParametersOld['versionCfg'] + 0.0001;
			if(strlen($arrParametersOld['versionCfg']) == 1)
			    $arrParametersOld['versionCfg'] .= ".0";	
			while(strlen($arrParametersOld['versionCfg']) < 6)
			    $arrParametersOld['versionCfg'] .= "0";
		    }
                    else
                        $arrParametersOld['versionCfg'] = '2.0005';
                }
                break;

            case 'Snom':
                break;

            case 'Grandstream':
                break;

            case 'Zultys':
                break;

            case 'AudioCodes':
                break;

            case 'Yealink':
                break;

            case 'LG-ERICSSON':
                break;
        }

        return $arrParametersOld;
    }

        /*  The function find_version() find the files included P0S as, P0S3-xx-x-xx.sb2 or other.
	    This function return only the file name and not the extension.
	    Add by Franck danard.
	    Maybe there's several solution to do it!
	*/
	function find_version()
	{
            // Replace this code by the good directory tftp.
            $monrep = opendir($this->directory);
            while ($entryname = readdir($monrep)){
                // Finding begin file P0S
                $pos = strripos($entryname,"P0S");
                if ($pos === 0) // Cut the file extension .sb2
                    $image_version=strtok($entryname,".sb2");
            }
            return $image_version;
	}
}
?>
