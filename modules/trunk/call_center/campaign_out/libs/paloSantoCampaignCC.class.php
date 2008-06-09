<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: paloSantoCampaignCC.class.php,v 1.2 2008/06/06 07:15:07 cbarcos Exp $ */

include_once("libs/paloSantoDB.class.php");

/* Clase que implementa campaña (saliente por ahora) de CallCenter (CC) */
class paloSantoCampaignCC
{
    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloSantoCampaignCC(&$pDB)
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
    }
    
    /**
     * Procedimiento para obtener el listado de los campañas existentes. Si
     * se especifica id, el listado contendrá únicamente la campaña
     * indicada por el valor. De otro modo, se listarán todas las campañas.
     *
     * @param int   $id_campaign    Si != NULL, indica el id de la campaña a recoger
     *
     * @return array    Listado de campañas en el siguiente formato, o FALSE en caso de error:
     *  array(
     *      //array(id,nombre,fecha_ini,hora_ini,prompt,llamadas_prog,llamadas_real,reintentos,llamadas_pend,detalles),
     *		array(id, name, start_time, retries, b_status, trunk),
     *      ...
     *  )
     */
    function getCampaigns($limit, $offset, $id_campaign = NULL,$estatus='all')
    {
        global $arrLang;
        global $arrLan;
        $where = "";
        if($estatus=='all')
            $where .= " where 1";
        else if($estatus=='A')
            $where .= " where estatus='A'";
        else if($estatus=='I')
            $where .= " where estatus='I'";
        else if($estatus=='T')
            $where .= " where estatus='T'";

        $arr_result = FALSE;
        if (!is_null($id_campaign) && !ereg('^[[:digit:]]+$', "$id_campaign")) {
            $this->errMsg = $arrLan["Campaign ID is not valid"];
        } 
        else {
            if ($where=="") {
                $where = (is_null($id_campaign) ? '' : " WHERE id = $id_campaign");
            } else {
                $where =  $where." ".(is_null($id_campaign) ? '' : " and id = $id_campaign");
            }
            $this->errMsg = "";
            $sPeticionSQL = "SELECT id, name, trunk, context, queue, datetime_init, datetime_end, daytime_init, daytime_end, script, retries, promedio, num_completadas, estatus FROM campaign ".$where;
            $sPeticionSQL .=" ORDER BY datetime_init, daytime_init";
            if (!is_null($limit)) {
                $sPeticionSQL .= " LIMIT $limit OFFSET $offset";
            }

//echo "$sPeticionSQL<br>";
            $arr_result =& $this->_DB->fetchTable($sPeticionSQL, true);
            if (!is_array($arr_result)) {
                $arr_result = FALSE;
                $this->errMsg = $this->_DB->errMsg;
            }
        }
        return $arr_result;
    }

    /**
     * Procedimiento para crear una nueva campaña, vacía e inactiva. Esta campaña 
     * debe luego llenarse con números de teléfono en sucesivas operaciones.
     *
     * @param   $sNombre            Nombre de la campaña
     * @param   $iMaxCanales        Número máximo de canales a usar simultáneamente por campaña
     * @param   $iRetries           Número de reintentos de la campaña, por omisión 5
     * @param   $sTrunk             troncal por donde se van a realizar las llamadas (p.ej. "Zap/g0")
     * @param   $sContext           Contexto asociado a la campaña (p.ej. 'from-internal')
     * @param   $sQueue             Número que identifica a la cola a conectar la campaña saliente (p.ej. '402')
     * @param   $sFechaInicio       Fecha YYYY-MM-DD en que inicia la campaña
     * @param   $sFechaFinal        Fecha YYYY-MM-DD en que finaliza la campaña
     * @param   $sHoraInicio        Hora del día (HH:MM militar) en que se puede iniciar llamadas
     * @param   $sHoraFinal         Hora del día (HH:MM militar) en que se debe dejar de hacer llamadas
     * 
     * @return  int    El ID de la campaña recién creada, o NULL en caso de error
     */
    function createEmptyCampaign($sNombre, $iMaxCanales, $iRetries, $sTrunk, $sContext, $sQueue, 
        $sFechaInicial, $sFechaFinal, $sHoraInicio, $sHoraFinal, $script, $combo)
    {
        global $arrLang;
        global $arrLan;
        $id_campaign = NULL;
        $bExito = FALSE;
//hacemos el query para ver lasa colas seleccionadas
    global $arrConf;
    $error_cola = 0;
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    $query_call_entry = "SELECT queue FROM queue_call_entry WHERE estatus='A'";
    $arr_call_entry = $pDB->fetchTable($query_call_entry, true);
    $arreglo_colas = array();
    foreach($arr_call_entry as $cola){
        foreach($cola as $row){
                 array_push($arreglo_colas,$row);//llenamos el arreglo de colas que estan en queue_call_entry
        }
    }

//para traer valor de cola elegida en combo Colas
	$sCombo = trim($combo);
    if (is_array($arreglo_colas)){
        foreach($arreglo_colas as $queue) {
            if (in_array($sCombo,$arreglo_colas)){//si la cola de queue_call_entry no esta siendo usada la asignamos al combo
                $error_cola = "1";
            }
        }
    }


        $sNombre = trim($sNombre);
        $iMaxCanales = trim($iMaxCanales);
        $iRetries = trim($iRetries);
        $sTrunk = trim($sTrunk); 
        $sContext = trim($sContext);
        $sQueue = trim($sQueue);
        $sFechaInicial = trim($sFechaInicial);
        $sFechaFinal = trim($sFechaFinal);
        $sHoraInicio = trim($sHoraInicio);
        $sHoraFinal = trim($sHoraFinal);
        $script = trim($script);

        if ($sNombre == '') {
            $this->errMsg = $arrLan["Name Campaign can't be empty"];//'Nombre de campaña no puede estar vacío';
        } elseif ($sTrunk == '') {
            $this->errMsg = $arrLan["Trunk can't be empty"];//'Troncal no puede estar vacío';
        } elseif ($sContext == '') {
            $this->errMsg = $arrLan["Context can't be empty"];//'Contexto no puede estar vacío';
        } elseif (!ereg('^[[:digit:]]+$', $iRetries)) {
            $this->errMsg = $arrLan["Retries must be numeric"];//'Número de reintentos debe de ser numérico y entero';
        } elseif ($sQueue == '') {
            $this->errMsg = $arrLan["Queue can't be empty"];//'Número de cola no puede estar vacío';
        } elseif (!ereg('^[[:digit:]]+$', $sQueue)) {
            $this->errMsg = $arrLan["Queue must be numeric"];//'Número de cola debe de ser numérico y entero';
        } elseif (!ereg('^[[:digit:]]{4}-[[:digit:]]{2}-[[:digit:]]{2}$', $sFechaInicial)) {
            $this->errMsg = $arrLan["Invalid Start Date"];//'Fecha de inicio no es válida (se espera yyyy-mm-dd)';
        } elseif (!ereg('^[[:digit:]]{4}-[[:digit:]]{2}-[[:digit:]]{2}$', $sFechaFinal)) {
            $this->errMsg = $arrLan["Invalid End Date"];//'Fecha de final no es válida (se espera yyyy-mm-dd)';
        } elseif ($sFechaInicial > $sFechaFinal) {
            $this->errMsg = $arrLan["Start Date must be greater than End Date"];//'Fecha de inicio debe ser anterior a la fecha final';
        } elseif (!ereg('^[[:digit:]]{2}:[[:digit:]]{2}$', $sHoraInicio)) {
            $this->errMsg = $arrLan["Invalid Start Time"];//'Hora de inicio no es válida (se espera hh:mm)';
        } elseif (!ereg('^[[:digit:]]{2}:[[:digit:]]{2}$', $sHoraFinal)) {
            $this->errMsg = $arrLan["Invalid End Time"];//'Hora de final no es válida (se espera hh:mm)';
        } elseif (strcmp($sFechaInicial,$sFechaFinal)==0 && strcmp ($sHoraInicio,$sHoraFinal)>=0) {
            $this->errMsg = $arrLan["Start Time must be greater than End Time"];//'Hora de inicio debe ser anterior a la hora final';
   	} elseif ($error_cola==1){
	     $this->errMsg =  $arrLan["Queue is being used, choose other one"];//La cola ya está siendo usada, escoja otra
	}
	else {
                // Verificar que el nombre de la campaña es único
                $recordset =& $this->_DB->fetchTable("SELECT * FROM campaign WHERE name = ".paloDB::DBCAMPO($sNombre));
                if (is_array($recordset) && count($recordset) > 0) {
                    // Ya existe una campaña duplicada
                    $this->errMsg = $arrLan["Name Campaign already exists"];//'Nombre de campaña indicado ya está en uso';
                } else {
                    // Construir y ejecutar la orden de inserción SQL
                    $sPeticionSQL = paloDB::construirInsert(
                        "campaign",
                        array(
                            "name"          =>  paloDB::DBCAMPO($sNombre),
                            "max_canales"   =>  paloDB::DBCAMPO($iMaxCanales),
                            "retries"       =>  paloDB::DBCAMPO($iRetries),
                            "trunk"       =>  paloDB::DBCAMPO($sTrunk),
                            "context"     =>  paloDB::DBCAMPO($sContext),
                            "queue"       =>  paloDB::DBCAMPO($sQueue),
                            "datetime_init" =>  paloDB::DBCAMPO($sFechaInicial),
                            "datetime_end"       =>  paloDB::DBCAMPO($sFechaFinal),
                            "daytime_init"       =>  paloDB::DBCAMPO($sHoraInicio),
                            "daytime_end"       =>  paloDB::DBCAMPO($sHoraFinal),
                            "script"       =>  paloDB::DBCAMPO($script),
                        )
                    );

    	            $result = $this->_DB->genQuery($sPeticionSQL);
    	            if ($result) {
                        // Leer el ID insertado por la operación
                        $sPeticionSQL = 'SELECT MAX(id) FROM campaign WHERE name = '.paloDB::DBCAMPO($sNombre);
                        $tupla =& $this->_DB->getFirstRowQuery($sPeticionSQL);
                		if (!is_array($tupla)) {
                			$this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
                		} else {
                                        $id_campaign = (int)$tupla[0];
                                        $bExito = TRUE;
                		}
    	            } else {
    	                $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
    	            }
                }
        }
        return $id_campaign;
    }

    /**
	 * Procedimiento para agregar los formularios a la campaña
	 *
     * @param	int		$id_campaign	ID de la campaña 
     * @param	string		$formularios	los id de los formularios 1,2,....., 
     * @return	bool            true or false       
    */
    function addCampaignForm($id_campania,$formularios)
    {
        global $arrLan;

        if ($formularios != "") {
            $arr_form = explode(",",$formularios);
            foreach($arr_form as $key => $value){
                $sPeticionSQL = paloDB::construirInsert(
                            "campaign_form",
                            array(
                                "id_campaign"    =>  paloDB::DBCAMPO($id_campania),
                                "id_form"        =>  paloDB::DBCAMPO($value)
                            ));
                $result = $this->_DB->genQuery($sPeticionSQL);
                if (!$result){ 
                    $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
                    return false;
                }
            }
        } else {
            $this->errMsg = $arrLan["There aren't form selected"];
            return false;
        }
        return true;
    }

    /**
	 * Procedimiento para actualizar los formularios a la campaña
	 *
     * @param	int		$id_campaign	ID de la campaña 
     * @param	string		$formularios	los id de los formularios 1,2,....., 
     * @return	bool            true or false       
    */
    function updateCampaignForm($id_campania,$formularios)
    {
        $arr_form = explode(",",substr($formularios,0,strlen($formularios)-1));
        $sql = "delete from campaign_form where id_campaign = $id_campania";
        $result = $this->_DB->genQuery($sql);
        if (!$result){ 
             $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
            return false;
        }
        else{
            return $this->addCampaignForm($id_campania,$formularios);
        }
    }

    /**
	 * Procedimiento para obtener los formualarios de una campaña
	 *
     * @param	int		$id_campaign	ID de la campaña 
     * @return	mixed	NULL en caso de error o los id formularios
    */
    function obtenerCampaignForm($id_campania)
    {
        $sPeticionSQL = "SELECT id_form FROM campaign_form WHERE id_campaign = $id_campania";
        $tupla =& $this->_DB->fetchTable($sPeticionSQL);
                if (!is_array($tupla)) {
                        $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
                        return null;
                } 
                else {
                    $salida = array();
                    foreach($tupla as $key => $value){
                        $salida[] = $value[0];
                    }
                    return $salida;
                }
    }

	/**
	 * Procedimiento para contar el número de teléfonos asignados a ser marcados
	 * en la campaña indicada por $idCampaign.
	 *
     * @param	int		$idCampaign	ID de la campaña a leer
     *
     * @return	mixed	NULL en caso de error o número de teléfonos total
	 */
    function countCampaignNumbers($idCampaign)
    {
        global $arrLan;
    	$iNumTelefonos = NULL;
    	
    	if (!ereg('^[[:digit:]]+$', $idCampaign)) {
    		$this->errMsg = $arrLan["Invalid Campaign ID"]; //;'ID de campaña no es numérico';
    	} else {
    		$sPeticionSQL = "SELECT COUNT(*) FROM calls WHERE id_campaign = $idCampaign";
    		$tupla =& $this->_DB->getFirstRowQuery($sPeticionSQL);
    		if (!is_array($tupla)) {
    			$this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
    		} else {
    			$iNumTelefonos = (int)$tupla[0];
    		}
    	}
    	return $iNumTelefonos;
    }
    
    /**
     * Procedimiento para agregar los números de teléfono indicados por la
     * ruta de archivo indicada a la campaña. No se hace intento alguno por
     * eliminar números existentes de la campaña (véase clearCampaignNumbers()), ni
     * tampoco para verificar si los números existentes se encuentran en el
     * listado nuevo definido.
     *
     * Esta función está construida en base a parseCampaignNumbers() y 
     * addCampaignNumbers()
     *
     * @param	int		$idCampaign	ID de la campaña a modificar
     * @param	string	$sFilePath	Archivo local a leer para los números
     *
     * @return bool		VERDADERO si éxito, FALSO si ocurre un error
     */
    function addCampaignNumbersFromFile($idCampaign, $sFilePath)
    {
    	$bExito = FALSE;
    	
    	$listaNumeros = $this->parseCampaignNumbers($sFilePath); 
    	if (is_array($listaNumeros)) {
    		$bExito = $this->addCampaignNumbers($idCampaign, $listaNumeros);
    	}
    	return $bExito;
    }
    
    /**
     * Procedimiento que carga un archivo CSV con números y parámetros en memoria
     * y devuelve la matriz de datos obtenida. El formato del archivo es CSV, 
     * con campos separados por comas. La primera columna contiene el número
     * telefónico, el cual consiste de cualquier cadena numérica. El resto de
     * columnas contienen parámetros que se agregan como campos adicionales. Las
     * líneas vacías se ignoran, al igual que las líneas que empiecen con #
     *
     * @param	string	$sFilePath	Archivo local a leer para la lista
     *
     * @return	mixed	Matriz cuyas tuplas contienen los contenidos del archivo,
     *					en el orden en que fueron leídos, o NULL en caso de error.
     */
    function parseCampaignNumbers($sFilePath)
    {
        global $arrLang;
        global $arrLan;

    	$listaNumeros = NULL;
    	
    	$hArchivo = fopen($sFilePath, 'rt');
    	if (!$hArchivo) {
    		$this->errMsg = $arrLan["Invalid CSV File"];//'No se puede abrir archivo especificado para leer CSV';
    	} else {
    		$iNumLinea = 0;
    		$listaNumeros = array();
    		$clavesColumnas = array();
    		while ($tupla = fgetcsv($hArchivo, 2048,",")) {
    			$iNumLinea++;
                        $tupla[0] = trim($tupla[0]);
    			if (count($tupla) == 1 && trim($tupla[0]) == '') {
    				// Línea vacía
    			} elseif ($tupla[0]{0} == '#') {
    				// Línea que empieza por numeral
    			} elseif (!ereg('^[[:digit:]#*]+$', $tupla[0])) {
                                if ($iNumLinea == 1) {
                                    // Podría ser una cabecera de nombres de columnas
                                    array_shift($tupla);
                                    $clavesColumnas = $tupla;
                                } else {
                                    // Teléfono no es numérico
                                    $this->errMsg = $arrLan["Invalid CSV File Line"]." "."$iNumLinea: ".$arrLan["Invalid number"];
                                    return NULL;
                                }
    			} else {
                    // Como efecto colateral, $tupla pierde su primer elemento
    				$tuplaLista = array('__PHONE_NUMBER' => array_shift($tupla));

                    // Asignar atributos de la tupla
    				for ($i = 0; $i < count($tupla); $i++) {
                        // Si alguna fila tiene más elementos que la lista inicial de nombres, el resto de columnas tiene números
    				    $sClave = "$i";
    				    if ($i < count($clavesColumnas) && $clavesColumnas[$i] != '') $sClave = $clavesColumnas[$i];    				    
    				    $tuplaLista[$sClave] = $tupla[$i];
    				}
  					$listaNumeros[] = $tuplaLista;
    			}
    		}
    		fclose($hArchivo);
    	}
    	return $listaNumeros;
    }
    
    /**
     * Procedimiento que agrega números a una campaña existente. La lista de
     * números consiste en un arreglo de tuplas, cuyo elemento __PHONE_NUMBER
     * es el número de teléfono, y el resto de claves es el conjunto clave->valor
     * a guardar en la tabla call_attribute para cada llamada
     *
     * @param int $idCampaign   ID de Campaña
     * @param array $listaNumeros   Lista de números como se describe arriba
     *      array('__PHONE_NUMBER' => '1234567', 'Name' => 'Fulano de Tal', 'Address' => 'La Conchinchina')
     *
     * @return bool VERDADERO si todos los números fueron insertados, FALSO en error
     */
    function addCampaignNumbers($idCampaign, $listaNumeros)
    {
        global $arrLan;
    	$bExito = FALSE;
    	
    	if (!ereg('^[[:digit:]]+$', $idCampaign)) {
    		$this->errMsg = $arrLan["Invalid Campaign ID"];//'ID de campaña no es numérico';
    	} elseif (!is_array($listaNumeros)) {
    		$this->errMsg = $arrLang[""];//'Lista de números tiene que ser un arreglo';
    	} else {
        	$bContinuar = TRUE;
        	$listaValidada = array(); // Se usa copia porque tupla se modifica en validación
        	
        	// Verificar si todos los elementos son de max. 4 parametros y son
        	// todos numéricos o NULL
        	if ($bContinuar) {
        		foreach ($listaNumeros as $tuplaNumero) {
/*
        			if (count($tuplaNumero) < 1) {
        				$this->errMsg = "Encontrado elemento sin número telefónico";
        				$bContinuar = FALSE;
        			} elseif (!ereg('^[[:digit:]]+$', $tuplaNumero[0])) {
        				$this->errMsg = "Teléfono encontrado que no es numerico";
        				$bContinuar = FALSE;
        			} elseif (count($tuplaNumero) > 1 + 4) {
						$this->errMsg = "Para teléfono $tuplaNumero[0]: implementación actual soporta máximo 4 parámetros";
						break;
        			} else {
        				$iCount = count($tuplaNumero) - 1;
        				for ($i = 1; $i <= $iCount; $i++) {
        					if (trim($tuplaNumero[$i]) == '') $tuplaNumero[$i] = NULL;
        					if (!is_null($tuplaNumero[$i]) && !is_numeric($tuplaNumero[$i])) {
        						$this->errMsg = "Para teléfono $tuplaNumero[0] se encontró parámetro $i = $tuplaNumero[$i] no numérico";
        						$bContinuar = FALSE;
        					}
        				}
        				if ($bContinuar) $listaValidada[] = $tuplaNumero;
        			}
*/
                    if (!isset($tuplaNumero['__PHONE_NUMBER'])) {
        				$this->errMsg = $arrLan["Element without phone number"];//"Encontrado elemento sin número telefónico";
        				$bContinuar = FALSE;
                    } elseif (!ereg('^[[:digit:]#*]+$', $tuplaNumero['__PHONE_NUMBER'])) {
        				$this->errMsg = $arrLan["Invalid number"];
        				$bContinuar = FALSE;
                    } else {
        				if ($bContinuar) $listaValidada[] = $tuplaNumero;
                    }
        			if (!$bContinuar) break;
                        			
        		}
        	}
        	
        	if ($bContinuar) {
                // Inicia transacción
//                 if(!$this->_DB->genQuery("BEGIN TRANSACTION")) {
//                 	$this->errMsg = $this->_DB->errMsg;
//                 } else {
					foreach ($listaValidada as $tuplaNumero) {

                        /********************************************
                        ///// codigo agregado por Carlos Barcos
                        *********************************************/
                        // obtengo el numero para realizar la busqueda del mismo en la lista de llamadas bloqueadas
                        $numero = $tuplaNumero['__PHONE_NUMBER'];
                        // obtengo la lista de llamadas bloqueadas
                        $listaDontCall=$this->convertir_array($this->getDontCallList());
                        // evaluo si el numero obtenido esta en la lista dellamadas bloquedas
			if( in_array($numero,$listaDontCall)){
                            // si se encuentra marca el campo dnc a 1 para bloquear la llamada
			    $dnc=1;
			}else{
                            // si se encuentra marca el campo dnc a 0 para permitir que se realice la llamada
			    $dnc=0;
			}
                        /********************************************
                        ///// fin codigo agregado por Carlos Barcos
                        *********************************************/

                        // arreglo con la informacion necesaria para realizar una llamada
                        $campos = array(
				'id_campaign'	=>	$idCampaign,
				'phone'			=>	paloDB::DBCAMPO($tuplaNumero['__PHONE_NUMBER']),
				'status'		=>	NULL,
                                // campo agregado para permitir o denegar la llamada
				'dnc'		=>	paloDB::DBCAMPO($dnc), // agregado por Carlos Barcos
			);


                        $sPeticionSQL = paloDB::construirInsert("calls", $campos);
						$result = $this->_DB->genQuery($sPeticionSQL);
						if (!$result) {
							$bContinuar = FALSE;
							$this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
							break;
						}
    
    			        $id_call = NULL;

                        // TODO: investigar equivalente de LAST_INSERT_ID() en SQLite
                		$sPeticionSQL = "SELECT MAX(id) FROM calls WHERE id_campaign = $idCampaign and phone = '$tuplaNumero[__PHONE_NUMBER]' and status IS NULL";
                		$tupla =& $this->_DB->getFirstRowQuery($sPeticionSQL);
                		if (!is_array($tupla)) {
                			$this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
                			$bContinuar = FALSE;
                		} else {
                			$id_call = (int)$tupla[0];
                		}
						if ($bContinuar){ 
                                                    $cont_number_column = 1;
                                                    foreach ($tuplaNumero as $sClave => $sValor) {
						    if ($sClave !== '__PHONE_NUMBER') {
						        $campos = array(
						            'id_call'         =>  $id_call,
						            'columna'             =>  paloDB::DBCAMPO($sClave),
						            'value'           =>  paloDB::DBCAMPO($sValor),
                                                            'column_number'   =>  $cont_number_column,
						        );
                                                        $sPeticionSQL = paloDB::construirInsert("call_attribute", $campos);
        						$result = $this->_DB->genQuery($sPeticionSQL);
        						if (!$result) {
        							$bContinuar = FALSE;
        							$this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
        							break;
        						}
                                                        $cont_number_column++;
						    }
						}
						}
						if (!$bContinuar) break;
					}

                    $bExito = $bContinuar;
//                     if ($bExito) {
//     	            	$this->_DB->genQuery("COMMIT");
//     	            } else{
//     	            	$this->_DB->genQuery("ROLLBACK");
//                     }
//                 }        		
        	}
    	}
    	
    	return $bExito;
    }

    /**
     * Procedimiento para crear una nueva campaña, vacía e inactiva. Esta campaña 
     * debe luego llenarse con números de teléfono en sucesivas operaciones.
     *
     * @param   $sNombre            Nombre de la campaña
     * @param   $iMaxCanales        Número máximo de canales a usar simultáneamente por campaña
     * @param   $iRetries           Número de reintentos de la campaña, por omisión 5
     * @param   $sTrunk             troncal por donde se van a realizar las llamadas (p.ej. "Zap/g0")
     * @param   $sContext           Contexto asociado a la campaña (p.ej. 'from-internal')
     * @param   $sQueue             Número que identifica a la cola a conectar la campaña saliente (p.ej. '402')
     * @param   $sFechaInicio       Fecha YYYY-MM-DD en que inicia la campaña
     * @param   $sFechaFinal        Fecha YYYY-MM-DD en que finaliza la campaña
     * @param   $sHoraInicio        Hora del día (HH:MM militar) en que se puede iniciar llamadas
     * @param   $sHoraFinal         Hora del día (HH:MM militar) en que se debe dejar de hacer llamadas
     * 
     * @return  int    El ID de la campaña recién creada, o NULL en caso de error
     */
    function updateCampaign($idCampaign,$sNombre, $iMaxCanales, $iRetries, $sTrunk, $sContext, $sQueue, 
        $sFechaInicial, $sFechaFinal, $sHoraInicio, $sHoraFinal, $script)
    {
        global $arrLang;
        global $arrLan;

        $bExito = FALSE;

        $sNombre = trim($sNombre);
        $iMaxCanales = trim($iMaxCanales);
        $iRetries = trim($iRetries);
        $sTrunk = trim($sTrunk);
        $sContext = trim($sContext);
        $sQueue = trim($sQueue);
        $sFechaInicial = trim($sFechaInicial);
        $sFechaFinal = trim($sFechaFinal);
        $sHoraInicio = trim($sHoraInicio);
        $sHoraFinal = trim($sHoraFinal);
        $script = trim($script);

        if ($sNombre == '') {
            $this->errMsg = $arrLan["Name Campaign can't be empty"];//'Nombre de campaña no puede estar vacío';
        } elseif ($sTrunk == '') {
            $this->errMsg = $arrLan["Trunk can't be empty"];//'Troncal no puede estar vacío';
        } elseif ($sContext == '') {
            $this->errMsg = $arrLan["Context can't be empty"];//'Contexto no puede estar vacío';
        } elseif (!ereg('^[[:digit:]]+$', $iRetries)) {
            $this->errMsg = $arrLan["Retries must be numeric"];//'Número de reintentos debe de ser numérico y entero';
        } elseif ($sQueue == '') {
            $this->errMsg = $arrLan["Queue can't be empty"];//'Número de cola no puede estar vacío';
        } elseif (!ereg('^[[:digit:]]{4}-[[:digit:]]{2}-[[:digit:]]{2}$', $sFechaInicial)) {
            $this->errMsg = $arrLan["Invalid Start Date"];//'Fecha de inicio no es válida (se espera yyyy-mm-dd)';
        } elseif (!ereg('^[[:digit:]]{4}-[[:digit:]]{2}-[[:digit:]]{2}$', $sFechaFinal)) {
            $this->errMsg = $arrLan["Invalid End Date"];//'Fecha de final no es válida (se espera yyyy-mm-dd)';
        } elseif ($sFechaInicial > $sFechaFinal) {
            $this->errMsg = $arrLan["Start Date must be greater than End Date"];//'Fecha de inicio debe ser anterior a la fecha final';
        } elseif (!ereg('^[[:digit:]]{2}:[[:digit:]]{2}$', $sHoraInicio)) {
            $this->errMsg = $arrLan["Invalid Start Time"];//'Hora de inicio no es válida (se espera hh:mm)';
        } elseif (!ereg('^[[:digit:]]{2}:[[:digit:]]{2}$', $sHoraFinal)) {
            $this->errMsg = $arrLan["Invalid End Time"];//'Hora de final no es válida (se espera hh:mm)';
        } elseif (strcmp($sFechaInicial,$sFechaFinal)==0 && strcmp ($sHoraInicio,$sHoraFinal)>=0) {
            $this->errMsg = $arrLan["Start Time must be greater than End Time"];//'Hora de inicio debe ser anterior a la hora final';
        } else {

            // Construir y ejecutar la orden de update SQL
            $sPeticionSQL = paloDB::construirUpdate(
                "campaign",
                array(
                    "name"          =>  paloDB::DBCAMPO($sNombre),
                    "max_canales"   =>  paloDB::DBCAMPO($iMaxCanales),
                    "retries"       =>  paloDB::DBCAMPO($iRetries),
                    "trunk"         =>  paloDB::DBCAMPO($sTrunk),
                    "context"       =>  paloDB::DBCAMPO($sContext),
                    "queue"         =>  paloDB::DBCAMPO($sQueue),
                    "datetime_init" =>  paloDB::DBCAMPO($sFechaInicial),
                    "datetime_end"  =>  paloDB::DBCAMPO($sFechaFinal),
                    "daytime_init"  =>  paloDB::DBCAMPO($sHoraInicio),
                    "daytime_end"   =>  paloDB::DBCAMPO($sHoraFinal),
                    "script"        =>  paloDB::DBCAMPO($script),
                ),
                " id=$idCampaign "
            );

            $result = $this->_DB->genQuery($sPeticionSQL);
            if ($result) {
                return true;
            } else {
                $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
            }
        }
        return false;
    }

    function activar_campaign($idCampaign,$activar)
    {
         $sPeticionSQL = paloDB::construirUpdate(
             "campaign",
             array("estatus"       =>  paloDB::DBCAMPO($activar)),
             " id=$idCampaign "
            );
        
            $result = $this->_DB->genQuery($sPeticionSQL);
            if ($result) 
                return true;
            else 
                $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
            return false;
    } 

    function delete_campaign($idCampaign)
    {
        global $arrLang;
        global $arrLan;
        $sQuery = "SELECT count(id) llamadas_realizadas FROM calls WHERE id_campaign=$idCampaign and status is not null";
//echo "query = $sQuery<br>";
        $result =& $this->_DB->getFirstRowQuery($sQuery, true);
//print_r($result); echo "<br>";
        $valido = false;
        if (is_array($result) && count($result)>0) {
            if ($result["llamadas_realizadas"] == 0) {
                $result = $this->_DB->genQuery("SET AUTOCOMMIT=0");
                if ($result) {
                    $sql = "DELETE FROM campaign_form WHERE id_campaign=$idCampaign";
                    $result = $this->_DB->genQuery($sql);
                    if (!$result) {
                        $this->errMsg = $this->_DB->errMsg;
                        $this->_DB->genQuery("ROLLBACK");
                        $this->_DB->genQuery("SET AUTOCOMMIT=1");
                        return false;
                    }
                    $sql = "DELETE FROM call_attribute WHERE id_call in (select id from calls where id_campaign=$idCampaign)";
                    $result = $this->_DB->genQuery($sql);
                    if (!$result) {
                        $this->errMsg = $this->_DB->errMsg;
                        $this->_DB->genQuery("ROLLBACK");
                        $this->_DB->genQuery("SET AUTOCOMMIT=1");
                        return false;
                    }
                    $sql = "DELETE FROM calls WHERE id_campaign=$idCampaign";
                    $result = $this->_DB->genQuery($sql);
                    if (!$result) {
                        $this->errMsg = $this->_DB->errMsg;
                        $this->_DB->genQuery("ROLLBACK");
                        $this->_DB->genQuery("SET AUTOCOMMIT=1");
                        return false;
                    }

                    $sql = "DELETE FROM campaign WHERE id=$idCampaign";
                    $result = $this->_DB->genQuery($sql);
                    if (!$result) {
                        $this->errMsg = $this->_DB->errMsg;
                        $this->_DB->genQuery("ROLLBACK");
                        $this->_DB->genQuery("SET AUTOCOMMIT=1");
                        return false;
                    }
                    $this->_DB->genQuery("COMMIT");
                    $result = $this->_DB->genQuery("SET AUTOCOMMIT=1");
                    $valido = true;
                }
            } else {
                $valido = true;
                $this->errMsg = $arrLan["This campaign have calls done"];
            }
        }
        return $valido;
    }

/********************************************
///// codigo agregado por Carlos Barcos
*********************************************/

    /*
        Funcion que me permite obtener una lista de llamadas a ser bloquedas
    */
    function getDontCallList(){
	$sql = "select id,caller_id from dont_call where status='A'";
	$arr_result =& $this->_DB->fetchTable($sql, true);
	if (!is_array($arr_result)) {
	    $arr_result = FALSE;
	    $this->errMsg = $this->_DB->errMsg;
	}
	return $arr_result;
    }

    /*
        Funcion que permite tomar un arreglo de la forma:
            [0]=>array("clave"=>valor1)
            [1]=>array("clave"=>valor2)
                .
                .
                .
        y convertirlo en otro de la forma:
            [0]=>valor1
            [1]=>valor1
                .
                .
                .
    */
    function convertir_array($data){
        $data_modificada=array();
        if(is_array($data) && count($data)>0){
            foreach($data as $d){
                $data_modificada[] = $d["caller_id"];
            }
        }
        return $data_modificada;
    }

/********************************************
///// Fin codigo agregado por Carlos Barcos
*********************************************/

}

//FUNCIONES AJAX
function desactivar_campania($idCampaign)
{
    global $arrLang;
    global $arrLan;
    global $arrConf;
    $respuesta = new xajaxResponse();
    
    // se conecta a la base
    $pDB = new paloDB($arrConf["cadena_dsn"]);

    if($pDB->errMsg != "") {
        $respuesta->addAssign("mb_message","innerHTML",$arrLang["Error when connecting to database"]."<br/>".$pDB->errMsg);
    }

    $oCampaign = new paloSantoCampaignCC($pDB);

    if($oCampaign->activar_campaign($idCampaign,'I'))
        $respuesta->addScript("window.open('?menu=campaign_out','_parent')");
    else{
        $respuesta->addAssign("mb_title","innerHTML",$arrLan["Desactivate Error"]."<br/>".$pDB->errMsg); 
        $respuesta->addAssign("mb_message","innerHTML",$arrLan["Error when desactivating the Campaign"]."<br/>".$pDB->errMsg); 
    }
    return $respuesta;
}
?>
