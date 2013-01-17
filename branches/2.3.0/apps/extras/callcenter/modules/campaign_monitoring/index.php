<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.8                                                  |
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
  $Id: default.conf.php,v 1.1.1.1 2007/03/23 00:13:58 elandivar Exp $ */


include_once "libs/paloSantoConfig.class.php";

require_once "modules/agent_console/libs/elastix2.lib.php";
require_once "modules/agent_console/libs/JSON.php";
require_once "modules/agent_console/libs/paloSantoConsola.class.php";

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    global $arrLang;
    global $arrConfig;
  
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloMonitorCampania.class.php";

    load_language_module($module_name);    

    //folder path for custom templates
    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir = (isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir = "$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    // Ember.js requiere jQuery 1.7.2 o superior.
    modificarReferenciasLibreriasJS($smarty);

    $sContenido = '';

    // Procesar los eventos AJAX.
    switch (getParameter('action')) {
    case 'getCampaigns':
        $sContenido = manejarMonitoreo_getCampaigns($module_name, $smarty, $local_templates_dir);
        break;
    case 'getCampaignDetail':
        $sContenido = manejarMonitoreo_getCampaignDetail($module_name, $smarty, $local_templates_dir);
        break;
    case 'checkStatus':
        $sContenido = manejarMonitoreo_checkStatus($module_name, $smarty, $local_templates_dir);
        break;
    default:
        // Página principal con plantilla
        $sContenido = manejarMonitoreo_HTML($module_name, $smarty, $local_templates_dir);
    }
    return $sContenido;
}

function manejarMonitoreo_HTML($module_name, $smarty, $sDirLocalPlantillas)
{
    $debug = "";
    $smarty->assign("MODULE_NAME", $module_name);
    $smarty->assign(array(
        'title'                         =>  _tr('Campaign Monitoring'),
        'icon'                          => '/images/list.png',
        'ETIQUETA_CAMPANIA'             =>  _tr('Campaign'),
        'ETIQUETA_FECHA_INICIO'         =>  _tr('Start date'),
        'ETIQUETA_FECHA_FINAL'          =>  _tr('End date'),
        'ETIQUETA_HORARIO'              =>  _tr('Schedule'),
        'ETIQUETA_COLA'                 =>  _tr('Queue'),
        'ETIQUETA_INTENTOS'             =>  _tr('Retries'),
        'ETIQUETA_TOTAL_LLAMADAS'       =>  _tr('Total calls'),
        'ETIQUETA_LLAMADAS_PENDIENTES'  =>  _tr('Pending calls'),
        'ETIQUETA_LLAMADAS_FALLIDAS'    =>  _tr('Failed calls'),
        'ETIQUETA_LLAMADAS_CORTAS'      =>  _tr('Short calls'),
        'ETIQUETA_LLAMADAS_EXITO'       =>  _tr('Connected calls'),
        'ETIQUETA_LLAMADAS_MARCANDO'    =>  _tr('Placing calls'),
        'ETIQUETA_LLAMADAS_COLA'        =>  _tr('Queued calls'),
        'ETIQUETA_LLAMADAS_TIMBRANDO'   =>  _tr('Ringing calls'),
        'ETIQUETA_LLAMADAS_ABANDONADAS' =>  _tr('Abandoned calls'),
        'ETIQUETA_LLAMADAS_NOCONTESTA'  =>  _tr('Unanswered calls'),
        'ETIQUETA_LLAMADAS_TERMINADAS'  =>  _tr('Finished calls'),
        'ETIQUETA_LLAMADAS_SINRASTRO'   =>  _tr('Lost track'),
        'ETIQUETA_AGENTES'              =>  _tr('Agents'),
        'ETIQUETA_NUMERO_TELEFONO'      =>  _tr('Phone Number'),
        'ETIQUETA_TRONCAL'              =>  _tr('Trunk'),
        'ETIQUETA_ESTADO'               =>  _tr('Status'),
        'ETIQUETA_DESDE'                =>  _tr('Since'),
        'ETIQUETA_AGENTE'               =>  _tr('Agent'),
        'ETIQUETA_REGISTRO'             =>  _tr('Campaign log'),
    ));
    $smarty->assign('INFO_DEBUG', $debug);
    return $smarty->fetch("file:$sDirLocalPlantillas/informacion_campania.tpl");
}

function manejarMonitoreo_getCampaigns($module_name, $smarty, $sDirLocalPlantillas)
{
    $respuesta = array(
        'status'    =>  'success',
        'message'   =>  '(no message)',
    );
	$oPaloConsola = new PaloSantoConsola();
    $listaCampanias = $oPaloConsola->leerListaCampanias();
    if (!is_array($listaCampanias)) {
    	$respuesta['status'] = 'error';
        $respuesta['message'] = $oPaloConsola->errMsg;
    } 
    $listaColas = $oPaloConsola->leerListaColasEntrantes();
    if (!is_array($listaColas)) {
        $respuesta['status'] = 'error';
        $respuesta['message'] = $oPaloConsola->errMsg;
    } 
    if (is_array($listaCampanias) && is_array($listaColas)) {
        foreach ($listaColas as $q) {
        	$listaCampanias[] = array(
                'id'        =>  $q['queue'],
                'type'      =>  'incomingqueue',
                'name'      =>  $q['queue'],
                'status'    =>  $q['status'],
            );
        }
        
        
        /* Para la visualización se requiere que primero se muestren las campañas 
         * activas, con el ID mayor primero (probablemente la campaña más reciente)
         * seguido de las campañas inactivas, y luego las terminadas */
        if (!function_exists('manejarMonitoreo_getCampaigns_sort')) {
            function manejarMonitoreo_getCampaigns_sort($a, $b)
            {
            	if ($a['status'] != $b['status'])
                    return strcmp($a['status'], $b['status']);
                return $b['id'] - $a['id'];
            }
        }
        usort($listaCampanias, 'manejarMonitoreo_getCampaigns_sort');
        $respuesta['campaigns'] = array();
        foreach ($listaCampanias as $c) { 
            $respuesta['campaigns'][] = array(
                'id_campaign'   => $c['id'],
                'desc_campaign' => '('._tr($c['type']).') '.$c['name'],
                'type'          =>  $c['type'],
                'status'        =>  $c['status'],
            );
        }
    }
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

function manejarMonitoreo_getCampaignDetail($module_name, $smarty, $sDirLocalPlantillas)
{
    $respuesta = array(
        'status'    =>  'success',
        'message'   =>  '(no message)',
    );
    $estadoCliente = array();
    
    $sTipoCampania = getParameter('campaigntype');
    $sIdCampania = getParameter('campaignid');
    if (is_null($sTipoCampania) || !in_array($sTipoCampania, array('incoming', 'outgoing', 'incomingqueue'))) {
        $respuesta['status'] = 'error';
        $respuesta['message'] = _tr('Invalid campaign type');
    } elseif (is_null($sIdCampania) || !ctype_digit($sIdCampania)) {
        $respuesta['status'] = 'error';
        $respuesta['message'] = _tr('Invalid campaign ID');
    } else {
        $oPaloConsola = new PaloSantoConsola();
        if ($respuesta['status'] == 'success') {
        	$infoCampania = $oPaloConsola->leerInfoCampania($sTipoCampania, $sIdCampania);
            if (!is_array($infoCampania)) {
            	$respuesta['status'] = 'error';
                $respuesta['message'] = $oPaloConsola->errMsg;
            }
        }
        if ($respuesta['status'] == 'success') {
            $estadoCampania = $oPaloConsola->leerEstadoCampania($sTipoCampania, $sIdCampania);
            if (!is_array($estadoCampania)) {
                $respuesta['status'] = 'error';
                $respuesta['message'] = $oPaloConsola->errMsg;
            }
        }
        if ($respuesta['status'] == 'success') {
        	$logCampania = $oPaloConsola->leerLogCampania($sTipoCampania, $sIdCampania);
            if (!is_array($logCampania)) {
                $respuesta['status'] = 'error';
                $respuesta['message'] = $oPaloConsola->errMsg;
            }
        }
    }
    if ($respuesta['status'] == 'success') {
    	$respuesta['campaigndata'] = array(
            'startdate'                 =>
                is_null($infoCampania['startdate']) 
                ? _tr('N/A') : $infoCampania['startdate'],
            'enddate'                   =>
                is_null($infoCampania['enddate']) 
                ? _tr('N/A') : $infoCampania['enddate'],
            'working_time_starttime'    =>
                is_null($infoCampania['working_time_starttime']) 
                ? _tr('N/A') : $infoCampania['working_time_starttime'],
            'working_time_endtime'      =>
                is_null($infoCampania['working_time_endtime']) 
                ? _tr('N/A') : $infoCampania['working_time_endtime'],
            'queue'                     =>  $infoCampania['queue'],
            'retries'                   => 
                is_null($infoCampania['retries'])
                ? _tr('N/A') : (int)$infoCampania['retries'],
        );
        
        // Traducción de estado de las llamadas no conectadas
        $estadoCampaniaLlamadas = array();
        foreach ($estadoCampania['activecalls'] as $activecall) {
            $estadoCampaniaLlamadas[] = formatoLlamadaNoConectada($activecall);
        }
        
        // Traducción de estado de los agentes
        $estadoCampaniaAgentes = array();
        foreach ($estadoCampania['agents'] as $agent) {
            $estadoCampaniaAgentes[] = formatoAgente($agent);
        }
        
        // Traducción de log de la campaña
        $logFinalCampania = array();
        foreach ($logCampania as $entradaLog) {
        	$logFinalCampania[] = formatoLogCampania($entradaLog);
        }
        
        // Se arma la respuesta JSON y el estado final del cliente
        $respuesta = array_merge($respuesta, crearRespuestaVacia());
        $respuesta['statuscount']['update'] = $estadoCampania['statuscount'];
        $respuesta['activecalls']['add'] = $estadoCampaniaLlamadas;
        $respuesta['agents']['add'] = $estadoCampaniaAgentes;
        $respuesta['log'] = $logFinalCampania;
        $estadoCliente = array(
            'campaignid'    =>  $sIdCampania,
            'campaigntype'  =>  $sTipoCampania,
            'statuscount'   =>  $estadoCampania['statuscount'],
            'activecalls'   =>  $estadoCampania['activecalls'],
            'agents'        =>  $estadoCampania['agents'],
        );
        
        $respuesta['estadoClienteHash'] = generarEstadoHash($module_name, $estadoCliente);
    }
    
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($respuesta);
}

function manejarMonitoreo_checkStatus($module_name, $smarty, $sDirLocalPlantillas)
{
    $respuesta = array();
    
    ignore_user_abort(true);
    set_time_limit(0);

    // Estado del lado del cliente
    $estadoHash = getParameter('clientstatehash');
    if (!is_null($estadoHash)) {
        $estadoCliente = isset($_SESSION[$module_name]['estadoCliente']) 
            ? $_SESSION[$module_name]['estadoCliente'] 
            : array();        
    } else {
        $estadoCliente = getParameter('clientstate');
        if (!is_array($estadoCliente)) return;
    }

    // Modo a funcionar: Long-Polling, o Server-sent Events
    $sModoEventos = getParameter('serverevents');
    $bSSE = (!is_null($sModoEventos) && $sModoEventos); 
    if ($bSSE) {
        Header('Content-Type: text/event-stream');
        printflush("retry: 1\n");
    } else {
        Header('Content-Type: application/json');
    }
    
    // Verificar hash correcto
    if (!is_null($estadoHash) && $estadoHash != $_SESSION[$module_name]['estadoClienteHash']) {
        $respuesta['estadoClienteHash'] = 'mismatch';
        jsonflush($bSSE, $respuesta);
        return;
    }

    $oPaloConsola = new PaloSantoConsola();
	
    // Estado del lado del servidor
    $estadoCampania = $oPaloConsola->leerEstadoCampania($estadoCliente['campaigntype'], $estadoCliente['campaignid']);
    if (!is_array($estadoCampania)) {
        $respuesta['error'] = $oPaloConsola->errMsg;
        jsonflush($bSSE, $respuesta);
        $oPaloConsola->desconectarTodo();
        return;
    }

    // Acumular inmediatamente las filas que son distintas en estado
    $respuesta = crearRespuestaVacia();
    
    // Cuenta de estados
    foreach (array_keys($estadoCliente['statuscount']) as $k) {
        // Actualización de valores de contadores
    	if ($estadoCliente['statuscount'][$k] != $estadoCampania['statuscount'][$k]) {
    		$respuesta['statuscount']['update'][$k] = $estadoCampania['statuscount'][$k];
            $estadoCliente['statuscount'][$k] = $estadoCampania['statuscount'][$k];
    	}
    }
    
    // Estado de llamadas no conectadas
    foreach (array_keys($estadoCliente['activecalls']) as $k) {
    	// Llamadas que cambiaron de estado o ya no están sin agente
        if (!isset($estadoCampania['activecalls'][$k])) {
        	// Llamada ya no está esperando agente
            $respuesta['activecalls']['remove'][] = array('callid' => $estadoCliente['activecalls'][$k]['callid']);
            unset($estadoCliente['activecalls'][$k]);
        } elseif ($estadoCliente['activecalls'][$k] != $estadoCampania['activecalls'][$k]) {
        	// Llamada ha cambiado de estado
            $respuesta['activecalls']['update'][] = formatoLlamadaNoConectada($estadoCampania['activecalls'][$k]);
            $estadoCliente['activecalls'][$k] = $estadoCampania['activecalls'][$k];
        }
    }
    foreach (array_keys($estadoCampania['activecalls']) as $k) {
    	// Llamadas nuevas
        if (!isset($estadoCliente['activecalls'][$k])) {
            $respuesta['activecalls']['add'][] = formatoLlamadaNoConectada($estadoCampania['activecalls'][$k]);
            $estadoCliente['activecalls'][$k] = $estadoCampania['activecalls'][$k];
        }
    }
    
    // Estado de agentes de campaña
    foreach (array_keys($estadoCliente['agents']) as $k) {
    	// Agentes que cambiaron de estado o desaparecieron (???)
        if (!isset($estadoCampania['agents'][$k])) {
        	// Agente ya no aparece (???)
            $respuesta['agents']['remove'][] = array('agent' => $estadoCliente['agents'][$k]['agentchannel']);
            unset($estadoCliente['agents'][$k]);
        } elseif ($estadoCliente['agents'][$k] != $estadoCampania['agents'][$k]) {
        	// Agente ha cambiado de estado
            $respuesta['agents']['update'][] = formatoAgente($estadoCampania['agents'][$k]);
            $estadoCliente['agents'][$k] = $estadoCampania['agents'][$k];
        }
    }
    foreach (array_keys($estadoCampania['agents']) as $k) {
        // Agentes nuevos (???)
        if (!isset($estadoCliente['agents'][$k])) {
            $respuesta['agents']['add'][] = formatoAgente($estadoCampania['agents'][$k]);
            $estadoCliente['agents'][$k] = $estadoCampania['agents'][$k];
        }
    }

    unset($estadoCampania);

    $oPaloConsola->escucharProgresoLlamada(TRUE);
    $iTimeoutPoll = PaloSantoConsola::recomendarIntervaloEsperaAjax();
    do {
        $oPaloConsola->desconectarEspera();
        
        // Se inicia espera larga con el navegador...
        session_commit();
        $iTimestampInicio = time();
        
        while (connection_status() == CONNECTION_NORMAL && esRespuestaVacia($respuesta) 
            && time() - $iTimestampInicio <  $iTimeoutPoll) {

            $listaEventos = $oPaloConsola->esperarEventoSesionActiva();
            if (is_null($listaEventos)) {
                $respuesta['error'] = $oPaloConsola->errMsg;
                jsonflush($bSSE, $respuesta);
                $oPaloConsola->desconectarTodo();
                return;
            }
            
            $iTimestampActual = time();
            foreach ($listaEventos as $evento) {
                $sCanalAgente = isset($evento['agent_number']) ? $evento['agent_number'] : NULL;
file_put_contents('/tmp/debug-campaignmonitoring.txt', print_r($evento, 1), FILE_APPEND);
                switch ($evento['event']) {
                case 'agentloggedin':
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                    	/* Se ha logoneado agente que atiende a esta campaña.
                         * ATENCIÓN: sólo se setean suficientes campos para la
                         * visualización. Otros campos quedan con sus valores
                         * antiguos, si tenían */
                        $estadoCliente['agents'][$sCanalAgente]['status'] = 'online';
                        $estadoCliente['agents'][$sCanalAgente]['callnumber'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['pausestart'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['linkstart'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['trunk'] = NULL;
                        
                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }
                    break;
                case 'agentloggedout':
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        /* Se ha deslogoneado agente que atiende a esta campaña.
                         * ATENCIÓN: sólo se setean suficientes campos para la
                         * visualización. Otros campos quedan con sus valores
                         * antiguos, si tenían */
                        $estadoCliente['agents'][$sCanalAgente]['status'] = 'offline';
                        $estadoCliente['agents'][$sCanalAgente]['callnumber'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['pausestart'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['linkstart'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['trunk'] = NULL;
                        
                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }
                    break;
                case 'callprogress':
                    $bProcesar = ($estadoCliente['campaigntype'] == 'incomingqueue')
                        ? ( $estadoCliente['campaignid'] == $evento['queue'] &&
                            is_null($evento['campaign_id']))
                        : ( $estadoCliente['campaignid'] == $evento['campaign_id'] && 
                            $estadoCliente['campaigntype'] == $evento['call_type']);
                    if ($bProcesar) {
                    	// Llamada corresponde a cola monitoreada
                        $callid = $evento['call_id'];

                        if (in_array($evento['new_status'], array('Failure', 'Abandoned', 'NoAnswer'))) {
                            if (isset($estadoCliente['activecalls'][$callid])) {
                                restarContadorLlamada($estadoCliente['activecalls'][$callid]['callstatus'], $estadoCliente, $respuesta);
                                agregarContadorLlamada($evento['new_status'], $estadoCliente, $respuesta);
                                
                                // Quitar de las llamadas que esperan un agente
                                $respuesta['activecalls']['remove'][] = array('callid' => $callid);
                                unset($estadoCliente['activecalls'][$callid]);
                            }
                        } elseif (in_array($evento['new_status'], array('OnHold', 'OffHold'))) {
                        	// Se supone que una llamada en hold ya fue asignada a un agente
                        } else {
                            if (isset($estadoCliente['activecalls'][$callid])) {
                                restarContadorLlamada($estadoCliente['activecalls'][$callid]['callstatus'], $estadoCliente, $respuesta);
                            	
                                $estadoCliente['activecalls'][$callid]['callstatus'] = $evento['new_status'];
                                $estadoCliente['activecalls'][$callid]['trunk'] = $evento['trunk'];
                                if ($evento['new_status'] == 'OnQueue')
                                    $estadoCliente['activecalls'][$callid]['queuestart'] = $evento['datetime_entry'];                                
                                $respuesta['activecalls']['update'][] =
                                    formatoLlamadaNoConectada($estadoCliente['activecalls'][$callid]);
                            } else {
                            	// Valores sólo para satisfacer formato
                                $estadoCliente['activecalls'][$callid] = array(
                                    'callid'        =>  $callid,
                                    'callnumber'    =>  $evento['phone'],
                                    'callstatus'    =>  $evento['new_status'],
                                    'dialstart'     =>  $evento['datetime_entry'],
                                    'dialend'       =>  NULL,
                                    'queuestart'    =>  $evento['datetime_entry'],
                                    'trunk'         =>  $evento['trunk'],
                                );
                                $respuesta['activecalls']['add'][] =
                                    formatoLlamadaNoConectada($estadoCliente['activecalls'][$callid]);
                            }

                            agregarContadorLlamada($evento['new_status'], $estadoCliente, $respuesta);
                        }

                        $respuesta['log'][] = formatoLogCampania(array(
                            'new_status'        =>  $evento['new_status'],
                            'datetime_entry'    =>  $evento['datetime_entry'],
                            'campaign_type'     =>  $evento['call_type'],
                            'campaign_id'       =>  $evento['campaign_id'],
                            'call_id'           =>  $evento['call_id'],
                            'retry'             =>  $evento['retry'],
                            'uniqueid'          =>  $evento['uniqueid'],
                            'trunk'             =>  $evento['trunk'],
                            'phone'             =>  $evento['phone'],
                            'queue'             =>  $evento['queue'],
                            'agentchannel'      =>  $sCanalAgente,
                            'duration'          =>  NULL,
                        ));
                    }
                    break;
                case 'pausestart':
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        // Agente ha entrado en pausa
                        $estadoCliente['agents'][$sCanalAgente]['status'] = 'paused';
                        $estadoCliente['agents'][$sCanalAgente]['pausestart'] = $evento['pause_start'];
                        
                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }
                    break;
                case 'pauseend':
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        // Agente ha salido de pausa
                        $estadoCliente['agents'][$sCanalAgente]['status'] =
                            is_null($estadoCliente['agents'][$sCanalAgente]['linkstart']) ? 'online' : 'oncall';
                        $estadoCliente['agents'][$sCanalAgente]['pausestart'] = NULL;
                        
                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }
                    break;
                case 'agentlinked':
                    // Si la llamada estaba en lista activa, quitarla
                    $callid = $evento['call_id'];
                    if (isset($estadoCliente['activecalls'][$callid])) {
                        restarContadorLlamada($estadoCliente['activecalls'][$callid]['callstatus'], $estadoCliente, $respuesta);
                        $respuesta['activecalls']['remove'][] = array('callid' => $estadoCliente['activecalls'][$callid]['callid']);
                        unset($estadoCliente['activecalls'][$callid]);
                    }
                
                    // Si el agente es uno de los de la campaña, modificar
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        $estadoCliente['agents'][$sCanalAgente]['status'] = is_null($estadoCliente['agents'][$sCanalAgente]['pausestart']) ? 'oncall' : 'paused';
                        $estadoCliente['agents'][$sCanalAgente]['callnumber'] = $evento['phone'];
                        $estadoCliente['agents'][$sCanalAgente]['linkstart'] = $evento['datetime_linkstart'];
                        $estadoCliente['agents'][$sCanalAgente]['trunk'] = $evento['trunk'];

                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                        $respuesta['log'][] = formatoLogCampania(array(
                            'new_status'        =>  'Success',
                            'datetime_entry'    =>  $evento['datetime_linkstart'],
                            'campaign_type'     =>  $evento['call_type'],
                            'campaign_id'       =>  $evento['campaign_id'],
                            'call_id'           =>  $evento['call_id'],
                            'retry'             =>  $evento['retries'],
                            'uniqueid'          =>  $evento['uniqueid'],
                            'trunk'             =>  $evento['trunk'],
                            'phone'             =>  $evento['phone'],
                            'queue'             =>  $evento['queue'],
                            'agentchannel'      =>  $sCanalAgente,
                            'duration'          =>  NULL,
                        ));

                        agregarContadorLlamada('Success', $estadoCliente, $respuesta);
                    }
                    break;
                case 'agentunlinked':
                    // Si el agente es uno de los de la campaña, modificar
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        /* Es posible que se reciba un evento agentunlinked luego
                         * del evento agentloggedout si el agente se desconecta con
                         * una llamada activa. */ 
                        if ($estadoCliente['agents'][$sCanalAgente]['status'] != 'offline') {
                            $estadoCliente['agents'][$sCanalAgente]['status'] =
                                is_null($estadoCliente['agents'][$sCanalAgente]['pausestart']) ? 'online' : 'paused';
                        }
                        $estadoCliente['agents'][$sCanalAgente]['callnumber'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['linkstart'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['trunk'] = NULL;
                        
                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                        $respuesta['log'][] = formatoLogCampania(array(
                            'new_status'        =>  $evento['shortcall'] ? 'ShortCall' : 'Hangup',
                            'datetime_entry'    =>  $evento['datetime_linkend'],
                            'campaign_type'     =>  $evento['call_type'],
                            'campaign_id'       =>  $evento['campaign_id'],
                            'call_id'           =>  $evento['call_id'],
                            'retry'             =>  NULL,
                            'uniqueid'          =>  NULL,
                            'trunk'             =>  NULL,
                            'phone'             =>  $evento['phone'],
                            'queue'             =>  NULL,
                            'agentchannel'      =>  $sCanalAgente,
                            'duration'          =>  $evento['duration'],
                        ));
                        
                        if ($evento['call_type'] == 'incoming') {
                        	restarContadorLlamada('Success', $estadoCliente, $respuesta);
                            agregarContadorLlamada('Finished', $estadoCliente, $respuesta);
                            agregarContadorLlamada('Total', $estadoCliente, $respuesta);
                        } else {
                        	if ($evento['shortcall']) {
                        		restarContadorLlamada('Success', $estadoCliente, $respuesta);
                                agregarContadorLlamada('ShortCall', $estadoCliente, $respuesta);
                        	}
                        }
                    }
                    break;
                }
            }
            
            
        }
        if (!esRespuestaVacia($respuesta)) {
            @session_start();
            $estadoHash = generarEstadoHash($module_name, $estadoCliente);
            $respuesta['estadoClienteHash'] = $estadoHash;
            session_commit();
        }
        jsonflush($bSSE, $respuesta);
        
        $respuesta = crearRespuestaVacia();

    } while ($bSSE && connection_status() == CONNECTION_NORMAL);
    $oPaloConsola->desconectarTodo();
}

function crearRespuestaVacia()
{
    return array(
        'statuscount'   =>  array('update' => array()),
        'activecalls'   =>  array('add' => array(), 'update' => array(), 'remove' => array()),
        'agents'        =>  array('add' => array(), 'update' => array(), 'remove' => array()),
        'log'           =>  array(),
    );
}

function esRespuestaVacia(&$respuesta)
{
	return count($respuesta['statuscount']['update']) == 0
        && count($respuesta['activecalls']['add']) == 0
        && count($respuesta['activecalls']['update']) == 0
        && count($respuesta['activecalls']['remove']) == 0
        && count($respuesta['agents']['add']) == 0
        && count($respuesta['agents']['update']) == 0
        && count($respuesta['agents']['remove']) == 0
        && count($respuesta['log']) == 0;
}

// Restar del contador Placing/Dialing/Ringing/OnQueue según corresponda
function restarContadorLlamada($old_status, &$estadoCliente, &$respuesta)
{
    $k = strtolower($old_status);
    if ($k == 'dialing') $k = 'placing';
    if (isset($estadoCliente['statuscount'][$k]) && $estadoCliente['statuscount'][$k] > 0) {
        $estadoCliente['statuscount'][$k]--;
        $respuesta['statuscount']['update'][$k] = $estadoCliente['statuscount'][$k];
    }
}

// Agregar al contador correspondiente de progreso
function agregarContadorLlamada($new_status, &$estadoCliente, &$respuesta)
{
    $k = strtolower($new_status);
    if ($k == 'dialing') $k = 'placing';
    if (isset($estadoCliente['statuscount'][$k])) {
        $estadoCliente['statuscount'][$k]++;
        $respuesta['statuscount']['update'][$k] = $estadoCliente['statuscount'][$k];
    }
}

function formatoLlamadaNoConectada($activecall)
{
    $sFechaHoy = date('Y-m-d');
    $sDesde = (!is_null($activecall['queuestart'])) 
        ? $activecall['queuestart'] : $activecall['dialstart'];
    if (strpos($sDesde, $sFechaHoy) === 0)
        $sDesde = substr($sDesde, strlen($sFechaHoy) + 1);
    $sEstado = ($activecall['callstatus'] == 'placing' && !is_null($activecall['trunk'])) 
        ? _tr('dialing') : _tr($activecall['callstatus']);
    return array(
        'callid'        =>  $activecall['callid'],
        'callnumber'    =>  $activecall['callnumber'],
        'trunk'         =>  $activecall['trunk'],
        'callstatus'    =>  $sEstado,
        'desde'         =>  $sDesde,
    );
}

function formatoAgente($agent)
{
    $sFechaHoy = date('Y-m-d');
    $sDesde = '-';
    if ($agent['status'] == 'paused')
        $sDesde = $agent['pausestart'];
    elseif ($agent['status'] == 'oncall')
        $sDesde = $agent['linkstart'];
    if (strpos($sDesde, $sFechaHoy) === 0)
        $sDesde = substr($sDesde, strlen($sFechaHoy) + 1);
    return array(
        'agent'         =>  $agent['agentchannel'],
        'status'        =>  _tr($agent['status']),
        'callnumber'    =>  is_null($agent['callnumber']) ? '-' : $agent['callnumber'],
        'trunk'         =>  is_null($agent['trunk']) ? '-' : $agent['trunk'],
        'desde'         =>  $sDesde,
    );
}

function formatoLogCampania($entradaLog)
{
    $listaMsg = array(
        'Placing'   =>  _tr('LOG_FMT_PLACING'),
        'Dialing'   =>  _tr('LOG_FMT_DIALING'),
        'Ringing'   =>  _tr('LOG_FMT_RINGING'),
        'OnQueue'   =>  _tr('LOG_FMT_ONQUEUE'),
        'Success'   =>  _tr('LOG_FMT_SUCCESS'),
        'Hangup'    =>  _tr('LOG_FMT_HANGUP'),
        'OnHold'    =>  _tr('LOG_FMT_ONHOLD'),
        'OffHold'   =>  _tr('LOG_FMT_OFFHOLD'),
        'Failure'   =>  _tr('LOG_FMT_FAILURE'),
        'Abandoned' =>  _tr('LOG_FMT_ABANDONED'),
        'ShortCall' =>  _tr('LOG_FMT_SHORTCALL'),
        'NoAnswer'  =>  _tr('LOG_FMT_NOANSWER'),
    );
    $sMensaje = $listaMsg[$entradaLog['new_status']];
    foreach ($entradaLog as $k => $v) {
        if ($k == 'duration') $v = sprintf('%02d:%02d:%02d', 
                ($v - ($v % 3600)) / 3600, 
                (($v - ($v % 60)) / 60) % 60, 
                $v % 60);
        $sMensaje = str_replace('{'.$k.'}', $v, $sMensaje);
    }

    return array(
        'timestamp' =>  $entradaLog['datetime_entry'],
        'mensaje'   =>  $sMensaje,
    );
}

function modificarReferenciasLibreriasJS($smarty)
{
    $listaLibsJS_framework = explode("\n", $smarty->get_template_vars('HEADER_LIBS_JQUERY'));
    $listaLibsJS_modulo = explode("\n", $smarty->get_template_vars('HEADER_MODULES'));

    /* Se busca la referencia a jQuery (se asume que sólo hay una biblioteca que
     * empieza con "jquery-") y se la quita. Las referencias a Ember.js y 
     * Handlebars se reordenan para que Handlebars aparezca antes que Ember.js 
     */ 
    $sEmberRef = $sHandleBarsRef = $sjQueryRef = NULL;
    foreach (array_keys($listaLibsJS_modulo) as $k) {
    	if (strpos($listaLibsJS_modulo[$k], 'themes/default/js/jquery-') !== FALSE) {
    		$sjQueryRef = $listaLibsJS_modulo[$k];
            unset($listaLibsJS_modulo[$k]);
    	} elseif (strpos($listaLibsJS_modulo[$k], 'themes/default/js/handlebars-') !== FALSE) {
            $sHandleBarsRef = $listaLibsJS_modulo[$k];
            unset($listaLibsJS_modulo[$k]);
        } elseif (strpos($listaLibsJS_modulo[$k], 'themes/default/js/ember-') !== FALSE) {
            $sEmberRef = $listaLibsJS_modulo[$k];
            unset($listaLibsJS_modulo[$k]);
        }
    }
    array_unshift($listaLibsJS_modulo, $sEmberRef);
    array_unshift($listaLibsJS_modulo, $sHandleBarsRef);
    $smarty->assign('HEADER_MODULES', implode("\n", $listaLibsJS_modulo));

    /* Se busca la referencia original al jQuery del framework, y se reemplaza
     * si es más vieja que el jQuery del módulo */
    $sRegexp = '/jquery-(\d.+?)(\.min)?\.js/'; $regs = NULL;
    preg_match($sRegexp, $sjQueryRef, $regs);
    $sVersionModulo = $regs[1];
    $sVersionFramework = NULL;
    foreach (array_keys($listaLibsJS_framework) as $k) {
    	if (preg_match($sRegexp, $listaLibsJS_framework[$k], $regs)) {
    		$sVersionFramework = $regs[1];
            
            // Se asume que la versión sólo consiste de números y puntos
            $verFramework = explode('.', $sVersionFramework);
            $verModulo = explode('.', $sVersionModulo);
            while (count($verFramework) < count($verModulo)) $verFramework[] = "0";
            while (count($verFramework) > count($verModulo)) $verModulo[] = "0";
            if ($verModulo > $verFramework) $listaLibsJS_framework[$k] = $sjQueryRef;
    	}
    }
    $smarty->assign('HEADER_LIBS_JQUERY', implode("\n", $listaLibsJS_framework));
}

function jsonflush($bSSE, $respuesta)
{
    $json = new Services_JSON();
    $r = $json->encode($respuesta);
    if ($bSSE)
        printflush("data: $r\n\n");
    else printflush($r);
}

function printflush($s)
{
    print $s;
    ob_flush();
    flush();
}

function generarEstadoHash($module_name, $estadoCliente)
{
    $estadoHash = md5(serialize($estadoCliente));
    $_SESSION[$module_name]['estadoCliente'] = $estadoCliente;
    $_SESSION[$module_name]['estadoClienteHash'] = $estadoHash;

    return $estadoHash;
}

?>