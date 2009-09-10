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

    function getNumEmaillist($filter_field, $filter_value)
    {
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT COUNT(*) FROM email_list $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getEmaillist($limit, $offset, $filter_field, $filter_value)
    {
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT * FROM email_list $where LIMIT $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
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
        $myFile='/etc/mailman/mm_cfg.py';
        $fh = fopen($myFile, 'r');
        return $fh;
    }

    function saveChangeFileMm_cfg($text){
        $fp = fopen('/etc/mailman/mm_cfg.py', 'w');
        fwrite($fp, $text);   

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


    function execConfigMailMan_1($passRootSite, $emailRootList, $passRootList){
        $FILE='/etc/mailman/mm_cfg.py';
        //Cambio de usuario a archivos
        exec("sudo -u root chown -R asterisk.asterisk /etc/postfix/main.cf");
        exec("sudo -u root chown -R asterisk.asterisk /etc/mailman/mm_cfg.py");
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases");
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases.db");
        //Cambio de usuarios a mailman
        exec("sudo -u root chown -R asterisk.asterisk /usr/lib/mailman/bin/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/spool/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/log/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lock/mailman/");

        exec("touch /usr/lib/mailman/bin/lista_member.tmp");

        exec("echo $passRootSite | /usr/lib/mailman/bin/mmsitepass passwd");
        $fp = fopen($FILE,'r');
        $text = "";
        //Proceso numero 1 al archivo mm_cfg.py
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("socket",$line)){
                $line = str_ireplace("from socket import *","#from socket import *",$line);
                $text .= $line;
                
            }elseif(eregi("try",$line)){
                $line = str_ireplace("try:","#try:",$line);
                $text .= $line;

            }elseif(eregi("getfqdn()",$line)){
                $line = str_ireplace("fqdn = getfqdn()","#fqdn = getfqdn():",$line);
                $text .= $line;

            }elseif(eregi("except",$line)){
                $line = str_ireplace("except:","#except:",$line);
                $text .= $line;

            }elseif(eregi("mm_cfg_has_unknown_host_domains",$line)){
                $line = str_ireplace("fqdn = 'mm_cfg_has_unknown_host_domains'", "#fqdn = 'mm_cfg_has_unknown_host_domains'",$line);
                $text .= $line;

            }elseif(eregi("DEFAULT_URL_HOST",$line)){
                $line = str_ireplace("fqdn", "www.midominio.net", $line);
                $text .= $line;

            }elseif(eregi("DEFAULT_EMAIL_HOST",$line)){
                $line = str_ireplace("fqdn", "midominio.net", $line);
                $text .= $line;

            }else{
                $text .= $line;
            }
        }
        $this->saveChangeFileMm_cfg($text);
        fclose($fp);
        
        $text="";
        exec("echo $passRootList | /usr/lib/mailman/bin/newlist mailman $emailRootList passwd --stdin");
        //Proceso numero 2 al archivo aliases
        $text .="\n";
        $text .="## lista de distribuci�n mailman\n";
        $text .="mailman:              \"|/usr/lib/mailman/mail/mailman post mailman\"\n";
        $text .="mailman-admin:        \"|/usr/lib/mailman/mail/mailman admin mailman\"\n";
        $text .="mailman-bounces:      \"|/usr/lib/mailman/mail/mailman bounces mailman\"\n";
        $text .="mailman-confirm:      \"|/usr/lib/mailman/mail/mailman confirm mailman\"\n";
        $text .="mailman-join:         \"|/usr/lib/mailman/mail/mailman join mailman\"\n";
        $text .="mailman-leave:        \"|/usr/lib/mailman/mail/mailman leave mailman\"\n";
        $text .="mailman-owner:        \"|/usr/lib/mailman/mail/mailman owner mailman\"\n";
        $text .="mailman-request:      \"|/usr/lib/mailman/mail/mailman request mailman\"\n";
        $text .="mailman-subscribe:    \"|/usr/lib/mailman/mail/mailman subscribe mailman\"\n";
        $text .="mailman-unsubscribe:  \"|/usr/lib/mailman/mail/mailman unsubscribe mailman\"\n";
        
        $this->saveChangeFileAliases($text);

        //Cambio de usuario a archivos
        exec("sudo -u root chown -R root.root /etc/postfix/main.cf");
        exec("sudo -u root chown -R root.root /etc/mailman/mm_cfg.py");
        exec("sudo -u root chown -R root.root /etc/aliases");
        exec("sudo -u root chown -R root.root /etc/aliases.db");

        exec("sudo -u root chown -R root.mailman /usr/lib/mailman/bin/");
        exec("sudo -u root chown -R root.mailman /var/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/spool/mailman/");
        exec("sudo -u root chown -R root.mailman /var/log/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lock/mailman/");
        exec("newaliases");
        exec("service mailman restart");
        exec("chkconfig --level 3 mailman on");
    }


    function addNewMailList($emailRootList, $passRootList, $nameList){
        $text="";
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases");
        exec("sudo -u root chown -R asterisk.asterisk /etc/aliases.db");

        exec("sudo -u root chown -R asterisk.asterisk /usr/lib/mailman/bin/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/spool/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/log/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lock/mailman/");
        //exec("/usr/lib/mailman/bin/newlist $nameList $emailRootList passwd $passRootList --stdin");
        exec("echo $passRootList | /usr/lib/mailman/bin/newlist $nameList $emailRootList passwd --stdin");
        exec("echo 'echo $passRootList | /usr/lib/mailman/bin/newlist $nameList $emailRootList passwd --stdin' > /tmp/oscar");
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

        exec("sudo -u root chown -R root.root /etc/aliases");
        exec("sudo -u root chown -R root.root /etc/aliases.db");
        
        exec("sudo -u root chown -R root.mailman /usr/lib/mailman/bin/");
        exec("sudo -u root chown -R root.mailman /var/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/spool/mailman/");
        exec("sudo -u root chown -R root.mailman /var/log/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lock/mailman/");
        exec("newaliases");
        exec("service mailman restart");
    }


    function addNewMember($nameList, $emailMember){
        exec("sudo -u root chown -R asterisk.asterisk /usr/lib/mailman/bin/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lib/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/spool/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/log/mailman/");
        exec("sudo -u root chown -R asterisk.asterisk /var/lock/mailman/");
        //exec("sudo /sbin/service mailman restart");

        exec("echo '$emailMember' > /usr/lib/mailman/bin/lista_member.tmp");
        exec("/usr/lib/mailman/bin/add_members -r /usr/lib/mailman/bin/lista_member.tmp $nameList");
  
        //cambio de usuario a como estaba inicialmente
        exec("sudo -u root chown -R root.mailman /usr/lib/mailman/bin/");
        exec("sudo -u root chown -R root.mailman /var/lib/mailman/");
        exec("sudo -u root chown -R root.mailman /var/spool/mailman/");
        exec("sudo -u root chown -R root.mailman /var/log/mailman/");
        exec("sudo -u root chown -R root.mailman /var/lock/mailman/");

        exec("service mailman restart");
    }


    //Funcion opcional
    function replaceFileMainCF(){
        $FILE='/etc/postfix/main.cf';
        $fp = fopen($FILE,'r');
        $text = "";
        
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("/etc/postfix/main.cf", $line)){
                $line = str_ireplace("/etc/postfix/main.cf", "hash:/etc/postfix/aliases", $line);
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
            $datos=split('[=]',$linea);

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
    
    function deleteEmailMemberDB($id)
    {
        $query = "DELETE FROM member_list WHERE id=$id";
        $result = $this->_DB->genQuery($query);
        if($result[0] > 0)
            return true;
        else return false;
    }
}
?>