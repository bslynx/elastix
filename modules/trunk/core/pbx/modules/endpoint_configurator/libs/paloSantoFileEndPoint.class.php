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
  $Id: paloSantoFileEndPoint.class.php,v 1.1 2008/01/22 15:05:57 afigueroa Exp $ */

class PaloSantoFileEndPoint
{
    var $directory;
    var $errMsg;
    var $ipAdressServer;

    function PaloSantoFileEndPoint($dir){
        $this->directory = $dir;
        $this->ipAdressServer = $_SERVER['SERVER_ADDR'];
    }

    /*
        La funcion createFiles nos permite crear los archivos de configuracion de un EndPoint
        Para ello recibimos un arreglo con los datos necesarios para crear estos archivos,
        Entre los datos tenemos el nombre del vendor, nombre de archivo, mac address.
     */
    function createFiles($ArrayData)
    {
        include_once "vendors/{$ArrayData['vendor']}.cfg.php";
        switch($ArrayData['vendor']){
            case 'Polycom':
                //Header Polycom
                $contentHeader = HeaderFilePolycom($ArrayData['data']['filename']);

                if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentHeader)){
                    //Archivo Principal
                    $contentFilePolycom = PrincipalFilePolycom($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret']);

                    if($this->createFileConf($this->directory, $ArrayData['data']['filename']."reg.cfg", $contentFilePolycom))
                        return true;
                    else return false;
                }else return false;

                break;

            case 'Linksys':
                $contentFileLinksys = PrincipalFileLinksys($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, "spa".$ArrayData['data']['filename'].".cfg", $contentFileLinksys)){
                    if(conexionHTTP($ArrayData['data']['ip_endpoint'], $this->ipAdressServer, $ArrayData['data']['filename']))
                        return true;
                    else return false;
                }
                else return false;

                break;

            case 'Aastra':
                $contentFileAastra = PrincipalFileAastra($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, strtoupper($ArrayData['data']['filename']).".cfg", $contentFileAastra) )
                    return true;
                else return false;

                break;

            case 'Cisco':
                 $contentFileCisco = PrincipalFileCisco($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer, $this->find_version() );
                if($this->createFileConf($this->directory, strtoupper("SIP".$ArrayData['data']['filename']).".cnf", $contentFileCisco))
                    return true;
                else return false;

                break;

            case 'Atcom':
                if($ArrayData['data']['model'] == "AT 320"){
                    $contentFileAtcom = PrincipalFileAtcom320($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['filename']);
                    $result = $this->telnet($ArrayData['data']['ip_endpoint'], "", "12345678", $contentFileAtcom);
                    if($result) return true;
                    else return false;
                }
                else if($ArrayData['data']['model'] == "AT 530" || $ArrayData['data']['model'] == "AT 620R"){
                    if(isset($ArrayData['data']['arrParameters']['versionCfg']))
                        $version = $ArrayData['data']['arrParameters']['versionCfg'];
                    else
                        $version = "2.0002";
                    $contentFileAtcom = PrincipalFileAtcom530($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['filename'], $version);
                    if($this->createFileConf($this->directory,"atc".$ArrayData['data']['filename'].".cfg", $contentFileAtcom))
                    {
                        $arrComandos = arrAtcom530($this->ipAdressServer, $ArrayData['data']['filename']);
                        $result = $this->telnet($ArrayData['data']['ip_endpoint'], "admin", "admin", $arrComandos);
                        if($result) return true;
                        else return false;
                    }else return false;
                }

                break;

            case 'Snom':
                $contentFileSnom = PrincipalFileSnom($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, "snom".$ArrayData['data']['model']."-".strtoupper($ArrayData['data']['filename']).".htm", $contentFileSnom))
                    return true;
                else return false;

                break;

            case 'Grandstream':
        	$contentFileGrandstream = PrincipalFileGrandstream($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['model']);
                if($this->createFileConf($this->directory, "gxp".$ArrayData['data']['filename'], $contentFileGrandstream)) {
		    exec("sudo -u root chmod o+rx /opt/openfire");
                    //ex: . /tftpboot/GS_CFG_GEN/bin/encode.sh 000945531b3b /tftpboot/gxp_config_1.1.6.46.template.cfg /tftpboot/cfg000945531b3b
		    exec("/tftpboot/GS_CFG_GEN/bin/encode.sh {$ArrayData['data']['filename']} /tftpboot/gxp{$ArrayData['data']['filename']} /tftpboot/cfg{$ArrayData['data']['filename']}",$arrConsole,$flagStatus);
		    exec("sudo -u root chmod o-rx /opt/openfire");
                    if($flagStatus == 0)
			return true;
		}
                else return false;

                break;

            case 'Zultys':
                //Common file Zultys models ZIP 2x1 and ZIP 2x2
                $contentCommon = CommonFileZultys($ArrayData['data']['model'],$this->ipAdressServer);
                if($this->createFileConf($this->directory,"{$ArrayData['data']['model']}_common.cfg",$contentCommon)){
                    //Archivo Principal
                    $contentFileZultys = PrincipalFileZultys($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret']);
                    if($this->createFileConf("{$this->directory}/{$ArrayData['data']['model']}",strtoupper($ArrayData['data']['filename']).".cfg",$contentFileZultys))
                        return true;
                    else return false;
                }
                else return false;

                break;

            case 'AudioCodes':
                $contentAudioCodes = PrincipalFileAudioCodes($ArrayData['data']['id_device'],$ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['model'],$ArrayData['data']['filename']);
                if($this->createFileConf($this->directory, $ArrayData['data']['model']."_".$ArrayData['data']['filename'].".cfg", $contentAudioCodes))
                    return true;
                else return false;
            break;
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
        }
    }

    function telnet($ip, $user, $password, $arrComandos){
        if ($fsock = fsockopen($ip, 23, $errno, $errstr, 30))
        {
            if(is_array($arrComandos) && count($arrComandos)>0)
            {
                if($user!="" && $user!=null){
                    fputs($fsock, "$user\r");
                    fread($fsock,1024);
                }
                if($password!="" && $password!=null){
                    fputs($fsock, "$password\r");
                    fread($fsock,1024);
                }
                foreach($arrComandos as $comando => $valor)
                {
                    $line = $comando;
                    if($valor!="")
                        $line = "$comando $valor";

                    fputs($fsock, "$line\r");
                    fread($fsock,1024);
                }
            }
            fclose($fsock);
            return true;
        }else return false;
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
                if($model == 'AT 530' || $model == 'AT 620R'){
                    if(isset($arrParametersOld['versionCfg']))
                        $arrParametersOld['versionCfg'] = $arrParametersOld['versionCfg'] + 0.0001;
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
