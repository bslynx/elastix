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
  $Id: paloSantoExample.class.php,v 1.0 2008/02/30 15:55:57 bmacias Exp $ */

class paloSantoExample {
    var $_DB;
    var $errMsg;

    function paloSantoExample(&$pDB)
    {
        // Receive reference of the paramenter of conection paloDB
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

    function getCDRs($offset, $limit)
    {
        $sQuery="select * from cdr";
        if(!empty($limit)) {
            $sQuery  .= " LIMIT $limit OFFSET $offset";
        }
        $arrayResult = $this->_DB->fetchTable($sQuery,true);
        if (!$arrayResult){
            $error = $this->_DB->errMsg;
        }else{
            if (is_array($arrayResult) && count($arrayResult)>0) {
                //$arrData[$item["null"]] = "No extension";
                /*foreach($arrayResult as $item) {
                    $arrData[$item["extension"]] = $item["extension"];
                }*/
                return $arrayResult;
            }
        }
    }

    function getTotalCDRs()
    {
        $sQuery="select count(*) from cdr;";
        $arrayResult = $this->_DB->getFirstRowQuery($sQuery,false);
        if (!$arrayResult){
            $error = $this->_DB->errMsg;
        }else{
            if (is_array($arrayResult) && count($arrayResult)>0) {
                //$arrData[$item["null"]] = "No extension";
                /*foreach($arrayResult as $item) {
                    $arrData[$item["extension"]] = $item["extension"];
                }*/
                return $arrayResult[0];
            }
        }
    }
}
?>