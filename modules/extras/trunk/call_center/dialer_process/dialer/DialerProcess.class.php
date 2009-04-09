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
  $Id: DialerProcess.class.php,v 1.48 2009/03/26 13:46:58 alex Exp $ */
require_once('AbstractProcess.class.php');
require_once 'DB.php';
require_once "phpagi-asmanager-elastix.php";
//require_once "predictive.lib.php";
require_once('Predictivo.class.php');
require_once('GestorLlamadasEntrantes.class.php');

// Número mínimo de muestras para poder confiar en predicciones de marcador
define('MIN_MUESTRAS', 10);

//dl('sqlite3.so');

class DialerProcess extends AbstractProcess
{
    private $oMainLog;      // Log abierto por framework de demonio
    //private $_sRutaDB;      // Ruta a la base de datos sqlite3
    private $_dbHost;
    private $_dbUser;
    private $_dbPass;
    private $_dbConn;       // Conexión PEAR a la base de datos

    private $_sAsteriskHost;
    private $_sAsteriskUser;
    private $_sAsteriskPass;
    private $_astConn;      // Conexión al Asterisk Manager
    
    private $_momentoUltimaConnAsterisk;	// Timestamp de cuando se conectó por última vez al Asterisk
    private $_intervaloDesconexion;			// Intervalo de desconexión regular, o 0 para persistente (por omisión)
    
    private $_infoLlamadas;                 // Información sobre las campañas leídas, por iteración
    private $_iUmbralLlamadaCorta;          // Umbral por debajo del cual llamada es corta
    private $_bSobrecolocarLlamadas = FALSE;// VERDADERO si se intenta compensar por baja contestación mediante
                                            // colocar más llamadas de las predichas por estado de cola. 

    private $_numLlamadasOriginadas;    // Llamadas originadas sin OriginateResponse, por cola

    private $_oGestorEntrante;      // Gestor de llamadas entrantes
    
    private $_plantillasMarcado;
    
    var $DEBUG = FALSE;
    var $REPORTAR_TODO = FALSE;
    var $_iUltimoDebug = NULL;

    function inicioPostDemonio($infoConfig, &$oMainLog)
    {
        $bContinuar = TRUE;
        $this->_numLlamadasOriginadas = array();
        $this->_oGestorEntrante = NULL;
        $this->_plantillasMarcado = array();

        // Guardar referencias al log del programa
        $this->oMainLog =& $oMainLog;
        
        // Interpretar la configuración del demonio
        $this->interpretarParametrosConfiguracion($infoConfig);

        if ($bContinuar) $bContinuar = $this->iniciarConexionBaseDatos();
        $infoConfigDB = $this->leerConfiguracionDesdeDB();
        $this->aplicarConfiguracionDB($infoConfigDB);
        if ($bContinuar) $bContinuar = $this->iniciarConexionAsterisk();

        // Cerrar DB si falla la conexión al Asterisk Manager
        if (!$bContinuar && !is_null($this->_dbConn)) {        	
            $this->_dbConn->disconnect();
            $this->_dbConn = NULL;
        }

        if ($bContinuar && !is_null($this->_dbConn)) {
        	// Recuperarse de cualquier fin anormal anterior
            $this->_dbConn->query('DELETE FROM current_calls WHERE 1');
            $this->_dbConn->query('DELETE FROM current_call_entry WHERE 1');
        }

        // Iniciar gestor de llamadas entrantes
        if ($bContinuar) {
            $this->_oGestorEntrante = new GestorLlamadasEntrantes(
                $this->_astConn, $this->_dbConn, $this->oMainLog);
            $this->_oGestorEntrante->DEBUG = $this->DEBUG;
        }

        $this->_iUltimoDebug = time();
        return $bContinuar;
    }

    /* Interpretar la configuración cuyo hash se indica en el parámetro. Los 
     * parámetros de la conexión a la base de datos se recogen, pero no se usan 
     * en este punto. Lo mismo con los parámetros de conexión al Asterisk Manager. 
     */
    private function interpretarParametrosConfiguracion(&$infoConfig)
    {
        $sRutaDB = NULL;
        
        // Recoger los parámetros para la conexión a la base de datos
        $this->_dbHost = 'localhost';
        $this->_dbUser = 'asterisk';
        $this->_dbPass = 'asterisk';
        if (isset($infoConfig['database']) && isset($infoConfig['database']['dbhost'])) {
        	$this->_dbHost = $infoConfig['database']['dbhost'];
            $this->oMainLog->output('Usando host de base de datos: '.$this->_dbHost);
        } else {
        	$this->oMainLog->output('Usando host (por omisión) de base de datos: '.$this->_dbHost);
        }
        if (isset($infoConfig['database']) && isset($infoConfig['database']['dbuser']))
            $this->_dbUser = $infoConfig['database']['dbuser'];
        if (isset($infoConfig['database']) && isset($infoConfig['database']['dbpass']))
            $this->_dbPass = $infoConfig['database']['dbpass'];
    }

    // Iniciar la conexión a la base de datos con los parámetros recogidos por
    // interpretarParametrosConfiguracion().
    private function iniciarConexionBaseDatos()
    {
        // La siguiente línea asume que el programa se conecta a una base sqlite3
        //$sConnStr = 'sqlite3:///'.$this->_sRutaDB;
        $sConnStr = 'mysql://'.$this->_dbUser.':'.$this->_dbPass.'@'.$this->_dbHost.'/call_center';
        $dbConn =  DB::connect($sConnStr);
        if (DB::isError($dbConn)) {
            $this->oMainLog->output("FATAL: no se puede conectar a DB - ".($dbConn->getMessage()));
            return FALSE;
        } else {
            $dbConn->setOption('autofree', TRUE);
            $this->_dbConn = $dbConn;
            return TRUE;
        }
    } 

	// Leer la configuración de la base de datos, validando los valores, pero sin comparar contra
	// el estado actual de configuración del programa
	private function leerConfiguracionDesdeDB()
	{
		$listaConfig =& $this->_dbConn->getAssoc('SELECT config_key, config_value FROM valor_config');
		if (DB::isError($listaConfig)) {
			$this->oMainLog->output('ERR: no se puede leer configuración actual - '.$listaConfig->getMessage());
			return NULL;
		}
		$infoConfig = array(
			'asterisk'	=>	array(
				'asthost'	=>	'127.0.0.1',
				'astuser'	=>	'',
				'astpass'	=>	'',
				'duracion_sesion' => 0,
			),
			'dialer'	=>	array(
				'llamada_corta'	=>	10,
				'tiempo_contestar' => 8,	// TODO: debería ser recalculado por campaña
				'debug'			=>	0,
				'allevents'		=>	0,
                'overcommit'    =>  0,
			),
		);
		foreach ($infoConfig as $seccion => $infoSeccion) {
			foreach ($infoSeccion as $clave => $valorOmision) {
				$sClaveDB = "$seccion.$clave";
				if (isset($listaConfig[$sClaveDB])) {
					$infoConfig[$seccion][$clave] = $listaConfig[$sClaveDB]; 
				}
			}
		}
		return $infoConfig;
	}

	// Aplicar la configuración leída desde la base de datos
	private function aplicarConfiguracionDB(&$infoConfig)
	{
		$bDesconectarAsterisk = FALSE;	// Seteado a TRUE si los parámetros Asterisk han cambiado

        // Recoger los parámetros para la conexión Asterisk
        if (isset($infoConfig['asterisk']) && isset($infoConfig['asterisk']['asthost'])) {
            if ($this->_sAsteriskHost != $infoConfig['asterisk']['asthost']) { 
            	$this->_sAsteriskHost = $infoConfig['asterisk']['asthost'];
            	$this->oMainLog->output("Usando host de Asterisk Manager: ".$this->_sAsteriskHost);
            	$bDesconectarAsterisk = TRUE;
            }
        } else {
        	if ($this->_sAsteriskHost != '127.0.0.1') {
        		$this->_sAsteriskHost = '127.0.0.1';
            	$this->oMainLog->output("Usando host (por omisión) de Asterisk Manager: ".$this->_sAsteriskHost);
            	$bDesconectarAsterisk = TRUE;
        	}
        }
        $sNuevoAsteriskUser = 
            (isset($infoConfig['asterisk']) && isset($infoConfig['asterisk']['astuser'])) 
            ? $infoConfig['asterisk']['astuser']
            : '';
        $sNuevoAsteriskPass = 
            (isset($infoConfig['asterisk']) && isset($infoConfig['asterisk']['astpass']))
            ? $infoConfig['asterisk']['astpass']
            : '';
        if ($this->_sAsteriskUser != $sNuevoAsteriskUser) $bDesconectarAsterisk = TRUE;
        if ($this->_sAsteriskPass != $sNuevoAsteriskPass) $bDesconectarAsterisk = TRUE;
        $this->_sAsteriskUser = $sNuevoAsteriskUser;
        $this->_sAsteriskPass = $sNuevoAsteriskPass;

		// Recoger parámetro de tiempo de desconexión
		$bSet = isset($this->_intervaloDesconexion);
		$this->_intervaloDesconexion = 0;
		if (isset($infoConfig['asterisk']) && isset($infoConfig['asterisk']['duracion_sesion'])) {
			$regs = NULL;
			if (ereg('^[[:space:]]*([[:digit:]]+)[[:space:]]*$', $infoConfig['asterisk']['duracion_sesion'], $regs)) {
				$this->_intervaloDesconexion = $regs[1];
                if (!$bSet) $this->oMainLog->output("Usando duración de sesión Asterisk de : ".$this->_intervaloDesconexion." segundos.");
			} else {
            	if (!$bSet) {
	            	$this->oMainLog->output("ERR: valor de ".$infoConfig['asterisk']['duracion_sesion']." no es válido para duración de sesión Asterisk.");
    	            $this->oMainLog->output("Usando duración de sesión Asterisk (por omisión): ".$this->_intervaloDesconexion." segundos.");
            	}
			}
        } else {
        	if (!$bSet) $this->oMainLog->output("Usando duración de sesión Asterisk (por omision): ".$this->_intervaloDesconexion." segundos.");
		}

		// Recoger parámetro de tiempo de contestado
		$bSet = isset($this->_iTiempoContestacion);
		$this->_iTiempoContestacion = 8;
        if (isset($infoConfig['dialer']) && isset($infoConfig['dialer']['tiempo_contestar'])) {
            $regs = NULL;
            if (ereg('^[[:space:]]*([[:digit:]]+)[[:space:]]*$', $infoConfig['dialer']['tiempo_contestar'], $regs)) {
                $this->_iTiempoContestacion = $regs[1];
                if (!$bSet) $this->oMainLog->output("Usando tiempo de contestado (inicial) de : ".$this->_iTiempoContestacion." segundos.");
            } else {
            	if (!$bSet) {
	            	$this->oMainLog->output("ERR: valor de ".$infoConfig['dialer']['tiempo_contestar']." no es válido para tiempo de contestado (inicial).");
    	            $this->oMainLog->output("Usando tiempo de contestado (inicial) (por omisión): ".$this->_iTiempoContestacion." segundos.");
            	}
            }
        } else {
        	if (!$bSet) $this->oMainLog->output("Usando tiempo de contestado (inicial) (por omision): ".$this->_iTiempoContestacion." segundos.");
        }

        // Recoger parámetro de llamada corta
        $bUmbralSet = isset($this->_iUmbralLlamadaCorta);
        $this->_iUmbralLlamadaCorta = 10;
        if (isset($infoConfig['dialer']) && isset($infoConfig['dialer']['llamada_corta'])) {
            $regs = NULL;
            if (ereg('^[[:space:]]*([[:digit:]]+)[[:space:]]*$', $infoConfig['dialer']['llamada_corta'], $regs)) {
                $this->_iUmbralLlamadaCorta = $regs[1];
                if (!$bUmbralSet) $this->oMainLog->output("Usando umbral de llamada corta: ".$this->_iUmbralLlamadaCorta." segundos.");
            } else {
            	if (!$bUmbralSet) {
	            	$this->oMainLog->output("ERR: valor de ".$infoConfig['dialer']['llamada_corta']." no es válido para umbral de llamada corta.");
    	            $this->oMainLog->output("Usando umbral de llamada corta (por omisión): ".$this->_iUmbralLlamadaCorta." segundos.");
            	}
            }
        } else {
        	if (!$bUmbralSet) $this->oMainLog->output("Usando umbral de llamada corta (por omisión): ".$this->_iUmbralLlamadaCorta." segundos.");
        }
        
        // Recoger estado de sobrecolocar llamadas
        $bSobreColocar = $this->_bSobrecolocarLlamadas;
        $this->_bSobrecolocarLlamadas = FALSE;
        if (isset($infoConfig['dialer']) && isset($infoConfig['dialer']['overcommit'])) {
            $this->_bSobrecolocarLlamadas = $infoConfig['dialer']['overcommit'] ? TRUE : FALSE;
            if (!$bSobreColocar && $this->_bSobrecolocarLlamadas) 
                $this->oMainLog->output("Sobre-colocación de llamadas está ACTIVADA.");
            if ($bSobreColocar && !$this->_bSobrecolocarLlamadas) 
                $this->oMainLog->output("Sobre-colocación de llamadas está DESACTIVADA.");
        }
        
        // Recoger nivel de depuración
        $bDebugSet = isset($this->DEBUG);
        $this->DEBUG = FALSE;
        if (isset($infoConfig['dialer']) && isset($infoConfig['dialer']['debug'])) {
        	$this->DEBUG = $infoConfig['dialer']['debug'] ? TRUE : FALSE;
        	if (!$bDebugSet && $this->DEBUG) $this->oMainLog->output("Información de depuración está ACTIVADA.");
        }
        if (!is_null($this->_oGestorEntrante)) $this->_oGestorEntrante->DEBUG = $this->DEBUG;        
        $bDebugSet = isset($this->REPORTAR_TODO);
        $this->REPORTAR_TODO = FALSE;
        if (isset($infoConfig['dialer']) && isset($infoConfig['dialer']['allevents'])) {
        	$this->REPORTAR_TODO = $infoConfig['dialer']['allevents'] ? TRUE : FALSE;
        	if (!$bDebugSet && $this->REPORTAR_TODO) $this->oMainLog->output("Se reportará información de todos los eventos Asterisk.");
        }
        
        if ($bDesconectarAsterisk && !is_null($this->_astConn)) {
            $this->oMainLog->output('INFO: Cambio de configuración, desconectando de sesión previa de Asterisk...');
            $this->_astConn->disconnect();
            $this->_astConn = NULL;            
        }
	}

    // Iniciar la conexión al Asterisk Manager
    private function iniciarConexionAsterisk()
    {
        if (!is_null($this->_astConn)) {
            $this->oMainLog->output('INFO: Desconectando de sesión previa de Asterisk...');
            $this->_astConn->disconnect();
            $this->_astConn = NULL;            
        }
        $astman = new AGI_AsteriskManager();
        $this->_momentoUltimaConnAsterisk = time();
        $astman->setLogger($this->oMainLog);

        $this->oMainLog->output('INFO: Iniciando sesión de control de Asterisk...');
        if (!$astman->connect(
                $this->_sAsteriskHost, 
                $this->_sAsteriskUser,
                $this->_sAsteriskPass)) {
            $this->oMainLog->output("FATAL: no se puede conectar a Asterisk Manager\n");
            return FALSE;
        } else {
            if ($this->DEBUG && $this->REPORTAR_TODO)
                $astman->add_event_handler('*', array($this, 'OnDefault'));
            $astman->add_event_handler('Join', array($this, 'OnJoin'));
            $astman->add_event_handler('Link', array($this, 'OnLink'));
            $astman->add_event_handler('Unlink', array($this, 'OnUnlink'));
            $astman->add_event_handler('Hangup', array($this, 'OnHangup'));
            $astman->add_event_handler('OriginateResponse', array($this, 'OnOriginateResponse'));
            $astman->SetTimeout(10);
            $this->_astConn = $astman;
            if (!is_null($this->_oGestorEntrante)) { 
                $this->_oGestorEntrante->setAstConn($this->_astConn);
            }
            return TRUE;
        }
    }

    function _leerCampania($idCampania)
    {
    	$sPeticionCampania = 
            'SELECT id, name, trunk, context, queue, max_canales, num_completadas, '.
                'promedio, desviacion, retries, datetime_init, datetime_end, daytime_init, daytime_end '.
            'FROM campaign '.
            'WHERE id = ? ';
        $tupla = $this->_dbConn->getRow($sPeticionCampania, array($idCampania), DB_FETCHMODE_OBJECT);
        if (!DB::isError($tupla)) { $tupla->variancia = $tupla->desviacion * $tupla->desviacion; }
        return DB::isError($tupla) ? NULL : $tupla;
    }

    // Ejecutar la revisión periódica de las llamadas pendientes por timbrar
    function procedimientoDemonio()
    {
        $bLlamadasAgregadas = FALSE;
        $iTimestamp = time();
        $sFecha = date('Y-m-d', $iTimestamp);
        $sHora = date('H:i:s', $iTimestamp);
        $sPeticionCampanias = 
            'SELECT id, name, trunk, context, queue, max_canales, num_completadas, '.
                'promedio, desviacion, retries, datetime_init, datetime_end, daytime_init, daytime_end '.
            'FROM campaign '.
            'WHERE datetime_init <= ? '.
                'AND datetime_end >= ? '.
                'AND estatus = "A" '.
                'AND ('.
                    '(daytime_init < daytime_end AND daytime_init <= ? AND daytime_end > ?) '.
                    'OR (daytime_init > daytime_end AND (? < daytime_init OR daytime_end < ?)))';
        $recordset = $this->_dbConn->query(
            $sPeticionCampanias, 
            array($sFecha, $sFecha, $sHora, $sHora, $sHora, $sHora));
        if (DB::isError($recordset)) {
            $this->oMainLog->output("ERR: no se puede leer lista de campañas - ".$recordset->getMessage());
            usleep(1000000);
        } else {
            // Verificar si se tiene que actualizar la configuración
            $infoConfigDB = $this->leerConfiguracionDesdeDB();
            if (!is_null($infoConfigDB)) {
            	$this->aplicarConfiguracionDB($infoConfigDB);
            }
            
            if (is_null($this->_astConn)) {
            	$this->iniciarConexionAsterisk();
            } elseif ($this->_intervaloDesconexion > 0 && time() - $this->_momentoUltimaConnAsterisk >= $this->_intervaloDesconexion) {
				$this->oMainLog->output("INFO: sesión de Asterisk excede {$this->_intervaloDesconexion} segundos, se desconecta...");
            	$this->iniciarConexionAsterisk();
            }
            if (!$this->_oGestorEntrante->isAstConnValid()) {
            	// La conexión al Asterisk se perdió en medio de proceso de llamadas 
                // entrantes.                
                $this->iniciarConexionAsterisk();
            }

            if (!is_null($this->_astConn)) {
                $this->_oGestorEntrante->actualizarCacheAgentes();
                
                $listaCampanias = array();
                while ($infoCampania = $recordset->fetchRow(DB_FETCHMODE_OBJECT)) {
                    $infoCampania->variancia = NULL;
                    if (!is_null($infoCampania->desviacion) && is_numeric($infoCampania->desviacion))
                        $infoCampania->variancia = $infoCampania->desviacion * $infoCampania->desviacion;
                    $listaCampanias[$infoCampania->id] = $infoCampania;
                }
            
                // Preparar la información a asignar a datos de app en astman
                if (!is_array($this->_infoLlamadas)) $this->_infoLlamadas = array();        
                $this->_infoLlamadas['campanias'] = $listaCampanias;
                if (!isset($this->_infoLlamadas['llamadas'])) $this->_infoLlamadas['llamadas'] = array();
            
                // Agregar llamadas para todas las campañas activas
                foreach ($this->_infoLlamadas['campanias'] as $infoCampania) {
                    $bLlamadasAgregadas = $this->actualizarLlamadasCampania($infoCampania) || $bLlamadasAgregadas;
                }
                
                // Consumir todos los eventos de llamada durante 3 segundos
                $iTimestampInicioEspera = time();
                while (time() - $iTimestampInicioEspera <= 3) {
                     $this->_astConn->SetTimeout(1);
                     $r = $this->_astConn->wait_response(TRUE);
                     if (is_null($r)) {
                        // Lo siguiente debería estar interno en AG_AsteriskManager
                        $metadata = stream_get_meta_data($this->_astConn->socket);
                        if (is_array($metadata) && !$metadata['timed_out']) {
                            $this->oMainLog->output("ERR: problema al esperar respuesta de Asterisk (en bucle de espera).");
                            $this->iniciarConexionAsterisk();
                            break;
                        }
                     }
                }
                if (!is_null($this->_astConn)) $this->_astConn->SetTimeout(10);
            } else {
                $this->oMainLog->output("ERR: no se puede reconectar al Asterisk, esperando...");
                usleep(1000000);
            }
        }

		// Remover llamadas viejas luego de 5 * 60 segundos de espera sin respuesta
		$listaClaves = array_keys($this->_infoLlamadas['llamadas']);
		foreach ($listaClaves as $k ) {
			$tupla = $this->_infoLlamadas['llamadas'][$k];
			if (is_null($tupla->OriginateEnd)) {
				$iEspera = time() - $tupla->OriginateStart;
				if ($iEspera > 5 * 60) {
					$this->oMainLog->output("ERR:llamada $k espera respuesta desde hace $iEspera segundos, se elimina.");
	                $idCampania = $this->_infoLlamadas['llamadas'][$k]->id_campaign;
	                $infoCampania = $this->_leerCampania($idCampania);
	                if (!is_null($infoCampania)) {
						// Marcar estado de fallo con esta llamada
		                $result = $this->_dbConn->query(
		                    'UPDATE calls SET status = ?, fecha_llamada = ?, start_time = NULL, end_time = NULL '.
		                        'WHERE id_campaign = ? AND id = ?',
		                    array('Failure', date('Y-m-d H:i:s'),
		                        $infoCampania->id, $this->_infoLlamadas['llamadas'][$k]->id));
		                if (DB::isError($result)) {
		                    $this->oMainLog->output(
		                        "ERR: no se puede actualizar llamada con limpieza de llamadas perdidas ".
		                        "[id_campaign=$infoCampania->id, id=".$this->_infoLlamadas['llamadas'][$k]->id."]".
		                        $result->getMessage());
		                }

						// Quitar llamada de lista de llamadas monitoreadas
	                	if (!isset($this->_numLlamadasOriginadas[$infoCampania->queue])) {
	                		$this->oMainLog->output("ERR: cola {$infoCampania->queue} no se encuentra entre llamadas originadas!");
	                	} elseif ($this->_numLlamadasOriginadas[$infoCampania->queue] <= 0) {
			                // Esta situación no debería ocurrir nunca
			            	$this->oMainLog->output("ERR: (limpieza de llamadas perdidas) ha encontrado llamada ".
			                    "propia, pero ".$this->_numLlamadasOriginadas[$infoCampania->queue].
			                    " llamadas en espera de respuesta! \n");
	                	} else {
	                		$this->_numLlamadasOriginadas[$infoCampania->queue]--;
	                	}
	                }
	                
	                unset($this->_infoLlamadas['llamadas'][$k]);
				}
			}
		}

        // Si se habilita debug, se muestra estado actual de las llamadas
        if ($iTimestamp - $this->_iUltimoDebug > 30) {
        	$this->_iUltimoDebug = $iTimestamp;
            if ($this->DEBUG) {
            	$this->oMainLog->output("DEBUG: estado actual de campañas => ".print_r($this->_infoLlamadas, TRUE));
                $this->oMainLog->output("DEBUG: estado actual de llamadas esperadas => ".print_r($this->_numLlamadasOriginadas, TRUE));
            }
        }

        return TRUE;
    }

    /**
     * Procedimiento que actualiza el número de llamadas que están siendo manejadas
     * por los agentes. A partir de MIN_MUESTRAS, se actualizan los valores de 
     * promedio y desviación estándar para implementar el algoritmo predictivo.
     *
     * @param object $infoCampania Información sobre la campaña
     *
     * @return bool VERDADERO si se agregaron llamadas a la campaña
     */
    private function actualizarLlamadasCampania($infoCampania)
    {
        $iNumLlamadasColocar = 0;

		// Construir patrón de marcado a partir de trunk de campaña
		$datosTrunk = $this->_construirPlantillaMarcado($infoCampania->trunk);
		if (is_null($datosTrunk)) {
			$this->oMainLog->output("ERR: no se puede construir plantilla de marcado a partir de trunk '{$infoCampania->trunk}'!");
			$this->oMainLog->output("ERR: Revise los mensajes previos. Si el problema es un tipo de trunk no manejado, ".
				"se requiere informar este tipo de trunk y/o actualizar su versión de CallCenter");
			return FALSE;
		}

        // Leer cuántas llamadas (como máximo) se pueden hacer por campaña
        $iNumLlamadasColocar = $infoCampania->max_canales;
        if ($iNumLlamadasColocar <= 0) return FALSE;

        // Averiguar cuantas llamadas se pueden hacer (por predicción), y tomar
        // el menor valor de entre máx campaña y predictivo
        $oPredictor = new Predictivo($this->_astConn);
        if (method_exists($oPredictor, 'setPromedioDuracion')) {
        	$oPredictor->setPromedioDuracion($infoCampania->queue, $infoCampania->promedio);
            $oPredictor->setDesviacionDuracion($infoCampania->queue, $infoCampania->desviacion);
            $oPredictor->setProbabilidadAtencion($infoCampania->queue, 0.97);
            
            // TODO: calcular sobre la marcha en base a respuestas sucesivas
            $oPredictor->setTiempoContestar($infoCampania->queue, $this->_iTiempoContestacion);
        }
        $iMaxPredecidos = $oPredictor->predecirNumeroLlamadas(
            $infoCampania->queue, 
            ($infoCampania->num_completadas >= MIN_MUESTRAS));
        if ($iNumLlamadasColocar > $iMaxPredecidos)
            $iNumLlamadasColocar = $iMaxPredecidos;

		$conflicto = $oPredictor->getAgentesConflicto();
		if (is_array($conflicto)) {
			$this->oMainLog->output(
				"ERR: los siguientes agentes están libres según 'agent show' pero ocupados según 'queue show' : ".
				join($conflicto, ' '));
			$this->oMainLog->output("ERR: se considera que los agentes mencionados están libres...");
		}

        if (!isset($this->_numLlamadasOriginadas[$infoCampania->queue])) {
        	$this->_numLlamadasOriginadas[$infoCampania->queue] = 0;
        }
        if ($this->DEBUG) {
            if ($this->_numLlamadasOriginadas[$infoCampania->queue] > 0)
                $this->oMainLog->output("DEBUG: (campania $infoCampania->id cola $infoCampania->queue) todavia quedan ".
                	$this->_numLlamadasOriginadas[$infoCampania->queue].
					" llamadas pendientes de OriginateResponse!");
			foreach ($this->_infoLlamadas['llamadas'] as $k => $tupla) {
				if (is_null($tupla->OriginateEnd) && $tupla->id_campaign == $infoCampania->id) {
					$iEspera = time() - $tupla->OriginateStart;
					$this->oMainLog->output("DEBUG:\tllamada $k espera respuesta desde hace $iEspera segundos.");
				}
			}
        }
        if ($iNumLlamadasColocar > $this->_numLlamadasOriginadas[$infoCampania->queue])
            $iNumLlamadasColocar -= $this->_numLlamadasOriginadas[$infoCampania->queue];
        else $iNumLlamadasColocar = 0;
        
        if ($this->_astConn->request_err) {
        	$this->oMainLog->output("ERR: problema al enviar petición a Asterisk durante predicción");
            $this->iniciarConexionAsterisk();
            return FALSE;
        }

        if ($iNumLlamadasColocar <= 0) {
            if ($this->DEBUG) {
            	$this->oMainLog->output("DEBUG: (campania $infoCampania->id cola $infoCampania->queue) no hay agentes libres ni a punto de desocuparse!");
            	// Se desactiva esto porque emite demasiada información y rellena el log
            	/*
            	$this->oMainLog->output("DEBUG: (campania $infoCampania->id cola $infoCampania->queue) estado de cola: ".
            		print_r($oPredictor->leerEstadoCola($infoCampania->queue), TRUE));
            	*/
            }
            return FALSE;	
        }

        if ($this->DEBUG) {
            $this->oMainLog->output("DEBUG: (campania $infoCampania->id cola $infoCampania->queue) se pueden colocar un máximo de $iNumLlamadasColocar llamadas...");	
        }
        
        if ($this->_bSobrecolocarLlamadas) {
            // Para compensar por falla de llamadas, se intenta colocar más de la cuenta. El porcentaje
            // de llamadas a sobre-colocar se determina a partir de la historia pasada de la campaña.
            $iVentanaHistoria = 60 * 30; // TODO: se puede autocalcular?
            $sPeticionASR = 
    			'SELECT COUNT(*) AS total, SUM(IF(status = "Failure" OR status = "NoAnswer", 0, 1)) AS exito ' .
    			'FROM calls ' .
    			'WHERE id_campaign = ? AND status IS NOT NULL ' .
    				'AND status <> "Placing" ' .
    				'AND fecha_llamada IS NOT NULL ' .
    				'AND fecha_llamada >= ?';
    		$tupla =& $this->_dbConn->getRow(
    			$sPeticionASR, 
    			array($infoCampania->id, date('Y-m-d H:i:s', time() - $iVentanaHistoria)), 
    			DB_FETCHMODE_OBJECT);
    		if (DB::isError($tupla)) {
    			$this->oMainLog->output("ERR: (campania $infoCampania->id cola $infoCampania->queue) no se puede consultar ASR para campaña - ".$tupla->getMessage());
    		} else {
    			// Sólo considerar para más de 10 llamadas colocadas durante ventana
    			if ($tupla->total >= 10 && $tupla->exito > 0) {
    				$ASR = $tupla->exito / $tupla->total;
    				$ASR_safe = $ASR;
    				if ($ASR_safe < 0.20) $ASR_safe = 0.20;
    				$iNumLlamadasColocar = (int)round($iNumLlamadasColocar / $ASR_safe); 
    				if ($this->DEBUG) {
    					$this->oMainLog->output("DEBUG: (campania $infoCampania->id cola $infoCampania->queue) ".
    							"en los últimos $iVentanaHistoria seg. tuvieron éxito " .
    							"$tupla->exito de $tupla->total llamadas colocadas (ASR=".(sprintf('%.2f', $ASR * 100))."%). Se colocan " .
    							"$iNumLlamadasColocar para compensar.");
    				}
    			}
    		}
        }
        
        // Leer tantas llamadas como fueron elegidas. Sólo se leen números con
        // status == NULL y bandera desactivada
        $sFechaSys = date('Y-m-d');
        $sHoraSys = date('H:i:s');
        $sPeticionLlamadas = <<<PETICION_LLAMADAS
(
SELECT id_campaign, id, phone FROM calls 
WHERE id_campaign = ? 
    AND status IS NULL 
    AND dnc = 0 
    AND date_init <= ? AND date_end >= ? AND time_init <= ? AND time_end >= ?
ORDER BY date_end, time_end, date_init, time_init
)
UNION
(
SELECT id_campaign, id, phone FROM calls 
WHERE id_campaign = ? 
    AND status IS NULL 
    AND dnc = 0
    AND date_init IS NULL AND date_end IS NULL AND time_init IS NULL AND time_end IS NULL  
)
UNION
(
SELECT id_campaign, id, phone FROM calls 
WHERE id_campaign = ? 
    AND status NOT IN ("Success", "Placing", "Ringing", "OnQueue", "OnHold")
    AND retries < ?   
    AND dnc = 0 
    AND date_init <= ? AND date_end >= ? AND time_init <= ? AND time_end >= ?
ORDER BY date_end, time_end, date_init, time_init
)
UNION
(
SELECT id_campaign, id, phone FROM calls 
WHERE id_campaign = ? 
    AND status NOT IN ("Success", "Placing", "Ringing", "OnQueue", "OnHold")
    AND retries < ?   
    AND dnc = 0 
    AND date_init IS NULL AND date_end IS NULL AND time_init IS NULL AND time_end IS NULL  
)
LIMIT 0,?
PETICION_LLAMADAS;

        $recordset =& $this->_dbConn->query(
            $sPeticionLlamadas, 
            array($infoCampania->id, 
                $sFechaSys, $sFechaSys, $sHoraSys, $sHoraSys,
                $infoCampania->id,
                $infoCampania->id,
                $infoCampania->retries,
                $sFechaSys, $sFechaSys, $sHoraSys, $sHoraSys,
                $infoCampania->id,
                $infoCampania->retries,
                $iNumLlamadasColocar));
        if (DB::isError($recordset)) {
            $this->oMainLog->output("ERR: (campania $infoCampania->id cola $infoCampania->queue) no se puede leer lista de teléfonos - ".$recordset->getMessage());
            return FALSE;
        }

        // Para cada llamada, su ID de ActionID es la combinación del PID del 
        // proceso, el ID de campaña y el ID de la llamada
        $listaLlamadas = array();
        $pid = posix_getpid();
        while ($tupla = $recordset->fetchRow(DB_FETCHMODE_OBJECT)) {
            $sKey = sprintf('%d-%d-%d', $pid, $infoCampania->id, $tupla->id);
            $listaLlamadas[$sKey] = $tupla;
        }

        if (count($listaLlamadas) == 0) {
        	/* Debido a que ahora las llamadas pueden agendarse a una hora específica, puede
             * ocurrir que la lista de llamadas por realizar esté vacía porque hay llamadas
             * agendadas, pero fuera del horario indicado por la hora del sistema. Si la
             * cuenta del query de abajo devuelve al menos una llamada, se interrumpe el
             * procesamiento y se sale 
             */
            $sPeticionTotal =
                'SELECT COUNT(*) AS N FROM calls '.
                'WHERE id_campaign = ? '.
                    'AND (status IS NULL OR status NOT IN ("Success", "Placing", "Ringing", "OnQueue", "OnHold")) '.
                    'AND retries < ? '.
                    'AND dnc = 0';
            $iNumTotal =& $this->_dbConn->getOne($sPeticionTotal, 
                array($infoCampania->id, $infoCampania->retries));
            if (DB::isError($iNumTotal)) {
                $this->oMainLog->output("ERR: (campania $infoCampania->id cola $infoCampania->queue) no se puede leer cuenta de teléfonos - ".$iNumTotal->getMessage());
                return FALSE;
            }
            if (!is_null($iNumTotal) && $iNumTotal > 0) {
                if ($this->DEBUG) {
                    $this->oMainLog->output("DEBUG: (campania $infoCampania->id cola $infoCampania->queue) no hay llamadas a colocar; $iNumTotal llamadas agendadas pero fuera de horario.");
                }
            	return FALSE;
            }
        }
        
        if (count($listaLlamadas) > 0) {
            if ($this->DEBUG) {
                $this->oMainLog->output("DEBUG: (campania $infoCampania->id cola $infoCampania->queue) total de llamadas a generar: ".count($listaLlamadas));
            }

            // Colocar todas las llamadas elegidas para ser realizadas por el Asterisk.
            foreach ($listaLlamadas as $sKey => $tupla) {
                $sCanalTrunk = str_replace('$OUTNUM$', $tupla->phone, $datosTrunk['TRUNK']);
                if ($this->DEBUG) {
                    $this->oMainLog->output("DEBUG: generando llamada\n".
						"\tDestino..... $tupla->phone\n" .
						"\tCola........ $infoCampania->queue\n" .
						"\tContexto.... $infoCampania->context\n" .
						"\tTrunk....... ".(is_null($infoCampania->trunk) ? '(by dialplan)' : $infoCampania->trunk)."\n" .
						"\tPlantilla... ".$datosTrunk['TRUNK']."\n" .
						"\tCaller ID... ".(isset($datosTrunk['CID']) ? $datosTrunk['CID'] : "(no definido)")."\n".
						"\tCadena de marcado $sCanalTrunk");
                }
                $resultado = $this->_astConn->Originate(
                    $sCanalTrunk, $infoCampania->queue, $infoCampania->context, 1,
                    NULL, NULL, NULL, 
                    (isset($datosTrunk['CID']) ? $datosTrunk['CID'] : NULL), 
                    "ID_CAMPAIGN={$infoCampania->id}|ID_CALL={$tupla->id}|NUMBER={$tupla->phone}|QUEUE={$infoCampania->queue}|CONTEXT={$infoCampania->context}",
                    NULL, 
                    TRUE, $sKey);
                if (!is_array($resultado) || count($resultado) == 0) {
                	$this->oMainLog->output("ERR: problema al enviar Originate a Asterisk");
                    $this->iniciarConexionAsterisk();
                }
                // TODO: aparece ActionID en la respuesta con Success si es Async?
                if ($resultado['Response'] == 'Success') {
                    // Guardar el momento en que se originó la llamada
                    $listaLlamadas[$sKey]->OriginateStart = time();
                    $listaLlamadas[$sKey]->OriginateEnd = NULL;
                    
                    $this->_numLlamadasOriginadas[$infoCampania->queue]++;
                    $bErrorLocked = FALSE;
                    do {
                    	$bErrorLocked = FALSE;
                        $result = $this->_dbConn->query(
                            'UPDATE calls SET status = ? WHERE id_campaign = ? AND id = ?',
                            array('Placing', $infoCampania->id, $tupla->id));
                        if (DB::isError($result)) {
                            $bErrorLocked = ereg('database is locked', $result->getMessage());
                            if ($bErrorLocked) {
                                usleep(125000);
                            } else {
                                $this->oMainLog->output("ERR: no se puede actualizar llamada [id_campaign=$infoCampania->id, id=$tupla->id]".$result->getMessage());
                            }
                        }                        
                    } while (DB::isError($result) && $bErrorLocked);
                } else {
                    $this->oMainLog->output("ERR: (campania $infoCampania->id cola $infoCampania->queue) no se puede llamar a número - ".
                    	print_r($resultado, TRUE));
                }
            }
            // Agregar todas las llamadas agregadas a la lista de llamadas pendientes
            // por timbrar, para filtrar según el evento Link y guardar en la 
            // base de datos.
            $this->_infoLlamadas['llamadas'] = array_merge($this->_infoLlamadas['llamadas'], $listaLlamadas);
            return (count($listaLlamadas) > 0);            
        } else {
            /* Si se llega a este punto, se presume que, con agentes disponibles, y campaña
               activa, se terminaron las llamadas. Por lo tanto la campaña ya ha terminado */
            $result = $this->_dbConn->query('UPDATE campaign SET estatus = "T" WHERE id = ?',
                array($infoCampania->id));
            if (DB::isError($result)) {
                $this->oMainLog->output("ERR: (campania $infoCampania->id cola $infoCampania->queue) no se puede marcar campaña como terminada - ".$result->getMessage());
            }

            return FALSE;
        }
    }

	/**
	 * Procedimiento que construye una plantilla de marcado a partir de una 
	 * definición de trunk. Una plantilla de marcado es una cadena de texto de
	 * la forma 'blablabla$OUTNUM$blabla' donde $OUTNUM$ es el lugar en que
	 * debe constar el número saliente que va a marcarse. Por ejemplo, para
	 * trunks de canales ZAP, la plantilla debe ser algo como Zap/g0/$OUTNUM$
	 * 
	 * @param	string	$sTrunk		Patrón que define el trunk a usar por la campaña
	 * 
	 * @return	mixed	La cadena de plantilla de marcado, o NULL en error 
	 */
	private function _construirPlantillaMarcado($sTrunk)
	{
		if (is_null($sTrunk)) {
			return array('TRUNK' => 'Local/$OUTNUM$@from-internal');
		} elseif (stripos($sTrunk, '$OUTNUM$') !== FALSE) {
			// Este es un trunk personalizado que provee $OUTNUM$ ya preparado
			return array('TRUNK' => $sTrunk);
		} elseif (ereg('^SIP/', $sTrunk) 
			|| eregi('^Zap/.+', $sTrunk)
            || eregi('^DAHDI/.+', $sTrunk) 
			|| ereg('^IAX/', $sTrunk)
            || ereg('^IAX2/', $sTrunk)) {
			// Este es un trunk Zap o SIP. Se debe concatenar el prefijo de marcado 
			// (si existe), y a continuación el número a marcar.
			$infoTrunk = $this->_leerPropiedadesTrunk($sTrunk);
			if (is_null($infoTrunk)) return NULL;
			
			// SIP/TRUNKLABEL/<PREFIX>$OUTNUM$
			$sPlantilla = $sTrunk.'/';
			if (isset($infoTrunk['PREFIX'])) $sPlantilla .= $infoTrunk['PREFIX'];
			$sPlantilla .= '$OUTNUM$';

			// Agregar información de Caller ID, si está disponible
			$plantilla = array('TRUNK' => $sPlantilla);
			if (isset($infoTrunk['CID']) && trim($infoTrunk['CID']) != '')
				$plantilla['CID'] = $infoTrunk['CID'];
			return $plantilla;
		} else {
			$this->oMainLog->output("ERR: trunk '$sTrunk' es un tipo de trunk desconocido. Actualice su versión de CallCenter.");
			return NULL;
		}
	}

	/**
	 * Procedimiento que lee las propiedades del trunk indicado a partir de la
	 * base de datos de FreePBX. Este procedimiento puede tomar algo de tiempo,
	 * porque se requiere la información de /etc/amportal.conf para obtener las
	 * credenciales para conectarse a la base de datos.
	 * 
	 * @param	string	$sTrunk		Trunk sobre la cual leer información de DB
	 * 
	 * @return	mixed	NULL en caso de error, o arreglo de propiedades
	 */
	private function _leerPropiedadesTrunk($sTrunk)
	{
		$sNombreConfig = '/etc/amportal.conf';	// TODO: vale la pena poner esto en config?

		/* Para evitar excesivas conexiones, se mantiene un cache de la información leída
		 * acerca de un trunk durante los últimos 30 segundos. 
		 */
		if (isset($this->_plantillasMarcado[$sTrunk])) {
			if (time() - $this->_plantillasMarcado[$sTrunk]['TIMESTAMP'] >= 30)
				unset($this->_plantillasMarcado[$sTrunk]);
		}
		if (isset($this->_plantillasMarcado[$sTrunk])) {
			return $this->_plantillasMarcado[$sTrunk]['PROPIEDADES'];
		}
		
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

		$infoTrunk = NULL;

        // FreePBX todavía guarda la información sobre troncales DAHDI bajo nombres ZAP.
        // Para encontrarla, se requiere de transformación antes de la consulta.
        $sTrunkConsulta = str_replace('DAHDI', 'ZAP', $sTrunk);

		/* Buscar cuál de las opciones describe el trunk indicado. En FreePBX, la información de los
		 * trunks está guardada en la tabla 'globals', donde globals.value tiene el nombre del
		 * trunk buscado, y globals.variable es de la forma OUT_NNNNN. El valor de NNN se usa para
		 * consultar el resto de las variables 
		 */
		$regs = NULL;		 
		$sPeticionSQL = "SELECT variable FROM globals WHERE value = ? AND variable LIKE 'OUT_%'";
		$sVariable = $dbConn->getOne($sPeticionSQL, array($sTrunkConsulta));
		if (DB::isError($sVariable)) {
			$this->oMainLog->output("ERR: al consultar información de trunk '$sTrunkConsulta' en FreePBX (1) - ".($sVariable->getMessage()));
		} elseif (is_null($sVariable)) {
			$this->oMainLog->output("ERR: al consultar información de trunk '$sTrunkConsulta' en FreePBX (1) - trunk no se encuentra!");
		} elseif (!ereg('^OUT_([[:digit:]]+)$', $sVariable, $regs)) {
			$this->oMainLog->output("ERR: al consultar información de trunk '$sTrunkConsulta' en FreePBX (1) - se esperaba OUT_NNN pero se encuentra $sVariable - versión incompatible de FreePBX?");
		} else {
			$iNumTrunk = $regs[1];
			
			// Consultar todas las variables asociadas al trunk
			$sPeticionSQL = 'SELECT variable, value FROM globals WHERE variable LIKE ?';
			$recordset =& $dbConn->query($sPeticionSQL, array('OUT%_'.$iNumTrunk));
			if (DB::isError($recordset)) {
				$this->oMainLog->output("ERR: al consultar información de trunk '$sTrunkConsulta' en FreePBX (2) - ".($recordset->getMessage()));
			} else {
				$infoTrunk = array();
				$sRegExp = '^OUT(.+)_'.$iNumTrunk.'$';
				while ($tupla = $recordset->fetchRow(DB_FETCHMODE_ASSOC)) {
					$regs = NULL;
					if (ereg($sRegExp, $tupla['variable'], $regs)) {
						$sValor = trim($tupla['value']);
						if ($sValor != '') $infoTrunk[$regs[1]] = $sValor;
					}
				}
				$this->_plantillasMarcado[$sTrunk] = array(
					'TIMESTAMP'		=>	time(),
					'PROPIEDADES'	=>	$infoTrunk,
				);
			}
		}

		$dbConn->disconnect();
		return $infoTrunk;
	}

    // Callback invocado al recibir el evento OriginateResponse
    function OnOriginateResponse($sEvent, $params, $sServer, $iPort)
    {
        if ($this->DEBUG) {
            $this->oMainLog->output("DEBUG: $sEvent:\nparams => ".print_r($params, TRUE));
        }
        if (!isset($params['ActionID'])) return FALSE;
        $sKey = $params['ActionID'];
        if (isset($this->_infoLlamadas['llamadas'][$sKey])) {
            $this->_infoLlamadas['llamadas'][$sKey]->OriginateEnd = time();
            $idCampaign = $this->_infoLlamadas['llamadas'][$sKey]->id_campaign;
            if (isset($this->_infoLlamadas['campanias'][$idCampaign])) {
                $infoCampania = $this->_infoLlamadas['campanias'][$idCampaign];
            } else {
            	// Puede ocurrir que se hayan originado llamadas, pero en la
                // siguiente iteración la campaña haya terminado. Todavía
                // debe de seguirse la pista de la campaña.
                $infoCampania = $this->_leerCampania($idCampaign);
            }
            if ($this->_numLlamadasOriginadas[$infoCampania->queue] > 0) {
                $this->_numLlamadasOriginadas[$infoCampania->queue]--;
            } else {
                // Esta situación no debería ocurrir nunca
            	$this->oMainLog->output("ERR: OnOriginateResponse ha encontrado llamada ".
                    "propia, pero ".$this->_numLlamadasOriginadas[$infoCampania->queue].
                    " llamadas en espera de respuesta! \n".print_r($params, TRUE));
            }
            
            $bErrorLocked = FALSE;
            do {
                $bErrorLocked = FALSE;
                $sStatus = $params['Response'];
                if ($params['Uniqueid'] == '<null>') $params['Uniqueid'] = NULL;
                if ($sStatus == 'Success') $sStatus = 'Ringing';
                
                $sQuery = 
                    'UPDATE calls ' .
                    'SET status = ?, Uniqueid = ?, fecha_llamada = ?, start_time = NULL, end_time = NULL, retries = retries + ? '.
                    'WHERE id_campaign = ? AND id = ?';
                $queryParams = array($sStatus, $params['Uniqueid'], date('Y-m-d H:i:s'), (($sStatus == 'Failure') ? 1 : 0),
                        $infoCampania->id, $this->_infoLlamadas['llamadas'][$sKey]->id);
                
                $result = $this->_dbConn->query($sQuery, $queryParams);
                if (DB::isError($result)) {
                    $bErrorLocked = ereg('database is locked', $result->getMessage());
                    if ($bErrorLocked) {
                        usleep(125000);
                    } else {
                        $this->oMainLog->output(
                            "ERR: no se puede actualizar llamada con OriginateResponse ".
                            "[id_campaign=$infoCampania->id, id=".$this->_infoLlamadas['llamadas'][$sKey]->id."]".
                            $result->getMessage());
                    }
                }                        
            } while (DB::isError($result) && $bErrorLocked);
            
            if ($params['Response'] == 'Success') {
                if (isset($this->_infoLlamadas['llamadas'][$sKey])) {
                    $this->_infoLlamadas['llamadas'][$sKey]->Uniqueid = $params['Uniqueid'];
                    $this->_infoLlamadas['llamadas'][$sKey]->Response = $params['Response'];
                    $this->_infoLlamadas['llamadas'][$sKey]->queue = $infoCampania->queue;
                    $this->_infoLlamadas['llamadas'][$sKey]->enterqueue_timestamp = NULL;
                    $this->_infoLlamadas['llamadas'][$sKey]->start_timestamp = NULL;
                    $this->_infoLlamadas['llamadas'][$sKey]->end_timestamp = NULL;
                }
                if ($this->DEBUG) {
                	$iSegundosEspera = 
                		$this->_infoLlamadas['llamadas'][$sKey]->OriginateEnd - 
                		$this->_infoLlamadas['llamadas'][$sKey]->OriginateStart;
                	$this->oMainLog->output("DEBUG: llamada colocada luego de $iSegundosEspera s. de espera."); 
                }                    
            } else {
				// Reportar tiempo transcurrido hasta fallo
                if ($this->DEBUG) {
                	$iSegundosEspera = 
                		$this->_infoLlamadas['llamadas'][$sKey]->OriginateEnd - 
                		$this->_infoLlamadas['llamadas'][$sKey]->OriginateStart;
                	$this->oMainLog->output("DEBUG: llamada falla en ser colocada luego de $iSegundosEspera s. de espera."); 
                }                    

                // Remover llamada que no se pudo colocar
                unset($this->_infoLlamadas['llamadas'][$sKey]);
                $sMensaje = print_r($params, TRUE);
                if ($this->DEBUG) {
                    $this->oMainLog->output("DEBUG: Información sobre no-éxito de OriginateResponse: \n$sMensaje");
                }
            }
        }
        return FALSE;
    }

    // Callback invocado al llegar el evento Join
    function OnJoin($sEvent, $params, $sServer, $iPort)
    {
        if ($this->DEBUG) {
            $this->oMainLog->output("DEBUG: $sEvent:\nparams => ".print_r($params, TRUE));
        }
        
        // Verificar si es una llamada entrante monitoreada. Si lo es, 
        // se termina el procesamiento sin hacer otra cosa
        if ($this->_oGestorEntrante->notificarJoin($params)) return FALSE;
        
        // Buscar llamada entre llamadas monitoreadas
        $sKey = NULL;
        foreach ($this->_infoLlamadas['llamadas'] as $key => $tupla) {
            if (isset($tupla->Uniqueid) && $tupla->Uniqueid == $params['Uniqueid']) {
                $sKey = $key;
            }
        }
        if (!is_null($sKey)) {
            $this->_infoLlamadas['llamadas'][$sKey]->enterqueue_timestamp = time();
            $sLlamadaEnCola = 
                'UPDATE calls SET status = "OnQueue", datetime_entry_queue = ?, '.
                    'duration_wait = NULL, duration = NULL, start_time = NULL, '.
                    'end_time = NULL '.
                'WHERE id_campaign = ? AND id = ?';
            $result =& $this->_dbConn->query(
                $sLlamadaEnCola,
                array(
                    date('Y-m-d H:i:s', $this->_infoLlamadas['llamadas'][$sKey]->enterqueue_timestamp),
                    $this->_infoLlamadas['llamadas'][$sKey]->id_campaign, 
                    $this->_infoLlamadas['llamadas'][$sKey]->id,
                    ));
            if (DB::isError($result)) {
                $this->oMainLog->output("ERR: $sEvent: no se puede actualizar fecha inicio llamada actual - ".$result->getMessage());
            }
        }
    }

    // Callback invocado al llegar el evento Link
    function OnLink($sEvent, $params, $sServer, $iPort)
    {    
        if ($this->DEBUG) {
        	$this->oMainLog->output("DEBUG: $sEvent:\nparams => ".print_r($params, TRUE));
        }
        
        // Verificar si es una llamada entrante monitoreada. Si lo es, 
        // se termina el procesamiento sin hacer otra cosa
        if ($this->_oGestorEntrante->notificarLink($params)) return FALSE;
        
        $sKey = NULL;
        foreach ($this->_infoLlamadas['llamadas'] as $key => $tupla) {
            if (isset($tupla->Uniqueid)) {
                if ($tupla->Uniqueid == $params['Uniqueid1']) $sKey = $key;
                if ($tupla->Uniqueid == $params['Uniqueid2']) $sKey = $key;
            }
        }
        if (!is_null($sKey)) {
        	/* Si una llamada regresa de HOLD a activa, se recibe un evento Link,
             * pero la llamada ya se encuentra en current_calls. */
            $iCuenta = $this->_dbConn->getOne(
                'SELECT COUNT(*) FROM current_calls WHERE Uniqueid = ?',
                array($this->_infoLlamadas['llamadas'][$sKey]->Uniqueid));
            if (DB::isError($iCuenta)) {
            	$this->oMainLog->output("ERR: $sEvent: no se puede consultar si llamada está activa - ".$iCuenta->getMessage());
            } elseif ($iCuenta > 0) {
            	/* La llamada ha sido ya ingresada en current_calls, y se omite 
                 * procesamiento futuro. */
                $this->oMainLog->output("DEBUG: $sEvent: llamada ".
                    ($this->_infoLlamadas['llamadas'][$sKey]->Uniqueid).
                    " regresa de HOLD, se omite procesamiento futuro.");
                $result =& $this->_dbConn->query(
                    "UPDATE calls SET status = 'Success' WHERE id = ?",
                    array($this->_infoLlamadas['llamadas'][$sKey]->id));
                if (DB::isError($result)) {
                    $this->oMainLog->output("ERR: $sEvent: no se puede actualizar estado de llamada actual a HOLD - ".$result->getMessage());
                }
                $sKey = NULL;
            }
        }
        if (!is_null($sKey) && is_null($this->_infoLlamadas['llamadas'][$sKey]->start_timestamp)) {
            $this->_infoLlamadas['llamadas'][$sKey]->start_timestamp = time();
            
            if ($this->DEBUG) {
            	$this->oMainLog->output("DEBUG: $sEvent: llamada $sKey => ".
                    print_r($this->_infoLlamadas['llamadas'][$sKey], TRUE));
            }

            $regs = NULL;
            $sAgentNum = NULL;
            $sChannel = NULL;
            $sRemChannel = NULL;
            if (ereg('^Agent/([[:digit:]]+)$', $params['Channel1'], $regs)) {
                $sAgentNum = $regs[1];
                $sChannel = $params['Channel1'];
                $sRemChannel = $params['Channel2'];
            }
            if (ereg('^Agent/([[:digit:]]+)$', $params['Channel2'], $regs)) {
                $sAgentNum = $regs[1];
                $sChannel = $params['Channel2'];
                $sRemChannel = $params['Channel1'];
            }
            if (!is_null($sAgentNum)) {
                if ($this->DEBUG) {
                	$this->oMainLog->output("DEBUG: $sEvent: identificado agente $sAgentNum");
                }

                // Borrado de la llamada para el agente antiguo. Esto es por 
                // precaución, porque no debería ocurrir en funcionamiento correcto.
                $sBorrado = 'DELETE FROM current_calls WHERE agentnum = ?';
                $bErrorLocked = FALSE;
                do {
                    $bErrorLocked = FALSE;
                    $result =& $this->_dbConn->query($sBorrado, array($sAgentNum));
                    if (DB::isError($result)) {
                        $bErrorLocked = ereg('database is locked', $result->getMessage());
                        if ($bErrorLocked) {
                            usleep(125000);
                        } else {
                            $this->oMainLog->output("ERR: $sEvent: no se puede purgar agente $sAgentNum - ".$result->getMessage());
                        }
                    }
                } while (DB::isError($result) && $bErrorLocked);
                
                $sFechaActual = date('Y-m-d H:i:s', $this->_infoLlamadas['llamadas'][$sKey]->start_timestamp);

                if ($this->DEBUG) {
                	$this->oMainLog->output("DEBUG: $sEvent: llamada $sKey asignada a agente $sAgentNum");
                }
                
                // Inserción de la llamada nueva
                $sInsercionEvent = 
                    'INSERT INTO current_calls (fecha_inicio, Uniqueid, queue, agentnum, id_call, event, Channel, ChannelClient) '.
                    'VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
                do {
                    $bErrorLocked = FALSE;
                    $result =& $this->_dbConn->query(
                        $sInsercionEvent,
                        array($sFechaActual, 
                        $this->_infoLlamadas['llamadas'][$sKey]->Uniqueid,
                        $this->_infoLlamadas['llamadas'][$sKey]->queue,
                        $sAgentNum,
                        $this->_infoLlamadas['llamadas'][$sKey]->id,
                        $params['Event'],
                        $sChannel, 
                        $sRemChannel));
                    if (DB::isError($result)) {
                        $bErrorLocked = ereg('database is locked', $result->getMessage());
                        if ($bErrorLocked) {
                            usleep(125000);
                        } else {
                            $this->oMainLog->output("ERR: $sEvent: no se puede insertar llamada actual - ".$result->getMessage());
                        }
                    }
                } while (DB::isError($result) && $bErrorLocked);
                // Obtengo los datos del agente 
                $sDatosAgente = 
                    'SELECT id '.
                    'FROM agent '.
                    'WHERE number = ? ';
                $tupla = $this->_dbConn->getRow($sDatosAgente, array($sAgentNum), DB_FETCHMODE_OBJECT);
                if (DB::isError($tupla)) {
                    $this->oMainLog->output("ERR: $sEvent: no se puede consultar los datos del agente $sAgentNum- ".$tupla->getMessage());
                    $idAgente = NULL;
                }
                else {
                    $idAgente = $tupla->id;
                }

                // Actualización de la fecha de inicio de la llamada
                if (is_null($this->_infoLlamadas['llamadas'][$sKey]->enterqueue_timestamp)) {
                    $this->oMainLog->output(
                        "ERR: $sEvent: se ha perdido evento OnJoin para llamada antes de OnLink, ".
                        "no se puede calcular el periodo de espera.\nparams => ".print_r($params, TRUE));
                    $sInicioLlamada = 
                        'UPDATE calls SET status = "Success", id_agent = ?, start_time = ?, end_time = NULL, '.
                            'retries = retries + 1 '.
                        'WHERE id_campaign = ? AND id = ?';
                    $result =& $this->_dbConn->query(
                        $sInicioLlamada,
                        array(
                            $idAgente,
                            $sFechaActual, 
                            $this->_infoLlamadas['llamadas'][$sKey]->id_campaign, 
                            $this->_infoLlamadas['llamadas'][$sKey]->id,
                            ));
                } else {
                    $sInicioLlamada = 
                        'UPDATE calls SET status = "Success", id_agent = ?, start_time = ?, end_time = NULL, '.
                            'retries = retries + 1, datetime_entry_queue = ?, duration_wait = ? '.
                        'WHERE id_campaign = ? AND id = ?';
                    $result =& $this->_dbConn->query(
                        $sInicioLlamada,
                        array(
                            $idAgente,
                            $sFechaActual, 
                            date('Y-m-d H:i:s', $this->_infoLlamadas['llamadas'][$sKey]->enterqueue_timestamp),
                            $this->_infoLlamadas['llamadas'][$sKey]->start_timestamp - $this->_infoLlamadas['llamadas'][$sKey]->enterqueue_timestamp,
                            $this->_infoLlamadas['llamadas'][$sKey]->id_campaign, 
                            $this->_infoLlamadas['llamadas'][$sKey]->id,
                            ));
                }
                if (DB::isError($result)) {
                	$this->oMainLog->output("ERR: $sEvent: no se puede actualizar fecha inicio llamada actual - ".$result->getMessage());
                }
            } else {
            	$this->oMainLog->output("ERR: no se puede identificar agente asignado a llamada $sKey!");
            }
        } elseif (!is_null($sKey)) {
            // Llamada ya estaba siendo monitoreada anteriormente.
            if ($this->DEBUG) {
                $this->oMainLog->output("DEBUG: $sEvent: (re-link) llamada $sKey => ".
                    print_r($this->_infoLlamadas['llamadas'][$sKey], TRUE));
            }
        } else {
            if ($this->DEBUG) {
                // Ocurre un evento Link que no corresponde a las llamadas en curso
                $this->oMainLog->output("DEBUG: $sEvent: evento no corresponde a llamadas monitoreadas!");
            }
        }
        return FALSE;
    }

    // Callback invocado al llegar el evento Unlink
    function OnUnlink($sEvent, $params, $sServer, $iPort)
    {    
        if ($this->DEBUG) {
            $this->oMainLog->output("DEBUG: $sEvent:\nparams => ".print_r($params, TRUE));
        }

        $sKey = NULL;
        foreach ($this->_infoLlamadas['llamadas'] as $key => $tupla) {
            if (isset($tupla->Uniqueid)) {
                if (isset($params['Uniqueid']) && $tupla->Uniqueid == $params['Uniqueid']) $sKey = $key;
                if (isset($params['Uniqueid1']) && $tupla->Uniqueid == $params['Uniqueid1']) $sKey = $key;
                if (isset($params['Uniqueid2']) && $tupla->Uniqueid == $params['Uniqueid2']) $sKey = $key;
            }
        }
        if (!is_null($sKey) && $sEvent == 'unlink') {
        	/* En caso de que la llamada haya sido puesta en espera, la llamada 
             * se transfiere a la cola de parqueo. Esto ocasiona un evento Unlink
             * sobre la llamada, pero no debe de considerarse como el cierre de
             * la llamada.
             */
            $hold = $this->_dbConn->getOne(
                'SELECT hold FROM current_calls WHERE id_call = ?',
                array($this->_infoLlamadas['llamadas'][$sKey]->id),
                DB_FETCHMODE_ASSOC);
            if (DB::isError($hold)) {
                $this->oMainLog->output("ERR: $sEvent: no se puede consultar petición HOLD de llamada - ".$hold->getMessage());
            } elseif ($hold == 'S') {
            	/* Llamada ha sido puesta en hold. Se omite procesamiento futuro */
                $this->oMainLog->output("DEBUG: $sEvent: llamada ".($this->_infoLlamadas['llamadas'][$sKey]->Uniqueid).
                    " ha sido puesta en HOLD en vez de colgada.");
                $result =& $this->_dbConn->query(
                    "UPDATE calls SET status = 'OnHold' WHERE id = ?",
                    array($this->_infoLlamadas['llamadas'][$sKey]->id));
                if (DB::isError($result)) {
                    $this->oMainLog->output("ERR: $sEvent: no se puede actualizar estado de llamada actual a HOLD - ".$result->getMessage());
                }
                $sKey = NULL;
            }
        }
        return FALSE;
    }

    // Callback invocado al llegar el evento Hangup
    function OnHangup($sEvent, $params, $sServer, $iPort)
    {    
        if ($this->DEBUG) {
            $this->oMainLog->output("DEBUG: $sEvent:\nparams => ".print_r($params, TRUE));
        }

        // Verificar si es una llamada entrante monitoreada. Si lo es, 
        // se termina el procesamiento sin hacer otra cosa
        if ($this->_oGestorEntrante->notificarUnlink($params)) return FALSE;

        $sKey = NULL;
        foreach ($this->_infoLlamadas['llamadas'] as $key => $tupla) {
            if (isset($tupla->Uniqueid)) {
                if (isset($params['Uniqueid']) && $tupla->Uniqueid == $params['Uniqueid']) $sKey = $key;
                if (isset($params['Uniqueid1']) && $tupla->Uniqueid == $params['Uniqueid1']) $sKey = $key;
                if (isset($params['Uniqueid2']) && $tupla->Uniqueid == $params['Uniqueid2']) $sKey = $key;
            }
        }

        if (!is_null($sKey)) {
            $this->_infoLlamadas['llamadas'][$sKey]->end_timestamp = time();
            
            if ($this->DEBUG) {
                $this->oMainLog->output("DEBUG: $sEvent: llamada $sKey => ".
                    print_r($this->_infoLlamadas['llamadas'][$sKey], TRUE));
            }

            // Borrado de la llamada objetivo
            $sBorradoLlamada = 'DELETE FROM current_calls WHERE Uniqueid = ?';
            
            $bErrorLocked = FALSE;
            do {
                $bErrorLocked = FALSE;
                $result =& $this->_dbConn->query($sBorradoLlamada, array($this->_infoLlamadas['llamadas'][$sKey]->Uniqueid));
                if (DB::isError($result)) {
                    $bErrorLocked = ereg('database is locked', $result->getMessage());
                    if ($bErrorLocked) {
                        usleep(125000);
                    } else {
                        $this->oMainLog->output("ERR: $sEvent: no se puede purgar llamada - ".$result->getMessage());
                    }
                }        
            } while (DB::isError($result) && $bErrorLocked);

            // Se ha observado que ocasionalmente se pierde el evento Link
            if (is_null($this->_infoLlamadas['llamadas'][$sKey]->start_timestamp)) {
                $this->oMainLog->output("ERR: $sEvent: Hangup sin Link para llamada $sKey => ".
                    print_r($this->_infoLlamadas['llamadas'][$sKey], TRUE));
                
                // Resetear estado de llamada, para volver a intentarla
                $sActualizarLlamada = 
                    'UPDATE calls SET datetime_entry_queue = ?, duration_wait = ?, '.
                        'start_time = NULL, end_time = ?, duration = NULL, '.
                        'status = ?, retries = retries + 1 '.
                    'WHERE id = ?';
                if (is_null($this->_infoLlamadas['llamadas'][$sKey]->enterqueue_timestamp)) {
                    // Escenario en que llamada nunca fue respondida
                    $updateParams = array(
                        NULL, 
                        NULL, 
                        NULL, 
                        'NoAnswer', 
                        $this->_infoLlamadas['llamadas'][$sKey]->id);
                } else {
                    // Escenario en que llamada fue respondida y entró a cola, pero
                    // ningún agente se desocupó a tiempo para atenderla.
                    $updateParams = array(
                        date('Y-m-d H:i:s', $this->_infoLlamadas['llamadas'][$sKey]->enterqueue_timestamp), 
                        $this->_infoLlamadas['llamadas'][$sKey]->end_timestamp - $this->_infoLlamadas['llamadas'][$sKey]->enterqueue_timestamp, 
                        date('Y-m-d H:i:s', $this->_infoLlamadas['llamadas'][$sKey]->end_timestamp), 
                        'Abandoned', 
                        $this->_infoLlamadas['llamadas'][$sKey]->id);
                }
                $result =& $this->_dbConn->query($sActualizarLlamada, $updateParams);
                if (DB::isError($result)) {
                    $this->oMainLog->output("ERR: $sEvent: no se puede resetear llamada actual - ".$result->getMessage());
                }
            } else {

                // Calcular duración de llamada, para poder actualizar promedio y desviación estándar
                $iDuracionLlamada = $this->_infoLlamadas['llamadas'][$sKey]->end_timestamp -
                    $this->_infoLlamadas['llamadas'][$sKey]->start_timestamp;
                if ($this->DEBUG) {
                    $this->oMainLog->output("DEBUG: duración de la llamada fue de $iDuracionLlamada s.");
                }

                $bLlamadaCorta = ($iDuracionLlamada <= $this->_iUmbralLlamadaCorta);                
                if ($bLlamadaCorta) {
                    // Llamada corta que no se ha podido empezar a hablar
                    if ($this->DEBUG) {
                        $this->oMainLog->output("DEBUG: llamada fue identificada como llamada corta!");
                    }
                    $sActualizarLlamada = 'UPDATE calls SET end_time = ?, duration = ?, status = "ShortCall" WHERE id = ?';
                    $result =& $this->_dbConn->query($sActualizarLlamada, 
                        array(date('Y-m-d H:i:s', $this->_infoLlamadas['llamadas'][$sKey]->end_timestamp), 
                            $iDuracionLlamada, 
                            $this->_infoLlamadas['llamadas'][$sKey]->id));
                    if (DB::isError($result)) {
                        $this->oMainLog->output("ERR: $sEvent: no se puede actualizar fecha fin llamada actual - ".$result->getMessage());
                    }
                } else {
                    // Actualización de momento de fin de llamada y duración
                    $sActualizarLlamada = 'UPDATE calls SET end_time = ?, duration = ? WHERE id = ?';
                    $result =& $this->_dbConn->query($sActualizarLlamada, 
                        array(date('Y-m-d H:i:s', $this->_infoLlamadas['llamadas'][$sKey]->end_timestamp), 
                            $iDuracionLlamada, 
                            $this->_infoLlamadas['llamadas'][$sKey]->id));
                    if (DB::isError($result)) {
                        $this->oMainLog->output("ERR: $sEvent: no se puede actualizar fecha fin llamada actual - ".$result->getMessage());
                    }

                    // Puede ocurrir que se haya parado la campaña, y ya no esté en el
                    // arreglo, pero las llamadas generadas bajo esta campaña todavía 
                    // estén rezagadas.
                    $idCampaign = $this->_infoLlamadas['llamadas'][$sKey]->id_campaign; 
                    if (!isset($this->_infoLlamadas['campanias'][$idCampaign])) {
                        $tuplaCampaign = $this->_leerCampania($idCampaign);
                        if (!is_null($tuplaCampaign)) $this->_infoLlamadas['campanias'][$idCampaign] = $tuplaCampaign;
                    }
                    
                    // Calcular promedio y desviación estándar
                    if (is_null($this->_infoLlamadas['campanias'][$idCampaign]->num_completadas))
                        $this->_infoLlamadas['campanias'][$idCampaign]->num_completadas = 0;

                    // Calcular nuevo promedio
                    if ($this->_infoLlamadas['campanias'][$idCampaign]->num_completadas > 0) {
                        $iNuevoPromedio = $this->_nuevoPromedio(
                            $this->_infoLlamadas['campanias'][$idCampaign]->promedio, 
                            $this->_infoLlamadas['campanias'][$idCampaign]->num_completadas, 
                            $iDuracionLlamada);
                    } else {
                        $iNuevoPromedio = $iDuracionLlamada;
                    }

                    // Calcular nueva desviación estándar
                    if ($this->_infoLlamadas['campanias'][$idCampaign]->num_completadas > 1) {
                        $iNuevaVariancia = $this->_nuevaVarianciaMuestra(
                            $this->_infoLlamadas['campanias'][$idCampaign]->promedio,
                            $iNuevoPromedio,
                            $this->_infoLlamadas['campanias'][$idCampaign]->num_completadas, 
                            $this->_infoLlamadas['campanias'][$idCampaign]->variancia,
                            $iDuracionLlamada);
                    } else if ($this->_infoLlamadas['campanias'][$idCampaign]->num_completadas == 1) {
                        $iViejoPromedio = $this->_infoLlamadas['campanias'][$idCampaign]->promedio;
                        $iNuevaVariancia = 
                            ($iViejoPromedio - $iNuevoPromedio) * ($iViejoPromedio - $iNuevoPromedio) + 
                            ($iDuracionLlamada - $iNuevoPromedio) * ($iDuracionLlamada - $iNuevoPromedio);
                    } else {
                        $iNuevaVariancia = NULL;                
                    }            
                    $this->_infoLlamadas['campanias'][$idCampaign]->num_completadas++;
                    $this->_infoLlamadas['campanias'][$idCampaign]->promedio = $iNuevoPromedio;
                    $this->_infoLlamadas['campanias'][$idCampaign]->variancia = $iNuevaVariancia;
                    $this->_infoLlamadas['campanias'][$idCampaign]->desviacion = sqrt($iNuevaVariancia);

                    if ($this->DEBUG) {
                        $this->oMainLog->output("DEBUG: luego de ".($this->_infoLlamadas['campanias'][$idCampaign]->num_completadas)." llamadas: ".
                            sprintf('prom: %.2f var: %.2f std.dev: %.2f', 
                                $this->_infoLlamadas['campanias'][$idCampaign]->promedio,
                                $this->_infoLlamadas['campanias'][$idCampaign]->variancia,
                                $this->_infoLlamadas['campanias'][$idCampaign]->desviacion));
                    }

                    // Actualizar datos estadísticos de campaña
                    $sActualizarCampania = 'UPDATE campaign SET num_completadas = ?, promedio = ?, desviacion = ? WHERE id = ?';
                    do {
                        $bErrorLocked = FALSE;
                        $result =& $this->_dbConn->query(
                            $sActualizarCampania,
                            array(
                                $this->_infoLlamadas['campanias'][$idCampaign]->num_completadas,
                                $this->_infoLlamadas['campanias'][$idCampaign]->promedio,
                                $this->_infoLlamadas['campanias'][$idCampaign]->desviacion,
                                $idCampaign));
                        if (DB::isError($result)) {
                            $bErrorLocked = ereg('database is locked', $result->getMessage());
                            if ($bErrorLocked) {
                                usleep(125000);
                            } else {
                                $this->oMainLog->output("ERR: $sEvent: no se puede insertar llamada actual - ".$result->getMessage());
                            }
                        }
                    } while (DB::isError($result) && $bErrorLocked);
                } /* !$bLlamadaCorta */
            } /* is_null(start_timestamp) */
            
            // Al fin, quitar la llamada del arreglo de llamadas
            unset($this->_infoLlamadas['llamadas'][$sKey]);
        }
        return FALSE;
    }

    // Callback llamado para todos los eventos no manejados por otro callback
    function OnDefault($sEvent, $params, $sServer, $iPort)
    {
        if ($this->DEBUG) {
            $this->oMainLog->output("DEBUG: $sEvent:\nparams => ".print_r($params, TRUE));
        }
        return FALSE;
    }

    function _nuevoPromedio($iViejoProm, $n, $x)
    {
    	return $iViejoProm + ($x - $iViejoProm) / ($n + 1);
    }
    
    function _nuevaVarianciaMuestra($iViejoProm, $iNuevoProm, $n, $iViejaVar, $x) 
    {
        return ($n * $iViejaVar + ($x - $iNuevoProm) * ($x - $iViejoProm)) / ($n + 1);
    }

    // Al terminar el demonio, se desconecta Asterisk y base de datos
    function limpiezaDemonio($signum)
    {
        // Marcar como inválidas las llamadas que sigan en curso
        if (!is_null($this->_oGestorEntrante)) $this->_oGestorEntrante->finalizarLlamadasEnCurso();

        if (!is_null($this->_astConn)) {
        	$this->_astConn->disconnect();
            $this->_astConn = NULL;
        }
        if (!is_null($this->_dbConn)) {
        	$this->_dbConn->disconnect();
            $this->_dbConn = NULL;
        }
    }
}
?>
