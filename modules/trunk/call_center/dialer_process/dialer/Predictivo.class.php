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
  $Id: Predictivo.class.php,v 1.2 2008/09/08 18:29:36 alex Exp $ */

class Predictivo
{
    private $_astConn;  // Conexión al Asterisk

    private $_estadisticasCola = NULL;
    
    function Predictivo(&$astman)
    {
        if (!($astman instanceof AGI_AsteriskManager)) {
        	throw new Exception('Not a subclass of AGI_AsteriskManager!');
        }
        $this->_estadisticasCola = array();
        $this->_astConn = $astman;
    }

    /**
     * Procedimiento para recuperar las estadísticas y parámetros de predicción
     * para la cola.
     * 
     * @param string    $sNombreCola    Cola sobre la cual hacer la predicción
     *
     * @return mixed    NULL si la cola no se conoce, o parámetros
     */
    function getEstadisticasCola($sNombreCola)
    {
    	return isset($this->_estadisticasCola[$sNombreCola]) ? $this->_estadisticasCola[$sNombreCola] : NULL;
    }

    private function _iniciarParamCola($sNombreCola)
    {
        $this->_estadisticasCola[$sNombreCola] = array(
            'TIEMPO_CONTESTAR'  =>  8,          // Tiempo que tarda abonado en contestar (segundos)
            'PROBABILIDAD_ATENCION' =>  0.97,   // Probabilidad de que usuario encolado sea atendido
            'PROMEDIO_DURACION'     =>  75.0,   // Promedio de duración de llamada callcenter (segundos)
            'DESVIACION_DURACION'   =>  17.0,   // Desviación estándar de llamada callcenter (segundos)
        );
    } 

    function setTiempoContestar($sNombreCola, $iTiempoContestar)
    {
    	if (is_numeric($iTiempoContestar) && $iTiempoContestar >= 0) {
            if (!isset($this->_estadisticasCola[$sNombreCola])) {
        		$this->_iniciarParamCola($sNombreCola);            
            }
            $this->_estadisticasCola[$sNombreCola]['TIEMPO_CONTESTAR'] = $iTiempoContestar;
        }
    }

    function setProbabilidadAtencion($sNombreCola, $iProbAtencion)
    {
        if (is_numeric($iProbAtencion) && $iProbAtencion >= 0) {
            if (!isset($this->_estadisticasCola[$sNombreCola])) {
                $this->_iniciarParamCola($sNombreCola);            
            }
            $this->_estadisticasCola[$sNombreCola]['PROBABILIDAD_ATENCION'] = $iProbAtencion;
        }
    }

    function setPromedioDuracion($sNombreCola, $iPromedioDuracion)
    {
        if (is_numeric($iPromedioDuracion) && $iPromedioDuracion >= 0) {
            if (!isset($this->_estadisticasCola[$sNombreCola])) {
                $this->_iniciarParamCola($sNombreCola);            
            }
            $this->_estadisticasCola[$sNombreCola]['PROMEDIO_DURACION'] = $iPromedioDuracion;
        }
    }

    function setDesviacionDuracion($sNombreCola, $iDesviacionDuracion)
    {
        if (is_numeric($iDesviacionDuracion) && $iDesviacionDuracion > 0) {
            if (!isset($this->_estadisticasCola[$sNombreCola])) {
                $this->_iniciarParamCola($sNombreCola);            
            }
            $this->_estadisticasCola[$sNombreCola]['DESVIACION_DURACION'] = $iDesviacionDuracion;
        }
    }

    /**
     * Procedimiento para calcular cuántas llamadas nuevas deben colocarse
     * según el estado actual de las llamadas.
     * 
     * @param string    $sNombreCola    Cola sobre la cual hacer la predicción
     * @param boolean   $bPredecir      Si VERDADERO (por omisión), usar algoritmo predictivo
     *                                  Si FALSO, sólo devuelve número de agentes ociosos
     * 
     * @return mixed    FALSE en caso de error, o número de llamadas a colocar
     */
    function predecirNumeroLlamadas($sNombreCola, $bPredecir = TRUE)
    {
    	if (!isset($this->_estadisticasCola[$sNombreCola])) {
    		// Inventarse parámetros. Lo correcto es que la aplicación realice
            // mediciones y especifique los verdaderos parámetros
            $this->_iniciarParamCola($sNombreCola);
    	}
        
        $estadoCola = $this->leerEstadoCola($sNombreCola);
        if (!is_array($estadoCola)) return FALSE;
        
        // Obtener número de ociosos más número de llamadas a punto de terminar
        $iNumLlamadasColocar = 0;
        foreach ($estadoCola['members'] as $infoAgente) {
        	// Ociosos
            if ($infoAgente['status'] == 'canBeCalled') $iNumLlamadasColocar++;
            
            // Llamadas a punto de terminar. Puede ocurrir que no se pueda alcanzar a
            // identificar el tiempo de habla de un agente, así que se verifica aquí
            // que se tenga un tiempo válido.
            if ($infoAgente['status'] == 'inUse' && $bPredecir && 
                !is_null($infoAgente['talkTime'])) {
            	$iTiempoTotal = 
                    $this->_estadisticasCola[$sNombreCola]['TIEMPO_CONTESTAR'] + 
                    $infoAgente['talkTime'];
/*
                $iProbabilidad = $this->_probabilidadNormalAcumulada(
                    $iTiempoTotal,
                    $this->_estadisticasCola[$sNombreCola]['PROMEDIO_DURACION'],
                    $this->_estadisticasCola[$sNombreCola]['DESVIACION_DURACION']);
*/
                // Probabilidad de que 1 llamada haya terminado al cabo de $iTiempoTotal s.
                $iProbabilidad = $this->_probabilidadErlangAcumulada(
                    $iTiempoTotal,
                    1,
                    1 / $this->_estadisticasCola[$sNombreCola]['PROMEDIO_DURACION']);                    
                if ($iProbabilidad >= $this->_estadisticasCola[$sNombreCola]['PROBABILIDAD_ATENCION']) {
                	$iNumLlamadasColocar++;
                }
            }
        }
        
        // Restar del número de llamadas a colocar, el número de llamadas encoladas
        if ($iNumLlamadasColocar >= count($estadoCola['callers'])) {
        	$iNumLlamadasColocar -= count($estadoCola['callers']);
        } else {
        	$iNumLlamadasColocar = 0;
        }

        return $iNumLlamadasColocar;
    }

    /**
     * Procedimiento para interrogar al Asterisk sobre el estado de los agentes 
     * logoneados en una cola de código $sNombreCola. Con respecto a los agentes,
     * la intención final es la de averiguar desde cuándo han estado hablando, o
     * si están ociosos.
     * 
     * @param string    $sNombreCola    Código de la cola a interrogar
     * 
     * @result mixed    NULL en caso de error, o un arreglo en el sig. formato:
     * array(
     *      members =>  array(
     *          <CODIGO_AGENTE> =>  array(
     *              sourceline  =>  <linea parseada para agente>
     *              attributes  =>  array('dynamic', 'busy', ...),
     *              status      =>  {canBeCalled|inUse|unAvailable}
     *              talkTime    =>  {NULL|<segundos_hablando>}
     *          ),
     *      ),
     *      callers =>  array(
     *          <lista_llamadas_encoladas>
     *      ),
     * )
     */
    function leerEstadoCola($sNombreCola)
    {
    	$iTimestampActual = time();
        $estadoCola = NULL;
    
    	// TODO: validar formato de $sNombreCola
        $respuestaCola = NULL;
        $respuestaListaAgentes = NULL;
                
        // Leer información inmediata (que no depende de canal)
        $respuestaListaAgentes = $this->_astConn->Command('agent show');
        if (is_array($respuestaListaAgentes))
            $respuestaCola = $this->_astConn->Command("show queue $sNombreCola");        
        if (is_array($respuestaListaAgentes) && is_array($respuestaCola)) {

        	// Averiguar qué canal (si alguno) usa cada agente
            $lineasRespuesta = split("\n", $respuestaListaAgentes['data']);
            $tiempoAgente = array();
            foreach ($lineasRespuesta as $sLinea) {
            	$regs = NULL;
                if (ereg('^[[:space:]]*([[:digit:]]{2,})', $sLinea, $regs)) {
            		$sAgente = $regs[1];  // Agente ha sido identificado
                    $regs = NULL;
                    if (eregi('talking to ((SIP|IAX|ZAP|H323|OH323)/([[:alnum:]\-]{2,}))[[:space:]]+', $sLinea, $regs)) {
                    	$sCanalAgente = $regs[1];
                        
                        // Para el canal, averiguar el momento de inicio de llamada
                        $respuestaCanal = $this->_astConn->Command("core show channel $sCanalAgente");
                        if (!is_array($respuestaCanal)) return NULL;
                        $lineasCanal = split("\n", $respuestaCanal['data']);
                        foreach ($lineasCanal as $sLineaCanal) {
                        	$regs = NULL;
                            if (ereg('level [[:digit:]]+: start=(.*)', $sLineaCanal, $regs)) {
                            	$sFechaInicio = $regs[1];
                                $iTimestampInicio = strtotime($sFechaInicio);
                                $tiempoAgente[$sAgente] = $iTimestampActual - $iTimestampInicio;
                            }
                        }
                    }
            	}
            }

            $estadoCola = array(
                'members'   =>  array(),
                'callers'   =>  array(),
            );
            
            // Parsear la salida de la lista de colas
            $lineasRespuesta = split("\n", $respuestaCola['data']);
            $sSeccionActual = NULL;
            foreach ($lineasRespuesta as $sLinea) {
                if (ereg("^[[:space:]]*Members:", $sLinea)) {
                    $sSeccionActual = "members";
                } else if (ereg("^[[:space:]]*Callers:", $sLinea)) {
                    $sSeccionActual = "callers";
                } else if (!is_null($sSeccionActual)) {
                     switch ($sSeccionActual) {
                     case 'members':
                        $sLinea = trim($sLinea);
                        $regs = NULL;
                        if (eregi('^Agent/([[:digit:]]+)@?[[:space:]]*(.*)$', $sLinea, $regs)) {
                        	$sCodigoAgente = $regs[1];
                            $sInfoAgente = $regs[2];
                            $estadoCola['members'][$sCodigoAgente] = array(
                                'sourceline'    =>  $sLinea,
                                'attributes'    =>  array(),
                                'status'        =>  NULL,
                                'talkTime'      =>  NULL,
                            );
                            
                            // Separar todos los atributos del agente en la cola
                            // ej: "(dynamic) (Unavailable) has taken..."
                            $regs = NULL;
                            while (ereg('^\(([^)]+)\)[[:space:]]+(.*)', $sInfoAgente, $regs)) {
                            	$estadoCola['members'][$sCodigoAgente]['attributes'][] = $regs[1];
                                $sInfoAgente = $regs[2];                                
                                $regs = NULL;
                            }
                            
                      /* Fragmento del archivo main/devicestate.c
                         0 AST_DEVICE_UNKNOWN       "Unknown",      Valid, but unknown state
                         1 AST_DEVICE_NOT_INUSE     "Not in use",   Not used 
                         2 AST_DEVICE IN USE        "In use",       In use 
                         3 AST_DEVICE_BUSY          "Busy",         Busy 
                         4 AST_DEVICE_INVALID       "Invalid",      Invalid - not known to Asterisk 
                         5 AST_DEVICE_UNAVAILABLE   "Unavailable",  Unavailable (not registred) 
                         6 AST_DEVICE_RINGING       "Ringing",      Ring, ring, ring 
                         7 AST_DEVICE_RINGINUSE     "Ring+Inuse",   Ring and in use 
                         8 AST_DEVICE_ONHOLD        "On Hold"       On Hold */

                            // Decidir estado de agente en base a atributos presentes
                            if (in_array('paused', $estadoCola['members'][$sCodigoAgente]['attributes'])) {
                                // Agente está pausado y no disponible para ser llamado
                                $estadoCola['members'][$sCodigoAgente]['status'] = 'unAvailable';
                            } elseif (in_array('Not in use', $estadoCola['members'][$sCodigoAgente]['attributes']) ||
                                in_array('Ringing', $estadoCola['members'][$sCodigoAgente]['attributes'])) {
                                
                                // Agente está disponible para ser llamado
                                $estadoCola['members'][$sCodigoAgente]['status'] = 'canBeCalled';
                            } elseif (in_array('In use', $estadoCola['members'][$sCodigoAgente]['attributes']) ||
                                in_array('Busy', $estadoCola['members'][$sCodigoAgente]['attributes']) ||
                                in_array('Ring+Inuse', $estadoCola['members'][$sCodigoAgente]['attributes'])) {
                            	
                                // Agente está ocupado con una llamada
                                $estadoCola['members'][$sCodigoAgente]['status'] = 'inUse';
                                if (isset($tiempoAgente[$sCodigoAgente])) {
                                	$estadoCola['members'][$sCodigoAgente]['talkTime'] = $tiempoAgente[$sCodigoAgente]; 
                                }
                            } else {
                            	// Agente no está disponible
                                $estadoCola['members'][$sCodigoAgente]['status'] = 'unAvailable';
                            }
                        }
                        break;
                     case 'callers':
                        $estadoCola['callers'][] = trim($sLinea);
                        break;
                     }	
                } 
                
                
            }
        }
        return $estadoCola;
    }

    private function _probabilidadErlangAcumulada($x, $k, $lambda)
    {
        $iSum = 0;
        $iTerm = 1;
        for ($n = 0; $n < $k; $n++) {
            if ($n > 0) $iTerm *= $lambda * $x / $n;
            $iSum += $iTerm;
        }

        return 1 - exp(-$lambda * $x) * $iSum;    	
    }

    private function _probabilidadNormalAcumulada($x, $promedio=0, $desviacionEstandar=1) 
    {
        $z = ($x-$promedio)/$desviacionEstandar;
        $zabs = abs($z);
  
        // Esta tabla ha sido tomada de http://www.math.unb.ca/~knight/utility/NormTble.htm
        $arrNormalAcumuladaEstandar = array(
            '0.00'=>0.5000,'0.01'=>0.5040,'0.02'=>0.5080,'0.03'=>0.5120,'0.04'=>0.5160,'0.05'=>0.5199,'0.06'=>0.5239,'0.07'=>0.5279,'0.08'=>0.5319,'0.09'=>0.5359,     '0.10'=>0.5398,'0.11'=>0.5438,'0.12'=>0.5478,'0.13'=>0.5517,'0.14'=>0.5557,'0.15'=>0.5596,'0.16'=>0.5636,'0.17'=>0.5675,'0.18'=>0.5714,'0.19'=>0.5753,
            '0.20'=>0.5793,'0.21'=>0.5832,'0.22'=>0.5871,'0.23'=>0.5910,'0.24'=>0.5948,'0.25'=>0.5987,'0.26'=>0.6026,'0.27'=>0.6064,'0.28'=>0.6103,'0.29'=>0.6141,
            '0.30'=>0.6179,'0.31'=>0.6217,'0.32'=>0.6255,'0.33'=>0.6293,'0.34'=>0.6331,'0.35'=>0.6368,'0.36'=>0.6406,'0.37'=>0.6443,'0.38'=>0.6480,'0.39'=>0.6517,
            '0.40'=>0.6554,'0.41'=>0.6591,'0.42'=>0.6628,'0.43'=>0.6664,'0.44'=>0.6700,'0.45'=>0.6736,'0.46'=>0.6772,'0.47'=>0.6808,'0.48'=>0.6844,'0.49'=>0.6879,
            '0.50'=>0.6915,'0.51'=>0.6950,'0.52'=>0.6985,'0.53'=>0.7019,'0.54'=>0.7054,'0.55'=>0.7088,'0.56'=>0.7123,'0.57'=>0.7157,'0.58'=>0.7190,'0.59'=>0.7224,
            '0.60'=>0.7257,'0.61'=>0.7291,'0.62'=>0.7324,'0.63'=>0.7357,'0.64'=>0.7389,'0.65'=>0.7422,'0.66'=>0.7454,'0.67'=>0.7486,'0.68'=>0.7517,'0.69'=>0.7549,
            '0.70'=>0.7580,'0.71'=>0.7611,'0.72'=>0.7642,'0.73'=>0.7673,'0.74'=>0.7704,'0.75'=>0.7734,'0.76'=>0.7764,'0.77'=>0.7794,'0.78'=>0.7823,'0.79'=>0.7852,
            '0.80'=>0.7881,'0.81'=>0.7910,'0.82'=>0.7939,'0.83'=>0.7967,'0.84'=>0.7995,'0.85'=>0.8023,'0.86'=>0.8051,'0.87'=>0.8078,'0.88'=>0.8106,'0.89'=>0.8133,
            '0.90'=>0.8159,'0.91'=>0.8186,'0.92'=>0.8212,'0.93'=>0.8238,'0.94'=>0.8264,'0.95'=>0.8289,'0.96'=>0.8315,'0.97'=>0.8340,'0.98'=>0.8365,'0.99'=>0.8389,
            '1.00'=>0.8413,'1.01'=>0.8438,'1.02'=>0.8461,'1.03'=>0.8485,'1.04'=>0.8508,'1.05'=>0.8531,'1.06'=>0.8554,'1.07'=>0.8577,'1.08'=>0.8599,'1.09'=>0.8621,
            '1.10'=>0.8643,'1.11'=>0.8665,'1.12'=>0.8686,'1.13'=>0.8708,'1.14'=>0.8729,'1.15'=>0.8749,'1.16'=>0.8770,'1.17'=>0.8790,'1.18'=>0.8810,'1.19'=>0.8830,
            '1.20'=>0.8849,'1.21'=>0.8869,'1.22'=>0.8888,'1.23'=>0.8907,'1.24'=>0.8925,'1.25'=>0.8944,'1.26'=>0.8962,'1.27'=>0.8980,'1.28'=>0.8997,'1.29'=>0.9015,
            '1.30'=>0.9032,'1.31'=>0.9049,'1.32'=>0.9066,'1.33'=>0.9082,'1.34'=>0.9099,'1.35'=>0.9115,'1.36'=>0.9131,'1.37'=>0.9147,'1.38'=>0.9162,'1.39'=>0.9177,
            '1.40'=>0.9192,'1.41'=>0.9207,'1.42'=>0.9222,'1.43'=>0.9236,'1.44'=>0.9251,'1.45'=>0.9265,'1.46'=>0.9279,'1.47'=>0.9292,'1.48'=>0.9306,'1.49'=>0.9319,
            '1.50'=>0.9332,'1.51'=>0.9345,'1.52'=>0.9357,'1.53'=>0.9370,'1.54'=>0.9382,'1.55'=>0.9394,'1.56'=>0.9406,'1.57'=>0.9418,'1.58'=>0.9429,'1.59'=>0.9441,
            '1.60'=>0.9452,'1.61'=>0.9463,'1.62'=>0.9474,'1.63'=>0.9484,'1.64'=>0.9495,'1.65'=>0.9505,'1.66'=>0.9515,'1.67'=>0.9525,'1.68'=>0.9535,'1.69'=>0.9545,
            '1.70'=>0.9554,'1.71'=>0.9564,'1.72'=>0.9573,'1.73'=>0.9582,'1.74'=>0.9591,'1.75'=>0.9599,'1.76'=>0.9608,'1.77'=>0.9616,'1.78'=>0.9625,'1.79'=>0.9633,
            '1.80'=>0.9641,'1.81'=>0.9649,'1.82'=>0.9656,'1.83'=>0.9664,'1.84'=>0.9671,'1.85'=>0.9678,'1.86'=>0.9686,'1.87'=>0.9693,'1.88'=>0.9699,'1.89'=>0.9706,
            '1.90'=>0.9713,'1.91'=>0.9719,'1.92'=>0.9726,'1.93'=>0.9732,'1.94'=>0.9738,'1.95'=>0.9744,'1.96'=>0.9750,'1.97'=>0.9756,'1.98'=>0.9761,'1.99'=>0.9767,
            '2.00'=>0.9772,'2.01'=>0.9778,'2.02'=>0.9783,'2.03'=>0.9788,'2.04'=>0.9793,'2.05'=>0.9798,'2.06'=>0.9803,'2.07'=>0.9808,'2.08'=>0.9812,'2.09'=>0.9817,
            '2.10'=>0.9821,'2.11'=>0.9826,'2.12'=>0.9830,'2.13'=>0.9834,'2.14'=>0.9838,'2.15'=>0.9842,'2.16'=>0.9846,'2.17'=>0.9850,'2.18'=>0.9854,'2.19'=>0.9857,
            '2.20'=>0.9861,'2.21'=>0.9864,'2.22'=>0.9868,'2.23'=>0.9871,'2.24'=>0.9875,'2.25'=>0.9878,'2.26'=>0.9881,'2.27'=>0.9884,'2.28'=>0.9887,'2.29'=>0.9890,
            '2.30'=>0.9893,'2.31'=>0.9896,'2.32'=>0.9898,'2.33'=>0.9901,'2.34'=>0.9904,'2.35'=>0.9906,'2.36'=>0.9909,'2.37'=>0.9911,'2.38'=>0.9913,'2.39'=>0.9916,
            '2.40'=>0.9918,'2.41'=>0.9920,'2.42'=>0.9922,'2.43'=>0.9925,'2.44'=>0.9927,'2.45'=>0.9929,'2.46'=>0.9931,'2.47'=>0.9932,'2.48'=>0.9934,'2.49'=>0.9936,
            '2.50'=>0.9938,'2.51'=>0.9940,'2.52'=>0.9941,'2.53'=>0.9943,'2.54'=>0.9945,'2.55'=>0.9946,'2.56'=>0.9948,'2.57'=>0.9949,'2.58'=>0.9951,'2.59'=>0.9952,
            '2.60'=>0.9953,'2.61'=>0.9955,'2.62'=>0.9956,'2.63'=>0.9957,'2.64'=>0.9959,'2.65'=>0.9960,'2.66'=>0.9961,'2.67'=>0.9962,'2.68'=>0.9963,'2.69'=>0.9964,
            '2.70'=>0.9965,'2.71'=>0.9966,'2.72'=>0.9967,'2.73'=>0.9968,'2.74'=>0.9969,'2.75'=>0.9970,'2.76'=>0.9971,'2.77'=>0.9972,'2.78'=>0.9973,'2.79'=>0.9974,
            '2.80'=>0.9974,'2.81'=>0.9975,'2.82'=>0.9976,'2.83'=>0.9977,'2.84'=>0.9977,'2.85'=>0.9978,'2.86'=>0.9979,'2.87'=>0.9979,'2.88'=>0.9980,'2.89'=>0.9981,
            '2.90'=>0.9981,'2.91'=>0.9982,'2.92'=>0.9982,'2.93'=>0.9983,'2.94'=>0.9984,'2.95'=>0.9984,'2.96'=>0.9985,'2.97'=>0.9985,'2.98'=>0.9986,'2.99'=>0.9986,
            '3.00'=>0.9987,'3.01'=>0.9987,'3.02'=>0.9987,'3.03'=>0.9988,'3.04'=>0.9988,'3.05'=>0.9989,'3.06'=>0.9989,'3.07'=>0.9989,'3.08'=>0.9990,'3.09'=>0.9990,
        );

        $prob = NULL;
        foreach ($arrNormalAcumuladaEstandar as $Z => $P) {
            if($Z >= $zabs) {
                $prob = $P;
                break;
            }
        }

        /* Qué ocurre si valor se sale de la tabla? */
        if (is_null($prob)) $prob = 0.9991;
  
        if($z < 0) $prob = 1-$P;
  
        return $prob;
    }
}

/*
elastix*CLI> agent show
5000         (DUEÑA LUIS) not logged in (musiconhold is 'default')
5001         (LEON JOSE) not logged in (musiconhold is 'default')
5002         (ALAVA MARIO) not logged in (musiconhold is 'default')
5003         (MOLINA MOISES) not logged in (musiconhold is 'default')
5004         (ALAVA DIEGO) not logged in (musiconhold is 'default')
5005         (GARCIA DANNY) not logged in (musiconhold is 'default')
5006         (GUANANGA FREDDY) not logged in (musiconhold is 'default')
5007         (MARTINEZ GERMAN) not logged in (musiconhold is 'default')
5008         (MEGA JOSE) not logged in (musiconhold is 'default')
5009         (REYES ALVARO) not logged in (musiconhold is 'default')
5010         (RODRIGUEZ AMALIA) not logged in (musiconhold is 'default')
5011         (RON JORGE) not logged in (musiconhold is 'default')
5012         (SALINAS MARIA BELEN) not logged in (musiconhold is 'default')
5013         (SERRANO LUIS) not logged in (musiconhold is 'default')
5014         (BELTRAN DENNISSE) not logged in (musiconhold is 'default')
5015         (BUSTAMANTE MAYIN) not logged in (musiconhold is 'default')
5016         (CARRILLO RONNY) not logged in (musiconhold is 'default')
5017         (FERNANDEZ JONATHAN) not logged in (musiconhold is 'default')
5018         (GARCIA ALEX) not logged in (musiconhold is 'default')
5019         (GUILLEN RAFAEL) not logged in (musiconhold is 'default')
5020         (MERA RUTH) not logged in (musiconhold is 'default')
5021         (SALVATIERRA DANNY) not logged in (musiconhold is 'default')
5022         (SWETT JORGE) not logged in (musiconhold is 'default')
5023         (VARGAS JOSE) not logged in (musiconhold is 'default')
24 agents configured [0 online , 24 offline]

elastix*CLI> show queue 801
801          has 0 calls (max unlimited) in 'ringall' strategy (0s holdtime), W:0, C:1544, A:35, SL:14.2% within 0s
   Members: 
      Agent/5011 (dynamic) (Unavailable) has taken no calls yet
      Agent/5013 (dynamic) (Unavailable) has taken no calls yet
      Agent/5009 (dynamic) (Unavailable) has taken 1 calls (last was 7072 secs ago)
      Agent/5012 (dynamic) (Unavailable) has taken 9 calls (last was 10571 secs ago)
      Agent/5004 (dynamic) (Unavailable) has taken no calls yet
      Agent/5005 (dynamic) (Unavailable) has taken 3 calls (last was 11143 secs ago)
      Agent/5007 (dynamic) (Unavailable) has taken no calls yet
      Agent/5006 (dynamic) (Unavailable) has taken 5 calls (last was 11254 secs ago)
      Agent/5021 (dynamic) (Unavailable) has taken 1 calls (last was 69834 secs ago)
      Agent/5023 (dynamic) (Unavailable) has taken 2 calls (last was 69892 secs ago)
      Agent/5020 (dynamic) (Unavailable) has taken 3 calls (last was 69881 secs ago)
      Agent/5022 (dynamic) (Unavailable) has taken 20 calls (last was 69415 secs ago)
      Agent/5019 (dynamic) (Unavailable) has taken 27 calls (last was 69906 secs ago)
      Agent/5015 (dynamic) (Unavailable) has taken 25 calls (last was 69752 secs ago)
      Agent/5016 (dynamic) (Unavailable) has taken 34 calls (last was 69896 secs ago)
      Agent/5018 (dynamic) (Unavailable) has taken 22 calls (last was 69818 secs ago)
      Agent/5002 (Unavailable) has taken no calls yet
      Agent/5001 (Unavailable) has taken no calls yet
      Agent/5000 (Unavailable) has taken no calls yet
   No Callers

elastix*CLI> core show channels
Channel              Location             State   Application(Data)             
SIP/212-08e36b20     (None)               Up      Bridged Call(Zap/2-1)         
Zap/2-1              s@macro-dial:10      Up      Dial(SIP/212|15|trTwW)        
2 active channels
1 active call

 elastix*CLI> core show channel SIP/212-08e36b20
 -- General --
           Name: SIP/212-08e36b20
           Type: SIP
       UniqueID: 1189016147.976
      Caller ID: 212
 Caller ID Name: (N/A)
    DNID Digits: (N/A)
          State: Up (6)
          Rings: 0
  NativeFormats: 0x4 (ulaw)
    WriteFormat: 0x4 (ulaw)
     ReadFormat: 0x4 (ulaw)
 WriteTranscode: No
  ReadTranscode: No
1st File Descriptor: 36
      Frames in: 30926
     Frames out: 30777
 Time to Hangup: 0
   Elapsed Time: 0h10m25s
  Direct Bridge: Zap/2-1
Indirect Bridge: Zap/2-1
 --   PBX   --
        Context: from-internal
      Extension: 
       Priority: 1
     Call Group: 2
   Pickup Group: 2
    Application: Bridged Call
           Data: Zap/2-1
    Blocking in: ast_waitfor_nandfds
      Variables:
BRIDGEPEER=Zap/2-1
DIALEDPEERNUMBER=212
SIPCALLID=5f326b3978e782ee706168b4722a911b@192.168.1.160
KEEPCID=TRUE
TTL=64
IVR_CONTEXT=ivr-2
IVR_CONTEXT_ivr-2=
DIR-CONTEXT=default
FROM_DID=s

  CDR Variables:
level 1: clid=212
level 1: src=212
level 1: dst=s
level 1: dcontext=from-internal
level 1: channel=SIP/212-08e36b20
level 1: start=2007-09-05 13:15:47
level 1: answer=2007-09-05 13:15:56
level 1: end=2007-09-05 13:15:56
level 1: duration=0
level 1: billsec=0
level 1: disposition=ANSWERED
level 1: amaflags=DOCUMENTATION
level 1: uniqueid=1189016147.976

 
 */
?>