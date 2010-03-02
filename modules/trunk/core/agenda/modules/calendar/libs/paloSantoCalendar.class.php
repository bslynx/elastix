<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-7                                               |
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
  $Id: paloSantoCalendar.class.php,v 1.1 2010-01-05 11:01:26 Bruno Macias V. bmacias@elastix.org Exp $ */
class paloSantoCalendar {
    var $_DB;
    var $errMsg;

    function paloSantoCalendar(&$pDB)
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

    function getNumCalendar($filter_field, $filter_value)
    {
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT COUNT(*) FROM table $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getCalendar($limit, $offset, $filter_field, $filter_value)
    {
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT * FROM table $where LIMIT $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getEventById($id)
    {
        $query = "SELECT * FROM events WHERE id=$id";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function get_events_by_date($day, $month, $year)
    {
        /* event types:
        1 - Normal event
        2 - full day event
        3 - unknown time event
        4 - reserved
        5 - weekly event
        6 - monthly event
        */

        $startdate = "strftime('%Y-%m-%d', startdate)";
        $enddate = "strftime('%Y-%m-%d', enddate)";
        $date = "'" . date('Y-m-d', mktime(0, 0, 0, $month, $day, $year))
                . "'";

        // day of week
        $dow_startdate = "strftime('%w', startdate)";
        $dow_date = "strftime('%w', $date)";

        // day of month
        $dom_startdate = "strftime('%d', startdate)";
        $dom_date = "strftime('%d', $date)";

        $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
        $uid  = $this->Obtain_UID_From_User($user);

        $query = "SELECT * FROM events\n"
            ."WHERE $date >= $startdate AND $date <= $enddate\n"
                    // find normal events
                    ."AND (eventtype = 1 OR eventtype = 2 OR eventtype = 3 "
                    ."OR eventtype = 4\n"
                    // find weekly events
            ."OR (eventtype = 5 AND $dow_startdate = $dow_date)\n"
                    // find monthly events
            ."OR (eventtype = 6 AND $dom_startdate = $dom_date)\n"
                    .")\n"
            ."AND uid = $uid "
            ."ORDER BY starttime";

        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    // returns the event that corresponds to $id
    function get_event_by_id($id)
    {
        $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
        $uid  = $this->Obtain_UID_From_User($user);

        $query = "SELECT 
                    id,
                    uid,
                    subject event, 
                    startdate date, 
                    enddate `to`, 
                    description, 
                    asterisk_call asterisk_call_me, 
                    recording, 
                    starttime,
                    endtime,
                    call_to, 
                    notification, 
                    emails_notification, 
                    each_repeat as repeat,
                    days_repeat,
                    eventtype as it_repeat,
                    strftime('%H', starttime) hora1, 
                    strftime('%M', starttime) minuto1,
                    strftime('%H', endtime) hora2, 
                    strftime('%M', endtime) minuto2,
                    strftime('%Y', startdate) AS year,\n"
                    ."strftime('%m', startdate) AS month,\n"
                    ."strftime('%d', startdate) AS day,\n"
                    ."strftime('%Y', enddate) AS end_year,\n"
                    ."strftime('%m', enddate) AS end_month,\n"
                    ."strftime('%d', enddate) AS end_day\n"
            ."FROM 
                events\n"
            ."WHERE 
                id = '$id'\n"
                ."AND uid=$uid";
        $result = $this->_DB->getFirstRowQuery($query, true);
        if($result == FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }

        if(!is_array($result) || count($result) == 0) {
            $this->errMsg = "item doesn't exist!";
            return array();
        }
        return $result;
    }

    function Obtain_UID_From_User($user)
    {
        global $pACL;
        $uid = $pACL->getIdUser($user);
        if($uid!=FALSE)
            return $uid;
        else return -1;
    }

    function obtainExtension($db,$id){
        $query = "SELECT extension FROM acl_user WHERE id=$id";

        $result = $db->getFirstRowQuery($query,true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result['extension'];
    }

    function Obtain_Recordings_Current_User()
    {
        global $pACL;
        global $arrConf;
        $archivos = array();

        $username = $_SESSION["elastix_user"];
        $ext = $pACL->getUserExtension($username);
        if($ext){
            $folder_path = "/var/lib/asterisk/sounds/custom";
            $path = "$folder_path/$ext";

            $retval = 0;
            if(!file_exists($path)){
                $comando = "mkdir -p $path";
                exec($comando, $output, $retval);
                if ($retval==0){
                    $comando = "ln -s $folder_path/calendarEvent.gsm $path/calendarEvent.gsm";
                    exec($comando, $output, $retval);
                }
            }

            if(!$retval){
                if ($handle = opendir($path)) {
                    while (false !== ($dir = readdir($handle))) {
                        if (ereg("(.*)\.[gsm$|wav$]", $dir, $regs)) {
                            $archivos[$regs[1]] = $regs[1];
                        }
                    }
                }
            }
        }
        return $archivos;
    }

    function insertEvent($uid,$startdate,$enddate,$starttime,$eventtype,$subject,$description,$asterisk_call,$recording,$call_to,$notification,$email_notification, $endtime, $each_repeat,  $checkbox_days){
        $query = "INSERT INTO events(uid,startdate,enddate,starttime,eventtype,subject,description,asterisk_call,recording,call_to,notification,emails_notification,endtime,each_repeat,days_repeat) VALUES($uid,'$startdate','$enddate','$starttime',$eventtype,'$subject','$description','$asterisk_call','$recording','$call_to','$notification','$email_notification','$endtime',$each_repeat,'$checkbox_days')";
        $result = $this->_DB->genQuery($query);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true; 
    }

    function updateEvent($id_event,$startdate,$enddate,$starttime,$eventtype,$subject,$description,$asterisk_call,$recording,$call_to,$notification,$email_notification, $endtime ,$each_repeat,$checkbox_days){
        $query = "UPDATE events SET  startdate='$startdate',enddate='$enddate',starttime='$starttime',eventtype=$eventtype,subject='$subject',description='$description',asterisk_call='$asterisk_call',recording='$recording',call_to='$call_to',notification='$notification',emails_notification='$email_notification',endtime='$endtime',each_repeat=$each_repeat,days_repeat='$checkbox_days' WHERE id=$id_event";
        
        $result = $this->_DB->genQuery($query);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true; 
    }

    function updateDateEvent($id_event,$startdate,$enddate,$starttime,$endtime){
        $query = "UPDATE events SET  startdate='$startdate',enddate='$enddate',starttime='$starttime',endtime='$endtime' WHERE id=$id_event";

        $result = $this->_DB->genQuery($query);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true; 
    }

    function deleteEvent($id_event){
        $query = "DELETE FROM events WHERE id=$id_event";

        $result = $this->_DB->genQuery($query);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true; 
    }

    function getContactByTag($db, $tag, $userid)
    {
        $query = "SELECT  (lower(name)||' '||lower(last_name)||' '||'&lt;'||email||'&gt;') AS caption,id AS value FROM contact WHERE iduser = $userid";

        $result = $db->fetchTable($query,true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getEventByDate($startdate, $enddate){
        $query = "SELECT * FROM events WHERE (startdate <= '$startdate' AND enddate >= '$enddate') OR (startdate >= '$startdate' AND enddate <= '$enddate') OR (startdate <= '$startdate' AND enddate >= '$startdate') OR (startdate >= '$startdate' AND enddate >= '$enddate')";
        $result = $this->_DB->fetchTable($query,true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getAllEvents(){
        $query = "SELECT * FROM events";
        $result = $this->_DB->fetchTable($query,true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }
}
?>
