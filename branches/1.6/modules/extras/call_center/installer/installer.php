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
  $Id:  $ */
$DocumentRoot = "/var/www/html";

require_once "$DocumentRoot/libs/paloSantoInstaller.class.php";
include_once("$DocumentRoot/libs/paloSantoDB.class.php");

$tmpDir = '/tmp/new_module';  # in this folder the load module extract the package content
#generar el archivo db de campañas
$return=1;
$path_script_db="$tmpDir/installer/call_center.sql";
$datos_conexion['user'] = "asterisk";
$datos_conexion['password'] = "asterisk";
$datos_conexion['locate'] = "";
$oInstaller = new Installer();

if (file_exists($path_script_db))
{
    //STEP 1: Create database call_center
    $return=0;
    $return=$oInstaller->createNewDatabaseMySQL($path_script_db,"call_center",$datos_conexion);

    $pDB = new paloDB ('mysql://root:'.MYSQL_ROOT_PASSWORD.'@localhost/call_center');
    quitarColumnaSiExiste($pDB, 'call_center', 'agent', 'queue');
    crearColumnaSiNoExiste($pDB, 'call_center', 'calls', 
        'dnc', 
        "ADD COLUMN dnc int(1) NOT NULL DEFAULT '0'");
    crearColumnaSiNoExiste($pDB, 'call_center', 'call_entry', 
        'id_campaign', 
        "ADD COLUMN id_campaign  int unsigned, ADD FOREIGN KEY (id_campaign) REFERENCES campaign_entry (id)");
    crearColumnaSiNoExiste($pDB, 'call_center', 'calls', 
        'date_init', 
        "ADD COLUMN date_init  date, ADD COLUMN date_end  date, ADD COLUMN time_init  time, ADD COLUMN time_end  time");
    crearColumnaSiNoExiste($pDB, 'call_center', 'calls', 
        'agent', 
        "ADD COLUMN agent varchar(32)");
    crearColumnaSiNoExiste($pDB, 'call_center', 'call_entry', 
        'trunk', 
        "ADD COLUMN trunk varchar(20) NOT NULL");
    crearColumnaSiNoExiste($pDB, 'call_center', 'calls', 
        'failure_cause', 
        "ADD COLUMN failure_cause int(10) unsigned default null, ADD COLUMN failure_cause_txt varchar(32) default null");
    crearColumnaSiNoExiste($pDB, 'call_center', 'calls', 
        'datetime_originate', 
        "ADD COLUMN datetime_originate datetime default NULL");
    crearColumnaSiNoExiste($pDB, 'call_center', 'agent', 
        'eccp_password', 
        "ADD COLUMN eccp_password varchar(128) default NULL");
    crearColumnaSiNoExiste($pDB, 'call_center', 'campaign', 
        'id_url', 
        "ADD COLUMN id_url int unsigned, ADD FOREIGN KEY (id_url) REFERENCES campaign_external_url (id)");
    crearColumnaSiNoExiste($pDB, 'call_center', 'campaign_entry', 
        'id_url', 
        "ADD COLUMN id_url int unsigned, ADD FOREIGN KEY (id_url) REFERENCES campaign_external_url (id)");

    // Asegurarse de que todo agente tiene una contraseña de ECCP
    $pDB->genQuery('UPDATE agent SET eccp_password = SHA1(CONCAT(NOW(), RAND(), number)) WHERE eccp_password IS NULL');

    $pDB->disconnect();

    //STEP 2: Dialer process
    exec("sudo -u root chmod 777 /opt/",$arrConsole,$flagStatus);
    exec("mkdir -p /opt/elastix/dialer/",$arrConsole,$flagStatus);
    exec("mv -f $tmpDir/dialer_process/dialer/* /opt/elastix/dialer/",$arrConsole,$flagStatus);
    exec("mv -f $tmpDir/dialer_process/CHANGELOG /opt/elastix/dialer/",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /opt/",$arrConsole,$flagStatus);
 
    // STEP 3: logrotate configuration
    exec("sudo -u root chmod 777 /etc/logrotate.d/",$arrConsole,$flagStatus);
    exec("mv -f $tmpDir/installer/elastixdialer.logrotate /etc/logrotate.d/elastixdialer",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /etc/logrotate.d/",$arrConsole,$flagStatus);

    // STEP 4: init script
    exec("sudo -u root chmod 777 /etc/rc.d/init.d/",$arrConsole,$flagStatus);
    exec("mv $tmpDir/dialer_process/elastixdialer /etc/rc.d/init.d/",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /etc/rc.d/init.d/",$arrConsole,$flagStatus);
    exec("sudo -u root chkconfig --add elastixdialer");
    exec("sudo -u root chkconfig --level 2345 elastixdialer on");
    $return = ($flagStatus)?2:0;
}

instalarContextosEspeciales();

exit($return);

function quitarColumnaSiExiste($pDB, $sDatabase, $sTabla, $sColumna)
{
    $sPeticionSQL = <<<EXISTE_COLUMNA
SELECT COUNT(*)
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
EXISTE_COLUMNA;
    $r = $pDB->getFirstRowQuery($sPeticionSQL, FALSE, array($sDatabase, $sTabla, $sColumna));
    if (!is_array($r)) {
    	fputs(STDERR, "ERR: al verificar tabla $sTabla.$sColumna - ".$pDB->errMsg."\n");
        return;
    }
    if ($r[0] > 0) {
        fputs(STDERR, "INFO: Se encuentra $sTabla.$sColumna en base de datos $sDatabase, se ejecuta:\n");
        $sql = "ALTER TABLE $sTabla DROP COLUMN $sColumna";
        fputs(STDERR, "\t$sql\n");
        $r = $pDB->genQuery($sql);
        if (!$r) fputs(STDERR, "ERR: ".$pDB->errMsg."\n");
    } else {
        fputs(STDERR, "INFO: No existe $sTabla.$sColumna en base de datos $sDatabase. No se hace nada.\n");
    }
}

function crearColumnaSiNoExiste($pDB, $sDatabase, $sTabla, $sColumna, $sColumnaDef)
{
	$sPeticionSQL = <<<EXISTE_COLUMNA
SELECT COUNT(*) 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
EXISTE_COLUMNA;
    $r = $pDB->getFirstRowQuery($sPeticionSQL, FALSE, array($sDatabase, $sTabla, $sColumna));
    if (!is_array($r)) {
        fputs(STDERR, "ERR: al verificar tabla $sTabla.$sColumna - ".$pDB->errMsg."\n");
        return;
    }
    if ($r[0] <= 0) {
    	fputs(STDERR, "INFO: No se encuentra $sTabla.$sColumna en base de datos $sDatabase, se ejecuta:\n");
        $sql = "ALTER TABLE $sTabla $sColumnaDef";
        fputs(STDERR, "\t$sql\n");
        $r = $pDB->genQuery($sql);
        if (!$r) fputs(STDERR, "ERR: ".$pDB->errMsg."\n");
    } else {
    	fputs(STDERR, "INFO: Ya existe $sTabla.$sColumna en base de datos $sDatabase.\n");
    }
}

/**
 * Procedimiento que instala algunos contextos especiales requeridos para algunas
 * funcionalidades del CallCenter.
 */
function instalarContextosEspeciales()
{
    $sArchivo = '/etc/asterisk/extensions_custom.conf';
    $sInicioContenido = "; BEGIN ELASTIX CALL-CENTER CONTEXTS DO NOT REMOVE THIS LINE\n";
    $sFinalContenido =  "; END ELASTIX CALL-CENTER CONTEXTS DO NOT REMOVE THIS LINE\n";
    
    // Cargar el archivo, notando el inicio y el final del área de contextos de callcenter
    $bEncontradoInicio = $bEncontradoFinal = FALSE;
    $contenido = array();
    foreach (file($sArchivo) as $sLinea) {
        if ($sLinea == $sInicioContenido) {
            $bEncontradoInicio = TRUE;
        } elseif ($sLinea == $sFinalContenido) {
            $bEncontradoFinal = TRUE;
        } elseif (!$bEncontradoInicio || $bEncontradoFinal) {
            if (substr($sLinea, strlen($sLinea) - 1) != "\n")
                $sLinea .= "\n";
            $contenido[] = $sLinea;
        }
    }
    if ($bEncontradoInicio xor $bEncontradoFinal) {
        fputs(STDERR, "ERR: no se puede localizar correctamente segmento de contextos de Call Center\n");
    } else {
        $contenido[] = $sInicioContenido;
        $contenido[] = <<<CONTEXTOS_CALLCENTER

[llamada_agendada]
exten => _X.,1,NoOP("NUMERO DE AGENTE -------------- \${EXTEN}")
exten => _X.,n,Dial(Agent/\${EXTEN},300,t)


CONTEXTOS_CALLCENTER;
        $contenido[] = $sFinalContenido;
        file_put_contents($sArchivo, $contenido);
        chown($sArchivo, 'asterisk'); chgrp($sArchivo, 'asterisk');
    }
}
?>