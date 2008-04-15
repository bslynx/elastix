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
  $Id: new_campaign.php $ */

include_once("libs/paloSantoDB.class.php");

/* Clase que implementa campaña (saliente por ahora) de CallCenter (CC) */
class paloSantoCallsHour
{
    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloSantoCallsHour(&$pDB)
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
    }



     //Procedimiento para obtener el número de llamadas entrantes agrupadas por colas

    function getCalls($tipo='all',$entrantes='all', $salientes='all',$fecha_init, $fecha_end, $limit, $offset)
    {
        global $arrLang;
        global $arrLan;

        //validamos la fecha
        if($fecha_init!="" && $fecha_end!="" ) {
            $fecha_init = explode('-',$fecha_init);
            $fecha_end  = explode('-',$fecha_end);
        }else {
            $this->msgError .= "Debe ingresarse una fecha inicio y una fecha fin";
            return false;
        }
        // pregunto si la fecha inicial existe
        if( is_array( $fecha_init ) && count( $fecha_init )==3 && is_array( $fecha_end ) && count( $fecha_end )==3 ) {
            $year_init  = $fecha_init[0];
            $month_init = $fecha_init[1];
            $day_init   = $fecha_init[2];
            $year_end   = $fecha_end[0];
            $month_end  = $fecha_end[1];
            $day_end    = $fecha_end[2];
            $fechaInicial = $fecha_init[0]."-".$fecha_init[1]."-".$fecha_init[2]." "."00:00:00";
            $fechaFinal = $fecha_end[0]."-".$fecha_end[1]."-".$fecha_end[2]." "."23:59:59";
        //si fecha_init y fecha_end no existen envio un mensaje de error
        }else {
            $this->msgError .= "Fecha Inicio y/o Fecha Fin no valida";
            return false;
        }

        $arreglo = array();
        $where = "";
        if($tipo=='E'){//hacemos consulta en tabla call_entry
        //validamos las opciones del combo de ENTRANTES
            
            if($entrantes=='T') {
                $parametro = "TIME(call_e.datetime_init) as hora";
                 $where .= "WHERE  (
                                    datetime_init>='{$fechaInicial}'

                                           AND

                                    datetime_end<='{$fechaFinal}'
                                ) and status='terminada'";
            }elseif($entrantes=='E'){
                 $parametro = "TIME(call_e.datetime_init) as hora";
                 $where .= "WHERE  (
                                    datetime_init>='{$fechaInicial}'

                                           AND

                                    datetime_end<='{$fechaFinal}'
                                ) and status='terminada'";
            }elseif($entrantes=='A'){
                 $parametro = "TIME(call_e.datetime_entry_queue) as hora";
                 $where .= "WHERE  (
                                    datetime_entry_queue >='{$fechaInicial}'

                                           AND

                                    datetime_end<='{$fechaFinal}'
                                ) and status='abandonada'";
            }
            
            /*if($entrantes=='T')
                 $where .= " ";
            elseif($entrantes=='E')
                 $where .= " and status='terminada'";
            elseif($entrantes=='A')
                 $where .= " and status='abandonada'";
            */
            $arr_result = FALSE;
            $this->errMsg = "";

            $sPeticionSQL = "SELECT  queue_ce.queue as queue,".$parametro."
                             FROM call_entry call_e, queue_call_entry queue_ce 
                             ".$where."
                                AND call_e.id_queue_call_entry=queue_ce.id"; /*AND call_e.status is not null*/ 
            $sPeticionSQL_2 = "SELECT  queue_ce.queue as queue, TIME(call_e.datetime_entry_queue) as                   hora
                             FROM call_entry call_e, queue_call_entry queue_ce 
                             WHERE  (
                                    datetime_entry_queue >='{$fechaInicial}'

                                           AND

                                    datetime_end<='{$fechaFinal}'
                                ) and status='abandonada' 
                                AND call_e.id_queue_call_entry=queue_ce.id";
        }
        else if($tipo=='S'){//hacemos consulta en tabla calls
        //validamos las opciones del combo de SALIENTES
            if($salientes=='T')
                 $where .= " ";
            elseif($salientes=='E')
                 $where .= " and status='Success'";
            elseif($salientes=='N')
                 $where .= " and (status='NoAnswer' OR status='ShortCall')";
            elseif($salientes=='A')
                 $where .= " and status='Abandoned'";

            $sPeticionSQL = "SELECT camp.queue as queue,  TIME(c.start_time) as hora 
                            FROM calls c , campaign camp
                             WHERE   (
                                    start_time>='{$fechaInicial}'

                                           AND
                                    end_time<='{$fechaFinal}'
                                )
                                AND c.id_campaign=camp.id AND c.status is not null  ".$where;
        }


//     echo $sPeticionSQL."<br><br>";
//     echo $sPeticionSQL_2."<br><br>";
    $arr_result =& $this->_DB->fetchTable($sPeticionSQL, true);
    if($entrantes=='T') {
            $arr_result_1 =& $this->_DB->fetchTable($sPeticionSQL, true);
            $arr_result_2 =& $this->_DB->fetchTable($sPeticionSQL_2, true);
            $arr_result = array_merge($arr_result_1, $arr_result_2);
    }

    if (!is_array($arr_result)) {
        $arr_result = FALSE;
        $this->errMsg = $this->_DB->errMsg;
    }
    $resultado = array();
        //armamos el arreglo de todos los datos a presentar clasificandolos por cola y hora
    if(is_array($arr_result)){
        foreach($arr_result as $hora){
            if(!isset($resultado[$hora['queue']]['cola']))
                $resultado[$hora['queue']]['cola']="";
            if(!isset($hora['queue']))
                $hora['queue'] = "";

            if($hora['hora']>="00:00:00" && $hora['hora']<"01:00:00"){
                if(!isset($resultado[$hora['queue']][0]))
                    $resultado[$hora['queue']][0] =1;
                else $resultado[$hora['queue']][0] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];
            }
            if($hora['hora']>="01:00:00" && $hora['hora']<"02:00:00"){
                if(!isset($resultado[$hora['queue']][1]))
                    $resultado[$hora['queue']][1] =1;
                else $resultado[$hora['queue']][1] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="02:00:00" && $hora['hora']<"03:00:00") {
                if(!isset($resultado[$hora['queue']][2]))
                    $resultado[$hora['queue']][2] =1;
                else $resultado[$hora['queue']][2] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="03:00:00" && $hora['hora']<"04:00:00"){
                if(!isset($resultado[$hora['queue']][3]))
                    $resultado[$hora['queue']][3] =1;
                else $resultado[$hora['queue']][3] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="04:00:00" && $hora['hora']<"05:00:00"){
                if(!isset($resultado[$hora['queue']][4]))
                    $resultado[$hora['queue']][4] =1;
                else $resultado[$hora['queue']][4] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="05:00:00" && $hora['hora']<"06:00:00"){
                if(!isset($resultado[$hora['queue']][5]))
                    $resultado[$hora['queue']][5] =1;
                else $resultado[$hora['queue']][5] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="06:00:00" && $hora['hora']<"07:00:00"){
                if(!isset($resultado[$hora['queue']][6]))
                    $resultado[$hora['queue']][6] =1;
                else $resultado[$hora['queue']][6] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="07:00:00" && $hora['hora']<"08:00:00"){
                if(!isset($resultado[$hora['queue']][7]))
                    $resultado[$hora['queue']][7] =1;
                else $resultado[$hora['queue']][7] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="08:00:00" && $hora['hora']<"09:00:00"){
                if(!isset($resultado[$hora['queue']][8]))
                    $resultado[$hora['queue']][8] =1;
                else $resultado[$hora['queue']][8] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="09:00:00" && $hora['hora']<"10:00:00"){
                if(!isset($resultado[$hora['queue']][9]))
                    $resultado[$hora['queue']][9] =1;
                else $resultado[$hora['queue']][9] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="10:00:00" && $hora['hora']<"11:00:00"){
                if(!isset($resultado[$hora['queue']][10]))
                    $resultado[$hora['queue']][10] =1;
                else $resultado[$hora['queue']][10] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="11:00:00" && $hora['hora']<"12:00:00"){
                if(!isset($resultado[$hora['queue']][11]))
                    $resultado[$hora['queue']][11] =1;
                else $resultado[$hora['queue']][11] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="12:00:00" && $hora['hora']<"13:00:00"){
                if(!isset($resultado[$hora['queue']][12]))
                    $resultado[$hora['queue']][12] =1;
                else $resultado[$hora['queue']][12] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="13:00:00" && $hora['hora']<"14:00:00"){
                if(!isset($resultado[$hora['queue']][13]))
                    $resultado[$hora['queue']][13] =1;
                else  $resultado[$hora['queue']][13] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="14:00:00" && $hora['hora']<"15:00:00"){
                if(!isset($resultado[$hora['queue']][14]))
                    $resultado[$hora['queue']][14] =1;
                else $resultado[$hora['queue']][14] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="15:00:00" && $hora['hora']<"16:00:00"){
                if(!isset($resultado[$hora['queue']][15]))
                    $resultado[$hora['queue']][15] =1;
                else $resultado[$hora['queue']][15] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="16:00:00" && $hora['hora']<"17:00:00"){
                if(!isset($resultado[$hora['queue']][16]))
                    $resultado[$hora['queue']][16] =1;
                else $resultado[$hora['queue']][16] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="17:00:00" && $hora['hora']<"18:00:00"){
                if(!isset($resultado[$hora['queue']][17]))
                    $resultado[$hora['queue']][17] =1;
                else $resultado[$hora['queue']][17] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="18:00:00" && $hora['hora']<"19:00:00"){
                if(!isset($resultado[$hora['queue']][18]))
                    $resultado[$hora['queue']][18] =1;
                else $resultado[$hora['queue']][18] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="19:00:00" && $hora['hora']<"20:00:00"){
                if(!isset($resultado[$hora['queue']][19]))
                    $resultado[$hora['queue']][19] =1;
                else $resultado[$hora['queue']][19] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="20:00:00" && $hora['hora']<"21:00:00"){
                if(!isset($resultado[$hora['queue']][20]))
                    $resultado[$hora['queue']][20] =1;
                else  $resultado[$hora['queue']][20] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="21:00:00" && $hora['hora']<"22:00:00"){
                if(!isset($resultado[$hora['queue']][21]))
                    $resultado[$hora['queue']][21] =1;
                else $resultado[$hora['queue']][21] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="22:00:00" && $hora['hora']<"23:00:00"){
                if(!isset($resultado[$hora['queue']][22]))
                    $resultado[$hora['queue']][22] =1;
                else $resultado[$hora['queue']][22] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }
            if($hora['hora']>="23:00:00" && $hora['hora']<"24:00:00"){
                if(!isset($resultado[$hora['queue']][23]))
                    $resultado[$hora['queue']][23] =1;
                else $resultado[$hora['queue']][23] += 1;
                $resultado[$hora['queue']]['cola'] = $hora['queue'];

            }

        }
    }//fin de si existe al arreglo

    //convertimos los indices desde "0" ordenados
    sort($resultado);
    reset($resultado);
    
    $arrResult['NumCalls'] = count($arr_result);
    $arrResult['Data'] = $resultado;//toda la data
    $arrResult['NumRecords'] = count($arrResult['Data']); //contabilizamos la cantidad de datos
    $arrResult['Data'] = array_slice($arrResult['Data'], $offset, $limit);//para presentar segun el limit y offset enviado

    return $arrResult;//retorno el arreglo

    }



}

?>
