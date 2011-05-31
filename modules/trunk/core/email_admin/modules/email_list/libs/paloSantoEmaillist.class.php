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
  $Id: paloSantoEmaillist.class.php,v 1.1 2009-08-26 09:08:29 Oscar Navarrete onavarrete@palosanto.com Exp $ */
class paloSantoEmaillist {
    var $_DB;
    var $errMsg;

    function paloSantoEmaillist(&$pDB)
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

    function getNumEmaillist($id_domain)
    {
        $where = "";
        if(isset($id_domain))
            $where = "where id=$id_domain";

        $query   = "SELECT COUNT(*) FROM email_list $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    
    function getEmaillistById($id)
    {
        $query = "SELECT * FROM email_list WHERE id=$id";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    ///////////////////////////////////////////////////////////////////
    ///////////////////////NEW FUNCTIONS///////////////////////////////

    
    function readFileMm_cfg() {
        $myFile='/usr/lib/mailman/Mailman/mm_cfg.py';
        $fh = fopen($myFile, 'r');
        return $fh;
    }

    function saveChangeFileMm_cfg($text){
        exec("sudo -u root chown asterisk.asterisk /usr/lib/mailman/Mailman/mm_cfg.py");
        $fp = fopen('/usr/lib/mailman/Mailman/mm_cfg.py', 'w');
        fwrite($fp, $text);   
        exec("sudo -u root chown root.mailman /usr/lib/mailman/Mailman/mm_cfg.py");
        fclose($fp);
    }

    function saveChangeFileMainCF($text){
        $fp = fopen('/etc/postfix/main.cf', 'w');
        fwrite($fp, $text);   

        fclose($fp);
    }

    function saveChangeFileAliases($text){
        $fp = fopen('/etc/aliases', 'a');
        fwrite($fp, $text);   

        fclose($fp);
    }

    function updateChangeFileDefaultsPy($text){
    exec("sudo -u root chown asterisk.asterisk /usr/lib/mailman/Mailman/Defaults.py");
        $fp = fopen('/usr/lib/mailman/Mailman/Defaults.py', 'w');
        fwrite($fp, $text);
    exec("sudo -u root chown root.mailman /usr/lib/mailman/Mailman/Defaults.py");
        fclose($fp);
    }

    function saveChangeFileAliases2($text){
        $fp = fopen('/etc/aliases', 'w');
        fwrite($fp, $text);   

        fclose($fp);
    }

    function checkFileMm_cfg(){
        $FILE='/etc/mailman/mm_cfg.py';
        $fp = $this->readFileMm_cfg();
        $status = "Mod";

        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("DEFAULT_URL_HOST   = fqdn", $line) ){
                $status = "New";
            }
        }
        return $status;
    }

    function execConfigMailMan_1($passRootSite, $emailRootList, $passRootList, $domainName){
        //$FILE='/etc/mailman/mm_cfg.py';
//         $text1 = "";
        //Cambio de usuario a archivos
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/main.cf");
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases");
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases.db");
        //Cambio de usuarios a mailman
        exec("sudo -u root chown -R asterisk.asterisk /etc/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /usr/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/run/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/spool/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/log/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lock/mailman/");
        
        $this->replaceFileMM_cfg($domainName);
    $this->replaceFileDefaultsPy($domainName);

        exec("touch /usr/lib/mailman/bin/lista_member.tmp");

        exec("echo $passRootSite | /usr/lib/mailman/bin/mmsitepass passwd");
        exec("echo $passRootList | /usr/lib/mailman/bin/newlist mailman $emailRootList passwd --stdin");
        //Proceso numero 2 al archivo aliases
        $text1 .="\n";
        $text1 .="## lista de distribuci�n mailman\n";
        $text1 .="mailman:              \"|/usr/lib/mailman/mail/mailman post mailman\"\n";
        $text1 .="mailman-admin:        \"|/usr/lib/mailman/mail/mailman admin mailman\"\n";
        $text1 .="mailman-bounces:      \"|/usr/lib/mailman/mail/mailman bounces mailman\"\n";
        $text1 .="mailman-confirm:      \"|/usr/lib/mailman/mail/mailman confirm mailman\"\n";
        $text1 .="mailman-join:         \"|/usr/lib/mailman/mail/mailman join mailman\"\n";
        $text1 .="mailman-leave:        \"|/usr/lib/mailman/mail/mailman leave mailman\"\n";
        $text1 .="mailman-owner:        \"|/usr/lib/mailman/mail/mailman owner mailman\"\n";
        $text1 .="mailman-request:      \"|/usr/lib/mailman/mail/mailman request mailman\"\n";
        $text1 .="mailman-subscribe:    \"|/usr/lib/mailman/mail/mailman subscribe mailman\"\n";
        $text1 .="mailman-unsubscribe:  \"|/usr/lib/mailman/mail/mailman unsubscribe mailman\"\n";
        
        $this->saveChangeFileAliases($text1);

        //Cambio de usuario a archivos
        exec("sudo -u root chown -R root.root /etc/postfix/main.cf");
        exec("sudo -u root chown -R root.root /etc/aliases");
        exec("sudo -u root chown -R root.root /etc/aliases.db");

        exec("sudo -u root chown -R root.root /etc/mailman/");
        exec("sudo -u root chown -R root.mailman /usr/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/run/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/spool/mailman/");
        exec("sudo -u root chown -R root.mailman /var/log/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lock/mailman/");
        exec("sudo -u root newaliases");
        exec("sudo -u root service generic-cloexec mailman restart");
        exec("chkconfig --level 3 mailman on");
        //fclose($fp);
    }


    function replaceFileMM_cfg($domainName){
        $FILE='/usr/lib/mailman/Mailman/mm_cfg.py';
        $text = "";
        $fp = fopen($FILE,'r');

        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("socket", $line)) {
        if(!eregi("# from socket import *", $line))
                $line = str_ireplace("from socket import *","# from socket import *", $line);
                $text .= $line; 
            }else if(eregi("try:", $line)) {
                if(!eregi("# try:", $line))
                $line = str_ireplace("try:", "# try:", $line);
                $text .= $line; 
            }else if(eregi("getfqdn()", $line)) {
                if(!eregi("# fqdn = getfqdn():", $line))
                $line = str_ireplace("fqdn = getfqdn()", "# fqdn = getfqdn()", $line);
                $text .= $line; 
            }else if(eregi("except:", $line)) {
                if(!eregi("# except:", $line))
                $line = str_ireplace("except:", "# except:", $line);
                $text .= $line;
            }elseif(eregi("mm_cfg_has_unknown_host_domains", $line)) {
                if(!eregi("# fqdn = 'mm_cfg_has_unknown_host_domains'", $line))
                $line = str_ireplace("fqdn = 'mm_cfg_has_unknown_host_domains'", "# fqdn = 'mm_cfg_has_unknown_host_domains'", $line);
                $text .= $line;
            }elseif(eregi("DEFAULT_URL_HOST   = ", $line)) {
                $line = str_ireplace("fqdn", "\"www.$domainName\"", $line);
                $text .= $line;
            }elseif(eregi("DEFAULT_EMAIL_HOST = ", $line)) {
                $line = str_ireplace("fqdn", "\"$domainName\"", $line);
                $text .= $line;
            }else {
                $text .= $line;
            }
        }
        //return $text;
        $this->saveChangeFileMm_cfg($text);
        fclose($fp);
    }

    function replaceFileDefaultsPy($domainName){
        $FILE='/usr/lib/mailman/Mailman/Defaults.py';
        $text = "";
        $fp = fopen($FILE,'r');
        while($line = fgets($fp, filesize($FILE)))
        {
       if(eregi("POSTFIX_STYLE_VIRTUAL_DOMAINS =", $line)){
        $data = preg_split('/[=]/',$line);
        if(trim($data[0])=='POSTFIX_STYLE_VIRTUAL_DOMAINS'){
          $text .= "POSTFIX_STYLE_VIRTUAL_DOMAINS = ['$domainName']\n";
        }else
          $text .=$line;
        }else
        $text .= $line;
        }
        //return $text;
        $this->updateChangeFileDefaultsPy($text);
        fclose($fp);
    }


    function addNewMailList($emailRootList, $passRootList, $nameList){
        $text="";
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases");
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases.db");

        exec("sudo -u root chown -R asterisk.asterisk /usr/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/run/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/spool/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/log/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lock/mailman/");
        //exec("/usr/lib/mailman/bin/newlist $nameList $emailRootList passwd $passRootList --stdin");
        exec("echo $passRootList | /usr/lib/mailman/bin/newlist $nameList $emailRootList passwd --stdin");
        //*/usr/lib/mailman/bin/newlist list_miami themao@palosanto.com  passwd oscar --stdin
        $text .="\n";
        $text .="## lista de distribuci�n $nameList\n";
        $text .="$nameList:              \"|/usr/lib/mailman/mail/mailman post $nameList\"\n";
        $text .="$nameList-admin:        \"|/usr/lib/mailman/mail/mailman admin $nameList\"\n";
        $text .="$nameList-bounces:      \"|/usr/lib/mailman/mail/mailman bounces $nameList\"\n";
        $text .="$nameList-confirm:      \"|/usr/lib/mailman/mail/mailman confirm $nameList\"\n";
        $text .="$nameList-join:         \"|/usr/lib/mailman/mail/mailman join $nameList\"\n";
        $text .="$nameList-leave:        \"|/usr/lib/mailman/mail/mailman leave $nameList\"\n";
        $text .="$nameList-owner:        \"|/usr/lib/mailman/mail/mailman owner $nameList\"\n";
        $text .="$nameList-request:      \"|/usr/lib/mailman/mail/mailman request $nameList\"\n";
        $text .="$nameList-subscribe:    \"|/usr/lib/mailman/mail/mailman subscribe $nameList\"\n";
        $text .="$nameList-unsubscribe:  \"|/usr/lib/mailman/mail/mailman unsubscribe $nameList\"\n";

        $this->saveChangeFileAliases($text);

        exec("sudo -u root newaliases");
        exec("sudo -u root service generic-cloexec mailman restart");

        exec("sudo -u root chown -R root.root /etc/aliases");
        exec("sudo -u root chown -R root.root /etc/aliases.db");
        
        exec("sudo -u root chown -R root.mailman /usr/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/run/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/spool/mailman/");
        exec("sudo -u root chown -R root.mailman /var/log/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lock/mailman/");
        
    exec("rm -rf /var/run/mailman/master-qrunner.pid");
    exec("/etc/init.d/mailman start");
    }


    function addNewMember($nameList, $emailMember){
        exec("sudo -u root chown -R asterisk.asterisk /usr/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/run/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/spool/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/log/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lock/mailman/");
        //exec("sudo /sbin/service generic-cloexec mailman restart");

        exec("echo '$emailMember' > /usr/lib/mailman/bin/lista_member.tmp");
        exec("/usr/lib/mailman/bin/add_members -r /usr/lib/mailman/bin/lista_member.tmp $nameList");

        exec("sudo -u root service generic-cloexec mailman restart");
        //cambio de usuario a como estaba inicialmente
        exec("sudo -u root chown -R root.mailman /usr/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/run/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/spool/mailman/");
        exec("sudo -u root chown -R root.mailman /var/log/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lock/mailman/");
    }


    //Funcion opcional /etc/aliases esta por default
    function replaceFileMainCF(){
        $FILE='/etc/postfix/main.cf';
        $fp = fopen($FILE,'r');
        $text = "";
        
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("alias_database = hash:/etc/aliases", $line)){
                $line = str_ireplace("alias_database = hash:/etc/aliases", "alias_database = hash:/etc/postfix/aliases", $line);
                $text .= $line;
            }else{
                $text .= $line;
            }
        }
        $this->saveChangeFileMainCF($text);
        fclose($fp);
    }


    function saveFileMainCf($fh, $pDB){
        $query = "DELETE FROM email_list;";
        $result = $this->_DB->genQuery($query);

        while(!feof($fh))
        {
            $linea = fgets($fh);
            $datos=preg_split('/[=]/',$linea);

            if(count($datos)>1)
            {
                if(trim($datos[0])=="mydomain") {
                    $data = array();
                    $data['name']    = $pDB->DBCAMPO(trim($datos[0]));
                    $data['value']   = $pDB->DBCAMPO(trim($datos[1]));
                    $result = $this->addEmailList($data);
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


    function addEmailListDB($data)
    {
        $queryInsert = $this->_DB->construirInsert('email_list', $data);
        $result = $this->_DB->genQuery($queryInsert);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function deleteEmailListDB($id)
    {
        $query = "DELETE FROM email_list WHERE id=$id";
        $result = $this->_DB->genQuery($query);
        if($result[0] > 0)
            return true;
        else return false;
    }

    function deleteEmailListMM($nameList)
    {
        $FILE='/etc/aliases';
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases");
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases.db");

        exec("sudo -u root chown -R asterisk.asterisk /usr/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/run/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/spool/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/log/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lock/mailman/");

        exec("/usr/lib/mailman/bin/rmlist -a $nameList");

        $fp = fopen($FILE,'r');
        $text = "";

        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($nameList, $line)){
                //no muestro las lineas o paso por alto las lineas con ese nombre de lista
            }else{
                $text .= $line;
            }
        }
        $this->saveChangeFileAliases2($text);
        fclose($fp);
        exec("sudo -u root chown -R root.root /etc/aliases");
        exec("sudo -u root chown -R root.root /etc/aliases.db");

        exec("sudo -u root chown -R root.mailman /usr/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/run/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/spool/mailman/");
        exec("sudo -u root chown -R root.mailman /var/log/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lock/mailman/");
        exec("sudo -u root newaliases");
        //exec("sudo -u root service generic-cloexec mailman restart");
    }

    function getEmailListByDomainDB($id)
    {
        $query = "SELECT id, listname, password, mailadmin FROM email_list WHERE id_domain=$id";
        
        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    
    function addEmailMemberDB($data)
    {
        $queryInsert = $this->_DB->construirInsert('member_list', $data);
        $result = $this->_DB->genQuery($queryInsert);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getMembersByListDB($id)
    {
        $query = "SELECT id, mailmember, id_emaillist FROM member_list WHERE id_emaillist=$id";
        
        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }
    
    function getMemberlistByIdDB($id)
    {
        $query = "SELECT mailmember FROM member_list WHERE id=$id";
        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function deleteEmailMemberDB($id)
    {
        $query = "DELETE FROM member_list WHERE id=$id";
        $result = $this->_DB->genQuery($query);
        if($result[0] > 0)
            return true;
        else return false;
    }

    function deleteEmailMemberMM($nameList, $emailMember)
    {
        exec("sudo -u root chown -R asterisk.asterisk /usr/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/run/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/spool/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/log/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lock/mailman/");
        
        exec("/usr/lib/mailman/bin/remove_members $nameList $emailMember");
        exec("sudo -u root service generic-cloexec mailman restart");

        //cambio de usuario a como estaba inicialmente
        exec("sudo -u root chown -R root.mailman /usr/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/run/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/spool/mailman/");
        exec("sudo -u root chown -R root.mailman /var/log/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lock/mailman/");
        
    }

    function ejecucion(){
      exec("sudo -u root rm -f /var/run/mailman/master-qrunner.pid");
      //exec("sudo -u root etc/init.d/mailman start");
      exec("sudo -u root service generic-cloexec mailman restart");
    }

}
?>
