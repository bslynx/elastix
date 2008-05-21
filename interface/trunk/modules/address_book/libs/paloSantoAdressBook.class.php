<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: paloSantoCDR.class.php,v 1.1.1.1 2008/01/31 21:31:55 bmacias Exp $ */

//ini_set("display_errors", true);
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";

class paloAdressBook {
    var $_DB;
    var $errMsg;

    function paloAdressBook(&$pDB)
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

    function getAddressBook($limit=NULL, $offset=NULL, $field_name="", $field_pattern="",$count=FALSE)
    {
	
	$fields=($count)?"count(id) as total":"*";
        $query   = "SELECT $fields FROM contact ";

        $strWhere = "";

        if(!empty($field_name) and !empty($field_pattern)) $strWhere .= " AND $field_name like '%$field_pattern%' ";

        // Clausula WHERE aqui
        if(!empty($strWhere)) $query .= "WHERE $strWhere ";

        //ORDER BY
        $query .= " ORDER BY  id";

        // Limit
        if(!is_null($limit))
            $query .= " LIMIT $limit ";

	if(!is_null($offset) and $offset > 0)
	    $query .= " OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true);

        return $result;
    }

    function contactData($id)
    {
        $query   = "SELECT * FROM contact ";

        $strWhere = "id=$id";

        // Clausula WHERE aqui
        if(!empty($strWhere)) $query .= "WHERE $strWhere ";

        $result=$this->_DB->getFirstRowQuery($query, true);
        return $result;
    }

    function addContact($data)
    {
        $queryInsert = $this->_DB->construirInsert('contact', $data);
	echo "$queryInsert";
        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }

    function deleteContact($id)
    {
        $query = "DELETE FROM contact WHERE id=$id";
        $result = $this->_DB->genQuery($query);
        if($result[0] > 0)
            return true;
        else return false;
    }

}
?>
