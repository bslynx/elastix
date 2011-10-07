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
    
    function activar_campaign($id_campaign, $sNuevoEstado)
    {
    	if (!in_array($sNuevoEstado, array('A', 'I'))) {
            $this->errMsg = '(internal) Invalid new state for campaign';
    		return FALSE;
    	}
        $r = $this->_DB->genQuery(
            'UPDATE campaign_entry SET estatus = ? WHERE id = ?', 
            array($sNuevoEstado, $id_campaign));
        if (!$r) {
        	$this->errMsg = $this->_DB->errMsg;
        }
        return $r;
    }

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

    function delete_campaign($id_campaign) 
    {
    	$tupla =& $this->_DB->getFirstRowQuery(
            'SELECT COUNT(id) llamadas_realizadas FROM call_entry WHERE id_campaign = ?',
            true, array($id_campaign));
        if (!is_array($tupla)) {
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        if ($tupla['llamadas_realizadas'] > 0) {
            $this->errMsg = _tr('This campaign has already received calls');
        	return FALSE;
        }
        // TODO: si se implementan múltiples formularios, borrar aquí asociación
        // TODO: si se implementan contactos por campaña, borrar aquí
        $r = $this->_DB->genQuery('DELETE FROM campaign_entry WHERE id = ?', array($id_campaign));
        if (!$r) {
        	$this->errMsg = $this->_DB->errMsg;
        }
        return $r;
    }

    /**
     * Procedimiento para leer la totalidad de los datos de una campaña terminada, 
     * incluyendo todos los datos recogidos en los diversos formularios asociados.
     *
     * @param   object  $pDB            Conexión paloDB a la base de datos call_center
     * @param   int     $id_campaign    ID de la campaña a recuperar
     * @param(out) string $errMsg       Mensaje de error
     *
     * @return  NULL en caso de error, o una estructura de la siguiente forma:
    array(
        BASE => array(
            LABEL   =>  array(
                "id_call",
                "Phone Customer"
                ...
            ),
            DATA    =>  array(
                array(...),
                array(...),
                ...
            ),
        ),
        FORMS => array(
            {id_form} => array(
                NAME    =>  'TestForm',
                LABEL   =>  array(
                    "Label A",
                    "Label B"
                    ...
                ),
                DATA    =>  array(
                    {id_call} => array(...),
                    {id_call} => array(...),
                    ...
                ),
            ),
            ...
        ),
    )
     */
    function & getCompletedCampaignData($id_campaign)
    {

        $this->errMsg = NULL;

        $sqlLlamadas = <<<SQL_LLAMADAS
SELECT
    c.id                AS id,
    c.callerid          AS telefono,
    c.status            AS estado,
    a.number            AS number,
    IFNULL(c.datetime_init, c.datetime_entry_queue) AS fecha_hora,
    c.duration          AS duracion,
    c.uniqueid          AS uniqueid
FROM call_entry c
LEFT JOIN agent a 
    ON c.id_agent = a.id
WHERE
    c.id_campaign = ? AND
    (c.status='terminada' OR c.status='abandonada')
ORDER BY
    fecha_hora ASC
SQL_LLAMADAS;

        $datosCampania = NULL;
        $datosTelefonos = $this->_DB->fetchTable($sqlLlamadas, FALSE, array($id_campaign));
        if (!is_array($datosTelefonos)) {
            $this->errMsg = 'Unable to read campaign phone data - '.$this->_DB->errMsg;
            return $datosCampania;
        }
        for ($i = 0; $i < count($datosTelefonos); $i++) {
        	if ($datosTelefonos[$i][2] == 'terminada')
                $datosTelefonos[$i][2] = 'Success';
            if ($datosTelefonos[$i][2] == 'abandonada')
                $datosTelefonos[$i][2] = 'Abandoned';
        }
        $datosCampania = array(
            'BASE'  =>  array(
                'LABEL' =>  array(
                    'id_call',
                    _tr('Phone Customer'),
                    _tr('Status Call'),
                    "Agente",
                    _tr('Date & Time'),
                    _tr('Duration'),
                    'Uniqueid',
                ),
                'DATA'  =>  $datosTelefonos,
            ),
            'FORMS' =>  array(),
        );
        $datosTelefonos = NULL;

        // Construir índice para obtener la posición de la llamada, dado su ID
        $datosCampania['BASE']['ID2POS'] = array();
        foreach ($datosCampania['BASE']['DATA'] as $pos => $tuplaTelefono) {
            $datosCampania['BASE']['ID2POS'][$tuplaTelefono[0]] = $pos;
        }

        // Leer los datos de los atributos de cada llamada
        $iOffsetAttr = count($datosCampania['BASE']['LABEL']);
        $sqlAtributos = <<<SQL_ATRIBUTOS
SELECT
    call_entry.id AS id_call,
    contact.cedula_ruc,
    contact.name,
    contact.apellido
FROM call_entry, contact
WHERE call_entry.id_contact = contact.id AND call_entry.id_campaign = ? 
    AND (call_entry.status='terminada' OR call_entry.status='abandonada')
SQL_ATRIBUTOS;
        $datosAtributos = $this->_DB->fetchTable($sqlAtributos, TRUE, array($id_campaign));
        if (!is_array($datosAtributos)) {
            $this->errMsg = 'Unable to read attribute data - '.$this->_DB->errMsg;
            $datosCampania = NULL;
            return $datosCampania;
        }
        $datosCampania['BASE']['LABEL'][$iOffsetAttr + 0] = 'Cedula/RUC';
        $datosCampania['BASE']['LABEL'][$iOffsetAttr + 1] = _tr('First Name');
        $datosCampania['BASE']['LABEL'][$iOffsetAttr + 2] = _tr('Last Name');
        for ($i = 0; $i < count($datosCampania['BASE']['DATA']); $i++) {
        	// Relleno para llamadas sin contacto
            $datosCampania['BASE']['DATA'][$i][$iOffsetAttr + 0] = NULL;
            $datosCampania['BASE']['DATA'][$i][$iOffsetAttr + 1] = NULL;
            $datosCampania['BASE']['DATA'][$i][$iOffsetAttr + 2] = NULL;
        }
        foreach ($datosAtributos as $tuplaAtributo) {
            $pos = $datosCampania['BASE']['ID2POS'][$tuplaAtributo['id_call']];
            $datosCampania['BASE']['DATA'][$pos][$iOffsetAttr + 0] = $tuplaAtributo['cedula_ruc'];
            $datosCampania['BASE']['DATA'][$pos][$iOffsetAttr + 1] = $tuplaAtributo['name'];
            $datosCampania['BASE']['DATA'][$pos][$iOffsetAttr + 2] = $tuplaAtributo['apellido'];
        }

        // Leer los datos de los formularios asociados a esta campaña
        $sqlFormularios = <<<SQL_FORMULARIOS
(SELECT 
    f.id        AS id_form,
    ff.id       AS id_form_field,
    ff.etiqueta AS campo_nombre,
    f.nombre    AS formulario_nombre,
    ff.orden    AS orden
FROM campaign_entry ce, form f, form_field ff
WHERE ce.id_form = f.id AND f.id = ff.id_form AND ff.tipo <> 'LABEL' AND ce.id = ?)
UNION DISTINCT
(SELECT DISTINCT
    f.id        AS id_form,
    ff.id       AS id_form_field,
    ff.etiqueta AS campo_nombre,
    f.nombre    AS formulario_nombre,
    ff.orden    AS orden
FROM form f, form_field ff, form_data_recolected_entry fdr, call_entry c
WHERE f.id = ff.id_form AND ff.tipo <> 'LABEL' AND fdr.id_form_field = ff.id AND fdr.id_call_entry = c.id AND c.id_campaign = ?)
ORDER BY id_form, orden ASC
SQL_FORMULARIOS;
        $datosFormularios = $this->_DB->fetchTable($sqlFormularios, FALSE, array($id_campaign, $id_campaign));
        if (!is_array($datosFormularios)) {
            $this->errMsg = 'Unable to read form data - '.$this->_DB->errMsg;
            $datosCampania = NULL;
            return $datosCampania;
        }
        foreach ($datosFormularios as $tuplaFormulario) {
            if (!isset($datosCampania['FORMS'][$tuplaFormulario[0]])) {
                $datosCampania['FORMS'][$tuplaFormulario[0]] = array(
                    'NAME'  =>  $tuplaFormulario[3],
                    'LABEL' =>  array(),
                    'DATA'  =>  array(),
                    'FF2POS'=>  array(),
                );
            }
            $datosCampania['FORMS'][$tuplaFormulario[0]]['LABEL'][] = $tuplaFormulario[2];

            // Construir índice para obtener posición/orden del campo de formulario, dado su ID.
            $datosCampania['FORMS'][$tuplaFormulario[0]]['FF2POS'][$tuplaFormulario[1]] = count($datosCampania['FORMS'][$tuplaFormulario[0]]['LABEL']) - 1;
        }
        $datosFormularios = NULL;

        // Leer los datos recolectados de los formularios
        $sqlDatosForm = <<<SQL_DATOS_FORM
SELECT
    c.id AS id_call,
    ff.id_form AS id_form,
    ff.id AS id_form_field,
    fdr.value AS campo_valor
FROM call_entry c, form_data_recolected_entry fdr, form_field ff
WHERE fdr.id_call_entry = c.id AND fdr.id_form_field = ff.id AND c.id_campaign = ?
    AND ff.tipo <> 'LABEL'
    AND (c.status='terminada' OR c.status='abandonada')
ORDER BY id_call, id_form, id_form_field
SQL_DATOS_FORM;
        $datosRecolectados = $this->_DB->fetchTable($sqlDatosForm, TRUE, array($id_campaign));
        if (!is_array($datosRecolectados)) {
            $this->errMsg = 'Unable to read form fill-out data - '.$this->_DB->errMsg;
            $datosCampania = NULL;
            return $datosCampania;
        }
        foreach ($datosRecolectados as $vr) {
            if (!isset($datosCampania['FORMS'][$vr['id_form']]['DATA'][$vr['id_call']])) {
                // No está asignada la tupla de valores para esta llamada. Se construye
                // una tupla de valores NULL que será llenada progresivamente.
                $tuplaVacia = array_fill(0, count($datosCampania['FORMS'][$vr['id_form']]['LABEL']), NULL);
                $datosCampania['FORMS'][$vr['id_form']]['DATA'][$vr['id_call']] = $tuplaVacia;
            }
            $iPos = $datosCampania['FORMS'][$vr['id_form']]['FF2POS'][$vr['id_form_field']];
            $datosCampania['FORMS'][$vr['id_form']]['DATA'][$vr['id_call']][$iPos] = $vr['campo_valor'];
        }
        $datosRecolectados = NULL;

        return $datosCampania;
    }

}
?>