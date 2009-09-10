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
  $Id: paloSantoEmailRelay.class.php,v 1.1 2009-08-07 01:08:56 Oscar Navarrete onavarrete@palosanto.com Exp $ */
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

    /*HERE YOUR FUNCTIONS*/

    function init(){
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/");
        exec("mkdir /etc/postfix/sasl");
        exec("touch /etc/postfix/sasl/passwd");
        exec("chmod 600 /etc/postfix/sasl/passwd");

        exec("mkdir /etc/postfix/tls");
        exec("sudo -u root chown -R root.root /etc/postfix/");
    }


    function readFileMainCF()
    {
        $myFile='/etc/postfix/main.cf';
        $fh = fopen($myFile, 'r');
        return $fh;
    }

    function saveChangeFileMainCF($text){
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/");
        $archivo = file('/etc/postfix/main.cf');
         
        $fp = fopen('/etc/postfix/main.cf', 'w');
        fwrite($fp, $text);   
        exec("sudo -u root chown -R root.root /etc/postfix/");
        fclose($fp);
    }

    /*Cuando el archivo main.cf es original sin modificaciones*/
    function execConfigPosfix_1($host, $port, $user, $password){
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/");
        //exec("mkdir /etc/postfix/sasl");
        //exec("touch /etc/postfix/sasl/passwd");
        //exec("chmod 600 /etc/postfix/sasl/passwd");
        //exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/sasl/");
        //exec("sudo -u root chown asterisk.asterisk /etc/postfix/sasl/passwd.db");
        exec("echo '[smtp.$host.com]:$port    $user@$host.com:$password' > /etc/postfix/sasl/passwd");
        exec("postmap /etc/postfix/sasl/passwd");
        exec("sudo -u root chown -R root.root /etc/postfix/");
    }

    /*Cuando el archivo main.cf es original ya ha sido modificado*/
    function execConfigPosfix_1Mod($host, $port, $user, $password){
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/");
        exec("echo '[smtp.$host.com]:$port    $user@$host.com:$password' > /etc/postfix/sasl/passwd");
        exec("postmap /etc/postfix/sasl/passwd");
        exec("sudo -u root chown -R root.root /etc/postfix/");
    }


    function replaceFileMainCF($host, $port, $data_step1, $arr_MainCF){
        $FILE='/etc/postfix/main.cf';
        $text = "";
        $fp = fopen($FILE,'r');
        $data = split('[:]',$arr_MainCF[0]);
        if(!empty($data[1]))  $filter = $data[1];
        else $filter = "gateway.";
        $found=false;

        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($filter, $line)){
                $line = str_ireplace($arr_MainCF[0], "[smtp.".$host.".com]:$port", $line);
                $line = str_ireplace("#relayhost", "relayhost", $line);
                $text .= $line;
            }/*elseif(eregi($arr_MainCF[1], $line)){
                $line = str_ireplace($arr_MainCF[1], $host.".com", $line);
                $text .= $line; 
            }*/elseif(eregi("smtp_sasl_auth_enable ", $line)){
                $line = str_ireplace($arr_MainCF[2], $data_step1['smtp_sasl_auth_enable'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtp_sasl_password_maps ", $line)){
                $line = str_ireplace($arr_MainCF[3], $data_step1['smtp_sasl_password_maps'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtp_sasl_security_options ", $line)){
                $line = str_ireplace($arr_MainCF[4], $data_step1['smtp_sasl_security_options'], $line);
                $text .= $line;
                $found=true;
            }else{
                $text .= $line;
            }
        }

        if($found==false){
            $text .="smtp_sasl_auth_enable = ".$data_step1['smtp_sasl_auth_enable']."\n";
            $text .="smtp_sasl_password_maps = ".$data_step1['smtp_sasl_password_maps']."\n";
            $text .="smtp_sasl_security_options = ".$data_step1['smtp_sasl_security_options']."\n";
        }

        $this->saveChangeFileMainCF($text);
        fclose($fp);
    }


    function execConfigPosfix_2($password, $countryName, $ProvinceName, $localityName, $organizationName, $organizationalUnitName, $commonName){      
        exec("postfix reload");
        //exec("mkdir /etc/postfix/tls");
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/");
        //exec("echo "$contrasenia" | openssl genrsa -des3 -rand /etc/hosts -passout stdin -out smtpd.key 1024");
        exec("openssl genrsa -des3 -rand /etc/hosts -passout pass:$password -out /etc/postfix/tls/smtpd.key 1024");
        //exec("openssl genrsa -des3 -rand /etc/hosts -passout pass:Hola -out /etc/postfix/tls2/smtpd.key 1024");

        //exec("openssl req -new -key /etc/postfix/tls2/smtpd.key -passin pass:Hola -out /etc/postfix/tls2/smtpd.csr -subj '/C=EC/ST=Guayas/L=Guayaquil/O=Megatelcon/OU=Desarrollo/CN=elastix.palosanto.com'");
        exec("openssl req -new -key /etc/postfix/tls/smtpd.key -passin pass:$password -out /etc/postfix/tls/smtpd.csr -subj '/C=$countryName/ST=$ProvinceName/L=$localityName/O=$organizationName/OU=$organizationalUnitName/CN=$commonName'");
        
        //exec("openssl x509 -req -days 3650 -in /etc/postfix/tls2/smtpd.csr -signkey /etc/postfix/tls2/smtpd.key -passin pass:Hola -out /etc/postfix/tls2/smtpd.crt");
        exec("openssl x509 -req -days 3650 -in /etc/postfix/tls/smtpd.csr -signkey /etc/postfix/tls/smtpd.key -passin pass:$password -out /etc/postfix/tls/smtpd.crt");

        //exec("openssl rsa -in /etc/postfix/tls2/smtpd.key -passin pass:Hola -out /etc/postfix/tls2/smtpd.key.unencrypted");
        exec("openssl rsa -in /etc/postfix/tls/smtpd.key -passin pass:$password /etc/postfix/tls/smtpd.key.unencrypted");

        exec("mv -f /etc/postfix/tls/smtpd.key.unencrypted /etc/postfix/tls/smtpd.key");

        //exec("openssl req -new -x509 -extensions v3_ca -keyout /etc/postfix/tls2/cakey.pem -passout pass:12345 -out /etc/postfix/tls2/cacert.pem -days 3650 -subj '/C=EC/ST=Guayas/L=Guayaquil/O=Megatelcon/OU=Desarrollo/CN=elastix.palosanto.com'");
        exec("openssl req -new -x509 -extensions v3_ca -keyout /etc/postfix/tls/cakey.pem -passout pass:$password -out /etc/postfix/tls/cacert.pem -days 3650 -subj '/C=$countryName/ST=$ProvinceName/L=$localityName/O=$organizationName/OU=$organizationalUnitName/CN=$commonName'");
        exec("sudo -u root chown -R root.root /etc/postfix/");
    }

    function execConfigPosfix_2Mod($password, $countryName, $ProvinceName, $localityName, $organizationName, $organizationalUnitName, $commonName){
        exec("postfix reload");
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/");
        exec("openssl genrsa -des3 -rand /etc/hosts -passout pass:$password -out /etc/postfix/tls/smtpd.key 1024");
        exec("openssl req -new -key /etc/postfix/tls/smtpd.key -passin pass:$password -out /etc/postfix/tls/smtpd.csr -subj '/C=$countryName/ST=$ProvinceName/L=$localityName/O=$organizationName/OU=$organizationalUnitName/CN=$commonName'");
        exec("openssl x509 -req -days 3650 -in /etc/postfix/tls/smtpd.csr -signkey /etc/postfix/tls/smtpd.key -passin pass:$password -out /etc/postfix/tls/smtpd.crt");
        exec("openssl rsa -in /etc/postfix/tls/smtpd.key -passin pass:$password /etc/postfix/tls/smtpd.key.unencrypted");
        exec("mv -f /etc/postfix/tls/smtpd.key.unencrypted /etc/postfix/tls/smtpd.key");
        exec("openssl req -new -x509 -extensions v3_ca -keyout /etc/postfix/tls/cakey.pem -passout pass:$password -out /etc/postfix/tls/cacert.pem -days 3650 -subj '/C=$countryName/ST=$ProvinceName/L=$localityName/O=$organizationName/OU=$organizationalUnitName/CN=$commonName'");
        exec("sudo -u root chown -R root.root /etc/postfix/");
    }

    function replaceFileMainCF_2($data_step2, $arr_MainCF){
        $FILE='/etc/postfix/main.cf';
        $text = "";
        $fp = fopen($FILE,'r');

        $found=false;

        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("smtpd_tls_auth_only ", $line)){
                $line = str_ireplace($arr_MainCF[5], $data_step2['smtpd_tls_auth_only'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtp_use_tls ", $line)){
                $line = str_ireplace($arr_MainCF[6], $data_step2['smtp_use_tls'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtpd_use_tls ", $line)){
                $line = str_ireplace($arr_MainCF[7], $data_step2['smtpd_use_tls'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtp_tls_note_starttls_offer ", $line)){
                $line = str_ireplace($arr_MainCF[8], $data_step2['smtp_tls_note_starttls_offer'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtpd_tls_key_file ", $line)){
                $line = str_ireplace($arr_MainCF[9], $data_step2['smtpd_tls_key_file'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtpd_tls_cert_file ", $line)){
                $line = str_ireplace($arr_MainCF[10], $data_step2['smtpd_tls_cert_file'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtp_tls_CAfile ", $line)){
                $line = str_ireplace($arr_MainCF[11], $data_step2['smtp_tls_CAfile'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtpd_tls_loglevel ", $line)){
                $line = str_ireplace($arr_MainCF[12], $data_step2['smtpd_tls_loglevel'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtpd_tls_received_header ", $line)){
                $line = str_ireplace($arr_MainCF[13], $data_step2['smtpd_tls_received_header'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("smtpd_tls_session_cache_timeout ", $line)){
                $line = str_ireplace($arr_MainCF[14], $data_step2['smtpd_tls_session_cache_timeout'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("tls_random_source ", $line)){
                $line = str_ireplace($arr_MainCF[15], $data_step2['tls_random_source'], $line);
                $text .= $line;
                $found=true;
            }elseif(eregi("tls_daemon_random_source ", $line)){
                $line = str_ireplace($arr_MainCF[16], $data_step2['tls_daemon_random_source'], $line);
                $text .= $line;
                $found=true;
            }else{
                $text .= $line;
            }
        }

        if($found==false){
            $text .="smtpd_tls_auth_only = ".$data_step2['smtpd_tls_auth_only']."\n";
            $text .="smtp_use_tls = ".$data_step2['smtp_use_tls']."\n";
            $text .="smtpd_use_tls = ".$data_step2['smtpd_use_tls']."\n";
            $text .="smtp_tls_note_starttls_offer = ".$data_step2['smtp_tls_note_starttls_offer']."\n";
            $text .="smtpd_tls_key_file = ".$data_step2['smtpd_tls_key_file']."\n";
            $text .="smtpd_tls_cert_file = ".$data_step2['smtpd_tls_cert_file']."\n";
            $text .="smtp_tls_CAfile = ".$data_step2['smtp_tls_CAfile']."\n";
            $text .="smtpd_tls_loglevel = ".$data_step2['smtpd_tls_loglevel']."\n";
            $text .="smtpd_tls_received_header = ".$data_step2['smtpd_tls_received_header']."\n";
            $text .="smtpd_tls_session_cache_timeout = ".$data_step2['smtpd_tls_session_cache_timeout']."\n";
            $text .="tls_random_source = ".$data_step2['tls_random_source']."\n";
            $text .="tls_daemon_random_source = ".$data_step2['tls_daemon_random_source']."\n";
        }
        
        $this->saveChangeFileMainCF($text);
        fclose($fp);
    }


    function execConfigPosfix_3(){
        //se ejecuta de esa forma porque es usuario asterisk el que corre el programa de elastix
        exec("sudo /sbin/service saslauthd restart");
        exec("sudo /sbin/service postfix restart");
    }


    function saveFileMainCf($fh, $pDB){
        $query = "DELETE FROM email_relay";
        $result = $this->_DB->genQuery($query);

        while(!feof($fh))
        {
            $linea = fgets($fh);
            $datos=split('[=]',$linea);
            if(count($datos)>1)
            {
                if(((trim($datos[0])=="#relayhost") && trim($datos[1])=="[gateway.my.domain]") || trim($datos[0])=="relayhost" ) {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);
                    
                }elseif(trim($datos[0])=="mydomain") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtp_sasl_auth_enable") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtp_sasl_password_maps") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtp_sasl_security_options") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtpd_tls_auth_only") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtp_use_tls") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtpd_use_tls") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtp_tls_note_starttls_offer") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtpd_tls_key_file") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtpd_tls_cert_file") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtp_tls_CAfile") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtpd_tls_loglevel") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtpd_tls_received_header") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="smtpd_tls_session_cache_timeout") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="tls_random_source") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }elseif(trim($datos[0])=="tls_daemon_random_source") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addMainConfig($data);

                }
            }
        }
        if($result == false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        fclose($fh);
        return $result;
    }

    function addMainConfig($data)
    {
        $queryInsert = $this->_DB->construirInsert('email_relay', $data);
        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }

    function updateMainConfig($data,$where)
    {
        $queryUpdate = $this->_DB->construirUpdate('email_relay', $data,$where);
        $result = $this->_DB->genQuery($queryUpdate);

        return $result;
    }

    function getMainConfigByAll(){
        $query   = "SELECT id, name, value FROM email_relay ";
        
        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }

        $count=0;
        $arrMainConf = array();
        if(is_array($result) & count($result)>0){
            foreach($result as $key => $value){
                $arrMainConf[$count] = $value['value'];
                $count++;
            }
        }
        return $arrMainConf;
    }

    function getMainConfigByAll2(){
        $query   = "SELECT id, name, value FROM email_relay ";
        
        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function readFileSSL()
    {
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/");
        $myFile='/etc/postfix/sasl/passwd';
        $fh = fopen($myFile, 'r');
        exec("sudo -u root chown -R root.root /etc/postfix/");
        return $fh;
    }

    function getDataSsl($fhssl){
        $data = array();

        while(!feof($fhssl))
        {
            $linea = fgets($fhssl);
            $part1=split('[@]',$linea);
            if(count($part1)>1){
                $part2=split('[:]',$part1[0]);
                $part3=split('[:]',$part1[1]);
                if(ereg("[^0-9]([[:alpha:]]+)",$part2[1], $arrReg)){
                    $data['user'] =  trim($arrReg[1]);
                }
                $data['password'] = trim($part3[1]);
            }
        }
        fclose($fhssl);
        return $data;
    }

    function getEmailRelayAuthenticateById($id)
    {
        $query   = "SELECT * FROM email_authenticate ";
        $strWhere = "id=$id";

        // Clausula WHERE aqui
        if(!empty($strWhere)) $query .= "WHERE $strWhere ";

        $result=$this->_DB->getFirstRowQuery($query, true);
        return $result;
    }

    function addEmailRelayAuthenticate($data)
    {
        $queryInsert = $this->_DB->construirInsert('email_authenticate', $data);
        echo $queryInsert;
        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }

    function updateEmailRelayAuthenticate($data,$where)
    {
        $queryUpdate = $this->_DB->construirUpdate('email_authenticate', $data,$where);
        $result = $this->_DB->genQuery($queryUpdate);

        return $result;
    }

    function getEmailRelayAuthenticate(){
        $query   = "SELECT * FROM email_authenticate ";
        
        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

}
?>