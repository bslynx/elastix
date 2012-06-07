<?php
/*
  vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 Palosanto Solutions S. A.                    |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  +----------------------------------------------------------------------+
  | Este archivo fuente está sujeto a las políticas de licenciamiento    |
  | de Palosanto Solutions S. A. y no está disponible públicamente.      |
  | El acceso a este documento está restringido según lo estipulado      |
  | en los acuerdos de confidencialidad los cuales son parte de las      |
  | políticas internas de Palosanto Solutions S. A.                      |
  | Si Ud. está viendo este archivo y no tiene autorización explícita    |
  | de hacerlo, comuníquese con nosotros, podría estar infringiendo      |
  | la ley sin saberlo.                                                  |
  +----------------------------------------------------------------------+
  | Autores: Alberto Santos Flores <asantos@palosanto.com>               |
  +----------------------------------------------------------------------+
  $Id: SyncContacts.class.php,v 1.1 2012/05/17 23:49:36 Alberto Santos Exp $
*/

$documentRoot = $_SERVER["DOCUMENT_ROOT"];
require_once "$documentRoot/libs/REST_Resource.class.php";
require_once "$documentRoot/libs/paloSantoJSON.class.php";
require_once "$documentRoot/modules/address_book/libs/core.class.php";

/*
 * Para esta implementación de REST, se tienen los siguientes URI
 * 
 *  /SyncContacts            application/json
 *      GET     lista un par de URIs para sincronización diferencial y completa
 *  /SyncContacts/differential   application/json
 *      PUT     Realiza la sincronización diferencial, colocándo la petición
 *              en la cola
 *  /SyncContacts/differential/status/XXXX   application/json
 *      GET     obtiene el status del ticket XXXX de la cola, en caso de ser "OK" 
 *              deberá devolver al cliente los datos que tendrá que actualizar
 *  /SyncContacts/full   application/json
 *      GET     realiza una sincronización completa
 *
 */

class SyncContacts
{
    private $resourcePath;
    function __construct($resourcePath)
    {
	$this->resourcePath = $resourcePath;
    }

    function URIObject()
    {
	$uriObject = NULL;
	if (count($this->resourcePath) <= 0) {
		$uriObject = new SyncContactsBase();
	} elseif (in_array($this->resourcePath[0], array('differential', 'full'))) {
	    switch (array_shift($this->resourcePath)) {
		case 'differential':
		    if(count($this->resourcePath) <= 0)
			$uriObject = new DifferentialSync();
		    else{
			if(array_shift($this->resourcePath) == "status"){
			    if(count($this->resourcePath) > 0)
				$uriObject = new StatusQueue(array_shift($this->resourcePath));
			}
		    }
		    break;
		case 'full':
		    $uriObject = new FullSync();
		    break;
	    }
	}
	if(count($this->resourcePath) > 0)
	    return NULL;
	else
	    return $uriObject;
    }
}

class SyncContactsBase extends REST_Resource
{
    function HTTP_GET()
    {
    	$json = new Services_JSON();
        return $json->encode(array(
            'url_differential'  =>  $this->requestURL().'/differential',
            'url_full'	        =>  $this->requestURL().'/full',));
    }
}

class DifferentialSync extends REST_Resource
{
    function HTTP_PUT()
    {
	$json = new paloSantoJSON();
	$data = file_get_contents('php://input');
	$pCore_AddressBook = new core_AddressBook();
	$result = $pCore_AddressBook->contactDifferentialSync($data);
	if(!$result){
	    $error = $pCore_AddressBook->getError();
            if ($error["fc"] == "DBERROR")
                header("HTTP/1.1 500 Internal Server Error");
            else
                header("HTTP/1.1 400 Bad Request");
            $json->set_status("ERROR");
            $json->set_error($error);
            return $json->createJSON();
	}
	else{
	    $json = new Services_JSON();
	    $response["ticket_queue"] = $result;
	    return $json->encode($response);
	}
    }
}

class StatusQueue extends REST_Resource
{
    protected $ticket_queue;
    function __construct($ticket)
    {
	$this->ticket_queue = $ticket;
    }

    function HTTP_GET()
    {
	$json = new paloSantoJSON();
	$pCore_AddressBook = new core_AddressBook();
	$result = $pCore_AddressBook->getStatusQueue($this->ticket_queue);
	if($result === FALSE){
	    $error = $pCore_AddressBook->getError();
            if ($error["fc"] == "DBERROR")
                header("HTTP/1.1 500 Internal Server Error");
            else
                header("HTTP/1.1 400 Bad Request");
            $json->set_status("ERROR");
            $json->set_error($error);
            return $json->createJSON();
	}
	else{
	    $json = new Services_JSON();
	    return $json->encode($result);
	}
    }
}

class FullSync extends REST_Resource
{
    function HTTP_GET()
    {
	$json = new paloSantoJSON();
	$pCore_AddressBook = new core_AddressBook();
	$result = $pCore_AddressBook->getFullSync();
	if($result === FALSE){
	    $error = $pCore_AddressBook->getError();
            if ($error["fc"] == "DBERROR")
                header("HTTP/1.1 500 Internal Server Error");
            else
                header("HTTP/1.1 400 Bad Request");
            $json->set_status("ERROR");
            $json->set_error($error);
            return $json->createJSON();
	}
	else{
	    $json = new Services_JSON();
	    return $json->encode($result);
	}
    }
}

?>