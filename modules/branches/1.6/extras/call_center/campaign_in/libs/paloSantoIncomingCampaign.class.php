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
  $Id: paloSantoCampaignCC.class.php,v 1.2 2008/06/06 07:15:07 cbarcos Exp $ */

include_once("libs/paloSantoDB.class.php");


class paloSantoIncomingCampaign
{
    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloSantoIncomingCampaign(&$pDB)
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
    
    function delete_campaign($id_campaign) {}
    function activar_campaign($id_campaign) {}

    function countCampaigns($sEstado = 'all')
    {
        $listaWhere = array();
        $paramSQL = array();

        // Verificación de estado para filtrar
        switch ($sEstado) {
        case 'all':
            break;
        case 'A':
        case 'I':
            $listaWhere[] = 'estatus = ?';
            $paramSQL[] = $sEstado;
            break;
        default:
            $this->errMsg = '(internal) invalid filter state, must be [all A I]';
            return NULL;
        }
        $tupla = $this->_DB->getFirstRowQuery(
            'SELECT COUNT(*) AS N FROM campaign_entry'.((count($listaWhere) > 0) ? ' WHERE '.implode(' AND ', $listaWhere) : ''), 
            TRUE, $paramSQL);
        if (!is_array($tupla)) {
            $this->errMsg = $this->_DB->errMsg;
        	return NULL;
        }
        return $tupla['N'];
    }

    function getCampaigns($limit, $offset, $id_campaign, $sEstado = 'all') 
    {
        $listaWhere = array('ce.id_queue_call_entry = qce.id');
        $paramSQL = array();
        
        // Verificación de estado para filtrar
        switch ($sEstado) {
        case 'all':
            break;
        case 'A':
        case 'I':
            $listaWhere[] = 'ce.estatus = ?';
            $paramSQL[] = $sEstado;
            break;
        default:
            $this->errMsg = '(internal) invalid filter state, must be [all A I]';
            return NULL;
        }

        // Verificación de ID de campaña
        if (!is_null($id_campaign)) {
            if (!ctype_digit($id_campaign)) {
                $this->errMsg = _tr("Campaign ID is not valid");
                return NULL;
            }
            $listaWhere[] = 'ce.id = ?';
            $paramSQL[] = $id_campaign;
        }

        // Construcción de la sentencia SQL
        $sWhere = implode(' AND ', $listaWhere);
        $sPeticionSQL = <<<SQL_CAMPANIAS
SELECT ce.id, ce.name, qce.queue, ce.datetime_init, ce.datetime_end, 
    ce.daytime_init, ce.daytime_end, ce.script, 
    COUNT(call_entry.id) AS num_completadas, NULL as promedio, ce.estatus 
FROM (campaign_entry ce, queue_call_entry qce) 
LEFT JOIN (call_entry) ON (ce.id = call_entry.id_campaign) 
WHERE $sWhere GROUP BY ce.id ORDER BY ce.datetime_init, ce.daytime_init
SQL_CAMPANIAS;
        if (!is_null($limit)) {
            $sPeticionSQL .= ' LIMIT ? OFFSET ?';
            array_push($paramSQL, $limit, $offset);
        }

        $recordset =& $this->_DB->fetchTable($sPeticionSQL, true, $paramSQL);
        if (!is_array($recordset)) {
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        return $recordset;
    }
}
?>