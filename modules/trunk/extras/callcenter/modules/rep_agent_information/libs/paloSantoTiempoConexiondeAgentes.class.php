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
  $Id: paloSantoTiempoConexiondeAgentes.class.php,v 1.1.1.1 2009/07/27 09:10:19 dlopez Exp $ */

class paloSantoTiempoConexiondeAgentes {

    function paloSantoTiempoConexiondeAgentes(&$pDB)
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
    
    function Obtainrep_tiempoConexionAgentes($limit, $offset, $arrLang, $filter_field, $filter_value, $filter_pattern, $date_start="", $date_end="",$calltype="",$troncales=NULL)
    {
        $n_filter = 0;
        $sql = "";
        $where1 = $where2 = "";

        //Campos diferentes en tablas 
        if(!empty($date_start)){
            $where2 .= " AND cale.datetime_entry_queue between '$date_start' ";
            $where1 .= " AND datetime_init between '$date_start' ";
        }
        if(!empty($date_end)){
            $where2 .= " AND '$date_end' ";
            $where1 .= " AND '$date_end' ";
        }


        $where1 .= " AND agent.number = '$filter_pattern' ";
        $where2 .= "  AND age.number = '$filter_pattern' AND cale.id_queue_call_entry = '$filter_value'  ";

        //Para Conexion
        $sql = 
        " select min(datetime_init) primera_conexion, 
        (select max(datetime_end) from audit au where au.datetime_init  between '$date_start' AND '$date_end' and au.id_agent=agent.id AND  au.id_break is null )  ultima_conexion, 
        sec_to_time(sum(TIME_TO_SEC(duration))) tiempo_conexion, count(*) conteo_conexion, agent.name as agent_name 
        from audit, agent where agent.id=audit.id_agent AND  id_break is null ";
        $sql .= " $where1 group by id_agent";

//le quitamos and duration is not null and 
//12/06/09 le quitamos el AND  id_break is null
//y le cambiamos  max(datetime_end) ultima_conexion por lo que tiene actualmente
//le habiamos puesto esto pero no sabemos si es factible
// if( (select count(*) from audit au where au.datetime_init  between '$date_start' AND '$date_end' and au.id_agent=agent.id and au.id_break is null and au.datetime_end is null) >'0', '-', max(audit.datetime_end) )  ultima_conexion

        //Para LLamadas entrantes
        $tiempo_conectado = $this->tiempo_de_conexion($where1);

        $sql .= " UNION 
                select 
                (select count(*) 
                from call_entry cale left join agent age on age.id=cale.id_agent where cale.status='terminada' $where2 group by age.number) as conteo_llamadas_monitoreadas,
 
                ( (count(*)) / hour(sec_to_time('$tiempo_conectado')) ) llamadas_hora, sec_to_time(sum(duration)) tiempo_llamadas_entrantes, 

                (select  sec_to_time(avg(cale.duration))
                from call_entry cale left join agent age on age.id=cale.id_agent where cale.status='terminada' $where2 group by age.number) as promedio_sobre_monitoreadas,

                count(*) conteo_llamadas_totales

            from call_entry cale
            left join agent age on age.id=cale.id_agent 
            where 1 ";
//sec_to_time(count(*)/sum(duration))
        $sql .= " $where2 group by age.number";


        //Para listado de Breaks
        $sql .=" UNION select break.name as Break, count(*) as conteo, sec_to_time(sum(TIME_TO_SEC(duration))) as Hora, 
        ( (sum(TIME_TO_SEC(duration)))  /  ( select sum(TIME_TO_SEC(duration)) porcentaje from audit,agent,break where audit.id_break=break.id and  agent.id=audit.id_agent $where1 group by agent.id )  )*100  as porcentaje, ''  
        from audit,agent,break where agent.id=audit.id_agent and audit.id_break=break.id";
        $sql .= " $where1 group by Break  "; 

        if(!empty($limit)) {
	        $sql  .= " LIMIT $limit OFFSET $offset";
        }
//echo $sql;
        $result=$this->_DB->fetchTable($sql);
        $arrResult['Data'] = $result;
        $arrResult['NumRecords'] = count($arrResult['Data']);

        return $arrResult;
    }


    function tiempo_de_conexion($where){
        $sql = "select (sum(TIME_TO_SEC(duration))) tiempo
        from audit, agent 
        where agent.id=audit.id_agent  and id_break is null ";
        if(!empty($where)) $sql .= " $where group by id_agent";
        $result=$this->_DB->getFirstRowQuery($sql, true);
        if(is_array($result) && count($result)>0){
            return $result['tiempo'];
        }
        else
            return;

    }

    function obtener_agente(){
        $sql = "select number from agent limit 1";
        $result=$this->_DB->getFirstRowQuery($sql, true);
        if(is_array($result) && count($result)>0)
            return $result['number'];
        else
            return;
    }

}
?>
