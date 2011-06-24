<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4-23                                               |
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
  $Id: paloSantoVacations.class.php,v 1.1 2011-06-07 12:06:29 Eduardo Cueva ecueva@palosanto.com Exp $ */
class paloSantoVacations {
    var $_DB;
    var $errMsg;

    function paloSantoVacations(&$pDB)
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

    function getNumVacations($filter_field, $filter_value, $arrLang)
    {
        $where = "";
	$arrParam = array();

        if(isset($filter_field) && $filter_field !="" && $filter_value != ""){
	    if($filter_field == "username")
		$filter_field = "a.$filter_field";
	    else{
		$filter_field = "v.$filter_field";
	    }
	    if($filter_field == "v.vacation" && strtolower($filter_value) == $arrLang["no"]){
		$where = " WHERE $filter_field ISNULL OR $filter_field like ? ";
		$filter_value = "no";
	    }else{
		$where = " WHERE $filter_field like ? ";
		if(strtolower($filter_value) === $arrLang["yes"])
		    $filter_value = "yes";
	    }

	    $arrParam = array("$filter_value%");
	}

        $query   = "SELECT
		      COUNT(*)
		    FROM
		      accountuser a LEFT JOIN messages_vacations v ON a.username=v.account
		    $where
		    ORDER BY a.username;";

        $result=$this->_DB->getFirstRowQuery($query, false, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getVacations($limit, $offset, $filter_field, $filter_value, $arrLang)
    {
        $where = "";
	$arrParam = array();
        if(isset($filter_field) && $filter_field !="" && $filter_value != ""){
	    if($filter_field === "username")
		$filter_field = "a.$filter_field";
	    else{
		$filter_field = "v.$filter_field";
	    }
	    if($filter_field === "v.vacation" && strtolower($filter_value) === $arrLang["no"]){
		$where = " WHERE $filter_field ISNULL OR $filter_field like ? ";
		$filter_value = "no";
	    }else{
		$where = " WHERE $filter_field like ? ";
		if(strtolower($filter_value) === $arrLang["yes"])
		    $filter_value = "yes";
	    }

	    $arrParam = array("$filter_value%");
	}

        $query   = "SELECT
		      a.username as username,
		      v.vacation as vacation,
		      v.subject as subject,
		      v.body as body
		   FROM accountuser a LEFT JOIN messages_vacations v ON a.username=v.account
		   $where
		   ORDER BY a.username
		   LIMIT $limit OFFSET $offset";
        $result=$this->_DB->fetchTable($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getVacationsById($id)
    {
        $query = "SELECT * FROM table WHERE id=$id";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }


    /*********************************************************************************
    /* Funcion para subir un script de vacaciones dado los siguientes parametros:
    /* - $email:        cuenta de email a la cual se subira el script de vacaciones
    /* - $subject:      titulo del mensaje que se envia como respuesta
    /* - $body:         cuerpo o contenido del mensaje que se enviara
    /* - $objAntispam   objeto Antispam
    /* - $spamCapture   boleano que indica si esta activo el eveto de captura de spam
    /*
    /*********************************************************************************/
    function uploadVacationScript($email, $subject, $body, $objAntispam, $spamCapture, $arrLang){

	$SIEVE  = array();
        $SIEVE['HOST'] = "localhost";
        $SIEVE['PORT'] = 4190;
        $SIEVE['USER'] = "";
        $SIEVE['PASS'] = obtenerClaveCyrusAdmin("/var/www/html/");
        $SIEVE['AUTHTYPE'] = "PLAIN";
        $SIEVE['AUTHUSER'] = "cyrus";
	$SIEVE['USER'] = $email;

        $contentVacations  = $this->getVacationScript($subject, $body);
	$contentSpamFilter = "";

	// si esta activada la captura de spam entonces se deber reemplazar <require "fileinto";> por require ["fileinto","vacation"];
	if($spamCapture){
	    $contentSpamFilter = $objAntispam->getContentScript();
	    $contentSpamFilter = str_replace("require \"fileinto\";", "require [\"fileinto\",\"vacation\"];", $contentSpamFilter);
	}else{
	    $contentSpamFilter =  "require [\"fileinto\",\"vacation\"];";
	}
	$content = $contentSpamFilter."\n".$contentVacations;
        $fileScript = "/tmp/vacations.sieve";
        $fp = fopen($fileScript,'w');
        fwrite($fp,$content);
        fclose($fp);

	exec("echo ".$SIEVE['PASS']." | sieveshell --username=".$SIEVE['USER']." --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT']." -e 'put $fileScript'",$flags, $status);
	if($status!=0){
	    $this->errMsg = $arrLang["Error: Impossible upload "]."vacations.sieve";
	    return false;
	}else{
	    exec("echo ".$SIEVE['PASS']." | sieveshell --username=".$SIEVE['USER']." --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT']." -e 'activate vacations.sieve'",$flags, $status);
	    if($status!=0){
		$this->errMsg = $arrLang["Error: Impossible activate "]."vacations.sieve";
		return false;
	    }
	}

        if(is_file($fileScript))
            unlink($fileScript);
	return true;
    }


    /*********************************************************************************
    /* Funcion para eliminar un script de vacaciones dado los siguientes parametros:
    /* - $email:        cuenta de email a la cual se subira el script de vacaciones
    /* - $objAntispam   objeto Antispam
    /* - $spamCapture   boleano que indica si esta activo el eveto de captura de spam
    /*
    /*********************************************************************************/
    function deleteVacationScript($email, $objAntispam, $spamCapture, $arrLang){

        $SIEVE  = array();
        $SIEVE['HOST'] = "localhost";
        $SIEVE['PORT'] = 4190;
        $SIEVE['USER'] = "";
        $SIEVE['PASS'] = obtenerClaveCyrusAdmin("/var/www/html/");
        $SIEVE['AUTHTYPE'] = "PLAIN";
        $SIEVE['AUTHUSER'] = "cyrus";
	$SIEVE['USER'] = $email;

	exec("echo ".$SIEVE['PASS']." | sieveshell --username=".$SIEVE['USER']." --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT']." -e 'delete vacations.sieve'",$flags, $status);

	if($status!=0){
	    $this->errMsg = $arrLang["Error: Impossible remove "]."vacations.sieve";
	    return false;
	}

	if($spamCapture){
	    $contentSpamFilter = $objAntispam->getContentScript();
	    $fileScript = "/tmp/scriptTest.sieve";
	    $fp = fopen($fileScript,'w');
	    fwrite($fp,$contentSpamFilter);
	    fclose($fp);

	    exec("echo ".$SIEVE['PASS']." | sieveshell --username=".$SIEVE['USER']." --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT']." -e 'put $fileScript'",$flags, $status);

	    if($status!=0){
		$this->errMsg = $arrLang["Error: Impossible upload "]."scriptTest.sieve";
		return false;
	    }else{
		exec("echo ".$SIEVE['PASS']." | sieveshell --username=".$SIEVE['USER']." --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT']." -e 'activate scriptTest.sieve'",$flags, $status);
		if($status!=0){
		    $this->errMsg = $arrLang["Error: Impossible activate "]."scriptTest.sieve";
		    return false;
		}
	    }
	    if(is_file($fileScript))
		unlink($fileScript);
	}
	return true;
    }

    /*********************************************************************************
    /* Funcion retorna la plantilla basica del script de vacaciones:
    /* - $subject:      titulo del mensaje que se envia como respuesta
    /* - $body:         cuerpo o contenido del mensaje que se enviara
    /*
    /*********************************************************************************/
    function getVacationScript($subject, $body){
        $script = <<<SCRIPT

 vacation
        # Reply at most once a day to a same sender
        :days 1

        # Currently, encode subject, so you can't use
        # Non-English characters in subject field.
        # The easiest way is let your webmail do that.
        :subject "$subject"

        # Use 'mime' parameter to compose utf-8 message, you can use
        # Non-English characters in mail body.
        :mime
"MIME-Version: 1.0
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: 8bit
$body
";

SCRIPT;
        return $script;
    }

    /*********************************************************************************
    /* Funcion retorna la plantilla basica del script de vacaciones:
    /* - $idUserInt:      id del usuario elastix en session
    /* - $pDBACL:         conexion a la base de datos acl
    /*
    /*********************************************************************************/
    function getAccountByIdUser($idUserInt, $pDBACL)
    {
	$data = array($idUserInt);
	$account = "";
	$query   = "select * from acl_profile_properties where id_profile=? order by property DESC";
	$result  = $pDBACL->fetchTable($query, true, $data);

        if($result==FALSE){
            $this->errMsg = $pDBACL->errMsg;
            return false;
        }
	
	foreach($result as $key => $value){
	    $propiedad = $value['property'];
	    $valor     = $value['value'];
	    if($propiedad=="login")
		$account .= $valor;
	    if($propiedad=="domain")
		$account .= "@$valor";
	}
	return $account;
    }


    /*********************************************************************************
    /* Funcion retorna el mensaje de vacaciones que se ha guardado dado un email:
    /* - $email:      email del usuario elastix en session
    /*
    /* Retorna:
    /* - $result:     El resultado de la consulta realizada
    /*********************************************************************************/
    function getMessageVacationByUser($email)
    {
	$data = array($email);
	$query = "select * from messages_vacations where account=?";
	$result=$this->_DB->getFirstRowQuery($query,true,$data);
	if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	return $result;
    }

    /*********************************************************************************
    /* Funcion retorna si ya existe un mensage almacenado por un usuario dado:
    /* - $email:      email del usuario elastix en session
    /* - $id:         id del mensage
    /*
    /* Retorna:
    /* - $result:     Un booleano con el resultado si existe un registro de un usuario
    /*********************************************************************************/
    function existMessage($id, $email)
    {
	$data = array($email,$id);
	$query = "select * from messages_vacations where account=? and id=?";
	$result=$this->_DB->getFirstRowQuery($query,true,$data);
	if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	if(is_array($result) && count($result) > 0)
	    return true;
	else
	    return false;
    }

    /*********************************************************************************
    /* Funcion que inserta un mensaje dado los siguientes parametros:
    /* - $email:      email del usuario elastix en session
    /* - $subject:    Titulo del mensaje
    /* - $body:       Cuerpo del mensaje
    /*
    /* Retorna:
    /* - $result:     Un booleano con el resultado si se inserto el registro
    /*********************************************************************************/
    function insertMessageByUser($email, $subject, $body, $status=null)
    {
	$data = array();
	$query = "";
	if(isset($status)){
	    $query = "insert into messages_vacations(account,subject,body,vacation) values(?,?,?,?)";
	    $data = array($email, $subject, $body, $status);
	}else{
	    $data = array($email, $subject, $body);
	    $query = "insert into messages_vacations(account,subject,body) values(?,?,?)";
	}
	$result=$this->_DB->genQuery($query,$data);
	if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	return true;
    }

    /*********************************************************************************
    /* Funcion que actualiza un mensaje dado los siguientes parametros:
    /* - $email:      email del usuario elastix en session
    /* - $subject:    Titulo del mensaje
    /* - $body:       Cuerpo del mensaje
    /* - $id:         id del mensage
    /*
    /* Retorna:
    /* - $result:     Un booleano con el resultado si se actualizo el registro
    /*********************************************************************************/
    function updateMessageByUser($email, $subject, $body, $id, $status=null)
    {
	$data = array();
	$vacation = "";
	if(isset($status)){
	    $vacation = " ,vacation=? ";
	    $data = array($email, $subject, $body, $status, $id);
	}else
	    $data = array($email, $subject, $body, $id);
	$query = "update messages_vacations set account=?, subject=?,  body=? $vacation where id=?";
	$result=$this->_DB->genQuery($query,$data);
	if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	return true;
    }


    /*********************************************************************************
    /* Funcion que inserta un mensaje dado los siguientes parametros:
    /* - $email:      email del usuario elastix en session
    /* - $subject:    Titulo del mensaje
    /* - $body:       Cuerpo del mensaje
    /*
    /* Retorna:
    /* - $result:     Un booleano con el resultado si se inserto el registro
    /*********************************************************************************/
    function setStatusMessageByUser($email, $id, $vacation)
    {
	$data = array($vacation, $id, $email);
	$query = "update messages_vacations set vacation=? where id=? and account=?";
	$result=$this->_DB->genQuery($query,$data);
	if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	return true;
    }


    /*********************************************************************************
    /* Funcion que verifica si el sieve esta corriendo.
    /* Parametros de entrada:
    /*  - $arrLang:      arreglo de lenguaje
    /*
    /* Retorna:
    /*  - $result:       El resultado de la consulta realizada
    /*********************************************************************************/
    function verifySieveStatus($arrLang)
    {
	$response = array();

	exec("sudo /sbin/service generic-cloexec cyrus-imapd status",$arrConsole,$flagStatus);
        if($flagStatus != 0){
	    $response['response'] = false;
	    $response['message'] = $arrLang["Cyrus Imap is down"];
        }else{
	    $response['response'] = true;
	    $response['message'] = $arrLang["Cyrus Imap is up"];
	}
        return $response;
    }


}
?>