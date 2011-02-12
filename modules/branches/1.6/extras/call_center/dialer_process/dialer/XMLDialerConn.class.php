<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.2-2                                               |
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
  $Id: DialerConn.class.php,v 1.48 2009/03/26 13:46:58 alex Exp $ */

require_once ('DialerConn.class.php');

class XMLDialerConn extends DialerConn
{
    private $oMainLog;
    private $_astConn;
    private $_dbConn;
    private $_dialProc;
    public $_listaReq = array();    // Lista de requerimientos pendientes
    private $_parser = NULL;        // Parser expat para separar los paquetes
    private $_iPosFinal = NULL;     // Posición de parser para el paquete parseado
    private $_sTipoDoc = NULL;      // Tipo de paquete. Sólo se acepta 'request'
    private $_bufferXML = '';       // Datos pendientes que no forman un paquete completo
    private $_iNestLevel = 0;       // Al llegar a cero, se tiene fin de paquete

    // Estado de la conexión
    private $_sUsuarioECCP  = NULL; // Nombre de usuario para cliente logoneado, o NULL si no logoneado
    private $_sAppCookie = NULL;    // Cadena a usar como cookie de la aplicación
    private $_bFinalizando = FALSE;

    function XMLDialerConn($oMainLog)
    {
    	$this->oMainLog = $oMainLog;
        $this->_resetParser();
    }

    function setAstConn($astConn)
    {
    	$this->_astConn = $astConn;
    }

    function setDbConn($dbConn)
    {
        $this->_dbConn = $dbConn;
    }

    function setDialerProcess($dialProc)
    {
        $this->_dialProc = $dialProc;
    }

    // TODO: encontrar manera elegante de tener una sola definición
    private function _abrirConexionFreePBX()
    {
        $sNombreConfig = '/etc/amportal.conf';  // TODO: vale la pena poner esto en config?

        // De algunas pruebas se desprende que parse_ini_file no puede parsear 
        // /etc/amportal.conf, de forma que se debe abrir directamente.
        $dbParams = array();
        $hConfig = fopen($sNombreConfig, 'r');
        if (!$hConfig) {
            $this->oMainLog->output('ERR: no se puede abrir archivo '.$sNombreConfig.' para lectura de parámetros FreePBX.');
            return NULL;
        }
        while (!feof($hConfig)) {
            $sLinea = fgets($hConfig);
            if ($sLinea === FALSE) break;
            $sLinea = trim($sLinea);
            if ($sLinea == '') continue;
            if ($sLinea{0} == '#') continue;
            
            $regs = NULL;
            if (ereg('^([[:alpha:]]+)[[:space:]]*=[[:space:]]*(.*)$', $sLinea, $regs)) switch ($regs[1]) {
            case 'AMPDBHOST':
            case 'AMPDBUSER':
            case 'AMPDBENGINE':
            case 'AMPDBPASS':
                $dbParams[$regs[1]] = $regs[2];
                break;
            }
        }
        fclose($hConfig); unset($hConfig);
        
        // Abrir la conexión a la base de datos, si se tienen todos los parámetros
        if (count($dbParams) < 4) {
            $this->oMainLog->output('ERR: archivo '.$sNombreConfig.
                ' de parámetros FreePBX no tiene todos los parámetros requeridos para conexión.');
            return NULL;
        }
        if ($dbParams['AMPDBENGINE'] != 'mysql' && $dbParams['AMPDBENGINE'] != 'mysqli') {
            $this->oMainLog->output('ERR: archivo '.$sNombreConfig.
                ' de parámetros FreePBX especifica AMPDBENGINE='.$dbParams['AMPDBENGINE'].
                ' que no ha sido probado.');
            return NULL;
        }
        $sConnStr = 'mysql://'.$dbParams['AMPDBUSER'].':'.$dbParams['AMPDBPASS'].'@'.$dbParams['AMPDBHOST'].'/asterisk';
        $dbConn =  DB::connect($sConnStr);
        if (DB::isError($dbConn)) {
            $this->oMainLog->output("ERR: no se puede conectar a DB de FreePBX - ".($dbConn->getMessage()));
            return NULL;
        }
        $dbConn->setOption('autofree', TRUE);
        
        return $dbConn;
    }

    // Datos a mandar a escribir apenas se inicia la conexión
    function procesarInicial()
    {
/*
        //$this->oMainLog->output("DEBUG: XMLDialerConn::procesarInicial()");
    	//$s = "Prueba sin funcionalidad\n";
        $s = print_r($this->_astConn->Command('agent show'), 1);
        $this->dialSrv->encolarDatosEscribir($this->sKey, $s);
*/        
    }

    // Separar flujo de datos en paquetes, devuelve número de bytes de paquetes aceptados
    function parsearPaquetes($sDatos)
    {
        $this->parsearPaquetesXML($sDatos);
        return strlen($sDatos);
    }
    
    // Procesar cierre de la conexión
    function procesarCierre()
    {
        //$this->oMainLog->output("DEBUG: XMLDialerConn::procesarCierre()");
        if (!is_null($this->_parser)) {
            xml_parser_free($this->_parser);
            $this->_parser = NULL;
        }
    }
    
    // Preguntar si hay paquetes pendientes de procesar
    function hayPaquetes() {
        return (count($this->_listaReq) > 0);
    }
    
    // Procesar un solo paquete de la cola de paquetes
    function procesarPaquete()
    {
        $request = array_shift($this->_listaReq);
        if (is_object($request)) {
        	// Petición es un request, procesar
            if (count($request) != 1) {
                // La petición debe tener al menos un elemento hijo
            	$response = $this->_generarRespuestaFallo(400, 'Bad request');
            } elseif (!isset($request['id'])) {
                // La petición debe tener un identificador
                $response = $this->_generarRespuestaFallo(400, 'Bad request');
            } else {
                $comando = NULL;
                foreach ($request->children() as $c) $comando = $c;
                switch ($comando->getName()) {
                case 'login':
                    $response = $this->Request_Login($comando);
                    break;
                case 'logout':
                    $response = $this->Request_Logout($comando);
                    break;
                case 'loginagent':
                    $response = $this->Request_LoginAgent($comando);
                    break;
                case 'logoutagent':
                    $response = $this->Request_LogoutAgent($comando);
                    break;
                case 'getagentstatus':
                    $response = $this->Request_GetAgentStatus($comando);
                    break;
                case 'getcampaignstatus':
                    $response = $this->Request_GetCampaignStatus($comando);
                    break;
                case 'dial':
                    $response = $this->Request_Dial($comando);
                    break;
                case 'hangup':
                    $response = $this->Request_Hangup($comando);
                    break;
                case 'hold':
                    $response = $this->Request_Hold($comando);
                    break;
                case 'transfercall':
                    $response = $this->Request_TransferCall($comando);
                    break;
                case 'getcampaigninfo':
                    $response = $this->Request_GetCampaignInfo($comando);
                    break;
                case 'getcallinfo':
                    $response = $this->Request_GetCallInfo($comando);
                    break;
                case 'saveformdata':
                    $response = $this->Request_SaveFormData($comando);
                    break;
                case 'pauseagent':
                    $response = $this->Request_PauseAgent($comando);
                    break;
                case 'unpauseagent':
                    $response = $this->Request_UnpauseAgent($comando);
                    break;
                case 'getpauses':
                    $response = $this->Request_GetPauses($comando);
                    break;
                case 'setcontact':
                    $response = $this->Request_SetContact($comando);
                    break;
/*
                case 'getcallstatus':
                    $response = $this->Request_GetCallStatus($comando);
                    break;
*/                    
                default:
                    $response = $this->_generarRespuestaFallo(501, 'Not Implemented');
                    break;
                }
                $response->addAttribute('id', $request['id']);
            }
            $s = $response->asXML();
            $this->dialSrv->encolarDatosEscribir($this->sKey, $s);
            if ($this->_bFinalizando) $this->dialSrv->marcarCerrado($this->sKey);
        } else {
        	// Marcador de error, se cierra la conexión
            $r = $this->_generarRespuestaFallo(400, 'Bad request');
            $s = $r->asXML();
            $this->dialSrv->encolarDatosEscribir($this->sKey, $s);
            $this->dialSrv->marcarCerrado($this->sKey);
        }
    }
    
    // Función que construye una respuesta de petición incorrecta
    private function _generarRespuestaFallo($iCodigo, $sMensaje, $idPeticion = NULL)
    {
    	$x = new SimpleXMLElement("<response />");
        if (!is_null($idPeticion))
            $x->addAttribute("id", $idPeticion);
        $this->_agregarRespuestaFallo($x, $iCodigo, $sMensaje);
        return $x;
    }
    
    // Agregar etiqueta failure a la respuesta indicada
    private function _agregarRespuestaFallo($x, $iCodigo, $sMensaje)
    {
        $failureTag = $x->addChild("failure");
        $failureTag->addChild("code", $iCodigo);
        $failureTag->addChild("message", $sMensaje);
    } 
    
    // Procedimiento a llamar cuando se finaliza la conexión en cierre normal 
    // del programa.
    function finalizarConexion()
    {
        // Mandar a cerrar la conexión en sí
        $this->dialSrv->marcarCerrado($this->sKey);
        
        if (!is_null($this->_parser)) {
            xml_parser_free($this->_parser);
            $this->_parser = NULL;
        }
    }

    // Implementación de parser expat: inicio

    // Parsear y separar tantos paquetes XML como sean posibles
    private function parsearPaquetesXML($data)
    {
        $this->_bufferXML .= $data;
        $r = xml_parse($this->_parser, $data);
        while (!is_null($this->_iPosFinal)) {
            if ($this->_sTipoDoc == 'request') {
                $this->_listaReq[] = simplexml_load_string(substr($this->_bufferXML, 0, $this->_iPosFinal));
            } else {
                $this->_listaReq[] = array(
                    'errorcode'     =>  -1,
                    'errorstring'   =>  "Unrecognized packet type: {$this->_sTipoDoc}",
                    'errorline'     =>  xml_get_current_line_number($this->_parser),
                    'errorpos'      =>  xml_get_current_column_number($this->_parser),
                );
            }
            $this->_bufferXML = ltrim(substr($this->_bufferXML, $this->_iPosFinal));
            $this->_iPosFinal = NULL;
            $this->_resetParser();
            if ($this->_bufferXML != '')
                $r = xml_parse($this->_parser, $this->_bufferXML);
        }
        if (!$r) {
            $this->_listaReq[] = array(
                'errorcode'     =>  xml_get_error_code($this->_parser),
                'errorstring'   =>  xml_error_string(xml_get_error_code($this->_parser)),
                'errorline'     =>  xml_get_current_line_number($this->_parser),
                'errorpos'      =>  xml_get_current_column_number($this->_parser),
            );
        }
        return $r;
    }
    
    // Resetear el parseador, para iniciarlo, o luego de parsear un paquete
    private function _resetParser()
    {
        if (!is_null($this->_parser)) xml_parser_free($this->_parser);
        $this->_parser = xml_parser_create('UTF-8');
        xml_set_element_handler ($this->_parser,
            array($this, 'xmlStartHandler'),
            array($this, 'xmlEndHandler'));
        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, 0);
    }

    function xmlStartHandler($parser, $name, $attribs)
    {
        $this->_iNestLevel++;
    }

    function xmlEndHandler($parser, $name)
    {
        $this->_iNestLevel--;
        if ($this->_iNestLevel == 0) {
            $this->_iPosFinal = xml_get_current_byte_index($parser);
            $this->_sTipoDoc = $name;
        }
    }

    // Implementación de parser expat: final


    // Métodos que implementan los requerimiento del protocolo ECCP

    /**
     * Procedimiento que implementa el login del cliente del protocolo. No se 
     * debe mandar ningún evento ni obedecer ningún otro requerimiento hasta que
     * se haya usado este comando para logonearse exitosamente
     * 
     * @param   object   $comando    Comando de login
     *      <login>
     *          <username>alice</username>
     *          <password>[md5hash]</password> <!-- md5hash es hash md5 de passwd -->
     *      </login>
     * 
     * @return  object  Respuesta codificada como un SimpleXMLObject
     *      <login_response>
     *          <success /> | <failure>mensaje</failure>
     *      </login_response>
     */
    private function Request_Login($comando)
    {
        // Verificar que usuario y clave están presentes
        if (!isset($comando->username) || !isset($comando->password)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        
        $xml_response = new SimpleXMLElement('<response />');
        $xml_loginResponse = $xml_response->addChild('login_response');

        /* FIXME: No me queda claro de qué manera es más seguro mandar el hash
         * del password, que el password en texto plano, en una conexión sin
         * encriptar, ya que en ambos casos se puede recoger con un sniffer.
         * Por ahora se acepta el password con o sin hash. */
        /* TODO: se puede almacenar cuál agente(s) está autorizado a atender en 
         * la tabla eccp_authorized_clients */
        $sPeticionSQL = 
            'SELECT COUNT(*) AS N FROM eccp_authorized_clients '.
            'WHERE username = ? AND (md5_password = ? OR md5_password = md5(?))';
        $paramSQL = array($comando->username, $comando->password, $comando->password);
        $tupla = $this->_dbConn->getRow($sPeticionSQL, $paramSQL, DB_FETCHMODE_ASSOC);
        if (DB::isError($tupla)) {
            $this->oMainLog->output("ERR: no se puede consultar clave de acceso ECCP: ".$tupla->getMessage());
            $this->_agregarRespuestaFallo($xml_loginResponse, 503, 'Internal server error');
        } else {
            if ($tupla['N'] > 0) {
            	// Usuario autorizado
                $this->_sUsuarioECCP = $comando->username;
                $xml_status = $xml_loginResponse->addChild('success');
                
                // Generar una cadena de hash para cookie de aplicación
                $sAppCookie = md5(posix_getpid().time().mt_rand());
                $xml_loginResponse->addChild('app_cookie', $sAppCookie);
                $this->_sAppCookie = $sAppCookie;
            } else {
            	// Usuario no existe, o clave incorrecta
                $this->_agregarRespuestaFallo($xml_loginResponse, 401, 'Invalid username or password');
            }
        }
        return $xml_response;
    }
    
    /**
     * Procedimiento que implementa el logout del cliente del protocolo. Luego 
     * de este requerimiento, se espera que se cierre la conexión.
     * 
     * @param   object   $comando    Comando de logout
     *      <logout />
     * 
     * @return  object  Respuesta codificada como un SimpleXMLObject  
     *      <logout_response />
     */
    private function Request_Logout($comando)
    {
        $this->_sUsuarioECCP = NULL;
        $this->_sAppCookie = NULL;
        $xml_response = new SimpleXMLElement('<response />');
        $xml_loginResponse = $xml_response->addChild('logout_response');
        $xml_status = $xml_loginResponse->addChild('success');
        $this->_bFinalizando = TRUE;
        return $xml_response;
    }
    
    // Función que encapsula la generación de la respuesta
    private function Response_LoginAgentResponse($status, $msg = NULL)
    {
        $xml_response = new SimpleXMLElement('<response />');
        $xml_loginAgentResponse = $xml_response->addChild('loginagent_response');

        $xml_loginAgentResponse->addChild('status', $status);
        if (!is_null($msg)) 
            $this->_agregarRespuestaFallo($xml_loginAgentResponse, 417, $msg);
            
        return $xml_response;           
    }
    
    /**
     * Procedimiento que implementa el login de un agente estático al estilo
     * Agent/9000. Para esta versión se asume que el agente está asociado a una
     * extensión telefónica, a la cual se mandará una llamada que conecta tal
     * extensión con la cola. El comando regresa inmediatamente. Luego el cliente
     * debe de esperar el evento LoginAgent que indica que se ha completado
     * exitosamente el login del agente, y que empezará a recibir llamadas de la
     * campaña asociada a las colas del agente.
     * 
     * Implementación: las tareas a hacer para iniciar el login del agente son:
     * 1) Verificar si el agente existe en el sistema. Si no existe, se devuelve
     *    error sin hacer otra operación.
     * 2) Verificar si la extensión indicada es válida. Si no existe, se devuelve
     *    error sin hacer otra operación. 
     * 3) Verificar si el agente ya está logoneado. Si ya está logoneado, entonces
     *    se debe verificar si está logoneado en la extensión indicada en el 
     *    parámetro. Si es la misma extensión se devuelve éxito sin hacer nada 
     *    más. Si no es la misma extensión, se devuelve error informando la 
     *    situación.
     * 4) Para agente no logoneado, se inicia un Originate entre la extensión
     *    y el canal de Agent/XXXX. Como Action-Id, se indica la cadena 
     *    "ECCP:1.0:<PID>:AgentLogin:<canaldeagente>"
     *    para distinguir este login de los logines a colas por otros motivos.
     * Para el resto del procesamiento se debe ver el método OnAgentlogin
     * en la clase DialerProcess. 
     * 
     * @param   object   $comando    Comando de login
     *      <loginagent>
     *          <agent_number>Agent/9000</agent_number>
     *          <password>xxx</password> <!-- se ignora en implementación actual -->
     *          <extension>1064</extension>
     *      </loginagent>
     * 
     * @return  object  Respuesta codificada como un SimpleXMLObject
     *      <loginagent_response>
     *          <status>logged-out|logging|logged-in</status>
     *          <failure>mensaje</failure>
     *      </loginagent_response>
     */
    private function Request_LoginAgent($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');

        // Verificar que agente y extensión están presentes
        if (!isset($comando->agent_number) || !isset($comando->extension)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $sAgente = (string)$comando->agent_number;
        $sExtension = (string)$comando->extension;

        // Verificar que la extensión y el agente son válidos en el sistema
        $listaExtensiones = $this->listarExtensiones();
        $listaAgentes = $this->listarAgentes();
        if (!in_array($sAgente, array_keys($listaAgentes))) {
            return $this->Response_LoginAgentResponse('logged-out', 'Invalid agent number');
        } elseif (!in_array($sExtension, array_keys($listaExtensiones))) {
            return $this->Response_LoginAgentResponse('logged-out', 'Invalid extension number');
        }
        
        // Verificar si el número de agente no está ya ocupado por otra extensión
        $sCanalExt = $this->obtenerCanalLoginAgente($sAgente);
        if (!is_null($sCanalExt)) {
            // Hay un canal de login. Verificar si es el nuestro.
            $sRegexp = "|^\w+/(\\d+)-|"; $regs = NULL;
            if (preg_match($sRegexp, $sCanalExt, $regs)) {
                /* No se puede aceptar que el agente esté ya logoneado, incluso
                 * con la extensión que se ha pedido, porque no se tiene la
                 * información de estado del agente (Uniqueid, id_sesion, etc)
                 * hasta que se implemente la recolección de tales variables
                 * a partir de Asterisk y la base de datos call_center. La 
                 * excepción es si el programa ya hace seguimiento del agente
                 * indicado. */
                
                $infoSeguimiento = $this->_dialProc->infoSeguimientoAgente($sAgente);
                if ($regs[1] == $sExtension && !is_null($infoSeguimiento)) {
                    // Ya está logoneado este agente. Se procede directamente a interfaz
                    return $this->Response_LoginAgentResponse('logged-in');
                } else {
                    // Otra extensión ya ocupa el login del agente indicado, o no se dispone de traza
                    return $this->Response_LoginAgentResponse('logged-out',
                        'Specified agent already connected to extension: '.$regs[1]);
                }
            } else {
                // No se reconoce el canal de login
                return $this->Response_LoginAgentResponse('logged-out',
                    'Unable to parse extension from channel: '.$sCanalExt);
            }                
        } else {
            // No hay canal de login. Se inicia login a través de Originate
            $r = $this->loginAgente($listaExtensiones[$sExtension], $sAgente);
            $this->oMainLog->output("DEBUG: loginAgente responde: ".print_r($r, 1));
            return $this->Response_LoginAgentResponse('logging');            
        }

    }

    /**
     * Método que lista todas las extensiones SIP e IAX que están definidas en
     * el sistema. Estas extensiones pueden ser usadas por el agente para 
     * logonearse en el sistema. La lista se devuelve de la forma 
     * (1000 => 'SIP/1000'), ...
     *
     * @return  mixed   La lista de extensiones.
     */
    private function listarExtensiones()
    {
        // TODO: verificar si esta manera de consultar funciona para todo 
        // FreePBX. Debe de poder identificarse extensiones sin asumir una 
        // tecnología en particular. 
        $oDB = $this->_abrirConexionFreePBX();
        if (is_null($oDB)) return NULL;
        $sPeticion = <<<LISTA_EXTENSIONES
SELECT extension,
    (SELECT COUNT(*) FROM iax WHERE iax.id = users.extension) AS iax,
    (SELECT COUNT(*) FROM sip WHERE sip.id = users.extension) AS sip
FROM users ORDER BY extension
LISTA_EXTENSIONES;
        $recordset = $oDB->query($sPeticion);
        if (DB::isError($recordset)) {
            $this->oMainLog->output('ERR: (internal) Cannot list extensions - '.$recordset->getMessage());
            return NULL;
        }

        $listaExtensiones = array();
        while ($tupla = $recordset->fetchRow(DB_FETCHMODE_ASSOC)) {
            $sTecnologia = NULL;
            if ($tupla['iax'] > 0) $sTecnologia = 'IAX2/';
            if ($tupla['sip'] > 0) $sTecnologia = 'SIP/';
            
            // Cómo identifico las otras tecnologías?
            if (!is_null($sTecnologia)) {
                $listaExtensiones[$tupla['extension']] = $sTecnologia.$tupla['extension'];
            }
        }
        return $listaExtensiones;
    }

    /**
     * Método que lista todos los agentes registrados en la base de datos. La
     * lista se devuelve de la forma (9000 => 'Over 9000!!!'), ...
     *
     * @return  mixed   La lista de agentes activos
     */
    private function listarAgentes()
    {
        $sPeticion = "SELECT number, name FROM agent WHERE estatus = 'A' ORDER BY number";
        $recordset = $this->_dbConn->query($sPeticion);
        if (DB::isError($recordset)) {
            $this->oMainLog->output('ERR: (internal) Cannot list agents - '.$recordset->getMessage());
        	return NULL;
        }
        
        $listaAgentes = array();
        while ($tupla = $recordset->fetchRow(DB_FETCHMODE_ASSOC)) {
            $listaAgentes['Agent/'.$tupla['number']] = $tupla['number'].' - '.$tupla['name'];
        }        
        return $listaAgentes;
    }

    /**
     * Método para verificar cuál es el canal para el login del agente. Si no
     * se encuentra este canal, se deduce que se ha cancelado/deslogoneado el
     * login del agente. 
     *
     * @param   string  $sAgente    Número del agente que se busca
     *
     * @return  string  Canal por el cual se realiza el login del agente, o NULL
     */
    private function obtenerCanalLoginAgente($sAgente)
    {
        // Validar que sólo se use Agent/9000 como formato, y aislar el número de agente
        $regs = NULL;
        if (!preg_match('|^Agent/(\d+)$|', $sAgente, $regs))
            return NULL;
        $sAgente = $regs[1];

        /* Ejemplo de login de extensión 1064 para agente 9000:
        srv64local*CLI> core show channels
        Channel              Location             State   Application(Data)             
        SIP/1064-00000001    *88889000@from-inter Up      AgentLogin(9000)              
        */
        $r = $this->_astConn->Command('core show channels');
        if (isset($r['data'])) {
            $listaLineas = explode("\n", $r['data']);
            
            // TODO: el *8888 debería parametrizarse
            $sPista1 = '*8888'.$sAgente.'@';
            $sPista2 = 'AgentLogin('.$sAgente.')';
            foreach ($listaLineas as $sLinea) {
                $tupla = preg_split('/\s+/', $sLinea);
                if (count($tupla) >= 3 && substr($tupla[1], 0, strlen($sPista1)) == $sPista1 && $tupla[3] == $sPista2)
                    return $tupla[0];
            }
        }
        return NULL;
    }

    /**
     * Método para iniciar el login del agente con la extensión y el número de
     * agente que se indican. 
     *
     * @param   string  Extensión que está usando el agente, como "SIP/1064"
     * @param   string  Cadena del agente que se está logoneando: "Agent/9000"
     *
     * @return  VERDADERO en éxito, FALSE en error
     */
    private function loginAgente($sExtension, $sAgente)
    {
        // Validar que sólo se use Agent/9000 como formato, y aislar el número de agente
        $regs = NULL;
        if (!preg_match('|^Agent/(\d+)$|', $sAgente, $regs))
            return NULL;
        $sNumAgente = $regs[1];
        $r = $this->_astConn->Originate(
            $sExtension,        // channel
            "*8888".$sNumAgente,   // extension
            'from-internal',    // context
            1,                  // priority
            NULL,NULL, NULL, NULL, NULL, NULL,
            TRUE,               // async
            'ECCP:1.0:'.posix_getpid().':AgentLogin:'.$sAgente     // action-id
            );
        return $r;
    }
   
    // Función que encapsula la generación de la respuesta
    private function Response_LogoutAgentResponse($status, $msg = NULL)
    {
        $xml_response = new SimpleXMLElement('<response />');
        $xml_loginAgentResponse = $xml_response->addChild('logoutagent_response');

        $xml_loginAgentResponse->addChild('status', $status);
        if (!is_null($msg))
            $this->_agregarRespuestaFallo($xml_loginAgentResponse, 417, $msg);                
        return $xml_response;           
    }

    /**
     * Procedimiento que implementa el logoff de un agente estático al estilo
     * Agent/9000.
     * 
     * Implementación: las tareas a hacer para iniciar el login del agente son:
     * 1) Verificar si el agente existe en el sistema. Si no existe, se devuelve
     *    error sin hacer otra operación.
     * 2) El logoff sólo está implementado para agentes de tipo Agent/9000. Si
     *    se especifica otro tipo de agente, se rechaza con error de no 
     *    implementado. De otro modo, se recoge el número de agente (9000)
     * 3) Se ejecuta el comando de AMI Agentlogoff() con el número de agente
     * Para el resto del procesamiento se debe ver el método OnAgentlogoff en
     * la clase DialerProcess.
     * 
     * @param   object   $comando    Comando de logout
     *      <logoutagent>
     *          <agent_number>Agent/9000</agent_number>
     *      </logoutagent>
     * 
     * @return  object  Respuesta codificada como un SimpleXMLObject
     *      <logoutagent_response>
     *          <status>logged-out</status>
     *          <failure>mensaje</failure>
     *      </logoutagent_response>
     */
    private function Request_LogoutAgent($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');

        // Verificar que agente está presentes
        if (!isset($comando->agent_number)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $sAgente = (string)$comando->agent_number;

        // Verificar que el agente sea válido en el sistema
        $listaAgentes = $this->listarAgentes();
        if (!in_array($sAgente, array_keys($listaAgentes))) {
            return $this->Response_LogoutAgentResponse('logged-out', 'Invalid agent number');
        }

        /* Ejecutar Agentlogoff. Esto asume que el agente está de la forma 
         * Agent/9000. La actualización de las bases de datos de auditoría y 
         * breaks se delega a los manejadores de eventos */
        if (preg_match('|^Agent/(\d+)$|', $sAgente, $regs)) {
            $sNumAgente = $regs[1];
            $r = $this->_astConn->Agentlogoff($sNumAgente);
            $this->oMainLog->output("DEBUG: Agentlogoff($sNumAgente) -> ".print_r($r, 1));
            return $this->Response_LogoutAgentResponse('logged-out');
        }

        // No se ha implementado Agentlogoff para otros tipos de agente
        return $this->_generarRespuestaFallo(501, 'Not Implemented');
    }

    // Función que encapsula la generación de la respuesta
    private function Response_GetAgentStatusResponse($status, $msg = NULL)
    {
        $xml_response = new SimpleXMLElement('<response />');
        $xml_loginAgentResponse = $xml_response->addChild('getagentstatus_response');

        $xml_loginAgentResponse->addChild('status', $status);
        if (!is_null($msg))
            $this->_agregarRespuestaFallo($xml_loginAgentResponse, 417, $msg);                
        return $xml_response;           
    }
    
    /**
     * Procedimiento que implementa la verificación del estado de un agente 
     * estático al estilo Agent/9000.
     * 
     * @param   object   $comando    Comando
     *      <getagentstatus>
     *          <agent_number>Agent/9000</agent_number>
     *      </getagentstatus>
     * 
     * @return  object  Respuesta codificada como un SimpleXMLObject
     *      <getagentstatus_response>
     *          <status>offline|online|oncall|paused</status>
     *          <failure>mensaje</failure>
     *      </getagentstatus_response>
     */
    private function Request_GetAgentStatus($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');
        
        // Verificar que agente está presentes
        if (!isset($comando->agent_number)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $sAgente = (string)$comando->agent_number;

        // El siguiente código asume formato Agent/9000
        if (!preg_match('|^Agent/(\d+)$|', $sAgente, $regs)) {
            return $this->Response_GetAgentStatusResponse('offline', 'Invalid agent number');
        }
        $sNumAgente = $regs[1];
        $oPredictor = new Predictivo($this->_astConn);
        $estadoCola = $oPredictor->leerEstadoCola(''); // El parámetro vacío lista todas las colas
        if (!isset($estadoCola['members'][$sNumAgente])) {
            return $this->Response_GetAgentStatusResponse('offline', 'Invalid agent number');
        }
                
        // Reportar los estados conocidos 
        $estadoAgente = $estadoCola['members'][$sNumAgente];
        if (in_array('paused', $estadoAgente['attributes'])) {
            return $this->Response_GetAgentStatusResponse('paused');
        }
        if ($estadoAgente['status'] == 'inUse') {
        	return $this->Response_GetAgentStatusResponse('oncall');
        }
        if ($estadoAgente['status'] == 'canBeCalled') {
            return $this->Response_GetAgentStatusResponse('online');
        }
        if ($estadoAgente['status'] == 'unAvailable') {
            return $this->Response_GetAgentStatusResponse('offline');
        }

        return $this->Response_GetAgentStatusResponse('offline', 'Unknown status');
    }
    
    /**
     * Procedimiento que implementa la lectura de la información estática de 
     * una campaña entrante o saliente. Por información estática se entiende la
     * información que no cambia a medida que se progresa con las llamadas
     * asociadas a la campaña.
     * 
     * @param   object  $comando    Comando
     *      <getcampaigninfo>
     *          <campaign_type>outgoing|incoming</campaign_type> <!-- Opcional, por omisión es outgoing -->
     *          <campaign_id>123</campaign_id>
     *      </getcampaigninfo>
     * 
     * @return  object  Respuesta codificada como un SimpleXMLObject
     *      <getcampaigninfo_response>
     *          <name>Nombre de la campaña</name>
     *          <type>incoming|outgoing</type>
     *          <startdate>yyyy-mm-dd</startdate>
     *          <enddate>yyyy-mm-dd</enddate>
     *          <working_time_starttime>hh:mm:ss</working_time_starttime>
     *          <working_time_endtime>hh:mm:ss</working_time_endtime>
     *          <queue>8000</queue>
     *          <retries>5</retries>                <!-- Sólo saliente -->
     *          <trunk>SIP/saliente</trunk>         <!-- Sólo saliente. Si no presente, se asume Local/xxx@from-internal -->
     *          <context>from-internal</context>    <!-- Sólo saliente -->
     *          <maxchan>32</maxchan>               <!-- Sólo saliente -->
     *          <status>active|inactive|complete</status>
     *          <script>Texto a usar como script de la campaña</script>
     *          <form id="2">...</form>
     *          <form id="3">...</form>
     *      </getcampaigninfo_response> 
     */
    private function Request_GetCampaignInfo($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');

        // Verificar que id y tipo está presente
        if (!isset($comando->campaign_id)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $idCampania = (int)$comando->campaign_id;
        $sTipoCampania = 'outgoing';
        if (isset($comando->campaign_type)) {
            $sTipoCampania = (string)$comando->campaign_type;
        }

        switch ($sTipoCampania) {
        case 'incoming':
            return $this->_leerInfoCampaniaXML_incoming($idCampania);
        case 'outgoing':
            return  $this->_leerInfoCampaniaXML_outgoing($idCampania);
        default:
            return $this->_generarRespuestaFallo(400, 'Bad request');
        }
    }
    
    private function _leerInfoCampaniaXML_outgoing($idCampania)
    {
        $xml_response = new SimpleXMLElement('<response />');
        $xml_GetCampaignInfoResponse = $xml_response->addChild('getcampaigninfo_response');

        // Leer la información de la campaña saliente
        $sPeticionSQL = <<<LEER_CAMPANIA
SELECT name, 'outgoing' AS type, datetime_init AS startdate, datetime_end AS enddate,
    daytime_init AS working_time_starttime, daytime_end AS working_time_endtime, 
    queue, retries, trunk, context, max_canales AS maxchan, estatus AS status,
    script
FROM campaign WHERE id = ?
LEER_CAMPANIA;
        $tuplaCampania = $this->_dbConn->getRow($sPeticionSQL, array($idCampania), DB_FETCHMODE_ASSOC);
        if (DB::isError($tuplaCampania)) {
            $this->oMainLog->output("ERR: no se puede leer información de la campaña - ".$tuplaCampania->getMessage());
            $this->_agregarRespuestaFallo($xml_GetCampaignInfoResponse, 500, 'Cannot read campaign info');
            return $xml_response;
        }
        if (count($tuplaCampania) <= 0) {
            $this->_agregarRespuestaFallo($xml_GetCampaignInfoResponse, 404, 'Campaign not found');
            return $xml_response;
        }

        // Leer la lista de formularios asociados a esta campaña
        $idxForm = $this->_dbConn->getCol(
            'SELECT DISTINCT id_form FROM campaign_form WHERE id_campaign = ?',
            0, array($idCampania));
        if (DB::isError($idxForm)) {
            $this->oMainLog->output("ERR: no se puede leer información de la campaña (formularios) - ".$idxForm->getMessage());
            $this->_agregarRespuestaFallo($xml_GetCampaignInfoResponse, 500, 'Cannot read campaign info (forms)');
            return $xml_response;
        }
        
        // Leer los campos asociados a cada formulario
        $listaForm = $this->_leerCamposFormulario($idxForm);
        if (is_null($listaForm)) {
            $this->_agregarRespuestaFallo($xml_GetCampaignInfoResponse, 500, 'Cannot read campaign info (formfields)');
            return $xml_response;
        }

        // Construir la respuesta con la información del campo
        $descEstados = array(
            'A' =>  'active',
            'I' =>  'inactive',
            'T' =>  'finished',
        );
        foreach ($tuplaCampania as $sKey => $sValor) {
        	switch ($sKey) {
            case 'status':
                $sValor = $descEstados[$sValor];
                // Cae al siguiente caso
            case 'trunk':
                // Pasar al caso default si el valor no es nulo
                if (is_null($sValor)) break;
            default:
                $xml_GetCampaignInfoResponse->addChild($sKey, $sValor);
                break;
            }
        }

        // Construir la información de los formularios
        $xml_Forms = $xml_GetCampaignInfoResponse->addChild('forms');
        foreach ($listaForm as $idForm => $listaCampos) {
        	$this->_agregarCamposFormulario($xml_Forms, $idForm, $listaCampos);
        }

        return $xml_response;
    }
    
    private function _leerInfoCampaniaXML_incoming($idCampania)
    {
        $xml_response = new SimpleXMLElement('<response />');
        $xml_GetCampaignInfoResponse = $xml_response->addChild('getcampaigninfo_response');

        // Leer la información de la campaña entrante
        $sPeticionSQL = <<<LEER_CAMPANIA
SELECT name, 'incoming' AS type, datetime_init AS startdate, datetime_end AS enddate,
    daytime_init AS working_time_starttime, daytime_end AS working_time_endtime,
    queue, campaign_entry.estatus AS status, campaign_entry.script, id_form
FROM campaign_entry, queue_call_entry
WHERE campaign_entry.id = ? AND campaign_entry.id_queue_call_entry = queue_call_entry.id
LEER_CAMPANIA;
        $tuplaCampania = $this->_dbConn->getRow($sPeticionSQL, array($idCampania), DB_FETCHMODE_ASSOC);
        if (DB::isError($tuplaCampania)) {
            $this->oMainLog->output("ERR: no se puede leer información de la campaña - ".$tuplaCampania->getMessage());
            $this->_agregarRespuestaFallo($xml_GetCampaignInfoResponse, 500, 'Cannot read campaign info');
            return $xml_response;
        }
        if (count($tuplaCampania) <= 0) {
            $this->_agregarRespuestaFallo($xml_GetCampaignInfoResponse, 404, 'Campaign not found');
            return $xml_response;
        }

        // Leer la lista de formularios asociados a esta campaña
        $idxForm = array($tuplaCampania['id_form']);
        unset($tuplaCampania['id_form']);
        
        // Leer los campos asociados a cada formulario
        $listaForm = $this->_leerCamposFormulario($idxForm);
        if (is_null($listaForm)) {
            $this->_agregarRespuestaFallo($xml_GetCampaignInfoResponse, 500, 'Cannot read campaign info (formfields)');
            return $xml_response;
        }

        // Construir la respuesta con la información del campo
        $descEstados = array(
            'A' =>  'active',
            'I' =>  'inactive',
            'T' =>  'finished',
        );
        foreach ($tuplaCampania as $sKey => $sValor) {
            switch ($sKey) {
            case 'status':
                $sValor = $descEstados[$sValor];
                // Cae al siguiente caso
            default:
                $xml_GetCampaignInfoResponse->addChild($sKey, $sValor);
                break;
            }
        }

        // Construir la información de los formularios
        $xml_Forms = $xml_GetCampaignInfoResponse->addChild('forms');
        foreach ($listaForm as $idForm => $listaCampos) {
            $this->_agregarCamposFormulario($xml_Forms, $idForm, $listaCampos);
        }

        return $xml_response;
    }
    
    private function _leerCamposFormulario($idxForm)
    {
        $listaForm = array();
        foreach ($idxForm as $idForm) {
            $r = $this->_dbConn->getAll(
                'SELECT id, etiqueta AS label, value, tipo AS type, orden AS `order` '.
                'FROM form_field WHERE id_form = ? ORDER BY `order`', 
                array($idForm), DB_FETCHMODE_ASSOC);
            if (DB::isError($r)) {
                $this->oMainLog->output("ERR: no se puede leer información de la campaña (campos) - ".
                    $r->getMessage());
            	return NULL;
            } elseif (count($r) > 0) {
                $listaForm[$idForm] = array();
                foreach ($r as $tuplaCampo)
                    $listaForm[$idForm][$tuplaCampo['id']] = $tuplaCampo;
            }
        }
        return $listaForm;
    }
    
    private function _agregarCamposFormulario(&$xml_GetCampaignInfoResponse, $idForm, &$listaCampos)
    {
        $xml_Form = $xml_GetCampaignInfoResponse->addChild('form');
        $xml_Form->addAttribute('id', $idForm);
        foreach ($listaCampos as $tuplaCampo) {
            $xml_Field = $xml_Form->addChild('field');
            $xml_Field->addAttribute('order', $tuplaCampo['order']);
            $xml_Field->addAttribute('id', $tuplaCampo['id']);
            $xml_Field->addChild('label', $tuplaCampo['label']);
            $xml_Field->addChild('type', $tuplaCampo['type']);
            
            // TODO: permitir especificar longitud de la entrada
            if (!in_array($tuplaCampo['type'], array('LABEL', 'DATE'))) 
                $xml_Field->addChild('maxsize', 250);
            
            if ($tuplaCampo['type'] == 'LIST') {
                // OJO: PRIMERA FORMA ANORMAL!!!
                // La implementación actual del código de formulario
                // agrega una coma de más al final de la lista
                if (strlen($tuplaCampo['value']) > 0 && 
                    substr($tuplaCampo['value'], strlen($tuplaCampo['value']) - 1, 1) == ',') {
                    $tuplaCampo['value'] = substr($tuplaCampo['value'], 0, strlen($tuplaCampo['value']) - 1);
                }
                $xml_Values = $xml_Field->addChild('options');
                foreach (explode(',', $tuplaCampo['value']) as $sValor) {
                    $xml_Values->addChild('value', $sValor);
                }
            } else {
            	// Usar el valor 'value' como valor por omisión. 
                // TODO: (2011-02-02) soporte de formulario para valor por 
                // omisión todavía no está implementado en agent_console o en 
                // definición de formulario en interfaz web
                $sDefVal = trim($tuplaCampo['value']);
                if ($sDefVal != '') 
                    $xml_Field->addChild('default_value', $sDefVal);
            }
        }
    }
    
    private function Request_GetCallInfo($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');

        // Si no hay un tipo de campaña, se asume saliente
        $sTipoCampania = 'outgoing';
        if (isset($comando->campaign_type)) {
            $sTipoCampania = (string)$comando->campaign_type;
        }
        if (!in_array($sTipoCampania, array('incoming', 'outgoing')))
            return $this->_generarRespuestaFallo(400, 'Bad request');

        // El ID de campaña es opcional para campañas entrantes
        if (!isset($comando->campaign_id) && $sTipoCampania == 'incoming') 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $idCampania = isset($comando->campaign_id) ? (int)$comando->campaign_id : NULL; 

        // Verificar que id de llamada está presente
        if (!isset($comando->call_id)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $idLlamada = (int)$comando->call_id;

        // Ejecutar la llamada y verificar la respuesta...
        $infoLlamada = $this->_dialProc->leerInfoLlamada($sTipoCampania, $idCampania, $idLlamada);

        $xml_response = new SimpleXMLElement('<response />');
        $xml_GetCallInfoResponse = $xml_response->addChild('getcallinfo_response');
        if (is_null($infoLlamada)) {
            $this->_agregarRespuestaFallo($xml_GetCallInfoResponse, 500, 'Cannot read call info');
            return $xml_response;
        }
        if (count($infoLlamada) <= 0) {
            $this->_agregarRespuestaFallo($xml_GetCallInfoResponse, 404, 'Call not found');
            return $xml_response;
        }

        // Armar la respuesta XML
        $this->_construirRespuestaCallInfo($infoLlamada, $xml_GetCallInfoResponse);
        return $xml_response;
    }
    
    // Compartido entre getcallinfo y evento agentlinked
    private function _construirRespuestaCallInfo($infoLlamada, $xml_GetCallInfoResponse)
    {
        foreach ($infoLlamada as $sKey => $valor) {
            switch ($sKey) {
            case 'call_attributes':
                $xml_callAttrlist = $xml_GetCallInfoResponse->addChild($sKey);
                foreach ($valor as $tuplaAttr) {
                    $xml_callAttr = $xml_callAttrlist->addChild('attribute');
                    $xml_callAttr->addChild('label', $tuplaAttr['label']); 
                    $xml_callAttr->addChild('value', $tuplaAttr['value']);
                    $xml_callAttr->addChild('order', $tuplaAttr['order']);
                }
                break;
            case 'matching_contacts':
                $xml_contacts = $xml_GetCallInfoResponse->addChild($sKey);
                foreach ($valor as $id_contact => $tuplaContact) {
                    $xml_callAttrlist = $xml_contacts->addChild('contact');
                    $xml_callAttrlist->addAttribute('id', $id_contact);
                    foreach ($tuplaContact as $tuplaAttr) {
                        $xml_callAttr = $xml_callAttrlist->addChild('attribute');
                        $xml_callAttr->addChild('label', $tuplaAttr['label']); 
                        $xml_callAttr->addChild('value', $tuplaAttr['value']);
                        $xml_callAttr->addChild('order', $tuplaAttr['order']);
                    }
                }
                break;
            case 'call_survey':
                $xml_callFormlist = $xml_GetCallInfoResponse->addChild($sKey);
                foreach ($valor as $id_form => $valoresForm) {
                    $xml_callForm = $xml_callFormlist->addChild('form');
                    $xml_callForm->addAttribute('id', $id_form);
                    foreach ($valoresForm as $tuplaValor) {
                        $xml_callFormField = $xml_callForm->addChild('field');
                        $xml_callFormField->addAttribute('id', $tuplaValor['id']);
                        $xml_callFormField->addChild('label', $tuplaValor['label']);
                        $xml_callFormField->addChild('value', $tuplaValor['value']);
                    }
                }
                break;
            default:
                if (!is_null($valor)) $xml_GetCallInfoResponse->addChild($sKey, $valor);
                break;
            }
        }
    }

    private function Request_SetContact($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');

        // Verificar que id de llamada está presente
        if (!isset($comando->call_id)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $idLlamada = (int)$comando->call_id;

        // Verificar que id de contacto está presente
        if (!isset($comando->contact_id)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $idContacto = (int)$comando->contact_id;

        $xml_response = new SimpleXMLElement('<response />');
        $xml_setContactResponse = $xml_response->addChild('setcontact_response');

        $bExito = TRUE;

        // Verificar que existe realmente la llamada entrante
        if ($bExito) {
        	$tupla = $this->_dbConn->getRow(
                'SELECT COUNT(*) AS N FROM call_entry WHERE id = ?',
                array($idLlamada),
                DB_FETCHMODE_ASSOC);
            if (DB::isError($tupla)) {
                $this->oMainLog->output('ERR: no se puede consultar ID de llamada - '.$tupla->getMessage());
            	$this->_agregarRespuestaFallo($xml_setContactResponse, 500, 'Cannot check call ID');
                $bExito = FALSE;
            } elseif ($tupla['N'] < 1) {
                $this->_agregarRespuestaFallo($xml_setContactResponse, 404, 'Call ID not found');
            	$bExito = FALSE;
            }
        }
        
        // Verificar que existe realmente el contacto indicado
        if ($bExito) {
            $tupla = $this->_dbConn->getRow(
                'SELECT COUNT(*) AS N FROM contact WHERE id = ?',
                array($idContacto),
                DB_FETCHMODE_ASSOC);
            if (DB::isError($tupla)) {
                $this->oMainLog->output('ERR: no se puede consultar ID de contacto - '.$tupla->getMessage());
                $this->_agregarRespuestaFallo($xml_setContactResponse, 500, 'Cannot check contact ID');
                $bExito = FALSE;
            } elseif ($tupla['N'] < 1) {
                $this->_agregarRespuestaFallo($xml_setContactResponse, 404, 'Contact ID not found');
                $bExito = FALSE;
            }
        }
        
        if ($bExito) {
        	$r = $this->_dbConn->query('UPDATE call_entry SET id_contact = ? WHERE id = ?',
                array($idContacto, $idLlamada));
            if (DB::isError($r)) {
                $this->oMainLog->output('ERR: no se puede actualizar ID de contacto - '.$r->getMessage());
                $this->_agregarRespuestaFallo($xml_setContactResponse, 500, 'Cannot update contact ID for call');
            	$bExito = FALSE;
            }
        }

        if ($bExito) {
            $xml_setContactResponse->addChild('success');
        }

        return $xml_response;
    }
    
    private function Request_GetCampaignStatus($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');
        return $this->_generarRespuestaFallo(501, 'Not Implemented');
    }
    
    private function Request_Dial($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');
        return $this->_generarRespuestaFallo(501, 'Not Implemented');
    }
    
    private function Request_Hangup($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');
        return $this->_generarRespuestaFallo(501, 'Not Implemented');
    }
    
    private function Request_Hold($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');
        return $this->_generarRespuestaFallo(501, 'Not Implemented');
    }
    
    private function Request_TransferCall($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');
        return $this->_generarRespuestaFallo(501, 'Not Implemented');
    }
    
    private function Request_SaveFormData($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');

        // Si no hay un tipo de campaña, se asume saliente
        $sTipoCampania = 'outgoing';
        if (isset($comando->campaign_type)) {
            $sTipoCampania = (string)$comando->campaign_type;
        }
        if (!in_array($sTipoCampania, array('incoming', 'outgoing')))
            return $this->_generarRespuestaFallo(400, 'Bad request');

        // Verificar que id de llamada está presente
        if (!isset($comando->call_id)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $idLlamada = (int)$comando->call_id;

        // Verificar que elemento forms está presente
        if (!isset($comando->forms)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $infoDatos = array();
        foreach ($comando->forms->form as $xml_form) {
        	$idForm = (int)$xml_form['id'];
            
            // No se permiten IDs duplicados de formulario
            if (isset($infoDatos[$idForm]))
                return $this->_generarRespuestaFallo(400, 'Bad request');
            
            $infoDatos[$idForm] = array();
            foreach ($xml_form->field as $xml_field) {
            	$idField = (int)$xml_field['id'];
                $infoDatos[$idForm][$idField] = (string)$xml_field;
            }
        }

        $xml_response = new SimpleXMLElement('<response />');
        $xml_saveFormDataResponse = $xml_response->addChild('saveformdata_response');

        // Leer la información del formulario, para validación
        $infoFormulario = $this->_leerCamposFormulario(array_keys($infoDatos));
        if (is_null($infoFormulario)) {
        	$this->_agregarRespuestaFallo($xml_saveFormDataResponse, 500, 'Cannot read form information');
        } else {
            $listaSQL = array();
            
            /* Validación básica de los valores a guardar, combinada con 
             * generación de las sentencias SQL para almacenar */
            $bDatosValidos = TRUE;
            foreach ($infoDatos as $idForm => $infoDatosForm) {
        		foreach ($infoDatosForm as $idField => $sValor) {
        			if (!isset($infoFormulario[$idForm])) {
                        $bDatosValidos = FALSE;
                        $this->_agregarRespuestaFallo($xml_saveFormDataResponse, 404, 'Form ID not found: '.$idForm);
        			} elseif (!isset($infoFormulario[$idForm][$idField])) {
                        $bDatosValidos = FALSE;
                        $this->_agregarRespuestaFallo($xml_saveFormDataResponse, 404, 'Field ID not found in form: '.$idForm.' - '.$idField);
        			}
                    if (!$bDatosValidos) break;

                    $infoCampo = $infoFormulario[$idForm][$idField];
                    if ($infoCampo['type'] == 'LABEL') continue; 
                    
                    // TODO: extraer máxima longitud de base de datos
                    if (strlen($sValor) > 250) {
                    	$bDatosValidos = FALSE;
                        $this->_agregarRespuestaFallo($xml_saveFormDataResponse, 413, 'Form value too large: '.$idForm.' - '.$idField);
                    
                    // Validar que el campo de fecha tenga valor correcto
                    } elseif ($infoCampo['type'] == 'DATE' && 
                        !(preg_match('/^\d{4}-\d{2}-\d{2}$/', $sValor) || preg_match('/^\d{4}-\d{2}-\d{2} d{2}:\d{2}:\d{2}$/', $sValor))) {
                    	$bDatosValidos = FALSE;
                        $this->_agregarRespuestaFallo($xml_saveFormDataResponse, 406, 
                            'Date format not acceptable, must be yyyy-mm-dd or yyyy-mm-dd hh:mm:ss: '.$idForm.' - '.$idField);
                    } else {
                        if ($infoCampo['type'] == 'LIST') {
                            // OJO: PRIMERA FORMA ANORMAL!!!
                            // La implementación actual del código de formulario
                            // agrega una coma de más al final de la lista
                            if (strlen($infoCampo['value']) > 0 && 
                                substr($infoCampo['value'], strlen($infoCampo['value']) - 1, 1) == ',') {
                                $infoCampo['value'] = substr($infoCampo['value'], 0, strlen($infoCampo['value']) - 1);
                            }
                            if (!in_array($sValor, explode(',', $infoCampo['value']))) {
                            	$bDatosValidos = FALSE;
                                $this->_agregarRespuestaFallo($xml_saveFormDataResponse, 406, 
                                    'Value not in list of accepted values: '.$idForm.' - '.$idField);
                            }
                        }                     	
                    }
                    if (!$bDatosValidos) break;
                    
                    // En este punto este valor es válido y se puede generar SQL
                    $listaSQL[] = array(
                        ($sTipoCampania == 'incoming') 
                            ? 'REPLACE INTO form_data_recolected_entry (id_call_entry, id_form_field, value) VALUES (?, ?, ?)'
                            : 'REPLACE INTO form_data_recolected (id_calls, id_form_field, value) VALUES (?, ?, ?)',
                        array($idLlamada, $idField, $sValor),                        
                    );
        		}
                if (!$bDatosValidos) break;
        	}
            
            // Se procede a guardar los datos del formulario
            if ($bDatosValidos) {
            	foreach ($listaSQL as $infoSQL) {
            		$r = $this->_dbConn->query($infoSQL[0], $infoSQL[1]);
                    if (DB::isError($r)) {
                    	$this->oMainLog->output('ERR: no se puede guardar información de formulario - '.$r->getMessage());
                        $this->_agregarRespuestaFallo($xml_saveFormDataResponse, 500, 'Unable to save form data');
                        $bDatosValidos = FALSE;
                        break;
                    }
            	}
            }
            
            if ($bDatosValidos) {
            	$xml_saveFormDataResponse->addChild('success');
            }
        }

        return $xml_response;
    }
    
    private function Request_PauseAgent($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');

        // Verificar que agente está presente
        if (!isset($comando->agent_number)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $sAgente = (string)$comando->agent_number;

        // Verificar que ID de break está presente
        if (!isset($comando->pause_type))
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $idBreak = (int)$comando->pause_type;

        $xml_response = new SimpleXMLElement('<response />');
        $xml_pauseAgentResponse = $xml_response->addChild('pauseagent_response');

        // El siguiente código asume formato Agent/9000
        if (!preg_match('|^Agent/(\d+)$|', $sAgente, $regs)) {
            $this->_agregarRespuestaFallo($xml_pauseAgentResponse, 417, 'Invalid agent number');
            return $xml_response;
        }
        $sNumAgente = $regs[1];

        // Verificar si el agente está siendo monitoreado y que no esté en pausa
        $infoSeguimiento = $this->_dialProc->infoSeguimientoAgente($sAgente);
        if (is_null($infoSeguimiento)) {
            $this->_agregarRespuestaFallo($xml_pauseAgentResponse, 404, 'Agent not found or not logged in through ECCP');
        	return $xml_response;
        }
        if ($infoSeguimiento['estado_consola'] != 'logged-in') {
            $this->_agregarRespuestaFallo($xml_pauseAgentResponse, 417, 'Agent currenty not logged in');
            return $xml_response;
        }
        if (!is_null($infoSeguimiento['id_break']) && 
            $infoSeguimiento['id_break'] != $idBreak) {
            $this->_agregarRespuestaFallo($xml_pauseAgentResponse, 417, 'Agent already in incompatible break');
            return $xml_response;
        }

        // Verificar si la pausa indicada existe y está activa
        $tupla = $this->_dbConn->getRow(
            'SELECT COUNT(*) AS N FROM break WHERE tipo = "B" AND status = "A" AND id = ?',
            array($idBreak), DB_FETCHMODE_ASSOC);
        if (DB::isError($tupla)) {
            $this->oMainLog->output('ERR: no se puede revisar validez de ID de break - '.$tupla->getMessage());
            $this->_agregarRespuestaFallo($xml_pauseAgentResponse, 500, 'Cannot read break information');
            return $xml_response;
        }
        if ($tupla['N'] <= 0) {
            $this->_agregarRespuestaFallo($xml_pauseAgentResponse, 404, 'Break ID not found or not active');
            return $xml_response;
        }

        // Ejecutar la pausa a través del AMI
        $bExito = $this->_dialProc->iniciarBreakAgente($sAgente, $idBreak);
        if (!$bExito) {
        	$this->_agregarRespuestaFallo($xml_pauseAgentResponse, 500, 'Unable to start agent break');
        } else {
        	$xml_pauseAgentResponse->addChild('success');
        }
        return $xml_response;
    }

    private function Request_UnpauseAgent($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');

        // Verificar que agente está presente
        if (!isset($comando->agent_number)) 
            return $this->_generarRespuestaFallo(400, 'Bad request');
        $sAgente = (string)$comando->agent_number;

        $xml_response = new SimpleXMLElement('<response />');
        $xml_unpauseAgentResponse = $xml_response->addChild('unpauseagent_response');

        // El siguiente código asume formato Agent/9000
        if (!preg_match('|^Agent/(\d+)$|', $sAgente, $regs)) {
            $this->_agregarRespuestaFallo($xml_unpauseAgentResponse, 417, 'Invalid agent number');
            return $xml_response;
        }
        $sNumAgente = $regs[1];

        // Verificar si el agente está siendo monitoreado y que no esté en pausa
        $infoSeguimiento = $this->_dialProc->infoSeguimientoAgente($sAgente);
        if (is_null($infoSeguimiento)) {
            $this->_agregarRespuestaFallo($xml_unpauseAgentResponse, 404, 'Agent not found or not logged in through ECCP');
            return $xml_response;
        }
        if ($infoSeguimiento['estado_consola'] != 'logged-in') {
            $this->_agregarRespuestaFallo($xml_unpauseAgentResponse, 417, 'Agent currenty not logged in');
            return $xml_response;
        }

        // Ejecutar la pausa a través del AMI
        $bExito = $this->_dialProc->terminarBreakAgente($sAgente);
        if (!$bExito) {
            $this->_agregarRespuestaFallo($xml_unpauseAgentResponse, 500, 'Unable to stop agent break');
        } else {
            $xml_unpauseAgentResponse->addChild('success');
        }
        return $xml_response;
    }

    private function Request_GetPauses($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');

        $xml_response = new SimpleXMLElement('<response />');
        $xml_getPausesResponse = $xml_response->addChild('getpauses_response');

        $recordset = $this->_dbConn->getAll(
            "SELECT id, name, status, tipo, description FROM break WHERE tipo = 'B' ORDER BY id",
            NULL, DB_FETCHMODE_ASSOC);
        if (DB::isError($recordset)) {
            $this->oMainLog->output('ERR: no se puede leer lista de pausas - '.$recordset->getMessage());
            $this->_agregarRespuestaFallo($xml_getPausesResponse, 500, 'Unable to fetch active pauses');
        } else {
            foreach ($recordset as $tupla) {
        		$xml_pause = $xml_getPausesResponse->addChild('pause');
                $xml_pause->addAttribute('id', $tupla['id']);
                $xml_pause->addChild('name', $tupla['name']);
                $xml_pause->addChild('status', $tupla['status']);
                $xml_pause->addChild('type', $tupla['tipo']);
                $xml_pause->addChild('description', $tupla['description']);
        	}
        }

        return $xml_response;
    }
/*    
    private function Request_GetCallStatus($comando)
    {
        if (is_null($this->_sUsuarioECCP))
            return $this->_generarRespuestaFallo(401, 'Unauthorized');
        return $this->_generarRespuestaFallo(501, 'Not Implemented');
    }
*/    
    /***************************** EVENTOS *****************************/
    
    function notificarEvento_AgentLogin($sAgente, $listaColas, $bExitoLogin)
    {
        if (is_null($this->_sUsuarioECCP)) return;
        $xml_response = new SimpleXMLElement('<event />');
        $xml_agentLoggedIn = $bExitoLogin 
            ? $xml_response->addChild('agentloggedin')
            : $xml_response->addChild('agentfailedlogin');
        $xml_agentLoggedIn->addChild('agent', $sAgente);
        if ($bExitoLogin) {
            $xml_agentQueues = $xml_agentLoggedIn->addChild('queues');

            // Reportar también las colas a las que está suscrito el agente
            if (is_array($listaColas)) foreach ($listaColas as $sCola) {
            	$xml_agentQueues->addChild('queue', $sCola);
            }
        }
        
        $s = $xml_response->asXML();
        $this->dialSrv->encolarDatosEscribir($this->sKey, $s);
    }

    function notificarEvento_AgentLogoff($sAgente, $listaColas)
    {
        if (is_null($this->_sUsuarioECCP)) return;
        $xml_response = new SimpleXMLElement('<event />');
        $xml_agentLoggedIn = $xml_response->addChild('agentloggedout');
        $xml_agentLoggedIn->addChild('agent', $sAgente);
        $xml_agentQueues = $xml_agentLoggedIn->addChild('queues');

        // Reportar también las colas a las que está suscrito el agente
        if (is_array($listaColas)) foreach ($listaColas as $sCola) {
            $xml_agentQueues->addChild('queue', $sCola);
        }
        
        $s = $xml_response->asXML();
        $this->dialSrv->encolarDatosEscribir($this->sKey, $s);
    }
    
    function notificarEvento_AgentLinked($sAgente, $sRemChannel, $infoLlamada)
    {
        if (is_null($this->_sUsuarioECCP)) return;

        $xml_response = new SimpleXMLElement('<event />');
        $xml_agentLinked = $xml_response->addChild('agentlinked');
        $infoLlamada['agent_number'] = $sAgente;
        $infoLlamada['remote_channel'] = $sRemChannel;
        $this->_construirRespuestaCallInfo($infoLlamada, $xml_agentLinked);
    	
        $s = $xml_response->asXML();
        $this->dialSrv->encolarDatosEscribir($this->sKey, $s);
    }
    
    function notificarEvento_AgentUnlinked($sAgente, $infoLlamada)
    {
        if (is_null($this->_sUsuarioECCP)) return;

        $xml_response = new SimpleXMLElement('<event />');
        $xml_agentLinked = $xml_response->addChild('agentunlinked');
        $infoLlamada['agent_number'] = $sAgente;
        foreach ($infoLlamada as $sKey => $valor) {
        	if (!is_null($valor)) $xml_agentLinked->addChild($sKey, $valor);
        }
        
        $s = $xml_response->asXML();
        $this->dialSrv->encolarDatosEscribir($this->sKey, $s);
    }
}
?>