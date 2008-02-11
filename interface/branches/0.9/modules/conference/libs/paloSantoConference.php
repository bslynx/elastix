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
  $Id: paloSantoCDR.class.php,v 1.1.1.1 2008/01/31 21:31:55 afigueroa Exp $ */

require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";

class paloSantoConference {
    var $_DB;
    var $errMsg;

    function paloSantoConference(&$pDB)
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

    function ObtainConferences($limit, $offset, $date_start="", $date_end="", $field_name="", $field_pattern="", $conference_state)
    {
        $query   = "SELECT roomNo, confDesc, startTime, endTime, maxUser, bookId, roomPass, confOwner, silPass, aFlags, uFlags FROM booking ";

        $strWhere = "";
        if($conference_state=='Past_Conferences') $strWhere .= "endTime<='$date_end'";
        else if($conference_state=='Future_Conferences') $strWhere .= "startTime>='$date_start'";
        else $strWhere .=  "startTime<='$date_start' AND endTime>='$date_end'";

        if(!empty($field_name) and !empty($field_pattern)) $strWhere .= " AND $field_name like '%$field_pattern%' ";

        // Clausula WHERE aqui
        if(!empty($strWhere)) $query .= "WHERE $strWhere ";
        //ORDER BY
        $query .= " ORDER BY startTime ";
        // Limit
        if(!empty($limit)) {
            $query  .= " LIMIT $limit OFFSET $offset";
        }

        $result=$this->_DB->fetchTable($query, true);
        return $result;
    }

    function ObtainConferenceData($bookId)
    {
        $query   = "SELECT roomNo, confDesc, startTime, endTime, maxUser, bookId, roomPass, confOwner, silPass, aFlags, uFlags FROM booking ";

        $strWhere = "bookId=$bookId";

        // Clausula WHERE aqui
        if(!empty($strWhere)) $query .= "WHERE $strWhere ";

        $result=$this->_DB->getFirstRowQuery($query, true);
        return $result;
    }

    function ObtainNumConferences($date_start="", $date_end="", $field_name="", $field_pattern="", $conference_state)
    {
        $queryCount = "SELECT COUNT(*) FROM booking ";

        $strWhere = "";
        if($conference_state=='Past_Conferences') $strWhere .= "endTime<='$date_end'";
        else if($conference_state=='Future_Conferences') $strWhere .= "startTime>='$date_start'";
        else $strWhere .=  "startTime<='$date_start' AND endTime>='$date_end'";

        if(!empty($field_name) and !empty($field_pattern)) $strWhere .= " AND $field_name like '%$field_pattern%' ";

        // Clausula WHERE aqui
        if(!empty($strWhere)) $queryCount .= " WHERE $strWhere";

        $result = $this->_DB->getFirstRowQuery($queryCount);

        return $result;
    }

    function AddConference($data)
    {
        $queryInsert = $this->_DB->construirInsert('booking', $data);
        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }

    function ConferenceNumberExist($number)
    {
        $query = "SELECT COUNT(*) FROM booking WHERE roomNo=$number";
        $result = $this->_DB->getFirstRowQuery($query);
        if($result[0] > 0)
            return true;
        else return false;
    }

    function DeleteConference($BookId)
    {
        $query = "DELETE FROM booking WHERE bookId=$BookId";
        $result = $this->_DB->genQuery($query);
        if($result[0] > 0)
            return true;
        else return false;
    }

    function ObtainCallers($data_connection, $room)
    {
        $command = "meetme list $room";
        $arrResult = $this->AsteriskManager_Command($data_connection['host'], $data_connection['user'], $data_connection['password'], $command);

        $arrCallers = array();
        if(is_array($arrResult) && count($arrResult)>0){
            foreach($arrResult as $Key => $linea){
                if(eregi("^User #:[[:space:]]*([[:digit:]]+)[[:space:]]*([[:alnum:]| ]+)[[:space:]]*Channel: ([[:alnum:]|/|-]+)[[:space:]]*([[:alnum:]|\(|\)| ]+\))[[:space:]]*([[:digit:]|\:]+)$",$linea,$arrReg))
                {
                    $arrCallers[] = array('userId' => $arrReg[1], 'callerId' => $arrReg[2], 'mode' => $arrReg[4], 'duration' => $arrReg[5]);
                }
            }
        }
        return $arrCallers;
    }

    function MuteCaller($data_connection, $room, $userId, $mute)
    {
        if($mute=='on')
            $action = 'mute';
        else
            $action = 'unmute';
        $command = "meetme $action $room $userId";
        $arrResult = $this->AsteriskManager_Command($data_connection['host'], $data_connection['user'], $data_connection['password'], $command);
    }

    function KickCaller($data_connection, $room, $userId)
    {
        $action = 'kick';
        $command = "meetme $action $room $userId";
        $arrResult = $this->AsteriskManager_Command($data_connection['host'], $data_connection['user'], $data_connection['password'], $command);
    }

    function AsteriskManager_Command($host, $user, $password, $command) {
        global $arrLang;
        $astman = new AGI_AsteriskManager( );
        if (!$astman->connect("$host", "$user" , "$password")) {
            $this->errMsg = $arrLang["Error when connecting to Asterisk Manager"];
        } else{
            $salida = $astman->Command("$command");
            $astman->disconnect();
            if ($salida["Response"] != strtoupper("ERROR")) {
                return split("\n", $salida["data"]);
            }
        }
        return false;
    }
}
?>