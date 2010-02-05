<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.5-9                                               |
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
  $Id: paloSantoMonitoring.class.php,v 1.2 2010-02-04 12:03:59 Oscar Navarrete anavarre@espol.edu.ec Exp $ */
class paloSantoMonitoring {
    var $_DB;
    var $errMsg;

    function paloSantoMonitoring(&$pDB)
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

    /*FUNCIONES DIRECTAS CON LA BASE DE DATOS*/

    function ObtainNumMonitoring($filter_field, $filter_value)
    {
        //Here your implementation
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT COUNT(*) FROM asteriskcdrdb $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }


    function ObtainMonitoring($limit=NULL, $offset=NULL, $filter_field=NULL, $filter_value=NULL)
    {
        //Here your implementation
        $where = "";
        if(!empty($filter_field) and !empty($filter_value ))
            $where = "where $filter_field like '%$filter_value%'";

        $query   = "SELECT * FROM asteriskcdrdb $where LIMIT $limit OFFSET $offset";
        //ORDER BY
        //$query .= " ORDER BY name";        

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    
    function addCdrMonitoring($data)
    {
        $queryInsert = $this->_DB->construirInsert('asteriskcdrdb', $data);

        echo $queryInsert;
        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }


    function getCdrMonitoringById($id)
    {
        $query   = "SELECT * FROM asteriskcdrdb ";
        $strWhere = "id=$id";

        // Clausula WHERE aqui
        if(!empty($strWhere)) $query .= "WHERE $strWhere ";

        $result=$this->_DB->getFirstRowQuery($query, true);
        return $result;
    }
    

    function updateCdrMonitoring($data,$where)
    {
        $queryUpdate = $this->_DB->construirUpdate('asteriskcdrdb', $data,$where);
        $result = $this->_DB->genQuery($queryUpdate);

        return $result;
    }


    function deleteCdrMonitoring($id)
    {
        $query = "DELETE FROM asteriskcdrdb WHERE id=$id";
        $result = $this->_DB->genQuery($query);
        if($result[0] > 0)
            return true;
        else return false;
    }

    //////////////////////////FUNCIONES PASADAS DEL INDEX////////////////////////////

    function borrarRecordings($data)
    {
        $path = "/var/spool/asterisk/monitor";

        if(is_array($data) && count($data) > 0){
            foreach($data as $name => $on){
                if(substr($name,0,4)=='rcd-'){
                    $file = substr($name,4);
                    $file = str_replace("_",".",$file);
                    unlink("$path/$file");
                }
            }
        }   
    }


    function getCallsByRecording($offset, $end, $archivos, $pDBCDR, $extension, $esAdministrador, $origen, $destino){
        $llamadas = array();

        for($i=$offset; $i<$end; $i++)
        {
            $archivo = $archivos[$i][0];
        
            //tengo que obtener los archivos que pertenezcan a la extension
           //obtener los archivos con formato auto-timestamp-extension... grabacion ONDEMAND
            //"auto\-[[:digit:]]+\-$extension(.+)\.[wav|WAV]$"
            $llamada_incoming = false;
            $llamada_outgoing = false;
            if (ereg("^auto\-([[:digit:]]+)\-$extension(.+)\.[wav|WAV|gsm]",$archivo,$regs)){
                exec("echo 'A' > /tmp/oscar");
                //ya tengo el archivo, busco el correspondiente en el registro de llamadas - con el timestamp y la extension
                $llamada=$this->obtenerCDROnDemand($pDBCDR,$extension,$regs[1], $esAdministrado, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo']=$archivo;
                    $llamada['type'] = "on demand";
                    $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }
            //buscar llamadas incoming IN-extension-uniqueid
            else if (ereg("^IN\-$extension\-([[:digit:]]+(\.[[:digit:]]+)*)\.[wav|WAV|gsm]",$archivo,$regs)){
                exec("echo 'B' > /tmp/oscar");
                $llamada_incoming = true;
                $unique_id=$regs[1];
                $llamada=$this->obtenerCDR_with_uniqueid($pDBCDR,$unique_id, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "auto - incoming";
                    $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }
            //buscar llamadas incoming IN-extension-fecha-hora
            else if (!$llamada_incoming && ereg("^IN\-$extension\-([[:digit:]]+)\-([[:digit:]]+)\.[wav|WAV|gsm]",$archivo,$regs)){
                exec("echo 'C' > /tmp/oscar");
                //formar la fecha y la hora
                $fecha=substr($regs[1], 0, 4).'-'.substr($regs[1], 4, 2).'-'.substr($regs[1], 6, 2);
                $hora=substr($regs[2], 0, 2).':'.substr($regs[2], 2, 2).':'.substr($regs[2], 4, 2);
                $calldate="$fecha $hora";
                //busco por fecha y extension destino
                //ya tengo el archivo, busco el correspondiente en el registro de llamadas - con el timestamp y la extension
                $llamada=$this->obtenerCDRIncoming($pDBCDR,$extension, $calldate, $esAdministrador, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo']=$archivo;
                    $llamada['type'] = "auto - incoming";
                    $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }

            //g1-1207292249.1473.wav
            else if (!$llamada_incoming && ereg("^g$extension-([[:digit:]]+\.[[:digit:]]+)\.[wav|WAV|gsm]",$archivo,$regs))
            {
                exec("echo 'D' > /tmp/oscar");
                $unique_id=$regs[1];
                $llamada=$this->obtenerCDR_with_uniqueid($pDBCDR,$unique_id, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "always";
                    if($extension==$llamada['src'] || $extension==$llamada['dst'] || $extension=="[[:digit:]]+") //se se cumple esto es porque es el usuario solo puede ver sus llamadas y la otra es porque es administrador
                        $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }
            //g121-20070828-162421-1188336241.1610.wav
            else if (!$llamada_incoming && ereg("^g$extension-[[:digit:]]+-[[:digit:]]+-([[:digit:]]+\.[[:digit:]]+)\.[wav|WAV|gsm]",$archivo,$regs))
            {
                exec("echo 'E' > /tmp/oscar");
                $unique_id=$regs[1];
                //exec("echo 'unique_id: $unique_id' > /tmp/oscar");
                $llamada=$this->obtenerCDR_with_uniqueid($pDBCDR,$unique_id, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "always";
                    if($extension==$llamada['src'] || $extension==$llamada['dst'] || $extension=="[[:digit:]]+") //se se cumple esto es porque es el usuario solo puede ver sus llamadas y la otra es porque es administrador
                        $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }

             //buscar llamadas OUTGOING
             //OUT-ext-uniqueid.wav
            //OUT-104-1208782232.2382.wav 
            else if (ereg("^OUT\-$extension\-([[:digit:]]+(\.[[:digit:]]+)*)\.[wav|WAV|gsm]",$archivo,$regs)){
                exec("echo 'F' > /tmp/oscar");
                $llamada_outgoing = true;
                $unique_id=$regs[1];
                $llamada=$this->obtenerCDR_with_uniqueid($pDBCDR,$unique_id, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "auto - outgoing";
                    $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }
            //OUT405-20080620-095526-1213973725.84.wav
            else if (!$llamada_outgoing && ereg("^OUT$extension\-([[:digit:]]+)\-([[:digit:]]+)\-([[:digit:]]+)\.([[:digit:]]+)\.[wav|WAV|gsm]",$archivo,$regs))
            {
                exec("echo 'G' > /tmp/oscar");
                //formar la fecha y la hora
                $time = $regs[3];
                $calldate = date("Y-m-d H:i:s", $time);
                //busco por fecha y extension destino
                //ya tengo el archivo, busco el correspondiente en el registro de llamadas - con el timestamp y la extension
                $llamada=$this->obtenerCDROutgoing($pDBCDR,$extension, $calldate, $esAdministrador, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "auto - outgoing";
                    $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }
            else if (!$llamada_outgoing && ereg("^OUT$extension\-[(.+)|\-]*([[:digit:]]+)\-([[:digit:]]+)\.[wav|WAV|gsm]",$archivo,$regs))
            {
                exec("echo 'H' > /tmp/oscar");
                //formar la fecha y la hora
                $fecha=substr($regs[1], 0, 4).'-'.substr($regs[1], 4, 2).'-'.substr($regs[1], 6, 2);
                $hora=substr($regs[2], 0, 2).':'.substr($regs[2], 2, 2).':'.substr($regs[2], 4, 2);
                $calldate="$fecha $hora";
                //busco por fecha y extension destino
                //ya tengo el archivo, busco el correspondiente en el registro de llamadas - con el timestamp y la extension
                $llamada=$this->obtenerCDROutgoing($pDBCDR,$extension, $calldate, $esAdministrador, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "auto - outgoing";
                    $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }

            /****PARA LAS COLAS****/
            //q7000-20080411-180242-1207954962.473.wav
            else if($esAdministrador && ereg("^q[[:digit:]]+-[[:digit:]]+-[[:digit:]]+-([[:digit:]]+\.[[:digit:]]+)\.[wav|WAV|gsm]",$archivo,$regs))
            {
                exec("echo 'I' > /tmp/oscar");
                $unique_id=$regs[1];
                $llamada=$this->obtenerCDR_with_uniqueid($pDBCDR,$unique_id, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "queue - total";
                    $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }
            //q7000-20080411-162833-1207949313.9-in.wav
            else if($esAdministrador && ereg("^q[[:digit:]]+-[[:digit:]]+-[[:digit:]]+-([[:digit:]]+\.[[:digit:]]+)-in\.[wav|WAV|gsm]",$archivo,$regs))
            {
                exec("echo 'J' > /tmp/oscar");
                $unique_id=$regs[1];
                $llamada=$this->obtenerCDR_with_uniqueid($pDBCDR,$unique_id, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "queue - in";
                    $llamadas[strtotime($llamada['calldate'])."-in"]=$llamada;
                }
            }
            //q7000-20080411-162833-1207949313.9-out.wav
            else if($esAdministrador && ereg("^q[[:digit:]]+-[[:digit:]]+-[[:digit:]]+-([[:digit:]]+\.[[:digit:]]+)-out\.[wav|WAV|gsm]",$archivo,$regs))
            {
                exec("echo 'K' > /tmp/oscar");
                $unique_id=$regs[1];
                $llamada=$this->obtenerCDR_with_uniqueid($pDBCDR,$unique_id, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "queue - out";
                    $llamadas[strtotime($llamada['calldate'])."-out"]=$llamada;
                }
            }


             // El caso para cuando a la extension se le configuró sus records incoming or outgoing a always 
            else if (ereg("[[:digit:]]+\-[[:digit:]]+\-([[:digit:]]+.[[:digit:]]+).[wav|WAV|gsm]",$archivo,$regs)){
                exec("echo 'L' > /tmp/oscar");
                $unique_id = $regs[1];
                
                $llamada = $this->obtenerCDR_with_uniqueid($pDBCDR,$unique_id, $origen, $destino);
                if(count($llamada)>0){
                    $llamada['archivo'] = $archivo;
                    $llamada['type'] = "always";
                    if($extension==$llamada['src'] || $extension==$llamada['dst'] || $extension=="[[:digit:]]+") //se se cumple esto es porque es el usuario solo puede ver sus llamadas y la otra es porque es administrador
                        $llamadas[strtotime($llamada['calldate'])]=$llamada;
                }
            }
        }
        return $llamadas;
    }


    function obtenerCDROnDemand($db, $extension, $start_time, $esAdministrador, $origen, $destino)
    {
        $arr_result = array();
        $query  = "SELECT calldate, src, dst, channel, dstchannel, disposition, uniqueid, duration, billsec, accountcode FROM cdr ";
        $query .= "WHERE $start_time BETWEEN UNIX_TIMESTAMP(calldate) AND (UNIX_TIMESTAMP(calldate)+duration)";
        if(!$esAdministrador)
            $query .= " AND (src='$extension' OR dst='$extension')";
        else if(!empty($origen) || !empty($destino))
            $query .= " AND (src='$origen' and dst='$destino')";
            
    
        $arr_result=$db->getFirstRowQuery($query,TRUE);
        if (is_array($arr_result) && count($arr_result)>0) {
        }
        return $arr_result;
    }

    function obtenerCDRIncoming($db,$extension, $calldate, $esAdministrador, $origen, $destino)
    {
        $arr_result=array();
        $query   = "SELECT calldate, src, dst, channel, dstchannel, disposition, uniqueid, duration, billsec, accountcode FROM cdr ";
        $query .= "WHERE calldate='$calldate'";
        if(!$esAdministrador)
            $query .= " AND dst='$extension'";
        else if(!empty($origen) || !empty($destino))
            $query .= " AND (src='$origen' and dst='$destino')";
    
        $arr_result=$db->getFirstRowQuery($query,TRUE);
        if (is_array($arr_result) && count($arr_result)>0) {
        }
        return $arr_result;
    }

    function obtenerCDROutgoing($db,$extension, $calldate, $esAdministrador, $origen, $destino)
    {
        $arr_result=array();
        $query  = "SELECT calldate, src, dst, channel, dstchannel, disposition, uniqueid, duration, billsec, accountcode FROM cdr ";
        $query .= "WHERE calldate='$calldate'";
        if(!$esAdministrador)
            $query .= " AND src='$extension'";
        else if(!empty($origen) || !empty($destino))
            $query .= " AND (src='$origen' and dst='$destino')";
    
        $arr_result=$db->getFirstRowQuery($query,TRUE);
        if (is_array($arr_result) && count($arr_result)>0) {
        }
        return $arr_result;
    }

    function obtenerCDR_with_uniqueid($db,$uniqueid, $origen, $destino)
    {
        $arr_result=array();
        $query  = "SELECT calldate, src, dst, channel, dstchannel, disposition, uniqueid, duration, billsec, accountcode FROM cdr ";
        $query .= "WHERE uniqueid='$uniqueid'";
        if(!empty($origen) || !empty($destino))
            $query .= " AND (src='$origen' and dst='$destino')";
        //exec("echo '$query' > /tmp/oscar");
    
        $arr_result=$db->getFirstRowQuery($query,TRUE);
        if (is_array($arr_result) && count($arr_result)>0) {
        }
        return $arr_result;
    }




    function Files_Between_Dates($file, $extension, $date_start, $date_end, $esAdministrador)
    {
        //Se obtiene la fecha por timestamp
        //este valor es siempre unico generalmente lleva adjunto un id
        $fecha = 0;
        if (ereg("^auto\-([[:digit:]]+)\-$extension(.+)\.[wav|WAV|gsm]",$file,$regs))
            $fecha = $regs[1];
    
        /****llamadas incoming IN-extension-uniqueid****/
        //IN-100-1207645055.197.wav
        else if (ereg("^IN\-$extension\-([[:digit:]]+)[\.[[:digit:]]+]*\.[wav|WAV|gsm]",$file,$regs))
            $fecha = $regs[1];
        else if (ereg("^IN\-$extension\-([[:digit:]]+)\-([[:digit:]]+)\.[wav|WAV|gsm]",$file,$regs)){
            //formar la fecha y la hora
            $fecha=substr($regs[1], 0, 4).'-'.substr($regs[1], 4, 2).'-'.substr($regs[1], 6, 2);
            $hora=substr($regs[2], 0, 2).':'.substr($regs[2], 2, 2).':'.substr($regs[2], 4, 2);
            $calldate="$fecha $hora";
            $fecha = strtotime($calldate);
        }
    
        //if (ereg("g$extension\-([[:digit:]]+)\-([[:digit:]]+)(.+)\.[wav|WAV|gsm]",$file,$regs))
        //g1-1207292249.1473.wav
        else if (ereg("^g$extension-([[:digit:]]+)\.[[:digit:]]+\.[wav|WAV|gsm]",$file,$regs))
            $fecha = $regs[1];
        //g121-20070828-162421-1188336241.1610.wav
        else if (ereg("^g$extension-[[:digit:]]+-[[:digit:]]+-([[:digit:]]+)\.[[:digit:]]+\.[wav|WAV|gsm]",$file,$regs))
            $fecha = $regs[1];
    
        /****llamadas incoming IN-extension-uniqueid****/
    
        /****llamadas OUTGOING OUT-extension-uniqueid****/
        //OUT-504-1207151691.420.wav
        else if (ereg("^OUT\-$extension\-([[:digit:]]+)[\.[[:digit:]]+]*\.[wav|WAV|gsm]",$file,$regs))
            $fecha = $regs[1];
        //OUT504-20080402-133229-1207161149.873.wav
        else if (ereg("^OUT$extension\-([[:digit:]]+)\-([[:digit:]]+)\-([[:digit:]]+)\.([[:digit:]]+)\.[wav|WAV|gsm]",$file,$regs))
            $fecha = $regs[3];
        else if (ereg("^OUT$extension\-[(.+)|\-]*([[:digit:]]+)\-([[:digit:]]+)\.[wav|WAV|gsm]",$file,$regs))
        {
            //formar la fecha y la hora
            $fecha=substr($regs[1], 0, 4).'-'.substr($regs[1], 4, 2).'-'.substr($regs[1], 6, 2);
            $hora=substr($regs[2], 0, 2).':'.substr($regs[2], 2, 2).':'.substr($regs[2], 4, 2);
            $calldate="$fecha $hora";
            $fecha = strtotime($calldate);
        }
        /****llamadas OUTGOING OUT-extension-uniqueid****/
    
        /****Colas****/
        //q7000-20080411-180242-1207954962.473.wav
        //q7000-20080411-162833-1207949313.9-in.wav
        //q7000-20080411-162833-1207949313.9-out.wav
        else if($esAdministrador && ereg("^q[[:digit:]]+-[[:digit:]]+-[[:digit:]]+-([[:digit:]]+)\.[[[:digit:]]+|[[:digit:]]+-in|[[:digit:]]+-out]\.[wav|WAV|gsm]",$file,$regs))
                $fecha = $regs[1];
        /****Colas****/
    
        //El caso para cuando a la extension se le configuró sus records incoming or outgoing a always
        else if (ereg("^[[:digit:]]+\-[[:digit:]]+\-([[:digit:]]+).[[:digit:]]+.[wav|WAV|gsm]",$file,$regs))
            $fecha = $regs[1];
    
        //COMPARAR LAS FECHAS
        if ($fecha<=strtotime($date_end) && $fecha>=strtotime($date_start))
            return $fecha;
    
        return false;
    }

}


?>