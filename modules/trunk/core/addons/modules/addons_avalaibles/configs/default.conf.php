<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-15                                               |
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
  $Id: default.conf.php,v 1.1 2010-03-08 12:03:02 Bruno Macias bomv.27@gmail.com Exp $ */
    global $arrConf;
    global $arrConfModule;

    $arrConfModule['module_name']      = 'addons_avalaibles';
    $arrConfModule['templates_dir']    = 'themes';
    $arrConfModule['dsn_conn_database'] = "sqlite3:///$arrConf[elastix_dbdir]/addons.db";
    $arrConfModule['socket_conn_ip']   = "192.168.1.110";
    $arrConfModule['socket_conn_port'] = "20004";
    $arrConfModule['url_webservice']   = 'http://192.168.1.80/modules/addons_availables/webservice/addons.wsdl';
    $arrConfModule['url_images']       = 'http://192.168.1.80/modules/addons_availables/images';
?>