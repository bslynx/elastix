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
  $Id: PaloSantoRepositories.php $ */

include_once("libs/paloSantoDB.class.php");

class PaloSantoRepositories
{
    var $errMsg;

    function PaloSantoRepositories()
    {

    }

     /**
     * Procedimiento para obtener el listado de los repositorios 
     *
     * @return array    Listado de los repositorios 
     */
    function getRepositorios($ruta)
    {
        $arrArchivosRepo = $this->getArchivosRepo($ruta);
        $repositorios = array();
        foreach($arrArchivosRepo as $key => $archivoRepo){
            $auxRepo      = $this->scanFileRepo($ruta,$archivoRepo);
            $repositorios = array_merge($repositorios,$auxRepo); 
        }
        return $repositorios;
    }

    function getArchivosRepo($dir='/etc/yum.repos.d/')
    {
        global $arrLang;
        $arr_repositorios  = scandir($dir);
        $arr_respuesta = array();
        
        if (is_array($arr_repositorios) && count($arr_repositorios) > 0) {
            foreach($arr_repositorios as $key => $repositorio){ 
                if(!is_dir($dir.$repositorio) && $repositorio!="." && $repositorio!=".." && strstr($repositorio,".repo")) //que se un archivo y que el archivo tenga extension .repo
                    $arr_respuesta[$repositorio] = $repositorio;
            }
        } 
        else 
            $this->errMsg = $arrLang["Repositor not Found"];
        return $arr_respuesta;
    }


    function scanFileRepo($ruta,$file)
    {
        $repositorios = array();
        $indice = 0;
        if($report_handle = fopen($ruta.$file, "r")){
            $bandera = 'nofoundRepo';
            while(!feof($report_handle)){
                $linea = trim(fgets($report_handle,1024)); 
                if(substr($linea,0,1)!='#'){ //para ignorar los comentarios
                    if(ereg("^\[?(.+)\]",$linea,$reg1)){//se busca [repo] 
                        $bandera = 'foundRepo'; //sirve para indicar que encontre un repositorio, y en la proxima iteracion esta el nombre completo del repositorio, esto se hace en el proximo if(...)
                    }
                    else if($bandera=='foundRepo'){ 
                        if(ereg("^name=",$linea,$reg2)){
                            $name = substr($linea,5);
                            $repositorios[$indice] = array('id' => $reg1[1],'name' => $name, 'file' => $file, 'activo' => '0'); //activo esta setedo temporalmente para que despues sea seteado
                        }
                        else if(ereg("^gpgcheck=([[:digit:]]{1,})",$linea,$reg3)){
                            if($repositorios[$indice]['id']==$reg1[1]){ //aseguro que es el repositorio
                                $repositorios[$indice]['activo']=$reg3[1]; //cambio su estatus
                                $bandera = 'nofoundRepo';
                                $indice++;
                            }
                        }
                    }
                }
            }
        }
        fclose($report_handle);
        return $repositorios;
    } 
}
?>