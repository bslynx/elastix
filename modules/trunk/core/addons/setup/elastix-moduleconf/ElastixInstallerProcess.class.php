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
  $Id: ElastixInstallerProcess.class.php,v 1.48 2009/03/26 13:46:58 alex Exp $ */
require_once('AbstractProcess.class.php');
require_once('/var/www/html/libs/paloSantoDB.class.php');

class ElastixInstallerProcess extends AbstractProcess
{
    private $oMainLog;      // Log abierto por framework de demonio
    private $_hEscucha;     // Socket que escucha las conexiones entrantes
    private $_procYum;      // Objeto que administra conexión a YUM
    private $_procPipes;    // Arreglo de tuberías a YUM 0-STDIN 1-STDOUT 2-STDERR
    private $_conexiones;   // Arreglo de conexiones activas del sistema
    
    private $_sContenido;   // Contenido devuelto por yum shell como resultado del último comando
    private $_bCapturarStderr = FALSE;
    private $_stderrBuf = '';    // Salida de stderr para actividad actual
    private $_estadoPaquete = NULL;

    function inicioPostDemonio($infoConfig, &$oMainLog)
    {
        $bContinuar = TRUE;

        // Guardar referencias al log del programa
        $this->oMainLog =& $oMainLog;

        $this->_conexiones = array();

        // Socket para recibir peticiones entrantes
        if ($bContinuar) {
            $errno = $errstr = NULL;
            $sUrlSocket = $this->_construirUrlSocket();
            $this->_hEscucha = stream_socket_server($sUrlSocket, $errno, $errstr);
            if (!$this->_hEscucha) {
                $this->oMainLog->output("ERR: no se puede iniciar socket de escucha: ($errno) $errstr");
                $bContinuar = FALSE;
            } else {
                // No bloquearse en escucha de conexiones
                stream_set_blocking($this->_hEscucha, 0);
                $this->oMainLog->output("INFO: escuchando peticiones en $sUrlSocket ...");
            }
        }
        
        if ($bContinuar)
            $bContinuar = $this->_iniciarYumShell();
        
        return $bContinuar;
    }
    
    private function _iniciarYumShell()
    {
        $bContinuar = TRUE;
        $bFinInicio = FALSE;
        
        $this->_estadoPaquete = array(
            'status'    =>  'idle',
            'action'    =>  'none',

            'progreso'  =>  array(),
            'instalado' =>  array(),
            'errores'   =>  array(),
            'warning'   =>  array(),
        );

        // Abrir proceso de yum
        if ($bContinuar) {
            $descriptores = array(
	            0	=>	array('pipe', 'r'),
	            1	=>	array('pipe', 'w'),
	            2	=>	array('pipe', 'w'),
            );
            $this->_procPipes = NULL; $cwd = '/';
            $this->_procYum = proc_open('/usr/bin/yum shell', $descriptores, $this->_procPipes, $cwd);
            if (!is_resource($this->_procYum)) {
                $this->oMainLog->output("ERR: no se puede iniciar instancia de yum shell");
                $bContinuar = FALSE;
            } else {
                $this->oMainLog->output("INFO: arrancando yum shell ...");
                //stream_set_blocking($this->_procPipes[0], 0);
                stream_set_blocking($this->_procPipes[1], 0);
                stream_set_blocking($this->_procPipes[2], 0);                
            }
        }
        
        // Leer los datos de la salida de yum hasta que se obtenga la cadena
        // final que indica que se tiene el shell listo.
        $bFinInicio = FALSE; $sContenido = '';
        $sFinSetup = "Setting up Yum Shell\n";
        while ($bContinuar && !$bFinInicio) {
		    $salidaYum = array($this->_procPipes[1], $this->_procPipes[2]);
		    $entradaYum = NULL;
		    $exceptYum = NULL;
		    $iNumCambio = stream_select($salidaYum, $entradaYum, $exceptYum, 1);
		    if ($iNumCambio === false) {
		        $this->oMainLog->output("ERR: falla al esperar en select()");
		        $bContinuar = FALSE;
    		} elseif ($iNumCambio > 0) {
    		    if (in_array($this->_procPipes[2], $salidaYum)) {
    		        // Mensaje de stderr de yum, mandar a log
    		        $s = stream_get_contents($this->_procPipes[2]);
    		        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
    		        $this->oMainLog->output("yum(stderr): $s");
    		    }
    		    if (in_array($this->_procPipes[1], $salidaYum)) {
    		        // Mensaje de stdout de yum
    		        $s = stream_get_contents($this->_procPipes[1]);
    		        $sContenido .= $s;
    		        if ($s == '') {
        		        $this->oMainLog->output("ERR: fin no esperado de yum!");
    		            $bContinuar = false;
    		            break;
    		        }
    		        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
    		        $this->oMainLog->output("yum(stdout): $s");
    		        if (substr($sContenido, -strlen($sFinSetup)) == $sFinSetup) {
        		        $this->oMainLog->output("INFO: yum shell está preparado.");
        		        $bFinInicio = TRUE;
		            }
    		    }
            }
        }
        
        // Abortar procesos si no se puede iniciar yum
        if (!$bContinuar && is_resource($this->_procYum)) {
        	fclose($this->_procPipes[0]);
        	fclose($this->_procPipes[1]);
        	fclose($this->_procPipes[2]);
        	$this->_procPipes = NULL;
        	$ret = proc_close($this->_procYum);
        	$this->oMainLog->output("INFO: yum finaliza con ret=$ret");
        	$this->_procYum = NULL;
        }
        
        return $bContinuar;
    }

    private function _finalizarYumShell()
    {
        if (is_resource($this->_procYum)) {

            $yumStatus = proc_get_status($this->_procYum);
            if ($yumStatus['running']) {
                $sComando = "quit\n";
                fwrite($this->_procPipes[0], $sComando);

                $bFinInicio = FALSE; $sContenido = '';
                while (!$bFinInicio) {
		            $salidaYum = array($this->_procPipes[1], $this->_procPipes[2]);
		            $entradaYum = NULL;
		            $exceptYum = NULL;
		            $iNumCambio = stream_select($salidaYum, $entradaYum, $exceptYum, 1);
		            if ($iNumCambio === false) {
		                $this->oMainLog->output("ERR: falla al esperar en select()");
		                break;
            		} elseif ($iNumCambio > 0) {
            		    if (in_array($this->_procPipes[2], $salidaYum)) {
            		        // Mensaje de stderr de yum, mandar a log
            		        $s = stream_get_contents($this->_procPipes[2]);
            		        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
            		        $this->oMainLog->output("yum(stderr): $s");
            		    }
            		    if (in_array($this->_procPipes[1], $salidaYum)) {
            		        // Mensaje de stdout de yum
            		        $s = stream_get_contents($this->_procPipes[1]);
            		        $sContenido .= $s;
            		        if ($s == '') {
                		        $this->oMainLog->output("INFO: finalizada instancia de yum shell");
            		            $bFinInicio = TRUE;
            		            break;
            		        }
            		        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
            		        $this->oMainLog->output("yum(stdout): $s");
            		    }
                    }
                }
            }
        	fclose($this->_procPipes[0]);
        	fclose($this->_procPipes[1]);
        	fclose($this->_procPipes[2]);
        	$this->_procPipes = NULL;
        	$ret = proc_close($this->_procYum);
        	$this->oMainLog->output("INFO: yum finaliza con ret=$ret");
        	$this->_procYum = NULL;
        }
    }

    // Construir el URL que describe el socket para escuchar peticiones
    private function _construirUrlSocket()
    {
        // TODO: hacer configurable
        return 'tcp://127.0.0.1:20004';
    }
    
    function procedimientoDemonio()
    {
        $listoLeer = array();
        $listoEscribir = array();
        $listoErr = NULL;

        // Recolectar todos los descriptores que se monitorean
        $listoLeer[] = $this->_hEscucha;        // Escucha de nuevas conexiones
        $listoLeer[] = $this->_procPipes[1];    // yum salida estándar
        $listoLeer[] = $this->_procPipes[2];    // yum error estándar
        foreach ($this->_conexiones as &$conexion) {
            if (!$conexion['exit_request']) $listoLeer[] = $conexion['socket'];
            if (strlen($conexion['pendiente_escribir']) > 0) {
                $listoEscribir[] = $conexion['socket'];                
            }
        }
        $iNumCambio = stream_select($listoLeer, $listoEscribir, $listoErr, 1);
        if ($iNumCambio === false) {
            // Interrupción, tal vez una señal
            $this->oMainLog->output("INFO: select() finaliza con fallo - señal pendiente?");
            return FALSE;
        } elseif ($iNumCambio > 0 || count($listoLeer) > 0 || count($listoEscribir) > 0) {
            if (in_array($this->_hEscucha, $listoLeer)) {
                // Entra una conexión nueva
                $this->_procesarConexionNueva();
            }
            if (in_array($this->_procPipes[1], $listoLeer)) {
                // Se tiene nueva información del yum shell
                $bActivo = $this->_actualizarEstadoYumShell();
                if (!$bActivo) return FALSE;
            }
            if (in_array($this->_procPipes[2], $listoLeer)) {
		        // Mensaje de stderr de yum, mandar a log
		        $this->_actualizarStderrYumShell();
            }
            foreach ($this->_conexiones as $iPos => &$conexion) {
                if (in_array($conexion['socket'], $listoEscribir)) {
                    // Escribir lo más que se puede de los datos pendientes por mostrar
                    $iBytesEscritos = fwrite($conexion['socket'], $conexion['pendiente_escribir']);
                    if ($iBytesEscritos === FALSE) {
                        $this->oMainLog->output("ERR: error al escribir datos a ".$conexion['socket']);
                        $this->_cerrarConexion($iPos);
                    } else {
                        $conexion['pendiente_escribir'] = substr($conexion['pendiente_escribir'], $iBytesEscritos);
                    }
                }
                if (in_array($conexion['socket'], $listoLeer)) {
                    $this->_procesarEntradaConexion($iPos);
                }
            }

            // Cerrar todas las conexiones que no tienen más datos que mostrar
            // y que han marcado que deben terminarse
            foreach ($this->_conexiones as $iPos => &$conexion) {
                if (is_array($conexion) && $conexion['exit_request'] && strlen($conexion['pendiente_escribir']) <= 0) {
                    $this->_cerrarConexion($iPos);
                }
            }

            // Remover todos los elementos seteados a FALSE
            $this->_conexiones = array_filter($this->_conexiones);
            
            // Revisar regularmente la descarga de los paquetes
            if ($this->_estadoPaquete['action'] == 'downloading')
                $this->_revisarProgresoPaquetes();
        }        
        return TRUE;
    }
    
    private function _procesarConexionNueva()
    {
        $nuevaConn = array(
            'socket'                =>  stream_socket_accept($this->_hEscucha),
            'pendiente_leer'        =>  '',
            'pendiente_escribir'    =>  '',
            'exit_request'          =>  FALSE,
        );
        stream_set_blocking($nuevaConn['socket'], 0);                

        // TODO: enviar status de yum shell al socket antes de aceptar comandos
        $dummy = array();
        $nuevaConn['pendiente_escribir'] = $this->_procesarStatus($dummy);
        $this->_conexiones[] =& $nuevaConn; 
    }
    
    private function _procesarEntradaConexion($iPos)
    {
        $sNuevaEntrada = fread($this->_conexiones[$iPos]['socket'], 8192);
        if ($sNuevaEntrada == '') {
            // Lectura de cadena vacía indica que se ha cerrado la conexión remotamente
	        $this->_cerrarConexion($iPos);
	        return ;
        }

        // pendiente_leer puede tener un contenido previo que no es una línea completa
        $this->_conexiones[$iPos]['pendiente_leer'] .= $sNuevaEntrada;
        $listaComandos = explode("\n", $this->_conexiones[$iPos]['pendiente_leer']);
        while (count($listaComandos) > 1) {
            $sComando = array_shift($listaComandos);
            if (trim($sComando) != '') $this->_procesarComando($iPos, trim($sComando));
        }

        // Esto asigna, o la cadena vacía, o el pedazo de comando que se ha leído
        $this->_conexiones[$iPos]['pendiente_leer'] = $listaComandos[0];
    }

    private function _cerrarConexion($iPos)
    {
        fclose($this->_conexiones[$iPos]['socket']);
        $this->_conexiones[$iPos] = FALSE;  // Será removido por array_map()
    }

    function limpiezaDemonio()
    {
        // TODO: limpiar las conexiones activas
        foreach ($this->_conexiones as &$conexion) {
            fclose($conexion['socket']);
        }
    
        // TODO: cancelar la operación yum activa
        
        // Cerrar las conexiones al yum shell
        $this->_finalizarYumShell();
        
        // Cerrar el socket de escucha de eventos
        fclose($this->_hEscucha);
        $this->_hEscucha = NULL;
    }

    /**************************************************************************/

/*
Programa en PHP que ejecute a su vez "yum shell" como root.
Se debe exponer un socket para control desde página Web.
Tareas deben de poderse realizar incluso entre desconexiones de socket.
Interfaz simple de comandos vía socket:

* mostrar estado
* agregar paquete a transacción
* limpiar transacción
* remover paquete instalado como parte de transacción
* verificar actualización de paquete y agregarlo a transacción
* iniciar transacción
* cancelar transacción (mandar SIGINT a yum, posiblemente dos veces con demora)


*/

    /* La interfaz de comando que se presenta consiste en un protocolo texto.
       El comando a ingresar es de la forma: COMANDO [ARG1] [ARG2] ...
       seguido de un salto de línea que manda a procesar el comando. Los 
       comandos reconocidos son:
       status 
       add nombredepaquete( nombrepaquete2 ...)
       remove nombredepaquete( nombredepaquete2 ...)
       clear
       confirm
       update nombredepaquete( nombredepaquete2)
       cancel
       quit
       exit     
     */
    private function _procesarComando($iPos, $sComando)
    {
        $sTextoSalida = '';
        $listaComando = preg_split('/\s+/', $sComando);
        if (count($listaComando) <= 0) return;

        $sVerbo = array_shift($listaComando);
        
        switch ($sVerbo) {
        case 'status':
            $sTextoSalida = $this->_procesarStatus($listaComando);
            break;
        case 'add':
            $sTextoSalida = $this->_procesarAdd($listaComando);
            break;
        case 'remove':
            $sTextoSalida = $this->_procesarRemove($listaComando);
            break;
        case 'clear':
            $sTextoSalida = $this->_procesarClear($listaComando);
            break;
        case 'confirm':
            $sTextoSalida = $this->_procesarConfirm($listaComando);
            break;
        case 'update':
            $sTextoSalida = $this->_procesarUpdate($listaComando);
            break;
        case 'cancel':
            $sTextoSalida = $this->_procesarCancel($listaComando);
            break;
        case 'check':
            $sTextoSalida = $this->_procesarCheck($listaComando);
            break;
        case 'yumoutput':
            $sTextoSalida = $this->_sContenido;
            break;
        case 'yumerror':
            $sTextoSalida = $this->_stderrBuf;
            break;
        case 'exit':
        case 'quit':
            $this->_conexiones[$iPos]['exit_request'] = TRUE;
            break;
        default:
            $sTextoSalida = "ERR Unrecognized\n";
            break;
        }
        $this->_conexiones[$iPos]['pendiente_escribir'] .= $sTextoSalida;
    }

    private function _procesarStatus(&$listaArgs)
    {
        $sReporte = '';

        $sReporte .= "status ".$this->_estadoPaquete['status']."\n";
        $sReporte .= "action ".$this->_estadoPaquete['action']."\n"; // none confirm reporefresh depsolving downloading applying
        foreach ($this->_estadoPaquete['progreso'] as $infoProgreso) {
            $sReporte .= 'package'.
                ' '.$infoProgreso['pkgaction']. // pkgaction puede ser: install update remove
                ' '.$infoProgreso['nombre'].    // nombre del paquete
                ' '.$infoProgreso['longitud'].' '.$infoProgreso['descargado'].  // total y descarga
                ' '.$infoProgreso['currstatus']."\n"; // currstatus puede ser: waiting downloading downloaded installing installed removing removed
        }
        foreach ($this->_estadoPaquete['instalado'] as $infoInstalado) {
            $sReporte .= 'installed'.
                ' '.$infoInstalado['nombre'].
                ' '.$infoInstalado['arch'].
                ' '.$infoInstalado['epoch'].
                ' '.$infoInstalado['version'].
                ' '.$infoInstalado['release']."\n";
        }
        
        foreach ($this->_estadoPaquete['errores'] as $sMsg) {
            $sReporte .= 'errmsg '.$sMsg."\n";
        }
        foreach ($this->_estadoPaquete['warning'] as $sMsg) {
            $sReporte .= 'warnmsg '.$sMsg."\n";
        }
        $sReporte .= "end status\n";
        return $sReporte;
    }
    
    /*
================================================================================
 Package         Arch         Version            Repository                Size
================================================================================
Installing:
 pidgin          i386         2.6.6-1.el5        updates                  1.5 M
 pidgin          x86_64       2.6.6-1.el5        updates                  1.5 M
Installing for dependencies:
 gtkspell        i386         2.0.11-2.1         base                      30 k
 libpurple       i386         2.6.6-1.el5        updates                  8.3 M
 libpurple       x86_64       2.6.6-1.el5        virthost64-updates       8.4 M
 libsilc         i386         1.0.2-2.fc6        base                     412 k
 meanwhile       i386         1.0.2-5.el5        base                     108 k

    */
    private function _recogerPaquetesTransaccion()
    {
        $lineas = explode("\n", $this->_sContenido);
        $this->_estadoPaquete['progreso'] = array();
        $bReporte = FALSE;
        $sOperacion = NULL;
        foreach ($lineas as $sLinea) {
            $regs = NULL;
            if (!$bReporte && preg_match('/^\s+Package\s+Arch\s+Version\s+Repository\s+Size/', $sLinea)) {
                $bReporte = TRUE;
            } elseif (strpos($sLinea, "Transaction Summary") !== FALSE) {
                $bReporte = FALSE;
            } elseif ($bReporte) {
                $regs = NULL;
                if (preg_match('/^\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)/', $sLinea, $regs)) {
                    $this->_estadoPaquete['progreso'][] = array(
                        'pkgaction' =>  $sOperacion,
                        'nombre'    =>  $regs[1],
                        'arch'      =>  $regs[2],
                        'version'   =>  $regs[3],
                        'repo'      =>  $regs[4],
                        'longitud'  =>  $regs[5],
                        'rpmfile'   =>  NULL,
                        'descargado'=>  '-',
                        'currstatus'=>  ($sOperacion == 'remove') ? 'installed' : 'waiting',
                    );
                } elseif (strpos($sLinea, 'Installing') === 0) {
                    $sOperacion = 'install';
                } elseif (strpos($sLinea, 'Updating') === 0) {
                    $sOperacion = 'update';
                } elseif (strpos($sLinea, 'Removing') === 0) {
                    $sOperacion = 'remove';
                }
            } 
            if (preg_match('/No package (\S+) available/', $sLinea, $regs)) {
                $this->_estadoPaquete['status'] = 'error';
                $this->_estadoPaquete['errores'][] = "The following package is not available: ".$regs[1];
            }
        }
        
        if ($this->_estadoPaquete['status'] != 'error' && count($this->_estadoPaquete['progreso']) <= 0) {
            $this->_estadoPaquete['action'] = 'idle';
            $this->_estadoPaquete['warning'][] = 'No packages to install or update';
        }
        
        /* La información de tamaño que proporciona yum es demasiado poco detallada
           para poder seguir la pista de la descarga con precisión de bytes. Por lo
           tanto, hay que abrir las bases SQLITE3 de yum y leer los datos de allí.
         */

        // Validar las rutas base de los repos
        $infoRepo = array();
        if ($this->_estadoPaquete['status'] != 'error') {
            $sRutaCache = '/var/cache/yum/';
            foreach ($this->_estadoPaquete['progreso'] as $paquete) {
                if (!isset($infoRepo[$paquete['repo']])) {

                    $sNombreRepo = $paquete['repo'];
                    if ($sNombreRepo == 'installed') continue;
                    $sRutaRepo = $sRutaCache.$paquete['repo'].'/';
                    $infoRepo[$sNombreRepo] = array(
                        'ruta'  =>  $sRutaRepo,                        
                    );

                    if (!is_dir($sRutaRepo)) {
                        $this->_estadoPaquete['status'] = 'error';
                        $this->_estadoPaquete['errores'][] = "Unable to figure out cache directory for repo: $sNombreRepo";
                    } elseif (!is_readable($sRutaRepo.'repomd.xml')) {
                        $this->_estadoPaquete['status'] = 'error';
                        $this->_estadoPaquete['errores'][] = "Unable to read file repomd.xml from repo: $sNombreRepo";                        
                    } else {
                        // El siguiente código require el módulo php-xml
                        $repomd = new SimpleXMLElement(file_get_contents($sRutaRepo.'repomd.xml'));
                        foreach ($repomd->data as &$dataObj) {
                            if ($dataObj['type'] == 'primary_db') {
                                $sRutaPrimary = $dataObj->location['href'];
                                $regs = NULL;
                                if (preg_match('|^(.*)/(\S+)(\.bz2)|', $sRutaPrimary, $regs)) {
                                    $sRutaPrimary = $regs[2];
                                }
                                $infoRepo[$sNombreRepo]['primary_db'] = $sRutaPrimary;
                            } elseif (!isset($infoRepo[$sNombreRepo]['primary_db']) && $dataObj['type'] == 'primary') {
                                $sRutaPrimary = $dataObj->location['href'];
                                $regs = NULL;
                                if (preg_match('|^(.*)/(\S+)|', $sRutaPrimary, $regs)) {
                                    $sRutaPrimary = $regs[2];
                                }
                                $infoRepo[$sNombreRepo]['primary_db'] = $sRutaPrimary.'.sqlite';
                            }
                        }
                        if (!isset($infoRepo[$sNombreRepo]['primary_db'])) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Unable to locate primary_db from repo: $sNombreRepo";
                        } elseif (!is_readable($sRutaRepo.$infoRepo[$sNombreRepo]['primary_db'])) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Unable to read primary_db from repo: $sNombreRepo";
                            unset($infoRepo[$sNombreRepo]['primary_db']);
                        }
                    }
                }
            }
        }
        
        // Para cada paquete, se abre el archivo primary_db de su correspondiente
        // repo y se consulta vía SQL el tamaño del paquete.
        if ($this->_estadoPaquete['status'] != 'error') {
            foreach ($this->_estadoPaquete['progreso'] as &$infoPaquete) {
                if ($infoPaquete['repo'] == 'installed') continue;
                $repo =& $infoRepo[$infoPaquete['repo']];
                $regs = NULL;
                if (!preg_match('/^((\S+):)?(\S+)-(\S+)$/', $infoPaquete['version'], $regs)) {
                    $this->_estadoPaquete['status'] = 'error';
                    $this->_estadoPaquete['errores'][] = "Unable to parse version string for package: ".$infoPaquete['nombre'];
                } else {
                    $sEpoch = ($regs[2] == "") ? 0 : $regs[2];
                    $sVersion = $regs[3];
                    $sRelease = $regs[4];
                    
                    // Abrir la conexión a la base de datos
                    $dsn = "sqlite3:///".$repo['ruta'].$repo['primary_db'];
                    $oDB = new paloDB($dsn);
                    if ($oDB->connStatus) {
                        $this->_estadoPaquete['status'] = 'error';
                        $this->_estadoPaquete['errores'][] = "Unable to open primary_db for package: ".$infoPaquete['nombre'];
                    } else {
                        // select size_package from packages where name = "pidgin" and arch = "x86_64" and epoch = "0" and version = "2.6.6" and release = "1.el5"
                        $sql =
                            'SELECT size_package, location_href FROM packages '.
                            'WHERE name = ? AND arch = ? AND epoch = ? AND version = ? AND release = ?';
                        $recordset = $oDB->fetchTable($sql, FALSE, array(
                            $infoPaquete['nombre'],
                            $infoPaquete['arch'],
                            $sEpoch,
                            $sVersion,
                            $sRelease,
                        ));
                        if (!is_array($recordset)) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Unable to query primary_db for package: ".$infoPaquete['nombre'];
                        } elseif (count($recordset) <= 0) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Unable to locate package in primary_db for package: ".$infoPaquete['nombre'].
                                  " $infoPaquete[arch] $sEpoch $sVersion $sRelease";
                        } elseif (count($recordset) > 1) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Duplicate package information in primary_db for package: ".$infoPaquete['nombre'];
                        } else {
                            $infoPaquete['longitud'] = $recordset[0][0];
                            if ($infoPaquete['pkgaction'] != 'remove') 
                                $infoPaquete['descargado'] = 0;
                            $regs = NULL;
                            if (preg_match('|^((.*)/)?(\S+\.rpm)$|', $recordset[0][1], $regs)) {
                                $infoPaquete['rpmfile'] = $repo['ruta'].'packages/'.$regs[3];
                            } else {
                                $this->_estadoPaquete['status'] = 'error';
                                $this->_estadoPaquete['errores'][] = "Unable to discover RPM filename for package: ".$infoPaquete['nombre'];
                            }                            
                        }

                        $oDB->disconnect();
                    }
                }
            }
        }
    }

    private function _procesarAdd(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";
        
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'reporefresh';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();

        $sComando = "ts list\ninstall ".implode(' ', $listaArgs)."\nts solve\nts list\n";
        fwrite($this->_procPipes[0], $sComando);
        return "OK Processing\n";
    }

    private function _procesarUpdate(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";
        
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'reporefresh';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $sComando = "ts list\nupdate ".implode(' ', $listaArgs)."\nts solve\nts list\n";
        fwrite($this->_procPipes[0], $sComando);
        return "OK Processing\n";
    }

    private function _procesarCheck(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";

        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'checkinstalled';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $sComando =  "list ".implode(' ', $listaArgs)."\nts list\n";
        fwrite($this->_procPipes[0], $sComando);
        return "OK Processing\n";
    }

    private function _procesarClear(&$listaArgs)
    {
        if ($this->_estadoPaquete['status'] == 'busy')
            return "ERR Invalid status\n";
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'idle';
        $this->_estadoPaquete['action'] = 'none';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['progreso'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $sComando = "ts reset\nts list\n";
        fwrite($this->_procPipes[0], $sComando);
        return "OK\n";
    }
    
    private function _procesarCancel(&$listaArgs)
    {
        if ($this->_estadoPaquete['status'] != 'busy')
            return "ERR Nothing to cancel\n";
        if ($this->_estadoPaquete['action'] != 'downloading')
            return "ERR Cannot cancel\n";

        // YUM requiere dos SIGINT para cancelar una descarga. El primero se 
        // envía aquí. El segundo se envía en _actualizarEstadoYumShell() al 
        // detectar la cadena de aviso de ctrl-c.
        $infoYum = proc_get_status($this->_procYum);
        posix_kill($infoYum['pid'], SIGINT);
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'cancelling';

        return  "OK Cancelled\n";
    }
    
    private function _procesarConfirm(&$listaArgs)
    {
        if ($this->_estadoPaquete['status'] != 'idle' || $this->_estadoPaquete['action'] != 'confirm')
            return "ERR Invalid status\n";

        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'downloading';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();

        $sComando = "run\ny\n";
        fwrite($this->_procPipes[0], $sComando);
        $this->_activarCapturaStderr();
        return "OK Starting transaction...\n";
    }

    private function _actualizarEstadoYumShell()
    {
        $s = stream_get_contents($this->_procPipes[1]);
        $this->_sContenido .= $s;
        if ($s == '') {
	        $this->oMainLog->output("ERR: fin no esperado de yum!");
	        return FALSE;
        }
        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
        $this->oMainLog->output("yum(stdout): $s");
        
        if ($this->_estadoPaquete['status'] == 'busy') {
            switch ($this->_estadoPaquete['action']) {
            case 'cancelling':
                // Segundo SIGINT (véase _procesarCancel() para explicación)
                if (FALSE !== strpos($this->_sContenido, 'Current download cancelled, interrupt (ctrl-c) again within two seconds to exit')) {
                    $infoYum = proc_get_status($this->_procYum);
                    posix_kill($infoYum['pid'], SIGINT);
                    $this->_estadoPaquete = array(
                        'status'    =>  'idle',
                        'action'    =>  'none',

                        'progreso'  =>  array(),
                        'instalado' =>  array(),
                        'errores'   =>  array(),
                        'warning'   =>  array(),
                    );
                }
                $this->_inactivarCapturaStderr();
                break;
            case 'checkinstalled':
                // Se revisa si un paquete en particular está instalado
                $pos = strpos($this->_sContenido, "Transaction Summary");
                if ($pos !== FALSE) {
                    $this->_estadoPaquete['status'] = 'idle';
                    $lineas = explode("\n", $this->_sContenido);
                    $bReporteInstalado = FALSE;
                    foreach ($lineas as $sLinea) {
                        if (strpos($sLinea, 'Installed Packages') !== FALSE) {
                            $bReporteInstalado = TRUE;
                        } elseif (strpos($sLinea, 'Available Packages') === 0 || strpos($sLinea, 'Transaction Summary') === 0) {
                            $bReporteInstalado = FALSE;
                        } elseif ($bReporteInstalado) {
                            $regs = NULL;
                            if (preg_match('/^(\S+)\.(\S+)\s+((\S+):)?(\S+)-(\S+)\s+installed/', $sLinea, $regs)) {
                                $this->_estadoPaquete['instalado'][] = array(
                                    'nombre'    =>  $regs[1],
                                    'arch'      =>  $regs[2],
                                    'epoch'     =>  ($regs[4] == '') ? 0 : $regs[4],
                                    'version'   =>  $regs[5],
                                    'release'   =>  $regs[6],
                                );
                            }
                        }
                    }                    
                }
                break;
            case 'reporefresh':
                // Se inicia refresco de repos para poder realizar resolución de dependencias...
                $pos = strpos($this->_sContenido, "Transaction Summary");
                if ($pos !== FALSE) {
                    $this->_estadoPaquete['action'] = 'depsolving';
                }
                break;
            case 'depsolving':
                // Realizando resolución de dependencias
                $pos = strpos($this->_sContenido, "Success resolving dependencies");
                if ($pos !== FALSE) {
                    $pos2 = strpos($this->_sContenido, "Transaction Summary", $pos);
                    if ($pos2 !== FALSE) {
                        // Ya es seguro recolectar los paquetes que conforman la transacción
                        $this->_estadoPaquete['status'] = 'idle';
                        $this->_estadoPaquete['action'] = 'confirm';
                        $this->_recogerPaquetesTransaccion();
                    }
                }
                break;
            case 'downloading':
                // Descargando paquetes. Se monitorea el tamaño del archivo RPM descargado
                $this->_revisarProgresoPaquetes();
                if (strpos($this->_sContenido, 'Running Transaction Test') !== FALSE) {
                    $this->_estadoPaquete['action'] = 'applying';
                }
                break;
            case 'applying':
                // Aplicando la transacción
                $lineas = explode("\n", $this->_sContenido);
                $iPosPaquete = NULL;
                
                // Resetear el estado de todos los paquetes
                foreach ($this->_estadoPaquete['progreso'] as &$infoPaquete) {
                    if ($infoPaquete['pkgaction'] != 'remove') $infoPaquete['currstatus'] = 'downloaded';
                }
                
                // Verificar cada una de las líneas de instalación
                foreach ($lineas as $sLinea) {
                    $regs = NULL;
                    if (preg_match('/^\s+Installing\s+:\s+(\S+)/', $sLinea, $regs)) {
                        // Instalando un paquete
                        foreach ($this->_estadoPaquete['progreso'] as $iPos => &$infoPaquete) {
                            if ($infoPaquete['nombre'] == $regs[1] && 
                                $infoPaquete['pkgaction'] == 'install' && 
                                $infoPaquete['currstatus'] == 'downloaded') {
                                if (!is_null($iPosPaquete)) {
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'installing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'installed';
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'removing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'removed';
                                }
                                $iPosPaquete = $iPos;
                                $infoPaquete['currstatus'] = 'installing';
                                break;
                            }
                        }
                    } elseif (preg_match('/^\s+Updating\s+:\s+(\S+)/', $sLinea, $regs)) {
                        // Actualizando un paquete
                        foreach ($this->_estadoPaquete['progreso'] as $iPos => &$infoPaquete) {
                            if ($infoPaquete['nombre'] == $regs[1] && 
                                $infoPaquete['pkgaction'] == 'update' && 
                                $infoPaquete['currstatus'] == 'downloaded') {
                                if (!is_null($iPosPaquete)) {
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'installing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'installed';
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'removing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'removed';
                                }
                                $iPosPaquete = $iPos;
                                $infoPaquete['currstatus'] = 'installing';
                                break;
                            }
                        }
                    } elseif (preg_match('/^\s+Erasing\s+:\s+(\S+)/', $sLinea, $regs)) {
                        // Removiendo un paquete
                        foreach ($this->_estadoPaquete['progreso'] as $iPos => &$infoPaquete) {
                            if ($infoPaquete['nombre'] == $regs[1] && 
                                $infoPaquete['pkgaction'] == 'remove' && 
                                $infoPaquete['currstatus'] == 'installed') {
                                if (!is_null($iPosPaquete)) {
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'removing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'removed';
                                }
                                $iPosPaquete = $iPos;
                                $infoPaquete['currstatus'] = 'removing';
                                break;
                            }
                        }
                    } elseif (strpos($sLinea, 'Finished Transaction') === 0 && strpos($sLinea, 'Finished Transaction Test') === FALSE) {
                        $this->_estadoPaquete['status'] = 'idle';
                        $this->_estadoPaquete['action'] = 'none';
                        $this->_estadoPaquete['progreso'] = array();
                        $this->_estadoPaquete['errores'] = array();
                        $this->_estadoPaquete['warning'] = array();
                        $this->_inactivarCapturaStderr();
                    }
                }                
                break;
            }
        }

        return TRUE;
    }

    private function _actualizarStderrYumShell()
    {
        $s = stream_get_contents($this->_procPipes[2]);
        if ($this->_bCapturarStderr) {
            $this->_stderrBuf .= $s;
        }
        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
        $this->oMainLog->output("yum(stderr): $s");
        
        if ($this->_bCapturarStderr) switch ($this->_estadoPaquete['action']) {
        case 'downloading':
            // Buscar si yum ha terminado de descargar por errores
            $lineas = explode("\n", $this->_stderrBuf);
            $bDownloadError = FALSE;
            foreach ($lineas as $sLinea) {
                if (0 === strpos($sLinea, 'Error: Error Downloading Packages:')) {
                    $bDownloadError = TRUE;
                    $this->_estadoPaquete['status'] = 'error';
                    $this->_estadoPaquete['action'] = 'none';
                    $this->_estadoPaquete['progreso'] = array();
                    $this->_estadoPaquete['errores'] = array();
                    $this->_estadoPaquete['warning'] = array();

                    // Esto asume que el contenido luego del mensaje no está fragmentado
                    $this->_inactivarCapturaStderr();
                } elseif ($bDownloadError) {
                    if (trim($sLinea) != '') $this->_estadoPaquete['errores'][] = $sLinea;
                }
            }
            break;
        }
    }

    private function _revisarProgresoPaquetes()
    {
        clearstatcache();
        foreach ($this->_estadoPaquete['progreso'] as &$infoPaquete) {
            if ($infoPaquete['pkgaction'] != 'remove') {
                if (file_exists($infoPaquete['rpmfile'])) {
                    $infoPaquete['descargado'] = filesize($infoPaquete['rpmfile']);
                    $infoPaquete['currstatus'] = ($infoPaquete['descargado'] < $infoPaquete['longitud']) ? 'downloading' : 'downloaded';
                } else {
                    $infoPaquete['descargado'] = 0;
                    $infoPaquete['currstatus'] = 'waiting';
                }
            }
        }
    }

    private function _activarCapturaStderr()
    {
        $this->_bCapturarStderr = TRUE;
        $this->_stderrBuf = '';
    }
    
    private function _inactivarCapturaStderr()
    {
        $this->_bCapturarStderr = FALSE;
        $this->_stderrBuf = '';
    }
    
    private function _procesarRemove(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";
        
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'reporefresh';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $sComando = "ts list\nerase ".implode(' ', $listaArgs)."\nts solve\nts list\n";
        fwrite($this->_procPipes[0], $sComando);
        return "OK Processing\n";
    }
}
?>
