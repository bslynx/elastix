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

class paloSantoCallsAgent {

    function paloSantoCallsAgent(&$pDB)
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
    
    function obtenerCallsAgent($limit, $offset, $date_start="", $date_end="", $field_name, $field_pattern,$status="ALL",$calltype="",$troncales=NULL)
    {
        $sqlQuery = "";
        $strWhereCalls = "";
        $strWheredateCallEn = "";
        $field_name_1 = $field_name['field_name'];
        $field_name_2 = $field_name['field_name_1'];
        $field_pattern_1 = $field_pattern['field_pattern'];
        $field_pattern_2 = $field_pattern['field_pattern_1'];
     
        
        //Campos diferentes en tablas 
        if(!empty($date_start)){
            $strWhereCalls .= "AND cal.start_time>='$date_start' ";
            $strWheredateCallEn .= " AND cale.datetime_init>='$date_start' ";
        }
        if(!empty($date_end)){
           $strWhereCalls .= " AND cal.end_time<='$date_end' ";
            $strWheredateCallEn .= " AND cale.datetime_end<='$date_end' ";
        }
        
        $this->construirCondicion($field_name_1,$field_pattern_1,$strWhereCalls,$strWheredateCallEn);
        $this->construirCondicion($field_name_2,$field_pattern_2,$strWhereCalls,$strWheredateCallEn);

        //if(!empty($status) && $status!="ALL") $strWhere .= " AND disposition = '$status' ";

        $sqlQueryCalls = "select age.number,age.name,'Inbound' as type,cam.queue,count(*)            calls_answ ,sec_to_time(sum(duration)),                                                  sec_to_time(avg(duration)),sec_to_time(max(duration))
            from calls cal
            inner join campaign cam on cam.id=cal.id_campaign
            inner join agent age on age.id=cal.id_agent
            where status='Success'";
        if(!empty($strWhereCalls)) $sqlQueryCalls .= " $strWhereCalls group by age.number"; 
         $sqlQueryCallEn = "select age.number,age.name,'Outbound' as type,que.queue,count(*)        calls_answ, sec_to_time(sum(duration)),                                                 sec_to_time(avg(duration)),sec_to_time(max(duration))
            from call_entry cale
            inner join agent age on age.id=cale.id_agent 
            inner join queue_call_entry que on que.id=cale.id_queue_call_entry
            where status='terminada'";
        if(!empty($strWheredateCallEn)) $sqlQueryCallEn .= " $strWheredateCallEn group by age.number"; 
        
        if($field_name_1=="type" || $field_name_2=="type")
        {
            if($field_pattern_1=="Inbound"|| $field_pattern_2=="Inbound"){   
                $sqlQuery .= $sqlQueryCalls;
            }else if($field_pattern_1=="Outbound"|| $field_pattern_2=="Outbound"){
                $sqlQuery .= $sqlQueryCallEn;
            }
        } else {
            $sqlQuery=$sqlQueryCalls." union ".$sqlQueryCallEn;
        }

        $sqlQuery .= " order by number";

        if(!empty($limit)) {
	        $sqlQuery  .= " LIMIT $limit OFFSET $offset";
        }

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
            }else if($field_name="number"){
                 $strWhereCalls .= " AND age.$field_name like '%$field_pattern%' ";
                $strWheredateCallEn .= " AND age.$field_name like '%$field_pattern%' ";
            }else if($field_name=="type"){}
            else{
                $strWhereCalls .= " AND cal.$field_name like '%$field_pattern%' ";
                $strWheredateCallEn .= " AND cale.$field_name like '%$field_pattern%' ";
            }
        }
    }
}
?>
