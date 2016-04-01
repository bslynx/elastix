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
require_once 'ECCPHelper.lib.php';

class SQLWorkerProcess extends TuberiaProcess
{
    private $DEBUG = FALSE; // VERDADERO si se activa la depuración

    private $_log;      // Log abierto por framework de demonio
    private $_dsn;      // Cadena que representa el DSN, estilo PDO
    private $_db;       // Conexión a la base de datos, PDO
    private $_configDB; // Objeto de configuración desde la base de datos

    // Contadores para actividades ejecutadas regularmente
    private $_iTimestampActualizacion = 0;          // Última actualización remota
    private $_iTimestampUltimaRevisionConfig = 0;   // Última revisión de configuración

    /* Lista de acciones pendientes encargadas por otros procesos. Cada elemento
     * de este arreglo es una tupla cuyo primer elemento es callable y el segundo
     * elemento es la lista de parámetros con los que se debe invocar el callable.
     * Ya que todos los callables usan la base de datos, es posible que la
     * ejecución arroje excepciones PDOException. Todos los callables se invocan
     * dentro de una transacción de la base de datos, la cual se hará commit()
     * en caso de que no se arrojen excepciones. De lo contrario, y si la conexión
     * sigue siendo válida, se realizará un rollback() y se reintentará la operación
     * en un momento posterior. Todos los callables deben de devolver un arreglo
     * que contiene los eventos a ser lanzados como resultado de haber completado
     * las operaciones correspondientes.
     */
    private $_accionesPendientes = array();

    private $_finalizandoPrograma = FALSE;

    public function inicioPostDemonio($infoConfig, &$oMainLog)
    {
    	$this->_log = $oMainLog;
        $this->_multiplex = new MultiplexServer(NULL, $this->_log);
        $this->_tuberia->registrarMultiplexHijo($this->_multiplex);
        $this->_tuberia->setLog($this->_log);

        // Interpretar la configuración del demonio
        $this->_dsn = $this->_interpretarConfiguracion($infoConfig);
        if (!$this->_iniciarConexionDB()) return FALSE;

        // Leer el resto de la configuración desde la base de datos
        try {
            $this->_configDB = new ConfigDB($this->_db, $this->_log);
        } catch (PDOException $e) {
            $this->_log->output("FATAL: no se puede leer configuración DB - ".$e->getMessage());
            return FALSE;
        }

        // Registro de manejadores de eventos desde AMIEventProcess
        foreach (array('sqlinsertcalls', 'sqlupdatecalls',
            'sqlinsertcurrentcalls', 'sqldeletecurrentcalls',
            'sqlupdatecurrentcalls', 'sqlupdatestatcampaign', 'finalsql',
            'verificarFinLlamadasAgendables', 'agregarArchivoGrabacion') as $k)
            $this->_tuberia->registrarManejador('AMIEventProcess', $k, array($this, "msg_$k"));

        // Registro de manejadores de eventos desde ECCPWorkerProcess
        foreach (array('requerir_nuevaListaAgentes') as $k)
            $this->_tuberia->registrarManejador('*', $k, array($this, "msg_$k"));

        // Registro de manejadores de eventos desde HubProcess
        $this->_tuberia->registrarManejador('HubProcess', 'finalizando', array($this, "msg_finalizando"));

        $this->DEBUG = $this->_configDB->dialer_debug;

        // Informar a AMIEventProcess la configuración de Asterisk
        $this->_tuberia->AMIEventProcess_informarCredencialesAsterisk(array(
            'asterisk'  =>  array(
                'asthost'           =>  $this->_configDB->asterisk_asthost,
                'astuser'           =>  $this->_configDB->asterisk_astuser,
                'astpass'           =>  $this->_configDB->asterisk_astpass,
                'duracion_sesion'   =>  $this->_configDB->asterisk_duracion_sesion,
            ),
            'dialer'    =>  array(
                'llamada_corta'     =>  $this->_configDB->dialer_llamada_corta,
                'tiempo_contestar'  =>  $this->_configDB->dialer_tiempo_contestar,
                'debug'             =>  $this->_configDB->dialer_debug,
                'allevents'         =>  $this->_configDB->dialer_allevents,
            ),
        ));

        return TRUE;
    }

    private function _interpretarConfiguracion($infoConfig)
    {
        $dbHost = 'localhost';
        $dbUser = 'asterisk';
        $dbPass = 'asterisk';
        if (isset($infoConfig['database']) && isset($infoConfig['database']['dbhost'])) {
            $dbHost = $infoConfig['database']['dbhost'];
            $this->_log->output('Usando host de base de datos: '.$dbHost);
        } else {
            $this->_log->output('Usando host (por omisión) de base de datos: '.$dbHost);
        }
        if (isset($infoConfig['database']) && isset($infoConfig['database']['dbuser']))
            $dbUser = $infoConfig['database']['dbuser'];
        if (isset($infoConfig['database']) && isset($infoConfig['database']['dbpass']))
            $dbPass = $infoConfig['database']['dbpass'];

        return array("mysql:host=$dbHost;dbname=call_center", $dbUser, $dbPass);
    }

    private function _iniciarConexionDB()
    {
        try {
            $this->_db = new PDO($this->_dsn[0], $this->_dsn[1], $this->_dsn[2]);
            $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            return TRUE;
        } catch (PDOException $e) {
            $this->_db = NULL;
            $this->_log->output("FATAL: no se puede conectar a DB - ".$e->getMessage());
            return FALSE;
        }
    }

    public function procedimientoDemonio()
    {
        // Lo siguiente NO debe de iniciar operaciones DB, sólo acumular acciones
        $bPaqProcesados = $this->_multiplex->procesarPaquetes();
        $this->_multiplex->procesarActividad(($bPaqProcesados || (count($this->_accionesPendientes) > 0)) ? 0 : 1);

        // Verificar posible desconexión de la base de datos
        if (is_null($this->_db)) {
            if (count($this->_accionesPendientes) > 0) {
                $this->_log->output('INFO: falta conexión DB y hay '.count($this->_accionesPendientes).' acciones pendientes.');
                if ($this->DEBUG) {
                    foreach ($this->_accionesPendientes as $accion)
                        $this->_volcarAccion($accion);
                }
            }
            $this->_log->output('INFO: intentando volver a abrir conexión a DB...');
            if (!$this->_iniciarConexionDB()) {
                $this->_log->output('ERR: no se puede restaurar conexión a DB, se espera...');

                $t1 = time();
                do {
                    $this->_multiplex->procesarPaquetes();
                    $this->_multiplex->procesarActividad(1);
                } while (time() - $t1 < 5);
            } else {
                $this->_log->output('INFO: conexión a DB restaurada, se reinicia operación normal.');
                $this->_configDB->setDBConn($this->_db);
            }
        } else {
            $this->_procesarUnaAccion();
        }

        return TRUE;
    }

    private function _procesarUnaAccion()
    {
        try {
            if (!$this->_finalizandoPrograma) {
                // Verificar si se ha cambiado la configuración
                $this->_verificarCambioConfiguracion();

                // Verificar si hay que refrescar agentes disponibles
                $this->_verificarActualizacionAgentes();
            }

            /* Por ahora se intenta ejecutar todas las operaciones, incluso
             * si se intenta finalizar el programa. */
            if (count($this->_accionesPendientes) > 0) {
                $this->_db->beginTransaction();

                if ($this->DEBUG) {
                    $this->_volcarAccion($this->_accionesPendientes[0]);
                }
                $eventos = call_user_func_array(
                    $this->_accionesPendientes[0][0],
                    $this->_accionesPendientes[0][1]);
                if ($this->DEBUG) {
                    $this->_log->output('DEBUG: acción ejecutada correctamente.');
                }

                array_shift($this->_accionesPendientes);
                $this->_lanzarEventos($eventos);

                $this->_db->commit();
            }
        } catch (PDOException $e) {
            if ($this->DEBUG || !esReiniciable($e)) {
                $this->_log->output('ERR: '.__METHOD__.
                    ': no se puede realizar operación de base de datos: '.
                    implode(' - ', $e->errorInfo));
                $this->_log->output("ERR: traza de pila: \n".$e->getTraceAsString());
            }
            if ($e->errorInfo[0] == 'HY000' && $e->errorInfo[1] == 2006) {
                // Códigos correspondientes a pérdida de conexión de base de datos
                $this->_log->output('WARN: '.__METHOD__.
                    ': conexión a DB parece ser inválida, se cierra...');
                $this->_db = NULL;
            } else {
                $this->_db->rollBack();
            }
        }
    }

    private function _volcarAccion(&$accion)
    {
        $this->_log->output('DEBUG: acción pendiente '.$accion[0][1].': '.print_r($accion[1], TRUE));
    }

    private function _lanzarEventos(&$eventos)
    {
        foreach ($eventos as $ev) {
            list($target, $msg, $args) = $ev;
            call_user_func_array(
                array($this->_tuberia, 'msg_'.$target.'_'.$msg),
                $args);
        }
    }

    public function limpiezaDemonio($signum)
    {
        // Mandar a cerrar todas las conexiones activas
        $this->_multiplex->finalizarServidor();

        // Se intentan evacuar acciones pendientes
        if (count($this->_accionesPendientes) > 0)
            $this->_log->output('WARN: todavía hay '.count($this->_accionesPendientes).' acciones pendientes.');
        $t1 = time();
        while (time() - $t1 < 10 && !is_null($this->_db) &&
            count($this->_accionesPendientes) > 0) {
            $this->_procesarUnaAccion();

            // No se hace I/O y por lo tanto no se lanzan eventos
        }
        if (count($this->_accionesPendientes) > 0)
            $this->_log->output('ERR: no se pueden evacuar las siguientes acciones: '.
                print_r($this->_accionesPendientes, TRUE));

        // Desconectarse de la base de datos
        $this->_configDB = NULL;
        if (!is_null($this->_db)) {
            $this->_log->output('INFO: desconectando de la base de datos...');
            $this->_db = NULL;
        }
    }

    private function _verificarCambioConfiguracion()
    {
        $iTimestamp = time();
        if ($iTimestamp - $this->_iTimestampUltimaRevisionConfig > 3) {
            $this->_configDB->leerConfiguracionDesdeDB();
            $listaVarCambiadas = $this->_configDB->listaVarCambiadas();
            if (count($listaVarCambiadas) > 0) {
                foreach ($listaVarCambiadas as $k) {
                    if (in_array($k, array('asterisk_asthost', 'asterisk_astuser', 'asterisk_astpass'))) {
                        $this->_tuberia->msg_AMIEventProcess_actualizarConfig(
                            'asterisk_cred', array(
                                $this->_configDB->asterisk_asthost,
                                $this->_configDB->asterisk_astuser,
                                $this->_configDB->asterisk_astpass,
                            ));
                    } elseif (in_array($k, array('asterisk_duracion_sesion',
                        'dialer_llamada_corta', 'dialer_tiempo_contestar',
                        'dialer_debug', 'dialer_allevents'))) {
                        $this->_tuberia->msg_AMIEventProcess_actualizarConfig(
                            $k, $this->_configDB->$k);
                    }
                }

                if (in_array('dialer_debug', $listaVarCambiadas))
                    $this->DEBUG = $this->_configDB->dialer_debug;
                $this->_configDB->limpiarCambios();
            }
            $this->_iTimestampUltimaRevisionConfig = $iTimestamp;
        }
    }

    /* Mandar a los otros procedimientos la información que no pueden leer
     * directamente porque no tienen conexión de base de datos. */
    private function _verificarActualizacionAgentes()
    {
        $iTimestamp = time();
        if ($iTimestamp - $this->_iTimestampActualizacion >= 5 * 60) {
            $this->_actualizarInformacionRemota_agentes();

            $this->_iTimestampActualizacion = $iTimestamp;
        }
    }

    function _actualizarInformacionRemota_agentes()
    {
        $eventos = $this->_requerir_nuevaListaAgentes();
        $this->_lanzarEventos($eventos);
    }

    /**************************************************************************/

    private function _encolarAccionPendiente($method, $params)
    {
        array_push($this->_accionesPendientes, array(
            array($this, $method),    // callable
            $params,    // params
        ));

    }

    public function msg_requerir_nuevaListaAgentes($sFuente, $sDestino, $sNombreMensaje, $iTimestamp, $datos)
    {
        $this->_log->output("INFO: $sFuente requiere refresco de lista de agentes");
        $this->_encolarAccionPendiente('_requerir_nuevaListaAgentes', $datos);
    }

    public function msg_sqlinsertcalls($sFuente, $sDestino, $sNombreMensaje, $iTimestamp, $datos)
    {
        if ($this->DEBUG) {
            $this->_log->output('DEBUG: '.__METHOD__.' - '.print_r($datos, 1));
        }
        $this->_encolarAccionPendiente('_sqlinsertcalls', $datos);
    }

    public function msg_sqlupdatecalls($sFuente, $sDestino, $sNombreMensaje, $iTimestamp, $datos)
    {
        if ($this->DEBUG) {
            $this->_log->output('DEBUG: '.__METHOD__.' - '.print_r($datos, 1));
        }
        $this->_encolarAccionPendiente('_sqlupdatecalls', $datos);
    }

    public function msg_sqlupdatecurrentcalls($sFuente, $sDestino, $sNombreMensaje, $iTimestamp, $datos)
    {
        if ($this->DEBUG) {
            $this->_log->output('DEBUG: '.__METHOD__.' - '.print_r($datos, 1));
        }
        $this->_encolarAccionPendiente('_sqlupdatecurrentcalls', $datos);
    }

    public function msg_sqlinsertcurrentcalls($sFuente, $sDestino, $sNombreMensaje, $iTimestamp, $datos)
    {
        if ($this->DEBUG) {
            $this->_log->output('DEBUG: '.__METHOD__.' - '.print_r($datos, 1));
        }
        $this->_encolarAccionPendiente('_sqlinsertcurrentcalls', $datos);
    }

    public function msg_sqldeletecurrentcalls($sFuente, $sDestino, $sNombreMensaje, $iTimestamp, $datos)
    {
        if ($this->DEBUG) {
            $this->_log->output('DEBUG: '.__METHOD__.' - '.print_r($datos, 1));
        }
        $this->_encolarAccionPendiente('_sqldeletecurrentcalls', $datos);
    }

    public function msg_sqlupdatestatcampaign($sFuente, $sDestino, $sNombreMensaje, $iTimestamp, $datos)
    {
        if ($this->DEBUG) {
            $this->_log->output('DEBUG: '.__METHOD__.' - '.print_r($datos, 1));
        }
        $this->_encolarAccionPendiente('_sqlupdatestatcampaign', $datos);
    }

    public function msg_agregarArchivoGrabacion($sFuente, $sDestino,
        $sNombreMensaje, $iTimestamp, $datos)
    {
        if ($this->DEBUG) {
            $this->_log->output('DEBUG: '.__METHOD__.' - '.print_r($datos, 1));
        }
        $this->_encolarAccionPendiente('_agregarArchivoGrabacion', $datos);
    }

    public function msg_finalizando($sFuente, $sDestino, $sNombreMensaje, $iTimestamp, $datos)
    {
        $this->_log->output('INFO: recibido mensaje de finalización...');
        $this->_finalizandoPrograma = TRUE;
    }

    public function msg_finalsql($sFuente, $sDestino, $sNombreMensaje, $iTimestamp, $datos)
    {
        if (!$this->_finalizandoPrograma) {
            $this->_log->output('WARN: AMIEventProcess envió mensaje antes que HubProcess');
        }
        $this->_finalizandoPrograma = TRUE;
        $this->_tuberia->msg_HubProcess_finalizacionTerminada();
    }

    /**************************************************************************/

    // Mandar a AMIEventProcess una lista actualizada de los agentes activos
    private function _requerir_nuevaListaAgentes()
    {
        // El ORDER BY del query garantiza que estatus A aparece antes que I
        $recordset = $this->_db->query(
            'SELECT id, number, name, estatus, type FROM agent ORDER BY number, estatus');
        $lista = array(); $listaNum = array();
        foreach ($recordset as $tupla) {
            if (!in_array($tupla['number'], $listaNum)) {
                $lista[] = array(
                    'id'        =>  $tupla['id'],
                    'number'    =>  $tupla['number'],
                    'name'      =>  $tupla['name'],
                    'estatus'   =>  $tupla['estatus'],
                    'type'      =>  $tupla['type'],
                );
                $listaNum[] = $tupla['number'];
            }
        }

        /* Leer el estado de las banderas de activación de eventos de las colas
         * a partir del archivo de configuración. El código a continuación
         * depende de la existencia de queues_additional.conf de una instalación
         * FreePBX, y además asume Asterisk 11 o inferior. Se debe modificar
         * esto cuando se migre a una versión superior de Asterisk que siempre
         * emite los eventos. */
        $queueflags = array();
        if (file_exists('/etc/asterisk/queues_additional.conf')) {
            $queue = NULL;
            foreach (file('/etc/asterisk/queues_additional.conf') as $s) {
                $regs = NULL;
                if (preg_match('/^\[(\S+)\]/', $s, $regs)) {
                    $queue = $regs[1];
                    $queueflags[$queue]['eventmemberstatus'] = FALSE;
                    $queueflags[$queue]['eventwhencalled'] = FALSE;
                } elseif (preg_match('/^(\w+)\s*=\s*(.*)/', trim($s), $regs)) {
                    if (in_array($regs[1], array('eventmemberstatus', 'eventwhencalled'))) {
                        $queueflags[$queue][$regs[1]] = in_array($regs[2], array('yes', 'true', 'y', 't', 'on', '1'));
                    } elseif ($regs[1] == 'member' && (stripos($regs[2], 'SIP/') === 0 || stripos($regs[2], 'IAX2/') === 0)) {
                        $this->_log->output('WARN: '.__METHOD__.': agente estático '.
                            $regs[2].' encontrado en cola '.$queue.' - puede causar problemas.');
                    }
                }
            }
        }

        // Mandar el recordset a AMIEventProcess como un mensaje
        return array(
            array('AMIEventProcess', 'nuevaListaAgentes', array($lista, $queueflags)),
        );
    }

    private function _sqlinsertcalls($paramInsertar)
    {
        $eventos = array();

        // Porción que identifica la tabla a modificar
        $tipo_llamada = $paramInsertar['tipo_llamada'];
        unset($paramInsertar['tipo_llamada']);
        switch ($tipo_llamada) {
        case 'outgoing':
            $sqlTabla = 'INSERT INTO calls ';
            break;
        case 'incoming':
            $sqlTabla = 'INSERT INTO call_entry ';
            break;
        default:
            $this->_log->output('ERR: '.__METHOD__.' no debió haberse recibido para '.
                print_r($paramInsertar, TRUE));
            return $eventos;
        }

        // Recoger el canal para llamada entrante
        $channel = NULL;
        if (isset($paramInsertar['channel'])) {
            $channel = $paramInsertar['channel'];
            unset($paramInsertar['channel']);
        }

        // Caso especial: llamada entrante requiere ID de contacto
        if ($tipo_llamada == 'incoming') {
            /* Se consulta el posible contacto en base al caller-id. Si hay
             * exactamente un contacto, su ID se usa para la inserción. */
            $recordset = $this->_db->prepare('SELECT id FROM contact WHERE telefono = ?');

            $recordset->execute(array($paramInsertar['callerid']));
            $listaIdContactos = $recordset->fetchAll(PDO::FETCH_COLUMN, 0);
            if (count($listaIdContactos) == 1) {
                $paramInsertar['id_contact'] = $listaIdContactos[0];
            }
        }

        $sqlCampos = array();
        $params = array();
        foreach ($paramInsertar as $k => $v) {
            $sqlCampos[] = $k;
            $params[] = $v;
        }
        $sql = $sqlTabla.'('.implode(', ', $sqlCampos).') VALUES ('.
            implode(', ', array_fill(0, count($params), '?')).')';

        $sth = $this->_db->prepare($sql);
        $sth->execute($params);
        $idCall = $this->_db->lastInsertId();

        // Mandar de vuelta el ID de inserción a AMIEventProcess
        $eventos[] = array('AMIEventProcess', 'idnewcall',
            array($tipo_llamada, $paramInsertar['uniqueid'], $idCall));

        // Para llamada entrante se debe de insertar el log de progreso
        if ($tipo_llamada == 'incoming') {
            // Notificar el progreso de la llamada
            $infoProgreso = array(
                'datetime_entry'        =>  $paramInsertar['datetime_entry_queue'],
                'new_status'            =>  'OnQueue',
                'id_campaign_incoming'  =>  $paramInsertar['id_campaign'],
                'id_call_incoming'      =>  $idCall,
                'uniqueid'              =>  $paramInsertar['uniqueid'],
                'trunk'                 =>  $paramInsertar['trunk'],
            );

            // TODO: traer actualización SQL a este proceso
            $eventos[] = array('ECCPProcess', 'notificarProgresoLlamada',
                array($infoProgreso));
        }

        return $eventos;
    }

    // Procedimiento que actualiza una sola llamada de la tabla calls o call_entry
    private function _sqlupdatecalls($paramActualizar)
    {
        $eventos = array();

        $sql_list = array();
        $id_llamada = NULL;

        // Porción que identifica la tabla a modificar
        $tipo_llamada = $paramActualizar['tipo_llamada'];
        unset($paramActualizar['tipo_llamada']);
        switch ($tipo_llamada) {
        case 'outgoing':
            $sqlTabla = 'UPDATE calls SET ';
            break;
        case 'incoming':
            $sqlTabla = 'UPDATE call_entry SET ';
            break;
        default:
            $this->_log->output('ERR: '.__METHOD__.' no debió haberse recibido para '.
                print_r($paramActualizar, TRUE));
            return $eventos;
        }

        // Porción que identifica la tupla a modificar
        $sqlWhere = array();
        $paramWhere = array();
        if (isset($paramActualizar['id_campaign'])) {
            if (!is_null($paramActualizar['id_campaign'])) {
                $sqlWhere[] = 'id_campaign = ?';
                $paramWhere[] = $paramActualizar['id_campaign'];
            }
            unset($paramActualizar['id_campaign']);
        }
        if (isset($paramActualizar['id'])) {
            $sqlWhere[] = 'id = ?';
            $paramWhere[] = $paramActualizar['id'];
            $id_llamada = $paramActualizar['id'];
            unset($paramActualizar['id']);
        }

        // Parámetros a modificar
        $sqlCampos = array();
        $paramCampos = array();

        // TODO: revisar si es necesario inc_retries, porque campañas
        // salientes incrementan directamente al cambiar a Placing
        //
        // Caso especial: retries se debe de incrementar
        if (isset($paramActualizar['inc_retries'])) {
            $sqlCampos[] = 'retries = retries + ?';
            $paramCampos[] = $paramActualizar['inc_retries'];
            unset($paramActualizar['inc_retries']);
        }
        foreach ($paramActualizar as $k => $v) {
            $sqlCampos[] = "$k = ?";
            $paramCampos[] = $v;
        }
        $sql_list[] = array(
            $sqlTabla.implode(', ', $sqlCampos).' WHERE '.implode(' AND ', $sqlWhere),
            array_merge($paramCampos, $paramWhere),
        );

        $id_contact = NULL;
        $failstates = array('Failure', 'NoAnswer', 'ShortCall', 'Abandoned');

        foreach ($sql_list as $sql_item) {
            $sth = $this->_db->prepare($sql_item[0]);
            $sth->execute($sql_item[1]);
        }

        return $eventos;
    }

    // Procedimiento que inserta un solo registro en current_calls o current_call_entry
    private function _sqlinsertcurrentcalls($paramInsertar)
    {
        $eventos = array();

        // Porción que identifica la tabla a modificar
        $tipo_llamada = $paramInsertar['tipo_llamada'];
        unset($paramInsertar['tipo_llamada']);
        switch ($tipo_llamada) {
        case 'outgoing':
            $sqlTabla = 'INSERT INTO current_calls ';
            break;
        case 'incoming':
            $sqlTabla = 'INSERT INTO current_call_entry ';
            break;
        default:
            $this->_log->output('ERR: '.__METHOD__.' no debió haberse recibido para '.
                print_r($paramInsertar, TRUE));
            return $eventos;
        }

        $sqlCampos = array();
        $params = array();
        foreach ($paramInsertar as $k => $v) {
            $sqlCampos[] = $k;
            $params[] = $v;
        }
        $sql = $sqlTabla.'('.implode(', ', $sqlCampos).') VALUES ('.
            implode(', ', array_fill(0, count($params), '?')).')';

        $sth = $this->_db->prepare($sql);
        $sth->execute($params);

        // Mandar de vuelta el ID de inserción a AMIEventProcess
        $eventos[] = array('AMIEventProcess', 'idcurrentcall', array(
            $tipo_llamada,
            isset($paramInsertar['id_call_entry'])
            ? $paramInsertar['id_call_entry']
            : $paramInsertar['id_call'],
            $this->_db->lastInsertId())
        );

        return $eventos;
    }

    // Procedimiento que actualiza un solo registro en current_calls o current_call_entry
    private function _sqlupdatecurrentcalls($paramActualizar)
    {
        $eventos = array();

        // Porción que identifica la tabla a modificar
        switch ($paramActualizar['tipo_llamada']) {
        case 'outgoing':
            $sqlTabla = 'UPDATE current_calls SET ';
            break;
        case 'incoming':
            $sqlTabla = 'UPDATE current_call_entry SET ';
            break;
        default:
            $this->_log->output('ERR: '.__METHOD__.' no debió haberse recibido para '.
                print_r($paramActualizar, TRUE));
            return $eventos;
        }
        unset($paramActualizar['tipo_llamada']);

        // Porción que identifica la tupla a modificar
        $sqlWhere = array();
        $paramWhere = array();
        if (isset($paramActualizar['id'])) {
            $sqlWhere[] = 'id = ?';
            $paramWhere[] = $paramActualizar['id'];
            unset($paramActualizar['id']);
        }

        // Parámetros a modificar
        $sqlCampos = array();
        $paramCampos = array();

        foreach ($paramActualizar as $k => $v) {
            $sqlCampos[] = "$k = ?";
            $paramCampos[] = $v;
        }

        $sql = $sqlTabla.implode(', ', $sqlCampos).' WHERE '.implode(' AND ', $sqlWhere);
        $params = array_merge($paramCampos, $paramWhere);

        $sth = $this->_db->prepare($sql);
        $sth->execute($params);

        return $eventos;
    }

    private function _sqldeletecurrentcalls($paramBorrar)
    {
        $eventos = array();

        // Esto no debería pasar (manualdialing)
        if (!in_array($paramBorrar['tipo_llamada'], array('incoming', 'outgoing'))) {
            $this->_log->output('ERR: '.__METHOD__.' no debió haberse recibido para '.
                print_r($paramBorrar, TRUE));
            return $eventos;
        }

        // Porción que identifica la tabla a modificar
        $sth = $this->_db->prepare(($paramBorrar['tipo_llamada'] == 'outgoing')
            ? 'DELETE FROM current_calls WHERE id = ?'
            : 'DELETE FROM current_call_entry WHERE id = ?');
        $sth->execute(array($paramBorrar['id']));

        return $eventos;
    }

    private function _sqlupdatestatcampaign($id_campaign, $num_completadas,
            $promedio, $desviacion)
    {
        $eventos = array();

        $sth = $this->_db->prepare(
            'UPDATE campaign SET num_completadas = ?, promedio = ?, desviacion = ? WHERE id = ?');
        $sth->execute(array($num_completadas, $promedio, $desviacion, $id_campaign));

        return $eventos;
    }

    private function _agregarArchivoGrabacion($tipo_llamada, $id_llamada, $uniqueid, $channel, $recordingfile)
    {
        $eventos = array();

        // TODO: configurar prefijo de monitoring
        $sDirBaseMonitor = '/var/spool/asterisk/monitor/';

        // Quitar el prefijo de monitoring de todos los archivos
        if (strpos($recordingfile, $sDirBaseMonitor) === 0)
            $recordingfile = substr($recordingfile, strlen($sDirBaseMonitor));

        // Se asume que el archivo está completo con extensión
        $field = 'id_call_'.$tipo_llamada;
        $recordset = $this->_db->prepare("SELECT COUNT(*) AS N FROM call_recording WHERE {$field} = ? AND recordingfile = ?");
        $recordset->execute(array($id_llamada, $recordingfile));
        $iNumDuplicados = $recordset->fetch(PDO::FETCH_COLUMN, 0);
        $recordset->closeCursor();
        if ($iNumDuplicados <= 0) {
            // El archivo no constaba antes - se inserta con los datos actuales
            $sth = $this->_db->prepare(
                "INSERT INTO call_recording (datetime_entry, {$field}, uniqueid, channel, recordingfile) ".
                'VALUES (NOW(), ?, ?, ?, ?)');
            $sth->execute(array($id_llamada, $uniqueid, $channel, $recordingfile));
        }

        return $eventos;
    }


}
