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
  $Id: puetos  */

include_once("libs/paloSantoDB.class.php");

class PaloSantoHardwareDetection
{
    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function PaloSantoHardwareDetection()
    {
        
    }
    
    /**
     * Procedimiento para obtener el listado los puertos con la descripcion de la tarjeta 
     *
     * @return array    Listado de los puertos
     */
    function getPorts()
    {
        global $arrLang;
        $tarjetas = array();
        /* VERSION ANTERIOR
        unset($respuesta);
        exec('sudo /root/genzaptelconf -l',$respuesta,$retorno);
        if($retorno==0 && $respuesta!=null && count($respuesta) > 0 && is_array($respuesta)){
            $idTarjeta = 0;
            foreach($respuesta as $key => $linea){
                if(ereg("^(### Span ([[:digit:]]{1,}):)[[:space:]]{1}([[:alnum:]]{1,}/[[:digit:]]{1,})(\"?.+\")",$linea,$regs2)){
                   $idTarjeta = $regs2[2];
                   $tarjetas["TARJETA$idTarjeta"]['DESC'] = array('ID' => $regs2[2], 'SPAM' => $regs2[1],'TIPO' => $regs2[3], 'ADICIONAL' => $regs2[4]);
                }
                else if(ereg("^([[:digit:]]{1,})[[:space:]]{1}([[:alnum:]]{1,})",$linea,$regs1)){
                   unset($puertos);
                   exec("cat /proc/zaptel/$idTarjeta",$puertos,$retorno2);
                   if($retorno2==0 && $puertos!=null && count($puertos) > 0 && is_array($puertos)){
                        foreach($puertos as $puerto){
                                if(ereg("[[:space:]]{1,}([[:digit:]]{1,})[[:space:]]{1}[[:alnum:]]{1,}/[[:digit:]]{1,}/[[:digit:]]{1,}[[:space:]]{1,}[[:alnum:]]{1,}[[:space:]]{1}(\(?.+\))",$puerto,$regs3)){
                                        if($regs3[1]==$regs1[1])
                                                $estado = $regs3[2];
                                        }
                        }
                   }
                   $tarjetas["TARJETA$idTarjeta"]['PUERTOS']["PUERTO$regs1[1]"] = array('LOCALIDAD' =>$regs1[1],'TIPO' => $regs1[2], 'ESTADO' => $estado);
                }
            }
        } 
        else 
            $this->errMsg = $arrLang["Ports not Founds"];
        return($tarjetas);*/

        unset($respuesta);
	exec('lszaptel',$respuesta,$retorno);
        if($retorno==0 && $respuesta!=null && count($respuesta) > 0 && is_array($respuesta)){
            $idTarjeta = 0;
            foreach($respuesta as $key => $linea){
                if(ereg("^(### Span[[:space:]]{1,}([[:digit:]]{1,}):)[[:space:]]{1}([[:alnum:]]{1,}/[[:digit:]]{1,})(\"?.+\")",$linea,$regs)){
                   $idTarjeta = $regs[2];
                   $tarjetas["TARJETA$idTarjeta"]['DESC'] = array('ID' => $regs[2], 'SPAM' => $regs[1],'TIPO' => $regs[3], 'ADICIONAL' => $regs[4]);
                }
                else if(ereg("[[:space:]]{0,}([[:digit:]]{1,})[[:space:]]{1}([[:alnum:]]{1,})[[:space:]]{1,}([[:alnum:]]{1,})[[:space:]]{1,}(\(?.+\))",$linea,$regs1)){
                    if($regs1[4] == '(In use)'){
                        $estado = $arrLang['(In Use)'];
                        $colorEstado = 'green';
                   }
                   $tarjetas["TARJETA$idTarjeta"]['PUERTOS']["PUERTO$regs1[1]"] = array('LOCALIDAD' =>$regs1[1],'TIPO' => $regs1[2], 'ADICIONAL' => $regs1[3], 'ESTADO' => $estado,'COLOR' => $colorEstado);
                }
            }
        }

        if(count($tarjetas)<=0){ //si no hay tarjetas instaladas
            $this->errMsg = $arrLang["Cards undetected on your system, press for detecting hardware detection."];
            $tarjetas = array();
        }
        if(count($tarjetas)==1){ //si aparace la tarjeta por default ZTDUMMY
            if($tarjetas["TARJETA0"]['DESC']['TIPO']=='ZTDUMMY/1'){
                $this->errMsg = $arrLang["Cards undetected on your system, press for detecting hardware detection."];
                $tarjetas = array();
            }
        }
        return($tarjetas);
    } 

    function hardwareDetection()
    {
        global $arrLang;
        exec("/usr/sbin/genzaptelconf -d -s -M -F",$respuesta,$retorno);
         if(is_array($respuesta)){
            foreach($respuesta as $key => $linea){
                //falta validar algun error
                //if(ereg("^(\[Errno [[:digit:]]{1,}\])",$linea,$reg))
                //  return $linea;
            }
            return $arrLang["Satisfactory Hardware Detection"];
        } 
    }
}
?>
