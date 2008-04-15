#!/usr/bin/php
<?php 
require_once 'DB.php';
//require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
require_once "phpagi-asmanager-elastix.php";
require_once "predictive.lib.php";
/*
$sUser = 'campaign';
$sPass = 'campaign';
$sHost = 'localhost';
$sInicialDB = 'asterisk_campaign';
$sConnStr = "mysql://$sUser:$sPass@$sHost/$sInicialDB";
*/
dl('sqlite3.so');
$sConnStr = "sqlite3:////var/www/db/campaign.db";
/*
CREATE TABLE campaign
(
    id      INTEGER  PRIMARY KEY,
    name    varchar(64)     NOT NULL,
    datetime_init   date    NOT NULL,
    datetime_end    date    NOT NULL,
    daytime_init    time    NOT NULL,
    daytime_end     time    NOT NULL,
    retries int unsigned    NOT NULL    DEFAULT 1,
    
    trunk   varchar(16)     NOT NULL,
    context varchar(32)     NOT NULL,
    queue   varchar(16)     NOT NULL,
    
    max_canales int unsigned    NOT NULL DEFAULT 0,
    
    num_completadas int unsigned,
    promedio        int unsigned,
    desviacion      int unsigned
);
CREATE TABLE calls
(
    id          INTEGER  PRIMARY KEY,
    id_campaign int unsigned    NOT NULL,
    phone       varchar(32)     NOT NULL,
    status      varchar(32),
    
    Uniqueid    varchar(32),
    
    FOREIGN KEY (id_campaign)   REFERENCES campaign(id)
);
CREATE TABLE call_attribute
(
    id          INTEGER    PRIMARY KEY,
    id_call     int unsigned    NOT NULL,
    key         varchar(32)     NOT NULL,
    value       varchar(128)    NOT NULL,
    
    FOREIGN KEY (id_call)   REFERENCES calls(id)
);
CREATE TABLE current_calls
(
    id          INTEGER     PRIMARY KEY,
    fecha_inicio datetime   NOT NULL,
    Uniqueid    varchar(32) NOT NULL,
    queue       varchar(16) NOT NULL,
    agentnum    varchar(16) NOT NULL,    
    id_call     int unsigned NOT NULL,
    
    event       varchar(32) NOT NULL,
    Channel     varchar(32) NOT NULL DEFAULT '',
    
    FOREIGN KEY (id_call) REFERENCES calls(id)
);
*/
$dbConn = DB::connect($sConnStr);
if (DB::isError($dbConn)) {
    fprintf(STDERR, "FATAL: no se puede conectar a DB - ".($dbConn->getMessage())."\n");
} else {
    $astman = new AGI_AsteriskManager();
    if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
        fprintf(STDERR, "FATAL: no se puede conectar a Asterisk Manager\n");
    } else {
        $astman->add_event_handler('Link', 'OnLink');
        $astman->add_event_handler('Unlink', 'OnUnlink');
        $astman->add_event_handler('Hangup', 'OnHangup');

        do {
            print "DEBUG: revisando si hay campañas...\n";
            $bHayLlamadas = revisarCampaniasActivas($dbConn, $astman);
            if ($bHayLlamadas)
                print "DEBUG: se colocaron algunas llamadas\n";
            else print "DEBUG: no se colocaron llamadas\n";
            sleep(1);
        } while (1);
        $astman->disconnect();
    }
    $dbConn->disconnect();
}

/**
 * Procedimiento que revisa todas las campañas que estén activas en este momento.
 * Para todas las campañas que estén activas, se ejecuta el método para agregar
 * llamadas a la campaña.
 *
 * @param object $dbConn    Conexión PEAR a la base de datos sqlite
 * @param object $astman    Conexión al Asterisk Manager
 *if ($iNumLlamadasColocar <= 0) return FALSE;
 * @return bool VERDADERO si al menos en una campaña se agregaron llamadas
 */
function revisarCampaniasActivas($dbConn, &$astman)
{
    $bLlamadasAgregadas = FALSE;
    $iTimestamp = time();
    $sFecha = date('Y-m-d', $iTimestamp);
    $sHora = date('H:i:s', $iTimestamp);
    $sPeticionCampanias = 
        'SELECT id, name, trunk, context, queue, max_canales, num_completadas, '.
            'promedio, desviacion '.
        'FROM campaign '.
        'WHERE datetime_init <= ? '.
            'AND datetime_end >= ? '.
            'AND ('.
                '(daytime_init < daytime_end AND daytime_init <= ? AND daytime_end > ?) '.
                'OR (daytime_init > daytime_end AND (? < daytime_init OR daytime_end < ?)))';
    $recordset = $dbConn->query($sPeticionCampanias, array($sFecha, $sFecha, $sHora, $sHora, $sHora, $sHora));
    if (DB::isError($recordset)) {
        // TODO: al convertir en demonio, cambiar a log
        fprintf(STDERR, "ERR: no se puede leer lista de campañas - ".($recordset->getMessage())."\n");
        //print_r($recordset);
    } else {
        $listaCampanias = array();
        while ($infoCampania = $recordset->fetchRow(DB_FETCHMODE_OBJECT)) {
            $listaCampanias[$infoCampania->id] = $infoCampania;
        }
        
        // Preparar la información a asignar a datos de app en astman
        $infoLlamadas = $astman->get_app_data();
        if (!is_array($infoLlamadas)) $infoLlamadas = array();        
        $infoLlamadas['dbconn'] = $dbConn;
        $infoLlamadas['campanias'] = $listaCampanias;
        if (!isset($infoLlamadas['llamadas'])) $infoLlamadas['llamadas'] = array();
        $astman->add_app_data($infoLlamadas);        
        
        // Agregar llamadas para todas las campañas activas
        foreach ($infoLlamadas['campanias'] as $infoCampania) {
            $bLlamadasAgregadas = $bLlamadasAgregadas ||
                actualizarLlamadasCampania($dbConn, $astman, $infoCampania);
        }
    }

    return $bLlamadasAgregadas;
}

// Número mínimo de muestras para poder confiar en predicciones de marcador
define('MIN_MUESTRAS', 10);

/**
 * Procedimiento que actualiza el número de llamadas que están siendo manejadas
 * por los agentes. A partir de MIN_MUESTRAS, se actualizan los valores de 
 * promedio y desviación estándar para implementar el algoritmo predictivo.
 *
 * @param object $dbConn    Conexión PEAR a la base de datos SQLITE
 * @param object $astman    Conexión al Asterisk Manager
 * @param object $infoCampania Información sobre la campaña
 *
 * @return bool VERDADERO si se agregaron llamadas a la campaña
 */
function actualizarLlamadasCampania($dbConn, &$astman, $infoCampania)
{
    $iNumLlamadasColocar = 0;

    // Leer cuántas llamadas (como máximo) se pueden hacer por campaña
    $iNumLlamadasColocar = $infoCampania->max_canales;
    if ($iNumLlamadasColocar <= 0) return FALSE;

    // Averiguar cuantas llamadas se pueden hacer (por predicción), y tomar
    // el menor valor de entre máx campaña y predictivo
    // TODO: usar realmente los valores de promedio y desviación
    $oPredictor = new predictivo($astman);
    $iMaxPredecidos = $oPredictor->predecirNumeroLlamadas($infoCampania->queue, 
        ($infoCampania->num_completadas >= MIN_MUESTRAS));
    if ($iNumLlamadasColocar > $iMaxPredecidos)
        $iNumLlamadasColocar = $iMaxPredecidos;
    if ($iNumLlamadasColocar <= 0) return FALSE;
    
    // Leer tantas llamadas como fueron elegidas. Sólo se leen números con
    // status == NULL
    // TODO: también leer llamadas con status != éxito, respetando reintentos
    // máximos de campañas.
    $sPeticionLlamadas = 'SELECT id_campaign, id, phone FROM calls WHERE id_campaign = ? AND status IS NULL LIMIT 0,?';
    $recordset =& $dbConn->query($sPeticionLlamadas, array($infoCampania->id, $iNumLlamadasColocar));
    if (DB::isError($recordset)) {
        // TODO: al convertir en demonio, cambiar a log
        fprintf(STDERR, "ERR: no se puede leer lista de teléfonos - ".($recordset->getMessage())."\n");
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
        // TODO: si se implementa llamadas con status != éxito, se deben colocar 
        // aquí, para dar prioridad a los números no llamados.
    }

    if (count($listaLlamadas) > 0) {
        $astman->add_event_handler('OriginateResponse', 'OnOriginateResponse');
        // Colocar todas las llamadas elegidas para ser realizadas por el Asterisk.
        foreach ($listaLlamadas as $sKey => $tupla) {
            $resultado = $astman->Originate(
                $infoCampania->trunk."/".$tupla->phone, $infoCampania->queue, $infoCampania->context, 1,
                NULL, NULL, NULL, NULL, NULL, NULL, 
                TRUE, $sKey);
            if ($resultado['Response'] == 'Success') {
                $result = $dbConn->query('UPDATE calls SET status = ? WHERE id_campaign = ? AND id = ?',
                    array($resultado['Response'], $infoCampania->id, $tupla->id));
            } else {
                // TODO: al convertir en demonio, cambiar a log
                print "ERR: no se puede llamar a número - $resultado[Message]\n";
            }
        }
//print "\n\nDEBUG: justo antes de agregar llamadas...".count($astman->app_data['llamadas'])."\n\n\n";
        // Agregar todas las llamadas agregadas a la lista de llamadas pendientes
        // por timbrar, para filtrar según el evento Link y guardar en la 
        // base de datos.
        $temp_appData = $astman->get_app_data();
        $temp_appData['llamadas'] = array_merge($temp_appData['llamadas'], $listaLlamadas);
        $astman->add_app_data($temp_appData);
//print "\n\nDEBUG: justo luego de agregar llamadas...".count($astman->app_data['llamadas'])."\n\n\n";
    
        // Esperar OriginateResponse para todas las llamadas. Para cada una de 
        // ellas, el Uniqueid reportado debe de escribirse a la base de datos.
        while (count($listaLlamadas) > 0) {
            $resultado = $astman->wait_response(TRUE, TRUE);
            if ($resultado['Event'] == 'OriginateResponse' && 
                isset($listaLlamadas[$resultado['ActionID']])) {

                $tupla = $listaLlamadas[$resultado['ActionID']];
                unset($listaLlamadas[$resultado['ActionID']]);
                $result = $dbConn->query(
                    'UPDATE calls SET status = ?, Uniqueid = ? WHERE id_campaign = ? AND id = ?',
                    array($resultado['Response'], $resultado['Uniqueid'], $infoCampania->id, $tupla->id));
                    
                $temp_appData = $astman->get_app_data();
                if (isset($temp_appData['llamadas'][$resultado['ActionID']])) {
                    $temp_appData['llamadas'][$resultado['ActionID']]->Uniqueid = $resultado['Uniqueid'];
                    $temp_appData['llamadas'][$resultado['ActionID']]->Response = $resultado['Response'];
                    $temp_appData['llamadas'][$resultado['ActionID']]->queue = $infoCampania->queue;
                    $astman->add_app_data($temp_appData);
                }
            }
        }
        // Debería existir remove_event_handler() o similar
        unset($astman->event_handlers['OriginateResponse']);
    } else {
        return FALSE;
    }
}

function OnLink($sEvent, $params, $sServer, $iPort)
{
    $sKey = NULL;
    foreach ($params['AppData']['llamadas'] as $key => $tupla) {
        if ($tupla->Uniqueid == $params['Uniqueid1']) $sKey = $key;
        if ($tupla->Uniqueid == $params['Uniqueid2']) $sKey = $key;
    }
    if (!is_null($sKey)) {
        $regs = NULL;
        $sAgentNum = NULL;
        $sChannel = NULL;
        if (ereg('^Agent/([[:digit:]]+)$', $params['Channel1'], $regs)) {
            $sAgentNum = $regs[1];
            $sChannel = $params['Channel1'];
        }
        if (ereg('^Agent/([[:digit:]]+)$', $params['Channel2'], $regs)) {
            $sAgentNum = $regs[1];
            $sChannel = $params['Channel2'];
        }
        if (!is_null($sAgentNum)) {
            // Borrado de la llamada para el agente antiguo. Esto es por 
            // precaución, porque no debería ocurrir en funcionamiento correcto.
            $sBorrado = 'DELETE FROM current_calls WHERE agentnum = ?';
            $result =& $params['AppData']['dbconn']->query($sBorrado, array($sAgentNum));
            if (DB::isError($result)) {
                print "ERR: no se puede purgar agente $sAgentNum - ".$result->getMessage()."\n";
            }
            
            // Inserción de la llamada nueva
            $sInsercionEvent = 'INSERT INTO current_calls (fecha_inicio, Uniqueid, queue, agentnum, id_call, event, Channel) VALUES (?, ?, ?, ?, ?, ?, ?)';
            $result =& $params['AppData']['dbconn']->query(
                $sInsercionEvent,
                array(date('Y-m-d H:i:s'), 
                $params['AppData']['llamadas'][$sKey]->Uniqueid,
                $params['AppData']['llamadas'][$sKey]->queue,
                $sAgentNum,
                $params['AppData']['llamadas'][$sKey]->id,
                $params['Event'],
                $sChannel));
            if (DB::isError($result)) {
                // TODO: al volver demonio, convertir a log
                print "ERR: no se puede insertar llamada actual - ".$result->getMessage()."\n";
            }
        }

//        $params['AppData']['llamadas'][$sKey]
    }
    return false;
}

function OnHangup($sEvent, $params, $sServer, $iPort)
{
    // Lo siguiente sirve porque tanto Unlink como Hangup comparten un Uniqueid
    return OnUnlink($sEvent, $params, $sServer, $iPort);
}

function OnUnlink($sEvent, $params, $sServer, $iPort)
{
    $sKey = NULL;
    foreach ($params['AppData']['llamadas'] as $key => $tupla) {
        if ($tupla->Uniqueid == $params['Uniqueid1']) $sKey = $key;
        if ($tupla->Uniqueid == $params['Uniqueid2']) $sKey = $key;
    }
    if (!is_null($sKey)) {
        // Borrado de la llamada objetivo
        $sBorradoLlamada = 'DELETE FROM current_calls WHERE Uniqueid = ?';
        $result =& $params['AppData']['dbconn']->query($sBorradoLlamada, array($params['AppData']['llamadas'][$sKey]->Uniqueid));
        if (DB::isError($result)) {
            // TODO: al volver demonio, convertir a log
            print "ERR: no se puede purgar llamada - ".$result->getMessage()."\n";
        }        
    }
    return false;
}

function OnOriginateResponse($sEvent, $params, $sServer, $iPort)
{
    // Sólo hay que devolver VERDADERO en esta implementación, sin hacer otra cosa
    return TRUE;
}

function testEvent($sEvent, $params, $sServer, $iPort)
{
    print "En funcion testEvent ($sServer:$iPort)...\n";
    print_r($params);
    print "Saliendo de funcion testEvent...\n";
    return FALSE;
}
?>
