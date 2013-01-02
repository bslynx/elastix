<?php

/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2007 Palosanto Solutions S. A.                         |
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
  $Id: Agentes.class.php,v  $ */
if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
    include_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
} elseif (file_exists('libs/phpagi-asmanager.php')) {
	include_once 'libs/phpagi-asmanager.php';
} else {
	die('Unable to find phpagi-asmanager.php');
}
include_once("libs/paloSantoDB.class.php");

class Agentes
{
    var $arrAgents;
    private $_DB; // instancia de la clase paloDB
    var $errMsg;

    function Agentes(&$pDB)
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

        $this->arrAgents = array();
    }

    /**
     * Procedimiento para consultar los agentes estáticos que existen en la
     * base de datos de CallCenter. Opcionalmente, se puede consultar un solo
     * agente específico.
     * 
     * @param   int     $id     Número de agente asignado
     * 
     * @return  NULL en caso de error
     *          Si $id es NULL, devuelve una matriz de las columnas conocidas:
     *              id number name password estatus eccp_password
     *          Si $id no es NULL y agente existe, se devuelve una sola tupla
     *          con la estructura de las columnas indicada anteriormente.
     *          Si $id no es NULL y agente no existe, se devuelve arreglo vacío.  
     */
    function getAgents($id=null)
    {
        // CONSULTA DE LA BASE DE DATOS LA INFORMACIÓN DE LOS AGENTES
        $paramQuery = array();
        $where = array("estatus = 'A'", 'type <> "Agent"');
        if (!is_null($id)) {
        	$paramQuery[] = $id;
            $where[] = 'number = ?';
        }
        $sQuery =
            'SELECT id, type, number, name, password, estatus, eccp_password '.
            'FROM agent WHERE '.implode(' AND ', $where).' ORDER BY number';

        $arr_result =& $this->_DB->fetchTable($sQuery, true, $paramQuery);

        if (is_array($arr_result)) {
            if (is_null($id) || count($arr_result) <= 0) {
                return $arr_result;
            } else {
                return $arr_result[0];
            }
        } else {
            $this->errMsg = 'Unable to read agent information - '.$this->_DB->errMsg;
            return NULL;
        }
    }


    /**
     * Procedimiento para agregar un nuevo agente estático a la base de datos
     * de CallCenter y al archivo agents.conf de Asterisk.
     * 
     * @param   array   $agent  Información del agente con las posiciones:
     *                  0   =>  Número del agente a crear
     *                  1   =>  Contraseña telefónica del agente
     *                  2   =>  Nombre descriptivo del agente
     *                  3   =>  Contraseña para login de ECCP
     * 
     * @return  bool    VERDADERO si se inserta correctamente agente, FALSO si no.
     */
    function addAgent($agent)
    {
	
        if (!is_array($agent) || count($agent) < 3) {
            $this->errMsg = 'Invalid agent data';
            return FALSE;
        }

        $infoAgente = $this->getAgents($agent[0]);
        if (!is_null($infoAgente) && count($infoAgente) > 0) {
            $this->errMsg = 'Agent already exists';
            return FALSE;
        }
        
        /* Se debe de autogenerar una contraseña ECCP si no se especifica. 
         * La contraseña será legible por la nueva consola de agente */
        if (!isset($agent[3]) || $agent[3] == '') $agent[3] = sha1(time().rand());

	$typeExtension = explode("/",$agent[0]);

        // GRABAR EN BASE DE DATOS
        $sPeticionSQL = 'INSERT INTO agent (type, number, password, name, eccp_password) VALUES (?, ?, ?, ?, ?)';
        $paramSQL = array($typeExtension[0], $typeExtension[1], $agent[1], $agent[2], $agent[3]);
        
        $this->_DB->genQuery("SET AUTOCOMMIT = 0");
        $result = $this->_DB->genQuery($sPeticionSQL, $paramSQL);

        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            $this->_DB->genQuery("ROLLBACK");
            $this->_DB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }

	$this->_DB->genQuery("COMMIT");
        $this->_DB->genQuery("SET AUTOCOMMIT = 1");
        return true; 
    }

    /**
     * Procedimiento para modificar un agente estático exitente en la base de
     * datos de CallCenter y en el archivo agents.conf de Asterisk.
     * 
     * @param   array   $agent  Información del agente con las posiciones:
     *                  0   =>  Número del agente a crear
     *                  1   =>  Contraseña telefónica del agente
     *                  2   =>  Nombre descriptivo del agente
     *                  3   =>  Contraseña para login de ECCP
     * 
     * @return  bool    VERDADERO si se inserta correctamente agente, FALSO si no.
     */
    function editAgent($agent)
    {
        if (!is_array($agent) || count($agent) < 3) {
            $this->errMsg = 'Invalid agent data';
            return FALSE;
        }

        // Asumir ninguna contraseña de ECCP (agente no será usable por ECCP)
        if (!isset($agent[3]) || $agent[3] == '') $agent[3] = NULL;

        $typeExtension = explode("/",$agent[0]);

        // EDITAR EN BASE DE DATOS
        $sPeticionSQL = 'UPDATE agent SET password = ?, name = ?, type = ?';
        $paramSQL = array($agent[1], $agent[2], $typeExtension[0]);
        if (!is_null($agent[3])) {
        	$sPeticionSQL .= ', eccp_password = ?';
            $paramSQL[] = $agent[3];
        }
        $sPeticionSQL .= ' WHERE number = ?';
        $paramSQL[] = $typeExtension[1];

        $this->_DB->genQuery("SET AUTOCOMMIT = 0");
        $result = $this->_DB->genQuery($sPeticionSQL, $paramSQL);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            $this->_DB->genQuery("ROLLBACK");
            $this->_DB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }

        /* Se debe de autogenerar una contraseña ECCP si no se especifica. 
         * La contraseña será legible por la nueva consola de agente */
        if (is_null($agent[3])) {
            $agent[3] = sha1(time().rand());
            $sPeticionSQL = 'UPDATE agent SET eccp_password = ? WHERE number = ? AND eccp_password IS NULL';
            $paramSQL = array($agent[3], $typeExtension[1]);
            $result = $this->_DB->genQuery($sPeticionSQL, $paramSQL);
            if (!$result) {
                $this->errMsg = $this->_DB->errMsg;
                $this->_DB->genQuery("ROLLBACK");
                $this->_DB->genQuery("SET AUTOCOMMIT = 1");
                return false;
            }
        }

        // Leer el archivo y buscar la línea del agente a modificar
        $bExito = TRUE;
        if ($bExito) {
            $this->_DB->genQuery("COMMIT");
            $this->_DB->genQuery("SET AUTOCOMMIT = 1");
        } else {
            $this->_DB->genQuery("ROLLBACK");
            $this->_DB->genQuery("SET AUTOCOMMIT = 1");
            return FALSE;
        }
    }

    function deleteAgent($id_agent)
    {
        if (!ereg('^[[:digit:]]+$', $id_agent)) {
            $this->errMsg = '(internal) Invalid agent information';
            return FALSE;
        }

        // BORRAR EN BASE DE DATOS

        $sPeticionSQL = "UPDATE agent SET estatus='I' WHERE number=$id_agent";

        $result = $this->_DB->genQuery($sPeticionSQL);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    private function _get_AGI_AsteriskManager()
    {
        $ip_asterisk = '127.0.0.1';
        $user_asterisk = 'admin';
        $pass_asterisk = function_exists('obtenerClaveAMIAdmin') ? obtenerClaveAMIAdmin() : 'elastix456';
        $astman = new AGI_AsteriskManager();
        if (!$astman->connect($ip_asterisk, $user_asterisk , $pass_asterisk)) {
            $this->errMsg = "Error when connecting to Asterisk Manager";
            return NULL;
        } else {
            return $astman;
        }
    }

    function getOnlineAgents()
    {
        $astman = $this->_get_AGI_AsteriskManager();
        if (is_null($astman)) {
            return NULL;
        } else {
            $strAgentsOnline = $astman->Command('queue show');
            $astman->disconnect();
            $data = $strAgentsOnline['data'];
            $lineas = explode("\n", $data);
            $listaAgentes = array();

            $bMembers = FALSE;
            foreach ($lineas as $sLinea) {
                if (strpos($sLinea, 'No Members') !== FALSE || strpos($sLinea, 'Members:') !== FALSE)
                    $bMembers = TRUE;
                elseif (strpos($sLinea, 'No Callers') !== FALSE || strpos($sLinea, 'Callers:') !== FALSE)
                    $bMembers = FALSE;
                elseif ($bMembers) {
                	$regs = NULL;
                    if (preg_match('/^\s*(\S+)/', $sLinea, $regs)) {
                    	if (!in_array($regs[1], $listaAgentes)) $listaAgentes[] = $regs[1];
                    }
                }
            }
            return $listaAgentes;
        }
    }

    function desconectarAgentes($arrAgentes)
    {
        $datetime_end = date("Y-m-d H:i:s");
        $this->errMsg = NULL;

        if (!(is_array($arrAgentes) && count($arrAgentes) > 0)) {
            $this->errMsg = "Lista de agentes no válida";
            return FALSE;
        }

        $astman = $this->_get_AGI_AsteriskManager();
        if (is_null($astman)) {
            return FALSE;
        }

        for ($i =0 ; $i < count($arrAgentes) ; $i++) {
            $res = $this->Agentlogoff($astman, $arrAgentes[$i]);
            $this->_private_registrarLogout($arrAgentes[$i], $datetime_end,$this->errMsg);
            if ($res['Response']=='Error') {
                $this->errMsg = "Error logoff ".$res['Message'];
                $astman->disconnect();
                return false;
            } else {
                sleep(1);
                $tipoLlamada = $this->_private_getTipoLlamada($arrAgentes[$i]);
                if(!is_null($tipoLlamada) && !empty($tipoLlamada)) { 
                    $this->_private_actualizarTablas($tipoLlamada);
                }
            }
        }
        $astman->disconnect();
        return true;
    }

    private function _private_actualizarTablas($tipoLlamada)
    {
        if( is_array($tipoLlamada) && count($tipoLlamada)>0 ) {
            $tipo       = $tipoLlamada['tipo'];
            $id_call    = $tipoLlamada['id'];
            if($tipo == "ENTRANTE") {
                $SQLUpdateEntrante = 
                "
                    update call_entry 
                    set 
                        datetime_end=datetime_init ,
                        duration = 0
                    where id={$id_call}
                ";
                $result = $this->_DB->genQuery($SQLUpdateEntrante);
                if (!$result) {
                    $this->errMsg = $this->_DB->errMsg;
                    return false;
                } 
                $SQLDeleteEntrante = "delete from current_call_entry where id_call_entry={$id_call} ";
                $resDeleteEntrante = $this->_DB->genQuery($SQLDeleteEntrante);
                if(!$resDeleteEntrante) {
                    $this->errMsg = $this->_DB->errMsg;
                    return false;
                } else {
                    return true;
                }

            } else if($tipo == "SALIENTE") {

                $SQLUpdateSaliente =
                 "
                    update calls 
                    set 
                        end_time=start_time ,
                        duration = 0
                    where id={$id_call}
                ";
                $result = $this->_DB->genQuery($SQLUpdateSaliente);
                if (!$result) {
                    $this->errMsg = $this->_DB->errMsg;
                    return false;
                }
                $SQLDeleteSaliente = "delete from current_calls where id_call={$id_call} ";
                $resDeleteSaliente = $this->_DB->genQuery($SQLDeleteSaliente);
                if(!$resDeleteSaliente) {
                    $this->errMsg = $this->_DB->errMsg;
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            $this->errMsg = "No hay llamada";
            return false;
        }
    }

    /*
        Esta funcion devuelve una tupla que contiene el tipo de llamada y el id respectivo del
        tipo de llamada
    */
    private function _private_getTipoLlamada($agentNum)
    {
        // se hace consulta para saber si hay llamadas entrantes para el agente que esta en $agentNum
        $SQLConsultaEntrante = "select cce.id_call_entry as id from agent as a,current_call_entry as cce where cce.id_agent=a.id and a.estatus='A' and a.number='$agentNum'";
        $resConsultaEntrante = $this->_DB->getFirstRowQuery($SQLConsultaEntrante,true);
        // si hay llamadas entrantes ingresa al if
        if(is_array($resConsultaEntrante) && count($resConsultaEntrante)>0) {

            $tipo = "ENTRANTE";
            $id = $resConsultaEntrante['id'];
            $arrValor = array( "tipo"=>$tipo ,"id"=>$id );
            return $arrValor;
        }
        // se hace consulta para saber si hay llamadas salientes para el agente que esta en $agentNum
        $SQLConsultaSaliente = "select id_call as id from current_calls  where agentnum='$agentNum'";
        $resConsultaSaliente = $this->_DB->getFirstRowQuery($SQLConsultaSaliente,true);
        // si hay llamadas salientes ingresa al if
        if(is_array($resConsultaSaliente) && count($resConsultaSaliente)>0)  {

            $tipo = "SALIENTE";
            $id = $resConsultaSaliente['id'];
            $arrValor = array( "tipo"=>$tipo ,"id"=>$id );
            return $arrValor;
        }
        $this->errMsg = "No call";
        return false;
    }

    private function _private_registrarLogout($agentNum, $datetime_end)
    {
        $id_audit = $this->_private_getLastIdLoginAgent($agentNum);
        if(!$id_audit) {
            return false;
        } else {
            $SQLUpdateAudit = 
            "
                update audit
                set
                    datetime_end='{$datetime_end}' ,
                    duration=timediff('{$datetime_end}',datetime_init) 
                where id ={$id_audit} 
            ";
            $resQLUpdateAudit = $this->_DB->genQuery($SQLUpdateAudit);
            if(!$resQLUpdateAudit) {
                $this->errMsg .= $this->_DB->errMsg;
                return false;
            }else {
                return true;
            }
        }
    }

    function _private_getLastIdLoginAgent($agentNum)
    {
        $SQLConsultaIdAudit = 
        "
            select au.id as id
            from audit au , agent ag  
            where 
                    ag.id=au.id_agent 
                        and 
                    id_break is null 
                        and 
                    datetime_end is null 
                        and 
                    ag.number='{$agentNum}'
        ";

        $resConsultaIdAudit = $this->_DB->getFirstRowQuery($SQLConsultaIdAudit,true);
        if(is_array($resConsultaIdAudit) && count($resConsultaIdAudit)>0)  {
            $id = $resConsultaIdAudit['id'];
            return $id;
        } elseif(is_array($resConsultaIdAudit)) {
            $this->errMsg .= "Agente no ha iniciado sesion";
        }else{
            $this->errMsg .= $this->_DB->errMsg; 
        }
        return false;
    }

    /**
      Retorna un array de extensiones de la PBX no utilizadas como callback extensions.
    */    
    public function getUnusedExtensions()
    {
	$query = "SELECT data FROM (SELECT data FROM asterisk.sip WHERE keyword = 'Dial' UNION 
				    SELECT data FROM asterisk.iax WHERE keyword = 'Dial') 
				    AS union_extensiones WHERE data NOT IN (
				    SELECT concat(type,'/',number) 
				    FROM call_center.agent WHERE type<>'Agent') ORDER BY data";
	$result = $this->_DB->fetchTable($query,true);
	if($result == FALSE){
	    $this->_DB->errMsg;
	    return array("Unavailable extensions.");
	}

	foreach($result as $k => $array){
	    $arrResult[$array['data']] = $array['data'];
	}	
	return $arrResult;
    }

    /* FUNCIONES DEL AGI*/
    /**
    * Agent Logoff
    *
    * @link http://www.voip-info.org/wiki/index.php?page=Asterisk+Manager+API+AgentLogoff
    * @param Agent: Agent ID of the agent to login 
    */
    private function Agentlogoff($obj_phpAgi, $agent)
    {
        return $obj_phpAgi->send_request('Agentlogoff', array('Agent'=>$agent));
    }
}
?>
