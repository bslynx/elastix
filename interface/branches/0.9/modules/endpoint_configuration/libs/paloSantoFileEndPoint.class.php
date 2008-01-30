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
  $Id: paloSantoFile.class.php,v 1.1 2008/01/22 15:05:57 afigueroa Exp $ */

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
        Entre los datos tenemos el nombre del vendor, nombre de archivo, mas address.
     */
    function createFiles($ArrayData)
    {
        include_once "vendors/{$ArrayData['vendor']}XML.php";
        switch($ArrayData['vendor'])
        {
            case 'Polycom':
                //Header Polycom
                $contentHeader = HeaderFilePolycom($ArrayData['data']['filename']);

                if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentHeader))
                {
                    //Archivo Principal
                    $contentFilePolycom = PrincipalFilePolycom($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret']);

                    if($this->createFileConf($this->directory, $ArrayData['data']['filename']."reg.cfg", $contentFilePolycom))
                        return true;
                    else return false;
                }else return false;

                break;

            case 'Linksys':
                $contentFileLinksys =PrincipalFileLinksys($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, "spa".$ArrayData['data']['filename'].".cfg", $contentFileLinksys))
                    return true;
                else return false;

                break;

            case 'Aastra':
                $contentFileAastra =PrincipalFileAastra($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentFileAastra))
                    return true;
                else return false;

                break;

            case 'Cisco':
                $contentFileCisco =PrincipalFileCisco($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cnf", $contentFileCisco))
                    return true;
                else return false;

                break;

            case 'Atcom':
                $contentFileAtcom =PrincipalFileAtcom($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer,$ArrayData['data']['filename']);
                if($this->createFileConf($this->directory,"at".$ArrayData['data']['filename'].".cfg", $contentFileAtcom))
                    return true;
                else return false;

                break;

            case 'Snom':
                break;

            case 'Grandstream':
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

        $nameFileConf = strtolower($nameFileConf);
        $fd = fopen ($tftpBootPath.$nameFileConf, "w");
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
        switch($ArrayData['vendor'])
        {
            case 'Polycom':
                $this->deleteFileConf($this->directory, $ArrayData['data']['filename']."reg.cfg");
                $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
                break;

            case 'Linksys':
                $this->deleteFileConf($this->directory, "spa".$ArrayData['data']['filename'].".cfg");
                break;

            case 'Aastra':
                $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
                break;

            case 'Cisco':
                $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cnf");
                break;

            case 'Atcom':
                $this->deleteFileConf($this->directory, "at".$ArrayData['data']['filename'].".cfg");
                break;

            case 'Snom':
                break;

            case 'Grandstream':
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

        $nameFileConf = strtolower($nameFileConf);
        if (file_exists($tftpBootPath.$nameFileConf)) {
            if(!unlink($tftpBootPath.$nameFileConf)){
                $this->errMsg = $arrLang['Unable delete the file'].": $nameFileConf";
                return false;
            }
            return true;
        }
    }

    function executeScript($vendor, $module_name)
    {
        $dir = $_SERVER['DOCUMENT_ROOT']."/modules/$module_name/libs/vendors/";
        $cmd = "{$vendor}Script ".$this->ipAdressServer;
        exec($dir.$cmd,$arrConsole,$flagReturn);
        return ($flagReturn)?false:true;
    }
}
?>