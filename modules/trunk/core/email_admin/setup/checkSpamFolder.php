#!/usr/bin/php
<?php
// script para crear carpetas spam y suscribirlas
    $module_name = "antispam";
    include_once "/var/www/html/libs/misc.lib.php";
    include_once "/var/www/html/libs/paloSantoDB.class.php";
    include_once "/var/www/html/configs/email.conf.php";
    include_once "/var/www/html/libs/cyradm.php";
    include_once "/var/www/html/modules/$module_name/libs/paloSantoAntispam.class.php";

    $pDB = new paloDB("sqlite3:////var/www/db/email.db");
    $objAntispam = new paloSantoAntispam("", "", "", "");
    // primero se verifica si alguna cuenta no tiene la carpeta Spam creada
    $accounts = $objAntispam->listEmailSpam($pDB);
    $status = "";
    $SIEVE = array();
    $SIEVE['HOST'] = "localhost";
    $SIEVE['PORT'] = 4190;
    $SIEVE['USER'] = "";
    $SIEVE['PASS'] = obtenerClaveCyrusAdmin("/var/www/html/");
    $SIEVE['AUTHTYPE'] = "PLAIN";
    $SIEVE['AUTHUSER'] = "cyrus";
    $SIEVE['AUTHPASS'] = obtenerClaveCyrusAdmin("/var/www/html/");

    exec("/etc/init.d/spamassassin status", $flag, $status);

    if($status == 0){
        $content = $objAntispam->getContentScript();
        $fileScript = "/tmp/scriptTest.sieve";
        $fp = fopen($fileScript,'w');
        fwrite($fp,$content);
        fclose($fp);

        // si el arreglo es vacio no hace nada
        if(isset($accounts) & $accounts!=""){// existe alguna cuenta sin esa carpeta
            foreach($accounts as $key => $value){
                $status .= $objAntispam->creacionSpamFolder($value);
            }
        }
        // verificando usuarios que no tengan activados el script del sieve
        $emails = $objAntispam->getEmailList($pDB);
        if(isset($emails) & $emails!=""){// si no existe ninguna cuenta
            foreach($emails as $key2 => $value2){
                $SIEVE['USER']     = $value2["username"];
                $SIEVE['PASS']     = $value2["password"];
                $SIEVE['AUTHUSER'] = $value2["username"];
                $script = $objAntispam->listScriptSieveByUser($SIEVE);
                if(isset($script) & $script!="" & $script!="no Conection"){
                    $fileSieve = isset($script[0])?$script[0]:"";
                    if($fileSieve == ""){echo "\n".$value2["username"]."\n";
                        exec("echo ".$SIEVE['AUTHPASS']." | sieveshell --username=".$SIEVE['USER']." --authname=cyrus localhost:4190 -e 'put $fileScript'",$flags, $status);
                        exec("echo ".$SIEVE['AUTHPASS']." | sieveshell --username=".$SIEVE['USER']." --authname=cyrus localhost:4190 -e 'activate scriptTest.sieve'");
                    }
                }
            }
        }
        unlink($fileScript);
    }else{
        echo "ERROR: ".$flag[0]."\n";
    }

?>