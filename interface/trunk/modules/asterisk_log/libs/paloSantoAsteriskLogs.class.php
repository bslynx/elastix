<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.1-4                                               |
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
  $Id: default.conf.php,v 1.1 2008-06-13 04:06:20 Alex Villacís Lasso Exp $ */

include_once "modules/$module_name/libs/LogParser_Full.class.php";

class paloSantoAsteriskLogs {
    var $_DB;
    var $errMsg;
    var $astLog;

    function paloSantoAsteriskLogs(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
        $this->astLog = new LogParser_Full();
    }

    /*HERE YOUR FUNCTIONS*/

    function ObtainNumAsteriskLogs($sFecha)
    {
        $total = $this->astLog->numeroBytesMensajesFecha($sFecha);
        return array($total);
    }

    function ObtainAsteriskLogs($limit, $offset, $sFecha)
    {
        $iBytesLeidos = 0;
        $lineas = array();
        $this->astLog->posicionarMensaje($sFecha, $offset);
        $bContinuar = TRUE;
        while ($bContinuar) {
            $pos = $this->astLog->obtenerPosicionMensaje();
            $s = $this->astLog->siguienteMensaje();            
            if (!(count($lineas) == 0 && !is_null($s) && $s{0} != '[')) {
                $regs = NULL;
                if (ereg('^\[([[:alnum:][:space:]\:]+)\][[:space:]]+([[:alpha:]]+)(\[[[:digit:]]+\][[:space:]]+[^[:space:]]+\.c:)[[:space:]]+(.*)$', $s, $regs)) {
                    $l = array(
                        'offset'=> $pos[1],
                        'fecha' => $regs[1],
                        'tipo' => $regs[2],
                        'origen' => $regs[3],
                        'linea' => $regs[4],
                    );
                    $lineas[] = $l;
                } else {
                    $lineas[] = array(
                        'offset'=> $pos[1],
                        'fecha' =>  '',
                        'tipo'  =>  '',
                        'linea' =>  $s,
                    );
                }
            }
            $pos = $this->astLog->obtenerPosicionMensaje();
            $iBytesLeidos = $pos[1] - $offset;
            $bContinuar = (!is_null($s) && $iBytesLeidos < $limit);
        }
        return $lineas;
    }
}
?>
