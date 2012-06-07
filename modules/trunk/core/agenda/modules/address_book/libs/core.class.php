<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4                                                |
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
  $Id: puntosF_AddressBook.class.php,v 1.0 2011-03-31 11:30:00 Alberto Santos F.  asantos@palosanto.com Exp $*/

$root = $_SERVER["DOCUMENT_ROOT"];
require_once("$root/libs/misc.lib.php");
require_once("$root/configs/default.conf.php");
require_once("$root/modules/address_book/libs/paloSantoAdressBook.class.php");
require_once("$root/modules/address_book/configs/default.conf.php");
require_once("$root/libs/paloSantoACL.class.php");
require_once("$root/libs/paloSantoDB.class.php");
require_once("$root/libs/JSON.php");
require_once("$root/libs/paloSantoLongPoll.class.php");

if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
    require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
}

$arrConf = array_merge($arrConf,$arrConfModule);

class core_AddressBook extends LongPoll
{
    /**
     * Description error message
     *
     * @var array
     */
    private $errMsg;

    /**
     * DSN for connection to asterisk database
     *
     * @var string
     */
    private $_astDSN;

    /**
     * ACL User ID for authenticated user
     *
     * @var integer
     */
    private $_id_user;

    /**
     * Object paloACL
     *
     * @var object
     */
    private $_pACL;

    /**
     * Array that contains a paloDB Object, the key is the DSN of a specific database
     *
     * @var array
     */
    private $_dbCache;

    /**
     * String with the id of a queue
     *
     * @var string
     */
    private $_ticket;

    /**
     * Constructor
     *
     */
    public function core_AddressBook()
    {
        $this->_id_user = NULL;
        $this->_pACL = NULL;
        $this->errMsg = NULL;
	$this->_ticket = NULL;
        $this->_dbCache = array();
        $this->_astDSN = generarDSNSistema('asteriskuser', 'asterisk', $_SERVER['DOCUMENT_ROOT'].'/');
	parent::__construct();
    }

    /**
     * Static function that creates an array with all the functional points with the parameters IN and OUT
     *
     * @return  array     Array with the definition of the function points.
     */
    public static function getFP()
    {
        $arrData["listAddressBook"]["params_IN"] = array(
            "addressBookType"       => array("type" => "string",              "required" => true),
            "offset"                => array("type" => "positiveInteger",     "required" => false),
            "limit"                 => array("type" => "positiveInteger",     "required" => false)
        );

        $arrData["listAddressBook"]["params_OUT"] = array(
            "totalCount"     => array("type" => "positiveInteger",   "required" => true),
            "extension"      => array("type" => "array",             "required" => true, "minOccurs"=>"0", "maxOccurs"=>"unbounded",
                "params" => array(
                    "id"            => array("type" => "positiveInteger",  "required" => true),
                    "phone"         => array("type" => "string",           "required" => true),
                    "name"          => array("type" => "string",           "required" => true),
                    "first_name"    => array("type" => "string",           "required" => false),
                    "last_name"     => array("type" => "string",           "required" => false),
                    "email"         => array("type" => "string",           "required" => false)
                        )
                    )
            );

        $arrData["addAddressBookContact"]["params_IN"] = array(
            "phone"          => array("type" => "string",   "required" => true),
            "first_name"     => array("type" => "string",   "required" => false),
            "last_name"      => array("type" => "string",   "required" => false),
            "email"          => array("type" => "string",   "required" => false)
        );

        $arrData["addAddressBookContact"]["params_OUT"] = array(
            "return"         => array("type" => "boolean",    "required" => true)
        );

        $arrData["delAddressBookContact"]["params_IN"] = array(
            "id"            => array("type" => "positiveInteger",    "required" => true)
        );

        $arrData["delAddressBookContact"]["params_OUT"] = array(
            "return"        => array("type" => "boolean",     "required" => true)
        );

        return $arrData;
    }

    /**
     * Function that gets the extension of the login user, that assumed is on $_SERVER['PHP_AUTH_USER']
     *
     * @return  string   extension of the login user, or NULL if the user in $_SERVER['PHP_AUTH_USER'] does not have an extension     *                   assigned
     */
    private function _leerExtension()
    {
        // Identificar el usuario para averiguar el número telefónico origen
        $id_user = $this->_leerIdUser();

        $pACL = $this->_getACL();        
        $user = $pACL->getUsers($id_user);
        if ($user == FALSE) {
            $this->errMsg["fc"] = 'ACL';
            $this->errMsg["fm"] = 'ACL lookup failed';
            $this->errMsg["fd"] = 'Unable to read information from ACL - '.$pACL->errMsg;
            $this->errMsg["cn"] = get_class($pACL);
            return NULL;
        }

        // Verificar si tiene una extensión
        $extension = $user[0][3];
        if ($extension == "") {
            $this->errMsg["fc"] = 'EXTENSION';
            $this->errMsg["fm"] = 'Extension lookup failed';
            $this->errMsg["fd"] = 'No extension has been set for user '.$_SERVER['PHP_AUTH_USER'];
            $this->errMsg["cn"] = get_class($pACL);
            return NULL;
        }

        return $extension;
    }

    /**
     * Function that creates, if do not exist in the attribute dbCache, a new paloDB object for the given DSN
     *
     * @param   string   $sDSN   DSN of a specific database
     * @return  object   paloDB object for the entered database
     */
    private function & _getDB($sDSN)
    {
        if (!isset($this->_dbCache[$sDSN])) {
            $this->_dbCache[$sDSN] = new paloDB($sDSN);
        }
        return $this->_dbCache[$sDSN];
    }

    /**
     * Function that creates, if do not exist in the attribute _pACL, a new paloACL object
     *
     * @return  object   paloACL object
     */
    private function & _getACL()
    {
        global $arrConf;

        if (is_null($this->_pACL)) {
            $pDB_acl = $this->_getDB($arrConf['elastix_dsn']['acl']);
            $this->_pACL = new paloACL($pDB_acl);
        }
        return $this->_pACL;
    }

    /**
     * Function that reads the login user ID, that assumed is on $_SERVER['PHP_AUTH_USER']
     *
     * @return  integer   ACL User ID for authenticated user, or NULL if the user in $_SERVER['PHP_AUTH_USER'] does not exist
     */
    private function _leerIdUser()
    {
        if (!is_null($this->_id_user)) return $this->_id_user;

        $pACL = $this->_getACL();        
        $id_user = $pACL->getIdUser($_SERVER['PHP_AUTH_USER']);
        if ($id_user == FALSE) {
            $this->errMsg["fc"] = 'INTERNAL';
            $this->errMsg["fm"] = 'User-ID not found';
            $this->errMsg["fd"] = 'Could not find User-ID in ACL for user '.$_SERVER['PHP_AUTH_USER'];
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        $this->_id_user = $id_user;
        return $id_user;    
    }

    /**
     * Function that verifies if the authenticated user is authorized to the passed module.
     *
     * @param   string   $sModuleName   name of the module to check if the user is authorized
     * @return  boolean    true if the user is authorized, or false if not
     */
    private function _checkUserAuthorized($sModuleName)
    {
        $pACL = $this->_getACL();        
        $id_user = $this->_leerIdUser();
        if (!$pACL->isUserAuthorizedById($id_user, "access", $sModuleName)) { 
            $this->errMsg["fc"] = 'UNAUTHORIZED';
            $this->errMsg["fm"] = 'Not authorized for this module: '.$sModuleName;
            $this->errMsg["fd"] = 'Your user login is not authorized for this functionality. Please contact your system administrator.';
            $this->errMsg["cn"] = get_class($this);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Function that verifies the parameters offset and limit, if offset is not set it will be set to 0
     *
     * @param   integer   $offset   value of offset passed by reference
     * @param   integer   $limit    value of limit passed by reference
     * @return  mixed    true if both parameters are correct, or NULL if an error exists
     */
    private function _checkOffsetLimit(&$offset,&$limit)
    {
        // Validar los parámetros de offset y limit
        if (!isset($offset)) $offset = 0;
        if (!preg_match('/\d+/', $offset)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Invalid offset, must be numeric and positive';
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        if (isset($limit) && !preg_match('/\d+/', $limit)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Invalid limit, must be numeric and positive';
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        return TRUE;
    }

    /**
     * Functional point that consults the address book of the authenticated user. The address book has an intern part that is the list * of available extensions, and an extern part that resides in a SQLITE database managed by Elastix
     *
     * @param   string    $addressBookType    Can be 'internal' or 'external'
     * @param   integer   $offset             (Optional) start of records or 0 if omitted
     * @param   integer   $limit              (Optional) limit records or all if omitted
     * @return  array     Array with the information of the contact list (address book).
     */
    function listAddressBook($addressBookType, $offset, $limit, $id_contact=NULL)
    {
        global $arrConf;

        if (!$this->_checkUserAuthorized('address_book')) return false;

        if (!$this->_checkOffsetLimit($offset, $limit)) return false;

        // Elegir entre la agenda interna y externa
        if (!isset($addressBookType) || 
            !in_array($addressBookType, array('internal', 'external'))) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Unrecognized address book type, must be internal or external';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }

        $extension = array();
        $iNumTotal = NULL;

        $dbAddressBook = $this->_getDB($arrConf['dsn_conn_database']);

        $addressBook = new paloAdressBook($dbAddressBook);
        switch ($addressBookType) {
        case 'internal':
            // Contar número de elementos de la agenda interna
	    if(isset($id_contact)){
		$field_name = "telefono";
		$field_pattern = $id_contact;
	    }
	    else{
		$field_name = NULL;
		$field_pattern = NULL;
	    }
            $rs = $addressBook->getDeviceFreePBX($this->_astDSN, NULL, NULL, $field_name, $field_pattern, TRUE);
            if (!is_array($rs)) {
                $this->errMsg["fc"] = 'DBERROR';
                $this->errMsg["fm"] = 'Database operation failed';
                $this->errMsg["fd"] = 'Unable to count data from internal phonebook';
                $this->errMsg["cn"] = get_class($addressBook);
                return false;
            }
            $iNumTotal = $rs[0]['total'];
            if (!isset($limit)) $limit = $iNumTotal;

            // Recuperar la agenda interna
            $agendaInterna = $addressBook->getDeviceFreePBX($this->_astDSN, $limit, $offset, $field_name, $field_pattern);
            if (!is_array($agendaInterna)) {
                $this->errMsg["fc"] = 'DBERROR';
                $this->errMsg["fm"] = 'Database operation failed';
                $this->errMsg["fd"] = 'Unable to read data from internal phonebook';
                $this->errMsg["cn"] = get_class($addressBook);
                return false;
            }
            $listaEmails = $addressBook->getMailsFromVoicemail();
            foreach ($agendaInterna as $tuplaAgenda) {
		$extension[] = array(
		    'id'    =>  $tuplaAgenda['id'],
		    'phone' =>  $tuplaAgenda['id'],
		    'name'  =>  $tuplaAgenda['description'],
		    'email' =>  ((isset($listaEmails[$tuplaAgenda['id']]) && trim($listaEmails[$tuplaAgenda['id']]) != '') ? $listaEmails[$tuplaAgenda['id']] : NULL),
		);
            }
            break;
        case 'external':
            // Obtener el ID del usuario logoneado
            $id_user = $this->_leerIdUser();
            if (is_null($id_user)) return false;

            /* Contar número de elementos de la agenda externa. Debido a un mal 
             * diseño de la función getAddressBook, se requiere poner un filtro 
             * de mentira, porque de lo contrario, la función ignora id_user, y
             * devuelve los contactos de todos los usuarios. */ 
	    if(isset($id_contact)){
		$field_name = "id";
		$field_pattern = $id_contact;
	    }
	    else{
		$field_name = "name";
		$field_pattern = "%%";
	    }
            $rs = $addressBook->getAddressBook(
                $id_user, NULL, NULL, 
                $field_name, $field_pattern,
                TRUE);

            if (!is_array($rs)) {
                $this->errMsg["fc"] = 'DBERROR';
                $this->errMsg["fm"] = 'Database operation failed';
                $this->errMsg["fd"] = 'Unable to count data from external phonebook - '.$addressBook->_DB->errMsg;
                $this->errMsg["cn"] = get_class($addressBook);
                return false;
            }
            $iNumTotal = $rs[0]['total'];
            if (!isset($limit)) $limit = $iNumTotal;

            /* Recuperar la agenda externa. Debido a un mal diseño de la función
             * getAddressBook, se requiere poner un filtro de mentira, porque
             * de lo contrario, la función ignora id_user, y devuelve los 
             * contactos de todos los usuarios. */ 
            $agendaExterna = $addressBook->getAddressBook(
                $id_user, $limit, $offset, 
                $field_name, $field_pattern, 
                FALSE);
            if (!is_array($agendaExterna)) {
                $this->errMsg["fc"] = 'DBERROR';
                $this->errMsg["fm"] = 'Database operation failed';
                $this->errMsg["fd"] = 'Unable to read data from external phonebook - '.$addressBook->_DB->errMsg;
                $this->errMsg["cn"] = get_class($addressBook);
                return false;
            }
            foreach ($agendaExterna as $tuplaAgenda) {
		$extension[] = array(
		    'id'            =>  $tuplaAgenda['id'],
		    'phone'         =>  $tuplaAgenda['telefono'],
		    'name'          =>  $tuplaAgenda['name'].' '.$tuplaAgenda['last_name'],
		    'first_name'    =>  $tuplaAgenda['name'],
		    'last_name'     =>  $tuplaAgenda['last_name'],
		    'email'         =>  (trim($tuplaAgenda['email']) == '' ? NULL : $tuplaAgenda['email']),
		    'shared'	    =>  ($tuplaAgenda["status"] == 'isPrivate') ? "no" : "yes",
		);
            }
            break;
        }
        return array(
                'totalCount'    =>  $iNumTotal,
                'extension'     => $extension,
            );
    }

    /**
     * Functional point that add a new contact to the external address book of the authenticated user
     *
     * @param   integer   $phone            Can be 'internal' or 'external'
     * @param   string    $first_name       (Optional) First name of the new contact
     * @param   string    $last_name        (Optional) Last name of the new contact
     * @param   string    $email            (Optional) Email of the new contact
     * @return  boolean   True if the contact was successfully added, or false if an error exists
     */
    function addAddressBookContact($phone, $first_name, $last_name, $email, $getIdInserted=FALSE)
    {
        global $arrConf;

        if (!$this->_checkUserAuthorized('address_book')) return false;

        $dbAddressBook = $this->_getDB($arrConf['dsn_conn_database']);
        $addressBook = new paloAdressBook($dbAddressBook);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;
        // Validar que el teléfono está presente y es numérico
        if (!isset($phone) || !preg_match('/^\d+$/', $phone)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Invalid phone, must be numeric string';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }

        // Construir el arreglo de datos que hay que almacenar
        if (!isset($first_name)) $first_name = NULL;
        if (!isset($last_name)) $last_name = NULL;
        if (!isset($email)) $email = NULL;
        $data = array(
            $first_name,
            $last_name,
            $phone,
            $email,
            $id_user,
            NULL,   // picture
            NULL,   // address
            NULL,   // company
            NULL,   // notes
            'isPrivate',    // status
        );
        $result = $addressBook->addContact($data);
        if (!$result) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to write data to external phonebook - '.$addressBook->_DB->errMsg;
            $this->errMsg["cn"] = get_class($addressBook);
            return false;
        }
	if($getIdInserted)
	    return $dbAddressBook->getLastInsertId();
	else
	    return true;
    }

    function updateContact($id, $phone, $first_name, $last_name, $email)
    {
	global $arrConf;

        if (!$this->_checkUserAuthorized('address_book')) return false;

        $dbAddressBook = $this->_getDB($arrConf['dsn_conn_database']);
        $addressBook = new paloAdressBook($dbAddressBook);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

	$contactData = $addressBook->contactData($id,$id_user);
	if(!is_array($contactData) || count($contactData) == 0){
	    $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid id contact';
            $this->errMsg["fd"] = 'Contact do not exist';
            $this->errMsg["cn"] = get_class($this);
            return false;
	}

	if (isset($phone) && !preg_match('/^\d+$/', $phone)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Invalid phone, must be numeric string';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }

	$first_name = (isset($first_name))?$first_name:$contactData["name"];
	$last_name = (isset($last_name))?$last_name:$contactData["last_name"];
	$phone = (isset($phone))?$phone:$contactData["telefono"];
	$email = (isset($email))?$email:$contactData["email"];
	$data = array(
            $first_name,
            $last_name,
            $phone,
            $email,
            $id_user,
            $contactData["picture"],   // picture
            $contactData["address"],   // address
            $contactData["company"],   // company
            $contactData["notes"],   // notes
            $contactData["status"],    // status
        );
	$result = $addressBook->updateContact($data,$id);
	if (!$result) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to write data to external phonebook - '.$addressBook->_DB->errMsg;
            $this->errMsg["cn"] = get_class($addressBook);
            return false;
        }

        return true;
    }

    /**
     * Functional point that deletes a contact of the external address book of the authenticated user
     *
     * @param   integer   $id            ID of the contact to be deleted
     * @return  boolean   True if the contact was successfully deleted, or false if an error exists
     */
    function delAddressBookContact($id)
    {
        global $arrConf;

        if (!$this->_checkUserAuthorized('address_book')) return false;

        $dbAddressBook = $this->_getDB($arrConf['dsn_conn_database']);
        $addressBook = new paloAdressBook($dbAddressBook);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

        // Validar que el ID está presente y es numérico
        if (!isset($id) || !preg_match('/^\d+$/', $id)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Invalid ID, must be positive integer';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }

        // Verificar si el contacto existe y pertenece al usuario logoneado
        $tupla = $addressBook->contactData($id, $id_user);
        if (!is_array($tupla)) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to read data from external phonebook - '.$addressBook->_DB->errMsg;
            $this->errMsg["cn"] = get_class($addressBook);
            return false;
        }

        if (count($tupla) <= 0 || $tupla['iduser'] != $id_user) {
            $this->errMsg["fc"] = 'ADDRESSBOOK';
            $this->errMsg["fm"] = 'Contact lookup failed';
            $this->errMsg["fd"] = 'No contact was found for user '.$_SERVER['PHP_AUTH_USER'];
            $this->errMsg["cn"] = get_class($addressBook);
            return false;
        }

        // Borrar el contacto indicado
        $addressBook->deleteContact($id, $id_user);
        if (isset($tupla['picture']) && $tupla['picture'] != '' && 
            file_exists('/var/www/address_book_images/'.$tupla['picture'])) {
            unlink('/var/www/address_book_images/'.$tupla['picture']);
        }
        return true;
    }

    public function getContactImage($id, $thumbnail)
    {
	global $arrConf;

        if (!$this->_checkUserAuthorized('address_book')) return false;

        $dbAddressBook = $this->_getDB($arrConf['dsn_conn_database']);
        $addressBook = new paloAdressBook($dbAddressBook);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

        // Validar que el ID está presente y es numérico
        if (!isset($id) || !preg_match('/^\d+$/', $id)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Invalid ID, must be positive integer';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }

        // Verificar si el contacto existe y pertenece al usuario logoneado
        $tupla = $addressBook->contactData($id, $id_user);
	if (!is_array($tupla)) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to read data from external phonebook - '.$addressBook->_DB->errMsg;
            $this->errMsg["cn"] = get_class($addressBook);
            return false;
        }

	$ruta_destino = "/var/www/address_book_images";
	$arrIm = explode(".",$tupla['picture']);
	$typeImage = $arrIm[count($arrIm)-1];
	if($thumbnail=="yes"){
	    $imgDefault = $_SERVER['DOCUMENT_ROOT']."/modules/address_book/images/Icon-user_Thumbnail.png";
	    $image = $ruta_destino."/".$id."_Thumbnail.$typeImage";
	}
	else{
	    $imgDefault = $_SERVER['DOCUMENT_ROOT']."/modules/address_book/images/Icon-user.png";
	    $image = $ruta_destino."/".$tupla['picture'];
	}
	if(is_file($image)){
	    if(strtolower($typeImage) == "png"){
		Header("Content-type: image/png"); 
		$im = imagecreatefromPng($image); 
		ImagePng($im); // Mostramos la imagen 
		ImageDestroy($im); // Liberamos la memoria que ocupaba la imagen 
	    }else{
		Header("Content-type: image/jpeg"); 
		$im = imagecreatefromJpeg($image); 
		ImageJpeg($im); // Mostramos la imagen 
		ImageDestroy($im); // Liberamos la memoria que ocupaba la imagen 
	    }
	}else{
	    Header("Content-type: image/png"); 
	    $image = file_get_contents($imgDefault); 
	    return $image;
	}
    }

    /**
     * This function creates a queue for the differential sync
     *
     * @param   string   $data      String containing the JSON data to be sync   
     *
     * @return  mixed    returns the ticket of the queue, or false if an error exists
     */
    public function contactDifferentialSync($data)
    {
        global $arrConf;

        if (!$this->_checkUserAuthorized('address_book')) return false;

        $dbAddressBook = $this->_getDB($arrConf['dsn_conn_database']);
        $addressBook = new paloAdressBook($dbAddressBook);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;
       
        $result = $addressBook->addQueue($data,"contact",$id_user);
        if (!$result) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to write data - '.$addressBook->_DB->errMsg;
            $this->errMsg["cn"] = get_class($addressBook);
            return false;
        }
	else
	    return $result;
    }

    /**
     * This function gets the status of a queue and returns the data to be sync in the client.
     * Uses the long poll method
     *
     * @param   string   $ticket     Ticket of the queue   
     *
     * @return  mixed    returns an array with the data to be sync, or an array with an informative
     *                   message if the timeout has been reached and the queue is still unsolved,
     *			 or false if an error exists
     */
    public function getStatusQueue($ticket)
    {
        if (!$this->_checkUserAuthorized('address_book')) return false;

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

	$this->_ticket = $ticket;

	// Se llama al método definido en la clase LongPoll. Para establecer una conexión permanente con el cliente
	$data = $this->run();
	if(is_null($data)){
	    $result["status"] = "The ticket is still in the queue";
	    return $result;
	}
	elseif($data === false)
	    return false;
	else
	    return $data;
    }

    /**
     * This function gets all the contacts of the authenticated user in the server
     *
     * @return  mixed    returns an array with all the contacts, or false if an error exists
     */
    public function getFullSync()
    {
	global $arrConf;

        if (!$this->_checkUserAuthorized('address_book')) return false;

        $dbAddressBook = $this->_getDB($arrConf['dsn_conn_database']);
        $addressBook = new paloAdressBook($dbAddressBook);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

	$contacts = $addressBook->getUserContacts($id_user);
	if($contacts === FALSE){
	    $this->errMsg["fc"] = 'DBERROR';
	    $this->errMsg["fm"] = 'Database operation failed';
	    $this->errMsg["fd"] = 'Unable to get data - '.$addressBook->_DB->errMsg;
	    $this->errMsg["cn"] = get_class($addressBook);
	    return false;
	}
	else{
	    $result["last_sync"] = time();
	    //TODO: Este work around habrá que quitarlo cuando se cambie en la base el campo "telefono" por "phone"
	    foreach($contacts as $key => $value){
		$contacts[$key]["phone"] = $value["telefono"];
		unset($contacts[$key]["telefono"]);
	    }
	    $result["contacts"] = $contacts;
	    return $result;
	}
    }

    /**
     * This function gets the md5 hash for the data verification integrity of all the contacts
     *
     * @param   string   $fields      String containing the JSON of the fields to be verified    
     *
     * @return  mixed    returns an array with the hash, or false if an error exists
     */
    public function getHash($fields)
    {
	global $arrConf;

        if (!$this->_checkUserAuthorized('address_book')) return false;

        $dbAddressBook = $this->_getDB($arrConf['dsn_conn_database']);
        $addressBook = new paloAdressBook($dbAddressBook);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

	$json = new Services_JSON();
	$fields = $json->decode($fields);

	if(is_array($fields)){
	    //Se eliminan valores repetidos
	    $fields = array_unique($fields);
	    $key = array_search("id",$fields); // Se elimina el campo id en caso de que lo envie el cliente
	    if($key !== FALSE)
		unset($fields[$key]);
	}

	if(!is_array($fields) || count($fields) == 0 ){
	    $this->errMsg["fc"] = 'PARAMERROR';
	    $this->errMsg["fm"] = 'Wrong parameter';
	    $this->errMsg["fd"] = "The parameter \"fields\" must be an array json serialized and must contain at least one value different than \"id\".";
	    $this->errMsg["cn"] = get_class($this);
	    return false;
	}

	//TODO: Este arreglo contiene los campos de la tabla "contact", quiza se deba buscar una manera más eficiente de protegerse contra inyección de sql
	$arrFields = array("id","name","last_name","telefono","extension","email","iduser","picture","address","company","notes","status","last_update");
	$counter = 1;
	$queryFields = "id,";
	foreach($fields as $value){
	    if(!in_array($value,$arrFields)){
		$result["error"] = "Some field/s do not exist in the server";
		return $result;
	    }
	    if($counter == count($fields))
		$queryFields .= $value;
	    else
		$queryFields .= $value.",";
	    $counter++;
	}
	$result = $addressBook->getUserContacts($id_user,$queryFields);
	if($result === FALSE){
	    $this->errMsg["fc"] = 'DBERROR';
	    $this->errMsg["fm"] = 'Database operation failed';
	    $this->errMsg["fd"] = 'Unable to get data - '.$addressBook->_DB->errMsg;
	    $this->errMsg["cn"] = get_class($addressBook);
	    return false;
	}
	$contacts_json = $json->encode($result);
	$hash = md5($contacts_json);
	$response["hash"] = $hash;
	return $response;
    }

    /**
     * This function query the status of the ticket of a queue, if it is unsolved returns NULL,
     * but if it is solved it will get the data to be sync in the client. 
     *
     * @return  mixed    returns an array with the data to be sync in the client, or NULL it the ticket
     *			 is unsolved or false if an error exists
     */
    protected function getData()
    {
	global $arrConf;

	$dbAddressBook = $this->_getDB($arrConf['dsn_conn_database']);
        $addressBook = new paloAdressBook($dbAddressBook);

	$data_ticket = $addressBook->getDataTicket($this->_ticket,$this->_id_user);
	if(is_null($data_ticket)){
	    $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to write data - '.$addressBook->_DB->errMsg;
            $this->errMsg["cn"] = get_class($addressBook);
            return false;
	}
	elseif(!$data_ticket){
	    $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Wrong ticket';
            $this->errMsg["fd"] = "The ticket {$this->_ticket} does not exist or does not belong to you";
            $this->errMsg["cn"] = get_class($addressBook);
            return false;
	}
	else{
	    if($data_ticket["status"] != "OK")
		return null;
	    else{
		$result["status"] = "OK";
		$json = new Services_JSON();
		$data = $json->decode($data_ticket["data"]);
		if(!isset($data->last_sync) || !isset($data->contacts)){
		    $remove_queue = $addressBook->removeQueue($this->_ticket);
		    if($remove_queue === false){
			$this->errMsg["fc"] = 'DBERROR';
			$this->errMsg["fm"] = 'Database operation failed';
			$this->errMsg["fd"] = 'Unable to delete data - '.$addressBook->_DB->errMsg;
			$this->errMsg["cn"] = get_class($addressBook);
			return false;
		    }
		    $this->errMsg["fc"] = 'PARAMERROR';
		    $this->errMsg["fm"] = 'Wrong data';
		    $this->errMsg["fd"] = "The data of the ticket {$this->_ticket} is wrong or corrupted. This data has to be a JSON string containing the keywords \"last_sync\" and \"contacts\". The ticket will be deleted";
		    $this->errMsg["cn"] = get_class($addressBook);
		    return false;
		}
		if(!is_array($data->contacts)){
		    $remove_queue = $addressBook->removeQueue($this->_ticket);
		    if($remove_queue === false){
			$this->errMsg["fc"] = 'DBERROR';
			$this->errMsg["fm"] = 'Database operation failed';
			$this->errMsg["fd"] = 'Unable to delete data - '.$addressBook->_DB->errMsg;
			$this->errMsg["cn"] = get_class($addressBook);
			return false;
		    }
		    $this->errMsg["fc"] = 'PARAMERROR';
		    $this->errMsg["fm"] = 'Wrong data';
		    $this->errMsg["fd"] = "The data of the contacts in ticket {$this->_ticket} is wrong or corrupted. It has to be an array. The ticket will be deleted";
		    $this->errMsg["cn"] = get_class($addressBook);
		    return false;
		}
		$last_sync = $data->last_sync;
		if(isset($data_ticket["response_data"]) && !empty($data_ticket["response_data"]))
		    $response_data = $json->decode($data_ticket["response_data"]);
		else
		    $response_data = array();
		$contacts = $addressBook->getContactsAfterSync($last_sync,$data->contacts,$this->_id_user,$response_data);
		if($contacts === false){
		    $this->errMsg["fc"] = 'DBERROR';
		    $this->errMsg["fm"] = 'Database operation failed';
		    $this->errMsg["fd"] = 'Unable to get data - '.$addressBook->_DB->errMsg;
		    $this->errMsg["cn"] = get_class($addressBook);
		    return false;
		}
		else{
		    $remove_queue = $addressBook->removeQueue($this->_ticket);
		    if($remove_queue === false){
			$this->errMsg["fc"] = 'DBERROR';
			$this->errMsg["fm"] = 'Database operation failed';
			$this->errMsg["fd"] = 'Unable to delete data - '.$addressBook->_DB->errMsg;
			$this->errMsg["cn"] = get_class($addressBook);
			return false;
		    }
		    else{
			$result["last_sync"] = time();
			$result["contacts"] = $contacts;
			return $result;
		    }
		}
	    }
	}
    }

    /**
     * 
     * Function that returns the error message
     *
     * @return  string   Message error if had an error.
     */
    public function getError()
    {
        return $this->errMsg;
    }
}
?>