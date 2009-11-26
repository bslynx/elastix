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
  $Id: paloSantoCDR.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

class paloSantoCallsDetail {

    function paloSantoCallsDetail(&$pDB)
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
    
    function obtenerCallsDetails($limit, $offset, $date_start="", $date_end="", $field_name, $field_pattern,/*$status="ALL",*/$calltype="",$troncales=NULL)
    {   
        $n_field = 0;
        $sqlQuery = "";
        $strWhereCalls = "";
        $strWheredateCallEn = "";
        if(!isset($field_name['field_name']))
            $field_name['field_name'] = "";
        $field_name_1 = $field_name['field_name'];
        if(!isset($field_name['field_name_1']))
            $field_name['field_name_1'] = "";
        $field_name_2 = $field_name['field_name_1'];

        if(!isset($field_pattern['field_pattern']))
            $field_pattern['field_pattern']="";
        if(!isset($field_pattern['field_pattern_1']))
            $field_pattern['field_pattern_1']="";

        $field_pattern_1 = strtoupper($field_pattern['field_pattern']);
        $field_pattern_2 = strtoupper($field_pattern['field_pattern_1']);
     
        
        //Campos diferentes en tablas 
        if(!empty($date_start)){
            $strWhereCalls .= "cal.fecha_llamada between '$date_start' ";
            $strWheredateCallEn .= "cale.datetime_entry_queue between '$date_start' ";
        }
        if(!empty($date_end)){
           $strWhereCalls .= "AND '$date_end' "; // " AND cal.end_time<='$date_end' ";
            $strWheredateCallEn .= " AND '$date_end' ";//" AND cale.datetime_end<='$date_end' ";
        }
       
        if(($field_name_1==$field_name_2)&&($field_pattern_1!="")&&($field_pattern_2!=""))
        { 
            $this->construirCondicionIguales($n_field,$field_name_1,$field_pattern_1,$strWhereCalls,$strWheredateCallEn);
            $this->construirCondicionIguales($n_field,$field_name_2,$field_pattern_2,$strWhereCalls,$strWheredateCallEn);
        } else {
            $this->construirCondicion($field_name_1,$field_pattern_1,$strWhereCalls,$strWheredateCallEn);
            $this->construirCondicion($field_name_2,$field_pattern_2,$strWhereCalls,$strWheredateCallEn);
        }

        //if(!empty($status) && $status!="ALL") $strWhere .= " AND disposition = '$status' ";


        $sqlQueryCalls = "select age.number,age.name, SUBSTR(start_time,1,10) start_date,SUBSTR(start_time,12,19) start_time,SUBSTR(end_time,1,10) end_date,SUBSTR(end_time,12,19) end_time, sec_to_time(duration),sec_to_time(duration_wait),cam.queue,'Outbound' as type, phone, transfer, cal.status
        from calls cal
        inner join campaign cam on cam.id=cal.id_campaign
        left join agent age on age.id=cal.id_agent";
        if(!empty($strWhereCalls)) $sqlQueryCalls .= " WHERE $strWhereCalls"; 
        $sqlQueryCallEn = "select age.number,age.name, SUBSTR(datetime_init,1,10) start_date,SUBSTR(datetime_init,12,19) start_time,SUBSTR(datetime_end,1,10) end_date,SUBSTR(datetime_end,12,19) end_time,sec_to_time(duration),sec_to_time(duration_wait),que.queue,'Inbound' as type,IF(con.telefono is NULL,cale.callerid,con.telefono) as telefono, transfer, cale.status
        from call_entry cale
        left join contact con on con.id=cale.id_contact
        left join agent age on age.id=cale.id_agent
        inner join queue_call_entry que on que.id=cale.id_queue_call_entry";
        if(!empty($strWheredateCallEn)) $sqlQueryCallEn .= " WHERE $strWheredateCallEn"; 
        
        if($field_name_1=="type")
        {
            if($field_pattern_1=="INBOUND" || $field_pattern_1=="IN"){   
                $sqlQuery .= $sqlQueryCallEn;
            }else if($field_pattern_1=="OUTBOUND"|| $field_pattern_1=="OUT"){
                $sqlQuery .= $sqlQueryCalls;
            }else if($field_pattern_1==""){
                $sqlQuery=$sqlQueryCalls." union ".$sqlQueryCallEn;
            }else $sqlQuery=$sqlQueryCalls." union ".$sqlQueryCallEn;
        } 
        if($field_name_2=="type"){
            if($field_pattern_2=="INBOUND" || $field_pattern_2=="IN"){   
                $sqlQuery .= $sqlQueryCallEn;
            }else if($field_pattern_2=="OUTBOUND" || $field_pattern_2=="OUT"){
                $sqlQuery .= $sqlQueryCalls;
            }else if($field_pattern_2==""){
                $sqlQuery=$sqlQueryCalls." union ".$sqlQueryCallEn;
            } else $sqlQuery=$sqlQueryCalls." union ".$sqlQueryCallEn;
        } 
        if($field_name_1=="type" && $field_name_2=="type"){
            $sqlQuery=$sqlQueryCalls." union ".$sqlQueryCallEn;
        }
        if($field_name_1!="type" && $field_name_2!="type"){
            $sqlQuery=$sqlQueryCalls." union ".$sqlQueryCallEn;
        }
        $sqlQuery .= "order by start_time";

        if(!empty($limit)) {
	        $sqlQuery  .= " LIMIT $limit OFFSET $offset";
        }
        //echo $sqlQuery;
        $result=$this->_DB->fetchTable($sqlQuery);
        $arrResult['Data'] = $result;

	$arrResult['NumRecords'] = count($arrResult['Data']);

        return $arrResult;
    }
    function construirCondicion($field_name,$field_pattern, &$strWhereCalls, &$strWheredateCallEn )
    {
        if(!empty($field_name) and !empty($field_pattern)){
            if ($field_name=="queue") {
                $strWhereCalls .= " AND cam.$field_name like '%$field_pattern%' ";
                $strWheredateCallEn .= " AND que.$field_name like '%$field_pattern%' ";
            }else if($field_name=="phone"){
                $strWhereCalls .= " AND cal.$field_name like '%$field_pattern%' ";
                $strWheredateCallEn .= " AND con.telefono like '%$field_pattern%' ";
            }else if($field_name=="number"){
                 $strWhereCalls .= " AND age.$field_name like '%$field_pattern%' ";
                $strWheredateCallEn .= " AND age.$field_name like '%$field_pattern%' ";
            }else if($field_name=="type"){}
            else{
                $strWhereCalls .= " AND cal.$field_name like '%$field_pattern%' ";
                $strWheredateCallEn .= " AND cale.$field_name like '%$field_pattern%' ";
            }
        }
    }
    function construirCondicionIguales(&$n_field,$field_name,$field_pattern, &$strWhereCalls, &$strWheredateCallEn )
    {
        //quite la condicion para q si uno no pone nada! salga la info
        if(!empty($field_name) /*and !empty($field_pattern)*/){
            if ($field_name=="queue") {
                if($n_field==0){
                    $strWhereCalls .= " AND (cam.$field_name like '%$field_pattern%' ";
                    $strWheredateCallEn .= " AND (que.$field_name like '%$field_pattern%' ";
                    $n_field++;
                 } else {
                    $strWhereCalls .= " OR cam.$field_name like '%$field_pattern%') ";
                    $strWheredateCallEn .= " OR que.$field_name like '%$field_pattern%') ";
                    $n_field=0;
                 }
            }else if($field_name=="phone"){
                if($n_field==0){
                    $strWhereCalls .= " AND (cal.$field_name like '%$field_pattern%' ";
                    $strWheredateCallEn .= " AND (con.telefono like '%$field_pattern%' ";
                    $n_field++;
                } else {
                    $strWhereCalls .= " OR cal.$field_name like '%$field_pattern%') ";
                    $strWheredateCallEn .= " OR con.telefono like '%$field_pattern%') ";
                    $n_field=0;
                }
            }else if($field_name=="number"){
                if($n_field==0){
                    $strWhereCalls .= " AND (age.$field_name like '%$field_pattern%' ";
                    $strWheredateCallEn .= " AND (age.$field_name like '%$field_pattern%' ";
                    $n_field++;
                } else {
                    $strWhereCalls .= " OR age.$field_name like '%$field_pattern%') ";
                    $strWheredateCallEn .= " OR age.$field_name like '%$field_pattern%') ";
                    $n_field=0;
                }
            }else if($field_name=="type"){}
            else{
                if($n_field==0){
                    $strWhereCalls .= " AND (cal.$field_name like '%$field_pattern%' ";
                    $strWheredateCallEn .= " AND (cale.$field_name like '%$field_pattern%' ";
                    $n_field++;
                } else {
                    $strWhereCalls .= " OR cal.$field_name like '%$field_pattern%') ";
                    $strWheredateCallEn .= " OR cale.$field_name like '%$field_pattern%') ";
                    $n_field=0;
                }
            }
        }
    }
    /*
        Esta funcion recibe un arreglo con los tiempos de break del mismo tipo y retorna
        el tiempo total que el agente ha estado en este break.
    */
    function sumarTiempos($arrTime) {

        if(count($arrTime)==1) {
            if( is_null($arrTime[0]['duration'] ) ) {
                return "00:00:00";
            }
            return $arrTime[0]['duration'];
        }elseif(count($arrTime)==2) {
            if( is_null($arrTime[0]['duration']) ) {
                $arrTime[0]['duration'] = "00:00:00";
            }
            if( is_null($arrTime[1]['duration']) ) {
                $arrTime[1]['duration'] = "00:00:00";
            }
            $SQLConsulta = "select addtime('".$arrTime[0]['duration']."','".$arrTime[1]['duration']."') duracion";
            $resConsulta = $this->_DB->fetchTable($SQLConsulta,true);

            if(!$resConsulta)  {
                $this->msgError = $this->errMsg;
                return false;
            } else {
                 return $resConsulta[0]['duracion'];
            }
 
        }elseif(count($arrTime)>2) {
            if( is_null($arrTime[0]['duration']) ) {
                $arrTime[0]['duration'] = "00:00:00";
            }
            if( is_null($arrTime[1]['duration']) ) {
                $arrTime[1]['duration'] = "00:00:00";
            }
            $SQLConsulta = "select addtime('".$arrTime[0]['duration']."','".$arrTime[1]['duration']."') duracion";
            $resConsulta = $this->_DB->fetchTable($SQLConsulta,true);

            if(!$resConsulta)  {
                $this->msgError = $this->errMsg;
                return false;
            }else {
                $valorTime =$resConsulta[0]['duracion'];

                for($i =2 ;$i<count($arrTime) ; $i++) {
                    if( !is_null($arrTime[$i]['duration']) ) {
                        $SQLConsulta = "select addtime('".$valorTime."','".$arrTime[$i]['duration']."') duracion";
                        $resConsulta = $this->_DB->fetchTable($SQLConsulta,true);

                        if(!$resConsulta)  {
                            return false;
                        }else {
                            $valorTime =$resConsulta[0]['duracion'];
                        }
                    }
                }
                return $valorTime;
            }
        }
    }
}
?>
