<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.6-6                                               |
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
  $Id: paloSantoEmailRelay.class.php,v 1.1 2010-07-21 01:08:56 Bruno Macias bmacias@palosanto.com Exp $ */
class paloSantoEmailRelay {
    var $_DB;
    var $errMsg;

    function paloSantoEmailRelay(&$pDB)
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

    function getMainConfigByAll()
    {
        $query  = "SELECT id, name, value FROM email_relay ";
        $result = $this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }

        $arrData = null;
        if(is_array($result) && count($result)>0){
            foreach($result as $k => $data)
                $arrData[$data['name']] = $data['value'];
        }
        return $arrData;
    }

    function processUpdateConfiguration($arrData)
    {
        $this->_DB->beginTransaction();

        if($this->processUpdateConfigurationDB($arrData)){
            if($this->processUpdateConfigurationFile($arrData)){
                $this->_DB->commit();
                return true;
            }
            else{
                $this->_DB->rollBack();
                return false;
            }
        }
        else{
            $this->_DB->rollBack();
            return false;
        }
    }

    function processUpdateConfigurationDB($arrData)
    {
        if(is_array($arrData) && count($arrData)>0){
            $query = "delete from email_relay;";
            $ok = $this->_DB->genQuery($query);

            if(!$ok){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }
            foreach($arrData as $name => $value){
                $query = "insert into email_relay(name,value) values('$name','$value');";
                $ok = $this->_DB->genQuery($query);

                if(!$ok){
                    $this->errMsg = $this->_DB->errMsg;
                    return false;
                }
            }
        }
        return true;
    }

    function processUpdateConfigurationFile($arrData)
    {
        if(is_array($arrData) && count($arrData)>0){
            $activated = $arrData['status'];

            $arrReplaces['relayhost'] = ($activated)?$arrData['relayhost']:"";

            if($arrData['port']!="")
                $arrReplaces['relayhost'] = ($activated)?"$arrData[relayhost]:$arrData[port]":"";

            if($arrData['user'] && $arrData['password']){
                $arrReplaces['smtp_sasl_auth_enable']      = ($activated)?"yes":"no"; // default no
                $arrReplaces['smtp_sasl_password_maps']    = ($activated)?"hash:/etc/postfix/sasl/passwd":""; // default ""
                $arrReplaces['smtp_sasl_security_options'] = ($activated)?"":"noplaintext, noanonymous"; //default noplaintext, noanonymous
                $arrReplaces['broken_sasl_auth_clients']   = ($activated)?"yes":"no";// default no

                $this->createSASL();
                $data = ($activated)?"$arrData[relayhost] $arrData[user]:$arrData[password]":"";
                $this->writeSASL($data);
            }
            else{
                $arrReplaces['smtp_sasl_auth_enable']      = "no";
                $arrReplaces['smtp_sasl_password_maps']    = "";
                $arrReplaces['smtp_sasl_security_options'] = "noplaintext, noanonymous";
                $arrReplaces['broken_sasl_auth_clients']   = "no";

                $this->createSASL();
                $data = "";
                $this->writeSASL($data);
            }

            $conf_file = new paloConfig("/etc/postfix","main.cf"," = ","[[:space:]]*=[[:space:]]*");
            $bValido   = $conf_file->escribir_configuracion($arrReplaces);

            if($bValido){
                $this->restartingServices();
            }
        }
        return true;
    }

    function setStatus($status)
    {
        // Existe name status
        $query  = "select count(*) existe from email_relay where name='status';";
        $result = $this->_DB->getFirstRowQuery($query,true);

        if(is_array($result) && count($result) >0){
            if($result['existe']==1){
                $query = "update email_relay set value='$status' where name='status';";
                $ok = $this->_DB->genQuery($query);
            }
            else{
                $query = "insert into email_relay(name,value) values('status','$status');";
                $ok = $this->_DB->genQuery($query);
            }

            if(!$ok){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }
            return true;
        }
        else{
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
    }

    function getStatus()
    {
        // Existe name status
        $query  = "select value from email_relay where name='status';";
        $result = $this->_DB->getFirstRowQuery($query,true);

        if(is_array($result) && count($result) >0)
            return $result['value'];
        else return 0;
    }

    function createSASL()
    {
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/");
        exec("mkdir /etc/postfix/sasl");
        exec("touch /etc/postfix/sasl/passwd");
        exec("chmod 600 /etc/postfix/sasl/passwd");
        exec("sudo -u root chown -R root.root /etc/postfix/");
    }


    function writeSASL($data)
    {
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/");
        exec("echo '$data' > /etc/postfix/sasl/passwd");
        exec("postmap /etc/postfix/sasl/passwd");
        exec("sudo -u root chown -R root.root /etc/postfix/");
    }

    function restartingServices(){
        //se ejecuta de esa forma porque es usuario asterisk el que corre el programa de elastix
        exec("sudo /sbin/service saslauthd restart");
        exec("sudo /sbin/service postfix restart");
    }
}
?>
