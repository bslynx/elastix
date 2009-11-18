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
    private $AGENT_FILE;
    var $arrAgents;
    private $_DB; // instancia de la clase paloDB
    var $errMsg;

    function Agentes(&$pDB, $file = "/etc/asterisk/agents.conf")
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
        $this->AGENT_FILE=$file;
    }

    function getAgents($id=null)
    {
        // CONSULTA DE LA BASE DE DATOS LA INFORMACIÓN DE LOS AGENTES
        $qorder = '';
        if (is_null($id)) {
            $where = "";            
            $qorder = "ORDER BY `agent`.`number` ASC";
        } else {
            $where = " and number=$id";
        }

        $sQuery = "SELECT * FROM agent WHERE estatus='A' $where $qorder";

        $arr_result = array();
        $arr_result =& $this->_DB->fetchTable($sQuery, true);
        if ($arr_result) {
            if (is_null($id)) {
                return $arr_result;
            } else {
                return $arr_result[0];
            }
        } else {
            $this->errMsg = 'Unable to read agent information - '.$this->_DB->errMsg;
            return NULL;
        }
    }


    function existAgent($agent)
    {
        $this->_read_agents();
        foreach ($this->arrAgents as $agente){
            if ($agente[0] == $agent)
                return $agente;
        }
        return false;
    }

    function getAgentsFile()
    {
        $this->_read_agents();
        return array_keys($this->arrAgents);
    }

    function addAgent($agent)
    {
        if (!is_array($agent) || count($agent) < 3) {
            $this->errMsg = 'Invalid agent data';
            return FALSE;
        }

        // GRABAR EN BASE DE DATOS

        $sPeticionSQL = paloDB::construirInsert(
            "agent",
            array(
            "number"          =>  paloDB::DBCAMPO($agent[0]),
            "name"   =>  paloDB::DBCAMPO($agent[2]),
            "password"       =>  paloDB::DBCAMPO($agent[1]),
            )
        );
        $this->_DB->genQuery("SET AUTOCOMMIT = 0");
        $result = $this->_DB->genQuery($sPeticionSQL);

        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            $this->_DB->genQuery("ROLLBACK");
            $this->_DB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }

        $resp = $this->addAgentFile($agent);
        if ($resp) {
            $this->_DB->genQuery("COMMIT");
        } else {
            $this->_DB->genQuery("ROLLBACK");
        }
        $this->_DB->genQuery("SET AUTOCOMMIT = 1");
        return $resp; 
    }

    function editAgent($agent)
    {
        if (!is_array($agent) || count($agent) < 3) {
            $this->errMsg = 'Invalid agent data';
            return FALSE;
        }

        // EDITAR EN BASE DE DATOS

        $sPeticionSQL = paloDB::construirUpdate(
            "agent",
            array(
                "name"   =>  paloDB::DBCAMPO($agent[2]),
                "password"       =>  paloDB::DBCAMPO($agent[1]),
            ),
            "number=".$agent[0]
        );
        $this->_DB->genQuery("SET AUTOCOMMIT = 0");
        $result = $this->_DB->genQuery($sPeticionSQL);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            $this->_DB->genQuery("ROLLBACK");
            $this->_DB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }

        // Leer el archivo y buscar la línea del agente a modificar
        $bExito = TRUE;
        $contenido = file($this->AGENT_FILE);
        if (!is_array($contenido)) {
            $bExito = FALSE;
            $this->errMsg = '(internal) Unable to read agent file';
        } else {
            $sLineaAgente = "agent => {$agent[0]},{$agent[1]},{$agent[2]}\n";
            $bModificado = FALSE;
            for ($i = 0; $i < count($contenido); $i++) {
                $regs = NULL;
                if (ereg('^[[:space:]]*agent[[:space:]]*=>[[:space:]]*([[:digit:]]+),', $contenido[$i], $regs) &&
                    $regs[1] == $agent[0]) {
                    // Se ha encontrado la línea del agente modificado
                    $contenido[$i] = $sLineaAgente;
                    $bModificado = TRUE;
                }
            }
            if (!$bModificado) $contenido[] = $sLineaAgente;

            $hArchivo = fopen($this->AGENT_FILE, 'w');
            if (!$hArchivo) {
                $bExito = FALSE;
                $this->errMsg = '(internal) Unable to write agent file';
            } else {
                foreach ($contenido as $sLinea) fwrite($hArchivo, $sLinea);
                fclose($hArchivo);
            }
        }
        
        if ($bExito) {
            $this->_DB->genQuery("COMMIT");
            $this->_DB->genQuery("SET AUTOCOMMIT = 1");

            return $this->_reloadAsterisk();
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

        $this->_DB->genQuery("SET AUTOCOMMIT = 0");
        $result = $this->_DB->genQuery($sPeticionSQL);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            $this->_DB->genQuery("ROLLBACK");
            $this->_DB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }

        $resp = $this->deleteAgentFile($id_agent);
        if ($resp) {
            $this->_DB->genQuery("COMMIT");
        } else {
            $this->_DB->genQuery("ROLLBACK");
        }
        $this->_DB->genQuery("SET AUTOCOMMIT = 1");

        return $resp;
    }

    function addAgentFile($agent)
    {
        if (!is_array($agent) || count($agent) < 3) {
            $this->errMsg = '(internal) Invalid agent information';
            return FALSE;
        }

        // GRABAR EN EL ARCHIVO
        $archivo=$this->AGENT_FILE;
        $tamanio_linea = 4096;
        $open = fopen ($archivo,"a+");

        $nuevo_agente="agent => {$agent[0]},{$agent[1]},{$agent[2]}\n";
        // vas leyendo linea a linea , hasta llegar al final del archivo en
        //donde  fgets() retorna false

        while ($sLinea = fgets($open,$tamanio_linea))  // [0]
        {
            $regs = NULL;
            if (ereg('^[[:space:]]*agent[[:space:]]*=>[[:space:]]*([[:digit:]]+),', $sLinea, $regs) &&
                $regs[1] == $agent) {
                $this->errMsg = "Agent number already exists.";
                fclose($open);
                return false;
            }
        }

        $escribir = fwrite ( $open, $nuevo_agente);
        fclose($open);
        return $this->_reloadAsterisk();
    }

    function deleteAgentFile($id_agent)
    {
        if (!ereg('^[[:digit:]]+$', $id_agent)) {
            $this->errMsg = '(internal) Invalid agent ID';
            return FALSE;
        }

        // Leer el archivo y buscar la línea del agente a eliminar
        $bExito = TRUE;
        $contenido = file($this->AGENT_FILE);
        if (!is_array($contenido)) {
            $bExito = FALSE;
            $this->errMsg = '(internal) Unable to read agent file';
        } else {
            $bModificado = FALSE;
            $contenidoNuevo = array();

            // Filtrar las líneas, y setear bandera si se eliminó alguna
            foreach ($contenido as $sLinea) {
                $regs = NULL;
                if (ereg('^[[:space:]]*agent[[:space:]]*=>[[:space:]]*([[:digit:]]+),', $sLinea, $regs) &&
                    $regs[1] == $id_agent) {
                    // Se ha encontrado la línea del agente eliminado
                    $bModificado = TRUE;
                } else {
                    $contenidoNuevo[] = $sLinea;
                }
            }

            if ($bModificado) {
                $hArchivo = fopen($this->AGENT_FILE, 'w');
                if (!$hArchivo) {
                    $bExito = FALSE;
                    $this->errMsg = '(internal) Unable to write agent file';
                } else {
                    foreach ($contenidoNuevo as $sLinea) fwrite($hArchivo, $sLinea);
                    fclose($hArchivo);
                }
            }
        }

        return $this->_reloadAsterisk();
    }

    private function _read_agents()
    {
        $contenido = file($this->AGENT_FILE);
        if (!is_array($contenido)) {
            $bExito = FALSE;
            $this->errMsg = '(internal) Unable to read agent file';
        } else {
            $this->arrAgents = array();
            foreach ($contenido as $sLinea) {
                if (ereg('^[[:space:]]*agent[[:space:]]*=>[[:space:]]*([[:digit:]]+),([[:digit:]]+),(.*)', trim($sLinea), $regs)) {
                    $this->arrAgents[$regs[1]] = array($regs[1], $regs[2], $regs[3]);
                }
            }
        }
    }

    private function _get_AGI_AsteriskManager()
    {
        $sRutaConfig = "modules/agent_console/configs/default.conf.php";
        if (!file_exists($sRutaConfig)) {
            $this->errMsg = 'While connecting to Asterisk - configuration not found';
            return NULL;
        }
        include $sRutaConfig;
        if (!isset($acceso_asterisk) || !is_array($acceso_asterisk)) {
            $this->errMsg = 'While connecting to Asterisk - invalid configuration, does not define "acceso_asterisk"';
        	return NULL;
        }
        $ip_asterisk = $acceso_asterisk["ip"];
        $user_asterisk = $acceso_asterisk["user"];
        $pass_asterisk = $acceso_asterisk["pass"];
        $astman = new AGI_AsteriskManager();
        if (!$astman->connect($ip_asterisk, $user_asterisk , $pass_asterisk)) {
            $this->errMsg = "Error when connecting to Asterisk Manager";
            return NULL;
        } else {
            return $astman;
        }
    }

    private function _reloadAsterisk()
    {
        $astman = $this->_get_AGI_AsteriskManager();
        if (is_null($astman)) {
            return FALSE;
        } else {
            // TODO: verify whether reload actually succeeded
            $strReload = $astman->Command("module reload chan_agent.so");
            $astman->disconnect();
            return TRUE;
        }
    }

    function getOnlineAgents()
    {
        $astman = $this->_get_AGI_AsteriskManager();
        if (is_null($astman)) {
            return NULL;
        } else {
            $strAgentsOnline = $astman->Command("agent show online");
            $astman->disconnect();
            $data = $strAgentsOnline['data'];
            $lineas = explode("\n", $data);
            $listaAgentes = array();

            foreach ($lineas as $sLinea) {
                // El primer número de la línea es el ID del agente a recuperar
                $regs = NULL;
                if (strpos($sLinea, 'agents online') === FALSE &&
                    ereg('^([[:digit:]]+)[[:space:]]*', $sLinea, $regs)) {
                    $listaAgentes[] = $regs[1];
                }
            }
            return $listaAgentes;
        }
    }

    function isAgentOnline($agentNum)
    {
        $astman = $this->_get_AGI_AsteriskManager();
        if (is_null($astman)) {
            return FALSE;
        } else {
            $strAgentsOnline = $astman->Command("agent show online");
            $astman->disconnect();
            $data = $strAgentsOnline['data'];
            $res = explode($agentNum,$data);
            if(is_array($res) && count($res)==2) {
                return true;
            }
            return false;
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
