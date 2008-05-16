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
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
include_once("libs/paloSantoDB.class.php");
//JK
//require_once 'DB.php';

global $arrLan;

class Agentes {

    var $AGENT_FILE;
    var $arrAgents;

    function Agentes($file="/etc/elastix/agents.conf"){
        $this->arrAgents=array();
        $this->AGENT_FILE=$file;
    }

    function getAgents($id=null){

        // CONSULTA DE LA BASE DE DATOS LA INFORMACIÓN DE LOS AGENTES
        global $arrConf;
        $pDB = new paloDB($arrConf["cadena_dsn"]);

        if (is_null($id)) {
            $where = "";
        } else {
            $where = " and number=$id";
        }

        $sQuery = "SELECT * FROM agent WHERE estatus='A' ".$where;

        $arr_result = array();
        $arr_result =& $pDB->fetchTable($sQuery, true);
        if ($arr_result) {
            if (is_null($id)) {
                return $arr_result;
            } else {
                return $arr_result[0];
            }
        }
    }


    function existAgent($agent, &$msj) {
        $this->_read_agents();
        foreach ($this->arrAgents as $agente){
            if ($agente[0] == $agent)
                return $agente;
        }
        return false;
    }

    function getAgentsFile(&$msj) {
        $this->_read_agents();
        $agentes = array();
        foreach ($this->arrAgents as $agente){
            $agentes[] = $agente[0];
        }
        return $agentes;
    }


    function addAgent($agent=null,&$msj=""){
        if (!is_null($agent)){
            if (is_array($agent)){
                if (count($agent)==3){
                    return $this->_add_agent($agent,$msj);
                } else return false;
            } else return false;
        } else return false;
    }

    function editAgent($agent=null){
        if (!is_null($agent)){
            if (is_array($agent)){
                if (count($agent)==3){
                    return $this->_edit_agent($agent);
                } else return false;
            } else return false;
        } else return false;
    }

    function deleteAgent($id_agent=null){
        if (!is_null($id_agent) && !empty($id_agent) && isset($id_agent) && is_numeric($id_agent)){            
            return $this->_delete_agent($id_agent);
        } else return false;
    }

    function addAgentFile($agent, & $msj){
        if (!is_null($agent)){
            if (is_array($agent)){
                return $this->_add_agent_file($agent);
            } else return false;
        } else return false;
    }

    function deleteAgentFile($id_agent=null){
        if (!is_null($id_agent) && !empty($id_agent) && isset($id_agent) && is_numeric($id_agent)){            
            return $this->_delete_agent_file($id_agent);
        } else return false;
    }

    function _add_agent($agent,&$msj){
        // GRABAR EN BASE DE DATOS
        global $arrConf;
        $pDB = new paloDB($arrConf["cadena_dsn"]);

        $sPeticionSQL = paloDB::construirInsert(
            "agent",
            array(
            "number"          =>  paloDB::DBCAMPO($agent[0]),
            "name"   =>  paloDB::DBCAMPO($agent[2]),
            "password"       =>  paloDB::DBCAMPO($agent[1]),
//            "queue"       =>  paloDB::DBCAMPO($agent[3]),
            )
        );
echo "sql = ".$sPeticionSQL;
        $pDB->genQuery("SET AUTOCOMMIT = 0");
        $result = $pDB->genQuery($sPeticionSQL);

        if (!$result) {
            $msj = $pDB->errMsg;
            $pDB->genQuery("ROLLBACK");
            $pDB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }

        $resp = $this->_add_agent_file($agent,$msj);
        if ($resp) {
            $pDB->genQuery("COMMIT");
        } else {
            $pDB->genQuery("ROLLBACK");
        }
        $pDB->genQuery("SET AUTOCOMMIT = 1");
        return $resp; 
    }

    function _add_agent_file($agent,&$msj){
        // GRABAR EN EL ARCHIVO
        $archivo=$this->AGENT_FILE;
        $tamanio_linea = 4096;
        $open = fopen ($archivo,"a+");

        $nuevo_agente="agent => {$agent[0]},{$agent[1]},{$agent[2]}\n";
        // vas leyendo linea a linea , hasta llegar al final del archivo en
        //donde  fgets() retorna false

        while ($linea = fgets($open,$tamanio_linea))  // [0]
        {
        //en $linea tenes una linea del archivo
            if (substr($linea,0,9) === "agent => ")
            {
                $line = str_replace("agent => ","",$linea);
                $currentAgent = explode (",",$line);
                if ($agent[0] == $currentAgent[0]){
                    $msj=$arrLan["Agent number already exists."];
                    fclose($open);
                    $msj = $arrLan["Error saving agent in file"];
                    return false;
                }
            }
        }

        $escribir = fwrite ( $open, $nuevo_agente);
        fclose($open);
        $this->_reloadAsterisk();
        return true;
    }


    function _edit_agent($agent){

        // EDITAR EN BASE DE DATOS
        global $arrConf;
        $pDB = new paloDB($arrConf["cadena_dsn"]);

        $sPeticionSQL = paloDB::construirUpdate(
            "agent",
            array(
                "name"   =>  paloDB::DBCAMPO($agent[2]),
                "password"       =>  paloDB::DBCAMPO($agent[1]),
//                "queue"       =>  paloDB::DBCAMPO($agent[3]),
            ),
            "number=".$agent[0]
        );
        $pDB->genQuery("SET AUTOCOMMIT = 0");
        $result = $pDB->genQuery($sPeticionSQL);
        if (!$result) {
            $msj = $pDB->errMsg;
            $pDB->genQuery("ROLLBACK");
            $pDB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }

        // EDITAR EL ARCHIVO
        $archivo=$this->AGENT_FILE;
        $archivo_C=$this->AGENT_FILE."_C";

        $command=`cp $archivo $archivo_C`;
        chmod($archivo_C,0777);

        $tamanio_linea = 4096;

        if (!$open = fopen ($archivo,"w+")) {
            $msj = $arrLan["Error updating agent in file"];
            $pDB->genQuery("ROLLBACK");
            $pDB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }
        if (!$open_C = fopen ($archivo_C,"a+")) {
            $pDB->genQuery("ROLLBACK");
            $pDB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }

        $nuevo_agente="agent => {$agent[0]},{$agent[1]},{$agent[2]}\n";
        
        while ($linea = fgets($open_C,$tamanio_linea))  // [0]
        {      
            
            if (substr($linea,0,9) === "agent => ")
            {
                $line = str_replace("agent => ","",$linea);
                # [JAS]: busco la coma para separar la info         
                $currentAgent = explode (",",$line);
                if ($agent[0] == $currentAgent[0]){
                    $posicion = ftell ($open_C);
                    fseek($open_C,$posicion);
                    $escribir = fwrite ( $open,$nuevo_agente);
                } else {
                    $escribir = fwrite ($open,$linea);
                }
            } else {
                $escribir = fwrite ( $open,$linea);
            }
        }
        fclose($open);
        fclose($open_C);
        $pDB->genQuery("COMMIT");
        $pDB->genQuery("SET AUTOCOMMIT = 1");

        $command=`rm $archivo_C`;
        $this->_reloadAsterisk();
        return true;
    }


    function _delete_agent($id_agent){

        // BORRAR EN BASE DE DATOS
        global $arrConf;
        $pDB = new paloDB($arrConf["cadena_dsn"]);

        $sPeticionSQL = "UPDATE agent SET estatus='I' WHERE number=$id_agent";

        $pDB->genQuery("SET AUTOCOMMIT = 0");
        $result = $pDB->genQuery($sPeticionSQL);
        if (!$result) {
            $msj = $pDB->errMsg;
            $pDB->genQuery("ROLLBACK");
            $pDB->genQuery("SET AUTOCOMMIT = 1");
            return false;
        }

        $resp = $this->_delete_agent_file($id_agent);
        if ($resp) {
            $pDB->genQuery("COMMIT");
        } else {
            $pDB->genQuery("ROLLBACK");
        }
        $pDB->genQuery("SET AUTOCOMMIT = 1");

        return $resp;
    }

    function _delete_agent_file($id_agent){

        // BORRAR EN EL ARCHIVO
        $archivo=$this->AGENT_FILE;
        $archivo_C=$this->AGENT_FILE."_C";

        $command=`cp $archivo $archivo_C`;
        chmod($archivo_C,0777);

        $tamanio_linea = 4096;
        if (!$open = fopen ($archivo,"w+")) {
            $msj = $arrConf("Error when deleting agent in file");
            return false;
        }
        if (!$open_C = fopen ($archivo_C,"a+")) {
            $msj = $arrConf("Error when deleting agent in file");
            return false;
        }

        
        while ($linea = fgets($open_C,$tamanio_linea))  // [0]
        {
            if (substr($linea,0,9) === "agent => ")
            {
                $line = str_replace("agent => ","",$linea);
                # [JAS]: busco la coma para separar la info         
                $currentAgent = explode (",",$line);
                if ($id_agent == $currentAgent[0]){

                } else {
                    $escribir = fwrite ($open,$linea);
                }
            } else {
                $escribir = fwrite ( $open,$linea);
            }
        }
        fclose($open);
        fclose($open_C);
        $command=`rm $archivo_C`;
        $this->_reloadAsterisk();
        return true;
    }


    function _read_agents()
	{ 
		$file = $this->AGENT_FILE;
            // [JMA]: 
        if($report_handle = fopen($file, "r"))
        {
            $found=0;
            while(!feof($report_handle))
            {
                $line = fgets($report_handle,512);
                if($this->_scan_for_tag($agent,$line)) {
                    $found++; 
                    //print_r($agent);
                    $this->arrAgents[$agent[0]]=$agent;
                }
            }            
        }
        fclose($report_handle);

    }

	function _scan_for_tag(&$agent,$line)
	{
	    if(substr($line,0,9) === "agent => ")
	    {
	      $line = str_replace("agent => ","",$line);
	      # [JAS]: busco la coma para separar la info	      
          $agent = explode (",",$line);
	      if (count($agent)==3)  return true;
          else return false;
	    }
	    else return false;
	}

        function _reloadAsterisk()
        {
            // incluyendo archivo donde están los datos de acceso del asterisk
            include_once "modules/agent_console/configs/default.conf.php";
            $ip_asterisk = $acceso_asterisk["ip"];
            $user_asterisk = $acceso_asterisk["user"];
            $pass_asterisk = $acceso_asterisk["pass"];

            $astman = new AGI_AsteriskManager();
            if (!$astman->connect($ip_asterisk, $user_asterisk , $pass_asterisk)) {
                $resultado = "Error when connecting to Asterisk Manager";
            } else {
                $strReload = $astman->Command(" reload");
                $astman->disconnect();
            }
        }

        function isAgentOnline($agentNum) {
            // incluyendo archivo donde están los datos de acceso del asterisk
            include_once "modules/agent_console/configs/default.conf.php";
            $ip_asterisk = $_SESSION["ip_asterisk"];
            $user_asterisk = $_SESSION["user_asterisk"];
            $pass_asterisk = $_SESSION["pass_asterisk"];
            $astman = new AGI_AsteriskManager();
            if (!$astman->connect($ip_asterisk, $user_asterisk , $pass_asterisk)) {
                $resultado = "Error when connecting to Asterisk Manager";
            } else {
                $strAgentsOnline = $astman->Command("agent show online");
                $data = $strAgentsOnline['data'];
                $res = explode($agentNum,$data);
                if(is_array($res) && count($res)==2) {
                    return true;
                }
                return false;
                $astman->disconnect();
            }
        }

        function desconectarAgentes($cadena_dsn,$arrAgentes,&$msj) {
            $datetime_end = date("Y-m-d H:i:s");
            $msj = "";
            $pDB = new paloDB($cadena_dsn);

            if (!is_object($pDB->conn) || $pDB->errMsg!="") {
                $msj = $arrLang["Error when connecting to database"]." ".$pDB->errMsg;
                return false;
            } else {
                if(is_array($arrAgentes) && count($arrAgentes)>0) {
                    // incluyendo archivo donde están los datos de acceso del asterisk
                    include_once "modules/agent_console/configs/default.conf.php";
                    $ip_asterisk = $acceso_asterisk["ip"];
                    $user_asterisk = $acceso_asterisk["user"];
                    $pass_asterisk = $acceso_asterisk["pass"];
 
                    $astman = new AGI_AsteriskManager();
                    if (!$astman->connect($ip_asterisk, $user_asterisk , $pass_asterisk)) {
                        $msj = "Error when connecting to Asterisk Manager";
                    } else {
                        for($i =0 ; $i<count($arrAgentes) ; $i++) {
                            $res = $astman->Agentlogoff($arrAgentes[$i]);
                            $this->registrarLogout($_SESSION['elastix_agent_user'],$datetime_end,$msj);
                            if ($res['Response']=='Error') {
                                $msj = $arrLan["Error logoff"]." ".$res['Message'];
                                $astman->disconnect();
                                return false;
                            } else {
                                $tipoLlamada = $this->getTipoLlamada($pDB,$msj);
                                if(!is_null($tipoLlamada) && !empty($tipoLlamada)) { 
                                    $this->actualizarTablas($pDB,$tipoLlamada,$msj);
                                }
                            }
                        }
                        $astman->disconnect();
                        return true;
                    }
                }else {
                    $msj="array invalido";
                }
            }
            return false;
        }

        function actualizarTablas($pDB,$tipoLlamada,&$msj) {
            $agentNum = $_SESSION['elastix_agent_user'];
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
                    $result = $pDB->genQuery($SQLUpdateEntrante);
                    if (!$result) {
                        $msj = $pDB->errMsg;
                        return false;
                    } 
                    $SQLDeleteEntrante = "delete from current_call_entry where id_call_entry={$id_call} ";
                    $resDeleteEntrante = $pDB->genQuery($SQLDeleteEntrante);
                    if(!$resDeleteEntrante) {
                        $msj = $pDB->errMsg;
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
                    $result = $pDB->genQuery($SQLUpdateSaliente);
                    if (!$result) {
                        $msj = $pDB->errMsg;
                        return false;
                    }
                    $SQLDeleteSaliente = "delete from current_calls where id_call={$id_call} ";
                    $resDeleteSaliente = $pDB->genQuery($SQLDeleteSaliente);
                    if(!$resDeleteSaliente) {
                        $msj = $pDB->errMsg;
                        return false;
                    } else {
                        return true;
                    }
                }
            } else {
                $msj .= "No hay llamada";
                return false;
            }
        }
        /*
            Esta funcion devuelve una tupla que contiene el tipo de llamada y el id respectivo del
            tipo de llamada
        */
        function getTipoLlamada($pDB,&$msj) {
            global $arrLan;
            //$agentNum = $_SESSION['elastix_agent_user'];
            $agentNum = $_SESSION['elastix_agent_user'];
            // se hace consulta para saber si hay llamadas entrantes para el agente que esta en $agentNum
            $SQLConsultaEntrante = "select cce.id_call_entry as id from agent as a,current_call_entry as cce where cce.id_agent=a.id and a.estatus='A' and a.number='$agentNum'";
            $resConsultaEntrante = $pDB->getFirstRowQuery($SQLConsultaEntrante,true);
            // si hay llamadas entrantes ingresa al if
            if(is_array($resConsultaEntrante) && count($resConsultaEntrante)>0) {

                $tipo = "ENTRANTE";
                $id = $resConsultaEntrante['id'];
                $arrValor = array( "tipo"=>$tipo ,"id"=>$id );
                return $arrValor;
            }
            // se hace consulta para saber si hay llamadas salientes para el agente que esta en $agentNum
            $SQLConsultaSaliente = "select id_call as id from current_calls  where agentnum='$agentNum'";
            $resConsultaSaliente = $pDB->getFirstRowQuery($SQLConsultaSaliente,true);
            // si hay llamadas salientes ingresa al if
            if(is_array($resConsultaSaliente) && count($resConsultaSaliente)>0)  {

                $tipo = "SALIENTE";
                $id = $resConsultaSaliente['id'];
                $arrValor = array( "tipo"=>$tipo ,"id"=>$id );
                return $arrValor;
            }
            $msj .= $arrLan["No call"];
            return false;
        }

        function registrarLogout($agentNum,$datetime_end,&$msj) {
            global $arrConf;
            $pDB = new paloDB($arrConf['cadena_dsn']);
            if (!is_object($pDB->conn) || $pDB->errMsg!="") {
                $resultado = $arrLan["Error when connecting to Call Center"];
            } else { 
                $id_audit = $this->getLastIdLoginAgent($pDB,$agentNum,$msj);
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
                    $resQLUpdateAudit = $pDB->genQuery($SQLUpdateAudit);
                    if(!$resQLUpdateAudit) {
                        $msj .= $pDB->errMsg;
                        return false;
                    }else {
                        return true;
                    }
                }
            }
        }

        function getLastIdLoginAgent($pDB,$agentNum,&$msj) {
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

            $resConsultaIdAudit = $pDB->getFirstRowQuery($SQLConsultaIdAudit,true);
            if(is_array($resConsultaIdAudit) && count($resConsultaIdAudit)>0)  {
                $id = $resConsultaIdAudit['id'];
                return $id;
            } elseif(is_array($resConsultaIdAudit)) {
                $msj .= "Agente no ha iniciado sesion";
            }else{
                $msj .= $pDB->errMsg; 
            }
            return false;
        }


}


?>