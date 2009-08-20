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

    //STEP 2: Dialer process
    exec("sudo -u root chmod 777 /opt/",$arrConsole,$flagStatus);
    exec("mkdir -p /opt/elastix/dialer/",$arrConsole,$flagStatus);
    exec("mv -f $tmpDir/dialer_process/dialer/* /opt/elastix/dialer/",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /opt/",$arrConsole,$flagStatus);
 
    exec("sudo -u root chmod 777 /etc/rc.d/init.d/",$arrConsole,$flagStatus);
    exec("mv $tmpDir/dialer_process/elastixdialer /etc/rc.d/init.d/",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /etc/rc.d/init.d/",$arrConsole,$flagStatus);
    $return = ($flagStatus)?2:0;
}

exit($return);
?>
