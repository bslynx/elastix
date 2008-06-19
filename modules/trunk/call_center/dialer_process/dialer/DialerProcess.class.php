<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
/* Codificación: UTF-8
   +----------------------------------------------------------------------+
   | Copyright (c) 1997-2003 PaloSanto Solutions S. A.                    |
   +----------------------------------------------------------------------+
   | Cdla. Nueva Kennedy Calle E #222 y 9na. Este                         |
   | Telfs. 2283-268, 2294-440, 2284-356                                  |
   | Guayaquil - Ecuador                                                  |
   +----------------------------------------------------------------------+
   | Este archivo fuente esta sujeto a las politicas de licenciamiento    |
   | de PaloSanto Solutions S. A. y no esta disponible publicamente.      |
   | El acceso a este documento esta restringido segun lo estipulado      |
   | en los acuerdos de confidencialidad los cuales son parte de las      |
   | politicas internas de PaloSanto Solutions S. A.                      |
   | Si Ud. esta viendo este archivo y no tiene autorizacion explicita    |
   | de hacerlo comuniquese con nosotros, podria estar infringiendo       |
   | la ley sin saberlo.                                                  |
   +----------------------------------------------------------------------+
   | Autores: Alex Villacís Lasso <a_villacis@palosanto.com>              |
   +----------------------------------------------------------------------+
  
   $Id: DialerProcess.class.php,v 1.2 2008/06/06 06:46:14 alex Exp $
*/
require_once('AbstractProcess.class.php');
require_once 'DB.php';
require_once "phpagi-asmanager-elastix.php";
//require_once "predictive.lib.php";
require_once('Predictivo.class.php');
require_once('GestorLlamadasEntrantes.class.php');

// Número mínimo de muestras para poder confiar en predicciones de marcador
define('MIN_MUESTRAS', 10);

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
    
    private $_infoLlamadas; // Información sobre las campañas leídas, por iteración
    private $_iUmbralLlamadaCorta; // Umbral por debajo del cual llamada es corta

    private $_numLlamadasOriginadas;    // Llamadas originadas sin OriginateResponse, por cola

    private $_oGestorEntrante;      // Gestor de llamadas entrantes
    
    var $DEBUG = FALSE;
    var $REPORTAR_TODO = FALSE;
    var $_iUltimoDebug = NULL;

    function inicioPostDemonio($infoConfig, &$oMainLog)
    {
        $bContinuar = TRUE;
        $this->_numLlamadasOriginadas = array();
        $this->_oGestorEntrante = NULL;

        // Guardar referencias al log del programa
        $this->oMainLog =& $oMainLog;
        
        // Interpretar la configuración del demonio
        $this->interpretarParametrosConfiguracion($infoConfig);

        if ($bContinuar) $bContinuar = $this->iniciarConexionBaseDatos();
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
        
        // Recoger los parámetros para la conexión Asterisk
        if (isset($infoConfig['asterisk']) && isset($infoConfig['asterisk']['asthost'])) {
            $this->_sAsteriskHost = $infoConfig['asterisk']['asthost'];
            $this->oMainLog->output("Usando host de Asterisk Manager: ".$this->_sAsteriskHost);
        } else {
        	$this->_sAsteriskHost = '127.0.0.1';
            $this->oMainLog->output("Usando host (por omisión) de Asterisk Manager: ".$this->_sAsteriskHost);
        }
        $this->_sAsteriskUser = 
            (isset($infoConfig['asterisk']) && isset($infoConfig['asterisk']['astuser'])) 
            ? $infoConfig['asterisk']['astuser']
            : '';
        $this->_sAsteriskPass = 
            (isset($infoConfig['asterisk']) && isset($infoConfig['asterisk']['astpass']))
            ? $infoConfig['asterisk']['astpass']
            : '';
            
        // Recoger parámetro de llamada corta
        $this->_iUmbralLlamadaCorta = 10;
        if (isset($infoConfig['dialer']) && isset($infoConfig['dialer']['llamada_corta'])) {
            $regs = NULL;
            if (ereg('^[[:space:]]*([[:digit:]]+)[[:space:]]*$', $infoConfig['dialer']['llamada_corta'], $regs)) {
                $this->_iUmbralLlamadaCorta = $regs[1];
                $this->oMainLog->output("Usando umbral de llamada corta: ".$this->_iUmbralLlamadaCorta." segundos.");
            } else {
            	$this->oMainLog->output("ERR: valor de ".$infoConfig['dialer']['llamada_corta']." no es válido para umbral de llamada corta.");
                $this->oMainLog->output("Usando umbral de llamada corta (por omisión): ".$this->_iUmbralLlamadaCorta." segundos.");
            }
        } else {
        	$this->oMainLog->output("Usando umbral de llamada corta (por omisión): ".$this->_iUmbralLlamadaCorta." segundos.");
        }
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

    // Iniciar la conexión al Asterisk Manager
    private function iniciarConexionAsterisk()
    {
        if (!is_null($this->_astConn)) {
            $this->oMainLog->output('INFO: Desconectando de sesión previa de Asterisk...');
            $this->_astConn->disconnect();
            $this->_astConn = NULL;            
        }
        $astman = new AGI_AsteriskManager();
        $astman->setLogger($this->oMainLog);

        $this->oMainLog->output('INFO: Iniciando sesión de control de Asterisk...');
        if (!$astman->connect(
                $this->_sAsteriskHost, 
                $this->_sAsteriskUser,
                $this->_sAsteriskPass)) {
            $this->oMainLog->output("FATAL: no se puede conectar a Asterisk Manager\n");
            return FALSE;
        } else {
            if ($this->REPORTAR_TODO)
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
                'promedio, desviacion, retries '.
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
                'promedio, desviacion, retries '.
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
            if (is_null($this->_astConn)) {
            	$this->iniciarConexionAsterisk();
            }
            if (!$this->_oGestorEntrante->isAstConnValid()) {
            	// La conexión al Asterisk se perdió en medio de proceso de llamadas 
                // entrantes.                
                $this->iniciarConexionAsterisk();
            }

            if (!is_null($this->_astConn)) {
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
                    $bLlamadasAgregadas = $bLlamadasAgregadas ||
                        $this->actualizarLlamadasCampania($infoCampania);
                }
                
                // Consumir todos los eventos de llamada durante 3 segundos
                $iTimestampInicioEspera = time();
                while (time() - $iTimestampInicioEspera <= 3) {
                     $this->_astConn->SetTimeout(4);
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
            $oPredictor->setTiempoContestar($infoCampania->queue, 8);
        }
        $iMaxPredecidos = $oPredictor->predecirNumeroLlamadas(
            $infoCampania->queue, 
            ($infoCampania->num_completadas >= MIN_MUESTRAS));
        if ($iNumLlamadasColocar > $iMaxPredecidos)
            $iNumLlamadasColocar = $iMaxPredecidos;

        if (!isset($this->_numLlamadasOriginadas[$infoCampania->queue])) {
        	$this->_numLlamadasOriginadas[$infoCampania->queue] = 0;
        }
        if ($this->DEBUG) {
            if ($this->_numLlamadasOriginadas[$infoCampania->queue] > 0)
                $this->oMainLog->output("DEBUG: (cola $infoCampania->queue) todavia quedan ".$this->_numLlamadasOriginadas[$infoCampania->queue]." llamadas pendientes de OriginateResponse!");
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
            	$this->oMainLog->output("DEBUG: no hay agentes libres ni a punto de desocuparse!");
            }
            return FALSE;	
        }

        if ($this->DEBUG) {
            $this->oMainLog->output("DEBUG: se pueden colocar un máximo de $iNumLlamadasColocar llamadas...");	
        }        
        
        // Leer tantas llamadas como fueron elegidas. Sólo se leen números con
        // status == NULL y bandera desactivada
        $sPeticionLlamadas = 
            'SELECT id_campaign, id, phone FROM calls '.
            'WHERE id_campaign = ? AND status IS NULL AND dnc = 0 LIMIT 0,?';

        $recordset =& $this->_dbConn->query(
            $sPeticionLlamadas, 
            array($infoCampania->id, $iNumLlamadasColocar));
        if (DB::isError($recordset)) {
            $this->oMainLog->output("ERR: no se puede leer lista de teléfonos - ".$recordset->getMessage());
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
            // Leer los números que no han podido ser contactados antes 
            $sPeticionLlamadas = 
                'SELECT id_campaign, id, phone FROM calls '.
                'WHERE id_campaign = ? '.
                    'AND status NOT IN ("Success", "Placing", "Ringing", "OnQueue", "OnHold") '.
                    'AND retries < ? '.
                    'AND dnc = 0 '.
                'ORDER BY fecha_llamada, retries '.
                'LIMIT 0,?';
            $recordset =& $this->_dbConn->query(
                $sPeticionLlamadas, 
                array($infoCampania->id, $infoCampania->retries, $iNumLlamadasColocar));
            if (DB::isError($recordset)) {
                $this->oMainLog->output("ERR: no se puede leer lista de teléfonos - ".$recordset->getMessage());
                return FALSE;
            }
            
            // Ingresar las llamada a la lista de llamadas a realizar
            $listaLlamadas = array();
            $pid = posix_getpid();
            while ($tupla = $recordset->fetchRow(DB_FETCHMODE_OBJECT)) {
                $sKey = sprintf('%d-%d-%d', $pid, $infoCampania->id, $tupla->id);
                $listaLlamadas[$sKey] = $tupla;
            }
        }

        if (count($listaLlamadas) > 0) {
            if ($this->DEBUG) {
                $this->oMainLog->output("DEBUG: total de llamadas a generar: ".count($listaLlamadas));
            }

            // Colocar todas las llamadas elegidas para ser realizadas por el Asterisk.
            foreach ($listaLlamadas as $sKey => $tupla) {
                if ($this->DEBUG) {
                    $this->oMainLog->output("DEBUG: generando llamada hacia $tupla->phone en cola $infoCampania->queue, contexto $infoCampania->context trunk $infoCampania->trunk");
                }
                $resultado = $this->_astConn->Originate(
                    $infoCampania->trunk."/".$tupla->phone, $infoCampania->queue, $infoCampania->context, 1,
                    NULL, NULL, NULL, NULL, NULL, NULL, 
                    TRUE, $sKey);
                if (!is_array($resultado) || count($resultado) == 0) {
                	$this->oMainLog->output("ERR: problema al enviar Originate a Asterisk");
                    $this->iniciarConexionAsterisk();
                }
                if ($resultado['Response'] == 'Success') {
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
                    $this->oMainLog->output("ERR: no se puede llamar a número - $resultado[Message]");
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
                $this->oMainLog->output("ERR: no se puede marcar campaña como terminada - ".$result->getMessage());
            }

            return FALSE;
        }
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
                $result = $this->_dbConn->query(
                    'UPDATE calls SET status = ?, Uniqueid = ?, fecha_llamada = ?, start_time = NULL, end_time = NULL '.
                        'WHERE id_campaign = ? AND id = ?',
                    array($sStatus, $params['Uniqueid'], date('Y-m-d H:i:s'),
                        $infoCampania->id, $this->_infoLlamadas['llamadas'][$sKey]->id));
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
            } else {
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
        if (!is_null($sKey)) {
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
                $this->oMainLog->output("ERR: $sEvent: se perdió evento Link para llamada $sKey => ".
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

    // Callback invocado al llegar el evento Hangup
    function OnHangup($sEvent, $params, $sServer, $iPort)
    {    
        // Lo siguiente sirve porque tanto Unlink como Hangup comparten un Uniqueid
        return $this->OnUnlink($sEvent, $params, $sServer, $iPort);
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
