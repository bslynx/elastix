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

class paloSantoTiempoConexiondeAgentes
{
    private $_DB;
    var $errMsg;

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
    
    /**
     * Procedimiento para reportar la información de los breaks y llamadas 
     * atendidas por un agente específico. Actualmente sólo se implementa 
     * reporte de las llamadas entrantes.
     *
     * @param   string  $sNumAgente     Número del agente (ej. 8000 para Agent/8000)
     * @param   string  $sNumCol        Número de la cola
     * @param   string  $sFechaInicial  Fecha inicial en formato YYYY-MM-DD
     * @param   string  $sFechaFinal    Fecha final en formato YYYY-MM-DD
     *
     * @return  mixed   NULL en caso de error, o arreglo con la siguiente info:
     */
    function reportarBreaksAgente($sNumAgente, $sNumCola, $sFechaInicial, $sFechaFinal)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sFechaInicial)) {
            $this->errMsg = 'Invalid start date';
            return NULL;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sFechaFinal)) {
            $this->errMsg = 'Invalid end date';
            return NULL;
        }
        if (!preg_match('/^\d+$/', $sNumAgente)) {
            $this->errMsg = 'Invalid agent number';
            return NULL;
        }
        if (!preg_match('/^\d+$/', $sNumCola)) {
            $this->errMsg = 'Invalid queue number';
            return NULL;
        }
        $infoAgente = array();
        $sFechaInicial .= ' 00:00:00';
        $sFechaFinal .= ' 23:59:59';

        // Reporte de las conexiones
        $sPeticionSQL = <<<SQL_REPORTE_CONEXIONES
SELECT MIN(audit.datetime_init) AS primera_conexion, 
    MAX(audit.datetime_end) AS ultima_conexion, 
	SUM(TIME_TO_SEC(audit.duration)) AS tiempo_conexion, 
	COUNT(*) AS conteo_conexion, agent.name, agent.number 
FROM audit, agent 
WHERE audit.id_agent = agent.id AND agent.estatus = "A" 
    AND audit.id_break IS NULL AND agent.number = ? 
    AND audit.datetime_init BETWEEN ? AND ?
GROUP BY agent.number
SQL_REPORTE_CONEXIONES;
        $tupla = $this->_DB->getFirstRowQuery($sPeticionSQL, TRUE, array($sNumAgente, $sFechaInicial, $sFechaFinal));
        if (!is_array($tupla)) {
            $this->errMsg = 'Failed to fetch connection summary - '.$this->_DB->errMsg;
            return NULL;
        }
        $infoAgente = $tupla;

        // Reporte de tiempos en cada estado
        $sPeticionSQL = <<<SQL_REPORTE_TIEMPOS
SELECT call_entry.status, COUNT(*) AS N, 
    SUM(call_entry.duration) AS tiempo_llamadas_entrantes, 
    SEC_TO_TIME(AVG(duration)) AS promedio_sobre_monitoreadas 
FROM call_entry, queue_call_entry, agent 
WHERE call_entry.id_queue_call_entry = queue_call_entry.id 
    AND queue_call_entry.queue = ? AND call_entry.id_agent = agent.id 
    AND agent.estatus = "A" AND agent.number = ? 
    AND call_entry.datetime_entry_queue BETWEEN ? AND ?
GROUP BY call_entry.status, agent.number
SQL_REPORTE_TIEMPOS;
        $recordset = $this->_DB->fetchTable($sPeticionSQL, TRUE, array($sNumCola, $sNumAgente, $sFechaInicial, $sFechaFinal));
        if (!is_array($recordset)) {
            $this->errMsg = 'Failed to fetch time summary - '.$this->_DB->errMsg;
            return NULL;
        }
        $infoAgente['tiempos_llamadas'] = $recordset;

        // Reporte de breaks
        $sPeticionSQL = <<<SQL_REPORTE_BREAKS
SELECT break.id, break.name, COUNT(*) AS N, 
    SUM(TIME_TO_SEC(audit.duration)) AS total_break
FROM audit, agent, break 
WHERE audit.id_agent = agent.id AND agent.estatus = "A" 
    AND audit.id_break = break.id AND agent.number = ?
    AND audit.datetime_init BETWEEN ? AND ?
GROUP BY break.id ORDER BY break.name
SQL_REPORTE_BREAKS;
        $recordset = $this->_DB->fetchTable($sPeticionSQL, TRUE, array($sNumAgente, $sFechaInicial, $sFechaFinal));
        if (!is_array($recordset)) {
            $this->errMsg = 'Failed to fetch break summary - '.$this->_DB->errMsg;
            return NULL;
        }
        $infoAgente['tiempos_breaks'] = $recordset;

        return $infoAgente;
    }

    function Obtainrep_tiempoConexionAgentes($dummy1, $dummy2, $dummy3, $dummy4, $idCola, $sNumAgente, $sFechaInicio, $sFechaFin, $dummy5 = NULL, $dummy6 = NULL)
    {
        // Obtener la cola dado el ID (temporal)
        $sql = 'SELECT queue FROM queue_call_entry WHERE id = ?';
        $tupla = $this->_DB->getFirstRowQuery($sql, TRUE, array($idCola));
        $sNumCola = $tupla['queue'];
        // Fechas sin hora (temporal)
        $sFechaInicio = substr($sFechaInicio, 0, 10);
        $sFechaFin = substr($sFechaFin, 0, 10);

        $r = $this->reportarBreaksAgente($sNumAgente, $sNumCola, $sFechaInicio, $sFechaFin);
        $reporte = array();

        $reporte['Data'][] = array(
            $r['primera_conexion'],
            $r['ultima_conexion'],
            $this->_formatoHora($r['tiempo_conexion']),
            $r['conteo_conexion'],
            $r['name'],
        );
        $tempTiempos = array(
            0,  // número de llamadas monitoreadas (estatus 'terminada')
            0,  // número de llamadas por hora (de todos los estados)
            0,  // duración de todas las llamadas entrantes (cualquier estado)
            0,  // promedio de duración (estatus 'terminada')
            0,  // número de llamadas (todos los estados)
        );
        foreach ($r['tiempos_llamadas'] as $tupla) {
            $tempTiempos[1] = $tempTiempos[4] += $tupla['N'];
            $tempTiempos[2] += $tupla['tiempo_llamadas_entrantes'];
            if ($tupla['status'] == 'terminada') {
                $tempTiempos[0] = $tupla['N'];
                $tempTiempos[3] = $tupla['promedio_sobre_monitoreadas'];
            }
        }
        $tempTiempos[2] = $this->_formatoHora($tempTiempos[2]);
        $tempTiempos[1] /= $r['tiempo_conexion'] / 3600;
        $reporte['Data'][] = $tempTiempos;
        
        $tempBreaks = array();
        $iTotalSeg = 0;
        foreach ($r['tiempos_breaks'] as $tupla) {
            $tempBreaks[] = array(
                $tupla['name'],
                $tupla['N'],
                $this->_formatoHora($tupla['total_break']),
                $tupla['total_break'],
                ''
            );
            $iTotalSeg += $tupla['total_break'];
        }
        for ($i = 0; $i < count($tempBreaks); $i++) {
            $tempBreaks[$i][3] = 100.0 * ($tempBreaks[$i][3] / $iTotalSeg);
            $reporte['Data'][] = $tempBreaks[$i];
        }
        
        $reporte['NumRecords'] = count($reporte['Data']);

        return $reporte;
    }

    private function _formatoHora($s)
    {
        $sec = $s % 60; $s = ($s - $sec) / 60;
        $min = $s % 60; $hora = ($s - $min) / 60;
        return sprintf('%02d:%02d:%02d', $hora, $min, $sec);
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
