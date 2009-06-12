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
  $Id: GestorLlamadasEntrantes.class.php,v 1.8 2009/03/06 15:10:53 alex Exp $ */

/**
 * Esta clase es un gestor de llamadas entrantes. Luego de ser instanciada,
 * se espera que con cada event Link que reciba la aplicación, se pasen los
 * parámetros del evento al método notificarLink(). De forma análoga, se
 * espera que con cada evento Unlink, se pasen los parámetros del evento
 * al método notificarUnlink(). Esta clase encapsula el ingreso y remoción
 * en la tabla de llamadas actuales entrantes, consultada por la interfaz
 * web. Aunque la clase puede ser instanciada con cada evento Link, es más
 * eficiente conservar una instancia de la clase activa por toda la vida
 * de la aplicación, ya que la clase intenta llevar un cache de la cola a la
 * que pertenece cada agente, para ahorrar consultas a la base de datos.
 */

define ('MAX_TTL_CACHE_AGENTE', 5);

class GestorLlamadasEntrantes
{
    private $_astConn;  // Conexión al Asterisk
    private $_dbConn;   // Conexión a la base de datos
    private $_oMainLog; // Objeto de administración de log

    private $_timestampCache;   // Momento en que se leyó la info del caché
    private $_cacheAgentesCola; // Cache de a qué cola pertenece cada agente
    private $_cacheColasMonitoreadas;   // Cache de las colas monitoreadas
    
    private $_tieneCampaignEntry;	// VERDADERO si hay soporte para campañas de llamadas entrantes
    private $_tieneTrunk;           // VERDADERO si hay soporte para registrar trunk de llamadas entrantes

    var $DEBUG = FALSE;

    /**
     * Constructor. Requiere una conexión ya realizada al Asterisk, así como
     * una conexión a la base de datos. 
     */
    function GestorLlamadasEntrantes(&$astman, &$dbConn, &$oLog)
    {
        $this->setAstConn($astman);
        if (!DB::isConnection($dbConn)) {
        	throw new Exception('Not a valid database connection!');
        }
        if (!($oLog instanceof AppLogger)) {
        	throw new Exception('Not a subclass of AppLogger!');
        }
        $this->_dbConn = $dbConn;
        $this->_oMainLog = $oLog;
        $this->_timestampCache = NULL;
        $this->_cacheAgentesCola = NULL;
        $this->_cacheColasMonitoreadas = NULL;
        $this->_tieneCampaignEntry = FALSE;
        $this->_tieneTrunk = FALSE;

		// Verificar si el esquema de base de datos tiene soporte de campaña entrante
		$recordset =& $dbConn->query('DESCRIBE call_entry');
		if (DB::isError($recordset)) {
			$oLog->output("ERR: no se puede consultar soporte de campaña entrante - ".$recordset->getMessage());
		} else {
			while ($tuplaCampo = $recordset->fetchRow(DB_FETCHMODE_OBJECT)) {
				if ($tuplaCampo->Field == 'id_campaign') $this->_tieneCampaignEntry = TRUE;
                if ($tuplaCampo->Field == 'trunk') $this->_tieneTrunk = TRUE;
			}
			$oLog->output('INFO: sistema actual '.
				($this->_tieneCampaignEntry ? 'sí puede' : 'no puede').
				' registrar ID de campaña entrante.');
            $oLog->output('INFO: sistema actual '.
                ($this->_tieneTrunk ? 'sí puede' : 'no puede').
                ' registrar troncal de campaña entrante.');
		}

        // Llenar el cache de datos de los agentes
        $this->actualizarCacheAgentes();
        
        // Limpiar los datos de las llamadas que no se alcanzaron a marcar término
        $this->finalizarLlamadasEnCurso();
    }

    /**
     * Función para interrogar si la conexión al Asterisk es válida
     * 
     * @return bool VERDADERO si la conexión es válida, FALSO si no.
     */
    function isAstConnValid()
    {
    	return !is_null($this->_astConn);
    }

    /**
     * Procedimiento que asigna una nueva conexión Asterisk al gestor de llamadas
     * entrantes. Este método existe para que al desechar una conexión inválida al
     * Asterisk, el objeto llamador pueda comunicar la nueva conexión al gestor de
     * llamadas entrantes, sin tener que re-instanciar el objeto.
     * 
     * @param object $astman Conexión al Asterisk a usar en lugar de la actual.
     * 
     * @return void
     */
    function setAstConn(&$astman)
    {
        if (!($astman instanceof AGI_AsteriskManager)) {
            throw new Exception('Not a subclass of AGI_AsteriskManager!');
        }
        $this->_astConn = $astman;
    }

    function setDBConn(&$dbConn)
    {
    	if (!DB::isConnection($dbConn)) {
    		throw new Exception('Not a valid PEAR DB connection!');
    	}
        $this->_dbConn = $dbConn;
    }

    /**
     * Procedimiento que lee la lista de agentes que pertenecen a cada cola, 
     * parsea la información disponible, y construye la lista de cola a la 
     * que pertenece cada agente. Sólo se almacena la información de los
     * miembros que tienen la forma "Agent/DDDDDD". También se actualiza
     * la información de las colas monitoreadas.
     */
    function actualizarCacheAgentes()
    {
        if (is_null($this->_timestampCache) || time() - $this->_timestampCache >= MAX_TTL_CACHE_AGENTE) {
            $this->_leerColasMonitoreadas();
            $this->_leerListaAgentes();
            $this->_timestampCache = time();
        }
    }
    
    /**
     * Leer las colas monitoreadas desde la base de datos.
     */
    private function _leerColasMonitoreadas()
    {
    	$lista =& $this->_dbConn->getAssoc(
            'SELECT id, queue FROM queue_call_entry WHERE estatus = "A" ORDER BY queue');
        if (!DB::isError($lista)) {
        	$this->_cacheColasMonitoreadas = $lista;
        } else {
        	$this->_oMainLog->output('ERR: no se puede leer lista de colas - '.$lista->getMessage());
        }
    }
    
    /**
     * Leer la cola a la que pertenece cada agente.
     */
    private function _leerListaAgentes()
    {
        $listaAgentes = NULL;

        if (is_null($this->_astConn)) {
        	$this->_oMainLog->output('ERR: ya no se dispone de una conexión válida al Asterisk.');
            $this->_oMainLog->output('ERR: se requiere que se indique una conexión nueva.');
        } else {
        	// Leer la información de todas las colas...
            $respuestaCola = $this->_astConn->Command('queue show');
            if (is_array($respuestaCola)) {
                if (isset($respuestaCola['data'])) {
                    $listaAgentes = array();
                    $lineasRespuesta = split("\n", $respuestaCola['data']);
                    $sColaActual = NULL;
                    foreach ($lineasRespuesta as $sLinea) {
                    	$regs = NULL;
                        if (ereg('^([[:digit:]]+)[[:space:]]+has[[:space:]]+[[:digit:]]+[[:space:]]+calls', $sLinea, $regs)) {
                    	   // Se ha encontrado el inicio de una descripción de cola
                            $sColaActual = $regs[1];
                        } elseif (ereg('^[[:space:]]+(Agent/[[:digit:]]+)', $sLinea, $regs)) {
                        	// Se ha encontrado el agente en una cola en particular
                            if (!is_null($sColaActual)) {
                                if (!isset($listaAgentes[$regs[1]]))
                                	$listaAgentes[$regs[1]] = array();
                               	array_push($listaAgentes[$regs[1]], $sColaActual);
                            }
                        }
                    }
                    $this->_cacheAgentesCola = $listaAgentes;
                } else {
                	$this->_oMainLog->output('ERR: lost synch with Asterisk AMI ("queue show" response lacks "data").');
                }
            } else {
                /* Al gestor de llamadas entrantes no le compete reiniciar la 
                 * conexión al Asterisk. Lo que se puede hacer es olvidar la
                 * referencia al objeto de conexión que ahora es inválido, y
                 * esperar a que el objeto llamador actualice una nueva conexión
                 * a usar en lugar de la que se ha desechado.
                 */             
                $this->_oMainLog->output('ERR: no se puede enviar petición de listado de colas al Asterisk, se elimina referencia a conexión!');
                $this->_oMainLog->output('ERR: cache de agentes en colas puede estar desfasado.');
                $this->_astConn = NULL;
            }
        }
    }

    /**
     * Procedimiento que debe ser llamado para notificar un evento Join. 
     * Como parte de los parámetros, se espera que exista un Queue que 
     * indique cuál es la cola a la que ha ingresado la llamada. También
     * se espera que aparezca un CallerID con el número que llama, y un
     * Uniqueid que contiene el código de la llamada a almacenar.
     * 
     * @param   array   eventParams Parámetros que fueron pasados al evento 
     * 
     * @return bool     VERDADERO si esta llamada fue ingresada a la tabla de 
     *                  llamadas en curso, FALSO si la llamada fue ignorada. 
     */
    function notificarJoin($eventParams)
    {
        if ($this->DEBUG) $this->_oMainLog->output("DEBUG: ENTER notificarJoin");
        
        $bLlamadaManejada = FALSE;

        // Asegurarse de que el caché está fresco
        // TODO: POSIBLE PUNTO DE REENTRANCIA
        $this->actualizarCacheAgentes();
        
        if (in_array($eventParams['Queue'], $this->_cacheColasMonitoreadas)) {
        	// Esta es una llamada entrante que debe de ser registrada
        	$idCampania = NULL;

			if ($this->_tieneCampaignEntry) {
	            // Buscar la campaña que está asociada a la cola actual
	            $iTimestamp = time();
	            $sFecha = date('Y-m-d', $iTimestamp);
	            $sHora = date('H:i:s', $iTimestamp);
	            $sPeticionCampania = 
	                'SELECT campaign_entry.id '.
	                'FROM campaign_entry, queue_call_entry '.
	                'WHERE campaign_entry.id_queue_call_entry = queue_call_entry.id '.
	                    'AND queue_call_entry.queue = ? '.
	                    'AND datetime_init <= ? '.
	                    'AND datetime_end >= ? '.
	                    'AND campaign_entry.estatus = "A" '.
	                    'AND queue_call_entry.estatus = "A" '.
	                    'AND ('.
	                        '(daytime_init < daytime_end AND daytime_init <= ? AND daytime_end > ?) '.
	                        'OR (daytime_init > daytime_end AND (? < daytime_init OR daytime_end < ?)))';
	            $idCampania = $this->_dbConn->getOne($sPeticionCampania, 
	                array($eventParams['Queue'], $sFecha, $sFecha, $sHora, $sHora, $sHora, $sHora));            
	            // ATENCION: $idCampania puede ser nulo
	            if (DB::isError($idCampania)) {
	                $this->_oMainLog->output("ERR: no se puede consultar posible campaña para llamada entrante - ".
	                    $idCampania->getMessage());
	                $this->_oMainLog->output('DEBUG: '.print_r($idCampania, 1));
	                $idCampania = NULL;
	            }
			}
            
            $sTrunkLlamada = '';
            if ($this->_tieneTrunk) {
                if ($this->DEBUG) {
                    $this->_oMainLog->output('DEBUG: OnJoin: se tiene Channel='.$eventParams['Channel']);
                }
                $regs = NULL;
                if (!ereg('^(.+)-[0-9a-fA-F]+$', $eventParams['Channel'], $regs)) {
                	$this->_oMainLog->output('ERR: no se puede extraer trunk a partir de Channel='.$eventParams['Channel']);
                } else {
                	$sTrunkLlamada = $regs[1];
                    if ($this->DEBUG) {
                        $this->_oMainLog->output('DEBUG: OnJoin: se tiene trunk='.$sTrunkLlamada);
                    }
                }
            }
            
            $idCola = array_search($eventParams['Queue'], $this->_cacheColasMonitoreadas);
            $camposSQL = array(
                array('id_agent',               'NULL',         null),
                array('id_queue_call_entry',    '?',            $idCola),
                array('id_contact',             'NULL',         null),
                array('callerid',               '?',            $eventParams['CallerID']),
                array('datetime_entry_queue',   'NOW()',        null),
                array('datetime_init',          'NULL',         null),
                array('datetime_end',           'NULL',         null),
                array('duration_wait',          'NULL',         null),
                array('duration',               'NULL',         null),
                array('status',                 "'en-cola'",    null),
                array('uniqueid',               '?',            $eventParams['Uniqueid']),
            );
            if ($this->_tieneCampaignEntry && !is_null($idCampania))
                $camposSQL[] = array('id_campaign', '?', $idCampania);
            if ($this->_tieneTrunk)
                $camposSQL[] = array('trunk', '?', $sTrunkLlamada);
            
            $sListaCampos = $sListaValores = '';
            $queryParams = array();
            foreach ($camposSQL as $tuplaCampo) {
            	if (strlen($sListaCampos) > 0) $sListaCampos .= ', ';
                if (strlen($sListaValores) > 0) $sListaValores .= ', ';
                $sListaCampos .= $tuplaCampo[0];
                $sListaValores .= $tuplaCampo[1];
                if (!is_null($tuplaCampo[2])) $queryParams[] = $tuplaCampo[2];
            }
            $sQueryInsert = sprintf('INSERT INTO call_entry (%s) VALUES (%s)', $sListaCampos, $sListaValores);
            if ($this->DEBUG) {
            	$this->_oMainLog->output('DEBUG: OnJoin: a punto de ejecutar ['.
                    $sQueryInsert.'] con valores ['.join($queryParams, ',').']...');
            }
            
            $resultado =& $this->_dbConn->query(
                $sQueryInsert, 
                $queryParams);
            if (DB::isError($resultado)) {
                $this->_oMainLog->output(
                    'ERR: no se puede insertar registro de llamada (log) - '.
                    $resultado->getMessage());
            }
                    
        }

        if ($this->DEBUG) $this->_oMainLog->output("DEBUG: EXIT notificarJoin");
        return $bLlamadaManejada;    	
    }
    
    /**
     * Procedimiento que debe ser llamado para notificar un evento Link. Como
     * parte de los parámetros, se espera que exista un Channel1 o Channel2
     * que contenga un agente. Según el lado que contenga el agente, se
     * examina Uniqueid[1|2] y CallerID[1|2]. Con esto se consigue el CallerID
     * y el UniqueID para ingresar a la tabla de llamadas en curso y de 
     * llamadas recibidas.
     * 
     * @param   array   eventParams Parámetros que fueron pasados al evento 
     * 
     * @return bool     VERDADERO si esta llamada fue ingresada a la tabla de 
     *                  llamadas en curso, FALSO si la llamada fue ignorada. 
     */
    function notificarLink($eventParams)
    {
        if ($this->DEBUG) $this->_oMainLog->output("DEBUG: ENTER notificarLink");
        $bLlamadaManejada = FALSE;

        // Asegurarse de que el caché está fresco
        // TODO: POSIBLE PUNTO DE REENTRANCIA
        $this->actualizarCacheAgentes();

        // Nótese que para canal 1, se requiere ID y CID 2, y viceversa.
        $sKey_Uniqueid = NULL;
        $sKey_CallerID = NULL;
        $listaColasCandidatas = NULL;
        $sNombreAgente = NULL;
        $sRemChannel = NULL;
        if (isset($eventParams['Channel1']) &&            
            isset($this->_cacheAgentesCola[$eventParams['Channel1']])) {
            $sNombreAgente = $eventParams['Channel1'];
            $sRemChannel = $eventParams['Channel2'];
            $listaColasCandidatas = $this->_cacheAgentesCola[$sNombreAgente];
            $sKey_Uniqueid = 'Uniqueid2';
            $sKey_CallerID = 'CallerID2';
        } elseif (isset($eventParams['Channel2']) && 
            isset($this->_cacheAgentesCola[$eventParams['Channel2']])) {
            $sNombreAgente = $eventParams['Channel2'];
            $sRemChannel = $eventParams['Channel1'];
            $listaColasCandidatas = $this->_cacheAgentesCola[$sNombreAgente];
            $sKey_Uniqueid = 'Uniqueid1';
            $sKey_CallerID = 'CallerID1';
        } elseif ($this->DEBUG) {
            $this->_oMainLog->output("DEBUG: no se encuentra un agente llamado ".
                "$eventParams[Channel1] ni uno llamado $eventParams[Channel2] en cache de agentes : ".
                print_r($this->_cacheAgentesCola, TRUE));
        }

        $idCallEntry = $this->_dbConn->getOne(
                'SELECT current_call_entry.id_call_entry '.
                'FROM current_call_entry, call_entry '.
                'WHERE current_call_entry.id_call_entry = call_entry.id '.
                    'AND call_entry.status = "hold" '.
                    'AND (current_call_entry.uniqueid = ? OR current_call_entry.uniqueid = ?)', 
                array($eventParams['Uniqueid1'], $eventParams['Uniqueid2']));
        if (DB::isError($idCallEntry)) {
        	$this->_oMainLog->output("ERR: no se puede consultar estado HOLD en llamadas entrantes - ".
                $idCallEntry->getMessage());
        } elseif (!is_null($idCallEntry)) {
            /* La llamada ha sido ya ingresada en current_calls, y se omite 
             * procesamiento futuro. */
            $this->_oMainLog->output("DEBUG: notificarLink(): llamada ".
                $eventParams['Uniqueid1'].'/'.$eventParams['Uniqueid2'].
                " regresa de HOLD, se omite procesamiento futuro.");
        	$result =& $this->_dbConn->query(
                'UPDATE call_entry SET status = "activa" WHERE id = ?',
                array($idCallEntry));
            if (DB::isError($result)) {
            	$this->_oMainLog->output(
                    "ERR: no se puede actualizar estado de llamada entrante (hold->activa) - ".
                    $result->getMessage());
            }
            $listaColasCandidatas = NULL;
        }

        if (!is_null($listaColasCandidatas)) {
            // Verificar que la cola se encuentra entre las colas monitoreadas
            if (count(array_intersect($listaColasCandidatas, $this->_cacheColasMonitoreadas)) > 0) {            	
                // Esta es una llamada entrante que debe de ser registrada
                
                // Obtener el ID del agente en la base, dado su identificación
                $regs = NULL;
                ereg('^[[:alnum:]]+/([[:digit:]]+)$', $sNombreAgente, $regs);
                $sNumeroAgente = $regs[1];
                $idAgente =& $this->_dbConn->getOne(
                    "SELECT id FROM agent WHERE number = ? AND estatus = 'A'",
                    array($sNumeroAgente));
                if (!DB::isError($idAgente) && is_numeric($idAgente)) {
                    $bLlamadaManejada = TRUE;
                    // Recolectar los índices de las colas monitoreadas que constan en las
                    // colas a las que pertenece el agente
                    $listaIdCola = array();
                    foreach ($this->_cacheColasMonitoreadas as $keyCola => $sColaMonitoreada) {
                    	if (in_array($sColaMonitoreada, $listaColasCandidatas)) $listaIdCola[] = $keyCola;
                    }
                    if (count($listaIdCola) == 0) {
                    	$this->_oMainLog->output(
                    		"BUG: se supone que hay al menos una cola candidada, pero no hay índices:\n".
                    			print_r($this->_cacheColasMonitoreadas, TRUE)."\n".
                    			print_r($listaColasCandidatas, TRUE));
                    	die();
                    }
                    
                    $tuplaLlamada =& $this->_dbConn->getRow(
                        'SELECT id, id_queue_call_entry, callerid FROM call_entry WHERE uniqueid = ?',
                        array($eventParams[$sKey_Uniqueid]),
                        DB_FETCHMODE_OBJECT);
                    if (DB::isError($tuplaLlamada)) {
                        $this->_oMainLog->output(
                            'ERR: no se puede leer registro de llamada (log) - '.
                            $tuplaLlamada->getMessage());
                    } elseif (is_null($tuplaLlamada)) {
                        $this->_oMainLog->output(
                            "ERR: no se encuentra registro de llamada {$eventParams[$sKey_Uniqueid]} (log)");
                    } else {
                    	$idCola = NULL;
                    	if (in_array($tuplaLlamada->id_queue_call_entry, $listaIdCola))
                    		$idCola = $tuplaLlamada->id_queue_call_entry;
                    	if ($tuplaLlamada->id_queue_call_entry != $idCola) {
                            $this->_oMainLog->output(
                                "ERR: registro de llamada {$tuplaLlamada->id} ".
                                "uniqueid={$eventParams[$sKey_Uniqueid]} indica ".
                                "ID de cola {$tuplaLlamada->id_queue_call_entry} vs. $idCola!");                    		
                    	}
                        if ($this->DEBUG && $tuplaLlamada->callerid != $eventParams[$sKey_CallerID]) {
                            $this->_oMainLog->output(
                                "ERR: registro de llamada {$tuplaLlamada->id} ".
                                "uniqueid={$eventParams[$sKey_Uniqueid]} indica ".
                                "callerid {$tuplaLlamada->callerid} vs. {$eventParams[$sKey_CallerID]}!");                            
                        }
                        $resultado =& $this->_dbConn->query(
                            'UPDATE call_entry SET id_agent = ?, datetime_init = NOW(), '.
                                'duration_wait = UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(datetime_entry_queue), '.
                                "status = 'activa' ".
                            'WHERE id = ?',
                            array($idAgente, $tuplaLlamada->id));
                        if (!DB::isError($resultado)) {
                            $resultado =& $this->_dbConn->query(
                                'INSERT INTO current_call_entry (id_agent, id_queue_call_entry, '.
                                    'id_call_entry, callerid, datetime_init, uniqueid, ChannelClient) '.
                                'VALUES (?, ?, ?, ?, NOW(), ?, ?)',
                                array($idAgente, $idCola, $tuplaLlamada->id, $eventParams[$sKey_CallerID], 
                                    $eventParams[$sKey_Uniqueid], $sRemChannel));
                            if (DB::isError($resultado)) {
                                $this->_oMainLog->output(
                                    'ERR: no se puede insertar registro de llamada (actual) - '.
                                    $resultado->getMessage());
                            }
                        } else {
                            $this->_oMainLog->output(
                                'ERR: no se puede actualizar registro de llamada (log) - '.
                                $resultado->getMessage());
                        }
                    }
                } else if (DB::isError($idAgente)) {
                	$this->_oMainLog->output(
                        'ERR: no se puede leer lista de agentes activos - '.
                        $idAgente->getMessage());
                }
            } elseif ($this->DEBUG) {
                $this->_oMainLog->output("DEBUG: cola(s) candidata(s) [".(join($listaColasCandidatas, ' '))."] no se ".
                    "encuentra en cache de colas monitoreadas: ".
                    print_r($this->_cacheColasMonitoreadas, TRUE));
            }
        }
        
        if ($this->DEBUG) $this->_oMainLog->output("DEBUG: EXIT notificarLink");
        return $bLlamadaManejada;
    }
    
    /**
     * Procedimiento que remueve una llamada ya terminada de la lista de las 
     * llamadas en curso en current_call_entry, en base a los parámetros en
     * $eventParams. La llamada es idempotente, y por lo tanto no causa daño
     * el llamarla múltiples veces (para unlink y luego para hangup).
     * 
     * @param array eventParams Parámetros que fueron pasados al evento 
     * 
     * @return bool VERDADERO si la llamada fue reconocida y procesada
     */    
    function notificarUnlink($eventParams)
    {
        if ($this->DEBUG) $this->_oMainLog->output("DEBUG: ENTER notificarUnlink");
        $bLlamadaManejada = FALSE;
        $tuplaLlamada = NULL;

        // Buscar el Uniqueid de la llamada recibida
        foreach (array('Uniqueid1', 'Uniqueid2', 'Uniqueid') as $sKey) {
            if (isset($eventParams[$sKey])) {
                $tuplaLlamada =& $this->_dbConn->getRow(
                    'SELECT id, id_call_entry, hold FROM current_call_entry WHERE uniqueid = ?',
                    array($eventParams[$sKey]),
                    DB_FETCHMODE_ASSOC);
                if (DB::isError($tuplaLlamada)) {
                    $this->_oMainLog->output(
                        'ERR: no se puede buscar registro de llamada (actual) - '.
                        $tuplaLlamada->getMessage());
                    $tuplaLlamada = NULL;                	
                } elseif (is_array($tuplaLlamada)) {
                	break;
                } else {
                	$tuplaLlamada = NULL;
                }
            }
        }

        if (is_null($tuplaLlamada) && isset($eventParams['Uniqueid'])) {
        	// Caso Hangup/abandonada - también se debe buscar en call_entry
            $tuplaLlamada =& $this->_dbConn->getRow(
                'SELECT NULL AS id, call_entry.id AS id_call_entry, NULL as hold '.
                'FROM call_entry WHERE uniqueid = ? AND datetime_end IS NULL',
                array($eventParams[$sKey]),
                DB_FETCHMODE_ASSOC);
            if (DB::isError($tuplaLlamada)) {
                $this->_oMainLog->output(
                    'ERR: no se puede buscar registro de llamada (actual) - '.
                    $tuplaLlamada->getMessage());
                $tuplaLlamada = NULL;                   
            }
        }
        
        if (!is_null($tuplaLlamada)) {
        	$bLlamadaManejada = TRUE;
            
            if (!is_null($tuplaLlamada['hold']) && $tuplaLlamada['hold'] == 'S') {
                /* En caso de que la llamada haya sido puesta en espera, la llamada 
                 * se transfiere a la cola de parqueo. Esto ocasiona un evento Unlink
                 * sobre la llamada, pero no debe de considerarse como el cierre de
                 * la llamada.
                 */
            	$this->_oMainLog->output("DEBUG: notificarUnlink - llamada ha sido puesta en HOLD en vez de colgada.");
                $result =& $this->_dbConn->query(
                    "UPDATE call_entry SET status = 'hold' WHERE id = ?",
                    array($tuplaLlamada['id_call_entry']));
                if (DB::isError($result)) {
                    $this->_oMainLog->output(
                        'ERR: no se puede actualizar registro de llamada en HOLD (log) - '.
                        $result->getMessage());
                }
                $tuplaLlamada = NULL;                   
            }
        }
        if (!is_null($tuplaLlamada)) {
            $bLlamadaManejada = TRUE;
        	if (!is_null($tuplaLlamada['id'])) {
                $result =& $this->_dbConn->query(
                    'DELETE FROM current_call_entry WHERE id = ?',
                    array($tuplaLlamada['id']));            
                if (DB::isError($result)) {
                    $this->_oMainLog->output(
                        'ERR: no se puede remover registro de llamada (actual) - '.
                        $result->getMessage());
                }
            }                   
            $result =& $this->_dbConn->query(
                'UPDATE call_entry SET datetime_end = NOW(), '.
                    'duration_wait = IF(datetime_init IS NULL, '.
                        'UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(datetime_entry_queue), '.
                        'duration_wait), '.
                    'duration = IF(datetime_init IS NULL, '.
                        'NULL, '.
                        'UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(datetime_init)), '.
                    "status = IF(datetime_init IS NULL, 'abandonada', 'terminada') ".
                'WHERE id = ?',
                array($tuplaLlamada['id_call_entry']));
            if (DB::isError($result)) {
                $this->_oMainLog->output(
                    'ERR: no se puede actualizar registro de llamada (log) - '.
                    $result->getMessage());
            }                   
        }
        
        if ($this->DEBUG) $this->_oMainLog->output("DEBUG: EXIT notificarUnlink");
        return $bLlamadaManejada;
    }
    
    /**
     * Procedimiento que intenta limpiar la tabla de llamadas en curso entrantes,
     * y marca las llamadas no terminadas en el log, como marcadas sin 
     * finalización, para mantener consistencia en el log. Este método sólo debe
     * ser llamado al construir el objeto (automáticamente), o cuando se está a 
     * punto de terminar el programa.
     * 
     * @return void
     */
    function finalizarLlamadasEnCurso()
    {
        // Remover rastro de llamadas en la lista de llamadas actuales
        $result =& $this->_dbConn->query('DELETE FROM current_call_entry');
        if (DB::isError($result)) {
            $this->_oMainLog->output(
                'ERR: no se puede limpiar registro de llamada (actual) - '.
                $result->getMessage());
        }
        
        // Marcar toda llamada sin fecha de finalización, como inválida
        $result =& $this->_dbConn->query(
            "UPDATE call_entry SET status = 'fin-monitoreo' WHERE datetime_end IS NULL");                   
        if (DB::isError($result)) {
            $this->_oMainLog->output(
                'ERR: no se puede marcar registro de llamada (log) - '.
                $result->getMessage());
        }
    }
}
?>
