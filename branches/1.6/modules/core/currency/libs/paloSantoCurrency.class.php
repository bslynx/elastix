<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.4-1                                               |
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
  $Id: paloSantoCurrency.class.php,v 1.1 2008-08-25 05:08:01 jvega jvega@palosanto.com Exp $ */
class paloSantoCurrency {
    var $_DB;
    var $errMsg;

    function paloSantoCurrency(&$pDB)
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

    function ObtainNumCurrency()
    {
        //Here your implementation
        $query   = "SELECT COUNT(*) FROM ";
        /*
        $result=$this->_DB->getFirstRowQuery($query);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
        */

        /*THIS LINE SHOULD BE ERASE*/ return 0; /*THIS LINE SHOULD BE ERASE*/
    }

    function ObtainCurrency($limit, $offset, $field_pattern)
    {
        //Here your implementation
        $query   = "SELECT * FROM ";
        /*
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
        */

        /*THIS LINE SHOULD BE ERASE*/ return array(); /*THIS LINE SHOULD BE ERASE*/
    }

    function loadCurrency()
    {
        $query = "SELECT * FROM settings WHERE key='currency'";

        $result = $this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }

        $result = $result[0];
        $curr = $result['value'];

        return $curr;
    }

    function SaveOrUpdateCurrency($curr)
    {
        if( $this->loadCurrency() == false )//no tiene registro de currency
            $query = "INSERT INTO settings(key,value) values('currency','$curr')";
        else
            $query = "UPDATE settings SET value='$curr' WHERE key='currency'";

        $result = $this->_DB->genQuery($query);
        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }

        return true;
    }
}
?>