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

        unset($respuesta);
    exec('lsdahdi',$respuesta,$retorno);
        if($retorno==0 && $respuesta!=null && count($respuesta) > 0 && is_array($respuesta)){
            $idTarjeta = 0;
            foreach($respuesta as $key => $linea){
                $estado = $arrLang['Unknown'];
                $colorEstado = 'gray';

                if(ereg("^### Span[[:space:]]+([[:digit:]]{1,}): ([[:alnum:]| |-|\/]+)(.*)$",$linea,$regs)){
                   $idTarjeta = $regs[1];
                   $tarjetas["TARJETA$idTarjeta"]['DESC'] = array('ID' => $regs[1], 'TIPO' => $regs[2], 'ADICIONAL' => $regs[3]);
                }
                else if(ereg("[[:space:]]*([[:digit:]]+) ([[:alnum:]]+)[[:space:]]+([[:alnum:]]+)(.*)",$linea,$regs1)){
                    //Estados de las lineas
                   if(eregi("In use.*RED",$regs1[4])){
                        $estado = $arrLang['(In Use)'];
                        $colorEstado = '#FF7D7D';
                   }
                   else if(eregi("In use",$regs1[4])){
                        $estado = $arrLang['(In Use)'];
                        $colorEstado = '#00CC00';
                   }
                   else if(eregi("RED",$regs1[4])){
                        $estado = $arrLang['(Not in Use)'];
                        $colorEstado = '#FF7D7D';
                   }
                   else{
                        $estado = $arrLang['(Not in Use)'];
                        $colorEstado = '#00CC00';
                   }

                   $tipo = $regs1[2];
                    //Tipo de las lineas
                   /*if($regs1[3]=='FXSKS')
                        $tipo ='FXO'; 
                   else if($regs1[3]=='FXOKS')
                        $tipo ='FXS';
                   else
                        $tipo = "PRI/BRI";*/

                   $tarjetas["TARJETA$idTarjeta"]['PUERTOS']["PUERTO$regs1[1]"] = array('LOCALIDAD' =>$regs1[1],'TIPO' => $tipo, 'ADICIONAL' => "$regs1[2] - $regs1[3]", 'ESTADO' => $estado,'COLOR' => $colorEstado);
                }
                else if(ereg("[[:space:]]*([[:digit:]]+) ([[:alnum:]]+)",$linea,$regs1)){
                   if($regs1[2] == 'unknown'){
                        $estado = $arrLang['Unknown'];
                        $colorEstado = 'gray';
                   }
                   $tarjetas["TARJETA$idTarjeta"]['PUERTOS']["PUERTO$regs1[1]"] = array('LOCALIDAD' =>$regs1[1],'TIPO' => "&nbsp;", 'ADICIONAL' => $regs1[2], 'ESTADO' => $estado,'COLOR' => $colorEstado);
                }
            }
        }

        if(count($tarjetas)<=0){ //si no hay tarjetas instaladas
            $this->errMsg = $arrLang["Cards undetected on your system, press for detecting hardware detection."];
            $tarjetas = array();
        }
        if(count($tarjetas)==1){ //si aparace la tarjeta por default ZTDUMMY
            $valor = $tarjetas["TARJETA1"]['DESC']['TIPO'];
            if(eregi("^DAHDI_DUMMY/1", $valor))
            {
                $this->errMsg = $arrLang["Cards undetected on your system, press for detecting hardware detection."];
                $tarjetas = array();
            }
        }
        return($tarjetas);
    }

    function getMisdnPortInfo()
    {

        exec('/usr/bin/misdnportinfo',$arrConsole,$flagStatus);
        if($flagStatus == 0)
            return $arrConsole;
        else return array();
    }

    function hardwareDetection($chk_dahdi_replace,$path_file_dahdi,$there_is_sangoma_card,$there_is_misdn_card)
    {
        global $arrLang;
        $there_is_other_card= "";
        $message = $arrLang["Satisfactory Hardware Detection"];
        $there_is_other_card  ="";
        $overwrite_chan_dahdi ="";

        if($there_is_sangoma_card=="true")
            $there_is_other_card = "-t";
        if($there_is_misdn_card=="true")
            $there_is_other_card .= " -m";
        if($chk_dahdi_replace=="true")
            $overwrite_chan_dahdi = " -o";

        exec("sudo /usr/sbin/hardware_detector $there_is_other_card $overwrite_chan_dahdi",$respuesta,$retorno);
        if(is_array($respuesta)){
            foreach($respuesta as $key => $linea){
                //falta validar algun error
                //if(ereg("^(\[Errno [[:digit:]]{1,}\])",$linea,$reg))
                //  return $linea;
            }
            return $message;
        }
    }    
}
?>
