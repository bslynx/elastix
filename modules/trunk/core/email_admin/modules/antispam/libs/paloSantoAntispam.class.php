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
  $Id: default.conf.php,v 1.1 2008-09-01 05:09:57 Bruno Macias <bmacias@palosanto.com> Exp $ */

include_once "/var/www/html/libs/cyradm.php";
include_once "/var/www/html/modules/antispam/libs/sieve-php.lib.php";

class paloSantoAntispam {
    var $fileMaster;
    var $fileLocal;
    var $folderPostfix;
    var $folderSpamassassin;
    var $errMsg;

    function paloSantoAntispam($pathPostfix,$pathSpamassassin,$fileMaster,$fileLocal)
    {
        $this->fileLocal     = $fileLocal;
        $this->fileMaster    = $fileMaster;
        $this->folderPostfix = $pathPostfix;
        $this->folderSpamassassin = $pathSpamassassin;
    }

    /*HERE YOUR FUNCTIONS*/

    function areFilesConfigurated()
    {
        // Trato de abrir el archivo de configuracion 
        $step_one_config = false;
        $step_two_config = false;
        if($fh = @fopen($this->fileMaster, "r")) {
            while($line_file = fgets($fh, 4096)) {
                //line to valid:smtp      inet  n       -       n       -       -       smtpd
                if(ereg("(smtp[[:space:]]{1,}inet[[:space:]]{1,}n[[:space:]]{1,}-[[:space:]]{1,}n[[:space:]]{1,}-[[:space:]]{1,}-[[:space:]]{1,}smtpd)",$line_file,$arrReg)){
                        $line_file_next = fgets($fh, 4096);
                        //line to valid:  -o content_filter=spamfilter:dummy
                        if(ereg("([[:space:]]{1,}-o[[:space:]]{1,}content_filter=spamfilter:dummy)",$line_file_next,$arrReg)){
                            $step_one_config = true;
                        }
                }
                //line to valid:spamfilter unix -       n       n       -       -       pipe
                if(ereg("(spamfilter[[:space:]]{1,}unix[[:space:]]{1,}-[[:space:]]{1,}n[[:space:]]{1,}n[[:space:]]{1,}-[[:space:]]{1,}-[[:space:]]{1,}pipe)",$line_file,$arrReg)){
                        $line_file_next = fgets($fh, 4096);
                        //line to valid:  flags=Rq user=spamfilter argv=/usr/local/bin/spamfilter.sh -f ${sender} -- ${recipient}
                        if(ereg("([[:space:]]{1,}flags=Rq[[:space:]]{1,}user=spamfilter[[:space:]]{1,}argv=/usr/local/bin/spamfilter.sh[[:space:]]{1,}-f[[:space:]]{1,}\\$\{sender\}[[:space:]]{1,}--[[:space:]]{1,}\\$\{recipient\})",$line_file_next,$arrReg)){
                            $step_two_config = true;
                        }
                }
            }
        }
        return array("isOk" => $step_one_config && $step_two_config, "is_smtp" => $step_one_config, "is_spamfilter" => $step_two_config);
    }

    function isActiveSpamFilter()
    {
        $step_three_config = false;
        exec("sudo /sbin/service generic-cloexec spamassassin status",$arrConsole,$flagStatus);
        if($flagStatus == 0){
            if(preg_match("/pid/",$arrConsole[0]))
                $step_three_config = true;
        }
        return $step_three_config;
    }

    function getValueRequiredHits()
    {
        // Trato de abrir el archivo de configuracion 
        $data = array();
        if($fh = @fopen($this->fileLocal, "r")) {
            while($line_file = fgets($fh, 4096)) {
                //line to valid:required_hits 5
                if(preg_match("/[[:space:]]*required_hits[[:space:]]+([[:digit:]]{0,2})/",$line_file,$arrReg)){
                        $data['level'] = $arrReg[1];
                }
                if(preg_match("/[[:space:]]*rewrite_header[[:space:]]*Subject[[:space:]]+(.*)/",$line_file,$arrReg2)){
                        $data['header'] = $arrReg2[1];
                }
            }
        }
        return $data;
    }

    function activateSpamFilter()
    {
        global $arrLangModule;
        $return = false;

        $cmd_one  =  "sed -ie 's/smtp[[:space:]]\{1,\}inet[[:space:]]\{1,\}n[[:space:]]\{1,\}-[[:space:]]\{1,\}n[[:space:]]\{1,\}-[[:space:]]\{1,\}-[[:space:]]\{1,\}smtpd/";
        $cmd_one .=  "smtp      inet  n       -       n       -       -       smtpd\\n  -o content_filter=spamfilter:dummy/' {$this->fileMaster}";

        $cmd_two  = "echo '#\n#Add by Elastix\n#\nspamfilter unix -       n       n       -       -       pipe\n";
        $cmd_two .= "  flags=Rq user=spamfilter argv=/usr/local/bin/spamfilter.sh -f \${sender} -- \${recipient}' >> {$this->fileMaster}";

        $arrSpamFilter = $this->areFilesConfigurated();

        exec("sudo -u root chmod 777 {$this->folderPostfix}",$arrConsole1,$flatStatus1);
        exec("sudo -u root chmod 777 {$this->fileMaster}",$arrConsole2,$flatStatus2);

        if($flatStatus1 != 0 || $flatStatus2 != 0){
            $this->errMsg = $arrLangModule["Could not give permissions to files"];
            return $return;
        }

        $flatStatus3 = 0;
        if(!$arrSpamFilter["is_smtp"])
            exec($cmd_one,$arrConsole3,$flatStatus3);

        if($flatStatus3 == 0){
            $flatStatus4 = 0;
            if(!$arrSpamFilter["is_spamfilter"])
                exec($cmd_two,$arrConsole4,$flatStatus4); 
            if($flatStatus4 == 0){
                    $return = true;
            }
            else $this->errMsg = $arrLangModule["Could not make the configuration file"];
        }
        else $this->errMsg = $arrLangModule["Could not make the configuration file"];

        exec("sudo -u root chmod 644 {$this->fileMaster}",$arrConsole5,$flatStatus5);
        exec("sudo -u root chmod 755 {$this->folderPostfix}",$arrConsole6,$flatStatus6);
        exec("sudo -u root chown root.root {$this->fileMaster}",$arrConsole5,$flatStatus5);

        exec("sudo /sbin/service generic-cloexec spamassassin start",$arrConsole,$flagStatus);
        if($flagStatus != 0){
            $this->errMsg = $arrLangModule["Could not start the service antispam"];
            $return = false;
        }else
            $return = true;
        return $return;
    }

    function disactivateSpamFilter()
    {
        global $arrLangModule;
        $return = false;
        $activated = $this->isActiveSpamFilter();
        if(!$activated){
            $this->errMsg = $arrLangModule['Antispam service is already desactivated'];
            return $return;
        }
        exec("sudo /sbin/service generic-cloexec spamassassin stop",$arrConsole,$flagStatus);
        if($flagStatus != 0){
            $this->errMsg = $arrLangModule["Could not stop the service antispam"];
        }else
            $return = true;
        return $return;
    }

    function changeFileLocal($level,$header)
    {
        global $arrLangModule;

        $cmd ="sed -ie 's/required_hits[[:space:]]*[[:digit:]]\{0,2\}/required_hits $level/' {$this->fileLocal}";
        $cmd_two = "sed -ie 's/[[:space:]]*rewrite_header[[:space:]]*Subject[[:space:]]*.*/rewrite_header Subject $header/' {$this->fileLocal}";
        exec("sudo -u root chmod 777 {$this->folderSpamassassin}",$arrConsole1,$flatStatus1);
        exec("sudo -u root chmod 777 {$this->fileLocal}",$arrConsole2,$flatStatus2);

        if($flatStatus1 == 0 && $flatStatus2 == 0){
            exec($cmd,$arrConsole3,$flatStatus3);
            if($flatStatus3 == 0){
                 exec($cmd_two,$arrConsole6,$flatStatus6);
                 if($flatStatus6 == 0){
                        exec("sudo -u root chmod 644 {$this->fileLocal}",$arrConsole4,$flatStatus4);
                        exec("sudo -u root chown root.root {$this->fileLocal}",$arrConsole4,$flatStatus4);
                        exec("sudo -u root chmod 755 {$this->folderSpamassassin}",$arrConsole5,$flatStatus5);
                        return true;
                 }
                 else $this->errMsg = $arrLangModule["The command failed when attempting to change the header"];
            }
            else $this->errMsg = $arrLangModule["Commad failed, try change thoroughness level"];
        }
        else $this->errMsg = $arrLangModule["Failed change thoroughness level and header"];
        return false;
    }
/********************************************************************************************************************/
    //funcion que devuelve todas las cuentas de correos
    function getEmailList($pDB)
    {
        //$pDB = new paloDB("sqlite3:////var/www/db/email.db");
        $query = "SELECT username, password FROM accountuser";
        $result=$pDB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $pDB->errMsg;
            return array();
        }
        return $result;
    }

    //funcion que crea la carpeta de Spam dado un email en el servidor IMAP mediante telnet
    function creacionSpamFolder($email)
    {
        global $CYRUS;
        $cyr_conn = new cyradm;
        $error_msg = "";
        $error = $cyr_conn->imap_login();
        $dataEmail = explode("@",$email);
        if ($error===FALSE){
            $error_msg = "IMAP login error: $error <br>";
        }else{
            $seperator  = '/';
            $bValido=$cyr_conn->createmb("user" . $seperator . $dataEmail[0] . $seperator . "Spam@" . $dataEmail[1]);
            if(!$bValido)
                $error_msg = "Error creating Spam folder:".$cyr_conn->getMessage()."<br>";
            else{
                $bValido=$cyr_conn->command(". subscribe \"user" . $seperator . $dataEmail[0] . $seperator . "Spam@" . $dataEmail[1] ."\"");
                if(!$bValido)
                    $error_msg = "error cannot be subscribe the Spam folder for $email:".$cyr_conn->getMessage()."<br>";
            }
            $cyr_conn->imap_logout();
        }
        return $error_msg;
    }

    // verifica si una cuenta tiene creada la carpeta spam en su buzon de correo
    function haveFolderSpam($email)
    {
        global $CYRUS;
        global $arrLang;
        $cyr_conn = new cyradm();
        $error = $cyr_conn->imap_login();
        $dataEmail = explode("@",$email);
        if ($error===FALSE){
            $error_msg .= "IMAP login error: $error <br>";
            return "login error";
        }else{
            $dataEmail = explode("@",$email);
            $pathString = "*".$dataEmail[0]."*";
            $data = $cyr_conn->GetFolders($pathString);
            $cyr_conn->imap_logout();
            for($i=0; $i<count($data); $i++){
                $value = $data[$i];
                $domain = str_replace(".","/",$dataEmail[1]);
                $bool = strpos($value,"Spam@".$domain);
                if($bool){// tiene carpeta spam
                    return true;
                }
            }
        }
        return false;// no tiene carpeta spam
    }

    // funcion que devuelve la lista de correos que no tienen una carpeta Spam asignada
    function listEmailSpam($pDB)
    {
        $emails = $this->getEmailList($pDB);
        $data = array();
        $i = 0;
        foreach($emails as $key => $value){
            $account = $value['username'];
            $bool = $this->haveFolderSpam($account);
            if($bool === "login error"){
                return $bool;
            }
            if($bool === false)
                $data[$i] = $account;
            $i++;
        }
        return $data;
    }

    // activa un script determinado en el servidor
    function activateScriptSieveByUser($SIEVE, $scriptName)
    {
        $sieve=new sieve($SIEVE['HOST'], $SIEVE['PORT'], $SIEVE['USER'], $SIEVE['PASS'], $SIEVE['AUTHUSER'], $SIEVE['AUTHTYPE']);
        if ($sieve->sieve_login()){
            if($sieve->sieve_setactivescript($scriptName)){
                $sieve->sieve_logout();
                return TRUE;
            }else{
                //$sieve->error_raw;
                //$sieve->error;
                $sieve->sieve_logout();
                return FALSE;
            }
        }else{
            return "no connection";
        }
    }


    // sube un script al servidor
    function putScriptSieveByUser($SIEVE, $scriptName, $activescript){
        $sieve=new sieve($SIEVE['HOST'], $SIEVE['PORT'], $SIEVE['USER'], $SIEVE['PASS'], $SIEVE['AUTHUSER'], $SIEVE['AUTHTYPE']);
        if ($sieve->sieve_login()){
            if($sieve->sieve_sendscript($scriptName,$activescript)){
                $sieve->sieve_logout();
                return TRUE;
            }else{
                echo $sieve->error_raw;
                echo $sieve->error;
                $sieve->sieve_logout();
                return FALSE;
            }
        }else{
            return "no connection";
        }
    }

    // lista todos los script que esten en el servidor
    function listScriptSieveByUser($SIEVE){
        $sieve = new sieve($SIEVE['HOST'], $SIEVE['PORT'], $SIEVE['USER'], $SIEVE['PASS'], $SIEVE['AUTHUSER'], $SIEVE['AUTHTYPE']);
        $i = 0;
        $scripts = array();
        if ($sieve->sieve_login()){
            $sieve->sieve_listscripts();
            if(is_array($sieve->response)) {
                foreach($sieve->response as $result) {
                    $scripts[$i] = $result;
                    $i++;
                }
            }
            $sieve->sieve_logout();
        }else{
            return "no Conection";
        }
        return $scripts;
    }
    // descargar un script que este en el servidor(la salida por consola)
    function getScriptSieveByUser($SIEVE, $scriptName){
        $sieve=new sieve($SIEVE['HOST'], $SIEVE['PORT'], $SIEVE['USER'], $SIEVE['PASS'], $SIEVE['AUTHUSER'], $SIEVE['AUTHTYPE']);
        $i = 0;
        $scripts = array();
        if ($sieve->sieve_login()){
            if($sieve->sieve_getscript($scriptName)){
                if(is_array($sieve->response)) {
                    foreach($sieve->response as $result) {
                        $scripts[$i] = $result;
                        $i++;
                    }
                }
                $sieve->sieve_logout();
                return TRUE;
            }else{
                //$sieve->error_raw;
                //$sieve->error;
                $sieve->sieve_logout();
                return FALSE;
            }
        }else{
            return "no connection";
        }
        return $scripts;
    }

    // elimina un script que este en el servidor
    function deleteScriptSieveByUser($SIEVE, $scriptName){
        $sieve=new sieve($SIEVE['HOST'], $SIEVE['PORT'], $SIEVE['USER'], $SIEVE['PASS'], $SIEVE['AUTHUSER'], $SIEVE['AUTHTYPE']);
        if ($sieve->sieve_login()){
            if($sieve->sieve_deletescript($scriptName)){
                $sieve->sieve_logout();
                return TRUE;
            }else{
                //$sieve->error_raw;
                //$sieve->error;
                $sieve->sieve_logout();
                return FALSE;
            }
        }else{
            return "no connection";
        }
    }

    // funcion que sube un script y lo activa apar todos los buzones de correo
    function uploadScriptSieve($pDB){
        // creando cron
        $this->createCron();
        $emails = $this->getEmailList($pDB);
        //creando carpetas Spam
        $accounts = $this->listEmailSpam($pDB);
        $status = "";
        // si el arreglo es vacio no hace nada
        if(isset($accounts) & $accounts!=""){// existe alguna cuenta sin esa carpeta
            foreach($accounts as $key => $value){
                $status .= $this->creacionSpamFolder($value);
            }
        }
        $content = $this->getContentScript();
        $fileScript = "/tmp/scriptTest.sieve";
        $fp = fopen($fileScript,'w');
        fwrite($fp,$content);
        fclose($fp);
        //recorriendo por buzon
        $SIEVE  = array();
        $SIEVE['HOST'] = "localhost";
        $SIEVE['PORT'] = 4190;
        $SIEVE['USER'] = "";
        $SIEVE['PASS'] = obtenerClaveCyrusAdmin("/var/www/html/");
        $SIEVE['AUTHTYPE'] = "PLAIN";
        $SIEVE['AUTHUSER'] = "cyrus";
        foreach($emails as $key => $value){
            $SIEVE['USER'] = $value['username'];

            //$activescript = "if header :contains 'X-Spam-Flag' 'YES' {  discard; }";
            //$status = $this->putScriptSieveByUser($SIEVE, "scriptTestd", $activescript);
            exec("echo ".$SIEVE['PASS']." | sieveshell --username=".$SIEVE['USER']." --authname=".$SIEVE['AUTHUSER']." localhost:4190 -e 'put $fileScript'",$flags, $status);
            /*if($status==="no connection")
                return "no connection";*/
            //$this->activateScriptSieveByUser($SIEVE, "scriptTest");
            exec("echo ".$SIEVE['PASS']." | sieveshell --username=".$SIEVE['USER']." --authname=".$SIEVE['AUTHUSER']." localhost:4190 -e 'activate scriptTest.sieve'");
        }
        unlink($fileScript);
    }

    function deleteScriptSieve($pDB){
        //eliminando cron
        $this->deleteCron();
        $emails = $this->getEmailList($pDB);
        //creando carpetas Spam
        $accounts = $this->listEmailSpam($pDB);
        $status = "";
        // si el arreglo es vacio no hace nada
        if(isset($accounts) & $accounts!=""){// existe alguna cuenta sin esa carpeta
            foreach($accounts as $key => $value){
                $status .= $this->creacionSpamFolder($value);
            }
        }
        //recorriendo por buzon
        $SIEVE  = array();
        $SIEVE['HOST'] = "localhost";
        $SIEVE['PORT'] = 4190;
        $SIEVE['USER'] = "";
        $SIEVE['PASS'] = obtenerClaveCyrusAdmin("/var/www/html/");
        $SIEVE['AUTHTYPE'] = "PLAIN";
        $SIEVE['AUTHUSER'] = "cyrus";
        foreach($emails as $key => $value){
            $SIEVE['USER'] = $value['username'];
            //$this->deleteScriptSieveByUser($SIEVE, "scriptTest");
            exec("echo ".$SIEVE['PASS']." | sieveshell --username=".$SIEVE['USER']." --authname=".$SIEVE['AUTHUSER']." localhost:4190 -e 'delete scriptTest.sieve'");
        }
    }

    // funcion que se encarga de crear el cron o escribirlo si ya existe
    function createCron(){
        // primero cambiamos los permisos de la carpèta cron.d
        $FILE = "/etc/cron.d/checkSpamFolder.cron";
        exec("sudo -u root chown asterisk.asterisk /etc/cron.d/");
        if(is_file($FILE))
            exec("sudo -u root chown asterisk.asterisk /etc/cron.d/checkSpamFolder.cron");
        $line = "59 23 * * *  root /usr/bin/php -q /var/www/checkSpamFolder.php";
        $fp = fopen($FILE,'w');
        fwrite($fp,$line);
        fclose($fp);
        exec("sudo -u root chown root.root /etc/cron.d/checkSpamFolder.cron");
        exec("sudo -u root chown root.root /etc/cron.d/");
    }

    function deleteCron(){
        $FILE = "/etc/cron.d/checkSpamFolder.cron";
        exec("sudo -u root chown asterisk.asterisk /etc/cron.d/");
        if(is_file($FILE)){
            exec("sudo -u root chown asterisk.asterisk /etc/cron.d/checkSpamFolder.cron");
            unlink($FILE);
        }
        exec("sudo -u root chown root.root /etc/cron.d/");
    }

    function getContentScript(){
        $script = "require \"fileinto\";
if header :contains \"X-Spam-Flag\" \"YES\" {
  fileinto \"Spam\";
}elsif header :contains \"X-Spam-Status\" \"Yes\" {
    fileinto \"Spam\";
}";
        return $script;
    }

    //funcion que crea la carpeta de Spam dado un email en el servidor IMAP mediante telnet
    function deleteSpamMessages($email)
    {
        global $CYRUS;
        $cyr_conn = new cyradm;
        $error_msg = "";
        $error = $cyr_conn->imap_login();
        $dataEmail = explode("@",$email);
        if ($error===FALSE){
            $error_msg = "IMAP login error: $error <br>";
        }else{
            $seperator  = '/';
            $bValido=$cyr_conn->command(". select \"user" . $seperator . $dataEmail[0] . $seperator . "Spam@" . $dataEmail[1] ."\"");
            if(!$bValido)
                $error_msg = "Error selected Spam folder:".$cyr_conn->getMessage()."<br>";
            else{
                $bValido=$cyr_conn->command(". store 1:* +flags \Deleted");
                if(!$bValido)
                    $error_msg = "error cannot be added flags Deleted to the messages of Spam folder for $email:".$cyr_conn->getMessage()."<br>";
                else{
                    $bValido=$cyr_conn->command(". expunge");
                    if(!$bValido)
                        $error_msg = "error cannot be deleted the messages of Spam folder for $email:".$cyr_conn->getMessage()."<br>";
                }
            }
            $cyr_conn->imap_logout();
        }
        return $error_msg;
    }

}
?>
