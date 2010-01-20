<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.5-9                                               |
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
  $Id: paloSantoSip_buddies.class.php,v 1.2 2010-01-15 12:03:59 Oscar Navarrete anavarre@espol.edu.ec Exp $ */
class paloSantoIax_buddies {
    var $_DB;
    var $errMsg;

    function paloSantoIax_buddies(&$pDB)
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

    function ObtainNumIax_buddies($filter_field, $filter_value)
    {
        //Here your implementation
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT COUNT(*) FROM iax_buddies $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }


    function ObtainIax_buddies($limit=NULL, $offset=NULL, $filter_field=NULL, $filter_value=NULL)
    {
        //Here your implementation
        $where = "";
        if(!empty($filter_field) and !empty($filter_value ))
            $where = "where $filter_field like '%$filter_value%'";

        $query   = "SELECT * FROM iax_buddies $where LIMIT $limit OFFSET $offset";
        //ORDER BY
        //$query .= " ORDER BY name";        

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    
    function addIaxBuddies($data)
    {
        $queryInsert = $this->_DB->construirInsert('iax_buddies', $data);

        echo $queryInsert;
        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }


    function getIaxBuddiesById($id)
    {
        $query   = "SELECT * FROM iax_buddies ";
        $strWhere = "id=$id";

        // Clausula WHERE aqui
        if(!empty($strWhere)) $query .= "WHERE $strWhere ";

        $result=$this->_DB->getFirstRowQuery($query, true);
        return $result;
    }
    

    function updateIaxBuddies($data,$where)
    {
        $queryUpdate = $this->_DB->construirUpdate('iax_buddies', $data,$where);
        $result = $this->_DB->genQuery($queryUpdate);

        return $result;
    }


    function deleteIaxBuddies($id)
    {
        $query = "DELETE FROM iax_buddies WHERE id=$id";
        $result = $this->_DB->genQuery($query);
        if($result[0] > 0)
            return true;
        else return false;
    }

}


?>